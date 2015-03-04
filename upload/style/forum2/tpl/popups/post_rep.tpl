<div class="tits">
	{$lang('post', 'rep')}
</div>
<br class="clear" />
<div class="row-nobot">
	{$lang('post', 'rep_con')}
</div>
<div class="row-nobot">
	{$lang('post', 'ent_rea_rep')}<br />
	<input type="text" name="del_reason" class="input-text-norm" id="rep_reason_{$display->vars['post_id']}"></input>
</div>
<br class="clear" />
<div class="opts">
	<span class="but red ajax_gen" cmd="post,ajax_rep_conf,{$display->vars['post_id']}" moredata="rep_reason_{$display->vars['post_id']}">{$lang('cat', 'rep')}</span><span id="no" class="but grey">{$lang('all', 'canc')}</span>
</div>