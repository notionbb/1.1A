<?php

	//===================================================
	//	Cipher Pixel Board © All Rights Reserved
	//---------------------------------------------------
	//	CP.Board
	//		by michaelbenner.net
	//---------------------------------------------------
	//	File created: April 04, 2013 
	//===================================================

class admin_dtools extends display {
	
	public $cautionHtml;		// Read Templates to display caution messages
	
	/**
	** rowsToTable() - makes table from rows
	** 
	** DEPRECIATED: use classes/object_table
	**
	** @rows		= fetch() array of rows
	** @cols		= array(key, eng) columns to show in table
	** @lang		= lang pack
	** @class		= table class
	** @titleRow	= whether to display the table title
	*/
	public function rowsToTable( $rows, $cols, $lang, $class='cptable', $titleRow=true )
	{

		Debug::mess('rowsToTable() used - has depreciated');

		/**
		** Get column titles
		*/
		foreach( $cols as $k => $v )
		{
			$colKeyArray[ $k ] = $v;
			
			if ( !is_array( $v ) ){
				$table .= '<td class="tdTit">' . cp::lang( $lang, $v ) . '</td>';
			}else{
				$table .= '<td width="'.$v['width'].'px" class="tdTit '.(($v['class'] )? ' '.$v['class']: '').'">' . cp::lang( $lang, $v['lang_key'] ) . '</td>';
			}
		}
		
		$table = ( $titleRow ) ? '<tr>'.$table.'</tr>': null;			
		
		/**
		** Foreach row
		*/
		foreach( $rows as $id => $array )
		{			
			$table .= '<tr>';			
			foreach ( $colKeyArray as $rowKey => $colArray )
			{	
				
				/* Do we want a simple lang display or something more complicated? */
				if ( !is_array( $colArray ) )
					$table .= '<td>'. $array[ $rowKey ] . '</td>';
				else
				
				/* Normal Row */
				if ( $colArray['type'] == 'norm' )
				{
					
					$table .= '<td'.(($class)? ' class='.$colArray['class']: '').'>'. $array[ $rowKey ] . '</td>';
					
				}
				
				else
				
				/* URL Link */
				if ( $colArray['type'] == 'url' )
				{				
					$table .= '<td'.(($class)? ' class='.$colArray['class']: '').'><a href="'. $array[ $rowKey ] . '">'. $array[ $colArray['eng_key'] ] . '</a></td>';					
				}
				
				else
				
				/* On off tick */
				if ( $colArray['type'] == 'tick' )
				{
					
					$table .= '<td'.(($class)? ' class='.$colArray['class']: '').'><img src="'.cp::display()->vars['lprefix'].'style/'.cp::display()->vars['styleFolder'].'/images/'. (( $array[ $rowKey ] ) ? 'tick': 'cross' ) . '.png" /></td>';
					
				}
				
				else
				
				/* Array to value */
				if ( $colArray['type'] == 'arrayToValue' )
				{
					
					//print_r(cp::db()->get( 'groups', '2' ));
					$b = null;

					if( is_array( $array[ $colArray['keyForArr'] ] ) )
					{				
						foreach ( $array[ $colArray['keyForArr'] ] as $k => $groupId )
						{
							if ( !$groupId ) continue;
							if ( $b ) $b .= ', ';
							$gA = cp::db()->get( $colArray['table'], $groupId );
							$b .= $gA[ $colArray['dispCol'] ];
						}
					}
						
					$table .= '<td'.(($class)? ' class='.$colArray['class']: '').'>'. $b . '</td>';
					
				}
				
				else
				
				/* OptionLinks */
				if ( $colArray['type'] == 'opt' )
				{
					$temp = array();					
					foreach( $colArray['items'] as $do => $doKey )
					{						
						$temp[ $do ] = '?page='.cp::$GET['page'].'&'.$do.'='.$array[ $doKey ];						
					}					
					$table .= '<td'.(($class)? ' class='.$colArray['class']: '').'>' . $this->options( $temp ) . '</td>';
					
				}
				
				else
				
				/* Pip */
				if ( $colArray['type'] == 'pip' )
				{
					
					$c = 0;
					$b = null;
					
					if ( is_numeric( $array[ $rowKey ] ) )
					{
						
						while( $c != $array[ $rowKey ] )
						{
							$b .= '<img src="'.cp::display()->vars['lprefix'].'style/'.cp::display()->vars['styleFolder'].'/images/'.cp::set('pipImg').'" />';
							$c++;
						}					
					}
					else
					{
						$b = '<img src="'.cp::display()->vars['lprefix'].'style/'.cp::display()->vars['styleFolder'].'/images/'.$array[ $rowKey ].'" />';
					}
					
					$table .= '<td'.(($class)? ' class='.$colArray['class']: '').'>' . $b . '</td>';
					
				}
				
				else
				
				/* Image link */
				if ( $colArray['type'] == 'img' )
				{		
					$img = '<img src="' . $array[ $rowKey ] . '" />';
					$table .= '<td'.(($class)? ' width="'.$colArray['width'].'px" class='.$colArray['class']: '').'>' . $img . '</td>';					
				}
				
				else
				
				/* Icon */
				if ( $colArray['type'] == 'icon' )
				{		
					$img = '<img src="' . cp::display()->vars['lprefix'] . $array[ $rowKey ] . '" />';
					$table .= '<td'.(($class)? ' width="'.$colArray['width'].'px" class='.$colArray['class']: '').'>' . $img . '</td>';					
				}
				
				else
				
				/* Date! */
				if ( $colArray['type'] == 'date' )
				{				
					$table .= '<td'.(($class)? ' width="'.$colArray['width'].'px" class='.$colArray['class']: '').'>' . cp::display()->time2str( $array[ $rowKey ] ) . '</td>';						
				}
				
				else
				
				/* Global Category Title */
				if ( $colArray['type'] == 'globalcat' )
				{				
					$table .= '<td'.(($class)? ' class='.$colArray['class']: '').'><a href="?page=settings&edit='.$array['text_id'].'">' . $array['name'] . '</a><br />' . $array['desc'] . '</td>';					
				}
							
			}			
			$table .= '</tr>';			
		}
		
		$table = '<div class='.$class.'><table>' . $table . '</table></div>';
		
		return $table;
		
	}
	
