<?php
	$leaves = $this->portlet->get_user_leaves( $this->user->user_id, 5 );
	$oot = $this->portlet->get_user_oot( $this->user->user_id, 1 );
	$obt = $this->portlet->get_user_obt( $this->user->user_id, 1 );
	$out = $this->portlet->get_user_out( $this->user->user_id, 1 );
	$et = $this->portlet->get_user_et( $this->user->user_id, 1 );
	$cws = $this->portlet->get_user_cws( $this->user->user_id, 1 );
	$dtrp = $this->portlet->get_user_dtrp( $this->user->user_id, 1 );

	
	//$sub_leaves = $this->portlet->get_sub_leaves($this->user->user_id);
	$sub_leaves = $this->portlet->get_sub_leaves_to_approve($this->user->user_id);
	$sub_oot = $this->portlet->get_sub_oot($this->user->user_id, 'Oot');
	$sub_obt = $this->portlet->get_sub_obt($this->user->user_id, 'Obt');
	$sub_out = $this->portlet->get_sub_out($this->user->user_id, 'Out');
	$sub_et = $this->portlet->get_sub_et($this->user->user_id, 'Et');
	$sub_cws = $this->portlet->get_sub_cws($this->user->user_id, 'Cws');
	$sub_dtrp = $this->portlet->get_sub_dtrp($this->user->user_id, 'Dtrp');

	$no_approval = $this->portlet->get_no_approval($this->user->user_id);
	$leave_approval = $this->portlet->get_leaves_approval($this->user->user_id);
	$no_approval = $leave_approval->num_rows();
	$no_other_forms = count($sub_dtrp) + count($sub_cws) + count($sub_et) + count($sub_out) + count($sub_obt) + count($sub_oot);

	$totalCount = 0;

