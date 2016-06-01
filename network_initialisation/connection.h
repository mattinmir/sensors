#ifndef CONNECTION_H
#define CONNECTION_H

#include <string>
#include <vector>

class Connection
{
private:
	std::string transID;
	double rssi;

public:
	Connection(std::string _transID, double _rssi);

	bool operator>(Connection c2);

	bool operator<(Connection c2);

	std::string get_transID();
};


class Sensor
{
private:
	std::string id;
	std::vector<Connection> connections;

public:
	Sensor(std::string _id);

	std::string getSensorID();

	std::string strongestLink();

	void add_connection(Connection c);
};

#endif // CONNECTION_H