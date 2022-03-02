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

		<title>Moje přání</title>

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

			table {
				width: 100%;
			}

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
				height: calc(100vh - 80px - 41px - 40px);
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
					height: calc(100vh - 60px - 41px - 60px);
				}

			}

			@media only screen and (max-height: 500px) {

				.content {
					height: calc(100% - 60px);
				}

				.tableDiv {
					height: calc(100vh - 60px - 41px - 60px);
				}

			}

		</style>

	</head>

    <body>

		<?php include('php/titlebar.php'); ?>

		<?php

		$stmt = $db->prepare("select value from Config where name='wishAccessTime'");
		$stmt->execute();
		$res = $stmt->get_result();
		$availDays = $res->fetch_assoc()['value'];
		$stmt->close();

		$stmt = $db->prepare('select uid, number, preview_text, date_created, lastEdited, mail_date, mail_sent from Wish where userId=? and ifnull(mail_sent, 0)=0 and not deleted');
		$stmt->bind_param("i", $_SESSION['user']['id']);
		$stmt->execute();
		$res = $stmt->get_result();
		$stmt->close();

		$row = $res->fetch_assoc();

		?>

		<div class="content">

			<?php if($row) { ?>
				<div class="warn">
						Přání, u kterých není nastavený den odeslání, jsou dostupné do <?php echo $availDays; ?> dnů od poslední úpravy.
				</div>
			<?php } ?>

			<div class="subtitlebar">
				<a class="backbtn" href="index.php"><</a><span class="subtitle">Moje přání</span>
				<a href="create_wish.php"><button class="addbtn">+</button></a>
			</div>

		<div class="tableDiv">
			<?php

			if(!$row){
				?><div class="warn">Nenalezena žádná přání.</div><?php
			} else {

			?>
			<table>
					<tr>
							<th>Narozeniny</th>
							<th>Text přání</th>
							<th>Vytvořeno</th>
							<th>Upraveno</th>
							<th>Stav</th>
							<th>Zobrazit/Upravit</th>
						</tr>
							<?php

								while($row){
									?>
									<tr class="tableRow">
										<td><?php echo htmlspecialchars($row['number']); ?></td>
										<td><?php echo htmlspecialchars($row['preview_text']); ?></td>
										<td><?php echo htmlspecialchars($row['date_created']); ?></td>
										<td><?php echo htmlspecialchars($row['lastEdited']); ?></td>
										<td><?php echo htmlspecialchars($row['mail_sent']=='1'?'Odesláno':($row['mail_date']?('Bude odesláno '.$row['mail_date']):'Neodesláno')); ?></td>
										<td><a class="editbtn" href="create_wish.php?uid=<?php echo $row['uid'] ?>">Zobrazit/Upravit</a></td>
									</tr>
									<?php
									$row = $res->fetch_assoc();
								}

							?>
		</table>
	<?php } ?>
	</div>

		</div>

    </body>

</html>
<?php
$db->close();
?>
