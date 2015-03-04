<form 
	method="post"
	name="{$display->vars['form']['name']}"
	<if $display->vars['form']['action']>
	 action="{$display->vars['form']['action']}"
	</if>
	<if $display->vars['form']['enctype']>
	 enctype="multipart/form-data"
	</if>
	<if $display->vars['form']['autoComplete']>
	 autocomplete="off"
	</if>>
	
	<div class="gentable gensmall">
			
		<div class="hold">
			
			<div class="main">
				<if $display->vars['form']['title']>
				<div class="cell tits">
					{$display->vars['form']['title']}
				</div></if>
				{$display->vars['form']['fields']}
									
			</div>			
		</div>
		
		<div class="submitDiv {$display->vars['form']['submitDivC']}">
			<input class="submit but grey {$display->vars['form']['submitC']}" type="submit" name="{$display->vars['form']['name']}" value="{$display->vars['form']['submit']}">
		</div>
		
	</div>

</form>