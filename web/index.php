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
- CHANGE FOR bool check if check box returns false otherwise if box is set then isset
*/


// Defining shorthand variables
$POST_ID = $_POST['sensorid'];
$POST_TABLE = $_POST['tableref'];
$POST_FLOORS = $_POST['floors'];
$POST_DATE = $_POSt['daterange'];
//$POST_LOCATIONS = $_POST['Locations'];


//$POST_ID = '45';
//$POST_TABLE = 'Lux';
//$POST_FLOORS = '3';
$POST_LIFTS = $_POST['Lifts'];
$POST_STAIRWELLS = $_POST['Stairwells'];
$POST_CORRIDORS = $_POST['Corridors'];
$POST_PARKING = $_POST['Parking'];



// This defines what rows are found in each SQL table (SensorID is implied)
$columnformat = array(
				"Temperature" 	=> array("Timestamp", "Temperature"),
				"Location"		=> array("Floor", "Located", "Active"),
				"Humidity"		=> array("Timestamp", "Humidity"),
				"Lux"		=> array("Timestamp", "Lux"));
				
$tableunit	=	array(
				"Temperature" 	=> "&deg;C",
				"Humidity"		=> "%",
				"Lux" 		=> " Lux",
				"Timestamp"		=> "");
//for somereason output array at the end 
//$output = array();	


/*********************************SENSOR ID CHOSEN********************************/

//ID has been selected 
if(isset($POST_ID) && (strlen(trim($POST_ID)) != 0)){
	
	if(is_numeric($POST_ID)){
	
		$sensorid = mysqli_real_escape_string($link, $POST_ID);
		$result = mysqli_fetch_assoc($link->query("SELECT * FROM Location WHERE sensorID = '$sensorid'"));	
		$table = $result['Type']; // Get the Location table, and find out what type of sensor is (same as table name)
		$result = $link->query("SELECT * FROM $table JOIN Location USING (sensorID) WHERE sensorID=$sensorid");
		$output[$table] = PrintSingleTable($result, $table);
	}
	
	//fix:
	else{
		$output = 'DONT FUCK WITH US. RUN BITCH THE FBI IS ON YOU';
		include 'output.html.php';
		exit();
	}
}

/*********************************GENERIC QUERY - NO SENSOR ID *******************************/
else{
	
	/********************************NO SELECTION - POST ALL RESULTS*****************************************/
	//No selection is made. 
	//ask alex if post-lifts will be blank or false
	//test empty
	//if(!isset($POST_TABLE) && empty($POST_LOCATIONS) && !isset($POST_FLOORS) && strlen(trim($POST_FLOORS)) == 0){
	if(!isset($POST_TABLE) && !($POST_LIFTS) && !($POST_PARKING) && !($POST_STAIRWELLS) && !($POST_CORRIDORS) && !isset($POST_FLOORS) && strlen(trim($POST_FLOORS)) == 0){
		$query = "SELECT* FROM temporary JOIN Location USING(sensorID)";
		$output = PrintAllTables($link, $query);
		
	}

	/********************************A SELECTION HAS BEEN MADE**************************************/
	else{
		$query = "SELECT* FROM temporary JOIN Location USING(sensorID) ";
		/******************************** START LOCATIONS ********************************/
		$locationarray = array ($POST_LIFTS, $POST_CORRIDORS, $POST_STAIRWELLS, $POST_PARKING);
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
			$query = $query . 'WHERE ' . $loc_query; 
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
						$output = 'DONT FUCK WITH US. RUN BITCH THE FBI IS ON YOU';
						include 'output.html.php';
						exit(); 
					}
				}
		
				//form 2: between two floors e.g. 6-8
				else{
					$sepfloor = explode("-", $POST_FLOORS);
					if(is_numeric($sepfloor[0]) && is_numeric($sepfloor[1])){ 
						$floor_query = "OR floor BETWEEN $sepfloor[0] AND $sepfloor[1] ";
					}
						
					else{
						$output = 'DONT FUCK WITH US. RUN BITCH THE FBI IS ON YOU';
						include 'output.html.php';
						exit();
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
							$output = 'DONT FUCK WITH US. RUN BITCH THE FBI IS ON YOU';
							include 'output.html.php';
							exit(); 
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
								$output = 'DONT FUCK WITH US. RUN BITCH THE FBI IS ON YOU';
								include 'output.html.php';
								exit(); 
						}
					}
				}
			}
				
			//check if floors has been selected at all
			if(strlen(trim($floor_query)) !=0){
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
		}
		/******************************** END FLOORS ********************************/
		/******************************** START DATES ********************************/
		if(isset(P
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		/******************************** END DATES ********************************/
		
		/******************************** NO TABLE SELECTION****************************************/
		//need to write this 
		if(!isset($POST_TABLE)){
			$output = PrintAllTables($link, $query);
			/*$output = $query;
			include 'output.html.php';
			exit();*/
		}
			
		
		/********************************TABLE SELECTION****************************************/
		else{
			$result = $link->query(str_replace("temp", $POST_TABLE, $query)); 
			$output[$POST_TABLE] = PrintSingleTable($result, $POST_TABLE);
		}	
	
	}//a selection of somekind has been made
}//generic query

