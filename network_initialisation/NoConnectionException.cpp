#include "NoConnectionException.h"
#include <string>

NoConnectionException::NoConnectionException(std::string _sensorID) : msg(_sensorID + std::string(" not connected to any tranceivers!")) {}

NoConnectionException::~NoConnectionException() throw() {}

const char* NoConnectionException::what() const throw()
{

	return msg.c_str();

}

