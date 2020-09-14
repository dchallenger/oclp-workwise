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
            	<input type="hidden" name="previous_page" id="previous_page" value="<?=base_url().$this->module_link?>"/>             
							<h3 class="form-head">DTR Notification Settings</h3>
                <div class="col-2-form view">                    
                    <div class="form-item view odd">
                        <label for="company_code" class="label-desc view gray">Email To:</label>
                        <div class="text-input-wrap">
                            <?php echo $email_to_list; ?>
                        </div>
                    </div>
                    
                    <div class="form-item view even">
                        <label for="company_code" class="label-desc view gray">Email CC:</label>
                        <div class="text-input-wrap">
                            <?php echo $email_cc_list; ?>
                        </div>
                    </div>
                    
                </div>
                
                <div class="clear"></div>
                <div class="spacer"></div>
				
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