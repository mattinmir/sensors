'''
 Reads whitelist file and performs the command for perl so that fhem sends out telegram to repeater
'''
import os
import sys


com1= 'fhem.pl :7072 define Gateway Enocean 0101010101'
#os.system(com1)
print(com1)
whitelistfilename = sys.argv[1] # whitelist file
whitelist = open(whitelistfilename)
lines = whitelist.readlines()
lines = map(str.strip,lines) # Removing newlines


for a in range(len(lines)):
    string=lines[a]
    list=string.split()
    Repeater = list[a]
    com2 = 'perl fhem.pl :7072 attr Gateway destinationID[' + Repeater + ']'
    #os.system(com2)
    print(com2)
    com3 = 'perl fhem.pl :7072 set Gateway RBS ' + '00'
    print(com3)
    for a in range(len(list)-1):
        sensorID=list[a+1]
        com4 = 'perl fhem.pl :7072 set Gateway 4BS ' + sensorID
        #os.system(com4)
        print(com4)

