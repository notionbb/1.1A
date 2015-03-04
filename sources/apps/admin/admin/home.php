<?php

	//===================================================
	//	Cipher Pixel Board © All Rights Reserved
	//---------------------------------------------------
	//	CP-Core
	//		by michaelbenner.net
	//---------------------------------------------------
	//	File created: April 04, 2013 
	//===================================================

class admin_home extends controller {
	
	/**
	** Array of information from CipherPixel
	** 
	** @var		array
	*/
	public $from_cp;
	
	/**
	** URL to CP
	** 
	** @var		string
	*/
	public $cp_url = 'http://cipherpixel.net/cpext/uptodate2.php';
	
	public function main()
	{
		
		cp::cont()->page['title'] = cp::lang('all', 'adm-home');
		
		/* Get update json (etc) */
		$this->get_json();
		
		//-----------------------------------
		// Updates
		//-----------------------------------
		
		/* Get current apps */
		foreach( cp::$apps as $app )
		{
			cp::display()->vars['update']	= $this->from_cp['app'][ $app['name'] ];
			cp::display()->vars['sexyname'] = $app['sexyname'];
			cp::display()->vars['apps'] 	.= cp::display()->read('admin_app_row');
		}
		
		//-----------------------------------
		// Latest News
		//-----------------------------------
		
		if( $this->from_cp['news'] )
		{
			foreach( $this->from_cp['news'] as $news )
			{
				cp::display()->vars['news'] .= '<a class="item" href="'.$news['link'].'">'.$news['title'].'</a>';
			}
		}
		
		//-----------------------------------
		// Quick Stats
		//-----------------------------------
		
		if ( cp::db()->query('miscstat')->result() )
		{		
			while( $array = cp::db()->toArray() )
			{
				cp::display()->vars['misc'][ $array['arrayTitle'] ] = $array['value'];
			}			
		}
		
		/* How many people online? */
		
		$fifteenago = ( cp::$time - ( 60 * 15 ) );
		
		cp::db()->build( array(
			'select'	=> 'id, userId',
			'table'		=> 'online',
			'where'		=> 'lastClick > '.$fifteenago.' AND userId != "0"',
			'order'		=> 'lastClick desc',
			'record'	=> 'userId',
		) );
		
		cp::display()->vars['misc']['online'] = cp::db()->result();
		
		//-----------------------------------
		// Admin Notepad
		//-----------------------------------
		
		cp::display()->vars['notepad'] = cp::cache()->get_db('admin_notepad');
		
		//-----------------------------------
		// Latest Users
		//-----------------------------------
		
		$table = cp::callAppClass('object_table');
		
		$table->rows 	= cp::db()->fetch( array( 	'table' => 'users',
													'order' => 'id desc',
													'limit' => '5',
											) );
											
		$table->cols 	= array( '' => 'avatar', 'dname' => 'displayName', 'email' => 'email', 'mem_since' => 'regDate' );
		$table->classes = array( 'avatar' => 'avatar', 'regDate' => 'cent' );		
		$table->options	= array( 'edit' => 'id' );
		
		$table->parse	= array( 'avatar' 	=> 'img',
								 'regDate'	=> function( $row ) {
									 			return date( cp::set('shortdate'), $row['regDate'] );
								 			},
								);
		
		$table->page 		= 'user';
		$table->table_class	.= ' cptable-small';
		
		cp::display()->vars['new_user_table'] = $table->make();
													
		//$table->cols 	= array('gname' => 'name', 'members' => 'userCount', 'acp_acc' => 'acp', 'glo_mod' => 'globalMod');
		//$table->classes = array('userCount' => 'cent');
		
		/*$table->parse = array( 	'name' => function( $row ) {
											return $row['htmlPrefix'] . $row['name'] . $row['htmlSuffix'];
										},
								'acp'		=> 'tick',
								'globalMod'	=> 'tick',
							);*/
		
		

		//-----------------------------------
		// Display
		//-----------------------------------
		
		cp::cont()->output .= cp::display()->read('admin_home');
		
		return true;
		
	}
	
	/**
	** Get json array from CP Server's
	** 
	*/
	public function get_json()
	{
		
		//-----------------------------------
		// Build URL
		//-----------------------------------
		
		$url = $this->cp_url . '?from=' . urlencode( $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'] ) . '&';
		
		foreach( cp::$apps as $app )
		{
			$url .= 'check_ver_' . $app['name'] . '=' . $app['ver'] . '&';
		}
		
		$json = @file_get_contents( $url );
		
		if ( !$json OR ( $this->from_cp = json_decode( $json, true ) ) === null )
		{	
			cp::call('dtools')->caution( cp::lang('all', 'no-uptodate') );		
		}
			
	}
	
	/**
	** Save admin notepad
	** 
	*/
	public function ajax_notepad()
	{
		cp::cache()->put_db( 'admin_notepad', cp::$POST['note'] );		
		cp::call('ajax')->ret['res'] = 'suc';
	}
	
}

?>