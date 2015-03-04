<form method="post" name="mem_sea">

	{$lang('member', 'find_dp')}
	
	<select name="dp_name_q">
		<option value="begins">{$lang('member', 'b_with')}</option>
		<option value="contain">{$lang('member', 'cont')}</option>
	</select>
	
	<input name="display_name" type="text" class="input-text-little"></input>
	
	{$lang('member', 'in_gro')}
	
	<select name="group">
		{$display->vars['g_opts']}
	</select>
	
	{$lang('member', 'order')}
	
	<select name="order_by">
		<option value="reg_date">{$lang('member', 'jd')}</option>
		<option value="display_name">{$lang('member', 'dp')}</option>
		<option value="post_count">{$lang('member', 'pc')}</option>
	</select>
	
	<select name="order">
		<option value="desc">{$lang('member', 'desc')}</option>
		<option value="asc">{$lang('member', 'asc')}</option>
	</select>
	
	<input name="mem_sea" type="submit" value="{$lang('all', 'sub')}" class="but-less"></input>
</form>