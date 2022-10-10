<header style=" font-size:1em; padding:7px;">
	<span class="start" style="background: #424242; width:100%;">
		<?php echo stripslashes($_SESSION['CompanyRecord']['coyname']); ?> 
		&nbsp;|&nbsp;
		<span class="bold">
			<?php echo stripslashes($_SESSION['UsersRealName']); ?>
		</span>
		<span class="end">
			<a href="<?php echo $RootPath; ?>/index.php" style="color: white; text-decoration: none;">Main Menu</a>
			<a class="bold" href="<?php echo $RootPath; ?>/Logout.php" style="color: white; text-decoration: none; margin-left:20px;">Logout</a>
		</span>
	</span>
</header>