<div class="row <if $display->vars['history_rows']>top-border
	</if>">
	<div class="left row_pad">
		<img class="avasmall" src="{$display->vars['by']['avatar']}" />
	</div>
	<div class="left row_pad posthistory_opts">
		<span class="small">{$display->vars['by']['htmlName']} {$display->vars['type']} {$display->vars['engOn']}</span><br />
		<if $display->vars['reason']>
			{$display->vars['reason']}
		<else>
			<i>{$lang('post', 'no_reas')}</i>
		</if>
	</div>
	<br class="clear" />
</div>