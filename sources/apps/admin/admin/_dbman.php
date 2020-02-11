<?php

	/*===================================================
	//	NotionBB © All Rights Reserved
	//---------------------------------------------------
	//	NBB-Core
	//		by notionbb.com
	//---------------------------------------------------
	//	File Updated - 2-11-2020
	//=================================================*/
	
	/**
	** CP DB Manager
	** 
	** File not required by general users, feel free to remove
	*/
	
class admin__dbman extends controller {
	
	public function main()
	{
		
		if ( $_POST['database'] )
		{
			$this->process();
		}
		else
		if ( $_POST['init'] )
		{
			$this->process_init();
		}
		else
		{
			$this->show_form();
		}
		
		return true;
		
	}
	
	/**
	** process() - save the crazy form below
	** 
	*/
	public function process()
	{
		
		unset( $_POST['database'] );
		
		foreach( $_POST as $k => $v )
		{
			
			$exp = explode( "__", $k );
			
			if ( $exp['0'] == 'table' )
			{
				$newSchema[ $exp['1'] ][ 'mod' ] = $v;
				$last = $exp['1'];
			}
			
			if ( $exp['0'] == 'tableCols' )
			{
				if ( $exp['2'] )
					$exp['1'] .= '_' . $exp['2'];
				$newSchema[ $exp['1'] ][ 'cols' ] = $v;
			}
			
			if ( $exp['0'] == 'col' )
			{
				$newSchema[ $exp['1'] ]['col'][ $exp['2'] ] = $v;
			}
			
		}
		
		cp::db()->update_dep( 'schema', '1', array('array'=>json_encode($newSchema)), '1' );
		
	}
	
	/**
	** process_init
	** 
	*/
	public function process_init()
	{
		
		$row = cp::db()->fetch( array(
			'table'		=> '`schema`',
			'where'		=> '`1` = "'.$_POST['init_app'].'"',
			'r'			=> 'one',
		) );
		
		if ( $row['array'] )
		{
			$array = json_decode( $row['array'], true );
		}
		
		$array[ $_POST['init_table'] ][ $_POST['init_id'] ][ $_POST['init_key'] ] = $_POST['init_v'];
		
		$array = $this->add_slash($array);
		
		$array = json_encode( $array );
		
		cp::db()->update( 'schema', array('`1`'=>$_POST['init_app']), array('array'=>$array) );
		
	}
	
	public function add_slash( $array )
	{
		
		foreach( $array as $k => $v )
		{
			if ( is_array( $v ) )
			{
				$ret[$k] = $this->add_slash($v);
			}
			else
			{
				$ret[$k] = addslashes($v);
			}
		}
		return $ret;
	}
	
