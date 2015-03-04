<?php

	//===================================================
	//	Cipher Pixel Board © All Rights Reserved
	//---------------------------------------------------
	//	CP-Core
	//		by michaelbenner.net
	//---------------------------------------------------
	//	File created: April 04, 2013 
	//===================================================

class pub_post extends controller {
	
	/**
	** Thread we're posting to
	*/
	public	$thread;
	
	/**
	** Type of thing we're making (post,thread) 
	*/
	public	$type;
	
	/**
	** 
	*/
	public function main()
	{
		
		/**
		** Forum and thread slugs need to be independent!
		*/
		
		/////////////////////////////////
		
		# Assistant
		$this->class_forums = cp::callAppClass( 'class_forums' );
		
		if ( $this->prepare() )
		{
			
			if ( $this->process() )
				return $this->splash();
				
			# Thread?
			$this->thread_page();
			
			# Post
			$this->post_page();
			
			# Page information
			$this->page();
			
		}
		
		# Output
		cp::cont()->output .= cp::display()->read('page_gen');	
			
		cp::output('norm');
		
	}
	
	/**
	** Process Form
	*/
	public function process()
	{
		
		if ( !cp::$POST['post'] )
			return false;
			
		//-----------------------------------
		// Approval Status
		//-----------------------------------
		
		if ( cp::call('perm')->ban_check('force_unapp') )
		{
			$visible = 0;
		}
		else
		{
			$visible = 1;
		}
		
		//-----------------------------------
		// Notifications
		//-----------------------------------
		
		$add_noti = ( cp::$POST['subscribe_new_post'] ) ? array('new_post'=> array(cp::logged()->cur['id'])): false;
		
		//-----------------------------------
		// Process
		//-----------------------------------
			
		if ( $this->type == 'thread' )
		{

			# Did we set a title?
			if ( !cp::$POST['title'] )
			{
				cp::display()->vars['red_title'] = true;
				return false;
			}			
			
			$this->thread = cp::callAppClass('class_post')->create_thread( array(
				'title'		=> cp::$POST['title'],
				'forumId'	=> $this->forum['id'],
				'content'	=> cp::$POST['editor'],
				'attach'	=> cp::$POST['file-ids'],
				'visible'	=> $visible,
				'add_noti'	=> $add_noti,
			) );
			
			return $this->thread;
			
		}
		
		else
		
		if ( $this->type == 'post' )
		{			
			return cp::callAppClass('class_post')->insert( array(
				'threadId'	=> $this->thread['id'],
				'content'	=> cp::$POST['editor'],
				'attach'	=> cp::$POST['file-ids'],
				'visible'	=> $visible,
				'add_noti'	=> $add_noti,
			) );
		}
			
		return false;
		
	}
	
	/**
	** splash() - splash when complete
	*/
	public function splash()
	{
		
		# Path to thread
		if ( $this->type == 'thread' )
		{
			$path 	= $this->forum['path'];
			$path[] = $this->thread['slug'];
		}
		else
		# Path to post
		if ( $this->type == 'post' )
		{
			# Combine Paths
			$path = $this->forum['path'];
			
			# Post count...
			if ( cp::callAppClass('lib_perm')->isMod( $this->forum ) )
				$this->thread['postCount'] += $this->thread['hiddenCount'];
			
			# Do we need to add page?	
			$top_page = ceil( ( $this->thread['postCount'] + 1 ) / cp::set('postsPerPage') );		
			if ( $top_page != 1 ) 
			{
				$path[] = $this->thread['slug'];
				$path[] = $top_page . '#post_all_' . cp::db()->lastId;
			}
			else
			{
				$path[] = $this->thread['slug'] . '#post_all_' . cp::db()->lastId;
			}
					
		}	
		
		cp::display()->splash( cp::lang( 'post', 'added_' . $this->type ), cp::link($path) );		
		
		return true;
	}
	
