<?php

	/*===================================================
	//	CipherPixel © All Rights Reserved
	//---------------------------------------------------
	//	CP-Core
	//		by cipherpixel.net
	//---------------------------------------------------
	//	File created: September 08, 2014 
	//=================================================*/

class cp {
	
	/**
	** Script Timer
	** 
	** @var		int
	*/
	public static $scriptStartTime;
	
	/**
	** Current Time
	** 
	** @var		int
	*/
	public static $time;
	
	/**
	** Holds classes from call()
	** 
	** @var		array
	*/
	public static $class 	= array();
	
	/**
	** U Input
	** 
	** @var		array
	*/
	public static $GET;
	public static $POST;	
	
	/**
	** Application Array
	** 
	** @var		array
	*/
	public static $app;
	
	/**
	** Application configuration
	** 
	** @var		array
	*/
	public static $appConf;
	
	/**
	** Default App overwrite
	** 
	** @var		string
	*/
	public static $defApp;
	
	/**
	** Array of apps
	** 
	** @var		array
	*/
	public static $apps;
	
	/**
	** Have we loaded globals
	** 
	** @var		bool
	*/
	private static	$globals_loaded	= false;
	
	/**
	** Array of globals
	** 
	** @var		array
	*/
	private static	$globals;
	
	/**
	** conf.php data
	** 
	** @var		array
	*/
	public static	$conf;
	
	/**
	** Vars that change how the script runs
	** 
	** @var		array
	** 		'SKIP_APP'	bool
	*/
	public static $runTime	= array();
	
	/**
	** General variable Cache
	** 
	** @var		array
	*/
	public static $cache	= array();
	
	/**
	** Current Act
	** 
	** @var		string
	*/
	public static $act;
	
	/**
	** Script output
	** 
	** @var		string
	*/
	public static $final;	
	
	public static function main()
	{
		
		//-----------------------------------
		// Initialise
		//-----------------------------------
		
		self::$scriptStartTime = microtime();
		self::$time = time();
		
		/* Our own Autoloader */
		spl_autoload_register( array('cp', 'autoload') );
		
		/* Config */
		self::config();		
		
		//-----------------------------------
		// Core Classes
		//-----------------------------------
		
		self::call('lang');
		self::db_load( 'db',
					   self::$conf['db_host'],
					   self::$conf['db_user'],
					   self::$conf['db_pass'],
					   self::$conf['db_name']
					 );		
		self::call('cache');
		
		//-----------------------------------
		// User Input
		//-----------------------------------
		
		/* Clean user inputs */
		self::$POST = self::clean( $_POST );
		self::$GET	= self::clean( $_GET );
		
		/* Do we need to interpret the url? */
		self::call('link')->interpret();
		
		/* What app do we want to try? */
		self::$defApp = self::getApp();
		
		/* Application specific config */
		self::$app['name'] = self::$defApp; // call() loads config based on this setting. startApp() overwrites it.		
		self::$appConf = self::call('config', 'apps/' . self::$defApp .'/config', false )->config;

		/* What application do we want? */
		self::startApp();

		/* Reload Config? */
		if ( self::$defApp != self::$app['name'] )
		{

			self::$appConf = self::call('config', 'apps/' . self::$app['name'] .'/config' )->config;
			
			/* Load new cache files */
			cp::cache()->init_db( self::$appConf['req_cache'] );
			
			Debug::mess('Application loaded second time');			
		}
		
		/* Handle the current user */
		self::call('logged')->start();
		
		/* Load user if app requires it */
		if ( self::$appConf['establishCur'] ) self::call('logged')->establishCur();
		
		/* Display class */
		self::call('display')->start();
		
		//-----------------------------------
		// Controller
		//-----------------------------------
		
		/* Load controller, call pre and post if exist */
		self::methodCall( self::call('config'), 'pre' );

		
		/* Do we want an ajax control? */
		if ( self::$GET['ajax'] )
		{
			
			self::call('controller');
			self::call('ajax')->main();
			
		}
		
		else
		
		/* Normal application */
		if ( self::$runTime['SKIP_APP'] == false )
		{			
			
			/* What act ? */
			if ( !self::$act AND ( !self::$GET['act'] OR !in_array( self::$GET['act'], self::$appConf['pages'] ) ) )
			{
				self::$act = ( self::methodCall( self::call('config'), 'complexPages' ) ) ?: self::$appConf['def'];
			}

			else

			if ( !self::$act )
			{
				self::$act = self::$GET['act'];
			}
			
			/* Run */
			self::call( 'controller' );
			self::call( 'pub_' . self::$act, 'apps/' . self::$app['name'] . '/pub/' . self::$act )->main();
			
			/* Post */
			self::methodCall( self::call( 'pub_' . self::$act ), 'last' );		
			self::methodCall( self::call('config'), 'post' );
			
			/* Come back to this page if we want */
			if ( cp::$cache['save_url'] !== false )
			{
				$_SESSION['ret_url'] = cp::call('link')->getCurJSON();
			}
			
		}
		
		//-----------------------------------
		// Output
		//-----------------------------------

		echo self::$final;
		
		//-----------------------------------
		// Last calls and close
		//-----------------------------------
		
		cp::db()->runDelayedQ();
		
		//-----------------------------------
		// Debug
		//-----------------------------------
		
		if ( ( !self::$GET['ajax'] ) AND ( DEBUG or self::set('ow_DEBUG') ) )
		{
			Debug::print_debug();
		}
		
	}
	
