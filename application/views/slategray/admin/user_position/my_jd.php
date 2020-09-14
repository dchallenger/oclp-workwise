<?php
	if(isset($jd_html)){
		echo $jd_html; ?>
		<div class="form-submit-btn <?php echo $show_wizard_control && isset($fieldgroups) && sizeof($fieldgroups) > 1 ? 'hidden' : '' ?>">
				<?php $this->load->view($buttons)?>            
    </div>
		<?php
	}
?>