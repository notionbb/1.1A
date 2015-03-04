			<if $logged->cur['acp']>
				<div class="nav_item">	
					<div menu="admin" class="nav_title toggleShow" show="left_admin_menu">
						<img src="{$display->vars['lprefix']}style/{$display->vars['styleFolder']}/images/admin.png" />
						Admin
					</div>
					<div id="left_admin_menu" class="box_hide">
						<a href="?page=home">Home</a>
						<a href="?page=global">Global Settings</a>
						<a href="?page=module">Applications</a>
						<a href="?page=tools">Tools</a>
						<a href="#">Logout</a>
					</div>
				</div>
				
				<div class="nav_item">
					<div menu="users" class="nav_title toggleShow" show="left_users_menu">
						<img src="{$display->vars['lprefix']}style/{$display->vars['styleFolder']}/images/group.png" />
						Users and Groups
					</div>
					<div id="left_users_menu" class="box_hide">
						<a href="?page=group&edit=new">New Group</a>
						<a href="?page=group">Manage Groups</a>
						<a href="?page=user&edit=new">New Member</a>
						<a href="?page=search">Search Members</a>
						<a href="?page=perm">Permission Masks</a>
						<a href="?page=ranks">Manage Ranks</a>
					</div>
				</div>
			
				<div class="nav_item">
					<div menu="look" class="nav_title toggleShow" show="left_look_menu">
						<img src="{$display->vars['lprefix']}style/{$display->vars['styleFolder']}/images/layout.png" />
						Look and Feel
					</div>
					<div id="left_look_menu" class="box_hide">					
						<a href="?page=skinperm">Skin Permissions</a>
						<a href="?page=skinman">Skin Manager</a>
					</div>
				</div>

			</if>