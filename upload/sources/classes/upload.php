<?php

	/*===================================================
	//	CipherPixel © All Rights Reserved
	//---------------------------------------------------
	//	CP-Forum
	//		by cipherpixel.net
	//---------------------------------------------------
	//	File created: September 30, 2014 
	//=================================================*/
	
class upload {
	
	/**
	** This upload class provides a gateway to Josh Lockhart's "Upload".
	** URL: https://github.com/codeguy/Upload
	*/
	
	/**
	** Allowed file types
	*/
	public $types;
	
	/**
	** Autoloader class
	*/
	private	$auto;
	
	/**
	** Other Classes
	*/
	private $storage;
	private	$file;
	
	/**
	** Upload Directory
	*/
	private	$dir;
	private	$user_dir;
	public	$skin_dir;
	
	/**
	** Create Upload Classes etc
	*/
	public function __construct()
	{
		
		/* Composer Autoloader */
		cp::load_composer_autoload();
		
		/* Estab Vars */	
		$this->user_dir = ROOT . '/' . cp::set('userUploadDir');
		$this->skin_dir = ROOT . '/style/' . cp::display()->skinFolder . '/images';
		
		/* Allowed types */
		$this->setAllowed();
		
	}
	
	/**
	** file() - access Upload/File
	** 
	** @file	= post name of file
	** @dir		= directory
	*/
	public function file($file=false, $dir=false)
	{
		
		# Have we already loaded the class?
		if ( $this->file )
			return $this->file;
			
		try {
		
			# Set Directory
			$this->dir = ( $dir ) ?: $this->user_dir;
			
			# Load Storage Class
			if ( !$this->storage )
				$this->storage = new \Upload\Storage\FileSystem( $this->dir );
			
			# Save and return
			$this->file = new \Upload\File($file, $this->storage);
			
			return $this->file;
			
		}
		catch ( Exception $e )
		{
			Debug::master('none', 'Exception Caught: '. $e->getMessage());
		}
		
	}
	
	/**
	** allowed()
	*/
	public function setAllowed()
	{		
		$this->types = explode( ',', cp::set('allowedFileType') );		
	}
	
	/**
	** lastUrl()
	*/
	public function lastUrl()
	{
		return cp::display()->vars['lprefix'] . $this->lastRelPath();
	}
	
	/**
	** lastRelPath() - returns a relative path
	*/
	public function lastRelPath()
	{
		return substr( $this->dir, strlen( ROOT ) + 1 );
	}
	
}

?>