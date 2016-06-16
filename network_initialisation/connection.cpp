#include "Connection.h"
#include <string>
#include <iostream>
#include "NoConnectionException.h"


/********* Connection *********/
Connection::Connection(std::string _transID) : transID(_transID){}

bool Connection::operator>(Connection c2)
{
	return (this->rssis.median() > c2.rssis.median());
}

bool Connection::operator<(Connection c2)
{
	return (this->rssis.median() < c2.rssis.median());
}

std::string Connection::get_transID() const
{
	return transID;
}

bool operator>(Connection c1, Connection c2)
{
	return (c1.rssis.median() > c2.rssis.median());
}

bool operator<(Connection c1, Connection c2)
{
	return (c1.rssis.median() < c2.rssis.median());
}

void Connection::add_rssi(double val)
{
	rssis.push_back(val);
}