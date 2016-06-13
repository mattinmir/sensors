<?php
$username = $_REQUEST['user'];
$password = $_REQUEST['pass'];
$config = parse_ini_file('scripts/dbconfig.ini'); 
try{
	$db = new PDO("mysql:host=".$config['servername'].";dbname=".$config['dbname'].";charset=utf8mb4", $config['username'], $config['password']);
}
catch(PDOException $e)
{
	header('HTTP/1.1 500 Could not connect to db');
	header('Content-Type: application/json; charset=UTF-8');
	echo $e->getMessage();
	die();
}
$query = $db->prepare('
  SELECT
    *
  FROM users
  WHERE
    username = :username
  LIMIT 1
  ');

//:username is a placeholder replaced by our username variable
$query->bindParam(':username', $username);
//now run the code
$query->execute();
$user = $query->fetch(PDO::FETCH_OBJ);

// Hashing the password with its hash as the salt returns the same hash
if ( hash_equals($user->hash, crypt($password, $user->hash)) )
{
	$userdb = $user->db;
	//var_dump($userdb);
	$server = $userdb.".db.1and1.com";
	$dbname = $userdb;
	$user = "dbo" . substr($userdb, 2);
	$dbpw = "building"; 

	$db = null;
	try
	{
		// connect to sql db
		$db = new PDO("mysql:host=".$server.";dbname=".$userdb.";charset=utf8mb4", $user, $dbpw);
	}
	catch(PDOException $e)
	{
		header('HTTP/1.1 500 Could not connect to db2');
		header('Content-Type: application/json; charset=UTF-8');
		//echo "mysql:host=".$server.";dbname=".$userdb.";charset=utf8mb4".$user.$dbpw;
		echo $e->getMessage();
		die();
	}
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
	// get REST request
	$pathinfo = empty($_SERVER['PATH_INFO']) ? $_SERVER['ORIG_PATH_INFO'] : $_SERVER['PATH_INFO'];
	$request = explode('/', trim($pathinfo,'/'));

	// depending on how the data is delivered, change how it is applied
	switch ($_SERVER['REQUEST_METHOD'])
	{
		case 'GET': // get data
			// no support as of yet
			header('HTTP/1.1 501 Not Supported');
		break;
		case 'PUT': // update row
/* 			$query = $db->prepare("UPDATE `location` SET `active`=:state WHERE `sensorID`=:sensorid"); 
			$query->bindParam(':sensorid', $request[0]);
			$query->bindParam(':state', $queryState);
			switch($request[1])
			{
				case 'active':
					$queryState = 1;
				break;
				case 'deactive':
					$queryState = -1;
				break;
				case 'malfunctioned':
					$queryState = 0;
				break;
			}
			$query->execute(); */
			header('HTTP/1.1 501 Not Supported');
		break;
		case 'POST': // add new row
			// decode data from request
			$data = json_decode($_REQUEST["data"], TRUE);
			if ($data === null && json_last_error() !== JSON_ERROR_NONE) // check if there is a result and there has been an error
			{
				header('HTTP/1.1 400 Bad Request');
			}
			
			if($request[0] == "sensor")
			{
				$query = $db->prepare('INSERT INTO `location`(`sensorID`, `floor`, `located`, `active`, `type`) VALUES (:sensorid,:floor,:located,:active,:type)'); 
				$query->bindParam(':sensorid', $querySensorID);
				$query->bindParam(':floor', $queryFloor);
				$query->bindParam(':located', $queryLocated);
				$query->bindParam(':active', $queryActive);
				$query->bindParam(':type', $queryType);

				foreach($data as $dArray)
				{
					$querySensorID 	= $dArray["sensorID"];
					$queryFloor 	= $dArray["floor"];
					$queryLocated 	= $dArray["located"];
					$queryActive	= $dArray["active"];
					$queryType 		= $dArray["type"];
					
					try{$query->execute();}
					catch(PDOException $e)
					{
						header('HTTP/1.1 500 Error while executing query');
						header('Content-Type: application/json; charset=UTF-8');
						echo $e->getMessage();
						die();
					}
				}
			}
			elseif($request[0] == "data")
			{				
				foreach($data as $key => $keyArray)
				{
					$query = $db->prepare('INSERT INTO '.$key.'(`sensorID`, `timestamp`, `value`) VALUES (:sensorid,:timestamp,:value)');
					$query->bindParam(':sensorid', $querySensorID);
					$query->bindParam(':timestamp', $queryTimestamp);
					$query->bindParam(':value', $queryValue);
					
					foreach($keyArray as $dArray)
					{
						$querySensorID 	= $dArray["sensorID"];
						$queryTimestamp = $dArray["timestamp"];
						$queryValue 	= $dArray["value"];
						echo "INSERT INTO $key(`sensorID`, `timestamp`, `value`) VALUES ($querySensorID,'$queryTimestamp',$queryValue)";
						$query->execute();
					}
				}
			}
			elseif($request[0] == "status")
			{
				$query = $db->prepare("UPDATE `location` SET `active`=:state WHERE `sensorID`=:sensorid"); 
				$query->bindParam(':sensorid', $request[1]);
				$query->bindParam(':state', $queryState);
				switch($request[2])
				{
					case 'active':
						$queryState = 1;
					break;
					case 'deactive':
						$queryState = -1;
					break;
					case 'malfunctioned':
						$queryState = 0;
						mail('afalkengren@gmail.com', 
							/*Header*/ "WARNING: Sensor $request[1] has failed", 
							/*Body*/   "Dear $username, \r\nThe sensor with ID $request[1] has failed, and requires attention.",
							/*From*/   'From: noreply@smartlandlords.co.uk');
					break;
				}
				$query->execute();
			}			
		break;
	}
	exit();
}
else
{
	header('HTTP/1.1 511 AUTHENTICATION FAILED');
	header('Content-Type: application/json; charset=UTF-8');
	echo "stuff dont work mate";
	exit();
}