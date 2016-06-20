# Tells DB how many connecitons sensorID has

import requests
import sys

sensorID = sys.argv[1]
connections = sys.argv[2]
r = requests.post('http://api.smartlandlords.co.uk/api.php/update/' + sensorID + '/trans_connections/' + connections + '/', data = {"auth":"YWRtaW46Z2lyYWZmZXM="})
