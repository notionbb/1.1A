			<if $display->vars['type'] == 'thread'>
				{$display->vars['type_extra']}
			</if>
			<input id="file-ids" name="file-ids" type="text" value="" class="hide" />
			<div class="row-extra-padding">
				<span class="tits">{$lang('post','ent_post')}</span><br />
				<div class="div-editor-margin"><textarea name="editor" class="editor editor_load editor_new" id="editor_new"></textarea></div>
			</div>
			<div class="row-extra-padding center">
				<input class="but grey" type="submit" name="post" value="{$lang('post', 'sub_post')}">
			</div>