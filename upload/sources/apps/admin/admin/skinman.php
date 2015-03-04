<?php

	/*===================================================
	//	CipherPixel © All Rights Reserved
	//---------------------------------------------------
	//	CP-Core
	//		by cipherpixel.net
	//---------------------------------------------------
	//	File created: September 09, 2014 
	//=================================================*/

class admin_skinman extends controller {
	
	/**
	** Array of form settings
	** 
	** @var		array
	*/
	public $settings;
	
	/**
	** Array of form values
	** 
	** @var		array
	*/
	public $values;
	
	public function main()
	{
		
		
		//-----------------------------------
		// Controller
		//-----------------------------------
		
		if ( cp::$GET['install'] )
		{
			if ( !$this->install( cp::$GET['install'] ) )
				return false;
		}
		if ( cp::$GET['rec'] == 'all' )
		{
			if ( !$this->recache() )
				return false;
		}
		else
		if ( !cp::cont()->adminDo( get_class($this) ) )
		{
			return false;
		}
		
		/* Display */
		cp::cont()->navtree( cp::lang('all', 'skinman'), '?page=skinman', true );
		
		return true;
		
	}
	
	/**
	** Shows list of groups
	** 
	** @return	bool
	*/
	public function showAll()
	{
		
		//-----------------------------------
		// Currently Installed
		//-----------------------------------
		
		cp::cont()->page['title'] = cp::lang('all', 'man_skin');
		
		$rows = cp::db()->fetch( array(
			'table'	=> 'skins',
			'record'=> 'id',
		) );
		
		$installed_skins = cp::db()->record;
		
		$cols = $this->table();
		
		cp::cont()->output = cp::call('dtools')->rowsToTable( $rows, $cols, 'all' );
		
		/* Quick Button */
		cp::call('dtools')->page_but( cp::lang('all', 'skin_recache_set'), '?page=skinman&rec=all' );
		
		//-----------------------------------
		// Not yet installed
		//-----------------------------------
		
		$skin_folder = ROOT . '/style/';
		
		$scan = scandir( $skin_folder );
		
		foreach( $scan as $name )
		{			
			
			$skin_info = array();
			
			/* Check for folder */
			if ( !is_dir( $skin_folder. '/' . $name ) ) continue;
			
			/* Ignore relative paths */
			if ( $name == '.' OR $name == '..' ) continue;
			
			/* Skip installed skins */
			if ( in_array( $name, $installed_skins ) ) continue;
			
			$skin_info['folder'] = $name;
			
			/* Include extra information if we can */
			@include('style/' . $name .'/info.php');
			
			cp::display()->vars['row']	= $skin_info;
			cp::display()->vars['rows'] .= cp::display()->read('cpt_row_skin');
			$one = true;
			
		}
		
		if ( $one )
		{
			cp::cont()->output .= cp::display()->read('cptable');
		}
			
		
	}
	
	/**
	** Gets array of settings
	** 
	** @return	self
	*/
	public function getSettings()
	{
		$this->settings = array();
		
		return $this;
	}
	
	/**
	** Gets current from by id
	** 
	** @param	string/int	$rowId
	** @return	self
	*/
	public function getRow( $rowId )
	{				
		$this->row = cp::db()->get( 'skins', $rowId );
		
		return $this;	
	}
	
	/**
	** Shows the form to edit a row
	** 
	*/
	public function showForm()
	{		
		
	}
	
	/**
	** Process edit form
	** 
	** @return	bool
	*/
	public function processForm()
	{
		
		

		/* Splash Page */
		return false;
		
	}
	
	/**
	** Shows delete confirmation page
	** 
	*/
	public function showDeleteForm()
	{		
		
	}
	
	/**
	** Process delete row
	** 
	** @return	bool
	*/
	public function deleteRow()
	{
		
		/* Splash */
		return false;
		
	}
	
	/**
	** Install a skin
	** 
	** @param	string	$folder
	** @return	bool
	*/
	public function install( $folder )
	{
		
		//-----------------------------------
		// Check Unique
		//-----------------------------------
		
		$check = cp::db()->fetch( array(
			'table'	=> 'skins',
			'where' => array('id'=>$folder),
		) );
		
		if ( $check )
		{
			cp::call('dtools')->caution( cp::lang('all', 'skin_multi') );
			return true;
		}
		
		//-----------------------------------
		// Get info
		//-----------------------------------
		
		$skin_info['id'] = $folder;
		
		include( 'style/' . $skin_info['id'] .'/info.php' );
		
		/* Prevent designers breaking shit */
		foreach( $skin_info as $k => $v )
		{
			$skin_info[ $k ] = cp::clean( $v );
		}
		
		cp::db()->insert( 'skins', $skin_info );
		
		cp::display()->splash( cp::lang( 'all', 'skin_suc' ), '?page=skinman');
		return false;
			
		
	}
	
	/**
	** Recache Skins
	** 
	** @return	bool
	*/
	public function recache()
	{
		cp::cache()->task_cache_skins();
		
		cp::display()->splash( cp::lang( 'all', 'skin_recached_set' ), '?page=skinman');		
		return false;
		
	}
	
	/**
	** Table settings for showAll() page
	** 
	** @return	array
	*/
	public function table()
	{
		
		return array(
			'sexyname'		=> 'skin_name',
			'devlink'		=> array(
				'lang_key'	=> 'skin_dev',
				'type'		=> 'url',
				'eng_key'	=> 'dev',
			),
			'id'			=> 'skin_folder',
			'public'		=> array(
				'lang_key'	=> 'skin_pub',
				'type'		=> 'tick',
				'class'		=> 'cent',
				'width'		=> '200px',
			),
			'opt'		=> array(
				'lang_key'	=> 'opt',
				'type'		=> 'opt',
				'items'		=> array( 'del' => 'id' ),
				'class'		=> 'cent',
				'width'		=> '150px',
			),
		);
		
	}
	
}

?>