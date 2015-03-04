<if $display->vars['thread']['isPinned']>
	<img alt="{$lang('cat','thread_pin')}" src="{$display->vars['lprefix']}style/{$display->vars['styleFolder']}/images/arrow_up.png" /></if>
<if $display->vars['thread']['isLocked']>
	<img alt="{$lang('cat','thread_lock')}" src="{$display->vars['lprefix']}style/{$display->vars['styleFolder']}/images/locked.png" /></if>				