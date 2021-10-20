<?php
/*
 * Projekt: Narozeninová přání
 * Vytvořil: Michal
 */

session_start();

if(!isSet($_SERVER['HTTPS'])){
	header("Location: https://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);
}

if(!isSet($_SESSION['user']) || !$_SESSION['user']['verified']){
	die('Forbidden');
}

require_once('../php/db.php');

$db = DB_CONNECT();

if($_SERVER['REQUEST_METHOD']==='POST') {
	
	$uid = $_POST['uid'];
	$mailAddresses = htmlspecialchars($_POST['mailAddress']);
	$hiddenCopyAddresses = htmlspecialchars($_POST['mailHiddenCopy']);
	$date = htmlspecialchars($_POST['date']);
	
	$stmt = $db->prepare("select userId from Wish where uid=?");
	$stmt->bind_param("s", $uid);
	$stmt->execute();
	$res = $stmt->get_result();
	$stmt->close();
	
	$row = $res->fetch_assoc();
	
	if(!$row||$row['userId']!=$_SESSION['user']['id']){
		die('Forbidden');
	}
	
	$stmt = $db->prepare("update Wish set mail_address=?, mail_hidden=?, mail_date=? where uid=?");
	$stmt->bind_param("ssss", $mailAddresses, $hiddenCopyAddresses, $date, $uid);
	$stmt->execute();
	$stmt->close();
	
}

?>