<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Applicants extends MY_Controller {

	private $_detail_types;
	public $row_num = 100;

	function __construct() {
		parent::__construct();

		$this->load->helper(array('form', 'recruitment'));

		$this->_detail_types = array('education', 'employment', 'family', 'references', 'referral', 'training', 'affiliates', 'skill','test_profile');

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format

		$this->listview_title         = 'Applicants';
		$this->listview_description   = 'Lists all applicants';
		$this->jqgrid_title           = "Applicants List";
		$this->detailview_title       = 'Applicant Info';
		$this->detailview_description = 'This page shows detailed information about a particular applicant';
		$this->editview_title         = 'Applicant Add/Edit';
		$this->editview_description   = 'This page allows saving/editing information about an applicant';

		if (method_exists($this, 'print_record')) {
			$data['show_print'] = true;
		} else {
			$data['show_print'] = false;
		}

		$data['module_filters'] 	 = get_applicant_filters();
		$data['module_filter_title'] = 'Applicants';
		$this->load->vars($data);		

	}

	// START - default module functions
	// default jqgrid controller method
	function index() {
		$data['scripts'][] = jqgrid_listview(); // load jqgrid js and default grid js
		$data['content']   = 'recruitment/applicants/listview';
		$data['scripts'][] = chosen_script();

		if ($this->session->flashdata('flashdata')) {
			$info['flashdata'] = $this->session->flashdata('flashdata');
			$data['flashdata'] = $this->load->view($this->userinfo['rtheme'] . '/template/flashdata', $info, true);
		}

		//set default columnlist
		$this->_set_listview_query();

		//set grid buttons
		$data['jqg_buttons'] = $this->_default_grid_buttons();

		//set load jqgrid loadComplete callback
		$data['jqgrid_loadComplete'] = "";

		$data['additional_search_options'] = array("skill_type"=>"Skill Type","skill_name"=>"Skill Name","proficiency"=>"Proficiency","course"=>"Course");				

		//load variables to env
		$this->load->vars($data);

		//load the final view
		//load header

		$this->load->view($this->userinfo['rtheme'] . '/template/header');
		$this->load->view($this->userinfo['rtheme'] . '/template/header-nav');

		//load page content
		$this->load->view($this->userinfo['rtheme'] . '/template/page-content');

		//load footer
		$this->load->view($this->userinfo['rtheme'] . '/template/footer');
	}

	function filter($type = null) {
		if ($type == null) {
			redirect('recruitment/applicants');
		}

		$data['scripts'][] = jqgrid_listview(); // load jqgrid js and default grid js	
		$data['scripts'][] = chosen_script();	
		$data['content'] = 'recruitment/applicants/listview';			

		if ($this->session->flashdata('flashdata')) {
			$info['flashdata'] = $this->session->flashdata('flashdata');
			$data['flashdata'] = $this->load->view($this->userinfo['rtheme'] . '/template/flashdata', $info, true);
		}

		//set default columnlist
		$this->_set_listview_query();

		//set grid buttons
		$data['jqg_buttons'] = $this->_default_grid_buttons();

		//set load jqgrid loadComplete callback
		$data['jqgrid_loadComplete'] = "";

		$data['default_query'] = true;
		$data['default_query_field'] = $this->db->dbprefix.$this->module_table.'.application_status_id';
		$data['default_query_val'] = $type;

		//load variables to env
		$this->load->vars($data);

		//load the final view
		//load header
		$this->load->view($this->userinfo['rtheme'] . '/template/header');
		$this->load->view($this->userinfo['rtheme'] . '/template/header-nav');

		//load page content
		$this->load->view($this->userinfo['rtheme'] . '/template/page-content');

		//load footer
		$this->load->view($this->userinfo['rtheme'] . '/template/footer');
	}

	function detail( $id = null, $rp = null ) {

		$data['scripts'][] = chosen_script();

		if( $id != null || $_POST['candidate_id'] != "" ){

			if( $_POST['candidate_id'] ){
				$id = $_POST['candidate_id'];
			}


			if( $rp == null ){

				$this->db->select('recruitment_applicant.applicant_id, recruitment_manpower_candidate.mrf_id');
				$this->db->where('recruitment_manpower_candidate.candidate_id',$id);
				$this->db->join('recruitment_applicant','recruitment_applicant.applicant_id = recruitment_manpower_candidate.applicant_id','left');
				$rec = $this->db->get('recruitment_manpower_candidate')->row();

				$_POST = array(
					"record_id" => $rec->applicant_id,
				    "previous_page" => "http://localhost/hr.pioneer/recruitment/candidates/index/".$rec->mrf_id,
				    "prev_search_str" => "",
				    "prev_search_field" => "all",
				    "prev_search_option" => "",
				    "undefined" => "undefined"
				);

				$data['rp'] = $rp;
				$data['mrf_id'] = $rec->mrf_id;
				$data['candidate_id'] = $id;
				$data['buttons'] = '/recruitment/applicants/detail-buttons';

			}
			else{

				$this->db->select('recruitment_applicant.applicant_id, recruitment_manpower_candidate.mrf_id');
				$this->db->where('recruitment_manpower_candidate.candidate_id',$id);
				$this->db->join('recruitment_applicant','recruitment_applicant.applicant_id = recruitment_manpower_candidate.applicant_id','left');
				$rec = $this->db->get('recruitment_manpower_candidate')->row();

				if( $rp == 2 ){

					$_POST = array(
						"record_id" => $rec->applicant_id,
					    "previous_page" => "http://localhost/hr.pioneer/recruitment/candidate_contract_signing/",
					    "prev_search_str" => "",
					    "prev_search_field" => "all",
					    "prev_search_option" => "",
					    "undefined" => "undefined"
					);

				}
				else{

					$_POST = array(
						"record_id" => $rec->applicant_id,
					    "previous_page" => "http://localhost/hr.pioneer/recruitment/candidate_job_offer/",
					    "prev_search_str" => "",
					    "prev_search_field" => "all",
					    "prev_search_option" => "",
					    "undefined" => "undefined"
					);

				}


				$data['rp'] = $rp;
				$data['mrf_id'] = $rec->mrf_id;
				$data['candidate_id'] = $id;
				$data['buttons'] = '/recruitment/applicants/detail-buttons';

			}
		}
		else{

			$data['mrf_id'] = "";
		}


		parent::detail();

		//additional module detail routine here
		$data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/js/detailview.js"></script>';
		$data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/modules/recruitment/applicants_detailview.js"></script>';

		if (IS_AJAX && $this->input->post('flag') == 0) {
			$data['content'] = 'recruitment/detailview';
		} else {
			$data['content'] = 'recruitment/compactview';
		}

		//other views to load
		$data['views'] = array();

		$record_id = $this->input->post('record_id');


		foreach ($this->_detail_types as $detail) {
			$data[$detail] = $this->_get_applicant_detail($detail);
		}

		$data['applicant_name'] = 'New Applicant';

		if ($record_id > 0) {
			$this->db->limit(1);
			$applicant = $this->db->get_where('recruitment_applicant', array('applicant_id' => $record_id))->row();

			$data['applicant_name'] = $applicant->lastname . ', ' . $applicant->firstname;
			$data['application_status'] = $applicant->application_status_id;
			$data['position_id'] = $applicant->position_id;
			$data['position2_id'] = $applicant->position2_id;

			if ($applicant->photo != '' && file_exists($applicant->photo)) {
				$data['photo'] = $applicant->photo;
			}

		}

		$data['wizard_header'] = 'recruitment/applicants/wizard_header';

		if (!empty($this->module_wizard_form) || $this->input->post('record_id') == '-1') {
			$data['show_wizard_control'] = true;
			$data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/js/editview-wizard-form-custom.js"></script>';
		}
		//load variables to env
		$this->load->vars($data);

		if (!IS_AJAX) {
			//load the final view
			//load header
			$this->load->view($this->userinfo['rtheme'] . '/template/header');
			$this->load->view($this->userinfo['rtheme'] . '/template/header-nav');

			//load page content
			$this->load->view($this->userinfo['rtheme'] . '/template/page-content');

			//load footer
			$this->load->view($this->userinfo['rtheme'] . '/template/footer');
		} else {
			$data['html'] = $this->load->view($this->userinfo['rtheme'] . '/' . $data['content'], '', true);

			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
		}
	}

	function edit() {

		$data['scripts'][] = chosen_script();

		if( $_POST['candidate_id'] != "" ){

			if( $_POST['candidate_id'] ){
				$id = $_POST['candidate_id'];
			}

			if( $_POST['rp'] ){
				$rp = $_POST['rp'];
			}

			if( !$_POST['rp'] ){

				$this->db->select('recruitment_applicant.applicant_id, recruitment_manpower_candidate.mrf_id');
				$this->db->where('recruitment_manpower_candidate.candidate_id',$id);
				$this->db->join('recruitment_applicant','recruitment_applicant.applicant_id = recruitment_manpower_candidate.applicant_id','left');
				$rec = $this->db->get('recruitment_manpower_candidate')->row();

				$_POST = array(
					"record_id" => $rec->applicant_id,
				    "previous_page" => "http://localhost/hr.pioneer/recruitment/candidates/index/".$rec->mrf_id,
				    "prev_search_str" => "",
				    "prev_search_field" => "all",
				    "prev_search_option" => "",
				    "undefined" => "undefined"
				);

				$data['mrf_id'] = $rec->mrf_id;
				$data['candidate_id'] = $id;
				$data['buttons'] = '/recruitment/applicants/edit-buttons';

			}
			else{


				$this->db->select('recruitment_applicant.applicant_id, recruitment_manpower_candidate.mrf_id');
				$this->db->where('recruitment_manpower_candidate.candidate_id',$id);
				$this->db->join('recruitment_applicant','recruitment_applicant.applicant_id = recruitment_manpower_candidate.applicant_id','left');
				$rec = $this->db->get('recruitment_manpower_candidate')->row();

				if( $rp == 2 ){

					$_POST = array(
						"record_id" => $rec->applicant_id,
					    "previous_page" => "http://localhost/hr.pioneer/recruitment/candidate_contract_signing/",
					    "prev_search_str" => "",
					    "prev_search_field" => "all",
					    "prev_search_option" => "",
					    "undefined" => "undefined"
					);

				}
				else{

					$_POST = array(
						"record_id" => $rec->applicant_id,
					    "previous_page" => "http://localhost/hr.pioneer/recruitment/candidate_job_offer/",
					    "prev_search_str" => "",
					    "prev_search_field" => "all",
					    "prev_search_option" => "",
					    "undefined" => "undefined"
					);

				}


				$data['rp'] = $rp;
				$data['mrf_id'] = $rec->mrf_id;
				$data['candidate_id'] = $id;
				$data['buttons'] = '/recruitment/applicants/edit-buttons';

			}
		}
		else{
			$data['mrf_id'] = "";
			$data['buttons'] = '/recruitment/applicants/edit-buttons-cs';
		}


		if ($this->user_access[$this->module_id]['edit'] == 1) {

			parent::edit();

			//additional module edit routine here
			$data['show_wizard_control'] = false;
			$data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/js/editview.js"></script>';
			$data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/modules/recruitment/applicant_common.js"></script>';
			$data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/jquery/jquery.maskedinput-1.3.min.js"></script>';
			if (!empty($this->module_wizard_form) || $this->input->post('record_id') == '-1') {
				$data['show_wizard_control'] = true;
				$data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/js/editview-wizard-form-custom.js"></script>';
			}
			$data['content'] = 'recruitment/editview';

			// Get default fieldgroup to open, if any.
			$default_fg = $this->input->post('default_fg');
			if (isset($default_fg) && $default_fg > 0) {
				$data['default_fg'] = $default_fg;
			}

			//other views to load
			$data['views'] = array();
			$data['views_outside_record_form'] = array();

			$record_id = $this->input->post('record_id');

			$data['applicant_name'] = 'New Applicant';

			if ($record_id > 0) {
				$this->db->limit(1);
				$applicant = $this->db->get_where('recruitment_applicant', array('applicant_id' => $record_id))->row();

				$data['applicant_name'] = $applicant->lastname . ', ' . $applicant->firstname;
				$data['working_since'] = $applicant->working_since;
				$data['no_work_experience'] = $applicant->no_work_experience;

				if ($applicant->photo != '' && file_exists($applicant->photo)) {
					$data['photo'] = $applicant->photo;
				}
			}

			foreach ($this->_detail_types as $detail) {
				$data[$detail] = $this->_get_applicant_detail($detail);
			}
			
			//load variables to env
			$this->load->vars($data);

			//load the final view
			//load header
			$this->load->view($this->userinfo['rtheme'] . '/template/header');
			$this->load->view($this->userinfo['rtheme'] . '/template/header-nav');

			//load page content
			$this->load->view($this->userinfo['rtheme'] . '/template/page-content');

			//load footer
			$this->load->view($this->userinfo['rtheme'] . '/template/footer');
		} else {
			$this->session->set_flashdata('flashdata', 'You dont have sufficient privilege to execute the edit action! Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
	}

	function ajax_save() {
		if ($this->input->post('record_id') == '-1') {
			$_POST['application_status_id'] = 1;
		}

		//check mandatory field before saving
		$applicant_error = false;
		//validate educational attainment
		$educ_level = $this->input->post('education');
		foreach ($educ_level['education_level'] as $educ) {
		    if (empty($educ)) {
				$applicant_error = true;
				$validate_applicant = array('Educational Attainment is a mandatory field.');
		    }
		}

		if ($applicant_error){
			$response['msg'] = implode('<br />', $validate_applicant);
			$response['msg_type'] = 'error';		
			$data['json'] = $response;
			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
		}else{
			parent::ajax_save();

			$birthday = new DateTime($this->input->post('birth_date'));
			$current_date = new DateTime();
			$date_diff = $birthday->diff($current_date);
			$age = $date_diff->y;

			$data = array(
				'applicant_id' => $this->key_field_val,
				'position_applied' => $this->input->post('position_id'),
				'applied_date' => date('Y-m-d H:i:s'),
				'status' => 1,
				'mrf_id' => 0
			);

			//save aaplication
			$this->db->insert('recruitment_applicant_application',$data);

			$this->db->where($this->key_field, $this->key_field_val);
			$this->db->update($this->module_table,array('age' => $age));	

		//additional module save routine here
		// END.		
			if ($this->input->post('record_id') == '-1') {
				$this->send_email($this->key_field_val);
			}
		}
	}

	function send_email($applicant_id = 0)
	{	
		if (!IS_AJAX) {
			$this->session->set_flashdata('flashdata', 'Direct access is not allowed.');
			redirect(base_url());
		} else {
			$this->db->where('applicant_id', $applicant_id);
			$this->db->where('deleted', 0);
			$record = $this->db->get('recruitment_applicant');

			if (!$record || $record->num_rows() == 0) {
				$response->msg 	    = 'Record not found. Request was not sent.';
				$response->msg_type = 'error';
			} else {

				// Send email.
                // Load the template.            
                $this->load->model('template');

                $applicant_info = $record->row_array();

                $request['applicant_name'] = $applicant_info['firstname']." ".$applicant_info['lastname'];
                $request['position_applied_for'] = $this->db->get_where('user_position', array("position_id" => $applicant_info['position_id']))->row()->position; 

                $template = $this->template->get_module_template($this->module_id, 'applicant_saving');
                $message = $this->template->prep_message($template['body'], $request);

				$recepients[] = $applicant_info['email'];

                $this->template->queue(implode(',', $recepients), '', $template['subject']." : ".$applicant_info['firstname']." ".$applicant_info['lastname'], $message);

    //             $response->msg 	    = 'Your Application is successfully save.';
				// $response->msg_type = 'success';

			}

			// $data['json'] = $response;
			// $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
		}	
	}	

	/* START List View Functions */

	function _set_left_join() {
		parent::_set_left_join();
		$this->db->join('user_position afpos', 'afpos.position_id = ' . $this->db->dbprefix . $this->module_table . '.af_pos_id', 'left');
		$this->db->join('recruitment_candidate_blacklist_status bs', 'bs.recruitment_candidate_blacklist_status_id = ' . $this->db->dbprefix.$this->module_table . '.recruitment_candidate_blacklist_status', 'left');
		//$this->db->join('recruitment_applicant_skill', 'recruitment_applicant_skill.applicant_id = ' . $this->db->dbprefix . $this->module_table . '.applicant_id', 'left');

/*		if (CLIENT_DIR != 'oams'){
			$this->db->join('recruitment_applicant_training', 'recruitment_applicant_training.applicant_id = ' . $this->db->dbprefix . $this->module_table . '.applicant_id', 'left');		
		}*/
	}

	function listview()
	{
		if( $this->input->post('mrf') ){
			$mrf = $this->db->get_where('recruitment_manpower', array('request_id' => $this->input->post('mrf')))->row();
			$position_id = $mrf->position_id;
		}
		
		
		$response->msg = "";

		$page = $this->input->post('page');
		$limit = $this->input->post('rows'); // get how many rows we want to have into the grid
		$sidx = $this->input->post('sidx'); // get index row - i.e. user click to sort
		$sord = $this->input->post('sord'); // get the direction
		$related_module = ( $this->input->post('related_module') ? true : false );

		$view_actions = (isset($_POST['view']) && $_POST['view'] == 'detail') ? false : true ;

		//set columnlist and select qry
		$this->_set_listview_query( '', $view_actions );
		$this->listview_qry .= ',afpos.position as af_position, '.$this->db->dbprefix.$this->module_table.'.application_status_id';		
		$this->listview_qry .= ', bs.recruitment_candidate_blacklist_status';

		if ($this->input->post('searchField') == 'recruitment_applicant.position_id'){
			$_POST['searchField'] = 'position';
		}

		if ($this->input->post('searchField') == 'recruitment_applicant.application_date'){
			$output = preg_replace( '/[^0-9]/', '', $this->input->post('searchString'));
			switch (strlen($output)) {
				case 6:
						$_POST['searchField'] = 'DATE('.$this->db->dbprefix.'recruitment_applicant.application_date)';
						$_POST['searchString'] = date('Y-m-d',strtotime($this->input->post('searchString')));
					break;
				case 4:
						$_POST['searchField'] = 'YEAR('.$this->db->dbprefix.'recruitment_applicant.application_date)';
						$_POST['searchString'] = $this->input->post('searchString');
					break;	
				case 2:
						$_POST['searchField'] = 'MONTH('.$this->db->dbprefix.'recruitment_applicant.application_date)';
						$_POST['searchString'] = preg_replace( '/[0]/', '', $this->input->post('searchString'));
					break;
			}
		}
		
/*		if ($this->input->post('searchField') == 'recruitment_applicant.application_date'){
			$_POST['searchField'] = 'DATE('.$this->db->dbprefix.'recruitment_applicant.application_date)';
			$_POST['searchString'] = date('Y-m-d',strtotime($this->input->post('searchString')));
		}*/

		//set Search Qry string
		if($this->input->post('_search') == "true")
			$search = $this->input->post('searchField') == "all" ? $this->_set_search_all_query() : $this->_set_specific_search_query();
		else
			$search = 1;		

		if ($this->input->post('other') == 'candidate') {
			$search .= ' AND ' . $this->db->dbprefix . $this->module_table . '.application_status_id IN (1,5) ';
		}
		
		if(isset($position_id)) {
			$search .= 
				' AND (' . $this->db->dbprefix . $this->module_table . '.position_id = '. $position_id 
					. ' OR ' . $this->db->dbprefix . $this->module_table . '.af_pos_id = ' . $position_id 
					. ')';
		}
		
		if( $this->module == "user" && (!$this->is_admin && !$this->is_superadmin) ) $search .= ' AND '.$this->db->dbprefix.'user.user_id NOT IN (1,2)';

		/* count query */
		//build query
		$this->_set_left_join();
		$this->db->select($this->listview_qry, false);
		$this->db->from($this->module_table);
		$this->db->where($this->module_table.'.deleted = 0 AND '.$search);
		if (CLIENT_DIR != "oams") {
			$this->db->where( $this->module_table.'.application_status_id != 4' );	
		}
		

		//get list
		$result = $this->db->get();
		// $response->last_query = $this->db->last_query();

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
			if (CLIENT_DIR != "oams") {
				$this->db->where( $this->module_table.'.application_status_id != 4' );	
			}

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
									$cell[$cell_ctr] = ( $related_module ? $this->_default_field_module_link_actions( $row[$this->key_field], $this->input->post('container'), $this->input->post('fmlinkctr'), $row ) : $this->_default_grid_actions( $this->module_link, $this->input->post('container'), $row) );
									$cell_ctr++;
								}
							} else if ($detail['name'] == 't5application_status') {
								$this->listview_fields[$cell_ctr]['value'] = $row[$detail['name']];
								$cell[$cell_ctr] = $this->uitype_listview->fieldValue( $this->listview_fields[$cell_ctr] );

								if ($row['application_status_id'] == 2) {

									$candidate_status = $this->system->get_applicant_candidate_status($row['applicant_id']);
									$cell[$cell_ctr] .= '<br /><span class="blue"><small>'.$candidate_status.'</small></span>';
								}


								if ($row['application_status_id'] == 5) {
									$cell[$cell_ctr] .= '<br />(' . $row['af_position'] . ')';
								}

								if ($row['application_status_id'] == '6') {
									$cell[$cell_ctr] .= '<br /><small class="red">' . $row['recruitment_candidate_blacklist_status'] . '</small>';
								}
								
								$cell_ctr++;
							} else{
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

	// END - default module functions
	// START custom module funtions

	protected function after_ajax_save() {

		if ($this->input->post('record_id') != '-1'){
			$this->db->where('applicant_id', $this->key_field_val);
			$this->db->update('recruitment_applicant', array('aux' => $this->input->post('aux')));				
		}

		if (isset($this->key_field_val) && $this->key_field_val > 0) {
			$applicant_id = $this->key_field_val;

			// Save applicant code.
			if ($this->input->post('record_id') == '-1') {			
				$this->db->where($this->key_field, $this->key_field_val);
				$applicant = $this->db->get($this->module_table)->row();

				$uin = date('Ymd', strtotime($applicant->application_date)) . '-' . number_pad($applicant_id, '6');
				$this->db->update(
					$this->module_table, 
					array('uin' => $uin),
					array($this->key_field => $this->key_field_val)
					);
			}

			// START.			
			// Process other details.
			$employment_data = array(
			    'no_work_experience' => $this->input->post('no_work_experience'),
			    'working_since' => $this->input->post('working_since')
			);

			$this->db->update('recruitment_applicant', $employment_data, array( $this->key_field => $applicant_id ));

			foreach ($this->_detail_types as $detail) {

				$table = 'recruitment_applicant_' . $detail;

				if ($this->db->table_exists($table)) {
					$this->db->delete($table, array('applicant_id' => $applicant_id));
					$post = $this->input->post($detail);

					if (!is_null($post) && is_array($post)) {
						// Handle the dates.
						foreach ($post as $key => $value) {
							$key_string_segments = explode('_', $key);

							if ($detail == 'education'){		

								if ((in_array(end($key_string_segments), array('from', 'to'))) || end($key_string_segments) == 'date'){
									foreach ($post[$key] as &$date){

										if($date != "" && $date != "1970-01-01" && $date != "0000-00-00" && $date !== "January 1970"){

											if (CLIENT_DIR == 'firstbalfour'){
												$date = $date . '-01-01';
											}
											else{
												$date = date('Y-m-d', strtotime($date));
											}

										}else{
											$date = 'NULL';
										}	

									}
								}
							}

							if ( $key == 'date_joined' || $key == 'date_resigned' ) {
								foreach ($post[$key] as &$date){
									if($date != ""){
										$date = date('Y-m-d', strtotime($date));
									}else{
										$date = $date;
									}
								}
							}	

							if ($detail == 'affiliates'){		

								if ((in_array(end($key_string_segments), array('from', 'to'))) || end($key_string_segments) == 'date'){
									foreach ($post[$key] as &$date){

										if($date != "" && $date != "1970-01-01" && $date != "0000-00-00" && $date !== "January 1970"){
											// $date = $date . '-01-01';
											$date = $date;
										}else{
											$date = 'NULL';
										}	
									}
								}

							}

							if ( $detail == 'test_profile' && $key == 'date_taken' ) {
								foreach ($post[$key] as &$date){
									if($date != ""){
										$date = date('Y-m-d', strtotime($date));
									}else{
										$date = $date;
									}
								}
							}

							if ( $detail == 'family' && $key == 'birth_date' ) {
								foreach ($post[$key] as &$date){
									if($date != ""){
										$date = date('Y-m-d', strtotime($date));
									}else{
										$date = $date;
									}
								}
							}

							if ( $detail == 'accountabilities' && ( $key == 'date_issued' || $key == 'date_returned') ) {
								foreach ($post[$key] as &$date){
									if($date != ""){
										$date = date('Y-m-d', strtotime($date));
									}else{
										$date = $date;
									}
								}
							}

							if ( $detail == 'training' && ( $key == 'from_date' || $key == 'to_date') ) {
								foreach ($post[$key] as &$date){
									if($date != "" && $date != "1970-01-01" && $date != "0000-00-00"){
										$date = date('Y-m-d', strtotime($date));
									}else{
										$date = 'NULL';
									}
								}
							}

							if ($detail == 'employment'){		
								if ((in_array(reset($key_string_segments), array('from', 'to'))) || reset($key_string_segments) == 'date'){
									foreach ($post[$key] as &$date){
										if($date != "" && $date != "1970-01-01" && $date != "0000-00-00" && $date !== "January 1970"){
											$date = date('Y-m-d', strtotime($date));
										}else{
											$date = 'NULL';
										}	

									}
								}
							}

						}

						$data = $this->_rebuild_array($post, $applicant_id);
						$this->db->insert_batch($table, $data);
					}
				}

			}
			
			// Manually add the skills. MY_Controller::ajax_save() 
			$skills_data = array(
			    'machine_operated' => $this->input->post('machine_operated'),
			    'software_used' => $this->input->post('software_used'),
			    'other' => $this->input->post('other'),
			    $this->key_field => $this->key_field_val
			);

			$this->db->where($this->key_field, $this->key_field_val);
			$this->db->where('deleted', 0);

			$result = $this->db->get('recruitment_applicant_skills');

			if (count($result->row()) > 0) {
				$this->db->where($this->key_field, $this->key_field_val);
				$this->db->update('recruitment_applicant_skills', $skills_data);
			} else {
				$this->db->insert('recruitment_applicant_skills', $skills_data);
			}			
		}

		$image_config = array();
		// Resize image if a new one is submitted.		
		if (file_exists($this->input->post('photo'))) {
			$orig_path    = explode('/', $this->input->post('photo'));
			$orig_path[0] .= '/thumbs';
			$thumb_path   = implode('/', $orig_path);

			unset($orig_path[count($orig_path) - 1]);
			
			$thumb_dir = implode('/', $orig_path);

			$this->load->library('image_lib');
			
			$image_config['source_image']   = $this->input->post('photo');
			$image_config['create_thumb']   = TRUE;
			$image_config['maintain_ratio'] = TRUE;
			$image_config['thumb_marker']   = '';
			$image_config['new_image']      = $thumb_path;
			$image_config['width']          = 50;
			$image_config['height']         = 50;
		}

		if (count($image_config) > 0) {
			if (!is_dir($thumb_dir)) {
				if (!mkdir($thumb_dir, 0755, true)) {
				$response->msg 		= 'Could not create directory. DIR:' . $thumb_dir;
				$response->msg_type = 'attention';
				}
			}

			$this->image_lib->initialize($image_config);

			if (!$this->image_lib->resize()) {
				// How to handle error?
				$response->msg 		= $this->image_lib->display_errors();
				$response->msg_type = 'attention';

				$this->set_message($response);					
			}
		}			
		
		parent::after_ajax_save();			
	}

	function get_form($type) {
		if (IS_AJAX) {
			if ($type == '' && !in_array($type, $this->_detail_types)) {
				show_error("Insufficient data supplied.");
			} else {

				$data['count'] = $this->input->post('counter_line');
				$data['rand'] = rand(1000,9999);

				$response = $this->load->view($this->userinfo['rtheme'] . '/recruitment/applicants/' . $type . '/form',$data);

				$data['html'] = $response;

				$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
			}
		} else {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
	}

	private function _get_applicant_detail($detail, $record_id = 0) {
		if ($record_id == 0) {
			$record_id = $this->input->post('record_id');
		}

		$response = array();

		if ($detail == '' && !in_array($detail, $this->_detail_types)) {
			show_error("Insufficient data supplied.");
		} else {
			$table = 'recruitment_applicant_' . $detail;

			$this->db->where('applicant_id', $record_id);

			if ($detail == 'education') {
				$this->db->select($table . '.degree,'
					. $table . '.date_from,'
					. $table . '.graduate,'
					. $table . '.employee_degree_obtained_id,'										
					. $table . '.course,'
					. $table . '.date_to,'
					. $table . '.date_graduated,'
					. $table . '.school,'
					. $table . '.education_school_id,'					
					. $table . '.honors_received,'
					. ', do.option_id, do.value as education_level');
				$this->db->join('dropdown_options do', 'do.option_id = ' . $table . '.education_level', 'left');
			}

			$result = $this->db->get($table);

			if ($result->num_rows() == 0) {
				$fields = $this->db->list_fields($table);
				$vals   = array_fill(0, count($fields), '');
				// Return assoc array with empty value so template does not print out the placeholders.
				$response[] = array_combine($fields, $vals);
			} else {
				$response = $result->result_array();
			}				
		}

		return $response;
	}

	/**
	 * Rearrange the array to a new array which can be used for insert_batch
	 *
	 * @param array $array
	 * @param int $key
	 *
	 * @return array
	 */
	private function _rebuild_array($array, $fkey = null) {
		if (!is_array($array)) {
			return array();
		}

		$new_array = array();

		$count = count(end($array));
		$index = 0;

		while ($count > $index) {
			foreach ($array as $key => $value) {
				$new_array[$index][$key] = $array[$key][$index];
				if (!is_null($fkey)) {
					$new_array[$index]['applicant_id'] = $fkey;
				}
			}

			$index++;
		}

		return $new_array;
	}

	function quick_edit( $customview = "" )
	{
		if( IS_AJAX ){
			$response->msg = "";
			if( !isset($_POST['record_id']) && $this->uri->rsegment(3) ) $_POST['record_id'] = $this->uri->rsegment(3);
			if( $this->input->post( 'record_id' ) ){
				
				if( ($this->input->post( 'record_id' ) == "-1" && $this->user_access[$this->module_id]['add'] == 1) || ($this->input->post( 'record_id' ) != "-1" && $this->user_access[$this->module_id]['edit'] == 1) ){
					$data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/modules/'.$this->module_link.'.js"></script>';

					$data['module'] = $this->module;
					$data['module_link'] = $this->module_link;
					$data['fmlinkctr'] = $this->input->post( 'fmlinkctr' );

					$this->load->model( 'uitype_edit' );
					$data['fieldgroups'] = $this->_record_detail( $this->input->post('record_id' ), true);

					$data['show_wizard_control'] = false;
					if( !empty($this->module_wizard_form) && $this->input->post('record_id') == '-1' ){
						$data['show_wizard_control'] = true;
						$data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/js/editview-wizard-form.js"></script>';
					}

					//other views to load
					$data['views'] = array();

					//load the final view
					$this->load->vars($data);
					if( isset($_POST['mode']) && $_POST['mode'] == 'copy' ) $_POST['record_id'] = -1;
					$response->quickedit_form = $this->load->view( $this->userinfo['rtheme']. $customview ."/quickedit" , "", true );
				}
				else{
					$response->msg = "You dont have sufficient privilege to execute the action! Please contact the System Administrator.";
					$response->msg_type = 'attention';	
				}
			}
			else{
				$response->msg = "Insufficient data supplied.";
				$response->msg_type = 'attention';
			}
			
			$data['json'] = $response;
			$this->load->view($this->userinfo['rtheme'].'/template/ajax', $data);
		}
		else{
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url().$this->module_link);
		}
	}

	function delete() {
		parent::delete();

		//additional module delete routine here
		// Delete other details
		foreach ($this->_detail_types as $detail) {
			$table = 'recruitment_applicant_' . $detail;

			$this->db->where('applicant_id', $this->input->post('record_id'));
			$this->db->delete($table);
		}
	}

	function print_record($applicant_id = 0) {			
		if (!$this->user_access[$this->module_id]['visible']) {
			$this->session->set_flashdata('flashdata', 'You dont have sufficient access to the requested module. <span class="red">Please contact the System Administrator.</span>.');
			redirect(base_url() . $this->module_link);
		}

		// Get from $_POST when the URI is not present.
		if ($applicant_id == 0) {
			$applicant_id = $this->input->post('record_id');
		}				

		$this->load->library('pdf');
		$this->load->model(array('uitype_detail', 'template'));

		$template = $this->template->get_module_template($this->module_id, 'applicant_personal_info_final');		
		
		// Get applicant details. (This returns the fieldgroup array.
		$check_record = $this->_record_exist($applicant_id);
		if ($check_record->exist) {
			$vars = array();

			// Get the vars to pass to the template.

			$vars = get_record_detail_array($applicant_id);

			$this->db->select('*,up.position,upot.position AS other_position,c1.city as pres_city,c2.city as perm_city');
			$this->db->join('referred_by','recruitment_applicant.referred_by_id = referred_by.referred_by_id','left');
			$this->db->join('user_position up','recruitment_applicant.position_id = up.position_id','left');
			$this->db->join('user_position upot','recruitment_applicant.position2_id = upot.position_id','left');
			$this->db->join('civil_status cs','recruitment_applicant.civil_status_id = cs.civil_status_id','left');
			$this->db->join('religion rel','recruitment_applicant.religion_id = rel.religion_id','left');
			$this->db->join('cities c1','recruitment_applicant.pres_city = c1.city_id','left');
			$this->db->join('cities c2','recruitment_applicant.perm_city = c2.city_id','left');
			$result = $this->db->get_where("recruitment_applicant",array("applicant_id"=>$applicant_id));

			if ($result && $result->num_rows() > 0){
				$vars = $result->row_array();
			}

			$vars['date_of_marriage'] = ((isset($vars['date_of_marriage']) && $vars['date_of_marriage'] != '' && $vars['date_of_marriage'] != '0000-00-00') ? date('Y-m-d',strtotime($vars['date_of_marriage'])) : '');
			$vars['application_date'] = ((isset($vars['application_date']) && $vars['application_date'] != '' && $vars['application_date'] != '0000-00-00 00:00:00') ? date('Y-m-d',strtotime($vars['application_date'])) : '');
			$vars['available_start_date'] = ((isset($vars['availability_date']) && $vars['availability_date'] != '' && $vars['availability_date'] != '0000-00-00 00:00:00') ? date('Y-m-d',strtotime($vars['availability_date'])) : '');
			$vars['currently_employed'] = ($currently_employed == 1 ? 'Yes' : 'No');
			$vars['middleinitial'] = '';
			$vars['taxcode'] = '';
			$vars['passport_no'] = '';
			$vars['date_of_issue'] = '';
			$vars['id_number'] = '';
			$vars['status'] = '';
			$vars['company'] = '';
			$vars['employed_date'] = '';
			$vars['location'] = '';
			$vars['regular_date'] = '';
			$vars['department'] = '';
			$vars['department'] = '';
			$vars['immediate_superior'] = '';
			$vars['position_is'] = '';

			$vars['bday'] = date('m/d/Y',strtotime($vars['birth_date']));
			$vars['family_background'] = $this->get_family_background($applicant_id);
			$vars['educational_background'] = $this->get_educational_background($applicant_id);
			$vars['training'] = $this->get_training($applicant_id);
			$vars['employment_history'] = $this->get_employment_history($applicant_id);
			$vars['character_ref'] = $this->get_character_ref($applicant_id);
			$vars['skills'] = $this->get_skills($applicant_id);
			$vars['affilliation'] = $this->get_affilliation($applicant_id);
			$vars['exam_certification'] = $this->get_exam($applicant_id);
			$vars['referral'] = $this->get_referral($applicant_id);
			$vars['gender_male'] = ($vars['sex'] == 'male' ? 'X' : '&nbsp'); 
			$vars['gender_civil'] = '<td align="left" style="border-bottom:1px dotted black" width="10%">Gender:</td>
						<td align="center" style="border-bottom:1px dotted black" width="13%">( '.($vars['sex'] == 'male' ? 'X' : '&nbsp;').' )</td>
						<td align="center" style="border-bottom:1px dotted black" width="13%">( '.($vars['sex'] == 'female' ? 'X' : '&nbsp;').' )</td>
						<td align="center" style="border-bottom:1px dotted black" width="10%">Civil Status:</td>
						<td align="center" style="border-bottom:1px dotted black" width="13%">( '.($vars['civil_status'] == 'Single' ? 'X' : '&nbsp;').' )</td>
						<td align="center" style="border-bottom:1px dotted black" width="13%">( '.($vars['civil_status'] == 'Married' ? 'X' : '&nbsp;').' )</td>
						<td align="center" style="border-bottom:1px dotted black" width="13%">( '.($vars['civil_status'] == 'Widowed' ? 'X' : '&nbsp;').' )</td>
						<td align="center" style="border-bottom:1px dotted black" width="13%">( '.($vars['civil_status'] == 'Divorced' ? 'X' : '&nbsp;').' )</td>';
			//load variables to applicant view files.
/*			foreach ($this->_detail_types as $detail) {
				$vars[$detail] = $this->_get_applicant_detail($detail, $applicant_id);
			}	*/		

			if (isset($vars['photo']) && ($vars['photo'] == '' || !file_exists($vars['photo']))) {
				$vars['photo'] = 'themes/blue/images/no-photo.jpg';
			}			

			$referred_by_choices = $this->db->get('referred_by')->result();
			
			foreach ($referred_by_choices as $choice) {
				if ($vars['referred_by_id'] == $choice->referred_by) {
					$vars[$choice->referred_by] = 'x';
				} else {
					$vars[$choice->referred_by] = '&nbsp;';
				}
			}
			
/*			preg_match('/([a-zA-Z]{3}\s{1}\d{2},\s\d{4})\s([0-9]{2}:[0-9]{2}\s[ap]m)/', $vars['application_date'], $x);

			$vars['application_date'] = $x[1];
			$vars['time'] 			  = $x[2];*/
			// Compute age.
			$vars['age'] = get_age($vars['birth_date']);

			// Suppress errors because the template model does not take into account the possibility that a variable may not have been set.
			@$html = $this->template->prep_message($template['body'], $vars, false, true);

			// Prepare and output the PDF.
			$this->pdf->setLeftMargin('5.00');			
			$this->pdf->setRightMargin('5.00');			
			$this->pdf->addPage();
			$header = 'uploads/system/fb_logo.jpg';
			$this->pdf->Image($header, 0, 5, 0, 0, 'JPG', '', '', false, 100, '', false, false, 0, false, false, false);
			$this->pdf->writeHTML($html, true, false, false, false, '');
			$this->pdf->Output(date('Y-m-d').' '.$vars['firstname'] . '_' . $vars['lastname'] . '.pdf', 'D');
		} else {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
	}

	function get_family_background($applicant_id){
		$fb_result_html = '<table border="1" cellpadding="10" cellspacing="0" style="border-collapse: collapse;" width="100%">
					<tbody>
						<tr>
							<td align="center" style="padding:3px 0;margin:0" width="15%">&nbsp;</td>
							<td align="center" style="padding:3px 0;margin:0" width="25%">Last Name, First Name, M.I.</td>
							<td align="center" style="padding:3px 0;margin:0" width="20%">Birth Date</td>
							<td align="center" style="padding:3px 0;margin:0" width="15%">Age</td>
							<td align="center" style="padding:3px 0;margin:0" width="25%">Occupation</td>
						</tr>';
		$fb_result = $this->db->get_where('recruitment_applicant_family',array("applicant_id"=>$applicant_id));

		if ($fb_result && $fb_result->num_rows() > 0){
			foreach ($fb_result->result() as $row) {
				$birth_date = (($row->birth_date && $row->birth_date != '0000-00-00') ? date('Y-m-d',strtotime($row->birth_date)) : '');
				$fb_result_html .= '<tr>
						<td align="left" style="padding:3px 0;margin:0">'.$row->relationship.'</td>
						<td align="left" style="padding:3px 0;margin:0">'.$row->name.'</td>
						<td align="left" style="padding:3px 0;margin:0">'.$birth_date.'</td>
						<td align="left" style="padding:3px 0;margin:0">'.$row->age.'</td>
						<td align="left" style="padding:3px 0;margin:0">'.$row->occupation.'</td>
					</tr>';
			}
		}
		else{
			for ($i=0; $i < 6; $i++) { 
				$fb_result_html .= '<tr>
						<td align="left" style="padding:3px 0;margin:0">&nbsp;</td>
						<td align="left" style="padding:3px 0;margin:0">&nbsp;</td>
						<td align="left" style="padding:3px 0;margin:0">&nbsp;</td>
						<td align="left" style="padding:3px 0;margin:0">&nbsp;</td>
						<td align="left" style="padding:3px 0;margin:0">&nbsp;</td>
					</tr>';
			}
		}

		$fb_result_html .= '</tbody>
				</table>';

		return $fb_result_html;
	}

	function get_educational_background($applicant_id){
		$edu_result_html = '<table border="1" cellpadding="10" cellspacing="0" style="border-collapse: collapse;" width="100%">
					<tbody>
						<tr>
							<td align="center" style="padding:3px 0;margin:0" width="15%">&nbsp;</td>
							<td align="center" style="padding:3px 0;margin:0" width="35%">School’s Name & Location</td>
							<td align="center" style="padding:3px 0;margin:0" width="35%">Degree / Course <br />Year Graduated</td>
							<td align="center" style="padding:3px 0;margin:0" width="15%">Honors / Awards</td>
						</tr>';

		$this->db->join('employee_degree_obtained','recruitment_applicant_education.employee_degree_obtained_id = employee_degree_obtained.employee_degree_obtained_id','left');
		$this->db->join('education_school','recruitment_applicant_education.education_school_id = education_school.education_school_id','left');
		$this->db->join('dropdown_options','recruitment_applicant_education.education_level = dropdown_options.option_id','left');
		$edu_result = $this->db->get_where('recruitment_applicant_education',array("applicant_id"=>$applicant_id));

		if ($edu_result && $edu_result->num_rows() > 0){
			foreach ($edu_result->result() as $row) {
				$date_from = (($row->date_from && $row->date_from != '0000-00-00') ? date('Y-m',strtotime($row->date_from)) : '');
				$date_to = (($row->date_to && $row->date_to != '0000-00-00') ? date('Y-m',strtotime($row->date_to)) : '');
				$school = ($row->education_school != '' ? $row->education_school : $row->school);
				$edu_result_html .= '<tr>
					<td align="left" style="padding:3px 0;margin:0">'.$row->value.'</td>
					<td align="left" style="padding:3px 0;margin:0">'.$school.'</td>
					<td align="left" style="padding:3px 0;margin:0">'.$row->employee_degree_obtained.' '.($row->date_to != '0000-00-00' && $row->date_to ? date('Y',strtotime($row->date_to)) : '').'</td>
					<td align="left" style="padding:3px 0;margin:0">'.$row->honors_received.'</td>				
				</tr>';
			}
		}
		else{
			for ($i=0; $i < 6; $i++) { 
				$edu_result_html .= '<tr>
					<td align="left" style="padding:3px 0;margin:0">&nbsp;</td>
					<td align="left" style="padding:3px 0;margin:0">&nbsp;</td>
					<td align="left" style="padding:3px 0;margin:0">&nbsp;</td>
					<td align="left" style="padding:3px 0;margin:0">&nbsp;</td>
				</tr>';
			}
		}

		$edu_result_html .= '</tbody>
				</table>';

		return $edu_result_html;
	}

	function get_training($applicant_id){
		$training_result_html = '<table border="1" cellpadding="10" cellspacing="0" style="border-collapse: collapse;" width="100%">
					<tbody>
						<tr>
							<td align="center" style="padding:3px 0;margin:0" width="30%">Course/ Seminar Title</td>
							<td align="center" style="padding:3px 0;margin:0" width="20%">Inclusive Dates <br /> / No. of Hours</td>
							<td align="center" style="padding:3px 0;margin:0" width="35%">Facilitators / Company</td>
							<td align="center" style="padding:3px 0;margin:0" width="15%">Venue</td>
						</tr>';

		$this->db->select('*,course.course as final_course');				
		$this->db->join('course','recruitment_applicant_training.course_id = course.course_id','left');
		$training_result = $this->db->get_where('recruitment_applicant_training',array("applicant_id"=>$applicant_id));

		if ($training_result && $training_result->num_rows() > 0){
			foreach ($training_result->result() as $row) {
				$date_from = (($row->from_date && $row->from_date != '0000-00-00') ? date('Y-m',strtotime($row->from_date)) : '');
				$date_to = (($row->to_date && $row->to_date != '0000-00-00') ? date('Y-m',strtotime($row->to_date)) : '');
				$date_from_to = ($date_from != '' ? $date_from.' ~ '.$date_to : '');

				$training_result_html .= '<tr>
					<td align="left" style="padding:3px 0;margin:0">'.$row->final_course.'</td>
					<td align="left" style="padding:3px 0;margin:0">'.$date_from_to.' / '.$row->no_of_hours.'</td>
					<td align="left" style="padding:3px 0;margin:0">'.$row->institution.'</td>					
					<td align="left" style="padding:3px 0;margin:0">'.$row->address.'</td>
				</tr>';
			}
		}
		else{
			for ($i=0; $i < 6; $i++) { 
				$training_result_html .= '<tr>
					<td align="left" style="padding:3px 0;margin:0">&nbsp;</td>
					<td align="left" style="padding:3px 0;margin:0">&nbsp;</td>
					<td align="left" style="padding:3px 0;margin:0">&nbsp;</td>
					<td align="left" style="padding:3px 0;margin:0">&nbsp;</td>
				</tr>';
			}
		}

		$training_result_html .= '</tbody>
				</table>';

		return $training_result_html;
	}

	function get_employment_history($applicant_id){
		$eh_result_html = '';
		$eh_result = $this->db->get_where('recruitment_applicant_employment',array("applicant_id"=>$applicant_id));

		if ($eh_result && $eh_result->num_rows() > 0){
			foreach ($eh_result->result() as $row) {
				$date_from = (($row->from_date && $row->from_date != '0000-00-00') ? date('Y-m-d',strtotime($row->from_date)) : '');
				$date_to = (($row->to_date && $row->to_date != '0000-00-00') ? date('Y-m-d',strtotime($row->to_date)) : '');

				$date_from_to = ($date_from != '' ? $date_from.' ~ '.$date_to : '');
				$eh_result_html .= '<table border="1" cellpadding="10" cellspacing="0" style="border-collapse: collapse;" width="100%">
					<tbody>
						<tr>
							<td align="left" style="padding:3px 0;margin:0">Name of Employer: '.$row->company.'</td>
							<td align="left" style="padding:3px 0;margin:0" rowspan="2">Employment Dates: <br/> '.$date_from_to.'</td>
						</tr>
						<tr>
							<td align="left" style="padding:3px 0;margin:0">Type of Industry: '.$row->nature_of_business.'</td>
						</tr>
						<tr>
							<td align="left" style="padding:3px 0;margin:0">Job Title: '.$row->position.'</td>
							<td align="left" style="padding:3px 0;margin:0" rowspan="2">Employment Status: <br/> '.$row->last_employment_status.'</td>
						</tr>
						<tr>
							<td align="left" style="padding:3px 0;margin:0">Address: '.$row->address.'</td>
						</tr>
						<tr>
							<td align="left" style="padding:3px 0;margin:0">Contact Number: '.$row->contact_number.'</td>
							<td align="left" style="padding:3px 0;margin:0">Basic Salary: '.$row->last_salary.'</td>
						</tr>
						<tr>
							<td align="left" style="padding:3px 0;margin:0">Reason for Leaving: '.$row->reason_for_leaving.'</td>
							<td align="left" style="padding:3px 0;margin:0">Allowance: '.$row->allowance.'</td>
						</tr>
					</tbody>
				</table>
				<table border="0" cellpadding="30" cellspacing="0" style="border-collapse: collapse;" width="100%">
					<tbody>
						<tr>
							<td align="left">Responsibilities:</td>
						</tr>																		
					</tbody>
				</table>				
				<table border="1" cellpadding="10" cellspacing="0" style="border-collapse: collapse;" width="100%">
					<tbody>
						<tr>
							<td align="left" style="padding:3px 0;margin:0">'.$row->duties.'</td>
						</tr>
					</tbody>
				</table>
				<table border="0" cellpadding="30" cellspacing="0" style="border-collapse: collapse;" width="100%">
					<tbody>
						<tr>
							<td align="left">Accomplishments:</td>
						</tr>																		
					</tbody>
				</table>				
				<table border="1" cellpadding="10" cellspacing="0" style="border-collapse: collapse;" width="100%">
					<tbody>
						<tr>
							<td align="left" style="padding:3px 0;margin:0">'.$row->accomplishment.'</td>
						</tr>
					</tbody>
				</table>
				<table border="0" cellpadding="30" cellspacing="0" style="border-collapse: collapse;" width="100%">
					<tbody>
						<tr>
							<td align="left">What do/did you like most of your job:</td>
						</tr>																		
					</tbody>
				</table>				
				<table border="1" cellpadding="10" cellspacing="0" style="border-collapse: collapse;" width="100%">
					<tbody>
						<tr>
							<td align="left" style="padding:3px 0;margin:0">'.$row->most_like_job.'</td>
						</tr>
					</tbody>
				</table>
				<table border="0" cellpadding="30" cellspacing="0" style="border-collapse: collapse;" width="100%">
					<tbody>
						<tr>
							<td align="left">What do/did you least enjoy:</td>
						</tr>																		
					</tbody>
				</table>				
				<table border="1" cellpadding="10" cellspacing="0" style="border-collapse: collapse;" width="100%">
					<tbody>
						<tr>
							<td align="left" style="padding:3px 0;margin:0">'.$row->least_enjoy.'</td>
						</tr>
					</tbody>
				</table>
				<table border="0" cellpadding="10" cellspacing="0" style="border-collapse: collapse;" width="100%">
					<tbody>
						<tr>
							<td align="left" width="18%">&nbsp;&nbsp;Name of Superior: </td>
							<td align="center" width="32%" style="border-bottom:1px solid black">'.$row->supervisor_name.'</td>
							<td align="center" width="18%">Title:</td>
							<td align="center" width="32%" style="border-bottom:1px solid black">'.$row->supervisor_position.'</td>
						</tr>
						<tr>
							<td align="left" width="18%">&nbsp;&nbsp;Contact Numbers: </td>
							<td align="center" width="32%" style="border-bottom:1px solid black">'.$row->supervisor_contact.'</td>
							<td align="center" width="18%">&nbsp;</td>
							<td align="center" width="32%">&nbsp;</td>
						</tr>						
					</tbody>
				</table>
				<table border="0" cellpadding="30" cellspacing="0" style="border-collapse: collapse;" width="100%">
					<tbody>
						<tr>
							<td align="left">How do he/she support and help you with your responsibilities/ How would you rate him/her as your supervisor?</td>
						</tr>																		
					</tbody>
				</table>				
				<table border="1" cellpadding="10" cellspacing="0" style="border-collapse: collapse;" width="100%">
					<tbody>
						<tr>
							<td align="left" style="padding:3px 0;margin:0">'.$row->supervisor_rate.'</td>
						</tr>
					</tbody>
				</table>
				<table border="0" cellpadding="5" cellspacing="0" style="border-collapse: collapse;" width="100%">
					<tbody>
						<tr>
							<td align="center" style="border-bottom:1px dotted black">&nbsp;</td>
						</tr>																		
					</tbody>
				</table><br />';
			}		
		}
		else{
			for ($i=0; $i < 6; $i++) { 
				$eh_result_html .= '<tr>
					<td align="left" style="padding:3px 0;margin:0">&nbsp;</td>
					<td align="left" style="padding:3px 0;margin:0">&nbsp;</td>				
				</tr>';
			}
				$eh_result_html .= '</tbody>
						</table>';			
		}

		return $eh_result_html;
	}

	function get_character_ref($applicant_id){
		$cr_result_html = '<table border="1" cellpadding="10" cellspacing="0" style="border-collapse: collapse;" width="100%">
					<tbody>
						<tr>
							<td align="center" style="padding:3px 0;margin:0" width="35%">Name <br /><i>(Last Name, First Name, M.I.)</i></td>
							<td align="center" style="padding:3px 0;margin:0" width="30%">Company/ Designation</td>
							<td align="center" style="padding:3px 0;margin:0" width="15%">Contact Number</td>
							<td align="center" style="padding:3px 0;margin:0" width="20%">Address</td>
						</tr>';
		$cr_result = $this->db->get_where('recruitment_applicant_references',array("applicant_id"=>$applicant_id));

		if ($cr_result && $cr_result->num_rows() > 0){
			foreach ($cr_result->result() as $row) {
				$cr_result_html .= '<tr>
					<td align="left" style="padding:3px 0;margin:0">'.$row->name.'</td>
					<td align="left" style="padding:3px 0;margin:0">'.$row->company_name.' / '.$row->position.'</td>
					<td align="left" style="padding:3px 0;margin:0">'.$row->telephone.'</td>
					<td align="left" style="padding:3px 0;margin:0">'.$row->address.'</td>
				</tr>';
			}
		}
		else{
			for ($i=0; $i < 6; $i++) { 
				$cr_result_html .= '<tr>
					<td align="left" style="padding:3px 0;margin:0">&nbsp;</td>
					<td align="left" style="padding:3px 0;margin:0">&nbsp;</td>
					<td align="left" style="padding:3px 0;margin:0">&nbsp;</td>
					<td align="left" style="padding:3px 0;margin:0">&nbsp;</td>								
				</tr>';
			}
		}

		$cr_result_html .= '</tbody>
				</table>';

		return $cr_result_html;
	}

	function get_referral($applicant_id){
		$referral_html = '<table border="1" cellpadding="10" cellspacing="0" style="border-collapse: collapse;" width="100%">
					<tbody>
						<tr>
							<td align="center" style="padding:3px 0;margin:0" width="35%">Name <br /><i>(Last Name, First Name, M.I.)</i></td>
							<td align="center" style="padding:3px 0;margin:0" width="30%">Position Applied for</td>
							<td align="center" style="padding:3px 0;margin:0" width="15%">Contact Number</td>
							<td align="center" style="padding:3px 0;margin:0" width="20%">E-mail</td>
						</tr>';
		$referral_result = $this->db->get_where('recruitment_applicant_referral',array("applicant_id"=>$applicant_id));

		if ($referral_result && $referral_result->num_rows() > 0){
			foreach ($referral_result->result() as $row) {
				$referral_html .= '<tr>
					<td align="left" style="padding:3px 0;margin:0">'.$row->name.'</td>
					<td align="left" style="padding:3px 0;margin:0">'.$row->position.'</td>
					<td align="left" style="padding:3px 0;margin:0">'.$row->contact_no.'</td>
					<td align="left" style="padding:3px 0;margin:0">'.$row->email.'</td>
				</tr>';
			}
		}
		else{
			for ($i=0; $i < 6; $i++) { 
				$referral_html .= '<tr>
					<td align="left" style="padding:3px 0;margin:0">&nbsp;</td>
					<td align="left" style="padding:3px 0;margin:0">&nbsp;</td>
					<td align="left" style="padding:3px 0;margin:0">&nbsp;</td>
					<td align="left" style="padding:3px 0;margin:0">&nbsp;</td>		
				</tr>';
			}
		}

		$referral_html .= '</tbody>
				</table>';

		return $referral_html;
	}

	function get_skills($applicant_id){
		$skills_result_html = '<table border="1" cellpadding="10" cellspacing="0" style="border-collapse: collapse;" width="100%">
					<tbody>
						<tr>
							<td align="center" style="padding:3px 0;margin:0" width="75%">Skills</td>
							<td align="center" style="padding:3px 0;margin:0" width="25%">Proficiency Level</td>
						</tr>';
		$skills_result = $this->db->get_where('recruitment_applicant_skill',array("applicant_id"=>$applicant_id));

		if ($skills_result && $skills_result->num_rows() > 0){
			foreach ($skills_result->result() as $row) {
				$skills_result_html .= '<tr>
					<td align="left" style="padding:3px 0;margin:0">'.$row->computer_skills.'</td>
					<td align="left" style="padding:3px 0;margin:0">'.$row->proficiency.'</td>
				</tr>';
			}
		}
		else{
			for ($i=0; $i < 6; $i++) { 
				$skills_result_html .= '<tr>
					<td align="left" style="padding:3px 0;margin:0">&nbsp;</td>
					<td align="left" style="padding:3px 0;margin:0">&nbsp;</td>		
				</tr>';
			}
		}

		$skills_result_html .= '</tbody>
				</table>';

		return $skills_result_html;
	}

	function get_affilliation($applicant_id){
		$aff_result_html = '<table border="1" cellpadding="10" cellspacing="0" style="border-collapse: collapse;" width="100%">
					<tbody>
						<tr>
							<td align="center" style="padding:3px 0;margin:0" width="50%">Name of Organization</td>
							<td align="center" style="padding:3px 0;margin:0" width="20%">Position</td>
							<td align="center" style="padding:3px 0;margin:0" width="30%">Dates of Membership</td>
						</tr>';

		$this->db->join('affiliation','recruitment_applicant_affiliates.affiliation_id = affiliation.affiliation_id','left');
		$aff_result = $this->db->get_where('recruitment_applicant_affiliates',array("applicant_id"=>$applicant_id));

		if ($aff_result && $aff_result->num_rows() > 0){
			foreach ($aff_result->result() as $row) {
				$date_joined = (($row->date_joined && $row->date_joined != '0000-00-00') ? date('Y-m-d',strtotime($row->date_joined)) : '');
				$date_resigned = (($row->date_resigned && $row->date_resigned != '0000-00-00') ? date('Y-m-d',strtotime($row->date_resigned)) : '');				
				$date_from = (($row->date_from && $row->date_from != '0000-00-00') ? date('Y-m-d',strtotime($row->date_from)) : '');
				$date_to = (($row->date_to && $row->date_to != '0000-00-00') ? date('Y-m-d',strtotime($row->date_to)) : '');
				$date_from_to = ($date_from != '' ? $$date_from.' ~ '.$date_to : '');

				$aff_result_html .= '<tr>
					<td align="left" style="padding:3px 0;margin:0">'.$row->affiliation.'</td>
					<td align="left" style="padding:3px 0;margin:0">'.$row->position.'</td>
					<td align="left" style="padding:3px 0;margin:0">'.$date_from_to.'</td>
				</tr>';
			}
		}
		else{
			for ($i=0; $i < 6; $i++) { 
				$aff_result_html .= '<tr>
					<td align="left" style="padding:3px 0;margin:0">&nbsp;</td>
					<td align="left" style="padding:3px 0;margin:0">&nbsp;</td>
					<td align="left" style="padding:3px 0;margin:0">&nbsp;</td>							
				</tr>';
			}
		}

		$aff_result_html .= '</tbody>
				</table>';

		return $aff_result_html;
	}

	function get_exam($applicant_id){
		$exam_result_html = '<table border="1" cellpadding="10" cellspacing="0" style="border-collapse: collapse;" width="100%">
					<tbody>
						<tr>
							<td align="center" style="padding:3px 0;margin:0" width="45%">Title</td>
							<td align="center" style="padding:3px 0;margin:0" width="25%">Date Taken / Location</td>
							<td align="center" style="padding:3px 0;margin:0" width="15%">Rating</td>
							<td align="center" style="padding:3px 0;margin:0" width="15%">License No.</td>
						</tr>';

		$this->db->select('*,exam_title.exam_title as exam_title_master,recruitment_applicant_test_profile.exam_title as exam_title_pro');
		$this->db->join('exam_title','recruitment_applicant_test_profile.exam_title_id = exam_title.exam_title_id','left');
		$exam_result = $this->db->get_where('recruitment_applicant_test_profile',array("applicant_id"=>$applicant_id));

		if ($exam_result && $exam_result->num_rows() > 0){
			foreach ($exam_result->result() as $row) {
				$date_taken = (($row->date_taken && $row->date_taken != '0000-00-00') ? date('Y-m-d',strtotime($row->date_taken)) : '');
				$exam_title = ($row->exam_title_master && $row->exam_title_master != '' ? $row->exam_title_master : $row->exam_title_pro);				
				$exam_result_html .= '<tr>
					<td align="left" style="padding:3px 0;margin:0">'.$exam_title.'</td>
					<td align="left" style="padding:3px 0;margin:0">'.$date_taken.' / ' .$row->location.'</td>
					<td align="left" style="padding:3px 0;margin:0">'.$row->score_rating.'</td>
					<td align="left" style="padding:3px 0;margin:0">'.$row->license_no.'</td>
				</tr>';
			}
		}
		else{
			for ($i=0; $i < 6; $i++) { 
				$exam_result_html .= '<tr>
					<td align="left" style="padding:3px 0;margin:0">&nbsp;</td>
					<td align="left" style="padding:3px 0;margin:0">&nbsp;</td>
					<td align="left" style="padding:3px 0;margin:0">&nbsp;</td>
					<td align="left" style="padding:3px 0;margin:0">&nbsp;</td>
				</tr>';
			}
		}

		$exam_result_html .= '</tbody>
				</table>';

		return $exam_result_html;
	}

	function get_applicant() {
		if (!IS_AJAX) {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		} else if ($this->user_access[$this->module_id]['view'] != 1) {
			$response->msg_type = 'error';
			$response->msg 		= 'You do not have access to the selected module.';
		} else {
			$this->db->where('recruitment_applicant.applicant_id', $this->input->post('applicant_id'));
			$this->db->where('recruitment_applicant.deleted', 0);
			$this->db->limit(1);

			$applicant = $this->db->get('recruitment_applicant');

			if (!$applicant || $applicant->num_rows() == 0) {
				$response->msg_type = 'error';
				$response->msg 		= 'Employee not found.';
			} else {
				$response->msg_type = 'success';

				$response->data = $applicant->row_array();
			}			
		}

		$this->load->view('template/ajax', array('json' => $response));
	}

	function _default_grid_actions($module_link = "", $container = "", $row = array()) {

		// set default
		if ($module_link == "")
			$module_link = $this->module_link;
		if ($container == "")
			$container = "jqgridcontainer";

		$actions = '<span class="icon-group">';

		$actions .= '<a class="icon-button icon-16-document-stack position_applied_list" applicant_id="'.$row['applicant_id'].'" module_link="' . $module_link . '" tooltip="Position Applied" href="javascript:void(0)"></a>';

		if ($this->user_access[$this->module_id]['view']) {
			$actions .= '<a class="icon-button icon-16-info" module_link="' . $module_link . '" tooltip="View" href="javascript:void(0)"></a>';
		}

		if ($this->user_access[$this->module_id]['print']) {
			$actions .= '<a class="icon-button icon-16-print print-record" tooltip="Print" href="javascript:void(0)" module_link="' . $module_link . '" ></a>';
		}

		if ($this->user_access[$this->module_id]['edit']) {
			$actions .= '<a class="icon-button icon-16-edit" tooltip="Edit" href="javascript:void(0)" module_link="' . $module_link . '" ></a>';
		}

		if ($this->user_access[$this->module_id]['delete']) {
			if( $row['application_status_id']==1){
				$actions .= '<a class="icon-button icon-16-delete delete-single" tooltip="Delete" container="' . $container . '" module_link="' . $module_link . '" href="javascript:void(0)"></a>';				
			}
		}

		$actions .= '</span>';

		return $actions;
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

        // if ( get_export_options( $this->module_id ) ) {
        //     $buttons .= "<div class='icon-label'><a class='icon-16-export module-export' container='".$container."' module_link='".$module_link."' href='javascript:void(0)'><span>Export</span></a></div>";
        //     $buttons .= "<div class='icon-label'><a class='icon-16-import module-import' container='".$container."' module_link='".$module_link."' href='javascript:void(0)'><span>Import</span></a></div>";
        // }        

   //      if ($this->input->post('from_cs') == 1){
			// $buttons .= "<div class='icon-label'><a class='bak-candidate-schedule' container='".$container."' module_link='candidate_schedule' href='javascript:void(0)'><span>Back to Schedule</span></a></div>";        	
   //      }

        $buttons .= "</div>";
                
		return $buttons;
	}

	function show_related_module() {

		if (IS_AJAX) {
			$data['container'] = $this->module . '-fmlink-container';
			$data['pager']     = $this->module . '-fmlink-pager';
			$data['fmlinkctr'] = $this->input->post('fmlinkctr');

			//set default columnlist
			$this->_set_listview_query();

			//set grid buttons
			$data['jqg_buttons'] = $this->_listview_in_boxy_grid_buttons();
			$data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/js/listview_in_boxy.js"></script>';

			//set load jqgrid loadComplete callback
			$data['jqgrid_loadComplete'] = "";
			$data['other'] = 'candidate';
			if($this->input->post('mrf_id')) $data['mrf_id'] = $this->input->post('mrf_id');

			$this->load->vars($data);
			$boxy = $this->load->view($this->userinfo['rtheme'] . '/' .$this->module_link . "/listview_in_boxy", $data, true);

			$data['html'] = $boxy;
			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
		} else {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
	}

	function get_applicant_data($record_id = 0) {
		if (IS_AJAX) {
			if ($record_id == 0) {
				$record_id = $this->input->post('record_id');
			}

			if ($record_id == '') {
				$data['json'] = 0;
				$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
			}

			$response['type']    = 'error';
			$response['message'] = 'Record does not exist.';

			$record = get_record_detail_array($record_id, true);
			if ($record) {
				$response['type'] = 'success';
				$response['data'] = $record;

				$this->db->select(array('department','division','recruitment_manpower.division_id','recruitment_manpower.department_id', 'recruitment_applicant.aux', 'recruitment_manpower.status_id', 'user_position.company_id', 'user_position.position_id', 'recruitment_candidate_job_offer.date_from', 'user_position.position'));
				$this->db->where('recruitment_manpower_candidate.applicant_id', $record_id);
				$this->db->where_in('recruitment_manpower_candidate.candidate_status_id', array(5,6,12,13,24));
				$this->db->join('recruitment_manpower', 'recruitment_manpower.request_id = recruitment_manpower_candidate.mrf_id', 'left');
				$this->db->join('user_position', 'user_position.position_id = recruitment_manpower.position_id', 'left');
				$this->db->join('user_company_department', 'user_company_department.department_id = recruitment_manpower.department_id', 'left');
				$this->db->join('user_company_division', 'user_company_division.division_id = recruitment_manpower.division_id', 'left');
				$this->db->join('recruitment_candidate_job_offer', 'recruitment_candidate_job_offer.candidate_id = recruitment_manpower_candidate.candidate_id');
				$this->db->join('recruitment_applicant', 'recruitment_manpower_candidate.applicant_id = recruitment_applicant.applicant_id');

				$result = $this->db->get('recruitment_manpower_candidate')->row_array();
				
				$response['data']['aux'] = $result['aux']; 
				$response['data']['position_id']   = $result['position_id'];
				$response['data']['position_name'] = $result['position'];
				$response['data']['company_id']    = $result['company_id'];
				$response['data']['department_id'] = $result['department_id'];
				$response['data']['status_id']     = $result['status_id'];
				$response['data']['employed_date'] = ($result['date_from'] != '0000-00-00') ? $result['date_from'] : '';

				$response['data']['gender']  = $record['sex'];	
				$response['data']['division']  = $result['division'];	
				$response['data']['division_id']  = $result['division_id'];	
				$response['data']['department']  = $result['department'];	


			}

			$data['json'] = $response;

			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
		} else {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
	}

	function verify_applicant() {
		if (!IS_AJAX) {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);			
		} else {
			$response->record_id = 0;
			
			$this->db->where('firstname', $this->input->post('firstname'));
			$this->db->where('middlename', $this->input->post('middlename'));
			$this->db->where('lastname', $this->input->post('lastname'));
			$this->db->where('sex', $this->input->post('sex'));
			$this->db->where('birth_date', date('Y-m-d', strtotime($this->input->post('birth_date'))));
			$this->db->where('deleted', 0);
			
			$result = $this->db->get($this->module_table);

			if ($result->num_rows() > 0) {
				$response->record_id = $result->row()->applicant_id;
			}

			$data['json'] = $response;
			$this->load->view('template/ajax', $data);
		}		
	}

	function download_file($file = false){
		$path = site_url() . 'uploads/' . $this->module_link . '/' . $file;
		header('Content-disposition: attachment; filename='.$file.'');
		header('Content-type: txt/pdf');
		readfile($path);
	}	

	function position_applied_list(){

		$applicant_id = $this->input->post('applicant_id');

		$sql = "SELECT p.position, aa.applied_date, s.application_status
		FROM hr_user_position p
		LEFT JOIN hr_recruitment_applicant_application aa ON aa.position_applied = p.position_id
		LEFT JOIN hr_application_status s ON s.application_status_id = aa.status
		WHERE aa.applicant_id = ".$applicant_id."
		GROUP BY aa.position_applied,aa.status
		ORDER BY aa.applied_date DESC";

		$result = $this->db->query($sql);
		//$response->last_query = $this->db->last_query();

		$sql2 = "SELECT a.firstname, a.lastname FROM hr_recruitment_applicant a WHERE a.applicant_id = ".$applicant_id;
		$result2 = $this->db->query($sql2)->row();

		$data = array(
			'position_list' => $result->result_array(),
			'applicant_name' => $result2->firstname." ".$result2->lastname
		);

		if( $result->num_rows() > 0 ){

			$response->form = $this->load->view( $this->userinfo['rtheme'].'/recruitment/applicants/position_applied_list',$data, true );
			$this->load->view('template/ajax', array('json' => $response));

		}
		else{

			$response->msg = "No applied position found";
	        $response->msg_type = "attention";
	        $data['json'] = $response;

	        $this->load->view('template/ajax', array('json' => $response));

		}

	}

	function simple_applicant_form(){
		$html = '<div style="padding:10px 15px 10px 10px">
					<div class="form-item odd" style="padding-left:20px">
						<label class="label-desc gray" for="date">Applicant Name:<span class="red font-large">*</span></label>
						<div class="text-input-wrap" style="clear:left;margin-left:0">
							<input type="text" class="input-text" value="" id="applicant_name" name="applicant_name">
						</div>
					</div>
					<div class="form-item odd" style="padding-left:20px">
						<label class="label-desc gray" for="date_schedule">Date Scheduled:</label>
						<div class="text-input-wrap" style="clear:left;margin-left:0">
							<input type="text" class="input-text datetimepicker" value="" id="date_schedule" name="date_schedule">
						</div>
					</div>
					<br />
					<div class="form-submit-btn">
			            <div class="icon-label-group">
			                <div class="icon-label">
			                    <a href="javascript:void(0);" id="save_applicant" rel="record-save">
			                        <span>Add Applicant</span>
			                    </a>            
			                </div>
			            </div>
					</div>
				</div>';

		$response->form = $html;
		$this->load->view('template/ajax', array('json' => $response));		
	}

	function simple_save_applicant(){
		$result = $this->db->get_where('recruitment_manpower_candidate',array("applicant_name"=>$this->input->post('applicant_name'),"deleted"=>0));

		if ($result && $result->num_rows() > 0){
			$response->msg = "applicant already exists.";
		    $response->msg_type = "error";
		    $data['json'] = $response;
		}
		else{
			$data = array(
				'mrf_id' => $this->input->post('mrf_id'),
				'applicant_name' => $this->input->post('applicant_name'),
				'date_schedule' => date('Y-m-d h:i:s',strtotime($this->input->post('date_schedule')))
			);

			$result = $this->db->insert('recruitment_manpower_candidate',$data);

			$response->msg = "applicant was successfully added.";
		    $response->msg_type = "success";
		    $data['json'] = $response;
		}

	    $this->load->view('template/ajax', array('json' => $response));				
	}

	function get_applicant_status()
	{
		if (!IS_AJAX) {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);			
		} else {
			
			$html = '<option value="'.$this->input->post('applicant_status_id').'" selected>'.$this->input->post('applicant_status').'</option>';
			$html .= '<option value="6">Black Listed</option>
						<option value="1">New</option>
						<option value="7">Failed</option>';
			$data['html'] = $html;
			$this->load->view('template/ajax', $data);	
		}
	}

	function get_advance_search(){
		$html = '<fieldset id="advance_search_container" style="width:800px">
					<div class="form-multiple-add" style="display: block;">
						<div class="col-2-form">
							<div class="form-item odd">
			                    <label for="position" class="label-desc gray">
			                        Position:
			                    </label>
			                    <div class="select-input-wrap">                        
			                        <select name="position" id="position" multiple="true"><option value="">Select Position</option>';
			                        	$position = $this->db->get_where('user_position',array('deleted' => 0));
			                        	if ($position && $position->num_rows() > 0){
			                        		foreach ($position->result() as $row) {
												$html .= '<option value="'.$row->position_id.'">'.$row->position.'</option>';
			                        		}
			                        	}
			                        $html .= '</select>
			                	</div>
		                	</div>
							<div class="form-item even">
			                    <label for="status" class="label-desc gray">
			                        Status:
			                    </label>
			                    <div class="select-input-wrap">                        
			                        <select name="status" id="status" multiple="true"><option value="">Select Application Status</option>';
			                        	$application_status = $this->db->get_where('application_status',array('deleted' => 0));
			                        	if ($application_status && $application_status->num_rows() > 0){
			                        		foreach ($application_status->result() as $row) {
												$html .= '<option value="'.$row->application_status_id.'">'.$row->application_status.'</option>';
			                        		}
			                        	}
			                        $html .= '</select>
			                	</div>
		                	</div>
							<div class="form-item odd">
			                    <label for="gender" class="label-desc gray">
			                        Gender:
			                    </label>
			                    <div class="text-input-wrap">
									<input type="checkbox" name="male" value="male">Male
									<input type="checkbox" name="female" value="female">Female 
			                	</div>
		                	</div>	
							<div class="form-item even">
			                    <label for="age" class="label-desc gray">
			                        Age:
			                    </label>
			                    <div class="text-input-wrap">                        
			                        <input name="age" id="age" value="">
			                	</div>
		                	</div>
							<div class="form-item odd">
			                    <label for="location" class="label-desc gray">
			                        Location:
			                    </label>
			                    <div class="select-input-wrap">                        
			                        <select name="location" id="location"><option value="">Select Location</option>';
			                        	$location = $this->db->get_where('cities',array('deleted' => 0));
			                        	if ($location && $location->num_rows() > 0){
			                        		foreach ($location->result() as $row) {
												$html .= '<option value="'.$row->city.'">'.$row->city.'</option>';
			                        		}
			                        	}			                        
			                        $html .= '</select>
			                	</div>
		                	</div>			                				                		                	
	                	</div>	                	
					</div>
					<div class="icon-label-group align-left">
						<div style="display: block;" class="icon-label"><a href="javascript:void(0);" class="advance_search">Search</a></div>
						<div style="display: block;margin-left:10px" class="icon-label"><a href="javascript:void(0);" class="close">Close</a></div>
					</div>					
				 </fieldset>';
		$data['html'] = $html;
		$this->load->view('template/ajax', $data);			
	}
}

/* End of file */
/* Location: system/application */