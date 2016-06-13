<?php
session_start();

session_unset(); // clear the $_SESSION variable

if(isset($_COOKIE[session_name()])) {
    setcookie(session_name(),'',time()-3600); # Unset the session id
}
session_destroy(); // finally destroy the session

header( "refresh:2; url='../index.php'" ); 
echo "Successfully logged off...Redirecting you";
exit;
