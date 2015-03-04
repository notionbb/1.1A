<?php

	//===================================================
	//	Cipher Pixel Board © All Rights Reserved
	//---------------------------------------------------
	//	CP-Core
	//		by michaelbenner.net
	//---------------------------------------------------
	//	File created: April 04, 2013 
	//===================================================

class pub_login extends controller {
	
	/**
	** Array of form elements
	*/
	private	$elements = array(
		'email' => array(
			'subcat'	=> 'basic_info',
			'name'		=> 'email',
			'lang_key'	=> 'email',
		),
		'displayName' => array(
			'subcat'	=> 'basic_info',
			'name'		=> 'displayName',
			'lang_key'	=> 'dpname',
		),
		'pass' => array(
			'subcat'	=> 'basic_info',
			'name'		=> 'pass',
			'lang_key'	=> 'pass',
			'type'		=> 'password',
		),
	);
	
	/**
	** Login Form Elements
	*/
	private $login_elements = array(
		'displayName' => array(
			'subcat'	=> 'basic_info',
			'name'		=> 'displayName',
			'lang_key'	=> 'dpname',
		),
	);
	
	public function main()
	{
		
		cp::$cache['save_url'] = false;
		
		/* Are we already logged in and require more information? */
		if ( cp::logged()->in )
		{
			
			$fix_user = $this->fix_user();
			
			/* Splash if nothing needs to be fixed */
			if ( $fix_user )
			{
				$url = ( cp::call('link')->readJSONLink( $_SESSION['ret_url'] ) ) ?: cp::call('link')->make(array('forum'));
				unset( $_SESSION['ret_url'] );			
				cp::display()->splash( cp::lang('login', 'log_in'), $url );	
			}
					
		}
		
		else
		
		if ( cp::$GET['2'] == 'google' )
		{
			if( $this->oauth_return('google') ) return;
		}
		
		else
		
		if ( cp::$GET['2'] == 'facebook' )
		{
			if( $this->oauth_return('facebook') ) return;
		}
		
		else
		
		if ( $this->login_form() )
		{
			return;	
		}
		
		cp::cont()->output .= cp::display()->read('page_gen');	
			
		cp::output('norm');	
			
	}
	
	/**
	** login_form()
	*/
	public function login_form()
	{
		
		/* Reg Form Required elements */
		$elements = array(	'email',
							'pass',
						);
						
		//-----------------------------------
		// Form Handler
		//-----------------------------------
		
		if ( cp::$POST['login_user'] )
		{	
			
			/* Splash if no errors, error messages are sent to cp::logged()->error */
			if ( !cp::logged()->loginError( cp::$POST['email'], cp::$POST['pass'] ) )
			{
				
				$url = ( cp::call('link')->readJSONLink( $_SESSION['ret_url'] ) ) ?: cp::call('link')->make(array('forum'));
				unset( $_SESSION['ret_url'] );			
				cp::display()->splash( cp::lang('login', 'log_in'), $url );				
				return true;				
				
			}
			else
			{
				
				/* Set Defaults */
				foreach( $elements as $row_name )
				{
					$this->elements[ $row_name ]['def'] = cp::$POST[ $row_name ];
				}
				
			}		
			
		}					
						
		//-----------------------------------
		// Show Form
		//-----------------------------------
		
		cp::display()->vars['error'] = cp::logged()->error;
										
		cp::display()->vars['form_error'] = $form_error;
				
		$this->form( $elements, 'login_user' );		
		
		return false;
		
	}
	
