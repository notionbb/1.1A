<?php

	/*===================================================
	//	NotionBB © All Rights Reserved
	//---------------------------------------------------
	//	NBB-Core
	//		by notionbb.com
	//---------------------------------------------------
	//	File Updated - 2-11-2020
	//=================================================*/

class object_table {
	
	/**
	** Rows for table
	** @var		array
	*/
	public $rows;
	
	/**
	** Columns to include in table
	** @var		array
	*/
	public $cols	= false;
	
	/**
	** Current lang pack we're using
	** @var		object
	*/
	public $lang;
	
	/**
	** How to parse each column
	** @var		array
	*/
	public $parse;
	
	/**
	** Extra display class to add
	** @var		array
	*/
	public $classes;
	
	/**
	** Option buttons to display
	** @var		array
	*/
	public $options;
	
	/**
	** Page to use as options link, defaults to cp::GET['page']
	** @var		string
	*/
	public $page;
	
	/**
	** Table Class
	** @var		string
	*/
	public $table_class = 'cptable';
	
	/**
	** Output table
	** 
	** @return	string
	*/
	public function make()
	{
		
		//-----------------------------------
		// Init
		//-----------------------------------
		
		$this->lang = ( $this->lang ) ?: cp::call('lang')->load('all');
		
		//-----------------------------------
		// Print Rows
		//-----------------------------------
		
		foreach( $this->rows as $row )
		{
			
			$table .= '<tr>';
			
			foreach( $this->cols as $col )
			{
				
				$class = '';
				
				/* Parse as a function */
				if ( is_callable( $this->parse[ $col ] ) )
				{
					$cell = $this->parse[ $col ]( $row );
				}
				else
				
				/* Parse as a tick */
				if ( $this->parse[ $col ] == 'tick' )
				{
					$cell = '<img src="'.cp::display()->vars['lprefix'].'style/'.cp::display()->vars['styleFolder'].'/images/'. (( $row[ $col ] ) ? 'tick': 'cross' ) . '.png" />';
					$center[ $col ] = true;
				}				
				else
				
				/* Parse as an image */
				if ( $this->parse[ $col ] == 'img' )
				{	
					$cell = '<img src="' . $row[ $col ] . '" />';
					$center[ $col ] = true;					
				}
				
				else
				
				{					
					$cell = $row[ $col ];
				}
				
				if ( $center[ $col ] )
				{
					$this->classes[ $col ] = $this->classes[ $col ] . ' cent';
				}
				
				$table .= '<td class="'.$this->classes[ $col ].'">' . $cell . '</td>';
				
			}
			
			if ( $this->options )
			{
				$table .= '<td class="cent">' . $this->options( $row ) . '</td>';
			}
			
			$table .= '</tr>';
						
		}
		
		//-----------------------------------
		// Print column titles
		//-----------------------------------
		
		foreach( $this->cols as $key => $col )
		{			
			$col_title = ( is_int( $key ) ) ? $this->lang->get( $col ): $this->lang->get( $key );
				
			$columns .= '<td class="tdTit ' . $this->classes[ $col ] . '">'.$col_title.'</td>';			
		} 
		
		if ( $this->options )
		{
			$columns .= '<td  class="tdTit cent">' . $this->lang->get('opt') . '</td>';
		}
		
		return '<div class="'.$this->table_class.'"><table>' . $columns . $table . '</table></div>';
		
	}
	
	/**
	** Options Buttons
	** 
	** @param	array	$row	Row array
	*/
	public function options( $row )
	{
		
		$this->page = ( $this->page ) ?: cp::$GET['page'];
		
		/* Parse column ids into urls */
		foreach( $this->options as $key => $col )
		{
			$this->parsed_options[ $key ] = '?page='.$this->page.'&'.$key.'='.$row[ $col ];
		}
		
		//-----------------------------------
		// Print buttons
		//-----------------------------------
		
		if ( $this->parsed_options['edit'] )
		{			
			$ret .= '<a href="'.$this->parsed_options['edit'].'"><img title="Edit" class="imghigh" src="'.cp::display()->vars['lprefix'].'style/'.cp::display()->vars['styleFolder'].'/images/pencil.png" /></a>';	
		}
		
		if ( $this->parsed_options['switch'] )
		{			
			$ret .= '<a href="'.$this->parsed_options['switch'].'"><img title="Switch" class="imghigh" src="'.cp::display()->vars['lprefix'].'style/'.cp::display()->vars['styleFolder'].'/images/switch.png" /></a>';			
		}
		
		if ( $this->parsed_options['toggle'] )
		{			
			$ret .= '<a href="'.$this->parsed_options['toggle'].'"><img title="Toggle" class="imghigh" src="'.cp::display()->vars['lprefix'].'style/'.cp::display()->vars['styleFolder'].'/images/switch.png" /></a>';			
		}
		
		if ( $this->parsed_options['del'] )
		{			
			$ret .= '<a href="'.$this->parsed_options['del'].'"><img title="Delete" class="imghigh" src="'.cp::display()->vars['lprefix'].'style/'.cp::display()->vars['styleFolder'].'/images/delete.png" /></a>';			
		}
		
		if ( $this->parsed_options['do'] )
		{			
			$ret .= '<a href="'.$this->parsed_options['do'].'"><img title="Do" class="imghigh" src="'.cp::display()->vars['lprefix'].'style/'.cp::display()->vars['styleFolder'].'/images/do.png" /></a>';			
		}
		
		if ( $this->parsed_options['default'] )
		{			
			$ret .= '<a href="'.$this->parsed_options['default'].'"><img title="Set Default" class="imghigh" src="'.cp::display()->vars['lprefix'].'style/'.cp::display()->vars['styleFolder'].'/images/default.png" /></a>';			
		}
		
		return $ret;
		
	}
	
}
	
?>
