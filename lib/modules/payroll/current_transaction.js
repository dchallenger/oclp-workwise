$(document).ready(function(){
	$(document).ready(function(){
        $('.icon-16-add-listview').die().live('click', function () {edit_transaction('-1');});
        $('.icon-16-edit').die().live('click', function () {edit_transaction($(this).parents('tr').attr('id'));});

    });


    if (module.get_value('view') == 'index') {
		$('form#list-filter select[name="employee_id"], form#list-filter select[name="processing_type_id"]').chosen();
		$('form#list-filter select[name="processing_type_id"]').change(update_employee_ddlb);
		$('form#list-filter select[name="employee_id"]').change(filter_grid);
        $('form#list-filter select[name="processing_type_id"]').trigger('change');

        $('form#current_transaction-quick-edit-form select#employee_id').die().live('change', get_unit_rate);
        $('form#current_transaction-quick-edit-form select#transaction_id').die().live('change', get_unit_rate);
        $('form#current_transaction-quick-edit-form input#quantity').die().live('change', calc_amount);
        $('form#current_transaction-quick-edit-form input#unit_rate').die().live('change', calc_amount);
	}
});

function update_employee_ddlb(){
    $.ajax({
        url: module.get_value('base_url') + module.get_value('module_link') + '/update_employee_ddlb',
        type:"POST",
        data: 'processing_type_id=' + $('select[name="processing_type_id"]').val(),
        dataType: "json",
        beforeSend: function(){
           
        },
        success: function(data){
            $('form#list-filter select[name="employee_id"] option').remove();
            $('form#list-filter select[name="employee_id"]').append(data.option);
            $('form#list-filter select[name="employee_id"]').trigger("liszt:updated"); 
            filter_grid();
        }
    });
}

function get_unit_rate(){
    var employee_id = $('form#current_transaction-quick-edit-form select#employee_id').val();
    var transaction_id = $('form#current_transaction-quick-edit-form select#transaction_id').val();
    employee_id = $.trim(employee_id);
    transaction_id = $.trim(transaction_id);
    if( employee_id != "" && transaction_id != "" ){
        $.ajax({
            url: module.get_value('base_url') + module.get_value('module_link') + '/get_unit_rate',
            type:"POST",
            data: 'employee_id=' + employee_id+'&transaction_id='+transaction_id,
            dataType: "json",
            beforeSend: function(){
               
            },
            success: function(data){
                $('form#current_transaction-quick-edit-form input#unit_rate').val( addCommas(data.unit_rate) ); 
                calc_amount();
            }
        });
    }
}

function calc_amount(){
    var quantity = $('form#current_transaction-quick-edit-form input#quantity').val();
    var unit_rate = $('form#current_transaction-quick-edit-form input#unit_rate').val();

    if( quantity != "" && unit_rate != "" ){
        quantity = quantity.replace(/\,/g,'');
        unit_rate = unit_rate.replace(/\,/g,'');

        var amount = Number(quantity) * Number(unit_rate);
        $('form#current_transaction-quick-edit-form input#amount').val(addCommas(amount));
    }
    else{
        $('form#current_transaction-quick-edit-form input#amount').val("");  
    }

}



function edit_transaction(record_id){
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
                quickedit_boxy = new Boxy('<div id="boxyhtml">'+ data.quickedit_form +'</div>',
                {
                    title: 'Quick Add/Edit',
                    draggable: false,
                    modal: true,
                    center: true,
                    unloadOnHide: true,
                    beforeUnload: function (){
                        $('.tipsy').remove();
                    },          
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

function filter_grid()
{
    var jqgridcontainer = 'jqgridcontainer';
    var searchfield;
    var searchop;
    var searchstring = $('.search-'+ jqgridcontainer ).val() != "Search..." ? $('.search-'+ jqgridcontainer ).val() : "";
    
    var processing_type_id = $('select[name="processing_type_id"]').val();
    var employee_id = $('select[name="employee_id"]').val();
    
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
            processing_type_id: processing_type_id,
            employee_id: employee_id
        },  
    }).trigger("reloadGrid");   
}