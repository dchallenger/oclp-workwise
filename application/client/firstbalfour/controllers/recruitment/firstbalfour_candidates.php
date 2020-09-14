<?php

include (APPPATH . 'controllers/recruitment/candidates.php');

class Firstbalfour_candidates extends Candidates
{
	public function __construct() {
		parent::__construct();
	}	

	function qualify_candidate_form(){

		//$this->db->where('recruitment_manpower.position_id',$this->input->post('position_id'));
		//$this->db->or_where_in('recruitment_manpower.position_id',$this->input->post('position2_id'));
		$this->db->where_in('recruitment_manpower.status', array('Approved', 'In-Process'));
		$this->db->join('user','user.user_id = recruitment_manpower.requested_by','left');
		$this->db->join('user_position','user_position.position_id = recruitment_manpower.position_id','left');		
		$result = $this->db->get('recruitment_manpower');
		//$response->last_query = $this->db->last_query();

		if( $result->num_rows() > 0 ){

				$select_form = '<select name="mrf_listing" id="mrf_listing" style="width:380px">';
				$records = $result->result_array();
				$data['mrf_listing'] = $result->result_array();

				$select_form .= '<option value="0" selected="selected">Please Select</option>';

				foreach( $records as $record ){
					$cat_info = $this->system->get_recruitment_category($record['category_id'],$record['category_value_id']);
					$proj_dept = $cat_info['cat_value'];

					$select_form .= '<option value="'.$record['request_id'].'" '.($record['request_id'] == $this->input->post('mrf_from_posted_jobs') ? 'SELECTED="SELECTED"' : '').'>'.$record['document_number'].' - '.$proj_dept.' - '.$record['position'].'</option>';
				}

				$select_form .='</select>';

				$data['select_form'] = $select_form;

				$select_priority = '<select name="mt_priority_list" id="mt_priority_list">';

				$mt_priority = $this->db->get_where('recruitment_mt_priority',array('deleted'=>0))->result_array();

				$select_priority .= '<option value="0">Please Select</option>';

				foreach( $mt_priority as $priority_list ){
					$select_priority .= '<option value="'.$priority_list['mt_priority_id'].'">'.$priority_list['mt_priority'].'</option>';
				}

				$select_priority .='</select>';

				$data['select_priority'] = $select_priority ;

				//for status
				$select_status = '<select name="candidate_status_id" id="candidate_status_id">';

				$mt_status = $this->db->get_where('recruitment_candidate_status',array('deleted'=>0,'can_jump'=>1))->result_array();

				$select_status .= '<option value="0">Please Select</option>';

				foreach( $mt_status as $status_list ){
					$select_status .= '<option value="'.$status_list['candidate_status_id'].'">'.$status_list['candidate_status'].'</option>';
				}

				$select_status .='</select>';

				$data['select_status'] = $select_status ;
				//for status

				$response->form = $this->load->view( $this->userinfo['rtheme'].'/recruitment/candidates/qualify_candidate_form',$data, true );
				$this->load->view('template/ajax', array('json' => $response));
		}
		else{

			$response->msg = "No MRF for preferred position";
	        $response->msg_type = "error";
	        $data['json'] = $response;

	        $this->load->view('template/ajax', array('json' => $response));

		}

	}

