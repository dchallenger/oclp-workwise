
<div  style="width:85%; min-width:400px;">
	<p style="font-weight:bold;"><?php echo $manpower->position; ?></p>
	<p><span style="font-weight:bold;">Date Needed: </span><?php echo date($this->config->item('display_date_format'),strtotime($manpower->date_needed)); ?></p>
	<br />
	<p><span style="font-weight:bold;">Duties</span></p>
	<?php

	if ( $manpower->job_description_attachment ) {

		// $file_upload = $this->db->get_where('file_upload',array('upload_id'=>$manpower->job_description_attachment))->row();

	?>
		<p><?php echo $manpower->duties; ?></p><br/>
		<a href="<?php echo $manpower->job_description_attachment; ?>" img_target="<?php echo $manpower->job_description_attachment; ?>">
				<img src="<?php echo base_url().'/themes/slategray/images/file-icon-md.png'; ?>" width="25%" /></a>
       
    <?php
    } else {
     ?>
        <p><?php echo $manpower->duties; ?></p>
	<?php
	}
	?>
	<br />
	<?php /*
	<p><span style="font-weight:bold;">Qualifications</span></p>
	<p><?php echo $manpower->qualification; ?></p>
	*/ ?>
</div>
<br />
<div>
	<form id="export-form" method="post" action="">
          <input type="hidden" name="mrfid" id="mrfid" value="<?php echo $manpower->request_id; ?>" />
          <div id="form-div">
            <div class="col-2-form">
              
                  <div class="form-item odd ">
                    <label class="label-desc gray" for="company">Remarks:</label>
                    <div class="text-input-wrap">
                        <textarea id="remarks" name="remarks"></textarea>
                    </div>              
                </div>
    </form>
    <br /> 
</div>
<div>
<div class="form-submit-btn">
            <div class="icon-label-group">
                <div class="icon-label">
                    <a rel="record-save" class="icon-16-send-email" onclick="send_letter_intent()" href="javascript:void(0);">
                        <span>Send Application</span>
                    </a>            
                </div>
            </div>
    </div> 
</div>

<script type="text/javascript">
	$(document).ready(function() {
		
		
	});

	function send_letter_intent(){


		$.ajax({
			        url: module.get_value('base_url') + 'employee/letter_of_intent/send_letter_of_intent',
			        data: 'mrfid=' + $('#mrfid').val() + '&message=' + $('#remarks').val(),
			        type: 'post',
			        dataType: 'json',
			        success: function(data) {
			        	$.unblockUI();	
			        	Boxy.get($('#boxyhtml')).hide();
			        	message_growl(data.msg_type, data.msg);
			        }
			});

	}

</script>