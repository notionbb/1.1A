					<div class="rows-border space">
						<div class="row-extra-padding editor-hold">
							<div class="tits">{$lang('post', 'q_rep')}</div>
							<div class="div-editor-margin"><textarea name="editor" class="editor_wait" id="editor_new"></textarea></div>
							<div class="center"><span class="but grey submit ajax_gen" cmd="post,ajax_quickreply,{$display->vars['thread']['id']}" extra="{$display->vars['page']}" sce="editor_new">{$lang('post', 'sub_post')}</span></div>
						</div>
					</div>