#ifndef NODE_FAILURE_H
#define NODE_FAILURE_H

#include <ctime> 
#include <string>
#include <vector>
#include <fstream>
#include "helpers.h"
#include <thread>
#include <map>
#include <chrono>
#include <set>

// 2016-05-31_16:34:50
std::tm convert_timestamp(std::string timestamp);

// Timeout in seconds
bool failed(std::tm timestamp, double timeout);

// Will continuously read in new data saved to file
void update_last_seen(std::ifstream &logfile, std::map<std::string, std::tm> &last_seen, std::set<std::string> &failures);

void add_failures(std::set<std::string> &failures, const std::map<std::string, std::tm> &last_seen, double timeout);


#endif // NODE_FAILURE_H
