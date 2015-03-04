<?php

	/*===================================================
	//	CipherPixel © All Rights Reserved
	//---------------------------------------------------
	//	CP-Coure
	//		by cipherpixel.net
	//---------------------------------------------------
	//	File created: October 20, 2014 
	//=================================================*/
	
class pub_mod extends controller {
	
	public function main()
	{
		
		if ( $this->allowed() )
		{
			if ( $this->page_mod() ) return;
		}
		else
		{
			$this->page_noauth();
		}
		
		# Output	
		cp::cont()->output .= cp::display()->read('page_gen');
		cp::output('norm');
		
	}
	
	/**
	** allowed() - is this member a moderator of any forums?
	** 
	** @return	bool
	*/
	public function allowed()
	{
		
		if ( cp::logged()->cur['globalMod'] )
			return true;
			
		return false;
		
	}
	
	/**
	** page_noauth() - returns if member is not a moderator
	** 
	*/
	public function page_noauth()
	{
		cp::cont()->page['table'] = cp::display()->quickRead( 'cat_error', 'no_auth' );		
	}
	
	/**
	** page_mod() - moderator control panel
	** 
	** @return	bool	true=splash
	*/
	public function page_mod()
	{
		
		cp::cont()->navtree( cp::lang('report', 'mod' ), cp::link(array('mod')) );
		
		if ( cp::$GET['2'] == 'reports' )
		{
			$this->bit_reports();
		}
		else
		if ( cp::$GET['2'] == 'threads' )
		{
			$this->bit_threads();
		}
		
		cp::display()->vars['cat']['subHtml'] = cp::display()->read('page_mod');
		
		cp::cont()->page['title'] = cp::lang('report', 'mcp');
		cp::cont()->page['table'] = cp::display()->read('cat_cat');
		
		/* False prevents splash */
		return false;
		
	}
	
	/**
	** bit_reports() - shows current reports etc
	** 
	** @return	bool	true=splash
	*/
	public function bit_reports()
	{
		
		//-----------------------------------
		// Get reports and members
		//-----------------------------------
		
		cp::db()->build( array(
			'select'	=> 'reports.*, posts.id AS post_id, posts.posterId, posts.threadId, threads.forumId as forum_id, threads.title as thread_title, threads.slug as thread_slug',
			'table' 	=> 'reports',
			'where'		=> 'status=0 AND ( forums.parent="0" OR '.cp::call('perm')->allowMe('read').')',
			'order'		=> 'id asc',
			'limit'		=> '10',
			'joins'		=> array(
				array(
					'table'	=> 'posts',
					'where'	=> 'reports.type="post" and reports.relId=posts.id',
					'type'	=> 'left',
				),
				array(
					'table' => 'threads',
					'where' => 'threads.id=posts.threadId',
					'type'	=> 'left',
				),
				array(
					'table'	=> 'perm_reg',
					'where'	=> 'perm_reg.type="forum" AND threads.forumId=perm_reg.type_id',
					'type'	=> 'left',
				),
				array(
					'table'	=> 'forums',
					'where'	=> 'forums.id=perm_reg.type_id',
					'type'	=> 'left',
				),
			),
			'record'	=> array( 'users' => array('from', 'posterId') ),
		) );
		
		$reports = cp::db()->fetch();
		
		$members = cp::call('members')->fetch( array( 'id'=> cp::db()->record['users'] ) );
		
		if ( !$reports )
		{
			cp::display()->vars['bit'] = cp::display()->quickRead( 'cat_message', 'no_rep' );
			return false;
		}
		
		/* Init class_forums */
		cp::callAppClass('class_forums')->retrieveReadable();
		cp::call('class_forums')->loopForums();
		
		//-----------------------------------
		// Show reports
		//-----------------------------------
		
		$members =  cp::call('members')->prepMember( $members );

		foreach( $reports as $report )
		{

			/* Related Users */
			cp::display()->vars['sender'] 	= $members[ $report['from'] ];
			cp::display()->vars['poster']		= $members[ $report['posterId'] ];
			
			/* Modifiers */
			$report['engTime']	= cp::display()->time2str( $report['sendTime'] );
			$report['first']	= ( $html ) ? false: true;
			
			/* Report Navtree */
			$forum = cp::call('class_forums')->forum_array[ $report['forum_id'] ];
			
			foreach( $forum['path_id'] as $id )
			{
				$f  = cp::call('class_forums')->forum_array[ $id ];				
				$report['navtree'] .= ( $report['navtree'] ) ? ' >> ': '';
				$report['navtree'] .= '<a href="'.cp::link($f['path']).'">'.$f['name'].'</a>';
			}
			
			$f['path'][] = $report['thread_slug'];
			$report['navtree'] .= ( $report['navtree'] ) ? ' >> ': '';
			$report['navtree'] .= '<a href="'.cp::link($f['path']).'">'.$report['thread_title'].'</a>';

			/* Link to specific post */
			$f['path'][] = 'p_' . $report['post_id'] . '#post_all_' . $report['post_id'];
			
			$report['path'] = $f['path'];
				
			cp::display()->vars['report']		= $report;
			
			$html .= cp::display()->read( 'rep_row/' . $report['type'] );
			
		}
		
		cp::cont()->page['mod_buts'] = cp::display()->read('multi_report_options');
		
		cp::display()->vars['bit'] = $html;
	}
	
