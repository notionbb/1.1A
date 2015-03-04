<?php
	
	/*===================================================
	//	CipherPixel © All Rights Reserved
	//---------------------------------------------------
	//	CP-Core
	//		by cipherpixel.net
	//---------------------------------------------------
	//	File created: November 07, 2014 (Rewrite)
	//=================================================*/

class display {
	
	/**
	** Skin Folder
	** 
	** @var		string
	*/
	public	$skinFolder;
	
	/**
	** Whether to cache tpl files
	** 
	** @var		bool
	*/
	public	$tpl_cache;
	
	/**
	** Display Vars
	** 
	** @var		array
	*/
	public	$vars;
	
	/**
	** Extra JS. Display js with $this->javascript()
	** 
	** @var		string
	*/
	public	$javascript;
	
	/**
	** Javascript files we've loaded
	** 
	** @var		array
	*/
	public	$loadedJs	= array();
	
	/**
	** Last parsed
	** 
	** @var		string
	*/
	public	$parsed;
	
	/**
	** Construct
	** 
	*/
	public function start()
	{

		/* Set whether to cache templates */
		$this->tpl_cache = CACHE_TPLS;
		
		/* Set skin folder */
		$this->skinFolder = ( DEF_SKIN_FOLDER ) ?: $this->getFolder();
		
		/* Initial Vars */
		$this->vars = array(
			'styleFolder' 	=> $this->skinFolder,
			'lprefix'		=> cp::$conf['link_prefix'],
		);
		
	}
	
	/**
	** Add Variables
	** 
	** @param	string/array	$key	Key of of array of variables
	** @param	string			$value	Value of var
	** @return	$this
	*/
	public function add( $key, $value=null )
	{
		
		if ( is_array( $key ) AND $value === null )
		{
			foreach( $key as $k => $v )
			{
				$this->vars[ $k ] = $v;
			}
		}
		else
		{
			$this->vars[ $key ] = $value;
		}
		
		return $this;
		
	}
	
	/**
	** Gets the skin folder
	** 
	** @return	string
	*/
	public function getFolder()
	{
		
		$skin_array = cp::cache()->get_db('skins', true );
		
		$try_name 	= '';
		$try		= $skin_array[ $try_name ];
		
		$def = cp::$app['defSkin'];

		/* Does skin exist? */
		if ( !$try )
		{
			Debug::mess('Not trying to set any skin, returned from cp::$app[\'defSkin\']');
			return $def;
		}
		
		/* Skin Public? */
		if ( $try['public'] == 1 )
		{
			Debug::mess('Skin set is public');
			return $try_name;
		}
		
		/* Is this application allowed that skin? */
		if ( !cp::call('perm')->check( $try, 'apps', cp::$app['name'] ) )
		{
			Debug::mess('Skin not allowed for app, default set');
			return $def;
		}
		
		/* Check masks */
		if ( cp::call('perm')->check( $try, 'read' ) )
		{
			Debug::mess('Skin set through mask');
			return $try_name;
		}
		
		/* Check groups */
		if ( cp::logged()->in AND cp::call('perm')->check( $try, 'g_read', cp::logged()->cur['gId'] ) )
		{
			Debug::mess('Skin set through group');
			return $try_name;
		} 
		
		Debug::mess('No permission, default set');				
		return cp::$app['defSkin'];		
	}
	
	/**
	** Quickly adds a var and returns read
	**
	** @param	string	$temp	Name of template file to load
	** @param	string	$var	Var to load to vars['quick_var']
	** @return	string
	*/
	public function quickRead( $temp, $var )
	{		
		$this->vars['quick_var'] = $var;
		return $this->read($temp);		
	}
	
	/**
	** Easy redirecter
	**
	** @param	string	$message	Message to display to user
	** @param	string	$url		Url to redirect to (null=refresh)
	** @param	int		$time		Time to show redirect page
	*/
	public function splash($message, $url=null, $time=false) {
		
		/* Time (seconds) to show splash for */
		$time = ( $time ) ?: cp::set('splashTime');
		
		/* Vars! */		
		$this->vars['splash'] = array(
			'time' 	=> $time,
			'url'	=> $url,
			'mess'	=> $message,
		);
		
		/* Output directly */
		cp::output('splash');
		
	}
	
