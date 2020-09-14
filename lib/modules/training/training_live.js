$(document).ready(function() {

    $('.date_complete').datepicker({
        changeMonth: true,
        changeYear: true,
        showOtherMonths: true,
        showButtonPanel: true,
        showAnim: 'slideDown',
        selectOtherMonths: true,
        showOn: "both",
        buttonImage: module.get_value('base_url') + user.get_value('user_theme') + "/icons/calendar-month.png",
        buttonImageOnly: true,
        buttonText: '',
        yearRange: 'c-90:c+10'
    });

    $('#department_id, #division_id, #position_id, #rank_id, #training_subject_id, #competency').attr('disabled', true);
    $('input[name="impact_to_behavior"]').attr('disabled', true);
    // $('#training_date-temp').attr("disabled", true);

    if (module.get_value('view') == 'edit') {
        $('label[for=employee_id]').parent().find('.select-input-wrap').hide();
        $('label[for=employee_id]').parent().append('<div class="text-input-wrap"><input type="text" class="input-text " readonly value="'+$('#employee_id option:selected').text()+'" ></div>');
    };

    $('#training_subject_id').css('width', '97%');

    $('.post_rating').live('change', function() {
        var post = $('option:selected', this).attr('post-rate');
        var pre = $(this).parents('.objective_type').find('.pre_rating').attr('pre-rate');
        var gap = $(this).parents('.objective_type').find('.gap');

        var gap_val = parseInt(post) - parseInt(pre);
        gap.val(gap_val);
        
        var post_total = 0;
        var flag = 0;

        $('select[name="objective[post_rating][]"] option:selected').each(function (index, element) {
            var option = $(element);
            var val = option.attr('post-rate')
            
            if (val != ' ') {
                post_total += parseFloat(val);
            };
                
            
        });
       if (post_total != 'NaN') {
            var total_pre = $('#total_pre').val();

            var total_gap = parseInt(post_total) - parseInt(total_pre);
            $('#total_gap').val(total_gap); 

            if (parseInt(total_gap) < 0) {
                $('input[value="3"]').attr('checked', 'checked');

            } else if(parseInt(total_gap) == 0) {
                $('input[value="2"]').attr('checked', 'checked');
            
            } else{
                $('input[value="1"]').attr('checked', 'checked');
            };
       };
    });


    // $('.gap').keydown( numeric_only ); 
});


function post_remarks(record_id) {
    
    var width = $(window).width()*.3;
    remarks_boxy = new Boxy.confirm(
        '<div id="boxyhtml" style="width:'+width+'px"><textarea style="height:100px;width:340px;" name="remarks_approver"></textarea></div>',
        function () {
            // url = module.get_value('base_url') + module.get_value('module_link') + '/' + action + '_request/';
            remarks = $('textarea[name="remarks_approver"]').val();
            $.ajax({
              url: module.get_value('base_url') + module.get_value('module_link') + '/save_remarks',
              type: 'post',
              data: 'remarks='+remarks+'&record_id='+record_id,
              beforeSend: function(){
                $.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Saving, please wait...</div>' });      
              },
              success: function (data) {
                    window.location = module.get_value('base_url') + module.get_value('module_link') + '/detail/' + data.record_id;
              }
            });
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

function ajax_save( on_success, is_wizard , callback ){
    if( is_wizard == 1 ){
        var current = $('.current-wizard');
        var fg_id = current.attr('fg_id');
        var ok_to_save = eval('validate_fg'+fg_id+'()')
    }
    else{
        ok_to_save = validate_form();
    }
    
    if( ok_to_save ) { 
        $('#department_id, #division_id, #position_id, #rank_id, #training_subject_id, #competency').attr('disabled', false);     
        $('input[name="impact_to_behavior"]').attr('disabled', false);   
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

function validate_fg680() {
    // validate_mandatory_array('transfer[transfer][]"', "Knowledge Transfer");
    return process_errors();
}

function validate_fg683() {
    // validate_mandatory_array('transfer[transfer][]"', "Knowledge Transfer");
    return process_errors();
}
function validate_fg678() {
    // validate_mandatory_array('transfer[transfer][]"', "Knowledge Transfer");
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
        postData: {
            to_filter: filter
        },  
    }).trigger("reloadGrid");   
}
