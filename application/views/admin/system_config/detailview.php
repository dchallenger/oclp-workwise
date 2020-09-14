	<!-- start #page-head -->
    <div id="page-head" class="page-info">
        <div id="page-title">
            <h2 class="page-title"><?='<span class="title">'.$this->detailview_title.'</span>';?></h2>	
        </div>	
        <div id="page-desc" class="align-left"><p><?=$this->detailview_description?></p></div>
        <div class="clear"></div>							
    </div>
    <!-- end #page-head -->
    
    <!-- content alert messages -->
    <div id="message-container">
		<?php
        	if( isset($msg) ){
        		echo is_array($msg) ? implode("\n", $msg) : $msg;
        	}
        	if(isset($flashdata)){
        		echo $flashdata;
        	}
		?>
    </div>
    <!-- content alert messages -->
    
    <div class="clear"></div>
    <!-- sidebar -->
		<?php $this->load->view($this->userinfo['rtheme'] . '/template/sidebar'); ?>

<div id="body-content-wrap">


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
                <div class="or-cancel">
                    <span class="or">or</span>
                    <a class="cancel" href="javascript:void(0)" rel="action-back">Go Back</a>
                </div>
            </div>
            <form class="style2 detail-view" name="record-form" id="record-form" method="post" enctype="multipart/form-data">
            	<input type="hidden" name="previous_page" id="previous_page" value="<?=base_url().$this->module_link?>"/>             
				<h3 class="form-head">Meta</h3>
                <div class="col-2-form view">                    
                    <div class="form-item view view odd">
                        <label for="company_code" class="label-desc view gray">Company Code: <span class="red font-large">*</span></label>
                        <div class="text-input-wrap">
                            <?=htmlentities($meta_raw['company_code'])?>
                        </div>
                    </div>
                    <div class="form-item view odd">
                        <label for="logo" class="label-desc view gray">Logo: <span class="red font-large">*</span></label>
                        <div class="text-input-wrap">
                            <?php
                            	if( isset( $meta_raw['logo'] ) && !empty($meta_raw['logo']) ){?>
                                	<a class="enlarge-image" href="javascript:void(0)" img_target="<?=base_url().htmlentities($meta_raw['logo'])?>">
									<img src="<?=base_url().htmlentities($meta_raw['logo'])?>" width="120"/>
									</a><?
                                }
							?>
                        </div>
                    </div>
                    <div class="form-item view even">
                        <label for="title" class="label-desc view gray">Title: <span class="red font-large">*</span></label>
                        <div class="text-input-wrap">
                            <?=htmlentities($meta_raw['title'])?>
                        </div>
                    </div>
                    <div class="clear"></div>
                    <div class="form-item view odd">
                        <label for="author" class="label-desc view gray">Author: <span class="red font-large">*</span></label>
                        <div class="text-input-wrap">
                           <?=htmlentities($meta_raw['author'])?>
                        </div>
                    </div>
                    <div class="form-item view even">
                        <label for="keywords" class="label-desc view gray">Keywords:</label>
                        <div class="text-input-wrap">
                            <?=htmlentities($meta_raw['keywords'])?>
                        </div>
                    </div>
                    <div class="clear"></div>    
                    <div class="form-item view odd">
                        <label for="description" class="label-desc view gray">Description:</label>
                        <div class="text-input-wrap">
                           	<?=htmlentities($meta_raw['description'])?>
                        </div>
                    </div>
                    <div class="form-item view even">
                        <label for="copyright_details" class="label-desc view gray">Copyright Details:</label>
                        <div class="textarea-input-wrap">
                            <?=htmlentities($meta_raw['copyright'])?>
                        </div>
                    </div>
                    <div class="clear"></div>
                    <div class="form-item view odd">
                        <label for="footer_details" class="label-desc view gray">Footer:</label>
                        <div class="textarea-input-wrap">
                           <?=htmlentities($meta_raw['footer'])?>
                        </div>
                    </div>
                    <div class="form-item view odd">
                        <label for="use_logo" class="label-desc view gray">Use Logo:</label>
                        <div class="textarea-input-wrap">
                           <?=($meta_raw['use_logo'] ? 'Yes' : 'No')?>
                        </div>
                    </div>                    
                    <div class="clear"></div>
                </div>
                
                <div class="clear"></div>
                <div class="spacer"></div>
				
                <h3 class="form-head">Application Directories</h3>
                <div class="col-2-form view">
                	<div class="form-item view odd">
                       <label for="system_settings_dir" class="label-desc view gray">System Settings: <span class="red font-large">*</span></label>
                       <div class="select-input-wrap">
                           <?=$app_directories['system_settings_dir']?>
                    	</div>
                    </div>
                    <div class="form-item view even">
                        <label for="user_settings_dir" class="label-desc view gray">User Settings: <span class="red font-large">*</span></label>
                        <div class="text-input-wrap">
                            <?=$app_directories['user_settings_dir']?>
                        </div>
                    </div>
                    <div class="clear"></div>
                </div>
				
                <div class="clear"></div>
                <div class="spacer"></div>

                <h3 class="form-head">Outgoing Mail Server</h3>
                <div class="col-2-form view"> 
                    <div class="form-item view odd">
                       <label for="mailer" class="label-desc view gray">Mail Protocol: <span class="red font-large">*</span></label>
                       <div class="select-input-wrap">
                           <?=$smtp['protocol']?>
                    	</div>
                    </div>
                    <div class="form-item view even">
                        <label for="smtp_host" class="label-desc view gray">SMTP Host:</label>
                        <div class="text-input-wrap">
                            <?=$smtp['smtp_host']?>
                        </div>
                    </div>
                    <div class="clear"></div>
                    <div class="form-item view odd">
                        <label for="smtp_port" class="label-desc view gray">SMTP Port:</label>
                        <div class="text-input-wrap">
                            <?=$smtp['smtp_port']?>
                        </div>
                    </div>
                    <div class="form-item view even">
                        <label for="smtp_user" class="label-desc view gray">SMTP User:</label>
                        <div class="text-input-wrap">
                            <?=$smtp['smtp_user']?>
                        </div>
                    </div>
                    <div class="clear"></div>
                    
                    <div class="form-item view odd">
                        <label for="smtp_password" class="label-desc view gray">SMTP Password:</label>
                        <div class="text-input-wrap">
                            <?=$smtp['smtp_pass']?>
                        </div>
                    </div>
                    <div class="form-item view even">
                        <label for="mailtype" class="label-desc view gray">Mail Type:</label>
                        <div class="text-input-wrap">
                            <?=$smtp['mailtype']?>
                        </div>
                    </div>
                    <div class="clear"></div>
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
                <div class="or-cancel">
                    <span class="or">or</span>
                    <a class="cancel" href="javascript:void(0)" rel="action-back">Go Back</a>
                </div>
            </div><?php	
        endif;
    ?>
    <!-- END MAIN CONTENT -->

</div>