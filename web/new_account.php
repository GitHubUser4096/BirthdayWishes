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

if($_SERVER['REQUEST_METHOD']==='POST'){
	
	$username = htmlspecialchars($_POST['username']);
	$mail = htmlspecialchars($_POST['mail']);
	$password = $_POST['password'];
	$verifyPass = $_POST['verify_password'];
	
	if($username==""){
		$error = "Prosím zadejte uživatelské jméno!";
	} else if($mail==""){
		$error = "Prosím zadejte e-mail!";
	} else if(!preg_match("/^[A-Za-z0-9\.\_\-]+@[A-Za-z0-9\_\-]+\.[A-Za-z0-9]+$/", $mail)){
		$error = "Neplatný e-mail!";
	} else if(strlen($username)>30) {
		$error = "Uživatelské jméno nesmí být delší než 30 znaků!";
	} else if(strlen($mail)>63) {
		$error = "E-Mail nesmí být delší než 63 znaků!";
	} else if($password==""){
		$error = "Prosím zadejte heslo!";
	} else if($password!=$verifyPass){
		$error = "Hesla se neshodují!";
	} else {
		
		$stmt = $db->prepare("select * from User where username=?");
		$stmt->bind_param("s", $username);
		$stmt->execute();
		$res = $stmt->get_result();
		$stmt->close();
		
		if($res->num_rows>0) {
			$error = "Uživatelské jméno již existuje!";
		} else {
			
			$hashedPass = password_hash($_POST['password'], PASSWORD_DEFAULT);
			
			$stmt = $db->prepare('insert into User(username, password, email) values (?, ?, ?)');
			$stmt->bind_param("sss", $username, $hashedPass, $mail);
			$stmt->execute();
			$res = $stmt->get_result();
			$stmt->close();
			
			$id = $db->insert_id;
			
			$user = [];
			$user['id'] = $id;
			$user['username'] = $username;
			$user['email'] = $mail;
			$user['admin'] = false;
			$user['verified'] = false;
			
			$_SESSION['user'] = $user;
			
			header('Location: request_verify.php?'.(isSet($_GET['page'])?('page='.urlencode($_GET['page'])):''));
			
			//$page = isSet($_GET['page']) ? $_GET['page'] : 'index.php';
			//header("Location: ".$page);
			
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
			
			.createbtn {
				margin-left: 25%;
				width: 50%;
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
				
				<form method="post">
					
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
					
					<div class="midcol">
						
						<div class="formrow">
							<span class="formlbl">Uživatelské jméno:</span>
							<input class="formin" name="username" type="text" value="<?php if($_SERVER['REQUEST_METHOD']==='POST') echo $_POST['username'] ?>"></input>
						</div>
						<div class="formrow">
							<span class="formlbl">E-Mail:</span>
							<input class="formin" name="mail" type="email" value="<?php if($_SERVER['REQUEST_METHOD']==='POST') echo $_POST['mail'] ?>"></input>
						</div>
						<div class="formrow">
							<span class="formlbl">Heslo:</span>
							<input class="formin" name="password" type="password"></input>
						</div>
						<div class="formrow">
							<span class="formlbl">Potvrdit heslo:</span>
							<input class="formin" name="verify_password" type="password"></input>
						</div>
						<div class="formrow"><input class="bigbutton createbtn" value="Vytvořit" type="submit"></input></div>
						
					</div>
					
				</form>
				
			</div>
			
		</div>
		
    </body>
	
</html>
<?php
$db->close();
?>