//$output = $query;
//include 'output.html.php';
//exit();


// ========================================== NOTIFICATIONS ==========================================
// Check for failures!!!
$result = mysqli_query($link, "SELECT* FROM Failures");
$failure_output = "";
if ($result->num_rows != 0)	// If there is a row in Failures, then there is a failure. Unless the SQL database fails. Or maybe they both failed?
{
	while($row = mysqli_fetch_assoc($result))	// Print the data in the row with HTML
	{
		$failure_output .= '<div class="list-group-item" style="overflow:hidden"><i class="fa fa-bolt fa-fw"></i>Sensor ID '.$row["SensorID"].' has failed! 
			<span class="pull-right text-muted small"><em>'.$row["Timestamp"].'</em></span></div>'; 
	} // This looks like garbage but because html has "" in it, need to switch up the syntax
}

$result->free(); // I think that's all the query results

include 'index.html.php';	// Now ready for HTML

// =====================================================================================================
// ========================================= FUNCTIONS ================================================
// =====================================================================================================
function PrintAllTables($link, $query)
{
	//global $columnformat, $tableunit;
	 
	$alltables = array("Temperature", "Lux", "Humidity");
	$HTMLstring = array();
	
	foreach($alltables as $tablename)
	{
		$queryresult = $link->query(str_replace("temp", $tablename, $query)); 	
		$HTMLstring[$tablename] = PrintSingleTable($queryresult, $tablename);
	}
	
	return $HTMLstring;
}


function PrintSingleTable($queryresult, $table)
{
	global $columnformat, $tableunit;
	
	if(!$queryresult){
		return '';
	}
	
	// Initialise output with implied columns
	$HTMLstring =  "<th>SensorID</th>
					<th>Floor</th>
					<th>Located</th>"; // add timestamp
	
	// Now add on relevant columns from the previously defined columnformat array
	foreach($columnformat[$table] as $column)
	{
		$HTMLstring .= "<th>{$column}</th>";
	}
	
	// Finish the column HTML with some spicy end tags
	$HTMLstring .= "</tr></thead><tbody><tr>";

	// Error message if query fails including detailed error 
/*	if ($result == false)
	{
		$output = 'Error executing query: ' . mysqli_error($link). '</br>Please report this error to the administrator.';
		include 'output.html.php'; 
		exit();
	}*/
	
	
	// Now iterate through each row returned from the query	
	while($row = $queryresult->fetch_assoc())
	{
		$HTMLstring .= "<td>{$row['SensorID']}</td>		
					<td>{$row['Floor']}</td>
					<td>{$row['Located']}</td>";	// Since we only select two columns, its just the 0th and 1st columns
					
		// Now start printing the data from the rows. Row data key is equivalent to the data in the columnformat array
		foreach($columnformat[$table] as $column)
		{
			/*if ($column == "Active")	// The Active column is a Boolean so convert it from machine-lingo into simple English
			{
				if ($row[$column])
					$HTMLstring .= "<td>Yes</td>";	// 1 = Oui
				else
					$HTMLstring .= "<td>No</td>";	// 0 = Nein
			}
			else*/
				$HTMLstring .= "<td>{$row[$column]}{$tableunit[$column]}</td>";	
		}

		$HTMLstring .= "</tr>"; // End each row with a row end tag
	}
	return $HTMLstring;
}  					

?>