	/**
	** Returns the javascript required for the page
	** 
	** @return	string
	*/
	public function javascript()
	{
		
		/* Overwrite the style prefix */
		//$pref = ( cp::call('link')->linkPreOW ) ?: cp::set('rewriteLinkPrefix');
		$pref = cp::$conf['link_prefix'];
		
		/* Show */
		$vars = '<script type="text/javascript">
		window.lprefix	= "' . $pref . '";
		window.app	= "' . cp::$app['name'] . '";'.$this->gloVars.'
	</script>
	';
		
		return $vars . $this->javascript;
		
	}
	
	/**
	** Adds a javascript global
	** 
	** @param	string	$key	Key of Javascript global
	** @param	string	$value	Value of javascript global
	*/
	public function addJsGlobal( $key, $value )
	{
		$this->gloVars .= '
		window.'.$key.' = "'.$value.'";';
	}
	
	/*
	** Adds javascript file to the head
	**
	** @param	string	$file		File name or url to file
	** @param	bool	$outside	false (internal) or true (external url)
	** @return	object	$this
	*/
	public function jsLoad( $file, $outside=false )
	{
		
		/* Prevent reloading this file twice */
		if ( $this->loadedJs[ $file ] == true )
			return;
			
		$this->loadedJs[ $file ] = true;
		
		/* create URl to internal files */
		if ( !$outside )
		{
			$file = $this->vars['lprefix'] . 'js/' . $file . '.js';
		}
		
		$this->javascript .= '<script type="text/javascript" src="' . $file . '"></script>';
		$this->javascript .= "\n\t";
		
		/* Chaining */
		return $this;
		
	}
	
	/*
	** time2str() - returns a lovely English looking date
	** | Shamelessly stolen off an internet blog, which I have since forgot so cannot credit...
	** | Thank you to whom ever made this, I really couldn't be bothered!
	** 
	** @param	timestamp	$ts
	** @return	string
	*/
	public function time2str( $ts )
	{
	    if(!ctype_digit($ts))
	        $ts = strtotime($ts);
	
	    $diff = cp::$time - $ts;
	    if($diff == 0)
	        return 'now';
	    elseif($diff > 0)
	    {
	        $day_diff = floor($diff / 86400);
	        if($day_diff == 0)
	        {
	            if($diff < 60) return 'just now';
	            if($diff < 120) return '1 minute ago';
	            if($diff < 3600) return floor($diff / 60) . ' minutes ago';
	            if($diff < 7200) return '1 hour ago';
	            if($diff < 86400) return floor($diff / 3600) . ' hours ago';
	        }
	        if($day_diff == 1) return 'Yesterday';
	        if($day_diff < 7) return $day_diff . ' days ago';
	        if($day_diff < 31) return ceil($day_diff / 7) . ' weeks ago';
	        if($day_diff < 60) return 'last month';
	        return date('F Y', $ts);
	    }
	    else
	    {
	        $diff = abs($diff);
	        $day_diff = floor($diff / 86400);
	        if($day_diff == 0)
	        {
	            if($diff < 120) return 'in a minute';
	            if($diff < 3600) return 'in ' . floor($diff / 60) . ' minutes';
	            if($diff < 7200) return 'in an hour';
	            if($diff < 86400) return 'in ' . floor($diff / 3600) . ' hours';
	        }
	        if($day_diff == 1) return 'Tomorrow';
	        if($day_diff < 4) return date('l', $ts);
	        if($day_diff < 7 + (7 - date('w'))) return 'next week';
	        if(ceil($day_diff / 7) < 4) return 'in ' . ceil($day_diff / 7) . ' weeks';
	        if(date('n', $ts) == date('n') + 1) return 'next month';
	        return date('F Y', $ts);
	    }
	}
	
