<?php

	/*===================================================
	//	NotionBB © All Rights Reserved
	//---------------------------------------------------
	//	NBB-Core
	//		by notionbb.com
	//---------------------------------------------------
	//	File Updated - 2-11-2020
	//=================================================*/

class admin_group extends controller {
	
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
		if ( !cp::cont()->adminDo( get_class($this) ) )
			return false;
		
		/**
		** Nav trees
		*/
		cp::cont()->navtree( cp::lang('all', 'group'), '?page=group', true );
		
		return true;
		
	}
	
	/**
	** showAll() - shows list of group
	*/
	public function showAll()
	{
		
		/* Title */
		cp::cont()->page['title'] = cp::lang('all', 'man_group');
		
		//-----------------------------------
		// Table 2.0
		//-----------------------------------
		
		$table = cp::callAppClass('object_table');
		
		$table->rows 	= cp::db()->fetch( array( 'table' => 'groups' ) );		
		$table->cols 	= array('gname' => 'name', 'members' => 'userCount', 'acp_acc' => 'acp', 'glo_mod' => 'globalMod');
		$table->classes = array('userCount' => 'cent');
		$table->options	= array('edit' => 'id', 'del' => 'id');
		
		$table->parse = array( 	'name' => function( $row ) {
											return $row['htmlPrefix'] . $row['name'] . $row['htmlSuffix'];
										},
								'acp'		=> 'tick',
								'globalMod'	=> 'tick',
							);
		
		cp::cont()->output = $table->make();
		
		//-----------------------------------
		// Form to create a new group
		//-----------------------------------
		
		cp::cont()->output .= $this->newgroupBasedOn();
		
	}
	
	/**
	** getSettings() - gets settings
	*/
	public function getSettings()
	{
		$this->settings = cp::DB()->fetch_dep( array(
			'table' => 'groupsettings',
			'key'	=> 'name',
		) );
		return $this;
	}
	
	/**
	** getRow() - gets rows and settings
	*/
	public function getRow( $rowId )
	{				
		$this->row = cp::DB()->get( 'groups', $rowId );
		return $this;	
	}
	
	/**
	** showForm() - shows group form
	*/
	public function showForm()
	{		
		
		if ( cp::$GET['edit'] == 'new' )
		{	
			$this->getSettings();	
			cp::cont()->page['title'] = cp::lang('all', 'creatin_group');
			cp::cont()->navtree( cp::lang('all', 'new'), '' );
			
			/**
			** Have we got a base group?
			*/
			if ( cp::$POST['baseId'] )
			{
				$this->getRow( cp::$POST['baseId'] );
				
				/**
				** Lang fix...
				*/
				cp::cont()->page['title'] .= ' ' . cp::lang('all', 'base_on') . ' '. $this->row['name'];
				
				/**
				** Unset a few rows that won't be copied
				*/
				unset( $this->row['name'], $this->row['htmlPrefix'], $this->row['htmlSuffix'] );
				
			}
			
		}
		else
		{			
			
			/**
			** Gets current and setting
			*/
			$this->getSettings()->getRow( cp::$GET['edit'] );
			
			cp::cont()->page['title'] = cp::lang('all', 'editing_group').': '.$this->row['name'];
			cp::cont()->navtree( cp::lang('all', 'edit'), '' );
			
		}

		cp::cont()->output .= cp::display()->form( array(
			'name'		=> 'update',
			'submit'	=> cp::lang('all', ( ( cp::$GET['edit'] == 'new' ) ? 'create_group': 'edit_group' ) ),
			'fieldA'	=> cp::call('dtools')->settingToFields( $this->settings, $this->row, 'name', true ),
			),
			'tab'
		);
		
	}
	
	/**
	** processForm() - shows group form
	*/
	public function processForm()
	{
		
		if ( cp::$GET['edit'] == 'new' )
			$newRow = true;
			
		$this->getSettings();
		
		/**
		** Make settings database form
		*/
		$new = cp::call('dtools')->processSettings( $this->settings );
		
		/**
		** Update DB
		*/
		if ( $newRow )
		{
			cp::db()->insert( 'groups', $new['new'] );
			$lang = cp::lang( 'all', 'new_group' ); 
		}
		else
		{
			cp::db()->update_dep( 'groups', cp::$GET['edit'], $new['new'] );
			$lang = cp::lang( 'all', 'up_group' ); 
		}
		
		/**
		** Send splash screen.
		*/
		cp::display()->splash( $lang, '?page=group');

		/**
		** Makes main() false, which makes splash skip
		*/
		return false;
		
	}
	
	/**
	** showDeleteForm() - shows delete form with an option to move members
	*/
	public function showDeleteForm()
	{		
		
		$this->getRow( cp::$GET['del'] );
		
		cp::cont()->page['title'] = cp::lang('all', 'delete_group').': '.$this->row['name'];
		cp::cont()->navtree( cp::lang('all', 'del'), '' );
		
		cp::call('dtools')->caution( cp::lang('all', 'del_caution') );
		
		$settings = array(
			array(
				'name'			=> 'newGroupId',
				'title' 		=> 'Move Members',
				'desc'			=> 'Which group should current members be moved to?',
				'valueType'		=> 'drop',
				'valueFunction'	=> 'getGroups',
				'valueDef'		=> 2,
				'valueExc'		=> $this->row['id'],
			),
		);
			
			
		cp::cont()->output .= cp::display()->form(
			array(
				'name'		=> 'delete',
				'submit'	=> 'Delete',
				'submitC'	=> 'red',
				'title'		=> 'Delete Group',
				'fieldA'	=> cp::call('dtools')->settingToFields( $settings ),
			),
			'small'
		);
		
	}
	
		/**
	** processForm() - shows group form
	*/
	public function deleteRow()
	{
		
		/**
		** Get current row
		*/	
		$this->getRow( cp::$GET['del'] );
		
		/**
		** Update Members from group
		*/
		$newGroup = cp::$POST['newGroupId'];
		
		//				Table		groupId =			set groupId = newGroup		groupId = rowId
		cp::db()->update_dep( 'users', $this->row['id'], array( 'groupId' => $newGroup ), 'groupId' );
		
		/**
		** Delete Group
		*/
		cp::db()->delete( 'groups', $this->row['id'] );	
		
		/**
		** Send splash screen.
		*/
		cp::display()->splash( cp::lang( 'all', 'del_group' ), '?page=group');

		/**
		** Makes main() false, which makes splash skip
		*/
		return false;
		
	}
	
	/**
	** 
	**
	**
	*/
	public function newgroupBasedOn()
	{
		
		$settings = array(
			array(
				'name'			=> 'baseId',
				'title' 		=> 'Base new group on',
				'desc'			=> 'Pick a group to base the new group on',
				'valueType'		=> 'drop',
				'valueFunction'	=> 'getGroups',
				'valueDef'		=> 2,
			),
		);
			
			
		return cp::display()->form(
			array(
				'name'		=> 'baseG',
				'submit'	=> 'Continue',
				'action'	=> '?page=group&edit=new',
				'title'		=> 'Create new group',
				'fieldA'	=> cp::call('dtools')->settingToFields( $settings ),
			),
			'small'
		);
				
		
	}
	
}

?>
