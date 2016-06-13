<?php 
session_start();    
if(!isset($_SESSION['user']))
{
	header( "refresh:2; url='../index.php'" ); 
	echo "No valid credentials to access this page. Please log in again...";
	exit;
}

ob_start();
error_reporting(0);
@ini_set('display_errors', 0);

// ----------- Strips magic quotes away -----------
if (get_magic_quotes_gpc())
{
	function stripslashes_deep($value)
	{
		$value = is_array($value) ?
		array_map('stripslashes_deep', $value) :
		stripslashes($value);
		return $value;
	}
	$_POST = array_map('stripslashes_deep', $_POST);
	$_GET = array_map('stripslashes_deep', $_GET);
	$_COOKIE = array_map('stripslashes_deep', $_COOKIE);
	$_REQUEST = array_map('stripslashes_deep', $_REQUEST);
}

// ====================== Server Connection ======================

// ----------- Connection parameters -----------
//hardcoded params
/*
$servername = 'db629354564.db.1and1.com';
$dbname = 'db629354564';
$user = "dbo629354564";
$dbpw = "building1";*/

//softcoded params

$servername = $_SESSION['db'] . '.db.1and1.com';
$dbname = $_SESSION['db'];
$user = "dbo" . substr($_SESSION['db'], 2);
$dbpw = "building";

/*

//testing db params
$servername = 'localhost';
$dbname = $_SESSION['db'];
$user = root;
$dbpw = "";
*/

// ----------- Establish connection -----------
$link = mysqli_connect($servername, $user, $dbpw); 

// Connect to the server which contains the DB
if (!$link)
	errorForm('Unable to connect to the database server.');

// Ensuring correct encoding 
if (!mysqli_set_charset($link, 'utf8'))
	errorForm('Unable to set database connection encoding.');

// Selecting the correct database
if(!mysqli_select_db($link, $dbname))
	errorForm('Unable to locate the database.');
// =======================================================

/*tips:
- "" ensures the variable value not variable 
- "" use isset for checking blank field 
- Query is always left with a blank space at the end and trim at the end 
- CHANGE FOR bool check if check box returns false otherwise if box is set then isset
*/


// Defining shorthand variables
$POST_ID = $_POST['sensorid'];
$POST_TABLE = $_POST['tableref'];
$POST_FLOORS = $_POST['floors'];
$POST_DATE = $_POST['daterange'];
$POST_LIFTS = $_POST['lifts'];
$POST_STAIRWELLS = $_POST['stairwells'];
$POST_CORRIDORS = $_POST['corridors'];
$POST_PARKING = $_POST['parking'];
$POST_LOC = $_POST['location'];	
$POST_NOTIFICATION = $_POST['notification'];

/*
//$POST_ID = '3';
$POST_TABLE = 'Lux';
$POST_FLOORS = '3';
$POST_DATE = '20/05/2016 - 24/05/2016';
$POST_LIFTS = FALSE;
$POST_STAIRWELLS = FALSE;
$POST_CORRIDORS = FALSE;
$POST_PARKING = FALSE;
//$POST_LOCATIONS = $_POST['Locations'];*/


// This defines what rows are found in each SQL table (SensorID is implied)

$columnformat = array(
				"temperature" 	=> array("Timestamp", "Temperature"),
				//"location"		=> array("Floor", "Location", "Active"),
				"humidity"		=> array("Timestamp", "Humidity"),
				"lux"		=> array("Timestamp", "Lux"));
				
$tableunit	=	array(
				"temperature" 	=> "&deg;C",
				"humidity"		=> "%",
				"lux" 		=> " Lux");
		
$alltables = array("temperature", "lux", "humidity");
$JSONtable = array();

if($POST_LOC)
{
	$result = $link->query("SELECT * FROM nodes");
	while($row = $result->fetch_assoc())
	{
		$JSONtable[] = array("sensorID" => (int)$row['sensorID'], "floor" => (int)$row['floor'], 
			"location" => $row['located'], "status" => $row['status'], "type" => $row['type']. "trans_connections" => $row['trans_connections']); //active is not bool anymore and is called status, need to add row trans_connections 
	}
	ob_end_clean();
	echo json_encode($JSONtable);
	exit();
}

// ========================================== NOTIFICATIONS ==========================================
// Check for failures!!!

