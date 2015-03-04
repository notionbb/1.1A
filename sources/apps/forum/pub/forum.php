<?php

	//===================================================
	//	Cipher Pixel Board © All Rights Reserved
	//---------------------------------------------------
	//	CP-Core
	//		by michaelbenner.net
	//---------------------------------------------------
	//	File created: April 04, 2013 
	//===================================================

class pub_forum extends controller {
	
	/**
	** Forum Assist
	*/
	public	$class_forums;
	
	/**
	** Array of user ids to retrieve
	*/
	public	$userIds 	= array();
	public	$threadIds	= array();
	
	/**
	** True until proven otherwise...
	*/
	public	$read = true;
	
	/**
	** Current Forum
	*/
	public $forum;
	
	public function main()
	{
		
		# Assistant
		$this->class_forums = cp::callAppClass( 'class_forums' );
		
		# Get Forum Slug
		$forumSlug 	= ( cp::$GET['slug'] ) ?: print_r( cp::$GET ) . die('Slug not received');	

		# Prepare
		if ( $this->prepare( $forumSlug ) )
		{

			# Show Show Forums			
			$this->showSubforums();
			
			# This is "better" (paths included etc) now because of loop_forums
			$this->forum = $this->class_forums->forum_array[ $this->forum['id'] ];

			# Show threads, only if we have not set to hide them, and forum isn't category
			if ( $this->forum['allowThreads'] AND $this->forum['parent'] != '0' )
			{
				$this->mod_options();
				$this->showthreads();
			}
			
			# Page information
			$this->page();
			
		}		
		
		# Output
		cp::cont()->output .= cp::display()->read('page_gen');	
			
		cp::output('norm');
		
	}
	
	/**
	** Gets member and other preparation things
	*/
	public function prepare( $forumSlug )
	{
		
		/**
		** Get all Forums
		*/
		$forumId 	= $this->class_forums->retrieveReadable( array( 'slug' => $forumSlug ) );
		
		/**
		** Return if not found
		*/
		if ( !$forumId )
		{			
			cp::cont()->page['table'] = cp::display()->quickRead( 'cat_error', cp::lang('cat', 'no_fororth') );			
			return false;			
		}
		
		/**
		** Get Current Forum
		*/
		$this->forum = $this->class_forums->forum_array[ $forumId ];
		
		/**
		** Prepare users from forums
		*/		
		if ( $this->class_forums->forum_map[ $this->forum['id'] ] AND ( $IDs = $this->class_forums->extractUserIds( $this->class_forums->forum_map[ $this->forum['id'] ] ) ) )
		{
			$this->userIds = array_merge( $this->userIds, $IDs );
		}
		
		/**
		** Visibility?
		*/
		$vis = cp::callAppClass('class_thread')->visible( $this->forum );
		
		$this->forum['threadCount'] = $vis['threadCount'];
		$vis = $vis['vis_str'];
		
		/**
		** What page do we want?
		*/
		$this->page_num = ( cp::$cache['PAGE_NUM'] ) ?: '1';
		
		$max_threads_per_page = cp::set('threadsPerPage');
		
		if ( !$this->page_num OR $this->page_num == 1 OR $this->page_num < 1 )
			$lower_limit = '0';
		else
		if ( $this->page > 1 )
		{
			$lower_limit = ( $max_threads_per_page * $this->page_num ) - $max_threads_per_page;
		}
		
		$top_limit = $max_threads_per_page;		
		
		/**
		** Prepare threads
		*/
		if ( $this->forum['allowThreads'] AND $this->forum['parent'] != '0' )
		{
		
			$this->threads = cp::db()->fetch_dep( array(
				'table'		=> 'threads',
				'where'		=> 'forumId="'.$this->forum['id'].'" AND '.$vis,
				'order'		=> '`isPinned` desc, `'.$this->forum['threadSortField'].'` '.$this->forum['threadSortOrder'],
				'limit'		=> $lower_limit . ', '. $top_limit,
			) );
			
			if ( $this->threads )
			{
			
				foreach( $this->threads as $id => $thread )
				{
					$build[]	= $thread['starterId'];
					$build[]	= $thread['lastPostUser'];
				}
				
				$this->userIds = array_merge( $this->userIds, $build );
				
			}
			
		}
		
		/**
		** Prepare Users and Threads
		*/
		foreach( $this->class_forums->forum_map as $forumId => $childArray )
		{
			if ( $arr = $this->class_forums->extractUserIds( $this->class_forums->forum_map[$forumId] ) )
			{
				$this->userIds = array_merge( $this->userIds, $arr );
			}
			if ( $arr = $this->class_forums->extractThreadIds( $this->class_forums->forum_map[$forumId] ) )
			{
				$this->threadIds = array_merge( $this->threadIds, $arr );
			}
		}		
		
		/**
		** Get members
		*/
		if ( $this->userIds )
		{
			cp::call('members')->fetch( array( 'id' => $this->userIds ) );
		}
		
		/**
		** Get threads
		*/
		cp::db()->fetch_dep( array(
			'table'	=> 'threads',
			'where'	=> array( 'id' => $this->threadIds )
		) );
		
		return true;
		
	}
	
