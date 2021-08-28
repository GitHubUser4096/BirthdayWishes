<?php
session_start();
require_once('db.php');

$db = DB_CONNECT('wishes');

if(!isSet($_SESSION['wish'])){
	die('400 - Bad request');
}

$wish = $_SESSION['wish'];

?>
<!doctype html>
<html>

	<head>
		
		<title>Přání</title>
		
		<style>
			
			img {
				max-width: 100%;
			}
			
		</style>
		
	</head>

    <body>
		
		<h1><?php echo htmlspecialchars($wish['for']) ?>,</h1>
		<h2><?php echo htmlspecialchars($wish['from']) ?> ti přeje všechno nejlepší k <b><?php echo htmlspecialchars($wish['bdayNumber']) ?></b>. narozeninám!</h2>
		<?php
			
			$num = $wish['bdayNumber'];
			
			$stmt = $db->prepare('select id, content, link, imgSrc from numberinfo where number=? and approved=true');
			$stmt->bind_param("i", $num);
			$stmt->execute();
			$res = $stmt->get_result();
			$stmt->close();
			
			$rows = [];
			
			while($row = $res->fetch_assoc()){
				$rows[count($rows)] = $row;
			}
			
			$toadd = [];
			
			if($wish['choose']=='random') {
				shuffle($rows);
				for($i = 0; $i<min(count($rows), $wish['random_count']); $i++){
					$toadd[count($toadd)] = $rows[$i];
				}
			} else if($wish['choose']=='list') {
				foreach($rows as $row) {
					if(in_array($row['id'], $wish['choice'])) {
						$toadd[count($toadd)] = $row;
					}
				}
			}
			
			foreach($toadd as $row) {
				
				?><p><?php echo $row['content'] ?></p>
				<a href="<?php echo $row['link'] ?>"><?php echo $row['link'] ?></a>
				<br><img src="<?php echo $row['imgSrc'] ?>"></img><?php
				
			}
			
		?>
		
    </body>

</html>
<?php
$db->close();
?>