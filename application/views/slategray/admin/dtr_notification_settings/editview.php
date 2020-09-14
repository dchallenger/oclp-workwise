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
                <h3 class="form-head">DTR Notification Settings<a style="font-size: 12px;line-height: 18px;" onclick="toggleFieldGroupVisibility( $( this ) );return false;" class="align-right other-link noborder" href="#">Hide</a></h3>
                <p></p>
                <div class="col-2-form">
                  
                  <div class="form-item odd ">
                    <label class="label-desc gray" for="config[dtr_notification_settings][concurred_as_approver]">Email To:</label>
                    <div class="radio-input-wrap">
                        <!--<input type="radio" class="input-radio" value="1" id="config[dtr_notification_settings][enable_edit]-yes" name="config[dtr_notification_settings][enable_edit]" <?php echo $enable_edit_yes?>><label class="check-radio-label gray" for="config[dtr_notification_settings][enable_edit]-yes">Yes</label><input type="radio" class="input-radio" value="0" id="config[dtr_notification_settings][enable_edit]-no" name="config[dtr_notification_settings][enable_edit]" <?php echo $enable_edit_no?>><label class="check-radio-label gray" for="config[dtr_notification_settings][enable_edit]-no">No</label>-->
                        <select style="width:400px;" name="email_to" class="email_to" multiple>
                            <?php
                                foreach($employee_list as $employee_record){ ?>
                                <option value="<?php echo $employee_record['employee_id'] ?>" <?php echo $employee_record['email_to_selected'] ?>><?php echo $employee_record['firstname']." ".$employee_record['lastname']; ?></option>
                            <?php } ?>
                        </select>


                    </div>
                  </div>
                  <div class="form-item even ">
                    <label class="label-desc gray" for="config[dtr_notification_settings][date_from]">Email CC:</label>
                      <div class="text-input-wrap">
                        <!--
                        <input type="hidden" name="config[dtr_notification_settings][date_from]" id="date_from" />
                        <input type="text" name="date_from_dummy" id="date_start" style="width:30%;" disabled="disabled" value="<?php if(isset($dtr_notification_settings['date_from'])) echo $dtr_notification_settings['date_from'] ?>" class="input-text date"/>
                        &nbsp;&nbsp;&nbsp;to&nbsp;&nbsp;&nbsp;
                        <input type="hidden" name="config[dtr_notification_settings][date_to]" id="date_to" />
                        <input type="text" name="date_to_dummy" id="date_end" style="width:30%;" value="<?php if(isset($dtr_notification_settings['date_to'])) echo $dtr_notification_settings['date_to'] ?>" disabled="disabled" class="input-text date"/>
                        -->
                        <select style="width:400px;" name="email_cc" class="email_cc" multiple>
                            <?php
                                foreach($employee_list as $employee_record){ ?>
                                <option value="<?php echo $employee_record['employee_id'] ?>" <?php echo $employee_record['email_cc_selected'] ?>><?php echo $employee_record['firstname']." ".$employee_record['lastname']; ?></option>
                            <?php } ?>
                        </select>

                      </div>
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