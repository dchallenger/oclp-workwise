<style type="text/css">
.form-item {
    clear: left;
    float: left;
}
.form-item input {
    display: inline-block;
    float: left;
    height: 100%;
    width: auto;
}
.form-item span {
    display: inline-block;
    float: left;
    padding: 0 0 0 10px;
    width: 90%;
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
<!--                     <div class="icon-label">
                        <a rel="record-save-back" class="icon-16-disk-back" href="javascript:void(0);" onclick="ajax_save('back')">
                            <span>Save &amp; Back</span>
                        </a>            
                    </div> -->
                </div>
<!--                 <div class="or-cancel">
                    <span class="or">or</span>
                    <a class="cancel" href="javascript:void(0)" rel="action-back">Cancel</a>
                </div> -->
            </div>                
            <div class="clear"></div>          
            <form class="style2 edit-view" name="record-form" id="record-form" method="post" enctype="multipart/form-data">
		        <input type="hidden" name="previous_page" id="previous_page" value="<?=base_url().$this->module_link?>/detail"/>          
                <br /><br />
                <?php
                    $result_check_list_category = $this->db->get('ir_process_checklist_master_category');
                    if ($result_check_list_category && $result_check_list_category->num_rows() > 0){
                        foreach ($result_check_list_category->result() as $row_category) {
                            print '<div><h3 class="form-head">'.$row_category->ir_checklist_master_category.'
                                <a style="font-size: 12px;line-height: 18px;" onclick="toggleFieldGroupVisibility( $( this ) );" class="align-right other-link noborder" href="javascript:void(0)">Hide</a>
                            </h3>';
                ?>                                    
                            <div class="col-1-form">
                                <?php
                                    $check_list = array();
                                    $result_check_list = $this->db->get('ir_process_checklist');
                                    if ($result_check_list && $result_check_list->num_rows() > 0){
                                        $check_list = unserialize($result_check_list->row()->ir_checklist);
                                    }

                                    $this->db->order_by('ir_checklist_master');
                                    $this->db->where('ir_checklist_master_category_id',$row_category->ir_checklist_master_category_id);
                                    $result = $this->db->get('ir_process_checklist_master');
                                    if ($result && $result->num_rows() > 0){
                                        foreach ($result->result() as $row_info) {                                
                                ?>
                                            <div class="form-item odd" style="border-bottom: 1px dotted rgb(204, 204, 204);">
                                                <input type="checkbox" value="<?php echo $row_info->ir_checklist_master_id ?>" name="ir_process_checklist[]" <?php echo (in_array($row_info->ir_checklist_master_id, $check_list) ? "CHECKED" : "")?>>
                                                <span><?php echo $row_info->ir_checklist_master ?></span>
                                            </div>                    
                                <?php
                                        }
                                    }
                                ?>                                     
                                <div class="clear"></div>
                            </div>
                        </div>
                <?php
                        }
                    }                
                ?>
                <div class="clear"></div>
                <div class="spacer"></div>
                <?php
                    if( isset($views_outside_record_form) && sizeof($views_outside_record_form) > 0 ) :
                        foreach($views_outside_record_form as $view) :
                            $this->load->view($this->userinfo['rtheme'].'/'.$view);
                        endforeach;
                    endif;
                ?>   
                <div id="form-div"><?php
                    if (isset($fieldgroups) && sizeof($fieldgroups) > 0) :
                        $load_jqgrid_in_boxy = false;
                        $load_ckeditor = false;
                        $load_multiselect = false;
                        $load_uploadify = false;
                        $chosen_autocomplete = false;
                        $js = array();
                        foreach ($fieldgroups as $fieldgroup) : ?>
                            <div fg_id="<?php echo $fieldgroup['fieldgroup_id'] ?>" id="fg-<?php echo $fieldgroup['fieldgroup_id'] ?>" class="<?php echo $show_wizard_control ? 'wizard-type-form hidden' : '' ?>">
                                <h3 class="form-head"><?= $fieldgroup['fieldgroup_label'] ?><?php if (!$show_wizard_control) : ?><a href="javascript:void(0)" class="align-right other-link noborder" onclick="toggleFieldGroupVisibility( $( this ) );" style="font-size: 12px;line-height: 18px;">Hide</a><?php endif; ?></h3>
                                <?php if ($fieldgroup['description'] != ''):?>
                                    <p><small><?=$fieldgroup['description']?></small></p>
                                    <div class="spacer"></div>
                                <?php endif;?>
                                <div class="<?= !empty($fieldgroup['layout']) ? 'col-1-form' : 'col-2-form' ?>" >
                                    <?php
                                    if ($fieldgroup['edit_customview'] != "" && $fieldgroup['edit_customview_position'] == 1 && $fieldgroup['standard_custom_view'] == 0)
                                        $this->load->view($this->userinfo['rtheme'] . '/' . $fieldgroup['edit_customview']);
                                    if (isset($fieldgroup['fields']) && sizeof($fieldgroup['fields']) > 0 && $fieldgroup['standard_custom_view'] == 0) :
                                        foreach ($fieldgroup['fields'] as $field) :
                                            //set js validation params
                                            $datatypes = explode('~', $field['datatype']);
                                            $is_mandatory = false;
                                            $is_readonly = in_array( 'R', $datatypes )? true : false;
                                            if( $is_readonly ) $js['readonly'][] = $field['fieldname'];
                                            if($field['visible'] == 1){
                                                foreach ($datatypes as $datatype) {

                                                    if ($datatype == "M") {
                                                        $js['mandatory'][] = array($field['fieldname'], $field['fieldlabel']);
                                                        $is_mandatory = true;
                                                    }
                                                    if ($datatype == "I" && !$is_readonly){
                                                        if($field['uitype_id'] == 35){
                                                            $js['integer'][] = array($field['fieldname'].'_from', $field['fieldlabel']);
                                                            $js['integer'][] = array($field['fieldname'].'_to', $field['fieldlabel']);
                                                        }
                                                        else
                                                            $js['integer'][] = array($field['fieldname'], $field['fieldlabel']);
                                                    }

                                                    if ($datatype == "F" && !$is_readonly){
                                                                if($field['uitype_id'] == 35) {
                                                                $js['float'][] = array($field['fieldname'].'_from', $field['fieldlabel']);
                                                                $js['float'][] = array($field['fieldname'].'_to', $field['fieldlabel']);
                                                            }
                                                            else {
                                                               $js['float'][] = array($field['fieldname'], $field['fieldlabel']);
                                                            }
                                                    }
                                                    
                                                    if ($datatype == "N")
                                                        $js['numeric'][] = array($field['fieldname'], $field['fieldlabel']);
                                                    if ($datatype == "E")
                                                        $js['email'][] = array($field['fieldname'], $field['fieldlabel']);
                                                    if ($datatype == "U")
                                                        $js['url'][] = array($field['fieldname'], $field['fieldlabel']);
                                                    if ($datatype == "P")
                                                        $js['password'][] = array($field['fieldname'], $field['fieldlabel']);
                                                    if (preg_match("/LE/", $datatype) > 0)
                                                        $js['le'][] = array($field['fieldname'], $field['fieldlabel'], substr($datatype, 2));
                                                    if (preg_match("/LT/", $datatype) > 0)
                                                        $js['lt'][] = array($field['fieldname'], $field['fieldlabel'], substr($datatype, 2));
                                                    if (preg_match("/GE/", $datatype) > 0)
                                                        $js['ge'][] = array($field['fieldname'], $field['fieldlabel'], substr($datatype, 2));
                                                    if (preg_match("/GT/", $datatype) > 0)
                                                        $js['gt'][] = array($field['fieldname'], $field['fieldlabel'], substr($datatype, 2));
                                                }
                                         
                                                $this->uitype_edit->showFieldInput($field, $is_mandatory, $fieldgroup['use_tabindex']);
                                                if ($field['uitype_id'] == 5 && !$is_readonly)
                                                    $js['date'][] = array($field['fieldname'], $field['fieldlabel']);
                                                if ($field['uitype_id'] == 24 && !$is_readonly)
                                                    $js['date_from_to'][] = array($field['fieldname'], $field['fieldlabel']);
                                                if ($field['uitype_id'] == 40 && !$is_readonly)
                                                    $js['datetime_from_to'][] = array($field['fieldname'], $field['fieldlabel']);
                                                if ($field['uitype_id'] == 26 && !$is_readonly)
                                                    $js['time_start_end'][] = array($field['fieldname'], $field['fieldlabel']);
                                                
                                                if ($field['uitype_id'] == 33 && !$is_readonly)
                                                    $js['timepicker'][] = array($field['fieldname'], $field['fieldlabel']);
                                                if ($field['uitype_id'] == 38 && !$is_readonly)
                                                    $js['time_start_end_picker'][] = array($field['fieldname'], $field['fieldlabel']);
                                                if ($field['uitype_id'] == 37 && !$is_readonly)
                                                    $js['min_sec_picker'][] = array($field['fieldname'], $field['fieldlabel']);
                                                if ($field['uitype_id'] == 32 && !$is_readonly)
                                                    $js['datetime_picker'][] = array($field['fieldname'], $field['fieldlabel']);
                                                if ($field['uitype_id'] == 40 && !$is_readonly)
                                                    $js['datetime_from_to_picker'][] = array($field['fieldname'], $field['fieldlabel']);
                                                
                                                if ($field['uitype_id'] == 13)
                                                    $load_jqgrid_in_boxy = true;
                                                if ($field['uitype_id'] == 16) $js['ckeditor'][] = array($field['fieldname'], $field['fieldlabel']);
                                                if ($field['uitype_id'] == 11) {
                                                    $js['single_upload'][] = array($field['fieldname'], $field['fieldlabel']);
                                                    $load_uploadify = true;
                                                }
                                                if ($field['uitype_id'] == 20) {
                                                    $js['multiple_upload'][] = array($field['fieldname'], $field['fieldlabel'], $field['field_id']);
                                                    $load_uploadify = true;
                                                }
                                                if ($field['uitype_id'] == 21) {
                                                    $js['multiselect'][] = array($field['fieldname'], $field['fieldlabel']);
                                                    $load_multiselect = true;
                                                }

                                                if ($field['uitype_id'] == 39 || $field['uitype_id'] == 28) {
                                                    $js['chosen_autocomplete'][] = array($field['fieldname'], $field['fieldlabel']);
                                                    $chosen_autocomplete = true;
                                                }
                                            }                                       
                                        endforeach;
                                    endif;
                                    if ($fieldgroup['edit_customview'] != "" && $fieldgroup['edit_customview_position'] == 3){
                                        $this->load->view($this->userinfo['rtheme'] . '/' . $fieldgroup['edit_customview']);
                                    }
                                    elseif ($fieldgroup['edit_customview'] == "" && $fieldgroup['standard_custom_view'] == 1){
                                        $info['fieldgroup'] = $fieldgroup;
                                        $this->load->view($this->userinfo['rtheme'] . '/edit_custom_view_standard.php',$info);
                                    }                                
                                    ?>
                                </div>
                                <div class="spacer"></div>
                            </div><?php
                //create js validation
                if ($show_wizard_control) {
                    $js['fg_id'] = $fieldgroup['fieldgroup_id'];
                    $this->load->view($this->userinfo['rtheme'] . '/template/edit-wizard-form-js', $js);
                    $js = array();
                }
                endforeach;
                    endif;
            //load additional js base on field and validation
            if ($load_jqgrid_in_boxy) echo jqgrid_in_boxy();
            if ($load_multiselect) echo multiselect_script();
            if ($load_uploadify)  echo uploadify_script();
            if ($chosen_autocomplete) echo chosen_script();

            //create js validation
            if (!$show_wizard_control) $this->load->view($this->userinfo['rtheme'] . '/template/edit-form-js', $js);

        if (sizeof($views) > 0) :
            foreach ($views as $view) :
                $this->load->view($this->userinfo['rtheme'] . '/' . $view);
            endforeach;
        endif;
        ?>
                    <div class="clear"></div>
                </div>            
            </form> 
            <div class="form-submit-btn">
                <div class="icon-label-group">
                    <div class="icon-label">
                        <a rel="record-save" class="icon-16-disk" href="javascript:void(0);" onclick="ajax_save('')">
                            <span>Save</span>
                        </a>            
                    </div>
<!--                     <div class="icon-label">
                        <a rel="record-save-back" class="icon-16-disk-back" href="javascript:void(0);" onclick="ajax_save('back')">
                            <span>Save &amp; Back</span>
                        </a>            
                    </div> -->
                </div>
<!--                 <div class="or-cancel">
                    <span class="or">or</span>
                    <a class="cancel" href="javascript:void(0)" rel="action-back">Cancel</a>
                </div> -->
            </div>                
            <div class="clear"></div><?php
        endif;
    ?>
    <!-- END MAIN CONTENT -->
</div>