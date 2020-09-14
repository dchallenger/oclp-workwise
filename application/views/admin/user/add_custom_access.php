<form class="style2 edit-view" name="add-custom-access-form" id="add-custom-access-form" method="post" enctype="multipart/form-data">
    <?php
		// get list of module actions
		$this->db->order_by('id');
		$actionlist = $this->db->get('module_action')->result_array();
				
		//get list of modules
		$this->db->order_by('parent_id, sequence');
		$this->db->select('module_id, short_name');
		$modulelist = $this->db->get('module')->result_array();
		
		if($this->input->post('details') != 'undefined'){
			$details = explode('-', $this->input->post('details'));
			$remove_tr = $details[0].'-'.$details[1];
		}
		else{
			$details[0] = "";
			$details[1] = "";
			$details[2] = "";
			$remove_tr = "";
		}
	?>
    <div id="form-div">
        <div class="col-1-form">      
        	<div class="form-item">
        		<label for="module_id" class="label-desc gray">Module<span class="red font-large">*</span></label>			
        		<div class="select-input-wrap">
                	<select name="module_id">
                    	<option value="">Select...</option>
					<?php 
                        foreach( $modulelist as $module )
                        {
                            echo '<option value="'.$module['module_id'].'" '.($details[1] == $module['module_id'] ? 'selected="selected"' : '').'>'.$module['short_name'].'</option>';
                        }
                    ?>
                    </select>
                </div>
            </div>
            <div class="clear"></div>
        	<div class="form-item">
        		<label for="module_action" class="label-desc gray">Action<span class="red font-large">*</span></label>			
        		<div class="select-input-wrap">
                	<select name="module_action" id="module_action">
                    	<option value="">Select...</option>
                    <?php 
                        foreach( $actionlist as $action )
                        {
                            echo '<option value="'.$action['action'].'" '.($details[0] == $action['action'] ? 'selected="selected"' : '').'>'.$action['action'].'</option>';
                        }
                    ?>
                    </select>
                </div>
            </div>
        	<div class="clear"></div>
            <div class="form-item">
        		<label for="module_access" class="label-desc gray">Access<span class="red font-large">*</span></label>			
        		<div class="select-input-wrap">
                	<select name="module_access" id="module_access">
                    	<option value="">Select...</option>
                        <option value="1" <?=($details[2] == "1" ? 'selected="selected"' : '')?>>Yes</option>
                        <option value="0" <?=($details[2] == "0" ? 'selected="selected"' : '')?>>No</option>
                    </select>
                </div>
            </div>
            <div class="clear"></div>
        </div>    
    </div>
    <div class="form-submit-btn">
    	<div class="icon-label-group">
    		<div class="icon-label">
    			<a onclick="add_to_access_list('<?=$remove_tr?>');" href="javascript:void(0);" class="icon-16-add">
    				<span>Add</span>
   				 </a>            
    		</div>
    	</div>
    	<div class="or-cancel">
    		<span class="or">or</span>
    		<a href="javascript:void(0)" class="cancel" onclick="Boxy.get(this).unload();">Cancel</a>
    	</div>
    </div>
</form>
