#include "Sensor.h"
#include "NoConnectionException.h"
#include <vector>
#include <string>
#include <iostream>
#include <sstream>
#include <functional>
#include <algorithm>
#include <deque>
#include "helpers.h"

// Default constructor neccessary for use in std::map
Sensor::Sensor() : rssi_queue_size(10){}

Sensor::Sensor(std::string _id, int _rssi_queue_size = 10) : id(_id), rssi_queue_size(_rssi_queue_size){}


std::string Sensor::getSensorID() const
{
	return id;
}


// Returns vector of transIDs in descending order of connection strength
std::vector<std::string> Sensor::connectionList() 
{
	// No Connections
	if (connections.size() == 0)
		return std::vector<std::string>(); // Return empty vector
	
	else
	{
		std::map<double, std::string> averagedRssis; // Keyed by rssi so it is in strength order (Maps are sorted inherently)
		std::vector<double> rssis;
		
		// For every connection
		for (auto &conn : connections)
		{
			// order by median rssi
			averagedRssis[conn.second.median()] = conn.first; // link averaged rssis to transID in map
		}
		
		std::vector<std::string> transceiverList;
		std::map<double, std::string>::const_iterator avg_iter;
		for (avg_iter = averagedRssis.begin(); avg_iter != averagedRssis.end(); ++avg_iter) // Add transIDs to vector in correct order
			transceiverList.push_back(avg_iter->second);
		
		return transceiverList;

	}
}

void Sensor::add_connection(std::string transID)
{
	connections[transID] = {};
}


void Sensor::add_rssi(std::string transID, double rssi)
{
	connections[transID].push_back(rssi);
}

void Sensor::del_connection(std::string transID)
{
	connections.erase(transID);
}
