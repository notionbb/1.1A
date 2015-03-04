<?php

	//===================================================
	//	Cipher Pixel Board © All Rights Reserved
	//---------------------------------------------------
	//	CP.Board
	//		by michaelbenner.net
	//---------------------------------------------------
	//	File created: April 04, 2013 
	//===================================================

class lib_perm {
	
	/**
	** Function List:
	**
	** & isMod( forum_array )
	** & startThread( forum_array )
	** & replyThread( forum_array, thread_array )
	** & canPost( forum_array, thread_array )
	** & editPost( forum_array, thread_array, post_array )
	** & delPost( forum_array, thread_array, post_array )
	** & repPost( forum_array, thread_array, post_array )
	** & lockThread( forum_array, thread_array )
	** & hideThread( forum_array, thread_array ) 
	** & delThread( forum_array, thread_array ) 
	** & canMove( forum_array, thread_array ) 
	** & canMoveTo( next_forum_array thread_array )
	** & canRename( forum_array, thread_array ) 
	** @ canEditMem( member_array )
	*/
	
	/**
	** isMod() - check if user is a moderator on forum. If forum array is empty check if global mod
	**
	** @forum_array - array of forum
	*/
	public function isMod( $forum_array=false )
	{
		
		if ( cp::logged()->cur['globalMod'] )
			return true;
		
		return false;
	}
	
	/**
	** startThread() - Can start thread in forum?
	** 
	** @forum_array	= array of forum with perm table
	*/
	public function startThread( $forum_array )
	{
		
		/* Check ban */
		if( cp::call('perm')->ban_check('post') )
			return false;
		
		# Does this forum allow threads?
		if ( !$forum_array['allowThreads'] )
			return false;
			
		# Build permissions...
		if ( !cp::call('perm')->check( $forum_array, array('read', 'start') ) )
			return false;

		return true;		
		
	}
	
	/**
	** replyThread() - can we reply to a thread?
	**
	** @forum_array		= array of forum with perm table
	** @thread_array	= array of thread
	*/
	public function replyThread( $forum_array, $thread_array )
	{

		/* Check ban */
		if( cp::call('perm')->ban_check('post') )
			return false;
		
		# Can post in forum
		if ( !$this->canPost( $forum_array, $thread_array ) )
			return false;
			
		# Is thread locked?
		if ( $thread_array['isLocked'] )
		{
			
			# Is user a moderator?
			if ( !$this->isMod( $forum_array ) )
				return false;
				
			# Can moderator post in lock?
			if ( !cp::logged()->cur['canPostInLock'] )
				return false;
			
		}
			
		return true;
		
	}
	
	/**
	** canPost() - can we post in this thread, ignore if its locked
	** 
	** @forum_array		= array of forum with perm table
	** @thread_array	= array of thread
	*/
	public function canPost( $forum_array, $thread_array )
	{
		
		# Can post in forum
		if ( !cp::call('perm')->check( $forum_array, array('read', 'post') ) )
			return false;
			
		return true;
		
	}
	
	/**
	** editPost() - can we edit this post
	** 
	** @forum_array		= array of forum with perm table
	** @thread_array	= array of thread
	** @post_array		= array of post
	*/
	public function editPost( $forum_array, $thread_array, $post_array )
	{
		
		# Can read forum?
		if ( !cp::call('perm')->check( $forum_array, array('read') ) )
			return false;
			
		# Are we a moderator?
		if ( $this->isMod( $forum_array ) )
		{
			
			# Can we edit posts in forums we moderate?
			if ( cp::logged()->cur['canEditPosts'] )
				return true;
			
		}
		
		# Is this our own post?
		if ( $post_array['posterId'] == cp::logged()->cur['id'] )
		{
			
			# Can we edit our own posts?
			if ( cp::logged()->cur['canEditOwnPosts'] )
				return true;
			
		}
		
		return false;
		
	}
	
