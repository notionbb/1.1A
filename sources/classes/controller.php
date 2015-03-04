<?php

	/*===================================================
	//	CipherPixel © All Rights Reserved
	//---------------------------------------------------
	//	CP-Core
	//		by cipherpixel.net
	//---------------------------------------------------
	//	File created: September 09, 2014 
	//=================================================*/

class controller {
	
	/**
	** Controller Vars
	*/
	public $vars		= array();
	
	/**
	** Generic Page Template
	** 	'title'	=> gen_page title
	*/
	public $page		= array();
	
	/**
	** Navtree
	*/
	public $navtreeHtml;
	
	/**
	** Output
	*/
	public $output		= '';
	
	/**
	** genController() - general controller
	**
	** @name	= class name (string)
	** @options = array of options
	*/
	public function adminDo( $name, $options=array() )
	{
		
		/**
		** A Page like this does four things:
		** - Shows a table/list of current rows
		** - Shows Edit Form
		** - Processess Edit Form
		** - Deletes row
		*/

		/**
		** Editing/new?
		*/
		if ( cp::$GET['edit'] )
		{
			
			if ( CP::$POST['update'] )
			{
				return cp::call( $name )->processForm();
			}
			else
			{				
				cp::call( $name )->showForm();				
			}
			
		}
		
		else
		
		/**
		** Delete Row
		*/
		if ( cp::$GET['del'] )
		{
			
			if ( CP::$POST['delete'] )
			{
				return cp::call( $name )->deleteRow();
			}
			else
			{				
				cp::call( $name )->showDeleteForm();				
			}
			
		}
		
		else
		
		/**
		** Show list of groups
		*/
		{

			cp::call( $name )->showAll();
			
		}
		
		return true;
		
	}
	
	/**
	** Log an admin action
	** 
	** @param	string	$table		Table being edited
	** @param	int		$id			Id being inserted/edited
	** @param	array	$before		Original Row
	** @param	array	$after		New Row
	*/
	public function log( $table, $id, $before, $after )
	{
		
		if ( is_array( $before ) )
		{
		
			$new = array_diff_assoc( $after, $before );
			
			$change_keys = array_keys( $new );
			
			foreach( $change_keys as $key )
			{
				$before_col[ $key ] = $before[ $key ];
				$after_col[ $key ]	= $after[ $key ];
			}
			
		}
		else
		{
			$before = false;
		}
		
		cp::db()->insert( 'admin_logs', array( 'time'	=> cp::$time,
											   'table'	=> $table,
											   'row'	=> $id,
											   'before'	=> serialize( $before_col ),
											   'after'	=> serialize( $after_col ),
											   'by'		=> cp::logged()->cur['id'],
											) );
		
	}
	
	/**
	** navtree() - adds new row to navtree
	**
	** @array	= either string or array of lang => link
	** @link	= link, used if $array is string
	*/
	public function navtree( $array, $link=false, $start=false )
	{	
			
		/**
		** Add to html
		*/
		if ( is_array( $array ) )
		{			
			foreach( $array as $k => $v )
			{
				if ( $start )
					$this->navtreeHtml = ' >> <a href="'.$v.'">'.$k.'</a>' . $this->navtreeHtml;
				else
					$this->navtreeHtml .= ' >> <a href="'.$v.'">'.$k.'</a>';
			}
		}
		else
		{
			if ( $start )
					$this->navtreeHtml = ' >> <a href="'.$link.'">'.$array.'</a>' . $this->navtreeHtml;
				else
					$this->navtreeHtml .= ' >> <a href="'.$link.'">'.$array.'</a>';
		}
	
	}
	
	/**
	** showNavtree() - returns navtree html
	** 
	** @return	string
	*/
	public function showNavtree()
	{
		return '<a class="navtree_link" href="'. cp::link(array('app'=>cp::$app['name'])) .'">'. cp::set('siteName') .'</a>' . $this->navtreeHtml;
	}
	
	/**
	** Menubar
	** 
	** @return	string		Html list
	*/
	public function menubar()
	{		
		
		/* Current activated apps */
		$app_names 	= array_keys(cp::$apps);
		
		$app = cp::$app['name'];
		
		/* Cached Menubar Items. If not cached, cache it. */
		if ( !$item_array = cp::cache()->get_db( 'menu_bar', true ) )
		{
			$item_array = cp::cache()->task_cache_menubar();
		}
		
		/* Loop for items */
		if ( is_array( $item_array ) )
		{
			
			foreach( $item_array as $item )
			{
				
				/* Conditional Statements */
				$statement 	= ( $item['if'] ) ? 'return ( '.$item['if'].' );': false;				
				$item['sel']= ( $statement ) ? eval( $statement ): false;
				
				/* Link */
				if ( $item['link_eval'] )
				{
					$statement 		= ( $item['link_eval'] ) ? 'return ( '.$item['link_eval'].' );': false;
					$item['link']	= ( $statement ) ? eval( $statement ): false;
				}
				
				/* Lang */
				$item['lang'] = cp::lang( $item['lang_pack'], $item['lang_key'] );
				
				/* Echo Menu Item */
				cp::display()->vars['item'] = $item;
				$menu_html .= cp::display()->read('menu_item');
				
			}
			
		}
		
		return '<ul>' . $menu_html . '</ul>';
				
	}
	
