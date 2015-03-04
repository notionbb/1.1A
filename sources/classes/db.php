<?php

	/*===================================================
	//	CipherPixel © All Rights Reserved
	//---------------------------------------------------
	//	CP-Core
	//		by cipherpixel.net
	//---------------------------------------------------
	//	File created: October 07, 2014 
	//=================================================*/

class db extends dbOLD {
	
	/**
	** Holds the query from the last build
	** 
	** @var		array
	*/
	private	$last_build;
	
	/**
	** Raw MySql result
	** 
	** @var		object
	*/
	private	$last_res;
	
	/**
	** Run a SQL Query
	** 
	** @param	string	$query	String of query
	** @param	bool	$delay	Delay query till end of script
	** @return	object
	*/
	public function exec( $query, $delay=false )
	{
		
		/* Delay? */
		if ( $delay )
		{
			$this->addDelay( $query );
			return;
		}
		
		//-----------------------------------
		// Debugging
		//-----------------------------------
		
		/* Record Queries */
		if ( DEBUG_SQL === true )
		{
			$back = debug_backtrace();
			$this->qHistory[ $this->qCount ] = array('q' => $query, 'file' => $back['0']['file'], 'line' => $back['0']['line']);
		}
		
		/* Scout out large queries */
		if ( DEBUG === true )
		{
			Debug::settimer('query');
		}
		
		//-----------------------------------
		// Query
		//-----------------------------------

		$result = $this->driver->query( $query ) or $this->error($query);
		
		//-----------------------------------
		// Debugging
		//-----------------------------------
		
		if ( DEBUG === true )
		{
			$this->qHistory[ $this->qCount ]['time'] = Debug::endtimer();
		}
		
		/* Count query */
		$this->qCount++;
		
		return $result;
		
	}
	
	/**
	** Did we get a result?
	** 
	** @return	object/int
	*/
	public function result()
	{
		
		if ( $this->last_res )
		{
			return $this->last_res->num_rows;
		}
		else
		{
			$this->last_res = $this->exec( $this->last_build['query'] );
			return $this->last_res->num_rows;
		}
		
		Debug::mess('db::result() called without anything exec()');
	}
	
	/**
	** build() - builds and returns a query
	** 
	** @param	array	$array	Array detailing query information
	** 	'select'	=> str/ select
	** 	'table' 	=> str/ table name
	** 	'where'		=> str/ where conditions
	** 				=> arr/ id=> 1, name => mic
	** 				=> arr/ id=> array of ids, name=> array of names
	** 	'sep'		=> str/ where seperator (def=OR)
	** 	'join'		=> arr/ table=> join table, where=> join on, type=> join type (Def, left)
	** 	'joins'		=> arr/ will merge with 'join'
	** 	'order'		=> str/ order
	** 	'limit'		=> str/ limit
	** 	'echo'		=> bool/ (debug), echos query before execution
	**  @return	string
	*/    
	public function build( $array )
	{
		
		$this->last_res = false;
		
	   //-----------------------------------
	   // Initialize
	   //-----------------------------------    
		
		if ( !$array['table'] ) die('table must be set');
		
		$array['sep'] = ( isset( $array['sep'] ) ) ?: false;
		
		$join = false;
		
	   //-----------------------------------
	   // Build Query
	   //-----------------------------------	    	    
		
	    /* Select */
	    
		$query = 'SELECT';
		
		$query .= $array['select'] ? ' ' . $array['select']: ' * ';
		
		/* Table */
		
		$query .= ' FROM ' . $array['table'];
		
		/* Did some noob (me?) use different join array names? */
		
		if ( is_array( $array['join'] ) AND is_array( $array['joins'] ) )
		{
			$join = array_merge( $array['join'], $array['joins'] );
		}
		
		else
		
		/* Check joins is an array */
		
		if ( is_array( $array['joins'] ) )
		{
			$join = $array['joins'];
		}
		
		else
		
		/* Perhaps we've saved to 'join' */
		
		if ( $array['join']['table'] )
		{
			$join = array( $array['join'] );
		}
		
		else
		
		/* Or maybe perhaps just join */
		
		if ( is_array( $array['join'] ) )
		{
			$join = $array['join'];
		}

		/* Create Joins */
		
		if ( $join )
		{
		
			foreach( $join as $join_array )
			{
				
				$join_array['type'] = $join_array['type'] ? ' ' . $join_array['type']: '';
				
				$query .= strtoupper( $join_array['type'] ) . ' JOIN ' . $join_array['table'];
						
				if ( $join_array['where'] )
					$query .= ' ON ' . $join_array['where'];			
				
			}
			
			$join = true;
			
		}	
		
		/* Where */
		
		if ( is_array( $array['where'] ) )
		{
			
			/* Do we need to make the where column amiguous */
			$make_ambig = ( $join ) ? $array['table']: false;
			
			$query .= ' WHERE ' . $this->build_where( $array['where'], $array['sep'], $make_ambig );			
		}	
		else
		if ( $array['where'] )
		{
			$query .= ' WHERE ' . $array['where'];
		}
		
		/* Order */
		
		if ( $array['order'] )
		{
			$query .= ' ORDER BY ' . $array['order'];
		}
		
		/* Limit */
		
		if ( $array['limit'] )
		{
			$query .= ' LIMIT ' . $array['limit'];
		}
		
		/* Echo Query? */
		if ( $array['echo'] )
		{
			echo $query;
		}
		
		/* Options can be sent to fetch() like this */
		$this->last_build = array(
			'query'	=> $query,
			'table'	=> $array['table'],
			'key'	=> $array['key'],
			'record'=> $array['record'],
			'r'		=> $array['r'],
		);
		
		$this->last_query = $query;
			
		return $this;
		
	}
	
