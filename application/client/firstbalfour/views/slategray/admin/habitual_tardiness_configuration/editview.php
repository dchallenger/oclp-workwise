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
            <form class="style2 edit-view" name="record-form" id="record-form" method="post" enctype="multipart/form-data">

              <input type="hidden" name="previous_page" id="previous_page" value="<?=base_url().$this->module_link?>/detail"/>        

              <div id="form-div">
                    <h3 class="form-head">
                        Lates Tardiness Configuration
                        <a style="font-size: 12px;line-height: 18px;" onclick="toggleFieldGroupVisibility( $( this ) );return false;" class="align-right other-link noborder" href="#">
                            Hide
                        </a>
                    </h3>
                    <p></p>

                    <div class="col-2-form">

                        <div class="form-item odd">
                            <label for="minutes_tardy" class="label-desc gray">
                                Minutes Tardy:
                            </label>
                            <div class="text-input-wrap">
                                <input type="text" name="minutes_tardy" id="minutes_tardy" value="<?=$data['minutes_tardy']?>" class="input-text">
                            </div>
                        </div>

                        <div class="form-item even">
                            <label for="instances" class="label-desc gray">
                                Instances :
                            </label>
                            <div class="text-input-wrap">
                                <input type="text" name="instances" id="instances" value="<?=$data['instances']?>" class="input-text">
                            </div>
                        </div>

                        <div class="form-item odd">
                            <label for="months_within" class="label-desc gray">
                                Months Within :
                            </label>
                            <div class="text-input-wrap">
                                <input type="text" name="months_within" id="months_within" value="<?=$data['months_within']?>" class="input-text">
                            </div>
                        </div>
                        
                        <div class="spacer"></div>

                    </div>

                <div class="clear"></div>

                <div class="spacer"></div>

            </div>  

            <div id="form-div">

                <h3 class="form-head">
                    AWOL Configuration
                    <a style="font-size: 12px;line-height: 18px;" onclick="toggleFieldGroupVisibility( $( this ) );return false;" class="align-right other-link noborder" href="#">
                        Hide
                    </a>
                </h3>
                <p></p>

                <div class="col-2-form">
                    <div class="form-item odd">
                        <label for="consec_days_awol" class="label-desc gray">
                            Consecutive Days Absent:
                        </label>
                        <div class="text-input-wrap">
                            <input type="text" name="consec_days_awol" id="consec_days_awol" value="<?=$data['consec_days_awol']?>" class="input-text">
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