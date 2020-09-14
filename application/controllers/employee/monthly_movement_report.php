<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Monthly_movement_report extends MY_Controller
{
	function __construct()
    {
        parent::__construct();

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format	
		
		$this->listview_title = 'Monthly Movement Reports';
		$this->listview_description = '';
		$this->jqgrid_title = "";
		$this->detailview_title = '';
		$this->detailview_description = '';
		$this->editview_title = '';
		$this->editview_description = '';
    }

	// START - default module functions
	// default jqgrid controller method
	function index()
    {
    	// $data['scripts'][] = multiselect_script();
		$data['scripts'][] = jqgrid_listview();	// load jqgrid js and default grid js
		$data['content'] = 'employee/movement/monthly_movement_listview';

		$data['jqgrid'] = 'employees/movement/jqgrid';

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

		// $data['division'] = $this->db->get('user_company_division')->result_array();
		// $data['employee'] = $this->db->get('user')->result_array();
		// $data['company'] = $this->db->get('user_company')->result_array();
		// $data['department'] = $this->db->get('user_company_department')->result_array();

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
		parent::ajax_save();
		
		//additional module save routine here
				
	}
	
	function delete()
	{
		parent::delete();
		
		//additional module delete routine here
	}
	// END - default module functions
	
	// START custom module funtions

	function listview()
	{
        $page = $this->input->post('page');
        $limit = $this->input->post('rows'); // get how many rows we want to have into the grid
        $sidx = $this->input->post('sidx'); // get index row - i.e. user click to sort
        $sord = $this->input->post('sord'); // get the direction        
		
		if($this->input->post('_search') == "true")
			$search = $this->input->post('searchField') == "all" ? $this->_set_search_all_query() : $this->_set_specific_search_query();
		else
			$search = 1;			

		$query_id = '8';

		$this->db->where('export_query_id', $query_id);

		$campaign = "";

		if($this->input->post('campaign'))
			$campaign = $this->input->post('campaign');

		$result = $this->db->get('export_query');
		$export = $result->row();
		$sql = str_replace('{dbprefix}', $this->db->dbprefix, $export->query_string);

		$sql.= " WHERE ";
		if($this->input->post('date_period_start') != "" && $this->input->post('date_period_end') != "" ){
			$sql_string .= "(".$this->db->dbprefix."employee_movement.transfer_effectivity_date >= '".date('Y-m-d', strtotime($this->input->post('date_period_start')))."' AND ";
			$sql_string .= $this->db->dbprefix."employee_movement.transfer_effectivity_date <= '".date('Y-m-d', strtotime($this->input->post('date_period_end')))."') AND ";
		}
		else{
			$sql_string .= "(".$this->db->dbprefix."employee_movement.transfer_effectivity_date >= '".date('Y-m-1')."' AND ";
			$sql_string .= $this->db->dbprefix."employee_movement.transfer_effectivity_date <= '".date('Y-m-d', strtotime('-1 day', strtotime(date('Y-m-d', strtotime('+1 month',strtotime(date('Y-m-1')))))))."') AND ";
		}

		if($this->input->post('campaign') != "")
			$sql_string .= $this->db->dbprefix."user.company_id = '".$campaign = $this->input->post('campaign')."' AND ";
		$sql_string .= $this->db->dbprefix."employee_movement.deleted = 0 AND ";
		$sql_string .= "(".$this->db->dbprefix."employee_movement.status = 3 OR ";
		$sql_string .= $this->db->dbprefix."employee_movement.status = 6)";

		// $sql_string .= " ORDER BY ".$this->db->dbprefix."user.lastname DESC";

		$result  = $this->db->query($sql.$sql_string);

		$qry = "SELECT *
				FROM {dbprefix}employee 
				LEFT JOIN {dbprefix}user
				ON {dbprefix}employee.employee_id = {dbprefix}user.employee_id
				LEFT JOIN {dbprefix}user_company_department
				ON {dbprefix}user.department_id = {dbprefix}user_company_department.department_id
				WHERE {dbprefix}employee.deleted = 0
				AND {dbprefix}user.inactive = 0";
				if($this->input->post('campaign') != "")
					$qry .= " AND {dbprefix}user.company_id = ".$this->input->post('campaign');
				if($this->input->post('date_period_start') != "")
					$qry.= " AND {dbprefix}employee.employed_date BETWEEN '".date('Y-m-d', strtotime($this->input->post('date_period_start')))."' AND '".date('Y-m-d', strtotime($this->input->post('date_period_end')))."'";
				else 
					$qry.= " AND {dbprefix}employee.employed_date BETWEEN '".date('Y-m')."-01' AND '".date('Y-m')."-30'";

		$qry = str_replace('{dbprefix}', $this->db->dbprefix, $qry);

		$hiring = $this->db->query($qry);
		// $this->db->select(''.$this->db->dbprefix('user_company_department').'.department'.' as "Department",CONCAT(' . $this->db->dbprefix . 'user.lastname, ", ",' . $this->db->dbprefix . 'user.firstname) as "Full Name"', false);
		// $this->db->select(''.$this->db->dbprefix. 'user.birth_date'.' as Birthdate,'.$this->db->dbprefix. 'employee.employed_date'.' as "Date Hired",'.$this->db->dbprefix. 'employee.regular_date'.' as "Reg Date"');
		// $this->db->from('user');
		// $this->db->join($this->db->dbprefix('employee'),$this->db->dbprefix('user').'.employee_id = '.$this->db->dbprefix('employee').'.employee_id');
		// $this->db->join($this->db->dbprefix('user_company_department'),$this->db->dbprefix('user').'.department_id = '.$this->db->dbprefix('user_company_department').'.department_id',"left");
		// $this->db->where('user.deleted = 0 AND '.$this->db->dbprefix. 'employee.ecf = 1 AND '.$search);	

        // $result = $this->db->get('employee_movement');   
        
	    // die($this->db->last_query()); 
	    // dbug($this->db->last_query());
	    // return;


		if( $this->db->_error_message() != "" ){
			$response->msg = $this->db->_error_message();
			$response->msg_type = "error";
		}
		else{        
			$total_num_rows = $result->num_rows() + $hiring->num_rows();
	        $total_pages = $total_num_rows > 0 ? ceil($total_num_rows/$limit) : 0;
	        $response->page = $page > $total_pages ? $total_pages : $page;
	        $response->total = $total_pages;
	        $response->records = $total_num_rows;                        

	        $response->msg = "";

			// $this->db->select(''.$this->db->dbprefix('user_company_department').'.department'.' as "Department",CONCAT(' . $this->db->dbprefix . 'user.lastname, ", ",' . $this->db->dbprefix . 'user.firstname) as "Full Name"', false);
			// $this->db->select(''.$this->db->dbprefix. 'user.birth_date'.' as Birthdate,'.$this->db->dbprefix. 'employee.employed_date'.' as "Date Hired",'.$this->db->dbprefix. 'employee.regular_date'.' as "Reg Date"');
			// $this->db->from('user');
			// $this->db->join($this->db->dbprefix('employee'),$this->db->dbprefix('user').'.employee_id = '.$this->db->dbprefix('employee').'.employee_id');
			// $this->db->join($this->db->dbprefix('user_company_department'),$this->db->dbprefix('user').'.department_id = '.$this->db->dbprefix('user_company_department').'.department_id',"left");
			// $this->db->where('user.deleted = 0 AND '.$this->db->dbprefix. 'employee.ecf = 1 AND '.$search);	

	        // if ($this->input->post('sidx')) {
	        // 	if ($this->input->post('sidx') == 'date') 
	        // 	{
	        // 		$sidx = $this->db->dbprefix . 'user.lastname';
		       //      $sord = $this->input->post('sord');
	        // 	}
	        // 	else
	        // 	{	        		
		       //      $sidx = $this->input->post('sidx');
		       //      $sord = $this->input->post('sord');	
	        // 	}
	        //     $this->db->order_by($sidx . ' ' . $sord);
	        // }
	        // else
	        // {
	        // 	$this->db->order_by( $this->db->dbprefix . 'user.lastname ASC');
	        // }

	        $query_id = '8';

			$this->db->where('export_query_id', $query_id);

			$campaign = "";
			$sql = "";
			$sql_string	= "";

			if($this->input->post('campaign'))
				$campaign = $this->input->post('campaign');

			$result = $this->db->get('export_query');
			$export = $result->row();
			$sql = str_replace('{dbprefix}', $this->db->dbprefix, $export->query_string);

			$sql.= " WHERE ";
			// dbug($this->input->post('date_period_start'));
			// exit();
			if($this->input->post('date_period_start') != "" && $this->input->post('date_period_end') != "" ){
				$sql_string .= "(".$this->db->dbprefix."employee_movement.transfer_effectivity_date >= '".date('Y-m-d', strtotime($this->input->post('date_period_start')))."' AND ";
				$sql_string .= $this->db->dbprefix."employee_movement.transfer_effectivity_date <= '".date('Y-m-d', strtotime($this->input->post('date_period_end')))."') AND ";
			}
			else{
				$sql_string .= "(".$this->db->dbprefix."employee_movement.transfer_effectivity_date >= '".date('Y-m-1')."' AND ";
				$sql_string .= $this->db->dbprefix."employee_movement.transfer_effectivity_date <= '".date('Y-m-d', strtotime('-1 day', strtotime(date('Y-m-d', strtotime('+1 month',strtotime(date('Y-m-1')))))))."') AND ";
			}
			
			if($this->input->post('campaign') != "")
				$sql_string .= $this->db->dbprefix."user.company_id = '".$campaign = $this->input->post('campaign')."' AND ";
			$sql_string .= $this->db->dbprefix."employee_movement.deleted = 0 AND ";
			$sql_string .= "(".$this->db->dbprefix."employee_movement.status = 3 OR ";
			$sql_string .= $this->db->dbprefix."employee_movement.status = 6)";
			$sql_string .= " ORDER BY ".$this->db->dbprefix."user.firstname,".$this->db->dbprefix."user.middlename,".$this->db->dbprefix."user.lastname";
			// $sql_string .= " ORDER BY ".$this->db->dbprefix."user.lastname DESC";

	        $start = $limit * $page - $limit;
	        $this->db->limit($limit, $start);     

	        $sql_string .= " LIMIT ".$start.", ".$limit;

	        $result  = $this->db->query($sql.$sql_string);

	        $response->last_query = $this->db->last_query();
	        // $result = $this->db->get();
	        $ctr = 0;
	        foreach ($result->result() as $row) {
	        	$old_data = array();
	        	$changes_data = array();

	        	$name = $row->firstname." ".$row->middlename." ".$row->lastname;

				$response->rows[$ctr]['cell'][0] = $name;
				$response->rows[$ctr]['cell'][1] = date($this->config->item('display_date_format'),strtotime($row->{'created_date'}));
				$response->rows[$ctr]['cell'][2] = $row->{'movement_cause'};
				$response->rows[$ctr]['cell'][3] = $row->{'movement_type'};
				$response->rows[$ctr]['cell'][4] = date($this->config->item('display_date_format'),strtotime($row->{'transfer_effectivity_date'}));
				$response->rows[$ctr]['cell'][5] = $row->{'movement_current_position'};
				$response->rows[$ctr]['cell'][6] = $row->{'movement_new_position'};
				$response->rows[$ctr]['cell'][7] = $row->{'show_dept'};
				$response->rows[$ctr]['cell'][8] = $row->{'new_dept'};
				$response->rows[$ctr]['cell'][9] = $row->{'current_rank_dummy'};
				$response->rows[$ctr]['cell'][10] = $row->{'new_rank'};
				$response->rows[$ctr]['cell'][11] = $row->{'employee_approver_name'};
				$response->rows[$ctr]['cell'][12] = $row->{'position_reporting_to'};
				$response->rows[$ctr]['cell'][13] = $row->{'current_employee_type_dummy'};
				$response->rows[$ctr]['cell'][14] = $row->{'new_employee_type'};
				$response->rows[$ctr]['cell'][15] = $row->{'current_job_level_dummy'};
				$response->rows[$ctr]['cell'][16] = $row->{'new_job_level'};
				$response->rows[$ctr]['cell'][17] = $row->{'current_range_of_rank_dummy'};
				$response->rows[$ctr]['cell'][18] = $row->{'new_rank_range'};
				$response->rows[$ctr]['cell'][19] = $row->{'current_rank_code_dummy'};
				$response->rows[$ctr]['cell'][20] = $row->{'new_rank_code'};
				$response->rows[$ctr]['cell'][21] = $row->{'current_company_dummy'};
				$response->rows[$ctr]['cell'][22] = $row->{'new_company'};
				$response->rows[$ctr]['cell'][23] = $row->{'current_division_dummy'};
				$response->rows[$ctr]['cell'][24] = $row->{'new_division'};
				$response->rows[$ctr]['cell'][25] = $row->{'current_location_dummy'};
				$response->rows[$ctr]['cell'][26] = $row->{'new_location'};
				$response->rows[$ctr]['cell'][27] = $row->{'current_segment_1_dummy'};
				$response->rows[$ctr]['cell'][28] = $row->{'new_segment_1'};
				$response->rows[$ctr]['cell'][29] = $row->{'current_segment_2_dummy'};
				$response->rows[$ctr]['cell'][30] = $row->{'new_segment_2'};
				$response->rows[$ctr]['cell'][31] = $row->{'current_role'};
				$response->rows[$ctr]['cell'][32] = $row->{'new_role'};
				$response->rows[$ctr]['cell'][33] = $row->{'current_sick_leave'};
				$response->rows[$ctr]['cell'][34] = $row->{'sick_leave'};
				$response->rows[$ctr]['cell'][35] = $row->{'current_vacation_leave'};
				$response->rows[$ctr]['cell'][36] = $row->{'vacation_leave'};
				$response->rows[$ctr]['cell'][37] = $row->{'current_emergency_leave'};
				$response->rows[$ctr]['cell'][38] = $row->{'emergency_leave'};

	            $ctr++;
	        }

	        // for hiring date
		$qry = "SELECT {dbprefix}employee.*, {dbprefix}user.*, position.position, {dbprefix}user_company_department.department,
				rank.job_rank, employee_type.employee_type, job_level.job_level, rank_range.job_rank_range, rank_code.job_rank_code,
				company.company, location.location, segment_1.segment_1, segment_2.segment_2, role.role, division.division, movement.current_sick_leave,
				movement.current_vacation_leave, movement.current_emergency_leave 

				FROM {dbprefix}employee 
				LEFT JOIN {dbprefix}user ON {dbprefix}employee.employee_id = {dbprefix}user.employee_id
				LEFT JOIN {dbprefix}user_company_department ON {dbprefix}user.department_id = {dbprefix}user_company_department.department_id
				LEFT JOIN {dbprefix}user_position position ON position.position_id = {dbprefix}user.position_id
				LEFT JOIN {dbprefix}user_rank rank ON  {dbprefix}employee.rank_id = rank.job_rank_id
				LEFT JOIN {dbprefix}employee_type employee_type ON  {dbprefix}employee.employee_type = employee_type.employee_type_id
				LEFT JOIN {dbprefix}user_job_level job_level ON  {dbprefix}employee.job_level = job_level.job_level_id
				
				LEFT JOIN {dbprefix}user_rank_range rank_range ON  {dbprefix}employee.range_of_rank = rank_range.job_rank_range_id
				LEFT JOIN {dbprefix}user_rank_code rank_code ON  {dbprefix}employee.rank_code = rank_code.job_rank_code_id
				LEFT JOIN {dbprefix}user_company company ON  {dbprefix}user.company_id = company.company_id
				LEFT JOIN {dbprefix}user_company_division division ON  {dbprefix}user.division_id = division.division_id
				LEFT JOIN {dbprefix}user_location location ON  {dbprefix}employee.location_id = location.location_id
				LEFT JOIN {dbprefix}user_company_segment_1 segment_1 ON  {dbprefix}user.segment_1_id = segment_1.segment_1_id
				LEFT JOIN {dbprefix}user_company_segment_2 segment_2 ON  {dbprefix}user.segment_2_id = segment_2.segment_2_id
				LEFT JOIN {dbprefix}role role ON  {dbprefix}user.role_id = role.role_id
				LEFT JOIN {dbprefix}employee_movement movement ON  movement.employee_id = {dbprefix}employee.employee_id

				WHERE {dbprefix}employee.deleted = 0
				AND {dbprefix}user.inactive = 0";
				if($this->input->post('campaign') != "")
					$qry .= " AND {dbprefix}user.company_id = ".$this->input->post('campaign');
				if($this->input->post('date_period_start') != "")
					$qry.= " AND {dbprefix}employee.employed_date BETWEEN '".date('Y-m-d', strtotime($this->input->post('date_period_start')))."' AND '".date('Y-m-d', strtotime($this->input->post('date_period_end')))."'";
				else 
					$qry.= " AND {dbprefix}employee.employed_date BETWEEN '".date('Y-m-1')."' AND '".date('Y-m-d', strtotime('-1 day', strtotime(date('Y-m-d', strtotime('+1 month',strtotime(date('Y-m-1')))))))."'";
				//$qry .= " ORDER BY {dbprefix}employee.employed_date ASC";
				$qry .= " ORDER BY ".$this->db->dbprefix."user.firstname,".$this->db->dbprefix."user.middlename,".$this->db->dbprefix."user.lastname";

				$qry = str_replace('{dbprefix}', $this->db->dbprefix, $qry);


			$hiring = $this->db->query($qry);

			if($hiring && $hiring->num_rows() > 0)
			{
		        foreach($hiring->result() as $employed_date)
				{

					$name = $employed_date->firstname." ".$employed_date->middlename." ".$employed_date->lastname;

					$response->rows[$ctr]['cell'][0] = $name;
					$response->rows[$ctr]['cell'][1] = date($this->config->item('display_date_format'),strtotime($employed_date->created_date));
					$response->rows[$ctr]['cell'][2] = "";
					$response->rows[$ctr]['cell'][3] = "Hiring";
					$response->rows[$ctr]['cell'][4] = date($this->config->item('display_date_format'),strtotime($employed_date->employed_date));
					$response->rows[$ctr]['cell'][5] = "";
					$response->rows[$ctr]['cell'][6] = $employed_date->position;
					$response->rows[$ctr]['cell'][7] = "";
					$response->rows[$ctr]['cell'][8] = $employed_date->department;
					$response->rows[$ctr]['cell'][9] = "";
					$response->rows[$ctr]['cell'][10] = $employed_date->job_rank;
					$response->rows[$ctr]['cell'][11] = "";
					$response->rows[$ctr]['cell'][12] = "";
					$response->rows[$ctr]['cell'][13] = "";
					$response->rows[$ctr]['cell'][14] = $employed_date->employee_type;
					$response->rows[$ctr]['cell'][15] = "";
					$response->rows[$ctr]['cell'][16] = $employed_date->description;
					$response->rows[$ctr]['cell'][17] = "";
					$response->rows[$ctr]['cell'][18] = $employed_date->job_rank_range;
					$response->rows[$ctr]['cell'][19] = "";
					$response->rows[$ctr]['cell'][20] = $employed_date->job_rank_code;
					$response->rows[$ctr]['cell'][21] = "";
					$response->rows[$ctr]['cell'][22] = $employed_date->company;
					$response->rows[$ctr]['cell'][23] = "";
					$response->rows[$ctr]['cell'][24] = $employed_date->division;
					$response->rows[$ctr]['cell'][25] = "";
					$response->rows[$ctr]['cell'][26] = $employed_date->location;
					$response->rows[$ctr]['cell'][27] = "";
					$response->rows[$ctr]['cell'][28] = $employed_date->segment_1;
					$response->rows[$ctr]['cell'][29] = "";
					$response->rows[$ctr]['cell'][30] = $employed_date->segment_2;
					$response->rows[$ctr]['cell'][31] = "";
					$response->rows[$ctr]['cell'][32] = $employed_date->role;
					
					$response->rows[$ctr]['cell'][33] = "";
					$response->rows[$ctr]['cell'][34] = $employed_date->current_sick_leave;
					$response->rows[$ctr]['cell'][35] = "";
					$response->rows[$ctr]['cell'][36] = $employed_date->current_vacation_leave;
					$response->rows[$ctr]['cell'][37] = "";
					$response->rows[$ctr]['cell'][38] = $employed_date->current_emergency_leave;

					/*

					$response->rows[$ctr]['cell'][0] = date($this->config->item('display_date_format'), strtotime($employed_date->employed_date));
					$response->rows[$ctr]['cell'][1] = $employed_date->department;
					$response->rows[$ctr]['cell'][2] = 'Hiring';
					$name = $employed_date->firstname." ".$employed_date->middlename." ".$employed_date->lastname;
					$response->rows[$ctr]['cell'][3] = $name;

					$user_pos = $this->db->get_where('user_position', array('position_id' => $employed_date->position_id))->row();
						$response->rows[$ctr]['cell'][4] = $user_pos->position;

					$show_rank = $this->db->get_where('user_rank_code', array('job_rank_code_id' => $employed_date->rank_code))->row();
						$response->rows[$ctr]['cell'][5] = $show_rank->job_rank_code;

					$show_ror = $this->db->get_where('user_rank_range', array('job_rank_range_id' => $employed_date->range_of_rank))->row();
						$response->rows[$ctr]['cell'][6] = $show_ror->job_rank_range;

					*/



					$ctr++;
				}
			}
			// for hiring date
	    }
        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', array('json' => $response));
	}

	function _set_listview_query($listview_id = '', $view_actions = true) {
		//$this->listview_column_names = array('Department', 'Full Name', 'Position', 'Birthdate', 'Date Hired', 'Reg Date', 'Rank Code', 'Rank');
		//$this->listview_column_names = array('Date', 'Department', 'Movement', 'Employee', 'Position', 'Rank Code', 'Range of Rank', 'Changes');

		$this->listview_column_names = array('Employee Name','Date of Entry','Due To','Nature of Movement','Effectivity Date'
			,'From','Change To','From','Change To','From','Change To','Employee Approver','Position Reporting To','From','Change To',
			'From','Change To','From','Change To','From','Change To','From','Change To','From','Change To','From','Change To',
			'From','Change To','From','Change To','From','Change To','From','Change To','From','Change To','From','Change To','Interim Employee');

		$this->listview_columns = array(
					
				array('name' => 'employee_name', 'width' => '180','align' => 'center', 'sortable' => 'false'),
				array('name' => 'date_of_entry', 'width' => '180','align' => 'center', 'sortable' => 'false'),
				array('name' => 'due_to', 'width' => '180','align' => 'center', 'sortable' => 'false'),
				array('name' => 'nature_of_movement', 'width' => '180','align' => 'center', 'sortable' => 'false'),
				array('name' => 'effectivity_date', 'width' => '180','align' => 'center', 'sortable' => 'false'),
				array('name' => 'position_title_current', 'width' => '180','align' => 'center', 'sortable' => 'false'),
				array('name' => 'position_title_change_to', 'width' => '180','align' => 'center', 'sortable' => 'false'),
				array('name' => 'department_current', 'width' => '180','align' => 'center', 'sortable' => 'false'),
				array('name' => 'department_change_to', 'width' => '180','align' => 'center', 'sortable' => 'false'),
				array('name' => 'rank_current', 'width' => '180','align' => 'center', 'sortable' => 'false' ),
				array('name' => 'rank_change_to', 'width' => '180','align' => 'center', 'sortable' => 'false'),
				array('name' => 'employee_approver', 'width' => '180','align' => 'center', 'sortable' => 'false'),
				array('name' => 'position_reporting_to', 'width' => '180','align' => 'center', 'sortable' => 'false'),
				array('name' => 'employee_type_current', 'width' => '180','align' => 'center', 'sortable' => 'false'),
				array('name' => 'employee_type_change_to', 'width' => '180','align' => 'center', 'sortable' => 'false'),
				array('name' => 'job_level_current', 'width' => '180','align' => 'center', 'sortable' => 'false'),
				array('name' => 'job_level_change_to', 'width' => '180','align' => 'center', 'sortable' => 'false'),
				array('name' => 'range_of_rank_current', 'width' => '180','align' => 'center', 'sortable' => 'false'),
				array('name' => 'range_of_rank_change_to', 'width' => '180','align' => 'center', 'sortable' => 'false'),
				array('name' => 'rank_code_current', 'width' => '180','align' => 'center', 'sortable' => 'false'),
				array('name' => 'rank_code_change_to', 'width' => '180','align' => 'center', 'sortable' => 'false'),
				array('name' => 'company_current', 'width' => '180','align' => 'center', 'sortable' => 'false'),
				array('name' => 'company_change_to', 'width' => '180','align' => 'center', 'sortable' => 'false'),
				array('name' => 'division_current', 'width' => '180','align' => 'center', 'sortable' => 'false'),
				array('name' => 'division_change_to', 'width' => '180','align' => 'center', 'sortable' => 'false'),
				array('name' => 'location_current', 'width' => '180','align' => 'center', 'sortable' => 'false'),
				array('name' => 'location_change_to', 'width' => '180','align' => 'center', 'sortable' => 'false'),
				array('name' => 'profit_or_non_profit_center_current', 'width' => '180','align' => 'center', 'sortable' => 'false'),
				array('name' => 'profit_or_non_profit_center_change_to', 'width' => '180','align' => 'center', 'sortable' => 'false'),
				array('name' => 'organization_current', 'width' => '180','align' => 'center', 'sortable' => 'false'),
				array('name' => 'organization_change_to', 'width' => '180','align' => 'center', 'sortable' => 'false'),
				array('name' => 'hris_role_current', 'width' => '180','align' => 'center', 'sortable' => 'false'),
				array('name' => 'hris_role_change_to', 'width' => '180','align' => 'center', 'sortable' => 'false'),
				array('name' => 'no_of_sick_leaves_current', 'width' => '180','align' => 'center', 'sortable' => 'false'),
				array('name' => 'no_of_sick_leaves_change_to', 'width' => '180','align' => 'center', 'sortable' => 'false'),
				array('name' => 'no_of_vacation_leaves_current', 'width' => '180','align' => 'center', 'sortable' => 'false'),
				array('name' => 'no_of_vacation_leaves_change_to', 'width' => '180','align' => 'center', 'sortable' => 'false'),
				array('name' => 'no_of_emergency_leaves_current', 'width' => '180','align' => 'center', 'sortable' => 'false'),
				array('name' => 'no_of_emergency_leaves_change_to', 'width' => '180','align' => 'center', 'sortable' => 'false'),
				array('name' => 'interim_employee', 'width' => '180','align' => 'center', 'sortable' => 'false')
			); 

		/*
		$this->listview_columns = array(
					
				array('name' => 'Date', 'width' => '180','align' => 'center'),
				array('name' => 'Department', 'width' => '180','align' => 'center'),
				array('name' => 'Movement', 'width' => '180','align' => 'center'),
				array('name' => 'Employee', 'width' => '180','align' => 'center'),
				array('name' => 'Position', 'width' => '180','align' => 'center'),
				array('name' => 'Rank Code', 'width' => '180','align' => 'center'),
				array('name' => 'Range of Rank', 'width' => '180','align' => 'center'),
				array('name' => 'Changes', 'width' => '180','align' => 'center')
				// array('name' => 'position'),
				// array('name' => 'birth_date'),
				// array('name' => 'employed_date'),
				// array('name' => 'regular_date'),
				// array('name' => 'job_rank'),
				// array('name' => 'job_rank_code')
				//array('name' => 'workshift')
			);   
		*/                                  
    }

	// function _set_search_all_query()
	// {
	// 	$value =  $this->input->post('searchString');
	// 	$search_string = array();
	// 	foreach($this->search_columns as $search)
	// 	{
	// 		$column = strtolower( $search['column'] );
	// 		if(sizeof(explode(' as ', $column)) > 1){
	// 			$as_part = explode(' as ', $column);
	// 			$search['column'] = strtolower( trim( $as_part[0] ) );
	// 		}
	// 		$search_string[] = $search['column'] . ' LIKE "%'. $value .'%"' ;
	// 	}
	// 	$search_string[] = $this->db->dbprefix .'user.firstname LIKE "%' . $value . '%"';
	// 	$search_string[] = $this->db->dbprefix .'user.lastname LIKE "%' . $value . '%"';
	// 	$search_string[] = $this->db->dbprefix .'user_company_department.department LIKE "%' . $value . '%"';
	// 	$search_string = '('. implode(' OR ', $search_string) .')';
	// 	return $search_string;
	// }

	function get_biometrics_report_filter(){
		$html = '';
		switch ($this->input->post('category_id')) {
		    case 0:
                $html .= '';	
		        break;
		    case 1:
				$company = $this->db->get('user_company')->result_array();		
                $html .= '<select id="company" multiple="multiple" class="multi-select" style="width:400px;" name="company[]">';
                    foreach($company as $company_record){
                        $html .= '<option value="'.$company_record["company_id"].'">'.$company_record["company"].'</option>';
                    }
                $html .= '</select>';	
		        break;
		    case 2:
				$division = $this->db->get('user_company_division')->result_array();		
                $html .= '<select id="division" multiple="multiple" class="multi-select" style="width:400px;" name="division[]">';
                    foreach($division as $division_record){
                        $html .= '<option value="'.$division_record["division_id"].'">'.$division_record["division"].'</option>';
                    }
                $html .= '</select>';	
		        break;
		    case 3:
				$department = $this->db->get('user_company_department')->result_array();		
                $html .= '<select id="department" multiple="multiple" class="multi-select" style="width:400px;" name="department[]">';
                    foreach($department as $department_record){
                        $html .= '<option value="'.$department_record["department_id"].'">'.$department_record["department"].'</option>';
                    }
                $html .= '</select>';				
		        break;		        
		    case 4:
				$employee = $this->db->get('user')->result_array();		
                $html .= '<select id="employee" multiple="multiple" class="multi-select" style="width:400px;" name="employee[]">';
                    foreach($employee as $employee_record){
                    	if ($employee_record["firstname"] != "Super Admin"){
                        	$html .= '<option value="'.$employee_record["employee_id"].'">'.$employee_record["firstname"].'&nbsp;'.$employee_record["lastname"].'</option>';
                    	}
                    }
                $html .= '</select>';	
		        break;		        		        
		}	

        $data['html'] = $html;
        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);			
	}
	

	function export() {	
		//campaign is company
		$query_id = '8';

		if (!$query_id || $query_id < 0) {
			show_error('No ID specified');
		}

		$this->db->where('export_query_id', $query_id);

		$campaign = "";

		if($this->input->post('campaign'))
			$campaign = $this->input->post('campaign');

		$result = $this->db->get('export_query');
		$export = $result->row();
		$sql = str_replace('{dbprefix}', $this->db->dbprefix, $export->query_string);

		$sql.= " WHERE ";

		if($this->input->post('date_period_start') != "" && $this->input->post('date_period_end') != "" ){
			$sql_string .= "(".$this->db->dbprefix."employee_movement.transfer_effectivity_date >= '".date('Y-m-d', strtotime($this->input->post('date_period_start')))."' AND ";
			$sql_string .= $this->db->dbprefix."employee_movement.transfer_effectivity_date <= '".date('Y-m-d', strtotime($this->input->post('date_period_end')))."') AND ";
		}
		else{
			$sql_string .= "(".$this->db->dbprefix."employee_movement.transfer_effectivity_date >= '".date('Y-m-1')."' AND ";
			$sql_string .= $this->db->dbprefix."employee_movement.transfer_effectivity_date <= '".date('Y-m-d', strtotime('-1 day', strtotime(date('Y-m-d', strtotime('+1 month',strtotime(date('Y-m-1')))))))."') AND ";
		}

		$sql_string .= $this->db->dbprefix."user.company_id = '".$this->input->post('campaign')."' AND ";
		$sql_string .= $this->db->dbprefix."employee_movement.deleted = 0 AND ";
		$sql_string .= "(".$this->db->dbprefix."employee_movement.status = 3 OR ";
		$sql_string .= $this->db->dbprefix."employee_movement.status = 6)";

		$sql_string .= " ORDER BY ".$this->db->dbprefix."user.lastname DESC";

		$query  = $this->db->query($sql.$sql_string);
		$fields = $query->list_fields();

		/*
		$qry = "SELECT *
				FROM {dbprefix}employee 
				LEFT JOIN {dbprefix}user
				ON {dbprefix}employee.employee_id = {dbprefix}user.employee_id
				LEFT JOIN {dbprefix}user_company_department
				ON {dbprefix}user.department_id = {dbprefix}user_company_department.department_id
				WHERE {dbprefix}employee.deleted = 0
				AND {dbprefix}user.inactive = 0";
		*/


				$qry = "SELECT {dbprefix}employee.*, {dbprefix}user.*, position.position, {dbprefix}user_company_department.department,
				rank.job_rank, employee_type.employee_type, job_level.job_level, rank_range.job_rank_range, rank_code.job_rank_code,
				company.company, location.location, segment_1.segment_1, segment_2.segment_2, role.role, division.division, movement.current_sick_leave,
				movement.current_vacation_leave, movement.current_emergency_leave 

				FROM {dbprefix}employee 
				LEFT JOIN {dbprefix}user ON {dbprefix}employee.employee_id = {dbprefix}user.employee_id
				LEFT JOIN {dbprefix}user_company_department ON {dbprefix}user.department_id = {dbprefix}user_company_department.department_id
				LEFT JOIN {dbprefix}user_position position ON position.position_id = {dbprefix}user.position_id
				LEFT JOIN {dbprefix}user_rank rank ON  {dbprefix}employee.rank_id = rank.job_rank_id
				LEFT JOIN {dbprefix}employee_type employee_type ON  {dbprefix}employee.employee_type = employee_type.employee_type_id
				LEFT JOIN {dbprefix}user_job_level job_level ON  {dbprefix}employee.job_level = job_level.job_level_id
				

				LEFT JOIN {dbprefix}user_rank_range rank_range ON  {dbprefix}employee.range_of_rank = rank_range.job_rank_range_id
				LEFT JOIN {dbprefix}user_rank_code rank_code ON  {dbprefix}employee.rank_code = rank_code.job_rank_code_id
				LEFT JOIN {dbprefix}user_company company ON  {dbprefix}user.company_id = company.company_id
				LEFT JOIN {dbprefix}user_company_division division ON  {dbprefix}user.division_id = division.division_id
				LEFT JOIN {dbprefix}user_location location ON  {dbprefix}employee.location_id = location.location_id
				LEFT JOIN {dbprefix}user_company_segment_1 segment_1 ON  {dbprefix}user.segment_1_id = segment_1.segment_1_id
				LEFT JOIN {dbprefix}user_company_segment_2 segment_2 ON  {dbprefix}user.segment_2_id = segment_2.segment_2_id
				LEFT JOIN {dbprefix}role role ON  {dbprefix}user.role_id = role.role_id
				LEFT JOIN {dbprefix}employee_movement movement ON  movement.employee_id = {dbprefix}employee.employee_id

				WHERE {dbprefix}employee.deleted = 0
				AND {dbprefix}user.inactive = 0";


				if($this->input->post('campaign') != "")
					$qry .= " AND {dbprefix}user.company_id = ".$this->input->post('campaign');
				if( $this->input->post('date_period_start') != "" && $this->input->post('date_period_end') != "" )
					$qry.= " AND {dbprefix}employee.employed_date BETWEEN '".date('Y-m-d', strtotime($this->input->post('date_period_start')))."' AND '".date('Y-m-d', strtotime($this->input->post('date_period_end')))."'";
				else 
					$qry.= " AND {dbprefix}employee.employed_date BETWEEN '".date('Y-m-1')."' AND '".date('Y-m-d', strtotime('-1 day', strtotime(date('Y-m-d', strtotime('+1 month',strtotime(date('Y-m-1')))))))."'";

		$qry = str_replace('{dbprefix}', $this->db->dbprefix, $qry);

		$hiring = $this->db->query($qry);

		$this->_fields = $fields;
		$this->_export = $export;
		$this->_query  = $query;
		$this->_company = $this->input->post('campaign');
		$this->_hiring = $hiring;
		$this->_excel_export();
	}
	
	private function _excel_export()
	{

		$query  = $this->_query;
		$fields = $this->_fields;
		$export = $this->_export;
		$hiring = $this->_hiring;
		$company_code = $this->_company;

		$this->load->library('PHPExcel');		
		$this->load->library('PHPExcel/IOFactory');

		$objPHPExcel = new PHPExcel();

		$objPHPExcel->getProperties()->setTitle($query->description)
		            ->setDescription($query->description);
		               
		// Assign cell values
		$objPHPExcel->setActiveSheetIndex(0);
		$activeSheet = $objPHPExcel->getActiveSheet();

		$query = $query->result();
		$hiring = $hiring->result();

		//header
		$alphabet  = range('A','Z');
		// foreach($alphabet as $letter)
		// 	array_push($alphabet, 'A'.$letter);
		// foreach($alphabet as $letter)
		// 	array_push($alphabet, 'B'.$letter);

		$alpha_ctr = 0;
		$sub_ctr   = 0;

		//Default column width
		$objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(15);
		// $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
		// $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(15);


		//Initialize style
		$styleArray = array(
			'font' => array(
				'bold' => true,
			),			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
			)
		);

		$cellarray = array(
			'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb'=>'CCC'),
            ),

			'font' => array(
				'bold' => true,
			),	

			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
			)
		);

		$totalnumstyle = array(
			'font' => array(
				'bold' => true,
				),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
				)
			);

		$totaltitlestyle = array(
			'font' => array(
				'bold' => true,
				),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
				)
			);

		$headerstyle = array(
			'font' => array(
				'bold' => true,
				),
			'borders' => array(
			    'allborders' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THIN
			    )
			  ),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
				)
			);


		$top_ctr = 0;

		$activeSheet->setCellValueExplicit('F4', 'Position Title', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('H4', 'Department', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('J4', 'Rank', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('N4', 'Employee Type', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('P4', 'Job Level', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('R4', 'Range of Rank', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('T4', 'Rank Code', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('V4', 'Company', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('X4', 'Division', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('Z4', 'Location', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('AB4', 'Profit or Non - Profit Center', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('AD4', 'Organization', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('AF4', 'HRIS Role', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('AH4', 'No. of Sick Leaves', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('AJ4', 'No. of Vacation Leaves', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('AL4', 'No. of Emergency Leaves', PHPExcel_Cell_DataType::TYPE_STRING);


		$activeSheet->setCellValueExplicit('A4', 'Employee Name', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('B4', 'Date of Entry', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('C4', 'Due To', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('D4', 'Nature of Movement', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('E4', 'Effectivity Date', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('F5', 'From', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('G5', 'Change To', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('H5', 'From', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('I5', 'Change To', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('J5', 'From', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('K5', 'Change To', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('L4', 'Employee Approver', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('M4', 'Position Reporting To', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('N5', 'From', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('O5', 'Change To', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('P5', 'From', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('Q5', 'Change To', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('R5', 'From', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('S5', 'Change To', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('T5', 'From', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('U5', 'Change To', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('V5', 'From', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('W5', 'Change To', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('X5', 'From', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('Y5', 'Change To', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('Z5', 'From', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('AA5', 'Change To', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('AB5', 'From', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('AC5', 'Change To', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('AD5', 'From', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('AE5', 'Change To', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('AF5', 'From', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('AG5', 'Change To', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('AH5', 'From', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('AI5', 'Change To', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('AJ5', 'From', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('AK5', 'Change To', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('AL5', 'From', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('AM5', 'Change To', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('AN4', 'Interim Employee', PHPExcel_Cell_DataType::TYPE_STRING);

		$objPHPExcel->getActiveSheet()->getStyle('A4')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('B4')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('C4')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('D4')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('E4')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('F4')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('G4')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('H4')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('I4')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('J4')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('K4')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('L4')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('M4')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('N4')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('O4')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('P4')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('Q4')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('R4')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('S4')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('T4')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('U4')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('V4')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('W4')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('X4')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('Y4')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('Z4')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('AA4')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('AB4')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('AC4')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('AD4')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('AE4')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('AF4')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('AG4')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('AH4')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('AI4')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('AJ4')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('AK4')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('AL4')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('AM4')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('AN4')->applyFromArray($headerstyle);

		$objPHPExcel->getActiveSheet()->getStyle('A5')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('B5')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('C5')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('D5')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('E5')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('F5')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('G5')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('H5')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('I5')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('J5')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('K5')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('L5')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('M5')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('N5')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('O5')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('P5')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('Q5')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('R5')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('S5')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('T5')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('U5')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('V5')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('W5')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('X5')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('Y5')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('Z5')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('AA5')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('AB5')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('AC5')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('AD5')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('AE5')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('AF5')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('AG5')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('AH5')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('AI5')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('AJ5')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('AK5')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('AL5')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('AM5')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('AN5')->applyFromArray($headerstyle);

		$objPHPExcel->getActiveSheet()->mergeCells('F4:G4');
		$objPHPExcel->getActiveSheet()->mergeCells('H4:I4');
		$objPHPExcel->getActiveSheet()->mergeCells('J4:K4');
		$objPHPExcel->getActiveSheet()->mergeCells('N4:O4');
		$objPHPExcel->getActiveSheet()->mergeCells('P4:Q4');
		$objPHPExcel->getActiveSheet()->mergeCells('R4:S4');
		$objPHPExcel->getActiveSheet()->mergeCells('T4:U4');
		$objPHPExcel->getActiveSheet()->mergeCells('V4:W4');
		$objPHPExcel->getActiveSheet()->mergeCells('X4:Y4');
		$objPHPExcel->getActiveSheet()->mergeCells('Z4:AA4');
		$objPHPExcel->getActiveSheet()->mergeCells('AB4:AC4');
		$objPHPExcel->getActiveSheet()->mergeCells('AD4:AE4');
		$objPHPExcel->getActiveSheet()->mergeCells('AF4:AG4');
		$objPHPExcel->getActiveSheet()->mergeCells('AH4:AI4');
		$objPHPExcel->getActiveSheet()->mergeCells('AJ4:AK4');
		$objPHPExcel->getActiveSheet()->mergeCells('AL4:AM4');

		$objPHPExcel->getActiveSheet()->mergeCells('A4:A5');
		$objPHPExcel->getActiveSheet()->mergeCells('B4:B5');
		$objPHPExcel->getActiveSheet()->mergeCells('C4:C5');
		$objPHPExcel->getActiveSheet()->mergeCells('D4:D5');
		$objPHPExcel->getActiveSheet()->mergeCells('E4:E5');
		$objPHPExcel->getActiveSheet()->mergeCells('L4:L5');
		$objPHPExcel->getActiveSheet()->mergeCells('M4:M5');
		$objPHPExcel->getActiveSheet()->mergeCells('AN4:AN5');


		// echo $this->_company."|"; exit();

		$company_code = $this->db->get_where('user_company', array('company_id' => $company_code))->row();
		$code = $company_code->company_code;

		if( $this->input->post('date_period_start') != "" && $this->input->post('date_period_end') != "" ){
			$activeSheet->setCellValueExplicit('A1', $code.' Movements For the period covering from '.date('F d,Y',strtotime($this->input->post('date_period_start'))).' - '.date('F d,Y',strtotime($this->input->post('date_period_end'))), PHPExcel_Cell_DataType::TYPE_STRING); 
		}
		else{
			$activeSheet->setCellValueExplicit('A1', $code.' Movements For the period covering from '.date('F 1,Y').' - '.date('F d, Y', strtotime('-1 day', strtotime(date('Y-m-d', strtotime('+1 month',strtotime(date('Y-m-1'))))))), PHPExcel_Cell_DataType::TYPE_STRING); 
		}

		$line_ctr = 6;
		$name = "";

		foreach($query as $movement) {
			$changes = "";
			$current = "";
			$changes_data = array();
			$old_data = array();

			$name = $movement->firstname." ".$movement->middlename." ".$movement->lastname;

			$activeSheet->setCellValueExplicit('A'.$line_ctr, $name, PHPExcel_Cell_DataType::TYPE_STRING); 
			$activeSheet->setCellValueExplicit('B'.$line_ctr, date($this->config->item('display_date_format'),strtotime($movement->created_date)), PHPExcel_Cell_DataType::TYPE_STRING); 
			$activeSheet->setCellValueExplicit('C'.$line_ctr, $movement->movement_cause, PHPExcel_Cell_DataType::TYPE_STRING); 
			$activeSheet->setCellValueExplicit('D'.$line_ctr, $movement->movement_type, PHPExcel_Cell_DataType::TYPE_STRING); 
			$activeSheet->setCellValueExplicit('E'.$line_ctr, date($this->config->item('display_date_format'),strtotime($movement->transfer_effectivity_date)), PHPExcel_Cell_DataType::TYPE_STRING); 
			$activeSheet->setCellValueExplicit('F'.$line_ctr, $movement->movement_current_position, PHPExcel_Cell_DataType::TYPE_STRING); 
			$activeSheet->setCellValueExplicit('G'.$line_ctr, $movement->movement_new_position, PHPExcel_Cell_DataType::TYPE_STRING); 
			$activeSheet->setCellValueExplicit('H'.$line_ctr, $movement->show_dept, PHPExcel_Cell_DataType::TYPE_STRING); 
			$activeSheet->setCellValueExplicit('I'.$line_ctr, $movement->new_dept, PHPExcel_Cell_DataType::TYPE_STRING); 
			$activeSheet->setCellValueExplicit('J'.$line_ctr, $movement->current_rank_dummy, PHPExcel_Cell_DataType::TYPE_STRING); 
			$activeSheet->setCellValueExplicit('K'.$line_ctr, $movement->new_rank, PHPExcel_Cell_DataType::TYPE_STRING); 
			$activeSheet->setCellValueExplicit('L'.$line_ctr, $movement->employee_approver_name, PHPExcel_Cell_DataType::TYPE_STRING); 
			$activeSheet->setCellValueExplicit('M'.$line_ctr, $movement->position_reporting_to, PHPExcel_Cell_DataType::TYPE_STRING); 
			$activeSheet->setCellValueExplicit('N'.$line_ctr, $movement->current_employee_type_dummy, PHPExcel_Cell_DataType::TYPE_STRING); 
			$activeSheet->setCellValueExplicit('O'.$line_ctr, $movement->new_employee_type, PHPExcel_Cell_DataType::TYPE_STRING); 
			$activeSheet->setCellValueExplicit('P'.$line_ctr, $movement->current_job_level_dummy, PHPExcel_Cell_DataType::TYPE_STRING); 
			$activeSheet->setCellValueExplicit('Q'.$line_ctr, $movement->new_job_level, PHPExcel_Cell_DataType::TYPE_STRING); 
			$activeSheet->setCellValueExplicit('R'.$line_ctr, $movement->current_range_of_rank_dummy, PHPExcel_Cell_DataType::TYPE_STRING); 
			$activeSheet->setCellValueExplicit('S'.$line_ctr, $movement->new_rank_range, PHPExcel_Cell_DataType::TYPE_STRING); 
			$activeSheet->setCellValueExplicit('T'.$line_ctr, $movement->current_rank_code_dummy, PHPExcel_Cell_DataType::TYPE_STRING); 
			$activeSheet->setCellValueExplicit('U'.$line_ctr, $movement->new_rank_code, PHPExcel_Cell_DataType::TYPE_STRING); 
			$activeSheet->setCellValueExplicit('V'.$line_ctr, $movement->current_company_dummy, PHPExcel_Cell_DataType::TYPE_STRING); 
			$activeSheet->setCellValueExplicit('W'.$line_ctr, $movement->new_company, PHPExcel_Cell_DataType::TYPE_STRING); 
			$activeSheet->setCellValueExplicit('X'.$line_ctr, $movement->current_division_dummy, PHPExcel_Cell_DataType::TYPE_STRING); 
			$activeSheet->setCellValueExplicit('Y'.$line_ctr, $movement->new_division, PHPExcel_Cell_DataType::TYPE_STRING); 
			$activeSheet->setCellValueExplicit('Z'.$line_ctr, $movement->current_location_dummy, PHPExcel_Cell_DataType::TYPE_STRING); 
			$activeSheet->setCellValueExplicit('AA'.$line_ctr, $movement->new_location, PHPExcel_Cell_DataType::TYPE_STRING); 
			$activeSheet->setCellValueExplicit('AB'.$line_ctr, $movement->current_segment_1_dummy, PHPExcel_Cell_DataType::TYPE_STRING); 
			$activeSheet->setCellValueExplicit('AC'.$line_ctr, $movement->new_segment_1, PHPExcel_Cell_DataType::TYPE_STRING); 
			$activeSheet->setCellValueExplicit('AD'.$line_ctr, $movement->current_segment_2_dummy, PHPExcel_Cell_DataType::TYPE_STRING); 
			$activeSheet->setCellValueExplicit('AE'.$line_ctr, $movement->new_segment_2, PHPExcel_Cell_DataType::TYPE_STRING); 
			$activeSheet->setCellValueExplicit('AF'.$line_ctr, $movement->current_role, PHPExcel_Cell_DataType::TYPE_STRING); 
			$activeSheet->setCellValueExplicit('AG'.$line_ctr, $movement->new_role, PHPExcel_Cell_DataType::TYPE_STRING); 
			$activeSheet->setCellValueExplicit('AH'.$line_ctr, $movement->current_sick_leave, PHPExcel_Cell_DataType::TYPE_STRING); 
			$activeSheet->setCellValueExplicit('AI'.$line_ctr, $movement->sick_leave, PHPExcel_Cell_DataType::TYPE_STRING); 
			$activeSheet->setCellValueExplicit('AJ'.$line_ctr, $movement->current_vacation_leave, PHPExcel_Cell_DataType::TYPE_STRING); 
			$activeSheet->setCellValueExplicit('AK'.$line_ctr, $movement->vacation_leave, PHPExcel_Cell_DataType::TYPE_STRING); 
			$activeSheet->setCellValueExplicit('AL'.$line_ctr, $movement->current_emergency_leave, PHPExcel_Cell_DataType::TYPE_STRING); 
			$activeSheet->setCellValueExplicit('AM'.$line_ctr, $movement->emergency_leave, PHPExcel_Cell_DataType::TYPE_STRING); 
		
			$line_ctr++;
		}

		foreach($hiring as $employed_date)
		{

			/*
			$activeSheet->setCellValueExplicit('A'.$line_ctr, date($this->config->item('display_date_format'), strtotime($employed_date->employed_date)), PHPExcel_Cell_DataType::TYPE_STRING); 
			$activeSheet->setCellValueExplicit('B'.$line_ctr, $employed_date->department, PHPExcel_Cell_DataType::TYPE_STRING); 
			$activeSheet->setCellValueExplicit('C'.$line_ctr, 'Hiring', PHPExcel_Cell_DataType::TYPE_STRING); 
			$name = $employed_date->firstname." ".$employed_date->middlename." ".$employed_date->lastname;
			$activeSheet->setCellValueExplicit('D'.$line_ctr, $name, PHPExcel_Cell_DataType::TYPE_STRING); 

			$user_pos = $this->db->get_where('user_position', array('position_id' => $employed_date->position_id))->row();
				$activeSheet->setCellValueExplicit('E'.$line_ctr, $user_pos->position, PHPExcel_Cell_DataType::TYPE_STRING); 

			$show_rank = $this->db->get_where('user_rank_code', array('job_rank_code_id' => $employed_date->rank_code))->row();
				$activeSheet->setCellValueExplicit('F'.$line_ctr, $show_rank->job_rank_code, PHPExcel_Cell_DataType::TYPE_STRING); 

			$show_ror = $this->db->get_where('user_rank_range', array('job_rank_range_id' => $employed_date->range_of_rank))->row();
				$activeSheet->setCellValueExplicit('G'.$line_ctr, $show_ror->job_rank_range, PHPExcel_Cell_DataType::TYPE_STRING); 

			*/

			$name = $employed_date->firstname." ".$employed_date->middlename." ".$employed_date->lastname;

			$activeSheet->setCellValueExplicit('A'.$line_ctr, $name, PHPExcel_Cell_DataType::TYPE_STRING);
			$activeSheet->setCellValueExplicit('B'.$line_ctr, date($this->config->item('display_date_format'),strtotime($employed_date->created_date)), PHPExcel_Cell_DataType::TYPE_STRING);
			$activeSheet->setCellValueExplicit('C'.$line_ctr, "", PHPExcel_Cell_DataType::TYPE_STRING);
			$activeSheet->setCellValueExplicit('D'.$line_ctr, "Hiring", PHPExcel_Cell_DataType::TYPE_STRING);
			$activeSheet->setCellValueExplicit('E'.$line_ctr, date($this->config->item('display_date_format'),strtotime($employed_date->employed_date)), PHPExcel_Cell_DataType::TYPE_STRING);
			$activeSheet->setCellValueExplicit('F'.$line_ctr, "", PHPExcel_Cell_DataType::TYPE_STRING);
			$activeSheet->setCellValueExplicit('G'.$line_ctr, $employed_date->position, PHPExcel_Cell_DataType::TYPE_STRING);
			$activeSheet->setCellValueExplicit('H'.$line_ctr, "", PHPExcel_Cell_DataType::TYPE_STRING);
			$activeSheet->setCellValueExplicit('I'.$line_ctr, $employed_date->department, PHPExcel_Cell_DataType::TYPE_STRING);
			$activeSheet->setCellValueExplicit('J'.$line_ctr, "", PHPExcel_Cell_DataType::TYPE_STRING);
			$activeSheet->setCellValueExplicit('K'.$line_ctr, $employed_date->job_rank, PHPExcel_Cell_DataType::TYPE_STRING);
			$activeSheet->setCellValueExplicit('L'.$line_ctr, "", PHPExcel_Cell_DataType::TYPE_STRING);
			$activeSheet->setCellValueExplicit('M'.$line_ctr, "", PHPExcel_Cell_DataType::TYPE_STRING);
			$activeSheet->setCellValueExplicit('N'.$line_ctr, "", PHPExcel_Cell_DataType::TYPE_STRING);
			$activeSheet->setCellValueExplicit('O'.$line_ctr, $employed_date->employee_type, PHPExcel_Cell_DataType::TYPE_STRING);
			$activeSheet->setCellValueExplicit('P'.$line_ctr, "", PHPExcel_Cell_DataType::TYPE_STRING);
			$activeSheet->setCellValueExplicit('Q'.$line_ctr, $employed_date->description, PHPExcel_Cell_DataType::TYPE_STRING);
			$activeSheet->setCellValueExplicit('R'.$line_ctr, "", PHPExcel_Cell_DataType::TYPE_STRING);
			$activeSheet->setCellValueExplicit('S'.$line_ctr, $employed_date->job_rank_range, PHPExcel_Cell_DataType::TYPE_STRING);
			$activeSheet->setCellValueExplicit('T'.$line_ctr, "", PHPExcel_Cell_DataType::TYPE_STRING);
			$activeSheet->setCellValueExplicit('U'.$line_ctr, $employed_date->job_rank_code, PHPExcel_Cell_DataType::TYPE_STRING);
			$activeSheet->setCellValueExplicit('V'.$line_ctr, "", PHPExcel_Cell_DataType::TYPE_STRING);
			$activeSheet->setCellValueExplicit('W'.$line_ctr, $employed_date->company, PHPExcel_Cell_DataType::TYPE_STRING);
			$activeSheet->setCellValueExplicit('X'.$line_ctr, "", PHPExcel_Cell_DataType::TYPE_STRING);
			$activeSheet->setCellValueExplicit('Y'.$line_ctr, $employed_date->division, PHPExcel_Cell_DataType::TYPE_STRING);
			$activeSheet->setCellValueExplicit('Z'.$line_ctr, "", PHPExcel_Cell_DataType::TYPE_STRING);
			$activeSheet->setCellValueExplicit('AA'.$line_ctr, $employed_date->location, PHPExcel_Cell_DataType::TYPE_STRING);
			$activeSheet->setCellValueExplicit('AB'.$line_ctr, "", PHPExcel_Cell_DataType::TYPE_STRING);
			$activeSheet->setCellValueExplicit('AC'.$line_ctr, $employed_date->segment_1, PHPExcel_Cell_DataType::TYPE_STRING);
			$activeSheet->setCellValueExplicit('AD'.$line_ctr, "", PHPExcel_Cell_DataType::TYPE_STRING);
			$activeSheet->setCellValueExplicit('AE'.$line_ctr, $employed_date->segment_2, PHPExcel_Cell_DataType::TYPE_STRING);
			$activeSheet->setCellValueExplicit('AF'.$line_ctr, "", PHPExcel_Cell_DataType::TYPE_STRING);
			$activeSheet->setCellValueExplicit('AG'.$line_ctr, $employed_date->role, PHPExcel_Cell_DataType::TYPE_STRING);
			$activeSheet->setCellValueExplicit('AH'.$line_ctr, "", PHPExcel_Cell_DataType::TYPE_STRING);
			$activeSheet->setCellValueExplicit('AI'.$line_ctr, $employed_date->current_sick_leave, PHPExcel_Cell_DataType::TYPE_STRING);
			$activeSheet->setCellValueExplicit('AJ'.$line_ctr, "", PHPExcel_Cell_DataType::TYPE_STRING);
			$activeSheet->setCellValueExplicit('AK'.$line_ctr, $employed_date->current_vacation_leave, PHPExcel_Cell_DataType::TYPE_STRING);
			$activeSheet->setCellValueExplicit('AL'.$line_ctr, "", PHPExcel_Cell_DataType::TYPE_STRING);
			$activeSheet->setCellValueExplicit('AM'.$line_ctr, $employed_date->current_emergency_leave, PHPExcel_Cell_DataType::TYPE_STRING);

			$line_ctr++;
		}


		// Save it as an excel 2003 file
		$objWriter = IOFactory::createWriter($objPHPExcel, 'Excel5');

		header('Pragma: public');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Content-Type: application/force-download');
		header('Content-Type: application/octet-stream');
		header('Content-Type: application/download');
		header('Content-Disposition: attachment;filename='.date('Y-m-d').'-'.url_title($export->description).'.xls');
		header('Content-Transfer-Encoding: binary');
		
		$objWriter->save('php://output');		
	}

	function _default_grid_buttons( $module_link = "", $related_field = "", $related_field_value = "", $addtext = "", $deltext = "", $container = "" )
	{
		// set default
		if($module_link == "") $module_link = $this->module_link;
		if($addtext == "") $addtext = "Add";
		if($deltext == "") $deltext = "Delete";
		if($container == "") $container = "jqgridcontainer";



		$buttons = "<div class='icon-label-group'>";                    

    	if ($this->user_access[$this->module_id]['post']) {
	        if ( get_export_options( $this->module_id ) ) {
	        	// $buttons .= "<div class='icon-label'><a rel='record-save' class='icon-16-export' href='javascript:void(0);' onclick='export_list();'><span>Export</span></a></div>";

	        }        
    	}

        
        $buttons .= "</div>";
                
		return $buttons;
	}

	function _set_specific_search_query()
	{
		$field = $this->input->post('searchField');
		$operator =  $this->input->post('searchOper');
		$value =  $this->input->post('searchString');


		if($field == "employee_dtr.time_in1"){

			$value = date('Y-m-d h:i:s',strtotime($value));

		}
		

		foreach( $this->search_columns as $search )
		{
			if($search['jq_index'] == $field) $field = $search['column'];
		}

		$field = strtolower( $field );
		if(sizeof(explode(' as ', $field)) > 1){
			$as_part = explode(' as ', $field);
			$field = strtolower( trim( $as_part[0] ) );
		}

		switch ($operator) {
			case 'eq':
				return $field . ' = "'.$value.'"';
				break;
			case 'ne':
				return $field . ' != "'.$value.'"';
				break;
			case 'lt':
				return $field . ' < "'.$value.'"';
				break;
			case 'le':
				return $field . ' <= "'.$value.'"';
				break;
			case 'gt':
				return $field . ' > "'.$value.'"';
				break;
			case 'ge':
				return $field . ' >= "'.$value.'"';
				break;
			case 'bw':
				return $field . ' REGEXP "^'. $value .'"';
				break;
			case 'bn':
				return $field . ' NOT REGEXP "^'. $value .'"';
				break;
			case 'in':
				return $field . ' IN ('. $value .')';
				break;
			case 'ni':
				return $field . ' NOT IN ('. $value .')';
				break;
			case 'ew':
				return $field . ' LIKE "%'. $value  .'"';
				break;
			case 'en':
				return $field . ' NOT LIKE "%'. $value  .'"';
				break;
			case 'cn':
				return $field . ' LIKE "%'. $value .'%"';
				break;
			case 'nc':
				return $field . ' NOT LIKE "%'. $value .'%"';
				break;
			default:
				return $field . ' LIKE %'. $value .'%';
		}
	}

	// END custom module funtions
	
}

/* End of file */
/* Location: system/application */
?>