<textarea name="editor" class="editor editor_load editor_new" id="editor_{$display->vars['post_id']}"></textarea>
Edit Reason: 
<input type="text" name="edit_reason" class="input-text-norm" id="edit_reason_{$display->vars['post_id']}"></input>
<span class="but-less grey ajax_gen" cmd="post,ajax_edit_save,{$display->vars['post_id']}" sce="editor_{$display->vars['post_id']}" moredata="edit_reason_{$display->vars['post_id']}">{$lang('post', 'save_edit')}</span>