#include "Sensor.h"
#include "NoConnectionException.h"
#include "Connection.h"
#include <vector>
#include <string>
#include <iostream>
#include <sstream>
#include <functional>
#include <algorithm>
#include "connection.h"
#include <deque>
#include "helpers.h"

Sensor::Sensor(std::string _id, int _rssi_queue_size) : id(_id), rssi_queue_size(_rssi_queue_size){}


std::string Sensor::getSensorID() const
{
	return id;
}

std::vector<std::string> Sensor::connectionList() const
{
	// No Connections
	if (connections.size() == 0)
		throw NoConnectionException(id);
	

	else
	{
		std::map<double, std::string> averagedRssis; // Keyed by rssi so it is in strength order (Maps are sorted inherently)
		std::vector<double> rssis;
		std::map<Connection, std::deque<double>>::const_iterator con_iter;

		for (con_iter = connections.begin(); con_iter != connections.end(); ++con_iter)
		{
			// Transfer values to vector to be averaged
			rssis = std::vector<double>(con_iter->second.begin(), con_iter->second.end()); 
			averagedRssis[median_rssi(rssis)] = con_iter->first.get_transID(); // link averaged rssis to transID in map
		}
		
		std::vector<std::string> transceiverList;
		std::map<double, std::string>::const_iterator avg_iter;
		for (avg_iter = averagedRssis.begin(); avg_iter != averagedRssis.end(); ++avg_iter) // Add transIDs to vector in correct order
			transceiverList.push_back(avg_iter->second);
		

		return transceiverList;

	}
}

void Sensor::add_connection(Connection c)
{
	connections[c] = {};
}

// Adds rssi if connection exists, returns false if not
bool Sensor::add_rssi(std::string transID, double rssi)
{
	std::map<Connection, std::deque<double>>::iterator con_iter = connections.begin();
	while (con_iter->first.get_transID() != transID && con_iter != connections.end())
		++con_iter;

	if (con_iter == connections.end())
		return false;
	else
	{
		con_iter->second.push_back(rssi);
		if (con_iter->second.size() > rssi_queue_size)
			con_iter->second.pop_front();
		return true;
	}
}

// Deletes connection if it exists, returns false if not
bool Sensor::del_connection(std::string transID)
{
	std::map<Connection, std::deque<double>>::iterator con_iter = connections.begin();

	while (con_iter->first.get_transID() != transID && con_iter != connections.end())
		++con_iter;

	if (con_iter == connections.end())
		return false;
	else
	{
		connections.erase(con_iter);
		return true;
	}

}