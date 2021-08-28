<?php
session_start();
require_once('db.php');

$db = DB_CONNECT('wishes');

if(!isSet($_SESSION['user'])){
	die("401 - Unauthorized");
}

?>
<!doctype html>
<html>

	<head>

		<title>Přidat zajímavost</title>

	</head>

    <body>
		
		Přidat zajímavost
		
		<br><a href="index.php">Zpět</a>
		
		<?php
			
			if($_SERVER['REQUEST_METHOD']==='POST'){
				
				$image_name = $_FILES['image']['name'];
				$image = '';
				
				if(strlen(trim($image_name))>0) {
					if(getimagesize($_FILES['image']['tmp_name'])!==false){ // file is a valid image
						if(move_uploaded_file($_FILES['image']['tmp_name'], "images/".$image_name)){ // file is successfully copied from tmp
							$image = "images/".$image_name;
						}
					}
				} else if(strlen(trim($_POST['altImage']))>0) {
					$image = 'images/'.$_POST['altImage'];
				}
				
				if(!($_POST['number']>0)){
					echo "<br>Číslo musí být větší než 0!";
				} else if(strlen(trim($_POST['content']))==0) {
					echo "<br>Prosím vyplňte popis!";
				} else if(!isset($_POST['cat'])) {
					echo "<br>Prosím vyberte aspoň jednu kategorii!";
				} else {
					
					$number = $_POST['number'];
					$content = htmlspecialchars($_POST['content']);
					$link = htmlspecialchars($_POST['link']);
					$categories = $_POST['cat'];
					
					foreach($categories as $cat){
						
						$cat = htmlspecialchars($cat);
						
						$stmt = $db->prepare('select * from category where name=?');
						$stmt->bind_param("s", $cat);
						$stmt->execute();
						$res = $stmt->get_result();
						$stmt->close();
						
						if(!$res->fetch_assoc()){
							
							$stmt = $db->prepare('insert into category(name) values (?)');
							$stmt->bind_param("s", $cat);
							$stmt->execute();
							$stmt->close();
							
						}
						
					}
					
					$stmt = $db->prepare('insert into NumberInfo(number, content, link, imgSrc, createdBy, createdTime, approved) values (?, ?, ?, ?, ?, now(), false)');
					$stmt->bind_param("isssi", $number, $content, $link, $image, $_SESSION['user']['id']);
					$stmt->execute();
					$stmt->close();
					
					$id = $db->insert_id;
					
					foreach($categories as $cat){
						
						$stmt = $db->prepare('insert into infocat(infoId, catId) values (?, (select id from category where name=?))');
						$stmt->bind_param("ss", $id, $cat);
						$stmt->execute();
						$stmt->close();
						
					}
					
					header('Location: /index.php');
					
				}
				
			}
			
		?>
		
		<form method="POST" enctype="multipart/form-data">
			
			Číslo: <input type="number" name="number" value="<?php if($_SERVER['REQUEST_METHOD']==='POST') echo $_POST['number'] ?>"></input>
			<br>Popis:
			<br><textarea name="content"><?php if($_SERVER['REQUEST_METHOD']==='POST') echo $_POST['content'] ?></textarea>
			<br>Odkaz: <input type="text" name="link" value="<?php if($_SERVER['REQUEST_METHOD']==='POST') echo $_POST['link'] ?>"></input>
			<br>Obrázek: <input type="file" name="image" accept="image/*"></input>
			<?php if($_SERVER['REQUEST_METHOD']==='POST') echo $_FILES['image']['name'] ?>
			<input type="hidden" name="altImage" value="<?php if($_SERVER['REQUEST_METHOD']==='POST') echo $_FILES['image']['name'] ?>"></input>
			<br>Kategorie:
			<script>
				
				function addCat(){
					var name = newCatName.value;
					if(name.trim().length>0){
						var inp = document.createElement('input');
						var br = document.createElement('br');
						var span = document.createElement('span');
						span.innerText = name;
						inp.type = 'checkbox';
						inp.name = 'cat[]';
						inp.value = name;
						inp.checked = true;
						inp.innerHTML = name;
						//catField.innerHTML += "<br>";
						catField.appendChild(br);
						catField.appendChild(inp);
						catField.appendChild(span);
						//catField.innerHTML += name;
						newCatName.value = "";
					}
				}
				
			</script>
			<br><input id="newCatName"></input><button type="button" onclick="addCat();">+</button>
			<br><div id="catField">
				<?php
					
					$stmt = $db->prepare('select name from category');
					$stmt->execute();
					$res = $stmt->get_result();
					$stmt->close();
					
					while($row = $res->fetch_assoc()){
						$name = $row['name'];
						?><br><input type="checkbox" name="cat[]" value="<?php echo $name ?>"
							<?php if($_SERVER['REQUEST_METHOD']==='POST'&&isSet($_POST['cat'])) if(in_array($name, $_POST['cat'])) echo 'checked' ?>><?php echo $name ?></input><?php
					}
					
				?>
			</div>
			
			<br><input value="Přidat" type="submit"></input>
			
		</form>
		
    </body>

</html>
<?php
$db->close();
?>