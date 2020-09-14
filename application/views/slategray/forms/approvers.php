<?php if(!IS_AJAX || $this->method == 'quick_edit') :?><div id="approvers-container" class="col-2-form view"><?php endif;?>
<?php
	if(isset( $approvers )){
		foreach( $approvers as $approver){ 
			$class = $approver['sequence'] % 2 == 0 ? 'even' : 'odd';
			
			$user = $this->db->get_where('user', array('employee_id' => $approver['approver']))->row()?>
			<div class="form-item view <?php echo $class?>">
				<label class="label-desc view gray">Approver</label>
				<div class="text-input-wrap"><?php echo $user->firstname?> <?php echo $user->lastname?></div>
			</div> <?php
		}
	}
?>
<?php if(!IS_AJAX || $this->method == 'quick_edit') :?></div><?php endif;?>