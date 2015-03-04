$(document).ready(function(){
	
	$(".butPlus").click(function(){
		var doType = $(this).attr("do");
		$("input:checkbox[do=" + doType + "]").prop("checked", true);
	});
	
	$(".butMinus").click(function(){
		var doType = $(this).attr("do");
		$("input:checkbox[do=" + doType + "]").prop("checked", false);
	});
	
	$(".toggleInput").click(function(){
		var toggle = $(this).attr("toggle");
		$("input:checkbox[name=" + toggle + "]").prop("checked", function( i, val ) {
		  return !val;
		});		
	});
  
});

