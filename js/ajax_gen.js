/*===================================================
//	CipherPixel © All Rights Reserved
//---------------------------------------------------
//	CP-Core
//		by cipherpixel.net - Modified by: notionbb.com
//---------------------------------------------------
//	File created: August 31, 2014 
//=================================================*/

/**
** Simple Javascript function to make ajax calls easier
*/

$(document).ready(function(){
	
	// Glob Holder
	$.miniMenu = new Object();
	
	/**
	** Form Highlightss
	*/	
	$(document).on('focus', 'input', function () {
		if ( $(this).attr("type") == 'submit' ) return;
		$(this).addClass("inputfocus");
	});
	
	$(document).on('focusout', 'input', function () {
		$(this).removeClass("inputfocus");
	});
	
	$(document).on('focus', '.drop', function () {
		if ( $(this).attr("type") == 'submit' ) return;
		$(this).addClass("inputfocus");
	});
	
	$(document).on('focusout', '.drop', function () {
		$(this).removeClass("inputfocus");
	});
	
	/**
	** Popup Drivers
	*/
	$('.popup2').on('click', '#no', function (){
		$(".popup2").hide();
		$(".ajax_white").html("");
	});
	
	/**
	** On click...
	*/	
	$(document).on('click', '.ajax_gen', function (){

		var nopop 	= $(this).attr("nopop");
		var donthide= false;
		
		if ( !nopop ) { $(".popup").show(); }
		
		var attributes = {
			cmd: 		$(this).attr("cmd"),
			extra:		$(this).attr("extra"),
			moredata:	$( '#' + $(this).attr("moredata") ).val(),
			selectArray:$.miniMenu.selectArray
		};
		
		if ( $(this).attr("sce") ){
			attributes['sce'] = $("#" + $(this).attr("sce") ).sceditor('instance').val();
		}

		$.post( lprefix + "index.php?app=" + app + "&ajax=gen", attributes )
		.done(function( data ) {
			console.log(data);
			var ret = jQuery.parseJSON(data);
			
			if ( ret.res == 'error' ) {
				alert( ret.msg );
			}
			else
			{
				
				if ( ret.alert ) {
					alert( ret.alert );
				}
				
				if ( ret.log) {
					console.log( ret.log );
				}
				
				if ( ret.redirect ) {
					window.location.href = ret.redirect;
					donthide = true;
				}
				
				if ( ret.refresh ) {
					location.reload();
					donthide = true;
				}
			
				if ( ret.show ) {
					$( ret.show ).show();
				}
				
				if ( ret.hide ) {
					$( ret.hide ).hide();
				}
				
				if ( ret.toggle ) {
					$( ret.toggle ).toggle();
				}
				
				if ( ret.fadeOut ) {
					$( ret.fadeOut ).fadeOut("slow");
				}
				
				if ( ret.slideUp ) {
					$( ret.slideUp ).slideUp();
				}
				
				if ( ret.slideDown ) {
					$( ret.slideDown ).slideDown();
				}
				
				if ( ret.swop ) {
					$( ret.swop ).html( ret.html );
				}
				
				if ( ret.swop2 ) {
					$( ret.swop2 ).html( ret.html2 );
				}
				
				if ( ret.prepend ) {
					$( ret.html ).hide().prependTo( ret.prepend ).slideDown("slow");
				}
				
				if ( ret.append ) {
					$( ret.html ).hide().appendTo( ret.append ).slideDown("slow");
				}
				
				if ( ret.remove ) {
					$( ret.remove ).removeClass(ret.class);
				}
				
				if ( ret.editor ) {
					$("#post_content_" + ret.id).html(ret.editor);				    
				    $("#editor_" + ret.id).val(ret.post);
				    load_editor("editor_" + ret.id);
				}
				
				if ( ret.pop ) {
					$(".popup2").fadeIn();
			    	$(".popup").hide()
				}else{
					$(".popup2").hide();
				}
				
				if ( ret.enhance ) {
					$( ret.enhance ).trigger('create');
				}
				
			}
			
			if ( !donthide ){
				$(".popup").hide();
			}
		});

	});
	
	/**
	** Simple show a div
	*/
	$('body').on('click', '.toggleShow', function (){		
		var showId 	= $(this).attr("show");
		var showC	= $(this).attr("showC");
		$( "#" + showId ).toggle();
		$( "." + showC ).toggle();
	});
	
	$('body').on('click', '.toggleShowSlow', function (){		
		var showId = $(this).attr("show");
		var showC	= $(this).attr("showC");		
		$( "#" + showId ).fadeToggle("fast");
		$( "." + showC ).toggle();
	});
	
	$('body').on('click', '.ajax_hideMe', function (){		
		$(this).hide();
	});
	
	/**
	** Show a div
	*/	
	$(document).on({
	    mouseenter: function () {
	        $( "#" + $(this).attr("show") ).fadeIn();
	    },
	    mouseleave: function () {
	        $( "#" + $(this).attr("show") ).fadeOut();
	    }
	}, '.ajax_hoverShow');
	
	$(document).on({
	    mouseenter: function () {
	         $( "#" + $(this).attr("show") ).removeClass("trans", "slow");
	    },
	    mouseleave: function () {
	        $( "#" + $(this).attr("show") ).addClass("trans", "slow");
	    }
	}, '.ajax_transShow');
	
	/**
	** Div Highlighter
	*/
	if ( window.div_highlight ){
		$( div_highlight ).addClass("post-highlight");
		$( div_highlight ).removeClass("post-highlight", 1500);
	}
	
	/**
	** Multi Select
	*/
	$(document).on('click', '.ajax_multiclick', function (){		
		var unclicked = $(this).attr("unclicked");
		var clicked = $(this).attr("clicked");
		var arrayId = $(this).attr("arrayId");
		
		if ( !$.miniMenu.selectArray ){
			$.miniMenu.selectArray = [];
		}
		
		$( "#" + unclicked ).toggle();
		$( "#" + clicked ).toggle();
		
		var idx = $.inArray(arrayId, $.miniMenu.selectArray);
		if (idx == -1) {
		  $.miniMenu.selectArray.push(arrayId);
		} else {
		  $.miniMenu.selectArray.splice(idx, 1);
		}
		
	});
	
	/**
	** Notifications
	*/
	$(document).on('click', '.ajax_noti', function (){
		
		if( $.miniMenu.notiup == false ){
			$("#noti-pop").hide();
			$(".noti-pop-load").hide();
			$(".noti-pop-content").html("");
			$.miniMenu.notiup = true;
		}else{
			$.miniMenu.notiup = false;
			
			$(".noti-pop-load").show();
			$( "#noti-pop" ).show();
		
			var attributes = {
				cmd: 		$(this).attr("cmd"),
			};
	
			$.post( lprefix + "index.php?app=" + app + "&ajax=gen", attributes )
			.done(function( data ) {
				var ret = jQuery.parseJSON(data);
				
				if ( ret.res == 'error' ) {
					alert( ret.msg );
				}else{					
					$(".noti-pop-content").html( ret.html );
					$("#noti-count").html("");				
				}
				$(".noti-pop-load").hide();	
			});
			
		}

	});
	
	/**
	** Listener...
	*/
	setInterval(function() {
		var attributes = {
			cmd: 'listen',
		};
		$.post( lprefix + "index.php?app=" + app + "&ajax=gen", attributes )
		.done(function( data ) {
			console.log(data);
			var ret = jQuery.parseJSON(data);
		
			if ( ret.res == 'error' ) {
				alert( ret.msg );
			}else{					
				$("#noti-count").html(ret.new_noti);				
			}
			$(".noti-pop-load").hide();	
		});
	}, 30000); // 30 secs
  
});

