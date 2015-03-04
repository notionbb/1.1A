<?php

	//*===================================================
	//	CipherPixel © All Rights Reserved
	//---------------------------------------------------
	//	CP-Core
	//		by cipherpixel.net
	//---------------------------------------------------
	//	File created: September 08, 2014 
	//=================================================*/

class dbOLD {
	
	/**
	** Mysqli Driver
	*/
	public $driver;
	
	/**
	** Conf
	** @var	array
	*/
	private	$config;
	
	/**
	** Queries to run at end of scipt
	*/
	public	$delayQs 	= array();
	
	/**
	** Saved results
	*/
	public	$save		= array();
	public	$record		= false;	// Records IDs from fetch
	public	$lastId;				// Last Insert Id
	
	/**
	** Debugs
	*/
	public 	$qHistory	= array();	// Array of queries run. Use printHistory() ?
	public 	$qCount		= 0;		// int count of queries run, after they've been run
	
	/**
	** Globals
	*/
	public 	$globals			= array(); // Global array. Use cp::set()
	public 	$globalsLoaded 	= FALSE; // Have globals been loaded? cp.php
	
	/**
	** Constructor
	** 
	** @param	string	$host
	** @param	string	$user
	** @param	string	$pass
	** @param	string	$name
	*/
	public function __construct( $host, $user, $pass, $name )
	{		
		$this->config = array( 'host' => $host,
							   'user' => $user,
							   'pass' => $pass,
							   'name' => $name
							 );		
	}
	
	/**
	** start() - start the database
	**
	**
	*/
	public function start()
	{
		
		$this->driver = new mysqli( $this->config['host'], $this->config['user'], $this->config['pass'], $this->config['name'] ) or $this->error();
		
		/**
		** Load DB driver
		*/
		//$this->driver = new mysqli( cp::$conf['db_host'], cp::$conf['db_user'], cp::$conf['db_pass'], cp::$conf['db_name'] ) or $this->error();
		
	}
	
	/**
	** num() - return rows from sql query
	** 
	** @sqlResult	= sql exec() result
	*/
	public function num( $sqlResult )
	{
		return $sqlResult->num_rows;
	}
	
	/**
	** error() - returns detailed error report if allowed
	**
	**
	*/
	public function error( $query ) {

		/**
		** If error class exists else die
		*/
		if ( class_exists( Debug ) )
			Debug::master( 'sql', $this->driver->error, $query );
		else
			die( $this->driver->error );
		
	}
	
	/**
	** addDelay() and runDelayedQ - adds a delayed query and runs delayed query near end of script
	**
	** @query	= string of query to save
	*/
	public function addDelay( $query )
	{
		$this->delayQs[] = $query;
	}

	public function runDelayedQ()
	{
		foreach( $this->delayQs as $key => $query )
		{
			$this->exec( $query );
		}
	}
	
