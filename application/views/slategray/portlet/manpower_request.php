<?php
	$company_sql = '1';
	if(!$this->user_access[$this->module_id]['post']) {
		$company_id = $this->userinfo['company_id'];
		$company_sql = $this->db->dbprefix.'recruitment_manpower.company_id = "'.$company_id.'"';
	}

	$sql = 'SELECT 
				'.$this->db->dbprefix.'recruitment_manpower.request_id, 
				'.$this->db->dbprefix.'recruitment_manpower.requested_date, 
				'.$this->db->dbprefix.'recruitment_manpower.requested_by, 
				'.$this->db->dbprefix.'recruitment_manpower.position_id, 
				'.$this->db->dbprefix.'user_position.position, 
				'.$this->db->dbprefix.'recruitment_manpower.date_needed, 
				'.$this->db->dbprefix.'recruitment_manpower.status, 
				'.$this->db->dbprefix.'recruitment_manpower.document_number, 
				'.$this->db->dbprefix.'recruitment_manpower.status, 
				'.$this->db->dbprefix.'recruitment_manpower.approved_by, 
				'.$this->db->dbprefix.'recruitment_manpower.requested_by as rb_id, 
				CONCAT(rb.firstname, " ", rb.lastname) as requested_by, 
				concurred_as_approver, 
				concurred_by, 
				concurred_approved, 
				approver_approved, 
				concurred_optional, 
				'.$this->db->dbprefix.'recruitment_manpower.created_by
			FROM (`'.$this->db->dbprefix.'recruitment_manpower`)
				LEFT JOIN `'.$this->db->dbprefix.'user` rb 
					ON `rb`.`user_id` = `'.$this->db->dbprefix.'recruitment_manpower`.`requested_by`
				LEFT JOIN '.$this->db->dbprefix.'user_position 
					ON '.$this->db->dbprefix.'user_position.position_id = '.$this->db->dbprefix.'recruitment_manpower.position_id
			WHERE `'.$this->db->dbprefix.'recruitment_manpower`.`deleted` = 0 
				AND ( '.$this->db->dbprefix.'recruitment_manpower.status = "Approved" 
						OR '.$this->db->dbprefix.'recruitment_manpower.status = "In-Process" )
				AND '.$company_sql.'
			ORDER BY `request_id` desc, `'.$this->db->dbprefix.'recruitment_manpower`.`requested_date` asc';
		$result = $this->db->query($sql);
?>

<div>
	<table id="portlet-table" width="100%" border="0">
	  <?php if ($result->num_rows() == 0):?>
	  <tr><td>None as of this moment.</td></tr>  
	  <?php ;else:?>
	  <?php foreach ($result->result() as $manpower):?>
	  <tr>    
	    <td width="60%"><strong><?=$manpower->position?></strong><br /><small>Date Needed: <?=$manpower->date_needed?></small></td>
	    <td width="33%" align="right"><a style="float:right;font-size:10px;" href="javascript:void(0);" class='apply_button' mrfid='<?=$manpower->request_id ?>' >Apply for Position</a></td>
	  </tr>
	<?php endforeach;?>
	<?php endif;?>
	</table>
</div>

<script type="text/javascript">
	$(document).ready(function() {
		
		$('.apply_button').live('click', function (){

			var id = $(this).attr('mrfid');

			$.ajax({
			        url: module.get_value('base_url') + 'employee/letter_of_intent/get_template_form',
			        data: 'mrfid=' + id,
			        type: 'post',
			        dataType: 'json',
			        beforeSend: function(){
						$.blockUI({
							message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>'
						});  		
					},	
			        success: function(response) {

			        	if(response.msg_type == 'error'){
			        	
			        		$.unblockUI();	
			        		message_growl(response.msg_type, response.msg);

			      		}
						else{

			        	$.unblockUI();	

							template_form = new Boxy('<div id="boxyhtml" style="">'+ response.form +'</div>',
							{
									title: 'Job Vacancy',
									draggable: false,
									modal: true,
									center: true,
									unloadOnHide: true,
									beforeUnload: function (){
										template_form = false;
									}
								});
								boxyHeight(template_form, '#boxyhtml');			

						}

			        }
			});


		});

	});
</script>

