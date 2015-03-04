							<div class="row <if cp::cont()->build_html[ $display->vars['forum']['parent'] ]>
							 row-border-top</if>
							 ">
							 <ifnot $display->vars['forum']['isLink']>
							 	<div class="col norightpad">			
									<img class="clickable ajax_gen status_{$display->vars['forum']['id']} <if $display->vars['forum']['read']>
									hide</if>" cmd="board,ajax_setread,{$display->vars['forum']['id']}" src="{$display->vars['lprefix']}style/{$display->vars['styleFolder']}/images/thread_new.png" />
									<img class="clickable ajax_gen status_{$display->vars['forum']['id']} <ifnot $display->vars['forum']['read']>
									hide</if>" cmd="board,ajax_unread,{$display->vars['forum']['id']}" src="{$display->vars['lprefix']}style/{$display->vars['styleFolder']}/images/thread_nothing.png" />
								</div>
								<div class="col wide">
									<span class="tits"><a href="{$link(cp::call('display')->vars['forum']['path'])}">{$display->vars['forum']['name']}</a></span>
									<if $display->vars['forum']['desc']>
										<br /><span class="desc">{$display->vars['forum'][desc]}</span>
									</if>
									<if $display->vars['forum']['subHtml']>
										<br /><span class="subs"><img src="{$display->vars['lprefix']}style/{$display->vars['styleFolder']}/images/subforums.png" /> {$display->vars['forum']['subHtml']}</span>
									</if>
								</div>							
								<div class="col auto">
									<if $display->vars['forum']['threadCount']>
										{$display->vars['forum']['threadCount']} {$lang('cat', 'threads')}
									<else>
										{$lang('cat', 'no_t')}
									</if> <br />
									<if $display->vars['forum']['postCount']>
										{$display->vars['forum']['postCount']} {$lang('cat', 'posts')}
									<else>
										{$lang('cat', 'no_p')}
									</if>
								</div>
								<div class="col auto">
									<div class="lp">
									<if $display->vars['forum']['lastT']>
										<a href="{$link(cp::call('display')->vars['lastT']['path'])}">{$display->vars['lastT']['title']}</a><br />
										{$lang('cat', 'posted')} {$display->vars['forum']['lastT']} {$lang('cat', 'by')} <a href="{$display->vars['last']['path_str']}" class="memlink">{$display->vars['last']['displayName']}</a>
										<else>
										<i>No posts yet</i>
									</if>
									</div>
								</div>
							<else>
								<div class="col norightpad">			
									<img class="" src="{$display->vars['lprefix']}style/{$display->vars['styleFolder']}/images/thread_link.png" />
								</div>
								<div class="col wide">
									<span class="tits"><a href="{$display->vars['forum']['linkUrl']}" <if $display->vars['forum']['newTab']> target="_blank"
										</if>>
									{$display->vars['forum']['name']}</a> <img src="{$display->vars['lprefix']}style/{$display->vars['styleFolder']}/images/link.png" /></span>
									<if $display->vars['forum']['desc']>
										<br /><span class="desc">{$display->vars['forum'][desc]}</span>
									</if>
								</div>
							</if>
							</div>