	/**
	** show_form() - show the schema management form
	** 
	*/
	public function show_form()
	{

		//-----------------------------------
		// Get Possible Modules
		//-----------------------------------
		
		$res = cp::db()->fetch( array(
			'select'=> 'name',
			'table'	=> 'modules',
			'r'		=> 'res',
		) );
		
		while( $m = $res->fetch_assoc() )
		{
			$modules_array[ $m['name'] ] = $m['name'];
		}
		
		//-----------------------------------
		// Get Schema and Tables
		//-----------------------------------
		
		$schema = cp::call('port')->get_schema_row();
		
		$tables = cp::db()->fetch( array(
			'select'	=> 'table_name',
			'table'		=> 'information_schema.tables',
			'where'		=> array( 'table_schema' => cp::$conf['db_name'] ),
		), array('key'=>'table_name') );
		
		//-----------------------------------
		// Loop tables
		//-----------------------------------
		
		foreach( $tables as $table_name )
		{

			$table_name = $table_name['table_name'];
			
			/* Skip Schema */
			if ( $table_name == 'schema' ) continue;
			
			/* Used for data below */
			$drops_table_rows .= '<option value="'.$table_name.'">'.$table_name.'</option>';
			
			//-----------------------------------
			// Create Dropdown
			//-----------------------------------
			
			$dropdown = '<select name="table__'.$table_name.'">';
			
			$cur = FALSE;
			$bef = '';
			
			foreach( $modules_array as $k => $v ) {
					
				if ( $schema[ $table_name ]['mod'] == $k ) {
					$dropdown .= '<option value="'.$k.'" selected="selected">'.$k.' (current)</option>';
					$cur = TRUE;
				}else{
					$dropdown .= '<option value="'.$k.'">'.$k.'</option>';
				}
				
			}
			
			if ( $cur == FALSE ) $bef = '<b>WARNING:</b> ';
			
			$dropdown .= '</select>';
			
			//-----------------------------------
			// Column Settings
			//-----------------------------------
			
			$col = '<select name="tableCols__'.$table_name.'">';
			
			if ( $schema[ $table_name ][ 'cols' ] == 'inherit' )
			{
				$col .= '<option value="inherit" selected="selected">Inherit (current)</option>';
				$col .= '<option value="custom">Custom</option>';
			}
			else
			{
				
				$col .= '<option value="inherit">Inherit</option>';
				$col .= '<option value="custom" selected="selected">Custom (current)</option>';
				
				/* Create Column Options */
				
				$table_cols = cp::db()->fetch("SHOW COLUMNS FROM `".$table_name."`");
				
				$res = cp::db()->fetch( "SHOW COLUMNS FROM `".$table_name."`", array('r'=>'res') );
				
				while( $table_col = $res->fetch_assoc() )
				{
								
					/**
					** Create Dropdown
					*/
					$colDropDown = '<select name="col__'.$table_name.'__'.$table_col['Field'].'">';
	
					$cur2 = FALSE;
					
					foreach( $modules_array as $k => $v ) {
							
						if ( $schema[ $table_name ]['col'][ $table_col['Field'] ] == $k ) {
							$colDropDown .= '<option value="'.$k.'" selected="selected">'.$k.' (current)</option>';
							$cur2 = TRUE;
						}else{
							$colDropDown .= '<option value="'.$k.'">'.$k.'</option>';
						}
						
					}
					
					if ( $cur2 == FALSE ) $bef = '<b>WARNING:</b> '; 
					
					$colDropDown .= '</select>';
					
					$ext[] = array( '', '', $table_col['Field'], $colDropDown );				
				}
				
			}
			
			$col .= '</select>';
			
			//-----------------------------------
			// Add to array
			//-----------------------------------
			
			$cptable[] = array( $bef . $table_name, $dropdown, $col );
			
			if ( $ext )
			{
				$cptable = array_merge($cptable, $ext);
				$ext = NULL;
			}
			
		}
		
		//-----------------------------------
		// Print Table
		//-----------------------------------
		
		$p = '<form method="post"><div class="schema_table"><table>';
		
		$p .= '
			<td class="sch_title">Table Name</td>
			<td class="sch_title">Associated Mod</td>
			<td class="sch_title">Col Settings</td>
			<td class="sch_title"></td>
			</tr>
		';
		
		foreach( $cptable as $row )
		{
			
			$p .= '<tr>';
			
			foreach( $row as $col )
			{
				$p .= '<td width="">'.$col.'</td>';
			}
			
			$p .= '</tr>';
			
		}
		
		$p .= '</table><div class="submitDiv"><input class="submit but grey" type="submit" name="database" value="Save"></div></div></form>';
		
		cp::cont()->output = $p;
		
		//-----------------------------------
		// Initial queries
		//-----------------------------------
		
		$init = cp::db()->fetch( array(
			'table'		=> '`schema`',
			'where'		=> '`1` LIKE "init_%"',
			'key'		=> '1',
		) );
		
		$p = '<form method="post"><div id="first_q" class="schema_table"><pre>';
		
		$drops_app = '<select style="width: 200px" name="init_app">';
		
		foreach( $init as $key => $init_row )
		{
			//echo $init_row['array'] ."\n\n";
			
			$uns = json_decode( $init_row['array'], true );
			$p .= htmlentities( "<b>" .$key ."</b>\n" . print_r( $uns, true ) );			
						
			$drops_app .= '<option value="'.$key.'">'.$key.'</option>';					
		}
		
		$drops_app .= '</select>';		
		$drops_table= '<select style="width: 200px" name="init_table">'.$drops_table_rows.'</select>';		
		$drops_id 	= '<input type="text" name="init_id" value="" style="margin-left: 2px; margin-right: 2px; padding: 9px;"></input>';
		$drops_key 	= '<input type="text" name="init_key" value="key" style="margin-left: 2px; margin-right: 2px; padding: 9px;"></input>';
		$drops_v 	= '<input type="text" name="init_v" value="value" style="margin-left: 2px; margin-right: 2px; padding: 9px;"></input>';
		
		$p .= '</pre>'. $drops_app . $drops_table . $drops_id . $drops_key . $drops_v .'<div class="submitDiv"><input class="submit but grey" type="submit" name="init" value="Add"></div></div></form>';
		
		cp::cont()->output .= $p;	
		
	}
	
}

?>
