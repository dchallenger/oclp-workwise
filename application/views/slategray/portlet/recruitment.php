<?php
	$this->load->helper(array('preemployment', 'recruitment'));
	$mrf_mod = $this->hdicore->get_module('Manpower');
	$module_id = $mrf_mod->module_id;

	$mrf_personal = $this->db->get_where('recruitment_manpower', array('deleted' => 0, 'requested_by' => $this->user->user_id));
	// $mrf_total = ($manpower && $manpower->num_rows() > 0) ? $manpower->num_rows() : 0 ; //array_sum( $mrf );
	
	
	// get mrf approvers 
	$this->db->select('recruitment_manpower.status AS mrf_status, recruitment_manpower_approver.status AS approver_status');
	$this->db->join('recruitment_manpower', 'recruitment_manpower.request_id=recruitment_manpower_approver.request_id');
	$mrf_approvers = $this->db->get_where('recruitment_manpower_approver',array('approver'=>$this->user->user_id, 'recruitment_manpower.status !=' => 'Draft'));
	$approver = array();

	if ($mrf_approvers && $mrf_approvers->num_rows() > 0) {
		$mrf_approver = true;
		foreach ($mrf_approvers->result() as $value) {
			if ($value->mrf_status == $value->approver_status) {
				$approver[$value->mrf_status][] = $value->mrf_status;
			}else{
				// $approver_status = ($value->approver_status == 'Draft') ? 'Waiting' : $value->approver_status;
				switch ($value->approver_status) {
					case 'Draft':
					case 'For Approval':
						$approver_status = 'Waiting'; //$value->approver_status;
						break;
					case 'Approved':
						$approver_status = $value->mrf_status; //$value->approver_status;
						break;
					default:
						$approver_status = $value->approver_status;
						break;
				}
				$approver[$approver_status][] = $value->approver_status;
			}
			$approver['ALL'][] = $value->approver_status;
		}

	}
	
	$manpower_filters = get_manpower_filters(array('For Approval', 'Declined', 'Approved', 'Draft', 'In-Process','For HR Review', 'Closed'));

	$mrf_li = "";
	
	$cnt = 0;
	$mrf_statuses = array('For Approval', 'Declined', 'Approved', 'Draft', 'In-Process','For HR Review', 'Closed');
	$url  = site_url( 'recruitment/manpower/filter/');

	foreach ($manpower_filters as $mrf) {
		if ($mrf['count'] > 0 && $this->user_access[$module_id]['post']) {
			$cnt += 1; 
			$mrf_li .= '<li >' . anchor($mrf['link'], $mrf['text'] . '<span class="align-right ctr-inline bg-red">'. $mrf['count'] . '</span><div class="clear"></div>') . '</li>';
		}else{

			if ($mrf_approver) {
				if (in_array($mrf['text'], array('ALL', 'Approved', 'For Approval',  'For HR Review'))) {
					
					$approver_count = count($approver[$mrf['text']]);
					if ($approver_count > 0) {
						$cnt += 1;
						$mrf_li .= '<li>' . anchor($mrf['link'], $mrf['text'] . '<span class="align-right ctr-inline bg-red">'. $approver_count . '</span><div class="clear"></div>') . '</li>';
					}
					// else{
					// 	$mrf_li .= '<li>' . $mrf['text'] . '<span class="align-right ctr-inline bg-red">'. $approver_count . '</span><div class="clear"></div></li>';
					// }
				}
			}
			else{
				if ($mrf['count'] > 0) {
					$cnt += 1;
					$mrf_li .= '<li >' . anchor($mrf['link'], $mrf['text'] . '<span class="align-right ctr-inline bg-red">'. $mrf['count'] . '</span><div class="clear"></div>') . '</li>';
				}
			}
		}
	}


	$my_mrf = '';	
	$cnt_personal = false;
	if ($mrf_personal && $mrf_personal->num_rows() > 0) {
		$cnt_personal = true;
		$personal_mrf = array();
		foreach ($mrf_personal->result() as $personal) {
			$personal_mrf[$personal->status] += 1; 
		}

		foreach ($mrf_statuses as $key => $status_mrf) {
			
			if ($personal_mrf[$status_mrf] && $personal_mrf[$status_mrf] != 0) {
				$my_mrf .= '<li >' . anchor($url.'/'.$key, $status_mrf . '<span class="align-right ctr-inline bg-red">'. $personal_mrf[$status_mrf] . '</span><div class="clear"></div>') . '</li>';

			}

		}
	}



?>

<div id="<?php echo $portlet_file?>">
	<ul>

		<li><a href="#mrf-1" id="tab-1">Personal MRF</a></li>

		<?php if($mrf_approver || $this->user_access[$module_id]['post'] ):?>
			<li><a href="#mrf-2" id="tab-1">Manpower Request</a></li>
		<?php endif;?>

	</ul>

<div id="mrf-1" >
	<?php if ($cnt_personal > 0 ):?>
	<ul><?php echo $my_mrf;?></ul>
	<?php else:?>
	<p><small>None as of this moment.</small></p>
	<?php endif;?>
</div>


<?php if($mrf_approver || $this->user_access[$module_id]['post'] ):?>
<div id="mrf-2" >
	<?php if ($cnt > 0 ):?>
	<ul><?php echo $mrf_li;?></ul>
	<?php else:?>
	<p><small>None as of this moment.</small></p>
	<?php endif;?>
</div>
<?php endif;?>

</div>
<script type="text/javascript">
	$(document).ready(function() {
		$('#<?php echo $portlet_file?>').tabs();
	});
</script>