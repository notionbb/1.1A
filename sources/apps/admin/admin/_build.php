<?php

	/*===================================================
	//	CipherPixel © All Rights Reserved
	//---------------------------------------------------
	//	CP-Core
	//		by cipherpixel.net
	//---------------------------------------------------
	//	File created: November 05, 2014 
	//=================================================*/
	
	/**
	** CP Application Exporter
	** 
	** File not required by general users, feel free to remove.
	*/
	
class admin__build extends controller {
	
	/**
	** Content to put into div
	** 
	** @var		string
	*/
	public	$output;
	
	public function main()
	{
		
		if ( cp::$GET['export'] )
		{
			$this->export( cp::$GET['export'] );
		}
		else
		if ( cp::$GET['package'] )
		{
			$this->package( cp::$GET['package'] );
		}
		else
		{
			$this->listapps();
		}
		
		cp::cont()->output = '<div class="schema_table">'.$this->output.'</div>';
		
		return true;
		
	}
	
	/**
	** List Links to apps
	** 
	*/
	private function listapps()
	{
		$res = cp::db()->fetch( array(
			'select'=> 'name',
			'table'	=> 'modules',
			'r'		=> 'res',
		) );
		
		$this->output .= '';
		$this->output .= '<b>Build Flow:</b><br />';
		$this->output .= '<a href="?page=_dbman">1</a>. Database Manager<br />';
		$this->output .= '<a href="?page=_dbman#first_q">2</a>. First Query Maker<br />';
		$this->output .= '3. Build:<br />';
		
		
		while( $m = $res->fetch_assoc() )
		{
			$modules_array[ $m['name'] ] = $m['name'];
			$this->output .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="?page=_build&export='.$m['name'].'">'.$m['name'].'</a><br />';
			$package .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="?page=_build&package='.$m['name'].'">'.$m['name'].'</a><br />';
		}
		
		$this->output .= '4. Package:<br />';
		$this->output .= $package;
		
		$this->output .= '';
		
	}
	
	/**
	** Export application (save it's _install class)
	** 
	** @param	string	$app	Name of application
	*/
	private function export( $app_name )
	{
		
		//-----------------------------------
		// Get database structure
		//-----------------------------------
		
		$app = cp::db()->fetch( array(
			'table'	=> 'modules',
			'where'	=> 'name="'.$app_name.'"',
			'key'	=> 'name',
			'r'		=> 'one',
		) );
		
		$schema = cp::call('port')->get_schema_row();
		
		$json_structure = cp::call('port')->get_json_structure( $schema, $app_name );
		
		//-----------------------------------
		// Get compulsory rows
		//-----------------------------------
		
		 $comp_data = cp::call('port')->get_comp( $app['unique_id'] );
		 $comp_qs	= cp::call('port')->make_insert_array( $comp_data );
		
		//-----------------------------------
		// Get first data
		//-----------------------------------
		
		$first_data = cp::call('port')->get_schema_row('init_'. $app_name);
		$first_qs	= cp::call('port')->make_insert_array( $first_data );
		
		//-----------------------------------
		// Export
		//-----------------------------------
		
		$this->build_export_file( $app, $json_structure, $comp_qs, $first_qs );
		
		$this->output = 'Application Built';
		
	}
	
	/**
	** Pacake application
	** 
	** @param	string	$app	Name of application
	*/
	private function package( $app_name )
	{
		
		//-----------------------------------
		// Init
		//-----------------------------------
		
		$app = cp::db()->fetch( array(
			'table'	=> 'modules',
			'where'	=> 'name="'.$app_name.'"',
			'key'	=> 'name',
			'r'		=> 'one',
		) );
		
		$zip = new ZipArchive();
		
		$filename = './build/'. $app['sexyname'] .'-' . str_replace('.', '-', $app['ver']) .'.zip';

		if ( $zip->open( $filename, ZipArchive::CREATE )!== true ) {
		    exit("cannot open <$filename>\n");
		}
		
		//-----------------------------------
		// Make Package
		//-----------------------------------
		
		/* Package Core Files as well */
		if ( $app['name'] == 'forum' )
		{
			
			$file_paths = $this->scan_whole_dir( ROOT );
			$r_len = strlen( ROOT );
			
			foreach( $file_paths as $abs_path )
			{
				$zip_path = substr( $abs_path, $r_len + 1 );
				
				if ( $zip_path == 'init.php' ) continue;
				if ( $zip_path == 'conf.php' ) continue;
				if ( $zip_path == 'composer.json' ) continue;
				if ( $zip_path == 'composer.lock' ) continue;
				if ( $zip_path == 'README.md' ) continue;
				
				if ( $zip_path == '_init.php' OR $zip_path == '_conf.php' )
				{
					$zip_path = substr( $zip_path, 1 );
				}
								
				$zip->addFile( $abs_path, 'upload/' . $zip_path );
			}
			
			/* Add Application Files */
			$file_paths = $this->scan_whole_dir( ROOT . '/sources/apps/admin' );
			$r_len = strlen( ROOT );
			
			foreach( $file_paths as $abs_path )
			{
				$zip_path = substr( $abs_path, $r_len + 1 );				
				$zip->addFile( $abs_path, 'upload/' . $zip_path );
			}

			/* Add Application Skins */
			$file_paths = $this->scan_whole_dir( ROOT . '/style/'. cp::$apps['admin']['defSkin'] );

			$r_len = strlen( ROOT );
			
			foreach( $file_paths as $abs_path )
			{
				$zip_path = substr( $abs_path, $r_len + 1 );				
				$zip->addFile( $abs_path, 'upload/' . $zip_path );
			}
			
		}

		/* Add Application Files */
		$file_paths = $this->scan_whole_dir( ROOT . '/sources/apps/' . $app['name'] );		
		$r_len = strlen( ROOT );
		
		foreach( $file_paths as $abs_path )
		{
			$zip_path = substr( $abs_path, $r_len + 1 );				
			$zip->addFile( $abs_path, 'upload/' . $zip_path );
		}

		/* Add Application Skins */
		$file_paths = $this->scan_whole_dir( ROOT . '/style/'. $app['defSkin'] );		
		$r_len = strlen( ROOT );
		
		foreach( $file_paths as $abs_path )
		{
			$zip_path = substr( $abs_path, $r_len + 1 );				
			$zip->addFile( $abs_path, 'upload/' . $zip_path );
		}			
		
		$this->output = 'Application Packaged<br />';
		$this->output .= 'numfiles: ' . $zip->numFiles;
		$this->output .= 'status:' . $zip->status;
		
		$zip->close();
		
	}
	
