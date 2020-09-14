function generate(source) {
	var pdfobj = new PDFObject({	
		url: source,
		pdfOpenParams: {
			navpanes: 0,
			toolbar: 0,
			view: "FitV",
			pagemode: "thumbs"
		}
	});

	var htmlObj = pdfobj.embed('pdf');

	if(htmlObj){
		return true;
	}
	else{
		return false;
	}

}

$(document).ready(function () {

	$('.a-manual').each(function(){
		if($(this).hasClass('pdf-file')){
			if(generate($(this).attr('rel'))){
				return false;
			}
			else{
				
				$(this).attr('href',$(this).attr('rel'));
			}
		}
	});

	$('.a-manual').click(function () {
		if($(this).hasClass('pdf-file')){
			if(generate($(this).attr('rel'))){
				return false;
			}
		}
	});

	//generate($('.a-manual').first().attr('rel'));
	
	//$('.a-manual').click(function () {
	//	generate($(this).attr('rel'));
	//});
});