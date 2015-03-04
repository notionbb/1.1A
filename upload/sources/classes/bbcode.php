<?php

	//===================================================
	//	CP-Core © All Rights Reserved
	//---------------------------------------------------
	//	CP-Core
	//		by michaelbenner.net
	//---------------------------------------------------
	//	File created: April 04, 2013 
	//===================================================
	//	File Upload Class, checks files are safe
	//===================================================

class bbcode {
	
	/**
	** Converts BBCode to Html
	** 
	** @str	= string with bbcode!
	*/
	public function bb2html( $str )
	{
		
		//$str = nl2br( $str );
		
		//-----------------------------------
		// Replace what with what
		//
		$this->smile = cp::display()->vars['lprefix'] . 'images/emoticons/';
		
		#$this->contentURL = ( $this->cp->config['complexPages'] ) ? '/c/': '?app=c&title=';
		
		#$this->set();
		
		/**
		** BBCode Complete
		*/
		
		/**
		** New Lines
		*/
		
		$str = preg_replace( '/\\r\\n/', '<br />', $str );
		$str = preg_replace( '/\\r/', '<br />', $str );
		$str = preg_replace( '/\\n/', '<br />', $str );
		
		/**
		** Simple HTML
		*/
		
		$str = preg_replace( '/\[b\]((?s).*?)\[\/b\]/', '<b>$1</b>', $str );
		$str = preg_replace( '/\[i\]((?s).*?)\[\/i\]/', '<i>$1</i>', $str );
		$str = preg_replace( '/\[u\]((?s).*?)\[\/u\]/', '<u>$1</u>', $str );
		$str = preg_replace( '/\[s\]((?s).*?)\[\/s\]/', '<s>$1</s>', $str );
		$str = preg_replace( '/\[sub\]((?s).*?)\[\/sub\]/', '<sub>$1</sub>', $str );
		$str = preg_replace( '/\[sup\]((?s).*?)\[\/sup\]/', '<sup>$1</sup>', $str );
		$str = preg_replace( '/\[size=((?s).*?)\]((?s).*?)\[\/size\]/', '<font size="$1">$2</font>', $str );
		$str = preg_replace( '/\[font=((?s).*?)\]((?s).*?)\[\/font\]/', '<span style="font: $1;">$2</span>', $str );
		$str = preg_replace( '/\[color=((?s).*?)\]((?s).*?)\[\/color\]/', '<span style="color: $1;">$2</span>', $str );
		
		/**
		** Little extra HTML
		*/
		
		$str = preg_replace( '/\[mod\]((?s).*?)\[\/mod\]/', '<span class="post_mod">[Mod: $1]</mod>', $str );
		$str = preg_replace( '/\[hr\]/', '<hr>',  $str );
		$str = preg_replace( '/\[img]((?s).*?)\[\/img\]/', '<img src="$1" />', $str );
		$str = preg_replace( '/\[img=((?s).*?)x((?s).*?)\]((?s).*?)\[\/img\]/', '<img src="$3" width="$1" height="$2" />', $str );
		
		/**
		** Links
		*/
		
		$str = preg_replace( '/\[email=((?s).*?)\]((?s).*?)\[\/email\]/', '<a href="mailto:$1">$2</a>', $str );
		$str = preg_replace( '/\[url=((?s).*?)\]\[\/url\]/', '<a href="$1">$1</a>', $str );
		$str = preg_replace( '/\[url=((?s).*?)\]((?s).*?)\[\/url\]/', '<a href="$1">$2</a>', $str );
		$str = preg_replace( '/\[youtube]((?s).*?)\[\/youtube\]/', '<iframe id="ytplayer" class="post_youtube" type="text/html"  src="http://www.youtube.com/embed/$1"></iframe>', $str );
		
		/**
		** Tables and lists
		*/
		
		/*'/\[li\]((?s).*?)\[\/li\]/', '<li>$1</li>',
		'/\[ul\]/', '<ul>',
		'/\[\/ul\]/', '</ul>',
		'/\[ol\]/', '<ol>',
		'/\[\/ol\]/', '</ol>',
		'/\[table\]((?s).*?)\[\/table\]/', '<table>$1</table>',*/
		
		/**
		** Codes
		*/
		
		$str = preg_replace( '/\[code\]<br \/>((?s).*?)<br \/>\[\/code\]/', '<div class="post_code"><pre>$1</pre></div>', $str );
		$str = preg_replace( '/\[code\]<br \/>((?s).*?)\[\/code\]/', '<div class="post_code"><pre>$1</pre></div>', $str );
		$str = preg_replace( '/\[code\]((?s).*?)<br \/>\[\/code\]/', '<div class="post_code"><pre>$1</pre></div>', $str );
		$str = preg_replace( '/\[code\]((?s).*?)\[\/code\]/', '<div class="post_code"><pre>$1</pre></div>', $str );
		
		/**
		** Quotes...
		*/
		
		$str = $this->quote_special_callback($str);
		$str = $this->quote_normal_callback($str);
		
		/**
		** People Tags
		*/
		
		$str = preg_replace( '/\[@((?s).*?)\]/', '<a href="'.cp::link(array('members','$1')).'">$1</a>', $str );
		
				
		/**
		** Wikicode
		*/
		
		$str = preg_replace( '/::::: ((?s).*?) :::::/', '<div id="$1" class="title_4">$1</div>', $str );
		$str = preg_replace( '/:::: ((?s).*?) ::::/', '<div id="$1" class="title_3">$1</div>', $str );
		$str = preg_replace( '/::: ((?s).*?) :::/', '<div id="$1" class="title_2">$1</div>', $str );
		$str = preg_replace( '/:: ((?s).*?) ::/', '<div id="$1" class="title_1">$1</div>', $str );
		$str = preg_replace( '/\[\[((?s).*?)\|((?s).*?)\]\]/', '<a href="'.$this->contentURL.'$1">$2</a>', $str );
		$str = preg_replace( '/\[\[((?s).*?)\]\]/', '<a href="'.$this->contentURL.'$1">$1</a>', $str );
		
		/**
		** Smilies
		*/		
		
		$str = preg_replace( "/\[:\)\]/", '<img src="'.$this->smile.'smile.png" />', $str );
		$str = preg_replace( '/\[angel\]/', '<img src="'.$this->smile.'angel.png" />', $str );
		$str = preg_replace( '/\[angry\]/', '<img src="'.$this->smile.'angry.png" />', $str );
		$str = preg_replace( '/\[8-\)\]/', '<img src="'.$this->smile.'cool.png" />', $str );
		$str = preg_replace( '/\[:&#039;\(\]/', '<img src="'.$this->smile.'cwy.png" />', $str );
		$str = preg_replace( '/\[ermm\]/', '<img src="'.$this->smile.'ermm.png" />', $str );
		$str = preg_replace( '/\[:D\]/', '<img src="'.$this->smile.'grin.png" />', $str );
		$str = preg_replace( '/\[&lt;3\]/', '<img src="'.$this->smile.'heart.png" />', $str );
		$str = preg_replace( '/\[:\(\]/', '<img src="'.$this->smile.'sad.png" />', $str );
		$str = preg_replace( '/\[:O\]/', '<img src="'.$this->smile.'shocked.png" />', $str );
		$str = preg_replace( '/\[:P\]/', '<img src="'.$this->smile.'tongue.png" />', $str );
		$str = preg_replace( '/\[;\)\]/', '<img src="'.$this->smile.'wink.png" />', $str );
		$str = preg_replace( '/\[alien\]/', '<img src="'.$this->smile.'alien.png" />', $str );
		$str = preg_replace( '/\[blink\]/', '<img src="'.$this->smile.'blink.png" />', $str );
		$str = preg_replace( '/\[blush\]/', '<img src="'.$this->smile.'blush.png" />', $str );
		$str = preg_replace( '/\[cheerful\]/', '<img src="'.$this->smile.'cheerful.png" />', $str );
		$str = preg_replace( '/\[devil\]/', '<img src="'.$this->smile.'devil.png" />', $str );
		$str = preg_replace( '/\[dizzy\]/', '<img src="'.$this->smile.'dizzy.png" />', $str );
		$str = preg_replace( '/\[getlost\]/', '<img src="'.$this->smile.'getlost.png" />', $str );
		$str = preg_replace( '/\[happy\]/', '<img src="'.$this->smile.'happy.png" />', $str );
		$str = preg_replace( '/\[kissing\]/', '<img src="'.$this->smile.'kissing.png" />', $str );
		$str = preg_replace( '/\[ninja\]/', '<img src="'.$this->smile.'ninja.png" />', $str );
		$str = preg_replace( '/\[pinch\]/', '<img src="'.$this->smile.'pinch.png" />', $str );
		$str = preg_replace( '/\[pouty\]/', '<img src="'.$this->smile.'pouty.png" />', $str );
		$str = preg_replace( '/\[sick\]/', '<img src="'.$this->smile.'sick.png" />', $str );
		$str = preg_replace( '/\[sideways\]/', '<img src="'.$this->smile.'sideways.png" />', $str );
		$str = preg_replace( '/\[silly\]/', '<img src="'.$this->smile.'silly.png" />', $str );
		$str = preg_replace( '/\[sleeping\]/', '<img src="'.$this->smile.'sleeping.png" />', $str );
		$str = preg_replace( '/\[unsure\]/', '<img src="'.$this->smile.'unsure.png" />', $str );
		$str = preg_replace( '/\[woot\]/', '<img src="'.$this->smile.'w00t.png" />', $str );
		$str = preg_replace( '/\[wassat\]/', '<img src="'.$this->smile.'wassat.png" />', $str );
														
		//-----------------------------------
		// Replace and add a few bits and bobs
		//
		
		//$str = preg_replace( $this->bb, $this->html, $str );
		
		return $str;
		
	}
	
