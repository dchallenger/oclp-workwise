$(document).ready(function() {

    $('#employee_id').val(user.get_value('user_id'));
    $('#employee_id-name').val(user.get_value('nicename'));

    $('input[name="employee_id"]').change(function(){    
        if($('#date').val() != '' && $('#employee_id').val() != '')
            generate_date_used();
    });

    $('input[name="date-temp"]').change(function(){
        if($('#date').val() != '' && $('input[name="employee_id"]').val() != '')
            generate_date_used();
    });

     window.onload = function(){

         if( $('ul#grid-filter li').length > 0 ){  

            $('ul#grid-filter li').each(function(){ 

                if( $(this).hasClass('active') ){

                    if($(this).attr('filter') == 'for_approval'){
                       $('.status-buttons').parent().show();
                    }
                    else{
                       $('.status-buttons').parent().hide();
                    }

                }
            });

        }
        else{
            $('.status-buttons').parent().hide();
        }
    }

    $('.icon-16-approve').live('click', function () {
        if(!$(this).hasClass('status-buttons')){
          change_status($(this).parents('tr').attr('id'), 'approve');
        }
        else{


              var selected = $("#"+$(this).attr('container')).jqGrid("getGridParam", "selarrrow");

              if(selected.length > 0)
              {
                  if(selected[0] == '')
                  {
                      //remove the value for "check all"
                      selected.shift();
                  }
                  approve_record(selected, $(this).attr('module_link'), $(this).attr('container'));
              }
              else{
                  $('#message-container').html(message_growl('attention', 'No record was selected!'))
              }


        }
    });


    $('.icon-16-disapprove').live('click', function () {
        if(!$(this).hasClass('status-buttons')){
          change_status($(this).parents('tr').attr('id'), 'decline');
        }
        else{


                var selected = $("#"+$(this).attr('container')).jqGrid("getGridParam", "selarrrow");

                if(selected.length > 0)
                {
                    if(selected[0] == '')
                    {
                        //remove the value for "check all"
                        selected.shift();
                    }
                    disapprove_record(selected, $(this).attr('module_link'), $(this).attr('container'));
                }
                else{
                    $('#message-container').html(message_growl('attention', 'No record was selected!'))
                }

        }
    }); 

    if(user.get_value('view') == 'edit')
    {
        $('input[name="datetime_from"]').live('change', function(){

            var date = new Date($('input[name="datetime_from"]').val());

            date.setHours(date.getHours() + 9);

            var d = date.getDate();
            var m = date.getMonth()+1;
            var y = date.getFullYear();
            var h = date.getHours();
            var mnt = date.getMinutes();
            var ap = 'am';

            if(d < 10) 
                d = '0'+d;
            if(m < 10) 
                m = '0'+m;
            if(mnt < 10) 
                mnt = '0'+mnt;
            if(h < 10) 
                h = '0'+h;

            if(h > 12)
            {
                h = h - 12;
                ap = 'pm';
            }

            $('input[name="datetime_to"]').val(m+'/'+d+'/'+y+' '+h+':'+mnt+' '+ap);

        });

        if ( user.get_value('post_control') != 1 || $('#filter').val() == "personal" ) {
            $('input[name="employee_id"]').siblings('span.icon-group').remove();
        }
    }

});

function init_filter_tabs(){
    $('ul#grid-filter li').click(function(){
        $('ul#grid-filter li').each(function(){ $(this).removeClass('active') });
        $(this).addClass('active');

        if( $(this).attr('filter') == 'for_approval' ){
            $('.status-buttons').parent().show();
        }
        else{
            $('.status-buttons').parent().hide();
        }

        filter_memo( 'jqgridcontainer', $(this).attr('filter') );
    });
    //changes for edit button
    if($('ul#grid-filter #approve_tab').hasClass('active'))
    {
        $('.icon-16-edit').remove();
        $("#jqgridcontainer").jqGrid().trigger("reloadGrid");
        globaltag=true;
    }
    else globaltag=false;
    //changes for edit button
}

function filter_memo( jqgridcontainer, filter )
{
    var searchfield;
    var searchop;
    var searchstring = $('.search-'+ jqgridcontainer ).val() != "Search..." ? $('.search-'+ jqgridcontainer ).val() : "";
            
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
            filter: filter
        },  
    }).trigger("reloadGrid");   
}

function change_status(id, status) {
    $.ajax({
        url: module.get_value('base_url') + module.get_value('module_link') + '/change_status',
        data: 'status=' + status + '&record_id=' + id,
        type: 'post',
        success: function (response) {          
            $('#message-container').html(message_growl(response.msg_type, response.msg));
            $("#jqgridcontainer").jqGrid().trigger("reloadGrid");
        }
    });
}

function generate_date_used()
{
    var data = $('#date, #employee_id').serialize();

    $.ajax({
        url: module.get_value('base_url')+module.get_value('module_link')+'/generate_date_used',
        data: data,
        type: 'post',
        success: function (response) {
            if(response.msg_type == 'error')
                $('#message-container').html(message_growl(response.msg_type, response.msg));
            else
                $('#date_used').val(response.date_used);
        }
    })
}

// $(document).ready(function(){
    
// });