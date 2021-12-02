<?php
/*
 * Projekt: Narozeninová přání
 * Vytvořil: Michal
 */
session_start();

$_SESSION = [];

session_regenerate_id();
session_destroy();

header('Location: index.php');
exit;

?>
<!doctype html>
<html lang="cs">

	<head>

		<title>Odhlásit se</title>

	</head>

    <body>

		<br><a href="index.php">Zpět</a>

    </body>

</html>
