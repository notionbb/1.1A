<?php

if ( !IS_CP ) { die('No CP'); }

	//-----------------------------------
	// Cache
	//-----------------------------------
	
	/* Cache Skin Files */
	define( 'CACHE_TPLS', true );
	
	/* Cache Globals */
	define( 'CACHE_GLOS', true );
	
	//-----------------------------------
	// Debug
	//-----------------------------------
	
	/* CP Debug information at bottom of page. Also changes error messages, db setting does not */
	define( 'DEBUG', false );
	
	/* Show MySQL Errors */
	define( 'DEBUG_SQL', false );
	
	//-----------------------------------
	// Error Reporting
	//-----------------------------------
	
	if ( DEBUG !== true )
	{
		/* Normal operation */
		error_reporting( E_ERROR | E_WARNING | E_PARSE );
	}else{
		/* Debugging / developing */
		error_reporting(E_ALL & ~E_NOTICE);
	}
	
	//-----------------------------------
	// Charsets
	//-----------------------------------
	
	/* CP has only been tested with UTF-8 */
	define( 'DEF_CHAR', 'UTF-8' );
	
	//-----------------------------------
	// Emergency
	//-----------------------------------
	
	/* Overwrite Skin Folder
	  - Change skin in admin CP, this is for emergency
	  - Set to false for normal use */
	define( 'DEF_SKIN_FOLDER', false );
	
	//-----------------------------------
	// Link Types
	//-----------------------------------
	
	/* Change link type
	 Normally 'simulated' or else you may use 'complex', however this requires
	 an appropriately configured url_rewrite in your .htaccess file */
	define( 'LINK_TYPE', 'simulated' );
	
	//------------------------------------------------
	//	Advanced Users Only
	//------------------------------------------------
	
	/* Root path. No trailing slash */
	define( 'ROOT', str_replace( "\\", "/", dirname( __FILE__ ) ) );
	
	/* For Dev: Overwrite inserts to create application initial data
	 Set to false or the app's name */
	define( 'OW_INSERT_FOR_INIT_DATA', false );
	
?>