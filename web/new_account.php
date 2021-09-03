<?php
session_start();
require_once('db.php');

$db = DB_CONNECT('wishes');

if($_SERVER['REQUEST_METHOD']==='POST'){
	
	$username = htmlspecialchars($_POST['username']);
	$password = $_POST['password'];
	$verifyPass = $_POST['verify_password'];
	
	if($username==""){
		$error = "Prosím zadejte uživatelské jméno!";
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
			
			$stmt = $db->prepare('insert into User(username, password) values (?, ?)');
			$stmt->bind_param("ss", $username, $hashedPass);
			$stmt->execute();
			$res = $stmt->get_result();
			$stmt->close();
			
			$id = $db->insert_id;
			
			$user = [];
			$user['id'] = $id;
			$user['username'] = $username;
			$user['admin'] = false;
			
			$_SESSION['user'] = $user;
			
			header("Location: index.php");
			
		}
		
	}
	
}

?>
<!doctype html>
<html>

	<head>
		
		<title>Vytvořit účet</title>
		
		<link rel="icon" href="res/cake.png">
		<link rel="stylesheet" href="css/page.css">
		<link rel="stylesheet" href="css/controls.css">
		<link rel="stylesheet" href="css/titlebar.css">
		<script src="js/titlebar.js"></script>
		
		<link rel="stylesheet" href="css/form_page.css">
		<link rel="stylesheet" href="css/form.css">
		
		<style>
			
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