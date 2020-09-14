<p>Select query:</p>
<!-- <div><?=get_export_dropdown($module_id)?></div> -->
<div class="select-input-wrap" style="width:300px">                        
    <?php 
    $options = array(
            '' => 'Please Select',
            'all'  => 'All',
            'active'  => 'Active',            
            'inactive'  => 'Inactive',            
            'resigned'  => 'Resigned',
        );

    echo form_dropdown('criteria', $options,'none','id="criteria"');
    ?>
</div>
<input type="hidden" id="quick_export_query" value="<?php echo $module_id ?>">

<div class="clear"></div>
<div id="field-container"></div>
<div class="clear"></div>
<div id="export-buttons" class="form-submit-btn hidden">
    <div class="icon-label-group align-left">
    <span class="form-submit-btn">
        <div class="icon-label"> <a onclick="$('#export-form').submit()" href="javascript:void(0);" class="icon-16-disk" > <span>Export</span> </a> </div>
    </span>    
</div>