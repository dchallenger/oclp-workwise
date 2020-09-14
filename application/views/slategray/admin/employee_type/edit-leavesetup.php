<?php if(!IS_AJAX) :?><div id="leavesetup-container" class="col-2-form view"><?php endif;?>
	<?php if( $this->input->post('record_id') != '-1' ) : ?>
		<div class="form-submit-btn align-right nopadding">
	        <div class="icon-label-group">
	            <div class="icon-label">
	                <a onclick="edit_leave_setup('-1', <?php echo $this->input->post('record_id')?>)" class="icon-16-add" href="javascript:void(0)">                        
	                    <span>Add New Leave Setup</span>
	                </a>            
	            </div>
	        </div>
	    </div><?php
		$qry = "SELECT a.*, b.application_form, c.accumulation_type
		FROM {$this->db->dbprefix}employee_type_leave_setup a
		LEFT JOIN {$this->db->dbprefix}employee_form_type b ON b.application_form_id = a.application_form_id
		LEFT JOIN {$this->db->dbprefix}leave_accumulation c ON c.accumulation_type_id = a.accumulation_type_id
		WHERE a.deleted = 0 AND a.employee_type_id = {$this->input->post('record_id')}";
		$setup = $this->db->query( $qry ); 
		if($setup->num_rows() > 0){ ?>
		<table style="width: 100%" class="default-table boxtype">
			<thead>
				<tr class="odd">
					<th>Leave Type</th>
					<th>Accumalation</th>
					<th>Starting</th>
					<th>Paid</th>
					<th>Track</th>
					<th>Convertible</th>
					<th>Action</th>
				</tr>
			</thead>
			<tbody> <?php
				$ctr = 0;
				foreach($setup->result() as $leave){ 
					$class = $ctr % 2 == 0 ? 'class="even"' : 'class="odd"';
					?>
					<tr <?php echo $class?> id="leavesetup-<?php echo $leave->leave_setup_id?>">
						<td><?php echo $leave->application_form?></td>
						<td><?php echo $leave->accumulation_type?> - <?php echo $leave->accumulation?></td>
						<td><?php echo $leave->base?></td>
						<td><?php echo empty($leave->paid) ? 'No' : 'Yes'?></td>
						<td><?php echo empty($leave->track) ? 'No' : 'Yes'?></td>
						<td><?php echo empty($leave->convertible) ? 'No' : 'Yes'?></td>
						<td>
							<div class="icon-group">
								<a class="icon-button icon-16-edit align-right" onclick="edit_leave_setup('<?php echo $leave->leave_setup_id?>', '')" href="javascript:void(0)"></a>
								<a class="icon-button icon-16-delete align-right" onclick="delete_leave_setup('<?php echo $leave->leave_setup_id?>')" href="javascript:void(0)"></a>
							</div>
						</td>
					</tr>
				<?php
					$ctr++;
				}
			?>
			</tbody>
		</table> <?php 
		}
	endif;?>	
<?php if(!IS_AJAX) :?></div><?php endif;?>