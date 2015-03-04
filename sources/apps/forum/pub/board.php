<?php

	//===================================================
	//	Cipher Pixel Board © All Rights Reserved
	//---------------------------------------------------
	//	CP-Core
	//		by michaelbenner.net
	//---------------------------------------------------
	//	File created: April 04, 2013 
	//===================================================

class pub_board extends controller {
	
	/**
	** Forum Assist
	*/
	public	$class_forums;
	
	/**
	** Array of ids to retrieve
	*/
	public	$userIds = array();
	public	$threadIds = array();
	
	/**
	** main()
	*/
	public function main()
	{
		
		if ( cp::$GET['2'] == 'logout' )
		{
			cp::logged()->logout();
			return;
		}
		
		# Load Form Assistant
		$this->class_forums = cp::call( 'class_forums', 'apps/forum/classes/class_forums' );
		
		# Prepares user ids and thread ids
		$this->prepare();
		
		# Show Board	
		$this->showBoard();

		# Output
		cp::output('norm');

	}
	
	/**
	** Gets member and other preparation things
	*/
	public function prepare()
	{
		
		# Get all boards
		$this->class_forums->retrieveReadable();
		
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
		** Prepare Rightbar..., gets users we'll need
		*/
		$this->rightbar_prep();
		
		$this->stats_prep();
		
		# Get all Members
		Debug::flag_query('7', 'hello');
		cp::call('members')->fetch( array( 'id' => $this->userIds ) );
		
		if ( $this->threadIds )
		{

			# Fetch threads for "last posted in"
			cp::db()->fetch( array(
				'table'	=> 'threads',
				'where'	=> array( 'id' => $this->threadIds )
			) );
			
		}
		
	}
	
	public function showBoard()
	{
		
		# Loop through forums
		$this->class_forums->loopForums( '0', $this, 'showBoard_callback' );
		
		/**
		** Page Setup
		*/
		cp::cont()->page['table'] = cp::cont()->build_html['0'];
		
		/**
		** Right bar
		*/
		$this->rightbar();
		
		/**
		** Statistics
		*/
		$this->stats();
		
		# Output
		cp::cont()->output .= cp::display()->read('page_gen');
		
	}
	
