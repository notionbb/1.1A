<?php

	/*===================================================
	//	CipherPixel © All Rights Reserved
	//---------------------------------------------------
	//	CP-Core
	//		by cipherpixel.net
	//---------------------------------------------------
	//	File created: September 09, 2014 
	//=================================================*/

class admin_forums extends controller {
	
	/**
	** Shortcut to class_forums
	*/
	public	$class_forums;
	
	/**
	** Settings and Row
	*/
	public	$settings;
	public	$row;
	public	$type 	= 'forum';
	
	public function main()
	{
		
		/**
		** Assistant
		*/
		$this->class_forums = cp::call( 'class_forums', 'apps/forum/classes/class_forums' );
		
		/**
		** Controller
		*/
		if ( !cp::cont()->adminDo( get_class($this) ) )
			return false;
		
		# Disp Vars
		cp::cont()->navtree( cp::lang('board', 'forums'), '?f=forum&page=forums', true );	
			
		return true;
		
	}
	
	/**
	** showAll() - Show board!
	*/
	public function showAll()
	{
		
		if ( cp::$POST['reorder'] )
		{
			$this->reorder();
		}
		
		# Display Vars
		cp::cont()->page['title'] = cp::lang('board', 'man_board');
		
		# Get All Forums
		$c = $this->class_forums->retrieveAll();
		
		if ( !$c )
		{
			cp::call('dtools')->caution( cp::lang('all', 'no_f_c') );
		}
		else
		{		
		
			# Print Forums
			$this->class_forums->loopForums( '0', $this, 'forum_showAll_callback' );
			
			# Print Cat Table...
			cp::display()->vars['catHtml'] = $this->build_html['0'];
			cp::cont()->output .= cp::display()->form( array(
				'name'		=> 'reorder',
				'fields'	=> cp::display()->read('cat_table'),
				'submit'	=> cp::lang('board', 'reorder'),
				'submitC'	=> 'butbelow grey',
				'submitDivC'=> 'right',
				),
				'basic'
			);
			
		}
		
		/* Shortcuts */
		cp::call('dtools')->page_but( cp::lang('board', 'cre_for'), '?f=forum&page=forums&edit=new' );
		cp::call('dtools')->page_but( cp::lang('board', 'cre_cat'), '?f=forum&page=forums&edit=newcat' );

	}
	
	/**
	** forum_callback() - Show all forum callback
	** 
	** @forum	= current forum array
	*/	
	public function forum_showAll_callback( $forum )
	{
		
		if ( $forum['parent'] == '0' )
		{
			
			$tpl = 'cat';
			
		}
		else
		{
			
			$tpl = 'forum';
			
		}
		
		# How many in this level?
		$maxOrder = count( $this->class_forums->forum_map[ $forum['parent'] ] );
		
		# Create Dropbox
		$order = '<select class="small" name="order_'.$forum['id'].'">';
		$i = 0;
		while( $i != $maxOrder )
		{
			$i++;
			$sel = ( $forum['order'] == $i ) ? ' selected': '';
			$order .= '<option value="'.$i.'" '.$sel.'>'.$i.'</option>';
		}
		$order .= '</select>';
		
		$forum['orderForm'] = $order;
		
		# Option Links
		$forum['optHtml'] = cp::call('dtools')->options( array( 
			'edit' 	=> '?f=forum&page=forums&edit='.$forum['id'],
			'del' 	=> '?f=forum&page=forums&del='.$forum['id'],
		) );
		
		# Add Subs
		$forum['subHtml'] = $this->build_html[ $forum['id'] ];
		
		# Save Forum For Display
		cp::display()->vars['forum'] = $forum;
		
		# Add to forum html array
		$this->build_html[ $forum['parent'] ] .= cp::display()->read('cat_' . $tpl );	
		
	}
	
