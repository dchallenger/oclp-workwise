    <!-- EDIT TRANSACTION EBMARK/DISEMBARK -->
<form name="editview" style="overflow:auto; min-height:100px">
    <!-- INFO -->
  <?php
  if(!empty($this->key_field_val)){
    $report_id = $this->key_field_val;
  }else{
    $report_id = $this->report_id;
  }
  $qry_info = "SELECT report_no, embark_from, embark_to, vessel, reason, remarks
              FROM {$this->db->dbprefix}employee_vessel_embark_disembark ed
              LEFT JOIN {$this->db->dbprefix}vessel v ON ed.vessel_id = v.vessel_id
              WHERE ed.deleted = 0 AND ed.report_id = $report_id";
  $res_info = $this->db->query($qry_info)->row();
  $embark_from_to = date("Y-m-d",strtotime($res_info->embark_from)).' To '.date("Y-m-d",strtotime($res_info->embark_to));
  ?>

  
  <table id="" class="default-table boxtype" style="width:100%">
    <thead>
      <th width="5%"></th>
      <th width="15%">Last Name</th>
      <th width="15%">First Name</th>
      <th width="15%">Company</th>
      <th width="10%">Position</th>
      <th width="10%">Date Embark</th>
      <th width="10%">Date Disembark</th>
      <th width="10%">Reason</th>
      <th width="10%">Remarks</th>
    </thead>
    <tbody>
      <?php
          $qry = "SELECT lastname, firstname, company, position_code, embark_from, v.date_disembark, v.employee_id, v.report_id, v.vessel_id, v.report_no, v.disembark_reason, v.disembark_remarks, v.report_id
          FROM {$this->db->dbprefix}employee_vessel_embark_disembark_detail v
          LEFT JOIN {$this->db->dbprefix}user u ON u.employee_id = v.employee_id
          LEFT JOIN {$this->db->dbprefix}user_position p ON u.position_id = p.position_id
          LEFT JOIN {$this->db->dbprefix}user_company c ON c.company_id = u.company_id
          WHERE v.deleted = 0 AND v.report_id = $report_id
          GROUP BY v.employee_id";
          $results = $this->db->query($qry);
          
          if( $results->num_rows() > 0 ){
            $ctr = 0;
            foreach ($results->result() as $index => $row) { ?>
              <tr class="<?php echo ($ctr%2) ? 'odd': 'even'?>">
                <td align="center">
                  <?php
                   if(!empty($row->date_disembark))
                      $checked = 'checked="checked" disabled';
                  else
                      $checked = '';
                  $name = 'name="disembark_cb['.$ctr.']"';
                  $emp_id = 'name="employee_id['.$ctr.']"';
                  ?>
                  <input type="checkbox" <?php echo $name ?> class="disembark_cb" <?php echo $checked ?> readonly/>
                  <input  <?php echo $emp_id ?> value="<?php echo $row->employee_id?>" type="hidden">
                  <input  name="vessel_id" value="<?php echo $row->vessel_id?>" type="hidden">
                  <input  name="report_no" value="<?php echo $row->report_no?>" type="hidden">
                  <input  name="report_id" value="<?php echo $row->report_id?>" type="hidden">
                </td>

                <td><?php echo $row->lastname?></td>
                <td><?php echo $row->firstname?></td>
                <td><?php echo $row->company?></td>
                <td><?php echo $row->position_code?></td>
                <td><?php echo $row->embark_from?></td>
                <td><?php echo $row->date_disembark?></td>
                <td><?php echo $row->disembark_reason?></td>
                <td><?php echo $row->disembark_remarks?></td>
              </tr><?php 
              $ctr++;
            }
          }
        ?>

    </tbody>  
  <table>
    <div class="clear"></div>
    <div class="spacer"></div>
    <!-- <div class="form-submit-btn "> -->
      <!-- <div class="icon-label-group">
        <div class="icon-label">
            <a onclick="save_editview()" href="javascript:void(0);" class="icon-16-disk">
                <span>Save</span>
            </a>
          </div>
      </div>
      <div class="or-cancel">
              <span class="or">or</span>
              <a class="cancel" href="javascript:void(0)" onclick="Boxy.get(this).hide().unload()">Go Back</a>
          </div>
    </div> -->
</form>
