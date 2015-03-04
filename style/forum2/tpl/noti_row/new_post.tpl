<div class="noti-row <ifnot $display->vars['noti']['read']>noti-new
	</if>">
	<a href="{$link(cp::call('display')->vars['noti']['path'])}"></a>
	<div class="avatar">
		<img src="{$display->vars['noti']['from_avatar']}" />
	</div>
	<div class="text">
		<b>{$display->vars['noti']['from_displayName']}</b> 
		<if $display->vars['noti']['repetitive']>
			{$lang('noti', 'and')} {$display->vars['noti']['repetitive']} {$lang('noti', 'others')}
		</if>
			{$lang('noti', 'new_post')}</a> <b>{$display->vars['noti']['title']}</b><br />
		<span class="date">{$display->vars['noti']['engTime']}</span>
	</div>
</div>