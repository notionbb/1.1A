<if $display->vars['subcat']>
	<tr> <td colspan="2"> <div class="post-top member-content-tits space2">{$display->vars['subcat_title']}</div> </td> </tr>
</if>
		
	<tr>
		<td class="member-edit-padding center">
			<div class="tits">
				{$display->vars['row']['title']}
			</div>
			<div class="desc">
				{$display->vars['row']['desc']}
			</div>
		</td>
		<if $display->vars['row']['type'] == 'wide'>
			</tr>
			<tr>
				<td colspan="2">
					{$display->vars['row']['html']}
				</td>
		<else>
			<td class="member-right-td">
				<if $display->vars['row']['html']>
					{$display->vars['row']['html']}
				<else>
					<input class="input-text-norm<if $display->vars['form_error'][ $display->vars['row']['name'] ]>
					 red-border</if>" name="{$display->vars['row']['name']}" value="{$display->vars['row']['def']}" type="{$display->vars['row']['type']}"></input>
					<if $display->vars['form_error'][ $display->vars['row']['name'] ]>
						<div class="red-border-desc">{$display->vars['form_error'][ cp::call('display')->vars['row']['name'] ]}</div>
					</if>
				</if>
			</td>
		</if>
	</tr>
