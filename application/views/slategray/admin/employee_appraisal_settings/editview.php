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
                        <a rel="record-save-back" class="icon-16-disk-back" href="javascript:void(0);" onclick="ajax_save('back', '', '')">
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
            <form class="style2 edit-view" name="record-form" id="record-form" method="post" enctype="multipart/form-data">
              <input type="hidden" name="previous_page" id="previous_page" value="<?=base_url().$this->module_link?>/detail"/>        
              


                <div id="form-div">
                <h3 class="form-head">Appraisal Settings<a style="font-size: 12px;line-height: 18px;" onclick="toggleFieldGroupVisibility( $( this ) );return false;" class="align-right other-link noborder" href="#">Hide</a></h3>
                <p></p>
                <div class="col-2-form">
                  
                  <div class="form-item odd ">
                    <label class="label-desc gray" for="config[employee_appraisal_settings][periods]">Period:<span class="red font-large">*</span></label>
                    <div>
                        <select name="config[employee_appraisal_settings][periods]" id="appraisal_periods">
                            <option value="0" <?php if( $employee_appraisal_settings['periods'] == 0 ){ echo 'selected'; } ?>>Please Select</option>
                            <option value="1" <?php if( $employee_appraisal_settings['periods'] == 1 ){ echo 'selected'; } ?>>Monthly</option>
                            <option value="2" <?php if( $employee_appraisal_settings['periods'] == 2 ){ echo 'selected'; } ?>>Quarterly</option>
                            <option value="3" <?php if( $employee_appraisal_settings['periods'] == 3 ){ echo 'selected'; } ?>>Semi-Annual</option>
                        </select>
                    </div>
                  </div>

                  <div class="form-item even">
                    <label class="label-desc gray" for="config[employee_appraisal_settings][multiplier]">Multiplier:<span class="red font-large">*</span></label>
                    <div>
                        <input type="text" name="config[employee_appraisal_settings][multiplier]" id="appraisal_multiplier" value="<?=$employee_appraisal_settings['multiplier'];?>" />
                    </div>
                  </div>

                  <div class="spacer"></div>
                </div>
                <div class="clear"></div>
                <div class="spacer"></div>
                </div>



                <div id="form-div">
                <h3 class="form-head">Appraisal Status(es)<a style="font-size: 12px;line-height: 18px;" onclick="toggleFieldGroupVisibility( $( this ) );return false;" class="align-right other-link noborder" href="#">Hide</a></h3>
                <p></p>
                <div class="col-2-form">
                  
                    <div style="text-align:center; padding:0px 0px;">
                        <div>
                            <div class="text-input-wrap align-left" style="margin:0px 10px;">
                                <label class="label-desc gray" for="appraisal_add_status_name">Name:</label>
                                <input type="text" name="appraisal_add_status_name" id="appraisal_add_status_name" style="width:300px;" />
                            </div>
                            <div class=" icon-group align-left" style="margin:0px 0px;">
                                <a onclick="add_appraisal_status()" class="icon-16-add icon-button" href="javascript:void(0)" tooltip="Add Status"></a>            
                            </div>
                        </div>
                    </div>

                    <br /><br /><br />

                    <div id="status_list" style="padding:0px 0px; width:600px;">

                        <div class="list_header">
                            <div class="align-left" style="width:200px; font-weight:bold;">Name</div>
                            <div class="align-left"  style="width:150px; text-align:center; font-weight:bold;">Action</div>
                            <div class="align-left" style="width:250px; text-align:center; font-weight:bold;">&nbsp;</div>
                        </div>

                        <?php

                            if( count($appraisal_status) > 0 ){

                                foreach( $appraisal_status as $appraisal_status_info ){

                        ?>

                        <div class="list_row align-left">
                            <div class="align-left status_name_html" style="width:200px;"><?=$appraisal_status_info->appraisal_status;?></div>
                            <div class="align-left status_action"  style="width:150px; text-align:center;">
                                <input type="hidden" name="status[status_id][]" class="status_id" value="<?=$appraisal_status_info->appraisal_status_id;?>" />
                                <input type="hidden" name="status[status][]" class="status_name" value="<?=$appraisal_status_info->appraisal_status;?>" />
                                <span class="icon-group">
                                    <a href="javascript:void(0)" tooltip="Apply Changes" style="display:none;" class="appraisal_save_status icon-button icon-16-disk"></a>
                                    <a href="javascript:void(0)" tooltip="Cancel Changes" style="display:none;" class="appraisal_cancel_status icon-button icon-16-cancel"></a>
                                    <a href="javascript:void(0)" tooltip="Edit Status" class="appraisal_edit_status icon-button icon-16-edit"></a>
                                    <a href="javascript:void(0)" tooltip="Delete Status" class="appraisal_delete_status icon-button icon-16-delete"></a>   
                                </span>
                            </div>
                            <div class="align-left" style="width:250px;">&nbsp;</div>
                        </div>

                        <?php 

                                }
                            }

                        ?>


                    </div>



                  <div class="spacer"></div>
                </div>
                <div class="clear"></div>
                <div class="spacer"></div>
                </div> 



                <div id="form-div">
                <h3 class="form-head">Appraisal Scale(s)<a style="font-size: 12px;line-height: 18px;" onclick="toggleFieldGroupVisibility( $( this ) );return false;" class="align-right other-link noborder" href="#">Hide</a></h3>
                <p></p>
                <div class="col-2-form">
                  
                    <div style="text-align:center; padding:0px 0px;">
                        <div>
                            <div class="text-input-wrap align-left" style="margin:0px 10px;">
                                <label class="label-desc gray" for="appraisal_add_scale_name">Name:</label>
                                <input type="text" name="appraisal_add_scale_name" id="appraisal_add_scale_name" style="width:300px;" />
                            </div>
                            <div class="text-input-wrap align-left" style="margin:0px 0px;">
                                <label class="label-desc gray" for="appraisal_add_scale_times">Scale:</label>
                                <input type="text" name="appraisal_add_scale_times" id="appraisal_add_scale_times" style="width:100px;" />
                            </div>
                            <div class=" icon-group align-left" style="margin:0px 10px;">
                                <a onclick="add_appraisal_scale()" class="icon-16-add icon-button" href="javascript:void(0)" tooltip="Add Multipier"></a>            
                            </div>
                        </div>
                    </div>

                    <br /><br /><br />

                    <div id="scale_list" style="padding:0px 0px; width:600px;">

                        <div class="list_header">
                            <div class="align-left" style="width:200px; font-weight:bold;">Name</div>
                            <div class="align-left" style="width:100px; font-weight:bold; text-align:center;">Scale</div>
                            <div class="align-left"  style="width:150px; text-align:center; font-weight:bold;">Action</div>
                            <div class="align-left" style="width:150px; text-align:center; font-weight:bold;">&nbsp;</div>
                        </div>

                        <?php

                            if( count($appraisal_scale) > 0 ){

                                foreach( $appraisal_scale as $appraisal_scale_info ){

                        ?>

                        <div class="list_row align-left">
                            <div class="align-left scale_name_html" style="width:200px;"><?=$appraisal_scale_info->appraisal_scale;?></div>
                            <div class="align-left scale_times_html" style="width:100px; text-align:center;"><?=$appraisal_scale_info->appraisal_scale_times;?></div>
                            <div class="align-left scale_action"  style="width:150px; text-align:center;">
                                <input type="hidden" name="scale[scale_id][]" class="scale_id" value="<?=$appraisal_scale_info->appraisal_scale_id;?>" />
                                <input type="hidden" name="scale[scale][]" class="scale_name" value="<?=$appraisal_scale_info->appraisal_scale;?>" />
                                <input type="hidden" name="scale[scale_times][]" class="scale_times" value="<?=$appraisal_scale_info->appraisal_scale_times;?>" />
                                <span class="icon-group">
                                    <a href="javascript:void(0)" tooltip="Apply Changes" style="display:none;" class="appraisal_save_scale icon-button icon-16-disk"></a>
                                    <a href="javascript:void(0)" tooltip="Cancel Changes" style="display:none;" class="appraisal_cancel_scale icon-button icon-16-cancel"></a>
                                    <a href="javascript:void(0)" tooltip="Edit Status" class="appraisal_edit_scale icon-button icon-16-edit"></a>
                                    <a href="javascript:void(0)" tooltip="Delete Status" class="appraisal_delete_scale icon-button icon-16-delete"></a>   
                                </span>
                            </div>
                            <div class="align-left" style="width:150px;">&nbsp;</div>
                        </div>

                        <?php 

                                }
                            }

                        ?>

                    </div>



                  <div class="spacer"></div>
                </div>
                <div class="clear"></div>
                <div class="spacer"></div>
                </div> 



                <div id="form-div">
                <h3 class="form-head">Appraisal Group(s)<a style="font-size: 12px;line-height: 18px;" onclick="toggleFieldGroupVisibility( $( this ) );return false;" class="align-right other-link noborder" href="#">Hide</a></h3>
                <p></p>
                <div class="col-2-form">
                  
                    <div style="text-align:center; padding:0px 0px;">
                        <div>
                            <div class="text-input-wrap align-left" style="margin:0px 10px;">
                                <label class="label-desc gray" for="appraisal_add_group_name">Name:</label>
                                <input type="text" name="appraisal_add_group_name" id="appraisal_add_group_name" style="width:300px;" />
                            </div>
                            <div class=" icon-group align-left" style="margin:0px 0px;">
                                <a onclick="add_appraisal_group()" class="icon-16-add icon-button" href="javascript:void(0)" tooltip="Add group"></a>            
                            </div>
                        </div>
                    </div>

                    <br /><br /><br />

                    <div id="group_list" style="padding:0px 0px; width:600px;">

                        <div class="list_header">
                            <div class="align-left" style="width:200px; font-weight:bold;">Name</div>
                            <div class="align-left"  style="width:150px; text-align:center; font-weight:bold;">Action</div>
                            <div class="align-left" style="width:250px; text-align:center; font-weight:bold;">&nbsp;</div>
                        </div>

                        <?php

                            if( count($appraisal_group) > 0 ){

                                foreach( $appraisal_group as $appraisal_group_info ){

                        ?>

                        <div class="list_row align-left">
                            <div class="align-left group_name_html" style="width:200px;"><?=$appraisal_group_info->appraisal_group;?></div>
                            <div class="align-left group_action"  style="width:150px; text-align:center;">
                                <input type="hidden" name="group[group_id][]" class="group_id" value="<?=$appraisal_group_info->appraisal_group_id;?>" />
                                <input type="hidden" name="group[group][]" class="group_name" value="<?=$appraisal_group_info->appraisal_group;?>" />
                                <span class="icon-group">
                                    <a href="javascript:void(0)" tooltip="Apply Changes" style="display:none;" class="appraisal_save_group icon-button icon-16-disk"></a>
                                    <a href="javascript:void(0)" tooltip="Cancel Changes" style="display:none;" class="appraisal_cancel_group icon-button icon-16-cancel"></a>
                                    <a href="javascript:void(0)" tooltip="Edit group" class="appraisal_edit_group icon-button icon-16-edit"></a>
                                    <a href="javascript:void(0)" tooltip="Delete group" class="appraisal_delete_group icon-button icon-16-delete"></a>   
                                </span>
                            </div>
                            <div class="align-left" style="width:250px;">&nbsp;</div>
                        </div>

                        <?php 

                                }
                            }

                        ?>

                    </div>



                  <div class="spacer"></div>
                </div>
                <div class="clear"></div>
                <div class="spacer"></div>
                </div> 





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