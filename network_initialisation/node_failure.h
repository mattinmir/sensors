#ifndef NODE_FAILURE_H
#define NODE_FAILURE_H

#include <ctime> 
#include <string>
#include <vector>
#include <fstream>
#include "helpers.h"
#include "dirent.h"
#include <thread>
#include <map>
#include <chrono>
 

// 2016-05-31_16:34:50
std::tm convert_timestamp(std::string timestamp);

// Timeout in seconds
bool failed(std::tm timestamp, double timeout);

// Will continuously read in new data saved to file
void update_last_seen(std::ifstream &logfile, std::map<std::string, std::tm> &last_seen);

void add_failures(std::vector<std::string> &failures, const std::map<std::string, std::tm> &last_seen, double timeout);

void remove_failures(std::vector<std::string> &failures, std::ifstream &fixed);

#endif // NODE_FAILURE_H
