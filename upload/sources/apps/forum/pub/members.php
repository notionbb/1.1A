<?php

	/*===================================================
	//	CipherPixel © All Rights Reserved
	//---------------------------------------------------
	//	CP-Coure
	//		by cipherpixel.net
	//---------------------------------------------------
	//	File created: October 20, 2014 
	//=================================================*/
	
class pub_members extends controller {
	
	/**
	** Navtree Array
	*/
	public $tree 	= array();
	
	/**
	** Array of current member we're showing
	*/
	public $member 	= false;
	
	public function main()
	{
		
		/* Can we even view this page? */
		if ( !cp::logged()->cur['canViewMembers'] )
		{
			cp::cont()->page['table'] = cp::display()->quickRead( 'cat_error', cp::lang('member', 'no_view_mem') );			
		}
				
		/* Look for member */
		elseif ( ( $find_member = $this->find_member() ) === true )
		{
			if ( !$this->mem_page() ) return;			
		}
		
		elseif ( $find_member === false )
		{
			$this->mem_page();
		}
		
		else
		{
			$this->sea_page();
		}
		
		# Output	
		cp::cont()->output .= cp::display()->read('page_gen');
		cp::output('norm');
		
	}
	
	/**
	** find_member()
	** Gets a member else returns false
	*/
	public function find_member()
	{
		
		if ( !isset( cp::$GET['2'] ) )
		{
			return;
		}
		
		$fetch = cp::call('members')->fetch( array( 'displayName' => cp::$GET['2'] ), array( 'groups'=>true, 'online'=>true ) );
		
		if ( !$fetch )
		{
			return false;
		}
		
		$this->member = cp::call('members')->prepMember( reset( $fetch ), array('pips'=>true, 'sig'=>true) );
		
		return true;
		
	}
	
	/**
	** find_member_id()
	*/
	public function find_member_id($user_id)
	{
		return reset( cp::call('members')->fetchAndPrep( array( 'id' => $user_id ), array( 'groups'=>true, 'online'=>true ), array('pips'=>true, 'sig'=>true) ) );		
	}
	
