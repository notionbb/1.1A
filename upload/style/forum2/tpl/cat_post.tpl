<if $display->vars['post']['visible'] == 2>
	<div class="row post_delinfo_{$display->vars['post']['id']} trans">
		<div class="delinfo toggleShowSlow" show="post_all_{$display->vars['post']['id']}" showC="post_delinfo_{$display->vars['post']['id']}">
			Post by {$display->vars['poster']['displayName']} {$display->vars['post']['engTime']} deleted by {$display->vars['deleter']['htmlName']} (click to show)
		</div>
	</div>
	<div id="post_all_{$display->vars['post']['id']}" class="row hide trans ajax_transShow" show="opts_{$display->vars['post']['id']}">
<elseif $display->vars['post']['visible'] == 0>
	<div id="post_all_{$display->vars['post']['id']}" class="row row-unapp ajax_transShow" show="opts_{$display->vars['post']['id']}">
<else>
	<div id="post_all_{$display->vars['post']['id']}" class="row ajax_transShow" show="opts_{$display->vars['post']['id']}">
</if>
	
	<div class="post-left">
		<div class="post-top">
			<a href="{$display->vars['poster']['path_str']}">{$display->vars['poster']['displayName']}</a>
		</div>
		<div class="post-left-margin"><img class="avalarge" src="{$display->vars['poster']['avatar']}" /></div>
		<div class="post-left-margin post-left-text">
			<if $display->vars['poster']['displayRank']>
				{$display->vars['poster']['pip']['title']}
			<else>
				{$display->vars['poster']['htmlGroup']}
			</if>
		</div>
		<div class="">
			{$display->vars['poster']['pip']['img']}
		</div>
		<div class="really-small">
			{$display->vars['poster']['postCount']} {$lang('post', 'posts')}<br />
			{$display->vars['poster']['engSince']}
		</div>
	</div>
	<div class="post-right">
		<div class="post-top">
			<span class="">{$lang('post', 'posted')} {$display->vars['post']['engTime']}</span>
			<a href="{$link(cp::call('display')->vars['post']['path'])}"><img class="right" src="{$display->vars['lprefix']}style/{$display->vars['styleFolder']}/images/link.png" /></a>
		</div>
		<div id="post_content_{$display->vars['post']['id']}" class="post-content">{$display->vars['post']['postContent']}</div>
		<if $display->vars['post']['attach']>
			<div class="post-attach">
				Attachments:<if $display->vars['post']['attachHtml']>
				<ul>{$display->vars['post']['attachHtml']}</ul>
				<else><br /><span class="norm">{$lang('post', 'not_allow_attach')}</span>
				</if>
			</div>
		</if>
		<div class="post-content-sig">
			{$display->vars['poster']['htmlSig']}
		</div>
	</div>
	<div class="clear"></div>
	<div class="post-options">
		<div id="opts_{$display->vars['post']['id']}" class="trans">
			<if $display->vars['post']['can_edit']>
				<span class="but-small ajax_gen" cmd="thread,ajax_post_history,{$display->vars['post']['id']}">{$lang('post', 'post_his')}</span>
				<span class="but-small ajax_gen" cmd="post,ajax_edit,{$display->vars['post']['id']}">{$lang('cat', 'edit')}</span>
			</if>
			<if $display->vars['post']['can_del']>			
				<if $display->vars['post']['visible'] == 2>
					<span class="but-small ajax_gen" cmd="post,ajax_res,{$display->vars['post']['id']}">{$lang('cat', 'rest')}</span>
				<elseif $display->vars['post']['visible'] == 1>
					<span class="but-small ajax_gen" cmd="post,ajax_del,{$display->vars['post']['id']}">{$lang('cat', 'del')}</span>
				<elseif $display->vars['post']['visible'] == 0>
					<span class="but-small ajax_gen" cmd="post,ajax_res,{$display->vars['post']['id']}">{$lang('cat', 'app')}</span>
				</if>
			</if>
			<if $display->vars['post']['can_post']>
				<span class="but-small ajax_quote" postId="{$display->vars['post']['id']}" displayname="{$display->vars['poster']['displayName']}" time="{$display->vars['post']['postTime']}">{$lang('cat', 'quote')}</span>
			</if>
			<if $display->vars['post']['can_rep']>
				<span class="but-small ajax_gen" cmd="post,ajax_rep,{$display->vars['post']['id']}">{$lang('cat', 'rep')}</span>
			</if>
		</div>
	</div>
</div>