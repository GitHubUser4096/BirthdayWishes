<?php
/*
 * Projekt: Narozeninová přání
 * Vytvořil: Michal
 */
session_start();

if(!isSet($_SERVER['HTTPS'])){
	header("Location: https://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);
	exit;
}

require_once('php/db.php');

$db = DB_CONNECT();

if(!isSet($_SESSION['user'])) {
	header('Location: login.php?page=user_info_mgmt.php');
	exit;
}

if(!$_SESSION['user']['verified']){
	die('Účet není ověřen!');
}

?>
<!doctype html>
<html lang="cs">

	<head>

		<title>Spravovat zajímavosti</title>

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
			}

			.col1 { width: 35px; }
			.col2 { width: auto; }
			.col3 { width: 100px; }
			.col4 { width: 200px; }
			.col5 { width: 100px; }
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

			.warn {
				padding: 10px;
				font-weight: bold;
				font-size: 18px;
				color: white;
				background: #EECC00;
			}

			.tableDiv {
				width: 100%;
				height: calc(100vh - 80px - 41px);
				overflow: auto;
			}

			td, th {
				padding: 5px;
			}

			@media only screen and (max-width: 600px) {

				.content {
					height: calc(100% - 60px);
				}

				.tableDiv {
					height: calc(100vh - 60px - 41px);
				}

			}

			@media only screen and (max-height: 500px) {

				.content {
					height: calc(100% - 60px);
				}

				.tableDiv {
					height: calc(100vh - 60px - 41px);
				}

			}

		</style>

	</head>

    <body>

		<?php include('php/titlebar.php'); ?>

		<div class="content">

			<div class="subtitlebar">
				<a class="backbtn" href="index.php"><</a><span class="subtitle">Spravovat zajímavosti</span>
				<a href="add_info.php"><button class="addbtn">+</button></a>
			</div>

			<div class="tableDiv">

				<?php

				$stmt = $db->prepare('select id, number, content, imgSrc, createdBy, createdTime, state from NumberInfo where createdBy=? order by number');
				$stmt->bind_param("i", $_SESSION['user']['id']);
				$stmt->execute();
				$res = $stmt->get_result();
				$stmt->close();

				$row = $res->fetch_assoc();

				if(!$row){
					?><div class="warn">Nenalezeny žádné zajímavosti.</div><?php
				} else {

				?>

					<table>
						<thead>
							<tr>
								<th class="col1">Číslo</th>
								<th class="col2">Popis</th>
								<th class="col3">Obrázek</th>
								<th class="col4">Kategorie</th>
								<th class="col5">Vytvořil</th>
								<th class="col6">Stav</th>
								<th class="col7">Upravit</th>
							</tr>
						</thead>
						<tbody>
							<?php

								while($row){
									?>
									<tr>
										<td class="col1"><?php echo $row['number']; ?></td>
										<td class="col2"><?php echo $row['content']; ?></td>
										<td class="col3"><img src="<?php echo $row['imgSrc']; ?>"></img></td>
										<td class="col4"><?php

											$stmt = $db->prepare('select name from InfoCat inner join Category on Category.id=catid where infoid=?');
											$stmt->bind_param('i', $row['id']);
											$stmt->execute();
											$res2 = $stmt->get_result();
											$stmt->close();

											while($row2 = $res2->fetch_assoc()){
												echo $row2['name'].'<br>';
											}

										?></td>
										<td class="col5"><?php

											$stmt = $db->prepare('select username from User where id=?');
											$stmt->bind_param('i', $row['createdBy']);
											$stmt->execute();
											$res2 = $stmt->get_result();
											$stmt->close();

											while($row2 = $res2->fetch_assoc()){
												echo $row2['username'].'<br>';
											}

											echo $row['createdTime'];

										?></td>
										<td class="col6" style="text-align:center;">
											<?php
												if($row['state']=='pending') echo '<img title="Před schválením" src="res/pending.png"></img>';
												else if($row['state']=='approved') echo '<img title="Schváleno" src="res/approved.png"></img>';
												else if($row['state']=='dismissed') echo '<img title="Zamítnuto" src="res/dismissed.png"></img>';
											?>
										</td>
										<td class="col7"><a class="editbtn" href="edit_user_info.php?id=<?php echo $row['id'] ?>">Upravit</a></td>
									</tr>
									<?php
									$row = $res->fetch_assoc();
								}

							?>
						</tbody>
					</table>

			<?php } ?>

			</div>

		</div>

    </body>

</html>
<?php
$db->close();
?>
