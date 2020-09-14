<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Training_live extends MY_Controller
{
	function __construct()
    {
        parent::__construct();

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format	
		
		$this->listview_title = 'Training Live';
		$this->listview_description = 'This module lists all defined training live(s).';
		$this->jqgrid_title = "Training Live List";
		$this->detailview_title = 'Training Live Info';
		$this->detailview_description = 'This page shows detailed information about a particular training live.';
		$this->editview_title = 'Training Live Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about training live(s).';

		// $this->to_filter = array('1', '2', '3', '4', '5');
		$this->filter = $this->db->dbprefix.$this->module_table.'.deleted = 0';
		if (!$this->user_access[$this->module_id]['post']) {
			// $this->filter = $this->db->dbprefix.$this->module_table.'.employee_id = ' . $this->userinfo['user_id'];
			$emp = $this->db->get_Where('employee', array('employee_id' => $this->user->user_id ))->row();
			$subordinates = $this->hdicore->get_subordinates($this->userinfo['position_id'], $emp->rank_id, $this->user->user_id);
			$subordinate_id = array();
			if( count($subordinates) > 0 ){

				$subordinate_id = array();

				foreach ($subordinates as $subordinate) {
						$subordinate_id[] = $subordinate['user_id'];
				}
				$subordinate_id[] = $this->userinfo['user_id'];
			}
			
			$subordinate_list = implode(',', $subordinate_id);

			if( $subordinate_list != "" )
				$this->filter .= ' AND '. $this->db->dbprefix.$this->module_table.'.employee_id IN ('.$subordinate_list.') ';
			else
				$this->filter .= ' AND '. $this->db->dbprefix.$this->module_table.'.employee_id = ' . $this->userinfo['user_id'];
	

		}
		
		if (isset($_POST['to_filter'])) {
			$this->filter .= ' AND status_id = '. $_POST['to_filter']; 		
		}
    }

	// START - default module functions
	// default jqgrid controller method
	function index()
    {
		$data['scripts'][] = jqgrid_listview();	// load jqgrid js and default grid js	
		$data['content'] = 'listview';
		
		if($this->session->flashdata('flashdata')){
			$info['flashdata'] = $this->session->flashdata('flashdata');
			$data['flashdata'] = $this->load->view($this->userinfo['rtheme'].'/template/flashdata', $info, true);
		}
		
		//set default columnlist
		$this->_set_listview_query();

		//set grid buttons
		$data['jqg_buttons'] = $this->_default_grid_buttons();
	
		//set load jqgrid loadComplete callback
		$data['jqgrid_loadComplete'] = "";

		//load variables to env
		$this->load->vars( $data );
		
		//load the final view
		//load header
		$this->load->view( $this->userinfo['rtheme'].'/template/header' );
		$this->load->view( $this->userinfo['rtheme'].'/template/header-nav' );
		
		//load page content
		$this->load->view( $this->userinfo['rtheme'].'/template/page-content' );
		
		//load footer
		$this->load->view( $this->userinfo['rtheme'].'/template/footer' );
    }
	
	function filter($status = null)
    {
    	if ($status == null) {
			redirect('training/training_live');
		}		


		$data['scripts'][] = jqgrid_listview();	// load jqgrid js and default grid js	
		$data['content'] = 'listview';

		$data['to_filter'] = $status;


		if($this->session->flashdata('flashdata')){
			$info['flashdata'] = $this->session->flashdata('flashdata');
			$data['flashdata'] = $this->load->view($this->userinfo['rtheme'].'/template/flashdata', $info, true);
		}
		
		//set default columnlist
		$this->_set_listview_query();

		//set grid buttons
		$data['jqg_buttons'] = $this->_default_grid_buttons();

		//set load jqgrid loadComplete callback
		$data['jqgrid_loadComplete'] = "";

		//load variables to env
		$this->load->vars( $data );
		
		//load the final view
		//load header
		$this->load->view( $this->userinfo['rtheme'].'/template/header' );
		$this->load->view( $this->userinfo['rtheme'].'/template/header-nav' );
		
		//load page content
		$this->load->view( $this->userinfo['rtheme'].'/template/page-content' );

		$this->load->view( $this->module_link.'/filter_live' );
		
		//load footer
		$this->load->view( $this->userinfo['rtheme'].'/template/footer' );
    }

	function detail()
	{	
		parent::detail();
		
		//additional module detail routine here
		$data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/js/detailview.js"></script>';
	
		$data['show_wizard_control'] = true;
		$data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/js/editview-wizard-form-custom.js"></script>';
		$data['content'] = 'training/training_live/compactview';
		
		$rating = $this->db->get_where('training_rating_scale', array('deleted' => 0));
		$transfer = $this->db->get_where('training_knowledge_transfer', array('deleted' => 0));

		$data['ratings'] = ($rating && $rating->num_rows() > 0) ? $rating->result() : array();
		$data['transfers'] = ($transfer && $transfer->num_rows() > 0) ? $transfer->result() : array();

		$records = $this->db->get_where($this->module_table, array($this->key_field => $this->key_field_val));
 		if ($records && $records->num_rows() > 0) {
 			$data['records'] = $records->row();
 			$record = $records->row_array();
 		}

 		$data['can_approve'] = $this->_can_approve($record);
		// $data['can_decline'] = $this->_can_decline($record);

		//other views to load
		$data['views'] = array();
		
		//load variables to env
		$this->load->vars( $data );
		
		//load the final view
		//load header
		$this->load->view( $this->userinfo['rtheme'].'/template/header' );
		$this->load->view( $this->userinfo['rtheme'].'/template/header-nav' );
		
		//load page content
		$this->load->view( $this->userinfo['rtheme'].'/template/page-content' );
		
		//load footer
		$this->load->view( $this->userinfo['rtheme'].'/template/footer' );
	}
	
	function edit()
	{
		if($this->user_access[$this->module_id]['edit'] == 1){
			parent::edit();
			
			//additional module edit routine here
			$data['show_wizard_control'] = false;
			$data['scripts'][] = chosen_script();
			$data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/js/editview.js"></script>';
			$data['show_wizard_control'] = true;
			$data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/js/editview-wizard-form-custom.js"></script>';

			$data['content'] = 'training/training_live/editview';
			$data['buttons'] = 'training/training_live/edit-buttons';

			$rating = $this->db->get_where('training_rating_scale', array('deleted' => 0));
			$transfer = $this->db->get_where('training_knowledge_transfer', array('deleted' => 0));

			$data['ratings'] = ($rating && $rating->num_rows() > 0) ? $rating->result() : array();
			$data['transfers'] = ($transfer && $transfer->num_rows() > 0) ? $transfer->result() : array();

			$records = $this->db->get_where($this->module_table, array($this->key_field => $this->key_field_val));
	 		if ($records && $records->num_rows() > 0) {
	 			$data['records'] = $records->row();
	 			$record = $records->row_array();
	 		}

	 		$data['can_approve'] = $this->_can_approve($record);
			// $data['can_decline'] = $this->_can_decline($record);

			//other views to load
			$data['views'] = array();
			$data['views_outside_record_form'] = array();
			
			//load variables to env
			$this->load->vars( $data );
			
			//load the final view
			//load header
			$this->load->view( $this->userinfo['rtheme'].'/template/header' );
			$this->load->view( $this->userinfo['rtheme'].'/template/header-nav' );
			
			//load page content
			$this->load->view( $this->userinfo['rtheme'].'/template/page-content' );
			
			//load footer
			$this->load->view( $this->userinfo['rtheme'].'/template/footer' );
		}
		else{
			$this->session->set_flashdata('flashdata', 'You dont have sufficient privilege to execute the edit action! Please contact the System Administrator.');
			redirect(base_url().$this->module_link);
		}
	}		
	
	function ajax_save()
	{	
		$employee_id = $this->input->post('employee_id');
		$approvers_per_position = $this->system->get_approvers_and_condition($employee_id, $this->module_id);

		if( empty($approvers_per_position) ){
            $response->msg = "Please contact HR Admin. Approver has not been set.";
            $response->msg_type = "error";
            $response->page_refresh = "true";
            $data['json'] = $response;
            $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
            return;
        }


		if ($this->input->post('objective')) {
			$objectives = json_encode($this->input->post('objective'));
			$details['training_objectives'] = $objectives;
		}

		if ($this->input->post('transfer')) {
			$transfer = json_encode($this->input->post('transfer'));
			$details['knowledge_transfer'] 	= $transfer;
		}

		parent::ajax_save();
		
		$details['date_modified'] = date('Y-m-d');
		$this->db->where($this->key_field, $this->key_field_val);
		$this->db->update($this->module_table, $details);


		// $check_approvers = $this->get_approvers($this->key_field_val);

		// if (!$check_approvers) {
		// 	foreach( $approvers_per_position as $approver ){
		// 		$approver_sequence = $approver['sequence'];
		// 		$approver_id = $approver['approver'];
			
		// 		$approver['training_application_id'] = $this->key_field_val;
		// 		// if ($this->user_access[$this->module_id]['post'] && $employee_id != $this->userinfo['user_id']) {
		// 		// 	if ($approver['focus'] == 1) {
		// 		// 		$approver['status'] = '4';
		// 		// 	}else{
		// 		// 		$approver['status'] = '1';

		// 		// 	}
		// 		// }
		// 		$approver['module_id'] = $this->module_id;

		// 		$this->db->insert('training_approver', $approver);
		// 	}
		// }

		//additional module save routine here
				
	}

	function get_approvers($record_id)
	{
		$approvers = $this->db->get_where('training_approver', array('training_application_id' => $record_id, 'module_id' => $this->module_id));

		if($approvers && $approvers->num_rows() > 0){
			return $approvers->result_array();
		}else{
			return false;
		}
	}
	
	function delete()
	{
		parent::delete();
		
		//additional module delete routine here
	}

	function _default_grid_buttons( $module_link = "", $related_field = "", $related_field_value = "", $addtext = "", $deltext = "", $container = "" )
	{
		// set default
		if($module_link == "") $module_link = $this->module_link;
		if($addtext == "") $addtext = "Add";
		if($deltext == "") $deltext = "Delete";
		if($container == "") $container = "jqgridcontainer";

		$buttons = "<div class='icon-label-group'>";                    
                                  
        
        $buttons .= "</div>";
                
		return $buttons;
	}

	// END - default module functions
	
	// START custom module funtions
	function _default_grid_actions( $module_link = "",  $container = "", $record = array() )
	{
		
		// set default
		if($module_link == "") $module_link = $this->module_link;
		if($container == "") $container = "jqgridcontainer";

		$actions = '<span class="icon-group">';
                
        if ($this->user_access[$this->module_id]['view']) {
            $actions .= '<a class="icon-button icon-16-info" module_link="'.$module_link.'" tooltip="View" href="javascript:void(0)"></a>';
        }
        
		if ( $this->user_access[$this->module_id]['edit'] ) { 
			if ($record['status'] == 1 && $record['employee_id'] == $this->userinfo['user_id']) {

				// $actions .= '<a class="icon-button icon-16-edit" tooltip="Edit" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';

			}elseif($this->user_access[$this->module_id]['post'] == 1 && (in_array($record['status'] , array(1,2))) ){
				 
				 $actions .= '<a class="icon-button icon-16-edit" tooltip="Edit" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';

			}elseif ($record['status'] == 2 && $this->_can_approve($record)) {
				 $actions .= '<a class="icon-button icon-16-edit" tooltip="Edit" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
			}
           
        } 
				
        if ($this->user_access[$this->module_id]['print'] && method_exists($this, 'print_record')) {
            $actions .= '<a class="icon-button icon-16-print print-record" tooltip="Print" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
        }  

        // if ($record['status'] == 1){
        // 	if ($this->user_access[$this->module_id]['delete']) {
	       //      $actions .= '<a class="icon-button icon-16-delete delete-single" tooltip="Delete" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
	       //  }
        // }

        if ($record['status'] == 2 && ($record['employee_id'] != $this->userinfo['user_id'])) {
        	
        /*	if ($this->_can_approve($record)) {
        		$actions .= '<a class="icon-button icon-16-approve approve-single"  record_id="'.$record['training_application_id'].'" tooltip="Approve" container="' . $container . '" module_link="' . $module_link . '" href="javascript:void(0)"></a>';
			}

        	if ($this->_can_decline($record)) {
        		$tooltip = 'Disapprove';
				$actions .= '<a class="icon-button icon-16-disapprove cancel-single" record_id="'.$record['training_application_id'].'" tooltip="' . $tooltip . '" container="' . $container . '" module_link="' . $module_link . '" href="javascript:void(0)"></a>';
			}*/
				
        }

        $actions .= '</span>';

		return $actions;
	}

	function print_record($record_id = 0)
	{
		$check = $this->_record_exist($record_id);
		if($check && $record_id > 0){
			$this->load->library('pdf');
			$this->load->model(array('uitype_detail', 'template'));
			$this->load->library('parser');
			$template = $this->template->get_module_template($this->module_id, 'training_live');

			$this->db->select('training_live.*, CONCAT(salutation, " ", user.firstname, " ", user.lastname) AS attendee, position, department, division, job_rank as level, logo AS company_logo, training_type.training_type, training_provider.training_provider', false);
			$this->db->join('user', 'user.employee_id=training_live.employee_id', 'left');
			$this->db->join('user_position', 'user_position.position_id = user.position_id', 'left');
			$this->db->join('user_company', 'user_company.company_id = user.company_id', 'left');
			$this->db->join('user_company_department', 'user_company_department.department_id = user.department_id', 'left');
			$this->db->join('user_company_division', 'user_company_division.division_id = user.division_id', 'left');
			$this->db->join('employee', 'employee.employee_id = user.employee_id', 'left');
			$this->db->join('user_rank', 'employee.rank_id = user_rank.job_rank_id', 'left');
			$this->db->join('training_application', 'training_application.training_application_id = training_live.training_application_id', 'left');
			$this->db->join('training_type', 'training_application.training_type = training_type.training_type_id', 'left');
			$this->db->join('training_provider', 'training_application.training_provider = training_provider.training_provider_id', 'left');
			$record = $this->db->get_where('training_live', array($this->key_field => $record_id));
			// dbug($this->db->last_query());
			$vars = $record->row_array();

			$vars['training_date'] = date('F d, Y', strtotime($vars['training_date']));
			
			$oclp_logo = get_branding();			

			if(!empty($vars['company_logo'])) {
				$oclp_logo = '<img alt="" src="./'.$vars['company_logo'].'">'; 
			}
			$vars['oclp_logo'] = $oclp_logo;

			
			$vars['objectives'] = "";
			$objectives = json_decode($vars['training_objectives'], true);
			$cnt = 1;
			foreach ($objectives['objective'] as $id => $objective) {

				$pre_rating_result = $this->db->get_where('training_rating_scale', array('training_rating_scale_id' => $objectives['rating'][$id]))->row(); 
                $pre_rating = $pre_rating_result->training_rating_scale; //. ' - ' . $pre_rating_result->description;

                $post_rating_result = $this->db->get_where('training_rating_scale', array('training_rating_scale_id' => $objectives['post_rating'][$id]))->row(); 
                $post_rating = $post_rating_result->training_rating_scale; //. ' - ' . $post_rating_result->description;

				$vars['objectives'] .= '<tr><td >'.$cnt.'. ' .$objective.'</td>';
				$vars['objectives'] .= '<td>'.$pre_rating .'</td>';
				$vars['objectives'] .= '<td>'.$post_rating.'</td>';
				$vars['objectives'] .= '<td>'.$objectives['gap'][$id].'</td>';
				$vars['objectives'] .= '</tr>';
				$cnt ++;
			}
			switch ($vars['impact_to_behavior']) {
				case 1:
					$vars['box_1'] = 'recruitment/check.jpg';
					$vars['box_2'] = 'recruitment/uncheck.jpg';
					$vars['box_3'] = 'recruitment/uncheck.jpg';
					break;
				case 2:
					$vars['box_2'] = 'recruitment/check.jpg';
					$vars['box_3'] = 'recruitment/uncheck.jpg';
					$vars['box_1'] = 'recruitment/uncheck.jpg';
					break;
				case 3:
					$vars['box_3'] = 'recruitment/check.jpg';
					$vars['box_2'] = 'recruitment/uncheck.jpg';
					$vars['box_1'] = 'recruitment/uncheck.jpg';
					break;
				default:
					$vars['box_1'] = 'recruitment/uncheck.jpg';
					$vars['box_2'] = 'recruitment/uncheck.jpg';
					$vars['box_3'] = 'recruitment/uncheck.jpg';
					break;
			}
			
			$knowledge_transfer = json_decode($vars['knowledge_transfer'], true);
			$vars['transfer'] = "";
			foreach ($knowledge_transfer['transfer'] as $key => $transfer_id) {
				$color = ($key % 2) ? '#f3f3f3' : '#ccc' ;
				$date_completed = ($knowledge_transfer['date_complete'][$key]) ? date('F d, Y', strtotime($knowledge_transfer['date_complete'][$key])) : '';
				$transfer = $this->db->get_where('training_knowledge_transfer', array('deleted' => 0, 'training_knowledge_transfer_id' => $transfer_id))->row();
				$vars['transfer'] .= '<tr><td style="background-color:'.$color.'" align="center">'.$transfer->training_knowledge_transfer.'</td>';
				$vars['transfer'] .= '<td style="background-color:'.$color.'" align="center">'.$date_completed.'</td>';
				$vars['transfer'] .= '</tr>';

			}
			
			$approvers_per_position = $this->system->get_approvers_and_condition($vars['employee_id'], $this->module_id);

				if (!empty($approvers_per_position)) {
					$evaluator = $approvers_per_position[0]['approver'];
					$approver = $approvers_per_position[1]['approver'];
				}else{
					$evaluator = $this->system->get_reporting_to( $vars['employee_id']);	
				}
			
			$immediate = $this->system->get_employee($evaluator);
			$head = $this->system->get_employee($approver);
            
			$vars['evaluator'] = $immediate['salutation']." ".$immediate['firstname']." ".$immediate['lastname'];;
			$vars['evaluator_position'] = $immediate['position'];

			$vars['approver'] = $head['salutation']." ".$head['firstname']." ".$head['lastname'];;
			$vars['approver_position'] =$head['position'];

			$html = $this->template->prep_message($template['body'], $vars, false, true);

			// Prepare and output the PDF.
			// dbug($html);
			// die();
			$this->pdf->SetAutoPageBreak(true, 25.4);
			$this->pdf->SetMargins( 19.05, 19.05 );
			$this->pdf->addPage('P', 'LETTER', true);
			// $this->pdf->addPage();
			$this->pdf->writeHTML($html, true, false, true, false, '');
			$this->pdf->Output(date('Y-m-d').'-LIVE.pdf', 'D');

		} else {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}

	}

	function _append_to_select()
	{
		//$this->listview_qry .= ', employee_appraisal.employee_id, user.position_id';
		$this->listview_qry .= ', status_id AS status, training_live.employee_id'; 

	}


	function send_email() {
		if (IS_AJAX) {

			$record_id = $this->input->post('record_id');
			$records = $this->db->get_where($this->module_table, array($this->key_field => $record_id))->row_array();

			if ($records['status_id'] == 2 ) {
				$this->db->where($this->key_field, $record_id);
				$this->db->update($this->module_table, array('status_id' => '3', 'date_modified' => date('Y-m-d'), 'date_evaluated' => date('Y-m-d'), 'evaluated_by' => $this->user->user_id));
				
				$response->record_id  = $record_id;
				$response->msg_type = 'success';
				$response->msg = 'Training Live successfully evaluated.';

				$data['json'] = $response;			
				$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
				return;
			}

     		$employee = $this->db->get_where('user',array("user_id"=>$records['employee_id']))->row_array();
			$vars['employee'] =  $employee['salutation'] .' '. $employee['firstname']. ' ' . $employee['lastname'];
			$vars['training_title'] = $records['training_application_code'];
			$vars['training_dates'] = date('F d, Y' , strtotime($records['training_date']));
			$employee_id = $records['employee_id'];
			$vars['training_title'] = $records['course'];
			
			/*switch ($records['training_application_type_id']) {
				case 1: // EFAP
					$course = $this->db->get_where('training_subject', array('training_subject_id' => $records['training_subject_id']))->row();
					$vars['training_title'] = $course->training_subject;
					break;
				case 2: // PGSA
					$vars['training_title'] = $records['course'];
					break;
			}*/

			$mail_config = $this->hdicore->_get_config('outgoing_mailserver');
			if ($mail_config) {
				$recepients = array();

				$approvers_per_position = $this->system->get_approvers_and_condition($employee_id, $this->module_id);
				if (!empty($approvers_per_position)) {
					$approver = $approvers_per_position[0]['approver'];
				}else{
					$approver = $this->system->get_reporting_to( $employee_id);	
				}
				
				// Load the template.            
				$this->load->model('template');
				$template = $this->template->get_module_template(0, 'live_evaluation');

				$this->db->where_in('user_id', $approver);
                $result = $this->db->get('user');
                $row = $result->row_array();
                $recepient = trim($row['email']);
                $vars['approver'] = $row['salutation']." ".$row['firstname']." ".$row['lastname'];


					$message = $this->template->prep_message($template['body'], $vars);
					$emailed = $this->template->queue($recepient, '', $template['subject'], $message); 
					// If queued successfully set the status to For Approval.
					if (true) {
						$this->db->where($this->key_field, $record_id);
						$this->db->update($this->module_table, array('status_id' => '2', 'date_modified' => date('Y-m-d')));

						$response->record_id  = $record_id;
						$response->msg_type = 'success';
						$response->msg = 'Training Live Request Sent.';
					}
				
			}

			$data['json'] = $response;			
			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
		
		} else {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
	}
	

	function save_remarks(){
		$data = array(
			'approver_remarks' => $this->input->post('remarks'),
			'date_modified' => date('Y-m-d'),
			'remarked' => 1
		);

		$this->db->update( $this->module_table, $data, array($this->key_field => $this->input->post('record_id')) );
		$response->record_id = $this->input->post('record_id');
		$response->msg = "success";
		$response->msg_type = "success";
		$data['json'] = $response;
		$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
	}

	private function _can_approve($records)
	{
		$record_id = $records['training_live_id'];
		$employee  = $records['employee_id'];
		if ($records['status']) {
			$status  = $records['status'];
		}else{
			$status  = $records['status_id'];
		}

		// if ($status != '2' || $status != '3') {
		// 	return false;
		// }

		if (!in_array($status, array(2,3))) {
			return false;
		}
		$approvers_per_position = $this->system->get_approvers_and_condition($employee, $this->module_id);
		
		if (!empty($approvers_per_position)) {
			$approver = $approvers_per_position[0]['approver'];
			$head = $approvers_per_position[1]['approver'];
		}else{
			$approver = $this->system->get_reporting_to( $employee);	
		}

		if ($this->user->user_id == $approver && $status == '2') {
			return true;
		}
		if ($this->user->user_id == $head && $status == '3') {
			return true;
		}

		if($this->user_access[$this->module_id]['post'] && $this->user_access[$this->module_id]['approve'] && ($employee != $this->user->user_id)){
			return true;
		}
	}
	// END custom module funtions
	
}

/* End of file */
/* Location: system/application */
?>