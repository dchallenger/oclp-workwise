$(document).ready(function() {      

	$('#department_id, #division_id, #position_id, #rank_id, #competency').attr('disabled', true);
    var status = $('#status_id').val();
    $('label[for="inclusive_dates"]').after('<div class="text-input-wrap"><div id="dates-container"></div></div>');

    if (module.get_value('record_id') != -1  && module.get_value('view') == 'detail') {
        if ($('#div-detail-dates-affected').size() > 0) {
            $('#dates-container').html($('#div-detail-dates-affected').html());
        }
        
        if ($('#combined_allocation').attr('allocation') == 'combined') {
             $('label[for="allocated"]').parent().find('.text-input-wrap').html($('#combined_allocation').html());
        };
       

    }
	if (user.get_value('post_control') != 1) {
		$('#employee_id').val(user.get_value('user_id'))
		$('#employee_id').trigger('change');

        if (module.get_value('view') == 'edit') {
            if (module.get_value('record_id') == -1 ) {
                    get_employee_details(user.get_value('user_id'));
            }else{
                $('label[for=employee_id]').parent().find('.select-input-wrap').hide();
                $('label[for=employee_id]').parent().append('<div class="text-input-wrap"><input type="text" class=" input-text " readonly value="'+$('#employee_id :selected').text()+'" ></div>')
            }
        };
        

		$('#investment, #itb, #ctb, #remaining_itb, #excess_itb, #excess_ctb, #excess_stb, #remaining_ctb, #stb, #remaining_stb, #remarks , #idp_completion, #remaining_allocated').attr('readonly', true);
		$('input[name="budgeted"] , #allocated, input[name="service_bond"]').attr('disabled', true);
	}else{
        $('#stb').live('keyup', function() {
            var supplemental  = $(this).val();
            var investment = $('#investment').val();
           $('#remaining_stb').val(supplemental); 
           $('#remaining_stb').attr('remaining',supplemental.replace(/,/g, '')); 
          

           if ($('#training_type').val() == 3 && investment != "") {
                var remaining_budget = $('#remaining_stb').attr('remaining');

                var excess = parseInt(remaining_budget) - parseInt(investment.replace(/,/g, '')); 
                 if (excess < 0) {
      
                    $('#remaining_stb').val(0);
                    $('#excess_stb').val(Math.abs(excess));
                }else{
                    $('#remaining_stb').val(excess);
                    $('#excess_stb').val(0);
                };

           };
            
        });

        
        var rank_id = $('#rank_id').val();
        var course = $('#training_course_id').val();
        var employee_id = $('#employee_id').val();
        var date_from = $('#date_from').val();
        var training_type = $('#training_type').val();

        get_budget(rank_id, course, employee_id, date_from);
    
        get_reallocation_from(training_type);
    }
    
    if (module.get_value('record_id') != -1  && module.get_value('view') == 'edit') {
        get_dates($('#record_id').val(), $('#date_from').val(), $('#date_to').val());
        get_areas_development($('#employee_id').val(), $("#areas_development").val());
    };

    if (status == 3) {

        var training_type = $('#training_type').val();
        if (module.get_value('view') == 'edit') {

            var invest = $('#investment').val();

            if(parseFloat(invest.replace(/,/g, '')) <= 0){
                $('#investment').val('');
            }

            get_reallocation_from(training_type);
            var employee_id = $('#employee_id').val();
            var date_from = $('#date_from').val();
           
           
           
        };

        
        if (parseFloat($('#stb').val()) > 0) {
           // $('#remaining_stb').val($('#stb').val());    
           $('#stb').attr('disabled', true);
            get_budget($('#rank_id').val(), $('#training_course_id').val(), employee_id, date_from);
        };

        $('#stb').live('keyup', function() {
            var supplemental  = $(this).val();
            var investment = $('#investment').val();
           $('#remaining_stb').val(parseFloat(supplemental.replace(/,/g, ''))); 
           $('#remaining_stb').attr('remaining',supplemental.replace(/,/g, '')); 
          

           if ($('#training_type').val() == 3 && investment != "") {
                var remaining_budget = $('#remaining_stb').attr('remaining');

                var excess = parseInt(remaining_budget) - parseInt(investment.replace(/,/g, '')); 
 

                if (excess < 0) {
      
                    $('#remaining_stb').val(0);
                    $('#excess_stb').val(Math.abs(excess));
                }else{
                    $('#remaining_stb').val(excess);
                    $('#excess_stb').val(0);
                };

           };
            
        });
        
        $('#remaining_itb, #excess_itb, #excess_ctb, #excess_stb, #remaining_ctb, #remaining_stb,  #remaining_allocated').attr('readonly', true);
        $('#remaining_itb').attr('remaining', $('#remaining_itb').val());
        $('#remaining_ctb').attr('remaining', $('#remaining_ctb').val());
        $('#remaining_stb').attr('remaining', $('#remaining_stb').val());

    };

    $('#areas_development').live('change', function(){
        var option = $('option:selected', this).attr('afd');
        var pd = $('option:selected', this).attr('pd');
        $('#competency').val(option);
        $('#idp_completion').val(pd);
    });

    $('#training_type').live('change', function() {
        get_reallocation_from($(this).val());  
    });


	$('#employee_id').live('change', function() {
		var emp_id = $(this).val();	
		get_employee_details(emp_id)
	});

    $('#training_course_id').live('change', function() {
		var rank_id = $('#rank_id').val();
		var course = $(this).val();
        var employee_id = $('#employee_id').val();
        var date_from = $('#date_from').val();
		get_budget(rank_id, course, employee_id, date_from);
	});

	$('.add_row').click(function() {
		var type = $(this).attr('type');
		add_item(type, 1);
	});

	$('.delete_row').live('click',function(){
       var elem = $(this);
       $(elem).parent().parent().parent().parent().remove();

    });


    $('.approve-single').live('click', function() {
        var record_id = $(this).attr('record_id')
        forApproval(record_id, 5);
    });

    $('.cancel-single').live('click', function() {
        var record_id = $(this).attr('record_id')
        forApproval(record_id, 6);
    });

    $('.cancel-application').live('click', function() {
        var record_id = $(this).attr('record_id')
        forApproval(record_id, 7);
    });

    $('#investment').live('keyup', function() {
        var training_type = $('#training_type').val();
        var remaining_budget = 0;
        var investment = $(this).val();
        var excess = 0;
        // console.log(training_type);
         $('input[name="budgeted"]').filter('[checked="checked"]').attr('checked',  false);   
         $('#allocated').val('');  

        if (training_type == 1) { //ITB
            
           remaining_budget = $('#remaining_itb').attr('remaining');
            if (investment != '') {
                           
                excess = parseInt(remaining_budget) - parseInt(investment.replace(/,/g, '')); 

                if (excess < 0) {
                    $('#remaining_itb').val(0);
                    $('#excess_itb').val(Math.abs(excess));
                }else{
                    $('#remaining_itb').val(excess);
                    $('#excess_itb').val(0);
                };
                $('#remaining_ctb').val($('#remaining_ctb').attr('remaining'));
                $('#excess_ctb').val(0);
                $('#excess_stb').val(0);
                $('#remaining_stb').val($('#remaining_stb').attr('remaining'));
            };
            
        }else if(training_type == 2){ //CTB
            remaining_budget = $('#remaining_ctb').attr('remaining');
            if (investment != '') {
                           
                excess = parseInt(remaining_budget) - parseInt(investment.replace(/,/g, '')); 

                if (excess < 0) {
                    $('#remaining_ctb').val(0);
                    $('#excess_ctb').val(Math.abs(excess));
                }else{
                    $('#remaining_ctb').val(excess);
                    $('#excess_ctb').val(0);
                };
                $('#remaining_itb').val($('#remaining_itb').attr('remaining'));
                $('#remaining_stb').val($('#remaining_stb').attr('remaining'));
                $('#excess_itb').val(0);
                $('#excess_stb').val(0);
            };

        }else{
             remaining_budget = $('#remaining_stb').attr('remaining');
             if (investment != '') {
                excess = parseInt(remaining_budget) - parseInt(investment.replace(/,/g, '')); 

                if (excess < 0) {
                    $('#remaining_stb').val(0);
                    $('#excess_stb').val(Math.abs(excess));
                }else{
                    $('#remaining_stb').val(excess);
                    $('#excess_stb').val(0);
                };
                $('#remaining_itb').val($('#remaining_itb').attr('remaining'));
                $('#remaining_ctb').val($('#remaining_ctb').attr('remaining'));
                $('#excess_ctb').val(0);
                $('#excess_itb').val(0);
            };

        };
    });
    
     $('input[name="budgeted"]').click(function() {
        var selected = $(this).val();
        var training_type = $('#training_type').val();
        var investment = $('#investment').val();
        var error = false;
        var allocate  = false;
        // if (selected == 1) {
            if (training_type == 1) { // itb
                var excess_itb = parseFloat($('#excess_itb').val());
                var remaining_ctb = parseFloat($('#remaining_ctb').attr('remaining'));
                var remaining_stb = parseFloat($('#remaining_stb').attr('remaining'));
                

                if (excess_itb > 0) {
                    if ((excess_itb < remaining_ctb) ) {
                        if (selected == 1) {
                            error = true;
                            allocate = true;    
                        }else if(selected == 2) {
                            error = false;
                            allocate = false;    
                        };
                        
                        
                    }else if((excess_itb < remaining_stb)){
                        if (selected == 1) {
                            error = true;
                            allocate = true;    
                        }else if(selected == 2) {
                            error = false;
                            allocate = false;    
                        }; 
                    }

                }else if((parseFloat($('#remaining_itb').val()) > excess_itb) || (parseFloat($('#remaining_itb').val()) == 0 && excess_itb == 0)){
                    error = true;
                    allocate = false;
                      
                }

            } else if(training_type == 2){ // ctb
                var excess_ctb = parseFloat($('#excess_ctb').val());
                var remaining_itb = parseFloat($('#remaining_itb').attr('remaining'));
                var remaining_ctb = parseFloat($('#remaining_ctb').attr('remaining'));
                var remaining_stb = parseFloat($('#remaining_stb').attr('remaining'));
                
                
                if (excess_ctb > 0) {
                    if (excess_ctb < remaining_itb) {
                        if (selected == 1) {
                            error = true;
                            allocate = true;    
                        }else if(selected == 2) {
                            error = false;
                            allocate = false;    
                        };
                    }else if(excess_ctb < remaining_stb){
                        if (selected == 1) {
                            error = true;
                            allocate = true;    
                        }else if(selected == 2) {
                            error = false;
                            allocate = false;    
                        };
                    };
                   // error = true;
                   // allocate = true;
                    
                }else if((parseFloat($('#remaining_ctb').val()) > excess_ctb) || (parseFloat($('#remaining_ctb').val()) == 0 && excess_ctb == 0)){
                    error = true;
                    allocate = false;
                }
                
            } else{
                var excess_stb = parseFloat($('#excess_stb').val());
                var remaining_itb = parseFloat($('#remaining_itb').attr('remaining'));
                var remaining_ctb = parseFloat($('#remaining_ctb').attr('remaining'));
               
                if ( excess_stb > 0) {
                   if (excess_stb < remaining_itb) {
                    if (selected == 1) {
                            error = true;
                            allocate = true;    
                        }else if(selected == 2) {
                            error = false;
                            allocate = false;    
                        };
                   }else if(excess_stb < remaining_ctb){
                        if (selected == 1) {
                            error = true;
                            allocate = true;    
                        }else if(selected == 2) {
                            error = false;
                            allocate = false;    
                        };
                   };
                   // error = true;
                   // allocate = true;

                }else if((parseFloat($('#remaining_stb').val()) > excess_stb) || (parseFloat($('#remaining_stb').val()) == 0 && excess_stb == 0)){
                    error = true;
                    allocate = false;
                }
            }

        // }

        if (error) {
            $('#message-container').html(message_growl('attention', 'You still have sufficient budget or Application should be considered for Re-allocation'));

            if (allocate) {
                $('input[name="budgeted"]').filter('[value="2"]').attr('checked', 'checked');    
            }else{
  
                $('input[name="budgeted"]').filter('[value="2"]').attr('checked',  false);    
            };
        };
     });

    $('#allocated').live('change', function() {
        $('input[name="budgeted"]').filter('[value="2"]').attr('checked', 'checked');
        var type = $(this).val();
        var training_type = $('#training_type').val();
        if (training_type == 1) { // itb
            var budget = 'itb';
            var reallocate = 'ctb';

        } else if(training_type == 2){ // ctb
            var budget = 'ctb';
            var reallocate = 'itb';
        } else{ //stb
            var budget = 'stb';
            var reallocate = 'itb';
        };

        var remaining = 0;
        if (type == '1') { // itb
            var excess = $('#excess_'+budget).val();
            var training_budget = $('#remaining_itb').attr('remaining');
            remaining = parseFloat(training_budget) - parseFloat(excess);
            
            if (remaining > 0) { 
                $('#remaining_itb').val(remaining);
                if (training_type == 2) { // ctb
                    $('#remaining_stb').val($('#remaining_stb').attr('remaining'));

                } else if(training_type == 3){ // stb
                     $('#remaining_ctb').val($('#remaining_ctb').attr('remaining'));
                } 
            }else{
                $('#message-container').html(message_growl('attention', 'Individual Training Budget is insufficient'));
                $('input[name="budgeted"]').filter('[value="2"]').attr('checked', false);
                $(this).val('');   
            }; 
        } else if(type == '2'){ // ctb
            
            var excess = $('#excess_'+budget).val();
            var training_budget = $('#remaining_ctb').attr('remaining')
            remaining = parseFloat(training_budget) - parseFloat(excess);
            if (remaining > 0) {
                $('#remaining_ctb').val(remaining)
                 
                if (training_type == 1) { // itb
                    $('#remaining_stb').val($('#remaining_stb').attr('remaining'));
                } else if(training_type == 3){ // stb
                    $('#remaining_itb').val($('#remaining_itb').attr('remaining'));
                }
            }else{
                $('#message-container').html(message_growl('attention', 'Common Training Budget is insufficient'));
                $('input[name="budgeted"]').filter('[value="2"]').attr('checked', false);
                $(this).val('');   
            }; 

        } else if(type == '3'){ //stb
            var excess = $('#excess_'+budget).val();
            var training_budget = $('#remaining_stb').attr('remaining');
            remaining = parseFloat(training_budget) - parseFloat(excess);
            
            if (remaining > 0) {
                remaining = parseFloat(training_budget) - parseFloat(excess);
                $('#remaining_stb').val(remaining);

                if (training_type == 1) { // itb
                    $('#remaining_ctb').val($('#remaining_ctb').attr('remaining'));
                } else if(training_type == 2){ // ctb
                     $('#remaining_itb').val($('#remaining_itb').attr('remaining'));
                } 
            }else{
                $('#message-container').html(message_growl('attention', 'Supplemental Budget is insufficient'));
                $('input[name="budgeted"]').filter('[value="2"]').attr('checked', false);
                $(this).val('');
            };
        }else if(type == 'combined'){
            var excess = $('#excess_'+budget).val();
            var budget_allocate = $('#remaining_'+reallocate).attr('remaining');
            var other_budget = $('#remaining_stb').attr('remaining');
            
            if (budget_allocate > 0) {
                if (parseFloat(excess) >= parseFloat(budget_allocate)) {
                    var allocate_excess = parseFloat(excess) - parseFloat(budget_allocate);
                  
                    if (allocate_excess >= 0) {
                        var other_allocated = parseFloat(other_budget) - parseFloat(allocate_excess);
                        remaining = other_allocated;
                        if (other_allocated >= 0) {
                            $('#remaining_stb').val(other_allocated);  
                            $('#remaining_'+reallocate).val(0);  
                            $('#excess_'+reallocate).val(allocate_excess);
                            
                        }else{
                            $('#message-container').html(message_growl('attention', 'Supplemental Budget is insufficient'));
                            $('input[name="budgeted"]').filter('[value="2"]').attr('checked', false);
                            $(this).val('');
                        };
                        
                    };
                }else{
                    $('#message-container').html(message_growl('attention', 'You still have sufficient budget or Application should be considered for Re-allocation'));
                    $('input[name="budgeted"]').filter('[value="2"]').attr('checked', false);
                    $(this).val('');
                };
            }else{
                $('#message-container').html(message_growl('attention', 'You still have sufficient budget'));
                $('input[name="budgeted"]').filter('[value="2"]').attr('checked', false);
                $(this).val('');
            };
        };

        if (remaining > 0) {
            $('#remaining_allocated').val(remaining)    
        };
        
    });
        

    $('input[name="date-temp-to"]').attr('disabled', false).attr('readonly', true);
    $('input[name="date-temp-from"]').attr('disabled', false).attr('readonly', true);
    $('input[name="date-temp-to"]').live('change', function() {
        
        if ($('#date_from').val() != "") {
            get_dates($('#record_id').val(), $('#date_from').val(), $(this).val());
            var rank_id = $('#rank_id').val();
            var course = $('#training_course_id').val();
            var employee_id = $('#employee_id').val();
            get_budget(rank_id, course, employee_id, $('#date_from').val());
        };

    });

    $('input[name="date-temp-from"]').live('change', function() {
        if ($('#date_to').val() != "") {
            get_dates($('#record_id').val(), $(this).val(), $('#date_to').val());   
             var rank_id = $('#rank_id').val();
            var course = $('#training_course_id').val();
            var employee_id = $('#employee_id').val();
            get_budget(rank_id, course, employee_id, $('#date_from').val()); 
        };
        
    });
});