	/**
	** reorder
	*/
	public function reorder()
	{
		$order = cp::$POST;
		unset( cp::$POST['order'] );
		
		foreach ( $order as $k => $v )
		{
			
			$chk 	= substr( $k, 0, 6 );
			$id 	= substr( $k, 6 );
			
			if ( $chk != 'order_' ) continue;
			
			cp::db()->update_dep( 'forums', $id, array('order'=>$v) );
			
		}
		
	}
	
	/**
	** getSettings() - gets settings
	*/
	public function getSettings()
	{
		if ( $this->type == 'cat' )
		{
			$this->settings = $this->catTable();
		}
		else
		{
			$this->settings = $this->table();
		}
		
		return $this;
	}
	
	/**
	** getRow() - gets rows and settings
	*/
	public function getRow( $rowId )
	{		
				
		$this->row = cp::db()->fetch_dep( array(
			'select'=> 'forums.*, perm_reg.*',
			'table'	=> 'forums',
			'where' => 'id="'.$rowId.'"',
			'join'	=> array(
				'table'	=> 'perm_reg',
				'where'	=> 'perm_reg.type="forum" AND forums.id=perm_reg.type_id',
				'type'	=> 'left',
			),
			'one'	=> true
		) );
		
		if ( ( $this->row ) AND ( $this->row['parent'] == '0' OR !$this->row['parent'] ) )
		{
			$this->type = 'cat';
		}
		
		return $this;	
	}
	
	/**
	** showForum
	*/
	public function showForm()
	{
		
		# New?		
		if ( cp::$GET['edit'] == 'new' )
		{	
			
			# Set
			$this->getSettings();
			
			# Disp
			cp::cont()->page['title'] = cp::lang('board', 'new_forum');
			cp::cont()->navtree( cp::lang('all', 'new'), '' );
					
		}
		else
		if ( cp::$GET['edit'] == 'newcat' )
		{	
			
			$this->type = 'cat';
			
			# Set
			$this->getSettings();
			
			# Disp
			cp::cont()->page['title'] = cp::lang('board', 'new_cat');
			cp::cont()->navtree( cp::lang('all', 'new'), '' );
					
		}
		else
		{			
			/**
			** Gets current and setting
			*/
			$this->getRow( cp::$GET['edit'] )->getSettings();
			
			cp::cont()->page['title'] = cp::lang( 'board', 'edit_' . $this->type ).': '.$this->row['name'];
			cp::cont()->navtree( cp::lang('all', 'edit'), '' );
					
		}
		
		cp::cont()->output .= cp::display()->form( array(
			'name'		=> 'update',
			'submit'	=> cp::lang('all', ( ( cp::$GET['edit'] == 'new' ) ? 'create_group': 'edit_group' ) ),
			'fieldA'	=> cp::call('dtools')->settingToFields( $this->settings, $this->row, 'name', true ),
			),
			'tab'
		);		
		
	}
	
	/**
	** processForm
	*/
	public function processForm()
	{
		
		if ( cp::$GET['edit'] == 'new' )
			$newRow = true;
		else
		if ( cp::$GET['edit'] == 'newcat' )
		{
			$newRow = true;
			$this->type = 'cat';
		}			
		else
			$this->getRow(cp::$GET['edit']);
			
		$this->getSettings();
		
		/**
		** Make settings database form
		*/
		$new = cp::call('dtools')->processSettings( $this->settings, $this->row, 'forums', 'name' );
		
		/**
		** Update DB
		*/
		if ( $newRow )
		{
			
			/**
			** Find order...
			*/
			$order = cp::db()->fetch_dep( array(
				'table'	=> 'forums',
				'where'	=> 'parent="'.$new['new']['parent'].'"',
			) );
			
			$new['new']['order'] = count( $order ) + 1;
			
			/**
			** Insert Forum
			*/
			$fId	= cp::db()->insert( 'forums', $new['new'] );
			
			/**
			** Insert Perm_reg
			*/
			if ( $this->type == 'forum' )
				cp::db()->insert( 'perm_reg', array_merge( array('type'=>'forum','type_id'=>$fId), $new['perm'] ) );
					
			$lang	= cp::lang( 'board', 'c_' . $this->type ); 
		}
		else
		{
			
			/**
			** Update Forums
			*/
			cp::db()->update_dep( 'forums', cp::$GET['edit'], $new['new'] );

			/**
			** Update Perm Registar
			*/
			if ( $this->type == 'forum' )
				cp::db()->update_dep( 'perm_reg', array( 'type' => 'forum', 'type_id' => cp::$GET['edit'] ), $new['perm'] );
			
			$lang = cp::lang( 'board', 'up_' . $this->type ); 
		}
		
		/**
		** Send splash screen.
		*/
		cp::display()->splash( $lang, '?f=forum&page=forums');

		/**
		** Makes main() false, which makes splash skip
		*/
		return false;
		
	}
	
