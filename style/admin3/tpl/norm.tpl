<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
        "http://www.w3.org/TR/html4/loose.dtd">
        
<html>
	
<head>

	<title>Admin CP | {$set('siteName')}</title>
	
	<if cp::set('favicon')> !IGNORE: Chrome and Firefox will load a page twice if a favicon cannot be found :/
	<link rel="icon" type="image/x-icon" href="{$display->vars['lprefix']}{$set('favicon')}" />
	</if>
	
	<link href="{$display->vars['lprefix']}style/{$display->vars['styleFolder']}/normalize.css" rel="stylesheet" type="text/css">
	<!--<link href="{$display->vars['lprefix']}style/{$display->vars['styleFolder']}/fonts.css" rel="stylesheet" type="text/css">-->
	<link href="{$display->vars['lprefix']}style/{$display->vars['styleFolder']}/style2.css" rel="stylesheet" type="text/css">
	<link href="{$display->vars['lprefix']}js/minified/themes/square.min.css" rel="stylesheet" type="text/css" media="all">
	
	<meta charset="utf-8">
	<meta name="description" content="NBB-Forum is free community software. With features to match expensive alternatives and a passionate support team CP-Forum&#039;s simplicity and ease of use will be a joy.">
	<meta name="robots" content="index, follow">
	<meta name="keywords" content="">
	
	{$display->javascript()}

</head>

<body>
	

	<!---<div class="header">
		<span class="cptitle">NotionBB</span>
		<div class="right userinfo">
			Logged in as: <b>{$logged->cur['displayName']}</b> <i>({$logged->cur['gName']})</i> | Q: {$db->qCount};
		</div>
	</div>--->
	
	<div class="wrapper">
	
		<div class="left nav">
		
			<div class="nav_header">
				CipherPixel
			</div>
			
			<div class="nav_user">
				<div class="nav_ava">
					<img src="{$logged->cur['avatar']}" />
				</div>
				<div class="nav_user_info">
					<div class="nav_user_name">{$logged->cur['displayName']}</div>
					<div class="nav_user_group">{$logged->cur['gName']}</div>
					<div class="nav_user_buts">
						<a href="{$link(array('app'=>cp::$conf['default_app']))}" class="user_but">website</a><a href="{$link(array('app'=>cp::$conf['default_app'],'members',cp::logged()->cur['displayName']))}" class="user_but">profile</a>
					</div>
				</div>
				<br class="clear" />
			</div>
		
			
			<div class="nav_items">				
				{$display->vars['rightbar']}
			</div>
			
		</div>
			
		<div class="right content">
			
			<div class="title">{$cont()->page['title']}</div>
			<div class="right">
				{$display->vars['page_buts']}
			</div>
			<div class="navtree">
				{$cont()->showNavtree()}
			</div>
			<if $dtools->cautionHtml>
				{$dtools->cautionHtml}</if>	
			{$cont()->output}
				
		</div>
		
		<div class="clear"></div>
		
	</div>

	
</div>

<!--- <br class="clear" /> --->

</body>
	
</html>
