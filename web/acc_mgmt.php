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

if(!isSet($_SESSION['user'])){
	header('Location: login.php?page=acc_mgmt.php');
	//die("401 - Unauthorized");
}

if($_SERVER['REQUEST_METHOD']==='POST'){
	
	if(isSet($_POST['change_pass'])){
		
		$password = $_POST['password'];
		$verifyPass = $_POST['verify_password'];
		
		if($password==""){
			$error = "Prosím zadejte heslo!";
		} else if($password!=$verifyPass){
			$error = "Hesla se neshodují!";
		} else {
			
			$hashedPass = password_hash($_POST['password'], PASSWORD_DEFAULT);
			
			$stmt = $db->prepare('update User set password=? where id=?');
			$stmt->bind_param("si", $hashedPass, $_SESSION['user']['id']);
			$stmt->execute();
			$stmt->close();
			
			$info = "Heslo úspěšně změněno!";
			
		}
		
	} else if(isSet($_POST['change_mail'])){
		
		/*$mail = htmlspecialchars($_POST['mail']);
		
		if($mail==""){
			$error = "Prosím zadejte e-mail!";
		} else if(strlen($mail)>63) {
			$error = "E-Mail nesmí být delší než 63 znaků!";
		} else {
			
			$stmt = $db->prepare('update User set email=? where id=?');
			$stmt->bind_param("si", $mail, $_SESSION['user']['id']);
			$stmt->execute();
			$stmt->close();
			
			$_SESSION['user']['email'] = $mail;
			
			$info = "E-Mail úspěšně změněn!";
			
		}*/
		
	} else if(isSet($_POST['confirmDelete'])){
		
		$stmt = $db->prepare('delete from User where id=?');
		$stmt->bind_param("i", $_SESSION['user']['id']);
		$stmt->execute();
		$stmt->close();
		
		header('Location: logout.php');
		
	}
	
}

?>
<!doctype html>
<html>

	<head>
		
		<title>Spravovat účet</title>
		
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
			
			.deletebtn {
				background: red;
			}
			
			.deletebtn:hover {
				background: #F55;
			}
			
			.blackout {
				position: fixed;
				left: 0px;
				top: 0px;
				width: 100%;
				height: 100%;
				background: #00000055;
			}
			
			.confirmdialog {
				background: gray;
				position: fixed;
				width: 500px;
				height: 300px;
				left: calc(50% - 250px);
				top: calc(50% - 150px);
				padding: 10px;
			}
			
			@media only screen and (max-width: 600px) {
				.confirmdialog {
					width: 100%;
					height: 50%;
					left: 0;
					top: 25%;
				}
			}
			
			.dialogtitle {
				color: white;
				font-size: 28px;
			}
			
			.dialoginfo {
				color: white;
				font-weight: bold;
				font-size: 32px;
			}
			
			.btnwrapper {
				padding: 10px;
			}
			
			.confirmbtn {
				text-align: center;
				width: 200px;
				background: red;
				color: white;
				border: none;
				padding: 10px;
				font-size: 18px;
			}
			
			.cancelbtn {
				text-align: center;
				width: 200px;
				background: lightgray;
				color: black;
				border: none;
				padding: 10px;
				font-size: 18px;
			}
			
			.check {
				width: 24px;
				height: 24px;
			}
			
		</style>
		
	</head>

    <body>
		
		<?php include('php/titlebar.php'); ?>
		
		<div class="content">
			
			<div class="subtitlebar">
				<div class="backbtn"><a href="index.php"><</a></div><div class="subtitle">Spravovat účet</div>
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
							<input class="formin" value="<?php echo $_SESSION['user']['username']; ?>" type="text" disabled></input>
						</div>
						
						<div class="formrow">
							<span class="formlbl">Ověřený účet:</span>
							<input class="check" type="checkbox" <?php if($_SESSION['user']['verified']) echo 'checked'; ?> disabled></input>
							<?php if(!$_SESSION['user']['verified']) { ?>
								<a href="request_verify.php"><button type="button" class="bigbutton">Ověřit</button></a>
							<?php } ?>
						</div>
						
						<!--div class="formrow">
							<span class="formlbl"><b>Změnit e-mail:</b></span>
						</div-->
						<div class="formrow">
							<span class="formlbl">E-Mail:</span>
							<input class="formin" name="mail" type="email" value="<?php
								$stmt = $db->prepare('select email from User where username=?');
								$stmt->bind_param("s", $_SESSION['user']['username']);
								$stmt->execute();
								$res = $stmt->get_result();
								$stmt->close();
								$row = $res->fetch_assoc();
								echo $row['email'];
							?>" readonly></input>
						</div>
						<!--div class="formrow">
							<input class="bigbutton" type="submit" name="change_mail" value="Změnit e-mail">
						</div-->
						
						<div class="formrow">
							<span class="formlbl"><b>Změnit heslo:</b></span>
						</div>
						<div class="formrow">
							<span class="formlbl">Heslo:</span>
							<input class="formin" name="password" type="password"></input>
						</div>
						<div class="formrow">
							<span class="formlbl">Potvrdit heslo:</span>
							<input class="formin" name="verify_password" type="password"></input>
						</div>
						<div class="formrow">
							<input class="bigbutton" type="submit" name="change_pass" value="Změnit heslo">
						</div>
						<!--div class="formrow">
							<input class="bigbutton deletebtn" type="submit" name="delete" value="Smazat účet">
						</div-->
						
					</div>
					
					<?php
						
						if(isSet($_POST['delete'])){
							?>
							
							<div class="blackout"></div>
							<div class="confirmdialog">
								<div class="dialogtitle">Odstranit účet</div>
								<div class="dialoginfo">Opravdu chcete odstranit účet?</div>
								<div class="btnwrapper"><input class="confirmbtn" type="submit" name="confirmDelete" value="Potvrdit"></input></div>
								<div class="btnwrapper"><input class="cancelbtn" type="submit" name="cancelDelete" value="Zrušit"></input></div>
							</div>
							
							<?php
						}
						
					?>
					
				</form>
				
			</div>
			
		</div>
		
    </body>
	
</html>
<?php
$db->close();
?>