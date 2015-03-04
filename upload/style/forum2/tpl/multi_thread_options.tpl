{$lang('cat', 'mod_threads')}:&nbsp;
<select name="moredata" id="multi_type">
	<option value="">{$lang('cat', 'sel')}</option>
	<if $display->vars['mod']['del']>
		<option value="delete">{$lang('cat', 'del')}</option>
	</if>
	<if $display->vars['mod']['hide']>
		<option value="hide">{$lang('cat', 'hid')}</option>
		<option value="restore">{$lang('cat', 'res')}</option>
	</if>
	<if $display->vars['mod']['lock']>
		<option value="lock">{$lang('cat', 'lock')}</option>
		<option value="unlock">{$lang('cat', 'unlock')}</option>
	</if>
	<if $display->vars['mod']['move']>
		<option value="move">{$lang('cat', 'move')}</option>
	</if>
</select>
<span class="but-small but-mod ajax_gen" cmd="forum,ajax_multi_mod" moredata="multi_type">{$lang('all', 'sub')}</span>