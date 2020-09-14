<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class manpower_count extends MY_Controller
{
	function __construct()
    {
        parent::__construct();

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format	
		
		$this->listview_title = 'Manpower Count';
		$this->listview_description = 'This module lists all defined manpower count(s).';
		$this->jqgrid_title = "Manpower Count List";
		$this->detailview_title = 'Manpower Count Info';
		$this->detailview_description = 'This page shows detailed information about a particular manpower count.';
		$this->editview_title = 'Manpower Count Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about manpower count(s).';
    }

	// START - default module functions
	// default jqgrid controller method
	function index()
    {
    	$data['scripts'][] = multiselect_script();
		$data['scripts'][] = jqgrid_listview();	// load jqgrid js and default grid js
		$data['content'] = 'employee/manpower_count/listview';

		$data['scripts'][] = chosen_script();
		$data['jqgrid'] = 'employees/manpower_count/jqgrid';

		if($this->session->flashdata('flashdata')){
			$info['flashdata'] = $this->session->flashdata('flashdata');
			$data['flashdata'] = $this->load->view($this->userinfo['rtheme'].'/template/flashdata', $info, true);
		}

		//set default columnlist
		$this->listview_column_names = array('Department', 'Regular', 'Probationary', 'Consultant', 'Project Based',
			'Contractual (Direct Hired)','Contractual (Agency Hired)','OJT','Regular', 'Probationary', 'Consultant', 'Project Based',
			'Contractual (Direct Hired)','Contractual (Agency Hired)','OJT','Regular', 'Probationary', 'Consultant', 'Project Based',
			'Contractual (Direct Hired)','Contractual (Agency Hired)','OJT','Total');

		$this->listview_columns = array(
				array('name' => 'department', 'sortable' => false ),
				array('name' => 'off_regular', 'sortable' => false),
				array('name' => 'off_probationary', 'sortable' => false),
				array('name' => 'off_consultant', 'sortable' => false),
				array('name' => 'off_project', 'sortable' => false),
				array('name' => 'off_contractual_direct', 'sortable' => false),
				array('name' => 'off_contractual_agency', 'sortable' => false),
				array('name' => 'off_ojt', 'sortable' => false),
				array('name' => 'sup_regular', 'sortable' => false),
				array('name' => 'sup_probationary', 'sortable' => false),
				array('name' => 'sup_consultant', 'sortable' => false),
				array('name' => 'sup_project', 'sortable' => false),
				array('name' => 'sup_contractual_direct', 'sortable' => false),
				array('name' => 'sup_contractual_agency', 'sortable' => false),
				array('name' => 'sup_ojt', 'sortable' => false),
				array('name' => 'rank_regular', 'sortable' => false),
				array('name' => 'rank_probationary', 'sortable' => false),
				array('name' => 'rank_consultant', 'sortable' => false),
				array('name' => 'rank_project', 'sortable' => false),
				array('name' => 'rank_contractual_direct', 'sortable' => false),
				array('name' => 'rank_contractual_agency', 'sortable' => false),
				array('name' => 'rank_ojt', 'sortable' => false),
				array('name' => 'total', 'sortable' => false)
		);

		$company = array();
		$company[0] = "Please Select Company";

		$company_list = $this->db->get_where('user_company',array('deleted'=>0))->result();
		foreach( $company_list as $company_record ){
			$company[$company_record->company_id] = $company_record->company;
		}

		$data['company_list'] = $company;


		//set grid buttons
		$data['jqg_buttons'] = $this->_default_grid_buttons();

		//set load jqgrid loadComplete callback
		$data['jqgrid_loadComplete'] = "";

		$data['division'] = $this->db->get('user_company_division')->result_array();
		$data['employee'] = $this->db->get('user')->result_array();
		$data['company'] = $this->db->get('user_company')->result_array();
		$data['department'] = $this->db->get('user_company_department')->result_array();

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


	function listview() {
		$this->load->helper('time_upload');


		$response->msg_type = 'success';
		$response->msg = '';
		$response->page = 1;
		$response->records = 0;


		$page = $this->input->post('page');
		$limit = $this->input->post('rows'); // get how many rows we want to have into the grid
		$sidx = $this->input->post('sidx'); // get index row - i.e. user click to sort
		$sord = $this->input->post('sord'); // get the direction
		$related_module = ( $this->input->post('related_module') ? true : false );


		$view_actions = (isset($_POST['view']) && $_POST['view'] == 'detail') ? false : true ;

		$this->listview_columns = array(
				array('name' => 'department', 'sortable' => false ),
				array('name' => 'off_regular', 'sortable' => false),
				array('name' => 'off_probationary', 'sortable' => false),
				array('name' => 'off_consultant', 'sortable' => false),
				array('name' => 'off_project', 'sortable' => false),
				array('name' => 'off_contractual_direct', 'sortable' => false),
				array('name' => 'off_contractual_agency', 'sortable' => false),
				array('name' => 'off_ojt', 'sortable' => false),
				array('name' => 'sup_regular', 'sortable' => false),
				array('name' => 'sup_probationary', 'sortable' => false),
				array('name' => 'sup_consultant', 'sortable' => false),
				array('name' => 'sup_project', 'sortable' => false),
				array('name' => 'sup_contractual_direct', 'sortable' => false),
				array('name' => 'sup_contractual_agency', 'sortable' => false),
				array('name' => 'sup_ojt', 'sortable' => false),
				array('name' => 'rank_regular', 'sortable' => false),
				array('name' => 'rank_probationary', 'sortable' => false),
				array('name' => 'rank_consultant', 'sortable' => false),
				array('name' => 'rank_project', 'sortable' => false),
				array('name' => 'rank_contractual_direct', 'sortable' => false),
				array('name' => 'rank_contractual_agency', 'sortable' => false),
				array('name' => 'rank_ojt', 'sortable' => false),
				array('name' => 'total', 'sortable' => false)
		);

		//set Search Qry string
		if($this->input->post('_search') == "true")
			$search = $this->input->post('searchField') == "all" ? $this->_set_search_all_query() : $this->_set_specific_search_query();
		else
			$search = 1;

		if( $this->module == "user" && (!$this->is_admin && !$this->is_superadmin) ) $search .= ' AND '.$this->db->dbprefix.'user.user_id NOT IN (1,2)';


		if (method_exists($this, '_append_to_select')) {
			// Append fields to the SELECT statement via $this->listview_qry
			$this->_append_to_select();
		}

		if (method_exists($this, '_custom_join')) {
			$this->_custom_join();
		}


		if(!empty( $this->filter ) ) 	$this->db->where( $this->filter );

		if (method_exists($this, '_set_filter')) {
			$this->_set_filter();
		}

		$condition = "";

		if( $this->input->post('department') && $this->input->post('department') != 'null' ) $condition .= " AND d.department_id IN (".implode(',',$this->input->post('department')).")";
		if( $this->input->post('company') && $this->input->post('company') != 'null' ) $condition .= " AND c.company_id = ".$this->input->post('company');
		if( $this->input->post('date_asof') && $this->input->post('date_asof') != 'null' ){ 
			$condition .= ' AND ( ISNULL( b.resigned_date ) || b.resigned_date < "'.date('Y-m-d',strtotime($this->input->post('date_asof'))).'" )';
		}
		else{
			$condition .= ' AND ( ISNULL( b.resigned_date ) )';
		}

		$sql='SELECT a.location, c.department,
                SUM(IF(b.employee_type=1 AND b.status_id=1, 1, 0)) off_regular,
                SUM(IF(b.employee_type=1 AND b.status_id=2, 1, 0)) off_probationary,
				SUM(IF(b.employee_type=1 AND b.status_id=3, 1, 0)) off_consultant,
				SUM(IF(b.employee_type=1 AND b.status_id=4, 1, 0)) off_project,
				SUM(IF(b.employee_type=1 AND b.status_id=5, 1, 0)) off_contractual_direct,
                SUM(IF(b.employee_type=1 AND b.status_id=6, 1, 0)) off_contractual_agency,
                SUM(IF(b.employee_type=1 AND b.status_id=7, 1, 0)) off_ojt,
				SUM(IF(b.employee_type=2 AND b.status_id=1, 1, 0)) sup_regular,
                SUM(IF(b.employee_type=2 AND b.status_id=2, 1, 0)) sup_probationary,
				SUM(IF(b.employee_type=2 AND b.status_id=3, 1, 0)) sup_consultant,
				SUM(IF(b.employee_type=2 AND b.status_id=4, 1, 0)) sup_project,
				SUM(IF(b.employee_type=2 AND b.status_id=5, 1, 0)) sup_contractual_direct,
                SUM(IF(b.employee_type=2 AND b.status_id=6, 1, 0)) sup_contractual_agency,
                SUM(IF(b.employee_type=2 AND b.status_id=7, 1, 0)) sup_ojt,
				SUM(IF(b.employee_type=3 AND b.status_id=1, 1, 0)) rank_regular,
                SUM(IF(b.employee_type=3 AND b.status_id=2, 1, 0)) rank_probationary,
				SUM(IF(b.employee_type=3 AND b.status_id=3, 1, 0)) rank_consultant,
				SUM(IF(b.employee_type=3 AND b.status_id=4, 1, 0)) rank_project,
				SUM(IF(b.employee_type=3 AND b.status_id=5, 1, 0)) rank_contractual_direct,
                SUM(IF(b.employee_type=3 AND b.status_id=6, 1, 0)) rank_contractual_agency,
                SUM(IF(b.employee_type=3 AND b.status_id=7, 1, 0)) rank_ojt,
                COUNT(b.employee_id) total -- , d.lastname, d.firstname
FROM hr_user_location a
INNER JOIN hr_employee b ON b.location_id=a.location_id
INNER JOIN hr_user_company_department c ON c.division_id=b.division_id
INNER JOIN hr_user d ON d.employee_id=b.employee_id AND ( d.department_id LIKE CONCAT("%,", c.department_id) || d.department_id LIKE CONCAT( c.department_id ) || d.department_id LIKE CONCAT( c.department_id, ",%") || d.department_id LIKE CONCAT("%,", c.department_id, ",%") )
WHERE NOT b.status_id IN (8,9,10,11) AND b.location_id = 13 AND b.resigned = 0 AND d.deleted = 0 '.$condition.'
 GROUP BY 1,2

UNION

SELECT a.location, c.department,
                SUM(IF(b.employee_type=1 AND b.status_id=1, 1, 0)) off_regular,
                SUM(IF(b.employee_type=1 AND b.status_id=2, 1, 0)) off_probationary,
				SUM(IF(b.employee_type=1 AND b.status_id=3, 1, 0)) off_consultant,
				SUM(IF(b.employee_type=1 AND b.status_id=4, 1, 0)) off_project,
				SUM(IF(b.employee_type=1 AND b.status_id=5, 1, 0)) off_contractual_direct,
                SUM(IF(b.employee_type=1 AND b.status_id=6, 1, 0)) off_contractual_agency,
                SUM(IF(b.employee_type=1 AND b.status_id=7, 1, 0)) off_ojt,
				SUM(IF(b.employee_type=2 AND b.status_id=1, 1, 0)) sup_regular,
                SUM(IF(b.employee_type=2 AND b.status_id=2, 1, 0)) sup_probationary,
				SUM(IF(b.employee_type=2 AND b.status_id=3, 1, 0)) sup_consultant,
				SUM(IF(b.employee_type=2 AND b.status_id=4, 1, 0)) sup_project,
				SUM(IF(b.employee_type=2 AND b.status_id=5, 1, 0)) sup_contractual_direct,
                SUM(IF(b.employee_type=2 AND b.status_id=6, 1, 0)) sup_contractual_agency,
                SUM(IF(b.employee_type=2 AND b.status_id=7, 1, 0)) sup_ojt,
				SUM(IF(b.employee_type=3 AND b.status_id=1, 1, 0)) rank_regular,
                SUM(IF(b.employee_type=3 AND b.status_id=2, 1, 0)) rank_probationary,
				SUM(IF(b.employee_type=3 AND b.status_id=3, 1, 0)) rank_consultant,
				SUM(IF(b.employee_type=3 AND b.status_id=4, 1, 0)) rank_project,
				SUM(IF(b.employee_type=3 AND b.status_id=5, 1, 0)) rank_contractual_direct,
                SUM(IF(b.employee_type=3 AND b.status_id=6, 1, 0)) rank_contractual_agency,
                SUM(IF(b.employee_type=3 AND b.status_id=7, 1, 0)) rank_ojt,
                COUNT(b.employee_id) total -- , d.lastname, d.firstname
FROM hr_user_location a
INNER JOIN hr_employee b ON b.location_id=a.location_id
INNER JOIN hr_user_company_department c ON c.division_id=b.division_id
INNER JOIN hr_user d ON d.employee_id=b.employee_id AND ( d.department_id LIKE CONCAT("%,", c.department_id ) || d.department_id LIKE CONCAT( c.department_id ) || d.department_id LIKE CONCAT( c.department_id, ",%") || d.department_id LIKE CONCAT("%,", c.department_id, ",%") )
WHERE NOT b.status_id IN (8,9,10,11) AND b.location_id != 13 AND b.resigned = 0 AND d.deleted = 0 '.$condition.'
GROUP BY 1,2;';




		//get list
		$result = $this->db->query($sql);
		$response->company = $this->input->post('company');
		//$response->last_query = $this->db->last_query();

		if( $this->db->_error_message() != "" ){
			$response->msg = $this->db->_error_message();
			$response->msg_type = "error";
		}
		else{
			$total_pages = $result->num_rows() > 0 ? ceil($result->num_rows()/$limit) : 0;
			$response->page = $page > $total_pages ? $total_pages : $page;
			$response->total = $total_pages;
			$response->records = $result->num_rows();
			
			
			$order_by = "";
			$limit_by = "";

			if($sidx != ""){

				if($order_by == ""){
					$order_by .= " ORDER BY";
				}

				$order_by .= "  ".$sidx." ".$sord." ";
			}

			/*
			$start = $limit * $page - $limit;
			$limit_by .= " ".$start.", ".$limit." ";
			$this->db->limit($limit, $start);
			*/
			

			$condition = "";

		if( $this->input->post('department') && $this->input->post('department') != 'null' ) $condition .= " AND d.department_id IN (".implode(',',$this->input->post('department')).")";
		if( $this->input->post('company') && $this->input->post('company') != 'null' ) $condition .= " AND c.company_id = ".$this->input->post('company');
		if( $this->input->post('date_asof') && $this->input->post('date_asof') != 'null' ){ 
			$condition .= ' AND ( ISNULL( b.resigned_date ) || b.resigned_date < "'.date('Y-m-d',strtotime($this->input->post('date_asof'))).'" )';
		}
		else{
			$condition .= ' AND ( ISNULL( b.resigned_date ) )';
		}

			$sql='SELECT a.location, c.department,
                 SUM(IF(b.employee_type=1 AND b.status_id=1, 1, 0)) off_regular,
                SUM(IF(b.employee_type=1 AND b.status_id=2, 1, 0)) off_probationary,
				SUM(IF(b.employee_type=1 AND b.status_id=3, 1, 0)) off_consultant,
				SUM(IF(b.employee_type=1 AND b.status_id=4, 1, 0)) off_project,
				SUM(IF(b.employee_type=1 AND b.status_id=5, 1, 0)) off_contractual_direct,
                SUM(IF(b.employee_type=1 AND b.status_id=6, 1, 0)) off_contractual_agency,
                SUM(IF(b.employee_type=1 AND b.status_id=7, 1, 0)) off_ojt,
				SUM(IF(b.employee_type=2 AND b.status_id=1, 1, 0)) sup_regular,
                SUM(IF(b.employee_type=2 AND b.status_id=2, 1, 0)) sup_probationary,
				SUM(IF(b.employee_type=2 AND b.status_id=3, 1, 0)) sup_consultant,
				SUM(IF(b.employee_type=2 AND b.status_id=4, 1, 0)) sup_project,
				SUM(IF(b.employee_type=2 AND b.status_id=5, 1, 0)) sup_contractual_direct,
                SUM(IF(b.employee_type=2 AND b.status_id=6, 1, 0)) sup_contractual_agency,
                SUM(IF(b.employee_type=2 AND b.status_id=7, 1, 0)) sup_ojt,
				SUM(IF(b.employee_type=3 AND b.status_id=1, 1, 0)) rank_regular,
                SUM(IF(b.employee_type=3 AND b.status_id=2, 1, 0)) rank_probationary,
				SUM(IF(b.employee_type=3 AND b.status_id=3, 1, 0)) rank_consultant,
				SUM(IF(b.employee_type=3 AND b.status_id=4, 1, 0)) rank_project,
				SUM(IF(b.employee_type=3 AND b.status_id=5, 1, 0)) rank_contractual_direct,
                SUM(IF(b.employee_type=3 AND b.status_id=6, 1, 0)) rank_contractual_agency,
                SUM(IF(b.employee_type=3 AND b.status_id=7, 1, 0)) rank_ojt,
                COUNT(b.employee_id) total -- , d.lastname, d.firstname
FROM hr_user_location a
INNER JOIN hr_employee b ON b.location_id=a.location_id
INNER JOIN hr_user_company_department c ON c.division_id=b.division_id
INNER JOIN hr_user d ON d.employee_id=b.employee_id AND ( d.department_id LIKE CONCAT("%,", c.department_id) || d.department_id LIKE CONCAT( c.department_id ) || d.department_id LIKE CONCAT( c.department_id, ",%") || d.department_id LIKE CONCAT("%,", c.department_id, ",%") )
WHERE NOT b.status_id IN (8,9,10,11) AND b.location_id = 13 AND b.resigned = 0 AND d.deleted = 0 '.$condition.'
 GROUP BY 1,2 

UNION

SELECT a.location, c.department,
                 SUM(IF(b.employee_type=1 AND b.status_id=1, 1, 0)) off_regular,
                SUM(IF(b.employee_type=1 AND b.status_id=2, 1, 0)) off_probationary,
				SUM(IF(b.employee_type=1 AND b.status_id=3, 1, 0)) off_consultant,
				SUM(IF(b.employee_type=1 AND b.status_id=4, 1, 0)) off_project,
				SUM(IF(b.employee_type=1 AND b.status_id=5, 1, 0)) off_contractual_direct,
                SUM(IF(b.employee_type=1 AND b.status_id=6, 1, 0)) off_contractual_agency,
                SUM(IF(b.employee_type=1 AND b.status_id=7, 1, 0)) off_ojt,
				SUM(IF(b.employee_type=2 AND b.status_id=1, 1, 0)) sup_regular,
                SUM(IF(b.employee_type=2 AND b.status_id=2, 1, 0)) sup_probationary,
				SUM(IF(b.employee_type=2 AND b.status_id=3, 1, 0)) sup_consultant,
				SUM(IF(b.employee_type=2 AND b.status_id=4, 1, 0)) sup_project,
				SUM(IF(b.employee_type=2 AND b.status_id=5, 1, 0)) sup_contractual_direct,
                SUM(IF(b.employee_type=2 AND b.status_id=6, 1, 0)) sup_contractual_agency,
                SUM(IF(b.employee_type=2 AND b.status_id=7, 1, 0)) sup_ojt,
				SUM(IF(b.employee_type=3 AND b.status_id=1, 1, 0)) rank_regular,
                SUM(IF(b.employee_type=3 AND b.status_id=2, 1, 0)) rank_probationary,
				SUM(IF(b.employee_type=3 AND b.status_id=3, 1, 0)) rank_consultant,
				SUM(IF(b.employee_type=3 AND b.status_id=4, 1, 0)) rank_project,
				SUM(IF(b.employee_type=3 AND b.status_id=5, 1, 0)) rank_contractual_direct,
                SUM(IF(b.employee_type=3 AND b.status_id=6, 1, 0)) rank_contractual_agency,
                SUM(IF(b.employee_type=3 AND b.status_id=7, 1, 0)) rank_ojt,
                COUNT(b.employee_id) total -- , d.lastname, d.firstname
FROM hr_user_location a
INNER JOIN hr_employee b ON b.location_id=a.location_id
INNER JOIN hr_user_company_department c ON c.division_id=b.division_id
INNER JOIN hr_user d ON d.employee_id=b.employee_id AND ( d.department_id LIKE CONCAT("%,", c.department_id ) || d.department_id LIKE CONCAT( c.department_id ) || d.department_id LIKE CONCAT( c.department_id, ",%") || d.department_id LIKE CONCAT("%,", c.department_id, ",%") )
WHERE NOT b.status_id IN (8,9,10,11) AND b.location_id != 13 AND b.resigned = 0 AND d.deleted = 0 '.$condition.'
GROUP BY 1,2 ';
			
			$result = $this->db->query($sql);

			//$response->last_query = $this->db->last_query();

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

							$cell[$cell_ctr] = $row[$detail['name']];
							$cell_ctr++;

						}
						$response->rows[$ctr]['id'] = $row[$this->key_field];
						$response->rows[$ctr]['cell'] = $cell;
						$ctr++;
					}
				}
			}
		}


	

		$this->load->view('template/ajax', array('json' => $response));
	}

	function _set_listview_query( $listview_id = '', $view_actions = true ) {

		parent::_set_listview_query($listview_id, $view_actions);

	}	

	function _set_search_all_query()
	{
		$value =  $this->input->post('searchString');
		$search_string = array();
		foreach($this->search_columns as $search)
		{
			$column = strtolower( $search['column'] );
			if(sizeof(explode(' as ', $column)) > 1){
				$as_part = explode(' as ', $column);
				$search['column'] = strtolower( trim( $as_part[0] ) );
			}
			$search_string[] = $search['column'] . ' LIKE "%'. $value .'%"' ;
		}
		$search_string[] = 'u.firstname LIKE "%' . $value . '%"';
		$search_string[] = 'u.lastname LIKE "%' . $value . '%"';
		$search_string = '('. implode(' OR ', $search_string) .')';
		return $search_string;
	}

	function get_department_list(){

		if($this->input->post('company_id') != 0){

			$html = '';

			$department = $this->db->get_where('user_company_department',array('company_id'=>$this->input->post('company_id')))->result_array();		
	        $html .= '<select id="department" multiple="multiple" class="multi-select" style="width:400px;" name="department[]">';
	            foreach($department as $department_record){
	                $html .= '<option value="'.$department_record["department_id"].'">'.$department_record["department"].'</option>';
	            }
	        $html .= '</select>';				

	        $data['html'] = $html;

	        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);	

        }		
	}
	

	function export() {	


		$condition = "";


		if( $this->input->post('department') && $this->input->post('department') != 'null' ) $condition .= " AND d.department_id IN (".implode(',',$this->input->post('department')).")";
		if( $this->input->post('company_list') && $this->input->post('company_list') != 'null' ) $condition .= " AND d.company_id = ".$this->input->post('company_list');
		if( $this->input->post('date_asof') && $this->input->post('date_asof') != 'null' ){ 
			$condition .= ' AND (b.resigned = 0 || b.resigned_date < "'.date('Y-m-d',strtotime($this->input->post('date_asof'))).'" )';
		}
		else{
			$condition .= ' AND (b.resigned = 0)';
		}

			$sql='SELECT a.location, c.department,
                 SUM(IF(b.employee_type=1 AND b.status_id=1, 1, 0)) off_regular,
                SUM(IF(b.employee_type=1 AND b.status_id=2, 1, 0)) off_probationary,
				SUM(IF(b.employee_type=1 AND b.status_id=3, 1, 0)) off_consultant,
				SUM(IF(b.employee_type=1 AND b.status_id=4, 1, 0)) off_project,
				SUM(IF(b.employee_type=1 AND b.status_id=5, 1, 0)) off_contractual_direct,
                SUM(IF(b.employee_type=1 AND b.status_id=6, 1, 0)) off_contractual_agency,
                SUM(IF(b.employee_type=1 AND b.status_id=7, 1, 0)) off_ojt,
				SUM(IF(b.employee_type=2 AND b.status_id=1, 1, 0)) sup_regular,
                SUM(IF(b.employee_type=2 AND b.status_id=2, 1, 0)) sup_probationary,
				SUM(IF(b.employee_type=2 AND b.status_id=3, 1, 0)) sup_consultant,
				SUM(IF(b.employee_type=2 AND b.status_id=4, 1, 0)) sup_project,
				SUM(IF(b.employee_type=2 AND b.status_id=5, 1, 0)) sup_contractual_direct,
                SUM(IF(b.employee_type=2 AND b.status_id=6, 1, 0)) sup_contractual_agency,
                SUM(IF(b.employee_type=2 AND b.status_id=7, 1, 0)) sup_ojt,
				SUM(IF(b.employee_type=3 AND b.status_id=1, 1, 0)) rank_regular,
                SUM(IF(b.employee_type=3 AND b.status_id=2, 1, 0)) rank_probationary,
				SUM(IF(b.employee_type=3 AND b.status_id=3, 1, 0)) rank_consultant,
				SUM(IF(b.employee_type=3 AND b.status_id=4, 1, 0)) rank_project,
				SUM(IF(b.employee_type=3 AND b.status_id=5, 1, 0)) rank_contractual_direct,
                SUM(IF(b.employee_type=3 AND b.status_id=6, 1, 0)) rank_contractual_agency,
                SUM(IF(b.employee_type=3 AND b.status_id=7, 1, 0)) rank_ojt,
                COUNT(b.employee_id) total -- , d.lastname, d.firstname
FROM hr_user_location a
INNER JOIN hr_employee b ON b.location_id=a.location_id
INNER JOIN hr_user d ON d.employee_id=b.employee_id
INNER JOIN hr_user_company_department c ON ( d.department_id LIKE CONCAT("%,", c.department_id ) || d.department_id LIKE CONCAT( c.department_id ) || d.department_id LIKE CONCAT( c.department_id, ",%") || d.department_id LIKE CONCAT("%,", c.department_id, ",%") )
WHERE NOT b.status_id IN (8,9,10,11) AND b.location_id = 13  AND d.deleted = 0 '.$condition.' AND resigned = 0
 GROUP BY 1,2 ';


		$query = $this->db->query($sql);

		$fields = $query->list_fields();

		$this->_fields = $fields;
		$this->_export = $export;
		$this->_query  = $query;

		$this->_excel_export();
	}

	function export2() {	


		$condition = "";


		if( $this->input->post('department') && $this->input->post('department') != 'null' ) $condition .= " AND d.department_id IN (".implode(',',$this->input->post('department')).")";
		if( $this->input->post('company_list') && $this->input->post('company_list') != 'null' ) $condition .= " AND d.company_id = ".$this->input->post('company_list');
		if( $this->input->post('date_asof') && $this->input->post('date_asof') != 'null' ){ 
			$condition .= ' AND (b.resigned = 0 || b.resigned_date < "'.date('Y-m-d',strtotime($this->input->post('date_asof'))).'")';
		}
		else{
			$condition .= ' AND (b.resigned = 0)';
		}

			$sql='SELECT a.location, c.department,
                 SUM(IF(b.employee_type=1 AND b.status_id=1, 1, 0)) off_regular,
                SUM(IF(b.employee_type=1 AND b.status_id=2, 1, 0)) off_probationary,
				SUM(IF(b.employee_type=1 AND b.status_id=3, 1, 0)) off_consultant,
				SUM(IF(b.employee_type=1 AND b.status_id=4, 1, 0)) off_project,
				SUM(IF(b.employee_type=1 AND b.status_id=5, 1, 0)) off_contractual_direct,
                SUM(IF(b.employee_type=1 AND b.status_id=6, 1, 0)) off_contractual_agency,
                SUM(IF(b.employee_type=1 AND b.status_id=7, 1, 0)) off_ojt,
				SUM(IF(b.employee_type=2 AND b.status_id=1, 1, 0)) sup_regular,
                SUM(IF(b.employee_type=2 AND b.status_id=2, 1, 0)) sup_probationary,
				SUM(IF(b.employee_type=2 AND b.status_id=3, 1, 0)) sup_consultant,
				SUM(IF(b.employee_type=2 AND b.status_id=4, 1, 0)) sup_project,
				SUM(IF(b.employee_type=2 AND b.status_id=5, 1, 0)) sup_contractual_direct,
                SUM(IF(b.employee_type=2 AND b.status_id=6, 1, 0)) sup_contractual_agency,
                SUM(IF(b.employee_type=2 AND b.status_id=7, 1, 0)) sup_ojt,
				SUM(IF(b.employee_type=3 AND b.status_id=1, 1, 0)) rank_regular,
                SUM(IF(b.employee_type=3 AND b.status_id=2, 1, 0)) rank_probationary,
				SUM(IF(b.employee_type=3 AND b.status_id=3, 1, 0)) rank_consultant,
				SUM(IF(b.employee_type=3 AND b.status_id=4, 1, 0)) rank_project,
				SUM(IF(b.employee_type=3 AND b.status_id=5, 1, 0)) rank_contractual_direct,
                SUM(IF(b.employee_type=3 AND b.status_id=6, 1, 0)) rank_contractual_agency,
                SUM(IF(b.employee_type=3 AND b.status_id=7, 1, 0)) rank_ojt,
                COUNT(b.employee_id) total -- , d.lastname, d.firstname
FROM hr_user_location a
INNER JOIN hr_employee b ON b.location_id=a.location_id
INNER JOIN hr_user d ON d.employee_id=b.employee_id
INNER JOIN hr_user_company_department c ON ( d.department_id LIKE CONCAT("%,", c.department_id ) || d.department_id LIKE CONCAT( c.department_id ) || d.department_id LIKE CONCAT( c.department_id, ",%") || d.department_id LIKE CONCAT("%,", c.department_id, ",%") )
WHERE NOT b.status_id IN (8,9,10,11) AND b.location_id != 13  AND d.deleted = 0 '.$condition.'
 GROUP BY 1,2 ';

		$query = $this->db->query($sql);

		$this->_query  = $query;

	}
	
	private function _excel_export()
	{

		$query  = $this->_query;
		$fields = $this->_fields;
		$fields_nickname = array('Location','Department', 'Regular', 'Probationary', 'Consultant', 'Project Based',
			'Contractual (Direct Hired)','Contractual (Agency Hired)','OJT','Regular', 'Probationary', 'Consultant', 'Project Based',
			'Contractual (Direct Hired)','Contractual (Agency Hired)','OJT','Regular', 'Probationary', 'Consultant', 'Project Based',
			'Contractual (Direct Hired)','Contractual (Agency Hired)','OJT','Total');
		$export = $this->_export;

		$this->load->library('PHPExcel');		
		$this->load->library('PHPExcel/IOFactory');

		$objPHPExcel = new PHPExcel();

		$objPHPExcel->getProperties()->setTitle($query->description)
		            ->setDescription($query->description);
		               
		// Assign cell values
		$objPHPExcel->setActiveSheetIndex(0);
		$activeSheet = $objPHPExcel->getActiveSheet();

		//header
		$alphabet  = range('A','Z');
		$alpha_ctr = 0;
		$sub_ctr   = 0;

		//Default column width
		$objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(15);

		//Initialize style
		$default_border = array(
		    'style' => PHPExcel_Style_Border::BORDER_THIN,
		    'color' => array('rgb'=>'000000')
		);

		$styleArray = array(
			'font' => array(
				'bold' => true,
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			)
		);

		$styleColumn =  array(
			'borders' => array(
			  'bottom' => $default_border,
			  'left' => $default_border,
			  'top' => $default_border,
			  'right' => $default_border,
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			)
		);

		$styleHeader = array(
			'borders' => array(
			  'bottom' => $default_border,
			  'left' => $default_border,
			  'top' => $default_border,
			  'right' => $default_border,
			),
			'font' => array(
				'bold' => true,
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			)
		);

		$styleArray2 = array(
			'font' => array(
				'bold' => true,
			)
		);

		$employee_type_ctr = 1;

		foreach ($fields_nickname as $field) {

			if( $field != 'Location' ){
			
				if ($alpha_ctr >= count($alphabet)) {
					$alpha_ctr = 0;
					$sub_ctr++;
				}

				if ($sub_ctr > 0) {
					$xcoor = $alphabet[$sub_ctr - 1] . $alphabet[$alpha_ctr];
				} else {
					$xcoor = $alphabet[$alpha_ctr];
				}

				$activeSheet->setCellValue($xcoor . '6', $field);
				$objPHPExcel->getActiveSheet()->setCellValueExplicit($xcoor . '6',  $field, PHPExcel_Cell_DataType::TYPE_STRING);
				$objPHPExcel->getActiveSheet()->getStyle($xcoor . '6')->applyFromArray($styleHeader);

				if( ( $field == 'Department' ) || ( $field == 'Total' ) ){

					$objPHPExcel->getActiveSheet()->getStyle($xcoor . '5')->applyFromArray($styleHeader);
					$objPHPExcel->getActiveSheet()->mergeCells($xcoor .'5:'.$xcoor .'6');
					$activeSheet->setCellValue($xcoor . '5', $field);

				}
				elseif( $field == 'Regular' ){

					$objPHPExcel->getActiveSheet()->getStyle($alphabet[$alpha_ctr].'5')->applyFromArray($styleHeader);
	
					for($h_ctr=1; $h_ctr<=6; $h_ctr++){
						$objPHPExcel->getActiveSheet()->getStyle($alphabet[$alpha_ctr+$h_ctr].'5')->applyFromArray($styleHeader);
					}

					$objPHPExcel->getActiveSheet()->mergeCells($alphabet[$alpha_ctr].'5:'.$alphabet[$alpha_ctr+6].'5');

					switch($employee_type_ctr){
						case 1:
							$activeSheet->setCellValueExplicit($xcoor . '5', 'OFFICER', PHPExcel_Cell_DataType::TYPE_STRING);
						break;
						case 2:
							$activeSheet->setCellValueExplicit($xcoor . '5', 'SUPERVISOR', PHPExcel_Cell_DataType::TYPE_STRING);
						break;
						case 3:
							$activeSheet->setCellValueExplicit($xcoor . '5', 'RANK AND FILE', PHPExcel_Cell_DataType::TYPE_STRING);
						break;
					}

					$objPHPExcel->getActiveSheet()->getStyle($xcoor . '5')->applyFromArray($styleArray);

					$employee_type_ctr++;

				}
				
				
				$alpha_ctr++;

			}
		}

		for($ctr=1; $ctr<4; $ctr++){

			$objPHPExcel->getActiveSheet()->mergeCells($alphabet[0].$ctr.':'.$alphabet[$alpha_ctr - 1].$ctr);


		}


		if($this->input->post('date_asof')){
			$date = date('d F Y',strtotime($this->input->post('date_asof')));
		}
		else{
			$date = date('d F Y');
		}


		$company_name = "";
		
		if( $this->input->post('company_list') && $this->input->post('company_list') != 0 ){

			$this->db->where('company_id',$this->input->post('company_list'));
			$company_record = $this->db->get('user_company')->row();
			$company_name = $company_record->company;

		}

		//$activeSheet->getHeaderFooter()->setOddHeader('&C&HPlease treat this document as confidential!');
		$activeSheet->getHeaderFooter()->setOddFooter('&L&BPrinted By:'.$userinfo->firstname.' '.$userinfo->lastname.' &RPage &P of &N');
		$activeSheet->setCellValueExplicit('A2', $company_name, PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('A3', 'MANPOWER COUNT as of '.$date, PHPExcel_Cell_DataType::TYPE_STRING);


		$objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArray);
		$objPHPExcel->getActiveSheet()->getStyle('A2')->applyFromArray($styleArray2);
		$objPHPExcel->getActiveSheet()->getStyle('A3')->applyFromArray($styleArray2);

		
		// contents.
		$line = 7;

		$hq_sum = array();
		$br_sum = array();
		$grand_sum = array();

		foreach ($query->result() as $row) {
			$sub_ctr   = 0;
			$alpha_ctr = 0;		
			$sum_ctr = 1;	
			
			foreach ($fields as $field) {

				if( $field != 'location' ){

					if ($alpha_ctr >= count($alphabet)) {
						$alpha_ctr = 0;
						$sub_ctr++;
					}

					if ($sub_ctr > 0) {
						$xcoor = $alphabet[$sub_ctr - 1] . $alphabet[$alpha_ctr];
					} else {
						$xcoor = $alphabet[$alpha_ctr];
					}

					$objPHPExcel->getActiveSheet()->getStyle($xcoor . $line)->applyFromArray($styleColumn);

					if( $field != 'department'){

						$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line,  $row->{$field});

						$hq_sum[$sum_ctr] += $row->{$field};
						$sum_ctr++;

					}
					else{

						$objPHPExcel->getActiveSheet()->setCellValueExplicit($xcoor . $line,  $row->{$field}, PHPExcel_Cell_DataType::TYPE_STRING);

					}

					$alpha_ctr++;

				}

			}

			$line++;
		}

		$sub_ctr   = 0;
		$alpha_ctr = 0;		
		$sum_ctr = 1;	

		foreach ($fields as $field) {

				if( $field != 'location' ){

					if ($alpha_ctr >= count($alphabet)) {
						$alpha_ctr = 0;
						$sub_ctr++;
					}

					if ($sub_ctr > 0) {
						$xcoor = $alphabet[$sub_ctr - 1] . $alphabet[$alpha_ctr];
					} else {
						$xcoor = $alphabet[$alpha_ctr];
					}

					$objPHPExcel->getActiveSheet()->getStyle($xcoor . $line)->applyFromArray($styleColumn);

					if( $field != 'department'){

						$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, $hq_sum[$sum_ctr]);
						$sum_ctr++;

					}
					else{

						$objPHPExcel->getActiveSheet()->setCellValueExplicit($xcoor . $line,  'Total HO', PHPExcel_Cell_DataType::TYPE_STRING);
						$objPHPExcel->getActiveSheet()->getStyle($xcoor . $line)->applyFromArray($styleArray);

					}

					$alpha_ctr++;

				}

		}

		$line++;

		$this->export2();

		if( $this->_query->num_rows() > 0 ){

			foreach ($this->_query->result() as $row) {
				$sub_ctr   = 0;
				$alpha_ctr = 0;	
				$sum_ctr = 1;	
				
				foreach ($fields as $field) {

					if( $field != 'location' ){

						if ($alpha_ctr >= count($alphabet)) {
							$alpha_ctr = 0;
							$sub_ctr++;
						}

						if ($sub_ctr > 0) {
							$xcoor = $alphabet[$sub_ctr - 1] . $alphabet[$alpha_ctr];
						} else {
							$xcoor = $alphabet[$alpha_ctr];
						}

						$objPHPExcel->getActiveSheet()->getStyle($xcoor . $line)->applyFromArray($styleColumn);

						if( $field != 'department'){

							$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line,  $row->{$field});


							if( $row->{$field} != "" ){
								$br_sum[$sum_ctr] += $row->{$field};
							}
							else{
								$br_sum[$sum_ctr] += 0;
							}

							$sum_ctr++;

						}
						else{

							$objPHPExcel->getActiveSheet()->setCellValueExplicit($xcoor . $line,  $row->{$field}, PHPExcel_Cell_DataType::TYPE_STRING);

						}

						$alpha_ctr++;

					}

				}

				$line++;
			}



			$sub_ctr   = 0;
			$alpha_ctr = 0;		
			$sum_ctr = 1;	

			foreach ($fields as $field) {

					if( $field != 'location' ){

						if ($alpha_ctr >= count($alphabet)) {
							$alpha_ctr = 0;
							$sub_ctr++;
						}

						if ($sub_ctr > 0) {
							$xcoor = $alphabet[$sub_ctr - 1] . $alphabet[$alpha_ctr];
						} else {
							$xcoor = $alphabet[$alpha_ctr];
						}

						$objPHPExcel->getActiveSheet()->getStyle($xcoor . $line)->applyFromArray($styleColumn);

						if( $field != 'department'){

							$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, $br_sum[$sum_ctr]);
							$sum_ctr++;

						}
						else{

							$objPHPExcel->getActiveSheet()->setCellValueExplicit($xcoor . $line,  'Total Branches', PHPExcel_Cell_DataType::TYPE_STRING);
							$objPHPExcel->getActiveSheet()->getStyle($xcoor . $line)->applyFromArray($styleArray);

						}

						$alpha_ctr++;

					}

			}

			$line++;	

		}

		$sub_ctr   = 0;
		$alpha_ctr = 0;		
		$sum_ctr = 1;	

		foreach ($fields as $field) {

				if( $field != 'location' ){

					if ($alpha_ctr >= count($alphabet)) {
						$alpha_ctr = 0;
						$sub_ctr++;
					}

					if ($sub_ctr > 0) {
						$xcoor = $alphabet[$sub_ctr - 1] . $alphabet[$alpha_ctr];
					} else {
						$xcoor = $alphabet[$alpha_ctr];
					}

					$objPHPExcel->getActiveSheet()->getStyle($xcoor . $line)->applyFromArray($styleColumn);

					if( $field != 'department'){

						$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, $hq_sum[$sum_ctr] + $br_sum[$sum_ctr]);
						$sum_ctr++;

					}
					else{

						$objPHPExcel->getActiveSheet()->setCellValueExplicit($xcoor . $line,  'Grand Total', PHPExcel_Cell_DataType::TYPE_STRING);
						$objPHPExcel->getActiveSheet()->getStyle($xcoor . $line)->applyFromArray($styleArray);

					}

					$alpha_ctr++;

				}

		}

		

		// Save it as an excel 2003 file
		$objWriter = IOFactory::createWriter($objPHPExcel, 'Excel5');

		header('Pragma: public');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Content-Type: application/force-download');
		header('Content-Type: application/octet-stream');
		header('Content-Type: application/download');
		header('Content-Disposition: attachment;filename='.date('Y-m-d').'Manpower_Count.xls');
		header('Content-Transfer-Encoding: binary');
		
		$objWriter->save('php://output');		
	}

	function _default_grid_buttons( $module_link = "", $related_field = "", $related_field_value = "", $addtext = "", $deltext = "", $container = "" )
	{
		// set default

		$buttons = "";                    
                
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