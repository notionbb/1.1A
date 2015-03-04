<div class="forum_row padding">
	<div class="left">
		<span class="tits">{$display->vars['forum']['name']}</span>
		<if $display->vars['forum']['desc']>
		<br /><span class="desc">{$display->vars['forum']['desc']}</span></if>
	</div>
	<div class="right fopt">
		{$display->vars['forum']['orderForm']}
	</div>
	<div class="right fopt" id="{$display->vars['forum']['id']}_div">
		{$display->vars['forum']['optHtml']}
	</div>
	<div class="right fopt">
		{$display->vars['forum']['postCount']}
	</div>
	<div class="right fopt">
		{$display->vars['forum']['threadCount']}
	</div>
	<br class="clear" />	
</div>

<if $display->vars['forum']['subHtml']>
<div class="padding">
	{$display->vars['forum']['subHtml']}
</div>
</if>	