<?php

	//===================================================
	//	Cipher Pixel Board © All Rights Reserved
	//---------------------------------------------------
	//	CP.Board
	//		by michaelbenner.net
	//---------------------------------------------------
	//	File created: April 04, 2013 
	//===================================================

class formfunc {
	
	public $cp;
	
	/**
	** dropArray() - turns a sql row into a "dropArray"
	**
	**
	*/
	public function dropArray( $array, $keyKey, $valueKey='id' )
	{
		foreach( $array as $k => $v )
		{
			$dropArray[ $v[ $keyKey ] ] = $v[ $valueKey ];
		}	

		return $dropArray;		
	}
	
	/**
	** 
	*/	
	public function getSkins()
	{		
		
		$files = scandir( ROOT . '/style' );
		
		foreach ( $files as $k => $name )
		{
			if ( ( $name == '.' ) OR ( $name == '..' ) ) continue;
			$dropArray[ $name ] = $name;
		}
		
		return $dropArray;
	}
	
	public function getSettingGroups()
	{
		
		$get = cp::db()->fetch_dep( array(
			'select'=> 'globalcats.text_id, globalcats.name',
			'table'	=> 'globalcats',
		) );
		
		return $this->dropArray( $get, 'name', 'text_id' );
		
	}
	
	public function getMods()
	{
		
		$get = cp::db()->fetch( array(
			'select'=> 'sexyname, name',
			'table'	=> 'modules',
			'key'	=> 'name',
		) );
		
		return $this->dropArray( $get, 'sexyname', 'name' );
		
	}
	
	public function getGroups()
	{
		
		/**
		** NB: Query reduction trial...
		** - If any groups have been gotten before, we've probably gotten all of them
		** - So we'll try this...
		*/
		$get = cp::db()->get( 'groups' );
					
		/*$get = cp::db()->fetch_dep( array(
			'select'=> 'groups.id, groups.name',
			'table'	=> 'groups',
		) );*/
		
		return $this->dropArray( $get, 'name' );
		
	}
	
	public function getPerms()
	{
		
		$get = cp::db()->fetch( array(
			'select'=> 'perm.id, perm.name',
			'table'	=> 'perm',
		) );
		
		return $this->dropArray( $get, 'name' );
		
	}
	
	public function getBoard($exc=false)
	{
		
		/**
		** When editing forums it is important that you cannot move a forum to its own child!
		** $exc is the forums ID and when it gets read it will be ignored
		*/	
		
		/**
		** Assumes class_forums has already been loaded
		*/		
		cp::call('class_forums')->retrieveAll();
		
		/**
		** Create Forum Drop Array...
		*/
		cp::call('class_forums')->loopForums( '0', $this, 'getBoard_callback', true );
		
		/**
		** Remove Children...
		*/
		if ( is_array( $this->build ) )
		{
			foreach ( $this->build as $k => $v )
			{
				
				if ( $bypassUntil AND ( $bypassUntil < $v['level'] ) ) continue;
				
				$bypassUntil = false;
				
				if ( $v['id'] == $exc )
				{
					$bypassUntil = $v['level'];
					continue;
				}
				
				$b[ $v['indent'] . $v['name'] ] = $v['id'];
				
			}
		}
		else
		{
			$b[] = '';
		}
		
    	return $b;
		
	}
	
	public function getBoard_callback( $forum )
	{	
		
		$i = 1;
		$indent = false;
		while( cp::call('class_forums')->depth != $i )
		{
			$indent .= '-';
			$i++;
		}
		
		$indent = ( $indent ) ? $indent . ' ': '';
			
		$this->lastId 	= $forum['id'];
		$this->lastRent	= $forum['parent'];
		$this->build[] = array(
			'indent'	=> $indent,
			'level'		=> cp::call('class_forums')->depth,
			'name'		=> $forum['name'],
			'id'		=> $forum['id'],
		);	
	}	
	
	public function getForums()
	{
		
		$fetch = cp::db()->fetch_dep( array(
			'select'=> 'id, name',
			'table'	=> 'forums',
			'where' => 'parent != "0"',
		) );
		
		return $this->dropArray( $fetch, 'name' );
		
	}
	
	public function getPageType()
	{
		
		return array('Database' => 1, 'Hard Coded' => 2);
		
	}
	
	public function getLinkTypes()
	{
		
		cp::call('link');
		
		$methods = get_class_methods('link');
		
		foreach ( $methods as $k => $v )
		{			
			if ( substr( $v, 0, 5 ) != 'type_' ) continue;			
			$r[ ucfirst(substr( $v, 5 )) ] = substr( $v, 5 );
		}
		
		return $r;
		
	}
	
}
	
?>