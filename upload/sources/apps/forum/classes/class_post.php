<?php

	//===================================================
	//	Cipher Pixel Board © All Rights Reserved
	//---------------------------------------------------
	//	CP.Board
	//		by michaelbenner.net
	//---------------------------------------------------
	//	File created: April 04, 2013 
	//===================================================

class class_post {
	
	/**
	** Current Forum to post to
	*/
	public	$forum;
	
	/**
	** insert() - Insert post into DB, permissions should already be done
	** 
	** @param	array		$post		array of post information
	** 							'threadId'	= thread id to post into
	** 							'posterId'	= member id (def=current)
	** 							'content'	= content to save.
	** 							'postTime'	= current post time (def=now)
	** @param	array/bool	$thread		array of thread
	** @param	bool		$new_thread	whether we are creating a new post
	*/
	public function insert( $post, $thread=false, $new_thread=false )
	{		
		
		//-----------------------------------
		// Build arrays
		//-----------------------------------
		
		$thread = ( !$thread ) ? cp::db()->get( 'threads', $post['threadId'] ): $thread;
		$new = array();
		
		if ( !$thread )
			return;
		
		$post['posterId'] 	= ( $post['posterId'] ) ?: cp::logged()->cur['id'];	
		$post['postTime'] 	= ( $post['postTime'] ) ?: cp::$time;
		$post['postContent']= ( $post['content'] ) ?: $post['postContent'];
		$add_noti			= $post['add_noti'];
		
		unset( $post['add_noti'] );		
		unset( $post['content'] );
		
		//-----------------------------------
		// Attachments
		//-----------------------------------
		
		if ( $post['attach'] )
		{
			
			$attachments = explode( ',',  $post['attach'] );
			
			foreach( $attachments as $num )
			{
				if ( !is_numeric( $num ) ) continue;
				$checked[] = $num;
			}
			
			$post['attachments'] = implode(',', $checked);
			
		}
		
		unset( $post['attach'] );

		//-----------------------------------
		// Insert Post
		//-----------------------------------
				
		$post_id = cp::db()->insert( 'posts', $post );
		
		//-----------------------------------
		// Build Thread Array to update/insert
		//-----------------------------------
		
		if ( !$thread['firstPost'] )
			$new['firstPost'] = $post_id;
			
		$new['lastPostTime'] = $post['postTime'];		
		$new['lastPost'] 	 = $post_id;
		$new['postCount']	 = '+1';
		$new['lastPostUser'] = $post['posterId'];
		
		//-----------------------------------
		// Tag People
		//-----------------------------------
		
		preg_match_all( '/\[@((?s).*?)\]/', $post['postContent'], $tagByDisplayName );
		
		cp::db()->fetch_dep( array(
			'table'	=> 'users',
			'where' => array('displayName' => $tagByDisplayName['1'] ),
			'record'=> 'id',
		) );
		
		if ( cp::db()->record )
		{
		
			foreach( cp::db()->record as $uId )
			{
				cp::db()->insert( 'notifications', array( 
					'user_id' 	=> $uId,
					'from'		=> cp::logged()->cur['id'],
					'read'		=> 0,
					'type'		=> 'post_tag',
					'time'		=> cp::$time,
					'thread_id'	=> $post['threadId'],
					'post_id'	=> $post_id,
				) );
			}
			
			cp::db()->update_dep( 'users', cp::db()->record, array('unreadNoti'=>'+1'), 'id', false, 'OR' );
			
		}
		
		//-----------------------------------
		// Notifications
		//-----------------------------------
		
		/* Notification Arrays */
		$thread_noti = ( $thread['notiArray'] ) ? json_decode( $thread['notiArray'], true ): false;
		$user_noti = ( cp::logged()->cur['notiArray'] ) ? json_decode( cp::logged()->cur['notiArray'], true ): false;
		
		/* Merge */		
		$alert_ids = cp::merge( $thread_noti['new_post'], $user_noti['sub'] );
		
		if ( is_array( $alert_ids ) )
		{
			cp::call('members')->notify( $alert_ids, array(
				'from'		=> cp::logged()->cur['id'],
				'read'		=> 0,
				'type'		=> 'new_post',
				'time'		=> cp::$time,
				'thread_id'	=> $post['threadId'],
				'post_id'	=> $post_id,
			) );
		}
		
		/* Update noti array? */
		if ( $add_noti )
		{
			$new['notiArray'] = json_encode(array_merge( $add_noti, $notiArray ));			
		}
					
		//-----------------------------------
		// Update extra tables
		//-----------------------------------
		
		/* Update Threads */		
		cp::db()->update_dep( 
			'threads',
			$post['threadId'],
			$new
		);
		
		/* Update Current Poster */
		
		$user_update = array();
		$user_update['postCount'] = '+1';
		
		if ( $new_thread ) $user_update['threadCount'] = '+1';
			
		cp::db()->update_dep( 'users', $post['posterId'], $user_update );
		
		/* Update Forum */
		
		$new_forum = array(
			'lastPostTime' 	=> $post['postTime'],
			'lastPostThread'=> $thread['id'],
			'lastPostUser' 	=> $post['posterId']
		);
		
		/* Post Counters */
		if ( $post['visible'] == '1' )
		{
			$new_forum['postCount'] = '+1';
		}
		elseif ( $post['visible'] == '0' )
		{
			$new_forum['hiddenCount'] = '+1';
		}
		
		if ( $new_thread AND ( $thread['visible'] == '1' ) )
		{
			$new_forum['threadCount'] = '+1';
		}
		elseif ( $new_thread AND ( $thread['visible'] == '0' ) )
		{
			$new_forum['hiddenCount'] = '+1';
		}
		
		cp::db()->update_dep( 'forums', $thread['forumId'], $new_forum );
		
		/* Stats */
			
		cp::db()->update_dep(
			'miscstat',
			array(
				'arrayTitle' => 'totalPosts',
			),
			array(
				'value' => '+1'
			), 
			'', false, 'OR'
		);
		
		return $post_id;		
		
	}
	