	/**
	** processSettings() - saves settings
	**
	** @settings	= setting array as per settingToFields() (below)
	** @values		= current values - needed to process slug
	** @slugTable	= table to slug from
	** @slugFrom	= column to slug from (e.g. 'name');
	*/
	public function processSettings( $settings, $values=false, $slugTable=false, $slugFrom=false )
	{
		
		$new 	= false;
		$perm 	= false;
		
		$new = cp::$POST;		
		unset( $new['submit'] );
		unset( $new['update'] );

		/**
		** Foreach submitted setting
		*/
		foreach( $settings as $k => $setInfo )
		{
			
			$name 	= ( $setInfo['name'] ) ?: $setInfo['id'];
			$value 	= $new[ $name ];
			
			/**
			** Setting Info
			*/
			//$setInfo = $settings[ $name ];

			/**
			** Html Entities?
			*/
			if ( $setInfo['valueType'] == 'field_entity' OR $setInfo['valueType'] == 'area_entity' )
			{
				$onlyconsonants = str_replace($vowels, "", "Hello World of PHP");
				$new[ $name ] = addslashes( str_replace( '\'', '"', html_entity_decode( $_POST[ $name ], FALSE ) ) );
			}
			
			else

			/**
			** Date?
			*/
			if ( $setInfo['valueType'] == 'date' )
			{
				$new[ $name ] = strtotime($value);
			}
			
			else
			
			/**
			** Permissions...
			*/
			if ( $setInfo['valueType'] == 'permtable' )
			{
				
				$perm = array(
					'p_read' 	=> false,
					'p_start' 	=> false,
					'p_post'	=> false,
					'p_upload'	=> false,
					'p_download'=> false,
				);
				
				foreach( $new as $name => $value )
				{
					if ( substr( $name, 0, 2 ) != 'p_' )
						continue;
					else
					{				
						# Explode!
						# 1=permtype, 2=id
						$exp = explode( '_', $name );
						
						# Append comma if required and add to permissions
						$perm[ 'p_' . $exp['1'] ] .= ( $perm[ 'p_' . $exp['1'] ] ) ? $exp['2'].',': ',' . $exp['2'] . ',';
						
						# Remove from new array
						unset( $new[ $name ] );
								
					}
				}
				
			}
			
			else
			
			/**
			** 
			*/
			if ( $setInfo['valueType'] == 'area_array' )
			{

				$list = str_replace( "\r\n", "\n", $value );
				$list = explode( "\n", $list );
				
				$rows = array();
				
				foreach( $list as $k => $v )
				{
					if ( !$v ) continue;
					$exp = explode( " | ", $v );
					$rows[ $exp['0'] ] = $exp['1'];
				}
				
				if ( count($rows) > 0 )
				{				
					$new[ $name ] = serialize($rows);
				}

			}
			
			else
			
			/**
			** Slug
			*/
			
			if ( $setInfo['name'] == 'slug' )
			{
				
				/* Create a new slug if one hasn't been submitted */
		    	if ( !$new['slug'] )
				    $new['slug'] = cp::db()->slugify( $new[ $slugFrom ], $slugTable );
			    else
			    
			    /* If slug has changed we have to check its unique */
			    if ( $values['slug'] != $new['slug'] )
			    {    
			   		if ( cp::db()->checkslug( $new['slug'], $slugTable ) )
			   		{
				   		$new['slug'] = cp::db()->slugify( $new['slug'], $slugTable );
			   		}		   		
		   		}
				
			}
			
			else
			
			{
				
				$new[ $name ] = cp::$POST[ $name ];
					
			}
			
		}
   		
   		/**
   		** Password...
   		*/
   		if ( $settings['pass_ignore'] )
   		{	   		
	   		if ( $new['pass_ignore'] )
	   		{
		   		$hash = cp::call('members')->hash( $new['pass_ignore'] );
		   		$sec['pass'] = $hash['pass'];
		   		$sec['string'] = $hash['key'];
	   		}	   		
	   		unset($new['pass_ignore']);   		
   		}
		
		/**
		** Return
		*/		
		$array['new'] = $new;
		
		if ( $perm )
			$array['perm'] = $perm;
		
		if ( $sec )
			$array['sec'] = $sec;	
			
		return $array;
		
	}

