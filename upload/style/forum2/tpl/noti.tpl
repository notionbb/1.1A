<div class="noti-tits">{$lang('all', 'notis')}</div>
<if $display->vars['noti_rows']>
{$display->vars['noti_rows']}
<else>
<div class="noti-no">
	{$lang('all', 'no_notis')}
</div></if>