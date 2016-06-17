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

		std::lock_guard<std::mutex> lock_whitelist_updated(mutex_whitelist_updated);
		std::lock_guard<std::mutex> lock_sensors(mutex_sensors);
		std::lock_guard<std::mutex> lock_failures(mutex_failures);

		if (!updated)
		{
			/* Updating sensors' strongest connections */
			// For every sensor
			for (auto &s : sensors)
			{
				std::string sensorID = s.first;
				std::string strongest_trans;
				std::vector<std::string> connectionList = s.second.connectionList();

				// Send msg to db saying how many connections a sensor has
				std::stringstream ss;
				int size = connectionList.size();
				ss << size;
				std::string exec = "python update_connections.py " + sensorID + " " + ss.str();
				system(exec.c_str());

				
				if (size == 0)
					continue;
				else
				{
					strongest_trans = connectionList[0];


					//// Add to blacklist of all others
					//for (auto &w : whitelist)
					//{
					//	std::vector<std::string> &sensorlist = w.second;
					//	if (std::find(sensorlist.begin(), sensorlist.end(), sensorID) == sensorlist.end())
					//		sensorlist.push_back(sensorID);
					//}
					//// Remove from blacklist of strongest_trans
					//whitelist[strongest_trans].erase(std::remove(whitelist[strongest_trans].begin(), whitelist[strongest_trans].end(), sensorID), whitelist[strongest_trans].end());
					//
					//updated = true;

				}
				
					// If the sensor is not yet found in the whitelist of its strongest connected transceiver
				if (std::find(whitelist[strongest_trans].begin(), whitelist[strongest_trans].end(), s.first) == whitelist[strongest_trans].end())
				{
					
					// Add it there
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
					// If the transciever was connected to a sensor
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
void check_for_update(std::string blacklistfilename, std::map<std::string,  std::vector<std::string>> &whitelist, std::set<std::string> &db_transceievers, bool &updated)
{
	while (true)
	{
		std::lock_guard<std::mutex> lock_whitelist_updated(mutex_whitelist_updated);

		if (updated)
		{
			// Create blacklist here instead of creating in real time because new nodes may be added
			std::map<std::string, std::vector<std::string>> blacklist;
			// Send out new blacklist

			// For every transciever in the whitelist
			for (auto &wl_iter : whitelist)
			{
				std::string wl_transID = wl_iter.first;
				std::vector<std::string> &wl_sensors = wl_iter.second;
				// Add its sensors to the blacklist of every other transciever
				for (auto &bl_iter : whitelist)
				{
					std::string bl_transID = bl_iter.first;
					std::vector<std::string> &bl_sensors = bl_iter.second;
					if (bl_iter != wl_iter)
					{
						for (unsigned int i = 0; i < wl_sensors.size(); i++)
						{
							blacklist[bl_transID].push_back(wl_sensors[i]);
						}
					}
				}
			}

			std::ofstream blacklistfile(blacklistfilename.c_str());
			// Write blacklist to file
			for (auto output_iter = blacklist.begin(); output_iter != blacklist.end(); ++output_iter)
			{
				blacklistfile << output_iter->first;
				for (auto &id : output_iter->second)
					blacklistfile << " " << id;
				for (auto &t : db_transceievers) // Need to add all transceiver ids so messages are not duplicated
					blacklistfile << " " << t;
				blacklistfile << "\n";
			}
			blacklistfile << std::flush;
			blacklistfile.close();

			// Flip updated bool so that whitelist can be updated again
			updated = false;

			// Send out new blacklists
			std::string exec = "python distribute_blacklist.py " + blacklistfilename;
			system(exec.c_str());
		}

		std::this_thread::sleep_for(std::chrono::seconds(1));
	}
}

// Read logfiles and adds most recent rssi values to respective sensor objects
void update_rssis(std::map<std::string, Sensor> &sensors, std::string logfile_name, std::set<std::string> &db_sensors, std::set<std::string> &db_transceivers)
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

			// If one of our nodes
			if (db_sensors.find(sensorID) != db_sensors.end() && db_transceivers.find(transID) != db_transceivers.end())
			{
				// Add new data about rssi between sensor and transceiver
				sensors[sensorID].add_rssi(transID, rssi);
			}
		}
		std::this_thread::sleep_for(std::chrono::seconds(5));
		if (!logfile.eof())
			break;
		logfile.clear();
	}

}



void add_new_sensors(std::string sensorsfilename, std::set<std::string> &db_sensors, std::map<std::string, Sensor> &sensors)
{
	std::ifstream sensorsfile(sensorsfilename.c_str());
	std::string sensorID;
	while (true)
	{
		while (sensorsfile >> sensorID)
		{
			db_sensors.insert(sensorID);
			sensors[sensorID] = Sensor(sensorID, 10);
		}
		std::this_thread::sleep_for(std::chrono::seconds(300));
		if (!sensorsfile.eof())
			break;
		sensorsfile.clear();
	}
}

void add_new_trans(std::string transfilename, std::set<std::string> &db_transceivers, std::map<std::string, std::vector<std::string>> &whitelist)
{
	std::ifstream transfile(transfilename.c_str());
	std::string transID;
	while (true)
	{
		while (transfile >> transID)
		{
			db_transceivers.insert(transID);
			whitelist[transID];
		}
		std::this_thread::sleep_for(std::chrono::seconds(300));
		if (!transfile.eof())
			break;
		transfile.clear();
	}

}