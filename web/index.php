<?php
session_start();

?>
<!doctype html>
<html>

	<head>
		
		<title>Narozeninová Přání</title>
		
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		
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
				overflow: hidden;
				background: #e6e2d7;
				float: left;
			}
			
			.sidebarcontent {
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
				width: calc(100% / 3 - 10px);
				margin-right: 10px;
				height: 100%;
				float: left;
			}
			
			.slideimg {
				object-fit: contain;
				width: 100%;
				height: 100%;
			}
			
		</style>
		
		<script>
			
			function run(){
				
				var slides = document.querySelectorAll(".slide");
				var i = 1;
				
				setInterval(function(){
					
					slideshow.scroll({left: slides[i%slides.length].offsetLeft, behavior: 'smooth'});
					i++;
					
				}, 5000);
				
			}
			
		</script>
		
	</head>

    <body onload="run();">
		
		<?php include('php/titlebar.php'); ?>
		
		<div class="content">
			<div class="sidebar">
				<div class="sidebarcontent">
					<p>Web narozeninová přání ...</p>
					<a href="create_wish.php"><button class="bigbutton">VYTVOŘIT PŘÁNÍ ></button></a>
				</div>
			</div>
			<div id="slideshow" class="slideshow">
				<div class="slidecontainer">
					<div style="background: #003089;" class="slide"><img class="slideimg" src="res/info1.png"></img></div>
					<div style="background: #8d2722;" class="slide"><img class="slideimg" src="res/info2.png"></img></div>
					<div style="background: #000000;" class="slide"><img class="slideimg" src="res/info3.png"></img></div>
				</div>
			</div>
		</div>
		
    </body>

</html>