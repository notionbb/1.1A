<?php

	//===================================================
	//	Cipher Pixel Board © All Rights Reserved
	//---------------------------------------------------
	//	CP.Board
	//		by michaelbenner.net
	//---------------------------------------------------
	//	File created: April 04, 2013 
	//===================================================

class class_forums {
	
	/**
	** Saved Forums
	*/
	public	$forum_array;
	
	/**
	** Forum Map
	*/
	public	$forum_map = array();
	public	$hirec_map = array();
	
	/**
	** Current Depth when looping through...
	*/
	public	$depth = 0;
	
	/**
	** retrieveReadable() - Get only forums we can read
	**
	**
	*/
	public function retrieveReadable( $idFromOther=false )
	{
		
		$forum_build = cp::db()->build( array(
			'table'	=> 'forums',
			'where'	=> 'parent="0" OR '.cp::call('perm')->allowMe( 'read' ),
			'join'	=> array(
				'table'	=> 'perm_reg',
				'where'	=> 'perm_reg.type="forum" AND forums.id=perm_reg.type_id',
				'type'	=> 'left',
			),
			'order'	=> '`order` asc',
		));
		
		return $this->retrieveAll( $idFromOther, $forum_build );
		
	}	
	
	/**
	** retrieveAll() - Get forums with permissions attached
	** 
	** @idFromOther	= array() e.g. 'slug' => 'XX' returns the id where the slug is that
	** @forums		= forum array already fetched
	*/
	public function retrieveAll( $idFromOther=false, $forum_build=false )
	{
		
		if ( !$forum_build )
		{
		
			# Get forums
			$forum_build = cp::db()->build( array(
				'table'	=> 'forums',
				'join'	=> array(
					'table'	=> 'perm_reg',
					'where'	=> 'perm_reg.type="forum" AND forums.id=perm_reg.type_id',
					'type'	=> 'left',
				),
				'order'	=> 'forums.order asc',
			));
			
		}
		
		/* Map forums */		
		if ( !cp::db()->result() )
		{
			return false;
		}
		else
		{
		
			while( $forum_data = cp::db()->toArray() )
			{				
				$this->forum_map[ $forum_data['parent'] ][] = $forum_data['id'];
				
				if ( $idFromOther AND ( $forum_data[ key( $idFromOther ) ] ) == $idFromOther[ key( $idFromOther ) ] )
				{
					$id = $forum_data['id'];
				}
				
				$this->forum_array[ $forum_data['id'] ] = $forum_data;
			}
			
		}
		
		if ( $idFromOther )
			return $id;
		else
			return $this->forum_array;	
		
	}
	
	/**
	** loopForums() - loops forums!
	**
	** @parentId		= Parent to show forums for (0 = whole board)
	** @callback_scope	= Scope for function callback
	** @callback_name	= Callback function name
	** @callback_first	= true to callback before recursion, false to callback after
	*/
	public function loopForums( $parentId='0', $callback_scope=false, $callback_name=false, $callback_first=false )
	{
		
		$this->depth++;
		
		if ( !$this->forum_map[ $parentId ] ) return $this->depth--;
		
		/**
		** Do we need to create a path?
		*/
		if ( $parentId != '0' AND !$this->cur_path )
			$this->make_path( $parentId );
		
		foreach ( $this->forum_map[ $parentId ] as $forumId )
		{
			
			# Current Forum
			$forum = $this->forum_array[ $forumId ];
			
			# Forum Path
			$this->cur_path[] 		= $forum['slug'];
			$this->cur_path_id[] 	= $forum['id'];
			
			$forum['path'] 		= $this->cur_path;
			$forum['path_id'] 	= $this->cur_path_id;
			
			#Update Form
			$this->forum_array[ $forumId ] = $forum;

			# Call Back - this is first because we want to build forums in order
			if ( $callback_scope AND $callback_first )
				cp::methodCall( $callback_scope, $callback_name, $forum );			
			
			# Check for Children...
			$this->loopForums( $forum['id'], $callback_scope, $callback_name, $callback_first );
			
			# Call Back - this is second because we want to build subforum html first
			if ( $callback_scope AND !$callback_first )
				cp::methodCall( $callback_scope, $callback_name, $forum );
				
			# Cut own path off again
			array_pop($this->cur_path);
			array_pop($this->cur_path_id);
			
		}
		
		# Cut One off the path
		array_pop($this->cur_path);
		array_pop($this->cur_path_id);
		
		$this->depth--;
		
	}
	
	/**
	** make_path() - makes cur_path if we aren't starting from category
	*/
	public function make_path( $parentId )
	{
		$this->cur_path 	= array();
		$this->cur_path_id 	= array();
		$this->make_path_callback( $parentId );	
		
		$this->saved_path 		= $this->cur_path;
		$this->saved_path_id 	= $this->cur_path_id;
		
		return $this->saved_path;
								
	}
	
	public function make_path_callback($id)
	{
		
		foreach( $this->forum_map as $parent => $children )
		{
			
			if ( !in_array( $id, $children ) ) continue;
			
			array_unshift( $this->cur_path, 	$this->forum_array[ $id ]['slug'] );
			array_unshift( $this->cur_path_id, 	$this->forum_array[ $id ]['id'] );
			
			$this->make_path_callback( $parent );
			
		}
		
	}
	
	/**
	** extractUserIds() - extracts user Ids from forum_array
	** 
	** @array	= array of forum IDs
	*/
	public function extractUserIds( $array )
	{
		
		if ( !$build ) return false;
		
		foreach ( $array as $forumId )
		{	
			if ( $this->forum_array[ $forumId ]['lastPostUser'] != '0' )
				$build[] = $this->forum_array[ $forumId ]['lastPostUser'];			
		}
		
		return $build;
		
	}
	
	/**
	** extractThreadIds()
	** 
	** @array	= array of forum IDs
	*/
	public function extractThreadIds( $array )
	{
		
		if ( !$array ) return false;
		
		foreach ( $array as $forumId )
		{	
			if ( $this->forum_array[ $forumId ]['lastPostThread'] != '0' )
				$build[] = $this->forum_array[ $forumId ]['lastPostThread'];			
		}
		
		return $build;
		
	}
	
	/**
	** getpath() - gets path to forum, if not made will get forums
	** 
	** @id	= forum id
	*/
	public function getpath($id)
	{
		
		if ( !$this->forum_array )
		{
			$this->retrieveReadable();
			$this->loopForums();
		}
		
		return $this->forum_array[$id]['path'];
		
	}
	
	/**
	** navtree()
	** 
	** @forumId
	*/
	public function navtree( $forumId )
	{
		
		/**
		** Believe it or not I actually really hate loops
		*/
		
		# Navtree ARray
		$array = array();
		
		$i = 0;
		
		# Through the forum path
		foreach( $this->forum_array[ $forumId ]['path_id'] as $forum_id )
		{
			
			# Get Forum
			$forum 	= $this->forum_array[ $forum_id ];
			
			# If we have built a path, use it. If we haven't then take required elements from saved_path
			if ( !$forum['path'] )
			{
				$add = 0;
				while ( $add <= $i )
				{
					$forum['path'][] = $this->saved_path[ $add ];
					$add++;
				}
			}
			
			# Add to forum path
			cp::cont()->navtree( array( $forum['name'] => cp::link( $forum['path'] ) ) );
			
			$i++;
			
		}
		
	}
	
	/**
	** 
	*/
	
}

?>