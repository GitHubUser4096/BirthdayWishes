<?php
/*
 * Projekt: Narozeninová přání
 * Vytvořil: Michal
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

session_start();

if(!isSet($_SERVER['HTTPS'])){
	header("Location: https://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);
	exit;
}

require_once('php/db.php');
require_once('php/mail.php');

require_once('lib/phpMailer/src/Exception.php');
require_once('lib/phpMailer/src/PHPMailer.php');
require_once('lib/phpMailer/src/SMTP.php');

$db = DB_CONNECT();

if(!isSet($_SESSION['user'])){
	die("401 - Unauthorized");
}

// $token = $_SESSION['user']['id'].'_'.uniqid();
$token = random_bytes(9); // start with 9 random bytes (aligns to 12 base64 characters) ('cryptographically secure' according to php manual)
$token = base64_encode($token); // encode it to base64 to make it human-readable
// slash and plus (possible base64 values) can mess with URLs, change them to something else
// since we don't need to decode it back, we can choose anything
$token = str_replace('/', 'A', $token);
$token = str_replace('+', 'B', $token);

$page = $_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
$page = substr($page, 0, strrpos($page, '/'));

$valid_until = date_format(date_add(date_create(), date_interval_create_from_date_string("10 minutes")), 'Y-m-d H:i:s');

$stmt = $db->prepare("insert into VerifyRequests(token, userId, valid_until) values (?, ?, ?)");
$stmt->bind_param("sis", $token, $_SESSION['user']['id'], $valid_until);
$stmt->execute();
$stmt->close();

$link = 'https://'.$page.'/verify.php?token='.$token;
if(isSet($_GET['page'])) $link .= '&page='.urlencode($_GET['page']);

$mail = new PHPMailer(true);

$mail->CharSet = "UTF-8";
$mail->isSmtp();
$mail->Host = MAIL_HOST;
$mail->SMTPAuth = true;
$mail->Username = MAIL_USERNAME;
$mail->Password = MAIL_PASSWORD;
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port = 12345;

$mail->setFrom(MAIL_FROM, 'Narozeninová přání');
$mail->addAddress($_SESSION['user']['email']);

$mail->isHtml(true);
$mail->Subject = "Ověření účtu";
$mail->Body = 'Pro ověření účtu klikněte zde: '.$link;

$mail->send();

$resendLink = 'request_verify.php?';
if(isSet($_GET['page'])) $resendLink .= 'page='.urlencode($_GET['page']);

$info = 'Všechny možnosti budou dostupné až po ověření účtu. Účet ověříte kliknutím na odkaz, který bude zaslán na váš e-mail. (Odeslání e-mailu může trvat několik minut. <a class="link" href="'.$resendLink.'">Odeslat znovu</a>)';

?>
<!doctype html>
<html lang="cs">

	<head>

		<title>Vytvořit účet</title>

		<meta name="viewport" content="width=device-width, initial-scale=1.0">

		<link rel="icon" href="res/cake.png">
		<link rel="stylesheet" href="css/page.css">
		<link rel="stylesheet" href="css/controls.css">
		<link rel="stylesheet" href="css/titlebar.css">
		<script src="js/titlebar.js"></script>

		<link rel="stylesheet" href="css/form_page.css">
		<link rel="stylesheet" href="css/form.css">

		<style>

			.info {
				padding: 10px;
				background: #2edc15;
				font-weight: bold;
				font-size: 18px;
				color: white;
			}

			.link {
				color: white;
			}

		</style>

	</head>

    <body>

		<?php include('php/titlebar.php'); ?>

		<div class="content">

			<div class="subtitlebar">
				<div class="backbtn"><a href="index.php"><</a></div><div class="subtitle">Vytvořit účet</div>
			</div>

			<div class="form">

				<?php
					if(isSet($info)) {
						?><div class="info"><?php
							echo $info;
						?></div><?php
					}
				?>

			</div>

		</div>

    </body>

</html>
<?php
$db->close();
?>
