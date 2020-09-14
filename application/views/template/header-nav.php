<div class="ph-top">
      	<div class="account-box">
            <div class="account-link">
                <h4> <img class="avatar-small" src="<?php echo base_url() . $this->userinfo['photo']?>" /><?=$this->userinfo['firstname'].' '.$this->userinfo['middleinitial'].' '.$this->userinfo['lastname']?></h4>
                <div class="account-drop">
                    <img src="<?php echo base_url() . $this->userinfo['photo']?>" />
                  <ul class="align-right">
                      <li><a href="<?php echo site_url('profile')?>">Edit Profile</a></li>
                      <li><a href="<?php echo site_url('logout')?>">Logout</a></li>
                  </ul>
                  <hr />
                  <ul class="align-left">
                      <li class="account-position"><?php echo $this->userinfo['position']?></li>
                      <li class="account-department"><?php echo $this->userinfo['department']?></li>
                      <li class="account-company"><?php echo $this->userinfo['company']?></li>
                  </ul>
                </div>
            </div>
            
           
        </div>
        <ul class="sf-menu"> <?php
			$parent_size = sizeof($header_nav);
			$parent_ctr = 0;
			$depth = 1;
			foreach( $header_nav as $module_id => $user_nav ) :
				if ( $user_nav['is_visible'] == 1 && $user_nav['access']['visible'] == 1 ) :
						$class_last_child = ( $parent_size - 1 ) == $parent_ctr ? "last-nav" : '';
					?>
                    <li class="<?php echo $class_last_child?> <?php echo $this->module_id == $module_id ? 'current' : ''?>" depth="<?php echo $depth?>">
                        <a href="<?php echo ($user_nav['link'] == '#' || empty($user_nav['link'])) ? 'javascript:void(0)' : base_url().$user_nav['link'];?>">
                            <?php if( $module_id == 1 ){?><img class="dashboard-link" src="<?php echo base_url() . $this->userinfo['theme']?>/icons/<?php echo (empty($user_nav['sm_icon']) ? 'icon-dashboard.png' : $user_nav['sm_icon'] )?>" /><?php }else{?><img src="<?php echo base_url() . $this->userinfo['theme']?>/icons/<?php echo (empty($user_nav['sm_icon']) ? 'gear.png' : $user_nav['sm_icon'] )?>"/><?php }?>
                            <?php echo $user_nav['show_icon_only'] == 1 ? '' : $user_nav['short_name']?>
                        </a> <?php                    
						if ( !empty($user_nav['child']) ) :
							echo create_child_nav( $user_nav['child'], $this->userinfo['theme'], $this->module_id, ( $depth + 1 ) );
						endif; ?>	
                    </li> <?php
					$parent_ctr++;
				endif;
			endforeach; ?>
        </ul>
        
        <img class="ph-logo" src="<?php echo base_url() . $this->userinfo['theme']?>/images/ph-hris-logo.png" alt="HRIS" />

        
        
    </div>
<div id="page-header">
    <div class="ph-options">
    	<ul>
    		<li>Systems Development Manager</li>
    		<li class="allcaps">HDI System Technologies</li>
    		<li>E1155</li>
    	</ul>
      <div class="hide-options"><a href="javascript:void(0)">Hide Options</a></div>
    </div>
    <div class="show-options"><a href="javascript:void(0)">Show Options</a></div>
</div>