	/**
	** quote_special_callback() and
	** quote_normal_callback - recursions to preg_replace multi level quotes
	**
	**
	*/	
	public function quote_special_callback($str, $level=0)
	{
		
		/**
		** Prevents going any further than 5 deep quotes. This prevents someone making an infinite loop
		*/
		$this->level = $level;
		if ( $this->level == 5 ) return $str;
		$this->level++;
		
		/**
		** Replace!
		*/
		$str = preg_replace_callback( 
			'/\[quote postid=(.+?) displayname=(.+?) time=(.+?)\]((?s).*)\[\/quote\]/',
			function ($m) {
	            return
	            	'<blockquote postid='.$m['1'].' displayname='.$m['2'].' time='.$m['3'].'><div class="quote_info">Quote from <a href="'.cp::link(array('members',$m['2'])).'">'.$m['2'].'</a> '.cp::display()->time2str($m['3']).'</div>'.$this->quote_special_callback($m['4'], $this->level).'</blockquote>';
	            
	            
	            //<div class="post_quote"><div class="quote_info"><a href="#">Quote</a> from <a href="#">'.$m['2'].'</a> '.cp::display()->time2str($m['3']).'</div>'.$this->quote_special_callback($m['4']).'</div>';
	            
	        },
			$str
		);
		
		return $str;
		
	}
	
