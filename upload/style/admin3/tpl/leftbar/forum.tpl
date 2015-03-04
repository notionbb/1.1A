			<if $logged->cur['acp']>
				<div class="nav_item">
					<div menu="forums" class="nav_title toggleShow" show="left_forums_menu">
						<img src="{$display->vars['lprefix']}style/{$display->vars['styleFolder']}/images/forum.png" />
						Forums
					</div>
					<div id="left_forums_menu" class="box_hide">
						<a href="?f=forum&page=forums">Board Manager</a>
						<a href="?f=forum&page=forums&edit=newcat">New Category</a>
						<a href="?f=forum&page=forums&edit=new">New Forum</a>
						<!---<a href="?mod=forum&page=awards">Manage Awards</a>--->
					</div>
				</div>
			</if>