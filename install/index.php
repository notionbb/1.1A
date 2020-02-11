<?php

	/*===================================================
	//	NotionBB © All Rights Reserved
	//---------------------------------------------------
	//	NBB-Core
	//		by notionbb.com
	//---------------------------------------------------
	//	File Updated - 2-11-2020
	//=================================================*/

	ob_start();
	session_start();
	
	define( 'IS_CP', true );
	define( 'IS_INSTALLER', true );
	
	//-----------------------------------
	// Init
	//-----------------------------------
	
	include( '../init.php' );
	@include( '../conf.php' );
	$conf = $CACHE;
	unset( $CACHE );
	
	if ( $conf['lock_installer'] )
	{
		die('The installer has been locked, edit conf.php to unlock');
	}
	
	/* Master Class */
		
	include( '../sources/cp.php' );
	include('../sources/classes/Debug.php');
	
	cp::$conf = $conf;
	cp::$time = time();
	
	cp::$GET = cp::clean( $_GET );
	cp::$POST = cp::clean( $_POST );
	
	//-----------------------------------
	// Lang Packs
	//-----------------------------------
	
	include( '../sources/lang/en/admin_install.php' );
	$lang_array = $array;
	unset( $array );
	
	function lang($key)
	{
		global $lang_array;
		return $lang_array[ $key ];
	}
	
	//-----------------------------------
	// Controller
	//-----------------------------------
	
	$flow = array( '1begin', '2chmod', '3conf', '4db', '5user', '6done' );
	
	$page = ( cp::$GET['page'] ) ?: 1;
	
	$include = 'act/'. $flow[ $page - 1 ] .'.php';
	
	$url_curr_act = '?page=' . $page;
	$url_next_act = '?page=' . ( $page + 1 );
	
	//-----------------------------------
	// Display
	//-----------------------------------
	
	ob_start();
	include($include);
	$page_html = ob_get_clean();
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
        "http://www.w3.org/TR/html4/loose.dtd">
        
<html>
	
	<head>
	
		<title><?=lang('title')?></title>
		
		<?php if ( $next ) { ?>
			<meta http-equiv="Refresh" content="2; url=<?= $next ?>">
		<?php } ?>
		
		<style type="text/css">
			.infodiv {
				border: 1px solid;
				text-align: left;
				border: 1px solid #BCCACA;
				background: #ECF4F4;
				padding: 10px;
				margin-bottom: 10px;
			}
			
			.error {
				color: #BA161F;
				border: 1px solid #F26F6F;
				background-color: #FFBABA;
			}
			
			.green {
				color: #4F8A10;
				border: 1px solid #B1E854;
				background-color: #DFF2BF;
			}
			
			ul {
				margin-left: 20px;
				padding: 0px;
			}
			
			li {
				margin: 3px;
			}
		</style>

	</head>
	<body style="padding: 0px; margin: 0px; background: #4A6068; color: #1A1A1A; font: 12px Verdana;">
	
	<div style="margin-left: auto; margin-right: auto; width: 800px;">
	
		<div style="width: 750px; color: #FFF; background: #000; padding: 25px; font-size: 40px;">
			<?=lang('title')?>
		</div>
	
		<div style="height: 500px; float: left; padding: 10px; border-left: 1px solid; border-right: 1px solid; border-bottom: 1px solid; width: 228px; background: #E1E7EA;">
			
		</div>
		
		<div style="overflow: scroll; height: 500px; float: right; padding: 10px; border-right: 1px solid; border-bottom: 1px solid; width: 529px; background: #FFF;">
			<?= $page_html ?>
		</div>
		
		<div style="clear: both; width: 790px; color: #000; background: #E1E7EA; padding: 5px; text-align: center;">
			Powered by <a href="http://notionbb.com" target="_blank">NotionBB</a> {$lang('version', 'version')}
		</div>
	
	</div>
	
	</body>
	
</html>