	public function mem_page()
	{

		$tree[ cp::lang('member', 'mem') ] = cp::link('members');
		
		if ( $this->member )
		{

			# Member Found
			$tree[ $this->member['displayName'] ] = $this->member['path_str'];
			
			if ( cp::$GET['3'] == 'edit' AND cp::callAppClass('lib_perm')->canEditMem( $this->member ) )
			{
				
				/* Get fields */
				$fields = $this->elements();
				
				if ( cp::$POST['edit_profile'] )
				{
					
					/* Don't put all columns to the database or else user may edit unwanted fields */
					$new = array(
						'avatar' 	=> cp::$POST['avatar'],
						'location'	=> cp::$POST['location'],
						'occupation'=> cp::$POST['occupation'],
						'quote'		=> cp::$POST['quote'],
						'signature'	=> cp::$POST['editor'],
					);
					
					if ( cp::$POST['pass_ignore'] )
					{
						if ( !( strlen( cp::$POST['pass_ignore'] ) > 4 ) )
						{
							$form_error['pass_ignore'] = cp::lang('login', 'pass_short');
						}
						if ( cp::$POST['pass_ignore'] != cp::$POST['pass_c_ignore'] )
						{	
							$form_error['pass_c_ignore'] = cp::lang('login', 'pass_match');
						}
					}

					if ( $form_error )
					{			
						
						/* Set Defaults */
						foreach( $fields as $row_name => $row )
						{
							$fields[ $row_name ]['def'] = cp::$POST[ $row_name ];
						}
						
					}
					else
					{
						
						/* Set a new password */
						if ( cp::$POST['pass_ignore'] )
						{
							
							/* New user_secure table information */
							$tmp = cp::call('members')->hash( cp::$POST['pass_ignore'] );
							$secure = array(
								'pass' 		=> $tmp['pass'],
								'string'	=> $tmp['key'],
							);
							cp::db()->update_dep( 'users_secure', $this->member['id'], $secure );
							
							/* Update session to reflect new password, lil bit cheeky */
							$_SESSION['pass'] = $secure['pass'];
							
						}
						
						/* Update Other */
						cp::db()->update_dep( 'users', $this->member['id'], $new );					
						cp::display()->splash( cp::lang('member', 'prof_up'), cp::link( array('members',$this->member['displayName']) ) );	
						return false;
					}
					
					cp::display()->vars['form_error'] = $form_error;
					
				}
				
				# Member Found
				$tree[ cp::lang('all', 'edit') ] = '';
				
				/* JS Load */
				cp::display()->jsLoad('editor')->jsLoad('minified/jquery.sceditor.bbcode.min');				
				
				/* Edit Form */
				$form = cp::display()->form( array(
						'name'		=> 'edit_profile',
						'fieldA'	=> $fields,
						'lang_pack'	=> 'member',
						'submit'	=> cp::lang('member', 'upd'),
						'submitC'	=> 'but grey',
					),
					'gen'
				);
				
				cp::display()->vars['cat']['subHtml'] = $form;
				cp::display()->vars['cat']['right'] = 'hello';
				
				cp::cont()->page['table'] .= cp::display()->read('cat_cat');
				
			}
			else
			{
				
				# Update Views
				cp::db()->update_dep( 'users', $this->member['id'], array('profileViews' => '+1') );
				$this->member['profileViews']++;
				
				/* Are we subscribed to this member? */
				$noti_array = json_decode( $this->member['notiArray'], true );
				
				if ( is_array( $noti_array['sub'] ) AND in_array( cp::logged()->cur['id'], $noti_array['sub'] ) )
				{
					$this->member['subbed'] = true;
				}
				
				cp::display()->vars['member'] 			= $this->member;
				cp::display()->vars['about']			= cp::display()->read('member_about');
				//cp::display()->vars['about']			= $this->ajax_threads( $this->member['id'] );	
				cp::display()->vars['cat']['subHtml'] = cp::display()->read('page_member');
				
				cp::cont()->page['table'] .= cp::display()->read('cat_cat');
				
			}
			
		}
		else
		{
			
			# Member not found
			cp::cont()->page['title'] = cp::lang('member', 'mem_no');
			$tree[ cp::lang('all', 'error') ] = '';
			
			# Cat
			cp::cont()->page['table'] = cp::display()->quickRead( 'cat_error', cp::lang('member', 'mem_no_mess') );
			
		}
		
		cp::cont()->navtree( $tree );
		
		return true;
		
	}
	
