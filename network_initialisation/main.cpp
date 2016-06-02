#include "connection.h"
#include "helpers.h"
#include <vector>
#include <string>
#include <random>
#include <iostream>
#include <fstream>

using namespace std;

int main()
{
	vector<string> logfiles = {"EnO_VLD_019FEE73-2016.log"};
	vector<Sensor> sensors;

	for (int i = 0; i < logfiles.size(); i++)
	{
		ifstream infile;
		infile.open(logfiles[i].c_str());

		if (!infile.is_open())
		{
			cerr << "Could not open logfile " << logfiles[i].c_str() << "\n";
			exit(EXIT_FAILURE);
		}

		// Based on reading of format: 2016-06-01_14:06:16 EnO_VLD_019FEE73 00006A08019B9ACD2D
		string reading;
		while (infile >> reading)
		{
			vector<string> fields = split(reading, ' ');
			string transID = split(fields[1], '_')[2];
			string sensorID =
			double rssi = stoul(fields[2].erase(0, 16), nullptr, 16); // Converts last two chars of payload into int rssi

		}
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

	for (int i = 0; i < sensors.size(); i++)
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