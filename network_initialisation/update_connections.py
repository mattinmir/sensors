#-------------------------------------------------------------------------------
# Name:        module1
# Purpose:
#
# Author:      mm5213
#
# Created:     17/06/2016
# Copyright:   (c) mm5213 2016
# Licence:     <your licence>
#-------------------------------------------------------------------------------

import requests
import sys

sensorID = sys.argv[1]
connections = sys.argv[2]
r = requests.post('http://api.smartlandlords.co.uk/api.php/update/' + sensorID + '/trans_connections/' + connections + '/', data = {"auth":"YWRtaW46Z2lyYWZmZXM="})

# Keep trying until it wokrs
while (r.status_code != requests.codes.ok):
   r = requests.post('http://api.smartlandlords.co.uk/api.php/update/' + sensorID + '/trans_connections/' + connections + '/', data = {"auth":"YWRtaW46Z2lyYWZmZXM="})