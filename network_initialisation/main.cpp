#include "Connection.h"
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

using namespace std;

int main()
{
	vector<thread> threads;

	string current_dir = ".";
	map<string, Sensor> sensors;
	map<string, vector<string>> whitelist;
	vector<string> failures;

	bool updated = false;

	map<string, tm> last_seen;
	double timeout = 100; // In seconds
	string fixedfile = "fixed.txt";
	vector<string> logfiles = get_file_list(current_dir, ".log"); 
	ofstream whitelistfile("whitelist.txt");


	for (unsigned int i = 0; i < logfiles.size(); i++)
	{	
		// Split filename string using '_' to get 3 elements
		// Split 3rd element using '-' to get 2 elements, first of which is transID
		string transID = split(split(logfiles[i].c_str(), '_')[2], '-')[0];
		whitelist[transID] = {}; // Add new transceiver to whitelist

		// Start checking sensors for new rssi values
		threads.push_back(thread(update_sensors, ref(sensors), logfiles[i]));
	}
	
	// Need std::ref to pass items by reference to threads
	threads.push_back(thread(update_whitelist, ref(whitelist), ref(sensors), ref(failures), ref(updated))); // Update whitelist
	
	threads.push_back(thread(check_for_update, ref(whitelistfile), ref(whitelist), ref(updated))); // Check for updated whitelist
	
	for (unsigned int i = 0; i < logfiles.size(); i++)
		threads.push_back(thread(update_last_seen, ifstream(logfiles[i]), ref(last_seen))); // update last seen for every logfile
		
	threads.push_back(thread(add_failures, ref(failures), ref(last_seen), timeout)); // Adding failures to failure list based on last seen
	
	threads.push_back(thread(remove_failures, ref(failures), ifstream(fixedfile))); // Remove failed nodes from failures if they are fixed

	for (auto &t : threads)
		t.join(); // Begin concurrent execution

	// Continue checking for new logfiles
	vector<string> opened_logfiles(logfiles);
	while (true)
	{
		vector<string> new_logfiles = get_file_list(current_dir, ".log"); 
		for (unsigned int i = 0; i < new_logfiles.size(); i++)
		{
			// If we have not previously seen this logfile
			if (find(opened_logfiles.begin(), opened_logfiles.end(), new_logfiles[i]) != opened_logfiles.end())
			{
				// Start checking it for new rssi values
				thread(update_sensors, ref(sensors), new_logfiles[i]).join(); 
				threads.push_back(thread(update_last_seen, ifstream(new_logfiles[i]), ref(last_seen))); // update last seen
				opened_logfiles.push_back(new_logfiles[i]);
			}
		}

		this_thread::sleep_for(chrono::minutes(5));
	}

}
