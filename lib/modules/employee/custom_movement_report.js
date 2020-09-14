$(document).ready(function(){
    window.onload = function(){
        $(".multi-select").multiselect({
            show:['blind',250],
            hide:['blind',250],
            selectedList: 1
        });
    }

    $('#category').live('change',function(){     
        var category_id = $(this).val();
        var category = $("#category option:selected").data("alias");
        var category_for_id = $("#category option:selected").data("aliasid");

        if (category_id > 0){
            var eleid = category_for_id.toLowerCase()   

            $.ajax({
                url: module.get_value('base_url') + module.get_value('module_link') + '/populate_category',
                data: 'category_id=' + category_id,
                dataType: 'html',
                type: 'post',
                async: false,
                beforeSend: function(){
                    $('#multi-select-loader2').html('');                    
                    $('#multi-select-main-container2').hide();

                    $('#multi-select-main-container').hide();
                    $('#multi-select-loader').html('<div><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif" style="vertical-align:middle"><span style="padding-left:10px">Loading, please wait...</span></div>');                
                },                              
                success: function ( response ) {
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

            if (category_id != 7) {
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
                
            }else{
                $('#multi-select-loader2').html('');                    
                $('#multi-select-main-container2').hide();
            }
            // $('#employment_status_container').show();
            // $('#employee_type_container').show();
        }
        else{
            $('#multi-select-main-container').hide();
            $('#category_selected').html('');
            $('#multi-select-container').html('');          
        }   
    });  

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

        }
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
    var report_by = $('select[name="category"]').val();
    var employee_id = $('#employee').val();
    var year = $('#year').val();
    var date_from = $('input[name="date_from"]').val();
    var date_to = $('input[name="date_to"]').val();

    if(date_from == undefined || date_from == ""){
        add_error('date_from', 'Date From', "This field is mandatory.");
    }

    if(date_to == undefined || date_to == ""){
        add_error('date_from', 'Date From', "This field is mandatory.");
    }

    if ($('#employee').val() == "" || $('#employee').val() == undefined) {
        add_error('employee', 'Employee', "This field is mandatory.");
    };

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

    $('#employee_id_multiple').val(employee_id);
    $("#report-form").submit();

}
function date_validation(date_from,date_to)
    {
        parse_date_from = date_from;
        parse_date_to   = date_to;

        if (isNaN(Date.parse(date_from))) {
            parse_date_from = date_from + ' 1';
        }

        if (isNaN(Date.parse(date_to))) {
            parse_date_to = date_to + ' 1';
        } 

        if (Date.parse(parse_date_from) > Date.parse(parse_date_to)) 
        {
             message_growl("error","Invalid Date Range!\nStart Date cannot be after End Date!")
        }
        else
        {
            return 1;
        }
    }