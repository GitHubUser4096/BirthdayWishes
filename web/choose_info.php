<?php
session_start();
require_once('php/db.php');

$db = DB_CONNECT();

if(!isSet($_SESSION['wish'])){
	die('400 - Bad request');
}

$wish = $_SESSION['wish'];

if($_SERVER['REQUEST_METHOD']==='POST'){
	
	if($wish['choose']=='random'){
		
		if($_POST['random_count']<=0){
			$error = "Prosím zadejte číslo větší než 0!";
		} else {
			$wish['random_count'] = $_POST['random_count'];
		}
		
	} else if($wish['choose']=='list'){
		
		if(!isSet($_POST['choice'])){
			$error = "Prosím vyberte aspoň jednu zajímavost!";
		} else {
			$wish['choice'] = $_POST['choice'];
		}
		
	}
	
	if(!isSet($error)){
		$_SESSION['wish'] = $wish;
		$_SESSION['docname'] = null;
		header('Location: wish.php');
	}
	
}

?>
<!doctype html>
<html>

	<head>
		
		<title>Vytvořit přání</title>
		
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
				width: 18px;
				height: 18px;
			}
			
			img {
				max-width: 100px;
			}
			
			table {
				border-collapse: collapse;
				width: 100%;
			}
			
			td {
				/*border: solid 1px;
				border-bottom: solid 1px;
				border-top: solid 1px;
				background: #e6e2d7;*/
			}
			
			tr {
				border: solid 1px;
				background: #e6e2d7;
			}
			
		</style>
		
	</head>

    <body>
		
		<?php include('php/titlebar.php'); ?>
		
		<div class="content">
			
			<div class="subtitlebar">
				<div class="backbtn"><a href="create_wish.php"><</a></div><div class="subtitle">Vytvořit přání</div>
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
					
					<?php
						
						if($wish['choose']=='random') {
							
							?><div class="leftcol">
								<div class="formrow">
									<span class="formlbl">Počet zajímavostí:</span>
									<input class="formin" type="number" name="random_count" value=1></input>
								</div>
								<div class="formrow"><input class="bigbutton" value="Vytvořit >" type="submit"></input></div>
							</div><?php
							
						} else if($wish['choose']=='list') {
							
							?>
							<div class="formrow" id="choiceField">
								<span class="formlbl">Vyberte zajímavosti:</span>
								<table>
								<?php
									
									$num = $wish['bdayNumber'];
									$quoted = [];
									foreach($wish['cat'] as $catname) {
										$quoted[count($quoted)] = "'".$catname."'";
									}
									$catnames = '('.implode(",", $quoted).')';
									
									$stmt = $db->prepare('select distinct NumberInfo.id, content, imgSrc from InfoCat '.
											'inner join NumberInfo on NumberInfo.id=infoid inner join Category on Category.id=catid '.
											'where NumberInfo.number=? and Category.name in '.$catnames.' and NumberInfo.approved=true');
									if($stmt) {
										$stmt->bind_param("i", $num);
										$stmt->execute();
										$res = $stmt->get_result();
										$stmt->close();
										
										while($row = $res->fetch_assoc()){
											
											?>
											<tr>
													<td><input id="row<?php echo $row['id'] ?>" class="check" type="checkbox" name="choice[]" value="<?php echo $row['id'] ?>"></input></td>
													<td><label for="row<?php echo $row['id'] ?>"><?php echo $row['content'] ?></label></td>
													<td><label for="row<?php echo $row['id'] ?>"><img src="<?php echo $row['imgSrc'] ?>"></img></label></td>
											</tr>
											<?php
											
										}
									}
									
								?>
								</table>
							</div>
							<div class="formrow"><input class="bigbutton" value="Vytvořit >" type="submit"></input></div>
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