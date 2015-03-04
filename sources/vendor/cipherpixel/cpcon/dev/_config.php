<?php

	/*===================================================
	//	CipherPixel © All Rights Reserved
	//---------------------------------------------------
	//	CP-Core
	//		by cipherpixel.net 
	//---------------------------------------------------
	//	This file should hold all your OAuth Settings
	//=================================================*/
	
	//-----------------------------------
	// Compulsory data
	//-----------------------------------
	
	/* URL back to this play.php. This this as a valid redirect_uri
		Nb: In the example a callback will be type specific, i.e., ?ret=facebook will be appended on the facebook uri etc etc */
	$this_url = 'http://YOUR WEBSITE/dev/play.php';
	
	//-----------------------------------
	// Optional data
	//-----------------------------------
	
	$google_key		= 'GOOGLE_CLIENT_ID';
	$google_pass	= 'GOOGLE_CLIENT_SECRET';
	
	$facebook_key 	= 'FACEBOOK_APP_ID';
	$facebook_pass	= 'FACEBOOK_APP_SECRET';