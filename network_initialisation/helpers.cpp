// Supress warnings concerning typenames that are too long and have been truncated
#ifdef MSVC
	#pragma warning(disable : 4503)
#endif

#include "helpers.h"
#include <vector>
#include <sstream>
#include <string>
#include <algorithm>
#include "Sensor.h"
#include <map>
#include "NoConnectionException.h"
#include <thread>
#include <iostream>
#include <chrono>
#include <fstream>
#include <mutex>
#include <set>
#ifdef _WIN32
#include "dirent.h"
#else
#include <dirent.h>
#endif

extern std::mutex mutex_cout, mutex_whitelist_updated, mutex_failures, mutex_sensors;

std::vector<std::string> &split(const std::string &s, char delim, std::vector<std::string> &elems)
{
	std::stringstream ss(s);
	std::string item;
	while (std::getline(ss, item, delim)) {
		elems.push_back(item);
	}
	return elems;
}

std::vector<std::string> split(const std::string &s, char delim)
{
	std::vector<std::string> elems;
	split(s, delim, elems);
	return elems;
}

double median_rssi(std::vector<double> rssis)
{
	size_t n = rssis.size() / 2;
	std::nth_element(rssis.begin(), rssis.begin() + n, rssis.end());
	double rssisn = rssis[n];

	if (rssis.size() % 2 == 1)
		return rssisn;
	else
	{
		std::nth_element(rssis.begin(), rssis.begin() + n - 1, rssis.end());
		return 0.5*(rssisn + rssis[n - 1]);
	}
}




// Gets names of all files in `directory` with `extension`
std::vector<std::string> get_file_list(std::string directory, std::string extension)
{
	std::vector<std::string> logs;
	DIR* dir_point = opendir(directory.c_str());
	dirent* file = readdir(dir_point);
	while (file)
	{
		std::string filename = file->d_name;
		if (filename.find(extension, filename.length() - extension.length()) != std::string::npos)
			logs.push_back(filename);

		file = readdir(dir_point);
	}
	
	return logs;
}



void update_whitelist(std::map<std::string, std::vector<std::string>> &whitelist, std::map<std::string, Sensor> &sensors, std::set<std::string> &failures, bool &updated)
{
	while (true)
	{

		std::lock_guard<std::mutex> lock_whitelist(mutex_whitelist_updated);
		std::lock_guard<std::mutex> lock_sensors(mutex_sensors);
		std::lock_guard<std::mutex> lock_failures(mutex_failures);

		if (!updated)
		{
			/* Processing new sensors */
			// For every sensor
			for (auto &s : sensors)
			{
				std::string strongest_trans;
				std::vector<std::string> connectionList = s.second.connectionList();
				if (connectionList.size() == 0)
					continue;
				else
					strongest_trans = connectionList[0];

				
					// If the sensor is not yet found in the whitelist of its strongest connected transceiver, add it there
				if (std::find(whitelist[strongest_trans].begin(), whitelist[strongest_trans].end(), s.first) == whitelist[strongest_trans].end())
				{
					whitelist[strongest_trans].push_back(s.first);

					// Mark a flag so that check_for_updates() knows to send out a new whitelist
					updated = true;
				}
			}

			/* Processing failed transceievers */
			// For every transciever in the whitelist
			for (auto &w : whitelist) // w.first is transID, w.second is vector of sensors 
			{
				std::string transID = w.first;
				std::vector<std::string> &whitelisted_sensors = w.second;

				// If it has failed (i.e. is found in failures vector)
				if (failures.find(transID) != failures.end())
				{
					// If the transciever was connected to a transceiver
					if (whitelisted_sensors.size() > 0)
					{
						// For every sensor that was connected to it
						for (auto &sensorID : whitelisted_sensors)
						{
							// Remove the connection in the sensor's connection list
							sensors[sensorID].del_connection(transID);

							// Remove the sensor in the transceiver's whitelist
							// Assign a null value now and remove later as removing elements while iterating over the container mixes up the iteration
							std::find(whitelist[transID].begin(), whitelist[transID].end(), sensorID)->assign("null");

							// If that sensor is no longer connected to any transcievers as a result of the above pruning
							if (sensors[sensorID].connectionList().size() == 0)
							{
								// Add the sensor to the whitelist of all transceivers to try to find a new route
								for (auto &transceiver : whitelist)
								{
									// For every transciever other than the current one
									if (transceiver != w)
									{
										// If the sensor was not already in the whitelist
										if (!(std::find(transceiver.second.begin(), transceiver.second.end(), sensorID) != transceiver.second.end()))
										{
											// Add it
											transceiver.second.push_back(sensorID);
										}
									}
								}
							}

							// Otherwise, assign that sensorID to its next strongest connected transceiver
							else
							{
								whitelist[sensors[sensorID].connectionList()[0]].push_back(sensorID);
							}
						}

						// Remove null marked transceivers
						whitelisted_sensors.erase(std::remove(whitelisted_sensors.begin(), whitelisted_sensors.end(), "null"), whitelisted_sensors.end());
						// Mark a flag so that check_for_updates() knows to send out a new whitelist
						updated = true;
					}
				}
			}

			
			std::this_thread::sleep_for(std::chrono::seconds(1));
		}		
	}

}