	/**
	** 
	*/
	public function prepare()
	{
		
		/**
		** Previous slug else...
		*/
		$slug = cp::$cache['POST_TO'];
		
		/**
		** Check if thread...
		*/
		$thread = cp::db()->fetch_dep( array(
			'table'		=> 'threads',
			'where'		=> 'slug="'. $slug.'"',
			'one'		=> true,
		) );
		
		if ( $thread )
		{
			
			/**
			** Tell script what we're editing
			*/
			$this->type		= 'post';
			$this->thread 	= $thread;
			
			/**
			** Get all Forums
			*/
			$forumId = $this->class_forums->retrieveReadable( array( 'id' => $this->thread['forumId'] ) );
			
		}
		else
		{
			
			/**
			** Tell script what we're editing
			*/
			$this->type		= 'thread';
		
			/**
			** Get all forums
			*/
			$forumId = $this->class_forums->retrieveReadable( array( 'slug' => $slug ) );
		}
		
		/**
		** Return if not found
		*/
		if ( !$forumId )
		{			
			cp::cont()->page['table'] = cp::display()->quickRead( 'cat_error', cp::lang('cat', 'no_fororth') );			
			return false;			
		}
		
		# Loop Forums and save
		$this->class_forums->loopForums();
		
		$this->forum = $this->class_forums->forum_array[ $forumId ];
		
		/**
		** Check permissions...
		*/
		if ( !$this->checkPerms() ) return false;			
		
		return true;
		
	}
	
	/**
	** 
	*/
	public function checkPerms()
	{
		
		/**
		** Can we xx on this forum?
		*/
		if ( $this->type == 'thread' AND !cp::callAppClass('lib_perm')->startThread( $this->forum ) )
		{			
			cp::cont()->page['table'] = cp::display()->quickRead( 'cat_error', cp::lang('post', 'not_start') );			
			return false;	
		}
		
		else
		
		if ( $this->type == 'post' AND !cp::callAppClass('lib_perm')->replyThread( $this->forum, $this->thread ) )
		{			
			cp::cont()->page['table'] = cp::display()->quickRead( 'cat_error', cp::lang('post', 'not_reply') );			
			return false;	
		}
		
		return true;
		
	}
	
	/**
	** thread_page
	*/
	public function thread_page()
	{
		
		# Check Type
		if ( $this->type != 'thread' ) return;
		
		cp::display()->vars['type_extra'] = cp::display()->read('cat_post_page_thread');
		
	}
	
	/**
	** post_page
	*/
	public function post_page()
	{
		
		/**
		** Load Js
		*/
		cp::display()->jsLoad('editor')->jsLoad('minified/jquery.sceditor.bbcode.min');
		
		# Set type
		cp::display()->vars['type'] = $this->type;
		
		# Are we allowed file uploads?
		cp::display()->vars['upload'] = cp::call('perm')->check( $this->forum, 'upload' );
		
		/**
		** Build Form
		*/		
		$form = array(
			'name'			=> 'post',
			'autocomplete'	=> true, // turns it off'
			'fields'		=> cp::display()->read('cat_post_page'),
			'submit'		=> false,
			'no_close'		=> true,
		);
		
		/**
		** Vars
		*/
		
		cp::display()->vars['cat']['subHtml'] = cp::display()->form( $form );
		cp::display()->vars['cat']['right'] 	= cp::display()->read('post_right');
		
		/**
		** Show Page
		*/
		cp::cont()->page['table'] = cp::display()->read('cat_cat');
		
	}
	
	/**
	** 
	*/
	public function page()
	{
		
		# Thread Path...
		$this->thread['path']   = $this->forum['path'];
		$this->thread['path'][] = $this->thread['slug'];
		
		# Set a page title
		cp::cont()->page['title'] = '';
		
		# Build Navtree
		$this->class_forums->navtree( $this->forum['id'] );
		
		if ( $this->thread['title'] )
		{
			cp::cont()->navtree( array( $this->thread['title'] => cp::link($this->thread['path']) ) );
		}
		
		cp::cont()->navtree(array(cp::lang('post', 'cpost') => '') );
		
		# JS Vars
		cp::display()->addJsGlobal( 'cur_slug', $this->forum['slug'] );
		
	}
	
