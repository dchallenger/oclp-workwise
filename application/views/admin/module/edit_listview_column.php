<form class="style2 edit-view" name="editlistviewcolumn-form" id="editlistviewcolumn-form" method="post" enctype="multipart/form-data">
    <input type="hidden" name="listview_id" id="lvec_listview_id" value="<?php echo $listview_id?>" />
    <input type="hidden" name="field_id" id="lvec_field_id" value="<?php echo $field_id?>" />
    <div id="	">
        <div class="col-1-form">      
        	<h3 class="form-head"><?php echo$fieldlabel?> Column Setup</h3>
			<div class="form-item">
        		<label for="lvec_width" class="label-desc gray">Width<span class="red font-large">*</span></label>			
        		<div class="text-input-wrap"><input type="text" name="width" id="lvec_width" value="<?=$width?>" class="input-text"/></div>
            </div>
            <div class="clear"></div>
        	<div class="form-item">
        		<label for="lvec_alignment" class="label-desc gray">Alignment<span class="red font-large">*</span></label>			
        		<div class="select-input-wrap">
                	<?php
                    	$left = ($alignment == 'left' ? 'selected="selected"' : '');
						$center = ($alignment == 'center' ? 'selected="selected"' : '');
						$right = ($alignment == 'right' ? 'selected="selected"' : '');
					?>
                    <select name="alignment">
                    	<option value="left" <?php echo $left?>>Left</option>
                        <option value="right" <?php echo $right?>>Right</option>
                        <option value="center" <?php echo $center?>>Center</option>
                    </select>
                </div>
            </div>
        	<div class="clear"></div>
            <div class="form-item">
        		<label class="label-desc gray">Sort<span class="red font-large">*</span></label>			
        		<div class="radio-input-wrap">
                	<?php
                    	$yes = ($sort == 1 ? 'checked="checked"' : '');
						$no = ($sort == 0 ? 'checked="checked"' : '');
					?>
                    <input type="radio" class="input-radio" value="1" id="lvec_sort-yes" name="sort" <?php echo $yes?>>
                    <label class="check-radio-label gray" for="lvec_sort-yes">Yes</label>
                    <input type="radio" class="input-radio" value="0" id="lvec_sort-no" name="sort" <?php echo $no?>>
                    <label class="check-radio-label gray" for="lvec_sort-no">No</label>
					</div>
            </div>
            <div class="clear"></div>
            <div class="form-item">
        		<label for="lvec_sort_direction" class="label-desc gray">Sort Direction<span class="red font-large">*</span></label>			
        		<div class="select-input-wrap">
                	<?php
                    	$asc = ($alignment == 'asc' ? 'selected="selected"' : '');
						$desc = ($alignment == 'desc' ? 'selected="selected"' : '');
					?>
                    <select name="sort_direction">
                    	<option value="asc" <?php echo $asc?>>Ascending</option>
                        <option value="desc" <?php echo $desc?>>Descending</option>
                    </select>
                </div>
            </div>
        	<div class="clear"></div>
        </div>    
    </div>
    <div class="form-submit-btn">
    	<div class="icon-label-group">
    		<div class="icon-label">
    			<a onclick="lvec_save('');" href="javascript:void(0);" class="icon-16-disk">
    				<span>Save</span>
   				 </a>            
    		</div>
    	</div>
    	<div class="or-cancel">
    		<span class="or">or</span>
    		<a href="javascript:void(0)" class="cancel" onclick="Boxy.get(this).hide().unload();">Cancel</a>
    	</div>
    </div>
</form>
