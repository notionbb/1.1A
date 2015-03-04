<?php

die( "EXAMPLE IS LOCKED" );

if ( !$_POST['upload'] )
{
	
	?>
	
	<form method="POST" enctype="multipart/form-data">
	    <input type="file" name="foo" value=""/>
	    <input type="submit" name="upload" value="Upload File"/>
	</form>

	<?php
	
	
}
else
{

	require('sources/libs/Upload/Autoloader.php');
	
	$auto = new Upload\Autoloader;
	
	$auto->register();
	$auto->autoload('\Upload\Storage\FileSystem');
	$auto->autoload('\Upload\File');
	
	try {
		
		// ROOT . '/uploads';
		$storage = new \Upload\Storage\FileSystem('C:/xampp/htdocs/cpdev2');
		$file = new \Upload\File('foo', $storage);
		
		// Validate file upload
		// MimeType List => http://www.webmaster-toolkit.com/mime-types.shtml
		$file->addValidations(array(
		    // Ensure file is of type "image/png"
		    new \Upload\Validation\Mimetype('image/png'),
		
		    //You can also add multi mimetype validation
		    //new \Upload\Validation\Mimetype(array('image/png', 'image/gif'))
		
		    // Ensure file is no larger than 5M (use "B", "K", M", or "G")
		    new \Upload\Validation\Size('5M')
		));
		
		// Access data about the file that has been uploaded
		$data = array(
		    'name'       => $file->getNameWithExtension(),
		    'extension'  => $file->getExtension(),
		    'mime'       => $file->getMimetype(),
		    'size'       => $file->getSize(),
		    'md5'        => $file->getMd5(),
		    'dimensions' => $file->getDimensions()
		);
		
		// Try to upload file
		try {
		    // Success!
		    $file->upload();
		} catch (\Exception $e) {
		    // Fail!
		    $errors = $file->getErrors();
		}
		
		
	}
	catch ( Exception $e )
	{
		echo 'Exception Caught: '. $e->getMessage();
	}
	
}

?>