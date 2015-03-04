<?php

	/*===================================================
	//	CipherPixel © All Rights Reserved
	//---------------------------------------------------
	//	CP-Core
	//		by cipherpixel.net
	//---------------------------------------------------
	//	File created: September 09, 2014 
	//=================================================*/

class admin_ranks extends controller {
	
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
		** Show list of rows
		*/
		{

			/**
			** Display Vars
			*/
			cp::cont()->page['title'] = cp::lang('members', 'man_ranks');
			
			cp::call('dtools')->page_but( cp::lang('members', 'create_rank'), '?page=ranks&edit=new' );

			/**
			** Echo table
			*/
			$this->showTable();
			
		}	
		
		/**
		** Nav trees
		*/
		cp::cont()->navtree( cp::lang('members', 'ranks'), '?page=ranks', true );
		
		return true;
		
	}
	
	/**
	** getSettings() - gets settings
	*/
	public function getSettings()
	{
		$this->settings = array(
			array(
				'name' => 'title',
				'title' => 'Rank Title',
				'desc' => 'Enter the title of your new rank',
				'valueType' => 'field',
			),
			array(
				'name' => 'minPosts',
				'title' => 'Posts required',
				'desc' => 'Required number of posts to achieve this rank',
				'valueType' => 'field',
			),
			array(
				'name' => 'pip',
				'title' => 'Number of Pips',
				'desc' => 'Enter number of pips to display.<br />Can also enter a filename from style/***/images/',
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
		$this->row = cp::DB()->get( 'ranks', $rowId );
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
			cp::cont()->page['title'] = cp::lang('members', 'creatin_rank');
			cp::cont()->navtree( cp::lang('all', 'new'), '' );
			
		}
		else
		{			
			
			/**
			** Gets current and setting
			*/
			$this->getSettings()->getRow( cp::$GET['edit'] );
			
			cp::cont()->page['title'] = cp::lang('members', 'editing_rank').': '.$this->row['title'];
			cp::cont()->navtree( cp::lang('all', 'edit'), '' );
			
		}
		
		cp::cont()->output .= cp::display()->form( array(
			'name'		=> 'update',
			'submit'	=> cp::lang('members', ( ( cp::$GET['edit'] == 'new' ) ? 'create_rank': 'edit_rank' ) ),
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
			cp::db()->insert( 'ranks', $new['new'] );
			$lang = cp::lang( 'members', 'new_rank' ); 
		}
		else
		{
			cp::db()->update_dep( 'ranks', cp::$GET['edit'], $new['new'] );
			$lang = cp::lang( 'members', 'up_rank' ); 
		}
		
		/**
		** Send splash screen.
		*/
		cp::display()->splash( $lang, '?page=ranks');

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
		
		cp::cont()->page['title'] = cp::lang('members', 'del_rank').': '.$this->row['title'];
		cp::cont()->navtree( cp::lang('all', 'del'), '' );
		
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
		** Delete Group
		*/
		cp::db()->delete( 'ranks', $this->row['id'] );	
		
		/**
		** Send splash screen.
		*/
		cp::display()->splash( cp::lang( 'members', 'del_rank_done' ), '?page=ranks');

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
			'table' => 'ranks',
			'order'	=> 'minPosts asc',
		) );
		
		/**
		** Table settings
		*/
		$cols = array(
			'title'		=> 'rank_name',
			'minPosts'	=> array(
				'lang_key'	=> 'posts_needed',
				'type'		=> 'norm',
				'class'		=> 'cent',
			),
			'pip'	=> array(
				'lang_key'	=> 'pip',
				'type'		=> 'pip',
				'class'		=> 'cent',
			),
			'opt'		=> array(
				'lang_key'	=> 'opt',
				'type'		=> 'opt',
				'items'		=> array( 'edit' => 'id', 'del' => 'id' ),
				'class'		=> 'cent',
				'width'		=> '150px',
			),
		);
		
		cp::cont()->output = cp::call('dtools')->rowsToTable( $rows, $cols, 'members' );
		
	}
	
}

?>