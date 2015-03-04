<?php

	/*===================================================
	//	CipherPixel © All Rights Reserved
	//---------------------------------------------------
	//	CP-Core
	//		by cipherpixel.net
	//---------------------------------------------------
	//	File created: September 09, 2014 
	//=================================================*/

class admin_skinperm extends controller {
	
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
		
		if ( !cp::cont()->adminDo( get_class($this) ) )
		{
			return false;
		}
		
		/* Display */
		cp::cont()->navtree( cp::lang('all', 'skin_perm'), '?page=skinperm', true );
		
		return true;
		
	}
	
	/**
	** Shows list of groups
	** 
	** @return	bool
	*/
	public function showAll()
	{
		
		cp::cont()->page['title'] = cp::lang('all', 'skin_perm');
		
		$rows = cp::db()->fetch( array(
			'table'	=> 'skins',
		) );
		
		$cols = $this->table();
		
		/* Turn p_read into an array */
		foreach( $rows as $key => $array )
		{
			$rows[ $key ]['p_apps'] = array_filter( explode( ',', $array[ 'p_apps' ] ) );
			$rows[ $key ]['p_read'] = array_filter( explode( ',', $array[ 'p_read'] ) );
			$rows[ $key ]['p_g_read'] = array_filter( explode( ',', $array[ 'p_g_read' ] ) );
			$rows[ $key ]['p_u_read'] = array_filter( explode( ',', $array[ 'p_u_read' ] ) );
			
			$masks = cp::merge( $masks, $rows[$key]['p_read'] );
			$groups = cp::merge( $groups, $rows[$key]['p_g_read'] );
			$users = cp::merge( $users, $rows[$key]['p_u_read'] );
		}
		
		/* Optimise the queries a bit... */
		if ( count( $masks ) > 0 )
			cp::db()->fetch( array( 'table'	=> 'perm', 'where' => array('id' => $masks) ) );
			
		if ( count( $groups ) > 0 )
			cp::db()->fetch( array( 'table'	=> 'groups', 'where' => array('id' => $groups) ) );

		if ( count( $users ) > 0 )
			cp::db()->fetch( array( 'table'	=> 'users', 'where' => array('id' => $users) ) );
		
		cp::db()->fetch( array( 'table'	=> 'modules', 'key' => 'name' ) );
		
		cp::cont()->output = cp::call('dtools')->rowsToTable( $rows, $cols, 'all' );
		
	}
	
	/**
	** Gets array of settings
	** 
	** @return	self
	*/
	public function getSettings()
	{
		$this->settings = array(
			array(
				'name'		=> 'public',
				'title'		=> 'Is public',
				'desc'		=> 'Anyone can use this skin',
				'valueType'	=> 'onoff',
			),
			array(
				'name'		=> 'p_apps',
				'title'		=> 'Allowed Apps',
				'desc'		=> 'Apps that can use this skin set',
				'valueType'	=> 'list',
				'valueFunction'	=> 'getMods',
			),
			array(
				'name'		=> 'p_read',
				'title'		=> 'Allowed Masks',
				'desc'		=> 'Permission masks that are allowed to use this skin set',
				'valueType'	=> 'list',
				'valueFunction'	=> 'getPerms',
			),
			array(
				'name'		=> 'p_g_read',
				'title'		=> 'Allowed Groups',
				'desc'		=> 'Groups that are allowed to use this skin set',
				'valueType'	=> 'list',
				'valueFunction'	=> 'getGroups',
			),
		);
		
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
		
		$this->row['p_apps'] = array_filter( explode( ',', $this->row[ 'p_apps' ] ) );
		$this->row['p_read'] = array_filter( explode( ',', $this->row[ 'p_read'] ) );
		$this->row['p_g_read'] = array_filter( explode( ',', $this->row[ 'p_g_read' ] ) );
		$this->row['p_u_read'] = array_filter( explode( ',', $this->row[ 'p_u_read' ] ) );
		
		return $this;	
	}
	
	/**
	** Shows the form to edit a row
	** 
	*/
	public function showForm()
	{		
		
		if( !cp::$GET['edit'] )
			return;
		
		$this->getSettings()->getRow( cp::$GET['edit'] );
		
		cp::cont()->page['title'] = cp::lang('all', 'edit_skin_perm').': '.$this->row['sexyname'];
		cp::cont()->navtree( cp::lang('all', 'edit'), '' );
		
		cp::cont()->output .= cp::display()->form( array(
			'name'		=> 'update',
			'submit'	=> cp::lang('all', 'up_skin' ),
			'fieldA'	=> cp::call('dtools')->settingToFields( $this->settings, $this->row, 'name', true ),
			),
			'tab'
		);
		
	}
	
	/**
	** Process edit form
	** 
	** @return	bool
	*/
	public function processForm()
	{
			
		$this->getRow( cp::$GET['edit'] )->getSettings();
		
		$new = cp::call('dtools')->processSettings( $this->settings );
		
		if ( is_array( $new['new']['p_apps'] ) )
			$new['new']['p_apps'] = ',' . implode( ',', $new['new']['p_apps'] ) . ',';
			
		if ( is_array( $new['new']['p_read'] ) )	
			$new['new']['p_read'] = ',' . implode( ',', $new['new']['p_read'] ) . ',';
			
		if ( is_array( $new['new']['p_g_read'] ) )
			$new['new']['p_g_read'] = ',' . implode( ',', $new['new']['p_g_read'] ) . ',';
		
		if ( is_array( $new['new']['p_u_read'] ) )
			$new['new']['p_u_read'] = ',' . implode( ',', $new['new']['p_u_read'] ) . ',';
		
		cp::db()->update('skins', $this->row['id'], $new['new'] );
		cp::cache()->task_cache_skins();
		
		/* Splash Page */
		cp::display()->splash( cp::lang('all', 'skin_perm_chg'), '?page=skinperm');
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
	** Table settings for showAll() page
	** 
	** @return	array
	*/
	public function table()
	{
		
		return array(
			'sexyname'		=> 'skin_name',
			'public'		=> array(
				'lang_key'	=> 'skin_pub',
				'type'		=> 'tick',
				'class'		=> 'cent',
				'width'		=> '200px',
			),
			'apps'	=> array(
				'lang_key'	=> 'allowed_a',
				'type'		=> 'arrayToValue',
				'class'		=> 'cent',
				'table'		=> 'modules',
				'dispCol'	=> 'sexyname',
				'keyForArr'	=> 'p_apps',
			),
			'p_read'	=> array(
				'lang_key'	=> 'allow_m',
				'type'		=> 'arrayToValue',
				'class'		=> 'cent',
				'table'		=> 'perm',
				'dispCol'	=> 'name',
				'keyForArr'	=> 'p_read',
			),
			'p_g_read'	=> array(
				'lang_key'	=> 'allow_g',
				'type'		=> 'arrayToValue',
				'class'		=> 'cent',
				'table'		=> 'groups',
				'dispCol'	=> 'name',
				'keyForArr'	=> 'p_g_read',
			),
			/*'p_u_read'	=> array(
				'lang_key'	=> 'allow_u',
				'type'		=> 'arrayToValue',
				'class'		=> 'cent',
				'table'		=> 'users',
				'dispCol'	=> 'displayName',
				'keyForArr'	=> 'p_u_read',
			),*/
			'opt'		=> array(
				'lang_key'	=> 'opt',
				'type'		=> 'opt',
				'items'		=> array( 'edit' => 'id' ),
				'class'		=> 'cent',
				'width'		=> '150px',
			),
		);
		
	}
	
}

?>