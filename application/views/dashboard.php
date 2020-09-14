<!-- start #page-head -->
<div id="page-head" class="page-info">
    <div id="page-title">
        <h2 class="page-title"><span class="title"><?=$this->listview_title;?></span></h2>    
    </div>  
    <div id="page-desc" class="align-left"><p><?=$this->listview_description?></p></div>                        
    <?php                               
        // Page Nav Structure
        if ( isset($pnav) ) echo $pnav;                         
    ?>              
    <div class="clear"></div>
</div><!-- end #page-head -->
<?php $this->load->view( $this->userinfo['rtheme'].'/template/sidebar' ); ?>
<div id="body-content-wrap">
<div class="spacer"></div>
<div class="form-submit-btn align-right nopadding icon-label">
      	<a class="icon-16-refresh" href="javascript:void(0);" onclick=""> <span>Reset Dashboard</span> </a>
      </div>
 <?php 								
		// Page Nav Structure
		if ( isset($pnav) ) echo $pnav;
		if(isset($flashdata)) echo $flashdata;						
    ?>
  <div class="clear"></div>
  <div class="spacer"></div>
  <div class="spacer"></div>
  <div class="portlet-left portlet-container">
	<?php
		for( $ctr = 1; $ctr <= sizeof($portlets['left']); $ctr++) :
			if( isset($portlets['left'][$ctr]) ){
				$portlet               = $portlets['left'][$ctr];
				$data['isFolded']      = $portlet['is_folded'] == 1 ? $portlet['portlet_file'] : 0;
				$data['portlet_file']  = $portlet['portlet_file'];
				$data['portlet_class'] = $portlet['portlet_class'];
				$data['portlet_name']  = $portlet['portlet_name'];
				$data['portlet_id']    = $portlet['portlet_id'];
				$data['column']        = 'left';
				$visible = ( (isset($portlet_config[$portlet['portlet_id']]) && $portlet_config[$portlet['portlet_id']]['visible'] == "1") ? true : false );
				if( $visible ) : ?>
          <div id="portlet-<?=$portlet['portlet_id']?>" class="portlet <?=$portlet['portlet_file']?>-portlet">
          	<?php $this->load->view($this->userinfo['rtheme'].'/portlet', $data); ?>
          </div><?php
        endif;
			}
			else{
				echo "<div class='error'><img src='". base_url() . $this->userinfo['theme']."/images/cross-circle.png' alt='' /><p class='text-left'><strong>Error!</strong> Portlet order is ambiguous or not correctly set. Please contact the systems administrator.</p></div>";
			}
		endfor;
	?>
  </div>
  <div class="portlet-right portlet-container">
  <?php
		for( $ctr = 1; $ctr <= sizeof($portlets['right']); $ctr++) :
			if( isset($portlets['right'][$ctr]) ){
				$portlet = $portlets['right'][$ctr];
				$data['isFolded']      = $portlet['is_folded'] == 1 ? $portlet['portlet_file'] : 0;
				$data['portlet_file']  = $portlet['portlet_file'];
				$data['portlet_class'] = $portlet['portlet_class'];
				$data['portlet_name']  = $portlet['portlet_name'];
				$data['portlet_id']    = $portlet['portlet_id'];
				$data['column']        = 'left';
				$visible = ( (isset($portlet_config[$portlet['portlet_id']]) && $portlet_config[$portlet['portlet_id']]['visible'] == "1") ? true : false );
				if( $visible ) : ?>
          <div id="portlet-<?=$portlet['portlet_id']?>" class="portlet <?=$portlet['portlet_file']?>-portlet">
          	<?php $this->load->view($this->userinfo['rtheme'].'/portlet', $data); ?>
          </div><?php
        endif;
			}
			else{
				echo "<div class='error'><img src='". base_url() . $this->userinfo['theme']."/images/cross-circle.png' alt='' /><p class='text-left'><strong>Error!</strong> Portlet order is ambiguous or not correctly set. Please contact the systems administrator.</p></div>";
			}
		endfor;
	?>
  </div>
</div>