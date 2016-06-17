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

using namespace std;

mutex mutex_cout, mutex_whitelist_updated, mutex_failures, mutex_sensors, mutex_last_seen;

int main()
{
	vector<thread> threads;
	//map<string, string> thread_ids;


	set<string> db_transceivers;
	set<string> db_sensors;

	string current_dir = ".";
	map<string, Sensor> sensors;
	map<string, vector<string>> whitelist;
	set<string> failures;

	bool updated = false;

	map<string, tm> last_seen;
	double timeout = 30; // In seconds
	vector<string> logfiles = get_file_list(current_dir, ".log");
	string blacklistfile("blacklist.txt");


	// Read in node info from DB
	system("python get_sensor_info.py");

	// Importing DB node data into our data structures
	thread(add_new_sensors, "sensors.txt", ref(db_sensors), ref(sensors)).detach();
	thread(add_new_trans, "transceivers.txt", ref(db_transceivers), ref(whitelist)).detach();

	for (unsigned int i = 0; i < logfiles.size(); i++)
	{
		// Checking logfiles for connection rssi values
		thread(update_rssis, ref(sensors), logfiles[i], ref(db_sensors), ref(db_transceivers)).detach();

		// Send logfile data to DB
		string exec = "python readlog.py " + logfiles[i];
		system(exec.c_str());
	}

	// Need std::ref to pass items by reference to threads
	threads.push_back(thread(update_whitelist, ref(whitelist), ref(sensors), ref(failures), ref(updated))); // Update whitelist

	threads.push_back(thread(check_for_update, blacklistfile, ref(whitelist), ref(db_transceivers) ,ref(updated))); // Check for updated whitelist and create blacklist file

	vector<ifstream> infiles;
	for (unsigned int i = 0; i < logfiles.size(); i++) 
	{
		infiles.push_back(ifstream(logfiles[i].c_str()));
		threads.push_back(thread(update_last_seen, ref(infiles.back()), ref(last_seen), ref(failures), ref(db_sensors), ref(db_transceivers))); // update last seen for every node
	}

	threads.push_back(thread(add_failures, ref(failures), ref(last_seen), timeout)); // Adding failures to failure list based on last seen

	for (auto &t : threads)
		t.detach(); // Begin concurrent execution

					// Continue checking for new logfiles
	vector<string> opened_logfiles(logfiles);
	while (true)
	{
		vector<string> new_logfiles = get_file_list(current_dir, ".log");
		for (unsigned int i = 0; i < new_logfiles.size(); i++)
		{
			// If we have not previously seen this logfile
			if (!(find(opened_logfiles.begin(), opened_logfiles.end(), new_logfiles[i]) != opened_logfiles.end()))
			{
				// Start checking it for new rssi values
				thread(update_rssis, ref(sensors), new_logfiles[i], ref(db_sensors), ref(db_transceivers)).detach();
				infiles.push_back(ifstream(new_logfiles[i].c_str()));
				thread(update_last_seen, ref(infiles.back()), ref(last_seen), ref(failures), ref(db_sensors), ref(db_transceivers)).detach(); // update last seen
				opened_logfiles.push_back(new_logfiles[i]);

				// Send data to DB
				string exec = "python readlog.py " + new_logfiles[i];
				system(exec.c_str());
			}
		}

		this_thread::sleep_for(chrono::minutes(5));
	}

}