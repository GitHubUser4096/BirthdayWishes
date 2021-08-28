<?php
session_start();
require_once('db.php');

$db = DB_CONNECT('wishes');

if(!isSet($_SESSION['user']) || !$_SESSION['user']['admin']) {
	die('401 - Unauthorized');
}

?>
<!doctype html>
<html>

	<head>
		
		<title>Spravovat zajímavosti</title>
		
		<style>
			
			table {
				border-collapse: collapse;
			}
			
			th, td {
				border: solid 1px;
			}
			
			img {
				max-width: 100px;
			}
			
		</style>
		
	</head>

    <body>
	
		Spravovat zajímavosti
		
		<br><a href="index.php">Zpět</a>
		
		<table>
			<thead>
				<tr>
					<th>Číslo</th>
					<th>Popis</th>
					<th>Obrázek</th>
					<th>Kategorie</th>
					<th>Vytvořil</th>
					<th>Potvrzeno</th>
					<th>Upravit</th>
				</tr>
			</thead>
			<tbody>
				<?php
					
					$stmt = $db->prepare('select id, number, content, imgSrc, createdBy, createdTime, approved from numberinfo order by number');
					$stmt->execute();
					$res = $stmt->get_result();
					$stmt->close();
					
					while($row = $res->fetch_assoc()){
						?>
						<tr>
							<td><?php echo $row['number']; ?></td>
							<td><?php echo $row['content']; ?></td>
							<td><img src="<?php echo $row['imgSrc']; ?>"></img></td>
							<td><?php
								
								$stmt = $db->prepare('select name from infocat inner join category on category.id=catid where infoid=?');
								$stmt->bind_param('i', $row['id']);
								$stmt->execute();
								$res2 = $stmt->get_result();
								$stmt->close();
								
								while($row2 = $res2->fetch_assoc()){
									echo $row2['name'].'<br>';
								}
								
							?></td>
							<td><?php
								
								$stmt = $db->prepare('select username from user where id=?');
								$stmt->bind_param('i', $row['createdBy']);
								$stmt->execute();
								$res2 = $stmt->get_result();
								$stmt->close();
								
								while($row2 = $res2->fetch_assoc()){
									echo $row2['username'].'<br>';
								}
								
								echo $row['createdTime'];
								
							?></td>
							<td><input type="checkbox" <?php if($row['approved']) echo 'checked'; ?> disabled></input></td>
							<td><a href="edit_info.php?id=<?php echo $row['id'] ?>">Upravit</a></td>
						</tr>
						<?php
					}
					
				?>
			</tbody>
		</table>
		
		<a href="add_info.php">Přidat</a>
		
    </body>

</html>
<?php
$db->close();
?>