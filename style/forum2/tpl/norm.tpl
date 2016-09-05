<!DOCTYPE html>
<html lang="en">
<head>

	<meta charset="utf-8" />

	<title>{$set('siteName')}</title>
	<if cp::set('favicon')> !IGNORE: Chrome and Firefox will load a page twice if a favicon cannot be found :/
	<link rel="icon" type="image/x-icon" href="{$display->vars['lprefix']}{$set('favicon')}" />
	</if>
	<link href="{$display->vars['lprefix']}style/{$display->vars['styleFolder']}/normalize.css" rel="stylesheet" type="text/css">
	<link href="{$display->vars['lprefix']}style/{$display->vars['styleFolder']}/style.css" rel="stylesheet" type="text/css">
	<link href="{$display->vars['lprefix']}js/minified/themes/square.min.css" rel="stylesheet" type="text/css" media="all">
	
	<meta name="description" content="CP-Forum is free community software. With features to match expensive alternatives and a passionate support team CP-Forum&#039;s simplicity and ease of use will be a joy.">
	<meta name="robots" content="index, follow">
	<meta name="keywords" content="">
	
	{$display->javascript()}

</head>

<body>
	{$set('g_analytics')}
	
	<div class="popup">
		<div class="load">
			<img src="{$display->vars['lprefix']}images/ajax-loader.gif" />
		</div>
	</div>
	
	<div class="popup2">
		<div class="ajax_white">
		</div>
	</div>

	<ifnot cp::set('boardOnline')>
		<if cp::logged()->cur['canViewBoardOffline']>
			<div class="redTop">
				<b>{$lang('all', 'board_offline')}</b>
			</div>
		</if>
	</if>
	
	<div class="top">
	
		<div class="page-width header-bar">
		
			<div class="header-title">
				{$set('siteName')}
			</div>
			
			<div class="header-dets">
				<if $logged->in>
					<span class="header-dets-text">
						{$lang('all', 'wb')} <span id="member-popdown-trigger" class="clickable toggleShowSlow" show="member-popdown">{$logged->cur['displayName']}<img class="droparrow" src="{$display->vars['lprefix']}style/{$display->vars['styleFolder']}/images/droparrow.png" /></span>
					</span>
					<span class="header-dets-text">
						<div id="member-popdown" class="popdown">
							<div class="header-pop">
								<div class="left header-pop-left">
									<div class="header-pop-tits">{$lang('all', 'opts')}:</div>
									<div class="header-pop-item"> - <a href="{$link('members,'.cp::logged()->cur['displayName'].',edit')}">{$lang('all', 'edit_profile')}</a></div>
									<!--<div class="header-pop-item"> - <a href="#">{$lang('all', 'settings')}</a></div>-->
									<!--<div class="header-pop-item"> - <a href="#">{$lang('all', 'pm')}</a></div>-->
									<div class="header-pop-item"> - <a href="{$link('board,read')}">{$lang('all', 'mark_read')}</a></div>
									<div class="header-pop-item"> - <a href="{$link('board,logout')}">{$lang('all', 'logout')}</a></div>
								</div>
								<div class="right header-pop-right">
									<img class="avamedium" src="{$logged->cur['avatar']}" /><br />
									<a href="{$link('act=members,n='.cp::logged()->cur['displayName'])}">
										{$logged->cur['htmlName']}
									</a>
								</div>
								<div class="clear"></div>
							</div>
						</div>
					</span>
					<span class="header-dets-text">
						<if $logged->cur['acp']>
						 | <a class="less link" target="_blank" href="{$display->vars['lprefix']}admin.php">{$lang('all', 'admin_cp')}</a>
						</if>
						<if $logged->cur['globalMod']>
						 | <a class="less link" href="{$link('mod')}">{$lang('all', 'mod_cp')}</a>
						</if>						
					</span>
				<else>
					<span class="header-dets-text">
						Already a member?  <span class="link ajax_gen" cmd="cont,ajax_login">Login</span> or <span class="but-signup ajax_gen" cmd="cont,ajax_register">Sign Up</span>
						<!---<span class="link ajax_gen" cmd="cont,ajax_register">{$lang('all', 'reg')}</span>
						 {$lang('all', 'or')}
						 <span class="link ajax_gen" cmd="cont,ajax_login">{$lang('all', 'log')}</span>--->
					</span>			
				</if>
			</div>
				
			<div class="clear"></div>
			
		</div>
		
		<div class="page-width menu">
			<if $logged->in>
				<div class="noti_box">
					<span style="position: relative;">
						<img class="ajax_noti" show="noti-pop" showC="noti-pop-load" src="{$display->vars['lprefix']}images/mail-icon.png" />
						<div id="noti-count-mess" class="noti-num"><if $logged->cur['unreadMess']>
						2</if></div>
					</span>
					<span style="position: relative;">
						<img class="ajax_noti" cmd="cont,ajax_shownoti" src="{$display->vars['lprefix']}images/noti-icon.png" />
						<div id="noti-count" class="noti-num"><if $logged->cur['unreadNoti']>
						{$logged->cur['unreadNoti']}</if></div>
					</span>
				</div>
			</if>		
			{$cont()->menubar()}			
		</div>
		
	</div>
	
	<div id="noti-pop" class="page-width hide">
		<div class="noti-pop">
			<div class="noti-pop-load">
				<img src="{$display->vars['lprefix']}images/ajax-loader.gif" />
			</div>
			<div class="noti-pop-content">
			</div>
		</div>
	</div>
	
	<div class="middle">
		
		<div class="page-width middle-color">
		
			<div class="navtree">
				{$cont()->showNavtree()}
			</div>
			
			<div class="output">
				{$cont()->output}
			</div>
			
			<div class="navtree">
				<div class="left">
					{$cont()->showNavtree()}
				</div>
				<div class="right">
					<a href="#">{$lang('all', 'top')}</a>
				</div>
			</div>
			
		</div>
	
	</div>
	
	<div class="copyright">
		Powered by <a href="http://notionbb.com" target="_blank">NotionBB</a> {$lang('version', 'version')}
	</div>
	
	<!-- Make Sure this image gets preloaded -->
	<image src="{$display->vars['lprefix']}images/ajax-loader.gif" width="1" height="1" border="0">
	
</body>
		
</html>