	//-----------------------------------
	// Read Tools
	//-----------------------------------
	
	/**
	** Gets a template file and returns it as executed PHP
	**
	** @param	string		$temp		Template file name
	** @param	string		$var		Var to save template to
	** @param	string		$skip_non	Continue skip execution if TPL not found
	** @return	string
	*/
	public function read( $temp, $var=false, $skip_non=false )
	{

		//-----------------------------------
		// Get File
		//-----------------------------------
		
		/* Paths */
		$file_path	= ROOT . '/style/' . $this->skinFolder . '/tpl/' . $temp . '.tpl';
		$cache_path	= ROOT . '/sources/cache/skin_' . $this->skinFolder .'/' . $temp . '.php';
		
		/* Check if file exists */
		if ( !file_exists( $file_path ) )
		{			
			if ( $skip_non )
			{			
				return false;
			}
			else
			{
				Debug::master('tpl', $temp );
			}			
		}
		
		//-----------------------------------
		// Parse
		//-----------------------------------
		
		/* Cache... */
		if ( $this->tpl_cache )
		{
			
			//-----------------------------------
			// Cache
			//-----------------------------------
			
			if ( @include( $cache_path ) )
			{

				/* Get the last modified time of the tpl file */
				$modified = filemtime( $file_path );
				
				if ( $info['date'] > $modified )
				{
					/* Recache not required, eval from cache */
					if ( $var ) $this->vars[ $var ] .= stripslashes( $build );
					return stripslashes( $build );
				}
				
			}
			
		}
			
		//-----------------------------------
		// Get from TPL
		//-----------------------------------
		
		/* Parse and run */
		$parsed = $this->parse( $file_path, $temp );
		unset( $build );	
		$eval 	= eval( $parsed );
		
		/* Throw error message? */
		if( $eval === false )
		{
			Debug::master('tpl_parse', $temp );
		}
		
		/* Save to var */
		if ( $var ) $this->vars[ $var ] .= stripslashes( $build );
		
		/* If we're here and caching is on, we'll need to cache the file
		   If $eval returns false there's been a parse error, so don't cache */
		if ( $this->tpl_cache AND $eval !== false )
		{
			$this->cache( $parsed, $cache_path );			
		}
		
		/* Return */
		return stripslashes( $build );
		
	}
	
	/**
	** Open a file and tplToPhp
	** 
	** @param	string	$file	Path to file
	** @return	string
	*/
	public function parse( $file )
	{
		$tpl = file_get_contents( $file );					
		return $this->tplToPhp( $tpl );	
	}
	
