<style type="text/css">
button {
  background-color: #066424; /* Green */
  border: none;
  color: white;
  padding: 15px 32px;
  text-align: center;
  text-decoration: none;
  display: inline-block;
  font-size: 16px;
  cursor: pointer;
  width: 140px;
}
div.timeclick {
	padding-top: 20px;
	float: left;
}
div.location {
	float: left;
	padding-top: 25px;
	padding-left: 15px;
}
.label-desc {
	width: 5.3em !important;
}
.select-input-wrap {
	margin-left: 5.3em !important;
}
.location_val {
	font-size: 11px;
}
#error_container {
	padding: 10px 0;
	color: red;
}
.time_in_tag {
	width: 170px;
	display: block;
	float: left;
}
</style>
<?php
	$time_record = $this->portlet->get_user_time_record($this->user->user_id,date('Y-m-d'));
	$time_in = ($time_record ? $time_record->time_in1 : '');
	$time_out = ($time_record ? $time_record->time_out1 : '');
?>
<div id="message-container"></div>
<h3>Time Record for Today <?php echo date('M d, Y'); ?></h3>
<br />
<label class="time_in_tag"><b>Time In</b> : <span class="time_in"><?php echo $time_in ?></span></label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<label class="time_out_tag"><b>Time Out</b> : <span class="time_out"><?php echo $time_out ?></span></label>
<div class="time_entry_container">
	<div class="timeclick">
		<button class="timerec"><?php echo ($time_in == '' ? 'Time In' : 'Time Out') ?></button>	
	</div>
	<div class="location">
	            <div class="form-item even" style="padding-top:10px;"> 
	                <label class="label-desc gray" for="family[relationship][]">
	                    Location <span class="red font-large">*</span> :
	                </label>
	                <div class="select-input-wrap">
	                    <select name="location" class="location_val">
	                        <option value="">Select..</option>
	                        <?php
	                        	$user_location = $this->portlet->get_user_location();
	                        	if ($user_location) {
	                        		foreach ($user_location as $row) {
	                        ?>	
	                        			<option value="<?php echo $row->location_id?>" <?php echo ($time_record->user_location_id == $row->location_id ? 'SELECTED' : '') ?>><?php echo $row->location ?></option>
	                        <?php			
	                        		}
	                        	}
	                        ?>
	                    </select>
	                </div>
	            </div>
	</div>
</div>
<br clear="all">
<div id="error_container">
</div>

<script type="text/javascript">
	$(document).ready(function(){
		if ($('.time_in').html() != '' && $('.time_out').html() != '')
			$('.time_entry_container').hide();
		else
			$('.time_entry_container').show();

		$('.timerec').click(function() {
			var obj = $(this);
			$('#error_container').html("");

			var location_id = $('.location_val').val();
			var location = $( ".location_val option:selected" ).text();

			if (location_id == '' && $('.time_in').html() == '') {
				$('#error_container').html("Please select Location");
				return false;
			}

			$.ajax({
				url: module.get_value('base_url') + module.get_value('module_link') + "/process_time_entry",
				type:"POST",
				dataType: 'json',
				data: { location_id: location_id,location : location },
				beforeSend: function(){
					$(obj).html('Processing...');
				},
				success: function(data){
					if (data){
						$(".time_in").html(data.time_in);					
						$(".time_out").html(data.time_out);

						if (data.time_complete)
							$('.time_entry_container').hide();
						else
							$(obj).html('Time Out');
					}
				}
			});	
		})		
	});
</script>
