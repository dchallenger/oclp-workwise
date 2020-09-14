$(document).ready(function () {

	if (module.get_value('view') == "edit"){
	// $('select[name="employee_id"]').chosen();
	// $('#employees').hide();
	
	// $('#employee_appraisal_criteria_id').live('change', function () {
		if ($('#record_id').val() != "-1") {
			$.ajax({
				url: module.get_value('base_url') +  module.get_value('module_link') + '/get_header',
				dataType: 'json',
				type: 'POST',
				data: 'header='+$('#employee_appraisal_criteria_question_header_id').val(),
				beforeSend: function() {
					$.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>' });
				},
				success: function(response) {
					$.unblockUI();
					var html = "<input type='hidden' id='header_id' name='header_id' value='"+$('#employee_appraisal_criteria_question_header_id').val()+"'>";
					$('#employee_appraisal_criteria_question_header_id').parent().append(html);
					$('#employee_appraisal_criteria_question_header_id').val(response.header);
					$('#percentage').val(response.percent);
				}
			});
		};
	// });


	$('input[name="percentage"]').addClass('input-medium');
	$('.question_percentage').live('keydown', numeric_only);

	var percent = $('input[name="percentage"]').val();

	if (percent == "") {
		$('input[name="distribution"]').attr('disabled','disabled');
	};
	
	$('input[name="percentage"]').keydown(function (){
		 	$('input[name="distribution"]').attr('disabled', false);
	});

	if ($('#record_id').val() != "-1") {
		if ($('#distribution').val() == 1) {
			$('.question_percentage').attr('readonly', true);
		};
	
	}
	
	$('input[name="distribution"]').click(function () {
		if ($(this).val() == 0) {
			$('.question_percentage').attr('readonly', false);
		}else{
			var qpercent = 100 / parseInt($('#counter').val());
			// var qpercent = parseFloat($('input[name="percentage"]').val()) / $('#counter').val();
			$('input[name="percentage"]').attr('readonly', false);
			$('.question_percentage').attr('readonly', true);
			if (parseFloat($('input[name="percentage"]').val()) > 100) {
				message_growl('error', 'Percentage - This field should be less than or equal to 100');
			}else{
				$('.question_percentage').val(qpercent);
				$('.question_percentage_temp').val(qpercent.toFixed(2));
			}
			
		};
	});

		$('.add_row').live('click',function() {

			var elem = $(this);
			var id = $(elem).attr("columnid");
			var cnt = $('#counter').val();
			$('.delete_row').show();
			var html = '<div class="form-item odd">'+
						'<label class="label-desc gray" for="question">Question:<span class="red font-large">*</span>'+
						'<div class="text-input-wrap">'+
							'<input type="text" class="input-text question input-medium" value="" name="question[]" question="0">'+
							'<span style="vertical-align: middle;" class="del-button">'+
								'<a tooltip="Add row" href="javascript:void(0)" class="icon-16-add icon-button add_row" columnid="'+(parseInt(id)+1)+'" question="q"></a>'+
								'<a class="icon-16-delete icon-button delete_row" href="javascript:void(0)" original-title=""></a>'+
							'</span>'+
						'</div>'+
						'<div class="percent">'+
							'<label class="label-desc gray" for="tooltip">Question Percentage:</label>'+
							'<div class="textarea-input-wrap">'+
								'<input type="hidden" class="input-text question_percentage " Style="width:30%" value="" name="question_percentage[]" question_percentage="0" >'+
								'<input type="text" class="input-text question_percentage_temp " Style="width:30%" value="" name="question_percentage_temp[]" question_percentage="0" >'+
							'</div>'+
						'</div>'+
						'<label class="label-desc gray" for="tooltip">Tooltip/Description:</label>'+
						'<div class="textarea-input-wrap">'+
							'<textarea class="input-textarea tooltip" name="tooltip[]" tabindex="4" rows="5"></textarea>'+
						'</div>'+
						'</div>';

			$(elem).parent().parent().parent().after(html);
			var counter = parseInt(cnt)+1;
			$('#counter').val(counter);
			var percentage = $('input[name="percentage"]').val();
			// var qp = parseFloat(percentage) / counter;
			var qp = 100 / counter;
			if (parseFloat($('input[name="percentage"]').val()) > 100) {
				message_growl('error', 'Percentage - This field should be less than or equal to 100');
			}else{
				if (percentage != "" && $('#distribution-yes').attr('checked') == 'checked') {
					$('.question_percentage').val(qp);	
					$('.question_percentage_temp').val(qp.toFixed(2));	
				};
			}
			
		});
		
		$('.delete_row').live('click',function(){
				var elem = $(this);
				var cnt = $('#counter').val();
				var que = $(elem).parent().parent().find('.question').attr('question');
				

				if (module.get_value('record_id') != -1 && que != 0) {
					$(elem).parent().parent().find('.question').attr('name', 'del_question['+que+']');

					$(elem).parent().parent().next().find('.question_percentage').attr('name', 'del_question_percentage['+que+']');
					$(elem).parent().parent().next().find('.question_percentage_temp').attr('name', 'del_question_percentage_temp['+que+']');
					$(elem).parent().parent().parent().hide();
				}else{
					$(elem).parent().parent().parent().remove();
				}

		var counter = parseInt(cnt)-1;
		$('#counter').val(counter);
		var percentage = $('input[name="percentage"]').val();
			// var qp = parseFloat(percentage) / counter;
			var qp = 100 / counter;
			if (parseFloat($('input[name="percentage"]').val()) > 100) {
				message_growl('error', 'Percentage - This field should be less than or equal to 100');
			}else{
				if (percentage != "" && $('#distribution-yes').attr('checked') == 'checked') {
					$('.question_percentage').val(qp);	
					$('.question_percentage_temp').val(qp.toFixed(2));	
				};
			}

		if ($('#counter').val() == 1) {
			$('.delete_row').hide();
		};

		});


	}

});

function ajax_save( on_success, is_wizard , callback ){
  if( is_wizard == 1 ){
    var current = $('.current-wizard');
    var fg_id = current.attr('fg_id');
    var ok_to_save = eval('validate_fg'+fg_id+'()')
  }
  else{

	var question_error = false;
    $('input[name="question[]"]').each(function (index, element){

          if ($(element).val() == "") {
            question_error = true;
          };

    });

     if (question_error) {
        add_error('question[]', 'Question', "This field is mandatory.");
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