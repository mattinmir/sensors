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
#include "Mutex.h"
#include "node_failure.h"
#include <ctime>

using namespace std;

int main()
{
	string current_dir = ".";
	vector<string> logfiles = get_file_list(current_dir, ".log"); //{"EnO_VLD_019FEE73-2016.log"};
	vector<Sensor> sensors;

	for (unsigned int i = 0; i < logfiles.size(); i++)
	{
		ifstream infile;
		infile.open(logfiles[i].c_str());

		if (!infile.is_open())
		{
			cerr << "Could not open logfile " << logfiles[i].c_str() << "\n";
			exit(EXIT_FAILURE);
		}

		// Split filename string using '_' to get 3 elements
		// Split 3rd element using '-' to get 2 elements, first of which is transID
		string transID = split(split(logfiles[i].c_str(), '_')[2], '-')[0];

		// Based on reading of format: 2016-06-01_14:06:16 EnO_VLD_019FEE73 00006A08019B9ACD2D
		string timestamp, transcode, payload;
		map<string, vector<double>> rssis; // Map of sensors' connection strength to this transciever
		while (infile >> timestamp >> transcode >> payload)
		{
			string sensorID = payload;
			sensorID.erase(16,2).erase(0, 8); // Erase first 8 and last 2 chars of payload to get sensor ID

			double rssi = stoul(payload.erase(0, 16), nullptr, 16); // Erase first 16 chars of payload to get signal strength
			rssis[sensorID].push_back(rssi); // Add rssi value to rssis associated with that transceiver

			// RSSI is db scale so less is more
		}

		vector<Connection> connections;
		map<string, vector<double>>::const_iterator iter;

		// Iterate over map of sensors and their rssi values,  getting the avg rssi
		// then adding a new connection to each sensor connected to this transceiver
		// Use median rssi in case an anomaly cause a fluke reading 
		for (iter = rssis.begin(); iter != rssis.end(); ++iter)
			new_connection(sensors, transID, iter->first, median_rssi(iter->second)); // iter->first is sensorId, iter->second is vector of rssis
		
	}

	// map<sensorID, vector of transcievers the sensor can connect to, in descending order of strength>
	map<string, vector<string>> sensorConnections; 
	for (unsigned int i = 0; i < sensors.size(); i++)
		sensorConnections[sensors[i].getSensorID()] = sensors[i].connectionList();
	

	map<string, vector<string>> whitelist;
	vector<string> failures;
	// Try/catch will catch exception if a sensor has no connections
	try
	{
		generate_whitelist(whitelist, failures, sensorConnections);
	}
	catch (exception& e)
	{
		cerr << e.what() << '\n';
	}
	
	ofstream whitelistfile("whitelist.txt");
	map<string, vector<string>>::const_iterator iter;
	for (iter = whitelist.begin(); iter != whitelist.end(); ++iter)
		whitelistfile << "Transceiver " << iter->first << " : " << "Sensors " << iter->second << endl;
	

	/******************************* Threads *********************************************/
	bool updated = false;
	mutex failures_lock, last_seen_lock, updated_lock;
	vector<thread> threads;
	map<string, tm> last_seen;
	double timeout = 100; // In seconds
	string fixedfile = "fixed.txt";

	// Need std::ref to pass items by reference to threads
	threads.push_back(thread(update_whitelist, ref(whitelist), ref(failures), sensorConnections, ref(updated), ref(failures_lock),ref(updated_lock))); // Update whitelist
	
	threads.push_back(thread(check_for_update, ref(updated), ref(updated_lock))); // Check for updated whitelist
	
	for (unsigned int i = 0; i < logfiles.size(); i++)
		threads.push_back(thread(update_last_seen, ifstream(logfiles[i]), ref(last_seen), ref(last_seen_lock))); // update last seen for every logfile
		
	threads.push_back(thread(add_failures, ref(failures), ref(last_seen), timeout, ref(failures_lock), ref(last_seen_lock))); // Adding failures to failure list based on last seen
	
	threads.push_back(thread(remove_failures, ref(failures), ifstream(fixedfile), ref(failures_lock))); // Remove failed nodes from failures if they are fixed

	for (auto &t : threads)
		t.join(); // Begin concurrent execution

	
	

}
