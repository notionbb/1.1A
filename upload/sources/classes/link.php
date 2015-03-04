<?php

	/*===================================================
	//	CipherPixel © All Rights Reserved
	//---------------------------------------------------
	//	CP-Core
	//		by cipherpixel.net
	//---------------------------------------------------
	//	File created: September 21, 2014 
	//=================================================*/
	
class link {
	
	/**
	** Link type. Either 'simulated' or 'rewrite'. Set in init.php
	** 
	** @var		string
	*/
	private	$link_type 		= false;
	
	/**
	** Link types that need interpreting
	** 
	** @var		array
	*/
	public 	$to_interpret 	= array('simulated', 'complex');
	
	public function __construct()
	{
		
		$this->link_type = ( $this->link_type ) ?: LINK_TYPE;
		
	}
	
	/**
	** Set the link type, will overwrite init
	** 
	** @param	string	$type
	*/
	public function setType( $type )
	{
		$this->linkType = $type;
	}
	
	/**
	** Turns the url into an appropriate cp::$GET array
	** 
	** @return	null
	*/
	public function interpret()
	{
		
		/* Don't interpret ajax */
		if ( cp::$GET['ajax'] ) return;
		
		/* Do we need to interpret? */
		if ( in_array( $this->link_type, $this->to_interpret ) )
		{
			
			//-----------------------------------
			// Get path
			//-----------------------------------
			
			$request = parse_url($_SERVER['REQUEST_URI']);
			$path = cp::clean( urldecode( $request["path"] ) );
			
			//-----------------------------------
			// Remove excess path information
			//-----------------------------------
			
			/* Remove script name */
			$script_name 	= $_SERVER['SCRIPT_NAME'];
			$script_len		= strlen( $script_name );
			
			/* Remove script if present */
			if ( substr( $path, 0, $script_len ) == $script_name )
			{
				$path = trim( substr( $path, $script_len ), '/' );
			}
			else
			{
				
				$dir_len = strlen( substr( $script_name, 0, strrpos( $script_name, '/' ) ) );												
				$path = trim( substr( $path, $dir_len ), '/' );	
					
			}			
			
			//-----------------------------------
			// Turn path into array
			//-----------------------------------
			
			if ( $path )
			{

				$get_array = explode( '/', $path );
				
				/* Save app, marge and create */
				$get['app'] = $get_array['0'];				
				cp::$GET = array_merge( $get, $get_array );				
				unset( cp::$GET['0'] );
				
			}
			else
			{			
				cp::$GET['app'] = cp::$conf['default_app'];				
			}
			
		}
		
	}
	
	/**
	** Makes a link
	** 
	** @param		array/string		$array
	** @return		string
	*/
	public function make( $array, $array2=false )
	{

		if ( !is_array($array) )
		{
			$array = explode( ',', $array );
		}
		
		if( is_array( $array2 ) )
			$array = array_merge( $array, $array2 );
		else
		if ( $array2 )
			$array[] = $array2;
		
		$i = 1;
		foreach( $array as $v )
		{			
			$link = explode( '=', $v );
			
			if ( isset($link['1']) )
				$b[ $link['0'] ] = $link['1'];
			else
			{
				$b[$i] = $link['0'];
				$i++;
			}
			
		}
		
		return cp::methodCall( $this, 'type_' . $this->link_type, $array );			
		
	}
	
	/**
	** Creates a simulated url_rewrite - example.com/index.php/complex/etc
	** 
	** @param	array
	** @return	string
	*/
	public function type_simulated( $array )
	{		
		//$current_file = substr( $_SERVER['SCRIPT_NAME'], strrpos( $_SERVER['SCRIPT_NAME'], '/' ) + 1 );
		$current_file = 'index.php';			
		return cp::$conf['link_prefix'] . $current_file . $this->type_complex( $array );		
	}
	
	/**
	** 
	** 
	** @param	array
	** @return	string
	*/
	public function type_complex( $array )
	{

		/* Get current app */
		if ( !$array['app'] )
			$b .= '/' . cp::$app['name'];
		else
		{
			$b .= '/'.$array['app'];
			unset( $array['app'] );
		}
		
		$i = 1;
		foreach ( $array as $k => $v )
		{
			$b .= '/'.$v;
			$i++;
		}
		
		return $b;
					
	}
	
	/**
	** getCurJSON() - creates a link to the current page and returns as a json object
	** 
	** @return	string
	*/
	public function getCurJSON()
	{
		return json_encode(cp::$GET);
	}
	
	/**
	** readJSONLink() - creates an url from a json link
	** 
	** @param	string	$json
	** @return	string
	*/
	public function readJSONLink($json=false)
	{
		if ( !$json ) return false;
		return $this->make( json_decode( $json, true ) );
	}	
	
}

?>