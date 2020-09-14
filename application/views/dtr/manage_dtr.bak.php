<style>
	.datetimepicker{
		width: 126px;
	}
</style>
<form name="save-dtr" style="overflow:auto; min-height:100px">
	<input name="employee_id" value="<?php echo $this->input->post('employee_id')?>" type="hidden">
	<table id="" class="default-table boxtype" style="width:100%">
		<thead>
			<th width="7%">Date</th>
			<th width="7%">Shift</th>
			<th width="2%">Absent</th>
			<th width="13.5%">Time-in</th>
			<th width="13.5%">Break-out</th>
			<th width="13.5%">Break-in</th>
			<th width="13.5%">Time-out</th>
			<th width="28%">Overtime</th>
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
				<tr class="<?php echo ($ctr%2) ? 'odd': 'even'?>">
					<td align="right">
						<?php echo date('D, M d, Y', strtotime($date_from))?>
						<input name="date[]" value="<?php echo $date_from?>" type="hidden">
					</td>
					<td align="center"><?php 
						if(!empty($worksched)){
							 echo $worksched->shift;
						}
						else{
							echo 'Rest Day';
						}
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
						if(isset($dtr) && !empty($dtr->time_in1)){
							$value = date($this->config->item('edit_datetime_format'), strtotime($dtr->time_in1));
						}?>
						<input type="text" name="time_in1[]" value="<?php echo $value?>" class="input-text datetimepicker" readonly/>
					</td>
					<td><?php
						$value = '';
						if(isset($dtr) && !empty($dtr->time_out2)){
							$value = date($this->config->item('edit_datetime_format'), strtotime($dtr->time_out2));
						}?>
						<input type="text" name="time_out2[]" value="<?php echo $value?>" class="input-text datetimepicker" readonly/>
					</td>
					<td><?php
						$value = '';
						if(isset($dtr) && !empty($dtr->time_in2)){
							$value = date($this->config->item('edit_datetime_format'), strtotime($dtr->time_in2));
						}?>
						<input type="text" name="time_in2[]" value="<?php echo $value?>" class="input-text datetimepicker" readonly/>
					</td>
					<td><?php
						$value = '';
						if(isset($dtr) && !empty($dtr->time_out1)){
							$value = date($this->config->item('edit_datetime_format'), strtotime($dtr->time_out1));
						}?>
						<input type="text" name="time_out1[]" value="<?php echo $value?>" class="input-text datetimepicker" readonly/>
					</td>
					<td>
						<?php
						$startvalue = '';
						$endvalue = '';
						if(isset($dtr) && !empty($dtr->ot_start)){
							$startvalue = date($this->config->item('edit_datetime_format'), strtotime($dtr->ot_start));
						}
						if(isset($dtr) && !empty($dtr->ot_end)){
							$endvalue = date($this->config->item('edit_datetime_format'), strtotime($dtr->ot_end));
						}?>
						<input type="text" name="ot_start[]" value="<?php echo $startvalue?>" class="input-text datetimepicker" readonly/>
						&nbsp;&nbsp;&nbsp;to&nbsp;&nbsp;&nbsp;
						<input type="text" name="ot_end[]" value="<?php echo $endvalue?>" class="input-text datetimepicker" readonly/>
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