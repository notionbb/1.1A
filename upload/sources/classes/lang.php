<?php

	/*===================================================
	//	CipherPixel © All Rights Reserved
	//---------------------------------------------------
	//	CP-Core
	//		by cipherpixel.net
	//---------------------------------------------------
	//	File created: September 06, 2014 
	//=================================================*/

class lang {
	
	/**
	** TEMP: Current selected language
	*/
	public 	$lang		= 'en';
	
	/**
	** Packs cache - $packs[ $app_$name ][ $val ]
	*/
	public 	$packs 		= array();
	
	/**
	** Array of currently loaded keys
	*/
	private $current	= array();
	
	/**
	** load() - load a lang pack
	**
	** @array 	= Either string or array or lang names
	** @app		= Module name to load from. Leave default for current module
	*/
	public function load( $array, $mod=false, $temp_lang=false )
	{
		
		/**
		** Default module?
		*/
		$mod = ( $mod ) ?: cp::$app['name'];
		
		/**
		** Language?
		*/
		$this->lang = ( !$temp_lang ) ? $this->lang: $temp_lang;
		
		/**
		** Create string
		*/		
		if ( !is_array( $array ) )
			$pack_names[] = $array;
			
		/**
		** Create current
		*/
		unset( $this->current );
		$this->current = array();
		
		/**
		** Add pack arrays to current
		*/
		foreach( $pack_names as $name )
		{
			
			if ( !$this->packs[ $mod . '_' . $name ] )
				
				/**
				** Load Pack
				*/
				$this->load_pack( $mod, $name );
			
			$this->current = array_merge( $this->current, $this->packs[ $mod .'_' . $name ] );			
			
		}
			
		return $this;
		
	}
	
	/**
	** ret() - return the required lang string
	**
	** @key	= key of language
	*/
	public function get($key)
	{
		
		return $this->current[ $key ];
		
	}
	
	/**
	** load_pack() - loads language pack and adds to $this->packs
	**
	** @mod		= mod name
	** @name	= pack name
	*/
	public function load_pack( $mod, $name )
	{
		
		/**
		** Get Pack...
		*/
		require_once( ROOT . '/sources/lang/' . $this->lang . '/' . $mod . '_' . $name . '.php' );
		
		/**
		** Cache
		*/
		$this->packs[ $mod .'_' . $name ] = $array;
		
	}
	
}
	
?>