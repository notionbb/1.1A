					<div class="rows-border space">
						<div class="row-extra-padding editor-hold">
							<img class="left" src="{$display->vars['lprefix']}style/{$display->vars['styleFolder']}/images/cog.png">
							<div class="left modopt">
								{$lang('cat', 'mod_opts')}:
								<if $display->vars['mod']['lock']>
									<if $display->vars['thread']['isLocked']>
										<span class="opt ajax_gen" cmd="thread,ajax_unlock,{$display->vars['thread']['id']}">{$lang('cat', 'locked')}</span>
									<else>
										<span class="opt ajax_gen" cmd="thread,ajax_lock,{$display->vars['thread']['id']}">{$lang('cat', 'lock')}</span>
									</if>
								</if>
								<if $display->vars['mod']['hide']>
									<if $display->vars['thread']['visible'] == '0'>
										<span class="opt ajax_gen" cmd="thread,ajax_restore,{$display->vars['thread']['id']}">{$lang('cat', 'app')}</span>
									<elseif $display->vars['thread']['visible'] == '1'>
										<span class="opt ajax_gen" cmd="thread,ajax_hide,{$display->vars['thread']['id']}">{$lang('cat', 'hid')}</span>
									</if>
								</if>
								<if $display->vars['mod']['del']>
									<if $display->vars['thread']['visible'] == '2'>
										<span class="opt ajax_gen" cmd="thread,ajax_restore,{$display->vars['thread']['id']}">{$lang('cat', 'res')}</span>
									<else>
										<span class="opt ajax_gen" cmd="thread,ajax_delete,{$display->vars['thread']['id']}">{$lang('cat', 'del')}</span>
									</if>
								</if>
								<if $display->vars['mod']['move']>
									<span class="opt ajax_gen" cmd="thread,ajax_move,{$display->vars['thread']['id']}">{$lang('cat', 'move')}</span>
								</if>
								<if $display->vars['mod']['ren']>
									<span class="opt ajax_gen" cmd="thread,ajax_rename,{$display->vars['thread']['id']}">{$lang('cat', 'ren')}</span>
								</if>
							</div>
							<br class="clear" />
						</div>
					</div>