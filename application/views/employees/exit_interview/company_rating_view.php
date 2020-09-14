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
		<div><?=isset($record['benefits']) ? $record['benefits'] : 0?> - Benefits</div>
		<div><?=isset($record['pay']) ? $record['pay'] : 0?> - Pay</div>
		<div><?=isset($record['recruiting_process']) ? $record['recruiting_process'] : 0?> - Recruiting Process</div>
		<div><?=isset($record['orientation_process']) ? $record['orientation_process'] : 0?> - Orientation Process</div>
		<div><?=isset($record['initial_hiring']) ? $record['initial_hiring'] : 0?> - Initial Training</div>
		<div><?=isset($record['employee_interest']) ? $record['employee_interest'] : 0?> - Interest in the Employee</div>
		<div><?=isset($record['growth_opportunities']) ? $record['growth_opportunities'] : 0?> - Growth Opportunities</div>
		<div><?=isset($record['ongoing_training']) ? $record['ongoing_training'] : 0?> - Ongoing Training</div>
		<div><?=isset($record['working_condition']) ? $record['working_condition'] : 0?> - Physical Working Condition</div>
		<div><?=isset($record['keep_employees_informed']) ? $record['keep_employees_informed'] : 0?> - Keeping Employees Informed</div>
		<div><?=isset($record['treat_employees_fairly']) ? $record['treat_employees_fairly'] : 0?> - Treating Employees Fairly</div>
	</div>
	<div class="form-item even">
		<div><?=isset($record['open_door_policy']) ? $record['open_door_policy'] : 0?> - Open Door Policy</div>
		<div><?=isset($record['job_recognition']) ? $record['job_recognition'] : 0?> - Recognition for a job well done</div>
		<div><?=isset($record['concern_for_excellence']) ? $record['concern_for_excellence'] : 0?> - Company's Concern for Excellence</div>
		<div><?=isset($record['company_image']) ? $record['company_image'] : 0?> - Overall Company Image</div>
		<div><?=isset($record['performance_management_system']) ? $record['performance_management_system'] : 0?> - Performance Management System</div>
		<div><?=isset($record['human_resource_management']) ? $record['human_resource_management'] : 0?> - Human Resource Management</div>
		<div><?=isset($record['management_relations']) ? $record['management_relations'] : 0?> - Employee - Management Relations</div>
		<div><?=isset($record['tools']) ? $record['tools'] : 0?> - Tools to deliver job expectations</div>
		<div><?=isset($record['industrial_morale']) ? $record['industrial_morale'] : 0?> - Industrial Morale</div>
		<div><?=isset($record['company_values']) ? $record['company_values'] : 0?> - Upholding Company Values</div>
	</div>
</div>