	/**
	** delete() - Soft delete a post.
	** 
	** @postId	= ID of post to remove
	** @type	= soft or hard delete?
	*/
	public function delete( $postId, $type='soft' )
	{
		
		$post 	= cp::db()->get( 'posts', $postId );
		$thread = cp::db()->get( 'threads', $post['threadId'] );
		$forum	= cp::db()->get( 'forums', $thread['forumId'] );
		
		# Were we the last post?
		if ( $thread['lastPost'] == $post['id'] )
		{

			$check_last_forum_thread = true;
			
			$lastPostInThread = cp::db()->fetch_dep( array(
				'table'	=> 'posts',
				'where'	=> 'threadId="'.$thread['id'].'" AND id != "'.$post['id'].'" AND visible=1',
				'limit'	=> '1',
				'resono'=> true,
				'order'	=> 'postTime desc',
			) );
			
			# Update thread to reflect...
			$new_thread = array(
				'lastPostTime' 	=> $lastPostInThread['postTime'],		
			 	'lastPost'		=> $lastPostInThread['id'],
				'lastPostUser'  => $lastPostInThread['posterId'],
			);
			
		}
		
		$new_thread['postCount'] = '-1';
		
		if ( $type=='soft')
		{
			$new_thread['hiddenCount'] = '+1';
		}
		
		# Update Thread
		cp::db()->update_dep( 
			'threads',
			$thread['id'],
			$new_thread
		);
		
		# if we were last in thread, we may be last in forum. Were we last post in forum?
		if ( $check_last_forum_thread AND ( $thread['id'] == $forum['lastPostThread'] ) )
		{
			
			$lastThreadInForum = cp::db()->fetch_dep( array(
				'table'	=> 'threads',
				'where'	=> 'forumId="'.$forum['id'].'"',
				'limit'	=> '1',
				'one'	=> true,
				'order'	=> 'lastPostTime desc',
			) );
			
			$new_forum = array(
				'lastPostTime'	=> $lastThreadInForum['lastPostTime'],
				'lastPostUser'	=> $lastThreadInForum['lastPostUser'],
				'lastPostThread' => $lastThreadInForum['id'],
			);
			
		}
		
		$new_forum['postCount']	= '-1';
		
		cp::db()->update_dep(
			'forums',
			$forum['id'],
			$new_forum
		);
		
		cp::db()->update_dep( 'miscstat', array('arrayTitle' => 'totalPosts'), array('value'=>'-1') );
		
		/**
		** Update post history
		*/
		if ( $post['history'] )
			$history = unserialize( $post['history'] );
		else
			$history = array();
			
		$history[] = array(
			'type'	=> 'del',
			'by'	=> cp::logged()->cur['id'],
			'time'	=> cp::$time,
			'reason'=> cp::$POST['moredata'],
		);	
		
		# Delete Post by changing vis (soft!)
		if ( $type == 'soft' )
		{
			cp::db()->update_dep(
				'posts',
				$post['id'],
				array( 'visible' => 2, 'history' => serialize( $history ), 'deletedBy' => cp::logged()->cur['id'] )
			);
		}
		
	}
	