	/**
	** fix_user() - fixes any required "things"
	*/
	public function fix_user()
	{
		
		/* Check for any errors */
		if( !( $check_array = cp::logged()->check_setup() ) )
		{			
			return true;			
		}
		
		/* Check if DP is required */
		if ( $check_array['DP_REQ'] )
		{
			$elements[] = 'displayName';
			cp::display()->vars['error'] = cp::lang('login', 'dp_req');
		}
		
		/* Form Handler */
		if ( cp::$POST['user_fix'] )
		{		
			
			$up = array();
			
			/* Check display name, update and return */
			if ( $check_array['DP_REQ'] AND cp::$POST['displayName'] )
			{
				
				/* Check Unique */
				if ( cp::call('members')->fetch( array('displayName' => cp::$POST['displayName'] ) ) )
				{
					$form_error['displayName'] = cp::lang('login', 'dp_uniq');
				}
				
				$up['ow_displayname']	= 0;
				$up['displayName'] 		= cp::$POST['displayName'];
			}
			
			/* If no error in the form, update and splash page */
			if ( !$form_error )
			{
				
				//cp::display()->splash( cp::lang('login', 'up_suc'), '');
				
				cp::db()->update_dep( 'users', cp::logged()->cur['id'], $up );
							
				return true;
				
			}
						
		}

		cp::display()->vars['form_error'] = $form_error;
				
		$this->form( $elements, 'user_fix' );
		
		return false;
		
	}
	
	/**
	** form() - shows a form
	** 
	** @param	array	$elements	Array of form elements
	** @param	string	$name		Name of form
	*/
	public function form( $elements, $name )
	{
		
		//-----------------------------------
		// CP Form
		//-----------------------------------
		
		$cur = array();
		
		foreach( $elements as $v )
		{			
			$cur[] = $this->elements[ $v ];			
		}
		
		$form = cp::display()->form( array(
				'name'		=> $name,
				'fieldA'	=> $cur,
				'lang_pack'	=> 'login',
				'submit'	=> cp::lang('all', 'sub'),
				'submitC'	=> 'but grey',
				'action'	=> cp::link('login'),
			),
			'gen'
		);
		
		//-----------------------------------
		// Other Login Services...
		//-----------------------------------
		
		if ( !cp::logged()->in )
		{
		
			//-----------------------------------
			// Google
			//-----------------------------------
		
			if ( cp::set('googleLogin') AND ( $google = cp::call('oauth')->get('google') ) )
			{
			
				cp::display()->vars['google_auth'] = $google->get_url();
				
				$login_services[] = array( 'subcat' => 'log_quick',
										   'name'	=> '',
										   'type'	=> 'wide',
										   'html'	=> cp::display()->read('login_google'),
										 );
									 
			}
									 
			//-----------------------------------
			// Facebook
			//-----------------------------------
			
			if ( cp::set('facebook_allow') AND ( $facebook = cp::call('oauth')->get('facebook') ) )
			{
				
				cp::display()->vars['facebook_auth'] = $facebook->get_url();
				
				$login_services[] = array( 'subcat' => 'log_quick',
										   'name'	=> '',
										   'type'	=> 'wide',
										   'html'	=> cp::display()->read('login_facebook'),
										 );
				
			}
			
		}
			
		/* Add to form */
		
		if ( $login_services )
		{
									 
			$form .= cp::display()->form( array(
					'name'		=> '',
					'fieldA'	=> $login_services,
					'lang_pack'	=> 'all',
					'submit'	=> false,
				),
				'gen'
			);
			
		}
		
		//-----------------------------------
		// Display All
		//-----------------------------------
		
		cp::display()->vars['cat'] = array(
			'subHtml'	=> cp::display()->quickRead( 'cat_message', $form ),
		);
			
		cp::cont()->page['table'] = cp::display()->read('cat_cat');
		
	}
	
	/**
	** Login from oauth()
	** 
	** @param	string	$type
	** @return	bool
	*/
	public function oauth_return( $type )
	{
	
		$class = cp::call('oauth')->get( $type );
		
		if ( $class->process() )
		{
			
			if ( $user_data = $class->data() )
			{
				
				cp::call('members')->oauth_create( 
					array( 'email' => $user_data['email'], 'realName' => $user_data['name'], $type => $user_data['link'] ),
					array( 'google_id' => $user_data['id'] )
				);
				
				cp::logged()->oauth_login( $user_data['email'] );
				
				/* Login and Splash */
				$url = ( cp::call('link')->readJSONLink( $_SESSION['ret_url'] ) ) ?: cp::call('link')->make(array('app' => 'forum'));
				unset( $_SESSION['ret_url'] );			
				cp::display()->splash( cp::lang('login', 'log_in'), $url );
				
				return true;
				
			}
			
		}		
		
	}
	
}

?>