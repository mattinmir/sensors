#ifndef FIFO_H
#define FIFO_H
#include <deque>

class Fifo
{
private:
	int size;
	std::deque<double> fifo;
public:
	Fifo();

	Fifo(int _size);

	void push_back(double val);

	double median() ;
};

#endif // !FIFO_H
