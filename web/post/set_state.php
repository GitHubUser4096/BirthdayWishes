<?php

session_start();

if(!isSet($_SERVER['HTTPS'])){
	header("Location: https://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);
	exit;
}

if(!isSet($_SESSION['user']) || !$_SESSION['user']['admin']) {
	header('HTTP/1.1 401 Unauthorized');
	exit;
}

if($_SERVER['REQUEST_METHOD']!=='POST') {
  header('HTTP/1.1 400 Bad request');
  exit;
}

require_once('../php/db.php');

$db = DB_CONNECT();

$id = $_POST['id'];
$state = $_POST['state'];

$stmt = $db->prepare('update NumberInfo set state=? where id=?');
$stmt->bind_param("si", $state, $id);
$stmt->execute();
$stmt->close();

echo 'success';

?>
