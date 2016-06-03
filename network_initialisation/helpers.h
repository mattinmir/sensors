#include <vector>
#include <sstream>
#include <string>
#include <numeric>
#include "Sensor.h"
#include "Connection.h"

std::vector<std::string> &split(const std::string &s, char delim, std::vector<std::string> &elems) {
	std::stringstream ss(s);
	std::string item;
	while (std::getline(ss, item, delim)) {
		elems.push_back(item);
	}
	return elems;
}


std::vector<std::string> split(const std::string &s, char delim) {
	std::vector<std::string> elems;
	split(s, delim, elems);
	return elems;
}

double avg_rssi(std::vector<double> rssis)
{
	return std::accumulate(rssis.begin(), rssis.end(), 0.0) / rssis.size();
}

void new_connection(std::vector<Sensor> &sensors, std::string transID, std::string sensorID, double rssi)
{
	// If sensor exists already, add the connection to it
	for (int i = 0; i < sensors.size(); i++)
	{
		if (sensors[i].getSensorID() == sensorID)
		{
			sensors[i].add_connection(Connection(transID, rssi));
			return;
		}
	}

	// If sensor does not already exist, create a new sensor, then add the conneciton to it
	sensors.push_back(Sensor(sensorID));
	sensors.back().add_connection(Connection(transID, rssi));

}