<?php if ($modules):
	$this->db->where('position_id', $raw->reporting_to);
	$reporting = $this->db->get('user_position')->row();	
?>
	<table style="width: 100%" class="default-table boxtype">
		<thead>
			<tr class="odd">
				<th class="odd">Module</th>
				<th class="even">Condition<br/>Approver No.</th>
				<th class="odd">Approver</th>
				<th class="even">Email</th>
				<th class="odd">Action</th>
			</tr>
		</thead>	
		<tbody>
			<?php foreach ($modules as $module):?>		
			<tr id="module-notification-<?=$module->module_id?>">
				<td>
					<strong><?=$module->short_name?></strong>
					<a class="icon-button icon-16-add align-right" href="javascript:void(0);" onclick="get_positions_boxy('<?=$module->module_id?>')">Add</a>
				</td>
				<td align="center"><?php
						$condition = "";
						if (isset($module->approvers)){
							foreach ($module->approvers as $approver){
								$condition = $approver->condition;
							} 
						}
					?>
					<select name="condition[<?php echo $module->module_id?>]">
						<option value="">Select...</option>
						<?php
							$this->db->order_by('approver_condition');
							$conditions = $this->db->get_where('approver_condition', array('deleted' => 0));
							if( $conditions->num_rows() > 0 ){
								foreach($conditions->result() as $cond){
									echo '<option value="'.$cond->approver_condition_id.'"'.($condition == $cond->approver_condition_id ? ' selected="selected"' : '').'>'.$cond->approver_condition.'</option>';
								}
							}
						?>
					</select>
				</td>
				<td colspan="3">&nbsp;</td>
			</tr>
				<?php if (isset($module->approvers)):
					$approver_no = sizeof($module->approvers); ?>
					<?php foreach ($module->approvers as $approver):?>						
						<tr class="approver-<?=$module->module_id?>">
							<td class="odd">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?=$approver->approver_position?></td>
							<td class="even" align="center"><input class="approverno-<?=$module->module_id?>" type="hidden" value="<?php echo $approver_no?>" name="notifications[<?=$module->module_id?>][<?=$approver->approver_position_id?>][approver_no]"><span class="approvernolabel-<?=$module->module_id?>"><?php echo $approver_no?></span></td>
							<td class="odd" align="center"><input type="checkbox" name="notifications[<?=$module->module_id?>][<?=$approver->approver_position_id?>][approver]" value="1" <?=($approver->approver) ? 'checked' : ''?>/></td>
							<td class="even" align="center">
								<input type="checkbox" name="notifications[<?=$module->module_id?>][<?=$approver->approver_position_id?>][email]" value="1" <?=($approver->email) ? 'checked' : ''?>/>
							</td>
							<td>
								<div class="icon-group">
									<a href="javascript:void(0)" onclick="delete_approver($(this), <?=$module->module_id?>)" class="icon-button icon-16-delete align-right"></a>
								</div>
							</td>
						</tr> <?php
						$approver_no--;
					endforeach;?>
				<?php ;elseif($raw->reporting_to > 0):?>
						<tr>
							<td class="odd">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?=$reporting->position?></td>
							<td class="even" align="center"></td>
							<td class="odd" align="center"><input type="checkbox" name="notifications[<?=$module->module_id?>][<?=$raw->reporting_to?>][approver]" value="1" /></td>
							<td class="even" align="center">
								<input type="checkbox" name="notifications[<?=$module->module_id?>][<?=$raw->reporting_to?>][email]" value="1" />
							</td>
							<td></td>
						</tr>
				<?php endif;?>
			<?php endforeach;?>
		</tbody>
	</table>
<?php endif;?>