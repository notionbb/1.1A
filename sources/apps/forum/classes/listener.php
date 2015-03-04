<?php

	/*===================================================
	//	CipherPixel © All Rights Reserved
	//---------------------------------------------------
	//	CP-Forum
	//		by cipherpixel.net
	//---------------------------------------------------
	//	File created: September 28, 2014 
	//=================================================*/

class listener {
	
	public function start()
	{
		
		/**
		** Login Form...
		*/
		if ( cp::$POST['log_email'] OR cp::$POST['log_pass'] )
		{		
			$this->loginFromTop();
		}
		
	}
	
	/**
	** loginFromTop() - handles the login bar at the top
	*/
	public function loginFromTop()
	{
		
		$email 	= cp::$POST['log_email'];
		$pass	= cp::$POST['log_pass'];
		
		if ( !cp::logged()->loginError( $email, $pass ) )
		{
			
			# Redirect Page
			$url = ( cp::call('link')->readJSONLink( $_SESSION['ret_url'] ) ) ?: cp::call('link')->make(array('forum'));
			unset( $_SESSION['ret_url'] );			
			cp::display()->splash( cp::lang('login', 'log_in'), $url );
			
			# Skip app
			cp::$runTime['SKIP_APP'] = true;
			
		}				
		else
		{			
			cp::$act = 'login';
		}
		
	}
	
}

?>