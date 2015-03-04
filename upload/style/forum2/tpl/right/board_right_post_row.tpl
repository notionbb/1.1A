									<div class="feedrow">
										<div class="avatar">
											<img src="{$act->starter[avatar]}" />
										</div>
										<div class="text">
											{$act->post[content]}<a href="{$LINK(act=thread,cat=$act->cat[slug],forum=$act->forum[slug],thread=$act->thread[slug])}"></a> <br />
											<span class="small">Posted by <a href="{$LINK(act=members,n=$act->starter[displayName])}">{$act->starter[displayName]}</a><br />
											{$act->post[postTime]} in <a href="{$LINK(act=forum,cat=$act->cat[slug],forum=$act->forum[slug])}">{$act->forum[title]}</a></a>
										</div>
									</div>