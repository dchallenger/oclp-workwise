<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Training_evaluation extends MY_Controller
{
	function __construct()
    {
        parent::__construct();

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format	
		
		$this->listview_title = 'Training Evaluation';
		$this->listview_description = 'This module lists all defined training evaluation(s).';
		$this->jqgrid_title = "Training Evaluation List";
		$this->detailview_title = 'Training Evaluation Info';
		$this->detailview_description = 'This page shows detailed information about a particular training evaluation.';
		$this->editview_title = 'Training Evaluation Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about training evaluation(s).';
    
    	if(!$this->user_access[$this->module_id]['post']){
			$this->filter = "training_evaluation.employee_id = ".$this->userinfo['user_id'];
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
	
	function detail()
	{	
		parent::detail();
		
		//additional module detail routine here
		$data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/js/detailview.js"></script>';
		$data['content'] = 'training/training_evaluation/detailview';
		
		//other views to load
		$data['views'] = array();
		
		if( $this->input->post('record_id') != -1 ){

			$training_evaluation_info = $this->db->get_where('training_evaluation',array('training_evaluation_id'=>$this->input->post('record_id')))->row();

			$data['total_score'] = $training_evaluation_info->total_score;
			$data['average_score'] = $training_evaluation_info->average_score;
			$data['employee_id'] = $training_evaluation_info->employee_id;

		}
		else{

			$data['total_score'] = 0;
			$data['average_score'] = 0.00;

		}

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
			$data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/js/editview.js"></script>';
			if( !empty($this->module_wizard_form) && $this->input->post('record_id') == '-1' ){
				$data['show_wizard_control'] = true;
				$data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/js/editview-wizard-form.js"></script>';
			}
			$data['content'] = 'editview';
			
			//other views to load
			$data['views'] = array();
			$data['views_outside_record_form'] = array();

			if( $this->input->post('record_id') != -1 ){

				$training_evaluation_info = $this->db->get_where('training_evaluation',array('training_evaluation_id'=>$this->input->post('record_id')))->row();

				$data['total_score'] = $training_evaluation_info->total_score;
				$data['average_score'] = $training_evaluation_info->average_score;

			}
			else{

				$data['total_score'] = 0;
				$data['average_score'] = 0.00;

			}


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


		parent::ajax_save();

		//additional module save routine here

		$skills_item = $this->input->post('skills_item');
		$training_list = $this->input->post('training_list'); 
		$employee_id = $this->input->post('employee_id');
		$employee_info = $this->system->get_employee($employee_id);

		$this->db->where('training_evaluation_id',$this->input->post('record_id'));
		$this->db->delete('training_evaluation_competence_score');

		$this->db->select('training_position_skills.position_skills_id, training_position_skills.position_skills, training_position_skills.weight, training_position_skills_item.*');
		$this->db->join('training_position_skills_item','training_position_skills_item.position_skills_id = training_position_skills.position_skills_id','left');
		$this->db->where('training_position_skills.position_id',$employee_info['position_id']);
		$this->db->where('training_position_skills_item.inactive != 1');
		$this->db->where('training_position_skills.deleted',0);
		$this->db->order_by('training_position_skills.position_skills_id','ASC');
		$this->db->order_by('training_position_skills_item.skills_item_no','ASC');
		$questionnaire_list = $this->db->get('training_position_skills');
		$questionnaire_details = $questionnaire_list->result();

		foreach( $questionnaire_details as $questionnaire_detail_info ){

			if( in_array( $questionnaire_detail_info->score_type, array(1,2,4,5) ) ){
				$data = array(
					'training_evaluation_id' => $this->key_field_val,
					'skills_item_id' => $questionnaire_detail_info->skills_item_id,
					'score' => $skills_item[$questionnaire_detail_info->skills_item_id]
				);
			}
			elseif( in_array( $questionnaire_detail_info->score_type, array(6) ) ){

				$remarks = "";

				switch($skills_item[$questionnaire_detail_info->skills_item_id]['score']){
					case 1:
						$remarks = $skills_item[$questionnaire_detail_info->skills_item_id]['remarks1'];
					break;
					case 2:
						$remarks = $skills_item[$questionnaire_detail_info->skills_item_id]['remarks2'];
					break;
					case 3:
						$remarks = $skills_item[$questionnaire_detail_info->skills_item_id]['remarks3'];
					break;
					case 4:
						$remarks = $skills_item[$questionnaire_detail_info->skills_item_id]['remarks4'];
					break;
				}

				$data = array(
					'training_evaluation_id' => $this->key_field_val,
					'skills_item_id' => $questionnaire_detail_info->skills_item_id,
					'score' => $skills_item[$questionnaire_detail_info->skills_item_id]['score'],
					'remarks' => $remarks
				);

			}
			else{
				$data = array(
					'training_evaluation_id' => $this->key_field_val,
					'skills_item_id' => $questionnaire_detail_info->skills_item_id,
					'remarks' => $skills_item[$questionnaire_detail_info->skills_item_id]
				);
			}

			$this->db->insert('training_evaluation_competence_score',$data);

		}


		$this->db->where('training_evaluation_id',$this->key_field_val);
		$this->db->delete('training_evaluation_subject_list');

		foreach( $training_list as $training_subject_id ){

			$data = array(
				'training_evaluation_id' => $this->key_field_val,
				'training_subject_id' => $training_subject_id
			);

			$this->db->insert('training_evaluation_subject_list',$data);

		}
		
				
	}
	
	function delete()
	{

		$record_id_list = explode(',', $this->input->post('record_id'));

		foreach( $record_id_list as $record_id ){

			//additional module delete routine here
			$this->db->where($this->key_field,$record_id);
			$this->db->delete('training_evaluation_subject_list');

			$this->db->where($this->key_field,$record_id);
			$this->db->delete('training_evaluation_competence_score');

		}

		parent::delete();

		
	
	}

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
            $actions .= '<a class="icon-button icon-16-edit" tooltip="Edit" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
        } 
				
        if ($this->user_access[$this->module_id]['print'] ) {
            $actions .= '<a class="icon-button icon-16-print print-record" tooltip="Print" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
        }        
        
        if ($this->user_access[$this->module_id]['delete']) {
            $actions .= '<a class="icon-button icon-16-delete delete-single" tooltip="Delete" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
        }

        $actions .= '</span>';

		return $actions;
	}

	function print_record($record_id) {	

		$data = array();

		$this->load->model('template');
		$this->config->config['compress_output'] = 0;
		$template = $this->template->get_module_template($this->module_id, 'training_evaluation_form');

		$training_evaluation = $this->db->get_where('training_evaluation',array('deleted'=>0,'training_evaluation_id'=>$record_id))->row();

		$this->db->select('user.firstname, user.lastname, user_position.position, user_position.position_id, user_company_department.department, user_company_division.division, employee_work_assignment.employee_work_assignment_category_id');
		$this->db->join('employee','employee.employee_id = user.employee_id','left');
		$this->db->join('employee_work_assignment','employee_work_assignment.employee_id = user.employee_id AND '.$this->db->dbprefix('employee_work_assignment').'.assignment = "1"','left');
		$this->db->join('user_company_department','user_company_department.department_id = employee_work_assignment.department_id','left');
		$this->db->join('user_company_division','user_company_division.division_id = employee_work_assignment.division_id','left');
		$this->db->join('user_position','user.position_id = user_position.position_id','left');
		$this->db->where('user.user_id',$training_evaluation->employee_id);
		$employee_result = $this->db->get('user')->row();

		$data['date'] = date('F d, Y');
		$data['employee_name'] = $employee_result->firstname.' '.$employee_result->lastname;
		$data['position'] = $employee_result->position;
		$data['division_department'] = "";

		switch( $employee_result->employee_work_assignment_category_id ){
			case 1:
				$data['division_department'] = $employee_result->division;
			break;
			case 4:
				$data['division_department'] = $employee_result->department;
			break;
		}


		$answer_details_count = $this->db->get_where('training_evaluation_competence_score',array('training_evaluation_id'=>$record_id))->num_rows();

		if( $answer_details_count > 0 ){
			
			$this->db->select('training_position_skills.position_skills_id, training_position_skills.position_skills, training_position_skills.weight, training_position_skills_item.*');
			$this->db->join('training_position_skills_item','training_position_skills_item.position_skills_id = training_position_skills.position_skills_id','left');
			$this->db->where('training_position_skills.position_id',$employee_result->position_id);
			$this->db->where('training_position_skills_item.inactive != 1');
			$this->db->where('training_position_skills.deleted',0);
			$this->db->order_by('training_position_skills.position_skills_id','ASC');
			$this->db->order_by('training_position_skills_item.skills_item_no','ASC');
			$questionnaire_details = $this->db->get('training_position_skills');

		}
		else{

			$this->db->select('training_position_skills.position_skills_id, training_position_skills.position_skills, training_position_skills.weight, training_position_skills_item.*');
			$this->db->join('training_position_skills_item','training_position_skills_item.position_skills_id = training_position_skills.position_skills_id','left');
			$this->db->where('training_position_skills.position_id',$employee_result->position_id);
			$this->db->where('training_position_skills_item.inactive != 1');
			$this->db->where('training_position_skills.deleted',0);
			$this->db->order_by('training_position_skills.position_skills_id','ASC');
			$this->db->order_by('training_position_skills_item.skills_item_no','ASC');
			$questionnaire_details = $this->db->get('training_position_skills');

		}

		$data['competency_questionnaire_item_count'] = $questionnaire_details->num_rows();

		if( $questionnaire_details->num_rows() > 0 ){
			$data['competency_questionnaire_items'] = $questionnaire_details->result_array();

			foreach( $data['competency_questionnaire_items'] as $key => $val ){

				$competency_questionnaire_score = $this->db->get_where('training_evaluation_competence_score',array('training_evaluation_id'=>$record_id, 'skills_item_id'=> $data['competency_questionnaire_items'][$key]['skills_item_id'] ));

				if( $competency_questionnaire_score->num_rows() > 0 ){

					$competency_questionnaire_score_info = $competency_questionnaire_score->row();

					$data['competency_questionnaire_items'][$key]['score'] = $competency_questionnaire_score_info->score;
					$data['competency_questionnaire_items'][$key]['remarks'] = $competency_questionnaire_score_info->remarks;

				}

				if( $data['competency_questionnaire_items'][$key]['score_type'] == 6 ){
					$competency_questionaire_multiple = $this->db->get_where('training_position_skills_multiple_subcriteria',array('skills_item_id'=> $data['competency_questionnaire_items'][$key]['skills_item_id']));
				
					foreach( $competency_questionaire_multiple->row_array() as $mkey => $mval ){
						$data['competency_questionnaire_items'][$key]['subcriteria'][$mkey] = $mval;
					}

				}

			}
		}

		$data['competency_items'] = "";
		$current_position_skills_id = 0;
		$current_score_type = 0;

		foreach( $data['competency_questionnaire_items'] as $competency_items ){

			switch( $competency_items['score_type'] ){
				case 1: //5-point scale

					if( $current_position_skills_id != $competency_items['position_skills_id'] ){
						$data['competency_items'] .= '
						<tr>
							<td colspan="8" style="width:100%; background-color:#ccc;"><p style="font-size:small; font-weight:bold;">'.$competency_items['position_skills'].'</p></td>
						</tr>
						';

						$current_score_type = 0;
					}

					if( $current_score_type != $competency_items['score_type'] ){

						$data['competency_items'] .= '
						<tr>
							<td colspan="3" style="width:40%; background-color:#ccc;"><p style="font-size:small; font-weight:bold;"></p></td>
							<td style="width:15%; text-align:center; background-color:#ccc;"><p style="font-size:small; font-weight:bold;">Strongly Disagree</p></td>
							<td style="width:10%; text-align:center; background-color:#ccc;"><p style="font-size:small; font-weight:bold;">Disagree</p></td>
							<td style="width:10%; text-align:center; background-color:#ccc;"><p style="font-size:small; font-weight:bold;">Neutral</p></td>
							<td style="width:10%; text-align:center; background-color:#ccc;"><p style="font-size:small; font-weight:bold;">Agree</p></td>
							<td style="width:15%; text-align:center; background-color:#ccc;"><p style="font-size:small; font-weight:bold;">Strongly Agree</p></td>
						</tr>';
					}
					

					$data['competency_items'] .= '
					<tr><td colspan="8" style="background-color:#FAFAFA;">&nbsp;</td></tr>
					<tr>
						<td style="width:5%; background-color:#FAFAFA;">&nbsp;</td>
						<td colspan="2" style="width:35%; background-color:#FAFAFA;"><p style="font-size:small;">'.$competency_items['skills_item'].'</p></td>';
						
					switch( $competency_items['score'] ){
						case 1.00:

							$data['competency_items'] .= '<td style="width:15%; text-align:center; background-color:#FAFAFA;">X</td>
							<td style="width:10%; text-align:center; background-color:#FAFAFA;"></td>
							<td style="width:10%; text-align:center; background-color:#FAFAFA;"></td>
							<td style="width:10%; text-align:center; background-color:#FAFAFA;"></td>
							<td style="width:15%; text-align:center; background-color:#FAFAFA;"></td>';

						break;
						case 2.00:

							$data['competency_items'] .= '<td style="width:15%; text-align:center; background-color:#FAFAFA;"></td>
							<td style="width:10%; text-align:center; background-color:#FAFAFA;">X</td>
							<td style="width:10%; text-align:center; background-color:#FAFAFA;"></td>
							<td style="width:10%; text-align:center; background-color:#FAFAFA;"></td>
							<td style="width:15%; text-align:center; background-color:#FAFAFA;"></td>';

						break;
						case 3.00:

							$data['competency_items'] .= '<td style="width:15%; text-align:center; background-color:#FAFAFA;"></td>
							<td style="width:10%; text-align:center; background-color:#FAFAFA;"></td>
							<td style="width:10%; text-align:center; background-color:#FAFAFA;">X</td>
							<td style="width:10%; text-align:center; background-color:#FAFAFA;"></td>
							<td style="width:15%; text-align:center; background-color:#FAFAFA;"></td>';

						break;
						case 4.00:

							$data['competency_items'] .= '<td style="width:15%; text-align:center; background-color:#FAFAFA;"></td>
							<td style="width:10%; text-align:center; background-color:#FAFAFA;"></td>
							<td style="width:10%; text-align:center; background-color:#FAFAFA;"></td>
							<td style="width:10%; text-align:center; background-color:#FAFAFA;">X</td>
							<td style="width:15%; text-align:center; background-color:#FAFAFA;"></td>';

						break;
						case 5.00:

							$data['competency_items'] .= '<td style="width:15%; text-align:center; background-color:#FAFAFA;"></td>
							<td style="width:10%; text-align:center; background-color:#FAFAFA;"></td>
							<td style="width:10%; text-align:center; background-color:#FAFAFA;"></td>
							<td style="width:10%; text-align:center; background-color:#FAFAFA;"></td>
							<td style="width:15%; text-align:center; background-color:#FAFAFA;">X</td>';
						break;
						default:
							$data['competency_items'] .= '<td style="width:15%; text-align:center; background-color:#FAFAFA;"></td>
							<td style="width:10%; text-align:center; background-color:#FAFAFA;"></td>
							<td style="width:10%; text-align:center; background-color:#FAFAFA;"></td>
							<td style="width:10%; text-align:center; background-color:#FAFAFA;"></td>
							<td style="width:15%; text-align:center; background-color:#FAFAFA;"></td>';
						break;
					}

					$data['competency_items'] .= '</tr><tr><td colspan="8" style="background-color:#FAFAFA;">&nbsp;</td></tr>';
					

					$current_position_skills_id = $competency_items['position_skills_id'];
					$current_score_type = $competency_items['score_type'];
					$current_score_type = $competency_items['score_type'];

					

				break;
				case 2: //yes or no


					if( $current_position_skills_id != $competency_items['position_skills_id'] ){
						$data['competency_items'] .= '
						<tr>
							<td colspan="8" style="width:100%; background-color:#ccc;"><p style="font-size:small; font-weight:bold;">'.$competency_items['position_skills'].'</p></td>
						</tr>
						';

						$current_score_type = 0;
					}

					if( $current_score_type != $competency_items['score_type'] ){

						$data['competency_items'] .= '
						<tr>
							<td colspan="6" style="width:80%; background-color:#ccc;"><p style="font-size:small; font-weight:bold;"></p></td>
							<td style="width:10%; text-align:center; background-color:#ccc;"><p style="font-size:small; font-weight:bold;">Yes</p></td>
							<td style="width:10%; text-align:center; background-color:#ccc;"><p style="font-size:small; font-weight:bold;">No</p></td>
						</tr>';
					}

					$data['competency_items'] .= '
					<tr><td colspan="8" style="background-color:#FAFAFA;">&nbsp;</td></tr>
					<tr>
						<td style="width:5%; background-color:#FAFAFA;">&nbsp;</td>
						<td colspan="5" style="width:75%; background-color:#FAFAFA;"><p style="font-size:small;">'.$competency_items['skills_item'].'</p></td>';

						switch( $competency_items['score'] ){
							case 0.00:
								$data['competency_items'] .= '
								<td style="width:10%; text-align:center; background-color:#FAFAFA;"></td>
								<td style="width:10%; text-align:center; background-color:#FAFAFA;">X</td>';
							break;
							case 5.00:

								$data['competency_items'] .= '
								<td style="width:10%; text-align:center; background-color:#FAFAFA;">X</td>
								<td style="width:10%; text-align:center; background-color:#FAFAFA;"></td>';
							break;
							default:
								$data['competency_items'] .= '
								<td style="width:10%; text-align:center; background-color:#FAFAFA;"></td>
								<td style="width:10%; text-align:center; background-color:#FAFAFA;"></td>';
							break;
						}

						$data['competency_items'] .= '</tr><tr><td colspan="8" style="background-color:#FAFAFA;">&nbsp;</td></tr>';


					$current_position_skills_id = $competency_items['position_skills_id'];
					$current_score_type = $competency_items['score_type'];

				break;
				case 3: //essay

					if( $current_position_skills_id != $competency_items['position_skills_id'] ){
						$data['competency_items'] .= '
						<tr>
							<td colspan="8" style="width:100%; background-color:#ccc;"><p style="font-size:small; font-weight:bold;">'.$competency_items['position_skills'].'</p></td>
						</tr>
						';

						$current_score_type = 0;
					}

					if( $current_score_type != $competency_items['score_type'] ){

						$data['competency_items'] .= '
						<tr>
							<td colspan="8" style="width:100%; background-color:#ccc;"><p style="font-size:small; font-weight:bold;"></p></td>
						</tr>';
					}

					$data['competency_items'] .='
					<tr><td colspan="8" style="background-color:#FAFAFA;">&nbsp;</td></tr>
					<tr>
						<td colspan="8" style="width:100%; background-color:#FAFAFA;"><p style="font-size:small;">'.$competency_items['skills_item'].'</p></td>
					</tr>
					<tr>
						<td colspan="7" style="width:95%; background-color:#FAFAFA; border:1px solid #000;"><br/><p style="font-size:small;">'.$competency_items['remarks'].'</p><br/></td>
						<td style="width:5%; background-color:#FAFAFA;">&nbsp;</td>
					</tr>
					<tr><td colspan="8" style="background-color:#FAFAFA;">&nbsp;</td></tr>';

					$current_position_skills_id = $competency_items['position_skills_id'];
					$current_score_type = $competency_items['score_type'];


				break;
				case 4: //6-point scale

					if( $current_position_skills_id != $competency_items['position_skills_id'] ){
						$data['competency_items'] .= '
						<tr>
							<td colspan="8" style="width:100%; background-color:#ccc;"><p style="font-size:small; font-weight:bold;">'.$competency_items['position_skills'].'</p></td>
						</tr>
						';

						$current_score_type = 0;
					}

					if( $current_score_type != $competency_items['score_type'] ){

						$data['competency_items'] .= '
						<tr>
							<td colspan="2" style="width:40%; background-color:#ccc;"><p style="font-size:small; font-weight:bold;"></p></td>
							<td style="width:10%; text-align:center; background-color:#ccc;"><p style="font-size:small; font-weight:bold;">Not Much</p></td>
							<td style="width:10%; text-align:center; background-color:#ccc;"><p style="font-size:small; font-weight:bold;">Basic</p></td>
							<td style="width:10%; text-align:center; background-color:#ccc;"><p style="font-size:small; font-weight:bold;">Average</p></td>
							<td style="width:10%; text-align:center; background-color:#ccc;"><p style="font-size:small; font-weight:bold;">Good</p></td>
							<td style="width:10%; text-align:center; background-color:#ccc;"><p style="font-size:small; font-weight:bold;">Very Good</p></td>
							<td style="width:10%; text-align:center; background-color:#ccc;"><p style="font-size:small; font-weight:bold;">Excellent</p></td>
						</tr>';
					}

					
					$data['competency_items'] .= '
					<tr><td colspan="8" style="background-color:#FAFAFA;">&nbsp;</td></tr>
					<tr>
						<td style="width:5%; background-color:#FAFAFA;">&nbsp;</td>
						<td style="width:35%; background-color:#FAFAFA;"><p style="font-size:small;">'.$competency_items['skills_item'].'</p></td>';
						
						switch( $competency_items['score'] ){
							case 0.00:

								$data['competency_items'] .= '
								<td style="width:10%; text-align:center; background-color:#FAFAFA;">X</td>
								<td style="width:10%; text-align:center; background-color:#FAFAFA;"></td>
								<td style="width:10%; text-align:center; background-color:#FAFAFA;"></td>
								<td style="width:10%; text-align:center; background-color:#FAFAFA;"></td>
								<td style="width:10%; text-align:center; background-color:#FAFAFA;"></td>
								<td style="width:10%; text-align:center; background-color:#FAFAFA;"></td>';

							break;
							case 1.00:

								$data['competency_items'] .= '
								<td style="width:10%; text-align:center; background-color:#FAFAFA;"></td>
								<td style="width:10%; text-align:center; background-color:#FAFAFA;">X</td>
								<td style="width:10%; text-align:center; background-color:#FAFAFA;"></td>
								<td style="width:10%; text-align:center; background-color:#FAFAFA;"></td>
								<td style="width:10%; text-align:center; background-color:#FAFAFA;"></td>
								<td style="width:10%; text-align:center; background-color:#FAFAFA;"></td>';

							break;
							case 2.00:

								$data['competency_items'] .= '
								<td style="width:10%; text-align:center; background-color:#FAFAFA;"></td>
								<td style="width:10%; text-align:center; background-color:#FAFAFA;"></td>
								<td style="width:10%; text-align:center; background-color:#FAFAFA;">X</td>
								<td style="width:10%; text-align:center; background-color:#FAFAFA;"></td>
								<td style="width:10%; text-align:center; background-color:#FAFAFA;"></td>
								<td style="width:10%; text-align:center; background-color:#FAFAFA;"></td>';

							break;
							case 3.00:

								$data['competency_items'] .= '
								<td style="width:10%; text-align:center; background-color:#FAFAFA;"></td>
								<td style="width:10%; text-align:center; background-color:#FAFAFA;"></td>
								<td style="width:10%; text-align:center; background-color:#FAFAFA;"></td>
								<td style="width:10%; text-align:center; background-color:#FAFAFA;">X</td>
								<td style="width:10%; text-align:center; background-color:#FAFAFA;"></td>
								<td style="width:10%; text-align:center; background-color:#FAFAFA;"></td>';

							break;
							case 4.00:

								$data['competency_items'] .= '
								<td style="width:10%; text-align:center; background-color:#FAFAFA;"></td>
								<td style="width:10%; text-align:center; background-color:#FAFAFA;"></td>
								<td style="width:10%; text-align:center; background-color:#FAFAFA;"></td>
								<td style="width:10%; text-align:center; background-color:#FAFAFA;"></td>
								<td style="width:10%; text-align:center; background-color:#FAFAFA;">X</td>
								<td style="width:10%; text-align:center; background-color:#FAFAFA;"></td>';

							break;
							case 5.00:

								$data['competency_items'] .= '
								<td style="width:10%; text-align:center; background-color:#FAFAFA;"></td>
								<td style="width:10%; text-align:center; background-color:#FAFAFA;"></td>
								<td style="width:10%; text-align:center; background-color:#FAFAFA;"></td>
								<td style="width:10%; text-align:center; background-color:#FAFAFA;"></td>
								<td style="width:10%; text-align:center; background-color:#FAFAFA;"></td>
								<td style="width:10%; text-align:center; background-color:#FAFAFA;">X</td>';
							break;
							default:
								$data['competency_items'] .= '
								<td style="width:10%; text-align:center; background-color:#FAFAFA;"></td>
								<td style="width:10%; text-align:center; background-color:#FAFAFA;"></td>
								<td style="width:10%; text-align:center; background-color:#FAFAFA;"></td>
								<td style="width:10%; text-align:center; background-color:#FAFAFA;"></td>
								<td style="width:10%; text-align:center; background-color:#FAFAFA;"></td>
								<td style="width:10%; text-align:center; background-color:#FAFAFA;"></td>';
							break;
						}

						$data['competency_items'] .= '</tr>
						<tr><td colspan="8" style="background-color:#FAFAFA;">&nbsp;</td></tr>';
					

					$current_position_skills_id = $competency_items['position_skills_id'];
					$current_score_type = $competency_items['score_type'];


				break;
				case 5: //4-point scale


					if( $current_position_skills_id != $competency_items['position_skills_id'] ){
						$data['competency_items'] .= '
						<tr>
							<td colspan="8" style="width:100%; background-color:#ccc;"><p style="font-size:small; font-weight:bold;">'.$competency_items['position_skills'].'</p></td>
						</tr>
						';

						$current_score_type = 0;
					}

					if( $current_score_type != $competency_items['score_type'] ){

						$data['competency_items'] .= '
						<tr>
							<td colspan="4" style="width:40%; background-color:#ccc;"><p style="font-size:small; font-weight:bold;"></p></td>
							<td style="width:15%; text-align:center; background-color:#ccc;"><p style="font-size:small; font-weight:bold;">Unsatisfactory</p></td>
							<td style="width:15%; text-align:center; background-color:#ccc;"><p style="font-size:small; font-weight:bold;">Needs improvement</p></td>
							<td style="width:15%; text-align:center; background-color:#ccc;"><p style="font-size:small; font-weight:bold;">Meets requirements</p></td>
							<td style="width:15%; text-align:center; background-color:#ccc;"><p style="font-size:small; font-weight:bold;">Excellent</p></td>
						</tr>';
					}


					$data['competency_items'] .= '
					<tr><td colspan="8" style="background-color:#FAFAFA;">&nbsp;</td></tr>
					<tr>
						<td style="width:5%; background-color:#FAFAFA;">&nbsp;</td>
						<td colspan="3" style="width:35%; background-color:#FAFAFA;"><p style="font-size:small;">'.$competency_items['skills_item'].'</p></td>';
						
						switch( $competency_items['score'] ){
							case 1.25:

								$data['competency_items'] .= '
								<td style="width:15%; text-align:center; background-color:#FAFAFA;">X</td>
								<td style="width:15%; text-align:center; background-color:#FAFAFA;"></td>
								<td style="width:15%; text-align:center; background-color:#FAFAFA;"></td>
								<td style="width:15%; text-align:center; background-color:#FAFAFA;"></td>';

							break;
							case 2.50:

								$data['competency_items'] .= '
								<td style="width:15%; text-align:center; background-color:#FAFAFA;"></td>
								<td style="width:15%; text-align:center; background-color:#FAFAFA;">X</td>
								<td style="width:15%; text-align:center; background-color:#FAFAFA;"></td>
								<td style="width:15%; text-align:center; background-color:#FAFAFA;"></td>';

							break;
							case 3.75:

								$data['competency_items'] .= '
								<td style="width:15%; text-align:center; background-color:#FAFAFA;"></td>
								<td style="width:15%; text-align:center; background-color:#FAFAFA;"></td>
								<td style="width:15%; text-align:center; background-color:#FAFAFA;">X</td>
								<td style="width:15%; text-align:center; background-color:#FAFAFA;"></td>';

							break;
							case 5.00:

								$data['competency_items'] .= '
								<td style="width:15%; text-align:center; background-color:#FAFAFA;"></td>
								<td style="width:15%; text-align:center; background-color:#FAFAFA;"></td>
								<td style="width:15%; text-align:center; background-color:#FAFAFA;"></td>
								<td style="width:15%; text-align:center; background-color:#FAFAFA;">X</td>';
							break;
							default:
								$data['competency_items'] .= '
								<td style="width:15%; text-align:center; background-color:#FAFAFA;"></td>
								<td style="width:15%; text-align:center; background-color:#FAFAFA;"></td>
								<td style="width:15%; text-align:center; background-color:#FAFAFA;"></td>
								<td style="width:15%; text-align:center; background-color:#FAFAFA;"></td>';
							break;
						}

						$data['competency_items'] .= '</tr><tr><td colspan="8" style="background-color:#FAFAFA;">&nbsp;</td></tr>';
				

					$current_position_skills_id = $competency_items['position_skills_id'];
					$current_score_type = $competency_items['score_type'];

				break;
				
				case 6://multiple

					if( $current_position_skills_id != $competency_items['position_skills_id'] ){
						$data['competency_items'] .= '
						<tr>
							<td colspan="8" style="width:100%; background-color:#ccc;"><p style="font-size:small; font-weight:bold;">'.$competency_items['position_skills'].'</p></td>
						</tr>
						';

						$current_score_type = 0;
					}

					if( $current_score_type != $competency_items['score_type'] ){

						$data['competency_items'] .= '
						<tr>
							<td colspan="8" style="width:100%; margin-top:5px; background-color:#ccc;"><p style="font-size:small; font-weight:bold;"></p></td>
						</tr>';
					}


					$data['competency_items'] .= '
					<tr><td colspan="8" style="background-color:#FAFAFA;">&nbsp;</td></tr>
					<tr>
						<td colspan="8" style="width:100%; background-color:#FAFAFA;"><p style="font-size:small;">'.$competency_items['skills_item'].'</p></td>
					</tr>
					<tr><td colspan="8" style="background-color:#FAFAFA;">&nbsp;</td></tr>';
					
					switch( $competency_items['score'] ){
						case 1.00:

								$data['competency_items'] .= '
								<tr>
									<td style="width:5%; background-color:#FAFAFA;">&nbsp;</td>
									<td style="width:5%; border:1px solid #000; text-align:center; background-color:#FAFAFA;">X</td>
									<td style="width:30%; background-color:#FAFAFA;"><p style="font-size:small;">'.$competency_items['subcriteria']['sub_criteria1'].'</p></td>
									<td colspan="5" style="width:60%; border:2px solid #000; background-color:#FAFAFA;"><p style="font-size:small;">'.$competency_items['remarks'].'</p></td>
								</tr>
								<tr><td colspan="8" style="background-color:#FAFAFA;">&nbsp;</td></tr>
								<tr>
									<td style="background-color:#FAFAFA; width:5%;">&nbsp;</td>
									<td style="width:5%; border:1px solid #000; text-align:center; background-color:#FAFAFA;"></td>
									<td style="width:30%; background-color:#FAFAFA;"><p style="font-size:small;">'.$competency_items['subcriteria']['sub_criteria2'].'</p></td>
									<td colspan="5" style="width:60%; border:2px solid #000; background-color:#FAFAFA;"><p style="font-size:small;"></p></td>
								</tr>
								<tr><td colspan="8" style="background-color:#FAFAFA;">&nbsp;</td></tr>
								<tr>
									<td style="width:5%; background-color:#FAFAFA;">&nbsp;</td>
									<td style="width:5%; border:1px solid #000; text-align:center; background-color:#FAFAFA;"></td>
									<td style="width:30%; background-color:#FAFAFA;"><p style="font-size:small;">'.$competency_items['subcriteria']['sub_criteria3'].'</p></td>
									<td colspan="5" style="width:60%; border:2px solid #000; background-color:#FAFAFA;"><p style="font-size:small;"></p></td>
								</tr>
								<tr><td colspan="8" style="background-color:#FAFAFA;">&nbsp;</td></tr>
								<tr>
									<td style="width:5%; background-color:#FAFAFA;">&nbsp;</td>
									<td style="width:5%; border:1px solid #000; text-align:center; background-color:#FAFAFA;"></td>
									<td style="width:30%; background-color:#FAFAFA;"><p style="font-size:small;">'.$competency_items['subcriteria']['sub_criteria4'].'</p></td>
									<td colspan="5" style="width:60%; border:2px solid #000; background-color:#FAFAFA;"><p style="font-size:small;"></p></td>
								</tr>';

							break;
							case 2.00:

								$data['competency_items'] .= '
								<tr>
									<td style="width:5%; background-color:#FAFAFA;">&nbsp;</td>
									<td style="width:5%; border:1px solid #000; text-align:center; background-color:#FAFAFA;"></td>
									<td style="width:30%; background-color:#FAFAFA;"><p style="font-size:small;">'.$competency_items['subcriteria']['sub_criteria1'].'</p></td>
									<td colspan="5" style="width:60%; border:2px solid #000; background-color:#FAFAFA;"><p style="font-size:small;"></p></td>
								</tr>
								<tr><td colspan="8" style="background-color:#FAFAFA;">&nbsp;</td></tr>
								<tr>
									<td style="background-color:#FAFAFA; width:5%;">&nbsp;</td>
									<td style="width:5%; border:1px solid #000; text-align:center; background-color:#FAFAFA;">X</td>
									<td style="width:30%; background-color:#FAFAFA;"><p style="font-size:small;">'.$competency_items['subcriteria']['sub_criteria2'].'</p></td>
									<td colspan="5" style="width:60%; border:2px solid #000; background-color:#FAFAFA;"><p style="font-size:small;">'.$competency_items['remarks'].'</p></td>
								</tr>
								<tr><td colspan="8" style="background-color:#FAFAFA;">&nbsp;</td></tr>
								<tr>
									<td style="width:5%; background-color:#FAFAFA;">&nbsp;</td>
									<td style="width:5%; border:1px solid #000; text-align:center; background-color:#FAFAFA;"></td>
									<td style="width:30%; background-color:#FAFAFA;"><p style="font-size:small;">'.$competency_items['subcriteria']['sub_criteria3'].'</p></td>
									<td colspan="5" style="width:60%; border:2px solid #000; background-color:#FAFAFA;"><p style="font-size:small;"></p></td>
								</tr>
								<tr><td colspan="8" style="background-color:#FAFAFA;">&nbsp;</td></tr>
								<tr>
									<td style="width:5%; background-color:#FAFAFA;">&nbsp;</td>
									<td style="width:5%; border:1px solid #000; text-align:center; background-color:#FAFAFA;"></td>
									<td style="width:30%; background-color:#FAFAFA;"><p style="font-size:small;">'.$competency_items['subcriteria']['sub_criteria4'].'</p></td>
									<td colspan="5" style="width:60%; border:2px solid #000; background-color:#FAFAFA;"><p style="font-size:small;"></p></td>
								</tr>';

							break;
							case 3.00:

								$data['competency_items'] .= '
								<tr>
									<td style="width:5%; background-color:#FAFAFA;">&nbsp;</td>
									<td style="width:5%; border:1px solid #000; text-align:center; background-color:#FAFAFA;"></td>
									<td style="width:30%; background-color:#FAFAFA;"><p style="font-size:small;">'.$competency_items['subcriteria']['sub_criteria1'].'</p></td>
									<td colspan="5" style="width:60%; border:2px solid #000; background-color:#FAFAFA;"><p style="font-size:small;"></p></td>
								</tr>
								<tr><td colspan="8" style="background-color:#FAFAFA;">&nbsp;</td></tr>
								<tr>
									<td style="background-color:#FAFAFA; width:5%;">&nbsp;</td>
									<td style="width:5%; border:1px solid #000; text-align:center; background-color:#FAFAFA;"></td>
									<td style="width:30%; background-color:#FAFAFA;"><p style="font-size:small;">'.$competency_items['subcriteria']['sub_criteria2'].'</p></td>
									<td colspan="5" style="width:60%; border:2px solid #000; background-color:#FAFAFA;"><p style="font-size:small;"></p></td>
								</tr>
								<tr><td colspan="8" style="background-color:#FAFAFA;">&nbsp;</td></tr>
								<tr>
									<td style="width:5%; background-color:#FAFAFA;">&nbsp;</td>
									<td style="width:5%; border:1px solid #000; text-align:center; background-color:#FAFAFA;">X</td>
									<td style="width:30%; background-color:#FAFAFA;"><p style="font-size:small;">'.$competency_items['subcriteria']['sub_criteria3'].'</p></td>
									<td colspan="5" style="width:60%; border:2px solid #000; background-color:#FAFAFA;"><p style="font-size:small;">'.$competency_items['remarks'].'</p></td>
								</tr>
								<tr><td colspan="8" style="background-color:#FAFAFA;">&nbsp;</td></tr>
								<tr>
									<td style="width:5%; background-color:#FAFAFA;">&nbsp;</td>
									<td style="width:5%; border:1px solid #000; text-align:center; background-color:#FAFAFA;"></td>
									<td style="width:30%; background-color:#FAFAFA;"><p style="font-size:small;">'.$competency_items['subcriteria']['sub_criteria4'].'</p></td>
									<td colspan="5" style="width:60%; border:2px solid #000; background-color:#FAFAFA;"><p style="font-size:small;"></p></td>
								</tr>';

							break;
							case 4.00:

								$data['competency_items'] .= '
								<tr>
									<td style="width:5%; background-color:#FAFAFA;">&nbsp;</td>
									<td style="width:5%; border:1px solid #000; text-align:center; background-color:#FAFAFA;"></td>
									<td style="width:30%; background-color:#FAFAFA;"><p style="font-size:small;">'.$competency_items['subcriteria']['sub_criteria1'].'</p></td>
									<td colspan="5" style="width:60%; border:2px solid #000; background-color:#FAFAFA;"><p style="font-size:small;"></p></td>
								</tr>
								<tr><td colspan="8" style="background-color:#FAFAFA;">&nbsp;</td></tr>
								<tr>
									<td style="background-color:#FAFAFA; width:5%;">&nbsp;</td>
									<td style="width:5%; border:1px solid #000; text-align:center; background-color:#FAFAFA;"></td>
									<td style="width:30%; background-color:#FAFAFA;"><p style="font-size:small;">'.$competency_items['subcriteria']['sub_criteria2'].'</p></td>
									<td colspan="5" style="width:60%; border:2px solid #000; background-color:#FAFAFA;"><p style="font-size:small;"></p></td>
								</tr>
								<tr><td colspan="8" style="background-color:#FAFAFA;">&nbsp;</td></tr>
								<tr>
									<td style="width:5%; background-color:#FAFAFA;">&nbsp;</td>
									<td style="width:5%; border:1px solid #000; text-align:center; background-color:#FAFAFA;"></td>
									<td style="width:30%; background-color:#FAFAFA;"><p style="font-size:small;">'.$competency_items['subcriteria']['sub_criteria3'].'</p></td>
									<td colspan="5" style="width:60%; border:2px solid #000; background-color:#FAFAFA;"><p style="font-size:small;"></p></td>
								</tr>
								<tr><td colspan="8" style="background-color:#FAFAFA;">&nbsp;</td></tr>
								<tr>
									<td style="width:5%; background-color:#FAFAFA;">&nbsp;</td>
									<td style="width:5%; border:1px solid #000; text-align:center; background-color:#FAFAFA;">X</td>
									<td style="width:30%; background-color:#FAFAFA;"><p style="font-size:small;">'.$competency_items['subcriteria']['sub_criteria4'].'</p></td>
									<td colspan="5" style="width:60%; border:2px solid #000; background-color:#FAFAFA;"><p style="font-size:small;">'.$competency_items['remarks'].'</p></td>
								</tr>';


							break;	
							default:
								$data['competency_items'] .= '
									<tr>
										<td style="width:5%; background-color:#FAFAFA;">&nbsp;</td>
										<td style="width:5%; border:1px solid #000; text-align:center; background-color:#FAFAFA;"></td>
										<td style="width:30%; background-color:#FAFAFA;"><p style="font-size:small;">'.$competency_items['subcriteria']['sub_criteria1'].'</p></td>
										<td colspan="5" style="width:60%; border:2px solid #000; background-color:#FAFAFA;"><p style="font-size:small;"></p></td>
									</tr>
									<tr><td colspan="8" style="background-color:#FAFAFA;">&nbsp;</td></tr>
									<tr>
										<td style="background-color:#FAFAFA; width:5%;">&nbsp;</td>
										<td style="width:5%; border:1px solid #000; text-align:center; background-color:#FAFAFA;"></td>
										<td style="width:30%; background-color:#FAFAFA;"><p style="font-size:small;">'.$competency_items['subcriteria']['sub_criteria2'].'</p></td>
										<td colspan="5" style="width:60%; border:2px solid #000; background-color:#FAFAFA;"><p style="font-size:small;"></p></td>
									</tr>
									<tr><td colspan="8" style="background-color:#FAFAFA;">&nbsp;</td></tr>
									<tr>
										<td style="width:5%; background-color:#FAFAFA;">&nbsp;</td>
										<td style="width:5%; border:1px solid #000; text-align:center; background-color:#FAFAFA;"></td>
										<td style="width:30%; background-color:#FAFAFA;"><p style="font-size:small;">'.$competency_items['subcriteria']['sub_criteria3'].'</p></td>
										<td colspan="5" style="width:60%; border:2px solid #000; background-color:#FAFAFA;"><p style="font-size:small;"></p></td>
									</tr>
									<tr><td colspan="8" style="background-color:#FAFAFA;">&nbsp;</td></tr>
									<tr>
										<td style="width:5%; background-color:#FAFAFA;">&nbsp;</td>
										<td style="width:5%; border:1px solid #000; text-align:center; background-color:#FAFAFA;"></td>
										<td style="width:30%; background-color:#FAFAFA;"><p style="font-size:small;">'.$competency_items['subcriteria']['sub_criteria4'].'</p></td>
										<td colspan="5" style="width:60%; border:2px solid #000; background-color:#FAFAFA;"><p style="font-size:small;"></p></td>
									</tr>';
							break;

							
					}

					$data['competency_items'] .= '<tr><td colspan="8" style="background-color:#FAFAFA;">&nbsp;</td></tr>';
					

					$current_position_skills_id = $competency_items['position_skills_id'];
					$current_score_type = $competency_items['score_type'];

				break;
			}
		}


		$this->load->library('pdf');
		
		$html = $this->template->prep_message($template['body'],$data);

		// Prepare and output the PDF.
		$this->pdf->addPage();
		$this->pdf->writeHTML($html, true, false, true, false, '');
		$this->pdf->Output($employee_result->firstname.'_'.$employee_result->lastname.'_ICDP_Evaluation.pdf', 'D');
	}
	

	

	function _default_grid_buttons( $module_link = "", $related_field = "", $related_field_value = "", $addtext = "", $deltext = "", $container = "" )
	{
		// set default
		if($module_link == "") $module_link = $this->module_link;
		if($addtext == "") $addtext = "Add";
		if($deltext == "") $deltext = "Delete";
		if($container == "") $container = "jqgridcontainer";

		$buttons = "<div class='icon-label-group'>";                    
                            
        if ($this->user_access[$this->module_id]['add']) {
            $buttons .= "<div class='icon-label'>";
            $buttons .= "<a class='icon-16-add icon-16-add-listview' related_field='".$related_field."' related_field_value='".$related_field_value."' module_link='".$module_link."' href='javascript:void(0)'>";
            $buttons .= "<span>".$addtext."</span></a></div>";
        }

        
        if ($this->user_access[$this->module_id]['delete']) {
            $buttons .= "<div class='icon-label'><a class='icon-16-delete delete-array' container='".$container."' module_link='".$module_link."' href='javascript:void(0)'><span>".$deltext."</span></a></div>";                            
        }
        

        if ( get_export_options( $this->module_id ) ) {
            $buttons .= "<div class='icon-label'><a class='icon-16-export module-export' container='".$container."' module_link='".$module_link."' href='javascript:void(0)'><span>Export</span></a></div>";
            $buttons .= "<div class='icon-label'><a class='icon-16-import module-import' container='".$container."' module_link='".$module_link."' href='javascript:void(0)'><span>Import</span></a></div>";
        }        
        
        $buttons .= "</div>";
                
		return $buttons;
	}
	// END - default module functions
	
	// START custom module funtions
	

	function get_training_list(){

		if (IS_AJAX) {
			
				$employee_id = $this->input->post('employee_id');
				$employee_info = $this->system->get_employee($employee_id);

				$answer_training_list = $this->db->get_where('training_evaluation_subject_list',array('training_evaluation_id'=>$this->input->post('record_id')));

				$this->db->where('deleted',0);
				$this->db->where( '( `position_id`  LIKE "%'.$employee_info['position_id'].'%" OR  position_id  LIKE "%,'.$employee_info['position_id'].'%" OR  position_id  LIKE "%'.$employee_info['position_id'].',%" OR  position_id  LIKE "%,'.$employee_info['position_id'].',%" )' );
				$training_list = $this->db->get('training_subject');

				$response = $this->load->view($this->userinfo['rtheme'] . '/training/training_evaluation/training_list_form', array('training_list' => $training_list, 'answer_training_list' => $answer_training_list, 'type' => $this->input->post('type')));

				$data['html'] = $response;
				$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
			
		} else {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}

	}

	function get_training_competencies(){

		if (IS_AJAX) {

			$employee_id = $this->input->post('employee_id');

			$employee_info = $this->system->get_employee($employee_id);

			//Get Feedback Questionnaire Items
			$answer_details_count = $this->db->get_where('training_evaluation_competence_score',array('training_evaluation_id'=>$this->input->post('record_id')))->num_rows();

			if( $answer_details_count > 0 ){
				
				$this->db->select('training_position_skills.position_skills_id, training_position_skills.position_skills, training_position_skills.weight, training_position_skills_item.*');
				$this->db->join('training_position_skills_item','training_position_skills_item.position_skills_id = training_position_skills.position_skills_id','left');
				$this->db->where('training_position_skills.position_id',$employee_info['position_id']);
				$this->db->where('training_position_skills_item.inactive != 1');
				$this->db->where('training_position_skills.deleted',0);
				$this->db->order_by('training_position_skills.position_skills_id','ASC');
				$this->db->order_by('training_position_skills_item.skills_item_no','ASC');
				$questionnaire_details = $this->db->get('training_position_skills');

			}
			else{

				$this->db->select('training_position_skills.position_skills_id, training_position_skills.position_skills, training_position_skills.weight, training_position_skills_item.*');
				$this->db->join('training_position_skills_item','training_position_skills_item.position_skills_id = training_position_skills.position_skills_id','left');
				$this->db->where('training_position_skills.position_id',$employee_info['position_id']);
				$this->db->where('training_position_skills_item.inactive != 1');
				$this->db->where('training_position_skills.deleted',0);
				$this->db->order_by('training_position_skills.position_skills_id','ASC');
				$this->db->order_by('training_position_skills_item.skills_item_no','ASC');
				$questionnaire_details = $this->db->get('training_position_skills');

			}

			$data['competency_questionnaire_item_count'] = $questionnaire_details->num_rows();

			if( $questionnaire_details->num_rows() > 0 ){
				$data['competency_questionnaire_items'] = $questionnaire_details->result_array();

				foreach( $data['competency_questionnaire_items'] as $key => $val ){

					$competency_questionnaire_score = $this->db->get_where('training_evaluation_competence_score',array('training_evaluation_id'=>$this->input->post('record_id'), 'skills_item_id'=> $data['competency_questionnaire_items'][$key]['skills_item_id'] ));

					if( $competency_questionnaire_score->num_rows() > 0 ){

						$competency_questionnaire_score_info = $competency_questionnaire_score->row();

						$data['competency_questionnaire_items'][$key]['score'] = $competency_questionnaire_score_info->score;
						$data['competency_questionnaire_items'][$key]['remarks'] = $competency_questionnaire_score_info->remarks;

					}

					if( $data['competency_questionnaire_items'][$key]['score_type'] == 6 ){
						$competency_questionaire_multiple = $this->db->get_where('training_position_skills_multiple_subcriteria',array('skills_item_id'=> $data['competency_questionnaire_items'][$key]['skills_item_id']));
					
						foreach( $competency_questionaire_multiple->row_array() as $mkey => $mval ){
							$data['competency_questionnaire_items'][$key]['subcriteria'][$mkey] = $mval;
						}

					}

				}
			}

			$data['type'] = $this->input->post('type');

			$response = $this->load->view($this->userinfo['rtheme'] . '/training/training_evaluation/training_competencies_form', $data);


			$data['html'] = $response;
			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);

		} else {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}


	}

	function get_total_average(){

		$skills_item = $this->input->post('skills_item');
		$employee_id = $this->input->post('employee_id');
		$employee_info = $this->system->get_employee($employee_id);

		$this->db->select('training_position_skills.position_skills_id, training_position_skills.position_skills, training_position_skills.weight, training_position_skills_item.*');
		$this->db->join('training_position_skills_item','training_position_skills_item.position_skills_id = training_position_skills.position_skills_id','left');
		$this->db->where('training_position_skills.position_id',$employee_info['position_id']);
		$this->db->where('training_position_skills_item.inactive != 1');
		$this->db->where('training_position_skills.deleted',0);
		$this->db->where_in('training_position_skills_item.score_type',array(1,2,4,5));
		$this->db->order_by('training_position_skills.position_skills_id','ASC');
		$this->db->order_by('training_position_skills_item.skills_item_no','ASC');
		$questionnaire_list = $this->db->get('training_position_skills');
		$questionnaire_details_count = $questionnaire_list->num_rows();
		$questionnaire_details = $questionnaire_list->result();
		
		$sub_total = 0;
		$weight = 0;
		$current_position_skill = 0;
		$current_position_skill_count = 0;
		$total_average = 0;
		$total_score = 0;
		$weight_array = array();

		foreach( $questionnaire_details as $questionnaire_detail_info ){

			if( ( $current_position_skill != 0 ) && ( $current_position_skill != $questionnaire_detail_info->position_skills_id ) ){

				$total_average += $sub_total * 100;
				$sub_total = 0;
				$current_position_skill_count = 0;
				
			}

			if( ( $questionnaire_detail_info->item_weight >= 1 ) && ( $questionnaire_detail_info->item_weight <= 9 ) ){
				$item_weight = floatval( '0.0'.$questionnaire_detail_info->item_weight );
			}
			elseif( $questionnaire_detail_info->item_weight == 100 ){
				$item_weight = 1;
			}
			else{
				$item_weight = floatval( '0.'.$questionnaire_detail_info->item_weight );
			}

			if( ( $questionnaire_detail_info->weight >= 1 ) && ( $questionnaire_detail_info->weight <= 9 ) ){
				$weight = floatval( '0.0'.$questionnaire_detail_info->weight );
			}
			elseif( $questionnaire_detail_info->weight == 100 ){
				$weight = 1;
			}
			else{
				$weight = floatval( '0.'.$questionnaire_detail_info->weight );
			}

			$sub_total += ( ( ( $skills_item[$questionnaire_detail_info->skills_item_id] / 5) * $item_weight ) * $weight );

			$total_score += $skills_item[$questionnaire_detail_info->skills_item_id];
			$current_position_skill = $questionnaire_detail_info->position_skills_id;
			$current_position_skill_count++;

		}

		$total_average += $sub_total * 100;

		$response->total_score = $total_score;
		$response->average_score = number_format($total_average,2,'.','');
		
		$this->load->view('template/ajax', array('json' => $response));

	}

	function get_evaluation_employee_id(){

		$training_evaluation_info = $this->db->get_where('training_evaluation',array('training_evaluation_id'=>$this->input->post('record_id')))->row();
		$response->employee_id = $training_evaluation_info->employee_id;
		$this->load->view('template/ajax', array('json' => $response));

	}

	// END custom module funtions
	
}

/* End of file */
/* Location: system/application */
?>