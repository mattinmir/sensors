#include <ctime> 
#include <string>
#include <vector>
#include <fstream>
#include "helpers.h"
#include "dirent.h"
#include <thread>
#include <map>


// 2016-05-31_16:34:50
std::tm convert_timestamp(std::string timestamp)
{
	std::tm converted;
	
	std::string date = split(timestamp, '_')[0];
	converted.tm_year = atoi(split(date, '-')[0].c_str());
	converted.tm_mon = atoi(split(date, '-')[1].c_str());
	converted.tm_mday = atoi(split(date, '-')[2].c_str());

	std::string time = split(timestamp, '_')[1];
	converted.tm_hour = atoi(split(time, ':')[0].c_str());
	converted.tm_min = atoi(split(time, ':')[1].c_str());
	converted.tm_sec = atoi(split(time, ':')[2].c_str());

	return converted;
}

// Timeout in seconds
bool failed(std::tm timestamp, double timeout)
{
	time_t now;
	time(&now);

	// Return true if difference between now and timestamp is more than timeout
	return (difftime(now, mktime(&timestamp)) > timeout);
}

void update_last_seen(std::ifstream &infile, std::map<std::string, std::tm> &last_seen)
{
	/*
	std::string current_dir = ".";
	std::vector<std::string> logfiles = get_file_list(current_dir, ".log");

	std::vector<std::thread> threads(logfiles.size());
	*/

	std::string line;
	while (true)
	{
		std::string timestamp, transcode, payload;
		while (infile >> timestamp >> transcode >> payload)
		{
			std::string sensorID = payload;
			sensorID.erase(16, 2).erase(0, 8);
			last_seen[sensorID] = convert_timestamp(timestamp);

			std::string transID = split(transcode, '_')[2];
			last_seen[transID] = convert_timestamp(timestamp);

		}
		if (!infile.eof())
			break;
		infile.clear();
	}
}

void update_failures(std::vector<std::string> &failures, std::map<std::string, std::tm> last_seen, double timeout)
{
	std::map<std::string, std::tm>::const_iterator iter;

	for (iter = last_seen.begin(); iter != last_seen.end(); ++iter)
	{
		if (failed(iter->second, timeout))
		{
			failures.push_back(iter->first);
		}
	}
}