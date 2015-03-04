<?php

	//===================================================
	//	Cipher Pixel Board © All Rights Reserved
	//---------------------------------------------------
	//	CP.Board
	//		by michaelbenner.net
	//---------------------------------------------------
	//	File created: April 04, 2013 
	//===================================================

class forum_dtools extends display {
	
	/**
	** Rank Table Cache
	*/
	public	$rank_cache;
	
	/**
	** Pagetree temp
	*/
	private	$pagetree;
	
	/**
	** Right Bar threads
	*/
	private	$news_threads;
	private	$latest_threads;
	
	/**
	** Rightbar
	*/
	public function rightbar_prep()
	{
		
		# News threads
		if ( cp::set('newsForum') )
		{
			cp::db()->fetch( array(
				'select'	=> 'threads.id',
				'table'		=> 'threads',
				'where'		=> 'threads.visible=1 AND forumId='.cp::set('newsForum'),
				'record'	=> 'id',
				'order'		=> '`startTime` desc',
				'limit'		=> '5',
			) );
		}
		
		$threads = array();
		
		if ( is_array( cp::db()->record ) )
		{
			$this->news_threads = cp::db()->record;
			$threads = array_merge( $threads, cp::db()->record );
		}
		
		# Latest threads
		cp::db()->fetch( array(
			'select'=> 'threads.id, threads.starterId',
			'table'	=> 'threads',
			'where'	=> 'threads.visible=1 AND perm_reg.'.cp::call('perm')->allowMe( 'read' ),
			'joins' => array(
				array( 
					'table' => 'forums',
					'where' => 'threads.forumId=forums.id',
					'type'	=> 'left',
				),
				array(
					'table'	=> 'perm_reg',
					'where'	=> 'perm_reg.type="forum" AND forums.id=perm_reg.type_id',
					'type'	=> 'left',
				),
			),
			'record'	=> array('threads' => 'id', 'users' => 'starterId'),
			'order'		=> 'startTime desc',
			'limit'		=> '5',
		) );
		
		/**
		** Combine Thread Ids to one...
		*/
		if ( is_array( cp::db()->record['threads'] ) )
		{
			$this->latest_threads = cp::db()->record['threads'];
			$threads = array_merge( $threads, cp::db()->record['threads'] );
		}
			
		if ( is_array( cp::call('pub_board')->threadIds ) )
		{
			cp::call('pub_board')->threadIds = array_merge( cp::call('pub_board')->threadIds, $threads );
		}
		else
		{
			cp::call('pub_board')->threadIds = $threads;
		}
			
		/**
		** Same with users
		*/
		if ( !is_array( cp::call('pub_board')->userIds ) AND is_array( cp::db()->record['users'] ) )
		{
			cp::call('pub_board')->userIds = cp::db()->record['users'];
		}
		else
		if ( is_array( cp::db()->record['users'] ) AND is_array( cp::call('pub_board')->userIds ) )
		{
			cp::call('pub_board')->userIds = array_merge( cp::db()->record['users'], cp::call('pub_board')->userIds );
		}
		
	}
	