	/**
	** Sets table for db chain
	** 
	** @param	string	$table	Table name
	** @return	self
	*/
	public function query($table)
	{
		
		$this->last_res = false;
		
		$this->last_build = array(
			'table' => $table,
			'query' => 'SELECT * FROM ' . $table,
		);
		
		return $this;
		
	}
	
	/**
	** Sets where conditions for db chain
	** 
	** @param	mixed	$where
	** @return	self
	*/
	public function where($where)
	{
		
		if ( is_array( $where ) )
		{
			$this->last_build['query'] .= ' WHERE ' . $this->build_where( $where, false );			
		}	
		else
		if ( $array['where'] )
		{
			$this->last_build['query'] .= ' WHERE ' . $array['where'];
		}
		
		return $this;
		
	}
	
	/**
	** Runs sql query ($this->last_build) and returns an array
	** 
	** @return	array
	*/
	public function toArray()
	{
		
		/* Allow increment through while loops */
		if ( $this->last_res )
		{
			return $this->last_res->fetch_assoc();
		}

		/* Run query */			
		$query = $this->last_build['query'];
		$this->last_res = $this->exec( $query );
		
		return $this->toArray();		
		
	}
	
	/**
	** fetch() runs a sql query, returns results and saves if required
	** 
	** @param	string/array	$query		build() result or array for build()
	** @param	array			$options	array of options (save requirements etc)
	** 		'table'	=> Table to save, set to false to not save at all
	** 		'key'	=> Key to save result from (Def: id)
	** 		'record'=> String or array of column names to save
	** 		'r'		=> Return (arr=array, one=first result, res=pure sql) (Def: arr)
	** @return	bool/array
	*/
	public function fetch( $query=false, $options=array() )
	{
		
		if ( $query === false )
		{
			$query 		= $this->last_build['query'];
			$options	= array_merge( $this->last_build, $options );
		}
		
		/* Build query if we haven't already */
		if ( is_array( $query ) )
		{
			$query 		= $this->build( $query )->last_build['query'];
			$options	= array_merge( $this->last_build, $options );
		}
		
		/* Run Query */
		$res = $this->exec( $query );
		
		/* Did we get any results? */
		if ( !$res->num_rows )
		{
			return false;
		}
		
		$this->last_res = $res;
		
		/* Return pure sql result */
		if ( $options['r'] == 'res' )
		{
			return $res;
		}
		
		/* Default, Vars */
		$options['key'] = ( $options['key'] ) ?: 'id';
		$options['r'] = ( $options['r'] ) ?: 'arr';
		
		/* Destroy last record if we intend to record */
		if ( $options['record'] )
		{
			$this->record = null;
		}
		
		/* Turn Results into array */
		while( $res_array = $res->fetch_assoc() )
		{

			$to_save[ $res_array[ $options['key'] ] ] = $res_array;
			
			/* Save Keys */
			if ( $options['record'] )
			{
				
				if ( is_array( $options['record'] ) )
				{
					foreach( $options['record'] as $k => $col )
					{

						if ( is_array( $col ) )
						{
							foreach( $col as $res_col )
							{
								$this->record[$k][] = $res_array[ $res_col ];
							}
						}
						else
						{
						
							if ( !$res_array[ $col ] ) continue;
							
							if ( is_numeric( $k ) )
								$this->record[] = $res_array[ $col ];
							else
								$this->record[$k][] = $res_array[ $col ];
								
						}
						
					}
				}
				else
				{
					if ( !$res_array[ $options['record'] ] ) continue;
					$this->record[] = $res_array[ $options['record'] ];
				}
				
			}
			
		}
		
		/* Save table */
		if ( $options['table'] )
		{
			$this->save( $to_save, $options['table'] );
		}
		
		/* Return first result from query */
		if ( $options['r'] == 'one' )
		{
			return array_shift($to_save);
		}
		
		/* Return multidimensional array */
		if ( $options['r'] == 'arr' )
		{
			return $to_save;
		}		
		
	}
	
