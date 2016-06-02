#include "connection.h"
#include <string>
#include <iostream>
#include "NoConnectionException.h"


/********* Connection *********/
Connection::Connection(std::string _transID, double _rssi) : transID(_transID), rssi(_rssi){}

bool Connection::operator>(Connection c2)
{
	return (rssi > c2.rssi);
}

bool Connection::operator<(Connection c2)
{
	return (rssi < c2.rssi);
}

std::string Connection::get_transID()
{
	return transID;
}



/********** Sensor **************/
Sensor::Sensor(std::string _id) : id(_id){}



std::string Sensor::getSensorID()
{
	return id;
}

std::string Sensor::strongestLink()
{
	if (connections.size() == 0)
	{
		std::cerr << "Error with sensor " << id << ":\n";
		NoConnectionException e;
		throw e;
	}

	else if (connections.size() == 1)
		return connections[0].get_transID();

	else
	{
		Connection strongest = connections[0];

		for (int i = 1; i < connections.size(); ++i)
			strongest = (strongest > connections[i]) ? strongest : connections[i];

		return strongest.get_transID();
	}
}

void Sensor::add_connection(Connection c)
{
	connections.push_back(c);
}