	/**
	** settingToFields() - gets a settings array and turns it into html
	**
	** @settings 	= array of settings array(
	** 		'name'			= name of setting
	** 		'title' 		= title
	** 		'desc'			= setting
	** 		'valueType'		= see below fields
	** 		'valueFunction 	= function to run if field needs lots of stuff
	** 		'funcVar'		= var to send to function
	** 		'valueOptions' 	= serialized (or not) array of values array( eng => val )
	** 		'valueDef'		= value default
	** 		'valueExc'		= any values that need excluding from drop down boxes
	** @values		= value array or false for ['valueDef']
	** @arrayKey	= value of $settings to name each form field
	** @retAsArray	= return as array (true) or html (false)
	*/
	public function settingToFields( $settings, $values=false, $arrayKey='name', $retAsArray=false )
	{

		foreach ( $settings as $k => $array )
		{

			/**
			** Default value?
			*/
			$value = ( $values[ $array['name'] ] ) ? $values[ $array['name'] ]: ( ( $array['value'] ) ?: $array['valueDef'] );
			$key = ( $array[ $arrayKey ] ) ?: $array['id'];
			
			$html = '';
			
			/**
			** Types without any extra information...
			*/
			# Simple Field
			if ( $array['valueType'] == 'field' )
			{
				$html = '<input type="text" class="field" value="'.$value.'" name="'.$key.'">';
			}
			else
			#Blankfield (used for passwords)
			if ( $array['valueType'] == 'blankfield' )
			{
				$html = '<input type="text" class="field" value="" name="'.$key.'">';
			}
			else
			#Converts string to date and vice versa
			if ( $array['valueType'] == 'date' )
			{
				$value = date( $array['valueOptions'], $value );
				$html = '<input type="text" class="field" value="'.$value.'" name="'.$key.'">';
			}
			#Turns html into entity
			if ( $array['valueType'] == 'field_entity' )
			{
				$html = '<input type="text" class="field" value="'.htmlentities($value).'" name="'.$key.'">';
			}
			else
			
			#Textarea
			if ( $array['valueType'] == 'area' OR $array['valueType'] == 'area_array' )
			{
				$html = '<textarea class="area" name="'.$key.'">'.$value.'</textarea>';
			}
			
			else
			
			#Textarea (html)
			if ( $array['valueType'] == 'area_entity' )
			{
				$html = '<textarea class="area" name="'.$key.'">'.htmlentities($value).'</textarea>';
			}
			
			else
			
			#Large textarea
			if ( $array['valueType'] == 'large_area' )
			{
				$html = '<textarea class="large_area tabable" name="'.$key.'">'.$value.'</textarea>';
			}
			else
			
			#Text editor
			if ( $array['valueType'] == 'editor' )
			{
				/* Load Editor */
				cp::display()->jsLoad('editor')->jsLoad('minified/jquery.sceditor.bbcode.min');	
				
				$html = '<div class="div-editor-margin"><textarea name="'.$key.'" class="editor" id="editor_new">'.$value.'</textarea></div>';
			}
			
			#On/off button
			if ( $array['valueType'] == 'onoff' )
			{
				$c1 = ( $value == 1 ) ? ' checked': false;
				$c2 = ( $c1 ) ? false: ' checked';			
				$html = '
					<div class="onoff">
						<ul>
						<li class="sel_yes"><input class="radio" type="radio" name="'.$key.'" value="1"'.$c1.'>Yes</li>
						<li class="sel_no"><input class="radio" type="radio" name="'.$key.'" value="0"'.$c2.'>No</li>
						</ul>
					</div>
				';
			}			
			
			else
			
			#Perm Table
			if ( $array['valueType'] == 'permtable' )
			{
				$html = $this->permtable($values, false, $array['funcVar']);
			}
			
			else
			
			/**
			** Fields that do need more information
			*/
			
			{
			
				/**
				** Choice?
				** array( English => Value )
				*/
				if ( $array['valueFunction'] )
				{
					/**
					** Call if exists...
					*/

					$selArray = cp::methodCall( cp::call('formfunc', 'apps/admin/classes/formfunc' ), $array['valueFunction'], $array['funcVar'] );
					
					if ( !$selArray ) Debug::master('none', 'Cannot find form function');
						
				}
				else
				if ( $array['valueOptions'] )
				{ 
					$selArray = ( is_array( $array['valueOptions'] ) ) ? $array['valueOptions']: unserialize( stripslashes($array['valueOptions']) );
				}

				#Drop Box
				if ( $array['valueType'] == 'drop' )
				{

					$html = '<select class="drop" name="'.$key.'">';
					
					if ( $array['valueDef'] == '0' )
					{
						$html .= '<option value="" selected="selected"></option>';
					}
							
					foreach( $selArray as $k => $v ) {
						if ( $v == $array['valueExc'] ) continue;						
						if ( $v == $value ) {
							$html .= '<option value="'.$v.'" selected="selected">'.$k.' (current)</option>';
						}else{
							$html .= '<option value="'.$v.'">'.$k.'</option>';
						}						
					}
					
					$html .= '</select>';
				}
				else
				#List
				if ( $array['valueType'] == 'list' )
				{
					
					$cur = ( is_array( $value ) ) ? $value: unserialize( $value );
				
					$html = '<select name="'.$key.'[]" multiple>';			
					foreach( $selArray as $k => $v ) {						
						if ( is_array( $cur ) AND in_array( $v, $cur ) ) {
							$html .= '<option value="'.$v.'" selected="selected">'.$k.' (current)</option>';
						}else{
							$html .= '<option value="'.$v.'">'.$k.'</option>';
						}						
					}
					
					$html .= '</select>';
					
				}
				
			}
			
			/**
			** Return
			*/
			$this->formRow = array(
				'name'	=> $array['name'],
				'title'	=> $array['title'],
				'desc'	=> $array['desc'],
				'field' => $html,
				'type'	=> $array['valueType'],
				'subcat'=> $array['subcat'],
				'aK'	=> $array['arrayTitle'],
			);
			
			$array['tab'] = ( $array['tab'] ) ?: 'no_tab';
			
			if ( $retAsArray )
				$ret[ $array['tab'] ][] = $this->formRow;
			else
				$ret[ $array['tab'] ] .= cp::display()->read('setting_row');
			
		}
		
		return $ret;		
		
	}
	
