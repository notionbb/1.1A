					<if $display->vars['new_row']>
						<div class="row">
					</if>
							<div class="normcol quarter left">
								<div class="box center member-div-but">
									<div class="post-top">
										<a href="{$display->vars['member']['path_str']}">{$display->vars['member']['displayName']}</a>
									</div>
									<div class="post-left-margin"><img class="avalarge" src="{$display->vars['member']['avatar']}" /></div>
									<div class="post-left-margin post-left-text">
										<if $display->vars['member']['displayRank']>
											{$display->vars['member']['pip']['title']}
										<else>
											{$display->vars['member']['htmlGroup']}
										</if>
									</div>
									<div class="">
										{$display->vars['member']['pip']['img']}
									</div>
									<div class="really-small">
										<if $display->vars['member']['postCount']>
											{$display->vars['member']['postCount']} {$lang('post', 'posts')}<br /></if>
										{$display->vars['member']['engSince']}
									</div>
								</div>
							</div>
							
					<if $display->vars['end_row'] OR $display->vars['last_mem']>
						</div><br class="clear" />						
					</if>
						