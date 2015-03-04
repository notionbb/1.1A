<?php

	/*===================================================
	//	CipherPixel © All Rights Reserved
	//---------------------------------------------------
	//	CP-Core
	//		by cipherpixel.net
	//---------------------------------------------------
	//	File created: September 09, 2014 
	//=================================================*/

class admin_settings extends controller {
	
	/**
	** Array of form settings, array of form values
	*/
	public $settings;
	public $values;
	
	public function main()
	{
		
		//-----------------------------------
		// Init
		//-----------------------------------
		
		/* Support class */
		cp::callAppClass( 'class_forums', 'forum' );
		
		//-----------------------------------
		// Run
		//-----------------------------------
				
		/* Controller */
		$r = cp::cont()->adminDo( get_class($this) );
		
		if ( !$r )
			return false;			
		
		//-----------------------------------
		// Display
		//-----------------------------------
		
		cp::cont()->navtree( cp::lang('all', 'glo_set'), '?page=global', true );
		
		return true;
		
	}
	
	/**
	** showForm() - 
	*/
	public function showForm()
	{

		if ( cp::$GET['edit'] == 'new' )
		{

			cp::cont()->page['title'] = cp::lang('all', 'adding_set');
			cp::cont()->navtree( cp::lang('all', 'new'), false );
			
			/**
			** Add new setting to category
			*/
			$this->getNewSettings();

			/**
			** Display form
			*/
			cp::cont()->output .= cp::display()->form( array(
				'name'		=> 'update',
				'submit'	=> cp::lang('all', 'up_set'),
				'fieldA'	=> cp::call('dtools')->settingToFields( $this->settings, false, 'name', true ),
				),
				'tab'
			);
			
		}
		else
		{
			
			/**
			** Update all settings in category
			*/
			
			/**
			** Get settings and row
			*/
			$this->getSettings()->getRow( cp::$GET['edit'] );
			
			/**
			** Display Vars
			*/
			cp::cont()->page['title'] = cp::lang('all', 'up_set'). ': '.$this->row['name'];
			
			cp::cont()->output .= cp::display()->form( array(
				'name'		=> 'update',
				'submit'	=> cp::lang('all', 'up_set'),
				'fieldA'	=> cp::call('dtools')->settingToFields( $this->settings, $this->row, 'name', true ),
				),
				'tab'
			);
			
			/**
			** Add new "setting row" form...
			*/
			if ( cp::set('dev_mode') )
			{
				cp::cont()->output .= $this->newSettingForm();
			}
			
			/**
			** Navtree!
			*/			
			cp::cont()->navtree( $this->row['name'], '?page=settings&edit=' . cp::$GET['edit'] );
			cp::cont()->navtree( cp::lang('all', 'edit'), false );
			
		}
				
		
	}
	
	/**
	** processForm()
	*/
	public function processForm()
	{
		
		if ( cp::$GET['edit'] == 'new' )
		{

			/**
			** Add new setting to category
			*/
			
			$this->getNewSettings();
			
			$new = cp::call('dtools')->processSettings( $this->settings );
			
			$check = cp::db()->fetch_dep( array(
				'table'	=> 'globals',
				'where'	=> 'arrayTitle="'.$new['new']['arrayTitle'].'"',
				'one'	=> true,
			) );
			
			if ( $check )
			{
				$this->showForm();
				cp::call('dtools')->caution( cp::lang('all', 'warn_unique') );
				return true;
			}
			
			/**
			** Insert
			*/			
			cp::db()->insert( 'globals', $new['new'] );
			
			/**
			** Send splash screen.
			*/
			cp::display()->splash( cp::lang( 'all', 'new_set' ), '?page=settings&edit=' . cp::$POST['catId'] );			
			
		}
		else
		{
		
			/**
			** Update Settings in category
			*/
			
			$this->getSettings();

			$new = cp::call('dtools')->processSettings( $this->settings );
			
			foreach( $new['new'] as $id => $value )
			{
			
				/**
				** Don't update if setting hasnt changed
				*/
				if ( $this->settings[ $id ]['value'] == $value ) continue;
				
				/**
				** Update
				*/
				cp::db()->update_dep( 'globals', $id, array( 'value' => $value ) );
					
			}
			
			/* Recache Globals */
			cp::cache()->task_cache_globals();
			
			/**
			** Send splash screen.
			*/
			cp::display()->splash( cp::lang( 'all', 'up_set_cat' ), '?page=global');
			
		}
		
	}
	
