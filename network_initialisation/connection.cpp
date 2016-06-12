#include "Connection.h"
#include <string>
#include <iostream>
#include "NoConnectionException.h"


/********* Connection *********/
Connection::Connection(std::string _transID, double _rssi) : transID(_transID), rssi(_rssi){}

bool Connection::operator>(Connection c2)
{
	return (this->rssi > c2.rssi);
}

bool Connection::operator<(Connection c2)
{
	return (this->rssi < c2.rssi);
}

std::string Connection::get_transID() const
{
	return transID;
}

bool operator>(Connection c1, Connection c2)
{
	return (c1.rssi > c2.rssi);
}

bool operator<(Connection c1, Connection c2)
{
	return (c1.rssi < c2.rssi);
}
