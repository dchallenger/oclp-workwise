$(document).ready(function () {

    if($('#company_qualified-yes').is(':checked')){
        $('label[for="company_qualified_position_department"]').parent().show()
    }
    else{
        $('label[for="company_qualified_position_department"]').parent().hide()
    }

    //display on / off if external was click
    if ($('#with_amp-no').is(':checked')){
        $('label[for="with_amp_additional_headcount"]').parent().show();
    }
    else{
        $('label[for="with_amp_additional_headcount"]').parent().hide(); 
    }

    //display on / off if external was click
    if ($.inArray('2',$('#multiselect-recruitment_manpower_rc_id').val()) == 0){
        $('label[for="recruitment_manpower_rc_search_type"]').parent().show();
    }
    else{
        $('label[for="recruitment_manpower_rc_search_type"]').parent().hide();
        $('label[for="recruitment_manpower_online_search"]').parent().hide();   
    }

    //display on / off if online search was click
    if ($('input[name="recruitment_manpower_rc_search_type"]:checked').val() == 2){
        $('label[for="recruitment_manpower_online_search"]').parent().show();
    }
    else{
        $('label[for="recruitment_manpower_online_search"]').parent().hide();
    }

    $('a.approve-single').live('click', function () {      
      
        change_request_status($(this).parent().parent().parent().attr("id"),
	    'approve',
            page_refresh
        );        
    });
    
    $('a.cancel-single').live('click', function () {
        change_request_status($(this).parent().parent().parent().attr("id"),
	    'decline',
            page_refresh
        );        	    
    }); 

    $('a.mark-reviewed').live('click', function () {
        change_request_status($(this).parent().parent().parent().attr("id"),
        'review',
            page_refresh
        );     
        
    }); 

    $('a.for-evaluation').live('click', function () {
        change_request_status($(this).parent().parent().parent().attr("id"),
        'evaluation',
            page_refresh
        );     
        
    }); 

    if (module.get_value('view') != 'edit') {

        $('label[for="starting_salary_to"]').parent().hide();

        var start_from = $('label[for="starting_salary"]').next().html();
        var start_to = $('label[for="starting_salary_to"]').next().html();
        
        if (start_from !== null && start_to !== null && start_to.trim() !== '&nbsp;') {
            $('label[for="starting_salary"]').next().text(start_from.trim()+' to ' +start_to.trim()); 
        };
        

        $('.icon-16-send-email').die('click');
        $('.icon-16-send-email').live('click', function () {
            var id = $(this).parent().parent().parent().attr("id");
            Boxy.ask("Send request to approverss?", ["Yes", "Cancel"],
                function( choice ) {
                    if(choice == "Yes"){
                        $.ajax({
                            url: module.get_value('base_url') + module.get_value('module_link') + '/send_email',
                            data: 'record_id=' + id,
                            type: 'post',
                            beforeSend: function(){
                                $('.jqgfirstrow').removeClass('ui-state-highlight');
                                $.blockUI({
                                    message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Sending request, please wait...</div>'
                                });
                            },
                            success: function(data){
                                $.unblockUI();
                                $('#message-container').html(message_growl(data.msg_type, data.msg));
                                page_refresh();
                            }           
                        });
                    }
                },
                {
                    title: "Send Request"
                }
            );
        });      
    }

    if (module.get_value('view') == 'detail') {
        $.ajax({
                url: module.get_value('base_url') + module.get_value('module_link') + '/get_manpower_info',
                data: 'record_id=' + $('#record_id').val(),
                type: 'post',
                dataType: 'json',
                success: function(response){
                    $('label[for="status"]').next().html(response.status);
                }
            });    
    }

    if (module.get_value('view') == 'edit') {

        $('#turn_around_time').keyup(function(){
            if ($(this).val() != ''){

                $.ajax({
                    url: module.get_value('base_url') + module.get_value('module_link') + '/get_weekends',
                    data: 'tat=' + $(this).val() + '&start_date=' + $('#date_from').val()  + '&is_ajax=' + 1,
                    type: 'post',
                    dataType: 'json',
                    success: function(response){
                       setTimeout(function () {
                            $('#date-temp-to').datepicker('setDate', response.end_date);    
                        }, 100);
                    }
                });  
            }
        });



        $('#date-temp-from').change(function(){
            if ($(this).val() != ''){
                var duration = parseInt($('#turn_around_time').val());
                // var date1 = new Date($(this).val());
                // var month;

                // // "+7" = + 6 months    
                // if( (date1.getMonth() + 1 + duration) > 12 ){ 
                //     date1.setMonth( (date1.getMonth() + 1 + duration)-12); 
                //     date1.setFullYear( date1.getFullYear() + 1);
                //     month = date1.getMonth();
                // } else {
                //     date1.setMonth(date1.getMonth() + 1 + duration);
                //     month = date1.getMonth();
                // }
                
                // // Timeout is needed calendar does not update when two instances are open.
                

                $.ajax({
                    url: module.get_value('base_url') + module.get_value('module_link') + '/get_weekends',
                    data: 'tat=' + duration + '&start_date=' + $(this).val()  + '&is_ajax=' + 1,
                    type: 'post',
                    dataType: 'json',
                    success: function(response){
                       setTimeout(function () {
                            $('#date-temp-to').datepicker('setDate', response.end_date);    
                        }, 100);
                    }
                });  

            }
        });

        $('#starting_salary').attr('style', 'width:35% !important').addClass('input-small').parent().append('<span class="salary_to "> to </span>');
        $('#starting_salary_to').insertAfter('.salary_to');
        // $('#starting_salary').attr('style', 'width:35% !important').addClass('input-small').parent().append('<span class="to"> to </span> <input type="text" class="input-small input-text text-right" style="width:35% !important" id="starting_salary_to" name="starting_salary_to" value="">');
        $('#starting_salary_to').attr('style', 'width:35% !important').addClass('input-small');
        $('label[for="starting_salary_to"]').parent().hide();
        // .parent().append('<span class="to"> to </span> <input type="text" class="input-small input-text text-right" style="width:35% !important" id="starting_salary_to" name="starting_salary_to">');

        $('#starting_salary_to').live('keyup', maskFloat);

        var parent = $('label[for="status"]').parent().parent();

        $(parent).find('label[for="status"]').next().removeClass('text-input-wrap').addClass('select-input-wrap');
        $(parent).find('label[for="status"]').next().html('<select id="status_hr" name="status"><option value="">Select...</option></select>');

        var obj = {
            "Draft": "Draft",
            "For Approval": "For Approval",
            "Approved": "Approved",
            "Declined": "Declined",
            "In-Process": "In-Process",
            "Cancelled": "Cancelled",
            "Closed": "Closed",
            "For Evaluation": "For Evaluation",
            "For HR Review": "For HR Review"
        };

        $.each(obj, function(key, value)
        {   
            $('#status_hr').append($("<option></option>").attr("value",key).text(value)); 
        });        

        if ($('#record_id').val() != '-1'){
            $.ajax({
                url: module.get_value('base_url') + module.get_value('module_link') + '/get_manpower_info',
                data: 'record_id=' + $('#record_id').val(),
                type: 'post',
                dataType: 'json',
                success: function(response){
                    $('#status_hr').val(response.status);

                    var mrf_status = $("#status_hr").val();
                    if (mrf_status != "Approved") {
                        // alert(mrf_status)
                        $('label[for="contract_received"]').parent().remove();
                        $('label[for="date"]').parent().remove();
                        $('label[for="status"]').parent().hide();
                        $("#end_fill_rate").parent().parent().remove();
                        $("#turn_around_time").parent().parent().remove();
                    } 
                }
            });
        }

        if ($('#record_id').val() == '-1'){
            $('#category_value_id option').remove();
            
        }

        
        $("#division_id").live('change', function () {
            var division_id = $(this).val();
            var type = 'division';

            // get_head(division_id, type);
            // $.ajax({
            //     url: module.get_value('base_url') + module.get_value('module_link') + '/get_department_list',
            //     data: 'division_id=' + division_id,
            //     dataType: 'html',
            //     type: 'post',
            //     async: false,
            //     beforeSend: function(){
                
            //     },                              
            //     success: function ( response ) {
            //         $('#department_id').parent().empty();
            //         $('label[for="department_id"]').parent().find('.select-input-wrap').html(response);
            //         $('#department_id').chosen();

            //     }
            // }); 

        });

        $('label[for="existing_position_type"]').parent().hide();
        $('label[for="new_job_justification"]').parent().hide();


         $('input[name="budgetted_new_position"]').click(function() {
            if($(this).is(':checked')){
                $('input[name="budgetted_ml_to"]').attr('disabled', true);
                $('input[name="budgetted_original_req"]').attr('disabled', true);
            }
            else{
                $('input[name="budgetted_ml_to"]').attr('disabled', false);
                $('input[name="budgetted_original_req"]').attr('disabled', false);
            }

           
         });
            
        $('input[name="budgetted_ml_to"]').click(function() {
            if($(this).is(':checked')){
               $('input[name="budgetted_new_position"]').attr('disabled', true);
                $('input[name="budgetted_original_req"]').attr('disabled', true);
            }
            else{
                $('input[name="budgetted_new_position"]').attr('disabled', false);
                $('input[name="budgetted_original_req"]').attr('disabled', false);
            }
        });
            
        $('input[name="budgetted_original_req"]').click(function() {
            if($(this).is(':checked')){
                $('input[name="budgetted_new_position"]').attr('disabled', true);
                $('input[name="budgetted_ml_to"]').attr('disabled', true);
            }
            else{
                $('input[name="budgetted_new_position"]').attr('disabled', false);
                $('input[name="budgetted_ml_to"]').attr('disabled', false);
            }

            
         });

        if( $('label[for="reason_for_request"]').parent().find('input[value="1"]').attr('checked') == 'checked' ){
            
            // $('label[for="not_budgetted_replacement_name"]').parent().hide();
            $('input[name="budgetted_new_position"]').parent().show();
            $('input[name="budgetted_ml_to"]').parent().show();
            $('input[name="budgetted_original_req"]').parent().show();

        }
        else{

            $('label[for="not_budgetted_replacement_name"]').parent().show();
            $('input[name="budgetted_new_position"]').parent().hide();
            $('input[name="budgetted_ml_to"]').parent().hide();
            $('input[name="budgetted_original_req"]').parent().hide();

        }

        $('input[name="reason_for_request"]').live('click',function(){

            if( $('label[for="reason_for_request"]').parent().find('input[value="1"]').attr('checked') == 'checked' ){

                // $('label[for="not_budgetted_replacement_name"]').parent().hide();
                $('input[name="budgetted_new_position"]').parent().show();
                $('input[name="budgetted_ml_to"]').parent().show();
                $('input[name="budgetted_original_req"]').parent().show();

            }
            else{

                $('label[for="not_budgetted_replacement_name"]').parent().show();
                $('input[name="budgetted_new_position"]').parent().hide();
                $('input[name="budgetted_ml_to"]').parent().hide();
                $('input[name="budgetted_original_req"]').parent().hide();

            }
            
            
        });

        $('label[for="existing_position_type"]').parent().hide();
        $('label[for="new_job_justification"]').parent().hide();

        $('input[name="company_qualified"]').live('change',function(){
            if ($(this).val() == 1){
                $('label[for="company_qualified_position_department"]').parent().show()
            }
            else{
                $('label[for="company_qualified_position_department"]').parent().hide()
            }
        });

        $('input[name="with_amp"]').live('change',function(){
            if ($(this).val() == 0){
                $('label[for="with_amp_additional_headcount"]').parent().show();
            }
            else{
                $('label[for="with_amp_additional_headcount"]').parent().hide();   
            }
        });

        $('input[name="recruitment_manpower_rc_search_type"]').live('change',function(){
            if ($(this).val() == 2){
                $('label[for="recruitment_manpower_online_search"]').parent().show();
            }
            else{
                $('label[for="recruitment_manpower_online_search"]').parent().hide();   
            }
        });

        $("#multiselect-recruitment_manpower_rc_id").bind("multiselectclose", function(event, ui){
            var selected = $(this).val();
            if ($.inArray('2',selected) == 0){
                $('label[for="recruitment_manpower_rc_search_type"]').parent().show();
            }
            else{
                $('label[for="recruitment_manpower_rc_search_type"]').parent().hide();
                $('label[for="recruitment_manpower_online_search"]').parent().hide();   
            }
        });
        $('label[for="date_served"]').parent().hide();
        //$('label[for="with_amp"]').parent().hide();

        //$('label[for="reason_for_request"]').parent().children(':eq(4)').after('<label class="label-desc gray" for="reason_for_request">New Position:<span class="red font-large">*</span></label>')
        //$('label[for="reason_for_request"]').parent().children(':eq(9)').after('<label class="label-desc gray" for="reason_for_request">Justification:<span class="red font-large">*</span></label>')

        $('#position_id,#number_required,#date_needed-temp').live('change', function(){
            var position_id = $('#position_id').val();
            var number_required = $('#number_required').val();
            var date_needed = $('#date_needed').val();
            var record_id = $('#record_id').val();
            if (position_id != '' && number_required != '' && date_needed != ''){
                $.ajax({
                    url:module.get_value('base_url') + module.get_value('module_link') + '/get_annual_manpower_planning',
                    data: 'position_id=' + position_id + '&number_required=' + number_required + '&date_needed=' + date_needed + '&record_id=' + record_id,
                    type: 'post',
                    dataType: 'json',
                    success: function(response) {
                        var to_confirm = false;
                        var to_confirm_type = 0;
                        var question = "";
                        if (response){
                            if (parseFloat(response.total_number_required_manpower) > parseFloat(response.total)){
                                to_confirm = true;
                                to_confirm_type = 1;
                                question = "Requested position is greater than the requested personnel in Annual Manpoower Planning";
                            }
                        }
                        else{
                            to_confirm = true;
                            to_confirm_type = 2;
                            question = "Requested position is not included in the Annual Manpower Planning";
                            $('#record-form').append('<input id="with_amp" type="hidden" value="1" name="with_amp">');
                        }

                        if (to_confirm){
                            Boxy.ask(question + ", Do you want to continue?", ["Yes", "No"],
                                function( choice ) {
                                    if(choice == "Yes"){
                                        $.ajax({
                                            url: module.get_value('base_url') + module.get_value('module_link') + '/send_email_requisitioning',
                                            data: 'record_id=' + record_id + '&division_id=' + $('#division_id').val(),
                                            type: 'post',
                                            beforeSend: function(){
                                                $.blockUI({
                                                    message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Sending request, please wait...</div>'
                                                });
                                            },
                                            success: function(data){
                                               $.unblockUI();
                                                // $('#message-container').html(message_growl(data.msg_type, data.msg));
                                                // page_refresh();
                                            }           
                                        });
                                    }
                                    else{
                                        if (to_confirm_type == 2){
                                            // $('#position_id').val('');
                                            // $('#position_id').trigger("liszt:updated");
                                        }
                                        else if (to_confirm_type == 1){
                                            // $('#number_required').val('');
                                        }
                                        //window.location = module.get_value('base_url') + module.get_value('module_link');
                                    }
                                },
                                {
                                    title: "Send Request"
                                }
                            );                            
                        }
                    }            
                }) 
            }
        });
    }
});


