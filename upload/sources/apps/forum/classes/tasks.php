<?php

	//===================================================
	//	Cipher Pixel Board © All Rights Reserved
	//---------------------------------------------------
	//	CP.Board
	//		by michaelbenner.net
	//---------------------------------------------------
	//	File created: April 04, 2013 
	//===================================================

class tasks {
	
	/**
	** Check Forums have matching permissions
	*/
	public function forumToPerm()
	{
		
		if ( cp::$POST['confirm'] )
		{
			
			/**
			** Select all forums
			*/
			$forumQ = cp::db()->exec("SELECT * FROM `forums` WHERE `parent` != '0'");
			
			/**
			** Select all perm_reg
			*/
			$permQ	= cp::db()->exec("SELECT * FROM `perm_reg` WHERE `type` = 'forum'");
			
			/**
			** Key by type_id
			*/
			while ( $perm = $permQ->fetch_assoc() )
			{
				$perm_array[ $perm['type_id'] ]	= 1;
			}
			
			/**
			** Check Forums...
			*/
			while ( $forum = $forumQ->fetch_assoc() )
			{
				if ( $perm_array[ $forum['id'] ] ) continue;				
				$toMake[] = $forum['id'];
				
			}
			
			/**
			** Make new perm_reg rows
			*/
			foreach( $toMake as $type_id )
			{
				cp::db()->insert('perm_reg', array('type'=>'forum', 'type_id' => $type_id));
			}
			
			cp::display()->splash( cp::lang('tools', 'splash'), '?page=tools');
			
			return false;
			
		}
		else
		{
			
			cp::cont()->output .= cp::display()->read('div_tool');
			
			/**
			** Show tool information
			*/
			cp::cont()->output .= cp::display()->form(
				array(
					'name'		=> 'confirm',
					'submit'	=> cp::lang('tools', 'run_tool') . ' >>',
					'submitC'	=> 'grey',
				),
				'but'
			);
			
			return true;
			
		}
		
	}
	
	/**
	** Fix last poster
	*/
	public function lastPosts()
	{
		
		if ( cp::$POST['confirm'] )
		{
			
			# Get latest threads for each forum
			$threads = cp::db()->exec("SELECT id, max(lastPostTime) as lastPostTime, lastPostUser, forumId FROM threads GROUP BY forumId");
			
			while( $thread = $threads->fetch_assoc() )
			{
				$thread_array[ $thread['forumId'] ] = $thread;
				$thread_ids[] = $thread['id'];
			}
			
			/*$forums = cp::db()->fetch_dep( array(
				'table'	=> 'forums',
				'where'	=> array( 'id' => $thread_ids ),
			) );*/
			
			$forums = cp::db()->get( 'forums' );
			
			foreach ( $forums as $id => $array )
			{
				
				# Set zeros
				$thread = array(
					'lastPostTime' 	=> '0',
					'lastPostUser'	=> '0',
					'id'			=> '0',
				);
				
				# Overwrite
				$thread = ( $thread_array[ $array['id'] ] ) ?: $thread;
				
				# Update
				$forum						= $array;
				$forum['lastPostTime']		= $thread['lastPostTime'];
				$forum['lastPostUser']		= $thread['lastPostUser'];
				$forum['lastPostThread'] 	= $thread['id'];
				
				# Update
				cp::db()->update_dep( 'forums', $array['id'], $forum );
				
			}
			
			cp::display()->splash( cp::lang('tools', 'splash'), '?page=tools');
			
			return false;
			
		}
		else
		{
			
			cp::cont()->output .= cp::display()->read('div_tool');
			
			/**
			** Show tool information
			*/
			cp::cont()->output .= cp::display()->form(
				array(
					'name'		=> 'confirm',
					'submit'	=> cp::lang('tools', 'run_tool') . ' >>',
					'submitC'	=> 'grey',
				),
				'but'
			);
			
			return true;
			
		}
		
	}
	
	/**
	** Fix post counts
	*/
	public function countPosts()
	{
		
		if ( cp::$POST['confirm'] )
		{
			
			$posts_cache = cp::db()->fetch( array( 'table' => 'posts' ) );
			$threads_cache = cp::db()->fetch( array( 'table' => 'threads' ) );
			
			foreach( $posts_cache as $id => $post )
			{
				
				$thread = $threads_cache[ $post['threadId'] ];
				
				if ( $post['visible'] == 1 )
				{
					$k = 'postCount';
					$users[ $post['posterId'] ]++;
				}
				else
				{
					$k = 'hiddenCount';
				}
				
				$threads[ $thread['id'] ][ $k ]++;
								
			}
			
			foreach( $threads as $thread_id => $thread_array )
			{				
				if ( $threads_cache[ $thread_id ]['visible'] == 1 )
				{
					$forums[ $threads_cache[ $thread_id ]['forumId'] ] += $thread_array['postCount'];
					$total_posts += $thread_array['postCount'];
				}
				
				cp::db()->update( 'threads', $thread_id, $thread_array );
								
			}
			
			foreach( $forums as $forum_id => $forum_postCount )
			{			
				cp::db()->update( 'forums', $forum_id, array('postCount' => $forum_postCount) );
			}
			
			foreach( $users as $u_id => $u_postCount )
			{			
				cp::db()->update( 'users', $u_id, array('postCount' => $u_postCount) );
			}
				
			cp::db()->update( 'miscstat', array('arrayTitle'=>'totalPosts'), array('value'=>$total_posts) );
			
			echo cp::db()->printHistory();
			
			cp::display()->splash( cp::lang('tools', 'splash'), '?page=tools');
			
			return false;
			
		}
		else
		{
			
			cp::cont()->output .= cp::display()->read('div_tool');
			
			/**
			** Show tool information
			*/
			cp::cont()->output .= cp::display()->form(
				array(
					'name'		=> 'confirm',
					'submit'	=> cp::lang('tools', 'run_tool') . ' >>',
					'submitC'	=> 'grey',
				),
				'but'
			);

			return true;
			
		}
		
	}
	
}
	
?>