<div class="icon-label-group">
    <div class="icon-label">
         <a rel="record-save" class="icon-16-disk" href="javascript:void(0);" onclick="time_in_out('<?= $button_name ?>')">
            <span><?= ($button_name == "time_in1") ? "Time In" : "Time Out" ?></span>
        </a>
    </div>
</div>
<div class="or-cancel">
    <span class="or">or</span>    
    <a class="cancel" href="<?=site_url('dtr/uploading')?>">Cancel</a>
</div>

