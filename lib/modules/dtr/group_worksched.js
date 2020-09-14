$( document ).ready(function(){
	if( module.get_value('view') == "edit" ){
		get_subordinates();
	}
});

/**
 * Get the subordinates of logged in user who is editing a group worksched
 * @return void
 */
function get_subordinates(){
	// save original values to use after updating the list via ajax
	var values = $('#multiselect-employee_id :selected');
	$.ajax({
		url: module.get_value('base_url') + module.get_value('module_link') + "/get_subordinates",
		type:"POST",
		dataType: "json",
		beforeSend: function(){
			 		
		},
		success: function(data){		
			$('#multiselect-employee_id').html( data.select );
			$(values).each(function(index, option) {				
				$('#multiselect-employee_id option[value="' + $(option).val() + '"]').attr('selected','selected');
			});			
			$('#multiselect-employee_id').multiselect('refresh');
			$('#multiselect-employee_id').multiselect().multiselectfilter({
                show:['blind',250],
                hide:['blind',250],
                selectedList: 1
            });
		}
	});
}