function get_employee_details(employee_id) {
    get_areas_development(employee_id, 0);

	$.ajax({
        url: module.get_value('base_url') + module.get_value('module_link') + '/get_employee_details',
        data: 'employee_id=' + employee_id,
        type: 'post',
        beforeSend: function(){
            $.blockUI({
                message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>'
            });
       },
        success: function(data){
           	// $('#department_id, #division_id, #position_id, #rank_id').attr('disabled', true);

            $('#department_id').val(data.department);
            $('#division_id').val(data.division);
            $('#position_id').val(data.position);
            $('#rank_id').val(data.rank);
            // $('#allocated').html(data.allocate);

            if (user.get_value('post_control') != 1) {
            	$('label[for=employee_id]').parent().find('.select-input-wrap').hide();
            	$('label[for=employee_id]').parent().append('<div class="text-input-wrap"><input type="text" class=" input-text " readonly value="'+data.name+'" ></div>')
			}

           

            $.unblockUI();

            if (parseInt(data.idp) == 0 ) {
                $('#message-container').html(message_growl('error', 'You are not allowed to apply yet. Individual Development Planning not yet created'));
                setTimeout(function () {
                    go_to_previous_page( 'You are not allowed to apply yet. Individual Development Planning not yet created' );
                }, 2000);
            }



        }           
    });
}

