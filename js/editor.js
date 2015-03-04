$(document).ready(function(){
	
	/* Editor Loader */
	if( $("#editor_new").length ){
	     load_editor("editor_new");
	}
	
	$("#editor_click").one('focus', function(){
		load_editor("editor_click");
	});
	
	/* Quote Commander */
	$(".ajax_quote").click(function(){
		var postId = $(this).attr("postId");
		var id = $(this).attr("do");
			
		var displayname = $(this).attr("displayname");
		var time = $(this).attr("time");
		
		var quote = $("#post_content_" + postId).html();

		(function($) {
		    $.strRemove = function(theTarget, theString) {
		        return $("<div/>").append(
		            $(theTarget, theString).remove().end()
		        ).html();
		    };
		})(jQuery);
		
		quote = $.strRemove(".quote_info", "<div>"+quote+"</div>");
		
		var bbcode = $("#editor_new").sceditor('instance').toBBCode(quote);
		var current = $("#editor_new").sceditor('instance').val();
		
		var newcont = "[quote postid="+postId+" displayname="+displayname+" time="+time+"]" + bbcode + "[/quote]"; 
		
		if ( $(this).hasClass("but-small-sel") )
		{
			$(this).removeClass("but-small-sel");
			current = current.replace(newcont, '');
			$("#editor_new").sceditor('instance').val(current);
		}else{
			$(this).addClass("but-small-sel");
			$("#editor_new").sceditor('instance').val(current + newcont);			
		}

	});
	
	/* File Uploads */
	$("#file-upload-alt").click(function(){
		$("#file-upload-but").click();
	});
	
	$('input[type=file]').on('change', function(event){
 		if ( !files ){
			var files;
		}
		if ( !$.miniMenu.uploadDivs ){
			$.miniMenu.uploadDivs = new Array();
		}
		if ( !$.miniMenu.ids ){
			$.miniMenu.ids = new Array();
		}

		files = event.target.files;
 
		event.stopPropagation(); // Stop stuff happening
   		event.preventDefault();files = event.target.files;

		var data = new FormData();
		
		/* Foreach File */
		$.each(files, function(key, value)
		{
			
			/* Organise Load Boxes */
			var load_box 		= $("#load_box").clone();
			var load_box_name	= load_box.children("#load_box_file_name");
			
			load_box.attr("id", value.name);
			load_box.prependTo("#upload_holder");
			load_box_name.html(value.name);
			load_box.slideDown();
			
			/* Save for later... */
			$.miniMenu.uploadDivs[ value.name ] = load_box;
			
			/* Build post data */
			data.append("editor_" + key, value);
			
		});
		
		$.ajax(
		{
	        url: lprefix + "index.php?app="+ app +"&ajax=post_upload&forum=" + window.cur_slug,
	        type: 'POST',
	        data: data,
	        cache: false,
	        dataType: 'json',
	        processData: false, // Don't process the files
	        contentType: false, // Set content type to false as jQuery will tell the server its a query string request
    	}
    	).done(function( data ) {	    	
	    	if ( data.done )
	    	{
		    	$.each(data.done, function(key, value){
			    	var div = $.miniMenu.uploadDivs[ value ]
			    	div.children("#load_box_bar").hide();
			    	div.addClass("file-row-success");
			    	div.children("#load_box_file_name").html('<a target="_blank" href="'+data.urls[value]+'">'+value+'</a>');
			    	$.miniMenu.ids.push(data.time[value]);
			    	$("#file-ids").val( $.miniMenu.ids.join() );
		    	});
	    	}	    	
    	});
	  
	});
	
});

function load_editor(id) {
	
	$( "#" + id ).sceditor({
		plugins: 'bbcode',
		charset: 'utf-8',
		style: window.lprefix + 'js/minified/jquery.sceditor.default.min.css',
		fonts: "Helvetica,Arial,Sans-serif",
		emoticonsRoot: window.lprefix + 'images/',
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