	public function showSubforums()
	{
		
		/**
		** Print Current Forums...
		*/		
		$this->class_forums->loopForums( $this->forum['parent'], $this, 'showSubforums_callback' );
		
		//print_r($this->class_forums->forum_array);
		
		# Skip if we didn't get anything
		if ( cp::cont()->build_html[ $this->forum['id'] ] )
			cp::cont()->page['table'] = cp::cont()->build_html[ $this->forum['parent'] ];
		
	}
	
	public function showSubforums_callback( $forum )
	{
		
		# Skip other categories
		if ( $this->class_forums->depth == 1 AND ( $forum['id'] != $this->forum['id'] ) ) return;
		
		unset( $thread );

		# Update Forum
		$forum = $this->class_forums->forum_array[ $forum['id'] ];
		
		# What are we viewing?
		$tpl = ( $forum['id'] == $this->forum['id'] ) ? 'cat': 'forum';
		$tpl = ( $this->class_forums->depth > 2 ) ? 'sub': $tpl;

		# Add Subs
		$forum['subHtml'] = cp::cont()->build_html[ $forum['id'] ];
		
		# Save Forum For Display
		$forum['title'] = $forum['name'];
		$forum['lastT']	= ( $forum['lastPostTime'] ) ? cp::display()->time2str( $forum['lastPostTime'] ): false;

		# Last post user
		if ( $forum['lastPostUser']	)
		{
			//$user	= cp::call('members')->prepMember( cp::db()->get( 'users', $forum['lastPostUser'] ) );
		}
		
		# Last thread. Guard against '0' retrieveing all threads
		if ( $forum['lastPostThread'] )
		{

			$thread	= cp::db()->get( 'threads', $forum['lastPostThread'] );
			
			if ( $this->class_forums->forum_array[ $forum['parent'] ]['lastPostTime'] < $thread['lastPostTime'] )
			{
				$this->class_forums->forum_array[ $forum['parent'] ]['lastPostTime']	= $thread['lastPostTime'];
				$this->class_forums->forum_array[ $forum['parent'] ]['lastPostUser']	= $thread['lastPostUser'];
				$this->class_forums->forum_array[ $forum['parent'] ]['lastPostThread']	= $thread['id'];			
			}
			
			# Add thread count to parent
			$this->class_forums->forum_array[ $forum['parent'] ]['threadCount'] += $forum['threadCount'];
			$this->class_forums->forum_array[ $forum['parent'] ]['postCount'] 	+= $forum['postCount'];
			
			# Create thread path
			$thread['path'] 	= $this->class_forums->forum_array[ $thread['forumId'] ]['path'];
			$thread['path'][]	= $thread['slug'];
			
			# Do we need to cut the title?
			$thread['title'] = ( strlen( $thread['title'] ) > cp::set('threadTitlePreviewLen') ) ? substr($thread['title'], 0, 32) . '...': $thread['title'];
			
			# We also need the last user
			$user	= cp::call('members')->prepMember( cp::db()->get( 'users', $forum['lastPostUser'] ) );
			
		}
		
		/**
		** Have we read this forum?
		*/
		if ( cp::logged()->read( 'forum', $forum['id'], $forum['lastPostTime'] ) AND !$forum['child_unread'] )
		{
			$forum['read'] = true;
		}
		else
		{
			$this->class_forums->forum_array[ $forum['parent'] ]['child_unread'] = true;
		}
		
		# Varrrrsss
		cp::display()->vars[ $tpl ] 	= $forum;
		cp::display()->vars['last'] 	= $user;
		cp::display()->vars['lastT'] 	= $thread;
		
		# Add to forum html array
		cp::cont()->build_html[ $forum['parent'] ] .= cp::display()->read('cat_' . $tpl );
		
	}
	
