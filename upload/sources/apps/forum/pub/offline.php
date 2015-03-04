<?php

	//===================================================
	//	Cipher Pixel Board © All Rights Reserved
	//---------------------------------------------------
	//	CP-Core
	//		by michaelbenner.net
	//---------------------------------------------------
	//	File created: April 04, 2013 
	//===================================================

class pub_offline extends controller {
	
	/**
	** main()
	*/
	public function main()
	{

		cp::cont()->page['table'] = cp::display()->quickRead( 'cat_error', cp::set('offlineMsg') );		
		
		# Output
		cp::cont()->output .= cp::display()->read('page_gen');	
			
		cp::output('norm');
		
	}
	
	
}

?>