<div class="ph-top">
        <div class="announcer">
          <img src="<?php echo base_url() . $this->userinfo['theme']?>/icons/icon-info-32.png" alt="alert" />
          <span>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent sem elit, blandit id bibendum quis, sollicitudin sit amet libero.</span>
          <a class="close-btn" href=""><span>close</span></a> 
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
                            <?php if( $module_id == 1 ){?>
                            <img src="<?php echo base_url() . $this->userinfo['theme']?>/icons/menu/main-toolbar/16x16/dashboard.png" />
                            Dashboard<?php }else{?><img src="<?php echo base_url() . $this->userinfo['theme']?>/icons/<?php echo (empty($user_nav['sm_icon']) ? 'gear.png' : $user_nav['sm_icon'] )?>"/><?php }?>
                            <?php echo $user_nav['show_icon_only'] == 1 ? '' : $user_nav['short_name']?>
                        </a> <?php                    
						if ( !empty($user_nav['child']) ) :
							echo create_child_nav( $user_nav['child'], $this->userinfo['theme'], $this->module_id, ( $depth + 1 ), $user_nav['short_name'] );
						endif; ?>	
                    </li> <?php
					$parent_ctr++;
				endif;
			endforeach; ?>
        </ul>
        
        <?php
          $department = $this->userinfo['department'];

          if (CLIENT_DIR == 'firstbalfour'){
            $this->db->where('user.employee_id',$this->userinfo['user_id']);
            $this->db->where('user.deleted',0);
            // $this->db->where('employee_work_assignment.assignment',1);
            $this->db->join('user_company_division ucd','user.division_id = ucd.division_id','left');
            $this->db->join('project_name pn','user.project_name_id = pn.project_name_id','left');
            $this->db->join('group_name gn','user.group_name_id = gn.group_name_id','left');
            $this->db->join('user_company_department ucde','user.department_id = ucde.department_id','left');
            $result = $this->db->get('user');
            if ($result && $result->num_rows() > 0){
              $row = $result->row();
              if ($row->division_id != 0){
                $department = $row->division;
              }
              elseif ($row->project_name_id != 0){
                $department = $row->project_name;
              } 
              elseif ($row->group_name_id != 0){
                $department = $row->group_name;
              }          
              elseif ($row->department_id != 0){
                $department = $row->department;
              }                                   
            }
          }
        ?>
        <div class="account-box">
            <div class="account-link">
                <h4> <img class="avatar-small" src="<?php echo base_url() . $this->userinfo['photo']?>" /><?=$this->userinfo['firstname'].' '.$this->userinfo['middleinitial'].' '.$this->userinfo['lastname']?></h4>
                <div class="account-drop">
                    <img src="<?php echo base_url() . $this->userinfo['photo']?>" />
                    <ul class="align-left">
                      <li class="account-position"><?php echo $this->userinfo['position']?></li>
                      <li class="account-department"><strong><?php echo $department ?></strong></li>
                      <!-- <li class="account-department"><strong><?php echo $this->userinfo['department']?></strong></li> -->
                      <li class="account-company"><?php echo $this->userinfo['company']?></li>
                  </ul>
                  <hr />
                  <ul class="align-right">
                      <li class="acct-profile"><a href="<?php echo site_url('profile')?>">Edit Profile</a></li>
                      <li class="acct-logout"><a href="<?php echo site_url('logout')?>">Logout</a></li>
                  </ul>
                </div>
            </div>
        </div>
        
</div>
<div class="clearfix"></div> 
<div class="branding">
<?php 
$logo = get_branding();
$company_id = $this->userinfo['company_id'];
$company_qry = $this->db->get_where('user_company', array('company_id' => $company_id))->row();
if(!empty($company_qry->logo)) {
  $logo = '<img alt="" src="'.base_url().''.$company_qry->logo.'">';
}
?>
    <h1> <a href="<?php echo ($user_nav['link'] == '#' || empty($user_nav['link'])) ? 'javascript:void(0)' : base_url()?>"><?php echo $logo;?></a></h1>    
</div>


