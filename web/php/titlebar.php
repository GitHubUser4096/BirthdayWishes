<?php
/*
 * Projekt: Narozeninová přání
 * Vytvořil: Michal
 */
?>
<div class="titlebar">
	<a id="titlea" class="titlea" href="index.php">
		<div class="icon"><img class="iconimg" src="res/cake.png" alt="Ikona webu"></img></div>
		<div class="title">Narozeninová Přání</div>
	</a>
	<div class="menu" id="menu">
		<div class="menuItem"><a href="create_wish.php"><span class="menuTextMode">Vytvořit přání</span><img width=20 height=20 title="Vytvořit přání" alt="Vytvořit přání" class="menuImgMode" src="res/create_wish.png"></img></a></div>
		<?php if(isSet($_SESSION['user']) && $_SESSION['user']['verified']) { ?>
			<div class="vsep">|</div>
			<div class="menuItem"><a href="add_info.php"><span class="menuTextMode">Přidat zajímavost</span><img width=20 height=20 title="Přidat zajímavost" alt="Přidat zajímavost" class="menuImgMode" src="res/add_info.png"></img></a></div>
		<?php } ?>
		<?php if(isSet($_SESSION['user'])) { ?>
			<div class="vsep">|</div>
			<div class="menuItem" id="popupMenuBtn" onclick="togglePopupMenu();"><a><span class="menuTextMode userMenuBtn" id="usernameBtn"><span id="usernameText" title="<?php echo $_SESSION['user']['username'] ?>"><?php echo $_SESSION['user']['username'] ?></span></span><img class="userMenuIcon" title="Menu" src="res/hamburger.png"></img></a></div>
		<?php } else { ?>
			<div class="vsep">|</div>
			<div class="menuItem"><a href="login.php"><span class="menuTextMode">Přihlásit se</span><img width=24 height=24 alt="Přihlásit se" class="menuImgMode" src="res/user.png"></img></a></div>
		<?php } ?>
	</div>
	<div id="popupMenu" class="popupMenu">
		<?php if(isSet($_SESSION['user']) && $_SESSION['user']['verified']) { ?>
			<div class="popupMenuItem">
				<a href="wish_mgmt.php">Moje přání<img width=20 height=20 alt="Moje přání" src="res/list_wishes.png" class="menuItemIcon"></img></a>
			</div>
			<!-- <div class="popupMenuItem">
				<a href="add_info.php">Přidat zajímavost<img width=20 height=20 alt="Přidat zajímavost" src="res/add_info.png" class="menuItemIcon"></img></a>
			</div> -->
			<div class="popupMenuItem">
				<a href="user_info_mgmt.php">Moje zajímavosti<img width=20 height=20 alt="Moje zajímavosti" src="res/list_infos.png" class="menuItemIcon"></img></a>
			</div>
		<?php } ?>
		<?php if(isSet($_SESSION['user']) && $_SESSION['user']['admin']) { ?>
			<div class="popupMenuItem">
				<a href="info_mgmt.php">Všechny zajímavosti<img width=20 height=20 alt="Všechny zajímavosti" src="res/list_infos.png" class="menuItemIcon"></img></a>
			</div>
			<div class="popupMenuItem">
				<a href="user_mgmt.php">Spravovat uživatele<img width=20 height=20 alt="Spravovat uživatele" src="res/user.png" class="menuItemIcon"></img></a>
			</div>
			<div class="popupMenuItem">
				<a href="edit_config.php">Konfigurace webu<img width=20 height=20 alt="Konfigurace" src="res/settings.png" class="menuItemIcon"></img></a>
			</div>
		<?php } ?>
		<div class="popupMenuItem">
			<?php if(isSet($_SESSION['user'])) echo $_SESSION['user']['username']; ?>:
		</div>
		<div class="popupMenuItem">
			<a href="acc_mgmt.php">Můj účet<img width=20 height=20 alt="Můj účet" src="res/settings.png" class="menuItemIcon"></img></a>
		</div>
		<div class="popupMenuItem">
			<a href="logout.php">Odhlásit se<img width=20 height=20 alt="Odhlásit se" src="res/exit.png" class="menuItemIcon"></img></a>
		</div>
	</div>
	<script>
		initTitlebar();
	</script>
</div>
