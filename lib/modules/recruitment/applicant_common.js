$(document).ready(function () {
    if ($('#record_id').val() == '-1') {
		$('#firstname, #lastname, #middlename, #sex-male, #sex-female, #birth_date-temp').change(check_prev_applicant);
    }   

    $('.education_school').live('change', function(){
    	var selected = $(this).val();
    	if (selected == '-1') {
    		$(this).parents('.form-item').next().removeClass('hidden');
    	}else{
    		$(this).parents('.form-item').next().addClass('hidden');
    	};
    })
});

function check_prev_applicant()
{   
	if ($('#firstname').val() != '' && $('#lastname').val() != ''
		&& $('#birth_date').val() != '' && $('input[name="sex"]').val() != ''
		) {
		post = $('#firstname, #lastname, #middlename, input[name="sex"], #birth_date').serialize();

		$.ajax({
			url: module.get_value('base_url') + 'recruitment/appform/check_prev_applicant',
			data: post,
			type: 'post',
			dataType: 'json',
			success: function (response) {
				if (response.exists) {
					if (typeof(appform_prev_app) === typeof(Function)) {
						appform_prev_app(response);
					} else {						
						$.blockUI({message: '<div class="now-loading align-center"><small>This applicant exists on our records. What would you like to do? </small><hr><input type="button" id="ve" value="Verify Information" /><input type="button" id="ce" value="Continue Encoding" /><br /><br /></div>'});
					}
				}
			}
		});
	}
}
