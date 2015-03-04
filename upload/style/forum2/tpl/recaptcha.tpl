<div id="recaptcha_widget">
	
	<div class="recaptcha_only_if_image">Enter the words below:</div>
	<div id="recaptcha_image"></div>
	<div class="recaptcha_only_if_incorrect_sol" style="color:red">Incorrect please try again</div>
	
	<div class="recaptcha_only_if_audio">Enter the numbers you hear:</div>
	
	<input type="text" id="recaptcha_response_field" name="recaptcha_response_field" />
	
	<div class="recaptcha_links">
		<span><a href="javascript:Recaptcha.reload()">New Captcha</a></span>
		<span class="recaptcha_only_if_image"> | <a href="javascript:Recaptcha.switch_type('audio')">Switch to Audio</a></span>
		<span class="recaptcha_only_if_audio"> | <a href="javascript:Recaptcha.switch_type('image')">Switch to Image</a></span>
		<span> | <a href="javascript:Recaptcha.showhelp()">Help</a></span>
	</div>
	
</div>