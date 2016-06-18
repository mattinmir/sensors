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
import time

nodeID = sys.argv[1]
r = requests.post('http://api.smartlandlords.co.uk/api.php/update/' + nodeID + '/status/active/', data = {"auth":"YWRtaW46Z2lyYWZmZXM="})

# Keep trying until it wokrs
while (r.status_code != requests.codes.ok):
   r = requests.post('http://api.smartlandlords.co.uk/api.php/update/' + nodeID + '/status/active/', data = {"auth":"YWRtaW46Z2lyYWZmZXM="})