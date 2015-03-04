<?php

/*===================================================
//	CipherPixel © All Rights Reserved
//---------------------------------------------------
//	CP-Core
//		by cipherpixel.net
//---------------------------------------------------
//	@author		Michael Benner
//	@date		November 20, 2014 
//---------------------------------------------------
//	Member function library
//=================================================*/

class members {
	
	/**
	** Default Group join select information
	** @var		string
	*/
	public	$defGroupSel = 'groups.*, groups.id AS gId, groups.name AS gName';
	
	/**
	** Last id of created member
	** 
	** @var		int
	*/
	public	$last_id;
	
	//-----------------------------------
	// Getting Members...
	//-----------------------------------
	
	/**
	** Fetch a member or a whole bunch of members
	** 
	** e.g. array(
	**			'id' 	=> array('1', '2'),
	**			'email'	=> 'e@g.com'
	**		 )
	** 
	** @param	array		$where
	** @param	array/bool	$tables		Setting will always get groups else array of other information
	** @param	array		$extra		Extra query information
	** @return	array
	*/
	public function fetch( $where, $tables=true, $extra=false )
	{
		
		/* What other tables to we want? */
		if ( $tables )
		{
			
			/* Get groups */
			$joins[] = array( 'table'	=> 'groups',
							  'where'	=> 'users.groupId=groups.id',
							  'type'	=> 'left',
							);
						
			$select = $this->defGroupSel;
						
			/* Online Members */
			if ( $tables['online'] )
			{
				$joins[] = array(
					'table'		=> 'online',
					'where'		=> 'users.id=online.UserId',
					'type'		=> 'left',
				);
				
				$select .= ', online.lastClick';
			}
			
		}
		
		$select .= ( $select ) ? ', users.*': 'users.*';
		
		$build = array(	'select' => $select,
						'table'  => 'users',
						'where'  => $where,
						'joins'  => $joins,
				);
				
		if ( $extra )
		{
			$build = array_merge( $build, $extra );
		}
		
		//print_r($build);
		
		return cp::db()->fetch( $build );
		
	}
	
	/**
	** prepMember() - prepare member for echoing!
	**
	** @param	array	$array		Member array (or an array of members)
	** @param	array	$options	Extra things to prep
	** @return	array
	*/
	public function prepMember( $array, $options=false )
	{
		
		if ( !$array ) return false;
		
		/* Multidimensional? */
		if ( !$array['id'] OR is_array($array['id'] ) )
		{
			
			foreach( $array as $k => $member )
			{
				$array[$k] = $this->prepMember( $member, $options );				
			}
			
		}
		else
		{
		
			//-----------------------------------
			// Display Name
			//-----------------------------------
			
			if ( !$array['displayName'] )
			{
				$array['displayName'] = $array['realName'];
				$array['path_str']	= cp::link(array('members', '_g', $array['displayName']));
			}
			else
			{
				$array['path_str']	= cp::link(array('members', $array['displayName']));
			}
				
			//-----------------------------------
			// Beautify (html) things...
			//-----------------------------------
			
			$array['htmlName']	= $array['htmlPrefix'] . $array['displayName'] . $array['htmlSuffix'];
			$array['htmlGroup']	= $array['htmlPrefix'] . $array['name'] . $array['htmlSuffix'];
			$array['engSince']	= cp::call('display')->time2str( $array['regDate'] );
			
			//-----------------------------------
			// Online
			//-----------------------------------
			
			if ( $array['lastClick'] )
			{
				$array['engLastClick'] = cp::call('display')->time2str( $array['lastClick'] );
				$array['online']	= ( $array['lastClick'] > ( cp::$time - (15*60) ) ) ? true: false;
			}
			
			//-----------------------------------
			// Extra Options
			//-----------------------------------
			
			/* Create signature */
			if ( $options['sig'] )
			{			
				$array['htmlSig']	= cp::call('bbcode')->bb2html( $array['signature'] );
			}
			
			/* Add pips */
			if ( $options['pips'] )
			{
				$array['pip']	= cp::call('dtools')->getPips( $array['postCount'] );
			}
		
		}

		return $array;
		
	}
	
