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
require_once('php/process_image.php');

$db = DB_CONNECT();

if(!isset($_GET['id'])) {
	die('400 - Bad request');
}

if(!isSet($_SESSION['user']) || !$_SESSION['user']['admin']) {
	die('401 - Unauthorized');
}

if(!$row) {
	die('400 - Bad request');
}

if($_SERVER['REQUEST_METHOD']==='POST'){
	
	if(isset($_POST['confirmDelete'])) {
		
		$stmt = $db->prepare('delete from InfoCat where infoid=?');
		$stmt->bind_param("i", $_GET['id']);
		$stmt->execute();
		$stmt->close();
		
		$stmt = $db->prepare('delete from NumberInfo where id=?');
		$stmt->bind_param("i", $_GET['id']);
		$stmt->execute();
		$stmt->close();
		
		header('Location: info_mgmt.php');
		
	} else if(isSet($_POST['save'])) {
		
		$number = $_POST['number'];
		$content = htmlspecialchars($_POST['content']);
		$link = htmlspecialchars($_POST['link']);
		$state = htmlspecialchars($_POST['state']);
		$imageName = htmlspecialchars($_POST['imageName']);
		$imgAttrib = htmlspecialchars($_POST['imgAttrib']);
		
		if(isSet($_FILES['imageFile'])&&strlen(trim($_FILES['imageFile']['name']))>0&&strlen(trim($imageName))>0){ // upload image
			
			if(getimagesize($_FILES['imageFile']['tmp_name'])!==false){ // file is a valid image
				move_uploaded_file($_FILES['imageFile']['tmp_name'], $imageName);
				$imgRes = processImage($imageName);
			}
			
		}
		
		if(strlen($imageName)>80) {
			$error = "Název obrázku nesmí být delší než 80 znaků!";
		} else if(!($_POST['number']>0)){
			$error = "Číslo musí být větší než 0!";
		} else if($number>999){
			$error = "Číslo musí být menší než 1000!";
		} else if(strlen(trim($content))==0) {
			$error = "Prosím vyplňte popis!";
		} else if(strlen($content)>1023) {
			$error = "Popis nesmí být delší než 1023 znaků!";
		} else if(strlen($link)>100) {
			$error = "Odkaz nesmí být delší než 100 znaků!";
		} else if(!isSet($_POST['cat'])) {
			$error = "Prosím vyberte aspoň jednu kategorii!";
		} else {
			
			if(isSet($imgRes)){
				$stmt = $db->prepare("update NumberInfo set color=?, background=? where id=?");
				$stmt->bind_param("ssi", $imgRes['color'], $imgRes['background'], $_GET['id']);
				$stmt->execute();
				$stmt->close();
			}
			
			$stmt = $db->prepare('update NumberInfo set number=?, content=?, link=?, imgSrc=?, imgAttrib=?, state=? where id=?');
			$stmt->bind_param("isssssi", $number, $content, $link, $imageName, $imgAttrib, $state, $_GET['id']);
			$stmt->execute();
			$stmt->close();
			
			$stmt = $db->prepare('delete from InfoCat where infoid=?');
			$stmt->bind_param("i", $_GET['id']);
			$stmt->execute();
			$stmt->close();
			
			foreach($_POST['cat'] as $cat) {
				
				$cat = htmlspecialchars($cat);
				
				$stmt = $db->prepare('select * from Category where name=?');
				$stmt->bind_param("s", $cat);
				$stmt->execute();
				$res = $stmt->get_result();
				$stmt->close();
				
				if(!$res->fetch_assoc()){
					
					$stmt = $db->prepare('insert into Category(name) values (?)');
					$stmt->bind_param("s", $cat);
					$stmt->execute();
					$stmt->close();
					
				}
				
				$stmt = $db->prepare('insert into InfoCat(infoid, catid) values (?, (select id from Category where name=?))');
				$stmt->bind_param("is", $_GET['id'], $cat);
				$stmt->execute();
				$stmt->close();
				
			}
			
			header('Location: info_mgmt.php');
			
		}
		
	}
	
}

