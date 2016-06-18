<?php
$username = $_POST['user'];
$password = $_POST['pass'];
$config = parse_ini_file('../../scripts/dbconfig.ini'); 
try{
	$db = new PDO("mysql:host=".$config['servername'].";dbname=".$config['dbname'].";charset=utf8mb4", $config['username'], $config['password']);
}
catch(PDOException $e)
{
	header('HTTP/1.1 500 Could not connect to db');
	header('Content-Type: application/json; charset=UTF-8');
	echo $e->getMessage();
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
	//echo $user->db;
	echo "login success";
	session_start();
	$_SESSION['user'] = $username;
	//$_SESSION['pass'] = $user->hash;
	$_SESSION['db']	  = $user->db;
	//echo htmlspecialchars(SID); //echo incase we want non-cookie authentication- but less secure
}
else
{
	header('HTTP/1.1 500 AUTHENTICATION FAILED');
	header('Content-Type: application/json; charset=UTF-8');
	echo "stuff dont work mate";
}