<div class="tits">
	{$lang('post', 'res_con')}
</div>
<br class="clear" />
<div class="row-nobot">
	{$lang('post', 'ent_rea_r')}<br />
	<input type="text" name="del_reason" class="input-text-min" id="res_reason_{$display->vars['post_id']}"></input>
</div>
<div class="row-nobot">
	{$lang('post', 'res_conf')}
</div>
<br class="clear" />
<div class="opts">
	<span class="but red ajax_gen" cmd="post,ajax_res_conf,{$display->vars['post_id']}" moredata="res_reason_{$display->vars['post_id']}">{$lang('cat', 'res')}</span><span id="no" class="but grey">{$lang('all', 'canc')}</span>
</div>