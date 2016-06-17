#include <ctime> 
#include <string>
#include <vector>
#include <fstream>
#include "helpers.h"
#include <thread>
#include <map>
#include <chrono>
#include "node_failure.h"
#include <mutex>

extern bool DEBUG;
extern std::mutex mutex_cout, mutex_whitelist, mutex_updated, mutex_failures, mutex_sensors, mutex_last_seen;

// Timestamp of format 2016-05-31_16:34:50
std::tm convert_timestamp(std::string timestamp)
{
	std::tm converted;

	std::string date = split(timestamp, '_')[0];
	converted.tm_year = atoi(split(date, '-')[0].c_str()) - 1900; // Year has offset of 1900
	converted.tm_mon = atoi(split(date, '-')[1].c_str()) - 1; // Month goes from 0-11
	converted.tm_mday = atoi(split(date, '-')[2].c_str());

	std::string time = split(timestamp, '_')[1];
	converted.tm_hour = atoi(split(time, ':')[0].c_str());
	converted.tm_min = atoi(split(time, ':')[1].c_str());
	converted.tm_sec = atoi(split(time, ':')[2].c_str());

	converted.tm_isdst = -1; // mktime will calculate daylight savings itself

	return converted;
}

// Timeout in seconds
bool failed(std::tm timestamp, double timeout)
{
	time_t now, ts;
	time(&now);

	ts = mktime(&timestamp);

	// Return true if difference between now and timestamp is more than timeout
	return (now - ts) > timeout;
}

// Will continuously read in new data saved to file and update last seen/failures
void update_last_seen(std::ifstream &logfile, std::map<std::string, std::tm> &last_seen, std::set<std::string> &failures, std::set<std::string> &db_sensors, std::set<std::string> &db_transceivers)
{
	
	std::string timestamp, transcode, payload;
	while (true)
	{
		std::lock_guard<std::mutex> lock_last_seen(mutex_last_seen);
		std::lock_guard<std::mutex> lock_failures(mutex_failures);

		while (logfile >> timestamp >> transcode >> payload)
		{

			std::string sensorID = payload;
			sensorID.erase(16, 2).erase(0, 8);
			std::string transID = split(transcode, '_')[2];

			// If one of our nodes
			if (db_sensors.find(sensorID) != db_sensors.end() && db_transceivers.find(transID) != db_transceivers.end())
			{
				// Update last seen

				last_seen[sensorID] = convert_timestamp(timestamp);
				last_seen[transID] = convert_timestamp(timestamp);

				if (DEBUG)
				{
					std::lock_guard<std::mutex> lock_cout(mutex_cout);
					std::cout << "Just seen " << sensorID << " and" << transID << std::endl;
				}
				// Remove id from vector of failures
				failures.erase(sensorID);
				failures.erase(transID);

				// Send message to db saying nodes are not failed
				std::string exec = "python node_active.py " + sensorID;
				system(exec.c_str());

				exec = "python node_active.py " + transID;
				system(exec.c_str());
			}
		}
		std::this_thread::sleep_for(std::chrono::seconds(1));
		if (!logfile.eof())
			break;
		logfile.clear();
	}
}


void add_failures(std::set<std::string> &failures, const std::map<std::string, std::tm> &last_seen, double timeout)
{

	while (true)
	{
		std::lock_guard<std::mutex> lock_last_seen(mutex_last_seen);
		std::lock_guard<std::mutex> lock_failures(mutex_failures);

		
		for (auto &l : last_seen)
		{
			std::string nodeID = l.first;
			std::tm time_last_seen = l.second;

			if (failed(time_last_seen, timeout))
			{
				failures.insert(nodeID);
				
				if (DEBUG)
				{
					std::lock_guard<std::mutex> lock_cout(mutex_cout);
					std::cout << nodeID << " failed" << std::endl;
				}
					// Send message to DB saying node failed
				std::string exec = "python node_failed.py " + nodeID;
				system(exec.c_str());
			}
		}

		std::this_thread::sleep_for(std::chrono::seconds(1));

	}
}
