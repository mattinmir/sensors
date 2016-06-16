#include "Fifo.h"
#include <deque>
#include <algorithm>

Fifo::Fifo(){}

Fifo::Fifo(int _size) : size(_size){}

void Fifo::push_back(double val)
{
	fifo.push_back(val);
	if (fifo.size() > this->size)
		fifo.pop_front();
} 

double Fifo::median() 
{
	size_t n = fifo.size() / 2;
	std::nth_element(fifo.begin(), fifo.begin() + n, fifo.end());
	double fifon = fifo[n];

	if (fifo.size() % 2 == 1)
		return fifon;
	else
	{
		std::nth_element(fifo.begin(), fifo.begin() + n - 1, fifo.end());
		return 0.5*(fifon + fifo[n - 1]);
	}
}

