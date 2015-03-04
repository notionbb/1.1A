<?php

	if ( IS_CP !== true ) die('exit');

	if( !cp::$GET['start'] )
	{
		$premess = true;
	}
	else
	{
		
		if ( cp::$GET['do'] == '4' )
		{			
			$postmess = true;
		}
		else
		{
			
			$to_install = array('admin', 'forum');
			
			cp::$GET['to_install'] = ( cp::$GET['to_install'] ) ?: 0;
			
			$name = $to_install[ cp::$GET['to_install'] ];
		
			cp::db_load( 'db', cp::$conf['db_host'], cp::$conf['db_user'], cp::$conf['db_pass'], cp::$conf['db_name'] );
			
			$class = cp::call( 'app_install_' . $name, 'apps/' . $name . '/classes/' . $name . '_install' );
			
			$report = cp::call('port')->install_flow( $class, true );
			
			foreach( $report as $table => $array )
			{
				$array['lang'] = lang( 'rep_'.$array['type'] );
				$reports .= '<div class="infodiv">'.$array['table'].' '.$array['lang'].'</div>';
			}
			
			unset( $class );
			unset( $report );
			
			if ( $to_install[ cp::$GET['to_install'] + 1 ] )
			{
				$next = $url_curr_act.'&start=true&to_install='. ( cp::$GET['to_install'] + 1 ) . '&do='. ( cp::$GET['do'] );
			}
			else
			{
				$next = $url_curr_act.'&start=true&to_install=0&do='. ( cp::$GET['do'] + 1 );
			}
			
		}
		
	}

?>


<div class="infodiv" style="font-weight: bold; font-size: 20px; text-align: center;">
	4. <?= lang('in_tit') ?>
</div>

<?php if ( $premess ) { ?>

	<div class="infodiv">
		<?= lang('in_pre') ?>
	</div>
	
	<div style="float: right; margin: 5px 50px 5px 20px; font-size: 18px;">
		<a style="color: #4B7CA1" href="<?= $url_curr_act ?>&start=true"><?= lang('in_begin') ?></a>
	</div>
	
<?php } elseif ( $postmess ) { ?>

	<div class="infodiv"><b><?= lang('success') ?>!</b> <?= lang('click_next') ?></div>
	<div style="float: right; margin: 5px 50px 5px 20px; font-size: 18px;">
		<a style="color: #4B7CA1" href="<?= $url_next_act ?>"><?= lang('next_step') ?></a>
	</div>

<?php }else{ ?>

	<?= $reports ?>
	
	<div style="float: right; margin: 5px 50px 5px 20px; font-size: 18px;">
		<a style="color: #4B7CA1" href="<?= $next ?>"><?= lang('next_step') ?></a>
	</div>

<?php } ?>


