<div class="tits">
	{$lang('cat', 'move_thread')}
</div>
<br class="clear" />
<div class="row-nobot">
	{$lang('cat', 'thread_mov_conf')}
</div>
<div class="row-nobot">
	<select name="moredata" id="move_new_forum">
		{$display->vars['options']}
	</select>
</div>
<br class="clear" />
<div class="opts">
	<span class="but red ajax_gen" cmd="thread,ajax_move_lots{$display->vars['cmd_mod']}" moredata="move_new_forum">{$lang('cat', 'move')}</span><span id="no" class="but grey">{$lang('all', 'canc')}</span>
</div>