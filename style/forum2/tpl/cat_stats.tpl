<div class="row">
		<span class="tits">{$lang('cat', 'onl')}</span><br />
		<span class="small"><if $display->vars['online_mem']>
			{$display->vars['online_mem']} {$lang('cat', 'user_in')}<else>{$lang('cat', 'user_no')}</if> and {$display->vars['online_gue']} {$lang('cat', 'guests')}</span>
		<div class="tight">{$display->vars['online']}</div>
</div>
<div class="row row-border-top">
	<span class="tits">{$lang('cat', 'b_stats')}</span><br />
	<div class="tight">{$display->vars['latest']['totalPosts']['value']} {$lang('cat', 'cposts')}, {$display->vars['latest']['totalThreads']['value']} {$lang('cat', 'cthreads')}, {$display->vars['latest']['totalUsers']['value']} {$lang('cat', 'cmembers')}</div>
	<div class="tight">{$lang('cat', 'wel_new')} <a href="{$display->vars['latestMember']['path_str']}">{$display->vars['latestMember']['htmlName']}</a></div>
</div>