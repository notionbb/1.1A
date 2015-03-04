<?php

	//*===================================================
	//	CipherPixel © All Rights Reserved
	//---------------------------------------------------
	//	CP-Core
	//		by cipherpixel.net
	//---------------------------------------------------
	//	File created: September 08, 2014 
	//=================================================*/

class cache {
	
	/**
	** Db Cache init?
	** 
	** @var		bool
	*/
	private	$db_init = false;	
	
	/**
	** Db Keys
	** 
	** @var		array
	*/
	public	$db_keys = array();
	
	/**
	** Put array into cache file
	** 
	** @param	string		$file		File name to save
	** @param	array		$array		Array to put into file
	** @param	bool		$update		Whether to overwrite the previous content
	** @param	string		$path		Custom save path
	*/
	public function put( $file, $array, $overwrite=false, $path=false )
	{

		//-----------------------------------
		// Init
		//-----------------------------------
		
		$path = ( $path ) ?: ROOT . '/sources/cache/' . $file . '.php';
		
		//-----------------------------------
		// Do we want to overwrite everything...?
		//-----------------------------------
		
		if ( !$overwrite AND ( $old_array = $this->get( $file, $path ) )  )
		{
			/* $array overwrites $old_array */			
			$array = array_merge( $old_array, $array ); 
		}
		
		//-----------------------------------
		// Create File
		//-----------------------------------
		
		$save = "<?php

/**
** Created Automatically by CP-Core
** Do not edit globals here. Edit the db then recache globals.
** If this file is \"conf.php\" then editing will be fine :)
*/\n\n";
		
		foreach( $array as $key => $value )
		{
			
			/* Do we need to quote? */
			$value = ( is_string( $value ) ) ? '\''.$value.'\'': $value;
			
			/* How tabbed? */
			$tab	= ' ';
			$to_tab = ( 20 - strlen( $key ) );
			$to_tab = floor( $to_tab / 4 );
			
			$i = 0;
			while ( $i < $to_tab )
			{
				$tab .= '	';
				$i++;
			}
			
			/* Add to file */
			$save .= '$CACHE[\''.$key.'\']'.$tab.'= '.$value.';';
			$save .= "\n";
			
		}
		
		//-----------------------------------
		// Save to file
		//-----------------------------------
		
		$fp = fopen( $path, "wb");		
		fwrite($fp, $save);		
		fclose($fp);
		
	}
	
	/**
	** Get array from file
	** 
	** @param	string		$file		File name to save
	** @return	bool/array
	*/
	public function get( $file, $path=false )
	{
		
		$path = ( $path ) ?: ROOT . '/sources/cache/' . $file . '.php';
		
		/* Does the file exist */
		if ( ! ( @include ( $path ) ) )
		{ 
			return false;
		}
		
		return $CACHE;
		
	}
	
	/**
	** Init DB Cache
	** 
	** @param	array	$keys
	*/
	public function init_db( $keys=false )
	{
		
		if ( !$keys )
		{
			$keys = $this->db_keys;
			$this->db_keys = array();
		}
		
		cp::db()->fetch( array(
			'select'=> 'cache.key, cache.value',
			'table'	=> 'cache',
			'where' => '`key` IN ( \''.implode('\',\'', $keys).'\' )',
			'key'	=> 'key',
		) );
		
		$this->db_init = true;
		
	}
	
	/**
	** Create a new row in db cache
	** 
	** @param	string	$key
	** @param	string	$value	String to be saved
	** 			array	$value	Array to be serialized
	** @return	bool
	*/
	public function put_db( $key, $value )
	{
		
		if ( !$key OR !$value )
		{
			return false;
		}
		
		$value = ( is_array( $value ) ) ? serialize($value): $value;
		
		cp::db()->insertOnDupeUpdate( 'cache', array('key'=>$key, 'value'=>$value, 'last_update'=>cp::$time), 'key' );
		
		return true;
		
	}
	
	/**
	** Returns a cache setting
	** 
	** @param	string	$key	Key of setting
	** @param	bool	$uns	Whether to unserialize setting (if not false)
	** @return	mixed
	*/
	public function get_db( $key, $uns=false )
	{
		/* Check Init */
		if ( !$this->db_init )
		{
			$this->init_db();
		}
		
		/* Get value from db cache */
		if ( isset( cp::db()->save['cache'][ $key ] ) )
		{
			$r = cp::db()->save['cache'][ $key ];
			$r = $r['value'];
		}
		else
		{
			Debug::mess('get_db(), looking for key \''.$key.'\' not found');
		}
		
		/* Return */
		if ( $r AND $uns )
		{
			return unserialize( $r );
		}
		else
		{
			return $r;
		}
		
		
	}
	
	/**
	** Cache File Globals
	** 
	*/
	public function task_cache_globals()
	{
		
		if ( CACHE_GLOS == false )
		{
			return;
		}
		
		$modules = cp::db()->fetch( array(
			'table'		=> 'modules',
			'key'		=> 'name',
		) );
		
		foreach( $modules as $name => $array )
		{
			
			$module_cache = array();
			
			/* Global Vars */
			$module_globals = cp::db()->fetch( array(
				'select'=> '`id`, `arrayTitle`, `value`',
				'table'	=> 'globals',
				'where'	=> array('modId' => $array['unique_id']),
			) );
			
			if ( $module_globals )
			{
				
				foreach( $module_globals as $id => $global_array )
				{
					$module_cache[ $global_array['arrayTitle'] ] = $global_array['value'];
				}
				
			}
			
			/* Put empty content even if array is empty to prevent double loading when no globals are present */
			cp::cache()->put( 'globals_' . $name, $module_cache );
			
		}
		
	}
	
	/**
	** Cache DB Modules
	** 
	*/
	public function task_cache_modules()
	{

		$to_cache = cp::db()->fetch( array(
			//'select'	=> 'id, unique_id, name, sexyname, ver, defSkin',
			'table' 	=> 'modules',
			'where'		=> 'status=1',	
			'order'		=> 'id asc',
			'key'		=> 'name',
		) );
		
		$this->put_db( 'modules_cache', $to_cache );
		
		return $to_cache;
		
	}
	
	/**
	** Cache Skin Sets
	** 
	*/
	public function task_cache_skins()
	{

		$to_cache = cp::db()->fetch( array(
			'select'	=> 'id, public, p_apps, p_read, p_g_read, p_u_read',
			'table' 	=> 'skins',
			'key'		=> 'id',
		) );
		
		$this->put_db( 'skins', $to_cache );
		
		return $to_cache;
		
	}
	
	/**
	** Cache Menubar array
	** 
	** @return	array
	*/
	public function task_cache_menubar()
	{
		
		//-----------------------------------
		// Get Modules
		//-----------------------------------
		
		$modules = cp::db()->resort( cp::$apps, 'id' );	
		
		$to_cache = array();
		
		//-----------------------------------
		// Loop dtools
		//-----------------------------------
		
		foreach( $modules as $name => $array )
		{	
			
			/* Look for menubar() in dtools */	
			$items = cp::methodCall( cp::call( $array['name'] . '_dtools', 'apps/'.$array['name'].'/classes/'.$array['name'].'_dtools', false ), 'menubar' );
			
			if ( is_array( $items ) )
			{				
				$to_cache = array_merge( $to_cache, $items );				
			}
						
		}
		
		cp::cache()->put_db('menu_bar', $to_cache);
		
		return $to_cache;
		
	}
	
}
	
?>