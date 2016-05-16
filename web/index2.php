<?php    
	$linkz = mysqli_connect('localhost', 'root', ''); 
	
	if (!$link) 
	{
		print("cannot connect");
		exit(); 
	}
	if(!mysqli_select_db($link, 'residential'))
	{
		print("cannot find database");
		exit();
	}
	echo '<br>';
	
	$sql = "SELECT * FROM Temperature";
	$result = mysqli_query($link, $sql);
	
	while($row = mysqli_fetch_assoc($result))
	{
		$output .= "<tr>
			<td> {$row['SensorID']} </td>
			<td> {$row['Timestamp']} </td>
			<td> {$row['Temperature']} </td>
			</tr>";
	}
	
	include 'index2.html.php';
?>
