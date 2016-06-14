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
#include "Connection.h"
#include "dirent.h"
#include <map>
#include "NoConnectionException.h"
#include <thread>
#include <iostream>
#include <chrono>
#include <fstream>
#include <mutex>

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


//void new_connection(std::vector<Sensor> &sensors, std::string transID, std::string sensorID, double rssi)
//{
//	// If sensor exists already, add the connection to it
//	for (unsigned int i = 0; i < sensors.size(); i++)
//	{
//		if (sensors[i].getSensorID() == sensorID)
//		{
//			sensors[i].add_connection(Connection(transID, rssi));
//			return;
//		}
//	}
//
//	// If sensor does not already exist, create a new sensor, then add the conneciton to it
//	sensors.push_back(Sensor(sensorID));
//	sensors.back().add_connection(Connection(transID, rssi));
//
//}

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

//
//void generate_whitelist(std::map<std::string, std::vector<std::string>> &whitelist, std::vector<std::string> failures, std::map<std::string, std::vector<std::string>> connections)
//{
//	// Iterate over every sensor-transciever vector pair in the connections map
//	std::map<std::string, std::vector<std::string>>::iterator iter;
//	for (iter = connections.begin(); iter != connections.end(); ++iter)
//	{
//		unsigned int i = 0;
//
//		// Increment i until a non-failed transceiver is found in the connections list
//		while (std::find(failures.begin(), failures.end(), iter->second[0]) != failures.end())
//		{
//			iter->second.erase(iter->second.begin()); // Remove dead transciever from connection list
//
//			i++;
//			// If we have iterated over the entire list of connections and all are failed
//			if (i == connections.size())
//				throw NoConnectionException(iter->first);
//		}
//
//		// Add the current sensor to the non-failed transceiver's whitelist
//		whitelist[iter->second[0]].push_back(iter->first);
//	}
//}

void update_whitelist(std::map<std::string, std::vector<std::string>> &whitelist, std::map<std::string, Sensor> &sensors, std::vector<std::string> &failures, bool &updated)
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
				try
				{
					strongest_trans = s.second.connectionList()[0];
				}
				// If there are no connections for the sensor, do nothing
				catch(NoConnectionException)
				{
					continue;
				}
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
			for (auto &w : whitelist)
			{
				// If it has failed (i.e. is found in failures vector)
				if (std::find(failures.begin(), failures.end(), w.first) != failures.end())
				{
					// For every sensor that was connected to it
					for (auto &sensorID : w.second)
					{
						sensors[sensorID].del_connection(w.first);

						// If that sensor is no longer connected to any transcievers as a result of the above pruning
						if (sensors[sensorID].connectionList().size() == 0)
						{
							// Erase it from the vector of sensors
							sensors.erase(sensorID);
						}

						// Otherwise, assign that sensorID to its next strongest connected transceiver
						else
						{
							whitelist[sensors[sensorID].connectionList()[0]].push_back(sensorID);
						}
					}

					// Mark a flag so that check_for_updates() knows to send out a new whitelist
					updated = true;
				}
			}

			
			std::this_thread::sleep_for(std::chrono::seconds(1));
		}		
	}

	//while (true)
	//{
	//	try
	//	{
	//		// Waits for new whitelist to be sent before checking for updates again
	//		if (!updated)
	//		{
	//			// Iterate over every transceiver-sensor vector pair in the whitelist map
	//			std::map<std::string, std::vector<std::string>>::iterator wl_iter;
	//			for (wl_iter = whitelist.begin(); wl_iter != whitelist.end(); ++wl_iter)
	//			{
	//				//failures_lock.lock();
	//				// If that transceiver has failed
	//				if (std::find(failures.begin(), failures.end(), wl_iter->first) != failures.end())
	//				{
	//					for (unsigned int i = 0; i < wl_iter->second.size(); i++)
	//					{
	//						// Remove dead transceiver ID from its sensors' connections lists (connections list should still remain sorted)
	//						// wl_iter->second is the list of sensors currently connected to the dead transceiver
	//						// So connections[wl_iter->second[i]] is the vector of transceivers keyed by the sensor wl_iter->second[i], in the connections map

	//						// std::remove(begin, end, val) removes all instances of val in the ranges of the iterators begin and end
	//						// It keeps empty spaces at the end equal to the number of elements removed
	//						// The function returns an iterator to the element after the last non-removed element in the vector
	//						// So here we remove failed transceiver ids from the vector of transcievers for every sensor that was connected to it, and return an iterator to the space that transciever used to be in
	//						// which is now at the end of the array

	//						// vector::erase(start, end) erases elements from between iterators start and end (inclusively)
	//						// So we are removing that empty space
	//						connections[wl_iter->second[i]].erase(std::remove(connections[wl_iter->second[i]].begin(), connections[wl_iter->second[i]].end(), wl_iter->first), connections[wl_iter->second[i]].end());

	//						// If sensor is no longer connected to any transceivers as a result of the above pruning
	//						if (connections[wl_iter->second[i]].size() == 0)
	//						{
	//							// Remove sensor from the list of connections
	//							//	connections.erase(connections.find(wl_iter->second[i]));


	//							//throw NoConnectionException(wl_iter->second[i]);						
	//						}

	//						// connections[wl_iter->second[i]][0] is the strongest transceiver for the sensor wl_iter->second[i]
	//						// So whitelist[connections[wl_iter->second[i]][0]] is the entry in the whitelist keyed by that transceiver 
	//						// We add the sensor to the whitelist for that transceiver
	//						else
	//							whitelist[connections[wl_iter->second[i]][0]].push_back(wl_iter->second[i]);
	//					}
	//					//updated_lock.lock();
	//					updated = true;
	//					//updated_lock.unlock();
	//				}
	//				//failures_lock.unlock();
	//			}
	//			std::this_thread::sleep_for(std::chrono::seconds(1));

	//		}
	//	}

	//	catch (const NoConnectionException &e)
	//	{
	//		std::cout << e.what() << '\n';
	//		// TODO: Send a message to DB saying this sensor is cut off from the network
	//	}
	//	/*catch (const std::exception &e)
	//	{
	//	std::cout << e.what() << '\n';
	//	}*/
	//}
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