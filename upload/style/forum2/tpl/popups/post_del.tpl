<div class="tits">
	{$lang('post', 'del_con')}
</div>
<br class="clear" />
<div class="row-nobot">
	{$lang('post', 'ent_rea')}<br />
	<input type="text" name="del_reason" class="input-text-min" id="del_reason_{$display->vars['post_id']}"></input>
</div>
<div class="row-nobot">
	{$lang('post', 'del_conf')}
</div>
<br class="clear" />
<div class="opts">
	<span class="but red ajax_gen" cmd="post,ajax_del_conf,{$display->vars['post_id']}" moredata="del_reason_{$display->vars['post_id']}">{$lang('cat', 'del')}</span><span id="no" class="but grey">{$lang('all', 'canc')}</span>
</div>