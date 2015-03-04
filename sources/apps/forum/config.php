<?php

	//===================================================
	//	Cipher Pixel Board © All Rights Reserved
	//---------------------------------------------------
	//	CP-Core
	//		by michaelbenner.net
	//---------------------------------------------------
	//	File created: April 04, 2013 
	//===================================================

class forum_config {

	public $config = array(

		/**
		** ?act=pages
		*/
		'def'			=> 'board',
		'pages'			=> array(
			'board',
			'forum',
			'thread',
			'login',
			'register',
			'members',
			'mod',
			
			'play',
			'play2',
			'up_exp',
		),
		
		/**
		** Style fallbacks
		*/
		'defStyle'		=> 'forum2',
		
		/**
		** User Stuff
		*/
		'cookies'		=> true,
		'establishCur'	=> true,
		
		/**
		** Cache Rows
		*/
		'req_cache'		=> array('menu_bar'),

	);
	
	public function pre()
	{
		
		/**
		** Display!
		*/
		cp::callAppClass('forum_dtools');
		
		/**
		** Load Javascript
		*/
		cp::display()->jsLoad( cp::set('jQueryUrl'), true )
							->jsLoad( cp::set('jQueryUIUrl'), true )
							->jsLoad( 'ajax_gen' );
							
		/**
		** Global Handlers...
		*/
		cp::callAppClass('listener')->start();
		
		/**---------------------------
		** Act overwriters....
		*/
		
		/* Check board is online */
		if ( !cp::set('boardOnline') AND !cp::logged()->cur['canViewBoardOffline'] )
		{
			/* Show offline message */
			cp::$act 		= 'offline';
			
			/* Prevent Ajax Calls */
			cp::$GET['ajax']= false;
			
			return;
			
		}
		
		/* Do we require further setup? */
		if ( cp::logged()->check_setup() )
		{
			cp::$act = 'login';
			return;
		}
		
		/* Is user group allowed to see board? */
		if( !cp::logged()->cur['canViewBoard'] )
		{		
			cp::$act = 'login';
			return;		
		}
		
	}
	
	public function post()
	{
		
	}
	
	/**
	** compelxPages() - returns the act to load
	** 
	** @return	string	act file name
	*/
	public function complexPages()
	{
		
		if ( cp::$GET['1'] )
		{

			/**
			** Are we defining an act to load?
			*/
			if ( in_array( cp::$GET['1'], $this->config['pages'] ) )
				return cp::$GET['1'];
				
			/**
			** If we're not looking for an act we must be looking for a forum
			** Return the last item...
			*/
			$last = end( cp::$GET );
			
			if ( is_numeric( $last ) )
			{
				cp::$cache['PAGE_NUM'] = $last;
				$last = prev( cp::$GET );
			}
			
			/**
			** Looking for a specific post...
			*/
			if ( substr( $last, 0, 2 ) == 'p_' AND is_numeric( $p_id = substr( $last, 2 ) ) )
			{
				cp::$cache['post_id'] = $p_id;
				$last = prev( cp::$GET );
				array_pop( cp::$GET );
			}
			
			/**
			** Are we posting??
			*/
			if ( $last == 'post' )
			{
				cp::$cache['POST_TO'] = prev( cp::$GET );
				return 'post';
			}
				
			/**
			** We need to check if this is a thread.
			** "1" will always be a category
			** "2" will always be a forum
			** "3" and beyond needs to be checked for threads
			*/
			if ( cp::$GET['3'] )
			{
				
				$thread = cp::db()->fetch_dep( array(
					'table'		=> 'threads',
					'where'		=> 'slug="'. $last.'"',
					'one'		=> true,
				) );
				
				if ( $thread )
				{
					cp::$cache['THREAD_ID'] = $thread['id'];
					return 'thread';
				}			
				
			}
				
			/**
			** Return forum slug that we're getting...
			*/
			cp::$GET['slug'] = $last;

			return 'forum';		
			
		}
		else
		{
			return $this->config['def'];
		}
			
	}
	
}

?>