
<div class="form-item even">
	<label class="label-desc gray" for="department" id="category_selected">Move applicant to</label>
	<div>
		<br />
		<input type="radio" name="move_applicant_status" id="" checked="checked" value="21" />Kept for Reference<br />
		<input type="radio" name="move_applicant_status" id="" value="22" />Rejected<br />
		<!-- <input type="radio" name="move_applicant_status" id="" value="23" />Declined<br /> -->
		<input type="radio" name="move_applicant_status" id="" value="8" />Black Listed / Do Not Re-Hire<br />
		<input type="hidden" name="application_status_id" id="application_status_id" value="21" />
	</div>
</div>

<div class="form-submit-btn">
            <div class="icon-label-group">
                <div class="icon-label">
                    <a rel="record-save" class="icon-16-send-email" onclick="save_disqualified_candidate()" href="javascript:void(0);">
                        <span>Move Applicant</span>
                    </a>            
                </div>
            </div>
    </div> 
</div>

<script type="text/javascript">
	$(document).ready(function() {
		$('input[name="move_applicant_status"]').live('click',function(){
			$('#application_status_id').val($(this).val());
		});

/*		$('#move_applicant_status_active').live('click',function(){

			$('#move_applicant_status').val($(this).val());

		});

		$('#move_applicant_status_blacklist').live('click',function(){

			$('#move_applicant_status').val($(this).val());

		});*/

	});

	

	function save_disqualified_candidate(){
	
		$.ajax({
			        url: module.get_value('base_url') + 'recruitment/candidates/save_disqualified_candidate',
			        data: 'status=' + $('#application_status_id').val() + '&applicant_id=' + module.get_value('record_id'),
			        type: 'post',
			        dataType: 'json',
			        success: function(data) {
			        	$.unblockUI();	
			        	Boxy.get($('#boxyhtml')).hide();
			        	message_growl(data.msg_type, data.msg);
			        	window.location = module.get_value('base_url') + 'recruitment/applicants';
			        }
			});

	}

</script>