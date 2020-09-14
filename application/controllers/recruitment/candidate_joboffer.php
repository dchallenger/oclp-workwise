<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Candidate_joboffer extends MY_Controller
{
	function __construct()
    {
        parent::__construct();

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format

		$this->listview_title = 'Job Offers';
		$this->listview_description = 'This module lists the annual tax table.';
		$this->jqgrid_title = " List";
		$this->detailview_title = ' Info';
		$this->detailview_description = 'This page shows detailed information about an annual tax entry.';
		$this->editview_title = ' Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about an annual tax entry.';
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
		$data['content'] = 'detailview';

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
			$data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/js/editview.js"></script>';
			if( !empty($this->module_wizard_form) && $this->input->post('record_id') == '-1' ){
				$data['show_wizard_control'] = true;
				$data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/js/editview-wizard-form.js"></script>';
			}
			$data['content'] = 'editview';

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
		if ($this->input->post('record_id') == '-1') {
			$_POST['job_offer_status_id'] = 1;
		}

		parent::ajax_save();

		//additional module save routine here
		$benefits = $this->input->post('benefit');
		$hours = $this->input->post('hours');
		if (is_array($benefits) && count($benefits) > 0) {
			$this->db->delete('recruitment_candidate_job_offer_benefit',array('job_offer_id' => $this->key_field_val));
			foreach( $benefits as $benefit_id => $value ){
				$data = array(
					'job_offer_id' => $this->key_field_val,
					'benefit_id' => $benefit_id,
					// 'hours_required' => ($hours[$benefit_id] != 'Hours Required') ? $hours[$benefit_id] : 0,
					'value' => str_replace(',', '', $value)				
				);
				$this->db->insert('recruitment_candidate_job_offer_benefit', $data);
			}
		}
	}

	function delete()
	{
		parent::delete();

		//additional module delete routine here
	}

	function after_ajax_save() {
		// Various operations depending on status, only do if save is successful.
		if ($this->get_msg_type() == 'success' && $this->input->post('accepted')) {
			$x = $this->input->post('accepted');
			if ( $x == 1) {
				$status = 'accept';
			} else if ( $x == 2) {
				$status = 'reject';
			} else {
				$status = '';
			}

			$this->change_status($status);
			return;
		}

		parent::after_ajax_save();		
	}
	// END - default module functions

	// START custom module funtions

	function listview()
	{
		$response->msg = "";

		$page = $this->input->post('page');
		$limit = $this->input->post('rows'); // get how many rows we want to have into the grid
		$sidx = $this->input->post('sidx'); // get index row - i.e. user click to sort
		$sord = $this->input->post('sord'); // get the direction
		$related_module = ( $this->input->post('related_module') ? true : false );

		$view_actions = (isset($_POST['view']) && $_POST['view'] == 'detail') ? false : true ;

		//set columnlist and select qry
		$this->_set_listview_query( '', $view_actions );
		$this->listview_qry .= ', ' . $this->db->dbprefix.$this->module_table . '.job_offer_status_id';

		//set Search Qry string
		if($this->input->post('_search') == "true")
			$search = $this->input->post('searchField') == "all" ? $this->_set_search_all_query() : $this->_set_specific_search_query();
		else
			$search = 1;

		if( $this->module == "user" && (!$this->is_admin && !$this->is_superadmin) ) $search .= ' AND '.$this->db->dbprefix.'user.user_id NOT IN (1,2)';

		/* count query */
		//build query
		$this->_set_left_join();
		$this->db->select($this->listview_qry, false);
		$this->db->from($this->module_table);
		$this->db->where($this->module_table.'.deleted = 0 AND '.$search);
		if(!empty( $this->filter ) ) 	$this->db->where( $this->filter );

		//get list
		$result = $this->db->get();
		//dbug($this->db->last_query());
		if( $this->db->_error_message() != "" ){
			$response->msg = $this->db->_error_message();
			$response->msg_type = "error";
		}
		else{
			$total_pages = $result->num_rows() > 0 ? ceil($result->num_rows()/$limit) : 0;
			$response->page = $page > $total_pages ? $total_pages : $page;
			$response->total = $total_pages;
			$response->records = $result->num_rows();

			/* record query */
			//build query
			$this->_set_left_join();
			$this->db->select($this->listview_qry, false);
			$this->db->from($this->module_table);

			$this->db->where($this->module_table.'.deleted = 0 AND '.$search);
			if(!empty( $this->filter ) ) 	$this->db->where( $this->filter );
			
			if($sidx != ""){
				$this->db->order_by($sidx, $sord);
			}
			else{
				if( is_array($this->default_sort_col) ){
					$sort = implode(', ', $this->default_sort_col);
					$this->db->order_by($sort);
				}
			}
			$start = $limit * $page - $limit;
			$this->db->limit($limit, $start);
			
			$result = $this->db->get();
			//dbug($this->db->last_query());

			//check what column to add if this is a related module
			if($related_module){
				foreach($this->listview_columns as $column){                                    
					if($column['name'] != "action"){
						$temp = explode('.', $column['name']);
						if(strpos($this->input->post('column'), ',')){
							$column_lists = explode( ',', $this->input->post('column'));
							if( sizeof($temp) > 1 && in_array($temp[1], $column_lists ) ) $column_to_add[] = $column['name'];
						}
						else{
							if( sizeof($temp) > 1  && $temp[1] == $this->input->post('column')) $this->related_module_add_column = $column['name'];
						}
					}
				}
				//in case specified related column not in listview columns, default to 1st column
				if( !isset($this->related_module_add_column) ){
					if(sizeof($column_to_add) > 0)
						$this->related_module_add_column = implode('~', $column_to_add );
					else
						$this->related_module_add_column = $this->listview_columns[0]['name'];
				}
			}

			if( $this->db->_error_message() != "" ){
				$response->msg = $this->db->_error_message();
				$response->msg_type = "error";
			}
			else{
				$response->rows = array();
				if($result->num_rows() > 0){
					$columns_data = $result->field_data();
					$column_type = array();
					foreach($columns_data as $column_data){
						$column_type[$column_data->name] = $column_data->type;
					}
					$this->load->model('uitype_listview');
					$ctr = 0;
					foreach ($result->result_array() as $row){
						$cell = array();
						$cell_ctr = 0;
						foreach($this->listview_columns as $column => $detail){
							if( preg_match('/\./', $detail['name'] ) ) {
								$temp = explode('.', $detail['name']);
								$detail['name'] = $temp[1];
							}
							
							if(sizeof(explode(' AS ', $detail['name'])) > 1 ){
								$as_part = explode(' AS ', $detail['name']);
								$detail['name'] = strtolower( trim( $as_part[1] ) );
							}
							else if(sizeof(explode(' as ', $detail['name'])) > 1 ){
								$as_part = explode(' as ', $detail['name']);
								$detail['name'] = strtolower( trim( $as_part[1] ) );
							}
							
							if( $detail['name'] == 'action'  ){
								if( $view_actions ){
									$cell[$cell_ctr] = ( $related_module ? $this->_default_field_module_link_actions( $row[$this->key_field], $this->input->post('container'), $this->input->post('fmlinkctr'), $row ) : $this->_default_grid_actions( $this->module_link, $this->input->post('container'), $row ) );
									$cell_ctr++;
								}
							}else{
								if( in_array( $this->listview_fields[$cell_ctr]['uitype_id'], array( 2, 5, 4, 11, 12, 17, 19, 21, 24, 27, 32, 33, 35, 36, 37, 39) ) ){
									$this->listview_fields[$cell_ctr]['value'] = $row[$detail['name']];
									$cell[$cell_ctr] = $this->uitype_listview->fieldValue( $this->listview_fields[$cell_ctr] );
								}
								else if( in_array( $this->listview_fields[$cell_ctr]['uitype_id'], array( 3 ) ) && ( isset( $this->listview_fields[$cell_ctr]['other_info']['picklist_type'] ) && $this->listview_fields[$cell_ctr]['other_info']['picklist_type'] == 'Query' ) ){
									$cell[$cell_ctr] = "";
									foreach($this->listview_fields[$cell_ctr]['other_info']['picklistvalues'] as $picklist_val)
									{
										if($row[$detail['name']] == $picklist_val['id']) $cell[$cell_ctr] = $picklist_val['value'];
									}
								}
								else{
									$cell[$cell_ctr] = (is_numeric($row[$detail['name']]) && ($column_type[$detail['name']] != "253" && $column_type[$detail['name']] != "varchar") ) ? number_format($row[$detail['name']], 2, '.', ',') : $row[$detail['name']];
								}
								$cell_ctr++;
							}
						}
						$response->rows[$ctr]['id'] = $row[$this->key_field];
						$response->rows[$ctr]['cell'] = $cell;
						$ctr++;
					}
				}
			}
		}
		
		$data['json'] = $response;                		
		
		$this->load->view($this->userinfo['rtheme'].'/template/ajax', $data);
	}

	function _default_grid_actions($module_link = "", $container = "", $row = array()) {
		// set default
		if ($module_link == "")
			$module_link = $this->module_link;
		if ($container == "")
			$container = "jqgridcontainer";

		$actions = '<span class="icon-group">';
		$actions .= '<a class="icon-button icon-16-info" module_link="' . $module_link . '" tooltip="View" href="javascript:void(0)"></a>';

        if ($this->user_access[$this->module_id]['edit']) {
            $actions .= '<a class="icon-button icon-16-edit" tooltip="Edit" href="javascript:void(0)" module_link="'.$module_link.'" ></a>';
        }

		if ($this->user_access[$this->module_id]['print'] == 1) {
			$actions .= '<a class="icon-button icon-16-document-stack" tooltip="Print" onclick="return false;" href="javascript:void(0)" module_link="' . $module_link . '" ></a>';
		}
		
		if (in_array($row['job_offer_status_id'], array(1,2)) && $this->is_recruitment()) {
			$actions .= '<a class="icon-button icon-16-approve" tooltip="Accept" href="javascript:void(0)" module_link="' . $module_link . '" candidate_id="' . $row['candidate_id'] . '"></a>';
			$actions .= '<a class="icon-button icon-16-disapprove" tooltip="Reject" href="javascript:void(0)" module_link="' . $module_link . '" candidate_id="' . $row['candidate_id'] . '"></a>';
		}		

		if ($this->user_access[$this->module_id]['delete'] == 1 && $row['job_offer_status_id'] != 3) {
			$actions .= '<a class="icon-button icon-16-delete delete-single" tooltip="Delete" container="' . $container . '" module_link="' . $module_link . '" href="javascript:void(0)"></a>
			</span>';
		}

		return $actions;
	}

	function print_record($record_id = 0) {
		// Get from $_POST when the URI is not present.
		if ($record_id == 0) {
			$record_id = $this->input->post('record_id');
		}
		
		$tpl_file = 'JOB_OFFER'; //default template
		
		$this->load->library('pdf');
		$this->load->model(array('uitype_detail', 'template'));
		
		if( $this->uri->rsegment(4) )
			$template = $this->template->get_template( $this->uri->rsegment(4) );	
		else
			$template = $this->template->get_module_template($this->module_id, $tpl_file );
		
		// Get applicant details. (This returns the fieldgroup array.
		$check_record = $this->_record_exist($record_id);
		if ($check_record->exist) {
			$vars = get_record_detail_array($record_id);
			$total = $vars['basic'];
			$tax = $total * .02;

			$vars['basic'] = number_format( $vars['basic'], 2, '.', ',' );
			$vars['tax'] = number_format( $vars['tax'], 2, '.', ',' );
			$vars['date'] = date( $this->config->item('display_date_format') );
			$vars['time'] = date( $this->config->item('display_time_format') );
			$jo = $this->db->get_where($this->module_table, array($this->key_field => $record_id))->row();
			$candidate = $this->db->get_where('recruitment_manpower_candidate', array('candidate_id' => $jo->candidate_id ))->row();
			$mrf = $this->db->get_where('recruitment_manpower', array('request_id' => $candidate->mrf_id ))->row();
			$campaign = $this->db->get_where('campaign', array('campaign_id' => $mrf->campaign_id ))->row();
			$vars['campaign'] = $campaign->campaign;
			$position = $this->db->get_where('user_position', array('position_id' => $mrf->position_id))->row();
			$company = $this->db->get_where('user_company', array('company_id' => $position->company_id))->row();

			$meta = $this->config->item('meta');

			$vars['company'] = $meta['description'];
			$vars['position'] = $position->position;
			$vars['date_start'] = $vars['date_from'] = date( $this->config->item('display_date_format'), strtotime($jo->date_from) );
			$vars['date_end'] = $vars['date_to'] = date( $this->config->item('display_date_format'), strtotime($jo->date_to) );
			$facilitator = $this->hdicore->_get_userinfo( $this->user->user_id );
			$vars['facilitated_by'] = $facilitator->firstname.' '.$facilitator->lastname;
			$vars['benefits'] = "";
			$benefits = $this->db->get_where('recruitment_candidate_job_offer_benefit', array('job_offer_id' => $jo->job_offer_id));
			if( $benefits->num_rows() > 0 ){
				foreach( $benefits->result() as $benefit ){
					$benefit_detail = $this->db->get_where('benefit', array( 'benefit_id' => $benefit->benefit_id ))->row();
					$total += $benefit->value; 
					$vars['benefits'] .= '<tr>
							<td style="text-align: right; width: 60%; ">'. $benefit_detail->benefit .': &nbsp;</td>
							<td align="right" style="border-bottom: 1px solid black" width="40%">'. number_format( $benefit->value, 2, '.', ',') .'</td>
						</tr>';
				}
			}
			$vars['total'] = number_format( $total, 2, '.', ',' );
			$vars['fancy_date'] = date('jS \d\a\y \o\f F Y');

			$html = $this->template->prep_message($template['body'], $vars, false, true);

			// Prepare and output the PDF.
			$this->pdf->setPrintHeader(TRUE);
			$this->pdf->addPage();
			$this->pdf->writeHTML($html, true, false, true, false, '');
			$this->pdf->Output(date('Y-m-d').' Job Offer.pdf', 'D');
		} else {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
	}
	
	function print_contract($record_id = 0) {
		
		// Get from $_POST when the URI is not present.
		if ( $record_id == 0 ) $record_id = $this->input->post('record_id');
		
		// if (!$this->input->post('record_id')) $candidate_record_id = $this->uri->rsegment(5);
		$candidate_record_id = $this->uri->rsegment(3);

		//default template
		$tpl_file = 'PROBI_CONTRACT_NO_VOICE';
		
		$this->load->library('pdf');
		$this->load->model(array('uitype_detail', 'template'));
		
		if( $this->uri->rsegment(4) )
			$template = $this->template->get_template( $this->uri->rsegment(4) );	
		else
			$template = $this->template->get_module_template($this->module_id, $tpl_file );
		
		// Get applicant details. (This returns the fieldgroup array.
		$check_record = $this->_record_exist($candidate_record_id);

		$this->load->library('parser');

		if ($record_id) {
			$this->db->select('recruitment_candidate_job_offer_type,basic,recruitment_candidate_job_offer.date_from as date_start, recruitment_candidate_job_offer.date_to as date_end,  recruitment_manpower_candidate.*, recruitment_candidate_job_offer.*, recruitment_manpower.*');
			$this->db->join('recruitment_manpower_candidate','recruitment_candidate_job_offer.candidate_id = recruitment_manpower_candidate.candidate_id');						
			$this->db->join('recruitment_manpower','recruitment_manpower_candidate.mrf_id = recruitment_manpower.request_id');			
			$this->db->join('recruitment_candidate_job_offer_type','recruitment_candidate_job_offer_type.recruitment_candidate_job_offer_type_id = recruitment_candidate_job_offer.recruitment_candidate_job_offer_type_id');						
			$jo = $this->db->get_where('recruitment_candidate_job_offer', array('job_offer_id' => $record_id))->row();

			$candidate = $this->db->get_where('recruitment_manpower_candidate', array('candidate_id' => $jo->candidate_id ))->row();

			$vars = get_record_detail_array($jo->candidate_id );

			$total = $jo->basic;
			
			$tax = $total * .02;

			$number_word = number_to_words($vars['basic']);
			$vars['number_word'] = ucwords($number_word);
			$vars['tax'] = number_format( $tax, 2, '.', ',' );
			$vars['basic'] = number_format( $jo->basic, 2, '.', ',' );
			$vars['pay_per_annum'] = $jo->pay_per_annum . ' months';
			$vars['increase'] = $jo->increase;
			$vars['date'] = date( $this->config->item('display_date_format') );
			$vars['fancy_date'] = date('jS \d\a\y \o\f F Y');
			$vars['time'] = date( $this->config->item('display_time_format') );
			$vars['id_number'] = '';
			$vars['location'] = '';
			$vars['employment_status'] 	= $jo->recruitment_candidate_job_offer_type;
			$vars['job_grade'] = '';
			$vars['basic_rate'] = number_format($jo->basic,2,'.',',');
			$vars['gasoline'] = '';
			$vars['tax_code'] = '';
			
			$this->db->join('civil_status','civil_status.civil_status_id = recruitment_applicant.civil_status_id','left');
			$this->db->join('cities','recruitment_applicant.pres_city = cities.city_id','left');
			$this->db->join('province','province.province_id = cities.province_id','left');
			$applicant = $this->db->get_where('recruitment_applicant', array('applicant_id' => $candidate->applicant_id ))->row();

			$applicant_education = $this->db->get_where('recruitment_applicant_education', array('applicant_id' => $candidate->applicant_id ))->row();

			$salutation = ($applicant->sex == 'female' ? 'Ms.' : 'Mr.');

			$vars['salutation']	= $salutation;	
			$vars['firstname'] 	= $applicant->firstname;
			$vars['lastname'] 	= $applicant->lastname;
			$vars['middle_initial'] 	= substr($applicant->middlename, 0, 1);
			$vars['telephone'] 			= $applicant->home_phone;
			$vars['gender'] 			= ucfirst($applicant->sex);
			$vars['civil_status'] 		= $applicant->civil_status;	
			$vars['citizenship'] 		= $applicant->citizenship;
			$vars['birthdate'] 			= date(' F d, Y', strtotime($applicant->birth_date) );
			$vars['candidate_id'] = strtoupper($jo->applicant_name);
			
			if ($this->module_id == $template['module_id']) {
				$vars['lastname'] = $applicant->lastname. ',';
			}
			

			if( !empty($applicant_education->school) ){ 
				$vars['school_name'] = $applicant_education->school;
			}
			else{
				$vars['school_name'] = "&nbsp;";
			}

			if( !empty($applicant_education->course) ){ 
				$vars['course'] = $applicant_education->course;
			}else{
				$vars['course'] = "&nbsp;";
			}

			$vars['address'] = $applicant->pres_address1;
			if( !empty($applicant->pres_address2) ) $vars['address'] .= '<br/>'. $applicant->pres_address2;
			if( !empty($applicant->pres_city) ) $vars['address'] .= '<br/>'. $applicant->city;
			if( !empty($applicant->province) ) $vars['address'] .= ', '. $applicant->province;
			if( !empty($applicant->zipcode) ) $vars['address'] .= ' '. $applicant->zipcode;
			if( empty($vars['address']) ) $vars['address'] .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';

			

			$vars['sss'] = $applicant->sss;
			$vars['tin'] = $applicant->tin;
			$vars['philhealth'] = $applicant->philhealth;
			$vars['pagibig'] = $applicant->pagibig;

			$mrf = $this->db->get_where('recruitment_manpower', array('request_id' => $candidate->mrf_id ))->row();
			
			$rank = $this->db->get_where('user_rank', array('job_rank_id' => $mrf->job_rank_id))->row();
			
			$logo_2 = get_branding();
			$company_id = $mrf->company_id;
			$company_qry = $this->db->get_where('user_company', array('company_id' => $company_id))->row();
			if(!empty($company_qry->logo)) {
			  $logo_2 = '<img alt="" src="./'.$company_qry->logo.'">';
			  // $logo_2 = '<img alt="" src="'.base_url().''.$company_qry->logo.'">';
			}
			$vars['logo_2'] = '<table style="width:100%;margin-left:-200px">'.$logo_2.'</table>';

			$vars['div_dept'] = '';
			$vars['rank'] = $rank->job_rank;
			$applicant_name = '';
		
			$department = $this->db->get_where('user_company_department', array('department_id' => $mrf->department_id))->row();
			$division = $this->db->get_where('user_company_division', array('division_id' => $mrf->division_id))->row();
			$rank = $this->db->get_where('user_rank', array('job_rank_id' => $mrf->job_rank_id))->row();
			
			$vars['level_rank'] = $rank->job_rank;
			$vars['department'] = $department->department;
			$vars['division'] = $division->division;

			$endorsed_by = $this->hdicore->_get_userinfo( $division->division_manager_id );

			$vars['endorsed_by'] = $endorsed_by->firstname.' '. $endorsed_by->middleinitial.' '.$endorsed_by->lastname;
			$vars['position_endorsed_by'] = $endorsed_by->position;
			$vars['approved_by'] = '';
			$vars['position_approved_by'] = 'President and CEO';

			$this->db->join('user', 'user.position_id=user_position.position_id', 'left');
			$hr_pos = $this->db->get_where('user_position', array('position_code' => 'hr_head', 'user_id >' => '3', 'user.deleted' => 0));
			
			$vars['hr_head_position'] = ($hr_pos && $hr_pos->num_rows() > 0) ? $hr_pos->row()->position : '';
			$vars['hr_head_name'] = ($hr_pos && $hr_pos->num_rows() > 0) ? $hr_pos->row()->salutation.' '.$hr_pos->row()->firstname.' '.$hr_pos->row()->lastname : '';

			if ($jo->is_internal == 0){
				$this->db->join('cities','recruitment_applicant.pres_city = cities.city_id','left');
				$this->db->join('province','province.province_id = cities.province_id','left');
				$applicant_info = $this->db->get_where('recruitment_applicant',array('applicant_id' => $jo->applicant_id));
				if ($applicant_info && $applicant_info->num_rows() > 0){
					$applicant_info_row = $applicant_info->row();
					if ($applicant_info_row->middlename != ''){
						$applicant_fullname = $applicant_info_row->firstname.' '. substr($applicant_info_row->middlename,0,1) . '. '. $applicant_info_row->lastname. ' '. $applicant_info_row->aux;
					}
					else{
						$applicant_fullname = $applicant_info_row->firstname.' '. $applicant_info_row->lastname. ' '. $applicant_info_row->aux;
					}
					$applicant_name = $applicant_fullname;
					$array_address[] = $applicant_info_row->pres_address1;
					$array_address[] = $applicant_info_row->pres_address2;
					$array_address[] = $applicant_info_row->city;
					$array_address[] = $applicant_info_row->province;
					$address = implode(',', array_filter($array_address));
					$salutation = ($applicant_info_row->sex == 'female' ? 'Ms' : 'Mr');
					$surname = $salutation. '. ' .$applicant_info_row->lastname;
					$sss = $applicant_info_row->sss;
					$tin = $applicant_info_row->tin;
				}
			}
			else{
				$this->db->join('employee','user.employee_id = employee.employee_id','left');
				$this->db->join('cities','employee.pres_city = cities.city_id','left');
				$this->db->join('province','province.province_id = cities.province_id','left');
				$user_info = $this->db->get_where('user',array('employee_id' => $jo->employee_id));
				if ($user_info && $user_info->num_rows() > 0){
					$user_info_row = $user_info->row();
					$applicant_name = $applicant_info_row->firstname.' '. $applicant_info_row->middleinitial. ' '. $applicant_info_row->lastname. ' '. $applicant_info_row->aux;
					$array_address[] = $user_info_row->pres_address1;
					$array_address[] = $user_info_row->pres_address2;
					$array_address[] = $user_info_row->city;
					$array_address[] = $applicant_info_row->province;
					$address = implode(',', array_filter($array_address));
					$salutation = ($user_info_row->sex == 'female' ? 'Ms' : 'Mr');
					$surname = $salutation. '. ' .$user_info_row->lastname;
					$sss = $user_info_row->sss;
					$tin = $user_info_row->tin;										
				}				
			}

			$campaign = $this->db->get_where('campaign', array('campaign_id' => $mrf->campaign_id ))->row();
			$vars['campaign'] = $campaign->campaign;
			$position = $this->db->get_where('user_position', array('position_id' => $mrf->position_id))->row();
			$company = $this->db->get_where('user_company', array('company_id' => $position->company_id))->row();
			$department = $this->db->get_where('user_company_department', array('department_id' => $mrf->department_id))->row();
			

			$this->db->join('user', 'user.user_id=user_company_division.division_manager_id');
			$this->db->join('user_position', 'user.position_id=user_position.position_id');
			$div_head = $this->db->get_where('user_company_division', array('user_company_division.division_id' =>  $mrf->division_id))->row();
			$vars['division_head'] = strtoupper($div_head->salutation.' '.$div_head->firstname.' '.$div_head->middleinitial.' '.$div_head->lastname);
			$vars['division_position'] = $div_head->position;

			$meta = $this->config->item('meta');

			$vars['surname'] = $surname;
			$vars['salutation'] = $salutation.'.';
			$vars['applicant_name'] = strtoupper($applicant_name);
			$vars['company'] = $meta['description'];
			$vars['department'] = $department->department;

			$this->db->join('user_position', 'user_position.position_id=user.position_id');
			$dept_head = $this->db->get_where('user',array("user_id"=>$department->dm_user_id))->row();

			$vars['dept_head'] = $dept_head->firstname .' '.$dept_head->lastname;
			$vars['dept_head_pos'] = $dept_head->position;
			$vars['dept_head_name'] = $dept_head->salutation.'&nbsp;'.$dept_head->firstname.'&nbsp;'.$dept_head->middleinitial.'&nbsp;'.$dept_head->lastname;

			$vars['position'] = $position->position;
			$vars['date_start'] = $vars['date_from'] = date( $this->config->item('display_date_format'), strtotime($jo->date_start) );
			$vars['date_end'] = $vars['date_to'] = date( $this->config->item('display_date_format'), strtotime($jo->date_end) );
			$facilitator = $this->hdicore->_get_userinfo( $this->user->user_id );
			$vars['facilitated_by'] = $facilitator->firstname.' '.$facilitator->lastname;


			$package = $this->db->get_where('recruitment_benefit_packages', array('deleted'=> 0, "recruitment_benefit_package_id" => $jo->benefit_package_id))->row();
			$vars['company_initiated'] = $package->description;

			$vars['government_mandated'] = '<table width="100%"><tbody><tr>
												<td >&nbsp;</td>
												<td>&nbsp;</td>
											</tr>';
			$vars['duties']   = $position->duties_responsibilities;
			$vars['company_initiated_total'] = 0;
			$vars['government_mandated_total'] = 0;
			$vars['total_plus_premium'] = 0;
			$vars['monthly_total'] = '';
			$vars['yearly_total'] = '';
			$vars['increase_upon'] = ($vars['increase'] != "") ? "Increase Upon Regularization" : "";
			if($jo->date_from == "0000-00-00") $vars['date_from'] = '&nbsp;&nbsp;&nbsp;';
			if($jo->date_to == "0000-00-00") $vars['date_to'] = '&nbsp;&nbsp;&nbsp;';

			$benefits = $this->db->get_where('recruitment_candidate_job_offer_benefit', array('job_offer_id' => $jo->job_offer_id));

			$vars['government_mandated'] .= '</table></tbody>';
			$vars['monthly_total'] = number_format( $vars['company_initiated_total'] + $vars['government_mandated_total'] + $jo->basic, 2, '.', ',');
			$monthly = number_format($vars['company_initiated_total'] + $vars['government_mandated_total'] + $jo->basic,2,'.','');
			$vars['yearly_total'] = number_format( ($monthly * 12), 2, '.', ',');
			$html = $this->template->prep_message($template['body'], $vars, false, true);

			// Prepare and output the PDF.			
			$this->pdf->SetMargins(10, 15);
			$this->pdf->addPage('P', 'LETTER', true);
			$this->pdf->SetFontSize(11);						
			$this->pdf->setFont('Calibri');						
			$this->pdf->writeHTML($html, true, false, true, false, '');
			$this->pdf->Output(date('Y-m-d').' '.$template['subject'] . ' - '.$vars['candidate_id'].'.pdf', 'D');
		} else {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
	}

	function oams_print_contract($record_id = 0) {

		// Get from $_POST when the URI is not present.
		if ( $record_id == 0 ) $record_id = $this->input->post('record_id');
		
		//default template
		$tpl_file = 'PROBI_CONTRACT_NO_VOICE';
		
		$this->load->library('pdf');
		$this->load->model(array('uitype_detail', 'template'));
		
		if( $this->uri->rsegment(4) )
			$template = $this->template->get_template( $this->uri->rsegment(4) );	
		else
			$template = $this->template->get_module_template($this->module_id, $tpl_file );
		

		// Get applicant details. (This returns the fieldgroup array.
		$check_record = $this->_record_exist($record_id);
		$this->load->library('parser');

		if ($check_record->exist) {
			$this->db->join('recruitment_candidate_status','recruitment_candidate_job_offer.job_offer_status_id = recruitment_candidate_status.candidate_status_id','LEFT');
			$this->db->join('user_position','recruitment_candidate_job_offer.position_id = user_position.position_id','LEFT');			
			$this->db->join('recruitment_manpower_candidate','recruitment_candidate_job_offer.candidate_id = recruitment_manpower_candidate.candidate_id');						
			$result = $this->db->get_where('recruitment_candidate_job_offer',array("job_offer_id"=>$record_id));
			$jo = $result->row();

		    $vars['candidate_id'] = $jo->applicant_name;
		    $vars['date'] = date('d-M-Y',strtotime($jo->date_from)) .' to '. date('d-M-Y',strtotime($jo->date_to));
		    $vars['position_id'] = $jo->position;
		    $vars['remarks'] = $jo->remarks;
		    $vars['job_offer_status_id'] = $jo->candidate_status;
		    $vars['Basic_Pay_Numbers'] = number_format($jo->basic,2,'.',',');
		    $vars['Basic_Pay_Words'] = number_to_words($jo->basic);

			$total = $vars['basic'];
			$tax = $total * .02;
			
			$vars['tax'] = number_format( $tax, 2, '.', ',' );
			$vars['basic'] = number_format( $jo->basic, 2, '.', ',' );
			$vars['date'] = date( $this->config->item('display_date_format') );
			$vars['fancy_date'] = date('jS \d\a\y \o\f F Y');
			$vars['time'] = date( $this->config->item('display_time_format') );

			$candidate = $this->db->get_where('recruitment_manpower_candidate', array('candidate_id' => $jo->candidate_id ))->row();
			$this->db->select('*, c.city as permanent, pr.city as present');
			$this->db->join('cities c', 'perm_city = c.city_id');
			$this->db->join('cities pr', 'pres_city = pr.city_id');
			$applicant = $this->db->get_where('recruitment_applicant', array('applicant_id' => $candidate->applicant_id ))->row();
			$applicant_education = $this->db->get_where('recruitment_applicant_education', array('applicant_id' => $candidate->applicant_id ))->row();


			if( !empty($applicant_education->school) ){ 
				$vars['school_name'] = $applicant_education->school;
			}
			else{

				$vars['school_name'] = "&nbsp;";

			}

			if( !empty($applicant_education->course) ){ 
				$vars['course'] = $applicant_education->course;
			}else{

				$vars['course'] = "&nbsp;";

			}

			$vars['address'] = $applicant->pres_address1;
			if( !empty($applicant->pres_address2) ) $vars['address'] .= '<br/>'. $applicant->pres_address2;
			if( !empty($applicant->pres_city) ) $vars['address'] .= '<br/>'. $applicant->present;
			if( !empty($applicant->province) ) $vars['address'] .= ', '. $applicant->province;
			if( !empty($applicant->zipcode) ) $vars['address'] .= ' '. $applicant->zipcode;
			if( empty($vars['address']) ) $vars['address'] .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';

			$vars['address_inline'] = $applicant->pres_address1;
			if( !empty($applicant->pres_address2) ) $vars['address_inline'] .= ','. $applicant->pres_address2;
			if( !empty($applicant->pres_city) ) $vars['address_inline'] .= ','. $applicant->present;
			if( !empty($applicant->province) ) $vars['address_inline'] .= ', '. $applicant->province;
			if( !empty($applicant->zipcode) ) $vars['address_inline'] .= ' '. $applicant->zipcode;
			if( empty($vars['address_inline']) ) $vars['address_inline'] .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';

			$vars['sss'] = $applicant->sss;
			$vars['tin'] = $applicant->tin;
			$vars['philhealth'] = $applicant->philhealth;
			$mrf = $this->db->get_where('recruitment_manpower', array('request_id' => $candidate->mrf_id ))->row();
			$campaign = $this->db->get_where('campaign', array('campaign_id' => $mrf->campaign_id ))->row();
			$vars['campaign'] = $campaign->campaign;
			$position = $this->db->get_where('user_position', array('position_id' => $mrf->position_id))->row();
			$company = $this->db->get_where('user_company', array('company_id' => $position->company_id))->row();
			$department = $this->db->get_where('user_company_department', array('department_id' => $mrf->department_id))->row();
			$meta = $this->config->item('meta');

			$vars['department'] = $department->department;
			$vars['company'] = $meta['description'];
			$vars['position'] = $position->position;
			$vars['date_start'] = $vars['date_from'] = date( $this->config->item('display_date_format'), strtotime($jo->date_from) );
			$vars['date_end'] = $vars['date_to'] = date( $this->config->item('display_date_format'), strtotime($jo->date_to) );
			$facilitator = $this->hdicore->_get_userinfo( $this->user->user_id );
			$vars['facilitated_by'] = $facilitator->firstname.' '.$facilitator->lastname;
			$vars['allowances'] = "";
			$vars['premiums'] = "";
			$vars['duties']   = $position->duties_responsibilities;
			$vars['allowance_total'] = 0;
			$vars['premium_total'] = 0;
			$vars['total_plus_premium'] = 0;

			$info_reporting_to = $this->db->get_where('user',array("user_id"=>$position->reporting_to));

			if ($info_reporting_to && $info_reporting_to->num_rows() > 0){
				$reporting_to = $info_reporting_to->row();
				$vars['Immediate_Superior'] = $reporting_to->firstname .' '. $reporting_to->lastname;
			}
			else{
				$vars['Immediate_Superior'] = '';
			}

			if($jo->date_from == "0000-00-00") $vars['date_from'] = '&nbsp;&nbsp;&nbsp;';
			if($jo->date_to == "0000-00-00") $vars['date_to'] = '&nbsp;&nbsp;&nbsp;';

			$benefits = $this->db->get_where('recruitment_candidate_job_offer_benefit', array('job_offer_id' => $jo->job_offer_id));
			if( $benefits->num_rows() > 0 ){


					foreach( $benefits->result() as $benefit ){
						$benefit_detail = $this->db->get_where('benefit', array( 'benefit_id' => $benefit->benefit_id ))->row();
						$vars['total_benefits'] += $benefit->value; 
						$vars['benefits'] .= '<tr>
								<td >'. $benefit_detail->benefit .': &nbsp;</td>
								<td align="right" style="border-bottom: 1px solid black" >'. number_format( $benefit->value, 2, '.', ',') .'</td>
							</tr>';
					}

			

				$asterisk = '*';

				foreach( $benefits->result() as $benefit ){
					$benefit_detail = $this->db->get_where('benefit', array( 'benefit_id' => $benefit->benefit_id ))->row();
					switch($benefit_detail->benefit_type_id){
						case 1:
							$vars['allowance_total'] += $benefit->value;
							$vars['allowances'] .= '<tr>
									<td style="width: 50%;"><em>'.$benefit_detail->benefit.'</em></td>
									<td style="width: 50%;">'. number_format( $benefit->value, 2, '.', ',') .'</td>
								</tr>';
						break;
						case 2:
							$vars['premium_total'] += $benefit->value;
							$vars['premiums'] .= '<tr>
									<td style="width: 50%;"><em>'.$benefit_detail->benefit. $asterisk . '</em></td>
									<td style="width: 50%;">'. number_format( $benefit->value, 2, '.', ',') .'</td>
								</tr>';

							$conditions[] = $asterisk . ' ' . $this->parser->parse_string($benefit_detail->description, $benefit);
							$asterisk .= '*';
						break;
					}
				}
			}

			if ($template['code'] == "JOB_OFFER_OAM") {

				$total = $jo->basic + $vars['total_benefits'];
			}else{
				$total += $vars['allowance_total'];	
			}
			

			$vars['total'] = number_format( $total, 2, '.', ',' );
			$vars['allowance_total'] = number_format($vars['allowance_total'], 2, '.', ',');
			
			$vars['total_plus_premium'] = number_format( ($total + $vars['premium_total']), 2, '.', ',' );
			$vars['premium_total'] = number_format( $vars['premium_total'], 2, '.', ',' );	
						

				if(!empty( $vars['premium_total'] )){
					$vars['premiums'] = $vars['premiums'] . '<tr>
									<td style="width: 50%;"><strong>Total</strong></td>
									<td style="width: 50%;" align="right"><strong>'.$vars['total_plus_premium'].'</strong></td>
								</tr>';
				}


				

			$vars['conditions'] = '';
			if ($vars['premium_total'] > 0) {
				$vars['conditions'] = '<strong>Conditions</strong>:<br/><br/>';
			
				foreach ($conditions as $condition) {
					$vars['conditions'] .= '<br/>' . $this->parser->parse_string($condition, $vars);
				}
			}

			$html = $this->template->prep_message($template['body'], $vars, false, true);

			// Prepare and output the PDF.			
			$this->pdf->setPrintHeader(TRUE);
			$this->pdf->SetAutoPageBreak(true, 25.4);
			$this->pdf->SetMargins( 19.05, 38.1 );
			$this->pdf->addPage('P', 'LETTER', true);					
			$this->pdf->writeHTML($html, true, false, true, false, '');
			$this->pdf->Output(date('Y-m-d').' '.$template['subject'] . ' - '.$vars['candidate_id'].'.pdf', 'D');
		} else {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
	}


	function change_status($status = null) {

		if (!IS_AJAX) {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		} else {
			if (is_null($status)) {
				$status = $this->input->post('status');
			}

			$this->db->join('recruitment_manpower_candidate', 'recruitment_manpower_candidate.candidate_id = recruitment_candidate_job_offer.candidate_id');
			$this->db->join('recruitment_applicant', 'recruitment_applicant.applicant_id = recruitment_manpower_candidate.applicant_id');
			$this->db->where('job_offer_id', $this->input->post('record_id'));
			$this->db->where('recruitment_candidate_job_offer.deleted', 0);

			$record = $this->db->get('recruitment_candidate_job_offer')->row();

			$response->msg_type = 'success';
			switch ($status) {
				case 'accept':
					
					if ($record->job_offer_status_id == '1' || $record->job_offer_status_id == '0') {
						$status = '2';
						$candidate_status_id = 12;
						$this->db->update('recruitment_manpower_candidate',
							array('candidate_status_id' => 12),
							array('candidate_id' => $record->candidate_id)
							);
					} elseif ($record->job_offer_status_id == '2') {
						$status = '3';
						$candidate_status_id = 13;
						$this->db->update('recruitment_manpower_candidate',
							array('candidate_status_id' => 13),
							array('candidate_id' => $record->candidate_id)
							);						
						$this->db->insert('recruitment_preemployment', array('candidate_id' => $record->candidate_id));
					}
					
					$response->msg = 'Job offer accepted.';
					break;
				case 'reject':	
					$status = '4';	
					if ($record->job_offer_status_id == '1') {					
						$candidate_status_id = 15;	
					} elseif ($record->job_offer_status_id == '2') {						
						$candidate_status_id = 16;
					}
					$this->db->update('recruitment_manpower_candidate',
						array('candidate_status_id' => $candidate_status_id),
						array('candidate_id' => $record->candidate_id)
						);

					$this->db->update('recruitment_applicant',
						array('application_status_id' => 5),
						array('applicant_id' => $record->applicant_id)
						);

					$candidate_info = $this->db->get_where('recruitment_manpower_candidate',array('candidate_id' => $record->candidate_id))->row();

					$recent_position_applied = $this->db->get_where('recruitment_applicant_application', array( 'applicant_id'=>$record->applicant_id, 'lstatus' => 0 ))->row();

					$this->db->update('recruitment_applicant_application', array('lstatus' => 1 ), array('applicant_id' => $record->applicant_id));

					$data = array(
			            'applicant_id' => $record->applicant_id,
			            'position_applied' => $recent_position_applied->position_applied,
			            'applied_date' => date('Y-m-d H:i:s'),
			            'status' => 5,
			            'mrf_id' => 0
			        );

			        //save aaplication
					$this->db->insert('recruitment_applicant_application',$data);

					$this->db->insert('recruitment_applicant_history',
						array(
							'applicant_id' 			=> $record->applicant_id,
							'application_status_id' => 5,
							'candidate_status_id'   => $candidate_status_id,
							'remark'				=> $this->input->post('remark')
							)
						);

					$response->msg = 'Job offer rejected.';
					break;
				default:					
					$response->msg = 'Job offer prepared.';
			}

			//change status of applicant 
			$this->system->update_application_status( $record->applicant_id, $candidate_status_id);

			$this->db->where($this->key_field, $this->input->post('record_id'));
			$this->db->update($this->module_table, array('job_offer_status_id' => $status));

			$this->load->view('template/ajax', array('json' => $response));
		}
	}
	
	function add_benefit_field(){
		$response->field = $this->load->view( $this->userinfo['rtheme'].'/'.$this->module_link.'/benefit_field', array('benefit_id' => $this->input->post('benefit_id')), true );
		$response->selected_benefits = $this->input->post('selected_benefits');
		if( ! empty( $response->selected_benefits ) )
			$response->selected_benefits .= ',' . $this->input->post('benefit_id');
		else
			$response->selected_benefits = $this->input->post('benefit_id');
		$this->db->where('deleted', 0);
		$this->db->where('benefit_id not in ('. $response->selected_benefits .')');
		$benefits = $this->db->get('benefit');
   	$response->benefitddlb = '<option value="">Select...</option>';
	  foreach($benefits->result() as $benefit):
      $response->benefitddlb .= '<option value="'.$benefit->benefit_id.'">'.$benefit->benefit.'</option>';
    endforeach;
		
		$this->load->view('template/ajax', array('json' => $response));
	}
	
	function get_benefit_field(){
		$response->field = '';
		$response->benefitddlb = '';
		// $response->benefitddlb = '<option value="">Select...</option>';
		$response->selected_benefits = array();
		/*$benefits = $this->db->get_where('recruitment_candidate_job_offer_benefit', array('job_offer_id' => $this->input->post('job_offer_id')));
		if( $benefits->num_rows() > 0 ){
			foreach( $benefits->result() as $benefit ){				
				$response->field .= $this->load->view( $this->userinfo['rtheme'].'/'.$this->module_link.'/benefit_field', 
						array('benefit_id' => $benefit->benefit_id, 'value' => $benefit->value, 'hours' => $benefit->hours_required,'units' => $benefit->units), 
						true );
				$response->selected_benefits[] = $benefit->benefit_id;
			}
		}
		$response->selected_benefits = implode(',', $response->selected_benefits);
		$this->db->where('deleted', 0);
		if( !empty($response->selected_benefits) ) $this->db->where('benefit_id not in ('. $response->selected_benefits .')');
		$benefits = $this->db->get('benefit');
	  	if($benefits->num_rows() > 0){
		  foreach($benefits->result() as $benefit):
	      $response->benefitddlb .= '<option value="'.$benefit->benefit_id.'">'.$benefit->benefit.'</option>';
	    endforeach;
		}*/
		$this->load->view('template/ajax', array('json' => $response));
	}
	
	function get_template_form(){
		$jo = $this->db->get_where($this->module_table, array($this->key_field => $this->input->post('record_id')));
		$response->form = $this->load->view( $this->userinfo['rtheme'].'/'.$this->module_link.'/template_form',array('jo' => $jo->row(), 'candidate_status' => $this->input->post('candidate_status')), true );
		$this->load->view('template/ajax', array('json' => $response));
	}
	// END custom module funtions

}

/* End of file */
/* Location: system/application */