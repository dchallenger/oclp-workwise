$(document).ready(function () {
	$('#employee_id').change(function () {
		$.ajax({
			url: module.get_value('base_url') + module.get_value('module_link') + '/get_checklist',
			type: 'post',
			data: 'employee_id=' + $('#employee_id').val(),
			dataType: 'html',
			success: function (response) {
				$('#checklist').replaceWith(response);
			}
		});
	});


	$('.cancel').click(go_back);
	$('.icon-16-listback').click(go_back);
});

function go_back(){
    window.location = module.get_value('base_url') + 'employee/clearance';
}