	/**
	** bit_reports() - shows current reports etc
	** 
	** @return	bool	true=splash
	*/
	public function bit_threads()
	{
		
		//-----------------------------------
		// Get reports and members
		//-----------------------------------
		
		cp::db()->build( array(
			'select'	=> 'threads.*',
			'table' 	=> 'threads',
			'where'		=> 'visible=0 AND '.cp::call('perm')->allowMe('read'),
			'order'		=> 'id asc',
			'limit'		=> '10',
			'joins'		=> array(
				array(
					'table'	=> 'perm_reg',
					'where'	=> 'perm_reg.type="forum" AND threads.forumId=perm_reg.type_id',
					'type'	=> 'left',
				),
			),
			'record'	=> array( 'users' => array('starterId') ),
		) );
		
		$threads = cp::db()->fetch();
		
		$members = cp::call('members')->fetch( array( 'id'=> cp::db()->record['users'] ) );
		
		if ( !$threads )
		{
			cp::display()->vars['bit'] = cp::display()->quickRead( 'cat_message', 'no_threads' );
			return false;
		}
		
		/* Init class_forums */
		cp::callAppClass('class_forums')->retrieveReadable();
		cp::call('class_forums')->loopForums();
		
		//-----------------------------------
		// Display
		//-----------------------------------

		$html .= cp::callAppClass('class_thread')->show( $threads, cp::call('class_forums')->forum_array );
		
		/* Page Modeartor Options */
		cp::display()->vars['mod'] = array(
			'lock' 	=> cp::callAppClass('lib_perm')->lockThread( $this->forum, $this->thread ),
			'hide' 	=> cp::callAppClass('lib_perm')->hideThread( $this->forum, $this->thread ),
			'del'	=> cp::callAppClass('lib_perm')->delThread( $this->forum, $this->thread ),
			'move'	=> cp::callAppClass('lib_perm')->canMove( $this->forum, $this->thread ),
		);
		
		cp::cont()->navtree( cp::lang('report', 'th' ) );
		
		cp::cont()->page['mod_buts'] = cp::display()->read('multi_thread_options');
		
		cp::display()->vars['bit'] = $html;
	}
	
	/**
	** ajax_multi_mod() - manages the multi mod click links
	*/
	public function ajax_multi_mod()
	{
		
		$do 		= cp::$POST['moredata'];
		$allowed	= array('ignore');
		
		cp::call('ajax')->ret['alert'] = $do;
	}
	
}

?>