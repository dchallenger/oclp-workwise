<div class="form-submit-btn">
  <div class="icon-label-group">
	<?php $show_or = false; 
  ?>
  <?php if($this->user_access[$this->module_id]['print'] == 1 && method_exists(get_instance(), 'print_record')):
      $show_or = true; ?>
        <div class="icon-label">
            <a href="javascript:void(0);" onclick="print()" class="icon-16-print">
                <span>Print</span>
            </a>
        </div>
  <?php endif; ?>
  <?php if( $this->user_access[$this->module_id]['edit'] == 1 && ( $employee_id == $this->user->user_id ) && ( $status == 1 || $status == -1  ) ): ?>
        <div class="icon-label">
            <a class="icon-16-edit" href="javascript:void(0);" onclick="edit()">
                <span>Edit</span>
            </a>            
        </div>
  <?php endif; ?>
  <?php if( $this->user_access[$this->module_id]['approve'] == 1 && ( in_array($this->userinfo['position_id'], $approver) ) && ( $status == 2  ) ):?>
          <div class="icon-label">
              <a class="icon-16-approve" href="javascript:void(0);" onclick="approve_intent('back', <?php echo $show_wizard_control ? 1 : 0 ?>)">
                  <span>Approve</span>
              </a>            
          </div>
    <?php endif; ?>
    <?php if($this->user_access[$this->module_id]['decline'] == 1 && ( in_array($this->userinfo['position_id'], $approver) ) && ( $status == 2  )):
      $show_or = true;?>
          <div class="icon-label">
              <a class="icon-16-cancel" href="javascript:void(0);" onclick="decline_intent('back', <?php echo $show_wizard_control ? 1 : 0 ?>)">
                  <span>Decline</span>
              </a>            
          </div>
    <?php endif; ?>
    <div class="icon-label">
          <a rel="back-to-list" class="icon-16-listback" href="javascript:void(0);">
              <span>Back to list</span>
          </a>
      </div>
    </div>
</div>

<script>
  <?php if($this->user_access[$this->module_id]['approve'] == 1  && $status == 2 ) : ?>
    function approve_intent( on_success, is_wizard , callback ){
       Boxy.ask("Are you sure you want to continue?", ["Yes", "No"],function( choice ) {
        if(choice == "Yes"){
                 $('form#record-form').append('<input type="hidden" name="approve" value="true" />');
              ajax_save( on_success, is_wizard , callback );
            }
        },
        {
            title: "Approve Letter Of Intent"
        });

    }
  <?php endif?>

  <?php if($this->user_access[$this->module_id]['decline'] == 1  && $status == 2 ) : ?>
    function decline_intent( on_success, is_wizard , callback ){
       Boxy.ask("Are you sure you want to continue?", ["Yes", "No"],function( choice ) {
        if(choice == "Yes"){
                 $('form#record-form').append('<input type="hidden" name="decline" value="true" />');
              ajax_save( on_success, is_wizard , callback );
            }
        },
        {
            title: "Decline Letter Of Intent"
        });

    }
  <?php endif?>

  if( module.get_value('view') == 'detail' ){


        function ajax_save( on_success, is_wizard , callback ){

            if( is_wizard == 1 ){
                var current = $('.current-wizard');
                var fg_id = current.attr('fg_id');
                var ok_to_save = eval('validate_fg'+fg_id+'()')
            }
            else{
                //ok_to_save = validate_form();
                ok_to_save = true;
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
                        if( data.msg_type != "error" && data.record_id != null ){                   
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
                                        //check if new record, update record_id
                                        if($('#record_id').val() == -1 && data.record_id != ""){
                                            $('#record_id').val(data.record_id);
                                            $('#record_id').trigger('change');
                                            if( is_wizard == 1 ) window.location = module.get_value('base_url') + module.get_value('module_link') + '/edit/' + data.record_id;
                                        }
                                        else{
                                            $('#record_id').val( data.record_id );
                                        }
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
    }
</script>