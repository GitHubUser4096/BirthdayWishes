<?php
session_start();
require_once('db.php');

$db = DB_CONNECT('wishes');

if(!isSet($_SESSION['wish'])){
	die('400 - Bad request');
}

$wish = $_SESSION['wish'];

if($_SERVER['REQUEST_METHOD']==='POST'){
	
	if($wish['choose']=='random'){
		
		if($_POST['random_count']<=0){
			$error = "<br>Prosím zadejte číslo větší než 0!";
		} else {
			$wish['random_count'] = $_POST['random_count'];
		}
		
	} else if($wish['choose']=='list'){
		
		if(!isSet($_POST['choice'])){
			$error = "<br>Prosím vyberte aspoň jednu zajímavost!";
		} else {
			$wish['choice'] = $_POST['choice'];
		}
		
	}
	
	if(!isSet($error)){
		$_SESSION['wish'] = $wish;
		header('Location: wish.php');
	}
	
}

?>
<!doctype html>
<html>

	<head>
		
		<title>Vytvořit přání</title>
		
		<style>
			
			img {
				max-width: 100px;
			}
			
			table {
				border-collapse: collapse;
			}
			
			td {
				border: solid 1px;
			}
			
		</style>
		
	</head>

    <body>
	
		Vytvořit přání
		
		<form method="POST">
			
			<?php
				
				if(isSet($error)){
					echo $error;
				}
				
				if($wish['choose']=='random') {
					
					?><br>Počet zajímavostí: <input type="number" name="random_count" value=1></input><?php
					
				} else if($wish['choose']=='list') {
					
					?>
					<div id="choiceField">
						<table>
						<?php
							
							$num = $wish['bdayNumber'];
							$quoted = [];
							foreach($wish['cat'] as $catname) {
								$quoted[count($quoted)] = "'".$catname."'";
							}
							$catnames = '('.implode(",", $quoted).')';
							
							$stmt = $db->prepare('select distinct numberinfo.id, content, imgSrc from infocat '.
									'inner join numberinfo on numberinfo.id=infoid inner join category on category.id=catid '.
									'where numberinfo.number=? and category.name in '.$catnames.' and numberinfo.approved=true');
							$stmt->bind_param("i", $num);
							$stmt->execute();
							$res = $stmt->get_result();
							$stmt->close();
							
							while($row = $res->fetch_assoc()){
								
								?>
								<tr>
									<td><input type="checkbox" name="choice[]" value="<?php echo $row['id'] ?>"></input></td>
									<td><?php echo $row['content'] ?></td>
									<td><img src="<?php echo $row['imgSrc'] ?>"></img></td>
								</tr>
								<?php
								
							}
							
						?>
						</table>
					</div>
					<?php
					
				}
				
			?>
			
			<br><input value="Vytvořit" type="submit"></input>
			
		</form>
		
    </body>

</html>
<?php
$db->close();
?>