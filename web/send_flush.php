<?php
/*
 * Projekt: Narozeninová přání
 * Vytvořil: Michal
 */
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

session_start();

require_once('php/db.php');
require_once('php/mail.php');

require_once('lib/phpMailer/src/Exception.php');
require_once('lib/phpMailer/src/PHPMailer.php');
require_once('lib/phpMailer/src/SMTP.php');

$db = DB_CONNECT();

$stmt = $db->prepare('select uid, mail_address, mail_hidden, mail_sign, preview_text from Wish where ifnull(mail_sent, 0) = 0 and mail_date=date(now())');
$stmt->execute();
$res = $stmt->get_result();
$stmt->close();

echo "Sending mails...<br>";

$i = 0;

while($row = $res->fetch_assoc()) {

	try {

		$addresses = [];
		$bccs = [];

		foreach(explode("\n", $row['mail_address']) as $address){
			if(strlen(trim($address))>0) $addresses[] = trim($address);
		}

		foreach(explode("\n", $row['mail_hidden']) as $address){
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

		foreach($addresses as $address){
			$mail->addAddress($address);
		}

		foreach($bccs as $address){
			$mail->addBCC($address);
		}

		$mail->addAttachment('generated/pdf/'.$row['uid'].'.pdf', 'Přání.pdf');

		$mail->isHtml(true);
		$mail->Subject = "Všechno nejlepší k narozeninám!";
		if($row['mail_sign']){
			$mail->Body = $row['preview_text'].'<br>Přání vytvořil <a href="mailto:'.$_SESSION['user']['email'].'">'.$_SESSION['user']['email'].'</a>';
		} else {
			$mail->Body = $row['preview_text'];
		}
		// $mail->Body = $row['preview_text'];

		$mail->send();

		$stmt = $db->prepare('update Wish set mail_sent=true where uid=?');
		$stmt->bind_param('s', $row['uid']);
		$stmt->execute();
		$stmt->close();

		$i++;

	} catch(Exception $e) {

		echo 'Failed sending: '.$e.'<br>';

	}

}

echo 'Sent '.$i.' mails';

$db->close();

?>
