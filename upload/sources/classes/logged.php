<?php

	/*===================================================
	//	CipherPixel © All Rights Reserved
	//---------------------------------------------------
	//	CP-Core
	//		by cipherpixel.net
	//---------------------------------------------------
	//	File created: September 08, 2014 
	//=================================================*/

class logged {
	
	/**
	** Current user
	** 
	** @var		array
	*/
	public	$cur;
	
	/**
	** Array of ban types
	** 
	** @var		array
	*/
	public	$cur_bans;
	
	/**
	** Current user bans from db
	** 
	** @var		array
	*/
	public	$cur_ban_cache;
	
	/**
	** Current browser profile
	** 
	** @var		array
	*/
	public	$prof;
	
	/**
	** Simple var...
	** 
	** @var		bool
	*/
	public	$in		= false;
	
	/**
	** Holds "read" cookie...
	** 
	** @var		array
	*/
	public	$read;
	
	/**
	** Prevents having to check twice
	** 
	** @var		bool
	*/
	private	$check_setup_save	= false;
	
	/**
	** start() - exactly what it looks like
	**
	*/
	public function start()
	{
		
		/* Set browser information */
		$this->prof = array(
			'ip'		=> getenv('REMOTE_ADDR'),
			'user_agent'=> $_SERVER['HTTP_USER_AGENT'],
			/* get_browser() requires "browscap set in php.ini, null warnings from this function */
			'browser'	=> @get_browser( null, true ),
		);
		
	}
	
	/**
	** establishCur() - create the current user
	**
	*/
	public function establishCur()
	{
		
		//-----------------------------------
		// Create User
		//-----------------------------------
		
		/* Login from session */	
		if ( $this->create( $_SESSION['id'], $_SESSION['pass'] ) )
			$this->in = true;
			
		else

		/* Login from cookies */
		if ( cp::set('forceLogin') )
		{
			if ( $this->create( $_COOKIE['cpId'], $_COOKIE['cpPass'], true ) )
			{				
				
				$_SESSION['id'] 	= $_COOKIE['cpId'];
				$_SESSION['pass'] 	= $_COOKIE['cpPass'];
				
				$this->in = true;			
						
			}
		}
		
		/* Did we get logged in, else estab guest */
		if ( $this->in )
		{
			cp::display()->vars['cur'] = $this->cur;
			$uId = $this->cur['id'];
		}
		else
		{
			
			$this->cur = cp::db()->fetch_dep( array(
				'select'=> 'groups.id as gId, groups.*',
				'table' => 'groups',
				'where'	=> 'id="'.cp::set('unloggedGroup').'"',
				'one'	=> true,
			) );
			
			$uId = 0;
			
		}
		
		//-----------------------------------
		// Check for bans
		//-----------------------------------
		
		if ( $this->cur['cur_ban'] )
		{
			
			$this->cur_ban_cache = cp::db()->fetch( array(
				'table'	=> 'users_bans',
				'where'	=> 'user_id="'.$this->cur['id'].'" AND time_end > '.cp::$time,
				'record'=> 'type',
			) );
			
			$this->cur_bans = cp::db()->record;
			
			/* No bans? Change. */
			if ( !$this->cur_ban_cache )
			{
				cp::db()->update_dep('users', $this->cur['id'], array('cur_ban'=>0), 'id', true );
			}		
				
		}		
		
		//-----------------------------------
		// Online user stats
		//-----------------------------------
		
		if ( !cp::$GET['ajax'] )
		{
			cp::db()->insertOnDupeUpdate( 'online', array(
													'id'		=> $this->prof['ip'],
													'userId'	=> $uId,
													'lastClick'	=> cp::$time
													),
											'id', true );
		}
		
		return;
		
	}
	
	/**
	** create() - checks password and creates user
	**
	** @param	int		$id		User id
	** @param	string	$pass	Encrypted password of user
	** @param	bool			Update user cookies
	** @return	bool
	*/
	public function create( $id, $pass, $updateCookies=false )
	{
		
		//-----------------------------------
		// Get User
		//-----------------------------------
		
		if ( !$id  OR !$pass ) return false;
		
		cp::clean( array( $id, $pass ) );
		
		$user = cp::db()->fetch( array(
			'select'=> cp::call('members')->defGroupSel . ', users_secure.*, users.*',
			'table' => 'users_secure',
			'where' => 'users_secure.id="'.$id.'" AND users_secure.pass="'.$pass.'"',
			'joins'	=> array(
				array( 
					'table' => 'users',
					'where'	=> 'users.id=users_secure.id',
					'type'	=> 'left',
				),
				array( 
					'table' => 'groups',
					'where'	=> 'users.groupId=groups.id',
					'type'	=> 'left',
				),
			),
			'r'		=> 'one'
		) );
		
		/* Does user exist? */
		if ( !$user )
			return false;
			
		/* Update cookies */
		if ( $updateCookies )
		{
			$this->cookie('cpId', $id );
			$this->cookie('cpPass', $pass );
		}
		
		//-----------------------------------
		// Prep Member
		//-----------------------------------
		
		/* Really shouldn't be needed by anything. Could create problems. */
		unset( $user['pass'] );
		
		$this->cur = cp::call('members')->prepMember($user);
		
		return true;
		
	}
	