	/**
	** ajax_quickreply
	*/
	public function ajax_quickreply( $threadId )
	{
		
		/**
		** Parent Forum and Thread
		*/
		if ( !$this->thread = cp::db()->get( 'threads', $threadId ) )
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
			
		/**
		** Permissions
		*/ 
		$this->type = 'post';
		
		if ( !$this->checkPerms() )
			return cp::call('ajax')->error( cp::display()->vars['quick_var'] );
		
		
		/**
		** Approval
		*/			
		if ( cp::call('perm')->ban_check('force_unapp') )
		{
			$visible = 0;
		}
		else
		{
			$visible = 1;
		}
			
		/**
		** Insert
		*/
		$post_id = cp::callAppClass('class_post')->insert( array(
			'threadId'	=> $this->thread['id'],
			'content'	=> cp::$POST['sce'],
			'visible'	=> $visible,
		));
		
		/**
		** New page?
		*/
		$cur_page = cp::$POST['extra'];
		
		if ( cp::callAppClass('lib_perm')->isMod( $this->forum ) )
			$this->thread['postCount'] += $this->thread['hiddenCount'];
		
		$top_page = ceil( ( $this->thread['postCount'] + 1 ) / cp::set('postsPerPage') );		
		
		/**
		** Post on new page, redirect...
		*/
		if ( $top_page != $cur_page )
		{
			
			cp::callAppClass('class_forums')->retrieveReadable();
			$path 	= cp::call('class_forums')->make_path($this->forum['id']);
			$path[]	= $this->thread['slug'];
			$path[] = $top_page . '#post_all_' . $post_id;
			
			cp::call('ajax')->ret['redirect'] = cp::link($path);
		}
		
		else
		
		/**
		** Return Post
		*/
		
		{
		
			/**
			** Return Post...
			*/
			$post = cp::db()->get( 'posts', $post_id );		
			cp::call( 'pub_thread', 'apps/forum/pub/thread' )->showPosts( array( cp::db()->lastId => $post ), $this->thread, $this->forum );
			
			/**
			** Ajax Return
			*/
			cp::call('ajax')->ret['append']	= '#ajax_attack_extra_subHtml';
			cp::call('ajax')->ret['html']	= cp::display()->vars['cat']['subHtml'];
			
			/**
			** Mark Read
			*/
			cp::logged()->setread('thread', $this->thread['id']);
			
		}
		
	}
	
