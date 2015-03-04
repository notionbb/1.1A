<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
        "http://www.w3.org/TR/html4/loose.dtd">
        
<html>
	
<head>

	<title></title>
	
	<if cp::set('favicon')> !IGNORE: Chrome and Firefox will load a page twice if a favicon cannot be found :/
	<link rel="icon" type="image/x-icon" href="{$display->vars['lprefix']}{$set('favicon')}" />
	</if>
	
	<link href="{$display->vars['lprefix']}style/admin3/login.css" rel="stylesheet" type="text/css">
	
	<meta charset="utf-8">
	<meta name="description" content="CP-Forum is free community software. With features to match expensive alternatives and a passionate support team CP-Forum&#039;s simplicity and ease of use will be a joy.">
	<meta name="robots" content="index, follow">
	<meta name="keywords" content="">
	
	<if $display->vars['splash']['url']>	
		<meta http-equiv="Refresh" content="{$display->vars['splash']['time']}; url={$display->vars['splash']['url']}">
	<else>
		<meta http-equiv="refresh" content="{$display->vars['splash']['time']}" >
	</if>

</head>

<body class="adminLoginPage">

<div align="center">

	<div class="splash">
		<div class="splashin">
			<br />
			<span class="mess">{$display->vars['splash']['mess']}</span><br />
			<br />
			<a href="{$display->vars['splash']['url']}">Click here if your browser does not redirect you</a>
		</div>
	</div>
	
</div>

</body>
	
</html>