	/**
	** delPost() - can we delete this post?
	** 
	** @forum_array		= array of forum with perm table
	** @thread_array	= array of thread
	** @post_array		= array of post
	*/
	public function delPost( $forum_array, $thread_array, $post_array )
	{
		
		# Can read forum?
		if ( !cp::call('perm')->check( $forum_array, array('read') ) )
			return false;
			
		# Are we a moderator?
		if ( $this->isMod( $forum_array ) )
		{
			
			# Can we edit posts in forums we moderate?
			if ( cp::logged()->cur['canDeletePosts'] )
				return true;
			
		}
		
		# Is this our own post?
		if ( $post_array['posterId'] == cp::logged()->cur['id'] )
		{
			
			# Can we edit our own posts?
			if ( cp::logged()->cur['canDeleteOwnPosts'] )
				return true;
			
		}
		
		return false;
		
	}
	
	/**
	** repPost() - can we report this post?
	** 
	** @forum_array		= array of forum with perm table
	** @thread_array	= array of thread
	** @post_array		= array of post
	*/
	public function repPost( $forum_array, $thread_array, $post_array )
	{
		
		# Can this group file reports...
		if ( !cp::logged()->cur['canRep'] )
			return false;
			
		# Can you report yourself?...
		# Yes... maybe you want something deleted...
			
		return true;
		
	}
	
	/**
	** lockThread() - can we lock the thread?
	** 
	** @forum_array
	** @thread_Array
	*/
	public function lockThread( $forum_array, $thread_array )
	{
		
		# Is mod and can delete own
		if ( $this->isMod( $forum_array ) AND cp::logged()->cur['canLock'] )
		{			
			return true;			
		}
		
		# Can delete own and is own thread
		if ( cp::logged()->cur['canLockOwn'] AND ( $thread_array['starter_id'] == cp::logged()->cur['id'] ) )
		{
			return true;
		}
		
		return false;
		
	}
	
	/**
	** hideThread() - can we lock the thread?
	** 
	** @param	array	$forum_array
	** @param	array	$thread_array
	** @return	boolean
	*/
	public function hideThread( $forum_array, $thread_array=false )
	{
		
		# Is mod and can delete own
		if ( $this->isMod( $forum_array ) AND cp::logged()->cur['canHide'] )
		{			
			return true;			
		}
		
		return false;
		
	}
	
	/**
	** delThread() - can we lock the thread?
	** 
	** @forum_array
	** @thread_Array
	*/
	public function delThread( $forum_array, $thread_array=false )
	{
		
		# Is mod and can delete own
		if ( $this->isMod( $forum_array ) AND cp::logged()->cur['canDelete'] )
		{			
			return true;			
		}
		
		if ( !$thread_array )
			return false;
		
		# Can delete own and is own thread
		if ( cp::logged()->cur['canDeleteOwn'] AND ( $thread_array['starter_id'] == cp::logged()->cur['id'] ) )
		{
			return true;
		}
		
		return false;
		
	}
	
	/**
	** canMove() - can we move the thread?
	** 
	** @forum_array
	** @thread_Array
	*/
	public function canMove( $forum_array, $thread_array )
	{
		
		if ( $this->isMod( $forum_array ) AND cp::logged()->cur['canMove'] )
		{
			return true;
		}
		
		return false;
		
	}
	
	/**
	** canMoveTo() - can we move to the next forum?
	** 
	** @next_forum_array - array of forum we're moving it to
	** @thread_Array
	*/
	public function canMoveTo( $next_forum_array, $thread_array )
	{
		
		if ( $next_forum_array['allowThreads'] AND $this->isMod( $next_forum_array ) )
		{
			return true;
		}
		
		return false;
		
	}
	
	/**
	** canRename() - can we rename the thread
	** 
	** @forum_array
	** @thread_Array
	*/
	public function canRename( $forum_array, $thread_array )
	{
		
		if ( $this->isMod( $forum_array ) AND cp::logged()->cur['canRename'] )
		{
			return true;
		}
		
		return false;
		
	}
	
	/**
	** canEditMem() - can we edit this member?
	** 
	** @param	array	$member	Array of member we're trying to edit
	** @return	bool
	*/
	public function canEditMem( $member )
	{
		
		/* Admins can edit (almost) everyone */
		if ( cp::logged()->cur['acp'] == true )
		{
			return true;
		}
		
		/* Mods can edit anyone who aren't admins */
		if (  cp::logged()->cur['globalMod'] )
		{
			return true;
		}
		
		/* Every can edit themselves */
		if ( $member['id'] == cp::logged()->cur['id'] )
		{
			return true;
		}
		
		return false;
		
	}
	
}

?>