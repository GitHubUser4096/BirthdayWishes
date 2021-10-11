<?php
/*
 * Projekt: Narozeninová přání
 * Vytvořil: Michal
 */
session_start();

$_SESSION = [];

session_destroy();

if(!isSet($_SERVER['HTTPS'])){
	header("Location: https://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);
}

header('Location: index.php');

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
