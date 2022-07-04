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

if(!isSet($_SESSION['user'])){
	die('Forbidden');
}

if(!$_SESSION['user']['verified']){
	die('Forbidden');
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
	$sign = intval($_POST['signMail']);

	if(strlen($mailAddresses)>100||strlen($hiddenCopyAddresses)>100) {
		die('400 - Bad request');
	}

	$stmt = $db->prepare('select * from Wish where uid=?');
	$stmt->bind_param('s', $uid);
	$stmt->execute();
	$res = $stmt->get_result();
	$stmt->close();

	$row = $res->fetch_assoc();

	if($row && !$row['mail_sent']){

		if($row['userId']!=$_SESSION['user']['id']){
			die('Forbidden');
		}

		$addresses = [];
		$bccs = [];

		foreach(explode("\n", $mailAddresses) as $address){
			if(strlen(trim($address))>0) $addresses[] = trim($address);
		}

		foreach(explode("\n", $hiddenCopyAddresses) as $address){
			if(strlen(trim($address))>0) $bccs[] = trim($address);
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
		//$mail->Body = 'Všechno nejlepší k narozeninám!';
		if($sign){
			$mail->Body = $row['preview_text'].'<br>Přání vytvořil <a href="mailto:'.$_SESSION['user']['email'].'">'.$_SESSION['user']['email'].'</a>';
		} else {
			$mail->Body = $row['preview_text'];
		}

		$mail->send();

		$stmt = $db->prepare('update Wish set mail_sent=true where uid=?');
		$stmt->bind_param('s', $uid);
		$stmt->execute();
		$stmt->close();

	}

}

?>
