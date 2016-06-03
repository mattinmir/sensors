#include "Connection.h"
#include "helpers.h"
#include "Sensor.h"
#include <vector>
#include <string>
#include <random>
#include <iostream>
#include <fstream>
#include <map>

using namespace std;

int main()
{
	vector<string> logfiles = {"EnO_VLD_019FEE73-2016.log"};
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
		for (iter = rssis.begin(); iter != rssis.end(); ++iter)
			new_connection(sensors, transID, iter->first, avg_rssi(iter->second)); // iter->first is sensorId, iter->second is vector of rssis
		
	}
	/*
	sensors.push_back(Sensor("A"));
	sensors.push_back(Sensor("B"));
	sensors.push_back(Sensor("C"));
	sensors.push_back(Sensor("D"));

	for (int i = 0; i < sensors.size(); i++)
	{
		sensors[i].add_connection(Connection("t1", (double)(rand() % 30)));
		sensors[i].add_connection(Connection("t2", (double)(rand() % 30)));
		sensors[i].add_connection(Connection("t3", (double)(rand() % 30)));

	}

	sensors.push_back(Sensor("E"));
	*/

	for (unsigned int i = 0; i < sensors.size(); i++)
	{
		try
		{
			cout << sensors[i].getSensorID() << " : " << sensors[i].strongestLink() << "\n";
		}
		catch (exception& e)
		{
			cerr << e.what();
		}
	}

}
