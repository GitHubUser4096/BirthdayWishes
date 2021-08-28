<?php
session_start();
require_once('db.php');

$db = DB_CONNECT('wishes');

if(!isset($_GET['id'])) {
	die('400 - Bad request');
}

if(!isSet($_SESSION['user']) || !$_SESSION['user']['admin']) {
	die('401 - Unauthorized');
}

if($_SERVER['REQUEST_METHOD']==='POST'){
	
	if(isset($_POST['confirmDelete'])) {
		
		$stmt = $db->prepare('delete from infocat where infoid=?');
		$stmt->bind_param("i", $_GET['id']);
		$stmt->execute();
		$stmt->close();
		
		$stmt = $db->prepare('delete from numberinfo where id=?');
		$stmt->bind_param("i", $_GET['id']);
		$stmt->execute();
		$stmt->close();
		
		header('Location: /info_mgmt.php');
		
	} else if(isSet($_POST['save'])) {
		
		$image_name = $_FILES['imgSrc']['name'];
		
		if(strlen(trim($image_name))>0) {
			
			if(getimagesize($_FILES['imgSrc']['tmp_name'])!==false){ // file is a valid image
				if(move_uploaded_file($_FILES['imgSrc']['tmp_name'], "images/".$image_name)){ // file is successfully copied from tmp
					
					$image = "images/".$image_name;
					
					$stmt = $db->prepare('update numberinfo set imgSrc=? where id=?');
					$stmt->bind_param("si", $image, $_GET['id']);
					$stmt->execute();
					$stmt->close();
					
				}
			}
			
		}
		
		$approved = $_POST['approved']?1:0;
		
		$number = $_POST['number'];
		$content = htmlspecialchars($_POST['content']);
		$link = htmlspecialchars($_POST['link']);
		
		$stmt = $db->prepare('update numberinfo set number=?, content=?, link=?, approved=? where id=?');
		$stmt->bind_param("issii", $number, $content, $link, $approved, $_GET['id']);
		$stmt->execute();
		$stmt->close();
		
		$stmt = $db->prepare('delete from infocat where infoid=?');
		$stmt->bind_param("i", $_GET['id']);
		$stmt->execute();
		$stmt->close();
		
		foreach($_POST['cat'] as $cat) {
			
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
			
			$stmt = $db->prepare('insert into infocat(infoid, catid) values (?, (select id from category where name=?))');
			$stmt->bind_param("is", $_GET['id'], $cat);
			$stmt->execute();
			$stmt->close();
			
		}
		
		header('Location: /info_mgmt.php');
		
	}
	
}

?>
<!doctype html>
<html>

	<head>
		
		<title>Upravit zajímavost</title>
		
	</head>

    <body>
		
		Upravit zajímavost
		
		<br><a href="info_mgmt.php">Zpět</a>
		
		<form method="POST" enctype="multipart/form-data">
			
			<?php
				
				$stmt = $db->prepare('select number, content, link, imgSrc, approved from numberinfo where id=?');
				$stmt->bind_param("i", $_GET['id']);
				$stmt->execute();
				$res = $stmt->get_result();
				$stmt->close();
				
				$row = $res->fetch_assoc();
				
			?>
			
			<br>Číslo: <input type="text" name="number" value="<?php echo $row['number'] ?>"></input>
			<br>Popis:<br><textarea name="content"><?php echo $row['content'] ?></textarea>
			<br>Odkaz: <input type="text" name="link" value="<?php echo $row['link'] ?>"></input>
			<br>Obrázek: <input type="file" name="imgSrc"></input>
			<br>Potvrzeno: <input type="checkbox" name="approved" <?php if($row['approved']) echo 'checked'; ?>></input>
			
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
						catField.appendChild(br);
						catField.appendChild(inp);
						catField.appendChild(span);
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
						?><br><input type="checkbox" name="cat[]" <?php
							
							$stmt = $db->prepare('select * from infocat inner join category on category.id=catid where infoid=? and name=?');
							$stmt->bind_param('is', $_GET['id'], $row['name']);
							$stmt->execute();
							$res2 = $stmt->get_result();
							$stmt->close();
							
							if($res2->fetch_assoc()) echo 'checked';
							
						?> value="<?php echo $name ?>"><?php echo $name ?></input><?php
					}
					
				?>
			</div>
			
			<br><input type="submit" name="save" value="Uložit"></input>
			<br><input type="submit" name="delete" value="Odstranit"></input>
			
			<?php
				
				if(isSet($_POST['delete'])){
					?>
					
					<br>Potvrdit odstranění
					<input type="submit" name="confirmDelete" value="Potvrdit"></input>
					<input type="submit" name="cancelDelete" value="Zrušit"></input>
					
					<?php
				}
				
			?>
			
		</form>
		
    </body>

</html>
<?php
$db->close();
?>