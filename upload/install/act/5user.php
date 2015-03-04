<?php


	if ( IS_CP !== true ) die('exit');

	if ( cp::$POST['create_ua'] )
	{
		
		if ( cp::$POST['pass'] != cp::$POST['passc'] )
		{
			$error = lang('ad_nop');
		}
		else
		{

			cp::db_load( 'db', cp::$conf['db_host'], cp::$conf['db_user'], cp::$conf['db_pass'], cp::$conf['db_name'] );
			
			/* Make lang() work */
			cp::$app['name'] = 'admin';

			$error = cp::call('members')->create( array(
				'email' 		=> cp::$POST['email'],
				'displayName' 	=> cp::$POST['displayName'],
				'pass' 			=> cp::$POST['pass'],
				'groupId'		=> 1,
				'avatar'		=> $conf['link_prefix'] . 'images/noav.jpg',
				'postCount'		=> 1,
			) );

			if ( !$error )
			{
				$postmess = true;
			}
			
		}
		
	}
	

?>


<div class="infodiv" style="font-weight: bold; font-size: 20px; text-align: center;">
	5. <?= lang('ad_tit') ?>
</div>

<?php if ( $postmess ) { ?>

	<div class="infodiv"><b><?= lang('success') ?>!</b> <?= lang('click_next') ?></div>
	<div style="float: right; margin: 5px 50px 5px 20px; font-size: 18px;">
		<a style="color: #4B7CA1" href="<?= $url_next_act ?>"><?= lang('next_step') ?></a>
	</div>

<?php }else{ ?>

	<?php if ( $error ) { ?>
	<div class="infodiv error">
		<b><?= lang('error') ?>:</b> <?= $error ?>
	</div>
	<?php } ?>
	
	<div class="infodiv"><?= lang('ad_info') ?></div>
	<div class="infodiv">
		<form method="post" name="create_ua">
			<?= lang('ad_email') ?><br />
			<input type="text" name="email" value="<?= cp::$POST['email'] ?>"> <br />
			<br />
			<?= lang('ad_dp') ?><br />
			<input type="text" name="displayName" value="<?= cp::$POST['displayName'] ?>"> <br />
			<br />
			<?= lang('ad_pass') ?><br />
			<input type="password" name="pass" value="<?= cp::$POST['pass'] ?>"> <br />
			<br />
			<?= lang('ad_cpass') ?><br />
			<input type="password" name="passc"  value="<?= cp::$POST['passc'] ?>"> <br />
			<br />
			<input type="submit" value="Submit" name="create_ua"> <br />
		</form>
	
	</div>
	
<?php } ?>


