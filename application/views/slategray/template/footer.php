	<?php
    if($this->module != 'login') : ?>
        <div id="page-footer">
            <?php 
            $online_users = array();
            $offline_users = array();
            if($this->config->item('allow_chat_on_footer')):
        	   $online_users = $this->hdicore->get_online_users();
                $offline_users = $this->hdicore->get_offline_users( $online_users ); 
            endif;
            ?>
            <article class="chatlist-window widget p0 last-item hidden" id="ChatWidget">
              <div class="chatHead">
              	<input type="text" placeholder="Search..." id="chatsearch" name="chatsearch"/>
                <span class="icon-group align-right">
                  <a class="icon-button icon-16-list-view" tooltip="List" href="javascript:void(0)" onclick="chatListView()"></a>
                  <a class="icon-button icon-16-grid-view" tooltip="Compact" href="javascript:void(0)" onclick="chatThumbsView()"></a>
                </span>
              </div>	
              <div id="ChatWrap">
								<ul class="nostyle online-user-list listView"><?php
									if(sizeof($online_users) > 0){ 
											foreach($online_users as $user): 
												if((!empty($user['userinfo']->photo) && !file_exists($user['userinfo']->photo)) || empty($user['userinfo']->photo) ) $user['userinfo']->photo = $this->userinfo['theme'].'/images/no-photo.jpg'; ?>
												<li>
													<a href="javascript:void(0)" onclick="chatWith(<?php echo $user['userinfo']->user_id?>, '<?php echo $user['userinfo']->firstname.' '.$user['userinfo']->lastname?>', '<?php echo $user['userinfo']->photo;?>')" class="user-<?php echo $user['activity']?>" activity="user-<?php echo $user['activity']?>">
															<img width="20px" src="<?=base_url().$user['userinfo']->photo;?>" alt="" />
															<span class="userName"><?php echo $user['userinfo']->firstname.' '.$user['userinfo']->lastname?></span>
													</a>
												</li> <?php
											endforeach;
									}
									if(sizeof($offline_users) > 0){ 
											foreach($offline_users as $user): 
												if((!empty($user['userinfo']->photo) && !file_exists($user['userinfo']->photo)) || empty($user['userinfo']->photo) ) $user['userinfo']->photo = $this->userinfo['theme'].'/images/no-photo.jpg'; ?>
												<li>
													<a href="javascript:void(0)" onclick="chatWith(<?php echo $user['userinfo']->user_id?>, '<?php echo $user['userinfo']->firstname.' '.$user['userinfo']->lastname?>', '<?php echo $user['userinfo']->photo;?>')" class="user-<?php echo $user['activity']?>" activity="user-<?php echo $user['activity']?>">
															<img width="20px" src="<?=base_url().$user['userinfo']->photo;?>" alt="" />
															<span class="userName"><?php echo $user['userinfo']->firstname.' '.$user['userinfo']->lastname?></span>
													</a>
												</li> <?php
											endforeach;
									}?>
                </ul>
              </div>
            </article>
						<style>
						<?php
            	if($footer_widget_state == "collapse"){ ?>
								.pf-expand{
									margin-top: 100px;
								}
								
								.pf-collapse{
									margin-top: -140px;
								}
							<?php } ?>
						</style>
            <div class="pf-wrap">
            	<div class="pf-expand">
        			<div class="hide-footer"><a href="javascript:void(0);" class="tipsy-autons" ></a></div>
        			
                <ul>
                <?php if(isset( $quick_link ) && count($quick_link) > 0): foreach ($quick_link[1]['links'] as $quicklink):?>
               		<li class="quicklinks"><a href="<?=$quicklink['quicklink_link']?>" class="tipsy-autons" tooltip="<?=$quicklink['quicklink_name']?>"><img src="<?=site_url($quicklink['quicklink_icon'])?>" /></a></li>
                <?php endforeach; endif;?>
                <?php if($this->config->item('allow_chat_on_footer')): ?>
										<li><a href="javascript:void(0);" id="viewchatlist" title="Who's online" onclick="toggle_chatlist_window()"><img src="<?php echo base_url() . $this->userinfo['theme']?>/icons/icon-online.png"/>Who's Online (<?php echo sizeof($online_users)?>)</a></li>
                <?php endif; ?>
                    <li><a href="<?php echo site_url('employee/suggestion_box')?>" title="Suggestion Box" ><img src="<?php echo base_url() . $this->userinfo['theme']?>/icons/icon-suggestionbox.png"/>Suggestion Box</a></li>
                </ul>
              
        			<div class="footer-copyright"><span><?php echo $meta['footer']?> Powered by:</span> <img src="<?php echo base_url() . $this->userinfo['theme']?>/images/company-logo-small.png"/></div>
        		</div>
        		<div class="pf-collapse">
                	<ul>
                    <?php if(isset($quick_link) && count($quick_link) > 0): foreach ($quick_link[1]['links'] as $quicklink):?>
                        <li><a href="<?=$quicklink['quicklink_link']?>" class="tipsy-autons" tooltip="<?=$quicklink['quicklink_name']?>"><img src="<?=site_url($quicklink['quicklink_icon'])?>" /></a></li>
                    <?php endforeach; endif;?>                  	
                    <li><a href="javascript:void(0);" class="tipsy-autons" ><img src="<?php echo base_url() . $this->userinfo['theme']?>/icons/icon-online.png"/></a></li>
                    <li><a href="javascript:void(0);" class="tipsy-autons" tooltip="Suggestion Box"><img src="<?php echo base_url() . $this->userinfo['theme']?>/icons/icon-suggestionbox.png"/></a></li>
        			</ul>
        			<div class="show-footer">
            			<a href="javascript:void(0);"  class="tipsy-autons"></a>
            		</div>
        		</div>
        	</div>
        </div>
    <?php
    endif;
    ?>
    </body>
</html>