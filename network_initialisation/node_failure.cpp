#include <ctime> 
#include <string>
#include <vector>
#include <fstream>
#include "helpers.h"
#include "dirent.h"
#include <thread>
#include <map>
#include <chrono>
#include "node_failure.h"
#include <mutex>

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
void update_last_seen(std::ifstream &logfile, std::map<std::string, std::tm> &last_seen, std::vector<std::string> &failures)
{
	
	std::string timestamp, transcode, payload;
	while (true)
	{
		std::lock_guard<std::mutex> lock_last_seen(mutex_last_seen);
		std::lock_guard<std::mutex> lock_failures(mutex_failures);

		while (logfile >> timestamp >> transcode >> payload)
		{
			// Update last seen
			std::string sensorID = payload;
			sensorID.erase(16, 2).erase(0, 8);
			last_seen[sensorID] = convert_timestamp(timestamp);
			
			std::string transID = split(transcode, '_')[2];
			last_seen[transID] = convert_timestamp(timestamp);

			// Remove id from vector of failures
			failures.erase(std::remove(failures.begin(), failures.end(), transID), failures.end());
		}
		std::this_thread::sleep_for(std::chrono::seconds(1));
		if (!logfile.eof())
			break;
		logfile.clear();
	}
}

// TODO add code that removes failed transceiver's sensors from eevry blacklist to find new route
void add_failures(std::vector<std::string> &failures, const std::map<std::string, std::tm> &last_seen, double timeout)
{
	std::map<std::string, std::tm>::const_iterator iter;
	while (true)
	{
		std::lock_guard<std::mutex> lock_last_seen(mutex_last_seen);
		std::lock_guard<std::mutex> lock_failures(mutex_failures);

		for (iter = last_seen.begin(); iter != last_seen.end(); ++iter)
		{
			if (failed(iter->second, timeout))
			{

				failures.push_back(iter->first);

			}
		}


		std::this_thread::sleep_for(std::chrono::seconds(1));

	}
}
