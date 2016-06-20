
import sys
import requests

sensor_id = sys.argv[1]
timestamp = sys.argv[2]
value = sys.argv[3]

# Special code for teach in signal - can ignore packet
if value != '08280B80':
    r = requests.post('http://api.smartlandlords.co.uk/api.php/data/', data = {"auth":"YWRtaW46Z2lyYWZmZXM=","input":'[{"sensorID":"' + sensor_id + '","timestamp":"' + timestamp + '", "value":"' + value + '"}]' })