	public function quote_normal_callback($str)
	{
		
		$str = preg_replace_callback( 
			'/\[quote\]((?s).*)\[\/quote\]/',
			function ($m) {
	            return '<blockquote>'.$this->quote_normal_callback($m['1']).'</blockquote>';
	        },
			$str
		);
		
		return $str;
		
	}	
	
	/**
	** wiki()
	**
	**
	*/
	public function wiki($str)
	{
		
		$exp = explode("[/TOC]\n", $str);
		
		/**
		** TOCneeds to be applied...
		*/
		if ( $exp['1'] )
		{
			$ret['toc'] = $this->toc( $exp['0'] );
			$ret['main'] = $this->bb2html( $exp['1'] );
		}
		else
		{
			$ret['main'] = $this->bb2html( $exp['0'] );
		}
		
		return $ret;
		
	}
	
	public function toc($url)
	{
		
		$links = explode( "\n", $url );
		
		foreach( $links as $str )
		{
			if ( $str )
			{			
				$level = substr_count($str, ':') - 1;
				$array[] = array('level' => $level, 'link' => substr( $str, $level + 1 ));
			}
		}
		
		$level = array('0', '0', '0', '0');
		
		foreach ( $array as $id => $link )
		{
			
			if( $link['level'] == 1 )
			{
				$level['1']++;
				$level['2'] = 0;
				$level['3'] = 0;
				$level['4'] = 0;
				$ret = $level['1']. ' ' . $link['link'] .'';
			}
			
			else
			if( $link['level'] == 2 )
			{
				$level['2']++;
				$level['3'] = 0;
				$level['4'] = 0;
				$ret = $level['1']. '.' . $level['2']. ' ' . $link['link'] .'';
			}
			
			else
			if( $link['level'] == 3 )
			{
				$level['3']++;
				$level['4'] = 0;
				$ret = $level['1']. '.' . $level['2']. '.' . $level['3']. ' ' . $link['link'] .'';
			}
			
			else
			if( $link['level'] == 4 )
			{
				$level['4']++;
				$ret = $level['1']. '.' . $level['2']. '.' . $level['3']. '.' . $level['4']. ' ' . $link['link'] .'';
			}
			
			$toc .= '<li class="level_'.$link['level'].'"><a href="#'.$link['link'].'">'.$ret.'</a></li>';
			
		}
		
		return '<ul>'.$toc.'</ul>';
		
	}
	
}

?>