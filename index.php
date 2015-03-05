<?php

	/*===================================================
	//	CipherPixel © All Rights Reserved
	//---------------------------------------------------
	//	CP-Core
	//		by cipherpixel.net - Modified by: notionbb.com
	//---------------------------------------------------
	//	File created: March 29, 2014  =)
	//=================================================*/
	
	ob_start();
	session_start();
	
	include( 'sources/cp.php' );
	
	cp::main();
	
?>