	/**
	** insert() - insert an array
	**
	** ^return	= returns insert id
	*/
	public function insert( $table, $array )
	{
		
		/* This allows us to update init tables instead of insert, useful for building initial data */
		if ( OW_INSERT_FOR_INIT_DATA === true  )
		{
			if ( $table == 'online' ) return;
			
			$row = cp::db()->fetch( array(
				'table'		=> '`schema`',
				'where'		=> '`1` = "init_'.OW_INSERT_FOR_INIT_DATA.'"',
				'r'			=> 'one',
			) );
			
			if ( $row['array'] )
			{
				$row = json_decode( $row['array'], true );
			}
			
			$array = cp::slash_array($array);
			
			$row[ $table ][] = $array;		
			$row = addslashes(json_encode($row));
		
			cp::db()->update( 'schema', array('`1`'=>'init_'.OW_INSERT_FOR_INIT_DATA), array('array'=>$row) );
			
		}
		else
		{
			
			$this->exec("
				INSERT INTO `".$table."`
					( ".$this->paramMaker( array_keys( $array ), ',', '`', '`' )." )
				VALUES
					( ".$this->paramMaker( $array, ',', '\'', '\'' )." )
			");
			
			$this->lastId = $this->driver->insert_id;
			
			return $this->lastId;
			
		}
					
	}
	
	/**
	** insertMany() - insert an array
	*/
	public function insertMany( $table, $arrays, $build=false )
	{
		
		$keys = array();
		
		/* Get array with largest key count */
		foreach( $arrays as $key => $array )
		{			
			$keys = array_merge( array_keys( $array ), $keys );		
		}
		
		$keys = array_unique( $keys );
		
		/* default array to merge into */
		$default_array = array_fill_keys( $keys, '' );	
		
		/* We'll now use that array as a base */
		foreach( $arrays as $key => $array )
		{
			/* Merge into... */
			$array = array_merge( $default_array, $array );
			
			/* Build Q */
			if ( $vals ) $vals .= ', ';
			$vals .= "( ".$this->paramMaker( $array, ',', '\'', '\'' )." )";
			
		}
		
		$query = "
				INSERT INTO `".$table."`
					( ".$this->paramMaker( $keys, ',', '`', '`' )." )
				VALUES
					".$vals."
		";
		
		if ( $build ) return $query;
		
		$this->exec($query);
		
		$this->lastId = $this->driver->insert_id;
		
		return $this->lastId;
					
	}
	
	/**
	** update() - update a row
	**
	** @table	= Table to update
	** @key		= Id (or keyKey) of row OR "where array" following fetch() format
	** @array	= Array of values to update row with
	** 			  Set '+1' to update by one, set -1 to minus by one
	** @keyKey	= Col to use as row key (if not 'id')
	** @delay	= delay query
	** @sep		= if $key is array, use sep as where seperator (OR / AND)
	*/
	public function update_dep( $table, $key, $array, $keyKey='id', $delay=false, $sep='AND' )
	{
		
		/**
		** Array into query
		*/
		foreach( $array as $k => $v )
		{
			if ( $b ) $b .=', ';
			
			/**
			** Update things
			*/			
			if ( $v === '+1' )
				$b .= "`".$k."` = `".$k."` + 1";
			elseif ( $v === '-1' )
				$b .= "`".$k."` = `".$k."` - 1";
			else		
				$b .= "`".$k."` = '".$v."'";
		}
		
		/**
		** Where
		*/
		if ( is_array( $key ) )
		{
			foreach( $key as $col => $val )
			{
				if ( is_numeric( $col ) ) $col = $keyKey;
				if ( $where ) $where .= ' '.$sep.' ';
				$where .= "`".$col."` = '".$val."'";
			}					
		}
		else
		{
			$where = "`".$keyKey."` = '".$key."'";
		}
		
		$q = "UPDATE `".$table."` SET ".$b;
		
		if ( $key )
		{
			$q .= " WHERE ".$where;
		}
		
		/**
		** Run query
		*/		
		$this->exec($q, $delay );
				
	}
	
	/**
	** insertOnDupeUpdate() - insert, else updates!
	** 
	** @table	= Table to update
	** @key		= Id (or keyKey) of row OR "where array" following fetch() format
	** @array	= Array of values to update row with
	** 			  Set '+1' to update by one, set -1 to minus by one
	** @keyKey	= Col to use as row key (if not 'id')
	** @delay	= delay query
	** @sep		= if $key is array, use sep as where seperator (OR / AND)
	*/
	public function insertOnDupeUpdate( $table, $array, $table_key='id', $delay=false )
	{
		
		$q = "INSERT INTO `".$table."` ( ".$this->paramMaker( array_keys( $array ), ',', '`', '`' )." ) VALUES ( ".$this->paramMaker( $array, ',', '\'', '\'' )." )";
		
		unset( $array[ $table_key ] );
		
		$q .= " ON DUPLICATE KEY UPDATE ";
		
		foreach( $array as $k => $v )
		{
			if ( $b ) $b .=', ';
			
			/**
			** Update things
			*/			
			if ( $v === '+1' )
				$b .= "`".$k."` = `".$k."` + 1";
			elseif ( $v === '-1' )
				$b .= "`".$k."` = `".$k."` - 1";
			else		
				$b .= "`".$k."` = '".$v."'";
		}
		
		$q .= $b;
		
		return $this->exec($q, $delay);		
		
	}
	
	/**
	** count()
	*/
	public function count($table, $col, $where, $sep='AND')
	{
		$q .= 'SELECT COUNT('.$col.') AS NumHits FROM '.$table.' WHERE ';
		
		if ( is_array( $where ) )
		{
			foreach( $where as $col => $val )
			{
				if ( $b ) $b .= ' '.$sep.' ';
				$b .= "`".$col."` = '".$val."'";
			}					
		}
		else
		{
			$b = $where;
		}
		
		$q.= $b;
		
		return $this->exec($q);
	}
	
	
	/**
	** delete() - delete row
	**
	** @table	= Table to delete from
	** @key		= Id (or keyKey) of row
	** @keyKey	= Col to use as row key
	** @delay	= delay query
	*/
	public function delete( $table, $key, $keyKey='id', $delay=false )
	{		
		$q = 'DELETE FROM `'.$table.'` WHERE `'.$keyKey.'` = "'.$key.'"';		
		$this->exec($q);		
	}
	
	/**
	** fetch() - fetch information from database
	** 
	** NB:	To get a single row or all rows you may want to use $this->get() instead
	**
	** @array
	** 	 'select'	= Defaults to *,
	** 	 'table'	= Table to select
	**	 'save'		= Def: table (above), overwrites table name to save to
	** 	 'join'		= @array( 'table', 'where' (on) )
	** 	 'joins'	= @array of join arrays
	** 	 'where'	= Where column, string or array
	** 	 'order'	= Order
	** 	 'limit'	= Limit
	** 	 'one'		= Return result as a single array
	** 	 'ret'		= ID of row to return (use instead of 'one')?
	**   'key'		= Col to use as array key to save information (usually id)
	** 	 'resono'	= Return only the array from the sql query
	** 	 'wATQ'		= 'whereArrayToQuery' secondary var
	** 	 'result'	= Return pure sql result
	** 	 'record'	= Records this column to $this->record
	** 	 'r'		= return rows
	** @array = query string
	** @table = table to save information if not set as array
	** @key	  = shortcut to array['key']
	** @one   = shortcut to array['one']
	*/
	public function fetch_dep( $array, $table=false, $key='id', $one=false )
	{

		Debug::flag_query( $this->qCount, 'fetch_dep() depreciated' );
		
		if ( is_array( $array ) )
		{
		
			/**
			** Set other Vars
			*/	
			$table 	= $array['table'];
			$key	= ( $array['key'] ) ?: $key;
			$one	= $array['one'];
			
			/**
			** Create Query
			*/			
			if ( !$array['table'] )
			{
				//return;
				die('fetch() must name a table');
			}
			
			if ( $array['select'] )
				$q .= "SELECT {$array['select']} FROM {$array['table']}";
			else
				$q .= "SELECT * FROM {$array['table']}";
				
			/**
			** Join...
			*/
			if ( $array['join'] )
			{
				$q .= " " . strtoupper($array['join']['type']) . " JOIN {$array['join']['table']}";
				
				if ( $array['join']['where'] )
					$q .= " ON {$array['join']['where']}";
				
			}
			
			if ( $array['joins'] )
			{
				
				foreach( $array['joins'] as $k => $joinArray )
				{
					
					$q .= " " . strtoupper( $joinArray['type'] ) . " JOIN {$joinArray['table']}";
					
					if ( $joinArray['where'] )
						$q .= " ON {$joinArray['where']}";
						
				}
				
			}
				
			/**
			** Where...
			*/
			if ( is_array( $array['where'] ) )
			{

				# Return if nothing in array
				$queried = cp::db()->whereArrayToQ( $array['where'], $array['wATQ'] );

				# Return false if not exist
				if ( !$queried ) return false;
				
				# Add
				$q .= " WHERE " . $queried;
				
			}
			else
			if ( $array['where'] )
				$q .= " WHERE {$array['where']}";
			
			
			/**
			** Order
			*/		
			if ( $array['order'] )
				$q .= " ORDER BY {$array['order']}";
			
				
			/**
			** Limit
			*/	
			if ( $array['limit'] )
				$q .= " LIMIT {$array['limit']}";
				
		}
		else
		{
			$q = $array;
		}
		
		/**
		** Run Query
		*/
		$res = $this->exec($q);
		
		/**
		** Will we be recording results?
		*/
		if ( $array['record'] )
		{
			$this->record = array();
		}
		
		/**
		** Did we get anything?
		*/
		if ( $res->num_rows ) {

			while( $a = $res->fetch_assoc() )
			{	
				$r[ $a[ $key ] ] = $a;
				
				if ( $array['record'] )
				{
					if ( is_array( $array['record'] ) )
					{
						foreach( $array['record'] as $k => $col )
						{
							if ( !$a[ $col ] ) continue;
							
							if ( is_numeric( $k ) )
							{
								$this->record[] = $a[ $col ];
							}
							else
							{
								$this->record[$k][] = $a[ $col ];
							}
						}
					}
					else
					{
						if ( !$a[ $array['record'] ] ) continue;
						$this->record[] = $a[ $array['record'] ];
					}
				}
								
			}
		
		}else{			
			return FALSE;			
		}
		
		$save = ( $array['save'] ) ?: $table;
		
		$this->save( $r, $save );

		/**
		** What to return?
		*/
		if ( $array['result'] )
		{
			return $res;
		}
		else
		if ( $one )
		{	
			$arr = $this->get( $save ); /* Strict standards */		
			return array_shift( $arr );
		}
		else
		if ( $array['ret'] )
		{
			return $this->get( $save, $array['ret'] );
		}
		else
		if ( $array['resono'] )
		{
			return current($r);
		}
		else
		if ( $array['r'] )
		{
			return $r;
		}
		else
			return $this->get( $save );
		
	}
	
	/**
	** save() - saves rows from fetch()
	**
	** @res		= MySql Result
	** @table	= Table
	*/
	public function save( $res, $table )
	{
		
		if ( is_array( $res ) )
	    {
		    
		    /**
		    ** Have we saved this table before?
		    */		    
		    if ( $this->save[ $table ] )
		    {			    
			    $this->save[ $table ] = array_replace(  $this->save[ $table ], $res );				    
		    }
		    
		    /**
		    ** We've never used this table... simple!
		    */		    
		    else
		    {			    
			    $this->save[ $table ] = $res;			    
		    }
		    
	    }
		
	}
	
	/**
	** get() - return saved rows
	**
	** @table	= Table to get from
	** @key		= Save key, if false get all
	*/
	public function get( $table, $key=false, $keyName='id' )
	{

		if ( $key )
		{
			
			if ( $this->save[ $table ][ $key ] )
			{
				return $this->save[ $table ][ $key ];				
			}
			else
			{

				/*$new = $this->fetch_dep( array(
					'table' => $table,
					'where' => $keyName . '="'.$key.'"',
					'ret'	=> $key,
				) );*/
				
				$new = $this->fetch_dep( array(
					'table' => $table,
					'where' => $keyName . '="'.$key.'"',
					'ret'	=> $key,
				) );

				return $new;
				
			}
			
		}
		else
		
			if( $this->save[ $table ] )
				return $this->save[ $table ];
			else
			{				
				$new = $this->fetch_dep( array(
					'table' => $table,
				) );				
				return $new;				
			}			
		
	}
	
	/**
	** resort() - reorder a result
	**
	** @rows	= array of rows (from get() or fetch())
	** 			= string of table to "get". If the table is not already received, use fetch() not this.
	** @key		= key to sort by
	** @by		= Sort order ('asc','desc', SORT_ASC etc );
	*/
	public function resort( $rows, $keyBy, $order='asc' )
	{
		
		if ( !is_array( $rows ) )
			$rows = $this->get( $rows );
		
		if ( $order == 'asc' )
			$order = SORT_ASC;
		else
		if ( $order == 'desc' )
			$order = SORT_DESC;
			
		# Obtain a list of columns
		foreach ($rows as $key => $row) {
		    $id[ $key ]  = $row[ $keyBy ];
		}
			
		array_multisort( $id, $order, $rows );
		
		return $rows;		
		
	}
	
	/**
	** whereArrayToQ() - turns a multi dimensional array into a complex where statement
	** 
	** @array	= array(
	**					'id' 	=> array('1', '2'),
	**					'email'	=> 'e@g.com'
	**				 )
	** 			 becomes, select ( id = 1 or id = 2 ) AND ( email = e@g.com );
	** @defAmbig = When joining, its important to define cols as table.col, this variable sets default table if not defined
	*/
	public function whereArrayToQ( $array, $defAmbig )
	{
		
		$keys = array_keys( $array );

		/**
		** Create wheres....
		*/		
		foreach( $keys as $col )
		{

			if ( $where ) $where .= ' AND ';
			
			if ( !is_array( $array[ $col ] ) )
			{	
				if ( $defAmbig AND !strpos( $col, '.' ) ) $coln = $defAmbig . '.' . $col;
				$where .= '( '.$coln.'="'.$array[ $col ].'" )';	
				$one = true;
			}
			else
			{
				# Remove Duplicates
				$array[ $col ] = array_unique( $array[ $col ] );
				
				# Build Q
				$where .= '( ';
				
				unset( $t );
				
				foreach ( $array[ $col ] as $value )
				{
					if ( $value ) $one = true;
					if ( $t ) $t .= ' OR ';
					if ( $defAmbig AND !strpos( $col, '.' ) ) $col = $defAmbig . '.' . $col;
					$t .= ''.$col.'="'.$value.'"';
				}
				
				$where .= $t . ' )';
				
			}
			
			
		}
		
		if ( $one )
			return $where;
		else
			return false;
		
	}
	
	/**
	** getGlobals()
	** 
	** Depreciated
	*/
	public function getGlobals()
	{
		
		/**
		** Get Globals
		*/
		$settings = $this->exec(
			"SELECT globals.arrayTitle, globals.value FROM globals LEFT JOIN globalcats ON globalcats.text_id=globals.catId AND enabled=1"
		);
				
		/**
		** Save...
		*/
		while( $a = $settings->fetch_assoc() )
		{	
			$this->globals[ $a['arrayTitle'] ] = $a['value'];				
		}
		
		/**
		** Loaded
		*/			
		$this->globalsLoaded = true;
		
	}
		
	/*
	**	paramMaker() - Seperates each value of $array with $seperator and returns string	
	*/
	public function paramMaker($array, $seperator, $prefix = NULL, $suffix = NULL) {
		
		foreach( $array as $k => $v ) {
			if ( $ret ) $ret .= $seperator;
			$ret .= $prefix . $v . $suffix;
			$i = TRUE;
		}
		
		return $ret;
		
	}
	
	/**
	** permRegexBuild() - call perm->allowMe() instead?
	**
	** @col - column to check against
	** @array - array of values
	*/
	public function permRegexBuild( $col, $array )
	{		
		if ( is_array( $array ) ){
			return "{$col} REGEXP '," . implode( ',|,', $array ) . ',|\\\*\'';
		}else{
			return "{$col} REGEXP '," . $array . ',|\\\*\'';
		}
	}
	
	/**
	** slugify() - turns a string into a slug
	**
	** @text	= String to slugify
	** @table	= Table to check slug for uniqueness
	*/
	public function slugify( $text, $table )
	{
	    // Swap out Non "Letters" with a -
	    $text = preg_replace('/[^\\pL\d]+/u', '-', $text); 
	
	    // Trim out extra -'s
	    $text = trim($text, '-');
	
	    // Convert letters that we have left to the closest ASCII representation
	    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
	
	    // Make text lowercase
	    $text = strtolower($text);
	
	    // Strip out anything we haven't been able to convert
	    $text = preg_replace('/[^-\w]+/', '', $text);
	    
	    $notallowed = cp::$appConf['pages'];
	    
	   	$text = ( in_array( $text, $notallowed ) ) ? $text.'-1': $text;
	   	
	   	$text = ( is_numeric( $text ) ) ? '-'.$text: $text;
	   	
	   	/*if ( is_array( $table ) )
		{
			
			foreach( $table as $k => $joinArray )
			{
				
				$q .= " " . strtoupper( $joinArray['type'] ) . " JOIN {$joinArray['table']}";
				
				if ( $joinArray['where'] )
					$q .= " ON {$joinArray['where']}";
					
			}
			
		}*/
		
		if ( is_array( $table ) )
		{
			$q = "SELECT ";
			foreach( $table as $name )
			{
				$b .= ( $b ) ? " + ": '';
				$b .= "( SELECT COUNT(*) FROM `".$name."` WHERE `slug` LIKE '".$text."%' )";
			}
			$q .= $b . " AS NumHits";			
		}
		else
		{
			$q = "SELECT COUNT(*) AS NumHits FROM `".$table."` WHERE `slug` LIKE '".$text."%'";			
		}
	   	
	   	$r 			= $this->exec($q);
	    $row 		= $r->fetch_assoc();	    
		$numHits 	= $row['NumHits'];
		
		return ($numHits > 0) ? ($text . '-' . $numHits) : $text;

	}
	
	public function checkslug( $text, $table )
	{

		if ( $this->exec("SELECT * FROM `".$table."` WHERE `slug` = '".$text."'") )
		{
			return TRUE;
		}else{
			return FALSE;
		}
		
	}
	
	
}
	
?>