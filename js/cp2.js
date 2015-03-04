$(document).ready(function(){
	var lastId,
    topMenu = $("#topBar"),
    topMenuHeight = topMenu.outerHeight() + 35,
    menuItems = topMenu.find(".jslink"),
    scrollItems = menuItems.map(function(){
      var item = $($(this).attr("href"));
      if (item.length) { return item; }
    });
    
    menuItems.click(function(e){
	  e.preventDefault();
	  var href = $(this).attr("href"),
	      offsetTop = href === "#" ? 0 : $(href).offset().top-topMenuHeight+1;
	  $('html, body').stop().animate({ 
	      scrollTop: offsetTop
	  }, 300);
	});
	
	$( ".page_link" ).click(function(e){
	  e.preventDefault();
	  var href = $(this).attr("href"),
	      offsetTop = href === "#" ? 0 : $(href).offset().top-topMenuHeight+1;
	  $('html, body').stop().animate({ 
	      scrollTop: offsetTop
	  }, 300);
	});
	
	// Bind to scroll
	$(window).scroll(function(){
	   // Get container scroll position
	   var fromTop = $(this).scrollTop()+topMenuHeight;
	   
	   // Get id of current scroll item
	   var cur = scrollItems.map(function(){
	     if ($(this).offset().top < fromTop){
	     	return this;
     	 }else{
	     	 return;
     	 }
	   });
	   
	   // Get the id of the current element
	   cur = cur[0];

	   if (lastId !== cur.attr("id")) {
		   $( "#link_" + lastId ).removeClass( "menusel", 200 );
	       lastId = cur.attr("id");
	       $( "#link_" + lastId ).addClass( "menusel", 200 );	       
	   }                   
	});

});

/* Fixed contents
$(window).load(function() {
	var position2 = $('#meta').position();
	$('#meta').css({position: 'fixed', top: position2.top, right: position2.right });
	var position = $('#toc').position();
	$('#toc').css({position: 'fixed', top: position.top, right: position.right });
});*/