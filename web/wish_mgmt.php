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
	header('Location: login.php?page=user_info_mgmt.php');
}

?>
<!doctype html>
<html>

	<head>
		
		<title>Moje přání</title>
		
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		
		<link rel="icon" href="res/cake.png">
		<link rel="stylesheet" href="css/page.css">
		<link rel="stylesheet" href="css/titlebar.css">
		<script src="js/titlebar.js"></script>
		
		<!--link rel="stylesheet" href="css/form_page.css">
		<link rel="stylesheet" href="css/form.css"-->
		
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
			
			.col1 { width: 100px; }
			.col2 { width: auto; }
			.col3 { width: 160px; }
			.col4 { width: 100px; }
			.col5 { width: 120px; }
			.col6 { width: 70px; }
			.col7 { width: 80px; }
			
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
			
			.addbtn {
				float: right;
				margin: 5px;
				width: 30px;
				height: 30px;
				font-size: 20px;
				border: none;
				background: #2edc15;
				color: white;
				cursor: pointer;
			}
			
			.addbtn:hover {
				background: #7be96c;
			}
			
		</style>
		
	</head>

    <body>
		
		<?php include('php/titlebar.php'); ?>
		
		<div class="content">
			
			<div class="subtitlebar">
				<a class="backbtn" href="index.php"><</a><span class="subtitle">Moje přání</span>
				<a href="create_wish.php"><button class="addbtn">+</button></a>
			</div>
			
			<div class="tablecont">
				
				<div class="tableheadcont">
					<table>
						<thead>
							<tr>
								<th class="col1">Narozeniny</th>
								<th class="col2">Text přání</th>
								<th class="col3">Datum vytvoření</th>
								<th class="col4">Stav</th>
								<th class="col5">Zobrazit/Upravit</th>
							</tr>
						</thead>
					</table>
				</div>
				<div class="tablebodycont">
					<table>
						<tbody>
							<?php
								
								$stmt = $db->prepare('select uid, number, preview_text, date_created, mail_date, mail_sent from Wish where userId=?');
								$stmt->bind_param("i", $_SESSION['user']['id']);
								$stmt->execute();
								$res = $stmt->get_result();
								$stmt->close();
								
								while($row = $res->fetch_assoc()){
									?>
									<tr>
										<td class="col1"><?php echo $row['number']; ?></td>
										<td class="col2"><?php echo $row['preview_text']; ?></td>
										<td class="col3"><?php echo $row['date_created']; ?></td>
										<td class="col4"><?php echo $row['mail_sent']=='1'?'Odesláno':($row['mail_date']?('Bude odesláno '.$row['mail_date']):'Neodesláno'); ?></td>
										<td class="col5"><a class="editbtn" href="create_wish.php?uid=<?php echo $row['uid'] ?>">Zobrazit/Upravit</a></td>
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