$(document).ready(function () {

    $('.add_row').live('click',function() {
      var elem = $(this);
      var get_form_url = module.get_value('base_url') + module.get_value('module_link') + '/get_form/';
      $.ajax({
            url: get_form_url,
            dataType: 'html',
            type:"POST",
           /* data:'type='+$(this).attr('rel')+'&count='+$(".count").val(),*/
            success: function (response) {

              $("#competency_div").before(response);
                 
            }
        });    
    });

    $('.delete_row').live('click',function(){
        var elem = $(this);
        var competency_value = $(elem).parents('.competency_values').find('.competency').attr('competency-value');

        if (module.get_value('record_id') != -1 && competency_value != 0) {
          $(elem).parents('.competency_values').find('.competency').attr('name', 'del_competency['+competency_value+']');
          $(elem).parents('.competency_values').hide();
        }else{
          $(elem).parents('.competency_values').remove();
        };


    });



});


function ajax_save( on_success, is_wizard , callback ){
  if( is_wizard == 1 ){
    var current = $('.current-wizard');
    var fg_id = current.attr('fg_id');
    var ok_to_save = eval('validate_fg'+fg_id+'()')
  }
  else{

/*  var flag_error = false;
  var name_field = '';
    $('div.competency-div').find('.competency_name').each(function (index, element){

          if ($(element).val() == "") {
            flag_error = true;
            name_field = 'Competency';
          };

    });

    $('div.competency-div').find('.competency_level').each(function (index, element){

          if ($(element).val() == "") {
            flag_error = true;
            name_field = 'Level';
          };

    });

    $('div.competency-div').find('.competency_level_description').each(function (index, element){

          if ($(element).val() == "") {
            flag_error = true;
            name_field = 'Description';
          };

    });

     if (flag_error) {
        add_error('competencies[]', name_field, "This field is mandatory.");
      };*/

    ok_to_save = validate_form();
  }
  
  if( ok_to_save ) {    
    $('#record-form').find('.chzn-done').each(function (index, elem) {
      if (elem.multiple) {
        if ($(elem).attr('name') != $(elem).attr('id') + '[]') {
          $(elem).attr('name', $(elem).attr('name') + '[]');
        }
        
        var values = new Array();
        for(var i=0; i< elem.options.length; i++) {
          if(elem.options[i].selected == true) {
            values[values.length] = elem.options[i].value;
          }
        }
        $(elem).val(values);
      }
    });

    var data = $('#record-form').serialize();
    var saveUrl = module.get_value('base_url')+module.get_value('module_link')+"/ajax_save"   

    $.ajax({
      url: saveUrl,
      type:"POST",
      data: data,
      dataType: "json",
      /**async: false, // Removed because loading box is not displayed when set to false **/
      beforeSend: function(){
          show_saving_blockui();
      },
      success: function(data){
        if(  data.record_id != null ){
          //check if new record, update record_id
          if($('#record_id').val() == -1 && data.record_id != ""){
            $('#record_id').val(data.record_id);
            $('#record_id').trigger('change');
            if( is_wizard == 1 ) window.location = module.get_value('base_url') + module.get_value('module_link') + '/edit/' + data.record_id;
          }
          else{
            $('#record_id').val( data.record_id );
          }
        }

        if( data.msg_type != "error"){          
          switch( on_success ){
            case 'back':
              go_to_previous_page( data.msg );
              break;
            case 'email':             
              if (data.record_id > 0 && data.record_id != '') {
                // Ajax request to send email.                    
                $.ajax({
                  url: module.get_value('base_url') + module.get_value('module_link') + '/send_email',
                  data: 'record_id=' + data.record_id,
                  dataType: 'json',
                  type: 'post',
                  async: false,
                  beforeSend: function(){
                      show_saving_blockui();
                    },                
                  success: function () {
                  }
                });
              }             
              //custom ajax save callback
              if (typeof(callback) == typeof(Function)) callback( data );
            default:
              if (typeof data.page_refresh != 'undefined' && data.page_refresh == "true"){
                  window.location = module.get_value('base_url') + module.get_value('module_link') + '/edit/' + data.record_id;
              }
              else{
                //generic ajax save callback
                if(typeof window.ajax_save_callback == 'function') ajax_save_callback();
                //custom ajax save callback
                if (typeof(callback) == typeof(Function)) callback( data );
                $.unblockUI({ onUnblock: function() { message_growl(data.msg_type, data.msg) } });
                window.location = module.get_value('base_url') + module.get_value('module_link') + '/edit/' + data.record_id;
              }
              break;
          } 
        }
        else{
          $.unblockUI({ onUnblock: function() { message_growl(data.msg_type, data.msg) } });
        }
      }
    });
  }
  else{
    return false;
  }
  return true;
}