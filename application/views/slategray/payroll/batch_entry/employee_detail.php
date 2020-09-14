<table style="width:100%" class="default-table boxtype" id="listview-list">
    <colgroup>
      <col width="10%">
      <col width="25%">
      <col width="13%">
      <col width="23%">
      <col width="29%">
    </colgroup>
    <thead>
        <tr>
            <th>ID No</th>
            <th>Employee Name</th>
            <th>Quantity</th>
            <th>Unit Rate</th>
            <th>Amount</th>
        </tr>
    </thead>
    <tbody id="employee-list"><?php
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
          $where = ' AND u.employee_id in('. implode(',', $curemp) .')';
        }
      }

      $qry = "SELECT u.*, c.department, c.department_id
      FROM {$this->db->dbprefix}user u 
      LEFT JOIN {$this->db->dbprefix}user_company_department c ON u.department_id = c.department_id
      WHERE u.deleted = 0 AND u.inactive = 0 AND u.department_id != 0 AND u.department_id IS NOT NULL AND u.department_id != '' ".$where;

      $results = $this->db->query($qry); 
      if( $results->num_rows() > 0 ){
        foreach( $results->result() as $row ) : ?>
          <tr>
              <td align="right"><?php echo $row->login?></td>
              <td><?php echo $row->firstname.' '.$row->lastname?></td>
              <td align="center"><?php echo $curdata[$row->employee_id]['quantity']?></td>
              <td align="center"><?php echo $curdata[$row->employee_id]['unit_rate']?></td>
              <td align="center"><?php echo $curdata[$row->employee_id]['amount']?></td>
            </tr> <?php
        endforeach;
      } ?>
    </tbody>
</table>