	/**
	** permtable() - creates a permission table
	**
	** @cur		= current perm table values
	** @rows	= y-axis, usually permission masks
	** @vals	= x-axis (defaults to read, start, post)
	*/
	public function permtable( $cur, $rows=false, $vals='' )
	{
		
		/**
		** Do we need to fetch rows?
		*/
		if ( !$rows )
			$rows = cp::db()->get( 'perm' );
			
		/**
		** Def Values?
		*/
		if ( !is_array( $vals ) )
			$vals = array( 'read', 'start', 'post' );
			
		cp::display()->jsLoad('permclick');
			
		/**
		** Colors...
		*/
		$colours = array('blu', 'gre', 'red', 'yel', 'ora');
		
		$build = '<table class="perm permsmall">';
		
		/**
		** Top Buttons
		*/
		foreach ( $vals as $name )
    	{
	    	$topRows .= '<td class="buts"><div class="butPlus" do="'.$name.'">+</div><div class="butMinus" do="'.$name.'">-</div></td>';
    	}
    	
    	$build .= '
    		<tr class="tits">
				<td class="catcell notop">'.cp::lang('board', 'perm_sets').'</td>
				'.$topRows.'
			</tr>
		';
				
		/**
		** Foreach Row...
		*/
		foreach ( $rows as $k => $row_array )
		{
			
			$build .= '<tr>';
			
			$build .= '<td class="forcell">
							'.$row_array['name'].'
						</td>
					';
			
			$i = 0;
			foreach( $vals as $perm_key )
			{
				
				$check = ( cp::call('perm')->check( $cur, $perm_key, $row_array['id'] ) ) ? ' checked': '';
				
				$key 	= 'p_'.$perm_key;				
				$build .= '<td class="' . $colours[$i] . '_cell toggleInput" toggle="p_'.$perm_key.'_'.$row_array['id'].'">
								' . cp::lang('board', $perm_key) . '<br />
								<input type="checkbox" do="'.$perm_key.'" name="p_'.$perm_key.'_'.$row_array['id'].'" value="yes"'.$check.'>
							</td>';
				$i++;		
				
					
			}
			
			$build .= '</tr>';
			
		}
		
		$build .= '</table>';
		
		return $build;
		
	}
	
