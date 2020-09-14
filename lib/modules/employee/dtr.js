$(document).ready(function () {
    setTimeout(init_datepick, 1000);

    $('.icon-16-info').die().live('click', function () {
        $.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>' });
        $.ajax({
            url: module.get_value('base_url') + module.get_value('module_link') + '/fetch_forms',
            data: 'forms=' + $(this).attr('rel'),
            type: 'post',
            dataType: 'json',
            success: function (data) {
                var height = $(window).height()*.8;
                detail_boxy = new Boxy('<div id="boxyhtml" style="width:640px;height:'+height+'px;overflow:auto">' + data.html + '</div>',
                {
                    title: 'Forms',
                    draggable: false,
                    modal: true,
                    center: true,
                    unloadOnHide: true,
                    beforeUnload: function (){
                        $('.tipsy').remove();
                    }
                });
                boxyHeight(detail_boxy, '#boxyhtml');
            },
            complete: function () {
                $.unblockUI();
            }
        });
    });

    if (module.get_value('view') == 'index') {
        $('.jqgrow').die().live('dblclick', function(){
            return false;  
        });
        
        setTimeout(
            function () {
                $('select[name="employee_id"]').chosen();               
            }, 100
            );      
        //merging two column
        $("#jqgridcontainer").jqGrid({
            url: module.get_value('base_url') + module.get_value('module_link') + '/listview',
            loadtext: '<img src="'+ module.get_value('base_url') + user.get_value('user_theme') + '/images/loading.gif"><br />Loading...',
            datatype: "json",
            mtype: "POST",
            rowNum: 30,
            rowList: [10,15,25, 30, 60, 85, 100],
            toolbar: [true,"top"],
            height: 'auto',
            autowidth: true,
            pager: "#jqgridpager",
            pagerpos: 'right',
            toppager: true,
            viewrecords: true,
            altRows: true,
            forceFit: true,
            shrinkToFit: true,
            loadonce: true,
            colNames:["Date","IN","OUT","Work Shift","Hours Worked", "ET (Hours)", "Lates (Hours)", "Authorized UT (Hours)","UT (Hours)","OT (Hours)",""],
            colModel:[{name : 'date'},
                      {name : 'timein', width : '150',
                        cellattr: function(rowId, tv, rawObject, cm, rdata) {

                              if (tv == "AWOL" || tv == "Absent" || tv == "LEAVE" || tv =="Suspended" || tv == "Resigned" || tv == "Floating" || tv == "Paternity Leave" || tv == "Sick Leave" || tv == "Vacation Leave" || tv == "Leave Without Pay" || tv == "Maternity Leave" || tv == "Emergency Leave" || tv == "Special Leave for Women" || tv == "Birthday Leave" || tv == "Anniversary Leave" || tv == "Service Leave" || tv == "Multi-Purpose Leave" || tv == "Compensatory Leave " || tv == "Compensatory Leave"  || tv == "Annual Leave" ) { return ' colspan=2' }
                        }
                      },
                      {name : 'timeout', width : '150',
                        cellattr: function(rowId, tv, rawObject, cm, rdata) {
                            if ($.type(rawObject) == "object"){
                                var chk = false;
                                $.each(rawObject, function(key, element) {
                                    if (key == "timein") {
                                        if (element == "AWOL" || element == "LEAVE" || element == "Absent" || element == "Paternity Leave" || element == "Sick Leave" || element == "Vacation Leave" || element == "Leave Without Pay" || element == "Maternity Leave" || element == "Emergency Leave" || element == "Special Leave for Women" || element == "Birthday Leave" || element == "Anniversary Leave"  || element == "Service Leave" || element == "Multi-Purpose Leave" || element == "Compensatory Leave " || element == "Compensatory Leave"  || element == "Annual Leave" ){
                                            chk = true;
                                        }
                                    }
                                });                    
                                if (chk) { return 'style="display:none;"'; }
                            }
                            else{
                              if ($.inArray("AWOL", rawObject) != -1 || $.inArray("LEAVE", rawObject) != -1 || $.inArray("Absent", rawObject) != -1 || $.inArray("Paternity Leave", rawObject) != -1 || $.inArray("Sick Leave", rawObject) != -1 || $.inArray("Vacation Leave", rawObject) != -1 || $.inArray("Leave Without Pay", rawObject) != -1 || $.inArray("Maternity Leave", rawObject) != -1 || $.inArray("Emergency Leave", rawObject) != -1 || $.inArray("Special Leave for Women", rawObject) != -1  || $.inArray("Birthday Leave", rawObject) != -1 || $.inArray("Anniversary Leave", rawObject) != -1 || $.inArray("Service Leave", rawObject) != -1 || $.inArray("Multi-Purpose Leave", rawObject) != -1 || $.inArray("Compensatory Leave ", rawObject) != -1 || $.inArray("Compensatory Leave", rawObject) != -1  || $.inArray("Annual Leave", rawObject) != -1 ){
                                return 'style="display:none;"'
                              }
                            }
                        }                  
                      },
                      {name : 'workshift'},
                      {name : 'hours_worked'},
                      {name : 'excused_tardiness'},
                      {name : 'lates'},
                      {name : 'authorized_undertime', width : '240'},
                      {name : 'undertime'},
                      {name : 'overtime'},
                      {name : 'forms',width : '50',align : 'center',classes : 'td-action'}],
            loadComplete: function(data){
                if (data.msg_type != 'success') {
                    $('#message-container').html(message_growl(data.msg_type, data.msg));
                }
            },
            gridComplete:function(){
            },
            caption: " List",
            ondblClickRow: function(rowid) {
                $.ajax({
                    url: module.get_value('base_url') + module.get_value('module_link') + '/get_client_no',
                    dataType: 'json',
                    type: 'post',
                    async: false,                          
                    success: function ( response ) {
                        if (response == 2){
                            if (rowid != "null"){
                                record_action("edit", rowid, "employee/employee_dtr_master");
                            }
                            else{
                                record_action("edit", "-1", "employee/employee_dtr_master","","");   
                            }
                        }
                    }
                }); 
            }        
        });
    }

    $('#filter-dtr').live('click', function () {
        $('#jqgridcontainer').jqGrid('setGridParam', 
        {
            datatype: 'json',
            page: 1,
            search: true,
            postData: {
                employee_id: $('select[name="employee_id"]').val(),
                date_from: $('input[name="date_from"]').val(),
                date_to: $('input[name="date_to"]').val(),
            },  
        }).trigger("reloadGrid");           
    });
});


