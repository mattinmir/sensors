#Sendtele reads the file blacklist and performs the comand for perl so that
# fhem sends out telegram to repeater

import os
import sys 
com1= 'fhem.pl :7072 define Gateway Enocean 010101'  #define gateway
os.system(com1)
#print(com1)
logfile = sys.argv[1] #read black/white-list
log = open(logfile)
l = log.readlines() #put all lines in a list l
l2=map(str.strip,l) #delete '\n' from strings

for a in range(len(l2)):   #for loop goes through all lines
    string=l2[a] #line
    list=string.split() #split line in on every space put in list
    Repeater = list[a]
    com2 = 'perl fhem.pl :7072 attr Gateway destinationID ' + Repeater #define the repeater destination ID
    os.system(com2)
    #print(com2)
    com3 = 'perl fhem.pl :7072 set Gateway RPS ' + '00' #clear list on repeater
    print(com3)
    for a in range(len(list)-1): #for loop sends all ID we want to update the list on repeater with
        sensorID=list[a+1]
        com4 = 'perl fhem.pl :7072 set Gateway 4BS ' + sensorID #send ID to repeater 
        os.system(com4)
        #print(com4)

