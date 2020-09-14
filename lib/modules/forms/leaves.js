var advance_sl = get_config( 'advance_sl' );
advance_sl = advance_sl == 1;

$(document).ready(function () {
    if( module.get_value('module_link') != "employee/dtr" ) bindMe();
});

function bindMe(){ 
    $('#el_prev_balance_header').html('');
    $('#mpl_prev_balance_header').html('');
    $('#bl_prev_balance_header').html('');
    $('#el_prev_balance').html('');
    $('#mpl_prev_balance').html('');
    $('#bl_prev_balance').html('');

/*    $('#el_prev_balance_header').html('');
    $('#el_prev_balance').hide();
    $('#mpl_prev_balance_header').html('');
    $('#mpl_prev_balance').hide();
    $('#bl_prev_balance_header').html('');
    $('#bl_prev_balance').hide();*/
    
    $('.icon-16-send-email').die('click');
    
    if(module.get_value('view') == "detail")
    {
        $.ajax({
            url: module.get_value('base_url') + module.get_value('module_link') + '/get_leave_balance',
            data: 'record_id=' + $('#record_id').val(),
            type: 'post',
            dataType: 'json',
            success: function (balance) {

                if(balance.show_carried == 1)
                {
                    ( balance.vl == 'NaN' ) ? $('#vl').empty() : $('#vl').empty().html(balance.vl) ;
                    ( balance.sl == 'NaN' ) ? $('#sl').empty() : $('#sl').empty().html(balance.sl) ;
                    ( balance.el == 'NaN' ) ? $('#el').empty() : $('#el').empty().html(balance.el) ;
                    ( balance.mpl == 'NaN' ) ? $('#mpl').empty() : $('#mpl').empty().html(balance.mpl) ;
                    ( balance.bl == 'NaN' ) ? $('#bl').empty() : $('#bl').empty().html(balance.bl) ;

                    ( balance.vl_used == 'NaN' ||  balance.vl_used == 0 ) ? $('#vl_used').empty() :  $('#vl_used').empty().html(balance.vl_used) ;
                    ( balance.sl_used == 'NaN' || balance.sl_used == 0 ) ? $('#sl_used').empty() :  $('#sl_used').empty().html(balance.sl_used) ;
                    ( balance.el_used == 'NaN' || balance.el_used == 0) ? $('#el_used').empty() :  $('#el_used').empty().html(balance.el_used) ;
                    ( balance.mpl_used == 'NaN' || balance.mpl_used == 0) ? $('#mpl_used').empty() :  $('#mpl_used').empty().html(balance.mpl_used) ;
                    ( balance.bl_used == 'NaN' ||  balance.bl_used == 0) ? $('#bl_used').empty() :  $('#bl_used').empty().html(balance.bl_used) ;             

                    ( (parseFloat(balance.vl) - parseFloat(balance.vl_used)).toFixed(2) == 'NaN' ) ? $('#vl_balance').empty() :  $('#vl_balance').empty().html((parseFloat(balance.vl) - parseFloat(balance.vl_used)+0.00).toFixed(2)) ;
                    ( (parseFloat(balance.sl) - parseFloat(balance.sl_used)).toFixed(2) == 'NaN' ) ? $('#sl_balance').empty() :  $('#sl_balance').empty().html((parseFloat(balance.sl) - parseFloat(balance.sl_used)+0.00).toFixed(2)) ;
                    ( (parseFloat(balance.el) - parseFloat(balance.el_used)).toFixed(2) == 'NaN' ) ? $('#el_balance').empty() :  $('#el_balance').empty().html((parseFloat(balance.el) - parseFloat(balance.el_used)+0.00).toFixed(2)) ;
                    ( (parseFloat(balance.mpl) - parseFloat(balance.mpl_used)).toFixed(2) == 'NaN' ) ? $('#mpl_balance').empty() :$('#mpl_balance').empty().html((parseFloat(balance.mpl) - parseFloat(balance.mpl_used)+0.00).toFixed(2)) ;
                    ( (parseFloat(balance.bl) - parseFloat(balance.bl_used)).toFixed(2) == 'NaN' ) ? $('#bl_balance').empty() :  $('#bl_balance').empty().html((parseFloat(balance.bl) - parseFloat(balance.bl_used)+0.00).toFixed(2)) ;
                } 

                if(balance.show_carried == 2)
                {
                    var total_vl_balance = parseFloat(balance.vl) + parseFloat(balance.carried_vl);
                    var total_sl_balance = parseFloat(balance.sl) + parseFloat(balance.carried_sl);                    

                    ( balance.vl == 'NaN' ) ? $('#vl').html('0.00') : $('#vl').empty().html(balance.vl) ;
                    ( balance.sl == 'NaN' ) ? $('#sl').html('0.00') : $('#sl').empty().html(balance.sl) ;
                    ( balance.el == 'NaN' ) ? $('#el').html('0.00') : $('#el').empty().html(balance.el) ;
                    ( balance.mpl == 'NaN' ) ? $('#mpl').html('0.00') : $('#mpl').empty().html(balance.mpl) ;
                    ( balance.bl == 'NaN' ) ? $('#bl').html('0.00') : $('#bl').empty().html(balance.bl) ;

                    ( balance.vl_used == 'NaN' || !balance.vl_used) ? $('#vl_used').html('0.00') : $('#vl_used').empty().html(balance.vl_used) ;
                    ( balance.carried_vl == 'NaN' || !balance.carried_vl) ? $('#vl_prev_balance').html('0.00') : $('#vl_prev_balance').empty().html(balance.carried_vl) ;
                    ( total_vl_balance == 0) ? $('#vl_balance').html('0.00') : $('#vl_balance').empty().html(parseFloat(total_vl_balance - balance.vl_used).toFixed(2)) ;

                    ( balance.sl_used == 'NaN' || !balance.sl_used) ? $('#sl_used').html('0.00') : $('#sl_used').empty().html(balance.sl_used) ;
                    ( balance.carried_sl == 'NaN' || !balance.carried_sl) ? $('#sl_prev_balance').html('0.00') : $('#sl_prev_balance').empty().html(balance.carried_sl) ;
                    ( total_sl_balance == 0) ? $('#sl_balance').html('0.00') : $('#sl_balance').empty().html(parseFloat(total_sl_balance - balance.sl_used).toFixed(2)) ;

                    ( balance.el_used == 'NaN' || balance.el_used == 0 || !balance.el_used) ? $('#el_used').html('0.00') :  $('#el_used').empty().html(balance.el_used) ;
                    ( balance.mpl_used == 'NaN' || balance.mpl_used == 0 || !balance.mpl_used) ? $('#mpl_used').html('0.00') :  $('#mpl_used').empty().html(balance.mpl_used) ;
                    ( balance.bl_used == 'NaN' ||  balance.bl_used == 0 || !balance.bl_used) ? $('#bl_used').html('0.00') :  $('#bl_used').empty().html(balance.bl_used) ;             

                    ( balance.carried_bl == 'NaN' || !balance.carried_bl) ? $('#bl_prev_balance').html('0.00') : $('#bl_prev_balance').empty().html(balance.carried_bl) ;

                    ( (parseFloat(balance.el) - parseFloat(balance.el_used)).toFixed(2) == 'NaN' ) ? $('#el_balance').html('0.00') :  $('#el_balance').empty().html((parseFloat(balance.el) - parseFloat(balance.el_used)+0.00).toFixed(2)) ;
                    ( (parseFloat(balance.mpl) - parseFloat(balance.mpl_used)).toFixed(2) == 'NaN' ) ? $('#mpl_balance').html('0.00') :$('#mpl_balance').empty().html((parseFloat(balance.mpl) - parseFloat(balance.mpl_used)+0.00).toFixed(2)) ;
                    ( (parseFloat(balance.bl) - parseFloat(balance.bl_used)).toFixed(2) == 'NaN' ) ? $('#bl_balance').html('0.00') :  $('#bl_balance').empty().html((parseFloat(balance.bl) - parseFloat(balance.bl_used)+0.00).toFixed(2)) ;

                    $('#el_prev_balance').empty().html('0.00');
                    $('#mpl_prev_balance').empty().html('0.00');
                }
            }
        });
        

        if($.trim($('label[for="form_status_id"]').siblings('div').text()) == "Disapproved")
            $('label[for="decline_remarks"]').parent().show();
        
    }

    if( module.get_value('view') == "edit" || module.get_value('module_link') == "employee/dtr" ){

        //check if hra health
         $.ajax({
            url: module.get_value('base_url') + module.get_value('module_link') + '/check_if_hra',
            type: 'post',
            data: 'employee_id='+user.get_value('user_id'),
            dataType: 'json',
            success: function (response) {     
                if(response.data != 1)
                    $('#actual_date-temp').replaceWith('<input type="textbox" id="actual_date-temp" value="'+$('#actual_date').val()+'" readonly=readonly />');
            }
        });

    }

    if(  module.get_value('view') == "index"  ){

        $('ul#grid-filter li').live('click',function(){

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

    $('#no_of_pregnancy').hide();

    if ( user.get_value('post_control') != 1 || $('#filter').val() == "personal" || module.get_value('module_link') == "employee/dtr") {
        $('input[name="employee_id"]').siblings('span.icon-group').remove();
    }

    var pregnancies = '';

    window.onload = function(){
        if($('#application_form_id').val() == 1 && !advance_sl){

            $('input[name="date-temp-from"]').datepicker( "option", "maxDate", new Date() );  
            $('input[name="date-temp-to"]').datepicker( "option", "maxDate", new Date() ); 

        }

         if( $('ul#grid-filter li').length > 0 ){  

            $('ul#grid-filter li').each(function(){ 

                if( $(this).hasClass('active') ){

                    if($(this).attr('filter') == 'for_approval'){
                       $('.status-buttons').parent().show();
                    }
                    else{
                       $('.status-buttons').parent().hide();
                    }

                }
            });

        }
        else{
            $('.status-buttons').parent().hide();
        }

        $('#application_form_id').trigger('change');

    }

    $('input[name="employee_id"]').change(function(){
    
       generate_affected_dates();

    });

    $('input[name="date-temp-to"]').change(function(){
    
        if( $('#application_form_id').val() == 5 ){
            var date_to = $('#date_to').val();
            var detail = date_to.split('/');

            $.ajax({
                url: module.get_value('base_url') + module.get_value('module_link') + '/get_ml_specifics',
                type: 'post',
                data: 'employee_id=' + $('input[name="employee_id"]').val() + '&date=' +date_to,
                dataType: 'json',
                success: function (response) {     

                    var return_date = new Date(response.date);

                    $('input[name="return_date-temp"]').val( return_date.getMonth() + 1 +'/'+return_date.getDate()+'/'+return_date.getFullYear() );
                    $('input[name="return_date"]').val( return_date.getMonth() + 1 +'/'+return_date.getDate()+'/'+return_date.getFullYear() );   
                }
            });
        }

        if($('#application_form_id').val() == 16) {

            $.ajax({
                url: module.get_value('base_url') + module.get_value('module_link') + '/get_bol_report_date',
                type: 'post',
                data: 'employee_id=' + $('input[name="employee_id"]').val() + '&date=' + $('#date_to').val(),
                dataType: 'json',
                success: function (response) {     

                    var report_date = response.date;
                    $('#report_date').val(report_date);
                }
            });

        }

        generate_affected_dates();
        if(module.get_value('module_link') != "employee/dtr"){
            get_approvers();
        }

    });

    $('.approve-array').live('click', function(){
        if(user.get_value('approve_control') == 1)
        {
            var selected = $("#"+$(this).attr('container')).jqGrid("getGridParam", "selarrrow");
            
            if(selected.length > 0)
            {
                if(selected[0] == '')
                {
                    //remove the value for "check all"
                    selected.shift();
                }
                approve_record(selected, $(this).attr('module_link'), $(this).attr('container'));
            }
            else{
                $('#message-container').html(message_growl('attention', 'No record was selected!'))
            }
        }
        else{
            $('#message-container').html(message_growl('error', 'You dont have delete privileges! Please contact the System Administrator.'));
        }
    });

  $('.disapprove-array').live('click', function(){
        if(user.get_value('decline_control') == 1)
        {
            var selected = $("#"+$(this).attr('container')).jqGrid("getGridParam", "selarrrow");
            
            if(selected.length > 0)
            {
                if(selected[0] == '')
                {
                    //remove the value for "check all"
                    selected.shift();
                }
                disapprove_record(selected, $(this).attr('module_link'), $(this).attr('container'));
            }
            else{
                $('#message-container').html(message_growl('attention', 'No record was selected!'))
            }
        }
        else{
            $('#message-container').html(message_growl('error', 'You dont have delete privileges! Please contact the System Administrator.'));
        }
    });

    $('input[name="date-temp-from"]').change(function(){
        if( $('#application_form_id').val() == 5 && $(this).val() != ''){
            var date_from = $('#date_from').val();
            var detail = date_from.split('/');            
        
            var add_days = 59;
            if( $('#delivery_type_id').val() == 2 && $('#application_form_id').val() == 5 ) add_days = 77;

            var end_date = new Date();
            end_date.setMonth(detail[0] - 1);
            end_date.setYear(detail[2]);
            end_date.setDate(detail[1]);
            end_date.setDate(end_date.getDate() + add_days);

            $('input[name="date-temp-to"]').val( end_date.getMonth() + 1 +'/'+end_date.getDate()+'/'+end_date.getFullYear() );
            $('input[name="date_to"]').val( end_date.getMonth() + 1 +'/'+end_date.getDate()+'/'+end_date.getFullYear() );
            $('input[name="date-temp-to"]').trigger('change');
            

            $.ajax({
                url: module.get_value('base_url') + module.get_value('module_link') + '/get_ml_specifics',
                type: 'post',
                data: 'employee_id=' + $('input[name="employee_id"]').val() + '&date=' +end_date.getFullYear() +'-'+ (end_date.getMonth() + 1) + '-' + end_date.getDate(),
                dataType: 'json',
                success: function (response) {                    
                    var return_date = new Date(response.date);
                    switch(response.pregnancies) {
                        case 1: pregnancies = '1st'; break;
                        case 2: pregnancies = '2nd'; break;
                        case 3: pregnancies = '3rd'; break;
                        case 4: pregnancies = '4th'; break;
                        default: pregnancies = 'Up to 4th pregnacy only.';
                    }

                    if ($('#dummy-span').size() == 0) {
                        $('label[for="no_of_pregnancy"]').append('<span id="dummy-span"></span>');                    
                    }

                    $('#dummy-span').html(pregnancies);
                    
                    $('#no_of_pregnancy').val(response.pregnancies); 

                    $('input[name="return_date-temp"]').val( return_date.getMonth() + 1 +'/'+return_date.getDate()+'/'+return_date.getFullYear() );
                    $('input[name="return_date"]').val( return_date.getMonth() + 1 +'/'+return_date.getDate()+'/'+return_date.getFullYear() );   
                }
            });
        }

        
        if( $('#application_form_id').val() == 13 ){
            $('input[name="date-temp-to"]').val( $(this).val() );
            $('input[name="date_to"]').val( $(this).val() );             
        }

        generate_affected_dates();
        if(module.get_value('module_link') != "employee/dtr"){
            get_approvers();
        }
    });

    if ($('#div-detail-dates-affected').size() > 0) {
        $('label[for="label-dates-affected"]').next('div').append($('#div-detail-dates-affected').html());
    }

    //alert($.trim($('label[for="name_relative"]').next('div').html()));
    if ($.trim($('label[for="name_relative"]').next('div').html()) === "&nbsp;" || $.trim($('label[for="name_relative"]').next('div').html()) === ""){
        $('label[for="name_relative"]').parent('div').remove();
    }   
    if ($.trim($('label[for="relationship_id"]').next('div').html()) === "&nbsp;" || $.trim($('label[for="relationship_id"]').next('div').html()) === ""){
        $('label[for="relationship_id"]').parent('div').remove();
    }    
    if ($.trim($('label[for="calamity_remarks"]').next('div').html()) === "&nbsp;" || $.trim($('label[for="calamity_remarks"]').next('div').html()) === ""){
        $('label[for="calamity_remarks"]').parent('div').remove();
    }       

    
    $('a.approve-single').live('click', function () {
        change_status($(this).parent().parent().parent().attr("id"),
            3,
            function () {
                $('#jqgridcontainer').trigger('reloadGrid');
                $.ajax({
                    url: module.get_value('base_url') + module.get_value('module_link') + '/get_for_approval_count',
                    type: 'get',
                    dataType: 'json',
                    success: function (response) {
                        $('#approval-counter').html(response.count);
                    }
                });
            }
        );
    });

    $('a.comments-single').live('click', function () {    
        var record_id = $(this).parent().parent().parent().attr("id");        
        Boxy.ask("Are you sure you want to send this to HR for validation?", ["Yes", "No"],function( choice ) {
        if(choice == "Yes"){
              change_status(record_id, 6, function () { $('#jqgridcontainer').trigger('reloadGrid'); });
            }
        },
        {
            title: "Decline Leave Request"
        });        
    }); 
    
    $('a.decline-single').live('click', function () {    
        var record_id = $(this).parent().parent().parent().attr("id");        
        Boxy.ask("Are you sure you want to disapprove this request?", ["Yes", "No"],function( choice ) {
            if(choice == "Yes"){
                Boxy.ask("Add Remarks: <br /> <textarea name='decline_remarks' id='decline_remarks'></textarea>", ["Send", "Cancel"],function( add ) {
                    if(add == "Send"){
                        change_status(record_id, 4, function () { $('#jqgridcontainer').trigger('reloadGrid'); }, $('#decline_remarks').val());
                    }
                },
                {
                    title: "Decline Remarks"
                });
            }
        },
        {
            title: "Decline Leave Request"
        });        
    });    
    
    var form_status_id = 0;

    $('a.cancel-single').live('click', function () {  
        var record_id = $(this).parent().parent().parent().attr("id");      
        form_status_id = $(this).attr("form_status");
        Boxy.ask("Are you sure you want to cancel this request?", ["Yes", "No"],function( choice ) {
        if(choice == "Yes"){
                if (form_status_id == 2){
                    Boxy.ask("Add Remarks: <br /> <textarea name='decline_remarks' id='decline_remarks'></textarea>", ["Send", "Cancel"],function( add ) {
                        if(add == "Send"){
                            change_status(record_id, 2, function () { $('#jqgridcontainer').trigger('reloadGrid'); }, $('#decline_remarks').val()); 
                        }
                    },
                    {
                        title: "Decline Remarks"
                    }); 
                }
                else{
                    change_status(record_id, 5, function () { $('#jqgridcontainer').trigger('reloadGrid'); });                     
                }           
            }
        },
        {
            title: "Cancel Leave Request"
        });        
    }); 

    //to get the employee info even hr or admin as the login user - tirso garcia (purpose is to birth day leave)
    var birth_date_tmp;
    $('#employee_id').bind('change', function() {
        var employee_id = $(this).val();
         $.ajax({
            url: module.get_value('base_url') + module.get_value('module_link') + '/get_employee_info',
            data: 'employee_id=' + employee_id,
            type: 'post',
            dataType: 'json',
            success: function (response) {
                birth_date_tmp = response.birth_date
            }
        });
    });
    //end to get the employee info even hr or admin as the login user - tirso garcia (purpose is to birth day leave)    

    if( module.get_value('view') == "edit" || module.get_value('module_link') == "employee/dtr" ){
        $('input[name="project-temp-from"]').change(function(){
            if($('#project_from').val() != "" && $('#project_to').val() != "")
                $('#base_off_allowed').val(compute_allowed_base_off($('#project_from').val(), $('#project_to').val()));
        });

        $('input[name="project-temp-to"]').change(function(){
            if($('#project_from').val() != "" && $('#project_to').val() != "")
                $('#base_off_allowed').val(compute_allowed_base_off($('#project_from').val(), $('#project_to').val()));
        });

        $('label[for="fit_to_work"]').parent().parent().parent().remove();
        var afdate = $('label[for="label-dates-affected"]').parent();
        $('#dates-container').appendTo(afdate).next();

        $('label[for="reason"]').append('<span class="red font-large">*</span>'); 
        
        $('#application_form_id').change(function(){
            $('#date-temp-from').siblings('span, input').show().next('img').show();
            $('label[for="label-dates-affected"], label[for="reason"], label[for="documents"]').parent('div').show();
            switch( true ){
                case $(this).val() == 6:
                    $('label[for="reason"]').parent('div').hide();
                    $('#reason').val('paternity');
                    //changes for paternity JR
                    check_actual_date(user.get_value('user_id'));
                    $('label[for="actual_date_delivery"]').parent().parent().parent().show();
                    $('label[for="delivery_type_id"]').parent().parent().parent().hide();
                    //changes for paternity JR
                    break;
                case $(this).val() == 5:
                    $('label[for="delivery_type_id"]').parent().parent().parent().show();
                    $('label[for="actual_date_delivery"]').parent().parent().parent().hide();
                    $('#delivery_type_id').val(''); 
                    $('#no_of_pregnancy').val(''); 
                    $('label[for="label-dates-affected"], label[for="reason"]').parent('div').hide();
                    $('#reason').val('maternity');
                    break;
                case $(this).val() == 13:
                    //var bday = user.get_value('birth_date');
                    var bday = birth_date_tmp;
                    var bday_part = bday.split('-');
                    var birth_date = bday_part['1'] +'/'+ bday_part['2'] +'/'+ new Date().getFullYear();
                    $('input[name="date-temp-from"]').val( birth_date );
                    $('input[name="date_from"]').val( birth_date );
                    $('input[name="date-temp-to"]').val( birth_date );
                    $('input[name="date_to"]').val( birth_date );
                    $('input[name="date-temp-from"]').trigger('change');

                    setTimeout(function () {
                        $('#date-temp-from').siblings('span, input').hide().next('img').hide();
                    }, 100);
                    $('#reason').val('birthday');
                    $('label[for="label-dates-affected"], label[for="reason"], label[for="documents"]').parent('div').hide();                    
                case $(this).val() != 5:
                    $('label[for="delivery_type_id"]').parent().parent().parent().hide();  
                    $('#delivery_type_id').val('5');  
                    $('#no_of_pregnancy').val('0'); 
                    //changes for paternity JR
                    $('label[for="actual_date_delivery"]').parent().parent().parent().hide();
                    $('#actual_date_delivery').val('0000-00-00');
                case $(this).val() != 3:
                    $('label[for="reason_type_id"]').parent().parent().parent().hide();
            }
            
            $('input[name="date-temp-from"]').trigger('change');

            if($(this).val() == 16) {
                $('label[for="report_date"]').parent().parent().parent().show();
            } else {
                $('label[for="report_date"]').parent().parent().parent().hide();
            }
        });

        $('#application_form_id').trigger('change');
        $('#delivery_type_id').change(function(){ $('input[name="date-temp-from"]').trigger('change'); });
        disable_field( $('input[name="return_date-temp"]') );
        if( module.get_value('record_id') == '-1' ){
            $('#form_status_id').val(1);
        } 

        $('#application_form_id').change(function(){
            if ($(this).val() == 3){
                $('label[for="name_relative"]:not(:has(span))').append('<span class="red font-large">*</span>');
                $('label[for="calamity_remarks"]:not(:has(span))').append('<span class="red font-large">*</span>');
                $('label[for="relationship_id"]:not(:has(span))').append('<span class="red font-large">*</span>');
                $('label[for="reason_type_id"]:not(:has(span))').append('<span class="red font-large">*</span>');                             
                $('label[for="name_relative"]').parent('div').hide();
                $('label[for="relationship_id"]').parent('div').hide();

                if ($('#reason_type_id').val() == 3){
                    $('label[for="calamity_remarks"]').parent('div').show();
                    $('label[for="others_reason"]').html('').parent('div').show();   
                }
                else{
                    $('label[for="calamity_remarks"]').parent('div').hide();
                    $('label[for="others_reason"]').html('').parent('div').hide();                       
                }
                
                $('label[for="reason_type_id"]').parent().parent().parent().show();                
                $('label[for="reason"]').parent('div').hide();                          
            }
            else{
                $('label[for="reason_type_id"]').parent().parent().parent().hide();                
                $('label[for="reason"]').parent('div').show();               
            }

            if( $(this).val() == 1 && !advance_sl){
                $('input[name="date-temp-from"]').datepicker( "option", "maxDate", new Date() );  
                $('input[name="date-temp-to"]').datepicker( "option", "maxDate", new Date() ); 
            }
            else{
                $('input[name="date-temp-from"]').datepicker( "option", "maxDate", null );  
                $('input[name="date-temp-to"]').datepicker( "option", "maxDate", null ); 
            }
        });  

        $('#reason_type_id').change(function(){
            if ($(this).val() == 1){
                $('label[for="name_relative"]').parent('div').show();
                $('label[for="relationship_id"]').parent('div').show();
                $('label[for="others_reason"]').parent('div').hide();
                $('label[for="calamity_remarks"]').parent('div').hide();                               
            }
            else if ($(this).val() == 2){
                $('label[for="name_relative"]').parent('div').hide();
                $('label[for="relationship_id"]').parent('div').hide();
                $('label[for="others_reason"]').parent('div').hide();
                $('label[for="calamity_remarks"]').parent('div').show();                
            }
            else if ($(this).val() == 3){
                $('label[for="name_relative"]').parent('div').hide();
                $('label[for="relationship_id"]').parent('div').hide();
                $('label[for="others_reason"]').html('').parent('div').show();                
                $('label[for="calamity_remarks"]').parent('div').show();
            }            
            else{
                $('label[for="others_reason"]').parent('div').hide();
                $('label[for="name_relative"]').parent('div').hide();
                $('label[for="relationship_id"]').parent('div').hide();
                $('label[for="calamity_remarks"]').parent('div').hide();                 
            }
        });              
    }

    if( module.get_value('view') == "detail" ){
        $('label[for="others_reason"]').html('&nbsp;').parent('div').show();                
        var reason = $('label[for="reason"]').next().html();
        var reason = reason.replace(/^\s\s*/, '').replace(/\s\s*$/, '');
        if (reason == "maternity"){
            $('label[for="label-dates-affected"]').parent().hide();
        }

        if( $.trim( $('label[for="fit_to_work"]').parent().find('div').text() ) == "Yes" ){
             $('label[for="fit_to_work"]').parent().find('div').html('Valid');
        }
        else{
            $('label[for="fit_to_work"]').parent().find('div').html('Invalid');
        }
    
        $('label[for="fit_to_work"]').parent().find('div').removeClass();
        $('label[for="fit_to_work"]').remove();

        window.onload = function(){

             $.ajax({
                url: module.get_value('base_url') + module.get_value('module_link') + '/get_user_info_via_leave',
                data: 'record_id=' + $('#record_id').val(),
                type: 'post',
                dataType: 'json',
                success: function (response) {
                    if (response.sex == "female"){
                        $('table.balance thead tr td:nth-child(6)').html('ML');
                    }
                    else{
                        $('table.balance thead tr td:nth-child(6)').html('PL');   
                    }
                }
            });

        }

    }

    $('#employee_id').stop().change(function () {
        if(module.get_value('module_link') != "employee/dtr"){
            get_approvers();
        }
        var original_val = $('#application_form_id').val();
        
        $.ajax({
            url: module.get_value('base_url') + module.get_value('module_link') + '/get_leave_type_dropdown',
            data: 'user_id=' + $(this).val(),
            type: 'post',
            dataType: 'json',
            success: function(response) {
                $('#application_form_id option').remove();
                $('#application_form_id').append('<option value="">Select&hellip;</option');
                $.each(response.types, function (index, type) {
                    $('#application_form_id')
                        .append($('<option></option>').val(type.application_form_id).text(type.application_form));
                });

                $('#application_form_id').val(original_val).trigger("liszt:updated");
            }
        });

        $.ajax({
            url: module.get_value('base_url') + module.get_value('module_link') + '/get_leave_balance',
            data: 'employee_id=' + $(this).val(),
            type: 'post',
            dataType: 'json',
            success: function (balance) {
                if(balance.show_carried == 1)
                {
                    ( balance.vl == 'NaN' ) ? $('#vl').empty() : $('#vl').empty().html(balance.vl) ;
                    ( balance.sl == 'NaN' ) ? $('#sl').empty() : $('#sl').empty().html(balance.sl) ;
                    ( balance.el == 'NaN' ) ? $('#el').empty() : $('#el').empty().html(balance.el) ;
                    ( balance.mpl == 'NaN' ) ? $('#mpl').empty() : $('#mpl').empty().html(balance.mpl) ;
                    ( balance.bl == 'NaN' ) ? $('#bl').empty() : $('#bl').empty().html(balance.bl) ;

                    ( balance.vl_used == 'NaN' ||  balance.vl_used == 0 ) ? $('#vl_used').empty() :  $('#vl_used').empty().html(balance.vl_used) ;
                    ( balance.sl_used == 'NaN' || balance.sl_used == 0 ) ? $('#sl_used').empty() :  $('#sl_used').empty().html(balance.sl_used) ;
                    ( balance.el_used == 'NaN' || balance.el_used == 0) ? $('#el_used').empty() :  $('#el_used').empty().html(balance.el_used) ;
                    ( balance.mpl_used == 'NaN' || balance.mpl_used == 0) ? $('#mpl_used').empty() :  $('#mpl_used').empty().html(balance.mpl_used) ;
                    ( balance.bl_used == 'NaN' ||  balance.bl_used == 0) ? $('#bl_used').empty() :  $('#bl_used').empty().html(balance.bl_used) ;             

                    ( (parseFloat(balance.vl) - parseFloat(balance.vl_used)).toFixed(2) == 'NaN' ) ? $('#vl_balance').empty() :  $('#vl_balance').empty().html(((parseFloat(balance.vl) + (parseFloat(balance.carried_vl))) - parseFloat(balance.vl_used)+0.00).toFixed(2)) ;
                    ( (parseFloat(balance.sl) - parseFloat(balance.sl_used)).toFixed(2) == 'NaN' ) ? $('#sl_balance').empty() :  $('#sl_balance').empty().html(((parseFloat(balance.sl) + (parseFloat(balance.carried_sl))) - parseFloat(balance.sl_used)+0.00).toFixed(2)) ;
                    ( (parseFloat(balance.el) - parseFloat(balance.el_used)).toFixed(2) == 'NaN' ) ? $('#el_balance').empty() :  $('#el_balance').empty().html((parseFloat(balance.el) - parseFloat(balance.el_used)+0.00).toFixed(2)) ;
                    ( (parseFloat(balance.mpl) - parseFloat(balance.mpl_used)).toFixed(2) == 'NaN' ) ? $('#mpl_balance').empty() :$('#mpl_balance').empty().html((parseFloat(balance.mpl) - parseFloat(balance.mpl_used)+0.00).toFixed(2)) ;
                    ( (parseFloat(balance.bl) - parseFloat(balance.bl_used)).toFixed(2) == 'NaN' ) ? $('#bl_balance').empty() :  $('#bl_balance').empty().html((parseFloat(balance.bl) - parseFloat(balance.bl_used)+0.00).toFixed(2)) ;
                } 

                if(balance.show_carried == 2)
                {
                    
                    if( balance.client_no == 2 ){

                        if( balance.sex == "male" ){
                            $('#mpl').show();
                            $('#mpl_used').show();
                            $('#mpl_balance').show();
                            $('#mpl_prev_balance').show();
                            $('#mpl_header').show();
                        }
                        else{
                            $('#mpl').hide();
                            $('#mpl_used').hide();
                            $('#mpl_balance').hide();
                            $('#mpl_prev_balance').hide();
                            $('#mpl_header').hide();
                        }

                    }
                    
                    var total_vl_balance = parseFloat(balance.vl) + parseFloat(balance.carried_vl);
                    var total_sl_balance = parseFloat(balance.sl) + parseFloat(balance.carried_sl);

                    ( balance.vl == 'NaN' || !balance.vl ) ? $('#vl').html('0.00') : $('#vl').empty().html(balance.vl) ;
                    ( balance.sl == 'NaN' || !balance.sl) ? $('#sl').html('0.00') : $('#sl').empty().html(balance.sl) ;
                    ( balance.el == 'NaN' || !balance.el) ? $('#el').html('0.00') : $('#el').empty().html(balance.el) ;
                    ( balance.mpl == 'NaN' || !balance.mpl) ? $('#mpl').html('0.00') : $('#mpl').empty().html(balance.mpl) ;
                    ( balance.bl == 'NaN' || !balance.bl) ? $('#bl').html('0.00') : $('#bl').empty().html(balance.bl) ;
                    ( balance.bol == 'NaN' || !balance.bol) ? $('#bol').html('0.00') : $('#bol').empty().html(balance.bol) ;
                    ( balance.sil == 'NaN' || !balance.sil) ? $('#sil').html('0.00') : $('#sil').empty().html(balance.sil) ;
                    ( balance.ul == 'NaN' || !balance.ul) ? $('#ul').html('0.00') : $('#ul').empty().html(balance.ul) ;

                    ( balance.vl_used == 'NaN' || !balance.vl_used) ? $('#vl_used').html('0.00') : $('#vl_used').empty().html(balance.vl_used) ;
                    ( balance.carried_vl == 'NaN' || !balance.carried_vl) ? $('#vl_prev_balance').html('0.00') : $('#vl_prev_balance').empty().html(balance.carried_vl) ;
                    ( total_vl_balance == 0) ? $('#vl_balance').html('0.00') : $('#vl_balance').empty().html(parseFloat(total_vl_balance - balance.vl_used).toFixed(2)) ;

                    ( balance.sl_used == 'NaN' || !balance.sl_used) ? $('#sl_used').html('0.00') : $('#sl_used').empty().html(balance.sl_used) ;
                    ( balance.carried_sl == 'NaN' || !balance.carried_sl) ? $('#sl_prev_balance').html('0.00') : $('#sl_prev_balance').empty().html(balance.carried_sl) ;
                    ( total_sl_balance == 0) ? $('#sl_balance').html('0.00') : $('#sl_balance').empty().html(parseFloat(total_sl_balance - balance.sl_used).toFixed(2)) ;

                    ( balance.el_used == 'NaN' || !balance.el_used) ? $('#el_used').html('0.00') :  $('#el_used').empty().html(balance.el_used) ;
                    ( balance.mpl_used == 'NaN' || !balance.mpl_used) ? $('#mpl_used').html('0.00') :  $('#mpl_used').empty().html(balance.mpl_used) ;
                    ( balance.bol_used == 'NaN' || !balance.bol_used) ? $('#bol_used').html('0.00') :  $('#bol_used').empty().html(balance.bol_used) ;
                    ( balance.bl_used == 'NaN' || !balance.bl_used) ? $('#bl_used').html('0.00') :  $('#bl_used').empty().html(balance.bl_used) ;             
                    ( balance.sil_used == 'NaN' || !balance.sil_used) ? $('#sil_used').html('0.00') :  $('#sil_used').empty().html(balance.sil_used) ;
                    ( balance.ul_used == 'NaN' || !balance.ul_used) ? $('#ul_used').html('0.00') :  $('#ul_used').empty().html(balance.ul_used) ;

                    ( balance.carried_bl == 'NaN' || !balance.carried_bl) ? $('#bl_prev_balance').html('0.00') : $('#bl_prev_balance').empty().html(balance.carried_bl) ;

                    var el_bal = parseFloat(balance.el) - parseFloat(balance.el_used);
                    var pl_bal = parseFloat(balance.mpl) - parseFloat(balance.mpl_used);
                    var bl_bal = parseFloat(balance.bl) - parseFloat(balance.bl_used);
                    var bol_bal = parseFloat(balance.bol) - parseFloat(balance.bol_used);
                    var sil_bal = parseFloat(balance.sil) - parseFloat(balance.ail_used);
                    var ul_bal = parseFloat(balance.ul) - parseFloat(balance.ul_used);

                    ( el_bal == 'NaN' ) ? $('#el_balance').html('0.00') :  ( el_bal > 0 ) ? $('#el_balance').empty().html((el_bal).toFixed(2)) : $('#el_balance').html('0.00') ;
                    ( pl_bal == 'NaN' ) ? $('#mpl_balance').html('0.00') : ( pl_bal > 0 ) ? $('#mpl_balance').empty().html((pl_bal).toFixed(2)) : $('#mpl_balance').html('0.00') ;
                    ( bl_bal == 'NaN' ) ? $('#bl_balance').html('0.00') :  ( bl_bal > 0 ) ? $('#bl_balance').empty().html((bl_bal).toFixed(2)) : $('#bl_balance').html('0.00') ;
                    ( bol_bal == 'NaN' ) ? $('#bol_balance').html('0.00') : ( bol_bal > 0 ) ? $('#bol_balance').empty().html((bol_bal).toFixed(2)) : $('#bol_balance').html('0.00') ;
                    ( sil_bal == 'NaN' ) ? $('#sil_balance').html('0.00') :  ( sil_bal > 0 ) ? $('#sil_balance').empty().html((sil_bal).toFixed(2)) : $('#sil_balance').html('0.00') ;
                    ( ul_bal == 'NaN' ) ? $('#ul_balance').html('0.00') :  ( ul_bal > 0 ) ? $('#ul_balance').empty().html((ul_bal).toFixed(2)) : $('#ul_balance').html('0.00') ;

                    $('#el_prev_balance').empty().html('0.00');
                    $('#mpl_prev_balance').empty().html('0.00');
                }
            }
        });

        $.ajax({
            url: module.get_value('base_url') + module.get_value('module_link') + '/get_user_info',
            data: 'employee_id=' + $(this).val(),
            type: 'post',
            dataType: 'json',
            success: function (response) {
                if (response.sex == "female"){
                    $('table.balance thead tr td:nth-child(6)').html('ML');
                }
                else{
                    $('table.balance thead tr td:nth-child(6)').html('PL');   
                }
            }
        });
    });

    $('#employee_id').trigger('change');
}

function get_approvers(){
    if(module.get_value('module_link') == "employee/dtr"){
        var leave_url = module.get_value('base_url') + 'forms/leaves' + "/get_approvers";  
    }else{
        var leave_url = module.get_value('base_url') + module.get_value('module_link') + '/get_approvers';
    }
    if( $('#employee_id').val() != "" ){
        $.ajax({
            url: leave_url,
            data: 'employee_id=' + $('#employee_id').val() + '&form=leaves',
            type: 'post',
            dataType: 'json',
            success: function (data) {
                //$('#approvers-container').html(data.approvers);
                if (data.approvers != '' && data.approvers !== 'admin'){
                    $('#approvers-container').html(data.approvers);
                }
                else if (data.approvers == ''){
                    $('#message-container').html(message_growl('error', 'Please contact HR Admin. Approver has not been set.'));
                    setTimeout(function () {
                        $('#application_form_id option[value!=""]').remove();
                        $('#application_form_id').trigger("liszt:updated");
                    }, 100);
                    $('#approvers-container').html('');                                        
                }
            }
        });  
    }
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

function generate_affected_dates() {
    var ok_to_proceed = true;
    if( initial_load ){
        if (module.get_value('record_id ') == '-1') {
            ok_to_proceed = false;
        }
        initial_load = false; 
    }

    if ($('input[name="date_from"]').val() != '' && $('input[name="date_to"]').val() != '' && ok_to_proceed) {
        $.ajax({
            url: module.get_value('base_url') + module.get_value('module_link') + '/get_affected_dates',
            type: 'post',
            data: $('input[name="date_from"], input[name="record_id"], input[name="date_to"], input[name="employee_id"]').serialize(),
            success: function (response) {
                selectValues = { "00": "00", "01": "01" };
                if (response.type == 'success') {
                    if ($('#dates-container').size() > 0) {
                        $('#dates-container').remove();
                    }
                    
                    $('label[for="label-dates-affected"]').after('<div id="dates-container"></div>');
                    var ctr = 1;
                    $.each(response.dates, function (index, data) {

                        date = '<div style="padding:2px 0" class="leave_inclusive_date_'+data.date2+'"><span style="padding-right:5px">' + data.date + '</span>';
                        
                        wd = (data.duration_id == 0) ? 'selected' : '';
                        fs = (data.duration_id == 1) ? 'selected' : '';
                        ss = (data.duration_id == 2) ? 'selected' : '';
                        
                        date += '<input type="hidden" name="employee_leave_date_id[]" value="' + data.employee_leave_date_id + '"/>';
                        date += '<input type="hidden" name="dates[]" value="' + data.date + '"/>';
                        date += '<span> - ';
                        date += response.duration;
                        date += '</span></div>';
                        
                        $('#dates-container').append(date);

                        $('.leave_inclusive_date_'+data.date2).find('select').val(data.duration_id);

                        if (data.considered_restday){
                            $('.leave_inclusive_date_'+data.date2).find(".duration > option:not(:selected)").attr('disabled', true);
                        }
                        else{
                            $('.leave_inclusive_date_'+data.date2).find(".duration > option[value=4]").attr('disabled', true);
                        }

                        ctr++;                                                
                    });
                }
            }
        });
    }

    return false;
}

function ajax_save( on_success, is_wizard ){

    if( is_wizard == 1 ){
        var current = $('.current-wizard');
        var fg_id = current.attr('fg_id');
        var ok_to_save = eval('validate_fg'+fg_id+'()')
    }
    else{
        ok_to_save = validate_form();
    }
    
    if( ok_to_save ){
        var data = $('#record-form').serialize();
        data += "&on_success=" + on_success;
        var saveUrl = module.get_value('base_url')+module.get_value('module_link')+"/ajax_save"
                
        $.ajax({
            url: saveUrl,
            type:"POST",
            data: data,
            dataType: "json",
            async: false,
            beforeSend: function(){
                if( $('.now-loading').length == 0) $.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Saving, please wait...</div>' });
            },
            success: function(data){    
                if(on_success == "back") {

                    if( data.msg_type == 'attention' || data.msg_type == 'error' ){
                         $.unblockUI({ onUnblock: function() { message_growl(data.msg_type, data.msg) } });
                    }
                    else{
                        go_to_previous_page( data.msg );
                    }
                } else if (on_success == "email" && data.msg_type == "success") { 

                                    // Ajax request to send email.
                                    $.ajax({
                                        url: module.get_value('base_url') + module.get_value('module_link') + '/send_email',
                                        data: 'record_id=' + data.record_id,
                                        type: 'post',
                                        success: function () {
                                          window.location = module.get_value('base_url') + module.get_value('module_link') + '/detail/' + data.record_id;
                                          $.unblockUI({ onUnblock: function() { message_growl(data.msg_type, data.msg) } });
                                        }
                                    });                                    
                                } else{
                    //check if new record, update record_id
                    if($('#record_id').val() == -1 && data.record_id != ""){
                        $('#record_id').val(data.record_id);
                        $('#record_id').trigger('change');
                        if( is_wizard == 1 ) window.location = module.get_value('base_url') + module.get_value('module_link') + '/edit/' + data.record_id;
                    }                                        
                    $.unblockUI({ onUnblock: function() { message_growl(data.msg_type, data.msg) } });
                    if(data.msg_type == 'error' && data.record_id == null ){
                        go_to_previous_page( data.msg );
                    }

                }
            }
        }); 
    }
    else{
        return false;
    }
    return true;
}

function save_and_email() {
    ajax_save('email', 0);
}

function approve() {
    record_id = $('#record_id').val();
    change_status(record_id, 3);
}

function change_status_boxy(record_id, form_status_id, callback){
    switch(form_status_id){
        case 3:
            var question = "Are you sure you want to approve this request?";
            var title = "Approve Leave Request";
            break;
        case 4:
            var question = "Are you sure you want to disapprove this request?";
            var title = "Decline Leave Request";
            break;
        case 5:
            var question = "Are you sure you want to cancel this request";
            var title = "Cancel Leave Request";
            break;
        case 6:
            var question = "Are you sure you want to send this to HR for validation";
            var title = "For HR Validation";
            break; 
    }
    Boxy.ask(question, ["Yes", "No"],function( choice ) {
    if(choice == "Yes"){
             change_status(record_id, form_status_id, callback);
        }
    },
    {
        title: title
    });   
}

function change_status(record_id, form_status_id, callback, decline_remarks) {
    var data = 'record_id=' + record_id + '&form_status_id=' + form_status_id;

    if(decline_remarks){
        data += '&decline_remarks='+decline_remarks;
    }else{
        decline_remarks = '';
        data += '&decline_remarks='+decline_remarks;
    }

    $.ajax({
        url: module.get_value('base_url') + module.get_value('module_link') + '/change_status',
        data: data,
        type: 'post',
        dataType: 'json',
        success: function(response) {
            message_growl(response.type, response.message);
                    
            if (typeof(callback) == typeof(Function))
                callback(response);
        }
    }); 
}

function change_status_multiple(record_id, form_status_id, callback) {
    $.ajax({
        url: module.get_value('base_url') + module.get_value('module_link') + '/change_status_multiple',
        data: 'record_id=' + record_id + '&form_status_id=' + form_status_id,
        type: 'post',
        dataType: 'json',
        success: function(response) {
            message_growl(response.type, response.message);
                    
            if (typeof(callback) == typeof(Function))
                callback(response);
        }
    }); 
}

//changes for paternity JR
function check_actual_date(emp_id) {
    var send="employee_id="+emp_id;
    $.ajax({
        url: module.get_value('base_url') + module.get_value('module_link') + '/get_actual_delivery_date',
        data: send,
        type: 'post',
        dataType: 'json',
        success: function(response) {
             if (response.msg_type == 'new_record') {
                //$('#message-container').html(message_growl(response.msg_type, response.msg));
            } else {
                employee = response.data;
                    $('#actual_date_delivery').val(employee.actual_date_delivery);
                    $('#actual_date_delivery-temp').val(employee.actual_date_delivery);
                    //$('#actual_date_delivery').attr('disabled','true');
                    $('#actual_date_delivery-temp').attr('disabled','true');
                    $('#actual_date_delivery-temp').siblings('.ui-datepicker-trigger').hide();

            }
        }
    }); 
}
//changes for paternity JR
function goto_detail( data ){
    if (data.record_id > 0 && data.record_id != '') {
        module.set_value('record_id', data.record_id);    
        window.location.href = module.get_value('base_url') + module.get_value('module_link') + '/detail/' + module.get_value('record_id');         
    }
}

function sent_to_approver( record_id ){
    $.ajax({
      url: module.get_value('base_url') + module.get_value('module_link') + '/sent_to_approver',
      type: 'post',
      data: 'record_id='+record_id,
      beforeSend: function(){
        $.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>' });      
      },
      success: function (data) {
        go_to_previous_page( data.msg );
      }
    });
  }

  function approve_record(selected, action_url, container){
    Boxy.ask("Approve "+ selected.length +" selected record(s)?", ["Yes", "Cancel"],
        function( choice ) {
            if(choice == "Yes"){

                change_status_multiple(selected,
                    3,
                    function () {
                        $('#jqgridcontainer').trigger('reloadGrid');
                        $.ajax({
                            url: module.get_value('base_url') + module.get_value('module_link') + '/get_for_approval_count',
                            type: 'get',
                            dataType: 'json',
                            success: function (response) {
                                if(response){
                                    $('#approval-counter').html(response.count);
                                }
                            }
                        });
                    }
                );

            }
        },
        {
            title: "Approve Record"
        });
    }

    function disapprove_record(selected, action_url, container){
        if(selected.length){
            Boxy.ask("Disapprove "+ selected.length +" selected record(s)?", ["Yes", "Cancel"],
                function( choice ) {
                    if(choice == "Yes"){

                        change_status_multiple(selected,
                            4,
                            function () {
                                $('#jqgridcontainer').trigger('reloadGrid');
                                $.ajax({
                                    url: module.get_value('base_url') + module.get_value('module_link') + '/get_for_approval_count',
                                    type: 'get',
                                    dataType: 'json',
                                    success: function (response) {
                                        if(response){
                                            $('#approval-counter').html(response.count);
                                        }
                                    }
                                });
                            }
                        );

                    }
                },
            {
                title: "Disapprove Record"
            });
            return;
        }

        Boxy.alert('Bulk Disapprove is no longer allowed.');
    }

    function computes_allowed_base_off(date_from, date_to)
    {
        var from = new Date(date_from);
        var to = new Date(date_to);

        var bol = (to.getMonth()+1) - (from.getMonth()+1);

        return bol;
    }

    function compute_allowed_base_off(d1, d2) {

        var d1 = new Date(d1);
        var d2 = new Date(d2);
        var d1Y = d1.getFullYear();
        var d2Y = d2.getFullYear();
        var d1M = d1.getMonth();
        var d2M = d2.getMonth();
        var d1D = d1.getDate()+1;
        var d2D = d2.getDate()+1;
 
        var months_diff = (d2M+12*d2Y)-(d1M+12*d1Y);

        if(d2D < d1D)
            months_diff -= 1;

        if(months_diff % 2)
            $('#message-container').html(message_growl('error', 'base-off leave must be even'));
        else
            return months_diff;           

    }
