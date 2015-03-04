<?php

	/*===================================================
	//	CipherPixel © All Rights Reserved
	//---------------------------------------------------
	//	CP-Core
	//		by cipherpixel.net
	//---------------------------------------------------
	//	File created: September 09, 2014 
	//=================================================*/

class admin_perm extends controller {
	
	/**
	** Array of form settings, array of form values
	*/
	public $settings;
	public $values;
	
	public function main()
	{

		/**
		** Editing/new?
		*/
		if ( cp::$GET['edit'] )
		{
			
			if ( CP::$POST['update'] )
			{
				return $this->processForm();
			}
			else
			{				
				$this->showForm();				
			}
			
		}
		
		else
		
		/**
		** Delete Row
		*/
		if ( cp::$GET['del'] )
		{
			
			if ( CP::$POST['delete'] )
			{
				return $this->deleteRow();
			}
			else
			{				
				$this->showDeleteForm();				
			}
			
		}
		
		else
		
		/**
		** Show list of perm masks
		*/
		{

			/**
			** Display Vars
			*/
			cp::cont()->page['title'] = cp::lang('perm', 'man_perm');

			/**
			** Echo table
			*/
			$this->showTable();
			
			/**
			** Create new group based on form...
			*/
			cp::cont()->output .= $this->newBasedOn();
			
		}	
		
		/**
		** Nav trees
		*/
		cp::cont()->navtree( cp::lang('perm', 'perm'), '?page=perm', true );
		
		return true;
		
	}
	
	/**
	** getSettings() - gets settings
	*/
	public function getSettings()
	{
		$this->settings = array(
			array(
				'name' => 'name',
				'title' => 'Permission Set Title',
				'desc' => 'Enter the name of the permission set',
				'valueType' => 'field',
			),
		);
		return $this;
	}
	
	/**
	** getRow() - gets rows and settings
	*/
	public function getRow( $rowId )
	{				
		$this->row = cp::DB()->get( 'perm', $rowId );
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
			cp::cont()->page['title'] = cp::lang('perm', 'creatin_perm');
			cp::cont()->navtree( cp::lang('all', 'new'), '' );
			
			/**
			** Have we got a base mask?
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
				unset( $this->row['name'] );
				
			}
			
		}
		else
		{			
			
			/**
			** Gets current and setting
			*/
			$this->getSettings()->getRow( cp::$GET['edit'] );
			
			cp::cont()->page['title'] = cp::lang('perm', 'editing_perm').': '.$this->row['name'];
			cp::cont()->navtree( cp::lang('all', 'edit'), '' );
			
		}

		cp::cont()->output .= cp::display()->form( array(
			'name'		=> 'update',
			'submit'	=> cp::lang('perm', ( ( cp::$GET['edit'] == 'new' ) ? 'create_mask': 'edit_mask' ) ),
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
			cp::db()->insert( 'perm', $new['new'] );
			$lang = cp::lang( 'perm', 'new_mask' ); 
		}
		else
		{
			cp::db()->update_dep( 'perm', cp::$GET['edit'], $new['new'] );
			$lang = cp::lang( 'perm', 'up_mask' ); 
		}
		
		/**
		** Send splash screen.
		*/
		cp::display()->splash( $lang, '?page=perm');

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
		
		cp::cont()->page['title'] = cp::lang('perm', 'del_mask').': '.$this->row['name'];
		cp::cont()->navtree( cp::lang('perm', 'del'), '' );
		
		cp::call('dtools')->caution( cp::lang('all', 'del_caution') );
		
		$settings = array(
			array(
				'name'			=> 'newId',
				'title' 		=> 'Move Groups',
				'desc'			=> 'Which mask should current groups inherit?',
				'valueType'		=> 'drop',
				'valueFunction'	=> 'getPerms',
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
		$newRowId = cp::$POST['newId'];
		
		cp::db()->update_dep( 'groups', $this->row['id'], array( 'permSet' => $newRowId ), 'permSet' );
		
		/**
		** Delete Group
		*/
		cp::db()->delete( 'perm', $this->row['id'] );	
		
		/**
		** Send splash screen.
		*/
		cp::display()->splash( cp::lang( 'perm', 'del_mask_done' ), '?page=perm');

		/**
		** Makes main() false, which makes splash skip
		*/
		return false;
		
	}
	
	/**
	** showTable() - table settings to display all groups
	**
	**
	*/
	public function showTable()
	{
		
		/**
		** Fetch all groups and prep for echo
		*/
		$rows = cp::db()->fetch_dep( array(
			'table' => 'perm',
		) );
		
		/**
		** Table settings
		*/
		$cols = array(
			'name'		=> 'perm_name',
			'groups'	=> array(
				'lang_key'	=> 'used_by',
				'type'		=> 'arrayToValue',
				'class'		=> 'cent',
				'table'		=> 'groups',
				'dispCol'	=> 'name',
				'keyForArr'	=> 'gIds',
			),
			'opt'		=> array(
				'lang_key'	=> 'opt',
				'type'		=> 'opt',
				'items'		=> array( 'edit' => 'id', 'del' => 'id' ),
				'class'		=> 'cent',
				'width'		=> '150px',
			),
		);
		
		/**
		** Need groups to show more extra information on table
		*/
		$groups = cp::db()->fetch_dep( array(
			'table' => 'groups',
		) );
		
		foreach ( $groups as $id => $array )
		{
			$rows[ $array['permSet'] ]['gIds'][] = $array['id'];
		}
		
		cp::cont()->output = cp::call('dtools')->rowsToTable( $rows, $cols, 'perm' );
		
	}
	
	/**
	** 
	**
	**
	*/
	public function newBasedOn()
	{
		
		$settings = array(
			array(
				'name'			=> 'baseId',
				'title' 		=> 'Base new mask on',
				'desc'			=> 'Pick a massk to base the new permission mask on',
				'valueType'		=> 'drop',
				'valueFunction'	=> 'getPerms',
			),
		);
			
			
		return cp::display()->form(
			array(
				'name'		=> 'base',
				'submit'	=> 'Continue',
				'action'	=> '?page=perm&edit=new',
				'title'		=> 'Create new mask',
				'fieldA'	=> cp::call('dtools')->settingToFields( $settings ),
			),
			'small'
		);
				
		
	}
	
}

?>