function get_areas_development(employee_id, areas_development) {

    $.ajax({
        url: module.get_value('base_url') + module.get_value('module_link') + '/get_areas_development',
        data: 'employee_id=' + employee_id + '&areas_development=' + areas_development ,
        type: 'post',
        beforeSend: function(){
            $.blockUI({
                message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>'
            });
       },
        success: function(data){
            $("#areas_development").parent().html(data.competencies);

            // $('#competency').html(data.competencies);
            // $('#competency').trigger("liszt:updated");
            $.unblockUI();
            $("#areas_development").chosen();
        }           
    });
}

function get_budget(rank_id, course, employee_id, date_from) {
	$.ajax({
        url: module.get_value('base_url') + module.get_value('module_link') + '/get_budget',
        data: 'rank_id=' + rank_id + '&course=' + course + '&employee_id=' + employee_id + '&date=' + date_from + '&record_id='+module.get_value('record_id'),
        type: 'post',
        dataType: 'json',
        beforeSend: function(){
            // $.blockUI({
            //     message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>'
            // });
       },
        success: function(data){
         
            $('#remaining_itb').attr('remaining', data.remaining_it);
            $('#remaining_ctb').attr('remaining', data.remaining_ct);
            $('#remaining_stb').attr('remaining', data.remaining_others);
            $('#itb').attr('disabled', true);
            $('#ctb').attr('disabled', true);
            var stb_budget = $('#stb').val();
            if ($('#investment').val() == 0) {
                $('#itb').val(data.budget_it);
                $('#stb').val(data.budget_others);
                $('#ctb').val(data.budget_ct);
                
                $('#remaining_itb').val(data.remaining_it);
                $('#remaining_ctb').val(data.remaining_ct);
                $('#remaining_stb').val(data.remaining_others);
            }else{
                if ( module.get_value('view') == 'edit') {
                    $('#remaining_stb').attr('remaining',  parseInt(stb_budget.replace(/,/g, '')));    
                };
                
            } 

            $('#itb').attr('readonly', true);
        	$('#ctb').attr('readonly', true);
            
        	$('#training_provider').val(data.training_provider);

            if (user.get_value('post_control') != 1 && $('#status_id').val() != 3) {
                $('#investment').attr('disabled', true);
        	    $('#investment').val(0);
                $('#stb').attr('disabled', true);
            }
            
            if (parseFloat(data.budget_others) > 0) {
                $('#stb').attr('disabled', true);
            };
            // $.unblockUI();
            
        }           
    });
}

