<?php
session_start();

if(!isSet($_SERVER['HTTPS'])){
	header("Location: https://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);
}

require_once('php/db.php');

$db = DB_CONNECT();

if(!isSet($_SESSION['user'])||!$_SESSION['user']['verified']){
	header('Location: login.php?page=schedule_send.php');
}

if($_SERVER['REQUEST_METHOD']==='POST'){
	
	$mail = htmlspecialchars($_POST['mail']);
	$date = htmlspecialchars($_POST['date']);
	
	if($mail=="") {
		$error = "Prosím vyplňte e-mail'";
	} else if(date_create($date)<date_create()||date_create($date)>date_add(date_create(), date_interval_create_from_date_string("365 days"))) {
		$error = "Neplatné detum!";
	} else {
		
		$stmt = $db->prepare('insert into Scheduled(sent_by, email, date, document) values (?, ?, ?, ?)');
		$stmt->bind_param("ssss", $_SESSION['wish']['from'], $mail, $date, $_SESSION['docname']);
		$stmt->execute();
		$stmt->close();
		
		$info = "Přání bude odesláno!";
		
	}
	
}

?>
<!doctype html>
<html>

	<head>

		<title>Odeslat</title>
		
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
				<div class="backbtn"><a href="wish.php"><</a></div><div class="subtitle">Odeslat na e-mail</div>
			</div>
			
			<div class="form">
				
				<form method="POST" enctype="multipart/form-data">
					
					<div class="fullwidcol">
						
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
						
						<div class="formrow">
							<span class="formlbl">E-Mail:</span>
							<input class="formin" type="email" name="mail" value="<?php if($_SERVER['REQUEST_METHOD']==='POST') echo $_POST['mail'] ?>"></input>
						</div>
						<div class="formrow">
							<span class="formlbl">Datum:</span>
							<input class="formin" type="date" name="date" value="<?php if($_SERVER['REQUEST_METHOD']==='POST') echo $_POST['date'] ?>"></input>
						</div>
						
						<div class="formrow"><input class="bigbutton" value="Uložit" type="submit"></input></div>
						
					</div>
					
				</form>
				
			</div>
			
		</div>
		
    </body>

</html>
<?php
$db->close();
?>