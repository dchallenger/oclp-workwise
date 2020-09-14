<div id="login-wrap">
  <div class="login-body">
    <div class="login-grp">
    
          <div class="login-branding"> <img src="<?php echo $meta['logo']?>">
            <p class="copyr">
				<?php echo $meta['footer']?>
                <?=$this->lang->line('login_application_note')?><a href="<?="h"."t"."t"."p".":"."/"."/"."w"."w"."w"."."."h"."d"."i"."s"."y"."s"."t"."e"."c"."h"."."."c"."o"."m"?>" target="_blank"><br><?php echo $meta['author']?></a>
			</p>
          </div>
      

          <div class="login-form">
          	<div id="message-container">
          		<div id="message_box">
                	<? if(isset($html_msg)) echo $html_msg; ?>
                </div>
            </div>
            <!-- <h3><//?=$this->lang->line('login_sign_here')?></h3> -->
            
            
                <form id="form-login" name="login" method="post" action="">
                  <fieldset class="fieldset-login">
                      <p class="login-shelf">
                          <!-- <label for="login-username"><?//=$this->lang->line('login_email')?></label> -->
                          <input type="text" class="input-text" id="login-username" name="login" placeholder="Login ID" />
                      </p>
                      
                      
                      <p class="login-shelf">
                          <!-- <label for="login-password"><?//=$this->lang->line('login_password')?></label> -->
                          <input type="password" class="input-text" id="login-password" name="password" placeholder="Password" />
                      </p>
                      
                    <p class="login-gateway"> <a href="javascript:void(0)" class="other-link forgot-link text-red align-left"><?=$this->lang->line('login_forgot')?></a>
                      <input type="button" class="btn-login align-right hover-fade" id="submit-login" value="Sign In" />
                    </p>
                  </fieldset>
                  
                  <fieldset class="fieldset-forgot">
                      <p class="login-shelf">
                          <label for="forgot-email"><?=$this->lang->line('login_enter_email')?></label>
                          <input type="text" class="input-text" id="forgot-email" name="email"/>
                      </p>
                      
                    <div id="captcha-div"></div>
                    <p class="login-gateway"> 
                    <a href="javascript:void(0)" class="other-link remember-link text-red align-left"><?=$this->lang->line('login_remember')?></a>
                    <input type="button" class="btn-login align-right hover-fade" id="send-link" value="Submit" />
                    
                    </p>
                    
                    
                  </fieldset>
                  
                  <div class="loading-content" style="display: none"><img src="<?=base_url();?><?=$this->userinfo['theme']?>/images/loading.gif" alt="Loading Gif"/> <br/>
                    <span id="please-wait"><?=$this->lang->line('login_please_wait')?></span> </div>
                </form>
                
                
          </div>
    </div>
  </div>
</div>
