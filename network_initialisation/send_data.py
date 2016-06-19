
import sys
import requests

def decode(value):
    value = -0.0006*value + 39.959 # Decoding function
    value -= -0.0179*value + 0.7199 # Function to minimise error caused by ASK
    return value

sensor_id = sys.argv[1]
timestamp = sys.argv[2]
value = sys.argv[3]

# Special code for teach in signal - can ignore packet
if value != '08280B80':
    decoded_value = decode(int(value,16))
    r = requests.post('http://api.smartlandlords.co.uk/api.php/data/', data = {"auth":"YWRtaW46Z2lyYWZmZXM=","input":'[{"sensorID":"' + sensor_id + '","timestamp":"' + timestamp + '", "value":"' + str(decoded_value) + '"}]' })