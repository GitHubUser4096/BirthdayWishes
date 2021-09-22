<?php
session_start();

if(!isSet($_SERVER['HTTPS'])){
	header("Location: https://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);
}

require_once('php/db.php');

$db = DB_CONNECT();

if(!isset($_GET['id'])) {
	die('400 - Bad request');
}

if(!isSet($_SESSION['user']) || !$_SESSION['user']['admin']) {
	die('401 - Unauthorized');
}

if($_SERVER['REQUEST_METHOD']==='POST'){
	
	if(isset($_POST['confirmDelete'])) {
		
		$stmt = $db->prepare('delete from User where id=?');
		$stmt->bind_param("i", $_GET['id']);
		$stmt->execute();
		$stmt->close();
		
		header('Location: user_mgmt.php');
		
	} else if(isSet($_POST['save'])) {
		
		$admin = (isSet($_POST['admin']) && $_POST['admin'])?1:0;
		$verified = (isSet($_POST['verified']) && $_POST['verified'])?1:0;
		
		$stmt = $db->prepare('update User set admin=?, verified=? where id=?');
		$stmt->bind_param("iii", $admin, $verified, $_GET['id']);
		$stmt->execute();
		$stmt->close();
		
		header('Location: user_mgmt.php');
		
	} else if(isSet($_POST['reset_token'])) {
		
		$token = uniqid();
		
		$page = $_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
		$page = substr($page, 0, strrpos($page, '/'));
		
		$valid_until = date_format(date_add(date_create(), date_interval_create_from_date_string("10 minutes")), 'Y-m-d H:i:s');
		
		$stmt = $db->prepare("select username from User where id=?");
		$stmt->bind_param("i", $_GET['id']);
		$stmt->execute();
		$res = $stmt->get_result();
		$stmt->close();
		
		$username = $res->fetch_assoc()['username'];
		
		$stmt = $db->prepare("insert into PassRequests(token, username, valid_until) values (?, ?, ?)");
		$stmt->bind_param("sss", $token, $username, $valid_until);
		$stmt->execute();
		$stmt->close();
		
		$resetLink = 'https://'.$page.'/reset_pass.php?token='.$token;
		
	}
	
}

?>
<!doctype html>
<html>

	<head>
		
		<title>Upravit uživatele</title>
		
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		
		<link rel="icon" href="res/cake.png">
		<link rel="stylesheet" href="css/page.css">
		<link rel="stylesheet" href="css/controls.css">
		<link rel="stylesheet" href="css/titlebar.css">
		<script src="js/titlebar.js"></script>
		
		<link rel="stylesheet" href="css/form_page.css">
		<link rel="stylesheet" href="css/form.css">
		
		<style>
			
			.check {
				width: 24px;
				height: 24px;
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
			
		</style>
		
	</head>

    <body>
		
		<?php include('php/titlebar.php'); ?>
		
		<div class="content">
			
			<div class="subtitlebar">
				<div class="backbtn"><a href="user_mgmt.php"><</a></div><div class="subtitle">Upravit uživatele</div>
			</div>
			
			<div class="form">
				
				<form method="POST" enctype="multipart/form-data">
					
					<?php
						
						$stmt = $db->prepare('select id, username, email, admin, verified from User where id=?');
						$stmt->bind_param("i", $_GET['id']);
						$stmt->execute();
						$res = $stmt->get_result();
						$stmt->close();
						
						$row = $res->fetch_assoc();
						
					?>
					
					<div class="fullwidcol">
						
						<?php
							if(isSet($error)) {
								?><div class="error"><?php
									echo $error;
								?></div><?php
							}
						?>
						
						<div class="formrow">
							<span class="formlbl">Uživatelské jméno:</span>
							<input class="formin" type="text" name="number" value="<?php echo $row['username'] ?>" disabled></input>
						</div>
						
						<div class="formrow">
							<span class="formlbl">E-Mail:</span>
							<input class="formin" type="text" name="number" value="<?php echo $row['email'] ?>" disabled></input>
						</div>
						
						<!--div class="formrow">
							<span class="formlbl">Číslo:</span>
							<input class="formin" type="text" name="number" value="<?php echo $row['number'] ?>"></input>
						</div-->
						
						<div class="formrow">
							<label><span class="formlbl">Admin:</span>
							<input class="check" type="checkbox" name="admin" <?php if($row['admin']) echo 'checked'; ?>></input></label>
						</div>
						
						<div class="formrow">
							<label><span class="formlbl">Ověřený:</span>
							<input class="check" type="checkbox" name="verified" <?php if($row['verified']) echo 'checked'; ?>></input></label>
						</div>
						
						<div class="formrow">
							<input class="bigbutton" type="submit" name="reset_token" value="Resetovat heslo"></input>
						</div>
						<div class="formrow">
							<?php if(isSet($resetLink)) {
								?>
								Odkaz na resetování hesla: <a class="link" href="<?php echo $resetLink; ?>"><?php echo $resetLink; ?></a>
								<?php
							} ?>
						</div>
						
						<div class="formrow">
							<input class="bigbutton" type="submit" name="save" value="Uložit"></input>
						</div>
						<div class="formrow">
							<input class="bigbutton deletebtn" type="submit" name="delete" value="Odstranit"></input>
						</div>
						
					</div>
					
					<?php
						
						if(isSet($_POST['delete'])){
							?>
							
							<div class="blackout"></div>
							<div class="confirmdialog">
								<div class="dialogtitle">Odstranit uživatele</div>
								<div class="dialoginfo">Opravdu chcete odstranit uživatele?</div>
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