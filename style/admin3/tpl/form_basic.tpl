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
{$display->vars['form']['fields']}
<if $display->vars['form']['submit']>
<div class="submitDiv {$display->vars['form']['submitDivC']}">
	<input class="submit {$display->vars['form']['submitC']}" type="submit" name="{$display->vars['form']['name']}" value="{$display->vars['form']['submit']}">
</div></if>
</form>