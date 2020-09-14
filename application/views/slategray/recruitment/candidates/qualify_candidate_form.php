
<div id="mrf_form">
	<label class="label-desc gray" for="department" id="category_selected">Manpower Request<span class="red font-large">*</span></label>
	<div>
		<?php echo $select_form; ?>
	</div>
</div>
<br />
<div id="priority_form">
	<label class="label-desc gray" for="management_trainee" id="category_selected">Management Trainee Priority</label>
	<br />
	<div>
		<?php echo $select_priority; ?>
		<input type="hidden" name="management_trainee" id="management_trainee" value="0" />
	</div>
</div>
<br />
<div class="form-submit-btn">
            <div class="icon-label-group">
                <div class="icon-label">
                    <a rel="record-save" class="icon-16-send-email" onclick="save_qualified_candidate()" href="javascript:void(0);">
                        <span>Add Applicant</span>
                    </a>            
                </div>
            </div>
    </div> 
</div>

<script type="text/javascript">
	$(document).ready(function() {
		
		if (module.get_value('view') == 'detail') {
	    	setTimeout(
	    		function () {
	    			$('select[name="mrf_listing"]').chosen();    			
	    		}, 100
	    		);    	
	    }

	    $('#mt_priority_list').live('change',function(){
	    	$('#management_trainee').val( $('#mt_priority_list').val() );
	    });

	    $('#mrf_listing').live('change',function(){

			$.ajax({
				url: module.get_value('base_url') + 'recruitment/candidates/check_management_trainee',
				type: 'post',
				dataType: 'json',
				data: 'mrf_id=' + $('#mrf_listing').val(),
				success: function (response) {

					if( response.management_trainee == 0 ){
						$('#mt_priority_list').attr('disabled','disabled');
						$('#priority_form').hide();
						$('#management_trainee').val(0);
					}
					else{
						$('#mt_priority_list').removeAttr('disabled');
						$('#priority_form').show();
					}

				}
			});

		});

	});

	

	function save_qualified_candidate(){
	
		$.ajax({
			        url: module.get_value('base_url') + 'recruitment/candidates/save_qualified_candidate',
			        data: 'mrfid=' + $('#mrf_listing').val() + '&applicant_id=' + module.get_value('record_id') + '&mt_priority=' + $('#management_trainee').val() + '&from_cs=' + $('#from_cs').val(),
			        type: 'post',
			        dataType: 'json',
			        success: function(data) {

			        	 if(data.msg_type == 'error'){
                        
                            $.unblockUI();  
                            message_growl(data.msg_type, data.msg);

                        }
                        else{

				        	$.unblockUI();	
				        	Boxy.get($('#boxyhtml')).hide();
				        	message_growl(data.msg_type, data.msg);
				        	//window.location = module.get_value('base_url') + 'recruitment/applicants';
					        $('#record-form').attr("action", module.get_value('base_url') + "recruitment/applicants");
					        $('#record-form').submit();				        	
				        }
			        }
			});

	}

</script>