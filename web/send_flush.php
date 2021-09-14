<?php
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

$stmt = $db->prepare('select * from Scheduled');
$stmt->execute();
$res = $stmt->get_result();
$stmt->close();

echo "Sending...<br>";

while($row = $res->fetch_assoc()) {
	
	if($row['date']==date("Y-m-d")) {
		
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
		$mail->addAddress($row['email']);
		
		$mail->addAttachment('generated/'.$row['document'].'.pdf', 'Přání.pdf');
		
		$mail->isHtml(true);
		$mail->Subject = "Všechno nejlepší k narozeninám!";
		$mail->Body = htmlspecialchars($row['sent_by']).' ti přeje všechno nejlepší k narozeninám!';
		
		$mail->send();
		
		$stmt = $db->prepare('delete from Scheduled where id=?');
		$stmt->bind_param("i", $row['id']);
		$stmt->execute();
		$stmt->close();
		
		echo "Sent to ".$row['email']."<br>";
		
	}
	
}

$db->close();

?>