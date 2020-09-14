<div id="employee-list">
	<table style="width:100%" class="default-table boxtype" id="listview-list">
    <thead>
        <tr>
            <th>Employee Name</th>
            <th>Available Credit</th>
            <th>Convert</th>
        </tr>
    </thead>
	<?php
		if($this->key_field_val != '-1'){
			$record = $this->db->get_where('payroll_leave_conversion', array('leave_convert_id' => $this->key_field_val))->row();
			$form = $this->db->get_where('employee_form_type', array('application_form_id' => $record->application_form_id))->row();
			$qry = "select b.lastname, b.firstname, a.*
			FROM {$this->db->dbprefix}payroll_leave_conversion_employee a
			LEFT JOIN {$this->db->dbprefix}user b on b.employee_id = a.employee_id
			WHERE a.leave_convert_id = {$this->key_field_val}
			order by b.lastname, b.firstname";
			$employees = $this->db->query( $qry );
			foreach( $employees->result() as $row ){ 
				$balance = $this->db->get_where('employee_leave_balance', array('year' => $record->year, 'employee_id' => $row->employee_id))->row(); ?>
				<tr class="employee-row" id="employee-<?php echo $row->employee_id?>">
					<td><?php echo $row->lastname?>, <?php echo $row->firstname?></td>
					<td align="right">
						<?php
							switch( $form->application_code ){
								case 'SL':
									echo $balance->sl - $balance->sl_used;
									break;
								case 'VL':
									echo $balance->vl - $balance->vl_used;
									break;
								case 'MTPL':
									echo $balance->vl - $balance->sl_used -  $balance->vl_used - $balance->el_used;
									break;
							}
						?>
					</td>
					<td align="right"><input type="text" name="amount[<?php echo $row->employee_id?>]" value="<?php echo $row->amount?>" class="text-right"></td>
				</tr>
			<?php
			}
		}
	?>
</table>
</div>