<?php
session_start();

?>
<!doctype html>
<html>

	<head>
		
		<title>Narozeninová Přání</title>
		
	</head>

    <body>
	
		Narozeninová Přání
		
		<br><a href="create_wish.php">Vytvořit přání</a>
		<?php
			if(isSet($_SESSION['user'])) { ?>
				
				<br><?php echo $_SESSION['user']['username'] ?>
				<a href="acc_mgmt.php">Spravovat účet</a>
				<a href="logout.php">Odhlásit se</a>
				<br><a href="add_info.php">Přidat zajímavost</a>
				
				<?php
				if($_SESSION['user']['admin']) { ?>
					<br><a href="info_mgmt.php">Spravovat zajímavosti</a>
					<?php
				}
				
			} else { ?>
				
				<br><a href="login.php">Přihlásit se</a>
				<br><a href="newAccount.php">Vytvořit účet</a>
				<?php
				
			}
		?>
		
    </body>

</html>