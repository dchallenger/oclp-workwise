$( document ).ready( function() {
    init_datepick();

    var lastsel;
    $("#jqgridcontainer").jqGrid({
        url: module.get_value('base_url') + module.get_value('module_link') + '/listview',
        loadtext: '<img src="'+ module.get_value('base_url') + user.get_value('user_theme') + '/images/loading.gif"><br />Loading...',
        datatype: "json",
        mtype: "POST",
        rowNum: 25,
        rowList: [10,15,25, 40, 60, 85, 100],
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
        colNames:["Agent Name","date","Time In","Time Out","Biometrics","Dialed Hours","Difference","Remarks","OE for Discrepancy"],
        colModel:[{name : 'agent_name',width : '180',align : 'center'},{name : 'date'},{name : 'time_in'},{name : 'time_out'},{name : 'biometrics'},{name : 'dialed_hours'},{name : 'difference'},{name : 'remarks',editable:true},{name : 'oe_for_discrepancy',editable:true}],
        loadComplete: function(data){
            post_gridcomplete_function(data, '#jqgridcontainer');
        },
        gridComplete:function(){
        },
        onSelectRow: function(id){ 
            if(id && id!==lastsel){ 
                $('#jqgridcontainer').jqGrid('restoreRow',lastsel); 
                $('#jqgridcontainer').jqGrid('editRow',id,true,null,null,null,{date:$('#date').val()}); 
                lastsel=id; 
            } 
        },
        ondblClickRow: function(rowid) {
            return;
        },          
        editurl: module.get_value('base_url') + module.get_value('module_link') + '/save_row',        
        caption: " List",
    });
    $("#jqgridcontainer").jqGrid('navGrid','#jqgridpager',{refresh:false, edit:false, add:false, del:false, search:false});
    $("#jqgridcontainer_toppager_center").hide();
    $("#jqgridpager_center").hide();
    //$("#t_jqgridcontainer").append($('.search-wrap').html());
    $(".search-trigger[tooltip]").tipsy({
        title: 'tooltip',
        gravity: 'se',
        opacity: 0.85,
        live: true,
        delayIn: 500
    });


    $("#date").change(function(){
        $('#date').val($('#date').val());
    });

    $('#campaign_id').chosen();

    // $('#date').live('change',function(){
    //     generate_list();
    // });

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
/*    var sortColumnName = $("#jqgridcontainer").jqGrid('getGridParam','sortname');
    var sortOrder = $("#jqgridcontainer").jqGrid('getGridParam','sortorder');
    if (sortColumnName != ''){
        $('#previous_page').append('<input id="sidx" type="hidden" value="'+ sortColumnName +'" name="sidx"><input id="sord" type="hidden" value="'+ sortOrder +'" name="sord">');
    }*/

    if( ( $('#date').val() == "" ) ){
        add_error('date', 'Date Period', "This field is mandatory.");      
    }
    ok_to_save = validate_form();

    if( ok_to_save ){      
        $('#export-form').attr('action', $('#export_link').val());
        $('#export-form').submit();
        $('#export-form').attr('action', '');
        return false;
    }
}

function generate_list() {

    if( ( $('#date').val() == "" ) ){
        add_error('date', 'Date From Period', "This field is mandatory.");
    }

    if($('#date_to').val() == "") {
        add_error('date_to', 'Date To Period', "This field is mandatory.");      
    }

    ok_to_save = validate_form();

    if( ok_to_save ){
        $('#export-form').hasClass('export-search');
        list_search_grid( 'jqgridcontainer' );
        $('#export-form').removeClass('export-search');
        return false;
    }
    
}

function list_search_grid( jqgridcontainer ){

    $("#"+jqgridcontainer).jqGrid('setGridParam', 
    {
        postData: {
            date : $('#date').val(),
            date_to : $('#date_to').val(),
            campaign_id : $('#campaign_id').val()
        },  
    }).trigger("reloadGrid");

}