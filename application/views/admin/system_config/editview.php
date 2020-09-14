<!-- start #page-head -->
    <div id="page-head" class="page-info">
        <div id="page-title">
            <h2 class="page-title"><?='<span class="title">'.$this->editview_title.'</span>';?></h2>														
        </div>	
        <div id="page-desc" class="align-left"><p><?=$this->editview_description?></p></div>            
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
    
    <!-- sidebar -->
		<?php $this->load->view($this->userinfo['rtheme'] . '/template/sidebar'); ?>

<div id="body-content-wrap">
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
            <form class="style2 edit-view" name="record-form" id="record-form" method="post" enctype="multipart/form-data">
		        <input type="hidden" name="previous_page" id="previous_page" value="<?=base_url().$this->module_link?>/detail"/>        
                <h3 class="form-head">Meta</h3>
                <div class="col-2-form">                    
                    <div class="form-item odd">
                        <label for="company_code" class="label-desc gray">Company Code: <span class="red font-large">*</span></label>
                        <div class="text-input-wrap">
                            <input type="text" name="company_code" id="company_code" value="<?=htmlentities($meta_raw['company_code'])?>" class="input-text">
                        </div>
                    </div>
                    <div class="form-item even">
                        <label for="logo" class="label-desc gray">Logo: <span class="red font-large">*</span></label>
                        <div class="text-input-wrap">
                        	<?php
                            	if( isset($meta_raw['logo']) && $meta_raw['logo'] != "" )
								{?>
								    <div class="nomargin image-wrap" style="width: 120px;">
									    <img src="<?=base_url().$meta_raw['logo']?>"  width="120" id="logo-img" />
									<div class="image-delete nomargin"></div></div><div class="clear"></div>
                                    <input type="hidden" name="logo" id="logo" class="input-text" value="<?=$meta_raw['logo']?>"> 
								<?php
								}
								else{?>
                                	<img src=""  width="120" id="logo-img" />
									<input type="hidden" name="logo" id="logo" class="input-text" value="">	
								<?php
                                }
                            ?>
							<input type="file" name="uploadify-logo" id="uploadify-logo" class="input-text">
                        </div>
                    </div>
                    <div class="form-item odd">
                        <label for="title" class="label-desc gray">Title: <span class="red font-large">*</span></label>
                        <div class="text-input-wrap">
                            <input type="text" name="title" id="title" value="<?=htmlentities($meta_raw['title'])?>" class="input-text">
                        </div>
                    </div>
                    <div class="form-item even">
                        <label for="author" class="label-desc gray">Author: <span class="red font-large">*</span></label>
                        <div class="text-input-wrap">
                            <input type="text" name="author" id="author" value="<?=htmlentities($meta_raw['author'])?>" class="input-text">
                        </div>
                    </div>
                   
                    <div class="form-item odd">
                        <label for="keywords" class="label-desc gray">Keywords:</label>
                        <div class="textarea-input-wrap">
                            <textarea rows="5" name="keywords" id="keywords" class="input-textarea"><?=htmlentities($meta_raw['keywords'])?></textarea>
                            <p class="form-item-description">Comma separated</p>
                        </div>
                    </div>    
                    <div class="form-item even">
                        <label for="description" class="label-desc gray">Description:</label>
                        <div class="text-input-wrap">
                            <input type="text" name="description" id="description" value="<?=htmlentities($meta_raw['description'])?>" class="input-text">
                        </div>
                    </div>
                    <div class="clear"></div>
                    <div class="form-item even">
                        <label for="copyright_details" class="label-desc gray">Copyright Details:</label>
                        <div class="text-input-wrap">
                        	<input type="text" name="copyright" value="<?=htmlentities($meta_raw['copyright'])?>" class="input-text">
                        </div>
                    </div>
                    <div class="form-item odd">
                        <label for="footer_details" class="label-desc gray">Footer:</label>
                        <div class="text-input-wrap">
                            <input type="text" name="footer" value="<?=htmlentities($meta_raw['footer'])?>" class="input-text">
                        </div>
                    </div>
                    
                </div>
                
                <div class="clear"></div>
                <div class="spacer"></div>
                
                <h3 class="form-head">Application Directories</h3>
                <div class="col-2-form">                    
                    <div class="form-item odd">
                	   <label for="system_settings_dir" class="label-desc gray">System Settings: <span class="red font-large">*</span></label>
                       <div class="text-input-wrap">
                           <input type="text" name="system_settings_dir" id="system_settings_dir" value="<?=$app_directories['system_settings_dir']?>" class="input-text">
                    	</div>
                    </div>
                    <div class="form-item even">
                        <label for="user_settings_dir" class="label-desc gray">User Settings: <span class="red font-large">*</span></label>
                        <div class="text-input-wrap">
                        	<input type="text" name="user_settings_dir" id="user_settings_dir" value="<?=$app_directories['user_settings_dir']?>" class="input-text">
                        </div>
                    </div>
                    <div class="clear"></div>
                </div>
				
                <div class="clear"></div>
                <div class="spacer"></div>
                
				<h3 class="form-head">Outgoing Mail Server</h3>
                <div class="col-2-form">                    
                    <div class="form-item odd">
                        <label for="protocol" class="label-desc gray">Mail Protocol: <span class="red font-large">*</span></label>
                        <div class="select-input-wrap">
							<?php
                            $options = array(
								'' => 'Select...',
								'mail' => 'PHP Mail Function',
								'sendmail' => 'Sendmail',
								'smtp' => 'SMTP'
							);
							echo form_dropdown('protocol', $options, $smtp['protocol']);
							?>
						</div>
					</div>
                    <div class="form-item even">
                        <label for="smtp_host" class="label-desc gray">SMTP Host:</label>
                        <div class="text-input-wrap">
                            <input type="text" name="smtp_host" id="smtp_host" value="<?=$smtp['smtp_host']?>" class="input-text">
                        </div>
                    </div>
                    <div class="clear"></div>
                    <div class="form-item odd">
                        <label for="smtp_port" class="label-desc gray">SMTP Port:</label>
                        <div class="text-input-wrap">
                            <input type="text" name="smtp_port" id="smtp_port" value="<?=$smtp['smtp_port']?>" class="input-text">
                        </div>
                    </div>
                    <div class="form-item even">
                        <label for="smtp_user" class="label-desc gray">SMTP User:</label>
                        <div class="text-input-wrap">
                            <input type="text" name="smtp_user" id="smtp_user" value="<?=$smtp['smtp_user']?>" class="input-text">
                        </div>
                    </div>
                    <div class="clear"></div>
                    <div class="form-item odd">
                        <label for="smtp_password" class="label-desc gray">SMTP Password:</label>
                        <div class="text-input-wrap">
                            <input type="text" name="smtp_pass" id="smtp_pass" value="<?=$smtp['smtp_pass']?>" class="input-text">
                        </div>
                    </div>
                    <div class="form-item even">
                        <label for="mailtype" class="label-desc gray">Mail Type:</label>
                        <div class="radio-input-wrap">
                            <?php
                            	$html_check = $smtp['mailtype'] == "html" ? 'checked="checked"' : '';
								$text_check = $smtp['mailtype'] == "text" ? 'checked="checked"' : '';
							?>
                            <input type="radio" name="mailtype" id="mailtype" value="html" class="input-radio" <?=$html_check?>>
                            <label for="mailtype" class="check-radio-label gray">HTML</label>
                            <input type="radio" name="mailtype" id="mailtype" value="text" class="input-radio" <?=$text_check?>>
                            <label for="mailtype" class="check-radio-label gray">Plain Text</label>
                        </div>
                    </div>
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