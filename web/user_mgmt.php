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

$db = DB_CONNECT();

if(!isSet($_SESSION['user'])||!$_SESSION['user']['verified']) {
	header('Location: login.php?page=user_mgmt.php');
}

if(!$_SESSION['user']['admin']) {
	die('401 - Unauthorized');
}

?>
<!doctype html>
<html>

	<head>
		
		<title>Spravovat uživatele</title>
		
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		
		<link rel="icon" href="res/cake.png">
		<link rel="stylesheet" href="css/page.css">
		<link rel="stylesheet" href="css/titlebar.css">
		<script src="js/titlebar.js"></script>
		
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
			
			.content {
				position: absolute;
				width: 100%;
				height: calc(100% - 80px);
				background: #e6e2d7;
				overflow: hidden;
			}
			
			.subtitlebar {
				width: 100%;
				height: 40px;
			}
			
			.tablecont {
				width: 100%;
				height: calc(100% - 40px);
			}
			
			.tableheadcont {
				width: 100%;
				height: 30px;
			}
			
			.tablebodycont {
				width: 100%;
				height: calc(100% - 40px);
				overflow-y: overlay;
			}
			
			table {
				width: 100%;
				height: 100%;
			}
			
			.col1 { width: auto; }
			.col2 { width: 250px; }
			.col3 { width: 100px }
			.col4 { width: 100px }
			
			.editbtn {
				text-decoration: underline;
			}
			
			.subtitlebar {
				font-size: 24px;
			}
			
			.subtitle {
				padding: 10px;
			}
			
			.backbtn {
				padding: 10px;
			}
			
		</style>
		
	</head>

    <body>
		
		<?php include('php/titlebar.php'); ?>
		
		<div class="content">
			
			<div class="subtitlebar">
				<a class="backbtn" href="index.php"><</a><span class="subtitle">Spravovat uživatele</span>
			</div>
			
			<div class="tablecont">
				
				<div class="tableheadcont">
					<table>
						<thead>
							<tr>
								<th class="col1">Jméno</th>
								<th class="col2">E-mail</th>
								<th class="col3">Admin</th>
								<th class="col4">Upravit</th>
							</tr>
						</thead>
					</table>
				</div>
				<div class="tablebodycont">
					<table>
						<tbody>
							<?php
								
								$stmt = $db->prepare('select id, username, email, admin from User');
								$stmt->execute();
								$res = $stmt->get_result();
								$stmt->close();
								
								while($row = $res->fetch_assoc()){
									?>
									<tr>
										<td class="col1"><?php echo $row['username']; ?></td>
										<td class="col2"><?php echo $row['email']; ?></td>
										<td class="col3"><input type="checkbox" <?php if($row['admin']) echo 'checked'; ?> disabled></input></td>
										<td class="col4">
											<?php if($row['id']==$_SESSION['user']['id']) { ?>
												<a class="editbtn" href="acc_mgmt.php">Spravovat účet</a>
											<?php } else { ?>
												<a class="editbtn" href="edit_user.php?id=<?php echo $row['id'] ?>">Upravit</a>
											<?php } ?>
										</td>
									</tr>
									<?php
								}
								
							?>
						</tbody>
					</table>
				</div>
				
			</div>
			
		</div>
		
    </body>

</html>
<?php
$db->close();
?>