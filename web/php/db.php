<?php

$conf = file_get_contents('db.conf', true);
$rows = explode("\n", $conf);
foreach($rows as $row){
	$parts = explode(':', trim($row));
	define(trim($parts[0]), trim($parts[1]));
}

function DB_CONNECT() {
	
	$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
	
	if($conn->connect_error) die($conn->connect_error);
	
	return $conn;
	
}

?>