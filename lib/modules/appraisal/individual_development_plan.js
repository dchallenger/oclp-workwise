$(document).ready(function() {
    
    if (module.get_value('view') == 'detail') {
        $.ajax({
            url: module.get_value('base_url') + module.get_value('module_link') + '/get_employee_details',
            data: 'employee_id=' + user.get_value('user_id'),
            type: 'post',
            beforeSend: function(){
                $.blockUI({
                    message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>'
                });
            },
            success: function(data){
                $('label[for=employed_date]').next().html(data.employed_date);
                $.unblockUI();
            }
        });         
    }

    if (module.get_value('view') == 'edit') {
        toggleOn();
        $('#total_budget').css('text-align', 'right');
        $('#company_id, #department_id, #division_id, #position_id, #rank_id').attr('disabled', true);

        if (user.get_value('post_control') != 1) {
            // $('#employee_id').val(user.get_value('user_id'))
            $('#employee_id').trigger('change');

            if (module.get_value('record_id') == -1 ) {
                get_employee_details(user.get_value('user_id'));
                $('#employee_id').val(user.get_value('user_id'))
                $('#employee_id').trigger('change');
            }

             $('#itb, #ctb, #stb').attr('disabled', true);
        }else{
            $('#itb, #ctb').attr('disabled', true);
        }
    
        if(module.get_value('record_id') != -1){
            $('label[for=employee_id]').parent().find('.select-input-wrap').hide();
            $('label[for=employee_id]').parent().append('<div class="text-input-wrap"><input type="text" class=" input-text " readonly value="'+$('#employee_id :selected').text()+'" ></div>')
            check_training($('#employee_id').val());
        }

        $('#employee_id').live('change', function() {
            var emp_id = $(this).val(); 
            get_employee_details(emp_id);

        });
        
        $('.rating').live('change', function() {
            var option = $('option:selected', this).attr('criteria');
            var container = $(this).parent().find('.criteria_standard');
            container.html(option);

        });

        // var percent_total = 0
        // $('.percent_distribution').live('keyup', function() {
        //    var val = $(this).val();
        //    if (val != "") {
        //     percent_total += parseInt(val);
        //    };    
        //    $('#idp_completed_planned').val(percent_total);
        // });

        $('.percent_distribution').live('keyup', function() {
                var percent_total = 0;
                $('.percent_distribution').each(function (index, element){
                    var percent = $(element).val();
                    if (percent != "") {
                        percent_total += parseFloat(percent);
                    };
                });

             $('#idp_committed').val(percent_total); 
            });


        $('.add_row').click(function() {
            var html = '<tr class="idp-additional">' + $('#additional').html() + '</tr>';
            $('#idp-additional').before(html);
            var counter = $('.idp-additional').length

           if (parseInt(counter) == 1){
            $('#idp-table').find('.delete_row').hide()
           }else{
            $('#idp-table').find('.delete_row').show()
           };
        });

        $('.delete_row').live('click',function(){
           var elem = $(this);
           $(elem).parents('.idp-additional').remove();
           var counter = $('.idp-additional').length

           if (parseInt(counter) == 1){
            $('#idp-table').find('.delete_row').hide()
           };
        });

    };

    $('.approve-single').live('click', function() {
        var record_id = $(this).attr('record_id')
        forApproval(record_id, 'Approved');
    });

    $('.cancel-single').live('click', function() {
        var record_id = $(this).attr('record_id')
        forApproval(record_id, 'Decline');
    });

    // $('.cancel-application').live('click', function() {
    //     var record_id = $(this).attr('record_id')
    //     forApproval(record_id, 7);
    // });

    $('#itb, #ctb, #stb').live('keyup', function(){
        get_total()     
    });

    $('.percent_distribution').keydown( numeric_only );
    $('.budget_allocation').keydown( numeric_only );

});


function get_employee_details(employee_id) {
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
    
            $('#company_id').val(data.company);
            $('#department_id').val(data.department);
            $('#division_id').val(data.division);
            $('#position_id').val(data.position);
            $('#employed_date').val(data.employed_date);

            $('#itb').val(data.itb);
            $('#ctb').val(data.ctb);
            $('#stb').val(data.stb);
            
            var total_budget = parseFloat(data.itb) + parseFloat(data.ctb) + parseFloat(data.stb);
            
            $('#total_budget').val(total_budget);
            $('#rank_id').val(data.rank);


            if (user.get_value('post_control') != 1) {
                $('label[for=employee_id]').parent().find('.select-input-wrap').hide();
                $('label[for=employee_id]').parent().append('<div class="text-input-wrap"><input type="text" class=" input-text " readonly value="'+data.name+'" ></div>')
            }

            $.unblockUI();
        }           
    });


}

function check_training(employee_id) {
    $.ajax({
        url: module.get_value('base_url') + module.get_value('module_link') + '/check_training',
        data: 'employee_id=' + employee_id,
        type: 'post',
        beforeSend: function(){
            $.blockUI({
                message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>'
            });
       },
        success: function(data){
    
            $.unblockUI();

            $('#message-container').html(message_growl(data.msg_type, data.msg));
        }           
    });
}

function get_total () {
    var itb = ($('#itb').val() != "") ? $('#itb').val().replace(/,/g, '') : 0 ;
    var ctb = ($('#ctb').val() != "") ? $('#ctb').val().replace(/,/g, '') : 0 ;
    var stb = ($('#stb').val() != "") ? $('#stb').val().replace(/,/g, '') : 0 ;

    var total_budget = parseFloat(itb) + parseFloat(ctb) + parseFloat(stb)

    $('#total_budget').val(total_budget);
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

function ajax_save( on_success, is_wizard , callback ){
    if( is_wizard == 1 ){
        var current = $('.current-wizard');
        var fg_id = current.attr('fg_id');
       var ok_to_save = eval('validate_fg'+fg_id+'()')
    }
    else{
        var error_ad = false;
        var error_c = false;
        // var field_name = "";
        // var field = "";

    $('.areas_development').each(function (index, element){
       var parent_id = $(element).parent().parent().attr('id');
            if (parent_id != "additional") {
                if ($(element).val() == "") {
                    error_ad = true;
                };
            };
    });

    if (error_ad) {
        add_error('idp[areas_development][]', 'Areas for Development', "This field is mandatory.");
    };

    if (user.get_value('post_control') == 1) {
        $('.competencies').each(function (index, element){
            var parent_id = $(element).parent().parent().attr('id');
            if (parent_id != "additional") {
                if ($(element).val() == "") {
                    error_c = true;
                };
            }
        });

        if (error_c) {
            add_error('idp[competencies][]', 'Competencies', "This field is mandatory.");
        };

    }
        ok_to_save = validate_form();
    }

    if( ok_to_save ) { 
        $('#department_id, #division_id, #position_id, #rank_id, #company_id').attr('disabled', false);     
        $('#additional').remove();
        $('input:disabled ,  select:disabled , textarea:disabled').attr('disabled', false);
        
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
                        window.location = module.get_value('base_url') + module.get_value('module_link') + '/edit/' + data.record_id;
                        // if( is_wizard == 1 ) window.location = module.get_value('base_url') + module.get_value('module_link') + '/edit/' + data.record_id;
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
    
    // var width = $(window).width()*.3;
    remarks_boxy = new Boxy.confirm(
        '<div id="boxyhtml" ><textarea style="height:100px;width:340px;" name="remarks_approver"></textarea></div>',
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
