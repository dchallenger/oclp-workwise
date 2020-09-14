<div class="icon-label-group">
    <div class="icon-label">
        <a rel="record-save" class="icon-16-disk" href="javascript:void(0);" onclick="save_partial()">
            <span>Save</span>
        </a>
    </div>
    <div class="icon-label">
        <a rel="record-save-back" class="icon-16-disk-back" href="javascript:void(0);" onclick="save_partial(preemployment_back, <?php echo $show_wizard_control ? 1 : 0 ?>)">
            <span>Save &amp; Back</span>
        </a>
    </div>
    <div class="icon-label">
        <a rel="record-save-back" class="icon-16-disk" href="javascript:void(0);"  onclick="ajax_save('', <?php echo $show_wizard_control ? 1 : 0 ?>)">
            <span>Mark As Complete</span>
        </a>
    </div>    
    <div class="icon-label">
        <a rel="back-to-list" class="icon-16-listback" href="javascript:void(0);">
            <span>Back to list</span>
        </a>
    </div>
</div>
<div class="or-cancel">
    <span class="or">or</span>    
    <a class="cancel" href="<?=site_url('recruitment/preemployment/detail/' . $raw_data['preemployment_id'])?>">Cancel</a>
</div>