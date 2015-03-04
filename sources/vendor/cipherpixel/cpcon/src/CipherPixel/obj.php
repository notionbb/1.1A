<?php

/*===================================================
//	CipherPixel © All Rights Reserved
//---------------------------------------------------
//	CP-Connect
//		by cipherpixel.net
//---------------------------------------------------
//	@author		Michael Benner
//	@date		November 30, 2014 
//---------------------------------------------------
//	Creates an oauth object
//=================================================*/

namespace CipherPixel\cpcon;

class obj {
	
	/**
	** Current type of oauth
	** @var string
	*/
	public	$type;
	
	/**
	** Key / ID / App
	** @var	string
	*/
	public	$key;
	
	/**
	** Secret pass
	** @var	string
	*/
	public	$pass;
	
	/**
	** Redirect URI
	** @var	string
	*/
	public	$uri;
	
	/**
	** Current Client
	** @var	object
	*/
	public	$client;
	
	/**
	** Facebook specific client
	*/
	public	$helper;
	
	/**
	** Current Service for those that use it
	** @var	object
	*/
	public	$service;
	
	/**
	** Current session, used by fb.
	** @var	object
	*/
	public	$session;
	
	/**
	** Create class and settings
	** 
	** @param	string	$key	To set as $this->key
	** @param	string	$pass			  $this->pass
	** @param	string	$uri			  $this->uri
	*/
	public function __construct( $type, $key=false, $pass=false, $uri=false )
	{		
		if ( !isset( $type ) )
		{
			die("cpcon/obj() requires 'type' to be set");
		}
		else
		{
			$this->type = $type;
			$this->key 	= $key;
			$this->pass = $pass;
			$this->uri 	= $uri;
			call_user_func( array( $this, $this->type . '_set' ) );
		}		
	}
	
	/**
	** Get URL for auth flow
	** 
	** @return	string
	*/
	public function get_url()
	{
		return call_user_func( array( $this, $this->type . '_url' ) );
	}
	
	/**
	** Process callback/token
	** 
	** @return bool
	*/
	public function process()
	{
		return call_user_func( array( $this, $this->type . '_process' ) );
	}
	
	/**
	** Returns the current data
	** 
	** @return	array/bool
	*/
	public function data()
	{
		return call_user_func( array( $this, $this->type . '_data' ) );
	}
	
	//-----------------------------------
	// Google
	//-----------------------------------
	
	/**
	** Set the url strings etc
	** 
	** @param	string	$type	Login type (google, facebook etc)
	*/
	private function google_set()
	{
		
		$this->client = new \Google_Client();
		
		/* Unique Settings */
		$this->client->setClientId( $this->key );
		$this->client->setClientSecret( $this->pass );
		$this->client->setRedirectUri( $this->uri );
		
		/* Scope */
		$this->client->addScope("profile");
		$this->client->addScope("email");
		
		$this->service = new \Google_Service_Oauth2( $this->client );
		
	}
	
	/**
	** Return a url for google
	** 
	** @return	string
	*/
	private function google_url()
	{
		return $this->client->createAuthUrl();
	}
	
	/**
	** Get callback information and save array. Returns false if no callback to process.
	** 
	** @return	bool
	*/
	private function google_process()
	{
		
		if ( !isset( $_GET['code'] ) )
		{
			return false;
		}
		
		$this->client->authenticate( $_GET['code'] );
		$this->client->setAccessToken( $this->client->getAccessToken() );
		
		return true;
		
	}
	
	/**
	** Make user data
	** 
	** @return	mixed
	*/
	private function google_data()
	{
		return $this->service->userinfo->get();
	}
	
	//-----------------------------------
	// Facebook
	//-----------------------------------
	
	/**
	** Setup
	** 
	*/
	private function facebook_set()
	{
		\Facebook\FacebookSession::setDefaultApplication( $this->key, $this->pass );
		
		$this->helper = new \Facebook\FacebookRedirectLoginHelper( $this->uri );
	}
	
	/**
	** Return a url for facebook login
	** 
	** @return	string
	*/
	private function facebook_url()
	{
		return $this->helper->getLoginUrl( array( 'scope' => 'email' ) );
	}
	
	/**
	** Get callback information and save array. Returns false if no callback to process.
	** 
	** @return	bool
	*/
	private function facebook_process()
	{
		
		if ( !isset( $_GET['code'] ) )
		{
			return false;
		}
		
		try {
			$this->session = $this->helper->getSessionFromRedirect();
		}
		catch(FacebookRequestException $ex)
		{
			echo $ex->getMessage();
		}
		catch(\Exception $ex)
		{
			echo $ex->getMessage();
		}

		if ( $this->session )
		{
			return true;
		}
		
		return false;
		
	}
	
	/**
	** Make user data
	** 
	** @return	mixed
	*/
	private function facebook_data()
	{
		
		if( $this->session ) {
		
		  try {
		
			$class = new \Facebook\FacebookRequest( $this->session, 'GET', '/me');
		    $user_profile = $class->execute()->getGraphObject( \Facebook\GraphUser::className() );
			return $user_profile->asArray();
		
		  } catch( \Facebook\FacebookRequestException $e ) {
		
		    echo "Exception occured, code: " . $e->getCode();
		    echo " with message: " . $e->getMessage();
		    die();
		
		  }   
		
		}	

	}
	
}

?>