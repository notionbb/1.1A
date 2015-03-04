<if $display->vars['upload']>
	<div class="rightbox post_right">
		<div class="post_right_title">Upload</div>
		<div class="rightbox_content">
			<div id="upload_holder">
				!IGNORE: The element below gets cloned for each file uploaded
				<div id="load_box" class="file-row file-row-process hide">
					<div id="load_box_file_name">File Name</div>
					<div id="load_box_bar"><img src="{$display->vars['lprefix']}images/ajax-upload.gif" /></div>
				</div>
				<div class="but-hold">
					<input id="file-upload-but" class="hide" type="file" name="" value=""/>
					<span id="file-upload-alt" class="but-medium">Select file</span>
				</div>
			</div>
		</div>
	</div>
</if>

<div class="rightbox post_right">
		<div class="post_right_title">Subscribe</div>
		<div class="rightbox_content">
			<input name="subscribe_new_post" type="checkbox" class="input-check-margin">Would you like to receive notifications of new content
		</div>
	</div>