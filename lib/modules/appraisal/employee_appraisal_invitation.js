$(document).ready(function() {
	var view = module.get_value('view');
	if (view == 'edit') {
		var po = "[<span id='performance'>Performance Objective</span>]";
		
		var html = "Good day!<br><br> Please login to your workwise account and appraise <b>"+$("#appraisee_name").val() +"</b> for [<em><span id='performance'>Performance Objective</span></em>] <br> Kindly accomplish appraisal on or before [<em><span id='deadline'>Appraisal Deadline</span></em>] <br><br>";

		$('#employee_appraisal_criteria_question_id').live('change',function() {
			$.ajax({
                url: module.get_value('base_url') + module.get_value('module_link') + '/get_planning_item',
                type:"POST",
                data: 'performance_id='+$(this).val()+'&appraisee_id='+$('input[name="appraisee_id"]').val() + '&period_id='+$("#employee_appraisal_id").val(),
                dataType: "json",
                async: false,        
                success: function ( response ) {
                	$('label[for="planning_item"]').parent().remove();
                    var planning_item = '<div class="form-item even ">';
                    planning_item = planning_item + '<label class="label-desc gray" for="planning_item"> Planning Item: </label>';
                    planning_item = planning_item + '<div class="multiselect-input-wrap">';
                    planning_item = planning_item + response.planning_item_html;
                    planning_item = planning_item + '</div>';

                    $('label[for="employee_appraisal_criteria_question_id"]').parent().after(planning_item);

                    $('#planning_item').multiselect().multiselect({
                        show:['blind',250],
                        hide:['blind',250],
                        selectedList: 1
                    });
                }
            });
		});
		
		if (module.get_value('record_id') == '-1') {
			$('textarea[name="email_template"]').val(html);
		}

		// $('.icon-16-listback').live('click', function(){
		// 	window.location = module.get_value('base_url') + "employee/appraisal/index/" + $('#employee_appraisal_id').val();
		// });
	};

	$('.icon-16-listback').live('click', function(){
		window.location = module.get_value('base_url') + module.get_value('module_link') + '/index/' + $('input[name="appraisee_id"]').val() + '-' + $('input[name="employee_appraisal_id"]').val();
	});


	$('.icon-16-add-listview').die('click');
	$('.icon-16-add-listview').live('click', function(){
		$('#record-form').append('<input type="hidden" name="period_id" value="'+ $(this).attr('period_id') +' " />');
		record_action('edit', -1, $(this).attr('module_link'), 'appraisee_id', $(this).attr('employeeid'));
	});
});


// function goto_detail( data )
// {
//     // if (data.record_id > 0 && data.record_id != '') 
//     // {
//     //     module.set_value('record_id', data.record_id);    
//        //window.location = module.get_value('base_url') + "employee/appraisal/index/" + $('#employee_appraisal_id').val();  
//     // }
//     // console.log(data)
// }

function goto_detail( data ){
    if (data.record_id > 0 && data.record_id != '') {
        module.set_value('record_id', data.record_id);    
        window.location.href = module.get_value('base_url') + module.get_value('module_link') + '/detail/' + module.get_value('record_id');         
    }
}