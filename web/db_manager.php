<?php
session_start();

if(!isSet($_SERVER['HTTPS'])){
	header("Location: https://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);
}

require_once('php/db.php');

$db = DB_CONNECT();

if(!isSet($_SESSION['user']) || !$_SESSION['user']['admin']) {
	die('401 - Unauthorized');
}

if($_SERVER['REQUEST_METHOD']==='POST') {
	
	$stmt = $db->prepare($_POST['query']);
	$stmt->execute();
	$res = $stmt->get_result();
	$stmt->close();
	
}

?>
<!doctype html>
<html>
	
	<head>
	</head>
	
	<body>
		
		<form method="post">
			<textarea name="query"></textarea>
			<input type="submit"></input>
		</form>
		
		<?php
			
			if(isSet($res) && $res){
				while($row = $res->fetch_assoc()){
					foreach($row as $entry){
						echo $entry;
					}
				}
			}
			
		?>
		
	</body>
	
</html>