function record_action(action, record_id, action_link, related_field, related_field_val)
{
    //update_gridsearch_parameters();
    $.blockUI({
        message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>'
    });
    if(related_field != "")
    {
        $('#record-form').append('<input type="hidden" name="'+ related_field +'" value="'+ related_field_val +'" >');
    }

    $('#record_id').val(record_id);
    if(action_link == "") action_link = module.get_value('module_link');
    $('#record-form').attr("action", module.get_value('base_url') + action_link + "/" + action);
    
    switch( action ){
        case "detail":
            $('#record-form').submit(); 
            break;
        case "edit":
            if(user.get_value('edit_control') == 1){
                $('#form-div').html('');
                $('#record-form').submit(); 
            }
            else{
                $.unblockUI();
                $('#message-container').html(message_growl('attention', 'Insufficient Access grant!\nPlease contact the system administrator.'))
            }
            break;
        case "duplicate_record":
            if(user.get_value('edit_control') == 1){
                $('#record-form').attr("action", module.get_value('base_url') + action_link + "/edit");
                $('#form-div').html('');
                $('#record-form').append('<input name="duplicate" value="1">');
                $('#record-form').submit(); 
            }
            else{
                $.unblockUI();
                $('#message-container').html(message_growl('attention', 'Insufficient Access grant!\nPlease contact the system administrator.'))
            }
            break;
            break;
    }   
}

