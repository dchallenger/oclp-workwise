<form class="style2 edit-view" name="disembark_form" >
	<input type="hidden" name="vessel_id" value="<?php echo $this->vessel_id?>">
	<div id="form-div">
		<h3 class="form-head">Disembark<a style="font-size: 12px;line-height: 18px;" onclick="toggleFieldGroup( $( this ) );return false;" class="align-right other-link noborder" href="#">Hide</a></h3>
		<div class="col-1-form">
			<div class="form-item odd ">
				<label class="label-desc gray" for="report_no">
					Report Number:
				</label>
				<div class="text-input-wrap"><input type="text" class="input-text" value="" id="report_no" name="report_no"></div>
			</div>
			<div class="form-item even ">
				<label class="label-desc gray" for="date_embark">
					Date Disembark:
				</label>
				<div class="text-input-wrap"><input type="text" class="input-text datepick datepicker" value="" id="date_disembark" name="date_disembark"></div>
			</div>

			<div class="form-item odd ">
				<label class="label-desc gray" for="employee_id">
					Employees:
				</label>
				<div class="multiselect-input-wrap">
					<input type="hidden" name="employee_id" value="">
					<?php
						$this->load->helper('form');
						$qry = "select a.employee_id, b.lastname, b.firstname, c.department
						FROM {$this->db->dbprefix}employee a
						LEFT JOIN {$this->db->dbprefix}user b on b.employee_id = a.employee_id
						LEFT JOIN {$this->db->dbprefix}user_company_department c on c.department_id = b.department_id
						WHERE a.deleted = 0 and a.resigned = 0 and b.deleted = 0 and b.inactive = 0
						AND a.vessel_id = $this->vessel_id  ORDER BY CONCAT(b.firstname, b.lastname)";

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
			<div class="form-item even ">
				<label class="label-desc gray" for="disembark_reason">
					Disembark Reason:
				</label>
				<div class="textarea-input-wrap"><textarea class="input-textarea" id="disembark_reason" name="disembark_reason" rows="5"></textarea></div>
			</div>
			<div class="form-item even ">
				<label class="label-desc gray" for="disembark_remarks">
					Remarks:
				</label>
				<div class="textarea-input-wrap"><textarea class="input-textarea" id="disembark_remarks" name="disembark_remarks" rows="5"></textarea></div>
			</div>

	                
		</div>
		<div class="clear"></div>
		<div class="spacer"></div>
		<div class="form-submit-btn ">
			<div class="icon-label-group">
				<div class="icon-label">
			    	<a onclick="save_disembark()" href="javascript:void(0);" class="icon-16-disk">
			        	<span>Save</span>
			     	</a>
			    </div>
			</div>
			<div class="or-cancel">
            	<span class="or">or</span>
            	<a class="cancel" href="javascript:void(0)" onclick="Boxy.get(this).hide().unload()">Cancel</a>
        	</div>
		</div>
	</div>
</form>
