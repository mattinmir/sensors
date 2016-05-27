logfile = 'testlog.log'

log = open(logfile)

lines = [l for l in lines if 'temperature' in l]

for l in lines:
