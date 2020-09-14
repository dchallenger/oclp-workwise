<?php $record_id = $this->input->post('record_id'); ?>
<?php /*
<div class="icon-label-group align-left">
    <span class="<?php echo ($record_id < 0) ? 'form-submit-btn' : '' ?> <?php echo $show_wizard_control && isset($fieldgroups) && sizeof($fieldgroups) > 1 && $record_id < 0? 'hidden' : '' ?>">
	<?php if ($status == 'Draft'):?>
        <div class="icon-label"> <a onclick="ajax_save('', <?php echo $show_wizard_control ? 1 : 0 ?>)" href="javascript:void(0);" class="icon-16-disk" rel="record-save"> <span>Save as Draft</span> </a> </div>
        <!-- div class="icon-label"> <a onclick="ajax_save('back', <?php echo $show_wizard_control ? 1 : 0 ?>)" href="javascript:void(0);" class="icon-16-disk-back" rel="record-save-back"> <span>Save &amp; Back</span> </a> </div -->
        <div class="icon-label"> <a href="javascript:save_and_email(true);" class="icon-16-send-email" rel="record-save-email"> <span>Save &amp; Send Request</span> </a> </div>
    <?php else:?>
    	<div class="icon-label"> <a onclick="ajax_save('', <?php echo $show_wizard_control ? 1 : 0 ?>)" href="javascript:void(0);" class="icon-16-disk" rel="record-save"> <span>Save</span> </a> </div>
    <?php endif;?>
    </span>

    <div class="icon-label add-more-div"><a href="javascript:void(0);" class="icon-16-add icon-16-add-listview add-more"><span>Add</span></a></div>
</div>

<div class="icon-label-group align-left">
    <div class="icon-label"> <a href="javascript:void(0);" class="icon-16-listback" rel="back-to-list"> <span>Back to list</span> </a> </div>
</div>
*/ ?>

<div class="icon-label-group align-left">
    <span class="<?php echo ($record_id < 0) ? 'form-submit-btn' : '' ?> <?php echo $show_wizard_control && isset($fieldgroups) && sizeof($fieldgroups) > 1 && $record_id < 0? 'hidden' : '' ?>">
    <?php if(($status == 'Draft')):?>
        <div class="icon-label"> <a onclick="save_as_draft('', <?php echo $show_wizard_control ? 1 : 0 ?>)" href="javascript:void(0);" class="icon-16-disk" rel="record-save"> <span>Save as Draft</span> </a> </div>
        <!-- div class="icon-label"> <a onclick="ajax_save('back', <?php echo $show_wizard_control ? 1 : 0 ?>)" href="javascript:void(0);" class="icon-16-disk-back" rel="record-save-back"> <span>Save &amp; Back</span> </a> </div -->
        <div class="icon-label"> <a href="javascript:save_and_send_email(true);" class="icon-16-send-email" rel="record-save-email"> <span>Save &amp; Send Request</span> </a> </div>
   
    <?php elseif($status == 'For Evaluation'):?>
        <div class="icon-label"> <a href="javascript:save_evaluated_mrf('back', <?php echo $show_wizard_control ? 1 : 0 ?>);" class="icon-16-send-email" rel="record-save-email"> <span>Save &amp; Send Request</span> </a> </div>
    
    <?php elseif($status == 'For HR Review'):?>
        <div class="icon-label"> <a onclick="save_and_send('', <?php echo $show_wizard_control ? 1 : 0 ?>)" href="javascript:void(0);" class="icon-16-disk" rel="record-save"> <span>Save</span> </a> </div>
        <div class="icon-label"> <a href="javascript:save_and_email(true);" class="icon-16-send-email" rel="record-save-email"> <span>Save &amp; Send Request</span> </a> </div>
    <?php else:?>
        <div class="icon-label"> <a onclick="save_and_send('back', <?php echo $show_wizard_control ? 1 : 0 ?>)" href="javascript:void(0);" class="icon-16-disk" rel="record-save"> <span>Save</span> </a> </div>

    <?php endif;?>
    </span>

    <div class="icon-label add-more-div"><a href="javascript:void(0);" class="icon-16-add icon-16-add-listview add-more"><span>Add</span></a></div>
</div>

<div class="icon-label-group align-left">
    <div class="icon-label"> <a href="javascript:void(0);" class="icon-16-listback" rel="back-to-list"> <span>Back to list</span> </a> </div>
</div>


<script>

    function save_as_draft(on_success, is_wizard , callback){

        $('#status').val('Draft');
        var is_hr = $("#is_hr").val();

        if (is_hr == 'false') {
            $('#status_hr').val('Draft');
        };

        ajax_save(on_success, is_wizard , callback);

    }

    function save_evaluated_mrf(on_success, is_wizard , callback){

        if( $('label[for="reason_for_request"]').parent().find('input[value="1"]').attr('checked') == 'checked' ){
            $('#status').val('For Approval');
        }
        else{
            <?php if(CLIENT_DIR === 'oams'): ?>
                $('#status').val('For Approval');
            <?php else:?>
                $('#status').val('For HR Review');
            <?php endif;?>
            
        }

        ajax_save(on_success, is_wizard , callback);
    }

    function save_and_send_email(is_wizard, msg){

        // if( $('label[for="reason_for_request"]').parent().find('input[value="1"]').attr('checked') == 'checked' ){
        //     $('#status').val('For Approval');
        // }
        // else{
        //     <?php if(CLIENT_DIR === 'oams'): ?>
        //         $('#status').val('For Approval');
        //     <?php else:?>
        //         $('#status').val('For HR Review');
        //     <?php endif;?>
        // }
        var on_success = 'back';
        var callback = "";

        var is_hr = $("#is_hr").val();
        // $('#status_hr').val('For HR Review');
        save_and_email(true);   
        // if (is_hr != 'false') {
        //     save_and_email(true);    
        // } else{
        //     $('#status_hr').val('For HR Review');
        //     ajax_save(on_success, is_wizard , callback);
        // };
        
    }

    function save_and_send(on_success, is_wizard , callback){
        ajax_save(on_success, is_wizard , callback);
    }
</script>
