<?php

	if ( !IS_CP ) die();
	
	$array = array(
	
		'title'			=> 'CP-Core Installer',
		
		'next_step'		=> 'Next Step',
		'start'			=> 'Start',
		'ref'			=> 'Refresh',
		'error'			=> 'Error',
		'success'		=> 'Success',
		'click_next'    => 'Click next to continue',
		
		//-----------------------------------
		// Being
		//-----------------------------------
		
		'beg_ins'		=> 'Begin Installation',
		'need'			=> 'What you need to begin installation',
		'files'			=> 'Ensure all files are uploaded',
		'sql'			=> 'Have your MySQL username and password. If you don\'t have these contact your hosting provider.',
		'ready'			=> 'Once you have these you are ready to proceed',
		
		//-----------------------------------
		// CHMOD
		//-----------------------------------
		
		'ch_tit'		=> 'File Permissions',
		'chmod_files'	=> 'The following files and/or folders (in red) need to be CHMOD to "0755"',
		
		//-----------------------------------
		// conf.php
		//-----------------------------------
		
		'db_tit'		=> 'Configuration Settings',
		'db_info'		=> 'Enter your database settings. These can often be found inside your cPanel. For further help, contact your hosting service or try <a href="http://google.com">googling</a> it.',
		'db_host'		=> 'Database Host: (<i>Often localhost</i>)',
		'db_user'		=> 'Database Username:',
		'db_pass'		=> 'Database Password:',
		'db_name'		=> 'Database Name',
		
		//-----------------------------------
		// Install to DB
		//-----------------------------------
		
		'in_tit'		=> 'Database',
		'in_pre'		=> 'The script will now install the databse tables and data. This could mean long page loading times. Click begin to continue',
		'in_begin'		=> 'Begin',
		'in_post'		=> 'Database installation complete',
		
		'rep_structure'	=> 'Structure checked OK',
		'rep_comp'		=> 'Compulsory data checked OK',
		'rep_start'		=> 'Starting data not required',
		'rep_create'	=> 'created',
		'rep_alter'		=> 'altered',
		'rep_insert'	=> 'data inserted',
		'rep_update'	=> ' table updated',
		
		//-----------------------------------
		// Admin Account
		//-----------------------------------
		
		'ad_tit'		=> 'Create Admin Account',
		'ad_nop'		=> 'Passwords do not match',
		'ad_info'		=> 'Fill in the form below to create the admin account',
		'ad_email'		=> 'Email: <i>Used to login</i>',
		'ad_dp'			=> 'Display Name: <i>What users will see</i>',
		'ad_pass'		=> 'Password: <i>How you login</i>',
		'ad_cpass'		=> 'Confirm Password: <i>Just to make sure</i>',
		
		//-----------------------------------
		// Success Message
		//-----------------------------------
		
		's_tit'			=> 'Installation Complete',
		's_more'		=> 'To install more modules goto your admin panel then click "modules".',
		's_forum'		=> 'To go to your forum click <a href="../index.php" target="_top">here</a>.<br /><i>Nb: You will need to give yourself Moderator privileges by editing the "Admin" Group from the admin panel.</i>',
		's_admin'		=> 'To access your admin panel click <a href="../admin.php" target="_top">here</a>.',
		's_cp'			=> 'To get the most out of your board view the docs available at <a href="http://cipherpixel.net"  target="_top">CipherPixel.net</a>.',
		's_done'		=> 'Your installation is complete.',
		
	);