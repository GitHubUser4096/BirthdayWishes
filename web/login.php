<?php
session_start();
require_once('db.php');

$db = DB_CONNECT('wishes');

if($_SERVER['REQUEST_METHOD']==='POST'){
	
	$username = htmlspecialchars($_POST['username']);
	$password = $_POST['password'];
	
	if($username==""){
		$error = "<br>Prosím zadejte uživatelské jméno!";
	} else if($password==""){
		$error = "<br>Prosím zadejte heslo!";
	} else {
		
		$stmt = $db->prepare("select * from User where username=?");
		$stmt->bind_param("s", $username);
		$stmt->execute();
		$res = $stmt->get_result();
		$stmt->close();
		
		if($res->num_rows>0) {
			
			$user = $res->fetch_assoc();
			
			if(!password_verify($password, $user["password"])){
				$error = "<br>Neplatné heslo!";
			} else {
				
				$_SESSION['user'] = $user;
				
				header("Location: index.php");
				
			}
			
		} else {
			
			$error = "<br>Neplatné uživatelské jméno!";
			
		}
		
	}
	
}

?>
<!doctype html>
<html>

	<head>
		
		<title>Přihlásit se</title>
		
	</head>

    <body>
		
		<form method="post">
			
			Přihlásit se
			<br><a href="index.php">Zpět</a>
			<?php
				if(isSet($error)) echo $error;
			?>
			<br>Uživatelské jméno: <input name="username" type="text" value="<?php if($_SERVER['REQUEST_METHOD']==='POST') echo $_POST['username'] ?>"></input>
			<br>Heslo: <input name="password" type="password"></input>
			<br><input value="Přihlásit se" type="submit"></input>
			
		</form>
		
    </body>
	
</html>
<?php
$db->close();
?>