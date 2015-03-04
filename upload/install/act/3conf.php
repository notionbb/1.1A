<?php

	if ( IS_CP !== true ) die('exit');

	if ( $conf )
	{
		$db = @new mysqli( $conf['host'], $conf['user'], $conf['pass'], $conf['name']  );
		
		if ( $db->connect_error )
		{
			$error = $db->connect_error;
		}
	}
	else	
	if ( cp::$POST['sql'] )
	{
		
		/* Check Connection */
		$db = @new mysqli( cp::$POST['host'], cp::$POST['user'], cp::$POST['pass'], cp::$POST['name']  );
		
		if ( $db->connect_error )
			$error = $db->connect_error;
		else
		{
			
			/* Create conf.php */
			
			$save = array(
				'db_host' 		=> cp::$POST['host'],
				'db_user' 		=> cp::$POST['user'],
				'db_pass' 		=> cp::$POST['pass'],
				'db_name' 		=> cp::$POST['name'],
				'link_prefix'	=> substr( $_SERVER['PHP_SELF'], 0, ( 0 - strlen('install/index.php') ) ),
				'site_begin' 	=> time(),
				'default_app' 	=> 'forum',
			);
			
			cp::call('cache')->put( 'conf', $save, true, ROOT .'/conf.php' );
			
			@include( '../conf.php' );
			$conf = $CACHE;
			unset( $CACHE );
			
		}
		
	}

	if ( $error OR !$conf )
	{
		$showForm = true;
	}

?>


<div class="infodiv" style="font-weight: bold; font-size: 20px; text-align: center;">
	3. <?= lang('db_tit') ?>
</div>

<?php if ( $error ) { ?>
<div class="infodiv error">
	<b><?= lang('error') ?>:</b> <?= $error ?>
</div>
<?php } ?>

<?php if ( $showForm ) { ?>

	<div class="infodiv">
		<?= lang('db_info') ?>
		<br /> <br />
		<form method="post" name="sql">
			<?= lang('db_host') ?><br />
			<input type="text" name="host" value="<?= cp::$POST['host'] ?>"> <br />
			<br />
			<?= lang('db_user') ?><br />
			<input type="text" name="user" value="<?= cp::$POST['user'] ?>"> <br />
			<br />
			<?= lang('db_pass') ?><br />
			<input type="text" name="pass" value="<?= cp::$POST['pass'] ?>"> <br />
			<br />
			<?= lang('db_name') ?><br />
			<input type="text" name="name" value="<?= cp::$POST['name'] ?>"> <br />
			<br />
			<input type="submit" value="Submit" name="sql">
		</form>
	</div>

<?php }else{ ?>

	<div class="infodiv"><b><?= lang('success') ?>!</b> <?= lang('click_next') ?></div>
	<div style="float: right; margin: 5px 50px 5px 20px; font-size: 18px;">
		<a style="color: #4B7CA1" href="<?= $url_next_act ?>"><?= lang('next_step') ?></a>
	</div>
	
<?php } ?>
