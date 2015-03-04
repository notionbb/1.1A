<?php

	/*===================================================
	//	CipherPixel © All Rights Reserved
	//---------------------------------------------------
	//	CP-Form
	//		by cipherpixel.net
	//---------------------------------------------------
	//	File created: October 19, 2014 
	//=================================================*/

class pub_register extends controller {
	
	/**
	** Form Array
	*/
	private	$elements;
	
	public function main()
	{
		
		cp::$cache['save_url'] = false;
		
		/* Construct */
		$this->elements();
		
		/* Are we already logged in and require more information? */
		if ( cp::logged()->in )
		{		
			// Handled by pub/login		
		}
		else
		{			
			if ( $this->show_form() )
			{
				return;	
			}
			else
			{
				
				/* Page Setup */
				cp::cont()->page['title'] = cp::set('siteName') . ' ' . cp::lang('login', 'reg_for');
				
				cp::cont()->navtree( cp::lang('login', 'reg') );
				
			}
		}
		
		cp::cont()->output .= cp::display()->read('page_gen');	
			
		cp::output('norm');	
			
	}
	
	public function show_form()
	{
		
		/* Reg Form Required elements */
		$elements = array(	'email',
							'displayName',
							'pass',
							'pass_c',
						);
						
		if ( cp::set('ReCaptchaStatus') )
		{
			$elements[] = 'captcha';
		}	
		
		/* Form Handler */
		if ( cp::$POST['user_create'] )
		{		
			
			$new = array();
			
			/**
			** Email
			*/
			
			if ( !cp::$POST['email'] )
			{	
				$form_error['email'] = cp::lang('login', 'blank');
			}
			
			else
			
			if ( !filter_var( cp::$POST['email'], FILTER_VALIDATE_EMAIL ) )
			{
				$form_error['email'] = cp::lang('login', 'invalid_email');
			}
			
			else
				
			/* Check Unique */
			if ( cp::call('members')->fetch( array('email' => cp::$POST['email'] ) ) )
			{
					$form_error['email'] = cp::lang('login', 'email_uniq');
			}
			
			/**
			** Password
			*/
			if ( !cp::$POST['pass'] )
			{	
				$form_error['pass'] = cp::lang('login', 'blank');
			}
			if ( !cp::$POST['pass_c'] )
			{	
				$form_error['pass_c'] = cp::lang('login', 'blank');
			}
			else
			if ( !( strlen( cp::$POST['pass'] ) > 4 ) )
			{	
				$form_error['pass'] = cp::lang('login', 'pass_short');
			}
			else
			if ( cp::$POST['pass'] != cp::$POST['pass_c'] )
			{	
				$form_error['pass_c'] = cp::lang('login', 'pass_match');
			}
			
			/**
			** Display Name
			*/			
			if ( !cp::$POST['displayName'] )
			{
				$form_error['displayName'] = cp::lang('login', 'blank');
			}
			
			else
			
			/* Check Unique */
			if ( cp::call('members')->fetch( array('displayName' => cp::$POST['displayName'] ) ) )
			{
				$form_error['displayName'] = cp::lang('login', 'dp_uniq');
			}
			
			/**
			** Captcha
			*/
			if( $err = cp::call('recap')->checkError() )
			{
				$form_error['captcha'] = cp::lang('login', 'recap_' . $err );
			}
			
			/**
			** Insert
			*/
			
			if ( !$form_error )
			{
				
				cp::call('members')->create( array( 
												'email' 		=> cp::$POST['email'],
												'displayName'	=> cp::$POST['displayName'],
												'pass'			=> cp::$POST['pass'],
											), true );
											
				$secure = cp::db()->fetch( array(
					'table'	=> 'users_secure',
					'where'	=> array( 'id' => cp::call('members')->last_id ),
					'r'		=> 'one',
				) );
											
				cp::logged()->login( $secure['id'], $secure['pass'] );
				
				cp::display()->splash( cp::lang('login', 'cre_suc'), cp::link(array('login')) );
						
				return true;
			}
			else
			{
				
				cp::display()->vars['error'] = cp::lang('login', 'error' );
				
				/* Set Defaults */
				foreach( $elements as $row_name )
				{
					$this->elements[ $row_name ]['def'] = cp::$POST[ $row_name ];
				}
				
			}
						
		}
		
		cp::display()->vars['form_error'] = $form_error;
				
		$this->form( $elements, 'user_create' );
		
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
		
		$cur 		= array();
		$form_array = $this->elements;
		
		foreach( $elements as $v )
		{			
			$cur[] = $form_array[ $v ];			
		}
		
		$form = cp::display()->form( array(
				'name'		=> $name,
				'fieldA'	=> $cur,
				'lang_pack'	=> 'login',
				'submit'	=> cp::lang('all', 'sub'),
				'submitC'	=> 'but grey',
			),
			'gen'
		);
		
		cp::display()->vars['cat'] = array(
			'subHtml'	=> cp::display()->quickRead( 'cat_message', $form ),
		);
			
		cp::cont()->page['table'] = cp::display()->read('cat_cat');
		
	}
	
	public function elements()
	{
		
		/**
		** Array of form elements
		*/
		$this->elements = array(
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
			'pass_c' => array(
				'subcat'	=> 'basic_info',
				'name'		=> 'pass_c',
				'lang_key'	=> 'pass_c',
				'type'		=> 'password',
			),
			'captcha' => array(
				'subcat'	=> 'spam',
				'name'		=> 'captcha',
				'lang_key'	=> 'captcha',
				'html'		=> cp::call('recap')->addtoform(),
			),
		);
		
	}
	
}

?>