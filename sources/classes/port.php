<?php

	/*===================================================
	//	CipherPixel © All Rights Reserved
	//---------------------------------------------------
	//	CP-Core
	//		by cipherpixel.net
	//---------------------------------------------------
	//	File created: November 05, 2014 
	//=================================================*/
	
	/**
	** Contains the build functions to export applications, also the install applications
	** 
	*/

class port {
	
	/**
	** Compulsory tables
	** 
	** @var		array
	*/
	private	$comp_tables = array(
		'globalcats' 	=> 'text_id',
		'globals' 		=> 'arrayTitle',
		'groupsettings'	=> 'name',
		'modules'		=> 'name',
		'usersettings'	=> 'name',
		'tools'			=> 'task',
	);
	
	/**
	** Default unique key
	** 
	** @var		string
	*/
	private	$def_key	= 'name';
	
	/**
	** Install flow
	** 
	** @param	object	$class
	** @param	bool	$install	Set whether this is a first time install or nor
	** @return	array
	*/
	public function install_flow( $class, $install )
	{
		
		cp::$GET['do'] = ( cp::$GET['do'] ) ?: '1';
		
		$do = cp::$GET['do'];
		
		//-----------------------------------
		// Create Structure
		//-----------------------------------
		
		if ( $do == '1' )
		{
			$new	 = json_decode( $class->structure_queries(), true );
			
			//-----------------------------------
			// Compare current to new database schemas
			//-----------------------------------
			
			foreach( $new as $table_name => $cols )
			{
				
				/* Check if current table exists */
				$table_info = cp::db()->fetch( array(
					'select'	=> 'COUNT(*) as count',
					'table'		=> 'information_schema.tables',
					'where'		=> 'table_schema="'.cp::$conf['db_name'].'" AND table_name="'.$table_name.'"',
					'r'			=> 'one',
				) );
				
				/* If table doesn't exist insert whole table */
				if ( $table_info['count'] < 1 )
				{
					$tables_to_add[] = $table_name;
					continue;
				}
				
				/* Get table Columns */
				$c_cols = cp::db()->fetch("SHOW COLUMNS FROM `".$table_name."`", array('key'=>'Field'));
				$c_cols_array = array_keys($c_cols);
				
				
				foreach( $cols as $col_name => $col_array )
				{

					if( in_array( $col_name, $c_cols_array ) )
					{
						unset( $c_cols[ $col_name ] );
						continue;
					}
					
					$cols_to_add[ $table_name ][ $col_name ] = $col_array;
					
				}
				
			}
			
			//-----------------------------------
			// Create New Tables
			//-----------------------------------
			
			if( is_array( $tables_to_add ) )
			{
				foreach( $tables_to_add as $table_name )
				{
					$to_run[] = array(
						'table'		=> $table_name,
						'type'		=> 'create',
						'query'		=> cp::db()->create_table( $table_name, $new[ $table_name ] ),
					);
				}
			}
			
			//-----------------------------------
			// Alter Tables
			//-----------------------------------
			
			if( is_array( $cols_to_add ) )
			{
				foreach( $cols_to_add as $table_name => $cols )
				{
					$to_run[] = array(
						'table'		=> $table_name,
						'type'		=> 'alter',
						'query'		=> cp::db()->alter_table( $table_name, $cols ),
					);
				}
			}
			
			if ( !$to_run )
			{
				$report[] = array(
					'type'	=> 'structure',
				);
			}
			
		}
		else
		
		//-----------------------------------
		// Compulsary Data
		//-----------------------------------
		
		if ( $do == '2' )
		{
			
			$data = $class->init_queries();
			
			foreach( $data as $table_name => $array )
			{

				if ( $this->comp_tables[ $table_name ] )
				{
					
					$database = cp::db()->fetch( array(
						'table'	=> $table_name,
						'record'=> $this->comp_tables[ $table_name ],
						'key'	=> $this->comp_tables[ $table_name ],
					) );
					
					$current_unique_keys = cp::db()->record;
					
					unset( cp::db()->record );
					
					foreach( $array as $key => $cols )
					{
						
						/* Row not found, insert all */
						if ( !is_array( $current_unique_keys ) OR !in_array( $cols[ $this->comp_tables[ $table_name ] ], $current_unique_keys ) )
						{

							$to_ins[] = $cols;
							continue;
						}
						
						/* Look for differences */
						$differences = array_diff( $cols, $database[ $cols[ $this->comp_tables[ $table_name ] ] ] );
						
						if ( count( $differences ) > 0 )
						{
							
							$to_run[] = array(
								'table'		=> $table_name,
								'type'		=> 'update',
								'query'		=> cp::db()->update( 	$table_name,
																	array( $this->comp_tables[ $table_name ] => $cols[ $this->comp_tables[ $table_name ] ] ),
																	$differences,
																	array( 'query' => true )
																),
							);
							
						}
						
					}
					
				}
				
				if ( is_array( $to_ins ) )
				{
				
					$to_run[] = array(
						'table'		=> $table_name,
						'type'		=> 'insert',
						'query'		=> cp::db()->insertMany( $table_name, $to_ins, true ),
					);
					
				}
				
				unset( $to_ins );
			}
			
			if ( !$to_run )
			{
				$report[] = array(
					'type'	=> 'comp',
				);
			}
			
		}
		else
		
		//-----------------------------------
		// Fresh Data
		//-----------------------------------
		
		if ( cp::$GET['do'] == '3' )
		{
			
			if ( $install )
			{
				$first = $class->first_data();
				
				foreach( $first as $table_name => $rows )
				{
					
					foreach( $rows as $row_key => $row )
					{
						foreach( $row as $col_key => $col_value )
						{
							if ( $col_value == 'CP_INS_TIME' )
							{
								$rows[ $row_key ][ $col_key ] = cp::$time;
							}
						}
					}
					
					$to_run[] = array(
						'table'		=> $table_name,
						'type'		=> 'insert',
						'query'		=> cp::db()->insertMany( $table_name, $rows, true ),
					);
				}
				
			}
			else
			{
				$report[] = array(
					'type'	=> 'start',
				);
			}
			
		}
		
		//-----------------------------------
		// Run Queries
		//-----------------------------------
		
		if ( is_array( $to_run ) )
		{
			
			foreach( $to_run as $array )
			{
				cp::db()->exec($array['query']);
				$report[ $array['table'] ] = array(
					'table'	=> ucfirst( $array['table'] ),
					'type'	=> $array['type'],
				);
			}
		}
		
		if ( $report )
		{		
			return $report;
		}
		
	}
	
