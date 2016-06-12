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

	friend bool operator>(Connection c1, Connection c2);

	friend bool operator<(Connection c1, Connection c2);

	std::string get_transID() const;
};

#endif // CONNECTION_H