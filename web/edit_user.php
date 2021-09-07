<?php
session_start();
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
		
		$stmt = $db->prepare('update User set admin=? where id=?');
		$stmt->bind_param("ii", $admin, $_GET['id']);
		$stmt->execute();
		$stmt->close();
		
		header('Location: user_mgmt.php');
		
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
						
						$stmt = $db->prepare('select id, username, admin from User where id=?');
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
							<span class="formlbl"><?php echo $row['username'] ?></span>
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