   /**
    * adds buttons to top right of page
    */
    public function page_but( $lang, $url )
    {
	    
	    cp::display()->vars['url']	= $url;
	    cp::display()->vars['lang']	= $lang;
	    
	    cp::display()->vars['page_buts'] .= cp::display()->read('norm_but');
	    
    }    
	
	/**
	** formNorm() - basic form in cptable
	**
	**
	*/
	public function formSmall( $array )
	{
		
		foreach ( $array['fieldA'] as $tab => $html )
		{
			
			cp::display()->vars['form']['fields'] .= $html;
			
		}
		
	}
	
	/**
	** formTab() - form with top tabs
	**
	**
	*/
	public function formTab( $array )
	{
		
		$tabNum = "0";

		/**
		** Foreach Tab
		*/
		foreach ( $array['fieldA'] as $tab => $html )
		{
			
			$rows = array();
			$tabHtml = null;

			/**
			** Sort into subcat
			*/
			foreach( $html as $k => $formRow )
			{
				$rows[ $formRow['subcat'] ][] = $formRow;
			}
			
			/**
			** Foreach Subcat
			*/
			foreach( $rows as $subcat => $formRow )
			{
				
				/**
				** Foreach row
				*/
				foreach( $formRow as $k => $formRowArray )
				{
					
					if ( $formRowArray['subcat'] != $lastCat )
					{
						$this->newcat 	= cp::lang('all', 'sub_' . $formRowArray['subcat'] );
						$lastCat 		= $formRowArray['subcat'];
					}
					
					$this->formRow = $formRowArray;
					
					$tabHtml .= cp::display()->read('setting_row');
					
					$this->newcat 	= false;
					
				}
				
			}
			
			/**
			** Tabs
			*/
			cp::display()->vars['tabNum']		= $tabNum;
			cp::display()->vars['tab'] 	 	= $tab;
			cp::display()->vars['tab_lang'] 	= cp::lang('all', $tab );			
			cp::display()->vars['form']['tabs'] .= cp::display()->read('form_tab_tabs');
			
			/**
			** Create Div with form HTML
			*/
			cp::display()->vars['div'] = $tabHtml;
			
			/**
			** Add new html to form
			*/
			cp::display()->vars['form']['fields'] .= cp::display()->read('form_tab_div');
			
			$tabNum++;
			
		}
		
		if ( $tabNum > 1 )
			cp::display()->vars['form']['multipleTabs'] = true;
		
	}
	
