<form enctype="multipart/form-data" method="post" id="picklist-value-form" name="record-form" class="style2 edit-view">
    <input type="hidden" name="value_id" value="<?=$value_id?>" />
    <input type="hidden" name="picklist_name" value="<?=$picklist_name?>" />
    <input type="hidden" name="picklist_table" value="<?=$picklist_table?>" />
    <?php
    	if($value_id != -1)
		{
			$this->db->select($picklist_name.', description');
			$this->db->from($picklist_table);
			$this->db->where( array( $picklist_name.'_id' => $value_id ) );
			$details = $this->db->get()->row();
		}
		else{
			$details->$picklist_name = '';
			$details->description = '';
		}
	?>
    <div id="form-div">
    	<h3 class="form-head">Value Information</h3>
        <div class="col-2-form">      
			<div class="form-item odd">
				<label class="label-desc gray" for="field_id">Value: <span class="red font-large">*</span></label>			
				<div class="text-input-wrap">
					<input type="text" style="width: 90%;" class="input-text" name="picklist_value" value="<?=$details->$picklist_name?>" />
                </div>
            </div>
            <div class="form-item even">
                <label class="label-desc gray" for="value_description">Description:</label>			
                <div class="textarea-input-wrap">
                    <textarea class="input-textarea" id="picklist_value_description" name="picklist_value_description" rows="5"><?=$details->description?></textarea>
                </div>
            </div>
        </div>
        <div class="form-submit-btn">
            <div class="icon-label-group">
                <div class="icon-label">
                    <a onclick="picklist_value_save('')" href="javascript:void(0);" class="icon-16-disk" rel="record-save">
                        <span>Save</span>
                    </a>            
                </div>
                <div class="icon-label">
                    <a onclick="picklist_value_save('back')" href="javascript:void(0);" class="icon-16-disk-back" rel="record-save-back">
                        <span>Save &amp; Close</span>
                    </a>            
                </div>
            </div>
            <div class="or-cancel">
                <span class="or">or</span>
                <a onclick="Boxy.get(this).hide()" href="javascript:void(0)" class="cancel">Cancel</a>
            </div>
        </div>
    </div>
</form>