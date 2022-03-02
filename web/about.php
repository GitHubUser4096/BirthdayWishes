<?php
/*
 * Stránka O webu
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
				background: #e6e2d7;
				/* height: calc(100% - 80px); */
				position: absolute;
				width: 100%;
				overflow: auto;
				padding-bottom: 10px;
			}

			.main {
				background: #f3eee3;
				margin-left: 10%;
				width: 80%;
				/* height: 100%; */
				margin-top: 40px;
				padding-left: 10px;
				padding-right: 10px;
			}

			@media only screen and (max-width: 1000px) {

				/* .content {
					height: calc(100% - 60px);
				} */

				.video {
					width: 100%;
					height: calc(100% * (16 / 9));
				}

			}

			@media only screen and (max-height: 500px) {

				/* .content {
					height: calc(100% - 60px);
				} */

			}

		</style>

	</head>

    <body>

			<?php include('php/titlebar.php'); ?>

			<div class="content">
				<div class="main">
					<h1>O Webu</h1>
					<p>Tento web vám rychle vygeneruje narozeninové přání dle toho, kolikáté narozeniny oslavenec slaví a jaké jsou jeho zájmy. Obsahuje řadu zajímavostí spojených s čísly a oblastmi zájmů. Například “Píseň Echoes od skupiny Pink Floyd je dlouhá 23 minut” je zajímavost, kterou můžete do přání vybrat pro někoho, kdo slaví 23. narozeniny a má rád rockovou hudbu.</p>

					<p>Přání si můžete nechat vygenerovat zcela automaticky pouhým zadáním věku oslavence a jeho i vašeho jména. Pokud věnujete oslavenci více času, prohlédněte si zajímavosti, které web pro dané číslo nabízí a vyberte ty, které se mu budou nejvíce líbit. Můžete také vkládat vlastní zajímavosti a vytvořit přání velmi originální a na míru právě pro toho, koho máte rádi.</p>

					<p><b>WEB JE URČEN PRO VŠECHNY VĚKOVÉ SKUPINY A MÁ DĚLAT OSLAVENCŮM RADOST. PROTO PROSÍME NEVKLÁDEJTE PŘÁNÍ OBSAHUJÍCÍ URÁŽLIVÉ, ZLOMYSLNÉ, ODSUZUJÍCÍ NEBO JINAK NEVHODNÉ (EROTICKÉ, NÁSILNÉ ATD.) TEXTY ČI OBRÁZKY.</b></p>

					<h2>Videa</h2>
					<h3>Jak poslat přání</h3>
					<iframe width="560" height="315" src="https://www.youtube-nocookie.com/embed/ATSV3R6UhYc" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
					<h3>Jak sepsat zajímavost</h3>
					<iframe width="560" height="315" src="https://www.youtube-nocookie.com/embed/LMeX5YseOg4" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>

					<h2>Návody</h2>
						<h3>Jak vygenerovat přání</h3>
						<p>Pokyny pro generování zadáváte v levé (nebo horní)  části obrazovky, v pravé (nebo dolní) části pak vidíte, jak zatím generované přání vypadá.</p>
						<!-- <b>Postup:</b> -->
						<ol>
							<p><li>
								<ul>
									<li>Přihlásit se / zaregistrovat se (není třeba, pokud nechcete, aby se přání odeslalo automaticky).</li>
									<li>Kliknout na menu Vytvořit přání (Vpravo nahoře).</li>
								</ul>
							</li></p>
							<p><li>
								<ul>
									<li>Zadat číslo narozenin (kolonka “Pro kolikáté narozeniny”).</li>
									<li>Pokud chcete, necháte text přání generovaný webem (volba “Generovaný” v oblasti “Text přání”):</li>
									<ul>
										<li>Vyplnit, jak má být v přání oslavenec osloven (kolonka “Oslovení”).</li>
										<li>Vyplnit, jak chcete být pod přáním podepsaný (kolonka “Kdo přeje”).</li>
									</ul>
									<li>Pokud chcete text přání kompletně napsat sám/sama (volba Vlastní v oblasti Text přání):</li>
									<ul>
										<li>Vyplnit text (kolonka “Text”).</li>
									</ul>
									<li>Vybrat zájmy oslavence (oblast “Zájmy oslavence”). Web zařadí do přání pouze zajímavosti, které se vztahují k vybraným zájmům. Můžete také zaškrtnout “Vybrat všechny” (hned první řádek seznamu), pak bude výběr zajímavostí náhodný.</li>
									<li>Stisknout tlačítko “Další”.</li>
								</ul>
							</li></p>
							<p><li><ul>
								<li>Pokud chcete, aby výběr zajímavostí proběhl automaticky (volba “Vybrat náhodně” v oblasti “Zajímavosti”):</li>
								<ul>
									<li>Vyplnit, kolik zajímavostí má v přání být (kolonka “Kolik zajímavostí vybrat”).</li>
									<li>Stisknout tlačítko “Vybrat náhodně”.</li>
								</ul>
								<li>Pokud chcete vybrat zajímavosti sám/sama (volba “Vyberu sám” v oblasti “Zajímavosti”):</li>
								<ul>
									<li>V oblasti “Na výběr” vám web nabídne dostupné zajímavosti (pro zadané číslo narozenin a zadané zájmy).</li>
									<li>Zajímavosti, které chcete umístit do přání vybrat kliknutím na zelenou šipku. Vybraná zajímavost se přesune do oblasti “Vybrané” a zobrazí se v náhledu. V oblasti “Vybrané” můžete měnit pořadí zajímavostí nebo zajímavost z přání zase odebrat.</li>
								</ul>
								<li>Stisknout tlačítko “Vytvořit”.
							</ul></li></p>
							<p><li><ul>
								<li>Vyčkat několik sekund, než se přání vygeneruje.</li>
								<li>Pokud si chcete přání stáhnout:</li>
								<ul>
									<li>Stisknout tlačítko “Stáhnout PDF”.</li>
								</ul>
								<li>Pokud chcete zadat odeslání e-mailem:</li>
								<ul>
									<li>Stisknout tlačítko “Odeslat přání”</li>
									<li>Vyplnit e-mail (chcete-li, vyplňte i skrytou adresu pro kopii - typicky vaší, abyste měli(a) kontrolu, že e-mail byl odeslán a jak vypadal).</li>
									<li>Pokud chcete e-mail odeslat ihned:</li>
									<ul>
										<li>Vybrat v oblasti “Kdy odeslat e-mail” volbu “Ihned”.</li>
										<li>Stisknout tlačítko “Odeslat”.</li>
									</ul>
									<li>Pokud chcete odeslání e-mailu načasovat:</li>
									<ul>
										<li>Vybrat v oblasti “Kdy odeslat e-mail” volbu “Zadat datum”.</li>
										<li>Vyplnit kolonku “Datum”. E-mail bude odeslán během dopoledne zadaného dne.</li>
										<li>Potvrdit vybrané datum - Stisknout tlačítko “Uložit”.</li>
									</ul>
								</ul>
							</ul></li></p>
						</ol>

						<h3>Jak vložit zajímavost</h3>
						<p>Zajímavost se vždy vztahuje ke konkrétnímu číslu, které musíte uvést. Dále se skládá z textu, obrázku a URL odkazu (ten lze rozkliknout z vygenerovaného přání). Připravte si tedy text, obrázek (není povinný) a odkaz (není povinný) předem.</p>
						<ol>
							<p><li><ul>
								<li>Přihlásit se / zaregistrovat se.</li>
								<li>Kliknout na rozbalovací menu vpravo nahoře (uživatelské jméno) a vybrat volbu “Přidat zajímavost”.</li>
							</ul></li></p>
							<p><li><ul>
								<li>Vyplnit číslo, k němuž se zajímavost vztahuje (kolonka “Číslo”).</li>
								<li>Vyplnit text zajímavosti (kolonka “Popis”).</li>
								<li>Vyplnit URL, která půjde z vygenerovaného přání ze zajímavosti rozkliknout (kolonka “Odkaz”).</li>
								<li>Vložit obrázek (kliknout na tlačítko “Vybrat soubor”).</li>
								<li>Pokud víte, zadat zdroj obrázu (kolonka “Zdroj/autor obrázku”. Prosíme nevkládejte obrázky v rozporu s autorskými právy).</li>
								<li>V oblasti Kategorie vybrat kategorie, jichž se zajímavost týká. Pokud jste danou kategorii nenašel(la) vyplňte ji do kolonky “Kategorie” a stiskněte tlačítko Plus (“+”) vpravo od kolonky.</li>
							</ul></li></p>
							<p><li><ul>
								<li>Stisknout tlačítko Přidat.</li>
							</ul></li></p>
						</ol>

					<p>Děkujeme, že nám pomáháte rozvíjet obsah webu!</p>

					<h2>Kontakty:</h2>
					<p>Michal Klačer
						<ul>
							<li><a class="link" href="https://nggcv.cz">nggcv.cz</a></li>
							<li><a class="link">michal@narozeninovaprani.net</a></li>
						</ul>
					</p>
					<hr>
					<a class="link" href="/">Zpět</a>
					<p>© Michal Klačer, 2021</p>
				</div>
			</div>

    </body>

</html>
