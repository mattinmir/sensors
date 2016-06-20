#ifndef HELPERS_H
#define HELPERS_H

#include <vector>
#include <sstream>
#include <algorithm>
#include "Sensor.h"
#include <map>
#include "NoConnectionException.h"
#include <thread>
#include <iostream>
#include <chrono>
#include <set>

#ifdef _WIN32
#include "dirent.h"
#else
#include <dirent.h>
#endif


std::vector<std::string> &split(const std::string &s, char delim, std::vector<std::string> &elems);

std::vector<std::string> split(const std::string &s, char delim);

// Gets names of all files in `directory` with `extension`
std::vector<std::string> get_file_list(std::string directory, std::string extension);


void update_whitelist(std::map<std::string, std::vector<std::string>> &whitelist, std::map<std::string, Sensor> &sensors, std::set<std::string> &failures, bool &updated);

// Checking for updated whitelist
void check_for_update(std::string blacklistfilename, std::map<std::string, std::vector<std::string>> &whitelist, std::set<std::string> &db_transceievers, bool &updated);

void add_new_nodes(std::string sensorsfilename, std::set<std::string> &db_sensors, std::map<std::string, Sensor> &sensors, std::string transfilename, std::set<std::string> &db_transceivers, std::map<std::string, std::vector<std::string>> &whitelist);

void process_logfile(std::map<std::string, Sensor> &sensors, std::string logfile_name, std::set<std::string> &db_sensors, std::set<std::string> &db_transceivers, std::map<std::string, std::tm> &last_seen, std::set<std::string> &failures);

#endif // !HELPERS_H
