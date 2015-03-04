							<div class="row<ifnot $display->vars['thread']['first']>
							 row-border-top</if><ifnot $display->vars['thread']['visible']>
							  row-unapp</if>">
								<div class="col norightpad">
									<if $display->vars['thread']['visible'] == 0>
										<img src="{$display->vars['lprefix']}style/{$display->vars['styleFolder']}/images/thread_unapp.png" />
									<elseif $display->vars['thread']['isPinned']>
										<img src="{$display->vars['lprefix']}style/{$display->vars['styleFolder']}/images/thread_sticky.png" />
									<elseif $display->vars['thread']['read']>
										<img src="{$display->vars['lprefix']}style/{$display->vars['styleFolder']}/images/thread_nothing.png" />
									<else>
										<img src="{$display->vars['lprefix']}style/{$display->vars['styleFolder']}/images/thread_new.png" />
									</if>
								</div>
								<div class="col wide">
									<span class="tits">										
										<a href="{$link(cp::call('display')->vars['thread']['path'])}">{$display->vars['thread']['title']}</a>
										<if $display->vars['thread']['isLocked']>
											<img src="{$display->vars['lprefix']}style/{$display->vars['styleFolder']}/images/lock.png" /></if>
									</span> <br />
									<span class="desc">{$lang('cat', 'started')} {$display->vars['thread']['startT']} {$lang('cat', 'by')} <a href="{$display->vars['starter']['path_str']}">{$display->vars['starter']['displayName']}</a></span>
									 <if $display->vars['thread']['isLocked'] AND $display->vars['thread']['isPinned']>
									 	<span class="desc">({$set('lockedText')} and {$set('pinnedText')})</span>
									 <else>
										 <if $display->vars['thread']['isLocked']>
										 	<span class="desc">({$set('lockedText')})</span></if>
										 <if $display->vars['thread']['isPinned']>
										 	<span class="desc">({$set('pinnedText')})</span></if>
									 </if>
								</div>
								<div class="col auto">
									{$lang('cat', 'cposts')}: {$display->vars['thread']['postCount']} <br />
									{$lang('cat', 'cviews')}: {$display->vars['thread']['views']}
								</div>
								<div class="col auto">
									<img class="avatar avasmall" src="{$display->vars['last']['avatar']}" />
								</div>
								<div class="col auto">
									<div class="lpwide">
										<if $display->vars['thread']['lastT']>
										{$lang('cat', 'lp')} {$display->vars['thread']['lastT']} <br />
										{$lang('cat', 'cby')} <a href="{$display->vars['last']['path_str']}" class="memlink">{$display->vars['last']['displayName']}</a>
										</if>
									</div>
								</div>
								<div class="col nopad ajax_multiclick" arrayId="{$display->vars['thread']['id']}" unclicked="flag_not_{$display->vars['thread']['id']}" clicked="flag_select_{$display->vars['thread']['id']}">
									<img class="clickable trans" src="{$display->vars['lprefix']}style/{$display->vars['styleFolder']}/images/flag_not.gif" id="flag_not_{$display->vars['thread']['id']}"/>
									<img class="clickable hide" src="{$display->vars['lprefix']}style/{$display->vars['styleFolder']}/images/flag_select.gif" id="flag_select_{$display->vars['thread']['id']}"/>
								</div>
							</div>