	/**
	** Turn CP TPL to PHP
	** 
	** @param	string	$tpl	Template
	** @return	string
	*/
	public function tplToPhp( $tpl )
	{
		
		$tpl = addslashes( $tpl );
		
		/* Comment Lines */
		$tpl = preg_replace( '/!IGNORE:(.+)/', '', $tpl );
		
		//-----------------------------------
		// If Statements, idents keep it clean
		//-----------------------------------
		
		/* Normal */
		$tpl = preg_replace_callback( 
			'/<if (.+)(?!\-)>/',
			function ($m) {
				
				$b = preg_replace( '/\$([a-z]+?)->(.+?)/', 'cp::call(\'$1\')->$2', stripslashes($m['1']) );
				
	            return '\';
if ( '.$b.' ) {
$build .= \'';
	        },
			$tpl
		);

		/* If not */
		$tpl = preg_replace_callback( 
			'/<ifnot (.+)(?!\-)>/',
			function ($m) {
				
				$b = preg_replace( '/\$([a-z]+?)->(.+?)/', 'cp::call(\'$1\')->$2', stripslashes($m['1']) );
				
	            return '\';
if (!( '.$b.' )) {
$build .= \'';
	        },
			$tpl
		);
		
		/* Else If */
		$tpl = preg_replace_callback( 
			'/<elseif (.+)(?!\-)>/',
			function ($m) {
				
				$b = preg_replace( '/\$([a-z]+?)->(.+?)/', 'cp::call(\'$1\')->$2', stripslashes($m['1']) );
				
	            return '\';
}elseif ( '.$b.' ) {
$build .= \'';
	        },
			$tpl
		);
		
		/* Else */
		$tpl = preg_replace(
			'/<else>/',
			'\';
}else{
$build .= \'',
			$tpl
		);
		
		/* End If */
		$tpl = preg_replace(
			'/<\/if>/',
			'\';
}
$build .= \'',
			$tpl
		);
		
		//-----------------------------------
		// Vars
		//-----------------------------------
		
		/* {$var['hello']} => $this->vars[ $var ]['hello'] */
		$tpl = preg_replace_callback( 
			'/\{\$([a-z]+)->(.+?)\}/',
			function ($m) {
				return '\'.(cp::call(\''.$m['1'].'\')->'.stripslashes($m['2']).').\'';
			},
			$tpl
		);
		
		/* {$anything} => cp::anything */
		$tpl = preg_replace_callback(
			'/\{\$(.+?)\}/',
			function ($m) {
				return '\'.cp::'.stripslashes($m['1']).'.\'';
			},
			$tpl
		);
		
		$toRun = '$build .= \''. $tpl .'\';';
		
		return $toRun;
		
	}
	
	/**
	** Add a file to the skin cache
	** 
	** @param	string	$file	Parsed TPL file
	** @param	string	$file	Path to save file
	*/
	public function cache( $file, $cache )
	{
		
		/**
		** Create the cached page...
		*/
		$save = '<?php

/**
** Created Automatically by CP-Core
** Do not edit. Edit the tpl files
*/

$info = array(
	\'date\' => \''.cp::$time.'\',
);

'.$file.'

';
		
		/**
		** Do we need to create folders?
		*/
		$dirname = dirname($cache);
		if (!is_dir($dirname))
		{
		    mkdir($dirname, 0755, true);
		}

		/**
		** Create file and save
		*/
		$fp = fopen( $cache, "wb");		
		fwrite($fp, $save);		
		fclose($fp);
		
	}
	
	//-----------------------------------
	// Form. Don't bother. Just don't bother.
	//-----------------------------------
	
	/**
	** form()
	**
	** @array	= array of form settings
	** 	name		= form name
	**  title		= used by some types as a title
	**  action		= form action (def post)
	**  enctype		= true to encrypt form with "multipart/form-data"
	**  autocomplete= allowed browser auto complete?
	**  fieldA		= array of fields
	**  fields		= field html
	**  submit		= submit button value
	**  submitC		= extra class for submit button, e.g. 'red'
	** 	submitDivC	= extra class for submit div
	** 	lang_pack	= lang pack to use (used by 'gen')
	** 	no_close	= set to true to leave <form> open
	** @type	= form type
	*/
	public function form( $array, $type='basic' )
	{
		
		unset( $this->vars['form'] );
		unset( $this->vars['row'] );
		unset( $this->vars['rows'] );
		
		if ( $type == 'basic' )
		{
			if ( is_array( $array['fieldA'] ) )
			{
				foreach( $array['fieldA'] as $tab => $html )
				{
					$array['fields'] .= $html;
				}
			}
			$this->vars['form'] = $array;
		}
		
		else
		
		if ( $type == 'gen' )
		{
			$this->vars['form'] = $array;
			cp::call('dtools')->formGen( $array );
			$type = 'basic';
		}
		
		else
		
		if ( $type == 'small' )
		{
			$this->vars['form'] = $array;
			cp::call('dtools')->formSmall( $array );
		}
		
		else
		
		if ( $type == 'but' )
		{
			$this->vars['form'] = $array;
		}
		
		else
		
		if ( $type == 'tab' )
		{
			$this->vars['form'] = $array;
			cp::call('dtools')->formTab( $array );
		}
		
		return $this->read( 'form_' . $type );
		
	}
	
}

?>