	public function sea_page()
	{
		
		# Set a page title
		cp::cont()->page['title'] = cp::lang('member', 'mem_sea');
		
		# Build Navtree
		$tree = array(
			cp::lang('member', 'mem')	=> cp::link('members'),
			cp::lang('member', 'sea')	=> '',
		);
		
		cp::cont()->navtree( $tree );
		
		/**
		** Search Bar
		*/
		
		/* Get Groups */
		
		$groups = cp::db()->fetch_dep( array( 'select' => 'id, name', 'table' => 'groups' ) );
		
		cp::display()->vars['g_opts'] .= '<option value="=">'.cp::lang('member', 'any').'</option>';
		
		foreach( $groups as $g )
		{
			cp::display()->vars['g_opts'] .= '<option value="'.$g['id'].'">'.$g['name'].'</option>';
		}	
		
		$search_form = cp::display()->read('mem_sea_options');
		
		cp::display()->vars['cat']['subHtml'] = cp::display()->quickRead( 'cat_message', $search_form );
		
		cp::cont()->page['table'] .= cp::display()->read('cat_cat');	
		
		/**
		** Results
		*/
		
		/* Have we searched? */
		if ( cp::$POST['mem_sea'] )
		{
			
			$where = false;
			
			if ( cp::$POST['display_name'] AND cp::$POST['dp_name_q'] == 'begins' )
			{
				$where .= 'displayName LIKE "'.cp::$POST['display_name'].'%"';
			}
			elseif ( cp::$POST['display_name'] AND cp::$POST['dp_name_q'] == 'contain' )
			{
				$where .= 'displayName LIKE "%'.cp::$POST['display_name'].'%"';
			}
			
			if ( cp::$POST['group'] AND is_numeric( cp::$POST['group'] ) )
			{
				if ( $where ) $where .= ' AND ';				
				$where .= 'groupId = "'.cp::$POST['group'].'"';
			}
			
			$allowed = array('displayName' => 'display_name', 'regDate' => 'reg_date', 'postCount' => 'post_count');
			
			$order_by = ( $k = array_search( cp::$POST['order_by'], $allowed ) ) ? $k: 'displayName';
			
			if ( cp::$POST['order'] == 'desc' )
			{
				$order = 'desc';
				$extra['order'] = $order_by . ' desc';
			}
			else
			{
				$order = 'asc';
				$extra['order'] = $order_by . ' asc';
			}
			
			$extra['limit'] = 20;
			
			$members = cp::call('members')->fetch( $where, true, $extra );
			
			# Slightly different title
			cp::display()->vars['cat']['small'] = cp::lang('member', 'sea_res');

		}
		else
		{
		
			# Get and prep members
			$members = cp::call('members')->fetch( '', true, array('order' => 'postCount desc', 'limit' => '20') );
			
			# Page...
			cp::display()->vars['cat']['small'] = cp::lang('member', 'top_post');
			
		}
				
		$members = cp::call('members')->prepMember( $members, array('pips'=>true) );
		
		/* Loop Members */
		
		$place = 1;
		$count = 1;
		$total = count( $members );
		
		cp::display()->vars['cat']['subHtml'] = null;
		
		if ( !$members )
		{
			cp::display()->vars['cat']['subHtml'] = cp::display()->quickRead( 'cat_message', cp::lang('member', 'no_res') );
		}
		else
		{
		
			foreach ( $members as $id => $member )
			{
				
				if ( $place == 1 )
				{
					cp::display()->vars['new_row'] = true;
				}
				else
				if ( $place == 4 )
				{
					cp::display()->vars['end_row'] = true;
				}
				
				if ( $count == $total )
				{
					cp::display()->vars['last_mem'] = true;
				}
				
				cp::display()->vars['member'] = $member;
				
				cp::display()->vars['cat']['subHtml'] .= cp::display()->read('cat_memrow');
	
				cp::display()->vars['new_row'] = false;
				cp::display()->vars['end_row'] = false;
				
				$place++;
				$count++;		
				
				if ( $place == 5 )
				{
					$place = 1;
				}
			}
			
		}	
		
		/* Output */
		
		cp::cont()->page['table'] .= cp::display()->read('cat_cat');
		
	}
	
	/**
	** elements() - edit profile form elements
	*/
	public function elements()
	{
		
		return array(
			/*'email' => array(
				'subcat'	=> 'bi',
				'name'		=> 'email',
				'lang_key'	=> 'email',
			),*/
			'pass_ignore' => array(
				'subcat'	=> 'pw',
				'name'		=> 'pass_ignore',
				'lang_key'	=> 'pass',
				'type'		=> 'password',
			),
			'pass_c_ignore' => array(
				'subcat'	=> 'pw',
				'name'		=> 'pass_c_ignore',
				'lang_key'	=> 'pass_c',
				'type'		=> 'password',
			),
			'avatar' => array(
				'subcat'	=> 'bi',
				'name'		=> 'avatar',
				'lang_key'	=> 'avatar',
				'def'		=> $this->member['avatar'],
			),
			'location' => array(
				'subcat'	=> 'bi',
				'name'		=> 'location',
				'lang_key'	=> 'locale',
				'def'		=> $this->member['location'],
			),
			'occupation' => array(
				'subcat'	=> 'bi',
				'name'		=> 'occupation',
				'lang_key'	=> 'occ',
				'def'		=> $this->member['occupation'],
			),
			'quote' => array(
				'subcat'	=> 'sig',
				'name'		=> 'quote',
				'lang_key'	=> 'quote',
				'def'		=> $this->member['quote'],
			),
			'signature' => array(
				'subcat'	=> 'sig',
				'name'		=> 'signature',
				'lang_key'	=> 'sig',
				'type'		=> 'wide',
				'html'		=> cp::display()->quickRead( 'member_sig_edit', $this->member['signature'] ),
			),
		);
		
	}
	
