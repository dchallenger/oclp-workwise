<div class="icon-label-group">

    <div class="icon-label">
        <a rel="record-save" class="icon-16-disk" href="javascript:void(0);" onclick="ajax_save('', <?php echo $show_wizard_control ? 1 : 0 ?>)">
            <span>Save</span>
        </a>
    </div>
    
 <?php if (!$email_sent):?>     
    <div class="icon-label">
        <a rel="record-save-back" class="icon-16-disk icon-16-send-email" href="javascript:void(0);"  onclick="ajax_save('email', <?php echo $show_wizard_control ? 1 : 0 ?>)">
            <span>Save &amp; Send</span>
        </a> 
    </div>   
    <?php else:?> 
    <div class="icon-label">
        <a rel="record-save" class="icon-16-disk" href="javascript:void(0);" onclick="ajax_save('back', <?php echo $show_wizard_control ? 1 : 0 ?>)">
            <span>Save &amp; Back</span>
        </a>
    </div>
    <?php endif;?>
    <div class="icon-label">
        <a class="icon-16-listback" href="javascript:void(0)" rel="action-back"><span>Back</span></a>        
    </div>
</div>

