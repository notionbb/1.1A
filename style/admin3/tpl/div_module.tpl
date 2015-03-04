	<div class="gendiv gendivsmall">
			
		<p>
			<b>Module:</b> {$display->vars['mod']['sexyname']}
		</p>
		
		<p>
			<b>Description:</b><br />
			{$display->vars['mod']['desc']}
		</p>
		
		<p>
			<b>Version:</b> {$display->vars['mod']['ver']}
		</p>
		
		<div class="submitDiv cent">
			<a href="?page=module&install={$display->vars['mod']['name']}&start=true" class="but grey">Install Now</a>
		</div>
		
	</div>