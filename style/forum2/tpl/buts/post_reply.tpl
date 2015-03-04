<if $display->vars['thread']['isLocked']>
	<if $display->vars['can_reply']>
		<a class="but red" href="{$link(cp::call('display')->vars['thread']['path'], 'post')}">{$lang('cat', 'lock_reply')}</a>
	<else>
		<span class="but redNL">{$lang('cat', 'locked')}</span>
	</if>
<else>
	<if $display->vars['can_reply']>
		<a class="but grey" href="{$link(cp::call('display')->vars['thread']['path'], 'post')}">{$lang('cat', 'reply')}</a>
	</if>
</if>