					<div class="cat">
					<if $display->vars['cat']['title']>
						<div class="title big">
								{$display->vars['cat']['title']}
						</div>
					</if>
					<if $display->vars['cat']['small']>
						<div class="title title-small">
							{$display->vars['cat']['small']}
						</div>
					</if>
					<ifnot $display->vars['cat']['noHold']>
						<div class="<if $display->vars['cat']['hidden']>
							hide 
							</if>hold"
								<if $display->vars['cat']['expand']>
								 id="{$display->vars['cat']['id']}_exp_hold"
								 </if>
								 >
							<div class="rows-border">
					</if>
							<if $display->vars['cat']['right']>
									<div class="left">
										{$display->vars['cat']['subHtml']}
									</div>
									<div class="right">
										{$display->vars['cat']['right']}
									</div>
									<div class="clear"></div>
							<else>
								{$display->vars['cat']['subHtml']}
								<div id="ajax_attack_extra_subHtml"></div>
							</if>
							<ifnot $display->vars['cat']['noHold']>
								</div>
							</if>
							<if $display->vars['cat']['extra'] >
								{$display->vars['cat']['extra']}
							</if>
						<ifnot $display->vars['cat']['noHold']>
							</div>
						</if>
					</div>