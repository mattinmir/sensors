#include "NoConnectionException.h"
#include <string>

NoConnectionException::NoConnectionException(std::string _sensorID) : sensorID(_sensorID) {}

const char* NoConnectionException::what() const throw()
{
	std::string msg = sensorID + " not connected to any tranceivers!";
	return msg.c_str();

}

