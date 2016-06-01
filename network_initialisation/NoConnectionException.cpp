#include "NoConnectionException.h"
#include <string>

const char* NoConnectionException::what() const throw()
{
	return "Not connected to any tranceivers!";

}