	public function showBoard_callback( $forum )
	{
		
		# Update Forum
		$forum = $this->class_forums->forum_array[ $forum['id'] ];
		
		/**
		** What kind of "forum" is this?
		*/
		$tpl = ( $forum['parent'] == '0' ) ? 'cat': 'forum';
		$tpl = ( $this->class_forums->depth > 2 ) ? 'sub': $tpl;
		
		# Get Sub html
		$forum['subHtml'] 	= cp::cont()->build_html[ $forum['id'] ];
		
		# Skip categories with no forums
		if ( $tpl == 'cat' AND !$forum['subHtml'] ) return;
		
		/**
		** Save forum for display, adds subHtml as well
		*/
		$forum['title'] 	= $forum['name'];
		$forum['lastT']		= ( $forum['lastPostTime'] ) ? cp::display()->time2str( $forum['lastPostTime'] ): false;

		/**
		** Get last thread Information
		*/
		if ( $forum['lastPostThread'] )
		{
			
			# Thread!
			$thread = cp::db()->get( 'threads', $forum['lastPostThread'] );
			
			# Set parent as last post
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
		
		/**
		** Display vars =0
		*/
		cp::display()->vars[ $tpl ] 	= $forum;
		cp::display()->vars['last'] 	= $user;
		cp::display()->vars['lastT'] 	= $thread;
		
		# Add to forum html array
		cp::cont()->build_html[ $forum['parent'] ] .= cp::display()->read('cat_' . $tpl );
		
	}
	
	/**
	** Rightbar
	*/
	public function rightbar_prep()
	{	
		
		/**
		** Foreach app, look for a right bar... don't die if not  found
		*/
		foreach( cp::$apps as $name => $array )
		{
			cp::methodCall( cp::call( $array['name'] . '_dtools', 'apps/'.$array['name'].'/classes/'.$array['name'].'_dtools', false ), 'rightbar_prep' );
		}
				
	}
	
	public function rightbar()
	{
		
		/**
		** Foreach app, look for a right bar... don't die if not  found
		*/
		foreach( cp::$apps as $name => $array )
		{
			cp::methodCall( cp::call( $array['name'] . '_dtools', 'apps/'.$array['name'].'/classes/'.$array['name'].'_dtools', false ), 'rightbar' );
		}
		
		/**
		** Save to vars
		*/
		cp::cont()->page['right'] = cp::display()->read('right/feed');
		
	}
	
	/**
	** 
	*/
	public function stats_prep()
	{
		
		$this->latest = cp::db()->fetch( array(
			'select'	=> 'arrayTitle, value',
			'table'		=> 'miscstat',
			'key'		=> 'arrayTitle',
		) );
		
		$this->userIds[] = $this->latest['latestUser']['value'];
		
		$fifteenago = ( cp::$time - ( 60 * 15 ) );

		$this->online = cp::db()->fetch( array(
			'select'	=> 'id, userId',
			'table'		=> 'online',
			'where'		=> 'lastClick > '.$fifteenago.' AND userId != "0"',
			'order'		=> 'lastClick desc',
			'record'	=> 'userId',
		) );
		
		$this->userIds = cp::merge( $this->userIds, cp::db()->record );
		
	}
	
	/**
	** stats bar 
	*/
	public function stats()
	{
		
		$fifteenago = ( cp::$time - ( 60 * 15 ) );
		
		# Count Guests
		$guest_num	= cp::db()->count('online', '*', 'lastClick > '.$fifteenago.' AND userId = "0"');

	    $row 		= $guest_num->fetch_assoc();	    
		$guest_num 	= $row['NumHits'];
		
		# Print each user
		if ( !is_array( $this->online ) )
		{
			$online_num = 0;
		}
		else
		{
			$online_num = count( $this->online );
			
			foreach( $this->online as $ip => $a )
			{
				
				$i++;
				
				# Break if too many
				if ( $i > 50 ) break;
				
				# Comma!
				if ( $online ) $online .= ', ';
				
				# yadada
				$user 	= cp::call('members')->prepMember( cp::db()->get( 'users', $a['userId'] ) );
				
				$online .= '<a href="'.cp::link(array('members',$user['displayName'])).'">'.$user['htmlName'].'</a>';
				
			}
			
		}
		
		cp::display()->vars['online'] 	= $online;
		cp::display()->vars['online_mem']	= $online_num;
		cp::display()->vars['online_gue']	= $guest_num;
		
		# Total posts, threads, users
		cp::display()->vars['latest'] = $this->latest;
		
		# Newst User
		cp::display()->vars['latestMember'] = cp::call('members')->prepMember( cp::db()->get( 'users', $this->latest['latestUser']['value'] ) );
		
		cp::display()->vars['cat'] = array('subHtml' => cp::display()->read('cat_stats') );		
		cp::cont()->page['table'] .= cp::display()->read('cat_cat');
		
	}
	
	/**
	** ajax_setread() - set forums as read
	*/
	public function ajax_setread($forumId)
	{
		$this->class_forums = cp::call( 'class_forums', 'apps/forum/classes/class_forums' );		
		$this->class_forums->retrieveReadable();		
		$this->class_forums->loopForums($forumId, $this, 'setread_callback');
		cp::logged()->setread('forum', $forumId );
		
		cp::call('ajax')->ret['toggle'] = '.status_' . $forumId;
		
	}
	
	public function setread_callback($forum)
	{
		cp::logged()->setread('forum', $forum['id'] );
	}
	
	/**
	** ajax_setread() - set forums as unread
	*/
	public function ajax_unread($forumId)
	{
		$this->class_forums = cp::call( 'class_forums', 'apps/forum/classes/class_forums' );		
		$this->class_forums->retrieveReadable();		
		$this->class_forums->loopForums($forumId, $this, 'unread_callback');
		cp::logged()->unread( 'forum', $forumId );
		cp::call('ajax')->ret['toggle'] = '.status_' . $forumId;
		
	}
	
	public function unread_callback($forum)
	{
		cp::logged()->unread( 'forum', $forum['id'] );
	}
	
}

?>