	/**
	** Scan dir callback
	** 
	** @param
	** @param
	** @return	array
	*/
	private function scan_whole_dir( $path )
	{
		
		$scan = scandir( $path );
		
		foreach( $scan as $k => $value )
		{
			if ( substr( $value, 0, 1 ) == '.' ) continue;
			
			// Skip Build Folder
			if ( $value == 'build' ) continue;
			
			// Skip cpext
			if ( $value == 'cpext' ) continue;
			
			// Skip conf.php
			if ( $value == 'conf.php' ) continue;
			
			// Skip Cache Folder
			if ( $path == ROOT . '/sources/cache' AND $value != 'index.html' ) continue;
			
			// Skip Upload Folder
			if ( $path == ROOT . '/uploads' AND $value != 'index.html' ) continue;
			
			// Skip Apps Folder
			if ( $path == ROOT . '/sources/apps' ) continue;
			
			// Skip skin folder
			if ( $path == ROOT . '/style' ) continue;
			
			$file = $path .'/'. $value;
			
			if ( is_dir( $file ) )
			{
				$fol = $style_folders;
				$files = cp::merge( $files, $this->scan_whole_dir( $file, $style_folders ) );
			}
			else
			{
				$files[] = $file;
			}
			
		}
		
		return $files;
		
	}
	
	/**
	** Builds export file string
	** 
	*/
	public function build_export_file( $app, $structure, $comp, $first )
	{
		
		/* Build File */
		$file = $this->file_template();
		
		//-----------------------------------
		// Structure json
		//-----------------------------------
		
		$build = "'".$structure."';\n";
		
		//-----------------------------------
		// Comp Rows
		//-----------------------------------
		
		//$this->init_queries
		
		$build_initq = "array(\n";
		
		if ( is_array( $comp ) )
		{
			
			foreach( $comp as $table => $array )
			{
				$build_initq .= "\t\t\t'".$table."' => ".$array."\t\n";
			}
			
		}
		
		$build_initq .= "\t\t);\n";
		
		//-----------------------------------
		// First Data
		//-----------------------------------
		
		$build_dataq = "array(\n";
		
		if ( is_array( $first ) )
		{
			
			foreach( $first as $table => $array )
			{
				$build_dataq .= "\t\t\t'".$table."' => ".$array."\t\n";
			}
			
		}
		
		$build_dataq .= "\t\t);\n";
		
		//-----------------------------------
		// Replace into file
		//-----------------------------------
		
		$file = str_replace("%CP_REPLACE_STRUCQ%", $build, $file);
		
		$file = str_replace("%CP_REPLACE_INITQ%", $build_initq, $file);
		
		$file = str_replace("%CP_REPLACE_DATAQ%", $build_dataq, $file);
		
		$file = str_replace("%CP_REPLACE_UNIQUE_ID%", $app['unique_id'], $file);
		$file = str_replace("%CP_REPLACE_NAME%", $app['name'], $file);
		$file = str_replace("%CP_REPLACE_SEXYNAME%", $app['sexyname'], $file);
		$file = str_replace("%CP_REPLACE_DESC%", $app['desc'], $file);
		$file = str_replace("%CP_REPLACE_VER%", $app['ver'], $file);
	
		$path = ROOT . '/sources/apps/' . $app['name'] . '/classes/' . $app['name'] . '_install.php';
		
		/* Create Folder if required */
		$dirname = dirname($path);
		if (!is_dir($dirname))
		{
		    mkdir($dirname, 0755, true);
		}

		/**
		** Create file and save
		*/
		$fp = fopen( $path, "wb");		
		fwrite($fp, $file);		
		fclose($fp);
		
	}
	
	public function file_template()
	{
		return "<?php
	
class app_install_%CP_REPLACE_NAME% {
	
	/**
	** This file has been automatically generated
	** TS: ".cp::$time." (".date("jS \of F Y h:i:s A", cp::$time).")
	** 
	** Do not edit. Use CP Build Tools
	*/
	
	public function info()
	{
		return array(
			'unique_id'	=> '%CP_REPLACE_UNIQUE_ID%',
			'name' 		=> '%CP_REPLACE_NAME%',
			'sexyname' 	=> '%CP_REPLACE_SEXYNAME%',
			'desc' 		=> '%CP_REPLACE_DESC%',
			'ver'  		=> '%CP_REPLACE_VER%',
		);
	}
	
	public function structure_queries()
	{
		
		return %CP_REPLACE_STRUCQ%
		
	}
	
	public function init_queries()
	{
		
		return %CP_REPLACE_INITQ%
		
	}
	
	public function first_data()
	{
		
		return %CP_REPLACE_DATAQ%
		
	}
	
}

?>";

	}	
	
}

?>