	/**
	** Prepare group for echoing
	**
	** @param	array	$groups		Array of groups
	** @return	array
	*/
	public function prepGroups( $groups )
	{
		
		foreach ( $groups as $k => $array )
		{
			$groups[ $k ]['htmlGroup']	= $array['htmlPrefix'] . $array['name'] . $array['htmlSuffix'];
		}
		
		return $groups;		
	}
	
	/**
	** Shortcut / mirror for fetch() and prepMember()
	** 
	*/
	public function fetchAndPrep( $array, $tables=true, $options=false )
	{		
		return $this->prepMember( $this->fetch( $array, $tables=true ), $options );		
	}
	
	//-----------------------------------
	// Creating Members
	//-----------------------------------
	
	/**
	** create() - creates a new member given values. Returns false on success
	**
	** @param	array	$array	Array of information to insert, at a minimum
	** 								'displayName' 	- Users display name
	** 								'email'			- Users email
	** 								'pass'			- Password string	
	** @param	bool	$skip	Skip user checks
	** @return	bool
	*/
	public function create( $array, $skip=false )
	{

		/* Array fix */
		if ( $array['new'] )
		{
			$secure	= $array['sec'];
			$array 	= $array['new'];
		}
		
		//-----------------------------------
		// Check user
		//-----------------------------------
		
		if ( !$skip )
		{
					
			/**
			** All required information?
			*/
			if ( !$array['displayName'] )
				return  cp::lang('members', 'dp_no');
			
			if ( !$array['email'] )
				return  cp::lang('members', 'email_no');
				
			if ( !$array['pass'] AND !$secure )
				return  cp::lang('members', 'pass_no');
			
			/**
			** Check uniqueness...
			*/
			$new = cp::db()->fetch_dep( array(
				'select'=> 'displayName, email',
				'table'	=> 'users',
				'where' => 'displayName="'.$array['displayName'].'" OR email="'.$array['email'].'"',
				'one'	=> true
			) );
			
			if ( $new['email'] == $array['email'] )
				return cp::lang('members', 'email_not_u');
				
			if ( $new['displayName'] == $array['displayName'] )
				return cp::lang('members', 'displayName');
				
		}
			
		//-----------------------------------
		// Create Password hashes
		//-----------------------------------
		
		if ( !$secure )
		{
			$tmp = $this->hash( $array['pass'] );
			$secure = array(
				'pass' 		=> $tmp['pass'],
				'string'	=> $tmp['key'],
			);
			unset( $array['pass'] );
		}
		
		//-----------------------------------
		// Default Information
		//-----------------------------------
		
		/* Default Group */
		if ( !$array['groupId'] )
			$array['groupId'] = ( cp::set('validateMembers') ) ? cp::set('validatingGroup'): cp::set('newmemGroup');
			
		/* Default Avatar */	
		if ( !$array['avatar'] )
			$array['avatar'] = cp::$conf['link_prefix'] . cp::set('defAvatar');
		
		$array['regDate']	= cp::$time;

		//-----------------------------------
		// Insert Member
		//-----------------------------------
		
		cp::db()->insert( 'users', $array );
		cp::db()->insert( 'users_secure', $secure );

		$this->last_id = cp::db()->lastId;
		
		/* Group Counts */
		cp::db()->update_dep( 'groups', $array['groupId'], array('userCount' => '+1') );
		
		/* Latest Member */
		cp::db()->update_dep( 'miscstat', array('arrayTitle' => 'latestUser'), array('value' => $this->last_id ) );
		
		/* Misc stats */
		cp::db()->update_dep( 'miscstat', array('arrayTitle' => 'totalUsers'), array('value' => '+1' ) );
		
		return false;
		
	}
	
