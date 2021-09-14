<?php
session_start();

if(!isSet($_SERVER['HTTPS'])){
	header("Location: https://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);
}

require_once('php/db.php');

$db = DB_CONNECT();

if(!isSet($_GET['token'])) {
	die("401 - Forbidden");
}

if($_SERVER['REQUEST_METHOD']==='POST'){
	
	$password = $_POST['password'];
	$verifyPass = $_POST['verify_password'];

	if($password==""){
		$error = "Prosím zadejte heslo!";
	} else if($password!=$verifyPass){
		$error = "Hesla se neshodují!";
	} else {
		
		$hashedPass = password_hash($password, PASSWORD_DEFAULT);
		
		$stmt = $db->prepare('update User set password=? where username=?');
		$stmt->bind_param("ss", $hashedPass, $_POST['username']);
		$stmt->execute();
		$stmt->close();
		
		$info = "Heslo úspěšně obnoveno!";
		
		$stmt = $db->prepare('delete from PassRequests where token=?');
		$stmt->bind_param("s", $_GET['token']);
		$stmt->execute();
		$stmt->close();
		
	}
	
} else {
	
	$stmt = $db->prepare('select * from PassRequests where token=?');
	$stmt->bind_param("s", $_GET['token']);
	$stmt->execute();
	$res = $stmt->get_result();
	$stmt->close();
	$row = $res->fetch_assoc();

	if(!$row){
		$error = 'Heslo již bylo změněno!';
	} else {
		
		$valid_until = date_create($row['valid_until']);
		
		if($valid_until<date_create()) {
			$error = 'Čas pro změnu hesla vypršel!';
		} else {
			
			$username = $row['username'];
			$showForm = true;
			
		}
		
	}
	
}

?>
<!doctype html>
<html>

	<head>
		
		<title>Obnovit heslo</title>
		
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
				<div class="backbtn"><a href="index.php"><</a></div><div class="subtitle">Obnovit heslo</div>
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
					
					<?php
						if(isSet($showForm)&&$showForm) {
							?>
					
							<div class="midcol">
								
								<div class="formrow">
									<span class="formlbl">Uživatelské jméno:</span>
									<input class="formin" name="username" type="text" value="<?php echo $username ?>" readonly></input>
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
									<input class="bigbutton" type="submit" value="Obnovit heslo">
								</div>
								
							</div>
							
							<?php
							
						} else {
							?>
							
							<div class="formrow formlbl link"><a href="login.php">Přihlásit se/Obnovit heslo</a></div>
							
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
?>+