$(document).ready(function() {
	var get_form_url = module.get_value('base_url') + module.get_value('module_link') + '/get_form/';
	// var count = ;
	$('.add-more').live('click',function(event){
		event.preventDefault();
		var this_item = $(this);

		if ($(this).attr('rel') == "competency") {
			$.ajax({
		        url: get_form_url,
		        dataType: 'html',
		        type:"POST",
		        data:'type='+$(this).attr('rel')+'&count='+$(".count").val(),
		        success: function (response) {

		        	$('.form-multiple-add-competency-group fieldset.competency').append(response);
		        		 
		        }
		    });
			$(".count").val(parseInt($(".count").val())+1);

		}else if($(this).attr('rel') == "level"){


			$.ajax({
		        url: get_form_url,
		        dataType: 'html',
		        type:"POST",
		        data:'type='+$(this).attr('rel')+'&rand='+this_item.attr('count'),
		        success: function (response) {
                $("#competency-div").before(response);
		        	//this_item.parents('div.competency-div').find('div.form-multiple-add-level-group fieldset.level').append(response);
             
		        }
		    });
      	
       	};

       
	});
  if (module.get_value('view') == "edit"){
    get_values ($("#appraisal_competency_master_id").val(), $("#appraisal_competency_value_id").val());
  } 
  
  $("#appraisal_competency_master_id").live('change', function() {
    var master_id = $(this).val();
      get_values (master_id, 0)
  });

	$('a.delete-detail').live('click', function () {
		$(this).parents('div.competency-div').remove();
		if (parseInt($(".count").val()) != 0) {
			$(".count").val(parseInt($(".count").val())-1);
		};
		
	});
	
	$('a.delete-level').live('click', function () {
		var competency_level = $(this).parents('div.form-multiple-add-level').find('.competency_level').attr('competency-level');

    if (module.get_value('record_id') != -1 && competency_level != 0) {
        $(this).parents('div.form-multiple-add-level').find('.competency_level').attr('name', 'del_competency['+competency_level+']');
        $(this).parents('div.form-multiple-add-level').hide();
          
        }else{
          $(this).parents('div.form-multiple-add-level').remove();
        };

	});
})


function get_values (master_id, value_id) {
  $.ajax({
          url: module.get_value('base_url') + module.get_value('module_link') + '/get_values/',
          dataType: 'html',
          type:"POST",
          data:'master_id='+master_id+'&value_id='+value_id,
          beforeSend:function  (argument) {
            $.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>' });
      
          },
          success: function (response) {
            $.unblockUI();
            $('#appraisal_competency_value_id').html(response);
            $('#appraisal_competency_value_id').chosen().trigger("liszt:updated");
          }
      });
}
function ajax_save( on_success, is_wizard , callback ){
  if( is_wizard == 1 ){
    var current = $('.current-wizard');
    var fg_id = current.attr('fg_id');
    var ok_to_save = eval('validate_fg'+fg_id+'()')
  }
  else{

	var flag_error = false;
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
      };

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