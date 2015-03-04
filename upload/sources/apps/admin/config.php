<?php

	//===================================================
	//	Cipher Pixel Board © All Rights Reserved
	//---------------------------------------------------
	//	CP-Core
	//		by michaelbenner.net
	//---------------------------------------------------
	//	File created: April 04, 2013 
	//===================================================

class admin_config {

	public $config = array(

		/**
		** ?act=pages
		*/
		'def'			=> 'home',
		'pages'			=> array(
			'home',
		),
		
		/**
		** Complex Pages
		*/
		'complexPages'	=> false,
		
		/**
		** Style fallbacks
		*/
		'defStyle'		=> 'forum2',
		'skinFrom'		=> 'forumSkinFolder',
		
		/**
		** User Stuff
		*/
		'cookies'		=> TRUE,
		'establishCur'	=> TRUE,
		
		/**
		** Cache Rows
		*/
		'req_cache'		=> array('admin_notepad'),

	);
	
	public function pre()
	{

		cp::call('link')->setType('basic');
		cp::callAppClass('dtools');
		
		/**
		** Load Javascript
		*/
		cp::display()->jsLoad( cp::set('jQueryUrl'), true )
							->jsLoad( cp::set('jQueryUIUrl'), true )
							->jsLoad( 'ajax_gen' )
							->jsLoad( 'admin_lib' );
		
	}
	
	public function post()
	{
		
	}
	
}

?>