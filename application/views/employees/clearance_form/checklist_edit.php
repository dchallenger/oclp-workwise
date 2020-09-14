<?php
	$result = $this->db->query("SELECT division_manager_id,division,dm_user_id,department,CONCAT (udv.firstname,' ',udv.lastname) AS division_head,CONCAT (udp.firstname,' ',udp.lastname) AS department_head FROM {$this->db->dbprefix}user u
					JOIN {$this->db->dbprefix}user_company_division dv ON u.division_id = dv.division_id
					JOIN {$this->db->dbprefix}user_company_department dp ON u.department_id = dp.department_id
					LEFT OUTER JOIN {$this->db->dbprefix}user udv ON dv.division_manager_id = udv.employee_id
					LEFT OUTER JOIN {$this->db->dbprefix}user udp ON dp.dm_user_id = udp.employee_id
					WHERE u.employee_id = {$employee_id}
				");
	 // dbug($result->row()->division_head);die();
?>
<div id="checklist">
	<div class="spacer"></div>
	<input type="hidden" value="<?php echo $employee_id?>" name="employee_id" id="employee_id">
	<table class="default-table" style="width: 100%">
		<thead>
			<tr><th>Supervisor or Manager: Please confirm turnover of the following:</th></tr>
		</thead>
		<tbody>
			<?php   if ($result->row()->division_head): ?>
			<tr>
				<td>
					<table width="1024">
						<tr><td>Name</td><td><?=$result->row()->division_head?><span style="padding-left:10px;font-style:italic">(Division Head)</span></td></tr>
						<tr>
							<td style="vertical-align:middle">Comments</td>
							<td><textarea cols="40" name="a_status[comments][div_head]"><?=isset($status_data['comments']['div_head']) ? $status_data['comments']['div_head'] : ''?></textarea></td>
						</tr>
						<tr>
							<td style="vertical-align:middle">Accountabilities</td>
							<td><textarea cols="40" name="a_status[accountabilities][div_head]?>]"><?=isset($status_data['accountabilities']['div_head']) ? $status_data['accountabilities']['div_head'] : ''?></textarea></td>
						</tr>
						<tr>
							<td>Status</td>
							<td>
								<?php echo form_dropdown('a_status[status][div_head]', 
										array('0' => 'Pending', '1' => 'Approved'), 
										isset($status_data['status']['div_head']) ? $status_data['status']['div_head'] : 0
										);?>
							</td>
						</tr>
					</td>
					</table>
				</td>
			</tr>			
			<?php endif; ?>

			<?php if ($result->row()->department_head): ?>
			<tr>
				<td>
					<table width="1024">
						<tr><td>Name</td><td><?=$result->row()->department_head?><span style="padding-left:10px;font-style:italic">(Department Head)</span></td></tr>
						<tr>
							<td style="vertical-align:middle">Comments</td>
							<td><textarea cols="40" name="a_status[comments][dept_head]"><?=isset($status_data['comments']['dept_head']) ? $status_data['comments']['dept_head'] : ''?></textarea></td>
						</tr>
						<tr>
							<td style="vertical-align:middle">Accountabilities</td>
							<td><textarea cols="40" name="a_status[accountabilities][dept_head]"><?=isset($status_data['accountabilities']['dept_head']) ? $status_data['accountabilities']['dept_head'] : ''?></textarea></td>
						</tr>
						<tr>
							<td>Status</td>
							<td>
								<?php echo form_dropdown('a_status[status][dept_head]', 
										array('0' => 'Pending', '1' => 'Approved'), 
										isset($status_data['status']['dept_head']) ? $status_data['status']['dept_head'] : 0
										);?>
							</td>
						</tr>
					</td>
					</table>
				</td>
			</tr>			
			<?php endif; ?>

			<?php if ($checklist): foreach ($checklist as $c):?>
			<tr>
				<td>
					<table width="1024">
						<tr><td>Name</td><td><?=$c['firstname'] . ' ' . $c['lastname']?></td></tr>
						<tr>
							<td style="vertical-align:middle">Comments</td>
							<td><textarea <? echo ($this->userinfo['user_id'] === $c['approver_id']) ? '' : " readonly='readonly'" ;?> cols="40" name="a_status[comments][<?=$c['ecfid']?>]"><?=isset($status_data['comments'][$c['ecfid']]) ? $status_data['comments'][$c['ecfid']] : ''?></textarea></td>
						</tr>
						<tr>
							<td style="vertical-align:middle">Accountabilities</td>
							<td><textarea <? echo ($this->userinfo['user_id'] === $c['approver_id']) ? '' : " readonly='readonly'" ;?> cols="40" name="a_status[accountabilities][<?=$c['ecfid']?>]"><?=isset($status_data['accountabilities'][$c['ecfid']]) ? $status_data['accountabilities'][$c['ecfid']] : $c['equipment']?></textarea></td>
						</tr>
						<tr>
							<td>Status</td>
							<td>
								<?php 

									 if ($this->userinfo['user_id'] === $c['approver_id']) {

										echo form_dropdown('a_status[status]['.$c['ecfid'].']', 
										array('0' => 'Pending', '1' => 'Approved'), 
										isset($status_data['status'][$c['ecfid']]) ? $status_data['status'][$c['ecfid']] : 0);
									 }else{
									 										 
								 	?>
									 	<input type="hidden" name="<?php echo 'a_status[status]['.$c['ecfid'].']';?>" value="<?php if (isset($status_data['status'][$c['ecfid']])) {
									 		echo $status_data['status'][$c['ecfid']];
									 	}else{
									 		echo 0;
									 	}?>">
									 	<input type="text" readonly="readonly" 
									 			value ="<?php if (isset($status_data['status'][$c['ecfid']])) {
									 			if($status_data['status'][$c['ecfid']] === '0'){ 
									 				echo 'Pending';
									 			}elseif($status_data['status'][$c['ecfid']] === '1'){
									 			 	echo 'Approved';
									 			};
									 			}else{
									 				echo 'Pending';
									 			}?>" 
									 			>
								<?php }
								?>
							</td>
						</tr>
					</td>
					</table>
				</td>
			</tr>
			<?php endforeach; endif; ?>
		</tbody>
	</table>
	<script type="text/javascript">
		$('.default-table tbody tr:even').addClass('even');
		$('.default-table tbody tr:odd').addClass('odd');	
	</script>
</div>