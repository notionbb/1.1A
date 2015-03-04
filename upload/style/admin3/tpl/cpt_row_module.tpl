	<div class="row">
	
		<div class="cell vm">
			{$display->vars['row']['ver']}
		</div>
		<div class="cell nowhite vm">
			<span class="set_tit">{$display->vars['row']['sexyname']}</span><br />
			<span class="set_desc">{$display->vars['row']['desc']}</span>
		</div>
		<div class="cell nowhite vm">
			<a target="_blank" href="{$display->vars['row']['url']}">{$display->vars['row']['who']}</a>
		</div>
		<div class="cell expand vm">
			<a href="?page=module&install={$display->vars['row']['name']}">Install Now</a>
		</div>
			
	</div>