// Checking for updated whitelist
void check_for_update(std::string blacklistfilename, std::map<std::string,  std::vector<std::string>> &whitelist, bool &updated)
{
	while (true)
	{
		std::lock_guard<std::mutex> lock_whitelist(mutex_whitelist_updated);

		if (updated)
		{
			// Create blacklist here instead of creating in real time because new nodes may be added
			std::map<std::string, std::vector<std::string>> blacklist;
			// Send out new whitelist
			//std::map<std::string, std::vector<std::string>>::const_iterator wl_iter;

			// For every transciever in the whitelist
			for (auto wl_iter = whitelist.begin(); wl_iter != whitelist.end(); ++wl_iter)
			{
				// Add its sensors to the blacklist of every other transciever
				for (auto bl_iter = whitelist.begin(); bl_iter != whitelist.end(); ++bl_iter)
				{
					if (bl_iter != wl_iter)
					{
						for (unsigned int i = 0; i < wl_iter->second.size(); i++)
						{
							blacklist[bl_iter->first].push_back(wl_iter->second[i]);
						}
					}
				}
			}

			std::ofstream blacklistfile(blacklistfilename.c_str());
			// Write blacklist to file
			for (auto output_iter = blacklist.begin(); output_iter != blacklist.end(); ++output_iter)
			{
				blacklistfile << output_iter->first << " " << output_iter->second << "\n";
			}
			blacklistfile << std::flush;
			blacklistfile.close();
			// Flip updated bool so that whitelist can be updated again
			//updated_lock.lock();
			updated = false;
			//updated_lock.lock();
		}

		std::this_thread::sleep_for(std::chrono::seconds(1));
	}
}

// Read logfiles and adds most recent rssi values to respective sensor objects
void update_sensors(std::map<std::string, Sensor> &sensors, std::string logfile_name)
{
	std::string timestamp, transcode, payload;
	std::ifstream logfile(logfile_name.c_str());
	while (true)
	{
		std::lock_guard<std::mutex> lock_sensors(mutex_sensors);
		while (logfile >> timestamp >> transcode >> payload)
		{
			std::string sensorID = payload;
			sensorID.erase(16, 2).erase(0, 8);
			double rssi = stoul(payload.erase(0, 16), nullptr, 16);
			std::string transID = split(transcode, '_')[2];

			// Add new data about rssi between sensor and transceiver
			sensors[sensorID].add_rssi(transID, rssi);
		}
		std::this_thread::sleep_for(std::chrono::seconds(1));
		if (!logfile.eof())
			break;
		logfile.clear();
	}

}
