<div class="row row-border-top">
	<div class="col norightpad">
		<img src="{$display->vars['lprefix']}style/{$display->vars['styleFolder']}/images/thread_sticky.png" />
	</div>
	<div class="col wide">
		Occurance #{$display->vars['report']['id']} sent {$display->vars['report']['engTime']}<br />
		
		<div class="tits_report">
			Post (#{$display->vars['report']['post_id']}) reported by {$display->vars['sender']['htmlName']}.
			<if $display->vars['report']['msg']>
				<i>{$display->vars['report']['msg']}</i>
			<else>
				No reason given
			</if>
		</div>
		
		<a class="desc_a" href="{$link(cp::call('display')->vars['report']['path'])}">Context</a> 
		<!--{$display->vars['report']['navtree']}-->
	</div>
	<div class="col nopad ajax_multiclick" arrayId="{$display->vars['thread']['id']}" unclicked="flag_not_{$display->vars['thread']['id']}" clicked="flag_select_{$display->vars['thread']['id']}">
		<img class="clickable trans" src="{$display->vars['lprefix']}style/{$display->vars['styleFolder']}/images/flag_not.gif" id="flag_not_{$display->vars['thread']['id']}"/>
		<img class="clickable hide" src="{$display->vars['lprefix']}style/{$display->vars['styleFolder']}/images/flag_select.gif" id="flag_select_{$display->vars['thread']['id']}"/>
	</div>
</div>