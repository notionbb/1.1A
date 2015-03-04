	<div class="gendiv">
		
		<p>
			<b>Title:</b><br />
			{$display->vars['tool']['name']}
		</p>
		
		<p>
			<b>Description:</b><br />
			{$display->vars['tool']['desc']}
		</p>
		
		<p>
			<b>Required Resources:</b><br />
			<if $display->vars['tool']['res'] == 1>
				<span style="color: #33CC33;">Medium</span></if>
			<if $display->vars['tool']['res'] == 2>
				<span style="color: #FF9900;">High</span></if>
			<if $display->vars['tool']['res'] == 3>
				<span style="color: #CC0000;">Severe</span></if>
		</p>
		
	</div>