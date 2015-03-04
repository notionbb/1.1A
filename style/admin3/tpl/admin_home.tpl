<div class="gentable"><div class="hold"><div class="main"></div></div></div>

<div class="home space">

	<div class="left home-rightbar">
	
		<div class="left stats">
			<table>
				<tr>
					<td colspan="2"><b>Quick Stats:</b></td>
				</tr>
				<tr>
					<td>
						<span class="small">
						Members:<br />
						Users online:<br />						
						Threads:<br />
						Posts:<br />
						</span>
					</td>
					<td>
						<span class="small">
						{$display->vars['misc']['totalUsers']}<br />
						{$display->vars['misc']['online']}<br />
						{$display->vars['misc']['totalThreads']}<br />
						{$display->vars['misc']['totalPosts']}<br />
						</span>
					</td>					
				</tr>
			</table>			
		</div>
		
		<div class="left orange">
			<i>Coming Soon</i>			
		</div>
		
		<div class="left blue">
			<i>Coming Soon</i>			
		</div>
		
		<br class="clear" />
		
		<div class="noteholder">
			<textarea id="adminnote" cmd="home,ajax_notepad">{$display->vars['notepad']}</textarea>
			<div class="notestat">Ready</div>
		</div>
		
		<div class="new_users">5 Newest Members</div>
		{$display->vars['new_user_table']}
		
	</div>

	<div class="left home-leftbar">
	
		<div class="modules">
			<div class="mod-title">Installed Applications</div>
			{$display->vars['apps']}
		</div>
		
		<div class="modules">
			<div class="mod-title">CipherPixel News</div>
			{$display->vars['news']}
		</div>
		
		<div class="modules">
			<div class="mod-title">Admin Logs</div>
			
		</div>
		
	</div>

</div>