	/**
	** config() - loads init.php to grab global vars
	** 
	*/
	public static function config()
	{
		
		/* Include init (if not already) or die */
		if ( !defined('ROOT') )
		{ 
			( @include('./init.php') ) or die('init.php not found, have you <a href="install/">installed?</a>');
		}
		
		@include('./conf.php');
		self::$conf = $CACHE;
		
		/* Include db config or die */
		if ( !self::$conf )
		{ 
			die('No db settings found in conf.php, have you <a href="install/">installed?</a>');
		}
		
		/* Debug class holds core warnings as well */
		include('sources/classes/Debug.php');
		
	}
	
	/**
	** startApp()
	*/
	private static function startApp()
	{

		//-----------------------------------
		// Cache
		//-----------------------------------
		
		self::cache()->db_keys = array('modules_cache', 'skins');
		
		if ( self::$appConf['req_cache'] )
		{		
			self::cache()->db_keys = array_merge( self::cache()->db_keys, self::$appConf['req_cache'] );
		}
		
		//-----------------------------------
		// Init
		//-----------------------------------
		
		/* Get apps from cache, recache if we can't */
		if ( !self::$apps = self::cache()->get_db( 'modules_cache', true ) )
		{
			self::$apps = self::cache()->task_cache_modules();
		}
		
		if ( !self::$defApp )
		{
			self::$defApp = self::getApp();
		}
		
		//-----------------------------------
		// Return App
		//-----------------------------------
		
		/* Save current apps */
		if ( self::$apps[ self::$defApp ] )
			return self::$app = self::$apps[ self::$defApp ];
			
		/* Return default app */
		return self::$app = self::$apps[ self::$conf['default_app'] ];
		
	}
	
	/**
	** What app will we be looking for?
	** 
	** @return	string
	*/
	private static function getApp()
	{

		if ( self::$defApp )
		{
			return self::$defApp;
		}
		
		if ( cp::$GET['app'] )
		{
			/*if ( cp::$GET['app'] == 'admin' )
			{
				die('Cannot access admin app through URL, visit admin.php');
			}*/
			return basename( cp::$GET['app'] );
		}
		
		if ( self::$conf['default_app'] )
		{
			return self::$conf['default_app'];
		}
		
		return 'admin';
		
	}
	
	/**
	** setDefApp() - set the default application (overwrite the db setting)
	**
	** @param	string	$name	application name/folder
	*/
	public static function setDefApp($name)
	{
		self::$defApp = $name;
	}
	
	/**
	** call() - calls a class and loads if it exists
	**
	** @param	string		$cname		Name of class
	** @param	string		$dir		dir to class from sources folder including filename but not suffix (".php")
	** @param	bool		$die		die if class not found
	** @param	bool		$skip_cache	Loads class even if there is already a class of the same name loaded
	** @return	object|false
	*/
	public static function call( $cname, $dir=false, $die=true, $skip_cache=false )
	{
		
		/* dtools modifier */
		if ( $cname == 'dtools' )
		{
			$cname = self::$app['name'] . '_dtools';
		}
		
		/* config modifier */
		if ( $cname == 'config' )
		{
			$cname = self::$app['name'] . '_config';
		}
		
		/* Does this class already exist? */
		if ( isset( self::$class[ $cname ] ) AND !$skip_cache )
		{
		
			return self::$class[ $cname ];
			
		}
		else
		{

			if ( self::load_class_file( $cname, $dir, $die ) === false )
			{
				return false;
			}
				
			/* Save class to cache */
			self::$class[ $cname ] = new $cname;
			
			return self::$class[ $cname ];
				
		}		
		
	}
	
