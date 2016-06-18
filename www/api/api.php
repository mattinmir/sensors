<?php
// ===================================================================================================
// ======================================== PROCESSING INPUTS ========================================
// ===================================================================================================

$encryption_enable = false;

// Check if we are using the RSA encryption
if($encryption_enable)
{
	// Get the RSA private key stored on the server
	if (!$privateKey = openssl_pkey_get_private("file://../scripts/key.pem")) 
		exit('Loading Private Key failed');
	
	// Now use it to decrypt the RSA-encrypted AES key
	if (!openssl_private_decrypt(base64_decode($_REQUEST["key"]), $aesKey, $privateKey)) // The encrypted AES key is encoded in base64
		exit('Failed to decrypt data');
	
	// Use the decrypted AES key to decrypt the payload
	$decryptData = openssl_decrypt($_REQUEST["data"], "AES-256-CBC", $aesKey);
	if (!decryptData);
		exit('Failed to decrypt data');
	
	// Now that the payload is decrypted, split the payload into the authorization details and the input data
	$decryptData = json_decode($decryptData, TRUE);
	if ($decryptData === null && json_last_error() !== JSON_ERROR_NONE)	// Check for formatting errors
	{
		header('HTTP/1.1 400 Bad Request');
		echo "Incorrectly formatted data.";
		exit();
	}
	
	$auth = base64_decode($decryptData["auth"]);	// Extract 
	$data = json_decode($decryptData["input"], TRUE);
}
else
{
	$auth = $_REQUEST["auth"];
	$data = json_decode($_REQUEST["input"], TRUE);
}

// Check if JSON is valid, otherwise throw error
if ($data === null && json_last_error() !== JSON_ERROR_NONE) // check if there is a result AND if there has been an error
{
	header('HTTP/1.1 400 Bad Request');
	echo "Incorrectly formatted data.";
	exit();
}

// The authentication datum is in the form <username>:<password> so split on the colon
$auth_exploded = explode(":", $auth);

// Small check if it was in this format or not, otherwise throw error
if (count($auth_exploded) > 2)
{
	header('HTTP/1.1 511 AUTHENTICATION FAILED');
	header('Content-Type: application/json; charset=UTF-8');
	echo "Incorrectly formatted data.";
	exit();
}

// Now assign to permanent variables
$username = $auth_exploded[0];
$password = $auth_exploded[1];

// ===================================================================================================
// ======================================== AUTHENTICATION REQUEST ===================================
// ===================================================================================================

// Get database account details from external file for security
$config = parse_ini_file('../scripts/dbconfig.ini'); 
try
{
	// create a new database PDO object
	$db = new PDO("mysql:host=".$config['servername'].";dbname=".$config['dbname'].";charset=utf8mb4", $config['username'], $config['password']);
}
catch(PDOException $e) // catch any errors
{
	header('HTTP/1.1 500 Could not connect to password database');
	header('Content-Type: application/json; charset=UTF-8');
	echo $e->getMessage();
	die();
}
$query = $db->prepare('SELECT * FROM users WHERE username = :username LIMIT 1'); // prepare a SQL query

//:username is a placeholder replaced by our username variable
$query->bindParam(':username', $username);

// Now execute the query and fetch the data
$query->execute();
$user = $query->fetch(PDO::FETCH_OBJ);

