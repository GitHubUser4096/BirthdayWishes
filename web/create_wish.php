<?php
session_start();
require_once('php/db.php');

$db = DB_CONNECT();

if(isSet($_SESSION['wish'])) {
	$wish = $_SESSION['wish'];
}

if($_SERVER['REQUEST_METHOD']==='POST'){
	
	if(strlen(trim($_POST['for']))==0){
		$error = "Prosím vyplňte 'Oslovení'!";
	} else if(strlen(trim($_POST['from']))==0){
		$error = "Prosím vyplňte 'Kdo přeje'!";
	} else if(strlen(trim($_POST['bdayNumber']))==0){
		$error = "Prosím vyplňte 'Kolikáté narozeniny'!";
	} else if(!($_POST['bdayNumber']>0)) {
		$error = "Prosím zadejte číslo větší než 0!";
	} else if(!isSet($_POST['cat'])) {
		$error = "Prosím vyberte aspoň jeden zájem!";
	} else {
		
		$wish = [];
		$wish['for'] = $_POST['for'];
		$wish['from'] = $_POST['from'];
		$wish['bdayNumber'] = $_POST['bdayNumber'];
		$wish['cat'] = $_POST['cat'];
		//$wish['choose'] = $_POST['choose'];
		if(isSet($_POST['choose_random'])) $wish['choose'] = 'random';
		else if(isSet($_POST['choose_from_list'])) $wish['choose'] = 'list';
		$_SESSION['wish'] = $wish;
		
		header("Location: choose_info.php");
		
	}
	
}

?>
<!doctype html>
<html>

	<head>
		
		<title>Vytvořit přání</title>
		
		<link rel="icon" href="res/cake.png">
		<link rel="stylesheet" href="css/page.css">
		<link rel="stylesheet" href="css/controls.css">
		<link rel="stylesheet" href="css/titlebar.css">
		<script src="js/titlebar.js"></script>
		
		<link rel="stylesheet" href="css/form_page.css">
		<link rel="stylesheet" href="css/form.css">
		
		<style>
			
			.check {
				width: 18px;
				height: 18px;
			}
			
			hr {
				margin: 5px;
			}
			
		</style>
		
	</head>

    <body>
		
		<?php include('php/titlebar.php'); ?>
		
		<div class="content">
			
			<div class="subtitlebar">
				<div class="backbtn"><a href="index.php"><</a></div><div class="subtitle">Vytvořit přání</div>
			</div>
			
			<div class="form">
				
				
				<?php
					if(isSet($error)) {
						?><div class="error"><?php
							echo $error;
						?></div><?php
					}
				?>
				
				<form method="POST">
					
					<script>
						
						function selAll(){
							var cats = document.querySelectorAll(".catCheck");
							for(var cat of cats){
								cat.checked = true;
							}
						}
						
						function cancelSel(){
							var cats = document.querySelectorAll(".catCheck");
							for(var cat of cats){
								cat.checked = false;
							}
						}
						
						function checkCheck(){
							let all = true;
							let cats = document.querySelectorAll(".catCheck");
							for(var cat of cats){
								all = all && cat.checked;
							}
							checkAll.checked = all;
						}
						
						function toggleCheck(){
							let cats = document.querySelectorAll(".catCheck");
							for(var cat of cats){
								cat.checked = checkAll.checked;
							}
						}
						
					</script>
					
					<div class="leftcol">
						
						<div class="formrow">
							<span class="formlbl">Oslovení (např. Milý Michale):</span>
							<input class="formin" type="text" name="for" value="<?php if(isSet($wish)) echo $wish['for']; else if($_SERVER['REQUEST_METHOD']==='POST') echo $_POST['for'] ?>"></input>
						</div>
						<div class="formrow">
							<span class="formlbl">Kdo přeje:</span>
							<input class="formin" type="text" name="from" value="<?php if(isSet($wish)) echo $wish['from']; else if($_SERVER['REQUEST_METHOD']==='POST') echo $_POST['from'] ?>"></input>
						</div>
						<div class="formrow">
							<span class="formlbl">Kolikáté narozeniny:</span>
							<input class="formin" type="number" name="bdayNumber" value="<?php if(isSet($wish)) echo $wish['bdayNumber']; else if($_SERVER['REQUEST_METHOD']==='POST') echo $_POST['bdayNumber'] ?>"></input>
						</div>
						<div class="formrow"><span class="formlbl">Zájmy:</span>
							<div class="catfield" id="catField">
								<!--button type="button" onclick="selAll();">Vybrat všechny</button>
								<button type="button" onclick="cancelSel();">Zrušit výběr</button-->
								
								<label><input onclick="toggleCheck();" id="checkAll" class="check" type="checkbox">Vybrat všechny</label>
								<hr>
								
								<?php
									
									$stmt = $db->prepare('select distinct name from InfoCat inner join NumberInfo on NumberInfo.id=infoid inner join Category on Category.id=catid where approved=1');
									if($stmt) {
										$stmt->execute();
										$res = $stmt->get_result();
										$stmt->close();
										
										while($row = $res->fetch_assoc()){
											$name = $row['name'];
											?><div><label><input class="check catCheck" onclick="checkCheck();" type="checkbox" name="cat[]"
												<?php if($_SERVER['REQUEST_METHOD']==='POST' && isSet($_POST['cat']) && in_array($name, $_POST['cat']) || isSet($wish) && isSet($wish['cat']) && in_array($name, $wish['cat'])) echo 'checked' ?> value="<?php echo $name ?>">
												</input><?php echo $name ?></label></div><?php
										}
									}
									
								?>
							</div>
						</div>
						
					</div>
					
					<!--br><input type="radio" name="choose" value="random" checked></input>Vybrat náhodně
					<br><input type="radio" name="choose" value="list"></input>Vybrat ze seznamu-->
					
					<div class="rightcol">
						
						<div class="formrow"><span class="formlbl">Vybrat:</span></div>
						<div class="formrow"><input class="bigbutton" name="choose_random" value="Náhodně >" type="submit"></input></div>
						<div class="formrow"><span class="formlbl">nebo</span></div>
						<div class="formrow"><input class="bigbutton" name="choose_from_list" value="Ze seznamu >" type="submit"></input></div>
						
					</div>
					
				</form>
				
			</div>
			
		</div>
		
    </body>

</html>
<?php
$db->close();
?>