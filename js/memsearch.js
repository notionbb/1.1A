function memsea() {	
	
	var attributes = {		
		dname:  $('#memsearch').val(),
		cmd: 	$('#memsearch').attr("cmd")
	};
	
	$('#load').show();	
	$.post( lprefix + "index.php?app=" + app + "&ajax=gen", attributes )
		.done(function( data ) {
			console.log(data);
			var ret = jQuery.parseJSON(data);
			if ( ret.res == 'error' ) {
				alert( ret.msg );
			}
			else
			{
				
				if ( ret.swop ) {
					$( ret.swop ).html( ret.html );
				}
				
			}
			$('#load').hide();
		}
	);		
	
}

$(document).ready(function () {

	//setup before functions
	var typingTimer;                //timer identifier
	var doneTypingInterval = 1000;  //time in ms, 5 second for example
	
	//on keyup, start the countdown
	$('#memsearch').keyup(function(){
	    typingTimer = setTimeout(memsea, doneTypingInterval);
	});
	
	//on keydown, clear the countdown 
	$('#memsearch').keydown(function(){
	    clearTimeout(typingTimer);
	});
	
	memsea();

});
