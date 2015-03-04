<?php

	/*===================================================
	//	CipherPixel © All Rights Reserved
	//---------------------------------------------------
	//	CP-Core
	//		by cipherpixel.net
	//---------------------------------------------------
	//	File created: September 09, 2014 
	//=================================================*/

class ajax {
	
	/**
	** Return array
	*/
	public $ret	= array();
	
	/**
	** Array of command, 0=act, 1=method, 2=var
	*/
	public $cmd;
	
	/**
	** main()
	*/
	public function main()
	{
		
		/**
		** Post Upload?
		*/
		if( cp::$GET['ajax'] == 'post_upload' )
			$this->post_upload();
			
		else
		
		/**
		** Ajax Listener?
		*/
		if( cp::$POST['cmd'] == 'listen' )
			$this->listener();
			
		else
		
		/**
		** Check Command is OK, end error
		*/
		if ( $r = $this->checkCmd( cp::$POST['cmd'] ) )
			$this->error($r);			
		
		/**
		** Output!
		*/	
		cp::$final = json_encode($this->ret);
	}
	
	/**
	** error() - returns an alert (error)
	** 
	** @msg	= error message
	*/
	public function error( $msg )
	{
		
		$this->ret['res'] = 'error';
		$this->ret['msg'] = $msg;
		return FALSE;
		
	}
	
	/**
	** Popup an error
	** 
	** @param	string		$lang		Message to show
	*/	
	public function error_pop( $lang )
	{		
		cp::call('ajax')->ret['pop'] 	= true;
		cp::call('ajax')->ret['swop'] 	= '.ajax_white';
		cp::call('ajax')->ret['html'] 	= cp::display()->quickRead('popups/error', $lang);		
	}
	
	/**
	** Holder incase error_pop needs to be differentiated
	** 
	** @param	string		$lang		Message to show
	** @param	string		$title		Title to show above
	*/
	public function pop( $lang, $title=false )
	{
		
		cp::display()->vars['title'] = $title;
		cp::display()->vars['quick_var'] = $lang;
		
		cp::call('ajax')->ret['pop'] 	= true;
		cp::call('ajax')->ret['swop'] 	= '.ajax_white';
		cp::call('ajax')->ret['html'] 	= cp::display()->read('popups/gen');	
	}
	
	/**
	** popup() - shortcut to create a poopup
	**
	** @body	= body of popup
	*/	
	public function popup( $body )
	{		
		cp::call('ajax')->ret['pop'] 	= true;
		cp::call('ajax')->ret['swop'] 	= '.ajax_white';
		cp::call('ajax')->ret['html'] 	= $body;		
	}
	
	/**
	** checkCmd()
	*/
	public function checkCmd($cmdstr)
	{
		
		/**
		** Break down Cmd
		*/		
		$cmd = explode( ',', $cmdstr );
		
		/**
		** 0=act, 1=method, 2=var
		*/		
		if ( count( $cmd ) < 2 )
			return 'JS Command not created properly';
			
		/**
		** Folder?
		*/
		$folder = ( cp::$app['name'] == 'admin' ) ? 'admin': 'pub';
			
		/**
		** Check allowed file
		*/
		if ( substr( $cmd['1'], 0, 5 ) != 'ajax_' )
			return 'Not an ajax allowed function';
		
		/**
		** Save Command
		*/
		$this->cmd = $cmd;
		
		/**
		** Call Method
		*/
		if ( $cmd['0'] == 'cont' )
		{
			cp::methodCall( cp::cont(), $cmd['1'], $cmd['2'] );
		}
		else
		{
			cp::methodCall( cp::call( $folder . '_' . $cmd['0'], 'apps/' . cp::$app['name'] . '/' . $folder . '/' . $cmd['0'] ), $cmd['1'], $cmd['2'] );		
		}
			
		/**
		** Success
		*/
		return false;
		
	}
	
	/**
	** Listener...
	*/
	public function listener()
	{
		
		/**
		** Check for new notifications...
		*/
		if ( cp::logged()->cur['unreadNoti'] )
		{
			$this->ret['new_noti'] = cp::logged()->cur['unreadNoti'];
		}
		
	}
	
	/**
	** post_upload() - shortcut to direct cmd=post,ajax_upload
	*/
	public function post_upload()
	{
		cp::methodCall( cp::call( 'pub_post', 'apps/forum/pub/post' ), 'ajax_upload' );
	}
	
}
	
?>