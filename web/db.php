<?php

define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', 'root');

function DB_CONNECT($db) {
	
	$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, $db);
	
	if($conn->connect_error) die($conn->connect_error);
	
	return $conn;
	
}

?>