?>
<!doctype html>
<html>

	<head>
		
		<title>Upravit zajímavost</title>
		
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
			
			.filein {
				display: none;
			}
			
			.filebtn {
				width: 150px;
				text-align: center;
				display: inline;
				color: white;
				border: none;
				background: #2edc15;
				padding: 5px;
				font-size: 18px;
				cursor: pointer;
			}
			
			.newcat {
				font-size: 20px;
				border: none;
				background: #e6e2d7;
				color: black;
				padding: 5px;
			}
			
			.newcat:focus {
				outline: none;
			}
			
			.newcatbtn {
				border: none;
				background: #2edc15;
				cursor: pointer;
				color: white;
				font-size: 20px;
				padding: 5px;
				width: 35px;
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
				<div class="backbtn"><a href="info_mgmt.php"><</a></div><div class="subtitle">Upravit zajímavost</div>
			</div>
			
			<div class="form">
				
				<form method="POST" enctype="multipart/form-data">
					
					<?php
						
						$stmt = $db->prepare('select number, content, link, imgSrc, imgAttrib, state from NumberInfo where id=?');
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
							<span class="formlbl">Číslo:</span>
							<input class="formin" type="text" name="number" value="<?php if($_SERVER['REQUEST_METHOD']==='POST') echo $_POST['number']; else echo $row['number']; ?>"></input>
						</div>
						<div class="formrow">
							<span class="formlbl">Popis:</span>
							<textarea class="textarea" name="content"><?php if($_SERVER['REQUEST_METHOD']==='POST') echo $_POST['content']; else echo $row['content']; ?></textarea>
						</div>
						<div class="formrow">
							<span class="formlbl">Odkaz:</span>
							<input class="formin" type="text" name="link" value="<?php if($_SERVER['REQUEST_METHOD']==='POST') echo $_POST['link']; else echo $row['link']; ?>"></input>
						</div>
						
						<script>
							
							function chooseFile(){
								let val = filein.value.split('\\');
								val = val[val.length-1];
								imageName.value = "images/<?php echo $_SESSION['user']['username'] ?>_"+new Date().getTime()+"_"+val;
							}
							
							function cancelFile(){
								imageName.value = "";
							}
							
						</script>
						
						<div class="formrow">
							<!--span class="formlbl">Obrázek:</span>
							<label><input id="filein" onchange="chooseFile();" class="filein" type="file" name="image" accept=".png,.jpg,.jpeg,.gif"></input><div class="filebtn">Vybrat soubor</div></label>
							<div id="filename"><--?php if($_SERVER['REQUEST_METHOD']==='POST') echo 'Vybráno: '.$_FILES['image']['name'] ?></div>
							<input type="hidden" name="altImage" value="<--?php if($_SERVER['REQUEST_METHOD']==='POST') echo $_FILES['image']['name'] ?>"></input-->
							<span class="formlbl">Obrázek:</span>
							<input class="input" style="width:400px;" id="imageName" name="imageName" value="<?php if($_SERVER['REQUEST_METHOD']==='POST') echo $_POST['imageName']; else echo $row['imgSrc']; ?>" readonly></input>
							<label><input id="filein" onchange="chooseFile();" class="filein" type="file" name="imageFile" accept=".png,.jpg,.jpeg,.gif"></input>
							<br><br><div class="filebtn">Vybrat soubor</div></label>
							<div type="button" onclick="cancelFile();" class="filebtn">Zrušit</div>
						</div>
						
						<div class="formrow">
							<span class="formlbl">Zdroj obrázku:</span>
							<textarea class="textarea" name="imgAttrib"><?php if($_SERVER['REQUEST_METHOD']==='POST') echo $_POST['imgAttrib']; else echo $row['imgAttrib']; ?></textarea>
						</div>
						
						<div class="formrow">
							<label><span class="formlbl">Stav:</span>
							<select name="state">
								<option <?php if($row['state']=='pending') echo 'selected'; ?> value="pending">Před schválením</option>
								<option <?php if($row['state']=='approved') echo 'selected'; ?> value="approved">Schváleno</option>
								<option <?php if($row['state']=='dismissed') echo 'selected'; ?> value="dismissed">Zamítnuto</option>
							</select>
							<!--input class="check" type="checkbox" name="approved" <!--?php if($row['approved']) echo 'checked'; ?>></input></label-->
						</div>
						
						<div class="formrow">
							<span class="formlbl">Kategorie:</span>
							<script>
								
								function addCat(){
									var name = newCatName.value;
									catmsgbox.innerText = "";
									if(name.length>20) {
										catmsgbox.innerText = "Jméno kategorie nesmí být delší než 20 znaků!";
									} else if(name.trim().length>0){
										var inp = document.createElement('input');
										var div = document.createElement('div');
										var label = document.createElement('label');
										var span = document.createElement('span');
										span.innerText = name;
										inp.type = 'checkbox';
										inp.name = 'cat[]';
										inp.value = name;
										inp.checked = true;
										label.appendChild(inp);
										label.appendChild(span);
										div.appendChild(label);
										catField.appendChild(div);
										newCatName.value = "";
									}
								}
								
							</script>
							<br><input class="newcat" id="newCatName"></input><button class="newcatbtn" type="button" onclick="addCat();">+</button>
							<span style="color:red;" id="catmsgbox"></span>
							<br>
							<br><div class="catfield" id="catField">
								<?php
									
									$stmt = $db->prepare('select name from Category');
									$stmt->execute();
									$res = $stmt->get_result();
									$stmt->close();
									
									while($row = $res->fetch_assoc()){
										$name = $row['name'];
										?><div><label><input type="checkbox" name="cat[]" <?php
											
											if($_SERVER['REQUEST_METHOD']==='POST') {
												if(isSet($_POST['cat'])&&in_array($name, $_POST['cat'])) echo 'checked';
											} else {
												$stmt = $db->prepare('select * from InfoCat inner join Category on Category.id=catid where infoid=? and name=?');
												$stmt->bind_param('is', $_GET['id'], $name);
												$stmt->execute();
												$res2 = $stmt->get_result();
												$stmt->close();
												if($res2->fetch_assoc()) echo 'checked';
											}
											
										?> value="<?php echo $name ?>"></input><?php echo $name ?></label></div><?php
									}
									
								?>
							</div>
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
								<div class="dialogtitle">Odstranit zajímavost</div>
								<div class="dialoginfo">Opravdu chcete odstranit zajímavost?</div>
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