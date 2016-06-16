#ifndef NOCONNECTIONEXCEPTION_H
#define NOCONNECTIONEXCEPTION_H

#include <exception>
#include <string>


class NoConnectionException : public std::exception
{
private:
	std::string msg;

public:
	NoConnectionException(std::string _sensorID);
	~NoConnectionException() throw();

	virtual const char* what() const throw();
};


#endif // !NOCONNECTIONEXCEPTION_H
