<?php

	/*===================================================
	//	CipherPixel © All Rights Reserved
	//---------------------------------------------------
	//	CP-Forum
	//		by cipherpixel.net
	//---------------------------------------------------
	//	File created: October 19, 2014 
	//=================================================*/

/**
** @class	recap	Loads the ReCaptcha HTML and processes the response
** 
** How to use:
** $this->addtoform()	Returns Html for ReCapture
** $this->checkError()	Returns the error code, or FALSE if no error
*/

class recap {
    
	/**
	** Is captcha enabled?
	*/
	public $status = false;
	
	/**
	** Api pub and private key
	*/
	public $pubKey;	
	public $priKey;
	
	/**
	** ReCaptcha Error Code
	*/
	public $error;
	
	/**
	** Is class started?
	*/
	public $started = false;
	
	/**
	** ison() - checks to see if captcha has been loaded
	*/
	public function ison()
	{
		
		/* have we already done this */
		if ( $this->started ) return $this->status;
		
		/* ReCaptcha Disabled? */
		if ( !cp::set('ReCaptchaStatus') )
		{
			$this->started = true;
			return $this->status;
		}
		
		/* Set API Keys */		
		$this->pubKey = cp::set('recapPubKey');
		$this->priKey = cp::set('recapPriKey');
		
		/* Load JS */
		//$this->cp->jsLoad( 'recaptcha' );
		cp::display()->jsLoad('recaptcha');
		
		/* Enabled and Started */
		$this->status = true;
		$this->started = true;
		
		return $this->status;
		
	}
	
	/**
	** addtoform() - adds captcha to form
	** 
	** @return	string	html of captcha form
	*/
	public function addtoform()
	{

		/**
		** Load if Enabled
		*/
		if ( !$this->ison() ) return;
		
		/* Inc Lib */
		require_once( ROOT . '/sources/libs/recaptcha.php' );
		$publickey 	= $this->pubKey;
		$privatekey = $this->priKey;
		
		return cp::display()->read('recaptcha') . recaptcha_get_html($publickey, $this->error);
		
	}
	
	/**
	** checkError() - checks if captcha is okay
	** 
	** @return	bool	false if valid, true if error
	*/
	public function checkError()
	{
		
		/* Load */
		if ( !$this->ison() ) return false;
		
		/* Inc Lib */
		require_once( ROOT . '/sources/libs/recaptcha.php' );
		$publickey 	= $this->pubKey;
		$privatekey = $this->priKey;		
		
		/**
		** Check Captcha
		*/		
		if ( cp::$POST["recaptcha_response_field"] )
		{
			
			$resp = recaptcha_check_answer (
				$privatekey,
				$_SERVER['REMOTE_ADDR'],
				cp::$POST['recaptcha_challenge_field'],
				cp::$POST['recaptcha_response_field']
			);
			
			if ( $resp->is_valid )
			{
				return false;
			}
			else
			{
				// Set the error code so that we can display it
				$this->error = $resp->error;
				return $this->error;
			}
			
		}
		
		return true;
		
	}
	
}

?>