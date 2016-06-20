# Tells DB nodeID is active

import requests
import sys
import time

nodeID = sys.argv[1]
r = requests.post('http://api.smartlandlords.co.uk/api.php/update/' + nodeID + '/status/active/', data = {"auth":"YWRtaW46Z2lyYWZmZXM="})
