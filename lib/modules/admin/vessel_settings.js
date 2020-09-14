
$(document).ready(function () { 
    // $('.icon-16-active').die().live('click', function () {edit_Embark($(this).parents('tr').attr('id'));});
    
    $('.icon-16-edit').die().live('click', function () {edit_vessel($(this).parents('tr').attr('id'));});
    $('#populate').click(function () {
        
    });
});
function edit_vessel(record_id){
    module_url = module.get_value('base_url') + module.get_value('module_link') + '/quick_edit';

    $.ajax({
        url: module_url,
        type:"POST",
        data: 'record_id=' + record_id,
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
                var width = $(window).width()*.4;           
                quickedit_boxy = new Boxy('<div id="boxyhtml" style="width: '+width+'px;">'+ data.quickedit_form +'</div>',
                {
                    title: 'Edit Vessel',
                    draggable: false,
                    modal: true,
                    center: true,
                    unloadOnHide: true,
                    beforeUnload: function (){
                        $('.tipsy').remove();
                    
                    }               
                });

                boxyHeight(quickedit_boxy, '#boxyhtml');
                if (typeof(BindLoadEvents) == typeof(Function)) {
                    BindLoadEvents();
                }   
            }
        }
    });
}

function quickedit_boxy_callback(module) {                 
    $('#jqgridcontainer').jqGrid().trigger("reloadGrid");   
}


var embark_form_boxy = false;
function embark( vessel_id ){
    Boxy.ask("Do you really want to embark some employees on this vessel?", ["Yes", "No"],function( choice ) {
        if(choice == 'Yes'){
            $.ajax({
                url: module.get_value('base_url') + module.get_value('module_link') + '/get_embark_form',
                data: 'vessel_id=' + vessel_id,
                type: 'post',
                dataType: 'json',
                beforeSend: function(){
                    $.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>' });
                },
                success: function(data) {
                    $.unblockUI();
                    if( !embark_form_boxy ){
                        embark_form_boxy = new Boxy('<div id="embark_form_boxy" style="width: 677px;">'+ data.embark_form +'</div>',{
                            title: 'Embark',
                            draggable: false,
                            modal: true,
                            center: true,
                            unloadOnHide: true,
                            show: true,
                            afterShow: function(){ $.unblockUI(); },
                            beforeUnload: function(){ $.unblockUI(); embark_form_boxy = false; }
                        });
                        boxyHeight(embark_form_boxy, '#embark_form_boxy'); 

                        $('select[name="employee_id-multiselect"]').multiselect().multiselectfilter({show:['blind',250],hide:['blind',250],selectedList: 1});

                    }
                    
                    $('#date_embark').datetimepicker({
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
                        hourGrid: 4,
                        minuteGrid: 10,
                        timeFormat: 'hh:mm tt',
                        ampm: true,
                        yearRange: 'c-90:c+10',
                    }); 
                    $('#date_embark_to').datetimepicker({
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
                        hourGrid: 4,
                        minuteGrid: 10,
                        timeFormat: 'hh:mm tt',
                        ampm: true,
                        yearRange: 'c-90:c+10',
                    }); 
                }
            });
        }
    },
    {
    title: "Execute Task"
    });
}

var disembark_form_boxy = false;
function disembark( vessel_id ){
    Boxy.ask("Do you really want to disembark some employees on this vessel?", ["Yes", "No"],function( choice ) {
        if(choice == 'Yes'){
            $.ajax({
                url: module.get_value('base_url') + module.get_value('module_link') + '/get_disembark_form',
                data: 'vessel_id=' + vessel_id,
                type: 'post',
                dataType: 'json',
                beforeSend: function(){
                    $.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>' });
                },
                success: function(data) {
                    $.unblockUI();
                    if( !disembark_form_boxy ){
                        disembark_form_boxy = new Boxy('<div id="disembark_form_boxy" style="width: 677px;">'+ data.disembark_form +'</div>',{
                            title: 'Disembark',
                            draggable: false,
                            modal: true,
                            center: true,
                            unloadOnHide: true,
                            show: true,
                            afterShow: function(){ $.unblockUI(); },
                            beforeUnload: function(){ $.unblockUI(); disembark_form_boxy = false; }
                        });
                        boxyHeight(disembark_form_boxy, '#disembark_form_boxy'); 

                        $('select[name="employee_id-multiselect"]').multiselect().multiselectfilter({show:['blind',250],hide:['blind',250],selectedList: 1});
                    }

                    $('#date_disembark').datetimepicker({
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
                        hourGrid: 4,
                        minuteGrid: 10,
                        timeFormat: 'hh:mm tt',
                        ampm: true,
                        yearRange: 'c-90:c+10',
                    }); 
                }
            });
        }
    },
    {
    title: "Execute Task"
    });
}

function save_embark(){
    var temp = $.map($('select[name="employee_id-multiselect"]').multiselect("getChecked"),function( input ){
    return input.value;
    });

    $('input[name="employee_id"]').val(temp);

 
    $.ajax({
        url: module.get_value('base_url') + module.get_value('module_link') + '/save_embark',
        data: $('form[name="embark_form"]').serialize(),
        dataType: 'json',
        type: 'post',
        async: false,
        beforeSend: function(){
            $.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url') + user.get_value('user_theme') + '/images/loading.gif"><br />Saving, please wait...</div>' });
        },
        success: function ( data ) {
            $.unblockUI();
            message_growl(data.msg_type, data.msg);
        }
    });    
}

function save_disembark(){
    var temp = $.map($('select[name="employee_id-multiselect"]').multiselect("getChecked"),function( input ){
    return input.value;
    });

    $('input[name="employee_id"]').val(temp);
        
    $.ajax({
        url: module.get_value('base_url') + module.get_value('module_link') + '/save_disembark',
        data: $('form[name="disembark_form"]').serialize(),
        dataType: 'json',
        type: 'post',
        async: false,
        beforeSend: function(){
            $.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url') + user.get_value('user_theme') + '/images/loading.gif"><br />Saving, please wait...</div>' });
        },
        success: function ( data ) {
            $.unblockUI();
            message_growl(data.msg_type, data.msg);
        }
    });    
}