	/**
	** Rightbar()
	*/
	public function rightbar()
	{

		/**
		** News Forum! Assumes we have already gotten all the forums...
		*/
		if ( cp::set('newsForum') )
		{
			
			# Feed_row Vars
			$forum = cp::callAppClass('class_forums')->forum_array[ cp::set('newsForum') ];
			cp::display()->vars['feed_title'] = $forum['name'];
			cp::display()->vars['feed_rows'] = '';
			
			if ( $forum )
			{
			
				if ( $this->news_threads )
				{
	
					# Print Threads
					foreach ( $this->news_threads as $thread_id )
					{
	
						$thread = cp::db()->get( 'threads', $thread_id );
						
						# Set Array
						cp::display()->vars['thread']['title'] 	= $thread['title'];
						cp::display()->vars['thread']['engTime'] 	= date( cp::set('shortdate'), $thread['startTime'] );
						
						cp::display()->vars['thread']['path']		= $forum['path'];
						cp::display()->vars['thread']['path'][]	= $thread['slug'];
						
						# TPL Bit
						cp::display()->vars['feed_rows'] .= cp::display()->read('right/news_row');
						
					}
					
				}
				else
				{
					cp::display()->vars['feed_rows'] .= cp::display()->quickRead( 'right/feed_msg', cp::lang('cat', 'news_none') );
				}
			
				# Save
				cp::display()->vars['feed'] .= cp::display()->read('right/feed_row');
				
			}
			
		}
		
		/**
		** Latest Threads
		*/
		
		# Feed_row Vars
		cp::display()->vars['feed_title'] = cp::lang('cat', 'late_threads');
		cp::display()->vars['feed_rows'] = '';
		
		if ( $this->latest_threads )
		{

			foreach( $this->latest_threads as $thread_id )
			{
				
				# Changes
				$thread					= cp::db()->get( 'threads', $thread_id );
				$forum					= cp::callAppClass('class_forums')->forum_array[ $thread['forumId'] ];
				$thread['lastPostEng']	= cp::display()->time2str( $thread['lastPostTime'] );
				$thread['path']			= $forum['path'];
				$thread['path'][]		= $thread['slug'];
				
				$thread['title'] = ( strlen( $thread['title'] ) < 80 ) ? $thread['title']: substr( $thread['title'], 0, 80 ) . '...';
				
				# Disp Arrays
				cp::display()->vars['starter']= cp::call('members')->prepMember( cp::db()->get( 'users', $thread['starterId'] ) );
				cp::display()->vars['thread'] = $thread;
				cp::display()->vars['forum'] 	= $forum;
				
				
				# TPL Bit
				cp::display()->vars['feed_rows'] .= cp::display()->read('right/thread_row');
			}
			
		}
		else
		{
			cp::display()->vars['feed_rows'] .= cp::display()->quickRead( 'right/feed_msg', cp::lang('cat', 'new_none') );
		}
		
		# Save
		cp::display()->vars['feed'] .= cp::display()->read('right/feed_row');			
		
	}
	
	/**
	** Menubar array - nb: the result of this function becomes cached
	** 
	** @return	array
	*/
	public function menubar()
	{
		
		$array = array(
			array(
				'if'    	=> '$app == "forum" AND cp::$GET[1] != "members"',
				'lang_pack'	=> 'all',
				'lang_key'	=> 'menu_forums',
				'link'		=> array('board'),
			),
			array(
				'if'		=> 'cp::$GET[1] == "members" AND ( !isset( cp::$GET[2] ) OR cp::$GET[2] != cp::logged()->cur[displayName] )',
				'lang_pack'	=> 'all',
				'lang_key'	=> 'menu_members',
				'link'		=> array('members'),
			),
			array(
				'if'		=> 'cp::logged()->in AND cp::$GET[1] == "members" AND cp::$GET[2] == cp::logged()->cur[displayName]',
				'lang_pack'	=> 'all',
				'lang_key'	=> 'menu_profile',
				'link_eval'	=> '( cp::logged()->in ) ? array("members", cp::logged()->cur[displayName]): array("login") ',
			),
		);
		
		return $array;		

	}
	
	/**
	** Get Pips...
	** 
	** @post_count	= Member post count
	*/
	public function getPips( $post_count )
	{

		$i = 0;
		$r = NULL;
		
		/**
		** Ranks
		*/
		if ( !$this->rank_cache )
			$this->rank_cache = cp::db()->fetch_dep( array(
				'table' => 'ranks',
				'order' => 'minPosts asc',
			) );
			
		if ( !$this->rank_cache ) return FALSE;

		/**
		** Get the pip
		*/
		$this->lastUserPosts = $post_count;
		$ranks = array_filter( $this->rank_cache, array($this, "greaterThan") );		
		$rank = end($ranks);
		
		$r = $rank;
		
		/**
		** Repeat
		*/
		$i = 0;
		if ( is_numeric( $r['pip'] ) )
		{
			while ( $i != $r['pip'] )
			{
				$r['img'] .= '<img src="'.cp::display()->vars['lprefix'].'style/'.cp::display()->vars['styleFolder'].'/images/'.cp::set('pipImg').'" />';
				$i++;
			}
		}
		else
		{
			$r['img'] = '<img src="'.cp::display()->vars['lprefix'].'style/'.cp::display()->vars['styleFolder'].'/images/'.$r['pip'].'" />';
		}

		return $r;
		
	}
	
