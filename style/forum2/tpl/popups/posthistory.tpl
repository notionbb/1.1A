<div class="tits">
	{$lang('post', 'post_his')}
</div>
<br class="clear" />
	<if $display->vars['history_rows']>
	<div class="inline">{$display->vars['history_rows']}</div>
	<else>
	<div class="row-nobot">{$lang('post', 'post_his_no')}</div>
	</if>
<br class="clear" />
<div class="opts">
	<span id="no" class="but grey">{$lang('post', 'close')}</span>
</div>