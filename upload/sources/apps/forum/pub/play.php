<?php

	//===================================================
	//	Cipher Pixel Board © All Rights Reserved
	//---------------------------------------------------
	//	CP-Core
	//		by michaelbenner.net
	//---------------------------------------------------
	//	File created: April 04, 2013 
	//===================================================

class pub_play extends controller {
	
	/**
	** 
	** This class provides a quick testing playground inside the CP framework
	** 
	*/
	
	public function main()
	{		
		/* Get Google Redirect URL */
		/*$google = new cpobj_oauth('google');
		echo $google->get_url();*/
		
		$google = cp::call('oauth')->get_facebook();
		echo $google->get_url();
		
		
		
	}
	
}

?>