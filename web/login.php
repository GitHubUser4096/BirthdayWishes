<?php
/*
 * Přihlášení
 * Projekt: Narozeninová přání
 * Vytvořil: Michal
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

session_start();

// if(!isSet($_SERVER['HTTPS'])){
// 	header("Location: https://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);
// 	exit;
// }

require_once('php/db.php');
require_once('php/mail.php');

require_once('lib/phpMailer/src/Exception.php');
require_once('lib/phpMailer/src/PHPMailer.php');
require_once('lib/phpMailer/src/SMTP.php');

$db = DB_CONNECT();

if(isSet($_SESSION['user'])){
	header('Location: index.php');
	exit;
}

if($_SERVER['REQUEST_METHOD']==='POST'){

	if(isSet($_POST['forgot_pass'])) {

		$username = htmlspecialchars($_POST['username']);

		if($username==""){
			$error = "Prosím zadejte uživatelské jméno!";
		} else {

			$stmt = $db->prepare("select * from User where username=?");
			$stmt->bind_param("s", $username);
			$stmt->execute();
			$res = $stmt->get_result();
			$stmt->close();

			if($res->num_rows>0) {

				$row = $res->fetch_assoc();

				$address = $row['email'];

				if($address==""){
					$error = "Neplatný e-mail! Kontaktujte administrátora pro obnovení hesla";
				} else {

					// $token = uniqid();
					$token = random_bytes(9); // start with 9 random bytes (aligns to 12 base64 characters) ('cryptographically secure' according to php manual)
					$token = base64_encode($token); // encode it to base64 to make it human-readable
					// slash and plus (possible base64 values) can mess with URLs, change them to something else
					// since we don't need to decode it back, we can choose anything
					$token = str_replace('/', 'A', $token);
					$token = str_replace('+', 'B', $token);

					$page = $_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
					$page = substr($page, 0, strrpos($page, '/'));

					$valid_until = date_format(date_add(date_create(), date_interval_create_from_date_string("10 minutes")), 'Y-m-d H:i:s');

					$stmt = $db->prepare("insert into PassRequests(token, username, valid_until) values (?, ?, ?)");
					$stmt->bind_param("sss", $token, $username, $valid_until);
					$stmt->execute();
					$stmt->close();

					$link = 'https://'.$page.'/reset_pass.php?token='.$token;

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
					$mail->addAddress($address);

					$mail->isHtml(true);
					$mail->Subject = "Obnovení hesla";
					$mail->Body = 'Pro obnovení hesla klikněte zde: '.$link;

					$mail->send();

					$info = "Odkaz pro obnovení hesla byl odeslán na váš e-mail";

				}

			} else {

				$error = "Neplatné uživatelské jméno!";

			}

		}

	} else {

		$username = htmlspecialchars($_POST['username']);
		$password = $_POST['password'];

		if($username==""){
			$error = "Prosím zadejte uživatelské jméno!";
		} else if($password==""){
			$error = "Prosím zadejte heslo!";
		} else {

			$stmt = $db->prepare("select * from User where username=?");
			$stmt->bind_param("s", $username);
			$stmt->execute();
			$res = $stmt->get_result();
			$stmt->close();

			if($res->num_rows>0) {

				$user = $res->fetch_assoc();

				if(!password_verify($password, $user["password"])){
					$error = "Neplatné heslo!";
				} else {

					$_SESSION['user'] = $user;

					$page = isSet($_GET['page']) ? $_GET['page'] : 'index.php';

					header("Location: ".$page);

				}

			} else {

				$error = "Neplatné uživatelské jméno!";

			}

		}

	}

}

if(isSet($_GET['page'])) {
	$info = "Prosím přihlašte se pro pokračování.";
}

?>
<!doctype html>
<html lang="cs">

	<head>

		<title>Přihlásit se</title>

		<meta name="viewport" content="width=device-width, initial-scale=1.0">

		<link rel="icon" href="res/cake.png">
		<link rel="stylesheet" href="css/page.css">
		<link rel="stylesheet" href="css/controls.css">
		<link rel="stylesheet" href="css/titlebar.css">
		<script src="js/titlebar.js"></script>

		<link rel="stylesheet" href="css/form_page.css">
		<link rel="stylesheet" href="css/form.css">

		<style>

			.loginbtn {
				margin-left: 25%;
				width: 50%;
			}

			.cancelbtn {
				margin-left: 25%;
				width: 50%;
				background: gray;
				padding-top: 10px;
				padding-bottom: 10px;
			}

			.cancelbtn:hover {
				background: darkgray;
			}

			.link_submit {
				border: none;
				background: none;
				cursor: pointer;
				text-decoration: underline;
				font-size: 18px;
				padding: 0px;
			}

			.info {
				padding: 10px;
				background: #2edc15;
				font-weight: bold;
				font-size: 18px;
				color: white;
			}

		</style>

	</head>

    <body>

		<?php include('php/titlebar.php'); ?>

		<div class="content">

			<div class="subtitlebar">
				<div class="backbtn"><a href="index.php"><</a></div><div class="subtitle">Přihlásit se</div>
			</div>

			<div class="form">

				<form method="post">

					<?php
						if(isSet($error)) {
							?><div class="error"><?php
								echo $error;
							?></div><?php
						}
					?>

					<?php
						if(isSet($info)) {
							?><div class="info"><?php
								echo $info;
							?></div><?php
						}
					?>

					<div class="midcol">

						<div class="formrow">
							<span class="formlbl">Uživatelské jméno:</span>
							<input class="formin" name="username" type="text" value="<?php if($_SERVER['REQUEST_METHOD']==='POST') echo $_POST['username'] ?>"></input>
						</div>
						<div class="formrow">
							<span class="formlbl">Heslo:</span>
							<input class="formin" name="password" type="password"></input>
						</div>
						<div class="formrow"><input class="bigbutton loginbtn" value="Přihlásit se" type="submit"></input></div>
						<?php if(isSet($_GET['page'])) { ?><div class="formrow"><a href="<?php echo $_GET['page'] ?>"><input class="bigbutton cancelbtn" value="Zrušit" type="button"></input></a></div><?php } ?>
						<div class="formrow"><div class="formlbl link"><input style="font-size: 20px;" class="link_submit" type="submit" name="forgot_pass" value="Zapomenuté heslo"></input></div></div>
						<div class="formrow"><div class="formlbl link"><a href="new_account.php?<?php if(isSet($_GET['page'])) echo 'page='.urlencode($_GET['page']); ?>">Vytvořit nový účet</a></div></div>

					</div>

				</form>

			</div>

		</div>

    </body>

</html>
<?php
$db->close();
?>
