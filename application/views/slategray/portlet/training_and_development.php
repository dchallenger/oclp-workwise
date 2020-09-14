<?php

$this->db->where('code','Training_Calendar');
$this->db->where('deleted',0);
$training_calendar_module = $this->db->get('module')->row();

$training_application_mod = $this->hdicore->get_module('training_app');
$module_id = $training_application_mod->module_id;
$this->module_id = $module_id;

$this->db->join('training_calendar_participant','training_calendar_participant.training_calendar_id = training_calendar.training_calendar_id AND '.$this->db->dbprefix('training_calendar_participant').'.employee_id = '.$this->userinfo['user_id'] );
$this->db->join('training_subject','training_subject.training_subject_id = training_calendar.training_subject_id','left');
$this->db->where('training_calendar.publish_date <= "'.date('Y-m-d').'"');
$this->db->where('training_calendar.last_registration_date >= "'.date('Y-m-d').'"');
$this->db->where('training_calendar.deleted',0);
$this->db->where('training_calendar.closed',2);
$result = $this->db->get('training_calendar');

$employee_info = $this->db->get_where('employee',array('employee_id'=>$this->userinfo['user_id']))->row();

$subordinates = $this->hdicore->get_subordinates($this->userinfo['position_id'],$employee_info->rank_id, $this->userinfo['user_id']);

$subordinate_list = array();
// $training_live = false;

if( count($subordinates) > 0 ){
	$training_live = array();
	foreach( $subordinates as $subordinate_record ){
		array_push($subordinate_list, $subordinate_record['employee_id']);
		
	}
}

$this->db->join('training_calendar_participant','training_calendar_participant.training_calendar_id = training_calendar.training_calendar_id' );
$this->db->join('training_subject','training_subject.training_subject_id = training_calendar.training_subject_id','left');
$this->db->where_in('training_calendar_participant.employee_id',$subordinate_list);
$this->db->where('training_calendar.publish_date <= "'.date('Y-m-d').'"');
$this->db->where('training_calendar.last_registration_date >= "'.date('Y-m-d').'"');
$this->db->where('training_calendar.deleted',0);
$this->db->where('training_calendar.closed',2);
$this->db->group_by('training_calendar.training_calendar_id');
$subordinate_result = $this->db->get('training_calendar');

$to_approve  = (!$this->user_access[$module_id]['post'] && (count($subordinate_list) > 0 )) ? true : false;
$to_review 	 = ($this->user_access[$module_id]['post']) ? true : false;

if(($this->user_access[$module_id]['post']) && (count($subordinate_list) > 0 )){
	$to_approve = true;
	$to_review  = false;
}

$training_application = $this->portlet->get_sub_training($this->user->user_id, $to_approve, $to_review, false);
$training_live  	  = $this->portlet->get_sub_training($subordinate_list, $to_approve, $to_review, true);

$training_personal = $this->portlet->get_training($this->user->user_id);

$statuses = array('For Approval' => 'for_approval', 'Approved' => 'approved', 'HR Validation'=>'hr_validation');

?>

