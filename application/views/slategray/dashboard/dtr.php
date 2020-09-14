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
	$pathdir = base_url();

	//check all child that belongs to Time Record
	$navs = $this->hdicore->_create_navigation( 62, $this->user_access );
	//dbug( $navs );

	function array_key_exists_r($needle, $haystack)
	{
	    $result = array_key_exists($needle, $haystack);
	    if ($result) return $result;
	    foreach ($haystack as $v) {
	        if (is_array($v)) {
	            $result = array_key_exists_r($needle, $v);
	        }
	        if ($result) return $result;
	    }
	    return $result;
	}

	?>


	<div id="wrap">

		<?php //work schedule
			$nav = $this->hdicore->get_module(121);

			if( array_key_exists(121, $navs) ) {
		?>
			<div class="dashboard-panel">
				<div class="dp-content">
					<div class="dp-icon"><img src="<?php echo $icondir.$nav->big_icon; ?>"></div>
						<div style="overflow:hidden">
						<h2><?php echo $nav->short_name ?></h2>
						<p>Manage your subordinate work schedule.</p>
						<ul>
							<?php if( array_key_exists_r(152, $navs) ) { ?>
								<li><a href="<?php echo $pathdir.$navs[121][child][152]['link']?>">Update of all/staff work schedule</a></li>
							<?php } ?>
							<?php if( array_key_exists_r(153, $navs) ) { ?>
								<li><a href="<?php echo $pathdir.$navs[121][child][153]['link']?>">View work schedule per employee</a></li>
							<?php } ?>
						</ul>
					</div>
				</div>
			</div>
		<?php } ?>


		<?php //daily time record
			$nav = $this->hdicore->get_module(190);

			if( array_key_exists(190, $navs) ) {
		?>
			<div class="dashboard-panel">
				<div class="dp-content">
					<div class="dp-icon"><img src="<?php echo $icondir.$nav->big_icon; ?>"></div>
						<div style="overflow:hidden">
						<h2><?php echo $nav->short_name ?></h2>
						<p>Manage your time records forwarded by the device, applications on leaves and other forms.</p>
						<ul>
							<?php if( array_key_exists_r(190, $navs) ) { ?>
								<li><a href="<?php echo $pathdir.$navs[190]['link']?>">View your time records</a></li>
							<?php } ?>
							<?php if( array_key_exists_r(190, $navs) ) { ?>
								<li><a href="#">&nbsp;</a></li>
							<?php } ?>
						</ul>
					</div>
				</div>
			</div>
		<?php } ?>


		<?php //leave applications
			$nav = $this->hdicore->get_module(55);

			if( array_key_exists(55, $navs) ) {
		?>
			<div class="dashboard-panel">
				<div class="dp-content">
					<div class="dp-icon"><img src="<?php echo $icondir.$nav->big_icon; ?>"></div>
						<div style="overflow:hidden">
						<h2><?php echo $nav->short_name ?></h2>
						<p>Manage your applications on leaves.</p>
						<ul>
							<?php if( array_key_exists_r(55, $navs) ) { ?>
								<li><a href="<?php echo $pathdir.$navs[55]['link']?>">View your leave applications</a></li>
							<?php } ?>
						</ul>
					</div>
				</div>
			</div>
		<?php } ?>


		<?php //other forms applications
			$nav = $this->hdicore->get_module(56);

			if( array_key_exists(56, $navs) ) {
		?>
			<div class="dashboard-panel">
				<div class="dp-content">
					<div class="dp-icon"><img src="<?php echo $icondir.$nav->big_icon; ?>"></div>
						<div style="overflow:hidden">
						<h2><?php echo $nav->short_name ?></h2>
						<p>Manage your applications on other forms.</p>
						<ul>
							<?php if( array_key_exists_r(59, $navs) ) { ?>
								<li><a href="<?php echo $pathdir.$navs[56][child][59]['link']?>">View your change work schedule (CWS) applications</a></li>
							<?php } ?>
							<?php if( array_key_exists_r(58, $navs) ) { ?>
								<li><a href="<?php echo $pathdir.$navs[56][child][58]['link']?>">View your daily time record problem (DTRP) applications</a></li>
							<?php } ?>
							<?php if( array_key_exists_r(184, $navs) ) { ?>
								<li><a href="<?php echo $pathdir.$navs[56][child][184]['link']?>">View your excused tardiness (ET) applications</a></li>
							<?php } ?>
							<?php if( array_key_exists_r(57, $navs) ) { ?>
								<li><a href="<?php echo $pathdir.$navs[56][child][57]['link']?>">View your official business trip (OBT) applications</a></li>
							<?php } ?>
							<?php if( array_key_exists_r(60, $navs) ) { ?>
								<li><a href="<?php echo $pathdir.$navs[56][child][60]['link']?>">View your overtime (OT) applications</a></li>
							<?php } ?>
							<?php if( array_key_exists_r(61, $navs) ) { ?>
								<li><a href="<?php echo $pathdir.$navs[56][child][61]['link']?>">View your undertime (UT) applications</a></li>
							<?php } ?>
						</ul>
					</div>
				</div>
			</div>
		<?php } ?>



	</div>
