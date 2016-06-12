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
	
	// Connections list should always be sorted, so just return the list of connections
	else
	{
		std::map<double, std::string> averagedRssis; // Keyed by rssi so it is in strength order (Maps are sorted inherently)
		std::vector<double> rssis;
		std::map<Connection, std::deque<double>>::const_iterator con_iter;

		for (con_iter = connections.begin(); con_iter != connections.end(); ++con_iter)
		{
			rssis = std::vector<double>(con_iter->second.begin(), con_iter->second.end()); // Transfer values to vector to be averaged
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

void Sensor::add_rssi(std::string transID, double rssi)
{
	std::map<Connection, std::deque<double>>::iterator i = connections.begin();
	while (i->first.get_transID() != transID)
		++i;

	i->second.push_back(rssi);
	if (i->second.size() > rssi_queue_size)
		i->second.pop_front();
}

