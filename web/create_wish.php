<?php
session_start();
require_once('db.php');

$db = DB_CONNECT('wishes');

if($_SERVER['REQUEST_METHOD']==='POST'){
	
	if(strlen(trim($_POST['for']))==0){
		$error = "<br>Prosím vyplňte 'Oslovení'!";
	} else if(strlen(trim($_POST['from']))==0){
		$error = "<br>Prosím vyplňte 'Kdo přeje'!";
	} else if(strlen(trim($_POST['bdayNumber']))==0){
		$error = "<br>Prosím vyplňte 'Kolikáté narozeniny'!";
	} else if(!isSet($_POST['cat'])) {
		$error = "<br>Prosím vyberte aspoň jeden zájem!";
	} else {
		
		$wish = [];
		$wish['for'] = $_POST['for'];
		$wish['from'] = $_POST['from'];
		$wish['bdayNumber'] = $_POST['bdayNumber'];
		$wish['cat'] = $_POST['cat'];
		$wish['choose'] = $_POST['choose'];
		$_SESSION['wish'] = $wish;
		header("Location: choose_info.php");
		
	}
	
}

?>
<!doctype html>
<html>

	<head>
		
		<title>Vytvořit přání</title>
		
	</head>

    <body>
		
		Vytvořit přání
		
		<br><a href="index.php">Zpět</a>
		
		<?php
			
			if(isSet($error)) {
				echo $error;
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
				
			</script>
			
			Oslovení (např. Milý Michale): <input type="text" name="for" value="<?php if($_SERVER['REQUEST_METHOD']==='POST') echo $_POST['for'] ?>"></input>
			<br>Kdo přeje: <input type="text" name="from" value="<?php if($_SERVER['REQUEST_METHOD']==='POST') echo $_POST['from'] ?>"></input>
			<br>Kolikáté narozeniny: <input type="number" name="bdayNumber" value="<?php if($_SERVER['REQUEST_METHOD']==='POST') echo $_POST['bdayNumber'] ?>"></input>
			<br>Zájmy:
			<div id="catField">
				<button type="button" onclick="selAll();">Vybrat všechny</button>
				<button type="button" onclick="cancelSel();">Zrušit výběr</button>
				<?php
					
					$stmt = $db->prepare('select distinct name from infocat inner join numberinfo on numberinfo.id=infoid inner join category on category.id=catid where approved=1');
					$stmt->execute();
					$res = $stmt->get_result();
					$stmt->close();
					
					while($row = $res->fetch_assoc()){
						$name = $row['name'];
						?><br><input class="catCheck" type="checkbox" name="cat[]"
							<?php if($_SERVER['REQUEST_METHOD']==='POST'&&isSet($_POST['cat'])) if(in_array($name, $_POST['cat'])) echo 'checked' ?> value="<?php echo $name ?>">
							<?php echo $name ?></input><?php
					}
					
				?>
			</div>
			
			<br><input type="radio" name="choose" value="random" checked></input>Vybrat náhodně
			<br><input type="radio" name="choose" value="list"></input>Vybrat ze seznamu
			
			<br><input value="Pokračovat" type="submit"></input>
			
		</form>
		
    </body>

</html>
<?php
$db->close();
?>