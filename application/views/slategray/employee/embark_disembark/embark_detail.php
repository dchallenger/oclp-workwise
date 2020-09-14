<style type="text/css">
  #embark_tab {
    display:block;
  }
  #disembark_tab {display:none;}
</style>

<script type="text/javascript">
function showTab(elem) {
  hideTabs();
  document.getElementById(elem).style.display = "block";
}

function hideTabs() {
  document.getElementById('embark_tab').style.display = "none";
  document.getElementById('disembark_tab').style.display = "none";
}

$(document).ready(function () {   

$('ul#grid-filter li').click(function(){
        $('ul#grid-filter li').each(function(){ $(this).removeClass('active') });
        $(this).addClass('active');
        $('#filter').val( $(this).attr('filter') );

        if( $(this).attr('filter') == 'for_approval' ){
            $('.status-buttons').parent().show();
        }
        else{
            $('.status-buttons').parent().hide();
        }
    });
});
</script>

    <!-- DETAILS OF TRANSACTION EMBARK/DISEMBARK -->
<table style="width:100%" class="default-table boxtype" id="listview-list">
  <?php
    $qry_info = "SELECT * 
          FROM {$this->db->dbprefix}vessel 
          WHERE deleted = 0 AND vessel_id = $this->key_field_val";
    $res_info = $this->db->query($qry_info)->row();
  ?>
    <div class="col-2-form view">
      <div class="form-item view odd ">
        <label class="label-desc view gray" for="report_no">Code:</label>
        <div class="text-input-wrap"><?php echo $res_info->vessel_code ?></div>
      </div>

      <div class="form-item view odd ">
        <label class="label-desc view gray" for="vessel_id">Vessel Name:</label>
        <div class="text-input-wrap"><?php echo $res_info->vessel?></div>
      </div>

      <div class="form-item view odd ">
        <label class="label-desc view gray" for="embark_from">Capacity:</label>
        <div class="text-input-wrap"><?php echo $res_info->capacity?></div>   
      </div>  
    </div>
  </table>
  <div>
    <label><h2>Employee List</h2></label>
  </div>
  <div class="clear"></div>
  <div class="spacer"></div>

   <div class="label-desc view gray">
      <?php
        $tab=$_REQUEST['tab'];
        if($tab=='');
   ?>
       <ul id="grid-filter">
        
      <?php if($tab=='embark_tab'){ ?>
      <li><b><span>Embark List</span></b></li>
          <? }else{ ?>
      <li class="active"><a href="?tab=embark_tab" onclick="showTab('embark_tab');return false;" ><span>Embark List</span></a></li>
          <? } ?>
      <?php if($tab=='disembark_tab'){ ?>
      <li><b><span>Disembark List</span></b></li>
          <? }else{ ?>
      <li><a href="?tab=disembark_tab" onclick="showTab('disembark_tab');return false;" ><span>Disembark List</span></a></li>
          <? } ?>
    </ul>
  </div>
  <div class="clear"></div>
  <div id="embark_tab">
    <table id="" class="default-table boxtype" style="width:100%">
    <thead>
        <th width="10%">Last Name</th>
        <th width="10%">First Name</th>
        <th width="10%">Company</th>
        <th width="10%">Position</th>
        <th width="10%">Date Embark</th>
        <th width="10%">Reason</th>
        <th width="10%">Remarks</th>
        <th width="10%">Date Disembark</th>
        <th width="10%">Reason</th>
        <th width="10%">Remarks</th>
    </thead>
    <tbody id="embark-list"><?php
        $qry = "SELECT lastname, firstname, company, position, v.date_embark, v.date_disembark, v.employee_id, v.vessel_id, v.disembark_reason, v.disembark_remarks, v.embark_reason, v.embark_remarks
        FROM {$this->db->dbprefix}employee_vessel_embark_disembark_detail v
        LEFT JOIN {$this->db->dbprefix}user u ON u.employee_id = v.employee_id
        LEFT JOIN {$this->db->dbprefix}user_position p ON u.position_id = p.position_id
        LEFT JOIN {$this->db->dbprefix}user_company c ON c.company_id = u.company_id
        WHERE v.deleted = 0 AND v.vessel_id = {$this->key_field_val} AND date_disembark IS NULL AND c.company_id = 1
        GROUP BY v.employee_id";

        $results = $this->db->query($qry);
        
        if( $results->num_rows() > 0 ){
          foreach ($results->result() as $index=>$row) { ?>
            <tr class="<?php echo ($ctr%2) ? 'odd': 'even'?>">
              <td><?php echo $row->lastname?></td>
              <td><?php echo $row->firstname?></td>
              <td><?php echo $row->company?></td>
              <td><?php echo $row->position?></td>
              <td><?php echo $row->date_embark?></td>
              <td><?php echo $row->embark_reason?></td>
              <td><?php echo $row->embark_remarks?></td>
              <td><?php echo $row->date_disembark?></td>
              <td><?php echo $row->disembark_reason?></td>
              <td><?php echo $row->disembark_remarks?></td>
            </tr><?php $ctr++;
          }
        }
      ?>
    </tbody>
  </table>
  </div>
  <div id="disembark_tab">
    <table id="" class="default-table boxtype" style="width:100%">
    <thead>
        <th width="10%">Last Name</th>
        <th width="10%">First Name</th>
        <th width="10%">Company</th>
        <th width="10%">Position</th>
        <th width="10%">Date Embark</th>
        <th width="10%">Reason</th>
        <th width="10%">Remarks</th>
        <th width="10%">Date Disembark</th>
        <th width="10%">Reason</th>
        <th width="10%">Remarks</th>
    </thead>
    <tbody id="embark-list"><?php
        $qry = "SELECT lastname, firstname, company, position, v.date_embark, v.date_disembark, v.employee_id, v.vessel_id, v.disembark_reason, v.disembark_remarks, v.embark_reason, v.embark_remarks
        FROM {$this->db->dbprefix}employee_vessel_embark_disembark_detail v
        LEFT JOIN {$this->db->dbprefix}user u ON u.employee_id = v.employee_id
        LEFT JOIN {$this->db->dbprefix}user_position p ON u.position_id = p.position_id
        LEFT JOIN {$this->db->dbprefix}user_company c ON c.company_id = u.company_id
        WHERE v.deleted = 0 AND v.vessel_id = {$this->key_field_val}  AND date_disembark IS NOT NULL AND c.company_id = 1
        GROUP BY v.employee_id";

        $results = $this->db->query($qry);
        
        if( $results->num_rows() > 0 ){
          foreach ($results->result() as $index=>$row) { ?>
            <tr class="<?php echo ($ctr%2) ? 'odd': 'even'?>">
              <td><?php echo $row->lastname?></td>
              <td><?php echo $row->firstname?></td>
              <td><?php echo $row->company?></td>
              <td><?php echo $row->position?></td>
              <td><?php echo $row->date_embark?></td>
              <td><?php echo $row->embark_reason?></td>
              <td><?php echo $row->embark_remarks?></td>
              <td><?php echo $row->date_disembark?></td>
              <td><?php echo $row->disembark_reason?></td>
              <td><?php echo $row->disembark_remarks?></td>
            </tr><?php $ctr++;
          }
        }
      ?>
    </tbody>
    </table>
  </div>



