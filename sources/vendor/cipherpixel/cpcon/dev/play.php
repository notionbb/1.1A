<?php

	/*===================================================
	//	CipherPixel © All Rights Reserved
	//---------------------------------------------------
	//	CP-Connect
	//		by cipherpixel.net
	//---------------------------------------------------
	//	@author		Michael Benner
	//	@date		December 01, 2014 
	//---------------------------------------------------
	//	This page provides an example on how to use CP-Connect.
	//=================================================*/

	//-----------------------------------
	// Setup
	//-----------------------------------

	session_start();
	
	/* Composer autoloader */
	require '../vendor/autoload.php';
	require '../src/CipherPixel/obj.php';
	
	/* Check for settings file */
	( @include('config.php') ) or die('You have not made a config.php - edit _config.php then rename it to config.php');
	
	echo '<a href="' . $this_url . '">Restart</a><br /><br />';
	
	//-----------------------------------
	// Initlialise
	//-----------------------------------
	
	/* Google */
	$google		= new CipherPixel\cpcon\obj( 'google',
											 $google_key,
											 $google_pass,
											 $this_url . '?ret=google'
											);
											
	/* FB */
	$facebook 	= new CipherPixel\cpcon\obj( 'facebook',
											 $facebook_key,
											 $facebook_pass,
											 $this_url . '?ret=facebook'
											);
											
	//-----------------------------------
	// Process
	//-----------------------------------
	
	/* Google */
	if ( $_GET['ret'] == 'google' AND $google->process() )
	{
		$pre = print_r( $google->data(), true );
	}
	
	/* FB */
	if ( $_GET['ret'] == 'facebook' AND $facebook->process() )
	{
		$pre = print_r( $facebook->data(), true );
	}
	
	echo '<pre>' . $pre . '</pre><br />';
	
	//-----------------------------------
	// Login Links
	//-----------------------------------
	
	if ( !$pre )
	{
		echo '<strong>Login:</strong><br />';											
		echo '<a href="' . $google->get_url() . '">Google</a><br />';	
		echo '<a href="' . $facebook->get_url() . '">Facebook</a><br />';
	}
	
	

?>