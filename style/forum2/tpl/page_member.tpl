							<div class="row">
								<div class="left member-avatar">
									<img class="avahuge" src="{$display->vars['member']['avatar']}">
								</div>
								<div class="left member-head">
									<div class="member-tits">
										{$display->vars['member']['displayName']}
										<if $display->vars['member']['online']>
										<div class="member-online">Online</div>
										</if>
									</div>
									<div class="member-dets">Member since {$display->vars['member']['engSince']} - Last click {$display->vars['member']['engLastClick']}</div>
									<div class="member-dets member-quote">
										<if $display->vars['member']['quote']>
											"{$display->vars['member']['quote']}"
										</if>
									</div>
								</div>
								<div class="right member-buts">
									<if $logged->cur['id'] == $display->vars['member']['id']>
										<a class="but grey" href="{$link(array('members',cp::call('display')->vars['member']['displayName'],'edit'))}">Edit Your Profile</a>
									<else>
										<span id="sub_but" class="but grey ajax_gen" cmd="members,ajax_sub,{$display->vars['member']['id']}"><if $display->vars['member']['subbed']>
										{$lang('member', 'unsub')}<else>{$lang('member', 'sub')}</if>
										</span>
										<!--<a class="but grey" href="#">Send PM</a>-->
									</if>
								</div>
								<div class="menu-grey">
									<ul>
										<li class="m-left"><span class="men ajax_gen" cmd="members,ajax_about,{$display->vars['member']['id']}">About</span></li>
										<li><span class="men ajax_gen" cmd="members,ajax_threads,{$display->vars['member']['id']}">Threads</span></li>
										<li class="b-right"><span class="men">Posts</span></li>
									</ul>
								</div>
								<div class="clear"></div>
							</div>
							<div class="row">					
								<div class="left member-left">
									<div class="member-margin-four">
										<div class="member-margin-two">{$display->vars['member']['htmlGroup']}</div>
										<div class="member-margin-two">{$display->vars['member']['pip']['img']}</div>
										<div class="tits">{$display->vars['member']['pip']['title']}</div>
									</div>
									<div class="small">
										{$display->vars['member']['threadCount']} threads<br />
										{$display->vars['member']['postCount']} posts
									</div>
								</div>
								<div class="left member-content" id="ajax_member_div">
									{$display->vars['about']}
								</div>
								<div class="clear"></div>
								
							</div>