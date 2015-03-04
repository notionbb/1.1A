<div class="feedrow">
	<div class="avatar">
		<img src="{$display->vars['starter']['avatar']}" />
	</div>
	<div class="text">
		<span class="tits"><a href="{$link(cp::call('display')->vars['thread']['path'])}">{$display->vars['thread']['title']}</a></span> <br />
		<span class="small">{$lang('all', 'posted_by')} <a href="{$display->vars['starter']['path_str']}">{$display->vars['starter']['displayName']}</a> ({$display->vars['thread']['postCount']} {$lang('all', 'replies')}) <br />
		{$display->vars['thread']['lastPostEng']} {$lang('all', 'in')} <a href="{$link(cp::call('display')->vars['forum']['path'])}">{$display->vars['forum']['name']}</a></a>
	</div>
</div>