	/**
	** showDeleteForm()
	*/
	public function showDeleteForm()
	{
		
		$this->getRow( cp::$GET['del'] );
		
		cp::cont()->page['title'] = cp::lang('board', 'del_' . $this->type ).': '.$this->row['name'];
		cp::cont()->navtree( cp::lang('all', 'del'), '' );
		
		cp::call('dtools')->caution( cp::lang('board', 'del_caution') );
		
		/**
		** Move Subforums to?
		*/
		$settings = array(
			array(
				'name'			=> 'newParentId',
				'title' 		=> 'Move Forums',
				'desc'			=> 'Which category should any subforums be moved to?',
				'valueType'		=> 'drop',
				'valueFunction'	=> 'getBoard',
				'funcVar'		=> $this->row['id'],
			),
		);			
			
		cp::cont()->output .= cp::display()->form(
			array(
				'name'		=> 'delete',
				'submit'	=> 'Delete',
				'submitC'	=> 'red',
				'title'		=> 'Move Subforums',
				'fieldA'	=> cp::call('dtools')->settingToFields( $settings ),
			),
			'small'
		);
		
	}
	
	public function deleteRow()
	{
		
		/**
		** Get current row
		*/	
		$this->getRow( cp::$GET['del'] );
		
		/**
		** Update Members from group
		*/
		$newParent = cp::$POST['newParentId'];
		
		if ( $newParent )		
			//				Table		groupId =			set groupId = newGroup		groupId = rowId
			cp::db()->update_dep( 'forums', $this->row['id'], array( 'parent' => $newParent ), 'parent' );
		
		/**
		** Delete Group
		*/
		cp::db()->delete( 'forums', $this->row['id'] );	
		
		/**
		** Send splash screen.
		*/
		cp::display()->splash( cp::lang( 'board', 'deled_' . $this->type ), '?f=forum&page=forums');

		/**
		** Makes main() false, which makes splash skip
		*/
		return false;
		
	}
	
	/**
	** catTable() - settings array
	*/
	public function catTable()
	{
		
		return array(
			array(
				'name' 			=> 'name',
				'title' 		=> 'Title',
				'desc' 			=> 'Enter the category title',
				'valueType' 	=> 'field',
			),
			'slug'	=> array(
				'name' 			=> 'slug',
				'title' 		=> 'Slug',
				'desc' 			=> 'If left empty <i>(recommended)</i>, an automatic slug will be created; else you can enter it yourself',
				'valueType' 	=> 'field',
			),
		);		
		
	}
	