	function save_qualified_candidate(){

		if( $this->input->post('mrfid') != 0 ){
			$fullname = "";
			$mrf_id = $this->input->post('mrfid');
			$applicant_id = $this->input->post('applicant_id');
			$mt_priority_id = $this->input->post('mt_priority');

			$this->db->where('applicant_id',$applicant_id);
			$result = $this->db->get('recruitment_applicant');

			if ($result && $result->num_rows() > 0){
				$fullname = $result->row()->firstname .' '. $result->row()->lastname;
			}

			$data = array(
				'mrf_id' => $mrf_id,
				'applicant_id' => $applicant_id,
				'applicant_name' => $fullname,
				'is_internal' => 0,
				'employee_id' => 0,
				'contacted_thru' => 'Phone',
				'candidate_status_id' => $this->input->post('candidate_status_id'),
				'mt_priority_id' => $mt_priority_id
			);

			$result = $this->db->insert('recruitment_manpower_candidate',$data);

			if( $result ){
				$this->system->update_application_status($this->input->post('applicant_id'), $this->input->post('candidate_status_id'));
				
				$this->db->select('recruitment_manpower.position_id, user_position.position, recruitment_manpower.management_trainee');
				$this->db->where('recruitment_manpower.request_id',$mrf_id);
				$this->db->join('user_position','user_position.position_id = recruitment_manpower.position_id');
				$mrf_info = $this->db->get('recruitment_manpower')->row();

				if( $mrf_info->management_trainee == 1  ){

					//MT Positions
					
					$applicant_info = $this->db->get_where('recruitment_applicant',array('applicant_id'=>$applicant_id))->row();

					$email_data = array(
						'name' => $applicant_info->firstname." ".$applicant_info->lastname,
						'position' => $mrf_info->position,
						'no_of_days' => $this->config->item('applicant_prescreen_call_day_limit')
					);

					// $this->load->model('template');
	    //             $template = $this->template->get_module_template(38, 'applicant_prescreen_mt');
	    //             $message = $this->template->prep_message($template['body'], $email_data);
	    //             $recepients[] = $applicant_info->email;
	    //             $this->template->queue(implode(',', $recepients), '', $template['subject']." : ".$this->userinfo['firstname']." ".$this->userinfo['lastname'], $message);
					
				}
				else{
					//Other Positions

					
					$applicant_info = $this->db->get_where('recruitment_applicant',array('applicant_id'=>$applicant_id))->row();

					$email_data = array(
						'name' => $applicant_info->firstname." ".$applicant_info->lastname,
						'position' => $mrf_info->position,
						'no_of_days' => $this->config->item('applicant_prescreen_call_day_limit')
					);

					
					// $this->load->model('template');
	    //             $template = $this->template->get_module_template(38, 'applicant_prescreen_non_mt');
	    //             $message = $this->template->prep_message($template['body'], $email_data);
	    //             $recepients[] = $applicant_info->email;
	    //             $this->template->queue(implode(',', $recepients), '', $template['subject']." : ".$applicant_info->firstname." ".$applicant_info->lastname, $message);
					
				}

				$result = $this->db->get_where("recruitment_applicant_application",array("applicant_id"=>$applicant_id));

				if ($result->num_rows() < 1){
					$data = array(
						'applicant_id' => $applicant_id,
						'position_applied' => $mrf_info->position_id,
						'mrf_id' => $mrf_id,
						'applied_date' => date('Y-m-d H:i:s'),
						'status' => $this->input->post('candidate_status_id'),
						'mrf_id' => 0
					);
					//save aaplication
					$this->db->insert('recruitment_applicant_application',$data);
				}
				else{
					$this->db->where('lstatus',0);
					$this->db->where('status',$this->input->post('candidate_status_id'));
					$this->db->update('recruitment_applicant_application', array('mrf_id' => $mrf_id, 'status' =>$this->input->post('candidate_status_id'), 'position_applied' => $mrf_info->position_id ));
				}

				$this->db->where('request_id',$mrf_id);
				$this->db->update('recruitment_manpower',array("status"=>"In-Process"));				
				
				$response->msg = "Candidate is successfully added.";
			    $response->msg_type = "success";
			    $data['json'] = $response;

			}
			else{
				$response->msg = "There is an error in adding candidates in MRF.";
			    $response->msg_type = "error";
			    $data['json'] = $response;

			}

			$this->load->view('template/ajax', array('json' => $response));

		}
		else{

			$this->system->update_application_status($this->input->post('applicant_id'), $this->input->post('candidate_status_id'));

			$response->msg = "Applicant Status successfully applied";
			$response->msg_type = "success";
			$data['json'] = $response;

			$this->load->view('template/ajax', array('json' => $response));

		}
	}		
}
?>