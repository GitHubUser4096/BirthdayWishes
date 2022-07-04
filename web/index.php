<?php
/*
 * Úvodní stránka webu Narozeninová přání
 * Projekt: Narozeninová přání
 * Vytvořil: Michal
 */

session_set_cookie_params([
	'secure'=>true,
	'samesite'=>'None'
]);

session_start();

if(!isSet($_SERVER['HTTPS'])){
	header("Location: https://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);
	exit;
}

require_once('php/db.php');

$db = DB_CONNECT();

?>
<!doctype html>
<html lang="cs">

	<head>

		<title>Narozeninová Přání</title>

		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta charset="UTF-8">
		<meta name="description" content="Narozeninová přání">
		<meta name="keywords" content="Narozeniny, Přání, Zajímavosti">

		<link rel="icon" href="res/cake.png">
		<link rel="stylesheet" href="css/page.css">
		<link rel="stylesheet" href="css/controls.css">
		<link rel="stylesheet" href="css/titlebar.css">
		<script src="js/titlebar.js"></script>

		<style>

			.content {
				position: absolute;
				width: 100%;
				height: calc(100% - 80px);
				overflow: hidden;
			}

			.sidebar {
				width: 30%;
				height: 100%;
				overflow: auto;
				background: #e6e2d7;
				float: left;
			}

			.sidebarcontent {
				margin: 30px;
				font-size: 18px;
			}

			.sidebarmenu {
				margin: 30px;
			}

			p {
				font-size: 18px;
			}

			.slideshow {
				position: absolute;
				top: 0px;
				right: 0px;
				width: 70%;
				height: 100%;
				overflow: hidden;
			}

			.slidecontainer {
				width: calc(300% + 30px);
				height: 100%;
			}

			.slide {
				width: 100%;
				height: 100%;
			}

			.slideText {
				font-size: 24px;
				color: white;
				margin-left: 10%;
				margin-right: 10%;
				margin-top: 20px;
			}

			.slideImg {
				width: 80%;
				margin-left: 10%;
				margin-top: 20px;
			}

			.image {
				width: 100%;
			}

			.phoneBtn {
				display: none;
			}

			.indexBtn {
				width: calc(100% - 20px);
				margin: 10px;
			}

			.cookiePopup {
				display: none;
				background: rgba(0, 0, 0, .5);
				position: fixed;
				left: 0;
				top: 0;
				width: 100%;
				height: 100%;
			}

			.cookieBox {
				background: white;
				position: fixed;
				left: calc(50% - 350px);
				top: calc(50% - 250px);
				width: 700px;
				height: 500px;
				overflow: hidden;
			}

			.cookieTitle {
				background: #f3eee3;
			}

			.cookieBody {
				background: #e6e2d7;
				padding: 10px;
				position: absolute;
				width: calc(100% - 20px);
				height: calc(100% - 104px);
			}

			.cookieTitleImg {
				width: 64px;
				margin: 10px;
				vertical-align: bottom;
			}

			.cookieTitleText {
				display: inline-block;
				margin: 10px;
				line-height: 64px;
				font-size: 32px;
				font-weight: bold;
				vertical-align: top;
			}

			.acceptCookies {
				text-align: center;
				bottom: 10px;
				width: calc(100% - 20px);
				position: absolute;
			}

			.cookieSectionTitle {
				font-weight: bold;
			}

			.cookieSection {
				padding: 10px;
			}

			@media only screen and (max-width: 700px) {
				
				.cookieBox {
					left: 0;
					top: 0;
					width: 100%;
					height: 100%;
				}

				.cookieTitleImg {
					width: 32px;
					margin: 10px;
					vertical-align: bottom;
				}

				.cookieTitleText {
					display: inline-block;
					margin: 10px;
					line-height: 32px;
					font-size: 24px;
					font-weight: bold;
					vertical-align: top;
				}

				.cookieBody {
					background: #e6e2d7;
					padding: 10px;
					position: absolute;
					width: calc(100% - 20px);
					height: calc(100% - 72px);
					overflow-y: auto;
				}

				.acceptCookies {
					text-align: center;
					width: 100%;
					position: relative;
				}

			}

			@media only screen and (max-height: 500px) {
				
				.cookieBox {
					left: 0;
					top: 0;
					width: 100%;
					height: 100%;
				}

				.cookieTitleImg {
					width: 32px;
					margin: 10px;
					vertical-align: bottom;
				}

				.cookieTitleText {
					display: inline-block;
					margin: 10px;
					line-height: 32px;
					font-size: 24px;
					font-weight: bold;
					vertical-align: top;
				}

				.cookieBody {
					background: #e6e2d7;
					padding: 10px;
					position: absolute;
					width: calc(100% - 20px);
					height: calc(100% - 72px);
					overflow-y: auto;
				}

				.acceptCookies {
					text-align: center;
					width: 100%;
					position: relative;
				}

			}

			@media only screen and (max-width: 600px) {

				.content {
					position: absolute;
					width: 100%;
					height: calc(100% - 60px);
					overflow: auto;
				}

				.sidebar {
					width: 100%;
					height: auto;
					overflow: hidden;
					background: #e6e2d7;
					float: left;
				}

				.sidebarcontent {
					font-size: 20px;
				}

				.slideshow {
					position: relative;
					right: 0px;
					width: 100%;
					height: 60%;
					overflow: hidden;
				}

				.sidebarmenu {
					display: none;
					position: absolute;
					bottom: 0px;
					width: 100%;
					height: 10%;
				}

				.wishbtn {
					width: 100%;
				}

				.phoneBtn {
					display: block;
					padding: 20px;
					background: #e6e2d7;
				}

			}

			@media only screen and (max-height: 500px) {

				.content {
					height: calc(100% - 60px);
				}

			}

		</style>

		<script>

			function run(){

				var slides = document.querySelectorAll(".slide");
				var i = 1;

				setInterval(function(){

					if(slides.length>0) slideshow.scroll({left: slides[i%slides.length].offsetLeft, behavior: 'smooth'});
					i++;

				}, 5000);

				if(localStorage.getItem('acceptedCookies')!='true'){
					cookiePopup.style.display = 'block';
				}

				acceptCookies.onclick = function(){
					localStorage.setItem('acceptedCookies', 'true');
					cookiePopup.style.display = 'none';
				}

			}

			window.addEventListener('resize', function(e){
				slideshow.scroll({left: 0});
			});

		</script>

	</head>

	<body onload="run();">

		<?php include('php/titlebar.php'); ?>

		<div class="content">
			<div class="sidebar">
				<div class="sidebarcontent">
					<b>Chcete svému blízkému popřát k narozeninám netradičním způsobem?</b>
					<ul>
						<li>Sestavte si přání z vtipných i seriózních zajímavostí souvisejících s oslavencovým věkem a také jeho zájmy.</li>
						<li>Naplánujte odeslání přání dopředu a pusťte to z hlavy.</li>
						<li>Tvořte obsah webu s námi – zaregistrujte se a vkládejte vlastní zajímavosti.</li>
					</ul>
					<b>Je to opravdu jednoduché :)</b>

				</div>
				<div class="sidebarcontent">
					<a class="link" href="about.php">O Webu</a>
				</div>
				<div class="sidebarmenu">
					<a href="create_wish.php"><button class="bigbutton indexBtn">VYTVOŘIT PŘÁNÍ</button></a>
					<?php if(isSet($_SESSION['user']) && $_SESSION['user']['verified']) {?>
						<a href="add_info.php"><button class="bigbutton indexBtn">PŘIDAT ZAJÍMAVOST</button></a>
					<?php } ?>
				</div>
			</div>
			<div id="slideshow" class="slideshow">
				<div class="slidecontainer">
					<?php

						$stmt = $db->prepare("select * from NumberInfo where titlePage=1");
						$stmt->execute();
						$res = $stmt->get_result();
						$stmt->close();

						$i = 0;

						while($row = $res->fetch_assoc()){

							?><div class="slide" style="background:<?php echo $row['background']; ?>;position:absolute;left:<?php echo $i*101; ?>%;">
								<div class="slideText" style="color:<?php echo $row['color']; ?>"><?php echo $row['content']; ?></div>
								<div class="slideImg"><img alt="Obrázek zajímavosti" src="<?php echo $row['imgSrc']; ?>" class="image"></img></div>
							</div><?php

							$i++;

						}

					?>
				</div>
			</div>
			<div class="phoneBtn">
				<a href="create_wish.php"><button class="bigbutton wishbtn">VYTVOŘIT PŘÁNÍ</button></a>
			</div>
		</div>
		<div class="cookiePopup" id="cookiePopup">
			<div class="cookieBox">
				<div class="cookieTitle">
					<img class="cookieTitleImg" src="res/cake.png"></img><span class="cookieTitleText">Narozeninová přání</span>
				</div>
				<div class="cookieBody">
					<div class="cookieSectionTitle">Cookies:</div>
					<div class="cookieSection">Tento web využívá cookies, ukládá v nich však pouze identifikátor vaší komunikace se serverem.</div>
					<div class="cookieSectionTitle">Pravidla webu:</div>
					<ul>
						<li>Nebudu vkládat zajímavost, která by byla v rozporu s platnými zákony ČR</li>
						<li>Nebudu vkládat zajímavost, která by v textu či obrázku obsahovala neslušná slova</li>
						<li>Nebudu vkládat zajímavost, která by byla urážlivá na základě rasy, věku, pohlaví, zdravotního stavu apod.</li>
						<li>Web je určen pro všechny věkové kategorie, nebudu vkládat zajímavost, která by nebyla vhodná pro děti</li>
						<li>Pokud na webu najdu zajímavost, která je v rozporu s výše uvedenými pravidly, nepoužiji ji do generovaného přání. Mohu ji ohlásit administrátorovi webu - viz stránka O webu - Kontakty (v dolní části stránky)</li>
					</ul>
					<div class="acceptCookies">
						<button class="bigbutton acceptCookiesBtn" id="acceptCookies">Akceptuji výše uvedené cookies a budu dodržovat pravidla webu</button>
					</div>
				</div>
			</div>
		</div>

	</body>

</html>
<?php
$db->close();
?>
