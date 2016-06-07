#include "Sensor.h"
#include "NoConnectionException.h"
#include "Connection.h"
#include <vector>
#include <string>
#include <iostream>
#include <sstream>
#include <functional>
#include <algorithm>

Sensor::Sensor(std::string _id) : id(_id){}

Sensor::Sensor(std::string _id, std::vector<Connection> _connections) : id(_id), connections(_connections)
{
	std::sort(connections.begin(), connections.end()); // Sort connections in decreasing order of strength (remember rssi is dB scale, so lower is higher)
}

std::string Sensor::getSensorID()
{
	return id;
}

std::vector<std::string> Sensor::connectionList()
{
	// No Connections
	if (connections.size() == 0)
		throw NoConnectionException(id);
	
	// Connections list should always be sorted, so just return the list of connections
	else
	{
		std::vector<std::string> transceiverList;
		for (int i = 0; i < connections.size(); i++)
			transceiverList.push_back(connections[i].get_transID());

		return transceiverList;
	}
}

void Sensor::add_connection(Connection c)
{
	connections.push_back(c);
	std::sort(connections.begin(), connections.end(), std::greater<Connection>()); // Sort connections in decreasing order of strength
}

