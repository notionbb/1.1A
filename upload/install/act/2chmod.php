<?php

	if ( IS_CP !== true ) die('exit');

	$dirs = array(
		'/conf.php',
		'/uploads',
		'/sources/cache',
	);

	foreach( $dirs as $path )
	{
		if ( is_writeable( ROOT . $path ) )
		{
			$green .= '<div class="infodiv green">'.$path.'</div>';
		}
		else
		{			
			$red .= '<div class="infodiv error">'.$path.'</div>';
		}
		
	}
		
?>

<div class="infodiv" style="font-weight: bold; font-size: 20px; text-align: center;">
	<?= lang('ch_tit') ?>
</div>

<div class="infodiv">
	<?= lang('chmod_files') ?>
</div>

<?= $red ?>
<?= $green ?>

<?php if ( $red ) { ?>

	<div style="margin: 5px auto 5px auto; font-size: 18px; text-align: center;">
		<a style="color: #4B7CA1" href=""><?= lang('ref') ?></a>
	</div>

<?php }else{ ?>

	<div style="float: right; margin: 5px 50px 5px 20px; font-size: 18px;">
		<a style="color: #4B7CA1" href="<?= $url_next_act ?>"><?= lang('next_step') ?></a>
	</div>
	
<?php } ?>