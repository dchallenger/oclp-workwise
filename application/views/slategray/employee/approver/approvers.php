<?php if(!IS_AJAX) :?><div id="approvers-container" class="col-2-form view"><?php endif;?>
<?php
	if(isset( $approvers )){
		foreach( $approvers as $approver){ 
			//$class = $sequence % 2 == 0 ? 'odd' : 'even';
			$user = $this->db->get_where('user', array('employee_id' => $approver['approver']))->row();
			$module = $this->db->get_where('module', array('module_id' => $approver['module_id']))->row();
			?>
			<div class="form-item view odd">
				<label class="label-desc view gray">Approver</label>
				<div class="text-input-wrap"><?php echo $user->firstname." ".$user->middleinitial." ".$user->lastname." ".$user->aux?></div>
			</div>
			<div class="form-item view even">
				<label class="label-desc view gray">Module</label>
				<div class="text-input-wrap"><?php echo $module->short_name?></div>
			</div> <?php
		}
	}
?>
<?php if(!IS_AJAX) :?></div><?php endif;?>