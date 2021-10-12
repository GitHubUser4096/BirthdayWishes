<?php
/*
 * Projekt: Narozeninová přání
 * Vytvořil: Michal
 */
session_start();

$_SESSION = [];

session_destroy();

header('Location: index.php');

if(!isSet($_SERVER['HTTPS'])){
	header("Location: https://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);
}

?>
<!doctype html>
<html>

	<head>
		
		<title>Odhlásit se</title>
		
	</head>

    <body>
		
		<br><a href="index.php">Zpět</a>
		
    </body>
	
</html>
