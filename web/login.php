<?php
session_start();
require_once('php/db.php');

$db = DB_CONNECT();

if($_SERVER['REQUEST_METHOD']==='POST'){
	
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
				
				header("Location: index.php");
				
			}
			
		} else {
			
			$error = "Neplatné uživatelské jméno!";
			
		}
		
	}
	
}

?>
<!doctype html>
<html>

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
					
					<div class="midcol">
						
						<div class="formrow formlbl link"><a href="new_account.php">Vytvořit nový účet</a></div>
						<div class="formrow">
							<span class="formlbl">Uživatelské jméno:</span>
							<input class="formin" name="username" type="text" value="<?php if($_SERVER['REQUEST_METHOD']==='POST') echo $_POST['username'] ?>"></input>
						</div>
						<div class="formrow">
							<span class="formlbl">Heslo:</span>
							<input class="formin" name="password" type="password"></input>
						</div>
						<br><input class="bigbutton loginbtn" value="Přihlásit se" type="submit"></input>
						
					</div>
					
				</form>
				
			</div>
			
		</div>
		
    </body>
	
</html>
<?php
$db->close();
?>