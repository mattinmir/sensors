#!/usr/bin/perl 
my ($sensorID, $floor,$located,$active,$type) = @ARGV;

use DBI;
use strict;
use warnings;

my $driver = "mysql"; 
my $database = "sensors";
my $dsn = "DBI:$driver:database=$database";
my $userid = "root";
my $password = "";

my $dbh = DBI->connect($dsn, $userid, $password ) or die $DBI::errstr;

my $sth = $dbh->prepare("INSERT INTO location
                       (sensorID, floor, located, active, type)
                        values
                       (?,?,?,?,?)");
$sth->execute($sensorID,$floor,$located,$active,$type) or die $DBI::errstr;
$sth->finish();
