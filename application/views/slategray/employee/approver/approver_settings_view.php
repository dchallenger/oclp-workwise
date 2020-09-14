<?php
	$this->db->where('setup_notification', 1);
	$this->db->where('deleted', 0);

	$modules = $this->db->get('module');
	if ( $modules ) :
		$modules = $modules->result();
		if(!IS_AJAX) echo '<div class="approvers-container">'; ?>
		<table style="width: 100%" class="default-table boxtype">
			<thead>
				<tr class="odd">
					<th class="odd">Module</th>
					<th class="even">Condition</th>
					<th class="odd">Approver</th>
					<th class="even">Email</th>
				</tr>
			</thead>	
			<tbody>
				<?php foreach ($modules as $module):?>		
				<tr id="module-notification-<?=$module->module_id?>">
					<td colspan="5">
						<strong><?=$module->short_name?></strong>
					</td>
				</tr>
				<?php
					$approvers = $this->db->get_where('employee_approver', array('deleted' => 0, 'module_id' => $module->module_id, 'employee_id' => $this->input->post('record_id')));
					if( $approvers->num_rows() > 0 ){ 
						foreach($approvers->result() as $approver):
							$user = $this->db->get_where('user', array('user_id' => $approver->approver_employee_id))->row();
							$condition = $this->db->get_where('approver_condition', array('approver_condition_id' => $approver->condition))->row();
							?>
						<tr class="approver-<?php echo $approver->employee_approver_id?>">
							<td class="odd" align="right"><?php echo $user->firstname . ' ' . $user->middleinitial . ' ' . $user->lastname . ' ' . $user->aux?></td>
							<td class="even" align="center"><?php echo $condition->approver_condition?></td>
							<td class="odd" align="center" ><?php echo $approver->approver == 1 ? "Yes" : "No"?></td>
							<td class="even" align="center"><?php echo $approver->email == 1 ? "Yes" : "No"?></td>
						</tr>
					<?php
						endforeach;
					}
				?>
				<?php endforeach;?>
			</tbody>
		</table>
		<?php if(!IS_AJAX) echo '</div>'; ?>
	<?php endif;?>

<?php if(!IS_AJAX) : ?>
<script>
	function quick_edit_approver(employee_approver_id, module_id, employee_id){
		var data = "record_id="+employee_approver_id+"&module_id="+module_id+"&employee_id="+employee_id+"&page_refresh=true";
		showQuickEditForm( module.get_value('base_url') + "employee/approver_detail/quick_edit", data)	
	}
</script>
<?php endif;?>