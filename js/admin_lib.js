/*===================================================
//	CipherPixel © All Rights Reserved
//---------------------------------------------------
//	CP-Core
//		by cipherpixel.net
//---------------------------------------------------
//	File created: August 31, 2014 
//=================================================*/

$(document).ready(function(){
	
	/**
	** CP Table Tabs
	*/
	$(document).on('click', '.tabletab', function (){		
		var subcat = $(this).attr("subcat");
		var tabNum = $(this).attr("tabNum");
		document.currentTab = tabNum;
		$(".tabletab").removeClass("selected", 200);
		$(".tablediv").hide();
		$("#div_" +  subcat ).fadeIn("slow");
		$(this).addClass("selected", 200);
	});
	
	$(document).on('click', '.tabscroll', function (){
		var Tabs 		= new Array();
		var direction 	= $(this).attr("do");
		
		if ( !Tabs[0] ){
			$( ".tabletab" ).each(function( index ) {
				Tabs[Tabs.length] = $(this).attr("subcat");
			});
		}
		
		if ( !document.currentTab ){
			document.currentTab = 0;
		}
		
		if ( direction == 'up' ){
			document.currentTab++;
		}else{
			document.currentTab--;
		}
		
		if ( document.currentTab < 0 ) {
			document.currentTab = Tabs.length - 1;
		}
		
		if ( document.currentTab > ( Tabs.length - 1 ) ) {
			document.currentTab = 0;
		}
		
		$(".tabletab").removeClass("selected", 200);
		$(".tablediv").hide();
		$("#div_" +  Tabs[ document.currentTab ] ).fadeIn("slow");
		$("[subcat=" + Tabs[ document.currentTab ] + "]").addClass("selected", 200);
		
	});
	
	/**
	** Tabkey makes a tab
	*/
	$(document).delegate('.tabable', 'keydown', function(e) {
		var keyCode = e.keyCode || e.which;
		
		if (keyCode == 9) {
		e.preventDefault();
		var start = $(this).get(0).selectionStart;
		var end = $(this).get(0).selectionEnd;
		
		// set textarea value to: text before caret + tab + text after caret
		$(this).val($(this).val().substring(0, start)
		            + "\t"
		            + $(this).val().substring(end));
		
		// put caret at right position again
		$(this).get(0).selectionStart =
		$(this).get(0).selectionEnd = start + 1;
		}
	});
	
	/**
	** Admin notepad
	*/
	$("#adminnote").blur(function(){
		
		$( "#adminnote" ).css({ 'opacity' : 0.5 });
		$( ".notestat" ).html("Saving...");
		notepad = $("#adminnote").val();
	
		var attributes = {
			cmd: $('#adminnote').attr("cmd"),
			note: $("#adminnote").val(),
		};
	
		$.post( lprefix + "index.php?app=" + app + "&ajax=gen", attributes )
		.done(function( data ) {
			console.log(data);
			var ret = jQuery.parseJSON(data);
			if (ret.res == 'suc'){
				$( ".notestat" ).html("Saved");
				$( "#adminnote" ).css({ 'opacity' : 1 });
			}else{
				alert( ret.mess );
			}
		});		
		
	});
  
});

