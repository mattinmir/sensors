#include "Sensor.h"
#include "NoConnectionException.h"
#include "Connection.h"
#include <vector>
#include <string>
#include <iostream>


Sensor::Sensor(std::string _id) : id(_id){}

Sensor::Sensor(std::string _id, std::vector<Connection> _connections) : id(_id), connections(_connections){}

std::string Sensor::getSensorID()
{
	return id;
}

std::string Sensor::strongestLink()
{
	if (connections.size() == 0)
	{
		std::cerr << "Error with sensor " << id << ":\n";
		throw NoConnectionException();
		
	}

	else if (connections.size() == 1)
		return connections[0].get_transID();

	else
	{
		Connection strongest = connections[0];

		for (int i = 1; i < connections.size(); ++i)
			strongest = (strongest < connections[i]) ? strongest : connections[i]; // < because rssi is db scale so 44 actually means -44 dB

		return strongest.get_transID();
	}
}

void Sensor::add_connection(Connection c)
{
	connections.push_back(c);
}