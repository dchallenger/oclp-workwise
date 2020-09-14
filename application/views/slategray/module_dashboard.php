<style>
	.dashboard-panel a {color:#bc5d5c;text-decoration:none}
	.dashboard-panel a:hover {text-decoration:underline}
		
	.dashboard-panel {float:left; width:40%; margin: 0px 40px 20px 0px;line-height:1.5;}
	.dashboard-panel .dp-icon {float:left;margin-right:10px;}
	.dashboard-panel .dp-content {float:left; width:99%;}
	.dashboard-panel p {color:#777}
	.dashboard-panel ul {padding:10px 0px 0px 10px}
	.dashboard-panel li {list-style-type:none;padding:3px}
</style>
<?php

	
	$icondir = base_url().'themes/'.$this->userinfo['rtheme'].'/icons/';

	$navs =  $header_nav[$module->module_id];
	if($navs['access']['visible'] != 1){
		$this->session->set_flashdata('flashdata', 'You do not have enough privilege to the requested action.<br/>Please contact the System Administrator.');
		redirect(base_url());
	}

	$parent_child = array();

	$parent_child[$module->module_id] = array(
		'label' => $module->short_name,
		'big_icon' => $module->big_icon,
		'description' => $module->description
	);

	foreach($navs['child'] as $module_id => $detail){
		if($detail['is_visible'] == 1){
			if( isset($detail['child']) && sizeof($detail['child']) > 0 ){
				$parent_child[$module_id] = array(
					'label' => $detail['short_name'],
					'description' => $detail['short_name'],
					'big_icon' => $detail['big_icon'],
				);
				$children = build_children($detail['child']);
				unset($detail['child']);
				if(!empty($detail['link']) && $detail['link'] != "#") $parent_child[$module_id]['child'][$module_id] = $detail;
				if(isset($parent_child[$module_id]['child']))
					$parent_child[$module_id]['child'] = array_merge($parent_child[$module_id]['child'], $children);
				else
					$parent_child[$module_id]['child'] = $children;
			}
			else{
				if(!empty($detail['link']) && $detail['link'] != "#") $parent_child[$module->module_id]['child'][$module_id] = $navs['child'][$module_id];
			}
		}
	}

	function build_children($child){
		$children = array();
		foreach($child as $mod_id => $detail){
			if(!empty($detail['link']) && $detail['link'] != "#") $children[$mod_id] = $detail;
			$grand_children = array();
			if(isset($detail['child']) && sizeof($detail['child']) > 0) $grand_children = build_children($detail['child']);
			if(sizeof($grand_children) > 0) $children = array_merge($children, $grand_children);
		}
		return $children;
	}
?>
<div id="wrap"><?php
	$ctr = 0;
	foreach( $parent_child as $category ):
		if( isset($category['child']) && sizeof($category['child']) > 0 ): ?>
			<div class="dashboard-panel">		
				<div class="dp-content">
					<div class="dp-icon"><img src="<?php echo $icondir.$category['big_icon']; ?>"></div>
					<div style="overflow:hidden">
						<h2><?php echo $category['label'] ?></h2>
						<p><?php echo $category['description'] ?></p>
						<ul> <?php
							foreach($category['child'] as $module_id => $detail) : 
								if($detail['is_visible'] == 1):?>
									<li><img style="padding-right:10px;" src="<?php echo $icondir.$detail['sm_icon']; ?>"><a href="<?php echo base_url() . $detail['link']?>"><?php echo empty($detail['description']) ? $detail['long_name'] : $detail['description']?></a></li> <?php
								endif;
							endforeach; ?>
						</ul>
					</div>
				</div>
			</div> <?php
			if( $ctr % 2  == 1) echo '<div class="clear"></div>';
			$ctr++;
		endif;
	endforeach; ?>
</div>