// ===================================================================================================
// ======================================== PROCESS THE QUERY ========================================
// ===================================================================================================
// Hashing the password with its hash as the salt returns the same hash, meaning password is correct
if ( hash_equals($user->hash, crypt($password, $user->hash)) )
{
	// Processing database login credentials
	$userdb = $user->db;
	$server = $userdb.".db.1and1.com";
	$dbname = $userdb;
	$user = "dbo" . substr($userdb, 2);
	$dbpw = "building";	// Test password

	$db = null;
	try
	{
		// Create a DB object and connect to the sql DB
		$db = new PDO("mysql:host=".$server.";dbname=".$userdb.";charset=utf8mb4", $user, $dbpw);
	}
	catch(PDOException $e)
	{
		header('HTTP/1.1 500 Could not connect to content db');
		header('Content-Type: application/json; charset=UTF-8');
		echo $e->getMessage();
		die();
	}
	
	// DEBUG: Print any errors from the SQL database
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
	
	// Get REST request. /AAAA/BBBB/ will become array["AAAA", "BBBB"]
	$pathinfo = empty($_SERVER['PATH_INFO']) ? $_SERVER['ORIG_PATH_INFO'] : $_SERVER['PATH_INFO'];
	$request = explode('/', trim($pathinfo,'/'));
	
	// Depending on the HTTP method, change how it is applied
	switch ($_SERVER['REQUEST_METHOD'])
	{
		// GET: Get data
		// NOTE: Currently functionality lies in POST due to safety concerns
		case 'GET': 
			header('HTTP/1.1 501 Not Supported');
		break;
		
		// PUT: Update data
		// NOTE: Currently functionality lies in POST due to safety concerns
		case 'PUT': 
			header('HTTP/1.1 501 Not Supported');
		break;
		
		// POST: Set new data
		// NOTE: POST currently handles all requests, due to security concerns
		// TODO: Move functionality
		case 'POST': 
			switch($request[0])
			{
				case "sensor":
					addSensor($db, $data);
				break;
				case "data":
					setData($db, $data);
				break;
				case "update":
					updateSensor($db, $request);
				break
				case "getdata":
					getData($db, $request[1]);
				break;
			}			
		break;
		
		// Invalid input
		default:
			header('HTTP/1.1 501 Not Supported');
		break;
	}
	exit();
}
else	// If hash check fails, push error
{
	header('HTTP/1.1 511 AUTHENTICATION FAILED');
	header('Content-Type: application/json; charset=UTF-8');
	exit();
}

// =====================================================
//		Request Functions
// =====================================================
function addSensor($db, $data)
{
	$query = $db->prepare('INSERT INTO `nodes`(`deviceID`, `floor`, `located`, `status`, `type`, `trans_connections`) VALUES (:deviceid,:floor,:located,:status,:type,:trans_connections)'); 
	$query->bindParam(':deviceid', $queryDeviceID);
	$query->bindParam(':floor', $queryFloor);
	$query->bindParam(':located', $queryLocated);
	$query->bindParam(':status', $queryStatus);
	$query->bindParam(':type', $queryType);
	$query->bindParam(':trans_connections', $queryTransConnections);

	foreach($data as $dArray)
	{
		$queryDeviceID 	= $dArray["deviceID"];
		$queryFloor 	= $dArray["floor"];
		$queryLocated 	= $dArray["located"];
		$queryStatus	= $dArray["status"];
		$queryType 		= $dArray["type"];
		$queryTransConnections = $dArray["trans_connections"];
		
		try
		{
			$query->execute();
		}
		catch(PDOException $e)
		{
			header('HTTP/1.1 500 Error while executing query');
			header('Content-Type: application/json; charset=UTF-8');
			echo $e->getMessage();
			die();
		}
	}
}

function updateSensor($db, $request)
{
	$query = $db->prepare("UPDATE `nodes` SET `$request[2]`=:cdata WHERE `deviceID`=:deviceid"); 
	$query->bindParam(':deviceid', $request[1]);
	$query->bindParam(':cdata', $request[3]);
	
	try
	{
		$query->execute();
	}
	catch(PDOException $e)
	{
		header('HTTP/1.1 500 Error while executing query');
		header('Content-Type: application/json; charset=UTF-8');
		echo $e->getMessage();
		exit();
	}
}

function setData($db, $data)
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
			
			try
			{
				$query->execute();
			}
			catch(PDOException $e)
			{
				header('HTTP/1.1 500 Error while executing query');
				header('Content-Type: application/json; charset=UTF-8');
				echo $e->getMessage();
				exit();
			}
		}
	}
}

function getData($db, $type)
{
	if($type == "transceivers")
		$query = $db->prepare("SELECT * FROM nodes WHERE type='transceiver'"); 
	elseif($type == "sensors")
		$query = $db->prepare("SELECT * FROM nodes WHERE type!='transceiver'"); 
	else
		$query = $db->prepare("SELECT * FROM nodes"); 
	
	try
	{
		$query->execute();
	}
	catch(PDOException $e)
	{
		header('HTTP/1.1 500 Error while executing query');
		header('Content-Type: application/json; charset=UTF-8');
		echo $e->getMessage();
		exit();
	}
	$result = $query->fetchAll();
	foreach($result as $row)
	{
		
		$return[] = array("sensorID" => $row['deviceID'], "floor" => (int)$row['floor'], "location" => $row['located'], 
		"status" => $row['status'], "type" => $row['type']);
	}
	echo json_encode($return);

}