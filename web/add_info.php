<?php
/*
 * Formulář pro přidání nové zajímavosti
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

if(!isSet($_SESSION['user'])||!$_SESSION['user']['verified']){
	header('Location: login.php?page=add_info.php');
	//die("401 - Unauthorized");
}

if($_SERVER['REQUEST_METHOD']==='POST'){
	
	$number = $_POST['number'];
	$content = htmlspecialchars($_POST['content']);
	$link = htmlspecialchars($_POST['link']);
	$imageName = htmlspecialchars($_POST['imageName']);
	$imgAttrib = htmlspecialchars($_POST['imgAttrib']);
	
	if(isSet($_FILES['imageFile'])&&strlen(trim($_FILES['imageFile']['name']))>0&&strlen(trim($imageName))>0){ // upload image
		
		if(getimagesize($_FILES['imageFile']['tmp_name'])!==false){ // file is a valid image
			move_uploaded_file($_FILES['imageFile']['tmp_name'], $imageName);
			$imgRes = processImage($imageName);
		} else {
			$error = 'Neplatný soubor obrázku!';
		}
		
	}
	
	if(strlen($imageName)>80) {
		$error = "Název obrázku nesmí být delší než 80 znaků!";
	} else if(!($_POST['number']>0)){
		$error = "Číslo musí být větší než 0!";
	} else if($_POST['number']>999){
		$error = "Číslo musí být menší než 1000!";
	} else if(strlen(trim($_POST['content']))==0) {
		$error = "Prosím vyplňte popis!";
	} else if(strlen($_POST['content'])>1023) {
		$error = "Popis nesmí být delší než 1023 znaků!";
	} else if(strlen($_POST['link'])>100) {
		$error = "Odkaz nesmí být delší než 100 znaků!";
	} else if(!isset($_POST['cat'])) {
		$error = "Prosím vyberte aspoň jednu kategorii!";
	}
	
	if(!isSet($error)){
		
		$stmt = $db->prepare("select value from Config where name='infoLimit'");
		$stmt->execute();
		$res = $stmt->get_result();
		$stmt->close();
		$infoLimit = $res->fetch_assoc()['value'];
		
		$stmt = $db->prepare("select value from Config where name='infoLimitReset'");
		$stmt->execute();
		$res = $stmt->get_result();
		$stmt->close();
		$infoLimitReset = $res->fetch_assoc()['value'];
		
		$stmt = $db->prepare('select * from NumberInfo where createdBy=? and createdTime>DATE_SUB(NOW(), INTERVAL ? MINUTE)');
		$stmt->bind_param("si", $_SESSION['user']['id'], $infoLimitReset);
		$stmt->execute();
		$res = $stmt->get_result();
		$count = mysqli_num_rows($res);
		$stmt->close();
		
		if($count>=$infoLimit) {
			$error = "Limit prekročen!";
		} else {
			
			foreach($_POST['cat'] as $cat){
				
				$cat = htmlspecialchars($cat);
				
				$stmt = $db->prepare('select * from Category where name=?');
				if($stmt) {
					$stmt->bind_param("s", $cat);
					$stmt->execute();
					$res = $stmt->get_result();
					$stmt->close();
				}
				
				if(!isSet($res)||!$res->fetch_assoc()){
					
					$stmt = $db->prepare('insert into Category(name) values (?)');
					$stmt->bind_param("s", $cat);
					$stmt->execute();
					$stmt->close();
					
				}
				
			}
			
			$background = isSet($imgRes)?$imgRes['background']:null;
			$color = isSet($imgRes)?$imgRes['color']:null;
			
			$stmt = $db->prepare('insert into NumberInfo(number, content, link, imgSrc, imgAttrib, background, color, createdBy, createdTime) values (?, ?, ?, ?, ?, ?, ?, ?, now())');
			$stmt->bind_param("issssssi", $number, $content, $link, $imageName, $imgAttrib, $background, $color, $_SESSION['user']['id']);
			$stmt->execute();
			$stmt->close();
			
			$id = $db->insert_id;
			
			foreach($_POST['cat'] as $cat){
				
				$cat = htmlspecialchars($cat);
				
				$stmt = $db->prepare('insert into InfoCat(infoId, catId) values (?, (select id from Category where name=?))');
				$stmt->bind_param("ss", $id, $cat);
				$stmt->execute();
				$stmt->close();
				
			}
			
			//$info = 'Vaše zajímavost byla přidána, bude dostupná po potvrzení administrátorem. <a class="link" href="user_info_mgmt.php">Zobrazit/Upravit moje zajímavosti</a>';
			//header('Location: index.php');
			
			header('Location: edit_user_info.php?id='.$id.'&justAdded');
			
		}
		
	}
	
}

?>
<!doctype html>
<html>

	<head>

		<title>Přidat zajímavost</title>
		
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		
		<link rel="icon" href="res/cake.png">
		<link rel="stylesheet" href="css/page.css">
		<link rel="stylesheet" href="css/controls.css">
		<link rel="stylesheet" href="css/titlebar.css">
		<script src="js/titlebar.js"></script>
		
		<link rel="stylesheet" href="css/form_page.css">
		<link rel="stylesheet" href="css/form.css">
		
		<style>
			
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
			
			.info {
				padding: 10px;
				background: #2edc15;
				font-weight: bold;
				font-size: 18px;
				color: white;
			}
			
			.link {
				color: white;
			}
			
		</style>

	</head>

    <body>
		
		<?php include('php/titlebar.php'); ?>
		
		<div class="content">
			
			<div class="subtitlebar">
				<div class="backbtn"><a href="index.php"><</a></div><div class="subtitle">Přidat zajímavost</div>
			</div>
			
			<div class="form">
				
				<form method="POST" enctype="multipart/form-data">
					
					<div class="fullwidcol">
						
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
						
						<div class="formrow">
							<span class="formlbl">Číslo:</span>
							<input class="formin" type="number" name="number" value="<?php if($_SERVER['REQUEST_METHOD']==='POST') echo $_POST['number'] ?>"></input>
						</div>
						<div class="formrow">
							<span class="formlbl">Popis:</span>
							<textarea class="textarea" name="content"><?php if($_SERVER['REQUEST_METHOD']==='POST') echo $_POST['content'] ?></textarea>
						</div>
						<div class="formrow">
							<span class="formlbl">Odkaz:</span>
							<input class="formin" type="text" name="link" value="<?php if($_SERVER['REQUEST_METHOD']==='POST') echo $_POST['link'] ?>"></input>
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
							<span class="formlbl">Obrázek:</span>
							<input class="input" style="width:400px;" id="imageName" name="imageName" value="<?php if($_SERVER['REQUEST_METHOD']==='POST') echo $_POST['imageName']; ?>" readonly></input>
							<label><input id="filein" onchange="chooseFile();" class="filein" type="file" name="imageFile" accept=".png,.jpg,.jpeg,.gif"></input>
							<br><br><div class="filebtn">Vybrat soubor</div></label>
							<div type="button" onclick="cancelFile();" class="filebtn">Zrušit</div>
						</div>
						
						<div class="formrow">
							<span class="formlbl">Zdroj/autor obrázku:
								<!--img src="res/hint.png" onmousemove="
									attribInfo.style.display = 'block';
									attribInfo.style.left = event.clientX+10+'px';
									attribInfo.style.top = event.clientY+'px';
								" onmouseleave="
									attribInfo.style.display = 'none';
								"></img-->
							</span>
							<textarea class="textarea" name="imgAttrib"><?php if($_SERVER['REQUEST_METHOD']==='POST') echo $_POST['imgAttrib'] ?></textarea>
						</div>
						
						<div class="formrow">
							<span class="formlbl">Kategorie:</span>
							<script>
								
								function addCat(){
									var name = newCatName.value;
									catmsgbox.innerText = "";
									if(cats.includes(name)) {
										catmsgbox.innerText = "Kategorie již existuje!";
									} else if(name.length>20) {
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
										cats[cats.length] = name;
									}
								}
								
							</script>
							<br><input class="newcat" id="newCatName"></input><button class="newcatbtn" type="button" onclick="addCat();">+</button>
							<span style="color:red;" id="catmsgbox"></span>
							<br>
							<br><div class="catfield" id="catField">
								<?php
									
									$stmt = $db->prepare('select name from Category');
									if($stmt) {
										$stmt->execute();
										$res = $stmt->get_result();
										$stmt->close();
										$cats = [];
										
										while($row = $res->fetch_assoc()){
											$name = $row['name'];
											$cats[count($cats)] = '"'.$name.'"';
											?><div><label><input type="checkbox" name="cat[]" value="<?php echo $name ?>"
												<?php if($_SERVER['REQUEST_METHOD']==='POST'&&isSet($_POST['cat'])) if(in_array($name, $_POST['cat'])) echo 'checked' ?>></input><?php echo $name ?></label></div><?php
										}
									}
									
								?>
								<script>
									
									<?php
										$catnames = '['.implode(",", $cats).']';
									?>
									
									cats = <?php echo $catnames; ?>;
									
								</script>
							</div>
						</div>
						
						<div class="formrow"><input class="bigbutton" value="Přidat" type="submit"></input></div>
						
					</div>
					
				</form>
				
			</div>
			
		</div>
		
		<!--div id="attribInfo" class="tooltip">
			[TEMP] Popis licence obrázku
		</div-->
		
    </body>

</html>
<?php
$db->close();
?>