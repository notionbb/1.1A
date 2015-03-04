					<div class="page-top">
					<if cp::cont()->page['title']>
						<div class="page-title left">{$cont()->page['title']}</div>						
						<div class="page-options right">
							<div class="page-img">
								{$cont()->page['imgs']}
							</div>
							<div class="page-tree">
								{$cont()->page['tree']}
							</div>
							<div class="page-buts">
								{$cont()->page['buts']}
							</div>
						</div>
						<div class="clear"></div>
					</if>
					</div>
					<if cp::cont()->page['top-div']>
					<div class="page-div">
						{$cont()->page['top-div']}
					</div>
					</if>
					<if cp::cont()->page['right']>
					<div class="left cats">
						{$cont()->page['table']}
					</div>
					<div class="right rightbar">
						{$cont()->page['right']}
					</div>
					<br class="clear" />
					<else>
					{$cont()->page['table']}
					</if>
					<if cp::cont()->page['title']>
					<div class="page-top-norm">
						<div class="page-title-small left">
							<span class="page-tits">{$cont()->page['title']}</span><if cp::$act == 'thread' AND $display->vars['starter']>
							 {$lang('all', 'by')} <a href="{$link('members,'.cp::call('display')->vars['starter']['displayName'])}">{$display->vars['starter']['displayName']}</a> {$display->vars['engStart']}</if>
						</div>
						<div class="page-options right">
							<div class="page-mod-buts">
								{$cont()->page['mod_buts']}
							</div>
							<div class="page-tree">
								{$cont()->page['tree']}
							</div>
							<div class="page-buts">
								{$cont()->page['buts']}
							</div>
						</div>
						<div class="clear"></div>	
					</div>
					</if>