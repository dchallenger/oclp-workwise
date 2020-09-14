$( document ).ready( function() {

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
});

function export_list(){
    ok_to_save = 1;

    if ($('#employee').val() == "" || $('#employee').val() == undefined) {
        var error_str = 'Please Select Category.';
        $('#message-container').html(message_growl('error', error_str)); 
        ok_to_save = 0;
    };

    if(ok_to_save){        
        $('#export-form').attr('action', $('#export_link').val());
        $('#export-form').submit();
        $('#export-form').attr('action', '');
    }
}