	/**
	** table() - settings array
	*/
	public function table()
	{
		
		return array(
			array(
				'name' 			=> 'name',
				'title' 		=> 'Title',
				'desc' 			=> 'Enter the forum title',
				'valueType' 	=> 'field',
				'tab'			=> 'foruminfo',
				'subcat'		=> 'basic',
			),
			array(
				'name' 			=> 'desc',
				'title' 		=> 'Description',
				'desc' 			=> 'Enter a brief forum description',
				'valueType' 	=> 'area',
				'tab'			=> 'foruminfo',
				'subcat'		=> 'basic',
			),
			array(
				'name' 			=> 'slug',
				'title' 		=> 'Slug',
				'desc' 			=> 'If left empty <i>(recommended)</i>, an automatic slug will be created; else you can enter it yourself',
				'valueType' 	=> 'field',
				'tab'			=> 'foruminfo',
				'subcat'		=> 'basic',
			),
			array(
				'name' 			=> 'parent',
				'title' 		=> 'Category',
				'desc' 			=> 'Which category would you like this forum to be in?',
				'valueType' 	=> 'drop',
				'valueFunction' => 'getBoard',
				'funcVar'		=> $this->row['id'],
				'tab'			=> 'foruminfo',
				'subcat'		=> 'basic',
			),
			array(
				'name' 			=> 'allowThreads',
				'title' 		=> 'Show Threads',
				'desc' 			=> 'Set whether to show threads in this forum. Generally used to hide threads when creating a forum as a subforum container.',
				'valueType' 	=> 'onoff',
				'valueDef'		=> '1',
				'tab'			=> 'foruminfo',
				'subcat'		=> 'basic',
			),
			array(
				'name' 			=> 'isLink',
				'title' 		=> 'Forum is Link',
				'desc' 			=> 'Set whether to use this forum as a link',
				'valueType' 	=> 'onoff',
				'tab'			=> 'foruminfo',
				'subcat'		=> 'link',
			),
			array(
				'name' 			=> 'newTab',
				'title' 		=> 'Open in New Tab',
				'desc' 			=> 'If this forum is a link (above) set whether it will open in a new tab',
				'valueType' 	=> 'onoff',
				'tab'			=> 'foruminfo',
				'subcat'		=> 'link',
			),
			array(
				'name' 			=> 'linkUrl',
				'title' 		=> 'Forum link URL',
				'desc' 			=> 'If forum is being used as a link (above), set the URL to link to',
				'valueType' 	=> 'field',
				'tab'			=> 'foruminfo',
				'subcat'		=> 'link',
			),
			array(
				'name' 			=> '',
				'title' 		=> 'Permission Table',
				'desc' 			=> 'If forum is being used as a link (above), set the URL to link to',
				'valueType' 	=> 'permtable',
				'tab'			=> 'perm',
				'funcVar'		=> array('read', 'start', 'post', 'upload', 'download'),
			),
			array(
				'name' 			=> 'threadSortField',
				'title' 		=> 'Default field to sort threads by',
				'desc' 			=> '',
				'valueType' 	=> 'drop',
				'valueOptions'	=> array(
									'Last Post'		=> 'lastPostTime',
									'Start Time'	=> 'id',
								),
				'tab'			=> 'threadinfo',
				'subcat'		=> 'order',
			),
			array(
				'name' 			=> 'threadSortOrder',
				'title' 		=> 'Default order to sort threads',
				'desc' 			=> '',
				'valueType' 	=> 'drop',
				'valueDefault' 	=> 'desc',
				'valueOptions'	=> array(
									'Descending'	=> 'desc',
									'Ascending'		=> 'asc',
								),
				'tab'			=> 'threadinfo',
				'subcat'		=> 'order',
			),
			array(
				'name' 			=> 'show_rules',
				'title' 		=> 'Show Rules',
				'desc' 			=> 'Set whether to show the forum rules',
				'valueType' 	=> 'onoff',
				'valueDef'		=> '0',
				'tab'			=> 'frules',
				'subcat'		=> 'frules',
			),
			array(
				'name'			=> 'rules',
				'title'			=> 'Forum Rules',
				'desc'			=> 'Enter a set of forum rules that appear at the top of the page on the forum view.',
				'valueType'		=> 'editor',				
				'tab'			=> 'frules',
				'subcat'		=> 'frules',				
			),
		);		
		
	}
	
	
}

?>