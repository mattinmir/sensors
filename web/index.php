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
	
	$POST_ID = $_POST['sensorid'];
	$POST_TABLE = $_POST['tableref'];

	if(!isset($POST_TABLE) || strlen(trim($POST_TABLE)) == 0)
	{
		$table = 'Temperature'; // temporary variable
		$tabledef = FALSE;
	}
	else
	{
		$table = mysqli_real_escape_string($link, $POST_TABLE); 
		$tabledef = TRUE;
	}	

	if(!isset($POST_ID) || strlen(trim($POST_ID)) == 0)
	{
		if($tabledef)
			$result = mysqli_query($link, "SELECT* FROM $table");
		else
		{
			$result = $link->query("SELECT * FROM `Lighting` WHERE SensorID='{$POST_ID}' 
									UNION SELECT * FROM `Humidity` WHERE SensorID='{$POST_ID}' 
									UNION SELECT * FROM `Temperature` WHERE SensorID='{$POST_ID}'");
			$output = "table is ".mysqli_fetch_field_direct($result, 2)->name;
				include 'output.html.php'; 
				exit();
		}
	}
	else
	{
	//storing the result
		$sensorid = mysqli_real_escape_string($link, $POST_ID);
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
						"Temperature" 	=> array("Timestamp", "Temperature"),
						"Location"		=> array("Floor", "Location", "Active"),
						"Humidity"		=> array("Timestamp", "Humidity"),
						"Lighting"		=> array("Timestamp", "Lux"));
	
	//initialise (as we are concatenating)
	$output = "<th>SensorID</th>
				<th>Floor</th>
				<th>Location</th>";
	
	foreach($columnformat[$table] as $column)
	{
		$output .= "<th>{$column}</th>";
	}
	$output .= "</tr></thead><tbody><tr>";
			
	while($row = mysqli_fetch_assoc($result))
	{
		$currentID = $row['SensorID'];
		$location = mysqli_fetch_row($link->query("SELECT Floor,Location FROM Location WHERE SensorID='{$currentID}' AND Active='1'"));
		$output .= "<td>{$currentID}</td>
					<td>{$location[0]}</td>
					<td>{$location[1]}</td>";
		foreach($columnformat[$table] as $column)
		{
			if ($column == "Active")
			{
				if ($row[$column])
					$output .= "<td>Yes</td>";
				else
					$output .= "<td>No</td>";
			}
			else
				$output .= "<td>{$row[$column]}</td>";
		}
		$output .= "</tr>";
	}
	
	// Check for failures!!!
	$result = mysqli_query($link, "SELECT* FROM Failures");
	$failure_output = "";
	if ($result->num_rows != 0)
	{
		while($row = mysqli_fetch_assoc($result))
		{
			$failure_output .= '<div class="list-group-item" style="overflow:hidden"><i class="fa fa-bolt fa-fw"></i>Sensor ID '.$row["SensorID"].' has failed! 
				<span class="pull-right text-muted small"><em>'.$row["Timestamp"].'</em></span></div>';
		}
	}
	
	include 'index.html.php';
?>
