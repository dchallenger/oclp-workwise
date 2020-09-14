<!-- <ul class="header-ctrs">
    <li><span>52</span><h3>pending document</h3></li>
    <li><span>52</span><h3>pending document</h3></li>
    <li><span>52</span><h3>pending document</h3></li>
    <li><span>52</span><h3>pending document</h3></li>
</ul> -->

<div class="spacer"></div>
<?php 
  // Page Nav Structure
  if ( isset($pnav) ) echo $pnav;
  if(isset($flashdata)) echo $flashdata;						
?>
<div class="clear"></div>
<div class="spacer"></div>
<div class="spacer"></div>

<div class="portlet-top">
<?php
	if (isset( $portlets['top'] ) && count($portlets['top']) > 0):
		foreach ($portlets['top'] as $portlet) :						
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
		      </div>
		   	<?php
			endif;			
		endforeach;
	endif;
?>
</div>

<div class="portlet-left portlet-container">
<?php
	if (count($portlets['left']) > 0):
		foreach ($portlets['left'] as $portlet) :						
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
		      </div>
		   	<?php
			endif;			
		endforeach;
	endif;
?>
</div>
<div class="portlet-right portlet-container">
<?php
	if (count($portlets['right']) > 0):
		foreach ($portlets['right'] as $portlet) :					
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
		      </div>
		   	<?php
			endif;			
		endforeach;
	endif;
?>
</div>
</div>
