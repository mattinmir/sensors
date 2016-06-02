#!/usr/bin/perl 
my ($sensorID, $timestamp, $value) = @ARGV;

use DBI;
use strict;
use warnings;

my $driver = "mysql"; 
my $database = "sensors";
my $dsn = "DBI:$driver:database=$database";
my $userid = "root";
my $password = "";

my $dbh = DBI->connect($dsn, $userid, $password ) or die $DBI::errstr;

my $sth = $dbh->prepare("INSERT INTO temperature
                       (sensorID, timestamp, value)
                        values
                       (?,?,?)");
$sth->execute($sensorID, $timestamp, $value) or die $DBI::errstr;
$sth->finish();
