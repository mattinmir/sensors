# TODO:
# Program may break if sensor communicates directly with gateway as the location
# of ID in the packet will be different
# Perhaps consult a list of sensor IDs?

import subprocess
import time


def decode(value):
    value = -0.0006*value + 39.959 # Decoding function
    value -= -0.0179*value + 0.7199 # Function to minimise error caused by ASK
    return value

perlfile = 'updatedb.pl'
logfile = 'EnO_VLD_019FEE73.txt'
log = open(logfile)

#lines = [l for l in log if 'temperature' in l]

# Go to end of file
log.seek(0,2)

while True:
    l = log.readline()
    if not l:
        time.sleep(0.1)
    else:
        # Based on string being of the format '2016-06-01_16:31:55 EnO_VLD_019FEE73 00005C08019B9ACD29'
        fields = l.split()

        trans_id = fields[1].split('_')[2]
        timestamp = fields[0]
        payload = fields[2].rstrip('\n')

        value = payload[0:8]
        sensor_id = payload[8:16]
        rssi = payload[16:18]

        # Special code for teach in signal - can ignore packet
        if value is '08280B80':
            continue
        else:
            decoded_value = decode(int(value,16))
            execute_string = 'perl ' + perlfile + ' "' + sensor_id + '" "' + timestamp + '" ' + str(decoded_value)
            try:
                print(execute_string)
                subprocess.call(execute_string)

            except:
                print "Something went wrong!"
