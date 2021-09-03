<?php
session_start();

$_SESSION = [];

session_destroy();

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
