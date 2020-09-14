$( document ).ready( function(){
    window.onload = function(){
        $(".multi-select").multiselect({
            show:['blind',250],
            hide:['blind',250],
            selectedList: 1
        });
    }
    
    init_datepick();    

    $("#date_start").change(function(){
        $('#date_from').val($('#date_start').val());
    });

    $("#date_end").change(function(){
        $('#date_to').val($('#date_end').val());
    });

    $('#category').live('change',function(){
        var items = {1 : "Company",2 : "Division",3 : "Department",4 : "Employee",5 : "Section"};
        var category_id = $(this).val();
        var category = items[category_id];

        var eleid = category.toLowerCase()

        if (category_id > 0){
            $.ajax({
                url: module.get_value('base_url') + module.get_value('module_link') + '/get_employee_time_record',
                data: 'category_id=' + category_id,
                dataType: 'html',
                type: 'post',
                async: false,
                beforeSend: function(){
                    $('#multi-select-main-container').hide();
                    $('#multi-select-loader').html('<div><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif" style="vertical-align:middle"><span style="padding-left:10px">Loading, please wait...</span></div>');                
                },                              
                success: function ( response ) {
                    $('label[for="employee_id"]').parent().remove();
                    $('#multi-select-loader').html('');                    
                    $('#multi-select-main-container').show();
                    $('#category_selected').html(category + ':');
                    $('#multi-select-container').html(response);
                    $('#'+eleid).multiselect().multiselectfilter({
                        show:['blind',250],
                        hide:['blind',250],
                        selectedList: 1
                    });
                    
                }
            });
        }
        else{
            $('#multi-select-main-container').hide();
            $('#category_selected').html('');
            $('#multi-select-container').html('');          
        }

            if (category_id != 4) {
                $('#'+eleid).bind("multiselectclose", function(event, ui){
                     var selected = $(this).val();

                    $.ajax({
                        url: module.get_value('base_url') + module.get_value('module_link') + '/get_employees',
                        data: 'category_id=' + selected + '&category='+category_id,
                        dataType: 'html',
                        type: 'post',
                        async: false,
                        beforeSend: function(){
                            $('#multi-select-main-container2').hide();
                            $('#multi-select-loader2').html('<div><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif" style="vertical-align:middle"><span style="padding-left:10px">Loading, please wait...</span></div>');                
                        },                              
                        success: function ( response ) {
                            $('#multi-select-loader2').html('');                    
                            $('#multi-select-main-container2').show();
                            $('#category_selected2').html('Employee:');
                            $('#multi-select-container2').html(response);
                            $('#employee').multiselect().multiselectfilter({
                                show:['blind',250],
                                hide:['blind',250],
                                selectedList: 1
                            });
                        }
                    });
                        
                });
            }
            else{
                $('#multi-select-loader2').html('');                    
                $('#multi-select-main-container2').hide();
            }
    }); 

    $('#category1').live('change', function(){
        generate_list();         
        if ($(this).val() == 1) {
            dynamic_switch();
            $('#dynamic').attr('checked', false);
            $('#dynamic_conatiner').hide();
            return true;
        }
        else{
            //$('#dynamic').attr('checked', false);
            $('#dynamic_conatiner').hide();   
        }
    });  

    $('input[name="dynamic"]').click(function(){
        if( ( $('#date_start').val() != "" ) && ( $('#date_end').val() != "" ) ){
            generate_list();
        }
        else{
            dynamic_switch(); 
        }               
    })

    if (module.get_value('view') == 'index') {
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
            colNames:["Employee<br />Name","Date","Work<br />Shift","Time<br />In","Time<br />Out","Hours<br />Worked", "OT<br />In","OT<br />Out","ET<br />(Hours)", "Lates<br />(Hours)","Authorized<br />UT (Hours)","UT<br />(Hours)","OT<br />(Hours)", "OT>8<br />(Hours)","ND<br />(Hours)","Absent",""],
            colModel:[{name : 'employee_name', width : '240'},
                      {name : 'date'},
                      {name : 'workshift', width : '240'},
                      {name : 'timein', width : '150',
                        cellattr: function(rowId, tv, rawObject, cm, rdata) {

                              if (tv == "AWOL" || tv == "Absent" || tv == "Leave" || tv =="Suspended" || tv == "Resigned" || tv == "Floating" || tv == "Paternity Leave" || tv == "Sick Leave" || tv == "Vacation Leave" || tv == "Leave Without Pay" || tv == "Maternity Leave" || tv == "Emergency Leave" || tv == "Special Leave for Women" || tv == "Birthday Leave" || tv == "Anniversary Leave" || tv == "Service Leave" || tv == "Multi-Purpose Leave" || tv == "Compensatory Leave " || tv == "Compensatory Leave"  || tv == "Annual Leave" ) { return ' colspan=2' }
                        }
                      },
                      {name : 'timeout', width : '150',
                        cellattr: function(rowId, tv, rawObject, cm, rdata) {
                            if ($.type(rawObject) == "object"){
                                var chk = false;
                                $.each(rawObject, function(key, element) {
                                    if (key == "timein") {
                                        if (element == "AWOL" || element == "Leave" || element == "Absent" || element == "Paternity Leave" || element == "Sick Leave" || element == "Vacation Leave" || element == "Leave Without Pay" || element == "Maternity Leave" || element == "Emergency Leave" || element == "Special Leave for Women" || element == "Birthday Leave" || element == "Anniversary Leave"  || element == "Service Leave" || element == "Multi-Purpose Leave" || element == "Compensatory Leave " || element == "Compensatory Leave"  || element == "Annual Leave" ){
                                            chk = true;
                                        }
                                    }
                                });                    
                                if (chk) { return 'style="display:none;"'; }
                            }
                            else{
                              if ($.inArray("AWOL", rawObject) != -1 || $.inArray("Leave", rawObject) != -1 || $.inArray("Absent", rawObject) != -1 || $.inArray("Paternity Leave", rawObject) != -1 || $.inArray("Sick Leave", rawObject) != -1 || $.inArray("Vacation Leave", rawObject) != -1 || $.inArray("Leave Without Pay", rawObject) != -1 || $.inArray("Maternity Leave", rawObject) != -1 || $.inArray("Emergency Leave", rawObject) != -1 || $.inArray("Special Leave for Women", rawObject) != -1  || $.inArray("Birthday Leave", rawObject) != -1 || $.inArray("Anniversary Leave", rawObject) != -1 || $.inArray("Service Leave", rawObject) != -1 || $.inArray("Multi-Purpose Leave", rawObject) != -1 || $.inArray("Compensatory Leave ", rawObject) != -1 || $.inArray("Compensatory Leave", rawObject) != -1  || $.inArray("Annual Leave", rawObject) != -1 ){
                                return 'style="display:none;"'
                              }
                            }
                        }                  
                      },
                      {name : 'ot_in'},
                      {name : 'ot_out'},
                      {name : 'hours_worked'},
                      {name : 'excused_tardiness'},
                      {name : 'lates'},
                      {name : 'authorized_undertime', width : '240'},
                      {name : 'undertime'},
                      {name : 'overtime'},
                      {name : 'overtime_8'},
                      {name : 'ot_nd'},
                      {name : 'awol'},
                      {name : 'forms',width : '50',align : 'center',classes : 'td-action'}],
            loadComplete: function(data){
                if (data.msg_type != 'success') {
                    $('#message-container').html(message_growl(data.msg_type, data.msg));
                }
            },
            gridComplete:function(){
            },
            caption: " List"        
        });
    }
});


