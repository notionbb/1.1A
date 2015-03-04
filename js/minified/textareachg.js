function load_editor() {
	
	$(".editor").sceditor({
		plugins: 'bbcode',
		charset: 'utf-8',
		style: lprefix + 'js/minified/jquery.sceditor.default.min.css',
		fonts: "Helvetica,Arial,Sans-serif",
		emoticonsRoot: lprefix + 'images/',
		toolbar: 'bold,italic,underline,strike,subscript,superscript|font,size,color,removeformat|code|ident,horizontalrule|image,email,link,unlink,emoticon,youtube,date,time|print,maximize,source'
	});
	
	$.sceditor.plugins.bbcode.bbcode.set('quote', {
		tags: {
			blockquote: null
		},
		isInline: false,
		quoteType: $.sceditor.BBCodeParser.QuoteType.never,
		format: function(element, content) {
			var postid = element.attr('postid'),
				displayname = element.attr('displayname'),
				time = element.attr('time'),
				attA = '',
				attB = '',
				attC = '';
			
			if ( postid )
				attA = ' postid='+postid;
				
			if ( displayname )
				attB = ' displayname='+displayname;
				
			if ( time )
				attC = ' time='+time;
			
			return '[quote'+attA+attB+attC+']' + content + '[/quote]';
		},
		html: function(token, attrs, content) {
			return '<blockquote postid="'+attrs.postid+'" displayname="'+attrs.displayname+'" time="'+attrs.time+'">' + content + '</blockquote>';
		}
		
	});
	
}

function load_wait() {	
	$(function() {
		$(".editor_wait").sceditor({
			plugins: 'bbcode',
			charset: 'utf-8',
			style: lprefix + 'js/minified/jquery.sceditor.default.min.css',
			fonts: "Helvetica,Arial,Sans-serif",
			emoticonsRoot: lprefix + 'images/',
			toolbar: 'bold,italic,underline,strike,subscript,superscript|font,size,color,removeformat|code|ident,horizontalrule|image,email,link,unlink,emoticon,youtube,date,time|print,maximize,source'
		});
		
		$.sceditor.plugins.bbcode.bbcode.set('quote', {
			tags: {
				blockquote: null
			},
			isInline: false,
			quoteType: $.sceditor.BBCodeParser.QuoteType.never,
			format: function(element, content) {
				var postid = element.attr('postid'),
					displayname = element.attr('displayname'),
					time = element.attr('time'),
					attA = '',
					attB = '',
					attC = '';
				
				if ( postid )
					attA = ' postid='+postid;
					
				if ( displayname )
					attB = ' displayname='+displayname;
					
				if ( time )
					attC = ' time='+time;
				
				return '[quote'+attA+attB+attC+']' + content + '[/quote]';
			},
			html: function(token, attrs, content) {
				return '<blockquote postid="'+attrs.postid+'" displayname="'+attrs.displayname+'" time="'+attrs.time+'">' + content + '</blockquote>';
			}
			
		});
			
	});
}

$(document).ready(function(){

	load_editor();
	
});
	