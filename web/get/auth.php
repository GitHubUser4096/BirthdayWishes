<?php
/*
 * Projekt: Narozeninová přání
 * Vytvořil: Michal
 */

session_start();

if(!isSet($_SERVER['HTTPS'])){
	header("Location: https://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);
	exit;
}

if(!isSet($_SESSION['user'])) echo 'false';
// else echo '{"id":"'.$_SESSION['user']['id'].'", "username":"'.$_SESSION['user']['username'].'", "verified":"'.$_SESSION['user']['verified'].'"}';
else echo json_encode([
	'id'=>$_SESSION['user']['id'],
	'username'=>$_SESSION['user']['username'],
	'email'=>$_SESSION['user']['email'],
	'verified'=>$_SESSION['user']['verified']
]);

?>
