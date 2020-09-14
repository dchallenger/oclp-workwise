<p class="form-group-description align-left">Add employees as needed.</p>

<div class="form-submit-btn align-right nopadding"><?php
 	$where = '';
  $curemp = array();
  if( $this->input->post('record_id') != '-1' ){
 		$current = $this->db->get_where('payroll_bonus_employee', array($this->key_field => $this->key_field_val));
    if( $current->num_rows() > 0 ){
      foreach($current->result() as $row){
        $curemp[] = $row->employee_id;
        $curdata[$row->employee_id] = $row->amount;
      }
      $where = ' AND u.employee_id not in('. implode(',', $curemp) .')';
    }
 	}

 	$qry = "SELECT u.*, c.department, c.department_id
 	FROM {$this->db->dbprefix}user u 
	LEFT JOIN {$this->db->dbprefix}user_company_department c ON u.department_id = c.department_id
	WHERE u.deleted = 0 AND u.inactive = 0 AND u.department_id != 0 AND u.department_id IS NOT NULL AND u.department_id != '' ".$where;

	$results = $this->db->query($qry)->result_array();
	$options = array(' ' => ' ', 'all' => 'ALL');
	foreach ($results as $option) {
		$json[$option['user_id']] = array(
      'id_no' => $option['login'],
			'department' => $option['department'],
			'fullname' =>  $option['firstname'].' '.$option['middleinitial'].' '.$option['lastname'].' '.$option['aux']
		);
		$options[$option['department']][$option['user_id']] = $option['firstname'].' '.$option['middleinitial'].' '.$option['lastname'].' '.$option['aux'];
	}
	echo '<div class="select-input-wrap align-right" style="text-align:left">'. form_dropdown('temp-employee_id', $options, $value, $params) . '</div>'; ?> 
	<div class="align-right" style="padding:4px;"><strong>Add Employee:</strong></div>

</div>
<div class="clear"></div>
<table style="width:100%" class="default-table boxtype" id="listview-list">
    <colgroup>
      <col width="20%">
      <col width="35%">
      <col width="25%">
      <col width="20%">
    </colgroup>
    <thead>
        <tr>
            <th>Employee No</th>
            <th>Employee Name</th>
            <th>Amount</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody id="employee-list"><?php
      if( $this->input->post('record_id') != '-1' ){
        $where = ' AND u.employee_id in('. implode(',', $curemp) .')'; 
        $qry = "SELECT u.*, c.department, c.department_id
        FROM {$this->db->dbprefix}user u 
        LEFT JOIN {$this->db->dbprefix}user_company_department c ON u.department_id = c.department_id
        WHERE u.deleted = 0 AND u.inactive = 0 AND u.department_id != 0 AND u.department_id IS NOT NULL AND u.department_id != '' ".$where;
        
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
              <td align="center"><input type="text" value="<?php echo $curdata[$option['user_id']]?>" name="amount[]" style="width: 80%; text-align: right" class="input-text"></td>
              <td align="center"><span class="icon-group"><a href="javascript:delete_employee_row(<?php echo $option['user_id']?>)" tooltip="Delete" class="icon-button icon-16-delete"></a></span></td>
            </tr><?php
          }
        }
      }?>
    </tbody>
</table>

<script>
	var empdata = <?php echo json_encode($json)?>;
</script>