?>
<div id="<?php echo $portlet_file?>">
	<ul>
		<li><a href="#forms-personal">Personal</a></li>
		<?php if( ($no_approval + $no_other_forms) != 0 ){ ?>
		<li><a href="#forms-approval">For Approval 
			<span class="ctr-inline bg-orange" id="no_approval_display"><?php echo $no_approval + $no_other_forms; ?>---</span>
			<input type="hidden" id="no_approval" value="<?php echo $no_approval + $no_other_forms; ?>" />
		</a></li>
		<?php } ?>
	</ul>
	<div id="forms-personal">
		<p><small>Your personal application made recently.</small></p>
		<div style="margin:0px 5px;">
			Leave Forms
				<?php if(count($leaves)>=5)
					echo '<div style="height:200px;overflow-y: scroll;overflow-x: hidden;padding-right:10px;">'; ?>
				<?php
				if( count($leaves)==0 )
					echo '<p><small>None as of this moment.</small></p>';
				else {
				echo '<ul>';
				$limiter=0;
				foreach ($leaves as $app): 
					if($limiter<30)	{
					?>
					<li><a href="<?php echo base_url().'forms/leaves/detail/'.$app->employee_leave_id; ?>"><?php echo $app->application_form;?> </a>
						<small>filed on </small><?php echo date('M-j',strtotime($app->date_created));?>
						<?php if( $app->form_status_id == 2 ){ ?>
						<span class="align-right orange"><small>For Approval</small></span>
						<?php }elseif( $app->form_status_id == 3 ){ ?>
						<span class="align-right green"><small>Approved</small></span>
						<?php }elseif( $app->form_status_id == 4 ){ ?>
						<span class="align-right red"><small>Disapproved</small></span>
						<?php }elseif( $app->form_status_id == 5 ){ ?>
						<span class="align-right red"><small>Cancelled</small></span>
						<?php }elseif( $app->form_status_id == 1 ){ ?>
						<span class="align-right gray"><small>Draft</small></span>
						<?php }elseif( $app->form_status_id == 6 ){  ?>
						<span class="align-right orange"><small>For HR Review</small></span>
						<?php } ?>
					</li>
				<?php }	$limiter++;
				endforeach; 
				echo '</ul>';
				?>
				<a style="float:right;font-size:10px;" href="<?php echo base_url().'forms/leaves/'; ?>">  (View All Leave Forms) </a>
			<div class="clear"></div>
			<?php if(count($leaves)>=5)
					echo '</div>';
				}?>
			
		</div>
		
		<div style="margin:0px 5px;">
			Other Forms


			<?php 
				if( ( count( $oot ) == 0 ) &&  ( count( $cws ) == 0 ) && ( count( $out ) == 0 ) && ( count( $obt ) == 0 ) && ( count( $dtrp ) == 0 ) && ( count( $et ) == 0 ) ) {
					echo '<p><small>None as of this moment.</small></p>';
				}
				else {

					$totalCount=count( $oot ) + count( $cws ) + count( $out ) + count( $obt ) + + count( $dtrp ) + count( $et );
					
					if($totalCount>=5){
				 		echo '<div style="height:200px;overflow-y: scroll;overflow-x: hidden;padding-right:10px;">';
						$limiter=0; 
					}
			?>


			<?php if(sizeof($oot) != 0){ ?>

				<ul><?php
				if(sizeof($oot) == 0) echo '<li><em><small>No pending OT Form</small></em></li>';
				foreach ($oot as $app): 
					if($limiter<30) {
					?>
					<li><a href="<?php echo base_url().'forms/oot/detail/'.$app->employee_oot_id; ?>">Overtime</a>
						<small>filed on </small><?php echo date('M-j',strtotime($app->date_created));?>
						<?php if( $app->form_status_id == 2 ){ ?>
						<span class="align-right orange"><small>For Approval</small></span>
						<?php }elseif( $app->form_status_id == 3 ){ ?>
						<span class="align-right green"><small>Approved</small></span>
						<?php }elseif( $app->form_status_id == 4 ){ ?>
						<span class="align-right red"><small>Disapproved</small></span>
						<?php }elseif( $app->form_status_id == 5 ){ ?>
						<span class="align-right red"><small>Cancelled</small></span>
						<?php }elseif( $app->form_status_id == 1 ){ ?>
						<span class="align-right gray"><small>Draft</small></span>
						<?php }elseif( $app->form_status_id == 6 ){  ?>
						<span class="align-right orange"><small>For HR Review</small></span>
						<?php } ?>
					</li>
				<?php } $limiter++; ?>
				<?php endforeach; ?>
				</ul>
				<a style="float:right;font-size:10px;" href="<?php echo base_url().'forms/oot/'; ?>">  (View All OT Forms) </a>

			<?php } ?>

			<div class="clear"></div>

			<?php if(sizeof($obt) != 0){ ?>

				<ul>
				<?php if(sizeof($obt) == 0) echo '<li><em><small>No pending OBT Form</small></em></li>'?>
				<?php foreach ($obt as $app): 
						if($limiter<30) {
				?>
					<li><a href="<?php echo base_url().'forms/obt/detail/'.$app->employee_obt_id; ?>">Official Business Trip</a>
						<small>filed on </small><?php echo date('M-j',strtotime($app->date_created));?>
						<?php if( $app->form_status_id == 2 ){ ?>
						<span class="align-right orange"><small>For Approval</small></span>
						<?php }elseif( $app->form_status_id == 3 ){ ?>
						<span class="align-right green"><small>Approved</small></span>
						<?php }elseif( $app->form_status_id == 4 ){ ?>
						<span class="align-right red"><small>Disapproved</small></span>
						<?php }elseif( $app->form_status_id == 5 ){ ?>
						<span class="align-right red"><small>Cancelled</small></span>
						<?php }elseif( $app->form_status_id == 1 ){ ?>
						<span class="align-right gray"><small>Draft</small></span>
						<?php }elseif( $app->form_status_id == 6 ){  ?>
						<span class="align-right orange"><small>For HR Review</small></span>
						<?php } ?>
					</li>
				<?php } $limiter++; ?>
				<?php endforeach; ?>
				</ul>
				<a style="float:right;font-size:10px;" href="<?php echo base_url().'forms/obt/'; ?>">  (View All OBT Forms) </a>
				
			<?php } ?>

			<div class="clear"></div>

			<?php if(sizeof($out) != 0){ ?>

				<ul>
				<?php if(sizeof($out) == 0) echo '<li><em><small>No pending OUT Form</small></em></li>'?>
				<?php foreach ($out as $app): 
						if($limiter<30) {
				?>
					<li><a href="<?php echo base_url().'forms/out/detail/'.$app->employee_out_id; ?>">Undertime</a>
						<small>filed on </small><?php echo date('M-j',strtotime($app->date_created));?>
						<?php if( $app->form_status_id == 2 ){ ?>
						<span class="align-right orange"><small>For Approval</small></span>
						<?php }elseif( $app->form_status_id == 3 ){ ?>
						<span class="align-right green"><small>Approved</small></span>
						<?php }elseif( $app->form_status_id == 4 ){ ?>
						<span class="align-right red"><small>Disapproved</small></span>
						<?php }elseif( $app->form_status_id == 5 ){ ?>
						<span class="align-right red"><small>Cancelled</small></span>
						<?php }elseif( $app->form_status_id == 1 ){ ?>
						<span class="align-right gray"><small>Draft</small></span>
						<?php }elseif( $app->form_status_id == 6 ){  ?>
						<span class="align-right orange"><small>For HR Review</small></span>
						<?php } ?>
					</li>
				<?php } $limiter++; ?>
				<?php endforeach; ?>
				</ul>
				<a style="float:right;font-size:10px;" href="<?php echo base_url().'forms/out/'; ?>">  (View All OUT Forms) </a>
				
			<?php } ?>

			<div class="clear"></div>


			<?php if(sizeof($et) != 0){ ?>

				<ul>
				<?php if(sizeof($et) == 0) echo '<li><em><small>No pending ET Form</small></em></li>'?>
				<?php foreach ($et as $app): 
						if($limiter<30) {
				?>
					<li><a href="<?php echo base_url().'forms/et/detail/'.$app->employee_et_id; ?>">Excused Tardiness</a>
						<small>filed on </small><?php echo date('M-j',strtotime($app->date_created));?>
						<?php if( $app->form_status_id == 2 ){ ?>
						<span class="align-right orange"><small>For Approval</small></span>
						<?php }elseif( $app->form_status_id == 3 ){ ?>
						<span class="align-right green"><small>Approved</small></span>
						<?php }elseif( $app->form_status_id == 4 ){ ?>
						<span class="align-right red"><small>Disapproved</small></span>
						<?php }elseif( $app->form_status_id == 5 ){ ?>
						<span class="align-right red"><small>Cancelled</small></span>
						<?php }elseif( $app->form_status_id == 1 ){ ?>
						<span class="align-right gray"><small>Draft</small></span>
						<?php }elseif( $app->form_status_id == 6 ){  ?>
						<span class="align-right orange"><small>For HR Review</small></span>
						<?php } ?>
					</li>
				<?php } $limiter++; ?>
				<?php endforeach; ?>
				</ul>
				<a style="float:right;font-size:10px;" href="<?php echo base_url().'forms/et/'; ?>">  (View All ET Forms) </a>
				
			<?php } ?>

			<div class="clear"></div>

			<?php if(sizeof($cws) != 0){ ?>

				<ul>
				<?php if(sizeof($cws) == 0) echo '<li><em><small>No pending CWS Form</small></em></li>'?>
				<?php foreach ($cws as $app): 
						if($limiter<30) {
				?>
					<li><a href="<?php echo base_url().'forms/cws/detail/'.$app->employee_cws_id; ?>">Change Work Schedule</a>
						<small>filed on </small><?php echo date('M-j',strtotime($app->date_created));?>
						<?php if( $app->form_status_id == 2 ){ ?>
						<span class="align-right orange"><small>For Approval</small></span>
						<?php }elseif( $app->form_status_id == 3 ){ ?>
						<span class="align-right green"><small>Approved</small></span>
						<?php }elseif( $app->form_status_id == 4 ){ ?>
						<span class="align-right red"><small>Disapproved</small></span>
						<?php }elseif( $app->form_status_id == 5 ){ ?>
						<span class="align-right red"><small>Cancelled</small></span>
						<?php }elseif( $app->form_status_id == 1 ){ ?>
						<span class="align-right gray"><small>Draft</small></span>
						<?php }elseif( $app->form_status_id == 6 ){  ?>
						<span class="align-right orange"><small>For HR Review</small></span>
						<?php } ?>
					</li>
				<?php } $limiter++; ?>
				<?php endforeach; ?>
				</ul>
				<a style="float:right;font-size:10px;" href="<?php echo base_url().'forms/cws/'; ?>">  (View All CWS Forms) </a>
				
			<?php } ?>

			<div class="clear"></div>

			<?php if(sizeof($dtrp) != 0){ ?>

				<ul>
				<?php if(sizeof($dtrp) == 0) echo '<li><em><small>No pending DTRP Form</small></em></li>'?>
				<?php foreach ($dtrp as $app): ?>
					<li><a href="<?php echo base_url().'forms/dtrp/detail/'.$app->employee_dtrp_id; ?>">Daily Time Record Problem</a>
						<small>filed on </small><?php echo date('M-j',strtotime($app->date_created));?>
						<?php if( $app->form_status_id == 2 ){ ?>
						<span class="align-right orange"><small>For Approval</small></span>
						<?php }elseif( $app->form_status_id == 3 ){ ?>
						<span class="align-right green"><small>Approved</small></span>
						<?php }elseif( $app->form_status_id == 4 ){ ?>
						<span class="align-right red"><small>Disapproved</small></span>
						<?php }elseif( $app->form_status_id == 5 ){ ?>
						<span class="align-right red"><small>Cancelled</small></span>
						<?php }elseif( $app->form_status_id == 1 ){ ?>
						<span class="align-right gray"><small>Draft</small></span>
						<?php }elseif( $app->form_status_id == 6 ){  ?>
						<span class="align-right orange"><small>For HR Review</small></span>
						<?php } ?>
					</li>
				<?php endforeach; ?>
				</ul>
				<a style="float:right;font-size:10px;" href="<?php echo base_url().'forms/dtrp/'; ?>">  (View All DTRP Forms) </a>
				
			<?php } ?>
			<div class="clear"></div>


			<?php 
					if($totalCount>=5){
					 echo '</div>';
					}

				}//else end
			?>

		</div>

	</div>
	<div class="clear"></div>

	<?php if( $no_approval + $no_other_forms != 0 ): ?>
	<div id="forms-approval">
		<p><small>Applications made by your staff.</small></p>
		<div style="margin:0px 5px;">
			<a style="float:right;font-size:10px;" href="<?php echo base_url().'forms/leaves/'; ?>">  (View All Leave Forms) </a>
			<ul>Leave Forms
		<?php 
		if(count($sub_leaves)>=4)
			echo '<div style="height:200px;overflow-y: scroll;overflow-x: hidden;padding-right:10px;">';
			$limiter=0;
			$ctr_with_exceeded_l = 0;
		    foreach ($sub_leaves as $app){
		    	/*if($this->system->check_in_cutoff($app->date_from) != 2){ */
			    	if($limiter<30) { ?>
						<li><span class="red"><?php echo ucfirst($app->firstname) . " " . ucfirst(substr($app->middlename,0,1)) . ($app->middlename <> "" ? ". " : " ") . ucfirst($app->lastname) ;?></span><br />
							<a href="<?php echo base_url().'forms/leaves/detail/'.$app->employee_leave_id; ?>" id="link_<?php echo $app->employee_leave_id; ?>"><?php echo $app->application_form;?> </a><small>filed on </small><?php echo date('M-j',strtotime($app->date_created));?>
				        	<span class="icon-group align-right">
				        		<?php if ($this->portlet->can_approve( $app )) {?>
					        		<a class="icon-button icon-16-approve" href="javascript:void(0);" onclick="change_status('leaves','1','<?php echo $app->employee_leave_id; ?>')" tooltip="Approve">Approve</a>
					        	<? } ?>
					        	<?php if ($this->portlet->can_decline( $app )) {?>
					        		<a class="icon-button icon-16-disapprove" href="javascript:void(0);" onclick="change_status('leaves','2','<?php echo $app->employee_leave_id; ?>')" tooltip="Disapprove">Disapprove</a>
					        	<? } ?>
							</span>
							<br />
						</li>
			<?php 
						$ctr_with_exceeded_l++;
					}
					$limiter++;
				/*}*/	
			}
			if($ctr_with_exceeded>=4)
				echo '</div>';
			if($ctr_with_exceeded_l == 0)
				echo '<p><small>None as of this moment.</small></p>';
			?>

			</ul>
			<div class="clear"></div>
		</div>

		<div style="margin:0px 5px;">
			<ul>Other Forms 
			<?php 
			$limiter=0;
			$totalCount=count($sub_dtrp) + count($sub_cws) + count($sub_et) + count($sub_out) + count($sub_obt) + count($sub_oot);
			if($totalCount>=4)
				echo '<div style="height:200px;overflow-y: scroll;overflow-x: hidden;padding-right:10px;">';
			$ctr_with_exceeded = 0;
			foreach ($sub_oot as $app){
				// if($this->system->check_in_cutoff(date('Y-m-d', strtotime($app->datetime_from))) != 2){
					if($limiter<30) {
			?>
				<li><span class="red"><?php echo ucfirst($app->firstname) . " " . ucfirst(substr($app->middlename,0,1)) . ($app->middlename <> "" ? ". " : " ") . ucfirst($app->lastname) ;?></span><br />
					<a href="<?php echo base_url().'forms/oot/detail/'.$app->employee_oot_id; ?>" id="link_<?php echo $app->employee_oot_id; ?>">Overtime </a><small>filed on </small><?php echo date('M-j',strtotime($app->date_created));?>
		        	<span class="icon-group align-right">
		        		<?php if ($this->portlet->can_approve_forms( $app )) {?>
			        		<a class="icon-button icon-16-approve" href="javascript:void(0);" onclick="change_status('oot','1','<?php echo $app->employee_oot_id; ?>')" tooltip="Approve">Approve</a>
			        	<? } ?>
			        	<?php if ($this->portlet->can_decline_forms( $app )) {?>
			        		<a class="icon-button icon-16-disapprove" href="javascript:void(0);" onclick="change_status('oot','2','<?php echo $app->employee_oot_id; ?>')" tooltip="Disapprove">Disapprove</a>
						<? } ?>			        		
					</span>
					<br />
				</li>
			<?php
						$ctr_with_exceeded++;
					}
					$limiter++;
				// }	
			// endforeach;
			}
			foreach ($sub_obt as $app): 
				// if($this->system->check_in_cutoff($app->date_from) != 2){
					if($limiter<30) {
			?>
				<li><span class="red"><?php echo ucfirst($app->firstname) . " " . ucfirst(substr($app->middlename,0,1)) . ($app->middlename <> "" ? ". " : " ") . ucfirst($app->lastname) ;?></span><br />
					<a href="<?php echo base_url().'forms/obt/detail/'.$app->employee_obt_id; ?>" id="link_<?php echo $app->employee_obt_id; ?>">Official Business Trip </a><small>filed on </small><?php echo date('M-j',strtotime($app->date_created));?>
		        	<span class="icon-group align-right">
		        		<?php if ($this->portlet->can_approve_forms( $app )) {?>
			        		<a class="icon-button icon-16-approve" href="javascript:void(0);" onclick="change_status('obt','1','<?php echo $app->employee_obt_id; ?>')" tooltip="Approve">Approve</a>
			        	<? } ?>
			        	<?php if ($this->portlet->can_decline_forms( $app )) {?>
			        		<a class="icon-button icon-16-disapprove" href="javascript:void(0);" onclick="change_status('obt','2','<?php echo $app->employee_obt_id; ?>')" tooltip="Disapprove">Disapprove</a>
						<? } ?>
					</span>
					<br />
				</li>
				<?php 	
						$ctr_with_exceeded++;
					}
					$limiter++;
				// }	
			endforeach; ?>
			<?php 
			foreach ($sub_out as $app): 
				// if($this->system->check_in_cutoff($app->date) != 2){
					if($limiter<30) {
			?>
				<li><span class="red"><?php echo ucfirst($app->firstname) . " " . ucfirst(substr($app->middlename,0,1)) . ($app->middlename <> "" ? ". " : " ") . ucfirst($app->lastname) ;?></span><br />
					<a href="<?php echo base_url().'forms/out/detail/'.$app->employee_out_id; ?>" id="link_<?php echo $app->employee_out_id; ?>">Undertime </a><small>filed on </small><?php echo date('M-j',strtotime($app->date_created));?>
		        	<span class="icon-group align-right">
		        		<?php if ($this->portlet->can_approve_forms( $app )) {?>
			        		<a class="icon-button icon-16-approve" href="javascript:void(0);" onclick="change_status('out','1','<?php echo $app->employee_out_id; ?>')" tooltip="Approve">Approve</a>
			        	<? } ?>
			        	<?php if ($this->portlet->can_decline_forms( $app )) {?>
			        		<a class="icon-button icon-16-disapprove" href="javascript:void(0);" onclick="change_status('out','2','<?php echo $app->employee_out_id; ?>')" tooltip="Disapprove">Disapprove</a>
			        	<? } ?>
					</span>
					<br />
				</li>
			<?php 
						$ctr_with_exceeded++;
					}
					$limiter++;
				// }	
			endforeach; ?>
			<?php foreach ($sub_et as $app):
				// if($this->system->check_in_cutoff($app->datelate) != 2){
					if($limiter<30) {
			 ?>
				<li><span class="red"><?php echo ucfirst($app->firstname) . " " . ucfirst(substr($app->middlename,0,1)) . ($app->middlename <> "" ? ". " : " ") . ucfirst($app->lastname) ;?></span><br />
					<a href="<?php echo base_url().'forms/et/detail/'.$app->employee_et_id; ?>" id="link_<?php echo $app->employee_et_id; ?>" >Excuse Tardiness </a><small>filed on </small><?php echo date('M-j',strtotime($app->date_created));?>
		        	<span class="icon-group align-right">
		        		<?php if ($this->portlet->can_approve_forms( $app )) {?>
			        		<a class="icon-button icon-16-approve" href="javascript:void(0);" onclick="change_status('et','1','<?php echo $app->employee_et_id; ?>')" tooltip="Approve">Approve</a>
			        	<? } ?>
			        	<?php if ($this->portlet->can_decline_forms( $app )) {?>
			        		<a class="icon-button icon-16-disapprove" href="javascript:void(0);" onclick="change_status('et','2','<?php echo $app->employee_et_id; ?>')" tooltip="Disapprove">Disapprove</a>
			        	<? } ?>
					</span>
					<br />
				</li>
			<?php 
						$ctr_with_exceeded++;
					}
					$limiter++;
				// }	
			endforeach; ?>
			<?php 
			foreach ($sub_cws as $app):
				// if($this->system->check_in_cutoff($app->date_to) != 2){
					if($limiter<30) {
			 ?>
				<li><span class="red"><?php echo ucfirst($app->firstname) . " " . ucfirst(substr($app->middlename,0,1)) . ($app->middlename <> "" ? ". " : " ") . ucfirst($app->lastname) ;?></span><br />
					<a href="<?php echo base_url().'forms/cws/detail/'.$app->employee_cws_id; ?>" id="link_<?php echo $app->employee_cws_id; ?>" >Change Work Schedule </a><small>filed on </small><?php echo date('M-j',strtotime($app->date_created));?>
		        	<span class="icon-group align-right">
		        		<?php if ($this->portlet->can_approve_forms( $app )) {?>
			        		<a class="icon-button icon-16-approve" href="javascript:void(0);" onclick="change_status('cws','1','<?php echo $app->employee_cws_id; ?>')" tooltip="Approve">Approve</a>
			        	<? } ?>
			        	<?php if ($this->portlet->can_decline_forms( $app )) {?>			        		
			        		<a class="icon-button icon-16-disapprove" href="javascript:void(0);" onclick="change_status('cws','2','<?php echo $app->employee_cws_id; ?>')" tooltip="Disapprove">Disapprove</a>
						<? } ?>			        		
					</span>
					<br />
				</li>
			<?php 	
						$ctr_with_exceeded++;
					}
					$limiter++;
				// }	
			 endforeach; ?>
			<?php 
			foreach ($sub_dtrp as $app): 
				// if($this->system->check_in_cutoff($app->date) != 2){
					if($limiter<30) {
			?>
				<li><span class="red"><?php echo ucfirst($app->firstname) . " " . ucfirst(substr($app->middlename,0,1)) . ($app->middlename <> "" ? ". " : " ") . ucfirst($app->lastname) ;?></span><br />
					<a href="<?php echo base_url().'forms/dtrp/detail/'.$app->employee_dtrp_id; ?>" id="link_<?php echo $app->employee_dtrp_id; ?>">Daily Time Record Problem </a><small>filed on </small><?php echo date('M-j',strtotime($app->date_created));?>
		        	<span class="icon-group align-right">
		        		<?php if ($this->portlet->can_approve_forms( $app )) {?>
			        		<a class="icon-button icon-16-approve" href="javascript:void(0);" onclick="change_status('dtrp','1','<?php echo $app->employee_dtrp_id; ?>')" tooltip="Approve">Approve</a>
			        	<? } ?>
			        	<?php if ($this->portlet->can_decline_forms( $app )) {?>			        					        		
			        		<a class="icon-button icon-16-disapprove" href="javascript:void(0);" onclick="change_status('dtrp','2','<?php echo $app->employee_dtrp_id; ?>')" tooltip="Disapprove">Disapprove</a>
						<? } ?>			        		
					</span>
					<br />
				</li>
			<?php 
						$ctr_with_exceeded++;
					}
					$limiter++;
				// }	
			endforeach; ?>
		</ul>
		<?php 
		if($ctr_with_exceeded>=4)
			echo '</div>';

		if( $ctr_with_exceeded == 0 ) echo '<p><small>None as of this moment.</small></p>'; ?>
		<div class="clear"></div>
	</div>
	<?php endif;?>
