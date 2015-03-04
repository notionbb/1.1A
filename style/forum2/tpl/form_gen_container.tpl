	<div class="register">
		<table>
			<if $display->vars['error']>
				<div class="error">{$display->vars['error']}</div>
			</if>
			{$display->vars['rows']}
		</table>
	</div>
