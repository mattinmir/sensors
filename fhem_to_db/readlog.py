import subprocess
import time

perlfile = 'updatedb.pl'
logfile = 'testlog.log'
log = open(logfile)

#lines = [l for l in log if 'temperature' in l]

# Go to end of file
log.seek(0,2)

while True:
    l = log.readline()
    if not l:
        time.sleep(0.1)
    elif 'temperature' in l:
        # Based on string being of the format '2016-05-27_15:01:21 EnO_sensor_019B9ACD temperature: 23.8'
        fields = l.split()

        sensor_id = fields[1].split('_')[2]
        timestamp = fields[0]
        value = float(fields[3].rstrip('\n'))

        execute_string = 'perl ' + perlfile + ' "' + sensor_id + '" "' + timestamp + '" ' + str(value)
        # subprocess.call(execute_string)
        print(execute_string)
