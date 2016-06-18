// TODO delete node from db files at start to avoid duplication
// TODO replace python infinite loops with c++ infinite lops


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
	//map<string, string> thread_ids;


	set<string> db_transceivers;
	set<string> db_sensors;

	string log_dir = "./";//"/opt/fhem/log/";
	map<string, Sensor> sensors;
	map<string, vector<string>> whitelist;
	set<string> failures;

	bool updated = false;

	map<string, tm> last_seen;
	double timeout = 30; // In seconds
	vector<string> logfiles = get_file_list(log_dir, ".log");
	string blacklistfile("blacklist.txt");


	// Read in node info from DB
	//system("python get_sensor_info.py &");

	// Importing DB node data into our data structures
	thread(add_new_nodes, "sensors.txt", ref(db_sensors), ref(sensors), "transceivers.txt", ref(db_transceivers), ref(whitelist)).detach();
	
	vector<ifstream> infiles;
	for (unsigned int i = 0; i < logfiles.size(); i++)
	{
		vector<string> logfilename = split(logfiles[i], '_');
		if (logfilename.size() > 1 && logfilename[1] == "VLD")
		{
			thread(process_logfile, ref(sensors), logfiles[i], ref(db_sensors), ref(db_transceivers), ref(last_seen), ref(failures)).detach();
		}
	}

	// Need std::ref to pass items by reference to threads
	threads.push_back(thread(update_whitelist, ref(whitelist), ref(sensors), ref(failures), ref(updated))); // Update whitelist

	threads.push_back(thread(check_for_update, blacklistfile, ref(whitelist), ref(db_transceivers) ,ref(updated))); // Check for updated whitelist and create blacklist file

	threads.push_back(thread(add_failures, ref(failures), ref(last_seen), timeout)); // Adding failures to failure list based on last seen

	for (auto &t : threads)
		t.detach(); // Begin concurrent execution

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
				vector<string> logfilename = split(logfiles[i], '_');
				if (logfilename.size() > 1 && logfilename[1] == "VLD")
				{
					thread(process_logfile, ref(sensors), logfiles[i], ref(db_sensors), ref(db_transceivers), ref(last_seen), ref(failures)).detach();
					opened_logfiles.push_back(new_logfiles[i]);
				}
			}
		}

		this_thread::sleep_for(chrono::minutes(5));
	}

}