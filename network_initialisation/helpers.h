#ifndef HELPERS_H
#define HELPERS_H

#include <vector>
#include <sstream>
#include <string>
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

double median_rssi(std::vector<double> rssis);


//void new_connection(std::vector<Sensor> &sensors, std::string transID, std::string sensorID, double rssi);

// Gets names of all files in `directory` with `extension`
std::vector<std::string> get_file_list(std::string directory, std::string extension);


void generate_whitelist(std::map<std::string, std::vector<std::string>> &whitelist, std::vector<std::string> failures, std::map<std::string, std::vector<std::string>> connections);

void update_whitelist(std::map<std::string, std::vector<std::string>> &whitelist, std::map<std::string, Sensor> &sensors, std::set<std::string> &failures, bool &updated);

// Checking for updated whitelist
void check_for_update(std::string blacklistfilename, std::map<std::string, std::vector<std::string>> &whitelist, std::vector<std::string> &db_transceievers, bool &updated);

void update_sensors(std::map<std::string, Sensor> &sensors, std::string logfile_name);

#endif // !HELPERS_H