	/**
	** Upload on post page
	*/
	public function ajax_upload()
	{
		
		/**
		 * Check current permissions
		 */
		$forum = cp::db()->fetch_dep( array(
			'table'	=> 'forums',
			'where'	=> cp::call('perm')->allowMe( 'read' ).' AND '.cp::call('perm')->allowMe( 'upload' ),
			'join'	=> array(
				'table'	=> 'perm_reg',
				'where'	=> 'perm_reg.type="forum" AND forums.id=perm_reg.type_id',
				'type'	=> 'left',
			),
			'order'	=> '`order` asc',
			'resono'=> true,
		));
		
		if ( !$forum ) return;
		
		foreach( $_FILES as $k => $a )
		{
			
			$file = cp::call('upload')->file( $k );
			
			$file->addValidations(array(
				new \Upload\Validation\Mimetype( cp::call('upload')->types ),
				new \Upload\Validation\Size('5M')
			));
			
			$original_name 	= $file->getNameWithExtension();
			$new_name		= uniqid() . '-' . $file->getName();
			
			$file->setName($new_name);
			
			/*$data = array(
			    'name'       => $file->getNameWithExtension(),
			    'extension'  => $file->getExtension(),
			    'mime'       => $file->getMimetype(),
			    'size'       => $file->getSize(),
			    'md5'        => $file->getMd5(),
			    'dimensions' => $file->getDimensions()
			);*/
			
			/* Try to upload the file */
			try {
			    $file->upload();			    			    
			} catch (\Exception $e) {
			    $errors = $file->getErrors();
			}
			
			if ( !$errors )
			{
				
				$new = array(
					'time'	=> cp::$time,
					'upload_by' => cp::logged()->cur['id'],
					'name'	=> $original_name,
					'path'	=> cp::call('upload')->lastRelPath() . '/' . $file->getNameWithExtension(),
				);
				
				cp::db()->insert('attachment', $new);
				
				$done[ $original_name ] = $original_name;
				$urls[ $original_name ] = cp::call('upload')->lastUrl() . '/' . $file->getNameWithExtension();
				$time[ $original_name ] = cp::db()->lastId;
				
			}
			
		}
		
		/* Time is actually IDs */
		cp::call('ajax')->ret['done'] = $done;
		cp::call('ajax')->ret['urls'] = $urls;
		cp::call('ajax')->ret['time'] = $time;

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
	public function ajax_edit( $postId )
	{
		
		if ( !$this->ajax_from_post_id($postId) )
			return false;
		
		/**
		** Check Permission...
		*/
		if ( !cp::callAppClass('lib_perm')->editPost( $this->forum, $this->thread, $this->post ) )
			return cp::call('ajax')->error('Permission error');
		
		/**
		** Return
		*/
		
		cp::display()->vars['post_id'] = $postId;
		
		cp::call('ajax')->ret['id'] 	= $postId;
		cp::call('ajax')->ret['editor'] = cp::display()->read('cat_post_edit');
		cp::call('ajax')->ret['post']	= $this->post['postContent'];
		
	}
	
	/**
	** ajax_edit_save
	*/
	public function ajax_edit_save( $postId )
	{
		
		if ( !$this->ajax_from_post_id($postId) )
			return false;
			
		/**
		** Check Permission...
		*/
		if ( !cp::callAppClass('lib_perm')->editPost( $this->forum, $this->thread, $this->post ) )
			return cp::call('ajax')->error('Permission error');
			
		$content= cp::$POST['sce'];
		$reason	= cp::$POST['moredata'];
		
		/**
		** Update Post
		*/
		cp::callAppClass('class_post')->update( 
			array('id' => $this->post['id'], 'content' => $content ),
			array('reason' => $reason )
		);
		
		cp::call('ajax')->ret['swop'] = '#post_content_' . $postId;
		cp::call('ajax')->ret['html'] = cp::call('bbcode')->bb2html( $content );
		
	}
	
	/**
	** ajax_delete
	*/
	public function ajax_del( $postId )
	{
		
		if ( !$this->ajax_from_post_id($postId) )
			return false;
			
		/**
		** Check Permission...
		*/
		if ( !cp::callAppClass('lib_perm')->delPost( $this->forum, $this->thread, $this->post ) )
			return cp::call('ajax')->error('Permission error');
			
		/**
		** Confirmation
		*/
		cp::display()->vars['post_id'] = $postId;
		cp::call('ajax')->popup( cp::display()->read('popups/post_del') );
		
	}
	
	/**
	** 
	*/
	public function ajax_del_conf( $postId )
	{
		
		if ( !$this->ajax_from_post_id($postId) )
			return false;
			
		/**
		** Check Permission...
		*/
		if ( !cp::callAppClass('lib_perm')->delPost( $this->forum, $this->thread, $this->post ) )
			return cp::call('ajax')->error('Permission error');
		
		/**
		** Delete...
		*/	
		cp::callAppClass('class_post')->delete( $postId, 'soft' );
				
		/**
		** Hide Post.
		*/	
		cp::call('ajax')->ret['slideUp'] = '#post_all_' . $postId;
		
	}
	
	/**
	** ajax_res
	*/
	public function ajax_res( $postId )
	{
		
		if ( !$this->ajax_from_post_id($postId) )
			return false;
			
		/**
		** Check Permission...
		*/
		if ( !cp::callAppClass('lib_perm')->delPost( $this->forum, $this->thread, $this->post ) )
			return cp::call('ajax')->error('Permission error');
			
		/**
		** Confirmation
		*/
		cp::display()->vars['post_id'] = $postId;
		cp::call('ajax')->popup( cp::display()->read('popups/post_res') );
		
	}
	
	public function ajax_res_conf( $postId )
	{
		
		if ( !$this->ajax_from_post_id($postId) )
			return false;
			
		/**
		** Check Permission...
		*/
		if ( !cp::callAppClass('lib_perm')->delPost( $this->forum, $this->thread, $this->post ) )
			return cp::call('ajax')->error('Permission error');
		
		/**
		** Restore
		*/	
		cp::callAppClass('class_post')->restore( $postId );
				
		/**
		** Remove a class...
		*/	
		cp::call('ajax')->ret['remove'] = '#post_all_' . $postId;
		cp::call('ajax')->ret['class'] = 'trans';
		
	}
	
	/**
	** ajax_res
	*/
	public function ajax_rep( $postId )
	{
		
		if ( !$this->ajax_from_post_id($postId) )
			return false;
			
		/**
		** Check Permission...
		*/
		if ( !cp::callAppClass('lib_perm')->delPost( $this->forum, $this->thread, $this->post ) )
			return cp::call('ajax')->error('Permission error');
			
		/**
		** Confirmation
		*/
		cp::display()->vars['post_id'] = $postId;
		cp::call('ajax')->popup( cp::display()->read('popups/post_rep') );
		
	}
	
	public function ajax_rep_conf( $postId )
	{
		
		if ( !$this->ajax_from_post_id($postId) )
			return false;
			
		/**
		** Check Permission...
		*/
		if ( !cp::callAppClass('lib_perm')->repPost( $this->forum, $this->thread, $this->post ) )
			return cp::call('ajax')->error('Permission error');
		
		/**
		** Report
		*/	
		cp::db()->insert( 'reports', array(
			'type'		=> 'post',
			'relId'		=> $postId,
			'sendTime'	=> cp::$time,
			'from'		=> cp::logged()->cur['id'],
			'msg'		=> cp::$POST['moredata'],
		) );
				
		/**
		** Send Confirmation
		*/	
		cp::call('ajax')->popup( cp::display()->read('popups/post_rep_done') );
		
	}
	
}

?>