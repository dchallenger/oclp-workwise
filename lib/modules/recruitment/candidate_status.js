$(document).ready(function () {
	$('input[name="hired_flag"]').change(handle_flags);
	$('input[name="rejected_flag"]').change(handle_flags);
	$('input[name="interview_flag"]').change(handle_flags);
});

function handle_flags() {
	if ($(this).val() == 1) {
		$('input[id$="-no"]').attr('checked', 'checked');
		$('#' + $(this).attr('name') + '-yes').attr('checked', 'checked');
	}
	
	return true;
}