<?php
  if(empty($_FILES[csv])){
    $this->session->set_flashdata('flashdata', 'Select File To Upload.');
    $this->session->set_flashdata('msg_type', 'error');
  }
  else if($this->key_field_val == -1 && !empty($_FILES[csv])){
    $this->session->set_flashdata('flashdata', 'Create Batch Entry.');
    $this->session->set_flashdata('msg_type', 'error');
  }
  else
  {  
    $file_ext = pathinfo($_FILES[csv][name]);
    if ($file_ext[extension]  == 'csv'){
      if ($_FILES[csv][size] > 0) {
          //get the csv file
          $file = $_FILES[csv][tmp_name];
          $handle = fopen($file,"r");
          //loop through the csv file and insert into database
          do {
             if ($data[0]) { 
                $emp = $this->db->query("SELECT * FROM {$this->db->dbprefix}employee WHERE id_number = '{$data[0]}'")->row();
                if(!empty($emp->employee_id)){
                  $qry = $this->db->query("SELECT * FROM {$this->db->dbprefix}payroll_batch_entry_employee 
                            WHERE batch_entry_id = {$this->key_field_val} AND employee_id = {$emp->employee_id}")->num_rows();
                  if($qry > 0){
                    $this->db->where('batch_entry_id',$this->key_field_val);
                    $this->db->where('employee_id',$emp->employee_id);
                    $this->db->delete('payroll_batch_entry_employee');
                  }
                  $this->db->insert('payroll_batch_entry_employee',array(
                      'batch_entry_id' => $this->key_field_val,
                      'employee_id' => $emp->employee_id,
                      'quantity' => 1,
                      'unit_rate' => $data[1],
                      'amount' => $data[1]
                      )
                  );
                }
                $this->session->set_flashdata('msg_type', 'success');
                $this->session->set_flashdata('flashdata', 'Import success.');
            }
          } while ($data = fgetcsv($handle,1000,",","'"));
      }
      else{
          $this->session->set_flashdata('flashdata', 'Select File To Upload.');
          $this->session->set_flashdata('msg_type', 'error');
      }

    }
    else{
      $this->session->set_flashdata('flashdata', 'Import failed. The file must be csv format.');
      $this->session->set_flashdata('msg_type', 'error');
    }
  }
?>
<p class="form-group-description align-left">Add employees as needed.</p>

<div class="form-submit-btn align-right nopadding"><?php
 	$where = '';
  $curemp = array();
  if( $this->input->post('record_id') != '-1' ){
 		$current = $this->db->get_where('payroll_batch_entry_employee', array($this->key_field => $this->key_field_val));
    if( $current->num_rows() > 0 ){
      foreach($current->result() as $row){
        $curemp[] = $row->employee_id;
        $curdata[$row->employee_id] = array(
          'quantity' => number_format($row->quantity,2, '.', ','),
          'unit_rate' => number_format($row->unit_rate,2, '.', ','),
          'amount' => number_format($row->amount,2, '.', ','),
        );
      }
      $where = ' AND u.employee_id not in('. implode(',', $curemp) .')';
    }
 	}

 	$qry = "SELECT u.*
 	FROM {$this->db->dbprefix}user u 
	WHERE u.deleted = 0 AND u.inactive = 0 ".$where;

	$results = $this->db->query($qry)->result_array();
	$options = array(' ' => ' ');
	foreach ($results as $option) {
		$json[$option['user_id']] = array(
      'id_no' => $option['login'],
			'department' => $option['department'],
			'fullname' =>  $option['firstname'].' '.$option['middleinitial'].' '.$option['lastname'].' '.$option['aux']
		);
		$options[$option['department']][$option['user_id']] = $option['firstname'].' '.$option['middleinitial'].' '.$option['lastname'].' '.$option['aux'];
	}
  $params = 'multiple id="temp-employee_id"';
	echo '<div class="select-input-wrap align-right" style="text-align:left">'. form_dropdown('temp-employee_id', $options, '', $params ) . '</div>'; ?> 
	<div class="align-right" style="padding:4px;"><strong>Add Employee:</strong></div>
</div>
<div>
  <form class="align-left"  action="" method="post" enctype="multipart/form-data" name="batch_upload" id="batch_upload">
    Choose your file: <input name="csv" type="file" id="csv" />
      <input type="submit" name="Submit" value="Import" />
  </form>
</div>

<div class="clear"></div>

<table style="width:100%" class="default-table boxtype" id="listview-list">
    <colgroup>
      <col width="10%">
      <col width="25%">
      <col width="10%">
      <col width="20%">
      <col width="25%">
      <col width="10%">
    </colgroup>
    <thead>
        <tr>
            <th>ID No</th>
            <th>Employee Name</th>
            <th>Quantity</th>
            <th>Unit Rate</th>
            <th>Amount</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody id="employee-list"><?php
      if( $this->input->post('record_id') != '-1' ){
        if(!empty($curemp)){
          $where = ' AND u.employee_id in('. implode(',', $curemp) .')'; 
          $qry = "SELECT u.*
          FROM {$this->db->dbprefix}user u 
          WHERE u.deleted = 0 AND u.inactive = 0 ".$where;

          $results = $this->db->query($qry);
          if( $results->num_rows() > 0 ){
            $results = $results->result_array();
            foreach ($results as $option) {
              $json[$option['user_id']] = array(
                'id_no' => $option['login'],
                'department' => $option['department'],
                'fullname' =>  $option['firstname'].' '.$option['middleinitial'].' '.$option['lastname'].' '.$option['aux']
              ); ?>
            <tr id="employee_row-<?php echo $option['user_id']?>">
              <td align="right"><?php echo $option['login']?></td>
              <td><input type="hidden" value="<?php echo $option['user_id']?>" name="employee_id[]"><?php echo $option['firstname'].' '.$option['lastname']?></td>
              <td align="center"><input type="text" value="<?php echo $curdata[$option['user_id']]['quantity']?>" name="quantity[]" style="width: 80%; text-align: right" class="input-text"></td>
              <td align="center"><input type="text" value="<?php echo $curdata[$option['user_id']]['unit_rate']?>" name="unit_rate[]" style="width: 80%; text-align: right" class="input-text"></td>
              <td align="center"><input type="text" value="<?php echo $curdata[$option['user_id']]['amount']?>" name="amount[]" style="width: 80%; text-align: right" class="input-text" readonly></td>
              <td align="center"><span class="icon-group"><a href="javascript:delete_employee_row(<?php echo $option['user_id']?>)" tooltip="Delete" class="icon-button icon-16-delete"></a></span></td>
            </tr><?php
          }
        }
      }
      }?>
    </tbody>
</table>
<script type="text/javascript">
	var empdata = <?php echo json_encode($json)?>;
</script>