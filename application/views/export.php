<?php $ci =& get_instance();
?>
<div class="form-submit-btn">
    <div class="icon-label-group align-left">
    <span class="form-submit-btn">
        <div class="icon-label"> <a onclick="$('#export-form').submit()" href="javascript:void(0);" class="icon-16-disk" > <span>Export</span> </a> </div>
    </span>
    <div class="icon-label"> <a href="javascript:void(0);" class="icon-16-listback" rel="back-to-list"> <span>Back</span> </a> </div>
</div>

<div class="clear"></div>

<?=$ci->load->view('export_fields')?>