	/**
	** getSettings() - array for settings
	*/
	public function getSettings()
	{
		
		$this->settings = cp::db()->fetch_dep( array(
			'table'	=> 'globals',
			'where'	=> 'catId="'.cp::$GET['edit'].'"',
		) );
		
		return $this;
		
	}
	
	/**
	** getRow() - gets rows and settings
	*/
	public function getRow( $rowId )
	{				

		$this->row = cp::db()->fetch( array(
			'table'	=> 'globalcats',
			'where' => array('text_id' => $rowId ),
			'key'	=> 'text_id',
			'r'		=> 'one',
		) );
		return $this;	
	}
	
	/**
	** getNewSettings
	*/
	public function getNewSettings()
	{
		
		$this->settings = array(
			'catId' => array(
				'name' 			=> 'catId',
				'subcat'		=> 'system',
				'title'			=> 'Category',
				'desc'			=> '<i>Required -</i> Which category to put this setting into',
				'valueType' 	=> 'drop',
				'valueFunction' => 'getSettingGroups',
				'valueDef'		=> cp::$GET['baseId'],
			),
			'arrayTitle' => array(
				'name' 			=> 'arrayTitle',
				'subcat'		=> 'system',
				'title'			=> 'Setting array key',
				'desc'			=> '<i>Required -</i> One word. Letters only. The Global array key to access the field, i.e. $globals[ KEY ]',
				'valueType' 	=> 'field',
				'valueDef'		=> cp::$POST['arrayTitle'],
			),
			'modId' => array(
				'name' 			=> 'modId',
				'subcat'		=> 'system',
				'title'			=> 'Mod Id',
				'desc'			=> '<i>Required -</i> Enter the Unique Id of the mod this setting relates to.<br />Usually net.cipherpixel.admin',
				'valueType' 	=> 'field',
				'valueDef'		=> cp::$POST['arrayTitle'],
			),
			'title' => array(
				'name' 			=> 'title',
				'subcat'		=> 'aethstetic',
				'title'			=> 'Setting title',
				'desc'			=> 'The title of the setting. Aesthetic only.',
				'valueType' 	=> 'field',
			),
			'desc' => array(
				'name' 			=> 'desc',
				'subcat'		=> 'aethstetic',
				'title'			=> 'Setting description',
				'desc'			=> 'Text that appears under setting title. Aesthetic only.',
				'valueType' 	=> 'area',
			),
			'value' => array(
				'name' 			=> 'value',
				'subcat'		=> 'value',
				'title'			=> 'Setting value',
				'desc'			=> 'Can be changed once created. Can be left blank.',
				'valueType' 	=> 'field',
			),
			'valueType' => array(
				'name' 			=> 'valueType',
				'subcat'		=> 'value',
				'title'			=> 'Value type of the setting',
				'desc'			=> '<i>Required -</i> Can be changed once created',
				'valueType' 	=> 'drop',
				'valueOptions'	=> array('Yes or No Button' => 'onoff', 'Dropdown' => 'drop', 'Text field' => 'field', 'Text area' => 'area', 'List' => 'list', 'HTML Textfield' => 'field_entity' ),
			),
			'valueFunction' => array(
				'name' 			=> 'valueFunction',
				'subcat'		=> 'value',
				'title'			=> 'Function setting calls',
				'desc'			=> 'Set in admin/classes/formfuncs. Usually for drop downs. Leave blank if not using.',
				'valueType' 	=> 'field',
			),
			'valueOptions' => array(
				'name' 			=> 'valueOptions',
				'subcat'		=> 'value',
				'title'			=> 'Value Options',
				'desc'			=> 'Enter options. One Per line<br />Format: Option Text | Option Value',
				'valueType' 	=> 'area_array',
			),
			
		);
		
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
	** newSettingForm() - new CATEGORY form
	*/
	public function newSettingForm()
	{
		
		$settings = array(
			'arrayTitle' => array(
				'name' 			=> 'arrayTitle',
				'subcat'		=> 'system',
				'title'			=> 'Setting array key',
				'desc'			=> 'One word. Letters only. The Global array key to access the field, i.e. $globals[ KEY ]',
				'valueType' 	=> 'field',
			),
		);
			
			
		return cp::display()->form(
			array(
				'name'		=> 'baseName',
				'submit'	=> 'Continue',
				'action'	=> '?page=settings&edit=new&baseId=' . $this->row['text_id'],
				'title'		=> cp::lang('all', 'create_set'),
				'fieldA'	=> cp::call('dtools')->settingToFields( $settings ),
			),
			'small'
		);				
		
	}
	
}

?>