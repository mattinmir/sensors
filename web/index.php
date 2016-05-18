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
$dbname = 'residential';

// ----------- Establish connection -----------
$link = mysqli_connect('localhost', 'root', ''); 

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
*/

// Defining shorthand variables
$POST_ID = $_POST['sensorid'];
$POST_TABLE = $_POST['tableref'];

// Check if the table selection is blank or is just whitespace
if(!isset($POST_TABLE) || strlen(trim($POST_TABLE)) == 0) 
{
	$table = '';	// If it is, then pick a default table (temp)
	$tabledef = FALSE;		// Keep track that there was no selection
}
else
{
	$table = mysqli_real_escape_string($link, $POST_TABLE); // Get the POST and set the table variable
	$tabledef = TRUE;
}	

// Check if the SensorID field is blank or is just whitespace
if(!isset($POST_ID) || strlen(trim($POST_ID)) == 0)
{
	$result = $link->query("SELECT* FROM $table"); // If blank, then just select all rows from previously defined table
}
else
{
	$sensorid = mysqli_real_escape_string($link, $POST_ID);
	
	if(!$tabledef)	// If the table was not defined, but the SensorID was 
	{				// we need to find the table where that sensor data is located
		$result = mysqli_fetch_assoc($link->query("SELECT * FROM Location WHERE SensorID = '$sensorid'"));	
		$table = $result['Type']; // Get the Location table, and find out what type of sensor is (same as table name)
	}
	$result = $link->query("SELECT* FROM $table WHERE SensorID='$sensorid'"); // Find the relevant rows
}

// Error message if query fails including detailed error
if ($result == false)
{
	$output = 'Error executing query: ' . mysqli_error($link). '</br>Please report this error to the administrator.';
	include 'output.html.php'; 
	exit();
}

// This defines what rows are found in each SQL table (SensorID is implied)
$columnformat = array(
					"Temperature" 	=> array("Timestamp", "Temperature"),
					"Location"		=> array("Floor", "Location", "Active"),
					"Humidity"		=> array("Timestamp", "Humidity"),
					"Lighting"		=> array("Timestamp", "Lux"));
$tableunit	=	array(
					"Temperature" 	=> "&deg;C",
					"Humidity"		=> "%",
					"Lighting" 		=> "Lux");
// Initialise output with implied columns
$output =  "<th>SensorID</th>
			<th>Floor</th>
			<th>Location</th>";

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
function PrintAllTables($HTMLstring)
{
	global $columnformat, $table, $tableunit;
	$alltables = array("Temperature", "Lighting", "Humidity");
	
	foreach($alltables as $tablename)
	{
		$HTMLstring .= "<th>Timestamp</th><th>Sensor Data</th></tr></thead><tbody><tr>";
		$HTMLstring .= "<td>{$row['Timestamp']}</td><td>{$row[$table]$tableunit[$tablename]}</td>";
	}
}

// =====================================================================================================
function PrintSingleTable($queryresult, $HTMLstring)
{
	global $columnformat, $table;
	
	// Now add on relevant columns from the previously defined columnformat array
	foreach($columnformat[$table] as $column)
	{
		$HTMLstring .= "<th>{$column}</th>";
	}
	
	// Finish the column HTML with some spicy end tags
	$HTMLstring .= "</tr></thead><tbody><tr>";
	
	// Now iterate through each row returned from the query
	while($row = $queryresult->fetch_assoc())
	{
		// We have to cross-reference the SensorID with the Location table to obtain its location
		$currentID = $row['SensorID'];	// Store the SensorID and find its location where the sensor is ACTIVE (not replaced)
		$location = mysqli_fetch_row($link->query("SELECT Floor,Location FROM Location WHERE SensorID='{$currentID}' AND Active='1'"));
		$HTMLstring .= "<td>{$currentID}</td>		
					<td>{$location[0]}</td>
					<td>{$location[1]}</td>";	// Since we only select two columns, its just the 0th and 1st columns
					
		// Now start printing the data from the rows. Row data key is equivalent to the data in the columnformat array
		foreach($columnformat[$table] as $column)
		{
			if ($column == "Active")	// The Active column is a Boolean so convert it from machine-lingo into simple English
			{
				if ($row[$column])
					$HTMLstring .= "<td>Yes</td>";	// 1 = Oui
				else
					$HTMLstring .= "<td>No</td>";	// 0 = Nein
			}
			else
				$HTMLstring .= "<td>{$row[$column]}</td>";	
		}

		$HTMLstring .= "</tr>"; // End each row with a row end tag
	}
}

?>