	/*
	** loginError() - checks login forms, returns false if successful
	**
	** @param	string	$email
	** @param	string	$pass	raw password
	** @return	bool
	*/
	public function loginError( $email=false, $pass=false, $cookies=false ) {
		
		//-----------------------------------
		// Brute force defender
		//-----------------------------------
		
		$_SESSION['loginAttemps']++;
		
		if ( !$loginTime )
		{
			$loginTime = 30;
		}
		
		if ( ( cp::$time - $_SESSION['firstLoginAttempt'] ) > $loginTime )
		{
			$_SESSION['firstLoginAttempt'] = cp::$time;
			$_SESSION['loginAttemps'] = 1;
		}
		
		/* Too many attempts */
		if ( $_SESSION['loginAttemps'] > $loginTime )
			return $this->error = 'Too many attempts. Please wait '.($_SESSION['firstLoginAttempt'] + 30 - cp::$time).' seconds before trying again';
	
		
		//-----------------------------------
		// Find User
		//-----------------------------------	
			
		if ( !$email )
			return $this->error = cp::lang('all', 'no_email');

		if ( !$pass )
			return $this->error = cp::lang('all', 'no_pass');
			
		$user = cp::db()->fetch_dep( array(
			'select'	=> 'users.email, users_secure.*',
			'table'		=> 'users',
			'where'		=> 'email="'.$email.'"',
			'join'		=> array(
				'table'	=> 'users_secure',
				'where'	=> 'users.id=users_secure.id',
			),
			'one'		=> TRUE,
		) );
		
		if( !$user )
			return $this->error = cp::lang('all', 'no_user');
			
		/* Check Password */			
		if ( cp::call('members')->encrypt( $pass, $user['string'] ) != $user['pass'] )
			return $this->error = cp::lang('all', 'wrong_pass');
		
		$this->login( $user['id'], cp::call('members')->encrypt( $pass, $user['string'] ) );		
		
		/* False = no error */		
		return false;
		
	}
	
	/**
	** Log the user in
	** 
	** @param	string	$id
	** @param	string	$pass		(Encrypted)
	** @param	bool	$cookies	Set cooies
	*/
	public function login( $id, $pass, $cookies=false )
	{
		
		$_SESSION['id'] 	= $id;
		$_SESSION['pass'] 	= $pass;
		
		if ( $cookies )
		{
			$this->cookie('cpId', $id );
			$this->cookie('cpPass', $pass );
		}
		
	}
	
	/**
	** oauth_login() - creates session from email
	** 
	** @param	string	$email	Email to login with
	*/
	public function oauth_login( $email )
	{

		$user = cp::db()->fetch_dep( array(
			'select'	=> 'users.*, users_secure.*',
			'table'		=> 'users',
			'where'		=> 'users.email="'.$email.'"',
			'join'		=> array(
				'table'	=> 'users_secure',
				'where'	=> 'users.id=users_secure.id',
				'type'	=> 'left',
			),
			'resono'		=> TRUE,
		) );

		/* Set sessions */
		$_SESSION['id'] 	= $user['id'];
		$_SESSION['pass'] 	= $user['pass'];
		
	}
	
	/**
	** logout() - log user out!
	**
	*/
	public function logout() {
		
		unset( $_SESSION['id'] );
		unset( $_SESSION['pass'] );
		unset( $_SESSION['access_token'] );
		
		$this->cookie('cpId', 'dummy', cp::$time-6400 );
		$this->cookie('cpPass', 'dummy', cp::$time-6400 );
		
		/* Kill vars */
		$this->cur = null;
		$this->cur = new StdClass;
		$this->in = false;
		
		cp::display()->splash( cp::lang('all', 'logout_mess'), cp::link(array('')) );
		
	}
	
	/**
	** check_setup() - checks if the user is fully created
	** 
	** @return	bool
	*/
	public function check_setup()
	{

		/* Have we already saved? */
		if ( $this->check_setup_save )
		{
			return $this->check_setup_save;
		}
		
		/* Logged in requirements */
		if ( $this->in AND cp::set('forceDisplay') AND $this->cur['ow_displayName'] )
		{			
			$this->check_setup_save['DP_REQ'] = true;	
		}
		
		return $this->check_setup_save;
		
	}
	
	/**
	** read() - have we read this certain item?
	** setread() - set as read
	** unread()
	** 
	** @param	string	$item			forum, thread etc
	** @param	int		$id				id of item
	** @param	int		$late			timestamp of new item
	** @param	bool	$return_time	whether to return timestamp or bool
	** @return	bool/string
	*/
	public function read( $item, $id, $late, $return_time=false )
	{

		if ( !$this->read )
		{
			$this->read = unserialize( $_COOKIE['cpBoard_read_data'] );
		}
		
		if ( $return_time )	
			return $this->read[ $item ][ $id ];
		else
		{
			if ( $this->read[ $item ][ $id ] < $late )
				return false;
				
			return true;
			
		}
		
		
	}
	
	public function setread( $item, $id )
	{
		
		if ( !$this->read )
		{
			$this->read = unserialize( $_COOKIE['cpBoard_read_data'] );

		}
		
		if ( $id )
			$this->read[ $item ][ $id ] = cp::$time;
		else
			$this->read[ $item ] = cp::$time;
		
		$this->cookie('cpBoard_read_data', serialize( $this->read ) );
		
	}
	
	public function unread( $item, $id )
	{
		
		if ( !$this->read )
		{
			$this->read = unserialize( $_COOKIE['cpBoard_read_data'] );
		}
		
		if ( $id )
			unset($this->read[ $item ][ $id ]);
		else
			unset($this->read[ $item ]);
		
		$this->cookie('cpBoard_read_data', serialize( $this->read ) );
		
	}
	
	/**
	** cookie()
	** 
	** @name	= cookie name
	** @value	= cookie value
	** @time	= time to expire, (def: 1 month);
	** @folder	= folder to save (def: root);
	*/
	public function cookie( $name, $value, $time=false, $folder=false )
	{
		
		$time 	= ( $time ) ?: cp::$time + ( 60 * 43829 );
		$folder	= ( $folder ) ?: '/';
		
		setcookie($name, $value, $time, $folder, null );
		
	}
	
}

?>