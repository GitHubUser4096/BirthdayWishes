<?php
/*
 * Připojení k databázi
 * Popis: funkce pro připojení k databázi
 * Vytvořil: Michal
 */

require_once 'db.conf.php';

function DB_CONNECT() {
	
	$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
	
	if($conn->connect_error) die($conn->connect_error);
	
	return $conn;
	
}

?>