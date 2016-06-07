#ifndef NOCONNECTIONEXCEPTION_H
#define NOCONNECTIONEXCEPTION_H

#include <exception>
#include <string>


class NoConnectionException : public std::exception
{
private:
	std::string sensorID;

public:
	NoConnectionException(std::string _sensorID);

	virtual const char* what() const throw();
};


#endif // !NOCONNECTIONEXCEPTION_H
