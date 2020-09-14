
$(document).ready(function(){

	setTimeout("$('#loading').hide(); $('#Feeds').slideDown()", 3000);

	$("a.showDropdown").click( function() {
		showHideDropdown();
	     return false;
	});

	
	$(".myAccountDropdown").click( function(event){
	    event.stopPropagation();
	     //alert( event )
	});

	$(document).click(function(){
		$(".myAccountDropdown").stop().slideUp();
		$("li.dropdownActive").removeClass("dropdownActive");
	});
});

function showHideDropdown() {
	if ( $(".myAccountDropdown").is(":visible") ) {
		$("a.showDropdown").parent("li").removeClass("dropdownActive");
	} else {
		$("a.showDropdown").parent("li").addClass("dropdownActive");
	}

	$("a.showDropdown").next(".myAccountDropdown").stop().slideToggle();
	
}
