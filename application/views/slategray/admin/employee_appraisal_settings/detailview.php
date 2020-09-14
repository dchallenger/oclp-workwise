	<!-- PLACE YOUR MAIN CONTENT HERE -->
	<?php if( isset($error) ) : ?>
        <div id="message_box" class="attention" style="padding-left: 60px;width:60%;margin: 0 auto;">
            <img src="<?=base_url().$this->userinfo['theme']; ?>/images/exclamation-big.png" alt="" >
            <h3 style="margin: 0.3em 0 0.5em 0">Oops! <?=$error?></h3>

            <p><?=$error2?></p>
        </div>
    <? else :?>
            <div class="form-submit-btn">
                <div class="icon-label-group">
                    <div class="icon-label">
                        <a class="icon-16-edit" href="javascript:void(0);" onclick="edit()">
                            <span>Edit</span>
                        </a>            
                    </div>
                </div>
            </div>
            <form class="style2 detail-view" name="record-form" id="record-form" method="post" enctype="multipart/form-data">

                  <input type="hidden" name="previous_page" id="previous_page" value="<?=base_url().$this->module_link?>/detail"/>        

                <div id="form-div">
                <h3 class="form-head">Appraisal Settings<a style="font-size: 12px;line-height: 18px;" onclick="toggleFieldGroupVisibility( $( this ) );return false;" class="align-right other-link noborder" href="#">Hide</a></h3>
                <p></p>
                <div class="col-2-form view">

                    <div class="form-item view odd">
                        <label for="company_code" class="label-desc view gray">Period:</label>
                        <div class="text-input-wrap">
                        <?php
                            if(isset( $employee_appraisal_settings['periods'] ) ){

                                if( $employee_appraisal_settings['periods'] == 1 ){
                                    echo "Monthly";
                                }
                                elseif( $employee_appraisal_settings['periods'] == 2 ){
                                    echo "Quarterly";
                                }
                                elseif( $employee_appraisal_settings['periods'] == 3 ){
                                    echo "Semi-Annual";
                                }

                            }
                        ?>
                        </div>
                    </div>

                    <div class="form-item view even">
                        <label for="company_code" class="label-desc view gray">Multiplier:</label>
                        <div class="text-input-wrap">
                        <?php
                            if(isset( $employee_appraisal_settings['multiplier'] ) ){
                                echo $employee_appraisal_settings['multiplier'];
                            }
                        ?>
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
                <div class="col-2-form view">
                  
                        <?php
                            if( count($appraisal_status) > 0 ){
                                foreach( $appraisal_status as $appraisal_status_info ){
                        ?>

                        <div class="form-item view odd">
                            <label for="company_code" class="label-desc view gray">Status Name:</label>
                            <div class="text-input-wrap">
                            <?php
                                if(isset( $appraisal_status_info->appraisal_status ) ){
                                    echo $appraisal_status_info->appraisal_status;
                                }
                            ?>
                            </div>
                        </div>

                        <?php 
                                }
                            }
                        ?>

                  <div class="spacer"></div>
                </div>
                <div class="clear"></div>
                <div class="spacer"></div>
                </div> 



                <div id="form-div">
                <h3 class="form-head">Appraisal Scale(s)<a style="font-size: 12px;line-height: 18px;" onclick="toggleFieldGroupVisibility( $( this ) );return false;" class="align-right other-link noborder" href="#">Hide</a></h3>
                <p></p>
                <div class="col-2-form view">

                        <?php

                            if( count($appraisal_scale) > 0 ){

                                foreach( $appraisal_scale as $appraisal_scale_info ){

                        ?>

                        <div class="form-item view odd">
                            <label for="company_code" class="label-desc view gray">Scale Name:</label>
                            <div class="text-input-wrap">
                            <?php
                                if(isset( $appraisal_scale_info->appraisal_scale ) ){
                                    echo $appraisal_scale_info->appraisal_scale;
                                }
                            ?>
                            </div>
                        </div>

                        <div class="form-item view even">
                            <label for="company_code" class="label-desc view gray">Scale:</label>
                            <div class="text-input-wrap">
                            <?php
                                if(isset( $appraisal_scale_info->appraisal_scale_times ) ){
                                    echo $appraisal_scale_info->appraisal_scale_times;
                                }
                            ?>
                            </div>
                        </div>

                        <?php 

                                }
                            }

                        ?>

                  <div class="spacer"></div>
                </div>
                <div class="clear"></div>
                <div class="spacer"></div>
                </div> 



                <div id="form-div">
                <h3 class="form-head">Appraisal Group(s)<a style="font-size: 12px;line-height: 18px;" onclick="toggleFieldGroupVisibility( $( this ) );return false;" class="align-right other-link noborder" href="#">Hide</a></h3>
                <p></p>
                <div class="col-2-form view">
                  
                        <?php
                            if( count($appraisal_group) > 0 ){
                                foreach( $appraisal_group as $appraisal_group_info ){
                        ?>

                        <div class="form-item view odd">
                            <label for="company_code" class="label-desc view gray">Group Name:</label>
                            <div class="text-input-wrap">
                            <?php
                                if(isset( $appraisal_group_info->appraisal_group ) ){
                                    echo $appraisal_group_info->appraisal_group;
                                }
                            ?>
                            </div>
                        </div>

                        <?php 
                                }
                            }
                        ?>


                  <div class="spacer"></div>
                </div>
                <div class="clear"></div>
                <div class="spacer"></div>
                </div>
				
						</form>
            <div class="clear"></div>
            <div class="form-submit-btn">
                <div class="icon-label-group">
                    <div class="icon-label">
                        <a class="icon-16-edit" href="javascript:void(0);" onclick="edit()">
                            <span>Edit</span>
                        </a>            
                    </div>
                </div>
            </div><?php	
        endif;
    ?>
    <!-- END MAIN CONTENT -->

</div>