	/**
	** tabs()
	**
	**
	*/
	public function tabs( $page, $cols )
	{
		
		$tabNum = 0;
		
		foreach( $page as $tab => $sub )
		{
			
			/**
			** form_tab_tabs
			*/
			cp::display()->vars['tabNum']		= $tabNum;
			cp::display()->vars['tab'] 	 	= $sub['subcat'];
			cp::display()->vars['tab_lang'] 	= cp::lang('all', 'set_' . $sub['subcat'] );
			
			/**
			** form_tab_div
			*/
			cp::display()->vars['div'] 		= $this->rowsToTable( $sub['rows'], $cols, 'all', 'glotable', false );
			
			/**
			** tab_table
			*/		
			cp::display()->vars['form']['tabs']  	   .= cp::display()->read('form_tab_tabs');
			cp::display()->vars['form']['fields']	   .= cp::display()->read('form_tab_div');
			
			$tabNum++;
			
		}
		
		if ( $tabNum > 1 )
			cp::display()->vars['form']['multipleTabs'] = true;
		
		return cp::display()->read('tab_table');
		
	}
	
	/**
	** 
	**
	** Option Buttons
	*/
	public function options( $array )
	{
		
		if ( $array['edit'] )
		{			
			$ret .= '<a href="'.$array['edit'].'"><img title="Edit" class="imghigh" src="'.cp::display()->vars['lprefix'].'style/'.cp::display()->vars['styleFolder'].'/images/pencil.png" /></a>';	
		}
		
		if ( $array['switch'] )
		{			
			$ret .= '<a href="'.$array['switch'].'"><img title="Switch" class="imghigh" src="'.cp::display()->vars['lprefix'].'style/'.cp::display()->vars['styleFolder'].'/images/switch.png" /></a>';			
		}
		
		if ( $array['toggle'] )
		{			
			$ret .= '<a href="'.$array['toggle'].'"><img title="Toggle" class="imghigh" src="'.cp::display()->vars['lprefix'].'style/'.cp::display()->vars['styleFolder'].'/images/switch.png" /></a>';			
		}
		
		if ( $array['del'] )
		{			
			$ret .= '<a href="'.$array['del'].'"><img title="Delete" class="imghigh" src="'.cp::display()->vars['lprefix'].'style/'.cp::display()->vars['styleFolder'].'/images/delete.png" /></a>';			
		}
		
		if ( $array['do'] )
		{			
			$ret .= '<a href="'.$array['do'].'"><img title="Do" class="imghigh" src="'.cp::display()->vars['lprefix'].'style/'.cp::display()->vars['styleFolder'].'/images/do.png" /></a>';			
		}
		
		if ( $array['default'] )
		{			
			$ret .= '<a href="'.$array['default'].'"><img title="Set Default" class="imghigh" src="'.cp::display()->vars['lprefix'].'style/'.cp::display()->vars['styleFolder'].'/images/default.png" /></a>';			
		}
		
		return $ret;
		
	}
	
	/**
	** caution() - adds a caution message to var
	**
	** @msg	= message to be displayed.
	*/
	public function caution( $msg )
	{
		cp::display()->vars['cautionMsg'] = $msg;
		$this->cautionHtml .= cp::display()->read('caution');
	}
	
}

?>