<form id="form-period-options">
	<div class="col-1-form">
		<div class="form-item">
			<input type="hidden" name="date_from" id="date_from" value="<?php echo $period_date_from; ?>"/>
			<input type="hidden" name="date_to" id="date_to" value="<?php echo $period_date_to; ?>"/>
			<label for="type" class="label-desc gray">Category</label>
			<div class="select-input-wrap">			
				<select id="period-process-type" name="type">
					<option value="employee_id">By Employee</option>
					<option value="company_id">By Company</option>
					<option value="division_id">By Division</option>
					<!-- <option value="group_name_id">By Group</option> -->
					<option value="department_id">By Department</option>
					<!-- <option value="project_name_id">By Project</option> -->
				</select>
			</div>
		</div>
		<div class="form-item">		
			<label class="label-desc gray">Values</label>
			<div class="multiselect-input-wrap">
				<select id="values" name="values[]" multiple="multiple"></select>				
			</div>
		</div>
		<div class="spacer"></div>
		<div class="form-item">
			<label for="type" class="label-desc gray">Employment Status</label>
			<div class="multiselect-input-wrap">			
				<select id="period-process-status" name="status[]" multiple="multiple" class="multi-select">
					<?php echo $employment_status; ?>
				</select>
			</div>
		</div>
		<div class="clearfix"></div>
		<div class="spacer"></div>
		<div class="icon-label-group">
		    <div class="icon-label">
		         <a id="process-period" class="icon-16-settings" href="javascript:void(0);">
		            <span>Process</span>
		        </a>
		    </div>
		</div>
		<div class="or-cancel">
		    <span class="or">or</span>    
		    <a class="cancel" href="#" id="cancel">Cancel</a>
		</div>		
	</div>
</form>

<script type="text/javascript">
	$(document).ready(function () {		

		$('#cancel').die().live('click', function () {
			Boxy.get($(this)).hide();
		});

		$('#period-process-type').die().live('change', function () {
			$.ajax({
				url: module.get_value('base_url') + module.get_value('module_link') + '/get_dropdown_options',
				type: 'post',
				data: $('#form-period-options').serialize(),
				dataType: 'json',
				beforeSend: function(){
					$.blockUI({
						message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>'
					});
				},				
				success: function (response) {
					$.unblockUI();

					$('#values option').remove();
					// console.log(response.options)
					// $.each(response.options, function (index, value) {
					// 	$('#values').append($('<option></option>').text(value.text).val(value.value));
					// });
					$('#values').append(response.options);
					$('#values').multiselect('refresh');
				}
			});
		});	


		window.onload = function(){

			$('.multi-select').trigger('multiselectclose');

		};

		$('.multi-select').live('multiselectclose', function () {
			
			if($('#period-process-type').val() == "employee_id"){

				$.ajax({
					url: module.get_value('base_url') + module.get_value('module_link') + '/get_dropdown_options',
					type: 'post',
					data: $('#form-period-options').serialize(),
					dataType: 'json',
					beforeSend: function(){
						$.blockUI({
							message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>'
						});
					},				
					success: function (response) {
						$.unblockUI();

						$('#values option').remove();

						$.each(response.options, function (index, value) {
							$('#values').append($('<option></option>').text(value.text).val(value.value));
						});

						$('#values').multiselect('refresh');
					}
				});

			}

		});

		
	});
</script>