	/**
	** Load Class File
	** 
	** @param	string	$cname	Name of the class
	** @param	string	$dir	dir to class from sources folder including filename but not suffix (".php")
	** @param	bool	$die	die if class not found
	** @return	bool
	*/
	public static function load_class_file( $cname, $dir=false, $die=true )
	{		
		/* Build class directory */
		if ( !$dir ) 
			$dir = 'sources/classes/' . $cname . '.php';
		else
			$dir = 'sources/'  . $dir . '.php';
		
		/* Include; do we want to script to die if we cant find it */
		if ( $die )
		{
			( include ( ROOT . '/' . $dir ) ) or Debug::master( 'nofile', ROOT . '/' . $dir );
		}
		else
		{

			if ( ! ( @include ( ROOT . '/' . $dir ) ) )
			{ 
				return false;
			}

		}
	}
	
	/**
	** callAppClass() - calls a class specific to the app
	** 
	** Effectively a shortcut for call() but for classes inside app folders
	**
	** @param	string	$cname		Classsname
	** @param	string	$appname	App name (folder) to load class from. (Def:current)
	** @return	object
	*/
	public static function callAppClass( $cname, $appname=false )
	{
		
		if ( $cname == 'dtools' )
		{
			$cname = cp::$app['name'] . '_dtools';
		}
		
		$appname = ( $appname ) ?: cp::$app['name'];
			
		return cp::call( $cname, 'apps/'.$appname.'/classes/'.$cname );
		
	}
	
	/**
	** methodCall() - calls method if it exists
	**
	** @param	object	$scope	Scope to call method from
	** @param	string	$method	String name of method
	** @param	string	$var	Variable to hand to method
	** @return	method|false
	*/
	public static function methodCall( $scope, $method, $var=false )
	{
		
		if ( method_exists( $scope, $method ) )
		{
			if ( $var )
				return $scope->$method($var);
			else
				return $scope->$method();
		}
		else
			return false;
	}
	
	/**
	** destroy() - destroys a class created by self::call()
	** 
	** @param	string	$cname	Name of class
	*/
	public function destroy( $cname )
	{
		unset( self::$class[ $cname ] );
	}
	
	/**
	** output() - adds a template file to the output
	** 
	** @param	string	$tpl	template file to add
	*/
	public static function output( $tpl )
	{		
		self::$final = self::call('display')->read( $tpl );		
	}
	
	/**
	** Returns a global setting
	** Globals don't actually get loaded until this function is called
	** 
	** @param	string	$key	Key of global to return
	** @return	mixed
	*/
	public static function set($key)
	{
		
		/* Skip if installer... */
		if ( defined( 'IS_INSTALLER' ) AND IS_INSTALLER === true )
		{
			return false;
		}
		
		/* Do we need to load globals? */
		if ( !self::$globals_loaded )
		{
			self::get_globals();
		}

		return self::$globals[ $key ];
		
	}
	
	/**
	** Get globals from cache, else query for them...
	** 
	*/
	public static function get_globals()
	{
		
		self::$globals_loaded = true;
		
		/* Are we allowed to cache globals? */
		if ( CACHE_GLOS == true )
		{

			/* Get from cache */
			$admin_globals = self::cache()->get('globals_admin');
			
			if ( self::$app['name'] != 'admin' )
			{
				/* Null if file exists, false if file cannot be found */
				$app_globals = self::cache()->get('globals_' . self::$app['name'] );
			}
			else
			{
				$app_globals = true;
			}
			
		}
		
		/* Get all globals... */
		if ( $admin_globals AND $app_globals !== false )
		{

			/* Merge arrays and return */
			self::$globals = cp::merge( $admin_globals, $app_globals );
			return;

		}
		else
		{
			
			$b	= cp::db()->fetch( array(
				'select'	=> 'globals.arrayTitle, globals.value',
				'table'		=> 'globals',
				'join'		=> array(
					'type'	=> 'left',
					'table'	=> 'globalcats',
					'where'	=> 'globalcats.text_id=globals.catId AND enabled=1',
				),
				'r'			=> 'res',
			));
			
			while( $setting = $b->fetch_assoc() )
			{
				self::$globals[ $setting['arrayTitle'] ] = $setting['value'];
			}
					
		}
		
	}
	
