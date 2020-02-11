<?php

	/*===================================================
	//	NotionBB © All Rights Reserved
	//---------------------------------------------------
	//	NBB-Core
	//		by notionbb.com
	//---------------------------------------------------
	//	File Updated - 2-11-2020
	//=================================================*/

class admin_module extends controller {
	
	/**
	** Array of modules from db
	** 
	** @var		array
	*/
	private	$modules;
	
	/**
	** Tables that require toggling of rows
	** 
	** @var		array
	*/
	private	$toggle_tables = array( 'tools', 'globalcats', 'groupsettings', 'usersettings', );
	
	public function main()
	{
		
		//-----------------------------------
		// Controller
		//-----------------------------------
		
		/* Recache */
		if ( cp::$GET['rec'] == 'all' )
		{
			if ( !$this->doRecache() ) return false;
		}
		
		else
		
		/* Toggle a module on off */
		if ( cp::$GET['toggle'] )
		{
			$r = $this->toggle_module( cp::$GET['toggle'] );
			
			if ( !$r )
			{
				cp::display()->splash( cp::lang( 'all', 'mod_toggled' ), '?page=module' );
				return false;
			}
		}
		
		else
		
		/* Set a default app */
		if ( cp::$GET['default'] )
		{
			if ( !$this->set_default(  cp::$GET['default'] ) ) return false;
		}
		
		else
		
		/* Install / Upgrade */
		if ( cp::$GET['install'] )
		{
			if ( !$this->install( cp::$GET['install'] ) ) return false;
		}
		
		else
		
		{
			$this->showAll();
		}
		
		//-----------------------------------
		// Display
		//-----------------------------------
		
		/* Navtree */
		cp::cont()->navtree( cp::lang('all', 'mods'), '?page=module', true );
		
		return true;
		
	}
	
	/**
	** Recache Modules
	** 
	** @return	bool
	*/
	private function doRecache()
	{
		
		cp::cache()->task_cache_modules();
		
		cp::display()->splash( cp::lang( 'all', 'mod_recached' ), '?page=module' );
		
		return false;
		
	}
	
	/**
	** Enable/disable a module
	** Nb: Called from installer
	** 
	** @param	string/array	$name		Module Name
	** @param	bool			$ow_stat	Set to true to manually set the status
	** @param	int				$stat		Set to 1 to enable or 0 to disable
	** @return	bool
	*/
	public function toggle_module( $name, $ow_stat=false, $stat=false )
	{
		
		//-----------------------------------
		// Init
		//-----------------------------------
		
		$this->get_modules();
		
		$module = $this->modules[ $name ];
		
		/* Protect CP-Core */
		if ( $name == 'admin' )
		{
			cp::call('dtools')->caution( cp::lang('all', 'mod_c_adm') );
			return true;
		}
		
		//-----------------------------------
		// Toggle module
		//-----------------------------------
		
		$stat_cur	= $module['status'];
		
		if ( $ow_stat )
		{
			$stat_new = $stat;
		}
		else
		{
			$stat_new = ( $stat_cur ) ? 0: 1;
		}
		
		foreach( $this->toggle_tables as $table )
		{
			cp::db()->update( $table, array( 'modId' => $module['unique_id'] ), array( 'enabled' => $stat_new ) );
		}
		
		cp::db()->update( 'modules', $module['id'], array( 'status' => $stat_new ) );
		
		//-----------------------------------
		// Rebuild Caches
		//-----------------------------------
		
		cp::cache()->task_cache_modules();
		
		return false;
		
	}
	
	/**
	** Set default module
	** 
	** @return	bool
	*/
	private function set_default( $name )
	{
		
		cp::call('cache')->put( 'conf', array('default_app'=>$name), false, ROOT .'/conf.php' );
		
		cp::display()->splash( cp::lang( 'all', 'mod_deffed' ), '?page=module' );

		return false;
		
	}
	
