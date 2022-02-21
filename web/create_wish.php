<?php
/*
 * Dynamický formulář pro vytvoření přání s náhledem přání
 * Projekt: Narozeninová přání
 * Vytvořil: Michal
 */
session_start();

if(!isSet($_SERVER['HTTPS'])){
	header("Location: https://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);
	exit;
}

?>
<!doctype html>
<html lang="cs">

	<head>

		<title>Vytvořit přání</title>

		<meta name="viewport" content="width=device-width, initial-scale=1.0">

		<link rel="icon" href="res/cake.png">

		<link rel="stylesheet" href="css/page.css">
		<link rel="stylesheet" href="css/controls.css">
		<link rel="stylesheet" href="css/titlebar.css">
		<link rel="stylesheet" href="css/form_page.css">
		<link rel="stylesheet" href="css/form.css">
		<link rel="stylesheet" href="css/wish.css">
		<link rel="stylesheet" href="css/create_wish.css">

		<script src="js/titlebar.js"></script>
		<script src="js/xhr.js"></script>
		<script src="js/form.js"></script>
		<script src="js/doubleList.js"></script>
		<script src="js/bagList.js"></script>
		<script src="js/wishPages.js"></script>
		<script src="js/canvasUtils.js"></script>
		<script src="js/create_wish.js"></script>

	</head>

    <body onload="main();">

		<?php include('php/titlebar.php'); ?>

		<div class="content">

			<div class="subtitlebar">
				<div class="backbtn"><a href="index.php"><</a></div><div class="subtitle">Vytvořit přání</div>
			</div>

			<div class="form">

				<div id="formContainer" class="formcontainer"></div>

				<div id="previewBox" class="previewbox"></div>

				<div class="zoomControls">
					<button class="zoomBtn zoomout" onclick="zoomout();"><img src="res/zoomout.png"></img></button>
					<button class="zoomBtn resetZoom" onclick="resetZoom();"><img src="res/zoom.png"></img></button>
					<button class="zoomBtn zoomin" onclick="zoomin();"><img src="res/zoomin.png"></img></button>
				</div>

			</div>

		</div>

    </body>

</html>
<?php

?>