	/**
	** Load Database Shortcut
	** Loaded from a few different places (app, installer, cpext)
	** Keeps it easy to make changes
	** 
	** @param	string	$insance_name	Name of database instance (E.g. 'db')
	** @param	string	$host
	** @param	string	$user
	** @param	string	$pass
	** @param	string	$name
	*/
	public static function db_load( $instance_name, $host, $user, $pass, $name )
	{
		self::load_class_file('dbOLD');
		self::load_class_file('db');
		self::$class[ $instance_name ] = new db( $host, $user, $pass, $name );
		self::db()->start();	
	}
	
	/**
	** Numerous shortcut functions
	** 
	** db()		 - call('db')
	** logged()	 - call('logged')
	** cont()	 - call('controller')
	** 
	** @return	object
	*/
	
	public static function db()
	{
		return self::$class[ 'db' ];
	}
	
	public static function logged()
	{
		return self::$class[ 'logged' ];
	}
	
	public static function cont()
	{
		return self::$class[ 'controller' ];
	}
	
	public static function display()
	{
		return self::$class[ 'display' ];
	}
	
	public static function cache()
	{
		return self::$class[ 'cache' ];
	}
	
	/**
	** lang() - shortcut to lang->load()->get()
	** 
	** @param	string	$file	lang/en/app_$file.php
	** @param	string	$get	key of lang element
	** @return	string	
	*/
	public static function lang( $file, $get )
	{		
		return self::call('lang')->load( $file )->get($get);		
	}
	
	/**
	** link() - shortcut to make links
	** 
	** @param	array	$array	Link array
	** @param	array	$array2	Array to merge if required
	*/
	public static function link( $array, $array2=false )
	{		
		return self::call('link')->make( $array, $array2 );		
	}
	
	/**
	** clean() - clean user inputs
	** 
	** @param	string|array	$in		string to clean, or array of strings
	** @param	string			$html	whether to parse htmlentities
	** @return	string|array
	*/
	public static function clean($in, $html = true) {
		
		if ( is_array ($in) ) {
			foreach( $in as $k => $v ) {
				
				if ( is_array( $v ) ) {
					$ret[$k] = self::clean( $v );
				}else{					
					$v = htmlentities($v, ENT_NOQUOTES | ENT_IGNORE, DEF_CHAR );
					$ret[$k] = addslashes($v);
				}
				
			}
		}else{
			if ( $html ) $in = htmlentities($in, ENT_NOQUOTES | ENT_IGNORE, DEF_CHAR );
			$ret = addslashes($in);
		}
		
		if ( isset( $ret ) )
		{
			return $ret;
		}
		
	}
	
	/**
	** Our own autoloader
	** 
	** @param	string $class
	*/
	public static function autoload( $class )
	{
		if( substr( $class, 0, 6 ) == 'cpobj_' )
		{
			$dir = 'sources/classes/' . substr( $class, 6 ) . '.php';
			( include ( ROOT . '/' . $dir ) ) or Debug::master( 'nofile', ROOT . '/' . $dir );
		}
	}
	
	/**
	** Load composer's autoload (for dependencies)
	** 
	*/
	public static function load_composer_autoload()
	{
		require_once ROOT . '/sources/vendor/autoload.php';
	}
	
	/**
	** Merge arrays without a friggen php warning...
	** 
	** @param	array
	** @param	array
	** @return	bool/array
	*/
	public static function merge( $array1, $array2 )
	{
		
		/* Check for array content */
		if( !is_array( $array1 ) AND !is_array( $array2 ) )
		{
			return false;
		}
		
		if ( !is_array( $array1 ) )
		{
			return $array2;
		}
		
		if ( !is_array( $array2 ) )
		{
			return $array1;
		}
		
		return array_merge( $array1, $array2 );
		
	}
	
	/**
	** Addslashes to all levels of an array
	** 
	** @param	array	$array
	** @return	array
	*/
	public static function slash_array( $array )
	{
		
		foreach( $array as $k => $v )
		{
			if ( is_array( $v ) )
			{
				$ret[$k] = self::slash_array($v);
			}
			else
			{
				$ret[$k] = addslashes($v);
			}
		}
		
		return $ret;
		
	}
	
}

?>