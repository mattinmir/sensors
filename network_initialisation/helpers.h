#ifndef HELPERS_H
#define HELPERS_H

#include <vector>
#include <sstream>
#include <string>
#include <algorithm>
#include "Sensor.h"
#include "Connection.h"
#include "dirent.h"
#include <map>
#include "NoConnectionException.h"
#include <mutex>
#include <shared_mutex>
#include <thread>
#include <iostream>
#include <chrono>

std::vector<std::string> &split(const std::string &s, char delim, std::vector<std::string> &elems) {
	std::stringstream ss(s);
	std::string item;
	while (std::getline(ss, item, delim)) {
		elems.push_back(item);
	}
	return elems;
}

std::vector<std::string> split(const std::string &s, char delim) {
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

void new_connection(std::vector<Sensor> &sensors, std::string transID, std::string sensorID, double rssi)
{
	// If sensor exists already, add the connection to it
	for (unsigned int i = 0; i < sensors.size(); i++)
	{
		if (sensors[i].getSensorID() == sensorID)
		{
			sensors[i].add_connection(Connection(transID, rssi));
			return;
		}
	}

	// If sensor does not already exist, create a new sensor, then add the conneciton to it
	sensors.push_back(Sensor(sensorID));
	sensors.back().add_connection(Connection(transID, rssi));

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


void generate_whitelist(std::map<std::string, std::vector<std::string>> &whitelist, std::vector<std::string> failures, std::map<std::string, std::vector<std::string>> connections)
{
	// Iterate over every sensor-transciever vector pair in the connections map
	std::map<std::string, std::vector<std::string>>::iterator iter;
	for (iter = connections.begin(); iter != connections.end(); ++iter)
	{
		unsigned int i = 0;

		// Increment i until a non-failed transceiver is found in the connections list
		while (std::find(failures.begin(), failures.end(), iter->second[0]) != failures.end())
		{
			iter->second.erase(iter->second.begin()); // Remove dead transciever from connection list

			i++;
			// If we have iterated over the entire list of connections and all are failed
			if (i == connections.size())
				throw NoConnectionException(iter->first);
		}

		// Add the current sensor to the non-failed transceiver's whitelist
		whitelist[iter->second[0]].push_back(iter->first);
	}
}

void update_whitelist(std::map<std::string, std::vector<std::string>> &whitelist, std::vector<std::string> &failures, std::map<std::string, std::vector<std::string>> &connections, bool &updated, std::mutex &failures_lock, std::mutex &updated_lock)
{
	while (true)
	{
		try
		{
			// Waits for new whitelist to be sent before checking for updates again
			if (!updated)
			{
				// Iterate over every transceiver-sensor vector pair in the whitelist map
				std::map<std::string, std::vector<std::string>>::iterator iter;
				for (iter = whitelist.begin(); iter != whitelist.end(); ++iter)
				{
					//failures_lock.lock();
					// If that transceiver has failed
					if (std::find(failures.begin(), failures.end(), iter->first) != failures.end())
					{
						for (unsigned int i = 0; i < iter->second.size(); i++)
						{
							// Remove dead transceiver ID from its sensors' connections lists (connections list should still remain sorted)
							// iter->second[i] is the ith sensor currently connected to the dead transceiver
							// So connections[iter->second[i]] is the vector of transceivers keyed by the sensor iter->second[i] in the connections map
							connections[iter->second[i]].erase(std::remove(connections[iter->second[i]].begin(), connections[iter->second[i]].end(), iter->first), connections[iter->second[i]].end());

							if (connections[iter->second[i]].size() == 0)
								throw NoConnectionException(iter->second[i]);

							// connections[iter->second[i]][0] is the strongest transceiver for the sensor iter->second[i]
							// So whitelist[connections[iter->second[i]][0]] is the entry in the whitelist keyed by that transceiver 
							// We add the sensor to the whitelist for that transceiver
							else
								whitelist[connections[iter->second[i]][0]].push_back(iter->second[i]);
						}
						//updated_lock.lock();
						updated = true;
						//updated_lock.unlock();
					}
					//failures_lock.unlock();
				}
				std::this_thread::sleep_for(std::chrono::seconds(1));

			}
		}

		catch(const NoConnectionException &e)
		{
			std::cout << e.what() << '\n';
			// TODO: Send a message to DB saying this sensor is cut off from the network
		}
		/*catch (const std::exception &e)
		{
			std::cout << e.what() << '\n';
		}*/
	}
}

// Checking for updated whitelist
void check_for_update(bool &updated, std::mutex &updated_lock)
{
	while (true)
	{
		if (updated)
		{
			// TODO: send out new whitelist

			// Flip updated bool so that whitelist can be updated again
			//updated_lock.lock();
			updated = false;
			//updated_lock.lock();
		}

		std::this_thread::sleep_for(std::chrono::seconds(1));
	}
}


// Overloading operator<< for vectors
template < class T >
inline std::ostream& operator<< (std::ostream& os, const std::vector<T>& v)
{
	os << *v.begin();
	for (std::vector<T>::const_iterator ii = v.begin() + 1; ii != v.end(); ++ii)
		os << ", " << *ii;
	
	return os;
}
#endif // !HELPERS_H
