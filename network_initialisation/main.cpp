#include "connection.h"
#include <vector>
#include <string>
#include <random>
#include <iostream>

using namespace std;

int main()
{
	vector<Sensor> sensors;

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