<?php

	//===================================================
	//	Cipher Pixel Board © All Rights Reserved
	//---------------------------------------------------
	//	CP-Core
	//		by michaelbenner.net
	//---------------------------------------------------
	//	File created: April 04, 2013 
	//===================================================

class pub_thread extends controller {
	
	/**
	** Current Thread, posts and page number
	*/
	public	$thread;
	public	$posts;
	public	$page;
	
	/**
	** Forum Assist
	*/
	public	$class_forums;
	
	/**
	** Array of user ids to retrieve
	*/
	public	$userIds 	= array();
	
	/**
	** 
	*/
	public function main()
	{

		# Assistant
		$this->class_forums = cp::callAppClass( 'class_forums' );
		
		# Get cached thread?
		$this->thread = cp::db()->get( 'threads', cp::$cache['THREAD_ID'] );
		
		# Prepare
		if ( $this->prep() )
		{
			
			# Show posts
			$this->showPosts();
			
			# Moderator options
			$this->mod();
			
			# Quick Reply
			$this->quickReply();
			
			# Page information
			$this->page();
			
		}
		
		# Output
		cp::cont()->output .= cp::display()->read('page_gen');	
			
		cp::output('norm');
		
	}
	
	/**
	** 
	*/
	public function prep()
	{
		
		/**
		** Get all Forums
		*/
		$forumId = $this->class_forums->retrieveReadable( array( 'id' => $this->thread['forumId'] ) );
		
		/**
		** Return if not found
		*/
		if ( !$forumId )
		{			
			cp::cont()->page['table'] = cp::display()->quickRead( 'cat_error', cp::lang('cat', 'no_fororth') );			
			return false;			
		}
		
		# Loop Forums
		$this->class_forums->loopForums();
		
		/**
		** Get Current Forum
		*/
		$this->forum = $this->class_forums->forum_array[ $forumId ];
		
		/**
		** Can this member see deleted (hidden) posts or this thread?
		*/
		if ( cp::callAppClass('lib_perm')->isMod( $this->forum ) )
		{
			
			# Change the conditions of the query below 
			$where = 'threadId='.$this->thread['id'];
			
			# What cols to record
			$record = array( 'poster' => 'posterId', 'dby' => 'deletedBy' );
			
			# For the purposes of page trees...
			$this->thread['postCount'] += $this->thread['hiddenCount'];
			
		}
		else
		{
			$where = 'threadId='.$this->thread['id'].' AND visible=1';
			
			if ( $this->thread['visible'] != 1 )
			{
				cp::cont()->page['table'] = cp::display()->quickRead( 'cat_error', cp::lang('cat', 'no_fororth') );	
				return false;
			}
			
			# What cols to record
			$record = array('poster' => 'posterId');
			
		}
		
		$record['attachments'] = 'attachments';
		
		/**
		** Are we looking for a specific post?
		*/
		if ( cp::$cache['post_id'] )
		{
			
			$post_id = cp::clean( cp::$cache['post_id'] );
			
			$ret = cp::db()->fetch("
				SELECT x.id, x.position
					FROM (
						SELECT id, @rownum := @rownum + 1 AS position
          				FROM posts
          				JOIN (SELECT @rownum := 0) r
          				WHERE ".$where."
      					ORDER BY postTime asc
      				) x
				where x.id = '".$post_id."'
			");
			
			/* What page am I on */
			$this->page = ceil( ( $ret[ $post_id ]['position'] / cp::set('postsPerPage') ) );
			
			cp::display()->addJsGlobal( 'div_highlight', '#post_all_' . $post_id );
			
		}
		
		/**
		** Posts per page...
		*/
		if ( !$this->page )
		{			
			$this->page = ( cp::$cache['PAGE_NUM'] ) ?: '1';
		}
		
		$max_posts_per_page = cp::set('postsPerPage');
		
		if ( !$this->page OR $this->page == 1 OR $this->page < 1 )
			$lower_limit = '0';
		else
		if ( $this->page > 1 )
		{
			$lower_limit = ( $max_posts_per_page * $this->page ) - $max_posts_per_page;
		}
		
		$top_limit = $max_posts_per_page;
		
		/**
		** Are we on the last page?
		*/
		if ( ( $lower_limit + $top_limit ) > $this->thread['postCount'] )
		{
			cp::logged()->setread( 'thread', $this->thread['id'] );
		}

		/**
		** Get posts...
		*/
		$this->posts = cp::db()->fetch_dep( array(
			'table'		=> 'posts',
			'where'		=> $where,
			'order'		=> 'id asc',
			'limit'		=> $lower_limit . ', '. $top_limit,
			'record'	=> $record,
		) );
		
		/**
		** If no posts found, return page 1
		*/
		if ( !$this->posts )
		{			
			$this->page = 1;
			$this->posts = cp::db()->fetch_dep( array(
				'table'		=> 'posts',
				'where'		=> $where,
				'order'		=> 'id asc',
				'limit'		=> $max_posts_per_page,
				'record'	=> $record,
			) );	
		}
		
		/**
		** Combine the user arrays
		*/
		$user_to_get = cp::db()->record['poster'];
		
		if ( is_array( cp::db()->record['dby'] ) ) 
		{
			if( !is_array( $user_to_get ) )
			{
				$user_to_get = cp::db()->record['dby'];
			}else{
				$user_to_get = array_merge( $user_to_get, cp::db()->record['dby'] );
			}
		}
		
		if ( is_array( $user_to_get ) )
		{
			$user_to_get[] = $this->thread['starterId'];
		}else{
			$user_to_get = array($this->thread['starterId']);
		}
		
		/**
		** Get users
		*/
		cp::call('members')->fetch( array( 'id' => $user_to_get ) );
		
		/**
		** Get attachments...
		*/
		if ( cp::db()->record['attachments'] )
		{
			
			$attachments = array();
			
			foreach( cp::db()->record['attachments'] as $v )
			{
				$exp = explode(',', $v);
				if ( is_array( $exp ) ) 
				{
					$attachments = array_merge( $attachments, $exp );
				}
			}				
			
			cp::db()->fetch_dep( array(
				'table'	=> 'attachment',
				'where' => array('id' => $attachments ),
			) );
			
		}
		
		return true;
		
	}
	
	/**
	** Show each post
	** 	nb: called from pub/post
	** 
	*/
	public function showPosts($posts=false, $thread=false, $forum=false)
	{
		
		$posts 			= ( $posts ) ?: $this->posts;
		$this->thread 	= ( $thread ) ?: $this->thread;
		$this->forum 	= ( $forum ) ?: $this->forum;
		
		# Can we download attachments?
		$allow_attach	= cp::call('perm')->check( $this->forum, 'download' );
		
		foreach( $posts as $id => $post )
		{
			
			# Attachments...
			if ( $post['attachments'] )
			{
				
				$post['attach'] = true;
				
				if ( $allow_attach )
				{
					
					$a = explode(',', $post['attachments']);
					foreach ( $a as $aId )
					{
						
						# Get and create vars
						$ment = cp::db()->get('attachment', $aId);
						
						$ment['url'] = cp::display()->vars['lprefix'] . $ment['path'];
						
						cp::display()->vars['attach'] = $ment;
						$post['attachHtml'] .= cp::display()->read('attach_row');
									
					}
					
				}
				
			}
			
			# Post Variables
			$post['engTime']		= cp::display()->time2str( $post['postTime'] );			
			$post['postContent']	= cp::call('bbcode')->bb2html( $post['postContent'] );
			
			$post['path']			= $this->forum['path'];
			$post['path'][]		 	= $this->thread['slug'];
			$post['path'][]			= 'p_' . $post['id'] . '#post_all_' . $post['id'];
			
			# Permissions
			$post['can_post']	= cp::callAppClass('lib_perm')->canPost( $this->forum, $this->thread );
			$post['can_edit']	= cp::callAppClass('lib_perm')->editPost( $this->forum, $this->thread, $post );
			$post['can_del']	= cp::callAppClass('lib_perm')->delPost( $this->forum, $this->thread, $post );
			$post['can_rep']	= cp::callAppClass('lib_perm')->repPost( $this->forum, $this->thread, $post );
			
			# Vars
			cp::display()->vars['post']	= $post;
			cp::display()->vars['poster']	= cp::call('members')->prepMember( cp::db()->get( 'users', $post['posterId'] ), array('pips'=>true,'sig'=>true) );
			
			# Is this just a deleted post?
			if ( $post['deletedBy'] )
				cp::display()->vars['deleter'] = cp::call('members')->prepMember( cp::db()->get( 'users', $post['deletedBy'] ) );
			
			$thread['subHtml'] .= cp::display()->read('cat_post');
			
		}
		
		# Save to var
		cp::display()->vars['cat']	= $thread;
		
	}
	
	/**
	** Moderate Options
	*/
	public function mod()
	{
		
		if ( !cp::callAppClass('lib_perm')->isMod( $this->forum ) )
			return;
			
		cp::display()->vars['thread'] = $this->thread;
			
		cp::display()->vars['mod'] = array(
			'lock' 	=> cp::callAppClass('lib_perm')->lockThread( $this->forum, $this->thread ),
			'hide' 	=> cp::callAppClass('lib_perm')->hideThread( $this->forum, $this->thread ),
			'del'	=> cp::callAppClass('lib_perm')->delThread( $this->forum, $this->thread ),
			'move'	=> cp::callAppClass('lib_perm')->canMove( $this->forum, $this->thread ),
			'ren'	=> cp::callAppClass('lib_perm')->canRename( $this->forum, $this->thread ),
		);
			
		cp::display()->vars['cat']['extra'] .= cp::display()->read('cat_thread_mod');		
		
	}
	
	/**
	** Quick Reply
	*/
	public function quickReply()
	{
		
		if ( !cp::callAppClass('lib_perm')->replyThread( $this->forum, $this->thread ) )
			return;
		
		# JS
		cp::display()->jsLoad('editor')->jsLoad('minified/jquery.sceditor.bbcode.min');
		
		# Html
		cp::display()->vars['thread']['id'] 	= $this->thread['id'];
		cp::display()->vars['page']		 	= ( $this->page ) ?: '1';
		cp::display()->vars['cat']['extra'] .= cp::display()->read('cat_thread_postbox');
		
	}
	
	/**
	** 
	*/
	public function page()
	{
		
		# Output
		cp::cont()->page['table'] .= cp::display()->read('cat_cat');
		
		# Thread Path...
		$this->thread['path']   = $this->forum['path'];
		$this->thread['path'][] = $this->thread['slug'];
		
		# Set a page title
		cp::cont()->page['title'] = $this->thread['title'];
		
		# Thread Starter
		cp::display()->vars['starter'] 	= cp::call('members')->prepMember( cp::db()->get( 'users', $this->thread['starterId'] ) );
		cp::display()->vars['engStart']	= cp::display()->time2str( $this->thread['startTime'] );
		
		# Build Navtree
		$this->class_forums->navtree( $this->forum['id'] );
		
		# Title shrink
		$title = ( strlen( $this->thread['title'] ) < 50 ) ? $this->thread['title']: substr( $this->thread['title'], 0, 50 ) . '...';
		
		cp::cont()->navtree( $title );
		
		# Build Pagetree
		cp::cont()->page['tree'] = cp::call('dtools')->pagetree( $this->page, $this->thread['postCount'], cp::set('postsPerPage'), $this->thread['path'] );
		
		# Page stats
		cp::db()->driver->query("UPDATE `threads` SET `views` = " . ( $this->thread['views'] + 1 ) . " WHERE `id` = ".$this->thread['id']);	
		
		# Final var
		cp::display()->vars['thread'] = $this->thread;
		cp::display()->vars['forum'] 	= $this->forum;
		
		# Can Start Thread?	
		if ( cp::callAppClass('lib_perm')->startThread( $this->forum ) )
		{
			cp::cont()->page['buts'] .= cp::display()->read('buts/start_thread');
		}
		
		# Can Reply?	
		if ( cp::callAppClass('lib_perm')->canPost( $this->forum, $this->thread ) )
		{
			cp::display()->vars['can_reply'] = cp::callAppClass('lib_perm')->replyThread( $this->forum, $this->thread );
			cp::cont()->page['buts'] .= cp::display()->read('buts/post_reply');
		}
		
		cp::cont()->page['imgs'] = cp::display()->read('buts/thread_imgs');
		
	}
	
	/**
	** Get from post IDs... only used by ajax functions below
	*/
	public function ajax_from_post_id( $postId )
	{
		
		/**
		** Get Thread and forum...
		*/
		if ( !$this->post = cp::db()->get( 'posts', $postId ) )
			return cp::call('ajax')->error('Post not found');	
		
		if ( !$this->thread = cp::db()->get( 'threads', $this->post['threadId'] ) )
			return cp::call('ajax')->error('Thread not found');		
			
		$this->forum = cp::db()->fetch_dep( array(
			'table'	=> 'forums',
			'where'	=> 'id="'.$this->thread['forumId'].'" AND '.cp::call('perm')->allowMe( 'read' ),
			'join'	=> array(
				'table'	=> 'perm_reg',
				'where'	=> 'perm_reg.type="forum" AND forums.id=perm_reg.type_id',
				'type'	=> 'left',
			),
			'order'	=> '`order` asc',
			'one'	=> true,
		));	
			
		if ( !$this->forum )
			return cp::call('ajax')->error('Forum not found');
		
		return true;
			
	}
	
	/**
	** ajax_edit
	*/
	public function ajax_post_history( $postId )
	{
		
		/**
		** Also checks we can read this post...
		*/
		if ( !$this->ajax_from_post_id($postId) )
			return false;
			
		if ( !$this->post['history'] )
		{
		}
		else
		{
			
			$history = unserialize($this->post['history']);
			
			foreach( $history as $array )
			{
				$getIds[] = $array['by'];
			}
			
			$members = cp::call('members')->fetchAndPrep( array( 'id' => $getIds ) );
			
			foreach( $history as $edit => $array )
			{
				$edit++;
				cp::display()->vars['edit'] 	= $edit;
				cp::display()->vars['type'] 	= cp::lang('all', $array['type'].'ed');
				cp::display()->vars['by'] 	= $members[ $array['by'] ];
				cp::display()->vars['engOn'] 	= cp::display()->time2str( $array['time'] );
				cp::display()->vars['reason'] = $array['reason'];
				cp::display()->vars['history_rows'] .= cp::display()->read('popups/posthistory_row');
			}
			
		}
		
		/**
		** Return
		*/
		cp::call('ajax')->ret['pop'] 	= true;
		cp::call('ajax')->ret['swop'] 	= '.ajax_white';
		cp::call('ajax')->ret['html'] 	= cp::display()->read('popups/posthistory');
		
	}
	
	/**
	** Get forum and thread (ajax)
	*/
	public function get_forum_thread($threadId)
	{
		
		/**
		** Get Thread and forum...
		*/		
		if ( !$this->thread = cp::db()->get( 'threads',$threadId ) )
			return cp::call('ajax')->error('Thread not found');		
			
		$this->forum = cp::db()->fetch_dep( array(
			'table'	=> 'forums',
			'where'	=> 'id="'.$this->thread['forumId'].'" AND '.cp::call('perm')->allowMe( 'read' ),
			'join'	=> array(
				'table'	=> 'perm_reg',
				'where'	=> 'perm_reg.type="forum" AND forums.id=perm_reg.type_id',
				'type'	=> 'left',
			),
			'order'	=> '`order` asc',
			'one'	=> true,
		));	
			
		if ( !$this->forum )
			return cp::call('ajax')->error('Forum not found');
		
		return true;
		
	}
	
	/**
	** Hide a thread
	*/
	public function ajax_hide($threadId)
	{
		
		$this->get_forum_thread($threadId);
		
		/**
		** Permissions
		*/
		if ( !cp::callAppClass('lib_perm')->hideThread( $this->forum, $this->thread ) )
			return false;
		
		/**
		** Delete Thread
		*/
		cp::callAppClass('class_thread')->delete($threadId);		
		cp::call('ajax')->ret['refresh'] = true;
		
	}
	
	/**
	** hard delete a thread
	*/
	public function ajax_delete($threadId)
	{
		
		$this->get_forum_thread($threadId);
		
		/**
		** Permissions
		*/
		if ( !cp::callAppClass('lib_perm')->delThread( $this->forum, $this->thread ) )
			return false;
		
		/**
		** Delete Thread
		*/
		cp::callAppClass('class_thread')->delete($threadId, 'hard');		
		cp::call('ajax')->ret['refresh'] = true;
		
	}
	
	/**
	** restore a thread
	*/
	public function ajax_restore($threadId)
	{
		
		$this->get_forum_thread($threadId);
		
		/**
		** Permissions
		*/
		if ( !cp::callAppClass('lib_perm')->delThread( $this->forum, $this->thread ) )
			return false;
			
		/**
		** Delete Thread
		*/
		cp::callAppClass('class_thread')->restore($threadId);		
		cp::call('ajax')->ret['refresh'] = true;
		
	}
	
	/**
	** 
	*/
	public function ajax_lock($threadId)
	{
		
		$this->get_forum_thread($threadId);
		
		/**
		** Permissions
		*/
		if ( !cp::callAppClass('lib_perm')->lockThread( $this->forum, $this->thread ) )
			return false;
		
		/**
		** Lock Thread
		*/
		cp::db()->update_dep( 'threads', $threadId, array( 'isLocked'=>1 ) );
		cp::call('ajax')->ret['refresh'] = true;
		
	}
	
	/**
	** 
	*/
	public function ajax_unlock($threadId)
	{
		
		$this->get_forum_thread($threadId);
		
		/**
		** Permissions
		*/
		if ( !cp::callAppClass('lib_perm')->lockThread( $this->forum, $this->thread ) )
			return false;
		
		/**
		** Lock Thread
		*/
		cp::db()->update_dep('threads', $threadId, array('isLocked'=>0) );
		cp::call('ajax')->ret['refresh'] = true;
		
	}
	
	/**
	** Move thread
	*/
	public function ajax_move($id=false)
	{
		
		if ( !is_array($id) )
		{
			cp::display()->vars['cmd_mod'] = ',' . $id;
		}
		
		/**
		** Load, get readable, loop to create drop down.
		*/
		$this->class_forums = cp::callAppClass( 'class_forums' );		
		$this->class_forums->retrieveReadable();		
		$this->class_forums->loopForums( '0', $this, 'move_callback', true );
			
		/**
		** Confirmation
		*/
		cp::display()->vars['thread_id'] = $threadId;
		cp::call('ajax')->popup( cp::display()->read('popups/thread_move') );
		
	}
	
	public function move_callback($forum)
	{
		
		# Are we a mod of this forum?
		if ( !cp::callAppClass('lib_perm')->isMod( $forum ) )
			return;
		
		# Create Indent
		$i = 1;
		$indent = false;
		while( cp::call('class_forums')->depth != $i )
		{
			$indent .= '-';
			$i++;
		}
		
		$forum['name'] = $indent . $forum['name'];
		
		cp::display()->vars['forum']	= $forum;
		cp::display()->vars['options'] .= cp::display()->read('popups/thread_move_row');
		
	}
	
	public function ajax_move_lots($thread=false)
	{
		
		$threads 	= ( $thread ) ? array( $thread ): cp::$POST['selectArray'];
		$new_forum	= cp::$POST['moredata'];
		
		/**
		** Get threads we're moving...
		*/
		cp::db()->fetch_dep( array(
			'select'=> 'id, forumId, visible, postCount, slug',
			'table' => 'threads',
			'where' => array('id'=>$threads),
		) );
		
		if ( !$this->thread = cp::db()->get( 'threads', $threads['0'] ) )
			return cp::call('ajax')->error('Thread not found');	
		
		$this->class_forums = cp::callAppClass( 'class_forums' );		
		$this->class_forums->retrieveReadable();
		
		$this->forum = $this->class_forums->forum_array[ $this->thread['forumId'] ];
		$next_forum	 = $this->class_forums->forum_array[ $new_forum ];
		
		/*$this->get_forum_thread($threads['0']);
		
		$next_forum = cp::db()->fetch_dep( array(
			'table'	=> 'forums',
			'where'	=> 'id="'.$new_forum.'" AND '.cp::call('perm')->allowMe( 'read' ),
			'join'	=> array(
				'table'	=> 'perm_reg',
				'where'	=> 'perm_reg.type="forum" AND forums.id=perm_reg.type_id',
				'type'	=> 'left',
			),
			'order'	=> '`order` asc',
			'resono'=> true,
		));*/
		
		

		/**
		** Permissions
		*/
		if ( !cp::callAppClass('lib_perm')->canMove( $this->forum, $this->thread ) )
			return false;
			
		if ( !cp::callAppClass('lib_perm')->canMoveTo( $next_forum, $this->thread ) )
			return cp::call('ajax')->error_pop( cp::lang('all', 'forum_pro') );

		/**
		** Counter init
		*/
		$last_forum = array(
			'id' 				=> $this->forum['id'],
			'threadCount'		=> $this->forum['threadCount'],
			'hiddenThreadCount' => $this->forum['hiddenThreadCount'],
			'postCount'			=> $this->forum['postCount'],
		);
		$next_forum = array(
			'id' 				=> $next_forum['id'],
			'threadCount'		=> $next_forum['threadCount'],
			'hiddenThreadCount' => $next_forum['hiddenThreadCount'],
			'postCount'			=> $next_forum['postCount'],
		);
			
		/**
		** Move Thread
		*/
		foreach( $threads as $id )
		{
			
			$cur = cp::db()->get( 'threads', $id );
			
			/**
			** Thread Counts
			*/
			if ( $cur['visible'] == 1 )
			{
				$last_forum['threadCount'] = $last_forum['threadCount'] - 1;
				$next_forum['threadCount'] = $next_forum['threadCount'] + 1;
			}
			else
			{
				$last_forum['hiddenThreadCount'] = $last_forum['hiddenThreadCount'] - 1;
				$next_forum['hiddenThreadCount'] = $next_forum['hiddenThreadCount'] + 1;
			}
			
			$last_forum['postCount'] = $last_forum['postCount'] - $cur['postCount'];
			$next_forum['postCount'] = $next_forum['postCount'] + $cur['postCount'];
				
			/**
			** Update Thread
			*/
			cp::db()->update_dep( 'threads', $id, array( 'forumId' => $next_forum['id'] ) );
			
		}
		
		/**
		** Update Forums
		*/
		cp::db()->update_dep( 'forums', $last_forum['id'], $last_forum );
		cp::db()->update_dep( 'forums', $next_forum['id'], $next_forum );
		
		if ( $thread )
		{
			$new_path 	= $this->class_forums->make_path( $next_forum['id'] );
			$new_path[] = $this->thread['slug'];
			cp::call('ajax')->ret['redirect'] = cp::link($new_path);
		}
		else
		{
			cp::call('ajax')->ret['refresh'] = true;
		}
		
	}
	
	/**
	** Rename thread
	** 
	** @param	int		$threadId
	*/
	public function ajax_rename( $threadId )
	{
		
		$this->get_forum_thread($threadId);
		
		/**
		** Permissions
		*/
		if ( !cp::callAppClass('lib_perm')->canRename( $this->forum, $this->thread ) )
			return false;
			
		/**
		** Confirmation
		*/
		cp::display()->vars['thread'] = $this->thread;
		cp::call('ajax')->popup( cp::display()->read('popups/thread_rename') );		
		
	}
	
	/**
	** Rename thread (run) after confirmation
	** 
	** @param	int		$threadId
	*/
	public function ajax_rename_conf( $threadId )
	{
		
		$this->get_forum_thread($threadId);
		
		$post = cp::db()->get('posts', $this->thread['firstPost']);
		
		/* Permissions */
		if ( !cp::callAppClass('lib_perm')->canRename( $this->forum, $this->thread ) )
			return false;
			
		/* New title */
		$new_title = cp::$POST['moredata'];
		
		/* Post history */
		
		if ( $post['history'] )
			$history = unserialize( $post['history'] );
		else
			$history = array();
			
		$history[] = array(
			'type'	=> 'rename',
			'by'	=> cp::logged()->cur['id'],
			'time'	=> cp::$time,
		);
		
		/* Update DB */
		cp::db()->update( 'threads', $this->thread['id'], array('title'=>$new_title) );
		cp::db()->update( 'posts', $post['id'], array('history' => serialize( $history )) );
		
		/* */
		cp::call('ajax')->ret['refresh'] = true;	
		
	}
	
	
}

?>