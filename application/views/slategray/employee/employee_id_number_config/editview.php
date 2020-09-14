<style>
.form-item1,.form-item2 {
    float: left;
    margin-bottom: 10px;
}

.form-item1 {
    width: 45% !important;
}

.form-item2 {
    width: 5%;
}

</style>

	<?php if( isset($error) ) : ?>				
        <div id="message_box" class="attention" style="padding-left: 60px;width:60%;margin: 0 auto;">
            <img src="<?=base_url().$this->userinfo['theme']; ?>/images/exclamation-big.png" alt="" >
            <h3 style="margin: 0.3em 0 0.5em 0">Oops! <?=$error?></h3>

            <p><?=$error2?></p>
        </div>
    <?	else :?>
            <div class="form-submit-btn">
                <div class="icon-label-group">
                    <div class="icon-label">
                        <a rel="record-save" class="icon-16-disk" href="javascript:void(0);" onclick="ajax_save('')">
                            <span>Save</span>
                        </a>            
                    </div>
                    <div class="icon-label">
                        <a rel="record-save-back" class="icon-16-disk-back" href="javascript:void(0);" onclick="ajax_save('back')">
                            <span>Save &amp; Back</span>
                        </a>            
                    </div>
                </div>
                <div class="or-cancel">
                    <span class="or">or</span>
                    <a class="cancel" href="javascript:void(0)" rel="action-back">Cancel</a>
                </div>
            </div>                
            <div class="clear"></div>
            <div id="employee_id_number_config_container" class="hidden">
                <div class="header-container">
                    <div class="form-item odd">
                        <label for="company_code" class="label-desc gray">Id Number Config Type:</label>
                        <div class="multiselect-input-wrap">
                            <select id="id_number_config_type" name="id_number_config[employee_id_number_config_type_id][]" style="width:400px">
                                <?php
                                    if ($id_no_config_type && $id_no_config_type->num_rows() > 0){
                                        foreach ($id_no_config_type->result() as $row) {
                                            print '<option value="'.$row->employee_id_number_config_type_id.'">'.$row->employee_id_number_config_type.'</option>';
                                        }
                                    }
                                ?>
                            </select>
                        </div>                        
                    </div>
                    <div class="form-item1 even">
                        <label for="company_code" class="label-desc gray">Id Number Config Value:</label>
                        <div class="text-input-wrap">
                            <input type="text" class="input-text" value="" id="company_location" name="id_number_config[employee_id_number_config_value][]">
                        </div>                      
                    </div>
                    <div class="form-item2">
                        <div style="padding-top:13px">
                            <div class="icon-label" style="clear:left">
                                <a class="icon-16-delete icon-delete" href="javascript:void(0)">
                                    <span>Delete</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>                                   
            </div>            
            <form class="style2 edit-view" name="record-form" id="record-form" method="post" enctype="multipart/form-data">
		        <input type="hidden" name="previous_page" id="previous_page" value="<?=base_url().$this->module_link?>/detail"/>        
                <div>
                    <label style="">ID Number: &nbsp;&nbsp;<a class="icon-button icon-16-add" style="vertical-align:middle" href="javascript:void(0);" onclick="add_id_number_type()"></a></label>
                </div>            
                <br /><br />    
                <div class="col-2-form" id="id_number_config_container">
                    <?php
                        if ($id_no_config && $id_no_config->num_rows() > 0){
                            foreach ($id_no_config->result() as $row_info) {
                    ?> 
                                <div class="header-container">                                       
                                    <div class="form-item odd">
                                        <label for="company_code" class="label-desc gray">Id Number Config Type:</label>
                                        <div class="multiselect-input-wrap">
                                            <select id="id_number_config_type" name="id_number_config[employee_id_number_config_type_id][]" style="width:400px">
                                                <?php
                                                    if ($id_no_config_type && $id_no_config_type->num_rows() > 0){
                                                        foreach ($id_no_config_type->result() as $row) {
                                                            print '<option value="'.$row->employee_id_number_config_type_id.'" '.($row->employee_id_number_config_type_id == $row_info->employee_id_number_config_type_id ? 'SELECTED' : '').'>'.$row->employee_id_number_config_type.'</option>';
                                                        }
                                                    }
                                                ?>
                                            </select>
                                        </div>                        
                                    </div>
                                    <div class="form-item1 even">
                                        <label for="company_code" class="label-desc gray">Id Number Config Value:</label>
                                        <div class="text-input-wrap">
                                            <input type="text" class="input-text" value="<?php echo $row_info->employee_id_number_config_value ?>" id="company_location" name="id_number_config[employee_id_number_config_value][]">
                                        </div>                      
                                    </div>
                                    <div class="form-item2">
                                        <div style="padding-top:13px">
                                            <div class="icon-label" style="clear:left">
                                                <a class="icon-16-delete icon-delete" href="javascript:void(0)">
                                                    <span>Delete</span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>  
                                </div>                                    
                    <?php
                            }
                        }                    
                    ?>                                       
                    <div class="clear"></div>
                </div>
                <div class="clear"></div>
                <div class="spacer"></div>
                
            </form>
            <?php
                if( isset($views_outside_record_form) && sizeof($views_outside_record_form) > 0 ) :
                    foreach($views_outside_record_form as $view) :
                        $this->load->view($this->userinfo['rtheme'].'/'.$view);
                    endforeach;
                endif;
            ?>    
            <div class="form-submit-btn">
                <div class="icon-label-group">
                    <div class="icon-label">
                        <a rel="record-save" class="icon-16-disk" href="javascript:void(0);" onclick="ajax_save('')">
                            <span>Save</span>
                        </a>            
                    </div>
                    <div class="icon-label">
                        <a rel="record-save-back" class="icon-16-disk-back" href="javascript:void(0);" onclick="ajax_save('back')">
                            <span>Save &amp; Back</span>
                        </a>            
                    </div>
                </div>
                <div class="or-cancel">
                    <span class="or">or</span>
                    <a class="cancel" href="javascript:void(0)" rel="action-back">Cancel</a>
                </div>
            </div>                
            <div class="clear"></div><?php
        endif;
    ?>
    <!-- END MAIN CONTENT -->
</div>