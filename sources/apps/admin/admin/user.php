<?php

	/*===================================================
	//	CipherPixel © All Rights Reserved
	//---------------------------------------------------
	//	CP-Core
	//		by cipherpixel.net
	//---------------------------------------------------
	//	File created: September 09, 2014 
	//=================================================*/

class admin_user extends controller {
	
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
		cp::cont()->navtree( cp::lang('all', 'members'), '?page=user', true );
		
		return true;
		
	}
	
	/**
	** showAll() - shows list of group
	*/
	public function showAll()
	{
		
		/**
		** lala
		*/
		
	}
	
	/**
	** getSettings() - gets settings
	*/
	public function getSettings()
	{
		$this->settings = cp::DB()->fetch_dep( array(
			'table' => 'usersettings',
			'key'	=> 'name',
			'order' => '`order` desc',
		) );
		
		return $this;
		
	}
	
	/**
	** getRow() - gets rows and settings
	*/
	public function getRow( $rowId )
	{				
		$this->row = cp::call('members')->fetchMember( $rowId );
		return $this;	
	}
	
	/**
	** showForm() - shows edit form
	*/
	public function showForm()
	{		
		
		if ( cp::$GET['edit'] == 'new' )
		{	
			
			$this->getSettings();	
			cp::cont()->page['title'] = cp::lang('all', 'creatin_mem');
			cp::cont()->navtree( cp::lang('all', 'new'), '' );
			
		}
		else
		{			
			
			/**
			** Gets current and setting
			*/
			$this->getSettings()->getRow( cp::$GET['edit'] );
			
			cp::cont()->page['title'] = cp::lang('all', 'editing_mem').': '.$this->row['displayName'];
			cp::cont()->navtree( cp::lang('all', 'edit'), '' );
			
			cp::call('dtools')->page_but( cp::lang('members', 'em_mem'), '?page=emulate' );
			cp::call('dtools')->page_but( cp::lang('members', 'del_mem'), '?page=user&del=' . $this->row['id'] );
			
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
		else
			$this->getRow( cp::$GET['edit'] );
			
		$this->getSettings();
		
		/**
		** Make settings database form
		*/
		$new = cp::call('dtools')->processSettings( $this->settings );
		
		//cp::call('members')->create( $new );
		
		//print_r($new);
		
		/**
		** Update DB
		*/
		if ( $newRow )
		{
			/**
			** Try to create member, if true it means we have an error
			*/
			if ( ( $err = cp::call('members')->create( $new ) ) )
			{
				cp::call('dtools')->caution($err);
				$this->showForm();
				return true;
			}
				
			/**
			** Lang
			*/
			$lang = cp::lang( 'all', 'new_mem' );
			
			$id = cp::call('members')->last_id;
		}
		else
		{
			/**
			** Update Member
			*/
			cp::db()->update_dep( 'users', cp::$GET['edit'], $new['new'] );
			
			/**
			** Did we change the password?
			*/
			if ( $new['sec'] )
				cp::db()->update_dep( 'users_secure', cp::$GET['edit'], $new['sec'] );
			
			/**
			** Set lang pack
			*/
			$lang = cp::lang( 'all', 'up_mem' );
			
			$id = cp::$GET['edit'];
			
		}
		
		cp::cont()->log( 'users', $id, $this->row, $new['new'] );
		
		/**
		** Send splash screen.
		*/
		cp::display()->splash( $lang, '?page=user');

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
		
		cp::cont()->page['title'] = cp::lang('all', 'delin_mem').': '.$this->row['displayName'];
		cp::cont()->navtree( cp::lang('all', 'del'), '' );
	
		/**
		** Are we allowed to delete?
		*/
		if ( !$this->checkDelete() )
			return;
		
		/**
		** Show delete form
		*/
		cp::call('dtools')->caution( cp::lang('all', 'del_mem_caution') );			
			
		cp::cont()->output .= cp::display()->form(
			array(
				'name'		=> 'delete',
				'submit'	=> 'Confirm Delete',
				'submitC'	=> 'red',
			),
			'but'
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
		** Are we allowed to delete?
		*/
		if ( !$this->checkDelete() )
			return;
		
		/**
		** Delete Group
		*/
		cp::db()->delete( 'users', $this->row['id'] );
		cp::db()->delete( 'users_secure', $this->row['id'] );	
		
		/**
		** Send splash screen.
		*/
		cp::display()->splash( cp::lang( 'all', 'del_mem' ), '?page=user');

		/**
		** Makes main() false, which makes splash skip
		*/
		return false;
		
	}
	
	/**
	** checkDelete() - checks if the row can be deleted, returns true if can.
	*/
	public function checkDelete()
	{
		
		/**
		** Cannot delete users with ACP access
		*/
		if ( $this->row['acp'] )
		{
			cp::call('dtools')->caution( cp::lang('all', 'del_root') );
			return false;
		}
			
			
		return true;
			
	}
	
	/**
	** table() - table settings to display all groups
	**
	**
	*/
	public function table()
	{
		
		return array(
			'htmlGroup'		=> 'gname',
			'userCount' => array(
				'lang_key'	=> 'members',
				'type'		=> 'norm',
				'class'		=> 'cent',
				'width'		=> '200px',
			),
			'acp'		=> array(
				'lang_key'	=> 'acp_acc',
				'type'		=> 'tick',
				'class'		=> 'cent',
				'width'		=> '200px',
			),
			'globalMod'	=> array(
				'lang_key'	=> 'glo_mod',
				'type'		=> 'tick',
				'class'		=> 'cent',
				'width'		=> '200px',
			),
			'opt'		=> array(
				'lang_key'	=> 'opt',
				'type'		=> 'opt',
				'items'		=> array( 'edit' => 'id', 'del' => 'id' ),
				'class'		=> 'cent',
				'width'		=> '150px',
			),
		);
		
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