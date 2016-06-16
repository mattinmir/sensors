// TODO add threads which send data to DB via python file

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

	// TODO add threads which update these from the db and update sensors/whitelist
	vector<string> db_transceivers;
	vector<string> db_sensors;

	string current_dir = ".";
	map<string, Sensor> sensors;
	map<string, vector<string>> whitelist;
	set<string> failures;

	bool updated = false;

	map<string, tm> last_seen;
	double timeout = 30; // In seconds
	vector<string> logfiles = get_file_list(current_dir, ".log");
	string blacklistfile("blacklist.txt");

	/*
	
	TODO Read sensor info from DB

	*/
	for (auto &sensorID : db_sensors)
		sensors[sensorID] = Sensor(sensorID, 10); 

	for (auto &transID : db_transceivers)
		whitelist[transID]; // Add new transceiver to whitelist

	//for (unsigned int i = 0; i < logfiles.size(); i++)
	//{
	//	// Filename of format EnO_VLD_019FE089-2016.log
	//	// Split filename string using '_' to get 3 elements {EnO, VLD, 019FE089-2016.log}
	//	// Split 3rd element using '-' to get 2 elements, {019FE089, 2016.log}, first of which is transID
	//	string transID = split(split(logfiles[i].c_str(), '_')[2], '-')[0];
	//	whitelist[transID] = {}; // Add new transceiver to whitelist

	//							 // Start checking sensors for new rssi values
	//	//thread(update_sensors, ref(sensors), logfiles[i]).detach();
	//}

	//this_thread::sleep_for(chrono::minutes(20));

	// Need std::ref to pass items by reference to threads
	threads.push_back(thread(update_whitelist, ref(whitelist), ref(sensors), ref(failures), ref(updated))); // Update whitelist

	threads.push_back(thread(check_for_update, blacklistfile, ref(whitelist), ref(db_transceivers) ,ref(updated))); // Check for updated whitelist

	vector<ifstream> infiles;
	for (unsigned int i = 0; i < logfiles.size(); i++) 
	{
		infiles.push_back(ifstream(logfiles[i].c_str()));
		threads.push_back(thread(update_last_seen, ref(infiles.back()), ref(last_seen), ref(failures), ref(db_sensors), ref(db_transceivers))); // update last seen for every logfile
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
				thread(update_sensors, ref(sensors), new_logfiles[i]).detach();
				infiles.push_back(ifstream(new_logfiles[i].c_str()));
				thread(update_last_seen, ref(infiles.back()), ref(last_seen), ref(failures), ref(db_sensors), ref(db_transceivers)).detach(); // update last seen
				opened_logfiles.push_back(new_logfiles[i]);
			}
		}

		this_thread::sleep_for(chrono::minutes(5));
	}

}