	/**
	** Install or update an application
	** 
	** @param	string	$name	Name of application
	** @return	bool
	*/
	private function install( $name )
	{
		
		$this->get_modules();
		
		/* Installer class */
		$install_class = cp::call( 'app_install_' . $name, 'apps/' . $name . '/classes/' . $name .'_install' );		
		$install_info = $install_class->info();
		
		//-----------------------------------
		// Check
		//-----------------------------------
		
		if ( $this->modules[ $name ] )
		{
			
			/* Don't check version if we're already in the install flow */
			if ( !cp::$GET['start'] AND version_compare( $install_info['ver'], $this->modules[ $name ]['ver'] ) < 1 )
			{					
				cp::call('dtools')->caution( cp::lang('all', 'mod_c_ins') );
				return true;
			}
			
		}
		else
		{
			$install = true;
			$start 	 = 'install';
		}
		
		//-----------------------------------
		// Are we installing or updating?
		//-----------------------------------
		
		if ( $start == 'install' OR cp::$GET['start'] == 'install' )
		{
			$install = true;
			$start 	 = 'install';
		}
		else
		{
			$start = 'true';
		}
		
		//-----------------------------------
		// Install
		//-----------------------------------
				
		if ( cp::$GET['start'] )
		{
			
			if ( cp::$GET['do'] == '4' )
			{
				
				$this->toggle_module( $install_info['name'], true, 1 );				
				
				cp::display()->splash( cp::lang('update', 'done'), '?page=module');
				return false;
				
			}
			else
			{
			
				$report = cp::call('port')->install_flow( $install_class, $install );
				
				foreach( $report as $array )
				{
					$array['lang'] = cp::lang( 'update', 'rep_'.$array['type'] );
					cp::display()->vars['report'] = $array;
					$report_html .= cp::display()->read('div_port_rep');
				}
				
				cp::display()->vars['report_html'] 	= $report_html;
				cp::display()->vars['next_url'] 	= '?page=module&install='.$install_info['name'].'&start='.$start.'&do=' . ( cp::$GET['do'] + 1 );
				
				cp::cont()->output .= cp::display()->read('div_port');
			
				return true;
				
			}
			
		}
		else
		{
			
			//-----------------------------------
			// Display
			//-----------------------------------
			
			cp::cont()->page['title'] = cp::lang('update', 'install') . ': ' . $install_info['sexyname'];
			
			cp::display()->vars['mod'] = $install_info;
			
			cp::cont()->output .= cp::display()->read('div_module');
			
			return true;
			
		}
		
	}
	
	/**
	** Show table of modules
	** 
	** @return	bool
	*/
	private function showAll()
	{
		
		$this->get_modules();
		
		//-----------------------------------
		// Build Table
		//-----------------------------------
		
		$cols = array(
			'ver' => array(
				'lang_key'	=> 'mod_ver',
				'type'		=> 'norm',
			),
			'sexyname' => array(
				'lang_key'	=> 'mod_name',
				'type'		=> 'norm',
			),
			'name' => array(
				'lang_key'	=> 'mod_key',
				'type'		=> 'norm',
			),
			'unique_id' => array(
				'lang_key'	=> 'mod_uniq',
				'type'		=> 'norm',
			),
			'status'	=> array(
				'lang_key'	=> 'mod_enb',
				'type'		=> 'tick',
				'class'		=> 'cent',
			),
			'opt'		=> array(
				'lang_key'	=> 'mod_tog',
				'type'		=> 'opt',
				'items'		=> array( 'toggle' => 'name', 'default' => 'name' ),
				'class'		=> 'cent',
			),
		);
		
		/* Print Table */
		cp::cont()->output = cp::call('dtools')->rowsToTable( $this->modules, $cols, 'all' );
		
		//-----------------------------------
		// Uninstalled / needing update
		//-----------------------------------
		
		$app_folder = ROOT . '/sources/apps/';
		
		$scan = scandir( $app_folder );
		
		foreach( $scan as $name )
		{
			
			/* Check for folder */
			if ( !is_dir( $app_folder. '/' . $name ) ) continue;
			
			/* Ignore relative paths */
			if ( $name == '.' OR $name == '..' ) continue;
			
			/* Include and steal info */
			$install_class = cp::call( 'app_install_' . $name, 'apps/' . $name . '/classes/' . $name . '_install', false );
			
			if ( $install_class )
			{			
				$install[ $name ] = $install_class->info();
				
				if ( version_compare( $install[ $name ]['ver'], $this->modules[ $name ]['ver'] ) === 1 )
				{					
					cp::display()->vars['row']	= $install[ $name ];
					cp::display()->vars['rows'] .= cp::display()->read('cpt_row_module');
					$one = true;					
				}
						
			}
			
		}
		
		//-----------------------------------
		// Update Table
		//-----------------------------------
		
		if ( $one )
		{
			cp::cont()->output .= cp::display()->read('cptable');
		}
		
		//-----------------------------------
		// Page Display
		//-----------------------------------
		
		cp::cont()->page['title'] = cp::lang('all', 'man_mods');
		
		/* Top Right Shortcuts */
		cp::call('dtools')->page_but( cp::lang('all', 'mod_recache'), '?page=module&rec=all' );
		
	}
	
	/**
	** Get all modules from db
	** 
	** @return	array
	*/
	private function get_modules()
	{
		
		$this->modules = cp::db()->fetch( array(
			'table'		=> 'modules',
			'key'		=> 'name',
		) );
		
		return $this->modules;
		
	}
	
}

?>