function validate_form()
{
    
    //errors
    if(error.length > 0){
        var error_str = "Please correct the following errors:<br/><br/>";
        for(var i in error){
            if(i == 0) $('#'+error[i][0]).focus(); //set focus on the first error
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

function export_list(){
    if( ( $('#date_start').val() == "" ) || ( $('#date_end').val() == "" ) ){
        add_error('date', 'Date Period', "This field is mandatory.");
    }

    ok_to_save = validate_form();

    if(ok_to_save){
         $('#export-form').attr('action', $('#export_link').val());
         $('#export-form').submit();
        $('#export-form').attr('action', '');
    }
}

function generate_list(){

    if( ( $('#date_start').val() == "" ) || ( $('#date_end').val() == "" ) ){
        add_error('date', 'Date Period', "This field is mandatory.");
        dynamic_switch();       
    }
    ok_to_save = validate_form();

    if( ok_to_save ){
        $('#export-form').hasClass('export-search');
        list_search_grid( 'jqgridcontainer' );
        $('#export-form').removeClass('export-search');
        
        dynamic_switch();

        return false;
    }
}

function dynamic_switch(){
    var category1 = $('#category1').val();
    if(category1 == 7) category1 = 1;
    var col_pos = parseFloat(category1) + 2;
    var column_name = $('#jqgridcontainer').getGridParam("colModel")[col_pos].name
    if (category1 != 1){
        if ($('input[name="dynamic"]:checked').val()){
            hide_display_column(column_name);
        }
        else{
            $('#jqgridcontainer').jqGrid('showCol', 'hours_worked');
            $('#jqgridcontainer').jqGrid('showCol', 'absent');
            $('#jqgridcontainer').jqGrid('showCol', 'lates');
            $('#jqgridcontainer').jqGrid('showCol', 'overtime');
            $('#jqgridcontainer').jqGrid('showCol', 'undertime');
        }
    }
    else{
        $('#jqgridcontainer').jqGrid('showCol', 'hours_worked');
        $('#jqgridcontainer').jqGrid('showCol', 'absent');
        $('#jqgridcontainer').jqGrid('showCol', 'lates');
        $('#jqgridcontainer').jqGrid('showCol', 'overtime');
        $('#jqgridcontainer').jqGrid('showCol', 'undertime');
    }
    $('#jqgridcontainer').setGridWidth(1094, true);
}

function hide_display_column(column_name){
    $('#jqgridcontainer').jqGrid('hideCol', 'hours_worked');
    $('#jqgridcontainer').jqGrid('hideCol', 'absent');
    $('#jqgridcontainer').jqGrid('hideCol', 'lates');
    $('#jqgridcontainer').jqGrid('hideCol', 'overtime');
    $('#jqgridcontainer').jqGrid('hideCol', 'undertime');

    if (column_name == "Hours Worked"){
        column_name = "hours_worked";
    }

    $('#jqgridcontainer').jqGrid('showCol', column_name);      
}

function list_search_grid( jqgridcontainer ){
    $("#"+jqgridcontainer).jqGrid('setGridParam', 
    {
        postData: null
    });

    $("#"+jqgridcontainer).jqGrid('setGridParam', 
    {
        url: module.get_value('base_url') + module.get_value('module_link') + '/listview',
        datatype: 'json',
        search: true,
        postData: {
            category : $('#category').val(),
            category1 : $('#category1').val(),
            department : $('#department').val(),
            company : $('#company').val(),
            division : $('#division').val(),
            employee : $('#employee').val(), 
            section : $('#section').val(),           
            dynamic : $('#dynamic').val(),
            dateStart : $('#date_start').val(),
            dateEnd : $('#date_end').val(),
        },  
    }).trigger("reloadGrid");

	if(user.get_value('post_control') != 1){
		$('#jqgridcontainer').jqGrid('hideCol', 'employee_name'); 
	}

}