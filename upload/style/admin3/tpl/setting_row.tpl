
	<if $dtools->newcat>
		<div class="subcat"><span>{$dtools->newcat}</span><div class="back">&nbsp;</div></div>
	</if>
	
	<if $dtools->formRow['type'] == 'permtable'>
	
		<div class="perm_hold">			
			{$dtools->formRow['field']}			
		</div>
		
	<elseif $dtools->formRow['type'] == 'large_area' OR $dtools->formRow['type'] == 'editor'>
	
		<div class="row">
		
			<div class="cell left_cell vm">
				<span class="set_tit">{$dtools->formRow['title']}</span>
			</div>
			
			<div class="cell vm">
				<div class="set_desc_no_marg">{$dtools->formRow['desc']}</div>
			</div>
				
		</div>
		{$dtools->formRow['field']}
		
	<else>
	
		<div class="row">
		
			<div class="cell left_cell vm">
				<span class="set_tit">{$dtools->formRow['title']}</span><br /><br />
			</div>
			<div class="cell vm">
				{$dtools->formRow['field']}
				<div class="set_desc">{$dtools->formRow['desc']}</div>
			</div>
			<if $dtools->formRow['aK']>
			<div class="cell vm set_desc ajax_hoverShow" show="{$dtools->formRow['aK']}_div">
				<div id="{$dtools->formRow['aK']}_div" class="hide">{$dtools->formRow['aK']}</div>
			</div></if>
				
		</div>
	
	</if>