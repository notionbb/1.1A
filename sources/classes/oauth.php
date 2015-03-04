<?php

/*===================================================
//	CipherPixel © All Rights Reserved
//---------------------------------------------------
//	CP-Core
//		by cipherpixel.net
//---------------------------------------------------
//	@author		Michael Benner
//	@date		November 28, 2014 
//---------------------------------------------------
//	Creates an oauth handle to use with CP-Connect
//	Really just a convenient setting shell
//=================================================*/

class oauth {
	
	/**
	** Ensure we can use composer's autoloader
	** 
	*/
	public function __construct()
	{
		cp::load_composer_autoload();
	}
	
	/**
	** Shortcut
	** 
	** @param	string	$type
	** @return	object
	*/
	public function get( $type )
	{
		return call_user_func( array( $this, 'get_' . $type ) );
	}
	
	/**
	** @return	bool/object
	*/
	public function get_google()
	{

		return new CipherPixel\cpcon\obj( 'google',
								   		  cp::set('googleId'),
								   		  cp::set('googleSecret'),
								   		  cp::set('googleURI')
										);
	}
	
	/**
	** @return	bool/object
	*/
	public function get_facebook()
	{
		
		if ( version_compare( phpversion(), '5.4', '<' ) )
		{
			return false;
		}
		
		return new CipherPixel\cpcon\obj( 'facebook',
								   		  cp::set('facebook_app_id'),
								   		  cp::set('facebook_secret'),
								   		  cp::set('facebook_uri')
										);
	}
	
}

?>