function add_item(type, id) {
	$.ajax({
        url: module.get_value('base_url') + module.get_value('module_link') + '/get_form',
        data: 'type=' + type,
        type: 'post',
        dataType: 'html',
        async: false,
        beforeSend: function(){
            // $.blockUI({
            //     message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Sending request, please wait...</div>'
            // });
       },
        success: function(data){
            $('#training-'+type).before(data);
            $.unblockUI();           
        }           
    });
}

function get_reallocation_from(type, id) {

    $.ajax({
        url: module.get_value('base_url') + module.get_value('module_link') + '/get_reallocation_from',
        data: 'type=' + type+ '&record_id=' + $('#record_id').val(),
        type: 'post',
        dataType: 'html',
        async: false,
        beforeSend: function(){
            // $.blockUI({
            //     message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Sending request, please wait...</div>'
            // });
       },
        success: function(data){
            $('#allocated').html(data);
            // $.unblockUI();           
        }           
    });
}


function validate_fg671() {
	return true;
}


function validate_fg674() {
	
    validate_mandatory('transfer[transfer][]', "Knowledge Transfer");
    validate_mandatory_array('objective[objective][]', "Objective");
    validate_mandatory('objective[rating][]', "Rating");
    validate_mandatory_array('action[plan][]', "Action Plan");
    
	return process_errors();



}

