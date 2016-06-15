#ifndef SENSOR_H
#define SENSOR_H

#include <vector>
#include <string>
#include <map>
#include <deque>
#include "Fifo.h"

class Sensor
{
private:
	std::string id;
	std::map<std::string, Fifo> connections; // transID and rssi values Fifo
	int rssi_queue_size;

public:
	Sensor(); // Default constructor required to allow access to map using operator[]

	Sensor(std::string _id, int _rssi_queue_size);

	std::string getSensorID() const;

	std::vector<std::string> connectionList();

	void add_connection(std::string transID);

	void del_connection(std::string transID);

	void add_rssi(std::string transID, double rssi);

};
#endif // !SENSOR_H
