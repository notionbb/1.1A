	<form class="header-login-form" method="post" name="login">
	
	<div class="tit-block imp">
		{$lang('all', 'log')} {$lang('all', 'to')} {$set('siteName')}
	</div>
	
	<table>
		<tr>
			<td class="reg_td">
				<span class="imp">{$lang('login', 'email')}:</span><br />
				<span class="less">{$lang('login', 'email_desc2')}</span>
			</td>
			<td><input type="text" name="log_email" class="input-text-less"></input></td>
		</tr>
		<tr>
			<td class="reg_td">
				<span class="imp">{$lang('login', 'pass')}</span><br />
				<span class="less">{$lang('login', 'pass_desc2')}</span>
			</td>
			<td><input type="password" name="log_pass" class="input-text-less"></input></td>
		</tr>
	</table>
	
	<br class="clear" />
	
	<div class="opts opts-marg">
		<input class="but grey" type="submit" name="login" value="{$lang('all', 'log')}">
		<span id="no" class="but grey">{$lang('all', 'canc')}</span>
	</div>
	
	</form>
	
	<if $display->vars['alt_methods']>	
	<div class="tit-block imp">
		{$lang('all', 'log_quick')}
	</div>
	</if>
	
	<if $display->vars['login_google']>
	<div class="but-login-hold">
		{$display->vars['login_google']}
	</div></if>
	
	<if $display->vars['login_facebook']>
	<div class="but-login-hold">
		{$display->vars['login_facebook']}
	</div></if>