if($POST_NOTIFICATION)
{
	foreach($alltables as $tablename)
	{
		$failure_query .= str_replace("repp", $tablename, "SELECT sensorID, floor, located, type, timestamp AS last_seen FROM nodes JOIN (SELECT* FROM repp ORDER BY timestamp DESC) as latest ON latest.sensorID = nodes.deviceID WHERE status = 'failed' GROUP BY sensorID 
UNION ");
	}
	$failure_query = substr($failure_query, 0, -6);
	$failure_query = $failure_query . 'ORDER BY last_seen ASC';
	$result = $link->query($failure_query);
	if ($result->num_rows != 0)	// If there is a row in Failures, then there is a failure. Unless the SQL database fails. Or maybe they both failed?
	{
		while($row = mysqli_fetch_assoc($result))	// Print the data in the row with HTML
		{
			$JSONtable[] = array("sensorID" => (int)$row['sensorID'], "timestamp" => strtotime($row['last_seen']) );
		}
	}
	ob_end_clean();
	echo json_encode($JSONtable);
	exit();
}
// ========================================== INITIAL WARNINGS PANEL: DISMISSABLE PANEL ==========================================

//SELECT deviceID as sensorID, floor, located, status, type FROM nodes WHERE trans_connections = 1 AND status = 'active' AND type != 'transceiver'



// ========================================== INSOLATED SENSORS WARNINGS (IN THE EVENT OF TRANSCIEVER FAILURE)==========================================

//SELECT deviceID as sensorID, floor, located, status, type FROM nodes WHERE trans_connections = 0 AND status = 'active' AND type != 'transceiver'

// ========================================== MAIN CODE ==========================================

/*********************************SENSOR ID CHOSEN********************************/

//ID has been selected 
if(isset($POST_ID) && (strlen(trim($POST_ID)) != 0)){
		$sensorid = mysqli_real_escape_string($link, $POST_ID);
		$result = mysqli_fetch_assoc($link->query("SELECT * FROM nodes WHERE deviceID = '$sensorid'"));	
		$table = $result['type']; // Get the Nodes table, and find out what type of sensor is (same as table name)
		$result = $link->query("SELECT* FROM $table JOIN nodes ON $table.sensorID = nodes.deviceID WHERE sensorID=$sensorid");
		PrintSingleTable($result, $table);
}

/*********************************GENERIC QUERY - NO SENSOR ID *******************************/
else{
	//As dates always have a default value
	/******************************** START DATES ********************************/
		if(isset($POST_DATE)){
			//explode to seperate date from and date to 
			$daterange = explode(" - ", $POST_DATE); 
		
			//explode each date to seperate the MM, DD and YYYY
			$datefromarray = explode ("-", $daterange[0]);
			$datetoarray = explode ("-", $daterange[1]);
		
			//Put into the format of the database
			$datefrom = $datefromarray[2] . '-' . $datefromarray[1] . '-' . $datefromarray[0] . ' ' . '00:00:00';
			$dateto = $datetoarray[2] . '-' . $datetoarray[1] . '-' . ($datetoarray[0]) . ' ' . '23:59:59';
		
			//Form sub date query
			$date_query = "WHERE timestamp BETWEEN '$datefrom' AND '$dateto' ";
		}
		/******************************** END DATES ********************************/
	
	//Start query
	$query = "SELECT* FROM repp JOIN nodes ON repp.sensorID = nodes.deviceID " . $date_query;
	
	
	/********************************NO SELECTION - POST ALL RESULTS*****************************************/
	//Check all POST variables to ensure all have not been assigned to confirm no selection has been made 
	if(!isset($POST_TABLE) && !($POST_LIFTS) && !($POST_PARKING) && !($POST_STAIRWELLS) && !($POST_CORRIDORS) && !isset($POST_FLOORS) && strlen(trim($POST_FLOORS)) == 0){
		PrintAllTables($link, $query);
	}

	/********************************A SELECTION HAS BEEN MADE**************************************/
	else{
		/******************************** START LOCATIONS ********************************/
		$locationarray = array ($POST_LIFTS, $POST_CORRIDORS, $POST_STAIRWELLS, $POST_PARKING);
		//var_dump($locationarray);
		$loc_query = '';
		//check if any locations have been selected. 
		foreach($locationarray as $singleloc){
			//ask alex if post-lists will be false or blank 
			if(strlen(trim($singleloc)) !=0){
				$loc_query .= "OR located = '$singleloc' "; 
			}
		}
			
		//check to see if location has been selected at all. 
		if(strlen(trim($loc_query)) !=0){
			$loc_query = substr($loc_query, 3);
			$query = $query . 'AND ' . $loc_query; 
		}
		/******************************** END LOCATIONS ********************************/
		/******************************** START FLOORS ********************************/
		if(isset($POST_FLOORS) && strlen(trim($POST_FLOORS)) != 0){
			
			$floor_query = '';
			//$POST_FLOORS = mysqli_real_escape_string($link, $POST_FLOORS);
				
			//if the string does contain a comma, it may be of two forms 
			if(!strpos($POST_FLOORS, ',')){
		
				//form 1: just a single digit
				if(!strpos($POST_FLOORS, '-')){
					//leave a space at the end 
					if(is_numeric($POST_FLOORS)){
						$floor_query = "OR floor = $POST_FLOORS ";
					}				
					else{
						errorForm('Invalid input');
					}
				}
		
				//form 2: between two floors e.g. 6-8
				else{
					$sepfloor = explode("-", $POST_FLOORS);
					if(is_numeric($sepfloor[0]) && is_numeric($sepfloor[1])){ 
						$floor_query = "OR floor BETWEEN $sepfloor[0] AND $sepfloor[1] ";
					}
						
					else{
						errorForm('Invalid input');
					}
				}
			}
	
			//if the floor does contain a comma 
			else{
		
				//seperate the string
				$sepfloor = explode(",", $POST_FLOORS);
		
				//The sub strings may be single digits or of form 6-8
				foreach($sepfloor as $newvar){
			
					//single digits
					if(!strpos($newvar, '-')){	
						if(is_numeric($newvar)){
							$floor_query .=  "OR floor = $newvar ";
						}
							
						else{
							errorForm('Invalid input'); 
							}
					}
			
					//of form 6-8
					//ask alex if this works?
					//otherwise put in an strnlen test and chose 0 and 2
					else{
						$temparray = explode("-", $newvar);
						if(is_numeric($temparray[0]) && is_numeric($temparray[1])){
							$floor_query .= "OR floor BETWEEN $temparray[0] AND $temparray[1] ";
						}
							
						else{
								errorForm('Invalid input');
						}
					}
				}
			}
				
			//check if floors has been selected at all
			if(strlen(trim($floor_query)) !=0){
					$floor_query = substr($floor_query, 3); // removes OR and space 
					$query = $query . 'AND ' . $floor_query
					
				}
		}
		/******************************** END FLOORS ********************************/
		
		/******************************** NO TABLE SELECTION****************************************/
		if(!isset($POST_TABLE)){
			PrintAllTables($link, $query);
		}
			
		
		/********************************TABLE SELECTION****************************************/
		else{
			$result = $link->query(str_replace("repp", $POST_TABLE, $query)); 
			PrintSingleTable($result, $POST_TABLE);
		}	
	
	}//a selection of somekind has been made
}//generic query

//$result->free(); // I think that's all the query results

//echo '<div id="JSON-datatable" style="display: none;">'.htmlspecialchars(json_encode($JSONtable)).'</div>';
//ob_end_clean();
echo json_encode($JSONtable);
//$error = $query;
//echo $error;

// =====================================================================================================
// ========================================= FUNCTIONS ================================================
// =====================================================================================================
function PrintAllTables($link, $query)
{
	global $alltables;
	
	foreach($alltables as $tablename)
	{
		//var_dump(str_replace("repp", $tablename, $query));
		$queryresult = $link->query(str_replace("repp", $tablename, $query));
		//var_dump(str_replace("repp", $tablename, $query));
		//echo "<br>";
		if($queryresult->num_rows != 0){
			PrintSingleTable($queryresult, $tablename);
		}		
	}
}
function PrintSingleTable($queryresult, $table)
{
	global $JSONtable;
	
	if(!$queryresult){
		return;
	}
	
	while($row = $queryresult->fetch_assoc())
	{
		$JSONtable[$table][] = array("sensorID" => (int)$row['sensorID'], "floor" => (int)$row['floor'], 
			"location" => $row['located'], "timestamp" => strtotime($row['timestamp']), "value" => (double)$row['value'], "status" => $row['status']);
	}
}

function errorForm($output)
{
	include 'output.html.php';
	exit();
}
