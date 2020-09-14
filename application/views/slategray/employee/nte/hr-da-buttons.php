<div class="form-submit-btn">
  <div class="icon-label-group">
      <?php if( $this->user_access[$this->module_id]['edit'] == 1 ): ?>
        <div class="icon-label">
          <a rel="record-save-back" class="icon-16-notify" href="javascript:void(0);" onclick="give_da('back', <?php echo $show_wizard_control ? 1 : 0 ?>)">
              <span>For DA</span>
          </a>
        </div>
      <?php endif?>
      <?php if( $this->user_access[$this->module_id]['approve'] == 1 ): ?>
	      <div class="icon-label">
	        <a rel="record-save-back" class="icon-16-approve" href="javascript:void(0);" onclick="acquit('back', <?php echo $show_wizard_control ? 1 : 0 ?>)">
	            <span>Acquit</span>
	        </a>
	    </div>
	  <?php endif?>
      <div class="icon-label">
        <a rel="back-to-list" class="icon-16-listback" href="javascript:void(0);">
            <span>Back to list</span>
        </a>
    </div>
  </div>
  <!--
  <div class="or-cancel">
      <span class="or">or</span>
      <a class="cancel" href="javascript:void(0)" rel="action-back">Cancel</a>
  </div>
-->
</div>
<script>
	<?php if($this->user_access[$this->module_id]['approve'] == 1): ?>
		function acquit( on_success, is_wizard , callback ){

			
			 Boxy.ask("Are you sure you want to continue?", ["Yes", "No"],function( choice ) {
		        if(choice == "Yes"){
		               
		               var data = $('#record-form').serialize();
						var saveUrl = module.get_value('base_url')+module.get_value('module_link')+"/acquit";

						$.ajax({
							url: saveUrl,
							type:"POST",
							data: data,
							dataType: "json",
							beforeSend: function(){
									$.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Saving, please wait...</div>' });
							},
							success: function(){

								if(on_success == "back") {
									$.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />'+data.msg+' Redirecting to the previous page.</div>' });
									window.location = module.get_value('base_url') + module.get_value('module_link');
									
								} else if (on_success == "email") {
									// Ajax request to send email.                    
									$.ajax({
											url: module.get_value('base_url') + module.get_value('module_link') + '/send_email',
											data: 'record_id=' + data.record_id,
											dataType: 'json',
											type: 'post',
											success: function () {
													if( is_wizard == 1 ) {                            	
														window.location = module.get_value('base_url') + module.get_value('module_link') + '/edit/' + data.record_id;
													}
													$.unblockUI({ onUnblock: function() { message_growl(data.msg_type, data.msg) } });
				
													if (typeof(callback) == typeof(Function)) {
														callback();
													}
											}
									});                                   
								} else{
									//check if new record, update record_id
									if($('#record_id').val() == -1 && data.record_id != ""){
										$('#record_id').val(data.record_id);
										$('#record_id').trigger('change');
										if( is_wizard == 1 ) window.location = module.get_value('base_url') + module.get_value('module_link') + '/edit/' + data.record_id;
									}
									$.unblockUI({ onUnblock: function() { message_growl(data.msg_type, data.msg) } });
								}
								
							}
						});
						
		            }
		        },
		        {
		            title: "Acquit"
		        });

			

		}
	<?php endif?>
	<?php if($this->user_access[$this->module_id]['edit'] == 1): ?>
		function give_da( on_success, is_wizard , callback ){


			 Boxy.ask("Are you sure you want to continue?", ["Yes", "No"],function( choice ) {
		        if(choice == "Yes"){
		               
					var data = $('#record-form').serialize();
					var saveUrl = module.get_value('base_url')+module.get_value('module_link')+"/give_da";


					$.ajax({
						url: saveUrl,
						type:"POST",
						data: data,
						dataType: "json",
						beforeSend: function(){
								$.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Saving, please wait...</div>' });
						},
						success: function(data){

							if(on_success == "back") {
								$.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />'+data.msg+' Redirecting to the previous page.</div>' });
								window.location = module.get_value('base_url') + data.da_mod.class_path + '/edit/' + data.da_id;
								
							} else if (on_success == "email") {
								// Ajax request to send email.                    
								$.ajax({
										url: module.get_value('base_url') + module.get_value('module_link') + '/send_email',
										data: 'record_id=' + data.record_id,
										dataType: 'json',
										type: 'post',
										success: function () {
												if( is_wizard == 1 ) {                            	
													window.location = module.get_value('base_url') + module.get_value('module_link') + '/edit/' + data.record_id;
												}
												$.unblockUI({ onUnblock: function() { message_growl(data.msg_type, data.msg) } });
			
												if (typeof(callback) == typeof(Function)) {
													callback();
												}
										}
								});                                   
							} else{
								//check if new record, update record_id
								if($('#record_id').val() == -1 && data.record_id != ""){
									$('#record_id').val(data.record_id);
									$('#record_id').trigger('change');
									if( is_wizard == 1 ) window.location = module.get_value('base_url') + module.get_value('module_link') + '/edit/' + data.record_id;
								}
								$.unblockUI({ onUnblock: function() { message_growl(data.msg_type, data.msg) } });
							}
						}
					});

		            }
		        },
		        {
		            title: "For Disciplinary Action"
		        });

		}
	<?php endif?>
</script>