#ifndef SENSOR_H
#define SENSOR_H

#include <vector>
#include <string>
#include "Connection.h"

class Sensor
{
private:
	std::string id;
	std::vector<Connection> connections;

public:
	Sensor(std::string _id);

	Sensor(std::string _id, std::vector<Connection> _connections);

	std::string getSensorID();

	std::vector<std::string> connectionList();

	void add_connection(Connection c);
};
#endif // !SENSOR_H
