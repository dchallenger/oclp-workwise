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

</script>


	<!-- EMBARK EMPLOYEE -->
<form name="embark_form" style="overflow:auto;">
	<input type="hidden" name="vessel_id" value="<?php echo $this->vessel_id?>">
	<div id="form-div">
		<h3 class="form-head">Embark<a style="font-size: 12px;line-height: 18px;" onclick="toggleFieldGroup( $( this ) );return false;" class="align-right other-link noborder" href="#">Hide</a></h3>
		<div class="col-2-form view">
			<?php
  				$qry_info = "SELECT * 
              	FROM {$this->db->dbprefix}vessel
              	WHERE deleted = 0 AND vessel_id = $this->vessel_id";
  				$res_info = $this->db->query($qry_info)->row();
  			?>
		    <div class="form-item view odd ">
		      	<label class="label-desc view gray" for="report_no">Code:</label>
		      	<div class="text-input-wrap"><?php echo $res_info->vessel_code ?></div>
		    </div>

		    <div class="form-item view even ">
		      	<label class="label-desc view gray" for="embark_reason">Reason:</label>
		      	<div class="text-input-wrap" ><input type="text" class="input-text " value="" id="embark_reason" name="embark_reason" style="width:250px"></div>
		    </div>

		    <div class="form-item view odd ">
		      	<label class="label-desc view gray" for="vessel_id">Vessel Name:</label>
		      	<div class="text-input-wrap"><?php echo $res_info->vessel?></div>    
		    </div>

		    <div class="form-item view even ">
		      	<label class="label-desc view gray" for="embark_remarks">Remarks:</label>
		      	<div class="text-input-wrap" ><input type="text" class="input-text " value="" id="embark_remarks" name="embark_remarks" style="width:250px"></div>
		    </div>  

		    <div class="form-item view odd ">
		      	<label class="label-desc view gray" for="embark_from">Capacity:</label>
		     	<div class="text-input-wrap"><?php echo $res_info->capacity?></div>   
		    </div>  

		    <div class="form-item view even">
		      	<label class="label-desc view gray" for="date_embark">Date Embark:<span class="red font-large">*</span></label>
      			<div class="text-input-wrap">
        			<input type="text" class="input-text datepick datepicker" value="" id="date_embark" name="date_embark" style="width:200px">
      			</div>    
		    </div>  
	  		<div class="clear"></div>
			<div class="spacer"></div>
    		<div class="form-item view odd">
    			<label class="label-desc gray" for="employee_id">Employee:<span class="red font-large">*</span></label>
				<div class="multiselect-input-wrap">
					<input type="hidden" name="employee_id" value="">
					<?php
						$this->load->helper('form');
						$qry = "select a.employee_id, b.lastname, b.firstname, c.department
						FROM {$this->db->dbprefix}employee a
						LEFT JOIN {$this->db->dbprefix}user b on b.employee_id = a.employee_id
						LEFT JOIN {$this->db->dbprefix}user_company_department c on c.department_id = b.department_id
						WHERE a.deleted = 0 and a.resigned = 0 and b.deleted = 0 and b.inactive = 0 AND b.company_id = 1
						AND (a.vessel_id is null OR a.vessel_id = '' ) ORDER BY CONCAT(b.firstname, b.lastname)";

						$employees = $this->db->query( $qry );
						$employee_array = array();
						if( $employees->num_rows() > 0 ){
							foreach( $employees->result() as $row ){
								$employee_array[$row->department][$row->employee_id] = $row->firstname . ' ' . $row->lastname;	
							}
						}
						echo form_dropdown('employee_id-multiselect', $employee_array, '', 'style="width:400px;" multiple');
					?>
				</div>
			</div>  
			
		</div>
		<div>
    		<label><h2>Employee List</h2></label>
  		</div>
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
			    <tbody>
			      <?php
			          $qry = "SELECT lastname, firstname, company, position, v.date_embark, v.date_disembark, v.employee_id, v.vessel_id, v.disembark_reason, v.disembark_remarks, v.embark_reason, v.embark_remarks
			          FROM {$this->db->dbprefix}employee_vessel_embark_disembark_detail v
			          LEFT JOIN {$this->db->dbprefix}user u ON u.employee_id = v.employee_id
			          LEFT JOIN {$this->db->dbprefix}user_position p ON u.position_id = p.position_id
			          LEFT JOIN {$this->db->dbprefix}user_company c ON c.company_id = u.company_id
			          WHERE v.deleted = 0 AND v.vessel_id = $this->vessel_id AND date_disembark IS NULL AND c.company_id = 1";
			          $results = $this->db->query($qry);
			          
			          if( $results->num_rows() > 0 ){
			            $ctr = 0;
			            foreach ($results->result() as $index => $row) { ?>
			              <tr class="<?php echo ($ctr%2) ? 'odd': 'even'?>">
			                  <input  <?php echo $emp_id ?> value="<?php echo $row->employee_id?>" type="hidden">
			                  <input name="vessel_id" value="<?php echo $row->vessel_id?>" type="hidden">
			                  <input name="date_embark" value="<?php echo $row->date_embark?>" type="hidden">
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
			    <tbody>
			      <?php
			          $qry = "SELECT lastname, firstname, company, position, v.date_embark, v.date_disembark, v.employee_id, v.vessel_id, v.disembark_reason, v.disembark_remarks, v.embark_reason, v.embark_remarks
			          FROM {$this->db->dbprefix}employee_vessel_embark_disembark_detail v
			          LEFT JOIN {$this->db->dbprefix}user u ON u.employee_id = v.employee_id
			          LEFT JOIN {$this->db->dbprefix}user_position p ON u.position_id = p.position_id
			          LEFT JOIN {$this->db->dbprefix}user_company c ON c.company_id = u.company_id
			          WHERE v.deleted = 0 AND v.vessel_id = $this->vessel_id AND date_disembark IS NOT NULL AND c.company_id = 1";
			          $results = $this->db->query($qry);
			          
			          if( $results->num_rows() > 0 ){
			            $ctr = 0;
			            foreach ($results->result() as $index => $row) { ?>
			              <tr class="<?php echo ($ctr%2) ? 'odd': 'even'?>">
			                  <input  <?php echo $emp_id ?> value="<?php echo $row->employee_id?>" type="hidden">
			                  <input name="vessel_id" value="<?php echo $row->vessel_id?>" type="hidden">
			                  <input name="date_embark" value="<?php echo $row->date_embark?>" type="hidden">
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
			              </tr><?php 
			            }
			          }
			        ?>
			 	</tbody>  
		 	</table>
		</div>

  		<div>
			
  		</div>
		<div class="clear"></div>
		<div class="spacer"></div>
		<div class="form-submit-btn ">
			<div class="icon-label-group">
				<div class="icon-label">
			    	<!-- <a onclick="save_embark()" href="javascript:embark('<?php echo $this->vessel_id ?>');" class="icon-16-disk"> -->
			    	<a onclick="save_embark('<?php echo $this->vessel_id ?>')" href="javascript:void(0);" class="icon-16-disk">
			        	<span>Save</span>
			     	</a>
			    </div>
			</div>
			<div class="or-cancel">
            	<span class="or">or</span>
            	<a class="cancel" href="javascript:void(0)" onclick="Boxy.get(this).hide().unload()">Close</a>
        	</div>
		</div>
	</div>
</form>

