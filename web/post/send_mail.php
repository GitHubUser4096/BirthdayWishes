<?php
/*
 * Projekt: Narozeninová přání
 * Vytvořil: Michal
 */

session_start();

if(!isSet($_SERVER['HTTPS'])){
	header("Location: https://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require_once('../php/db.php');
require_once('../php/mail.php');

require_once('../lib/phpMailer/src/Exception.php');
require_once('../lib/phpMailer/src/PHPMailer.php');
require_once('../lib/phpMailer/src/SMTP.php');

$db = DB_CONNECT();

if($_SERVER['REQUEST_METHOD']==='POST') {
	
	$uid = $_POST['uid'];
	$mailAddresses = htmlspecialchars($_POST['mailAddress']);
	$hiddenCopyAddresses = htmlspecialchars($_POST['mailHiddenCopy']);
	
	$addresses = [];
	$bccs = [];
	
	foreach(explode('\n', $mailAddresses) as $address){
		if(strlen(trim($address))>0) $addresses[] = $address;
	}
	
	foreach(explode('\n', $hiddenCopyAddresses) as $address){
		if(strlen(trim($address))>0) $bccs[] = $address;
	}
	
	$mail = new PHPMailer(true);
	
	$mail->CharSet = "UTF-8";
	//$mail->SMTPDebug = SMTP::DEBUG_SERVER;
	$mail->isSmtp();
	$mail->Host = MAIL_HOST;
	$mail->SMTPAuth = true;
	$mail->Username = MAIL_USERNAME;
	$mail->Password = MAIL_PASSWORD;
	$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
	$mail->Port = 12345;
	
	$mail->setFrom(MAIL_FROM, 'Narozeninová přání');
	
	//$mail->addAddress($row['email']);
	
	foreach($addresses as $address){
		$mail->addAddress($address);
	}
	
	foreach($bccs as $address){
		$mail->addBCC($address);
	}
	
	//$mail->addAttachment('generated/'.$row['document'].'.pdf', 'Přání.pdf');
	$mail->addAttachment('../generated/pdf/'.$uid.'.pdf', 'Přání.pdf');
	
	$mail->isHtml(true);
	$mail->Subject = "Všechno nejlepší k narozeninám!";
	//$mail->Body = htmlspecialchars($row['sent_by']).' ti přeje všechno nejlepší k narozeninám!';
	$mail->Body = 'Všechno nejlepší k narozeninám!';
	
	$mail->send();
	
	$stmt = $db->prepare('update Wish set mail_sent=true where uid=?');
	$stmt->bind_param('s', $uid);
	$stmt->execute();
	$stmt->close();
	
}

?>