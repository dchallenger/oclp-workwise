var embark_form_boxy = false;
function embark(vessel_id){
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
                    embark_form_boxy = new Boxy('<div id="embark_form_boxy" style="width: 900px;">'+ data.embark_form +'</div>',{
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
                });
            }
        });
    }

function save_embark(vessel_id){
    var temp = $.map($('select[name="employee_id-multiselect"]').multiselect("getChecked"),function( input ){
    return input.value;
    });

    $('input[name="employee_id"]').val(temp);	
    if( $('input[name="employee_id"]').val(temp) == '') {
        $('#message-container').html(message_growl('error', 'Values field is required'));
        return false ;
    }
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
            if( data.msg_type == 'success' ){
                $.unblockUI();  
                Boxy.get($('#embark_form_boxy')).hide();
                embark(vessel_id);
                $("#jqgridcontainer").trigger("reloadGrid");
            }
        }
    });    
}

var embark_edit_boxy = false;
function disembark(vessel_id){
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
                if( !embark_edit_boxy ){
                    embark_edit_boxy = new Boxy('<div id="embark_edit_boxy" style="width: 900px;">'+ data.embark_edit +'</div>',{
                        title: 'Disembark',
                        draggable: false,
                        modal: true,
                        center: true,
                        unloadOnHide: true,
                        show: true,
                        afterShow: function(){ $.unblockUI(); },
                        beforeUnload: function(){ $.unblockUI(); embark_edit_boxy = false; }
                    });
                    boxyHeight(embark_edit_boxy, '#embark_edit_boxy'); 
                    
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
                });
            }
        });
    }

function save_disembark(vessel_id){
    $.ajax({
        url: module.get_value('base_url') + module.get_value('module_link') + '/save_disembark',
        data: $('form[name="embark_edit"]').serialize(),
        dataType: 'json',
        type: 'post',
        async: false,
        beforeSend: function(){
            $.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url') + user.get_value('user_theme') + '/images/loading.gif"><br />Saving, please wait...</div>' });
        },
        success: function ( data ) {
            $.unblockUI();
            message_growl(data.msg_type, data.msg);
            if( data.msg_type == 'success' ){
                $.unblockUI();  
                Boxy.get($('#embark_edit_boxy')).hide();
                disembark(vessel_id);
                $("#jqgridcontainer").trigger("reloadGrid");

            }
        }
    });    
}