	/**
	** oauth_create() - creates a user from an oauth call
	** 
	** @param	array	$array	array of user information
	** @param	array	$secure	array for users_secure
	** @return	bool|string
	*/
	public function oauth_create( $array, $secure )
	{
		
		/* Check unique */
		$new = cp::db()->fetch_dep( array(
			'table'	=> 'users',
			'where' => 'email="'.$array['email'].'"', 
			'one'	=> true
		) );
		
		if ( $new )
		{
			return;
		}
		
		/* Set default group, validating not required as we validate with the login */
		if ( !$array['groupId'] )
		{
			$array['groupId'] = cp::set('newmemGroup');
		}
		
		/* Set to overwrite display name */
		$array['ow_displayName'] = 1;
		
		/* Create a random password we can use for authentication */		
		$secure['pass'] = $this->encrypt( $array['avatar'] . $array[ rand(1,5) ] . cp::$time . microtime(), rand(11111, 99999) );
		
		/* Create */
		$this->create( array( 'new' => $array, 'sec' => $secure ), true );
		return;
		
	}
	
	//-----------------------------------
	// Encryption
	//-----------------------------------
	
	/*
	** encrypt() and hash() - encrypt encrypts the password with a key, hash creates a key and encryption
	**
	** @pass	= Password string
	** @key		= Key to encrypt with
	** @str		= Password string
	*/
	public function encrypt( $pass, $key ) {
		
		return base64_encode(md5(md5(base64_encode( $pass . $key ))));
		
	}
	
	public function hash( $str ) {
		
		$key 	= rand(11111, 99999);
		
		$pass	= $this->encrypt( $str , $key );
		
		return array(
			'key' 	=> $key,
			'pass' 	=> $pass,
		);
		
	}
	
	//-----------------------------------
	// Misc.
	//-----------------------------------
	
	/**
	** notify() - an array of user values
	** 
	** @param	array	$id_array	Id of user arrays
	** @param	array	$noti_array	Notification data for noti table
	*/
	public function notify( $id_array, $noti_array )
	{
		
		if ( !is_array( $id_array ) ) return;
		
		/* Repetitive Notifications */
		cp::db()->fetch_dep( array(
			'select'=> '`id`, `user_id`, `from`',
			'table' => 'notifications',
			'where' => '`type`="'.$noti_array['type'].'" AND `thread_id`="'.$noti_array['thread_id'].'" AND `read`="0"',
			'record'=> array('id' => 'id', 'user_id' => 'user_id'),
		) );
		
		$noti_ids = cp::db()->record['id'];
		$ignore_ids = cp::db()->record['user_id'];
		
		if ( $ignore_ids )
		{
			
			foreach( $noti_ids as $noti_id )
			{
				$noti = cp::db()->get( 'notifications', $noti_id );
				if ( $noti['from'] == $noti_array['from'] ) continue;
				$count_on[] = $noti['id'];
			}
			
			if ( $count_on )
			{
				cp::db()->update_dep( 'notifications', $count_on, array('repetitive'=>'+1'), 'id', false, 'OR' );
			}
			
		}	
		
		/* Create Insert Array */		
		foreach( $id_array as $id )
		{
			if ( is_array( $ignore_ids ) AND in_array( $id, $ignore_ids ) ) continue;
			$build[] = array_merge( $noti_array, array('user_id'=>$id) );
		}
		
		if ( $build )
		{
			cp::db()->insertMany( 'notifications', $build );
			cp::db()->update_dep( 'users', $id_array, array('unreadNoti'=>'+1'), 'id', false, 'OR' );
		}
		
	}
	
	/**
	** fetchMember() - gets a member from database. Preps them automatically
	**
	** @id	= Array of member fetch requirements ('select' => 'XX')
	** 		= ID of member to get
	** @key = Select member by key
	** 
	** /// Depreciated use $this->fetch()
	** 
	*/
	public function fetchMember( $array, $key='id' )
	{
		
		/**
		** Default Selection...
		*/
		$select = 'users.*, ' . $this->defGroupSel;
		
		/**
		** Define altered
		*/
		if ( is_array( $array ) )
		{
			$array['select'] = $array['select'] . $select;
		}
		else
		{
			
			$val = $array;
			
			$array = array(
				'select'=> $select,
				'table' => 'users',
				'where'	=> 'users.'.$key.'="'.$val.'"',
				'join'	=> array(
					'table' => 'groups',
					'where'	=> 'users.groupId=groups.id',
				),
				'ret'	=> $val,
			);
			
		}
		
		if ( !$array ) return false;
		
		return $this->prepMember( cp::db()->fetch_dep( $array ) );
		
	}
	
}

?>