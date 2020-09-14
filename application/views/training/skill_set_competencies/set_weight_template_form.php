<form id="export-form" method="post" action="">
<input type="hidden" name="positionid" id="positionid" value="<?= $position_id ?>" />
<table id="module-set-weight" style="width:100%;" class="default-table boxtype">
    <thead>
    	<tr>
    		<td class="odd" style="vertical-align:middle; width:40%; font-weight:bold;">Skill Set</td>
    		<td class="odd" style="vertical-align:middle; width:40%; font-weight:bold;">Weight</td>
    	</tr>
    </thead>
    <tbody>
    <?php foreach( $position_skills as $position_skills_info ){ ?>
    	<tr>
    		<td><?= $position_skills_info->position_skills ?></td>
    		<td><input type="text" name="weight[<?= $position_skills_info->position_skills_id ?>]" class="weight_text" value="<?= $position_skills_info->weight ?>" style="width:20%" />&nbsp; &#37;</td>
    	</tr>
    <?php } ?>
    	<tr>
    		<td style="text-align:right;"><span style="font-weight:bold;">Total Weight: </span></td>
    		<td><input type="text" name="total_weight" readonly="" class="total_weight" value="<?= $total_weight; ?>" style="width:20%" />&nbsp; &#37;</td>
    	</tr>
    </tbody>
</table>

<div class="form-submit-btn">
            <div class="icon-label-group">
                <div class="icon-label">
                    <a rel="record-save" class="icon-16-disk-back save_weight" onclick="save_weight()" href="javascript:void(0);">
                        <span>Save Weight</span>
                    </a>            
                </div>
            </div>
    </div> 
</div>
</form>

<script type="text/javascript">

	$(document).ready(function() {


		$('.weight_text').live('change',function(){

			get_total_weight();

		});

	});

	function get_total_weight(){

		var weight = 0;

		$('.weight_text').each(function(){

			var parse = parseFloat($(this).val());

			if( parse ){
				weight = weight + parse;
			}

		});

		$('.total_weight').val(weight);

	}

	function save_weight(){

		var count = 0;

		$('.weight_text').each(function(){

			var parse = parseFloat($(this).val());

			/*
			if( !parse ){
				count++;
			}
			else 
			*/
			if( ( parse % 1 ) != 0 ){
				count++;
			}



		});

		if( count > 0 ){

			message_growl('error','Weight must be a whole number');

		}
		else{

			if( $('.total_weight').val() != 100 ){

				message_growl('error','Total weight must be equal to 100%');

			}
			else{


				$.ajax({
				        url: module.get_value('base_url') + 'training/skill_set_competencies/save_weight',
				        data: $('#export-form').serialize(),
				        type: 'post',
				        dataType: 'json',
				        success: function(data) {
				        	$.unblockUI();	
				        	Boxy.get($('#boxyhtml')).hide();
				        	message_growl(data.msg_type, data.msg);
				        }
				});




			}

		}

	}


</script>

<?php /*
<div  style="width:85%; min-width:400px;">
	<p style="font-weight:bold;"><?php echo $manpower->position; ?></p>
	<p><span style="font-weight:bold;">Date Needed: </span><?php echo date($this->config->item('display_date_format'),strtotime($manpower->date_needed)); ?></p>
	<br />
	<p><span style="font-weight:bold;">Duties</span></p>
	<p><?php echo $manpower->duties; ?></p>
	<br />
	<p><span style="font-weight:bold;">Qualifications</span></p>
	<p><?php echo $manpower->qualification; ?></p>
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


*/ ?>