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

	//check all child that belongs to Employee
	$navs = $this->hdicore->_create_navigation( 65, $this->user_access );

	//dbug( $this->hdicore->get_module(31) );
	//dbug( $navs );
	//exit;
	
	//$result = array_replace($navs, array('big_icon' => $icondir."folder-table-32.png"), array('big_icon' => NULL));
	//dbug( $result );	

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

		<?php //employees
			$nav = $this->hdicore->get_module(31);
			//$navs = $this->hdicore->_create_navigation( 65, $this->user_access );
		?>

		<div class="dashboard-panel">		
			<div class="dp-content">
				<div class="dp-icon"><img src="<?php echo $icondir.$nav->big_icon; ?>"></div>
					<div style="overflow:hidden">
					<h2><?php echo $nav->short_name ?></h2>
					<p>Manages the employee 201, personal, and other employee historical records.</p>
					<ul>
						<?php if( array_key_exists(31, $navs) ) { ?>
							<li><a href="<?php echo $pathdir.$navs[31]['link']?>">List of all/staff 201 information</a></li>
						<?php } ?>
						<?php if( array_key_exists(126, $navs) ) { ?>
							<li><a href="<?php echo $pathdir.$navs[126]['link']?>">View your personal information</a></li>
						<?php } ?>
						<?php if( array_key_exists(123, $navs) ) { ?>
							<li><a href="<?php echo $pathdir.$navs[123]['link']?>">Update your personal information</a></li>
						<?php } ?>
						<?php if( array_key_exists_r(136, $navs) ) { ?>
							<li><a href="<?php echo $pathdir?>admin/user_position/my_jd">View your Job Description</a></li>
						<?php } ?>
					</ul>
				</div>
			</div>
		</div>

			
		<?php //resources
			$nav = $this->hdicore->get_module(165);

			if( array_key_exists(165, $navs) ) {
		?>
			<div class="dashboard-panel">
				<div class="dp-content">
					<div class="dp-icon"><img src="<?php echo $icondir.$nav->big_icon; ?>"></div>
						<div style="overflow:hidden">
						<h2><?php echo $nav->short_name ?></h2>
						<p>Company information and manuals.</p>
						<ul>

							<?php if( array_key_exists_r(131, $navs) ) { ?>
								<li><a href="<?php echo $pathdir.$navs[165][child][131]['link']?>">View policies and procedures</a></li>
							<?php } ?>
							<?php if( array_key_exists_r(213, $navs) ) { ?>
								<li><a href="<?php echo $pathdir.$navs[165][child][213]['link']?>">View organizatonal chart</a></li>
							<?php } ?>
							<?php if( array_key_exists_r(167, $navs) ) { ?>
								<li><a href="<?php echo $pathdir.$navs[165][child][167]['link']?>">Download and print government forms</a></li>
							<?php } ?>
							<?php if( array_key_exists_r(183, $navs) ) { ?>
								<li><a href="<?php echo $pathdir.$navs[165][child][183]['link']?>">View unit communication tree</a></li>
							<?php } ?>


							<?php if( array_key_exists_r(200, $navs) ) { ?>
								<li><a href="<?php echo $pathdir.$navs[165][child][211][child][200]['link']?>">List and view uniform survey</a></li>
							<?php } ?>
							<?php if( array_key_exists_r(203, $navs) ) { ?>
								<li><a href="<?php echo $pathdir.$navs[165][child][211][child][203]['link']?>">List and view canteen survey</a></li>
							<?php } ?>
						</ul>
					</div>
				</div>
			</div>
		<?php } ?>


		<?php 
			$nav = $this->hdicore->get_module(133);

			if( array_key_exists(133, $navs) ) {
		?>
			<div class="dashboard-panel">
				<div class="dp-content">
					<div class="dp-icon"><img src="<?php echo $icondir.$nav->big_icon; ?>"></div>
						<div style="overflow:hidden">
						<h2><?php echo $nav->short_name ?></h2>
						<p>Contains all employee updates to his/her company information.</p>
						<ul>
							<?php if( array_key_exists(133, $navs) ) { ?>
								<li><a href="<?php echo $pathdir.$navs[133]['link']?>">View update information</a></li>
							<?php } ?>
						</ul>
					</div>
				</div>

			</div>
		<?php } ?>


		<?php //code of conduct
			$nav = $this->hdicore->get_module(164);

			if( array_key_exists(164, $navs) ) {
		?>
			<div class="dashboard-panel">
				<div class="dp-content">
					<div class="dp-icon"><img src="<?php echo $icondir.$nav->big_icon; ?>"></div>
						<div style="overflow:hidden">
						<h2><?php echo $nav->short_name ?></h2>
						<p>Reported information to an employee.</p>
						<ul>

							<?php if( array_key_exists_r(115, $navs) ) { ?>
								<li><a href="<?php echo $pathdir.$navs[164][child][115]['link']?>">View incident reports</a></li>
							<?php } ?>
							<?php if( array_key_exists_r(124, $navs) ) { ?>
								<li><a href="<?php echo $pathdir.$navs[164][child][124]['link']?>">View notice to explain</a></li>
							<?php } ?>
							<?php if( array_key_exists_r(119, $navs) ) { ?>
								<li><a href="<?php echo $pathdir.$navs[164][child][119]['link']?>">View disciplinary action</a></li>
							<?php } ?>

						</ul>
					</div>
				</div>
			</div>
		<?php } ?>




		<!-- div class="dashboard-panel">
		
			<div class="dp-content">
				<div class="dp-icon"><img src="<?php echo $icondir.$navs[31]['big_icon']; ?>"></div>
					<div style="overflow:hidden">
					<h2><?php echo $navs[31]['short_name']; ?></h2>
					<p>This application contains and manages the employee personal records.</p>
					<ul>

						<li><a href="<?php echo $pathdir?>employees">List of all/staff 201 information</a></li>
						<li><a href="<?php echo $pathdir?>employees/my201">View your personal information</a></li>
						<li><a href="<?php echo $pathdir?>employee/employee_update">Update your personal information</a></li>
						<li><a href="<?php echo $pathdir?>admin/user_position/my_jd">View your Job Description</a></li>
					</ul>
				</div>
			</div>
		</div -->
		
		<!-- div class="dashboard-panel">
			
			<div class="dp-content">
				<div class="dp-icon"><img src="<?php echo $icondir.'folder-table32.png'; ?>"></div>
					<div style="overflow:hidden">
					<h2>Main Title</h2>
					<p>This application contains and manages the employee personal records.</p>
					<ul>
						<li><a href="#">Sub-menu 1</a></li>
						<li><a href="#">Sub-menu 2</a></li>
						<li><a href="#">Sub-menu 3</a></li>
						<li><a href="#">Sub-menu 4</a></li>
					</ul>
				</div>
			</div>
		</div -->
		

	</div>
