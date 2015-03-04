<div class="noti-row <ifnot $display->vars['noti']['read']>noti-new
	</if>">
	<a href="{$link(cp::call('display')->vars['noti']['path'])}"></a>
	<div class="avatar">
		<img src="{$display->vars['noti']['from_avatar']}" />
	</div>
	<div class="text">
		<b>{$display->vars['noti']['from_displayName']}</b> {$lang('noti', 'in_thread')}</a> <b>{$display->vars['noti']['title']}</b><br />
		<span class="date">{$display->vars['noti']['engTime']}</span>
	</div>
</div>