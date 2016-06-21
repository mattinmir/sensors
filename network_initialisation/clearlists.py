# Reads the blacklist file and executes perl scripts to distribute blacklists

import os
import sys

fhemdir = '/opt/fhem/'
define_gateway = 'perl ' + fhemdir + 'fhem.pl localhost:7072 "define Gateway Enocean 01010101"'  #define gateway
os.system(define_gateway)
#print(define_gateway)

logfilename = 'Blacklist.txt' #sys.argv[1] #Read blacklist
logfile = open(logfilename)
lines = logfile.readlines()
lines=map(str.strip,lines) # Delete '\n' from strings

for line in lines:
    ids=line.split() # Split each line on space
    repeater = ids[0]

    define_destID = 'perl ' + fhemdir + 'fhem.pl localhost:7072 "attr Gateway destinationID ' + repeater + '"' #define the repeater destination ID
    os.system(define_destID)
    #print(define_destID)
	
    clear_current_list = 'perl ' + fhemdir + 'fhem.pl localhost:7072 "set Gateway RPS 00"' #clear list on repeater
    os.system(clear_current_list)
    #print(clear_current_list)

    for line in lines:
        ids=line.split() # Split each line on space
        repeater = ids[0]
        add_repeater = 'perl ' + fhemdir + 'fhem.pl localhost:7072 "set Gateway 4BS ' + repeater + '"'
        os.system(add_id)
        #print(add_repeater)
        
    add_Gateway = 'perl ' + fhemdir + 'fhem.pl localhost:7072 "set Gateway 4BS 01010101"'
    os.system(add_Gateway)
    #print(add_Gateway)

	
