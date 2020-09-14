$(document).ready(function(){
	// $( 'input[name="effectivity_from"]' ).change( function(){

	// });

 //    $( 'input[name="effectivity_to"]' ).change( function(){
 //        alert($( 'input[name="effectivity_to"]' ).val());
 //        list_search_grid( 'jqgridcontainer' );
 //    });

    $('#effectivity_from').datepicker({
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
        dateFormat: 'mm/dd/yy',
        onSelect: function(selected) {
           $("#effectivity_to").datepicker("option","minDate", selected);
           if($( 'input[name="effectivity_from"]' ).val() == '' || $( 'input[name="effectivity_to"]' ).val() == '') {
            // message_growl('info','Effectivity to is empty!');
           } else {
              list_search_grid( 'jqgridcontainer' );
           }
        }
    }); 

    $('#effectivity_to').datepicker({
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
        dateFormat: 'mm/dd/yy',
        onSelect: function(selected) {
           $("#effectivity_from").datepicker("option","maxDate", selected);
           if($( 'input[name="effectivity_from"]' ).val() == '' || $( 'input[name="effectivity_to"]' ).val() == '') {
            message_growl('info','Effectivity from is empty!');
           } else {
              list_search_grid( 'jqgridcontainer' );
           }
        }
    }); 

    $('.module-export-employees').live('click', function () {
        $('#search_hidden').val($('#search').val());
        $('#export-form').attr('action', $('#export_link').val());
        $('#export-form').submit();
        $('#export-form').attr('action', '');   
    });
});

function list_search_grid( jqgridcontainer ){
	
    $("#"+jqgridcontainer).jqGrid('setGridParam', 
    {
        url: module.get_value('base_url') + module.get_value('module_link') + '/listview',
        datatype: 'json',
        postData: {
            effectivity_from : $('#effectivity_from').val(),
            effectivity_to : $('#effectivity_to').val(),
        },  
    }).trigger("reloadGrid");

}