	/**
	** restore() - restore a deleted post
	** 
	** @postId	= ID of post to remove
	** @type	= soft or hard delete?
	*/
	public function restore( $postId )
	{
		
		$post 	= cp::db()->get( 'posts', $postId );
		$thread = cp::db()->get( 'threads', $post['threadId'] );
		$forum	= cp::db()->get( 'forums', $thread['forumId'] );
		
		# Are we thte last post?
		if ( $thread['lastPostTime'] < $post['postTime'] )
		{

			$check_last_forum_thread = true;
			
			# Update thread to reflect...
			$new_thread = array(
				'lastPostTime' 	=> $post['postTime'],		
			 	'lastPost'		=> $post['id'],
				'lastPostUser'  => $post['posterId'],
			);
			
		}
		
		$new_thread['postCount'] = '+1';
		$new_thread['hiddenCount'] = '-1';
		
		# Update Thread
		cp::db()->update_dep( 
			'threads',
			$thread['id'],
			$new_thread
		);
		
		# if we were last in thread, we may be last in forum. Were we last post in forum?
		if ( $check_last_forum_thread AND ( $post['postTime'] > $forum['lastPostTime'] ) )
		{
			
			$new_forum = array(
				'lastPostTime'	=> $post['postTime'],
				'lastPostUser'	=> $post['posterId'],
				'lastPostThread' => $thread['id'],
			);
			
		}
		
		$new_forum['postCount']	= '+1';
		
		cp::db()->update_dep(
			'forums',
			$forum['id'],
			$new_forum
		);
		
		cp::db()->update_dep( 'miscstat', array('arrayTitle' => 'totalPosts'), array('value'=>'+1') );
		
		/**
		** Update post history
		*/
		if ( $post['history'] )
			$history = unserialize( $post['history'] );
		else
			$history = array();
			
		$history[] = array(
			'type'	=> 'rest',
			'by'	=> cp::logged()->cur['id'],
			'time'	=> cp::$time,
			'reason'=> cp::$POST['moredata'],
		);	
		
		cp::db()->update_dep(
			'posts',
			$post['id'],
			array( 'visible' => 1, 'history' => serialize( $history ), 'deletedBy' => 0 )
		);
		
	}
	
	/**
	** update() - updates a post.
	**
	** @post = array(
	** 	 'id' 	  = id of post to update
	** 	 'content'= content to save.
	** @edit_history = whether to update the edit history
	** 	 'by'	  = ID of person who edited this post (def: cur)
	**   'time'	  = Time post was edited (def: cur)
	**   'reason' = reason for update (def: null)
	*/
	public function update( $post, $edit_history=true )
	{
		
		# Do we want to update the edit history?
		if ( $edit_history )
		{
			$old = cp::db()->get( 'posts', $post['id'] );
			
			# Is there already history?
			if ( $old['history'] )
				$history = unserialize( $old['history'] );
			else
				$history = array();
				
			$history[] = array(
				'type'	=> 'edit',
				'by'	=> ( $edit_history['by'] ) ?: cp::logged()->cur['id'],
				'time'	=> ( $edit_history['time'] ) ?: cp::$time,
				'reason'=> $edit_history['reason'],
			);
			
			$new['history'] = serialize( $history );
			
		}
		
		# Vars
		$new['postContent'] = $post['content']; 
	
		# Query
		cp::db()->update_dep( 'posts', $post['id'], $new );
		
	}
	
	/**
	** create_thread() - inserts a thread, then inserts post
	**
	** @post	= array(
	** 		'forumId'	= forum id to insert into
	** 		'title'		= thread title
	** 		'posterId'	= member id (def=current)
	** 		'content'	= content to save.
	** 		'postTime'	= current post time (def=now)
	** 		'visible'	= visibility setting
	*/
	public function create_thread( $post )
	{
		
		/**
		** Check Thread
		*/
		if ( !$post['title'] ) 	return false;
		if ( !$post['forumId'] ) return false;
		
		$post['posterId'] = ( $post['posterId'] ) ?: cp::logged()->cur['id'];
		
		/**
		** Make Thread
		*/
		$thread = array(
			'title'			=> $post['title'],
			'slug'			=> cp::db()->slugify( $post['title'], array('threads', 'forums') ),
			'forumId' 		=> $post['forumId'],
			'starterId' 	=> $post['posterId'],
			'startTime' 	=> cp::$time,
			'visible'		=> $post['visible'],
		);
		
		/**
		** Unset thread only vars...
		*/
		unset( $post['title'] );
		unset( $post['forumId'] );
		
		$post['visible'] = 1;
		
		/**
		** Insert thread, get ID
		*/
		$thread['id'] = cp::db()->insert( 'threads', $thread );		
				
		$post['threadId'] = $thread['id'];
		
		/**
		** Insert post...
		*/
		$post_id = $this->insert( $post, $thread, true );
		
		/**
		** One, last, counter...
		*/	
		cp::db()->update_dep(
			'miscstat',
			array(
				'arrayTitle' => 'totalThreads',
			),
			array(
				'value' => '+1'
			), 
			'', false, 'OR'
		);

		return $thread;
		
	}
	
}

?>