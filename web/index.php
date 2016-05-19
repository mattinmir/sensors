<?php     
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
$servername = 'localhost';
$dbname = 'project';

// ----------- Establish connection -----------
$link = mysqli_connect('localhost', 'root', 'root'); 

// Connect to the server which contains the DB
if (!$link){
	$output = 'Unable to connect to the database server.';
	include 'output.html.php';
	exit();
}

// Ensuring correct encoding 
if (!mysqli_set_charset($link, 'utf8')){
	$output = 'Unable to set database connection encoding.';
	include 'output.html.php';
	exit();
}

// Selecting the correct database
if(!mysqli_select_db($link, $dbname)){
	$output = 'Unable to locate the database.';
	include 'output.html.php';
	exit(); 
}
// =======================================================


/*tips:
- "" ensures the variable value not variable 
- "" use isset for checking blank field 
- Query is always left with a blank space at the end and trim at the end 
*/

/*
// Defining shorthand variables
$POST_ID = $_POST['sensorid'];
$POST_TABLE = $_POST['tableref'];
$POST_FLOORS = $_POST['floors'];
$POST_LIFTS = $_POST['Lifts'];
$POST_STAIRWELLS = $_POST['Stairwells'];
$POST_CORRIDORS = $_POST['Corridors'];
$POST_PARKING = $_POST['Parking'];
*/

//$POST_ID = '67';
$POST_TABLE = 'Temperature';
$POST_FLOORS = '1, 6-8';
$POST_LIFTS = "lifts";
$POST_STAIRWELLS = 'stairwells';
$POST_CORRIDORS = FALSE;
$POST_PARKING = FALSE;


/*********************************SENSOR ID CHOSEN********************************/

//ID has been selected 
if(isset($POST_ID) || !strlen(trim($POST_ID)) == 0){
	
	/*$sensorid = mysqli_real_escape_string($link, $POST_ID);
	$result = mysqli_fetch_assoc($link->query("SELECT * FROM Location WHERE SensorID = '$sensorid'"));	
	$table = $result['Type']; // Get the Location table, and find out what type of sensor is (same as table name)
	$result = $link->query("SELECT * FROM $table JOIN Location USING (SensorID) WHERE SensorID=$sensorid");
	$output[$table] = PrintSingleTable($result, $table);*/
	
	$output = 'SENSORID chosen'; 
	include 'output.html.php';
	exit();
	
}

/*********************************GENERIC QUERY - NO SENSOR ID *******************************/
else{
	
	/********************************NO SELECTION - POST ALL RESULTS*****************************************/
	//No selection is made. 
	//ask alex if post-lifts will be blank or false
	if(!isset($POST_TABLE) && !isset($POST_LIFTS) && !($POST_PARKING) && !($POST_STAIRWELLS) && !($POST_CORRIDORS) && !isset($POST_FLOORS) && strlen(trim($POST_FLOORS)) == 0){
		$query1 = 'SELECT* FROM Location JOIN Temperature USING (SensorID) </br>'; 
		$query2 = 'SELECT* FROM Location JOIN Humidity USING (SensorID) </br>'; 
		$query3 = 'SELECT* FROM Location JOIN Lux USING (SensorID) </br>'; 
		$output = $query1 . $query2 . $query3;
		include 'output.html.php';
		exit();
		
	}

	/********************************A SELECTION HAS BEEN MADE**************************************/
	else{
		
		/******************************** NO TABLE SELECTION****************************************/
		//need to write this 
		if(!isset($POST_TABLE)){
		
			$output = '3 queries need - successfull'; 
			include 'output.html.php';
			exit();
			
		}	
		
		/********************************TABLE SELECTION****************************************/
		else{
			$query = "SELECT* FROM $POST_TABLE JOIN Location USING(SensorID) "; 
			//ask alex if this values can be put into an array like this"
			
			/******************************** START LOCATIONS ********************************/
			$locationarray = array ($POST_LIFTS, $POST_CORRIDORS, $POST_STAIRWELLS, $POST_PARKING);
			$loc_query = '';
			//check if any locations have been selected. 
			foreach($locationarray as $singleloc){
			
				//ask alex if post-lists will be false or blank 
				if(strlen(trim($singleloc)) !=0){
					$loc_query .= "OR location = '$singleloc' "; 
				}
				
				
			}
			
			//check to see if location has been selected at all. 
			if(strlen(trim($loc_query)) !=0){
				$loc_query = substr($loc_query, 3);
				$query = $query . 'WHERE ' . $loc_query; 
			}
			/******************************** END LOCATIONS ********************************/
			
			
			
			/******************************** START FLOORS ********************************/
			if(isset($POST_FLOORS) || !strlen(trim($POST_FLOORS)) == 0){
			//if the string does contain a comma, it may be of two forms 
				if(!strpos($POST_FLOORS, ',')){
		
					//form 1: just a single digit
					if(!strpos($POST_FLOORS, '-')){
						//leave a space at the end 
						$floor_query = "OR floor = $POST_FLOORS ";
					}
		
					//form 2: between two floors e.g. 6-8
					else{
						$sepfloor = explode("-", $POST_FLOORS); 
						$floor_query = "OR floor BETWEEN $sepfloor[0] AND $sepfloor[1] ";
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
							$floor_query .=  "OR floor = $newvar ";
						}
			
						//of form 6-8
						//ask alex if this works?
						//otherwise put in an strnlen test and chose 0 and 2
						else{
							$temparray = explode("-", $newvar);
							$floor_query .= "OR floor BETWEEN $temparray[0] AND $temparray[1] ";
						}
					}
				}
	
				$floor_query = substr($floor_query, 3); // remvoves OR and space 
	
				//check if WHERE has already been used 
				if(!strpos($query, 'WHERE')){
					$query = $query. 'WHERE ' . $floor_query;
				}
			
				//if WHERE has already been used i.e. location was selected
				else{
					$query = $query. 'AND ' . $floor_query;
				}
			}
			/******************************** END FLOORS ********************************/
			/******************************** START DATES ********************************/
			/******************************** END DATES ********************************/
			
		}//table selection
	}//a selection of somekind has been made
}//generic query

$output = $query;
include 'output.html.php';
exit();  					

?>