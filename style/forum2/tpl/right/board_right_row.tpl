									<div class="feedrow">
										<div class="avatar">
											<img src="{$act->starter[avatar]}" />
										</div>
										<div class="text">
											<a href="{$LINK(act=thread,cat=$act->thread[cat_slug],forum=$act->thread[forum_slug],thread=$act->thread[slug])}">{$act->thread[title]}</a> <br />
											<span class="small">{$lang->load(all)->get(posted_by)} <a href="{$LINK(act=members,n=$act->starter[displayName])}">{$act->starter[displayName]}</a> ({$act->thread[postCount]} {$lang->load(all)->get(replies)}) <br />
											{$act->thread[lastPostTime]} {$lang->load(all)->get(in)} <a href="{$LINK(act=forum,cat=$act->thread[cat_slug],forum=$act->thread[forum_slug])}">{$act->thread[forum_title]}</a></a>
										</div>
									</div>