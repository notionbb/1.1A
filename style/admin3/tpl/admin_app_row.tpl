<span>
	{$display->vars['sexyname']}
	<if $display->vars['update']['update']>
		<img align="right" src="{$display->vars['lprefix']}style/{$display->vars['styleFolder']}/images/cross.png" />
		<div class="up-box">
			<b>Notice!</b> {$display->vars['update']['up_type']}<br />
			({$display->vars['update']['latest']}) {$display->vars['update']['up_desc']}<br />
			<center><a href="{$display->vars['update']['up_link']}">Update Now</a></center>
		</div>
	<else>
		<img align="right" src="{$display->vars['lprefix']}style/{$display->vars['styleFolder']}/images/tick.png" />
	</if>
</span>