</div>
<script>
	$('#no_approval_display').text(<?= $ctr_with_exceeded_l+$ctr_with_exceeded ?>);
	$('#no_approval').val(<?= $ctr_with_exceeded_l+$ctr_with_exceeded ?>);
</script>
<div class="spacer"></div>
<script type="text/javascript">
	$(document).ready(function() {
		$('#<?php echo $portlet_file?>').tabs();
	});

	function change_status( form, form_status, id ){

		var status;
		var url;

		if( form_status == 1 ){ status = 3; }
		else if( form_status == 2 ){ status = 4; }


		if( form == "leaves" ){
			url = module.get_value('base_url') + 'forms/leaves/change_status';
		}
		else if( form == "oot" ){
			url = module.get_value('base_url') + 'forms/oot/change_status';
		}
		else if( form == "obt" ){
			url = module.get_value('base_url') + 'forms/obt/change_status';
		}
		else if( form == "out" ){
			url = module.get_value('base_url') + 'forms/out/change_status';
		}
		else if( form == "et" ){
			url = module.get_value('base_url') + 'forms/et/change_status';
		}
		else if( form == "dtrp" ){
			url = module.get_value('base_url') + 'forms/dtrp/change_status';
		}
		else if( form == "cws" ){
			url = module.get_value('base_url') + 'forms/cws/change_status';
		}

		var data = 'record_id='+id+'&form_status_id='+status;

		if(status == 4) {

			 Boxy.ask("Add Remarks: <br /> <textarea name='decline_remarks' style='width:100%;' id='decline_remarks'></textarea>", ["Send", "Cancel"],function( add ) {
	            if(add == "Send"){

	            	if(status == 4)
	            		data += '&decline_remarks='+$('#decline_remarks').val();
			
					 $.ajax({
					        url: url,
					        data: data,
					        type: 'post',
					        dataType: 'json',
					        success: function(response) {
					        	message_growl(response.type, response.message);
					        	if( response.type != 'error' ) $('#link_'+id).parent().remove();

					        	 var no_approval = $('#no_approval').val();

					        	 no_approval -= 1;

					        	 if( no_approval == 0 ){

					        	 	$('#approval_list').append('<p><small>None as of this moment.</small></p>');
					        	 	$('#no_approval').val(no_approval);
					        	 	$('#no_approval_display').remove();
					        	 }
					        	 else{
					        	 	$('#no_approval').val(no_approval);
					        	 	$('#no_approval_display').text(no_approval);
					        	 }
					        }
					});
			
			    }
	        },
	        {
	            title: "Decline Remarks"
	        });

		} else {

			 $.ajax({
			        url: url,
			        data: data,
			        type: 'post',
			        dataType: 'json',
			        success: function(response) {
			        	message_growl(response.type, response.message);
			        	if( response.type != 'error' ) $('#link_'+id).parent().remove();

			        	 var no_approval = $('#no_approval').val();

			        	 no_approval -= 1;

			        	 if( no_approval == 0 ){

			        	 	$('#approval_list').append('<p><small>None as of this moment.</small></p>');
			        	 	$('#no_approval').val(no_approval);
			        	 	$('#no_approval_display').remove();
			        	 }
			        	 else{
			        	 	$('#no_approval').val(no_approval);
			        	 	$('#no_approval_display').text(no_approval);
			        	 }
			        }
			});
		}

	}


</script>