	/**
	** update()
	** 
	** @param	string			$table			table to update
	** @param	string/array	$where			Id or row, or where array
	** @param	array			$new_array		array of new values
	** @param	array			$other			array of other query information
	** 				'query' => (true) returns query string
	*/
	public function update( $table, $where, $new_array, $other=false )
	{
		
		//-----------------------------------
		// Build Query
		//-----------------------------------
		
		$query = 'UPDATE `'.$table.'`';
		
		/* $new_array into a query string */

		foreach( $new_array as $k => $v )
		{
			if ( $b ) $b .=', ';
			
			/* +1 and -1 modifiers */		
			if ( $v === '+1' )
				$b .= "`".$k."` = `".$k."` + 1";
			elseif ( $v === '-1' )
				$b .= "`".$k."` = `".$k."` - 1";
			else		
				$b .= "`".$k."` = '".$v."'";
		}
		
		$query .= ' SET '.$b;
		
		/* Wheres */
		
		if ( $where )
		{
		
			$where = ( is_array( $where ) ) ? $where: array('id'=>$where);
		
			$query .= ' WHERE ' .$this->build_where( $where );
			
		}
		
		//-----------------------------------
		// Return
		//-----------------------------------
		
		if ( $other['query'] )
		{
			return $query;
		}
		else
		{
			$this->exec( $query );
		}
		
	}
	
   /**
    * Called from build to create where part of query.
    * 
    * @param	array	$array			Array to turn into where
    * 	'col...'	=> str/ 'val',
    * 	'col...'	=> arr/ array of values
    * @param	string	$sep			Default Seperator
    * @param	string	$make_ambig		Whether to add ambiguity to the query. If true set as table to use
    * @return	string
    */
    public function build_where( $array, $sep='OR', $make_ambig=false )
    {
	    
	    $b 		= false;
	    $sep 	= $sep ?: 'OR';
	    
	    foreach( $array as $k => $v )
	    {
		    
		    if ( is_numeric($k) AND ( $v === 'AND' OR $v === 'OR' ) )
		    {
			    $b.= ' '.$v.' ';
			    $seperated = true;
			    continue;
		    }
		    
		    if ( $b AND !$seperated )
		    {
			    $b.= ' ' . $sep . ' ';
		    }
		    
		    if ( is_array( $v ) )
		    {
			    /* Remove duplicate items in array */
			    $v = array_unique($v);
			    
			     /* Table prefix required when joining */
			    if ( $make_ambig )
			    {
				    $k = $make_ambig .'.'. $k;
			    }
			    
			    foreach( $v as $v2 )
			    {
				    if ( $b ) $b.= ' ' . $sep . ' ';
					$b .= $k.'="'.$v2.'"';
			    }			    
			    
		    }
		    else
		    {			    
			    /* Table prefix required when joining */
			    if ( $make_ambig ) $k = $make_ambig .'.'. $k;
			    
			    $b .= $k.'="'.$v.'"';
		    }
		    
		    $seperated = false;
		    
	    }
	    
	    return $b;
	    
    }
    
    //-----------------------------------
    // DB Structure Tools
    //-----------------------------------
    
    /**
    ** Create table query
    ** 
    ** @param	string	$table	Name of table
    ** @param	array	$cols	Array of columns
    ** @return	string
    */
    public function create_table( $table, $cols )
    {
	    
	    $query = "CREATE TABLE IF NOT EXISTS `".$table."` (\n";
	    
	    foreach( $cols as $col_name => $col_info )
	    {
		    $query .= "\t`".$col_name."` ".$col_info['Type'];		    
		    $query .= ( $col_info['Null'] == 'NO' ) ? ' NOT NULL': ' NULL';		    
		    $query .= ( $col_info['Default'] ) ? " DEFAULT \"".$col_info['Default']."\"": '';
		    $query .= ( $col_info['Extra'] ) ? " ".$col_info['Extra']: '';		    
		    $query .= ",\n";	
		    
		    if ( !$table_key AND $col_info['Key'] == 'PRI' )
		    {
		   		$table_key = $col_name;
	   	 	}	    	    
	    }
	    
	    $query = ( $table_key ) ? $query . " PRIMARY KEY (`".$table_key."`)\n": substr($query, 0, -2) . "\n";
	    $query .= ");";
	    
	    return $query;
	    
    }
    
    public function alter_table( $table, $cols )
    {
	    
	    $query = "ALTER TABLE `".$table."`\n";
	    
	    foreach( $cols as $col_name => $col_info )
	    {		    
		    $add .= ( $add ) ? ",\n": "";
		    
		    $add .= "\tADD `".$col_name."` ".$col_info['Type'];		    
		    $add .= ( $col_info['Null'] == 'NO' ) ? ' NOT NULL': ' NULL';		    
		    $add .= ( $col_info['Default'] ) ? " DEFAULT \"".$col_info['Default']."\"": '';
		    $add .= ( $col_info['Extra'] ) ? " ".$col_info['Extra']: '';		    
		    
		    if ( !$table_key AND $col_info['Key'] == 'PRI' )
		    {
		   		$table_key = $col_name;
	   	 	}	    	    
	    }	    

	    $query .= $add .";";
	    
	    return $query;
	    
    }
	
}
	
?>