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
import requests
import time

trans_list = []
sensor_list = []



trans = requests.post('http://api.smartlandlords.co.uk/api.php/getdata/transceivers', data = {"auth":"YWRtaW46Z2lyYWZmZXM="})
trans_json = trans.json()


transIDs = [str(line['deviceID']) for line in trans_json]
transceivers = open("transceivers.txt", 'w')
for t in transIDs:
    if t not in trans_list:
        trans_list.append(t)
        transceivers.write(str(t))
        transceivers.write('\n')
       # print str('trans ' + t)
transceivers.close()




sensor = requests.post('http://api.smartlandlords.co.uk/api.php/getdata/sensors', data = {"auth":"YWRtaW46Z2lyYWZmZXM="})
sensor_json = sensor.json()


sensorIDs = [str(line['deviceID']) for line in sensor_json]
sensors = open("sensors.txt", 'w')
for s in sensorIDs:
    if s not in sensor_list:
        sensor_list.append(s)
        sensors.write(str(s))
        sensors.write('\n')
       # print str('sensor ' + s)
sensors.close()