function validate_fg675() {
	validate_mandatory_array('action[plan][]', "Action Plan");
	return process_errors();
}

function validate_fg676() {
	validate_mandatory_array('transfer[transfer][]', "Knowledge Transfer");
	return process_errors();
}


function process_errors() {
    if(error.length > 0){
        var error_str = "Please correct the following errors:<br/><br/>";
        for(var i in error){
            if(i == 0) $('input[name="'+error[i][0] + '"]').focus(); //set focus on the first error
            error_str = error_str + (parseFloat(i)+1) +'. '+error[i][1]+" - "+error[i][2]+"<br/>";
        }
        $('#message-container').html(message_growl('error', error_str));

        //reset errors
        error = new Array();
        error_ctr = 0
        return false;
    }

    //no error occurred
    return true;    
}



function ajax_save( on_success, is_wizard , callback ){
    if( is_wizard == 1 ){
        var current = $('.current-wizard');
        var fg_id = current.attr('fg_id');

        if($('#investment').val() == '' ){
            add_error('investment', 'Investment', "This field is mandatory.");
        }
        
        var training_type = $('#training_type').val(); 
        var excess = 0;
        if (training_type == 1) { //ITB
            excess = $('#excess_itb').val();           
        }else if(training_type == 2){ //CTB
            excess = $('#excess_ctb').val();
        }else{
           excess = $('#excess_stb').val(); 
        };

        if(parseInt(excess) > 0){
                     
            if ($('input[name="budgeted"]:checked').length == 0) {
                // $('#message-container').html(message_growl('attention', 'Remaining Budget is insufficient : Is the Application Not Bugeted or for Re-allocation?'));  
                add_error('budgeted', 'Remaining Budget is insufficient', "Is the Application Not Bugeted or for Re-allocation?");         
            }
            // validate_mandatory('budgeted', 'Not Budgeted / Re-allocation:');  
        }
           
        if ($('input[name="budgeted"]:checked').val() == '2') {
            validate_mandatory('allocated', 'Re-allocation from'); 
        }else if($('input[name="budgeted"]:checked').val() == '1'){
            validate_mandatory('remarks', 'Remarks'); 
        };
           

        var ok_to_save = eval('validate_fg'+fg_id+'()')
    }
    else{
       
        ok_to_save = validate_form();
    }

    if( ok_to_save ) { 
        $('#department_id, #division_id, #position_id, #rank_id, #itb, #ctb, #stb, #investment, #competency').attr('disabled', false);     
        $('#record-form').find('.chzn-done').each(function (index, elem) {
            if (elem.multiple) {
                if ($(elem).attr('name') != $(elem).attr('id') + '[]') {
                    $(elem).attr('name', $(elem).attr('name') + '[]');
                }
                
                var values = new Array();
                for(var i=0; i< elem.options.length; i++) {
                    if(elem.options[i].selected == true) {
                        values[values.length] = elem.options[i].value;
                    }
                }
                $(elem).val(values);
            }
        });

        var data = $('#record-form').serialize();
        var saveUrl = module.get_value('base_url')+module.get_value('module_link')+"/ajax_save"     

        $.ajax({
            url: saveUrl,
            type:"POST",
            data: data,
            dataType: "json",
            /**async: false, // Removed because loading box is not displayed when set to false **/
            beforeSend: function(){
                    show_saving_blockui();
            },
            success: function(data){
                if(  data.record_id != null ){
                    //check if new record, update record_id
                    if($('#record_id').val() == -1 && data.record_id != ""){
                        $('#record_id').val(data.record_id);
                        $('#record_id').trigger('change');
                        if( is_wizard == 1 ) window.location = module.get_value('base_url') + module.get_value('module_link') + '/edit/' + data.record_id;
                    }
                    else{
                        $('#record_id').val( data.record_id );
                    }
                }

                if( data.msg_type != "error"){                  
                    switch( on_success ){
                        case 'back':
                            go_to_previous_page( data.msg );
                            break;
                        case 'email':                           
                            if (data.record_id > 0 && data.record_id != '') {
                                // Ajax request to send email.                    
                                $.ajax({
                                    url: module.get_value('base_url') + module.get_value('module_link') + '/send_email',
                                    data: 'record_id=' + data.record_id,
                                    dataType: 'json',
                                    type: 'post',
                                    async: false,
                                    beforeSend: function(){
                                            show_saving_blockui();
                                        },                              
                                    success: function () {
                                    }
                                });
                            }                           
                            //custom ajax save callback
                            if (typeof(callback) == typeof(Function)) callback( data );
                             window.location = module.get_value('base_url') + module.get_value('module_link') + '/detail/' + data.record_id;
                        default:
                            if (typeof data.page_refresh != 'undefined' && data.page_refresh == "true"){
                                    window.location = module.get_value('base_url') + module.get_value('module_link') + '/edit/' + data.record_id;
                            }
                            else{
                                //generic ajax save callback
                                if(typeof window.ajax_save_callback == 'function') ajax_save_callback();
                                //custom ajax save callback
                                if (typeof(callback) == typeof(Function)) callback( data );
                                $.unblockUI({ onUnblock: function() { message_growl(data.msg_type, data.msg) } });
                                 // go_to_previous_page( data.msg );
                            }
                            break;
                    }   
                }
                else{
                    $.unblockUI({ onUnblock: function() { message_growl(data.msg_type, data.msg) } });
                }
            }
        });
    }
    else{
        return false;
    }
    return true;
}