	public function greaterThan( $array )
	{		
		if ( $array['minPosts'] <= $this->lastUserPosts ) return true;		
		return false;
	}
	
	/**
	** pagetree()
	** 
	** @cur		= current page
	** @items	= total items (ie, 21 posts)
	** @perPage = how many items per page
	** @links	= array of links for links->makelinks
	*/
	public function pagetree( $cur, $items, $perPage, $links )
	{
		
		$this->pagetree = new stdClass();
		
		//-------------------------------
		//	Set $cur
		//
		if ( $cur == 0 ) $cur = 1;
		
		if ( $items == 0 ) $items = 1;
		
		//-------------------------------
		//	How many pages are there?
		//		
		$pages = ceil( ( $items ) / $perPage );
		
		//-------------------------------
		//	Show previous items?
		//
		if ( $cur != 1 )
		{
			$tree .= '<a href="'.cp::call('link')->make( array_merge( $links, array('1') ) ).'" class="butdark"><<</a> <a href="'.cp::call('link')->make( array_merge( $links, array($cur - 1) ) ).'" class="butdark">'.cp::lang('all', 'prev').'</a> ';
		}
		
		//-------------------------------
		//	Page x of y...
		//
		$tree .= '<span class="butlight">'.cp::lang('all', 'page').' '.$cur.' '.cp::lang('all', 'of').' '.$pages.'</span> ';
		
		/**
		** Is this the last page?
		*/
		
		if ( $cur == $pages )
			$this->pagetree->last = TRUE;
		
		//-------------------------------
		//	Light up the current page
		//
		$numbuts = '<a class="butsel">'.$cur.'</a> ';
		
		//-------------------------------
		//	Create two links before current
		//
		$count = ( $cur - 1 );
		$i = 0;
		while( ( $count != 0 ) AND ( $i != 2 ) )
		{
			
			$numbuts = '<a href="'.cp::call('link')->make( array_merge( $links, array($count) ) ).'" class="butlight">'.$count.'</a> ' . $numbuts;
			$i++;
			$count--;
		}
		
		//-------------------------------
		//	Create two links after current
		//
		$count = ( $cur );
		$i = 0;
		while( ( $count != $pages ) AND ( $i != 2 ) )
		{
			$count++;
			$numbuts .= '<a href="'.cp::call('link')->make( array_merge( $links, array($count) ) ).'" class="butlight">'.$count.'</a> ';
			$i++;
		}
		
		$tree .= $numbuts;
		
		//-------------------------------
		//	Last
		//
		if ( $cur != $pages )
		{
			
			$tree .= '<a href="'.cp::call('link')->make( array_merge( $links, array( $cur + 1 ) ) ).'" class="butdark">'.cp::lang('all', 'next').'</a> <a href="'. cp::call('link')->make(array_merge($links, array($pages))) .'" class="butdark">>></a> ';
			
		}
		
		return $tree;
		
	}
	
	/**
	** formGen() - creates a general form
	** 
	** @param	array	$array	array of form settings
	*/
	public function formGen( $array )
	{
		
		$lang = cp::call('lang')->load( $array['lang_pack'] );
		
		$last_subcat = false;
		
		foreach( $array['fieldA'] as $k => $field )
		{	
			
			/* New subcat? */
			if ( $field['subcat'] != $last_subcat )
			{	
				cp::display()->vars['subcat']	= true;				
				cp::display()->vars['subcat_title'] = $lang->get( $field['subcat'] );				
			}
			
			$last_subcat = $field['subcat'];			
					
			cp::display()->vars['row'] = array(
				'name'	=> $field['name'],
				'title'	=> $lang->get( $field['lang_key'] ),
				'desc'	=> $lang->get( $field['lang_key'] . '_desc' ),
				'type'	=> $field['type'],
				'html'	=> $field['html'],
				'def'	=> $field['def'],
			);
			
			cp::display()->vars['rows'] .= cp::display()->read('form_gen_row');	
			
			cp::display()->vars['subcat']	= false;		
			
		}
		
		$build = cp::display()->read('form_gen_container');
		
		cp::display()->vars['form']['fields'] = $build;
		
	}
	
}

?>