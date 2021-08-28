<?php
session_start();
require_once('db.php');

$db = DB_CONNECT('wishes');

if($_SERVER['REQUEST_METHOD']==='POST'){
	
	$username = htmlspecialchars($_POST['username']);
	$password = $_POST['password'];
	$verifyPass = $_POST['verify_password'];
	
	if($username==""){
		$error = "<br>Prosím zadejte uživatelské jméno!";
	} else if($password==""){
		$error = "<br>Prosím zadejte heslo!";
	} else if($password!=$verifyPass){
		$error = "<br>Hesla se neshodují!";
	} else {
		
		$stmt = $db->prepare("select * from User where username=?");
		$stmt->bind_param("s", $username);
		$stmt->execute();
		$res = $stmt->get_result();
		$stmt->close();
		
		if($res->num_rows>0) {
			$error = "<br>Uživatelské jméno již existuje!";
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
		
	</head>

    <body>
		
		<form method="post">
			
			Vytvořit účet
			<br><a href="index.php">Zpět</a>
			<?php
				if(isSet($error)) echo $error;
			?>
			<br>Uživatelské jméno: <input name="username" type="text" value="<?php if($_SERVER['REQUEST_METHOD']==='POST') echo $_POST['username'] ?>"></input>
			<br>Heslo: <input name="password" type="password"></input>
			<br>Potvrdit heslo: <input name="verify_password" type="password"></input>
			<br><input value="Create" type="submit"></input>
			
		</form>
		
    </body>
	
</html>
<?php
$db->close();
?>