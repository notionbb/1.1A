<div class="tits">
	{$lang('cat', 'ren_thre')}
</div>
<br class="clear" />
<div class="row-nobot">
	{$lang('cat', 'thre_cur')} {$display->vars['thread']['title']}
</div>
<div class="row-nobot">
	<input type="text" name="new_title" class="input-text-norm" id="new_title_{$display->vars['thread']['id']}" value="{$display->vars['thread']['title']}"></input>
</div>
<br class="clear" />
<div class="opts">
	<span class="but red ajax_gen" cmd="thread,ajax_rename_conf,{$display->vars['thread']['id']}" moredata="new_title_{$display->vars['thread']['id']}">{$lang('cat', 'ren')}</span><span id="no" class="but grey">{$lang('all', 'canc')}</span>
</div>