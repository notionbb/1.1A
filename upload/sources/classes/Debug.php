<?php
	
	/*===================================================
	//	CipherPixel © All Rights Reserved
	//---------------------------------------------------
	//	CP-Core
	//		by cipherpixel.net
	//---------------------------------------------------
	//	File created: November 08, 2014 
	//=================================================*/

class Debug {
	
	/**
	** Script timer from timer()
	** 
	** @var		int
	*/
	private static $stopwatch;
	
	/**
	** Timer information
	** 
	** @var		array
	*/
	private static $timer = array();
	
	/**
	** Included files
	** 
	** @var		string
	*/
	private static $included_files;
	
	/**
	** Messages
	** 
	** @var		array
	*/
	public static $messages;
	
	/**
	** Query flags
	** 
	** @var		array
	*/
	public static $query_flags;
	
	/**
	** Start timer
	** 
	** @var		string		$type		What are we timing
	*/
	public static function settimer( $type=false )
	{
		self::$timer = array( 'type' => $type );
		
		self::$stopwatch = microtime();
	}
	
	/**
	** End timer and log information
	** 
	** @return	int
	*/
	public static function endtimer()
	{
		return ( microtime() - self::$stopwatch );
	}
	
	/**
	** Send Debug message
	** 
	** @param	string
	*/
	public static function mess( $str )
	{
		self::$messages[] = $str;
	}
	
	/**
	** Flag a certain query
	** 
	** @param	int		$query	Query number
	** @param	string	$mess
	*/
	public static function flag_query( $query, $mess=true )
	{		
		self::$query_flags[ $query ] = self::$query_flags[ $query ] . $mess;		
	}
	
	//-----------------------------------
	// Below simply shows tools to putput the Debug file
	//-----------------------------------
	
	/**
	** Print Debug data
	*/
	public static function print_debug()
	{

		$allow = true;		
		require('sources/libs/tplDebug.php');
		
	}
	
	/**
	** Get request data
	** 
	** @return	string
	*/
	public static function request_data()
	{
		
		$r .= "<b>cp GET</b>: " . self::request_data_callback(cp::$GET) . "\n";
		$r .= "<b>cp POST</b>: " . self::request_data_callback(cp::$POST) . "\n";
		$r .= "<b>cp Cache</b>: " . self::request_data_callback(cp::$cache) . "\n";
		$r .= "<b>\$_GET</b>: " . self::request_data_callback($_GET) . "\n";
		$r .= "<b>\$_POST</b>: " . self::request_data_callback($_POST) . "\n";		
		$r .= "<b>\$_SESSION</b>: " . self::request_data_callback($_SESSION) . "\n";
		
		return $r;
	}
	
	private static function request_data_callback($array)
	{		
		if ( is_array( $array ) )
		{
			foreach( $array as $k => $v )
			{
				if ( $b ) $b .= ', ';
				$b .= $k .'=>'. $v;
			}
		}		
		return $b;		
	}
	
	/**
	** Included Files
	** 
	** @return	string
	*/
	public static function included_files()
	{
		if ( !self::$included_files ) self::$included_files = get_included_files();
		
		foreach (self::$included_files as $filename) {
		    $r .= "$filename\n";
		}
		return str_replace("\\", "\\\\\\", $r);
	}
	
	/**
	** Included Files Count
	** 
	** @return	int
	*/
	public static function included_files_count()
	{
		self::$included_files = get_included_files();
		return count( self::$included_files );
	}
	
	/**
	** Print all queries run so far
	**
	*/
	public static function printHistory()
	{
		foreach ( cp::db()->qHistory as $num => $q )
		{
			$r .= $num.". ".$q['q']."\n";
			$r .= "Line: ".$q['line'];			
			$r .= ( $q['time'] ) ? " | T: ".round( $q['time'], 4 ): '';
			$r .= ( self::$query_flags[ $num ] ) ? " | Flag: ".self::$query_flags[ $num ]: '';			
			$r .= "\n\n";
		}
		return $r;
	}
	
	/**
	** Show messages
	** 
	** @return	string
	*/
	public static function messages()
	{

		foreach( self::$messages as $str )
		{
			$r .= $str . "\n";
		}
		return $r;
	}
	
	//-----------------------------------
	// Master Errors
	//-----------------------------------
	
	/**
	** Critical Error and die
	** 
	** @param	string	$type		Type of error to display
	** @param	string	$mess		Extra information to display
	** @param	string	$mess_more	More information to display
	** @return	die()
	*/
	public static function master( $type='none', $mess=null, $mess_more=false )
	{
		
		/* Error Title */
		$title = 'CP Error';
		
		//-----------------------------------
		// Error Message
		//-----------------------------------
		
		switch( $type ) {
		
			case 'sql':			
				if( $mess ) {
					$errorMess = '<b>Database Error'. ( DEBUG_SQL ? ':</b> '.$mess.'<br /><br />'.$mess_more: '</b>' ) .'';
				}else{
					$errorMess = '<b>Database Error'. ( DEBUG_SQL ? ':</b> Cannot find SQL Server, may be offline or connection settings are incorrect': '</b>' ) .'';
				}
				break;
				
			case 'tpl':
				$errorMess = '<b>TPL Error</b>'. ( DEBUG ? ':</b> Template file ('.$mess.') could not be found': '</b>' ) .'';
				break;
				
			case 'tpl_parse':
				$errorMess = '<b>TPL Error</b>'. ( DEBUG ? ':</b> Template file ('.$mess.') contained an error': '</b>' ) .'';
				break;
				
			case 'nofile':
				$errorMess = '<b>Missing File</b>'. ( DEBUG ? ':</b> Cannot include file ('.$mess.')': '</b>' ) .'';
				break;
				
			case 'perm':
				$errorMess = '<b>Permission Error:</b> You do not have permission to access CP-Admin';
				break;
				
			case 'modoff':
				$errorMess = '<b>Module Error:</b> The module <i>('.$mess.')</i> you are trying to access is not currently active. Activate it via the Admin Control Panel';
				break;
				
			case 'none':
				$errorMess = '<b>Error:</b> '.$mess;
				break;
				
		}
		
		//-----------------------------------
		// Html
		//-----------------------------------
		
		/* Error_tpl vars */
		$allow = true;
		$title 		= ( cp::set('siteName') ) ? cp::set('siteName'). ' | Error' : 'CP Error';
		$errorTit	= ( $errorTit ) ?: 'CP Error';
		
		require( ROOT . '/sources/libs/tplError.php' );
		
		die();
		
	}

}
	
?>