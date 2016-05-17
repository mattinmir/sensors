<?php     
	//strips magic quotes away
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
	
	// Server parameters
	$servername = 'localhost';
	$dbname = 'residential';

	//establish connection 
	$link = mysqli_connect('localhost', 'root', ''); 

	//error 
	if (!$link){
		$output = 'Unable to connect to the database server.';
		include 'output.html.php';
		exit();
	}

	//ensuring correct encoding 
	if (!mysqli_set_charset($link, 'utf8')){
		$output = 'Unable to set database connection encoding.';
		include 'output.html.php';
		exit();
	}

	//selecting the database

	if(!mysqli_select_db($link, $dbname)){
		$output = 'Unable to locate the database.';
		include 'output.html.php';
		exit(); 
	}


	/*tips:
	- "" ensures the variable value not variable 
	- "" use isset for checking blank field 
	*/
	$table = 'Location'; // temporary variable
	$POSTresult = $_POST['sensorid'];
	
	if(!isset($POSTresult) || strlen(trim($POSTresult)) == 0)
	{
		$result = mysqli_query($link, "SELECT* FROM $table");
	}
	else
	{
	//storing the result
		$sensorid = mysqli_real_escape_string($link, $POSTresult); //fix: tell mattin to call variable sensorid
		$result = mysqli_query($link, "SELECT* FROM $table WHERE SensorID = '$sensorid'");
	}

	//error message for result including detailed error
	if ($result == false)
	{
		$output = 'Error fetching sensorid:' . mysqli_error($link);
		include 'output.html.php'; 
		exit();
	}
	$columnformat = array(
						"Temperature" 	=> array("SensorID", "Timestamp", "Temperature"),
						"Location"		=> array("SensorID", "Floor", "Location", "Active"),
						"Humidity"		=> array("SensorID", "Timestamp", "Humidity"));
	
	//initialise (as we are concatenating)
	$output = "";
	
	foreach($columnformat["Temperature"] as $column)
	{
		$output .= "<th>{$columnformat}</th>"
	}
	$output .= "</tr></thead><tbody><tr>";
			
	while($row = mysqli_fetch_assoc($result))
	{
		foreach($columnformat["Temperature"] as $column)
		{
			$output .= "<td>{$row[$columnformat}</th>"
		}
		$output .= "</tr>";
	}
	
	include 'index.html.php';
?>
