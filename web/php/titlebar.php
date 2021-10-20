<?php
/*
 * Projekt: Narozeninová přání
 * Vytvořil: Michal
 */
?>
<div class="titlebar">
	<a id="titlea" class="titlea" href="index.php">
		<div class="icon"><img class="iconimg" src="res/cake.png"></img></div>
		<div class="title">Narozeninová Přání</div>
	</a>
	<div class="menu" id="menu">
		<div class="menuItem"><a href="create_wish.php"><span class="menuTextMode">Vytvořit přání</span><img class="menuImgMode" src="res/create_wish.png"></img></a></div>
		<?php if(isSet($_SESSION['user'])) { ?>
			| <div class="menuItem" id="popupMenuBtn" onclick="togglePopupMenu();"><img class="menuImg" src="res/user.png"></img></div>
		<?php } else { ?>
			| <div class="menuItem"><a href="login.php"><span class="menuTextMode">Přihlásit</span><img class="menuImgMode" src="res/user.png"></img></a></div>
		<?php } ?>
	</div>
	<div id="popupMenu" class="popupMenu">
		<?php if(isSet($_SESSION['user']) && $_SESSION['user']['verified']) { ?>
			<div class="popupMenuItem">
				<a href="wish_mgmt.php">Moje přání<img src="res/list_wishes.png" class="menuItemIcon"></img></a>
			</div>
			<div class="popupMenuItem">
				<a href="add_info.php">Přidat zajímavost<img src="res/add_info.png" class="menuItemIcon"></img></a>
			</div>
			<div class="popupMenuItem">
				<a href="user_info_mgmt.php">Moje zajímavosti<img src="res/list_infos.png" class="menuItemIcon"></img></a>
			</div>
		<?php } ?>
		<?php if(isSet($_SESSION['user']) && $_SESSION['user']['admin']) { ?>
			<div class="popupMenuItem">
				<a href="info_mgmt.php">Všechny zajímavosti<img src="res/list_infos.png" class="menuItemIcon"></img></a>
			</div>
			<div class="popupMenuItem">
				<a href="user_mgmt.php">Spravovat uživatele<img src="res/user.png" class="menuItemIcon"></img></a>
			</div>
			<div class="popupMenuItem">
				<a href="edit_config.php">Konfigurace webu<img src="res/settings.png" class="menuItemIcon"></img></a>
			</div>
		<?php } ?>
		<div class="popupMenuItem">
			<?php if(isSet($_SESSION['user'])) echo $_SESSION['user']['username']; ?>:
		</div>
		<div class="popupMenuItem">
			<a href="acc_mgmt.php">Spravovat účet<img src="res/settings.png" class="menuItemIcon"></img></a>
		</div>
		<div class="popupMenuItem">
			<a href="logout.php">Odhlásit<img src="res/exit.png" class="menuItemIcon"></img></a>
		</div>
	</div>
</div>