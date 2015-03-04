<?php

	/*===================================================
	//	CipherPixel © All Rights Reserved
	//---------------------------------------------------
	//	CP-Core
	//		by cipherpixel.net
	//---------------------------------------------------
	//	File created: September 21, 2014 
	//=================================================*/
	
class perm {
	
	/**
	** check() - checks action is allowed
	**
	** @row		= (haystack) row where p_ cols are held	i.e. Forum Row
	** @action	= string (or array) of action(s) (read, start etc)
	** @needle	= id to look for
	*/
	public function check( $row, $action, $needle=false )
	{
		
		/**
		** Do we want to check an array of things?
		*/
		if ( is_array( $action ) )
		{

			foreach( $action as $act )
			{
				if ( !$this->check( $row, $act, $needle ) ) return false;			
			}

			return true;
			
		}

		/**
		** IF not set, default to current permission set
		*/
		$needle = ( $needle ) ?: cp::logged()->cur['permSet'];
		
		$action_array = array_filter( explode( ',', $row[ 'p_' . $action ] ) );

		if ( in_array( $needle, $action_array) )
			return true;
		else
			return false;
		
	}
	
	/**
	** ban_check() - checks if a ban is in place for current user
	** 
	** @param	string	$type	Ban Type
	** @return	bool
	*/
	public function ban_check( $type )
	{
		
		if ( cp::logged()->cur_bans AND in_array( $type, cp::logged()->cur_bans ) )
			return true;
			
		return false;
		
	}
	
	/**
    ** allowMe() - builds the REGEX "where" clause to retrieve allowed perm rows
    ** 
    ** @type	= 'read', what perm type are we checking or array of perm types (p_) can be obmitted)
    */
    public function allowMe( $type, $prefix='p_' )
    {
	    	
	    if ( is_array( $type ) )
	    {
		    foreach( $type as $k => $check )
		    {
			    if ( $b ) $b .= ' OR ';
			    $b .= cp::db()->permRegexBuild( $prefix . $check, cp::logged()->cur['permSet'] );
		    }
		    return $b;
	    }
	    else
	    {	    	
	    	return cp::db()->permRegexBuild( $prefix . $type, cp::logged()->cur['permSet'] );
    	}
	    
    }	
	
}

?>