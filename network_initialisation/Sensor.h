#ifndef SENSOR_H
#define SENSOR_H

#include <vector>
#include <string>
#include <map>
#include <deque>
#include "Connection.h"

class Sensor
{
private:
	std::string id;
	std::map<Connection, std::deque<double>> connections; // Connection and rssi values queue
	int rssi_queue_size;

public:
	Sensor(std::string _id, int _rssi_queue_size=10);

	std::string getSensorID() const;

	std::vector<std::string> connectionList() const;

	void add_connection(Connection c);

	void add_rssi(std::string transID, double rssi);

};
#endif // !SENSOR_H
