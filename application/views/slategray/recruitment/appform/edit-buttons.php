<?php $record_id = $this->input->post('record_id'); ?>
<div class="icon-label-group align-left">    
    <span class="form-submit-btn hidden">
        <div class="icon-label"> <a onclick="ajax_save('', <?php echo $show_wizard_control ? 1 : 0 ?>)" href="javascript:void(0);" class="icon-16-disk" rel="record-save"> <span>Submit</span> </a> </div>       
    </span>
    
</div>