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

if(!isSet($_SESSION['user'])||!$_SESSION['user']['verified']) {
	header('Location: login.php?page=edit_config.php');
}

if(!$_SESSION['user']['admin']) {
	die('401 - Unauthorized');
}

$db = DB_CONNECT();

if($_SERVER['REQUEST_METHOD']==='POST'){
	
	$stmt = $db->prepare('select description, name, value, type from Config');
	$stmt->execute();
	$res = $stmt->get_result();
	$stmt->close();
	
	while($row = $res->fetch_assoc()){
		
		$value = trim($_POST[$row['name']]);
		
		if(strlen($value)==0){
			$error = "Prosím vyplňte '".$row['description']."'!";
			break;
		} else if(strlen($value)>32){
			$error = "'".$row['description']."' nesmí být delší než 32 znaků!";
			break;
		}
		
		$stmt = $db->prepare('update Config set value=? where name=?');
		$stmt->bind_param('ss', $value, $row['name']);
		$stmt->execute();
		$stmt->close();
		
	}
	
	if(!isSet($error)){
		$info = "Konfigurace uložena!";
	}
	
}

?>
<!doctype html>
<html>

	<head>
		
		<title>Konfigurace</title>
		
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
				<div class="backbtn"><a href="index.php"><</a></div><div class="subtitle">Konfigurace</div>
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
					
					<div class="midcol">
						
						<?php
							
							$stmt = $db->prepare('select description, name, value, type from Config');
							$stmt->execute();
							$res = $stmt->get_result();
							$stmt->close();
							
							while($row = $res->fetch_assoc()){
								?>
								<div class="formrow">
									<span class="formlbl"><?php echo $row['description'] ?></span>
									<input class="formin" name="<?php echo $row['name'] ?>" type="<?php echo $row['type'] ?>" value="<?php echo $row['value'] ?>"></input>
								</div>
								<?php
							}
						?>
						
						<div class="formrow">
							<input class="bigbutton loginbtn" value="Uložit" type="submit"></input>
						</div>
						
					</div>
					
				</form>
				
			</div>
			
		</div>
		
    </body>
	
</html>
<?php
$db->close();
?>