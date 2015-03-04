<div class="cat_row">
	<div class="left tits">
		{$display->vars['forum']['name']}</span>
	</div>
	<div class="right copt">
		{$display->vars['forum']['orderForm']}
	</div>
	<div class="right copt">
		{$display->vars['forum']['optHtml']}
	</div>
	<br class="clear" />
</div>
<if $display->vars['forum']['subHtml']>

<div class="info_row">
	<div class="left">
		{$lang('board', 'finfo')}
	</div>
	<div class="right copt">
		{$lang('board', 'order')}
	</div>
	<div class="right copt">
		{$lang('board', 'opts')}
	</div>
	<div class="right copt">
		{$lang('board', 'posts')}
	</div>
	<div class="right copt">
		{$lang('board', 'threads')}
	</div>
	<br class="clear" />
</div>

	{$display->vars['forum']['subHtml']}
</if>