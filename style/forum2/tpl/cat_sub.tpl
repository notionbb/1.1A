<if cp::cont()->build_html[ $display->vars['sub']['parent'] ]>,
</if>
							<ifnot $display->vars['sub']['isLink']>
								<a href="{$link(cp::call('display')->vars['sub']['path'])}">{$display->vars['sub']['name']}</a><else>
								<a href="{$display->vars['sub']['linkUrl']}" <if $display->vars['sub']['newTab']> target="_blank"
										</if>>{$display->vars['sub']['name']}<img src="{$display->vars['lprefix']}style/{$display->vars['styleFolder']}/images/link.png" /></a></if>