<div id="<?php echo $portlet_file?>">
	<ul>
		<li>
			<a href="#training_calendar_personal">Personal</a>
		</li>
	<?php if( count($subordinate_list) > 0 ){ ?>
		<li>
			<a href="#training_calendar_subordinate">Approved Trainings</a>
		</li>
		<li>
			<a href="#training_application">Training Application <span class="ctr-inline bg-orange"><?=($training_application && $training_application['count']) ? $training_application['count'] : 0 ?></span></a>
		</li> 
		<li>
			<a href="#training_live">Training Live<span class="ctr-inline bg-orange"><?=($training_live && $training_live['For Evaluation']) ? $training_live['For Evaluation'] : 0 ?></a>
		</li> 
	<?php } ?>
	</ul>
	<diV id="training_calendar_personal">
		
		<div>
		<p>Training/s to Attend </p>
			<table id="portlet-table" width="100%" border="0">
			  <?php if ($result->num_rows() == 0):?>
			  <tr><td><small>None as of this moment.</small></td></tr>  
			  <?php else:?>
			  <?php foreach ($result->result() as $training_calendar):

			  	//get start date
				$this->db->where('training_calendar_id',$training_calendar->training_calendar_id);
				$this->db->order_by('session_date','asc');
				$training_calendar_session_info = $this->db->get('training_calendar_session')->row();

				$start_date = date('F d, Y',strtotime($training_calendar_session_info->session_date));

				//get end date
				$this->db->where('training_calendar_id',$training_calendar->training_calendar_id);
				$this->db->order_by('session_date','desc');
				$training_calendar_session_info = $this->db->get('training_calendar_session')->row();

				$end_date = date('F d, Y',strtotime($training_calendar_session_info->session_date));

			  ?>
			  <tr>    
			    <td width="60%">
			    	<a href="javascript:void(0);" class='more_info_button' subordinate='0' calendarid='<?=$training_calendar->training_calendar_id ?>' >
			    		<strong><?=$training_calendar->training_subject?></strong>
			    	</a>
			    	<br /><small>Training Dates: <?= ( $start_date == $end_date )? $start_date : $start_date.' - '.$end_date ?></small>
			    	
			    </td>
			    <td width="33%" align="right">

			    	<?php if( $training_calendar->participant_status_id <= 1 ){ ?>

			    	<a class="icon-button icon-16-approve calendar_status" calendarid="<?= $training_calendar->training_calendar_id ?>" statusid="2" tooltip="Confirm" href="javascript:void(0)"></a>
					<a class="icon-button icon-16-embark calendar_status" calendarid="<?= $training_calendar->training_calendar_id ?>" statusid="4" tooltip="Move" href="javascript:void(0)"></a>
			    
					<?php }
						else{
						?>
							&nbsp;

						<?php
					} ?>

			    </td>
			  </tr>
			<?php endforeach;?>
			<?php endif;?>
			</table>
			<div class="clear"></div>
		</div>
		<hr>
		<div style="margin:0px 5px;">
		<p>Training Application</p>
			<a style="float:right;font-size:10px;" href="<?php echo base_url().'training/employee_program/'; ?>">  (View All EPAF) </a>
			<ul>External Program Application Forms (EPAF)
			<?php if($training_personal && is_array($training_personal['epaf'])):
					foreach ($training_personal['epaf'] as $key_epaf => $value):
						$epaf_status_id = $statuses[$key_epaf]; 
						?>
						<li style="list-style-type: none;"><a href="<?php echo base_url().'training/employee_program/filter/'.$epaf_status_id; ?>"><?=$key_epaf?><span class="align-right ctr-inline bg-red"><?=$value?></span></a></li>
			<?php 	endforeach;
				  else:?>
			<li style="list-style-type: none;"><small>None as of this moment.</small></li>
			<?php endif;?>
			</ul>
			<div class="clear"></div>
		</div>
		
		<div style="margin:0px 5px;">
			<a style="float:right;font-size:10px;" href="<?php echo base_url().'training/post_graduate/'; ?>">  (View All PGSA) </a>
			<ul>Post-Graduate Studies Application Forms (PGSA)
			<?php if($training_personal && is_array($training_personal['pgsa'])):
					foreach ($training_personal['pgsa'] as $key_pgsa => $value):
						$pgsa_status_id = $statuses[$key_pgsa]; 
						?>
						<li style="list-style-type: none;"><a href="<?php echo base_url().'training/post_graduate/filter/'.$pgsa_status_id; ?>"><?=$key_pgsa?><span class="align-right ctr-inline bg-red"><?=$value?></span></a></li>
			<?php 	endforeach;
				  else:?>
			<li style="list-style-type: none;"><small>None as of this moment.</small></li>
			<?php endif;?>
			</ul>
			<div class="clear"></div>
		</div>
		<hr>
		<div style="margin:0px 5px;">
			<a style="float:right;font-size:10px;" href="<?php echo base_url().'training/training_live/'; ?>">  (View All Training Live) </a>
			<ul>Training Live
			<?php if($training_personal && is_array($training_personal['live'])):
					foreach ($training_personal['live'] as $key_live => $status_live):
							switch ($key_live) {
								case 'New':
									$status_id_live = 1;
									break;
								case 'For Evaluation':
									$status_id_live = 2;
									break;
								case 'Evaluated':
									$status_id_live = 3;
									break;
								case 'No Need to Evaluate':
									$status_id_live = 4;
									break;
								case 'Final':
									$status_id_live = 5;
									break;
								default:
									$status_id_live = '';
									break;
							}
						?>
						<li style="list-style-type: none;"><a href="<?php echo base_url().'training/training_live/filter/'.$status_id_live; ?>"><?=$key_live?><span class="align-right ctr-inline bg-red"><?=$status_live?></span></a></li>
			<?php 	endforeach;
				  else:?>
			<li style="list-style-type: none;"><small>None as of this moment.</small></li>
			<?php endif;?>
			</ul>
			<div class="clear"></div>
		</div>
	</div>


	<?php if( count($subordinate_list) > 0 ){ ?>
	<div id="training_calendar_subordinate">
		<div>
			<table id="portlet-table" width="100%" border="0">
			  <?php if ($subordinate_result->num_rows() == 0):?>
			  <tr><td>None as of this moment.</td></tr>  
			  <?php ;else:?>
			  <?php foreach ($subordinate_result->result() as $training_calendar):

			  	//get start date
				$this->db->where('training_calendar_id',$training_calendar->training_calendar_id);
				$this->db->order_by('session_date','asc');
				$training_calendar_session_info = $this->db->get('training_calendar_session')->row();

				$start_date = date('F d, Y',strtotime($training_calendar_session_info->session_date));

				//get end date
				$this->db->where('training_calendar_id',$training_calendar->training_calendar_id);
				$this->db->order_by('session_date','desc');
				$training_calendar_session_info = $this->db->get('training_calendar_session')->row();

				$end_date = date('F d, Y',strtotime($training_calendar_session_info->session_date));

			  ?>
			  <tr>    
			    <td width="60%">
			    	<a href="javascript:void(0);" class='more_info_button' subordinate='1' calendarid='<?=$training_calendar->training_calendar_id ?>' >
			    		<strong><?=$training_calendar->training_subject?></strong>
			    	</a>
			    	<br /><small>Training Dates: <?= ( $start_date == $end_date )? $start_date : $start_date.' - '.$end_date ?></small>
			    	
			    </td>
			    <td width="33%" align="right">
			    	&nbsp;
			    </td>
			  </tr>
			<?php endforeach;?>
			<?php endif;?>
			</table>
		</div>

	</div>

	<div id="training_application">
		
		<div style="margin:0px 5px;">
			
			<a style="float:right;font-size:10px;" href="<?php echo base_url().'training/employee_program/'; ?>">  (View All EPAF) </a>
			<ul>External Program Application Forms (EPAF)
				<?php if ($training_application && $training_application['epaf']):
						$epaf_status = array();
						
						foreach ($training_application['epaf'] as $epaf){
							$epaf_status[$epaf->training_application_status][] =$epaf->training_application_status;
						}	
						foreach ($statuses as $key => $status):
							if (count($epaf_status[$key]) > 0):
						?>
						
						<li style="list-style-type: none;"><a href="<?php echo base_url().'training/employee_program/filter/'.$status; ?>"><?=$key?><span class="align-right ctr-inline bg-red"><?=count($epaf_status[$key])?></span></a></li>
						
					<?php   endif; 
						endforeach; ?>
				<?php else: ?>
					<li style="list-style-type: none;"><small>None as of this moment.</small></li>
				<?php endif;?>
			</ul>
			<div class="clear"></div>
		</div>
		<hr>
		<div style="margin:0px 5px;">
			
			<a style="float:right;font-size:10px;" href="<?php echo base_url().'training/post_graduate/'; ?>">  (View All PGSA) </a>
			<ul>Post-Graduate Studies Application Forms (PGSA)
				<?php if ($training_application && $training_application['pgsa']):
						$pgsa_status = array();
						
						foreach ($training_application['pgsa'] as $pgsa){
							$pgsa_status[$pgsa->training_application_status][] = $pgsa->training_application_status;
						}	

						foreach ($statuses as $key => $status):
							if (count($pgsa_status[$key]) > 0):
						?>
						
						<li style="list-style-type: none;"><a href="<?php echo base_url().'training/post_graduate/filter/'.$status; ?>"><?=$key?><span class="align-right ctr-inline bg-red"><?=count($pgsa_status[$key])?></span></a></li>
						
					<?php   endif; 
						endforeach; ?>
				<?php else: ?>
					<li style="list-style-type: none;"><small>None as of this moment.</small></li>
				<?php endif;?>
			</ul>
			<div class="clear"></div>
		</div>
		
	</div>
	<div id="training_live">
		<div style="margin:0px 5px;">
			
			<a style="float:right;font-size:10px;" href="<?php echo base_url().'training/training_live/'; ?>">  (View All Training Live) </a>
			<ul>Training Live 
				<?php if ($training_live):
						foreach ($training_live as $status => $key):
							switch ($status) {
								case 'New':
									$status_id = 1;
									break;
								case 'For Evaluation':
									$status_id = 2;
									break;
								case 'Evaluated':
									$status_id = 3;
									break;
								case 'No Need to Evaluate':
									$status_id = 4;
									break;
								case 'Final':
									$status_id = 5;
									break;
								default:
									$status_id = '';
									break;
							}
							?>
						
					<li style="list-style-type: none;"><a href="<?php echo base_url().'training/training_live/filter/'.$status_id; ?>"><?=$status?><span class="align-right ctr-inline bg-red"><?=$key?></span></a></li> 
						
					<?php endforeach; ?>
				<?php else: ?>
					<li style="list-style-type: none;"><small>None as of this moment.</small></li>
				<?php endif;?>
			</ul>
			<div class="clear"></div>
		</div>
		
	</div>
	<?php } // end with subordinates ?>
</div>

<div class="training_confirm_reason_container" style="display:none;">
	<div>
		<textarea class="confirm_reason"></textarea>
	</div>
	<div class="icon-label-group" style="width:100%;">
	      <div class="icon-label" style="float:right;">
	      	<input type="hidden" name="training_calendar_id[]" class="confirm_reason_training_calendar_id" value="<?= $participant_info->training_calendar_id  ?>" />
	      	<input type="hidden" name="participant_status_id[]" class="confirm_reason_training_status_id" value="<?= $participant_info->training_calendar_id  ?>" />
			<input type="hidden" name="participant_id[]" class="confirm_reason_participant_id" value="<?= $participant_info->calendar_participant_id  ?>" />
		      <a class="icon-16-disk training_submit_confirm_reason" href="javascript:void(0);">
		          <span>Submit</span>
		      </a>
	      </div>
	      <div style="clear:both;"></div>
	   
	</div>
</div>


<script type="text/javascript">

	$(document).ready(function() {

		$('#<?php echo $portlet_file?>').tabs();
		
	});

</script>



