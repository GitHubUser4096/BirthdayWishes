<?php
session_start();
require_once('db.php');

$db = DB_CONNECT('wishes');

if(!isSet($_SESSION['user'])){
	die("401 - Unauthorized");
}

if($_SERVER['REQUEST_METHOD']==='POST'){
	
	if(isSet($_POST['change_pass'])){
		
		$password = $_POST['password'];
		$verifyPass = $_POST['verify_password'];
		
		if($password==""){
			$info = "<br>Prosím zadejte heslo!";
		} else if($password!=$verifyPass){
			$info = "<br>Hesla se neshodují!";
		} else {
			
			$hashedPass = password_hash($_POST['password'], PASSWORD_DEFAULT);
			
			$stmt = $db->prepare('update user set password=? where id=?');
			$stmt->bind_param("si", $hashedPass, $_SESSION['user']['id']);
			$stmt->execute();
			$stmt->close();
			
			$info = "<br>Heslo úspěšně změněno!";
			
		}
		
	} else if(isSet($_POST['confirmDelete'])){
		
		$stmt = $db->prepare('delete from user where id=?');
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
		
	</head>

    <body>
		
		<form method="post">
			
			Spravovat účet
			
			<?php
				
				if(isSet($info)) echo $info;
				
			?>
			
			<br><a href="index.php">Zpět</a>
			<br>Uživatelské jméno: <?php echo $_SESSION['user']['username'] ?>
			
			<br>Změnit heslo:
			<br>Heslo: <input name="password" type="password"></input>
			<br>Potvrdit heslo: <input name="verify_password" type="password"></input>
			<br><input type="submit" name="change_pass" value="Změnit heslo">
			<br><input type="submit" name="delete" value="Smazat účet">
			
			<?php
				
				if(isSet($_POST['delete'])){
					?>
					
					<br>Potvrdit odstranění
					<input type="submit" name="confirmDelete" value="Potvrdit"></input>
					<input type="submit" name="cancelDelete" value="Zrušit"></input>
					
					<?php
				}
				
			?>
			
		</form>
		
    </body>
	
</html>
<?php
$db->close();
?>