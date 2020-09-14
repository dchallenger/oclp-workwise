var error = new Array();
var error_ctr = 0;
var colModel = [
    {name: 'row_id', hidden: true, key:true},
    {name: 'cost_code', sortable: false, width: 300, align: 'left'},
    {name: 'ot_hours', sortable: false, width: 200, align: 'center'},
    {name: 'ot_code', sortable: false, width: 80, align: 'center'},
    {name: 'ot_amt', sortable: false, width: 50, align: 'center'}
];

$(document).ready(function(){

    $( 'input[name="date_from"]' ).datepicker({
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
		yearRange: 'c-90:c+10',
		beforeShow: function(input, inst) {						
			
		},
		onClose: function(dateText) {

		},
        onSelect: function(selected) {
           $('input[name="date_to"]').datepicker("option","minDate", selected);
        }
	});

	$( 'input[name="date_to"]' ).datepicker({
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
		yearRange: 'c-90:c+10',
		beforeShow: function(input, inst) {						
			
		},
		onClose: function(dateText) {

		},
        onSelect: function(selected) {
           $('input[name="date_from"]').datepicker("option","maxDate", selected);
        }
	});

    $('.module-export-employees').live('click', function () {
        $('#search_hidden').val($('#search').val());
        $('#export-form').attr('action', $('#export_link').val());
        $('#export-form').submit();
        $('#export-form').attr('action', '');   
    });
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

function export_file()
{	 
    var date_from = $('input[name="date_from"]').val();
    var date_to = $('input[name="date_to"]').val();

    if(date_from != '' && date_to != '') {
        $('#misc_export').attr('action', $('#export_link').val());
        $('#misc_export').submit();
    } else {
       message_growl("error","Date is Mandatory!") 
    }

}

function date_validation(date_from,date_to)
{
    if(date_from == '' || date_to == '') {
        message_growl("error","Invalid Date Range!")
    } else {
        return 1;
    }
}

function get_data(){
    $("#jqgridcontainer").jqGrid('clearGridData', {	clearfooter: true});

    var date_from = $('input[name="date_from"]').val();
    var date_to = $('input[name="date_to"]').val();
    var colNames = ["ID", "CC", "Day/Hr/M", "TRANS. TYPE", "AMOUNT"];
    var validation_dates = date_validation($('input[name="date_from"]').val(), $('input[name="date_to"]').val());

	if(!validation_dates) {         
		error = new Array();
		error_ctr = 0
		return false;
	}

	$("#jqgridcontainer").jqGrid('clearGridData', {	clearfooter: true});
	$("#jqgridcontainer").GridUnload();
	$("#jqgridcontainer").jqGrid({
        url: module.get_value('base_url') + module.get_value('module_link') + '/get_report',
        loadtext: '<img src="'+ module.get_value('base_url') + user.get_value('user_theme') + '/images/loading.gif"><br />Loading...',
        datatype: "json",
        mtype: "POST",
        height: 'auto',
        autowidth: true,
        altRows: true,
        loadonce: true,
        forceFit: true,
        shrinkToFit: true,
        treeGrid: true,
        gridview: true,
        treeGridModel: 'adjacency',
        colNames:colNames,
        colModel:colModel,
        ExpandColumn : 'cost_code',
        pager: "#jqgridpager",
        pagerpos: 'right',
        viewrecords: true,
       	toppager: true,
       	postData: {
            date_from: date_from, 
            date_to: date_to
        }, 
        rowNum: 9999,
        loadComplete: expand_all,
        gridComplete: function(){ },
    });
}

function expand_all(){
	$(".treeclick").each(function() {
		if( $(this).hasClass("tree-minus") ) $(this).trigger("click");
	});
}