	/**
	** Turns an array into an insert query
	** 
	** @param	array	$array	Array of tables and rows
	** @return	array
	*/
	public function make_insert_queries( $array )
	{
		
		foreach( $array as $table_name => $rows )
		{			
			$query = cp::db()->insertMany( $table_name, $array, true );
			$r[ $table_name ] = str_replace("), (", "),\n\t\t\t\t\t(", $query );			
		}
		
		return $r;
		
	}
	
	/**
	** Turns an array into an string for a php file to read
	** 
	** @param	array	$array	Array of tables and rows
	*/
	public function make_insert_array( $array )
	{
		
		foreach( $array as $table_name => $rows )
		{
			$b = "array(\n";
			
			foreach( $rows as $id => $cols )
			{				
				$b .= "\t\t\t\t'".$id."' => array(\n";
				
				foreach( $cols as $key => $value )
				{
					$b .= "\t\t\t\t\t'".$key."' => '".addslashes($value)."',\n";
				}
				
				$b .= "\t\t\t\t),\n";			
			}
			
			$b .= "\t\t\t),\n";
			
			$r[ $table_name ] = $b;
			
		}
		
		return $r;
		
	}
	
	/**
	** Returns the database schema
	** 
	** @param	string	$row		row id
	** @param	bool	$decode		whether to json decode or not
	** @return	array/string
	*/	
	public function get_schema_row($row="1", $decode=true)
	{
		
		$schema = cp::db()->fetch( array(
			'table'	=> '`schema`',
			'where'	=> '`1`="'.$row.'"',
			'limit'	=> '1',			
		), array('key'	=> '1') );
		
		
		return ( $decode ) ? json_decode( $schema[$row]['array'], true ): $schema[$row]['array'];
		
	}
	
	/**
	** Creates database structure
	** 
	** @param	array	$schema		Database schema
	** @param	string	$app_name	application name
	** @return	string
	*/
	public function get_json_structure($schema, $app_name)
	{
		
		foreach( $schema as $table_name => $table_schema )
		{
			
			/* Skip tables that have nothing to do with us */
			if ( $table_schema['mod'] != $app_name AND $table_schema['cols'] != 'custom' )
			{
				continue;
			}
			
			/* Tell build we'll be using this table */
			$build[ $table_name ] = array();
			
			/* Get column information */
			$columns = cp::db()->fetch("SHOW COLUMNS FROM `".$table_name."`", array('key'=>'Field'));
			
			foreach( $columns as $col_name => $col_array )
			{
				
				/* If how table is inherit, add each column or column is for this app */
				if ( $table_schema['cols'] == 'inherit' OR $table_schema['col'][ $col_name ] == $app_name )
				{
					$build[ $table_name ][ $col_name ] = $col_array;
				}
				
			}
			
		}
		
		return json_encode($build);
		
	}
	
	/**
	** Gets the compulsory (build) rows for the mod
	** 
	** @param	string	$app_unique_id		app's unique id
	** @param	array	$tables				tables to get data from
	** @return	string
	*/
	public function get_comp( $app_unique_id, $tables=false )
	{
		
		$tables = ( $tables ) ?: array_keys( $this->comp_tables );
		
		foreach( $tables as $table_name )
		{
			
			if ( $table_name == 'modules' )
			{
				$key = 'unique_id';
			}
			else
			{
				$key = 'modId';
			}
			
			$rows = cp::db()->fetch( array(
				'table'	=> $table_name,
				'where'	=> array( $key => $app_unique_id ),
				'r'		=> 'res',
			) );
			
			if ( $rows )
			{
			
				while( $row = $rows->fetch_assoc() )
				{
					if ( $table_name != 'globalcats' )
					{
						unset( $row['id'] );
					}
					
					/* Make 'modules' Def as actual value */
					if ( $table_name == 'globals' AND $row['valueDef'] != NULL )
					{
						$row['value'] = ( $row['valueDef'] == 'EMPTY' ) ? '': $row['valueDef'];
					}
						
					$many[] = cp::clean( $row );
					
				}
				
				$build[ $table_name ] = $many;
				unset( $many );
				
			}			
			
		}
		
		return $build;
		
	}
	
	
}
	
?>