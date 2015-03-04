<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
        "http://www.w3.org/TR/html4/loose.dtd">
        
<html>
	
<head>

	<title></title>
	
	<link rel="icon" type="image/x-icon" href="" />
	
	<link href="{$display->vars['lprefix']}style/{$display->vars['styleFolder']}/login.css" rel="stylesheet" type="text/css">
	
	{$display->javascript}

</head>

<body class="adminLoginPage">

<div align="center">

	<div class="title">
		{$lang('all', 'admin_login')}
	</div>

	<div class="splash nomarg">
	
		<div class="splashin">
	
			<if $logged->error>
			<div class="error">{$logged->error}<br /><br /></div>
			</if>
			<form method="post" name="login">
			{$lang('all', 'email')}:<br />
			<input type="text" class="textfield" name="email"><br />
			<br />
			{$lang('all', 'pass')}:<br />
			<input type="password" class="textfield" name="pass"><br />
			<br />
			<input type="submit" class="submit" value="{$lang('all', 'login')}">
			</form>
			
		</div>
		
	</div>
	
</div>
	
</body>

</html>