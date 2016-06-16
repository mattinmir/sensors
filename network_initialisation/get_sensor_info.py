#-------------------------------------------------------------------------------
# Name:        module1
# Purpose:
#
# Author:      mm5213
#
# Created:     16/06/2016
# Copyright:   (c) mm5213 2016
# Licence:     <your licence>
#-------------------------------------------------------------------------------

import json

j = json.loads('[{"sensorID":0,"floor":1,"location":"lifts","status":"failed","type":"humidity","trans_connections":"2"},\
{"sensorID":0,"floor":2,"location":"toilet","status":"active","type":"smoke","trans_connections":"1"},\
{"sensorID":0,"floor":1,"location":"corridor","status":"active","type":"water_leakage","trans_connections":"2"},\
{"sensorID":0,"floor":1,"location":"gym","status":"active","type":"co2","trans_connections":"2"},\
{"sensorID":0,"floor":2,"location":"lifts","status":"active","type":"lux","trans_connections":"1"},\
{"sensorID":0,"floor":3,"location":"lifts","status":"active","type":"temperature","trans_connections":"2"},\
{"sensorID":0,"floor":4,"location":"corridor","status":"active","type":"temperature","trans_connections":"2"},\
{"sensorID":0,"floor":5,"location":"windows","status":"active","type":"occupancy","trans_connections":"1"},\
{"sensorID":0,"floor":7,"location":"lifts","status":"active","type":"humidity","trans_connections":"2"}]')

sensorIDs = [str(line['sensorID']) for line in j]
sensors = open("sensors.txt", 'w')
for s in sensorIDs:
    sensors.write(str(s))
    sensors.write('\n')

sensors.close()