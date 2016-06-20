#include "helpers.h"
#include "Sensor.h"
#include <vector>
#include <string>
#include <random>
#include <iostream>
#include <fstream>
#include <map>
#include <thread>
#include "node_failure.h"
#include <ctime>
#include <algorithm>
#include <mutex>
#include <set>
#ifdef _WIN32
#include "dirent.h"
#else
#include <dirent.h>
#endif

bool DEBUG = true;

using namespace std;

mutex mutex_cout, mutex_whitelist, mutex_updated, mutex_failures, mutex_sensors, mutex_last_seen;

int main()
{
	vector<thread> threads;

	set<string> db_transceivers;
	set<string> db_sensors;

	string log_dir = "/opt/fhem/log/";
	map<string, Sensor> sensors;
	map<string, vector<string>> whitelist;
	set<string> failures;

	bool updated = false;

	map<string, tm> last_seen;
	double timeout = 180; // In seconds
	vector<string> logfiles = get_file_list(log_dir, ".log");
	string blacklistfile("blacklist.txt");

	// Importing DB node data into our data structures
	thread(add_new_nodes, "sensors.txt", ref(db_sensors), ref(sensors), "transceivers.txt", ref(db_transceivers), ref(whitelist)).detach();
	
	vector<ifstream> infiles;
	for (unsigned int i = 0; i < logfiles.size(); i++)
	{
		vector<string> logfilename = split(logfiles[i], '_');
		if (logfilename.size() > 1 && logfilename[1] == "VLD")
		{
			thread(process_logfile, ref(sensors), log_dir+logfiles[i], ref(db_sensors), ref(db_transceivers), ref(last_seen), ref(failures)).detach();
		}
	}

	// Need std::ref to pass items by reference to threads
	thread(update_whitelist, ref(whitelist), ref(sensors), ref(failures), ref(updated)).detach(); // Update whitelist

	thread(check_for_update, blacklistfile, ref(whitelist), ref(db_transceivers), ref(updated)).detach(); // Check for updated whitelist and create blacklist file

	thread(add_failures, ref(failures), ref(last_seen), timeout).detach(); // Adding failures to failure list based on last seen

	// Continue checking for new logfiles
	vector<string> opened_logfiles(logfiles);
	while (true)
	{
		vector<string> new_logfiles = get_file_list(log_dir, ".log");
		for (unsigned int i = 0; i < new_logfiles.size(); i++)
		{
			// If we have not previously seen this logfile
			if (!(find(opened_logfiles.begin(), opened_logfiles.end(), new_logfiles[i]) != opened_logfiles.end()))
			{
				vector<string> logfilename = split(new_logfiles[i], '_');
				if (logfilename.size() > 1 && logfilename[1] == "VLD")
				{
					thread(process_logfile, ref(sensors), log_dir+new_logfiles[i], ref(db_sensors), ref(db_transceivers), ref(last_seen), ref(failures)).detach();
					opened_logfiles.push_back(new_logfiles[i]);
				}
			}
		}

		this_thread::sleep_for(chrono::minutes(5));
	}

}