function for_evaluation() {
    record_id = $('#record_id').val();
    change_request_status(record_id, 'evaluation', function () { window.location = module.get_value('base_url') + module.get_value('module_link'); });
}

function mark_as_review() {
    record_id = $('#record_id').val();
    change_request_status(record_id, 'review', function () { window.location = module.get_value('base_url') + module.get_value('module_link'); });
}

function cancel() {
    record_id = $('#record_id').val();
    change_request_status(record_id, 'decline', function () { window.location = module.get_value('base_url') + module.get_value('module_link'); });
}

function approve() {
    record_id = $('#record_id').val();
    change_request_status(record_id, 'approve', function () { window.location = module.get_value('base_url') + module.get_value('module_link'); });
}

function decline() {
    record_id = $('#record_id').val();
    change_request_status(record_id, 'decline', function () { window.location = module.get_value('base_url') + module.get_value('module_link'); });
}

function manpower_settings() {
    var data = "record_id=1&module_id=32&sequence=1";
    showQuickEditForm( module.get_value('base_url') + "recruitment/manpower_settings/quick_edit", data );
}

function get_tat(record_id) {
    var url = module.get_value('base_url') + module.get_value('module_link') + '/get_tat';
    $.ajax({
        url: url,
        data: 'record_id=' + record_id ,
        type: 'post',
        dataType: 'json',
        beforeSend: function() {
            $.blockUI({message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>'});  
        },
        success: function(response) {
            $.unblockUI();
            // var width = $(window).width()*.5;
            var html = '<table class="boxyTable" style="text-align:center;width:400px;"><tr><td>'+response.html+' day(s)</td></tr></table>'
         
            quickedit_boxy = new Boxy(html,
                {
                    title: 'Number of Days',
                    draggable: false,
                    modal: true,
                    center: true,
                    unloadOnHide: true,
                    afterShow: function(){ $.unblockUI(); },
                    beforeUnload: function(){ $('.tipsy').remove(); }
                    
                }); 
                boxyHeight(quickedit_boxy, '#boxyhtml');

        }
    });
}


function change_request_status(record_id, action, callback) {


    if( action == "review" ){

        Boxy.confirm("Are you sure you want to mark this as reviewed?", function() {

            url = module.get_value('base_url') + module.get_value('module_link') + '/' + action + '_request/';
            $.ajax({
                url: url,
                data: 'record_id=' + record_id ,
                type: 'post',
                dataType: 'json',
                success: function(response) {
                    message_growl(response.type, response.message);
                    
                    if (typeof(callback) == typeof(Function))
                        callback();
                }
            });

        }, {title: 'Mark as Reviewed'});

    }
    else{

        var width = $(window).width()*.3;
        remarks_boxy = new Boxy.confirm(
            '<div id="boxyhtml" style="width:'+width+'px"><textarea style="height:100px;width:340px;" name="remarks"></textarea></div>',
            function () {
                url = module.get_value('base_url') + module.get_value('module_link') + '/' + action + '_request/';
                remarks = $('textarea[name="remarks"]').val()
                $.ajax({
                    url: url,
                    data: 'record_id=' + record_id + '&remarks=' + remarks,
                    type: 'post',
                    dataType: 'json',
                    success: function(response) {
                        message_growl(response.type, response.message);
                        
                        if (typeof(callback) == typeof(Function))
                            callback();
                    }
                });
            },
            {
                title: 'Additional remarks',
                draggable: false,
                modal: true,
                center: true,
                unloadOnHide: true,
                beforeUnload: function (){
                    $('.tipsy').remove();
                }
            });
        boxyHeight(remarks_boxy, '#boxyhtml');	

    }
}

if (typeof(showQuickEditForm) != typeof(Function))
{
    function showQuickEditForm( module_url, data)
    {
        $.ajax({
            url: module_url,
            type:"POST",
            data: data,
            dataType: "json",
            beforeSend: function(){
                $.blockUI({
                    message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>'
                });  		
            },
            success: function(data){
                $.unblockUI();
                if(data.msg != "") $('#message-container').html(message_growl(data.msg_type, data.msg));
                if(data.quickedit_form != ""){
                    var width = $(window).width()*.7;
                    quickedit_boxy = new Boxy('<div id="boxyhtml" style="width:'+width+'px">'+ data.quickedit_form +'</div>',
                    {
                        title: 'Quick Add/Edit',
                        draggable: false,
                        modal: true,
                        center: true,
                        unloadOnHide: true,
                        beforeUnload: function (){
                            $('.tipsy').remove();
                        }
                    });
                    boxyHeight(quickedit_boxy, '#boxyhtml');
                }
            }
        });
    }
}