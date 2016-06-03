#include "Connection.h"
#include <string>
#include <iostream>
#include "NoConnectionException.h"


/********* Connection *********/
Connection::Connection(std::string _transID, double _rssi) : transID(_transID), rssi(_rssi){}

bool Connection::operator>(Connection c2)
{
	return (rssi > c2.rssi);
}

bool Connection::operator<(Connection c2)
{
	return (rssi < c2.rssi);
}

std::string Connection::get_transID()
{
	return transID;
}
