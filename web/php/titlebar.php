<div class="titlebar">
	<a href="index.php">
		<div class="icon"><img class="iconimg" src="res/cake.png"></img></div>
		<div class="title">Narozeninová Přání</div>
	</a>
	<div class="hamburger" onclick="toggleMobileMenu();"><img src="res/hamburger.png"></img></div>
	<div class="mobilemenu" id="mobilemenu">
		<hr>
		<a href="create_wish.php">Vytvořit přání</a>
		<?php
			if(isSet($_SESSION['user'])) { ?>
				
				<hr><a href="add_info.php">Přidat zajímavost</a>
				
				<?php
				if($_SESSION['user']['admin']) { ?>
					<hr><a href="info_mgmt.php">Spravovat zajímavosti</a>
					<hr><a href="user_mgmt.php">Spravovat uživatele</a>
					<hr><a href="edit_config.php">Konfigurace</a>
					<?php
				} ?>
				
				<hr><a><?php echo $_SESSION['user']['username'] ?>:</a>
				<hr><a href="acc_mgmt.php">Spravovat účet</a>
				<hr><a href="logout.php">Odhlásit se</a>
				<?php
				
			} else { ?>
				
				<hr><a href="login.php">Přihlásit se</a>
				<?php
				
			}
		?>
		<hr>
	</div>
	<div class="menu">
		<a href="create_wish.php">Vytvořit přání</a>
		<?php
			if(isSet($_SESSION['user'])) { ?>
				
				| <a href="add_info.php">Přidat zajímavost</a>
				
				<?php
				if($_SESSION['user']['admin']) { ?>
					| <a href="info_mgmt.php">Spravovat zajímavosti</a>
					| <a href="user_mgmt.php">Spravovat uživatele</a>
					| <a href="edit_config.php">Konfigurace</a>
					<?php
				} ?>
				
				| <a id="userBtn" onclick="toggleUsermenu();"><?php echo $_SESSION['user']['username'] ?> &#x25BC;</a>
				<div id="usermenu" class="usermenu">
					<div class="usermenu_item"><a href="acc_mgmt.php">Spravovat účet</a></div>
					<div class="hsep"></div>
					<div class="usermenu_item"><a href="logout.php">Odhlásit se</a></div>
				</div>
				<?php
				
			} else { ?>
				
				| <a href="login.php">Přihlásit se</a>
				<?php
				
			}
		?>
	</div>
</div>