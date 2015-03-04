<?php

	/*===================================================
	//	CipherPixel © All Rights Reserved
	//---------------------------------------------------
	//	CP-Core
	//		by cipherpixel.net
	//---------------------------------------------------
	//	File created: September 09, 2014 
	//=================================================*/

class admin_tools extends controller {
	
	/**
	** Array of form settings, array of form values
	*/
	public $settings;
	public $values;
	
	public function main()
	{
		
		/**
		** Controller
		*/
		if ( cp::$GET['do'] )
		{
			$res = $this->doTask();
			
			if ( !$res )
				return false;
			
		}
		else
		{
			$this->showAll();
		}
		
		/**
		** Nav trees
		*/
		cp::cont()->navtree( cp::lang('tools', 'tools'), '?page=tools', true );
		
		return true;
		
	}
	
	/**
	** showAll() - shows list of group
	*/
	public function showAll()
	{
		
		/**
		** Display Vars
		*/
		cp::cont()->page['title'] = cp::lang('tools', 'man_tool');
		
		/**
		** Fetch all groups and prep for echo
		*/
		$rows = cp::db()->fetch_dep( array(
			'table' => 'tools',
			'where' => 'enabled=1',
		) );
		
		/**
		** Table settings
		*/
		$cols = $this->table();

		/**
		** Echo table
		*/
		cp::cont()->output = cp::call('dtools')->rowsToTable( $rows, $cols, 'all' );
		
	}
	
	/**
	** do() - does the task
	*/
	public function doTask()
	{
		
		cp::cont()->navtree( cp::lang('tools', 'run'), '' );
		
		$this->getTask( cp::$GET['do'] );
		
		cp::display()->vars['tool'] = $this->row;
		
		cp::cont()->page['title'] = cp::lang('tools', 'run_tools') . ': ' . $this->row['name'];
		
		return cp::methodCall( cp::call( 'tasks', 'apps/' . $this->row['folder'] . '/classes/tasks' ), $this->row['task'] );
		
	}	
	
	/**
	** getRow() - gets rows and settings
	*/
	public function getRow( $rowId )
	{				
		$this->row = cp::DB()->get( 'tools', $rowId );
		return $this;	
	}
	
	/**
	** getTask()
	*/
	public function getTask( $taskName )
	{
		$this->row = cp::db()->fetch_dep( array(
			'select'=> 'tools.*, modules.name AS folder',
			'table' => 'tools',
			'where'	=> 'task="'.$taskName.'"',
			'join'	=> array(
				'table'	=> 'modules',
				'where' => 'modules.unique_id=tools.modId',
				'type'	=> 'left',
			),
			'one'	=> true,
		) );
	}
	
	/**
	** table() - table settings to display all groups
	**
	**
	*/
	public function table()
	{
		
		return array(
			'name' => array(
				'lang_key'	=> 'task_name',
				'type'		=> 'norm',
				'width'		=> '250px',
			),
			'desc'		=> array(
				'lang_key'	=> 'desc',
				'type'		=> 'norm',
			),
			'opt'		=> array(
				'lang_key'	=> 'opt',
				'type'		=> 'opt',
				'items'		=> array( 'do' => 'task' ),
				'class'		=> 'cent',
				'width'		=> '150px',
			),
		);
		
	}
	
}

?>