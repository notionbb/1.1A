					<div class="feed">
						<if $globals[newsForum] >
						<div class="feedholder">
							<div class="feedtitle">{$lang->load(all)->get(l_news)}</div>
							<div class="feedwhite">
								<div class="table">
									{$ac_forumdisplaytools->getRightNews()}				
								</div>
							</div>
						</div>
						</if>
						<if $globals[enableBlogs] >
						{$ac_forumdisplaytools->getRightBlogs()}
						<div class="feedholder">
							<div class="feedtitle">{$ac_forumdisplaytools->blogRightTitle} {$lang->load(all)->get(posts)}</div>
							<div class="feedwhite">
								<div class="table">
									{$ac_forumdisplaytools->blogRightRows}				
								</div>
							</div>
						</div>
						</if>
						<div class="feedholder">
							<div class="feedtitle">{$lang->load(all)->get(l_threads)}</div>
							<div class="feedwhite">
								<div class="table">
									{$ac_forumdisplaytools->getRightThreads()}			
								</div>
							</div>
						</div>
						<if $act->prows >
						<div class="feedholder">
							<div class="feedtitle">Latest Posts</div>
							<div class="feedwhite">
								<div class="table">
									{$act->prows}				
								</div>
							</div>
						</div>
						</if>
					</div>