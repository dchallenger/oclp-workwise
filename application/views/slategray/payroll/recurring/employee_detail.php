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
        $qry = "SELECT u.*, c.department, c.department_id, d.amount
        FROM {$this->db->dbprefix}user u 
        LEFT JOIN {$this->db->dbprefix}user_company_department c ON u.department_id = c.department_id
        LEFT JOIN {$this->db->dbprefix}payroll_recurring_employee d ON d.employee_id = u.user_id
        WHERE u.deleted = 0 AND u.inactive = 0 AND u.department_id != 0 AND u.department_id IS NOT NULL AND u.department_id != '' AND d.recurring_id = {$this->key_field_val}";

        $results = $this->db->query($qry);
        
        if( $results->num_rows() > 0 ){
          foreach ($results->result() as $row) { ?>
            <tr>
              <td align="right"><?php echo $row->login?></td>
              <td><?php echo $row->firstname. ' '. $row->middleinitial. ' '.$row->lastname.' '.$row->aux ?></td>
              <td align="center"><?php echo number_format($row->amount, 2, '.', ',')?></td>
              <td align="center"></td>
            </tr><?php
          }
        }
      }?>
    </tbody>
</table>

<script>
	var empdata = <?php echo json_encode($json)?>;
</script>