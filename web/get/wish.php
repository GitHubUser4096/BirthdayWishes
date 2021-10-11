<?php
/*
 * Projekt: Narozeninová přání
 * Vytvořil: Michal
 */

session_start();

if(!isSet($_SERVER['HTTPS'])){
	header("Location: https://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);
}

require_once('../php/db.php');

$db = DB_CONNECT();

$uid = $_GET['uid'];

$stmt = $db->prepare("select mail_address, mail_hidden, mail_date, mail_sent from Wish where uid=?");
$stmt->bind_param("s", $uid);
$stmt->execute();
$res = $stmt->get_result();
$stmt->close();

$row = $res->fetch_assoc();

echo '{"document":"generated/pdf/'.$uid.'.pdf", "mail_address":"'.$row['mail_address'].'", "mail_hidden":"'.$row['mail_hidden'].'", "mail_date":"'.$row['mail_date'].'", "mail_sent":"'.$row['mail_sent'].'"}';

?>