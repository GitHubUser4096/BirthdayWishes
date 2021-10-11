<?php
/*
 * Projekt: Narozeninová přání
 * Vytvořil: Michal
 */
session_start();

if(!isSet($_SERVER['HTTPS'])){
	header("Location: https://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);
}

require_once('php/db.php');

$db = DB_CONNECT();

if(!isSet($_GET['token'])) {
	die("401 - Forbidden");
}

if(!isSet($_SESSION['user'])){
	$error = "Nelze ověřit účet. Prosím přihlašte se.";
} else {
	
	$stmt = $db->prepare('select * from VerifyRequests where token=?');
	$stmt->bind_param("s", $_GET['token']);
	$stmt->execute();
	$res = $stmt->get_result();
	$stmt->close();
	$row = $res->fetch_assoc();

	if(!$row){
		$error = 'Učet je již ověřen!';
	} else {
		
		$valid_until = date_create($row['valid_until']);
		
		if($valid_until<date_create()) {
			$error = 'Čas pro ověření účtu vypršel!';
		} else if($_SESSION['user']['id']!=$row['userId']) {
			$error = "Prosím přihlašte se pod účtem, který ověřujete!";
		} else {
			
			$_SESSION['user']['verified'] = true;
			
			$stmt = $db->prepare('update User set verified=true where id=?');
			$stmt->bind_param("i", $_SESSION['user']['id']);
			$stmt->execute();
			$res = $stmt->get_result();
			$stmt->close();
			
			$stmt = $db->prepare('delete from VerifyRequests where token=?');
			$stmt->bind_param("s", $_GET['token']);
			$stmt->execute();
			$stmt->close();
			
			$info = "Váš účet byl úspěšně ověřen.";
			
		}
		
	}
	
}

?>
<!doctype html>
<html>

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
			
		</style>
		
	</head>

    <body>
		
		<?php include('php/titlebar.php'); ?>
		
		<div class="content">
			
			<div class="subtitlebar">
				<div class="backbtn"><a href="login.php"><</a></div><div class="subtitle">Vytvořit účet</div>
			</div>
			
			<div class="form">
					
				<?php
					if(isSet($info)) {
						?><div class="info"><?php
							echo $info;
						?></div><?php
					}
				?>
				
				<?php
					if(isSet($error)) {
						?><div class="error"><?php
							echo $error;
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