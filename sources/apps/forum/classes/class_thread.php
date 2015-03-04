<?php

	//===================================================
	//	Cipher Pixel Board © All Rights Reserved
	//---------------------------------------------------
	//	CP.Board
	//		by michaelbenner.net
	//---------------------------------------------------
	//	File created: April 04, 2013 
	//===================================================

class class_thread {
	
	/**
	** Thread move, delete, restore etc
	** Create thread is contained in class_post
	*/
	
	/**
	** When showing a forum, records the if we've read everything
	*/
	public $read	= true;
	
	/**
	** show() - builds a whole bunch of "cat_thread"
	** 
	** @param	array	$threads	Array of threads
	** @param	array	$forums		Forum array
	** @return	string
	*/
	public function show( $threads, $forums )
	{
		
		foreach ( $threads as $id => $thread )
		{
			
			$forum = $forums[ $thread['forumId'] ];
			
			if ( !$r )
				$thread['first'] = true;
			
			# Display Changes	
			$thread['startT'] 	= cp::display()->time2str( $thread['startTime'] );
			$thread['lastT']	= cp::display()->time2str( $thread['lastPostTime'] );
			$thread['title'] 	= ( strlen( $thread['title'] ) < 200 ) ? $thread['title']: substr( $thread['title'], 0, 200 ) . '...';
			
			$thread['read'] = ( cp::logged()->read('forum', $forum['id'], '', true ) > $thread['lastPostTime'] ) ? true: cp::logged()->read( 'thread', $thread['id'], $thread['lastPostTime'] );
			
			# Set as unread if we have threads needing attention
			if ( !$thread['read'] AND $this->read ) $this->read = false;
			
			$thread['path']		= $forum['path'];
			$thread['path'][]	= $thread['slug'];
			
			cp::display()->vars['thread']		= $thread;
			cp::display()->vars['starter']	= cp::call('members')->prepMember( cp::db()->get( 'users', $thread['starterId'] ) );
			cp::display()->vars['last']		= cp::call('members')->prepMember( cp::db()->get( 'users', $thread['lastPostUser'] ) );
			
			$r .= cp::display()->read('cat_thread');
			
		}
		
		return $r;
		
	}
	
	/**
    ** visible() - builds the "where" clause for visibility of threads
    ** 
    ** @param	array	$forum	Array of forum
    ** @return	array
    */
    public function visible( $forum=false )
    {
	    
	    if ( cp::callAppClass('lib_perm')->isMod( $forum ) )
		{
			$vis = 'visible < 2';
			$forum['threadCount'] += $forum['hiddenThreadCount'];
		}
		else
		{
			$vis 		 = 'visible=1';
		}
		
		return array(
			'vis_str'	  => $vis,
			'threadCount' => $forum['threadCount'],
		);
	    
    }
	
	/**
	** delete() - soft delete a thread
	** 
	** @param	int		$threadId	Id of the thread to delete
	** @param	string	$type		hard/soft(hide)
	*/
	public function delete($threadId, $type='soft')
	{
		
		$thread = cp::db()->get( 'threads', $threadId );
		
		/**
		** Get thread's forum
		*/
		$forum = ( cp::callAppClass('class_forums')->forum_array[ $thread['forumId'] ] ) ?: cp::db()->get( 'forums', $thread['forumId'] );
		$new_forum = array();
		
		/**
		** Are we the last thread?
		*/
		if ( $forum['lastPostThread'] == $thread['id'] )
		{
			
			$last_thread = cp::db()->fetch_dep( array(
				'table'	=> 'threads',
				'where'	=> 'id != '.$thread['id'].' AND forumId='.$forum['id'].' AND visible=1',
				'order'	=> 'lastPostTime desc',
				'resono'=> true,
			) );
			
			$new_forum = array(
				'lastPostTime' 	=> $last_thread['lastPostTime'],
				'lastPostUser' 	=> $last_thread['lastPostUser'],
				'lastPostThread'=> $last_thread['id'],
			);
			
		}
		
		/* Unapprove */
		if ( $type == 'soft' )
		{
			cp::db()->update_dep( 'threads', $thread['id'], array('visible'=>0) );
			$new_forum['hiddenThreadCount']	= '+1';
		}
		
		if ( $type == 'hard' )
		{
			cp::db()->update_dep( 'threads', $thread['id'], array('visible'=>2) );
		}
		
		$new_forum['threadCount'] 	= '-1';
		$new_forum['postCount']		= ( $forum['postCount'] - $thread['postCount'] );
		
		cp::db()->update_dep( 'forums', $forum['id'], $new_forum );
		
		/* Update User Thread Count */
		cp::db()->update_dep( 'users', $thread['starterId'], array('threadCount' => '-1') );		
		
	}
	
	/**
	** restore() - restore a thread
	** 
	** @param	int	$threadId	Id of thread
	*/
	public function restore($threadId)
	{

		$thread = cp::db()->get( 'threads', $threadId );
		
		if ( $thread['visible'] === '1' )
		{
			return true;
		}
		
		/**
		** Get thread's forum
		*/
		$forum = ( cp::callAppClass('class_forums')->forum_array[ $thread['forumId'] ] ) ?: cp::db()->get( 'forums', $thread['forumId'] );
		$new_forum = array();
		
		/**
		** Are we more recent?
		*/
		if ( $thread['lastPostTime'] > $forum['lastPostTime'] )
		{
			
			$new_forum = array(
				'lastPostTime' 	=> $thread['lastPostTime'],
				'lastPostUser' 	=> $thread['lastPostUser'],
				'lastPostThread'=> $thread['id'],
			);
			
		}
		
		$new_forum['hiddenThreadCount']	= '-1';
		$new_forum['threadCount'] 		= '+1';
		$new_forum['postCount']			= ( $forum['postCount'] + $thread['postCount'] );	
		
		cp::db()->update_dep( 'forums', $forum['id'], $new_forum );		
		cp::db()->update_dep( 'threads', $thread['id'], array('visible'=>1) );
		
		/* Update User Thread Count */
		cp::db()->update_dep( 'users', $thread['starterId'], array('threadCount' => '+1') );
		
	}	
	
}

?>