function forApproval(record_id, status) {
    
    var width = $(window).width()*.3;
    remarks_boxy = new Boxy.confirm(
        '<div id="boxyhtml" style="width:'+width+'px"><textarea style="height:100px;width:340px;" name="remarks_approver"></textarea></div>',
        function () {
            // url = module.get_value('base_url') + module.get_value('module_link') + '/' + action + '_request/';
            remarks = $('textarea[name="remarks_approver"]').val();
            change_status(record_id, status, remarks);
        },
        {
            title: 'Remarks',
            draggable: false,
            modal: true,
            center: true,
            unloadOnHide: true,
            beforeUnload: function (){
                $('.tipsy').remove();
            }
        });
    // boxyHeight(remarks_boxy, '#boxyhtml');  

}

function get_dates(record_id, date_from, date_to) 
{
  $.ajax({
        url: module.get_value('base_url') + module.get_value('module_link') + '/get_dates',
        data: 'record_id=' + record_id + '&date_from=' + date_from + '&date_to=' + date_to,
        type: 'post',
        dataType: 'json',
        beforeSend: function(){
            $.blockUI({
                message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Sending request, please wait...</div>'
            });
        },
        success: function (data) {
            $.unblockUI();
            
            
            $('#dates-container').html(data.options);
            
        }
    })
}


