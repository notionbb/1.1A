			<if $logged->cur['ccp']>
				<div class="nav_item">
					<div menu="content" class="nav_title toggleShow" show="left_content_menu">
						<img src="{$display->vars['lprefix']}style/{$display->vars['styleFolder']}/images/content.png" />
						Content
					</div>
					<div id="left_content_menu" class="box_hide">
						<a href="?f=c&page=pages">Page Manager</a>
					</div>
				</div>
			</if>