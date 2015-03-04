	<div class="row">
		
		<div class="cell nowhite vm" style="width: 150px">
			<span class="set_tit">{$display->vars['row']['sexyname']}</span><br />
			<span class="set_desc">{$display->vars['row']['folder']}</span>
		</div>
		<div class="cell nowhite vm" style="width: 150px">
			<a href="{$display->vars['row']['devlink']}">{$display->vars['row']['dev']}</a>
		</div>
		
		<div class="cell vm">
			<a href="?page=skinman&install={$display->vars['row']['folder']}">Install Now</a>
		</div>
			
	</div>