	/**
	** Subscribe to a user's activity
	** 
	** @param	int		$user_id	ID Of user to subscribe to
	*/
	public function ajax_sub($user_id)
	{
		
		//-----------------------------------
		// Check we're logged in, get u etc
		//-----------------------------------
		
		if ( !cp::logged()->in ) return;
		
		if ( !$user_id ) return;
		
		$user = cp::call('members')->fetch( array( 'id' => $user_id ) );
		
		if ( !$user[ $user_id ]  ) return;
		
		//-----------------------------------
		// Add to
		//-----------------------------------
		
		$user = $user[ $user_id ];
		
		$noti_array = ( $user['notiArray'] ) ? json_decode( $user['notiArray'], true ): array();
		
		/* If in array, remove */
		if ( is_array( $noti_array['sub'] ) AND in_array( cp::logged()->cur['id'], $noti_array['sub'] ) )
		{
			$key = array_search( cp::logged()->cur['id'], $noti_array['sub'] );
			unset( $noti_array['sub'][ $key ] );
			
			$but	= cp::lang( 'member', 'sub' );
			$title 	= cp::lang( 'member', 'unsubbed' ); 
			$msg 	= cp::lang( 'member', 'unsubbed_more' );
			
		}
		
		/* Add to array */
		else		
		{
			
			if ( !is_array( $noti_array['sub'] ) )
			{
				$noti_array['sub'] = array();
			}
			
			$noti_array['sub'][] = cp::logged()->cur['id'];
			
			$but	= cp::lang( 'member', 'unsub' );
			$title 	= cp::lang( 'member', 'subbed' ); 
			$msg 	= cp::lang( 'member', 'subbed_more' );
		}
		
		/* Update user */
		cp::db()->update( 'users', array( 'id' => $user_id ), array( 'notiArray' => json_encode( $noti_array ) ) );		
		
		cp::call('ajax')->ret['swop2'] 	= '#sub_but';
		cp::call('ajax')->ret['html2'] 	= $but;
		
		cp::call('ajax')->pop( $msg, $title );
		
	}
	
	/**
	** ajax_about()
	*/
	public function ajax_about($user_id)
	{
		
		/* Get User */
		$user = ( $this->member ) ?: $this->find_member_id($user_id);
		
		cp::display()->vars['member']	= $user;
		cp::call('ajax')->ret['swop'] 		= '#ajax_member_div';
		cp::call('ajax')->ret['html'] 		= cp::display()->read('member_about');
	}
	
	/**
	** ajax_threads()
	*/
	public function ajax_threads($user_id)
	{
		
		/* Can we even view this page? */
		if ( !cp::logged()->cur['canViewMembers'] )
		{
			return cp::call('ajax')->error('Cannot view this member');	
		}
		
		/* Get User */
		$user = ( $this->member ) ?: $this->find_member_id($user_id);
		
		if ( !$user )
		{
			return cp::call('ajax')->error('User not found');
		}
		
		/* Init class_forums */
		cp::callAppClass('class_forums')->retrieveReadable();
		cp::call('class_forums')->loopForums();
		
		/* Visibility */
		$vis = cp::callAppClass('class_thread')->visible();
		$vis = $vis['vis_str'];
		
		/* Get Readable threads */
		$threads = cp::db()->fetch_dep( array(
			'select'	=> 'threads.*',
			'table'		=> 'threads',
			'where'		=> 'threads.starterId="'.$user['id'].'" AND '.$vis.' AND ( forums.parent="0" OR '.cp::call('perm')->allowMe('read').')',
			'joins'		=> array(
				array(
					'table'	=> 'perm_reg',
					'where'	=> 'perm_reg.type="forum" AND threads.forumId=perm_reg.type_id',
					'type'	=> 'left',
				),
				array(
					'table'	=> 'forums',
					'where'	=> 'perm_reg.type="forum" AND forums.id=perm_reg.type_id',
					'type'	=> 'left',
				),
			),
			'limit'		=> '10',
			'order'		=> 'startTime desc',
		) );
		
		if ( !$threads )
		{
			$thread_html = cp::display()->quickRead( 'cat_message', cp::lang('member', 'no_threads') );	
		}
		else
		{		
			/* Loop Threads */
			$thread_html = cp::callAppClass('class_thread')->show( $threads, cp::call('class_forums')->forum_array );			
		}
		
		/* Build var and return */	
		cp::display()->vars['cat'] = array(
			'small'		=> cp::lang('member', 'mem_threads'),
			'subHtml'	=> $thread_html,
		);
		
		cp::call('ajax')->ret['swop'] 	= '#ajax_member_div';
		cp::call('ajax')->ret['html'] 	= cp::display()->read('cat_cat');
		
		return cp::call('ajax')->ret['html'];
		
	}
	
}

?>