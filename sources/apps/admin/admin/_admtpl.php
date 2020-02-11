<?php

	/*===================================================
	//	NotionBB © All Rights Reserved
	//---------------------------------------------------
	//	NBB-Core
	//		by notionbb.com
	//---------------------------------------------------
	//	File Updated - 2-11-2020
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
		
		if ( !cp::cont()->adminDo( get_class($this) ) )
		{
			return false;
		}
		
		/* Display */
		cp::cont()->navtree( cp::lang('all', 'group'), '?page=group', true );
		
		return true;
		
	}
	
	/**
	** Shows list of groups
	** 
	** @return	bool
	*/
	public function showAll()
	{
		
		
		
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
		$this->row = cp::db()->get( '', $rowId );
		
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
	** Table settings for showAll() page
	** 
	** @return	array
	*/
	public function table()
	{
		
		return array(
			
		);
		
	}
	
}

?>
