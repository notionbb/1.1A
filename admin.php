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
	
	include( 'sources/cp.php' );
	
	cp::setDefApp('admin');
	cp::main();

	
?>