function change_status(record_id, status, remarks)
{
    $.ajax({
        url: module.get_value('base_url') + module.get_value('module_link') + '/change_status',
        data: 'record_id=' + record_id + '&status=' + status + '&remarks_approver=' + remarks,
        type: 'post',
        dataType: 'json',
        beforeSend: function(){
              show_saving_blockui();
        },
        success: function (data) {


             $.unblockUI({ onUnblock: function() { $('#message-container').html(message_growl(data.msg_type, data.msg)) } });
             
            // $('#message-container').html(message_growl(data.msg_type, data.msg));

            if (module.get_value('view') == 'index') {
                $('#jqgridcontainer').jqGrid().trigger("reloadGrid");
            }else{
                 window.location = module.get_value('base_url') + module.get_value('module_link') + '/detail/' + data.record_id;
            };
        }
    })
}
var initial_load = true;

function init_filter_tabs(){
    $('ul#grid-filter li').click(function(){
        $('ul#grid-filter li').each(function(){ $(this).removeClass('active') });
        $(this).addClass('active');
        $('#filter').val( $(this).attr('filter') );

        if( $(this).attr('filter') == 'for_approval' ){
            $('.status-buttons').parent().show();
        }
        else{
            $('.status-buttons').parent().hide();
        }

        filter_grid( 'jqgridcontainer', $(this).attr('filter') );
    });
    
}

function filter_grid( jqgridcontainer, filter )
{
    var searchfield;
    var searchop;
    var searchstring = $('.search-'+ jqgridcontainer ).val() != "Search..." ? $('.search-'+ jqgridcontainer ).val() : "";
    
    if( $("form.search-options-"+ jqgridcontainer).hasClass("hidden") ){
        searchfield = "all";
        searchop = "";
    }else{
        searchfield = $('#searchfield-'+jqgridcontainer).val();
        searchop = $('#searchop-'+jqgridcontainer).val()    
    }

    //search history
    $('#prev_search_str').val(searchstring);
    $('#prev_search_field').val(searchfield);
    $('#prev_search_option').val(searchop);
    $('#prev_search_page').val( $("#"+jqgridcontainer).jqGrid("getGridParam", "page") );

    $("#"+jqgridcontainer).jqGrid('setGridParam', 
    {
        search: true,
        postData: {
            searchField: searchfield, 
            searchOper: searchop, 
            searchString: searchstring,
            filter: filter
        },  
    }).trigger("reloadGrid");   
}