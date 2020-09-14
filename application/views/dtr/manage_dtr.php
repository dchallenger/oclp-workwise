<style>
	.datetimepicker{
		width: 126px;
	}
</style>
<form name="save-dtr" style="overflow:auto; min-height:100px">
	<input name="employee_id" value="<?php echo $this->input->post('employee_id')?>" type="hidden">
	<table id="" class="default-table boxtype" style="width:100%">
		<thead>
			<th width="10%">Date</th>
			<th width="7%">Shift</th>
			<th width="2%">Absent</th>
			<th width="13.5%">Time-in</th>
			<th width="13.5%">Break-out</th>
			<th width="13.5%">Break-in</th>
			<th width="13.5%">Time-out</th>
			<th width="25%">Forms</th>
		</thead>
		<tbody><?php 
			$period = $this->db->get_where('timekeeping_period', array('period_id' => $this->input->post('period_id')))->row();
			$employee = $this->db->get_where('employee', array('employee_id' => $this->input->post('employee_id')))->row();
			$date_from = $period->date_from;
			$ctr = 0;
			while($date_from <= $period->date_to):
				$worksched = $this->system->get_employee_worksched( $employee->employee_id, $date_from, true);
				$qry = "select a.*, b.*
				FROM {$this->db->dbprefix}employee_dtr a
				LEFT JOIN {$this->db->dbprefix}employee_dtr_ot b on (b.date = a.date AND a.employee_id = b.employee_id)
				WHERE a.deleted = 0 AND a.date = '{$date_from}' AND a.employee_id = {$employee->employee_id}";
				$dtr = $this->db->query( $qry );
				
				if($dtr->num_rows() == 1){
					$dtr = $dtr->row();
				}
				else{
					unset($dtr);
					if( $worksched ){
						$dtr->time_in1 = $date_from .' '.$worksched->shifttime_start;
						// $dtr->time_in2 = $date_from .' '.$worksched->noon_start;
						$dtr->time_in2 = $date_from .' '.$worksched->noon_end;
						$dtr->time_out1 = $date_from .' '.$worksched->shifttime_end;
						//$dtr->time_out2 = $date_from .' '.$worksched->noon_end;
						$dtr->time_out2 = $date_from .' '.$worksched->noon_start;
					}
				} 

				?>
				<tr class="<?php echo ($ctr%2) ? 'odd': 'even'?>" date="<?php echo $date_from?>">
					<td align="right">
						<?php echo date('D, M d, Y', strtotime($date_from))?>
						<input name="date[]" value="<?php echo $date_from?>" type="hidden">
					</td>
					<td align="center"><?php 					
						$is_holiday = false;
						if(!in_array( $worksched->shift_id, array(0,1))){
							 $shift_col = $worksched->shift;
						}
						else{
							$shift_col = 'RESTDAY';
						}

						// check holiday.
						$holiday = $this->system->holiday_check(date('Y-m-d', strtotime($date_from)), $this->input->post('employee_id'));
						$a_h = array();
						if ($holiday) {
							$is_holiday = true;
							foreach ($holiday as $h) {
								$a_h[] = $h['holiday'];
							}

							if ($shift_col == 'RESTDAY') {
								$shift_col = '<strong>HOLIDAY / REST DAY</strong>';
							} else {
								$shift_col = '<strong>HOLIDAY</strong>';
							}

							$shift_col .= '<br />' . implode(', ', $a_h);
						}
						echo $shift_col;
						?>
					</td>
					<td align="center">
						<?php
						if( !in_array( $worksched->shift_id, array(0,1)) ):
							$awol = '';
							if(isset($dtr) && ($dtr->awol > 0))
								$awol = 'checked="checked"';
							else
								$awol = '';
							$name = 'name="awol['.$ctr.']"';

							?>
							<input type="checkbox" <?php echo $name ?> class="awol" <?php echo $awol ?> readonly/>
						<?php endif;?>
					</td>
					<td><?php
						$value = '';
						if(isset($dtr) && !empty($dtr->time_in1) && !in_array( $worksched->shift_id, array(0,1)) && $is_holiday == false){
							$value = date($this->config->item('edit_datetime_format'), strtotime($dtr->time_in1));
						}?>
						<input type="text" name="time_in1[]" value="<?php echo $value?>" class="input-text datetimepicker" readonly/>
					</td>
					<td><?php
						$value = '';
						if(isset($dtr) && !empty($dtr->time_out2) && !in_array( $worksched->shift_id, array(0,1)) && $is_holiday == false){
							$value = date($this->config->item('edit_datetime_format'), strtotime($dtr->time_out2));
						}?>
						<input type="text" name="time_out2[]" value="<?php echo $value?>" class="input-text datetimepicker" readonly/>
					</td>
					<td><?php
						$value = '';
						if(isset($dtr) && !empty($dtr->time_in2) && !in_array( $worksched->shift_id, array(0,1)) && $is_holiday == false){
							$value = date($this->config->item('edit_datetime_format'), strtotime($dtr->time_in2));
						}?>
						<input type="text" name="time_in2[]" value="<?php echo $value?>" class="input-text datetimepicker" readonly/>
					</td>
					<td><?php
						$value = '';
						if(isset($dtr) && !empty($dtr->time_out1) && !in_array( $worksched->shift_id, array(0,1)) && $is_holiday == false){
							$value = date($this->config->item('edit_datetime_format'), strtotime($dtr->time_out1));
						}?>
						<input type="text" name="time_out1[]" value="<?php echo $value?>" class="input-text datetimepicker" readonly/>
					</td>
					<td>
						<div style="float: right">
							Add: <select name="form[<?php echo $ctr?>]" class="emp-forms">
								<option value="">select...</topn>
								<option value="leaves">Leave</topn>
								<option value="overtime">Overtime</topn>
								<option value="obt">OBT</topn>
								<option value="cws">CWS</topn>
							</select>
						</div>
						<?php
							$forms = array();

							$this->db->select('employee_leaves.employee_leave_id, duration_id, credit, credit,application_form_id');
							$this->db->join('employee_leaves_dates', 'employee_leaves_dates.employee_leave_id = employee_leaves.employee_leave_id', 'left');
							$this->db->where('employee_id', $this->input->post('employee_id'));
							$this->db->where('(\'' . $date_from . '\' BETWEEN date_from AND date_to)', '', false);
							$this->db->where('employee_leaves_dates.date', $date_from);
							$this->db->where('form_status_id', 3);
							$this->db->where('employee_leaves_dates.deleted', 0);
							$this->db->where('employee_leaves.deleted', 0);
							$leave = $this->db->get('employee_leaves');
							if( $leave->num_rows() > 0 ){
								foreach( $leave->result() as $row ){
									$forms[] = '<a href="javascript: edit_leave('.$row->employee_leave_id.', \'\')" >Leave</a>';
								}	
							}

							$oot = get_form($this->input->post('employee_id'), 'oot', NULL, $date_from, true, false, false);
							if( $oot->num_rows() > 0 ){
								foreach( $oot->result() as $row ){
									$forms[] = '<a href="javascript: edit_overtime('.$row->employee_oot_id.', \'\')" >OT</a>';
								}
							}

							$obt = get_form($this->input->post('employee_id'), 'obt', NULL, $date_from, true, false, false);
							if( $obt->num_rows() > 0 ){
								foreach( $obt->result() as $row ){
									$forms[] = '<a href="javascript: edit_obt('.$row->employee_obt_id.', \'\')" >OBT</a>';
								}
							}

							$cws = get_form($this->input->post('employee_id'), 'cws', NULL, $date_from, true, false, false);
							if( $cws->num_rows() > 0 ){
								foreach( $cws->result() as $row ){
									$forms[] = '<a href="javascript: edit_cws('.$row->employee_cws_id.', \'\')" >CWS</a>';
								}
							}

							echo implode(', ', $forms);
						?>
					</td>
				</tr> <?php
				$date_from = date('Y-m-d', strtotime('+1 day' . $date_from));
				$ctr++;
			endwhile;
		?>
		</tbody>	
	<table>
</form>

<div class="form-submit-btn ">
  <div class="icon-label-group">
    <div class="icon-label">
      <a onclick="save_dtr()" href="javascript:void(0);" class="icon-16-disk">
        <span>Save</span>
      </a>
    </div>
  </div>
  <div class="or-cancel">
    <span class="or">or</span>
    <a rel="action-back" href="javascript:void(0)" class="cancel">Go Back</a>
  </div>
</div>