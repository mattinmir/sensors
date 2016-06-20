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
// Converts timestamp to std::tm type
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

// Marks nodes as failed if they have not transmitted for a long time
void add_failures(std::set<std::string> &failures, const std::map<std::string, std::tm> &last_seen, double timeout)
{
	while (true)
	{
		// For every node
		for (auto &l : last_seen)
		{
			std::string nodeID = l.first;
			std::tm time_last_seen = l.second;
			
			// If it has failed
			if (failed(time_last_seen, timeout))
			{
				// Mark it as failed
				std::lock_guard<std::mutex> lock_failures(mutex_failures);
				bool inserted = failures.insert(nodeID).second; // True if insert worked, i.e. didnt exist before
				if (inserted)
				{
					if (DEBUG)
					{
						std::lock_guard<std::mutex> lock_cout(mutex_cout);
						std::cout << nodeID << " failed" << std::endl;
					}

					// Send message to DB saying node failed
					std::string exec = "python node_failed.py " + nodeID + " &";
					system(exec.c_str());
				}
			}
		}

		std::this_thread::sleep_for(std::chrono::seconds(10));

	}
}
