$(document).ready(function(){
    $('select[name="period_processing_type_id"]').change(filter_grid);
    $('select[name="company_id"]').change(filter_grid);
    $('input[name="payroll_date-temp"]').change(filter_grid);
});

function filter_grid()
{
    var processing_type_id = $('select[name="period_processing_type_id"]').val();
    var company_id = $('select[name="company_id"]').val();
    var payroll_date = $('input[name="payroll_date"]').val();
    
    if(processing_type_id == '' || company_id == '' || payroll_date == '') return false;

    $("#jqgridcontainer").jqGrid('clearGridData', { clearfooter: true});
    $("#jqgridcontainer").GridUnload();
    $("#jqgridcontainer").jqGrid({
        url: module.get_value('base_url') + module.get_value('module_link') + '/listview',
        loadtext: '<img src="'+ module.get_value('base_url') + user.get_value('user_theme') + '/images/loading.gif"><br />Loading...',
        datatype: "json",
        mtype: "POST",
        rowNum: -1,            
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
        colNames:["id", "Transaction Code", "Transaction", "Account Code", "Account Name", "Credit","Debit"],
        colModel:[
            {name: 'id', hidden: true},
            {name: 'transaction_code', align: 'left', sortable:false, width:'100px'},
            {name: 'transaction', align: 'left', sortable:false, width:'100px'},
            {name: 'account_code', align: 'left', sortable:false, width:'100px'},
            {name: 'account', align: 'left', sortable:false, width:'100px'},
            {name: 'credit', align: 'right', sortable:false, width:'100px'},
            {name: 'debit', align: 'right', sortable:false, width:'100px'},
        ],
        postData: {
            processing_type_id: processing_type_id,
            company_id: company_id,
            payroll_date: payroll_date
        },
    });   
}