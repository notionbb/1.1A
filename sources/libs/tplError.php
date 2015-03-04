<?php

	/**
	** This file is the error template for CP-Core.
	** 
	*/

	if ( !$allow )
	{
		die();
	}

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
			        "http://www.w3.org/TR/html4/loose.dtd">
<html>
	
<head>

	<title><?= $title ?></title>
	<style>
	
		body {
				overflow-y: scroll;
				margin: 0px;
				padding: 0px;
				font-family:Verdana, Arial, Helvetica, sans-serif;
				font-size: 12px;
				background: #4A6068;
				color: #1A1A1A;
				margin-bottom: 15px;
		}
		
		.holder {
			margin-left: auto;
			margin-right: auto;
			width: 600px;
			background: #DFDFDF;
			border: 2px solid #5C5C5C;
			border-top: 0px;
			border-bottom-left-radius: 10px;
			border-bottom-right-radius: 10px;
			padding: 20px;
		}
		
		.tits {
			margin-bottom: 10px;
			font-size: 46px;
		}
		
		
		
	</style>

</head>

<body>

	<div class="holder">
		<div class="tits"><?= $errorTit ?></div>
		<div><?= $errorMess ?></div>
	</div>

</body>

</html>