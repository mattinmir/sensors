// TODO add threads which send data to DB via python file

#include "helpers.h"
#include "Sensor.h"
#include <vector>
#include <string>
#include <random>
#include <iostream>
#include <fstream>
#include <map>
#include "dirent.h"
#include <thread>
#include "node_failure.h"
#include <ctime>
#include <algorithm>
#include <mutex>
#include <set>
#define DEBUG true

using namespace std;

mutex mutex_cout, mutex_whitelist_updated, mutex_failures, mutex_sensors, mutex_last_seen;

int main()
{
	vector<thread> threads;
	//map<string, string> thread_ids;

	string current_dir = ".";
	map<string, Sensor> sensors;
	map<string, vector<string>> whitelist;
	set<string> failures;

	bool updated = false;

	map<string, tm> last_seen;
	double timeout = 30; // In seconds
	vector<string> logfiles = get_file_list(current_dir, ".log"); 
	string blacklistfile("blacklist.txt");


	for (unsigned int i = 0; i < logfiles.size(); i++)
	{	
		// Split filename string using '_' to get 3 elements
		// Split 3rd element using '-' to get 2 elements, first of which is transID
		string transID = split(split(logfiles[i].c_str(), '_')[2], '-')[0];
		whitelist[transID] = {}; // Add new transceiver to whitelist

		// Start checking sensors for new rssi values
		thread(update_sensors, ref(sensors), logfiles[i]).detach();
	}

	//this_thread::sleep_for(chrono::minutes(20));
	
	// Need std::ref to pass items by reference to threads
	threads.push_back(thread(update_whitelist, ref(whitelist), ref(sensors), ref(failures), ref(updated))); // Update whitelist
	
	threads.push_back(thread(check_for_update, blacklistfile, ref(whitelist), ref(updated))); // Check for updated whitelist
	
	for (unsigned int i = 0; i < logfiles.size(); i++)
		threads.push_back(thread(update_last_seen, ifstream(logfiles[i]), ref(last_seen), ref(failures))); // update last seen for every logfile
		
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
				thread(update_sensors, ref(sensors), new_logfiles[i]).detach(); 
				thread(update_last_seen, ifstream(new_logfiles[i]), ref(last_seen), ref(failures)).detach(); // update last seen
				opened_logfiles.push_back(new_logfiles[i]);
			}
		}

		this_thread::sleep_for(chrono::minutes(5));
	}

}
