<div class="form-item odd">
	<div>Using the following scale, how would you rate the Company?</div>
	<div class="spacer"></div>
	<table style="width: 100%">
		<tr>
			<td width="25%">(4) - Excellent</td>
			<td width="25%">(3) - Good</td>
			<td width="25%">(2) - Fair</td>
			<td width="25%">(1) - Poor</td>
		</tr>
	</table>
	<div class="spacer"></div>
	<div class="form-item odd">
		<div><input type="text" class="rating_input" name="rating[benefits]" value="<?=isset($record['benefits']) ? $record['benefits'] : set_value('benefits', 0)?>" />Benefits</div>
		<div><input type="text" class="rating_input" name="rating[pay]" value="<?=isset($record['pay']) ? $record['pay'] : set_value('pay', 0)?>" />Pay</div>
		<div><input type="text" class="rating_input" name="rating[recruiting_process]" value="<?=isset($record['recruiting_process']) ? $record['recruiting_process'] : set_value('recruiting_process', 0)?>"/>Recruiting Process</div>
		<div><input type="text" class="rating_input" name="rating[orientation_process]" value="<?=isset($record['orientation_process']) ? $record['orientation_process'] : set_value('orientation_process', 0)?>"/>Orientation Process</div>
		<div><input type="text" class="rating_input" name="rating[initial_hiring]" value="<?=isset($record['initial_hiring']) ? $record['initial_hiring'] : set_value('initial_hiring', 0)?>"/>Initial Training</div>
		<div><input type="text" class="rating_input" name="rating[employee_interest]" value="<?=isset($record['employee_interest']) ? $record['employee_interest'] : set_value('employee_interest', 0)?>"/>Interest in the Employee</div>
		<div><input type="text" class="rating_input" name="rating[growth_opportunities]" value="<?=isset($record['growth_opportunities']) ? $record['growth_opportunities'] : set_value('growth_opportunities', 0)?>"/>Growth Opportunities</div>
		<div><input type="text" class="rating_input" name="rating[ongoing_training]" value="<?=isset($record['ongoing_training']) ? $record['ongoing_training'] : set_value('ongoing_training', 0)?>"/>Ongoing Training</div>
		<div><input type="text" class="rating_input" name="rating[working_condition]" value="<?=isset($record['working_condition']) ? $record['working_condition'] : set_value('working_condition', 0)?>"/>Physical Working Condition</div>
		<div><input type="text" class="rating_input" name="rating[keep_employees_informed]" value="<?=isset($record['keep_employees_informed']) ? $record['keep_employees_informed'] : set_value('keep_employees_informed', 0)?>"/>Keeping Employees Informed</div>
		<div><input type="text" class="rating_input" name="rating[treat_employees_fairly]" value="<?=isset($record['treat_employees_fairly']) ? $record['treat_employees_fairly'] : set_value('treat_employees_fairly', 0)?>"/>Treating Employees Fairly</div>
	</div>
	<div class="form-item even">
		<div><input type="text" class="rating_input" name="rating[open_door_policy]" value="<?=isset($record['open_door_policy']) ? $record['open_door_policy'] : set_value('open_door_policy', 0)?>"/>Open Door Policy</div>
		<div><input type="text" class="rating_input" name="rating[job_recognition]" value="<?=isset($record['job_recognition']) ? $record['job_recognition'] : set_value('job_recognition', 0)?>"/>Recognition for a job well done</div>
		<div><input type="text" class="rating_input" name="rating[concern_for_excellence]" value="<?=isset($record['concern_for_excellence']) ? $record['concern_for_excellence'] : set_value('concern_for_excellence', 0)?>"/>Company's Concern for Excellence</div>
		<div><input type="text" class="rating_input" name="rating[company_image]" value="<?=isset($record['company_image']) ? $record['company_image'] : set_value('company_image', 0)?>"/>Overall Company Image</div>
		<div><input type="text" class="rating_input" name="rating[performance_management_system]" value="<?=isset($record['performance_management_system']) ? $record['performance_management_system'] : set_value('performance_management_system', 0)?>"/>Performance Management System</div>
		<div><input type="text" class="rating_input" name="rating[human_resource_management]" value="<?=isset($record['human_resource_management']) ? $record['human_resource_management'] : set_value('human_resource_management', 0)?>"/>Human Resource Management</div>
		<div><input type="text" class="rating_input" name="rating[management_relations]" value="<?=isset($record['management_relations']) ? $record['management_relations'] : set_value('management_relations', 0)?>"/>Employee - Management Relations</div>
		<div><input type="text" class="rating_input" name="rating[tools]" value="<?=isset($record['tools']) ? $record['tools'] : set_value('tools', 0)?>"/>Tools to deliver job expectations</div>
		<div><input type="text" class="rating_input" name="rating[industrial_morale]" value="<?=isset($record['industrial_morale']) ? $record['industrial_morale'] : set_value('industrial_morale', 0)?>"/>Industrial Morale</div>
		<div><input type="text" class="rating_input" name="rating[company_values]" value="<?=isset($record['company_values']) ? $record['company_values'] : set_value('company_values', 0)?>"/>Upholding Company Values</div>
	</div>
</div>

<script type="text/javascript">
	$(document).ready(function () {
		$('.rating_input')
			.attr('maxlength', 1)
			.attr('size', 1)
			.css('margin-right', '10px')
			.keydown(function(event) {
		        // Allow: backspace, delete, tab, escape, and enter
		        if ( event.keyCode == 46 || event.keyCode == 8 || event.keyCode == 9 || event.keyCode == 27 || event.keyCode == 13 || 
		             // Allow: Ctrl+A
		            (event.keyCode == 65 && event.ctrlKey === true) || 
		             // Allow: home, end, left, right
		            (event.keyCode >= 35 && event.keyCode <= 39)) {
		                 // let it happen, don't do anything
		                 return;
		        }
		        else {
		            // Ensure that it is a number and stop the keypress
		            if (event.shiftKey || (event.keyCode < 49 || event.keyCode > 52) && (event.keyCode < 97 || event.keyCode > 100 )) {
		                event.preventDefault(); 
		            }   
		        }
		    })
		    .focus(function() {							  	
			   	this.select();
			});

	});
</script>