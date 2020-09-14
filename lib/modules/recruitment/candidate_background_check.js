$(document).ready(function() {

    $('input[name="applicant_id"]').siblings('span.icon-group').remove();
   
   $('.icon-16-users').live('click', function(){
        window.location = module.get_value('base_url') + "recruitment/applicants/detail/" + $(this).attr('candidate_id') + "/2" ;
        
    });

    if( module.get_value('view') != "index" )
    {
        if( module.get_value('view') == "edit" )
        {
            window.onload = function(){
                $(".multi-select").multiselect({
                    show:['blind',250],
                    hide:['blind',250],
                    selectedList: 1
                });
                var reference = $("#reference_id").val();
                if (reference != null && reference.length > 0) {
                    get_reference_info(reference, $('#bc_record_id').val());
                };
            }
        }

        // toggleOn();
        
        $.ajax({
            url: module.get_value('base_url') + module.get_value('module_link')+ '/get_mrf_details',
            type: 'post',
            data: 'applicant_id='+$('input[name="applicant_id"]').val()+ '&reference_ids='+ $('#reference_ids').val(),
            dataType: 'json',
            async: false,
            beforeSend:function() {
                $.blockUI({ 
                        message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>'
                         });
            },
            success:function(response) {
                $.unblockUI();
                if( module.get_value('view') == "edit" )
                {
                    $('#considered_position').val(response.position_id);
                    $('#considered_position').chosen().trigger("liszt:updated");
                    $('#considered_position').siblings().hide();
                    var position = $('#considered_position option:selected').html()
                    $('#considered_position').parent().append('<input type="text" disabled class="input-text" value="'+position+'">');

                    $('#department_id').val(response.department_id);
                    $('#department_id').chosen().trigger("liszt:updated")
                    $('#department_id').siblings().hide();
                    var department = $('#department_id option:selected').html()
                    $('#department_id').parent().append('<input type="text" disabled class="input-text" value="'+department+'">');


                    $("#reference_id").parent().remove() //html(response.references);    
                    $('label[for="reference_id"]').append('<br><div class="multiselect-input-wrap">'+response.references+'</div>');
                }else{
                    $('label[for="reference_id"]').next().html(response.reference_name)
                }
            }
        });
    
    
        $("#reference_id").bind("multiselectclose", function(event, ui){
            var selected = $(this).val();
            var record = $('#bc_record_id').val();
            get_reference_info(selected, record);
                
        }); 
    }



    $('.icon-16-approve').live('click', function () {
        var obj = $(this);
        var candidate_id = obj.attr('candidate_id');

        Boxy.ask(obj.attr('tooltip') + '?', ["Yes", "Cancel"],
            function( choice ) {
                if(choice == "Yes"){
                    $.ajax({
                        url: module.get_value('base_url') + 'recruitment/candidate_background_check/change_status',
                        data: 'status=accept&candidate_id=' + candidate_id,
                        dataType: 'json',
                        type: 'post',
                        beforeSend: function(){
                            $.blockUI({
                                message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>'
                            });         
                        },          
                        success: function (response) {
                            page_refresh();
                            $.unblockUI({
                                        onUnblock: function() {
                                            message_growl(response.msg_type, response.msg)
                                        }
                                    });                             
                               
                        }
                    });
                }
            },
            {
                title: obj.attr('tooltip')
            }
        );
    });

    $('.icon-16-disapprove').live('click', function () {
        var obj = $(this);
        var width = $(window).width()*.3;
        var candidate_id = obj.attr('candidate_id');
        
        Boxy.confirm(
            '<div id="boxyhtml" style="width:'+width+'px">'
            + obj.attr('tooltip') + '?'
            + '<div>Remarks</div><textarea style="height:100px;width:340px;" id="remarks" name="remarks"></textarea></div>',
            function() {                
                $.ajax({
                    url: module.get_value('base_url') + 'recruitment/candidate_background_check/change_status',
                    data: 'status=reject&candidate_id=' + candidate_id + '&remark=' + $('#remarks').val(),
                    dataType: 'json',
                    type: 'post',
                    beforeSend: function(){
                        $.blockUI({
                            message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>'
                        });         
                    },          
                    success: function (response) {              
                        $.unblockUI({
                            onUnblock: function() {
                                message_growl(response.msg_type, response.msg)
                            }
                        });             
                        page_refresh();
                    }
                });
            },
            {
                title: obj.attr('tooltip')
            }
        );

    });
    


});

function get_reference_info(references, bc_record_id) {
    $.ajax({
        url : module.get_value('base_url') + module.get_value('module_link')+ '/get_reference_info',
        data : 'reference_id='+ references+'&record_id='+bc_record_id,
        dataType : 'html',
        type : 'post',
        beforeSend: function() {
            $.blockUI({ 
                        message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>'
                    });
        },
        success: function(response) {
            $.unblockUI();
           $('#reference-info-div').html(response);   
        }
    })    
};

// function ajax_save( on_success, is_wizard ){

//     if( is_wizard == 1 ){
//         var current = $('.current-wizard');
//         var fg_id = current.attr('fg_id');
//         var ok_to_save = eval('validate_fg'+fg_id+'()')
//     }
//     else{
//         ok_to_save = validate_form();
//     }
    
//     if( ok_to_save ){
//         var data = $('#record-form').serialize();
//         data += "&on_success=" + on_success;
//         var saveUrl = module.get_value('base_url')+module.get_value('module_link')+"/ajax_save"
                
//         $.ajax({
//             url: saveUrl,
//             type:"POST",
//             data: data,
//             dataType: "json",
//             async: false,
//             beforeSend: function(){
//                 if( $('.now-loading').length == 0) $.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Saving, please wait...</div>' });
//             },
//             success: function(data){                   
//                 if(on_success == "back") {
//                     go_to_previous_page( data.msg );
//                 } else if (on_success == "email" && data.msg_type == "success") {
//                                     // Ajax request to send email.
//                                     $.ajax({
//                                         url: module.get_value('base_url') + module.get_value('module_link') + '/send_email',
//                                         data: 'record_id=' + data.record_id,
//                                         type: 'post',
//                                         success: function () {
//                                           window.location = module.get_value('base_url') + module.get_value('module_link') + '/detail/' + data.record_id;
//                                           $.unblockUI({ onUnblock: function() { message_growl(data.msg_type, data.msg) } });
//                                         }
//                                     });                                    
//                                 } else{
//                     //check if new record, update record_id
//                     if($('#record_id').val() == -1 && data.record_id != ""){
//                         $('#record_id').val(data.record_id);
//                         $('#record_id').trigger('change');
//                         if( is_wizard == 1 ) window.location = module.get_value('base_url') + module.get_value('module_link') + '/edit/' + data.record_id;
//                     }                                        
//                     $.unblockUI({ onUnblock: function() { message_growl(data.msg_type, data.msg) } });
//                 }
//             }
//         }); 
//     }
//     else{
//         return false;
//     }
//     return true;
// }

