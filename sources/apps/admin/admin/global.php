<?php

	/*===================================================
	//	NotionBB © All Rights Reserved
	//---------------------------------------------------
	//	NBB-Core
	//		by notionbb.com
	//---------------------------------------------------
	//	File Updated - 2-11-2020
	//=================================================*/

class admin_global extends controller {
	
	/**
	** Array of form settings, array of form values
	*/
	public $settings;
	public $values;
	
	public function main()
	{		
		
		/**
		** Display Vars
		*/
		cp::cont()->page['title'] = cp::lang('all', 'set');
		
		/**
		** Controller
		*/
		$r = cp::cont()->adminDo( get_class($this) );
		
		/**
		** Return if controller false
		*/
		if ( !$r )
			return false;			
		
		/**
		** Nav trees
		*/
		cp::cont()->navtree( cp::lang('all', 'glo_set'), '?page=global', true );
		
		return true;
		
	}
	
	/**
	** showAll() - shows the list of global cats
	**
	**
	*/
	public function showAll()
	{
		
		/**
		** Table Settings
		*/
		$cols = $this->table();
		
		/**
		** Get all global settings
		*/
		$cats = cp::db()->fetch_dep( array(
			'table'	=> 'globalcats',
			'where'	=> 'enabled=1',
			'order'	=> 'subcat, `order` asc',
			'key'	=> 'text_id',
		) );
		
		foreach ( $cats as $k => $v )
		{	
			$array[ $v['subcat'] ]['subcat'] = $v['subcat'];
			$array[ $v['subcat'] ]['rows'][] = $v;
		}
		
		cp::call('dtools')->page_but( cp::lang('all', 'rec_globs'), '?page=tools&do=cacheGlobals' );
		
		cp::cont()->output .= cp::call('dtools')->tabs( $array, $cols );
		
		/**
		** Create new setting based on form...
		*/
		if ( cp::set('dev_mode') )
		{
			cp::cont()->output .= $this->newSettingForm();
		}
		
	}
	
	/**
	** showForm() - 
	*/
	public function showForm()
	{
		
		if ( cp::$GET['edit'] == 'new' )
		{	
			$this->getSettings();	
			cp::cont()->page['title'] = cp::lang('all', 'creatin_glocat');
			cp::cont()->navtree( cp::lang('all', 'new'), '' );
			
			/**
			** Have we got a base group?
			*/			
			if ( cp::$POST['baseName'] )
			{
				
				$this->row = array(
					'name'	=> cp::$POST['name'],
				);
				
				/**
				** Lang fix...
				*/
				cp::cont()->page['title'] .= ' ' . cp::lang('all', 'base_on') . ' '. $this->row['name'];
				
			}
			
		}
		else
		{			
			
			/**
			** Gets current and setting
			*/
			$this->getSettings()->getRow( cp::$GET['edit'] );
			
			cp::cont()->page['title'] = cp::lang('all', 'editing_glocat').': '.$this->row['name'];
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
	** 
	*/
	public function processForm()
	{
		
		$this->getSettings();
		
		/**
		** Make settings database form
		*/
		$new = cp::call('dtools')->processSettings( $this->settings );
		
		$new['new']['active'] = 1;
		
		cp::db()->insert( 'globalcats', $new['new'] );
		
		cp::display()->splash( cp::lang('all', 'new_cat'), '?page=global');
		
		return false;
		
	}
	
	/**
	** showDeleteForm()
	*/
	public function showDeleteForm()
	{
		
		cp::cont()->navtree( cp::lang('all', 'del'), '' );
		
		cp::call('dtools')->caution( cp::lang('all', 'del_not_allowed') );		
		
	}
	
	/**
	** getSettings() - array for settings
	*/
	public function getSettings()
	{
		
		$this->settings = array(
			array(
				'name' => 'name',
				'title' => 'Category Title',
				'desc' => 'Enter the title of the setting category',
				'valueType' => 'field',
			),
			array(
				'name' => 'desc',
				'title' => 'Category Description',
				'desc' => 'Enter the description of the setting category',
				'valueType' => 'area',
			),
			array(
				'name' => 'subcat',
				'title' => 'Tab Language Title',
				'desc' => 'Enter the lang key of this setting\'s subcat. Enter "asystem" if unsure',
				'valueType' => 'field',
			),
			array(
				'name' => 'icon',
				'title' => 'Category Icon',
				'desc' => 'Enter the url for the icon',
				'valueType' => 'field',
				'valueDef'	=> 'images/globalicons/def.png',
			),
		);
		return $this;
		
	}
	
	/**
	** getRow() - gets rows and settings
	*/
	public function getRow( $rowId )
	{				
		$this->row = cp::DB()->get( 'globalcats', $rowId );
		return $this;	
	}	
	
	/**
	** table() - table settings to display all groups
	*/
	public function table()
	{
		
		return array(
			'icon'   => array(
				'type'		=> 'icon',
				'width'		=> '16',
				'class'		=> 'icon',
			),
			'name' => array(
				'type'		=> 'globalcat',
			),
			
		);
		
	}
	
	/**
	** newSettingForm()
	*/
	public function newSettingForm()
	{
		
		$settings = array(
			array(
				'name'			=> 'name',
				'title' 		=> 'Category Title',
				'desc'			=> 'Select a title for this category',
				'valueType'		=> 'field',
			),
		);
			
			
		return cp::display()->form(
			array(
				'name'		=> 'baseName',
				'submit'	=> 'Continue',
				'action'	=> '?page=global&edit=new',
				'title'		=> 'Create new category',
				'fieldA'	=> cp::call('dtools')->settingToFields( $settings ),
			),
			'small'
		);
				
		
	}
	
}

?>