	public function showthreads()
	{
		
		$forum = $this->forum;
		
		if ( !$this->threads )
		{			
			$forum['subHtml'] = cp::display()->quickRead(
				'cat_message',
				cp::lang('cat', 'no_threads') . ( cp::call('perm')->check( $this->forum, 'start' ) ? ' ' . cp::lang('cat', 'start_hint'): '') );
		}
		else
		{
		
			$forum['subHtml'] = cp::callAppClass('class_thread')->show( $this->threads, array( $forum['id'] => $forum ) );
			
		}
			
		/**
		** Forum Display Table
		*/
		
		# Unset Name, use small title		
		unset( $forum['name'] );		
		$forum['small'] = cp::lang('cat', 'view_threads');
		
		# Save to var
		cp::display()->vars['cat']	= $forum;
		
		# Output
		cp::cont()->page['table'] .= cp::display()->read('cat_cat');
		
	}
	
	public function page()
	{
		
		# Create Navtree
		$this->class_forums->navtree( $this->forum['id'] );
		
		# Build Pagetree
		cp::cont()->page['tree'] = cp::call('dtools')->pagetree( $this->page_num, $this->forum['threadCount'], cp::set('postsPerPage'), $this->forum['path'] );
		
		# Configure page
		cp::cont()->page['title'] = $this->forum['name'];
		
		# Forum rules?
		if ( $this->forum['show_rules'] AND $this->forum['rules'] )
		{
			cp::cont()->page['top-div'] = cp::call('bbcode')->bb2html( $this->forum['rules'] );
		}
		
		# Final var
		cp::display()->vars['forum'] 	= $this->forum;
		
		# Permissions
		if ( cp::callAppClass('lib_perm')->startThread( $this->forum ) )
		{
			cp::cont()->page['buts'] .= cp::display()->read('buts/start_thread');
		}

		# Save forum as read?
		if ( cp::callAppClass('class_thread')->read )
		{
			cp::logged()->setread('forum', $this->forum['id'] );
		}
		
	}
	
	/**
	** mod_options() - works out what multi thread options we can show
	*/
	public function mod_options()
	{
		
		$mod = false;
		
		if ( cp::callAppClass('lib_perm')->hideThread( $this->forum, $this->thread ) )
		{
			$mod['hide'] = true;
		}
		
		if ( cp::callAppClass('lib_perm')->delThread( $this->forum, $this->thread ) )
		{
			$mod['del'] = true;
		}
		
		if ( cp::callAppClass('lib_perm')->lockThread( $this->forum, $this->thread ) )
		{
			$mod['lock'] = true;
		}
		
		if ( cp::callAppClass('lib_perm')->canMove( $this->forum, $this->thread ) )
		{
			$mod['move'] = true;
		}
		
		if ( $mod )
		{
			cp::display()->vars['mod']	= $mod;
			cp::cont()->page['mod_buts'] 		= cp::display()->read('multi_thread_options');
		}
		
	}
	
	/**
	** Multi topic mod
	*/
	public function ajax_multi_mod()
	{
		
		# restore / delete etc
		$do 	= cp::$POST['moredata'];
		$allowed= array('delete', 'restore', 'hide', 'lock', 'unlock', 'move');
		
		if ( !in_array( $do, $allowed ) ) return;
		
		# Array of IDS
		$ids	= cp::$POST['selectArray'];
		
		if( $do == 'move' )
		{
			cp::methodCall( cp::call('pub_thread', 'apps/forum/pub/thread'), 'ajax_' . $do, $ids );
		}
		else
		{		
			foreach( $ids as $threadId )
			{
				cp::methodCall( cp::call('pub_thread', 'apps/forum/pub/thread'), 'ajax_' . $do, $threadId );
			}			
		}
		
	}
	
}

?>