import subprocess

perlfile = 'updatedb.pl'
logfile = 'EnO_sensor_019B9ACD-2016.log'
log = open(logfile)

lines = [l for l in log if 'temperature' in l]

# Based on string being of the format '2016-05-27_15:01:21 EnO_sensor_019B9ACD temperature: 23.8'
for l in lines:
    fields = l.split()

    id = fields[1].split('_')[2]
    timestamp = fields[0]
    value = float(fields[3].rstrip('\n'))

    execute_string = 'perl ' + perlfile + ' "' + id + '" "' + timestamp + '" ' + str(value)
    subprocess.call(execute_string)