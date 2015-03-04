	<form action="{$link('register')}" method="post" name="user_create">
	
	<div class="tit-block imp">
		{$lang('all', 'cre_acc')}
	</div>
	<table>
		<tr>
			<td class="reg_td">
				<span class="imp">{$lang('login', 'email')}</span><br />
				<span class="less">{$lang('login', 'email_desc')}</span>
			</td>
			<td><input type="text" name="email" value="" class="input-text-less"></input></td>
		</tr>
		<tr>
			<td class="reg_td">
				<span class="imp">{$lang('login', 'dpname')}</span><br />
				<span class="less">{$lang('login', 'dpname_desc')}</span>
			</td>
			<td><input type="text" name="displayName" value="" class="input-text-less"></input></td>
		</tr>
		<tr>
			<td class="reg_td">
				<span class="imp">{$lang('login', 'pass')}</span><br />
				<span class="less">{$lang('login', 'pass_desc')}</span>
			</td>
			<td><input type="password" name="pass" value="" class="input-text-less"></input></td>
		</tr>
		<tr>
			<td class="reg_td">
				<span class="imp">{$lang('login', 'pass_c')}</span><br />
				<span class="less">{$lang('login', 'pass_c_desc')}</span>
			</td>
			<td><input type="password" name="pass_c" value="" class="input-text-less"></input></td>
		</tr>
	</table>
	
	<br class="clear" />
	<div class="opts opts-marg">
		<input class="but grey" type="submit" name="user_create" value="{$lang('all', 'sub')}">
		<span id="no" class="but grey">{$lang('post', 'close')}</span>
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