	/**
	** ajax_shownoti()
	*/
	public function ajax_shownoti()
	{
		
		if ( !cp::logged()->in ) return;
		
		# Set as read
		if( cp::logged()->cur['unreadNoti'] > 0 )
			cp::db()->update_dep( 'users', cp::logged()->cur['id'], array('unreadNoti'=>0) );
		
		# This gone be a beast of a query...
		$alerts = cp::db()->fetch_dep( array(
			'select'=> 'notifications.*, threads.title, threads.slug, threads.forumId, users.displayName as from_displayName, users.avatar as from_avatar',
			'table'	=> 'notifications',
			'where'	=> 'user_id="'.cp::logged()->cur['id'].'" AND ( notifications.thread_id=0 OR '.cp::call('perm')->allowMe( 'read', 'perm_reg.p_' ).')',
			'order'	=> 'id desc',
			'limit'	=> '5',
			'record'=> 'id',
			'joins' => array(
				array(
					'table' => 'threads',
					'where'	=> 'threads.id=notifications.thread_id',
					'type'	=> 'left',
				),
				array(
					'table' => 'users',
					'where'	=> 'users.id=notifications.from',
					'type'	=> 'left',
				),
				array(
					'table'	=> 'perm_reg',
					'where'	=> 'perm_reg.type="forum" AND threads.forumId=perm_reg.type_id',
					'type'	=> 'left',
				),
				array(
					'table' => 'forums',
					'where'	=> 'forums.id=threads.forumId',
					'type'	=> 'left',
				),
			),
		) );
		
		if ( !is_array( $alerts ) )
		{
		}
		else
		{
			
			cp::db()->update_dep('notifications', cp::db()->record, array('read'=>1), 'id', false, 'OR' );
			
			foreach( $alerts as $id => $array )
			{
				
				# Get forum path	
				$array['path'] 	 = cp::callAppClass('class_forums')->getpath( $array['forumId'] );
				$array['path'][] = $array['slug'];
				$array['path'][] = 'p_'.$array['post_id'].'#post_all_'.$array['post_id'];
				
				# Other vars
				$array['engTime'] = cp::display()->time2str( $array['time'] );
				cp::display()->vars['noti'] 	= $array;
				
				# Read and add		
				cp::display()->vars['noti_rows'] .= cp::display()->read('noti_row/' . $array['type']);
				
			}
			
		}
		
		# Return to script
		cp::call('ajax')->ret['html'] = cp::display()->read('noti');
		
	}
	
	/**
	** ajax_register() - show the register popup
	*/
	public function ajax_register()
	{
		
		//-----------------------------------
		// Other Methods
		//-----------------------------------
		
		/* Google */
		if ( cp::set('googleLogin') )
		{			
			cp::display()->add( 'google_auth', cp::call('oauth')->get('google')->get_url() )
						 ->add( 'login_google', cp::display()->read('login_google') )
						 ->add( 'alt_methods', true );	
		}
		
		/* Facebook */
		if ( cp::set('facebook_allow') )
		{	
			cp::display()->add( 'facebook_auth', cp::call('oauth')->get('facebook')->get_url() )
						 ->add( 'login_facebook', cp::display()->read('login_facebook') )
						 ->add( 'alt_methods', true );		
		}
		
		//-----------------------------------
		// Popup
		//-----------------------------------
		
		$pop = cp::display()->read('popups/register');
		
		cp::call('ajax')->popup( $pop );
		
	}
	
	/**
	** ajax_login() - show the login popup
	*/
	public function ajax_login()
	{
		
		//-----------------------------------
		// Other Methods
		//-----------------------------------
		
		/* Google */
		if ( cp::set('googleLogin') AND ( $google = cp::call('oauth')->get('google') ) )
		{			
			cp::display()->add( 'google_auth', $google->get_url() )
						 ->add( 'login_google', cp::display()->read('login_google') )
						 ->add( 'alt_methods', true );	
		}
		
		/* Facebook */
		if ( cp::set('facebook_allow') AND ( $facebook = cp::call('oauth')->get('facebook') ) )
		{	
			cp::display()->add( 'facebook_auth', $facebook->get_url() )
						 ->add( 'login_facebook', cp::display()->read('login_facebook') )
						 ->add( 'alt_methods', true );		
		}
		
		//-----------------------------------
		// Popup
		//-----------------------------------
		
		$pop = cp::display()->read('popups/login');
		
		cp::call('ajax')->popup( $pop );
		
	}
	
}
	
?>