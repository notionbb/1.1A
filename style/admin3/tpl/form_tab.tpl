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
	
	<div class="tabs">
		{$display->vars['form']['tabs']}	
		<br class="clear" />				
	</div>
	
	<div class="gentable<if $display->vars['form']['tabs']>
	 notopb</if>">
			
		<div class="hold">
			<div class="main">
			
				{$display->vars['form']['fields']}
									
			</div>			
		</div>
		
		<div class="submitDiv">
		<if $display->vars['form']['multipleTabs'] >
		<span class="but green marginright tabscroll" do="down"><< Back</span></if>
		<input class="submit but grey" type="submit" name="{$display->vars['form']['name']}" value="{$display->vars['form']['submit']}">
		<if $display->vars['form']['multipleTabs'] >
		<span class="but green marginleft tabscroll" do="up">Next >></span></if>
		</div>
		
	</div>

</form>