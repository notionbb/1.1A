<?php

	/*===================================================
	//	CipherPixel © All Rights Reserved
	//---------------------------------------------------
	//	CP-Core
	//		by cipherpixel.net
	//---------------------------------------------------
	//	File created: September 09, 2014 
	//=================================================*/

class admin_search extends controller {
	
	/**
	** Array of form settings, array of form values
	*/
	public $settings;
	public $values;
	
	public function main()
	{
		
		cp::cont()->navtree( cp::lang('members', 'mem_sea'), '?page=search', true );
		
		$this->searchForm();
		
		return true;
		
	}
	
	/**
	** searchForm()
	*/
	public function searchForm()
	{
		
		cp::cont()->page['title'] = cp::lang('members', 'mem_sea');
		
		cp::display()->jsLoad( 'memsearch' );
		cp::cont()->output .= cp::display()->read('div_search');
		
	}
	
	/**
	** resRows() 
	*/
	public function ajax_resRows()
	{
		
		$dname = cp::$POST['dname'];
		
		$rows = cp::call('members')->fetch(
			'users.displayName LIKE "%'.$dname.'%" OR users.realName LIKE "%'.$dname.'%"',
			true,
			array( 'limit' => '20' )
		);
		
		$rows = cp::call('members')->prepMember( $rows );
		
		if ( !$rows )
		{
			
		}
		else
		{
			
			//print_r($rows);
			
			$cols = $this->table();
			
			cp::$GET['page'] = 'user';
			
			cp::call('ajax')->ret['swop'] = '.memres';
			cp::call('ajax')->ret['html'] = cp::call('dtools')->rowsToTable( $rows, $cols, 'all' );
			
		}
		
	}
	
	/**
	** table() - table to display members
	*/
	public function table()
	{
		
		return array(
			'avatar'	=> array(
				'lang_key'	=> 'opt',
				'type'		=> 'img',
				'class'		=> 'avatar',
			),
			'displayName'=> 'dname',
			'htmlGroup'		=> 'group1',
			'email'		=> 'email',
			'opt'		=> array(
				'lang_key'	=> 'opt',
				'type'		=> 'opt',
				'items'		=> array( 'edit' => 'id', 'del' => 'id' ),
				'class'		=> 'cent',
				'width'		=> '150px',
			),
		);
		
	}
		
}

?>