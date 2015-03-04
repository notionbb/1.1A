	<div class="row about">
		<div class="normcoltop auto">
			<table>
				<tr>
					<td colspan="2" class="post-top member-content-tits">{$lang('member', 'bi')}</td>
				</tr>
				<tr>
					<td height="6px" colspan="2"></td>
				</tr>
				<tr>
					<td class="member-left-td">{$lang('member', 'group')}</td>
					<td class="member-right-td">{$display->vars['member']['htmlGroup']}</td>
				</tr>
				<tr>
					<td class="member-left-td">{$lang('member', 'prof_views')}</td>
					<td class="member-right-td">{$display->vars['member']['profileViews']}</td>
				</tr>
				<if $display->vars['member']['location']>
				<tr>
					<td class="member-left-td">{$lang('member', 'locale')}</td>
					<td class="member-right-td">
						{$display->vars['member']['location']}
					</td>
				</tr>
				</if>
				<tr>
					<td class="member-left-td">{$lang('member', 'email')}</td>
					<td class="member-right-td">
						<if $display->vars['member']['email_show']>
							{$display->vars['member']['email']}
						<else>
							<span class="hidden_val">
								<if $logged->cur['acp']>
									{$display->vars['member']['email']}
								<else>
									{$lang('member', 'hid')}
								</if>
							</span>
						</if>
					</td>
				</tr>
				<if $display->vars['member']['djName']>
					<tr>
						<td class="member-left-td">{$lang('member', 'dj_prof')}</td>
						<td class="member-right-td">{$display->vars['member']['djName']}</td>
					</tr>
				</if>
				<if $display->vars['member']['occupation']>
					<tr>
						<td class="member-left-td">{$lang('member', 'occ')}</td>
						<td class="member-right-td">{$display->vars['member']['occupation']}</td>
					</tr>
				</if>
				<tr>
					<td height="6px" colspan="2"></td>
				</tr>
				<tr>
					<td colspan="2" class="post-top member-content-tits">{$lang('member', 'soc')}</td>
				</tr>
				<tr>
					<td height="6px" colspan="2"></td>
				</tr>
				<tr>
					<td class="member-left-td">{$lang('member', 'prof')}</td>
					<td class="member-right-td"><a class="cp" href="{$display->vars['member']['path_str']}">{$display->vars['member']['displayName']}</a></td>
				</tr>
				<if $display->vars['member']['youtube']>
					<tr>
						<td class="member-left-td">{$lang('member', 'yt')}</td>
						<td class="member-right-td"><a class="youtube" href="https://www.youtube.com/user/{$display->vars['member']['youtube']}">{$display->vars['member']['youtube']}</a></td>
					</tr>
				</if>
				<if $display->vars['member']['twitter']>
					<tr>
						<td class="member-left-td">{$lang('member', 'tw')}</td>
						<td class="member-right-td"><span class="twitter">@<a href="http://twitter.com/{$display->vars['member']['twitter']}">{$display->vars['member']['twitter']}</a></span></td>
					</tr>
				</if>
				<if $display->vars['member']['website']>
					<tr>
						<td class="member-left-td">{$lang('member', 'si')}</td>
						<td class="member-right-td"><a href="http://{$display->vars['member']['website']}">{$display->vars['member']['website']}</a></td>
					</tr>
				</if>	
			</table>
		</div>
		<div class="colnov wide">
			<div class="post-top member-content-tits">{$lang('member', 'sig')}</div>
			<div class="member-content-sig">
				<if $display->vars['member']['signature']>
					{$display->vars['member']['htmlSig']}
				<else>
					<i>{$lang('member', 'no_sig')}</i>
				</if>
			</div>
		</div>
	</div>