if(module.get_value('view') == 'manage'){
    $(document).ready(function(){

    //change header name based on module manager
        $.ajax({
            url: module.get_value('base_url') + module.get_value('module_link') + '/get_manage_module_name',
            dataType: 'json',
            type: 'post',
            async: false,
            success: function ( data ) {
                $('header h2').html(data);
                $('title').html(data);
            }
        });     
        
        $('select[id="period_year"]').change(get_period);
        $('select[id="period_year"]').trigger('change');

        $('select[name="period_id"]').change(get_dtr);
        $('select[name="dtr-employee_id"]').change(get_dtr);

        $('#period_year').chosen();
        $('#period_id').chosen();
        $('select[name="dtr-employee_id"]').chosen();

        $('input[type="checkbox"].awol').stop().live('click', function(){
            if( $(this).is(':checked') ){
                $(this).parent().parent().find('input[type="text"]').addClass('dim').attr('readonly', true).datepicker("disable");;
            }
            else{
                $(this).parent().parent().find('input[type="text"]').removeClass('dim').attr('readonly', false).datepicker("enable");;  
            }
        });

        $('select.emp-forms').stop().live('change', get_form);

        toggleOn();
    });

    function get_form(){
        var form = $(this).val();
        if( form != "" ){
            var date = $(this).parent().parent().parent().attr('date');
            var employee_id = $('select[name="dtr-employee_id"]').val()
            var data = "form="+form+"&employee_id="+employee_id;
            
            switch(form){
                case 'leaves':
                    data = data + '&date_from='+ date + '&date_to='+date;
                    edit_leave( '-1', data );
                    break;
                case 'obt':
                    data = data + '&date_from='+ date + '&date_to='+date;
                    edit_obt( '-1', data );
                    break;
                case 'cws':
                    data = data + '&date_from='+ date + '&date_to='+date;
                    edit_cws( '-1', data );
                    break;
                case 'overtime':
                    data = data + '&date='+ date;
                    edit_overtime( '-1', data );
                    break;
            }
            $(this).val('');
        }
    }

    var loaded_form = '';

    function CustomBindLoadEvents(){
       switch( loaded_form ){
        case 'leaves':
            $('input[name="employee_id"]').siblings('span.icon-group').remove();
            bindMe();
            break;
        case 'obt':
            bindOBTEvents();
            bindFormsmainEvents();
            break;
        case 'cws':
            $('input[name="employee_id"]').siblings('span.icon-group').remove();
            bindCWSEvents();
            bindFormsmainEvents();
            break;
        case 'overtime':
            $('input[name="employee_id"]').siblings('span.icon-group').remove();
            bindOvertimeEvents();
            bindFormsmainEvents();
            break;
       }
    }

    function edit_leave( record_id, data ){
        loaded_form = 'leaves';
        data = data + '&record_id='+record_id;
        showQuickEditForm( module.get_value('base_url') + 'forms/leaves' + "/quick_edit", data );  
    }

    function edit_obt( record_id, data ){
        loaded_form = 'obt';
        data = data + '&record_id='+record_id;
        showQuickEditForm( module.get_value('base_url') + 'forms/obt' + "/quick_edit", data );  
    }

    function edit_cws( record_id, data ){
        loaded_form = 'cws';
        data = data + '&record_id='+record_id;
        showQuickEditForm( module.get_value('base_url') + 'forms/cws' + "/quick_edit", data );  
    }

    function edit_overtime( record_id, data ){
        loaded_form = 'overtime';
        data = data + '&record_id='+record_id;
        showQuickEditForm( module.get_value('base_url') + 'forms/oot' + "/quick_edit", data );  
    }

    function quickedit_boxy_callback( e ) {                 
        get_dtr();   
    }

    function change_leave_status(record_id, form_status_id) {
        var data = 'record_id=' + record_id + '&form_status_id=' + form_status_id;

        $.ajax({
            url: module.get_value('base_url') + module.get_value('module_link') + '/change_leave_status',
            data: data,
            type: 'post',
            dataType: 'json',
            success: function(response) {
                message_growl(response.type, response.message);
                quickedit_boxy.hide().unload();
                get_dtr();
            }
        }); 
    }

    function change_oot_status(record_id, form_status_id) {
        var data = 'record_id=' + record_id + '&form_status_id=' + form_status_id;

        $.ajax({
            url: module.get_value('base_url') + module.get_value('module_link') + '/change_oot_status',
            data: data,
            type: 'post',
            dataType: 'json',
            success: function(response) {
                message_growl(response.type, response.message);
                quickedit_boxy.hide().unload();
                get_dtr();
            }
        }); 
    }

    function change_obt_status(record_id, form_status_id) {
        var data = 'record_id=' + record_id + '&form_status_id=' + form_status_id;

        $.ajax({
            url: module.get_value('base_url') + module.get_value('module_link') + '/change_obt_status',
            data: data,
            type: 'post',
            dataType: 'json',
            success: function(response) {
                message_growl(response.type, response.message);
                quickedit_boxy.hide().unload();
                get_dtr();
            }
        }); 
    }

    function change_cws_status(record_id, form_status_id) {
        var data = 'record_id=' + record_id + '&form_status_id=' + form_status_id;

        $.ajax({
            url: module.get_value('base_url') + module.get_value('module_link') + '/change_cws_status',
            data: data,
            type: 'post',
            dataType: 'json',
            success: function(response) {
                message_growl(response.type, response.message);
                quickedit_boxy.hide().unload();
                get_dtr();
            }
        }); 
    }

    function get_period(){
        $.ajax({
            url: module.get_value('base_url') + module.get_value('module_link') + '/get_period',
            data: 'period_year=' + $('#period_year').val(),
            dataType: 'json',
            type: 'post',
            async: false,
            success: function ( data ) {
                $('#period_id').html(data.options) ;
                $("#period_id").trigger("liszt:updated");
            }
        });     
    }

    function get_dtr(){
        if($('#period_id').val() != '' && $('select[name="dtr-employee_id"]').val() != ''){
            $.ajax({
                url: module.get_value('base_url') + module.get_value('module_link') + '/get_dtr',
                data: 'period_id=' + $('#period_id').val()+ '&employee_id='+$('select[name="dtr-employee_id"]').val(),
                dataType: 'json',
                type: 'post',
                async: false,
                beforeSend: function(){
                    $.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url') + user.get_value('user_theme') + '/images/loading.gif"><br />Loading, please wait...</div>' });
                },
                success: function ( data ) {
                    $.unblockUI();
                    $('#dtr-container').html(data.dtr);
                    $('input.datetimepicker').datetimepicker({                            
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

                    $('input.datetimepicker').keypress(function(event){
                        if(event.keyCode == 8){
                            $(this).val('');
                        }
                    });
                }
            });    
        }
    }

    function save_dtr(){
        $('input[type="checkbox"].awol:checked').each(function(){
             $(this).parent().parent().find('input[type="text"]').addClass('dim').attr('readonly', true).datepicker("enable");;
        });

        $.ajax({
            url: module.get_value('base_url') + module.get_value('module_link') + '/save_dtr',
            data: $('form[name="save-dtr"]').serialize(),
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

        $('input[type="checkbox"].awol:checked').each(function(){
             $(this).parent().parent().find('input[type="text"]').addClass('dim').attr('readonly', true).datepicker("disable");;
        }); 
    }
}