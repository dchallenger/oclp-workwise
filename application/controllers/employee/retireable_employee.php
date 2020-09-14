<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class retireable_employee extends MY_Controller
{
	function __construct()
    {
        parent::__construct();

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format	
		
		$this->listview_title = 'Retireable Employee';
		$this->listview_description = 'This module lists all defined retireable employee(s).';
		$this->jqgrid_title = "Retireable Employee List";
		$this->detailview_title = 'Retireable Employee Info';
		$this->detailview_description = 'This page shows detailed information about a particular retireable employee.';
		$this->editview_title = 'Retireable Employee Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about retireable employee(s).';

		$this->default_sort_col = array('fullname');
    }

	// START - default module functions
	// default jqgrid controller method
	function index()
    {
    	$data['scripts'][] = multiselect_script();
		$data['scripts'][] = jqgrid_listview();	// load jqgrid js and default grid js
		$data['content'] = 'employee/retireable_employee/listview';

		$data['scripts'][] = chosen_script();
		$data['jqgrid'] = 'employees/retireable_employees/jqgrid';

		if($this->session->flashdata('flashdata')){
			$info['flashdata'] = $this->session->flashdata('flashdata');
			$data['flashdata'] = $this->load->view($this->userinfo['rtheme'].'/template/flashdata', $info, true);
		}

		//set default columnlist
		$this->listview_column_names = array('Full Name', 'Birthdate', 'Date Hired', 'Age', 'Tenure');

		$this->listview_columns = array(
				array('name' => 'fullname'),
				array('name' => 'birthdate'),
				array('name' => 'datehired'),
				array('name' => 'age'),
				array('name' => 'tenure')
		);

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
				array('name' => 'fullname'),
				array('name' => 'birthdate'),
				array('name' => 'datehired'),
				array('name' => 'age'),
				array('name' => 'tenure')
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

		if( $this->input->post('years') && $this->input->post('years') != 'null'  ){

			$years = $this->input->post('years');
			$year_ctr = 2;
			$i_ctr = 1;

			while( $i_ctr <= $years ){

				$this->listview_columns[] = array('name' => 'age'.$year_ctr);
				$this->listview_columns[] = array('name' => 'tenure'.$year_ctr);

				$year_ctr++;
				$i_ctr++;

			}

		}

		// count query 
		//build query
		$this->_set_left_join();
		$this->db->select('CONCAT( u.firstname," ",u.lastname ) as "fullname"',FALSE); 
		$this->db->select('u.birth_date as "birthdate"');
		$this->db->select($this->db->dbprefix($this->module_table).'.employed_date as "datehired"');
		$this->db->select('u.birth_date AS "age"');
        $this->db->select($this->db->dbprefix($this->module_table).'.employed_date AS "tenure"');
        /*
		if( $this->input->post('date_asof') && $this->input->post('date_asof') != 'null' ){
			$this->db->select('ROUND(DATEDIFF( "'.date('Y-m-d h:i:s',strtotime($this->input->post('date_asof'))).'", u.birth_date ) / 365.25) AS "age"');
			$this->db->select('ROUND(DATEDIFF( "'.date('Y-m-d h:i:s',strtotime($this->input->post('date_asof'))).'", '.$this->db->dbprefix($this->module_table).'.employed_date ) / 365.25) AS "tenure"');
		}else{
			$this->db->select('ROUND(DATEDIFF( NOW(), u.birth_date ) / 365.25) AS "age"');
			$this->db->select('ROUND(DATEDIFF( NOW(), '.$this->db->dbprefix($this->module_table).'.employed_date ) / 365.25) AS "tenure"');
		}
		*/

		if( $this->input->post('years') && $this->input->post('years') != 'null' && $this->input->post('years') > 1  ){

			$years = $this->input->post('years');
			$year_ctr = 2;
			$i_ctr = 1;

			while( $year_ctr <= $years ){

                /*
				if( $this->input->post('date_asof') && $this->input->post('date_asof') != 'null' ){
					$this->db->select('( ROUND(DATEDIFF( "'.date('Y-m-d h:i:s',strtotime($this->input->post('date_asof'))).'", u.birth_date ) / 365.25) + '.$i_ctr.' ) AS "age'.$year_ctr.'"');
					$this->db->select('( ROUND(DATEDIFF( "'.date('Y-m-d h:i:s',strtotime($this->input->post('date_asof'))).'", '.$this->db->dbprefix($this->module_table).'.employed_date ) / 365.25) + '.$i_ctr.' ) AS "tenure'.$year_ctr.'"');
				}else{
					$this->db->select('( ROUND(DATEDIFF( NOW(), u.birth_date ) / 365.25) + '.$i_ctr.' ) AS "age'.$year_ctr.'"');
					$this->db->select('( ROUND(DATEDIFF( NOW(), '.$this->db->dbprefix($this->module_table).'.employed_date ) / 365.25) + '.$i_ctr.' ) AS "tenure'.$year_ctr.'"');
				}
				*/

				$this->db->select('u.birth_date AS "age'.$year_ctr.'"');
                $this->db->select($this->db->dbprefix($this->module_table).'.employed_date AS "tenure'.$year_ctr.'"');

				$year_ctr++;
				$i_ctr ++;

			}

		}

		//$this->db->select($this->listview_qry, false);
		$this->db->from($this->module_table);
		$this->db->join('user u','u.employee_id = '.$this->db->dbprefix($this->module_table).'.employee_id','left');
		$this->db->join('user_company uc','uc.company_id = u.company_id','left');
		$this->db->join('user_company_department ucd','ucd.department_id = u.department_id','left');
		$this->db->join('user_company_division ucdv','ucdv.division_id = u.division_id','left');
		$this->db->where($this->db->dbprefix($this->module_table).'.deleted = 0 AND '.$search);
		//$this->db->where('( YEAR( NOW() ) - YEAR( '.$this->db->dbprefix.$this->module_table.'.employed_date ) ) > 10');
		//$this->db->where('( ( YEAR( NOW() ) - YEAR( '.$this->db->dbprefix.$this->module_table.'.employed_date ) ) % 5 ) = 0 ');
		
		if( $this->input->post('department') && $this->input->post('department') != 'null' ) $this->db->where_in('ucd.department_id ',$this->input->post('department'));
		if( $this->input->post('company') && $this->input->post('company') != 'null' ) $this->db->where_in('uc.company_id ',$this->input->post('company'));
		if( $this->input->post('division') && $this->input->post('division') != 'null' ) $this->db->where_in('ucdv.division_id ',$this->input->post('division'));
		if( $this->input->post('employee') && $this->input->post('employee') != 'null' ) $this->db->where_in('u.employee_id ',$this->input->post('employee'));
		if( $this->input->post('retire_type') && $this->input->post('retire_type') != 'null' ){

			$retire_type = $this->input->post('retire_type');

			$type_condition = array();

			foreach( $retire_type as $type ){

				switch($type){

					case 1 :
						if( $this->input->post('date_asof') && $this->input->post('date_asof') != 'null' ){
							$type_condition[] = '( ROUND(DATEDIFF( "'.date('Y-m-d h:i:s',strtotime($this->input->post('date_asof'))).'", u.birth_date ) / 365.25) >= 60 )';
						}else{
							$type_condition[] = '( ROUND(DATEDIFF( NOW(), u.birth_date ) / 365.25) >= 60 )';
						}

						if( $this->input->post('years') && $this->input->post('years') != 'null' && $this->input->post('years') > 1  ){

							$year_ctr = 2;
							$iyear_ctr = 1;

							while( $year_ctr <= $this->input->post('years') ){

								if( $this->input->post('date_asof') && $this->input->post('date_asof') != 'null' ){
									$type_condition[] = '( ROUND(DATEDIFF( DATE_ADD("'.date('Y-m-d h:i:s',strtotime($this->input->post('date_asof'))).'", INTERVAL '.$iyear_ctr.' YEAR), u.birth_date ) / 365.25) >= 60 )'; 
								}else{
									$type_condition[] = '( ROUND(DATEDIFF( DATE_ADD(NOW(), INTERVAL '.$iyear_ctr.' YEAR), u.birth_date ) / 365.25) >= 60 )'; 
								}

								$iyear_ctr++;
								$year_ctr++;

							}


						}

					break;
					case 2:

						if( $this->input->post('date_asof') && $this->input->post('date_asof') != 'null' ){
							$type_condition[] ='( ( ROUND(DATEDIFF( "'.date('Y-m-d h:i:s',strtotime($this->input->post('date_asof'))).'", u.birth_date ) / 365.25) >= 55 AND ROUND(DATEDIFF( "'.date('Y-m-d h:i:s',strtotime($this->input->post('date_asof'))).'", u.birth_date ) / 365.25) <= 59 ) AND ROUND(DATEDIFF( "'.date('Y-m-d h:i:s',strtotime($this->input->post('date_asof'))).'", '.$this->db->dbprefix($this->module_table).'.employed_date ) / 365.25) >= 20 )';
						}else{
							$type_condition[] ='( ( ROUND(DATEDIFF( NOW(), u.birth_date ) / 365.25) >= 55 AND ROUND(DATEDIFF( NOW(), u.birth_date ) / 365.25) <= 59 ) AND ROUND(DATEDIFF( NOW(), '.$this->db->dbprefix($this->module_table).'.employed_date ) / 365.25) >= 20 )';
						}

						if( $this->input->post('years') && $this->input->post('years') != 'null' && $this->input->post('years') > 1  ){

							$year_ctr = 2;
							$iyear_ctr = 1;

							while( $year_ctr <= $this->input->post('years') ){

								if( $this->input->post('date_asof') && $this->input->post('date_asof') != 'null' ){
									$type_condition[] = '( ( ROUND(DATEDIFF( DATE_ADD("'.date('Y-m-d h:i:s',strtotime($this->input->post('date_asof'))).'", INTERVAL '.$iyear_ctr.' YEAR), u.birth_date ) / 365.25) >= 55 AND ROUND(DATEDIFF( DATE_ADD("'.date('Y-m-d h:i:s',strtotime($this->input->post('date_asof'))).'", INTERVAL '.$iyear_ctr.' YEAR), u.birth_date ) / 365.25) <= 59 ) AND ROUND(DATEDIFF( DATE_ADD("'.date('Y-m-d h:i:s',strtotime($this->input->post('date_asof'))).'", INTERVAL '.$iyear_ctr.' YEAR), '.$this->db->dbprefix($this->module_table).'.employed_date ) / 365.25) >= 20  )'; 
								}else{
									$type_condition[] = '( ( ROUND(DATEDIFF( DATE_ADD(NOW(), INTERVAL '.$iyear_ctr.' YEAR), u.birth_date ) / 365.25) >= 55 AND ROUND(DATEDIFF( DATE_ADD(NOW(), INTERVAL '.$iyear_ctr.' YEAR), u.birth_date ) / 365.25) <= 59 ) AND ROUND(DATEDIFF( DATE_ADD(NOW(), INTERVAL '.$iyear_ctr.' YEAR), '.$this->db->dbprefix($this->module_table).'.employed_date ) / 365.25) >= 20  )'; 
								}

								$iyear_ctr++;
								$year_ctr++;

							}

						}

					break;
					case 3:

						if( $this->input->post('date_asof') && $this->input->post('date_asof') != 'null' ){
							$type_condition[] ='( ( ROUND(DATEDIFF( "'.date('Y-m-d h:i:s',strtotime($this->input->post('date_asof'))).'", u.birth_date ) / 365.25) >= 50 AND ROUND(DATEDIFF( "'.date('Y-m-d h:i:s',strtotime($this->input->post('date_asof'))).'", u.birth_date ) / 365.25) <= 54 ) AND ROUND(DATEDIFF( "'.date('Y-m-d h:i:s',strtotime($this->input->post('date_asof'))).'", '.$this->db->dbprefix($this->module_table).'.employed_date ) / 365.25) >= 25 )';
						}else{
							$type_condition[] ='( ( ROUND(DATEDIFF( NOW(), u.birth_date ) / 365.25) >= 50 AND ROUND(DATEDIFF( NOW(), u.birth_date ) / 365.25) <= 54 ) AND ROUND(DATEDIFF( NOW(), '.$this->db->dbprefix($this->module_table).'.employed_date ) / 365.25) >= 25 )';
						}
					
						if( $this->input->post('years') && $this->input->post('years') != 'null' && $this->input->post('years') > 1  ){

							$year_ctr = 2;
							$iyear_ctr = 1;

							while( $year_ctr <= $this->input->post('years') ){


								if( $this->input->post('date_asof') && $this->input->post('date_asof') != 'null' ){
									$type_condition[] = '( ( ROUND(DATEDIFF( DATE_ADD("'.date('Y-m-d h:i:s',strtotime($this->input->post('date_asof'))).'", INTERVAL '.$iyear_ctr.' YEAR), u.birth_date ) / 365.25) >= 50 AND ROUND(DATEDIFF( DATE_ADD("'.date('Y-m-d h:i:s',strtotime($this->input->post('date_asof'))).'", INTERVAL '.$iyear_ctr.' YEAR), u.birth_date ) / 365.25) <= 54 ) AND ROUND(DATEDIFF( DATE_ADD("'.date('Y-m-d h:i:s',strtotime($this->input->post('date_asof'))).'", INTERVAL '.$iyear_ctr.' YEAR), '.$this->db->dbprefix($this->module_table).'.employed_date ) / 365.25) >= 25  )'; 
								}else{
									$type_condition[] = '( ( ROUND(DATEDIFF( DATE_ADD(NOW(), INTERVAL '.$iyear_ctr.' YEAR), u.birth_date ) / 365.25) >= 50 AND ROUND(DATEDIFF( DATE_ADD(NOW(), INTERVAL '.$iyear_ctr.' YEAR), u.birth_date ) / 365.25) <= 54 ) AND ROUND(DATEDIFF( DATE_ADD(NOW(), INTERVAL '.$iyear_ctr.' YEAR), '.$this->db->dbprefix($this->module_table).'.employed_date ) / 365.25) >= 25  )'; 
								}

								$iyear_ctr++;
								$year_ctr++;

							}
						}

					break;
				}
			}

			$type_condition = implode(' OR ',$type_condition);

			$this->db->where('( '.$type_condition.' )');

		}
		else{

			$type_condition = array();

			if( $this->input->post('date_asof') && $this->input->post('date_asof') != 'null' ){
				$type_condition[] = '( ROUND(DATEDIFF( "'.date('Y-m-d h:i:s',strtotime($this->input->post('date_asof'))).'", u.birth_date ) / 365.25) >= 60 )';
				$type_condition[] ='( ( ROUND(DATEDIFF( "'.date('Y-m-d h:i:s',strtotime($this->input->post('date_asof'))).'", u.birth_date ) / 365.25) >= 55 AND ROUND(DATEDIFF( "'.date('Y-m-d h:i:s',strtotime($this->input->post('date_asof'))).'", u.birth_date ) / 365.25) <= 59 ) AND ROUND(DATEDIFF( "'.date('Y-m-d h:i:s',strtotime($this->input->post('date_asof'))).'", '.$this->db->dbprefix($this->module_table).'.employed_date ) / 365.25) >= 20 )';
				$type_condition[] ='( ( ROUND(DATEDIFF( "'.date('Y-m-d h:i:s',strtotime($this->input->post('date_asof'))).'", u.birth_date ) / 365.25) >= 50 AND ROUND(DATEDIFF( "'.date('Y-m-d h:i:s',strtotime($this->input->post('date_asof'))).'", u.birth_date ) / 365.25) <= 54 ) AND ROUND(DATEDIFF( "'.date('Y-m-d h:i:s',strtotime($this->input->post('date_asof'))).'", '.$this->db->dbprefix($this->module_table).'.employed_date ) / 365.25) >= 25 )';
			}else{
				$type_condition[] = '( ROUND(DATEDIFF( NOW(), u.birth_date ) / 365.25) >= 60 )';
				$type_condition[] ='( ( ROUND(DATEDIFF( NOW(), u.birth_date ) / 365.25) >= 55 AND ROUND(DATEDIFF( NOW(), u.birth_date ) / 365.25) <= 59 ) AND ROUND(DATEDIFF( NOW(), '.$this->db->dbprefix($this->module_table).'.employed_date ) / 365.25) >= 20 )';
				$type_condition[] ='( ( ROUND(DATEDIFF( NOW(), u.birth_date ) / 365.25) >= 50 AND ROUND(DATEDIFF( NOW(), u.birth_date ) / 365.25) <= 54 ) AND ROUND(DATEDIFF( NOW(), '.$this->db->dbprefix($this->module_table).'.employed_date ) / 365.25) >= 25 )';
			}

			$type_condition = implode(' OR ',$type_condition);

			$this->db->where('( '.$type_condition.' )');

		}


		if(!empty( $this->filter ) ) 	$this->db->where( $this->filter );

		if (method_exists($this, '_set_filter')) {
			$this->_set_filter();
		}

		//get list
		$result = $this->db->get();
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

			// record query 
			//build query
			$this->_set_left_join();
		$this->db->select('CONCAT( u.firstname," ",u.lastname ) as "fullname"',FALSE); 
		$this->db->select('u.birth_date as "birthdate"');
		$this->db->select($this->db->dbprefix($this->module_table).'.employed_date as "datehired"');
        $this->db->select('u.birth_date AS "age"');
        $this->db->select($this->db->dbprefix($this->module_table).'.employed_date AS "tenure"');
        /*
		if( $this->input->post('date_asof') && $this->input->post('date_asof') != 'null' ){
			$this->db->select('ROUND(DATEDIFF( "'.date('Y-m-d h:i:s',strtotime($this->input->post('date_asof'))).'", u.birth_date ) / 365.25) AS "age"');
			$this->db->select('ROUND(DATEDIFF( "'.date('Y-m-d h:i:s',strtotime($this->input->post('date_asof'))).'", '.$this->db->dbprefix($this->module_table).'.employed_date ) / 365.25) AS "tenure"');
		}else{
			$this->db->select('ROUND(DATEDIFF( NOW(), u.birth_date ) / 365.25) AS "age"');
			$this->db->select('ROUND(DATEDIFF( NOW(), '.$this->db->dbprefix($this->module_table).'.employed_date ) / 365.25) AS "tenure"');
		}
		*/

		if( $this->input->post('years') && $this->input->post('years') != 'null' && $this->input->post('years') > 1  ){

			$years = $this->input->post('years');
			$year_ctr = 2;
			$i_ctr = 1;

			while( $year_ctr <= $years ){

                /*
				if( $this->input->post('date_asof') && $this->input->post('date_asof') != 'null' ){
					$this->db->select('( ROUND(DATEDIFF( "'.date('Y-m-d h:i:s',strtotime($this->input->post('date_asof'))).'", u.birth_date ) / 365.25) + '.$i_ctr.' ) AS "age'.$year_ctr.'"');
					$this->db->select('( ROUND(DATEDIFF( "'.date('Y-m-d h:i:s',strtotime($this->input->post('date_asof'))).'", '.$this->db->dbprefix($this->module_table).'.employed_date ) / 365.25) + '.$i_ctr.' ) AS "tenure'.$year_ctr.'"');
				}else{
					$this->db->select('( ROUND(DATEDIFF( NOW(), u.birth_date ) / 365.25) + '.$i_ctr.' ) AS "age'.$year_ctr.'"');
					$this->db->select('( ROUND(DATEDIFF( NOW(), '.$this->db->dbprefix($this->module_table).'.employed_date ) / 365.25) + '.$i_ctr.' ) AS "tenure'.$year_ctr.'"');
				}
				*/

				$this->db->select('u.birth_date AS "age'.$year_ctr.'"');
                $this->db->select($this->db->dbprefix($this->module_table).'.employed_date AS "tenure'.$year_ctr.'"');

				$year_ctr++;
				$i_ctr ++;

			}

		}

		//$this->db->select($this->listview_qry, false);
		$this->db->from($this->module_table);
		$this->db->join('user u','u.employee_id = '.$this->db->dbprefix($this->module_table).'.employee_id','left');
		$this->db->join('user_company uc','uc.company_id = u.company_id','left');
		$this->db->join('user_company_department ucd','ucd.department_id = u.department_id','left');
		$this->db->join('user_company_division ucdv','ucdv.division_id = u.division_id','left');
		$this->db->where($this->db->dbprefix($this->module_table).'.deleted = 0 AND '.$search);
		//$this->db->where('( YEAR( NOW() ) - YEAR( '.$this->db->dbprefix.$this->module_table.'.employed_date ) ) > 10');
		//$this->db->where('( ( YEAR( NOW() ) - YEAR( '.$this->db->dbprefix.$this->module_table.'.employed_date ) ) % 5 ) = 0 ');
		
		if( $this->input->post('department') && $this->input->post('department') != 'null' ) $this->db->where_in('ucd.department_id ',$this->input->post('department'));
		if( $this->input->post('company') && $this->input->post('company') != 'null' ) $this->db->where_in('uc.company_id ',$this->input->post('company'));
		if( $this->input->post('division') && $this->input->post('division') != 'null' ) $this->db->where_in('ucdv.division_id ',$this->input->post('division'));
		if( $this->input->post('employee') && $this->input->post('employee') != 'null' ) $this->db->where_in('u.employee_id ',$this->input->post('employee'));
		if( $this->input->post('retire_type') && $this->input->post('retire_type') != 'null' ){

			$retire_type = $this->input->post('retire_type');

			$type_condition = array();

			foreach( $retire_type as $type ){

				switch($type){

					case 1 :
						if( $this->input->post('date_asof') && $this->input->post('date_asof') != 'null' ){
							$type_condition[] = '( ROUND(DATEDIFF( "'.date('Y-m-d h:i:s',strtotime($this->input->post('date_asof'))).'", u.birth_date ) / 365.25) >= 60 )';
						}else{
							$type_condition[] = '( ROUND(DATEDIFF( NOW(), u.birth_date ) / 365.25) >= 60 )';
						}

						if( $this->input->post('years') && $this->input->post('years') != 'null' && $this->input->post('years') > 1  ){

							$year_ctr = 2;
							$iyear_ctr = 1;

							while( $year_ctr <= $this->input->post('years') ){

								if( $this->input->post('date_asof') && $this->input->post('date_asof') != 'null' ){
									$type_condition[] = '( ROUND(DATEDIFF( DATE_ADD("'.date('Y-m-d h:i:s',strtotime($this->input->post('date_asof'))).'", INTERVAL '.$iyear_ctr.' YEAR), u.birth_date ) / 365.25) >= 60 )'; 
								}else{
									$type_condition[] = '( ROUND(DATEDIFF( DATE_ADD(NOW(), INTERVAL '.$iyear_ctr.' YEAR), u.birth_date ) / 365.25) >= 60 )'; 
								}

								$iyear_ctr++;
								$year_ctr++;

							}


						}

					break;
					case 2:

						if( $this->input->post('date_asof') && $this->input->post('date_asof') != 'null' ){
							$type_condition[] ='( ( ROUND(DATEDIFF( "'.date('Y-m-d h:i:s',strtotime($this->input->post('date_asof'))).'", u.birth_date ) / 365.25) >= 55 AND ROUND(DATEDIFF( "'.date('Y-m-d h:i:s',strtotime($this->input->post('date_asof'))).'", u.birth_date ) / 365.25) <= 59 ) AND ROUND(DATEDIFF( "'.date('Y-m-d h:i:s',strtotime($this->input->post('date_asof'))).'", '.$this->db->dbprefix($this->module_table).'.employed_date ) / 365.25) >= 20 )';
						}else{
							$type_condition[] ='( ( ROUND(DATEDIFF( NOW(), u.birth_date ) / 365.25) >= 55 AND ROUND(DATEDIFF( NOW(), u.birth_date ) / 365.25) <= 59 ) AND ROUND(DATEDIFF( NOW(), '.$this->db->dbprefix($this->module_table).'.employed_date ) / 365.25) >= 20 )';
						}

						if( $this->input->post('years') && $this->input->post('years') != 'null' && $this->input->post('years') > 1  ){

							$year_ctr = 2;
							$iyear_ctr = 1;

							while( $year_ctr <= $this->input->post('years') ){

								if( $this->input->post('date_asof') && $this->input->post('date_asof') != 'null' ){
									$type_condition[] = '( ( ROUND(DATEDIFF( DATE_ADD("'.date('Y-m-d h:i:s',strtotime($this->input->post('date_asof'))).'", INTERVAL '.$iyear_ctr.' YEAR), u.birth_date ) / 365.25) >= 55 AND ROUND(DATEDIFF( DATE_ADD("'.date('Y-m-d h:i:s',strtotime($this->input->post('date_asof'))).'", INTERVAL '.$iyear_ctr.' YEAR), u.birth_date ) / 365.25) <= 59 ) AND ROUND(DATEDIFF( DATE_ADD("'.date('Y-m-d h:i:s',strtotime($this->input->post('date_asof'))).'", INTERVAL '.$iyear_ctr.' YEAR), '.$this->db->dbprefix($this->module_table).'.employed_date ) / 365.25) >= 20  )'; 
								}else{
									$type_condition[] = '( ( ROUND(DATEDIFF( DATE_ADD(NOW(), INTERVAL '.$iyear_ctr.' YEAR), u.birth_date ) / 365.25) >= 55 AND ROUND(DATEDIFF( DATE_ADD(NOW(), INTERVAL '.$iyear_ctr.' YEAR), u.birth_date ) / 365.25) <= 59 ) AND ROUND(DATEDIFF( DATE_ADD(NOW(), INTERVAL '.$iyear_ctr.' YEAR), '.$this->db->dbprefix($this->module_table).'.employed_date ) / 365.25) >= 20  )'; 
								}

								$iyear_ctr++;
								$year_ctr++;

							}

						}

					break;
					case 3:

						if( $this->input->post('date_asof') && $this->input->post('date_asof') != 'null' ){
							$type_condition[] ='( ( ROUND(DATEDIFF( "'.date('Y-m-d h:i:s',strtotime($this->input->post('date_asof'))).'", u.birth_date ) / 365.25) >= 50 AND ROUND(DATEDIFF( "'.date('Y-m-d h:i:s',strtotime($this->input->post('date_asof'))).'", u.birth_date ) / 365.25) <= 54 ) AND ROUND(DATEDIFF( "'.date('Y-m-d h:i:s',strtotime($this->input->post('date_asof'))).'", '.$this->db->dbprefix($this->module_table).'.employed_date ) / 365.25) >= 25 )';
						}else{
							$type_condition[] ='( ( ROUND(DATEDIFF( NOW(), u.birth_date ) / 365.25) >= 50 AND ROUND(DATEDIFF( NOW(), u.birth_date ) / 365.25) <= 54 ) AND ROUND(DATEDIFF( NOW(), '.$this->db->dbprefix($this->module_table).'.employed_date ) / 365.25) >= 25 )';
						}
					
						if( $this->input->post('years') && $this->input->post('years') != 'null' && $this->input->post('years') > 1  ){

							$year_ctr = 2;
							$iyear_ctr = 1;

							while( $year_ctr <= $this->input->post('years') ){


								if( $this->input->post('date_asof') && $this->input->post('date_asof') != 'null' ){
									$type_condition[] = '( ( ROUND(DATEDIFF( DATE_ADD("'.date('Y-m-d h:i:s',strtotime($this->input->post('date_asof'))).'", INTERVAL '.$iyear_ctr.' YEAR), u.birth_date ) / 365.25) >= 50 AND ROUND(DATEDIFF( DATE_ADD("'.date('Y-m-d h:i:s',strtotime($this->input->post('date_asof'))).'", INTERVAL '.$iyear_ctr.' YEAR), u.birth_date ) / 365.25) <= 54 ) AND ROUND(DATEDIFF( DATE_ADD("'.date('Y-m-d h:i:s',strtotime($this->input->post('date_asof'))).'", INTERVAL '.$iyear_ctr.' YEAR), '.$this->db->dbprefix($this->module_table).'.employed_date ) / 365.25) >= 25  )'; 
								}else{
									$type_condition[] = '( ( ROUND(DATEDIFF( DATE_ADD(NOW(), INTERVAL '.$iyear_ctr.' YEAR), u.birth_date ) / 365.25) >= 50 AND ROUND(DATEDIFF( DATE_ADD(NOW(), INTERVAL '.$iyear_ctr.' YEAR), u.birth_date ) / 365.25) <= 54 ) AND ROUND(DATEDIFF( DATE_ADD(NOW(), INTERVAL '.$iyear_ctr.' YEAR), '.$this->db->dbprefix($this->module_table).'.employed_date ) / 365.25) >= 25  )'; 
								}

								$iyear_ctr++;
								$year_ctr++;

							}
						}

					break;
				}
			}

			$type_condition = implode(' OR ',$type_condition);

			$this->db->where('( '.$type_condition.' )');

		}
		else{

			$type_condition = array();

			if( $this->input->post('date_asof') && $this->input->post('date_asof') != 'null' ){
				$type_condition[] = '( ROUND(DATEDIFF( "'.date('Y-m-d h:i:s',strtotime($this->input->post('date_asof'))).'", u.birth_date ) / 365.25) >= 60 )';
				$type_condition[] ='( ( ROUND(DATEDIFF( "'.date('Y-m-d h:i:s',strtotime($this->input->post('date_asof'))).'", u.birth_date ) / 365.25) >= 55 AND ROUND(DATEDIFF( "'.date('Y-m-d h:i:s',strtotime($this->input->post('date_asof'))).'", u.birth_date ) / 365.25) <= 59 ) AND ROUND(DATEDIFF( "'.date('Y-m-d h:i:s',strtotime($this->input->post('date_asof'))).'", '.$this->db->dbprefix($this->module_table).'.employed_date ) / 365.25) >= 20 )';
				$type_condition[] ='( ( ROUND(DATEDIFF( "'.date('Y-m-d h:i:s',strtotime($this->input->post('date_asof'))).'", u.birth_date ) / 365.25) >= 50 AND ROUND(DATEDIFF( "'.date('Y-m-d h:i:s',strtotime($this->input->post('date_asof'))).'", u.birth_date ) / 365.25) <= 54 ) AND ROUND(DATEDIFF( "'.date('Y-m-d h:i:s',strtotime($this->input->post('date_asof'))).'", '.$this->db->dbprefix($this->module_table).'.employed_date ) / 365.25) >= 25 )';
			}else{
				$type_condition[] = '( ROUND(DATEDIFF( NOW(), u.birth_date ) / 365.25) >= 60 )';
				$type_condition[] ='( ( ROUND(DATEDIFF( NOW(), u.birth_date ) / 365.25) >= 55 AND ROUND(DATEDIFF( NOW(), u.birth_date ) / 365.25) <= 59 ) AND ROUND(DATEDIFF( NOW(), '.$this->db->dbprefix($this->module_table).'.employed_date ) / 365.25) >= 20 )';
				$type_condition[] ='( ( ROUND(DATEDIFF( NOW(), u.birth_date ) / 365.25) >= 50 AND ROUND(DATEDIFF( NOW(), u.birth_date ) / 365.25) <= 54 ) AND ROUND(DATEDIFF( NOW(), '.$this->db->dbprefix($this->module_table).'.employed_date ) / 365.25) >= 25 )';
			}

			$type_condition = implode(' OR ',$type_condition);

			$this->db->where('( '.$type_condition.' )');

		}


			if(!empty( $this->filter ) ) 	$this->db->where( $this->filter );

			if (method_exists($this, '_set_filter')) {
				$this->_set_filter();
			}

			if (method_exists($this, '_custom_join')) {
				// Append fields to the SELECT statement via $this->listview_qry
				$this->_custom_join();
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


                            if( ( $detail['name'] == 'age' ) || ( substr($detail['name'],0,3) == 'age' ) ){

                                $bdate = $row[$detail['name']];

                                if( $this->input->post('date_asof') && $this->input->post('date_asof') != 'null' ){
                                    $date_asof = $this->input->post('date_asof');
                                }
                                else{
                                    $date_asof = date('Y-m-d');
                                }

                                $obdate = new DateTime( date('Y-m-d', strtotime($bdate) ) );
                                $odate_asof = new DateTime( date('Y-m-d', strtotime($date_asof) ) );
								$diff = $odate_asof->diff($obdate);
								
                                if( $detail['name'] == 'age' ){
								    $cell[$cell_ctr] = $diff->y.' year(s) '.$diff->m.' month(s)';
							    }
							    else{
                                    $no_y = substr($detail['name'],3) - 1;
                                    $cell[$cell_ctr] = $diff->y + $no_y.' year(s) '.$diff->m.' month(s)';
							    }


							    $cell_ctr++;

                            }
                            elseif( ( $detail['name'] == 'tenure' ) || ( substr($detail['name'],0,6) == 'tenure' ) ){

                                $edate = $row[$detail['name']];

                                if( $this->input->post('date_asof') && $this->input->post('date_asof') != 'null' ){
                                    $date_asof = $this->input->post('date_asof');
                                }
                                else{
                                    $date_asof = date('Y-m-d');
                                }

                                $oedate = new DateTime( date('Y-m-d', strtotime($edate) ) );
                                $odate_asof = new DateTime( date('Y-m-d', strtotime($date_asof) ) );
								$diff = $odate_asof->diff($oedate);
								
								if( $detail['name'] == 'tenure' ){
								    $cell[$cell_ctr] = $diff->y.' year(s) '.$diff->m.' month(s)';
							    }
							    else{
                                    $no_y = substr($detail['name'],6) - 1;
                                    $cell[$cell_ctr] = $diff->y + $no_y.' year(s) '.$diff->m.' month(s)';
							    }

							    $cell_ctr++;

                            }
                            else{
							    $cell[$cell_ctr] = $row[$detail['name']];
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

	function get_retireable_employee_filter(){
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

		$this->db->select('CONCAT( u.firstname," ",u.lastname ) as `Full Name`',FALSE); 
		$this->db->select('u.birth_date as "Birth Date"');
		$this->db->select($this->db->dbprefix($this->module_table).'.employed_date as `Date Hired`');
		$this->db->select('u.birth_date AS "Age"');
        $this->db->select($this->db->dbprefix($this->module_table).'.employed_date AS "Tenure"');
        /*
		if( $this->input->post('date_asof') && $this->input->post('date_asof') != 'null' ){
			$this->db->select('ROUND(DATEDIFF( "'.date('Y-m-d h:i:s',strtotime($this->input->post('date_asof'))).'", u.birth_date ) / 365.25) AS "age"');
			$this->db->select('ROUND(DATEDIFF( "'.date('Y-m-d h:i:s',strtotime($this->input->post('date_asof'))).'", '.$this->db->dbprefix($this->module_table).'.employed_date ) / 365.25) AS "tenure"');
		}else{
			$this->db->select('ROUND(DATEDIFF( NOW(), u.birth_date ) / 365.25) AS "age"');
			$this->db->select('ROUND(DATEDIFF( NOW(), '.$this->db->dbprefix($this->module_table).'.employed_date ) / 365.25) AS "tenure"');
		}
		*/

		if( $this->input->post('years') && $this->input->post('years') != 'null' && $this->input->post('years') > 1  ){

			$years = $this->input->post('years');
			$year_ctr = 2;
			$i_ctr = 1;

			while( $year_ctr <= $years ){

                /*
				if( $this->input->post('date_asof') && $this->input->post('date_asof') != 'null' ){
					$this->db->select('( ROUND(DATEDIFF( "'.date('Y-m-d h:i:s',strtotime($this->input->post('date_asof'))).'", u.birth_date ) / 365.25) + '.$i_ctr.' ) AS "age'.$year_ctr.'"');
					$this->db->select('( ROUND(DATEDIFF( "'.date('Y-m-d h:i:s',strtotime($this->input->post('date_asof'))).'", '.$this->db->dbprefix($this->module_table).'.employed_date ) / 365.25) + '.$i_ctr.' ) AS "tenure'.$year_ctr.'"');
				}else{
					$this->db->select('( ROUND(DATEDIFF( NOW(), u.birth_date ) / 365.25) + '.$i_ctr.' ) AS "age'.$year_ctr.'"');
					$this->db->select('( ROUND(DATEDIFF( NOW(), '.$this->db->dbprefix($this->module_table).'.employed_date ) / 365.25) + '.$i_ctr.' ) AS "tenure'.$year_ctr.'"');
				}
				*/

				$this->db->select('u.birth_date AS "age'.$year_ctr.'"');
                $this->db->select($this->db->dbprefix($this->module_table).'.employed_date AS "tenure'.$year_ctr.'"');

				$year_ctr++;
				$i_ctr ++;

			}

		}

		$this->db->from($this->module_table);
		$this->db->join('user u','u.employee_id = '.$this->db->dbprefix($this->module_table).'.employee_id','left');
		$this->db->join('user_company uc','uc.company_id = u.company_id','left');
		$this->db->join('user_company_department ucd','ucd.department_id = u.department_id','left');
		$this->db->join('user_company_division ucdv','ucdv.division_id = u.division_id','left');
		$this->db->where($this->db->dbprefix($this->module_table).'.deleted = 0');
		//$this->db->where('( YEAR( NOW() ) - YEAR( '.$this->db->dbprefix.$this->module_table.'.employed_date ) ) > 10');
		//$this->db->where('( ( YEAR( NOW() ) - YEAR( '.$this->db->dbprefix.$this->module_table.'.employed_date ) ) % 5 ) = 0 ');
		
		if( $this->input->post('department') && $this->input->post('department') != 'null' ) $this->db->where_in('ucd.department_id ',$this->input->post('department'));
		if( $this->input->post('company') && $this->input->post('company') != 'null' ) $this->db->where_in('uc.company_id ',$this->input->post('company'));
		if( $this->input->post('division') && $this->input->post('division') != 'null' ) $this->db->where_in('ucdv.division_id ',$this->input->post('division'));
		if( $this->input->post('employee') && $this->input->post('employee') != 'null' ) $this->db->where_in('u.employee_id ',$this->input->post('employee'));
		if( $this->input->post('retire_type') && $this->input->post('retire_type') != 'null' ){

			$retire_type = $this->input->post('retire_type');

			$type_condition = array();

			foreach( $retire_type as $type ){

				switch($type){

					case 1 :
						if( $this->input->post('date_asof') && $this->input->post('date_asof') != 'null' ){
							$type_condition[] = '( ROUND(DATEDIFF( "'.date('Y-m-d h:i:s',strtotime($this->input->post('date_asof'))).'", u.birth_date ) / 365.25) >= 60 )';
						}else{
							$type_condition[] = '( ROUND(DATEDIFF( NOW(), u.birth_date ) / 365.25) >= 60 )';
						}

						if( $this->input->post('years') && $this->input->post('years') != 'null' && $this->input->post('years') > 1  ){

							$year_ctr = 2;
							$iyear_ctr = 1;

							while( $year_ctr <= $this->input->post('years') ){

								if( $this->input->post('date_asof') && $this->input->post('date_asof') != 'null' ){
									$type_condition[] = '( ROUND(DATEDIFF( DATE_ADD("'.date('Y-m-d h:i:s',strtotime($this->input->post('date_asof'))).'", INTERVAL '.$iyear_ctr.' YEAR), u.birth_date ) / 365.25) >= 60 )'; 
								}else{
									$type_condition[] = '( ROUND(DATEDIFF( DATE_ADD(NOW(), INTERVAL '.$iyear_ctr.' YEAR), u.birth_date ) / 365.25) >= 60 )'; 
								}

								$iyear_ctr++;
								$year_ctr++;

							}


						}

					break;
					case 2:

						if( $this->input->post('date_asof') && $this->input->post('date_asof') != 'null' ){
							$type_condition[] ='( ( ROUND(DATEDIFF( "'.date('Y-m-d h:i:s',strtotime($this->input->post('date_asof'))).'", u.birth_date ) / 365.25) >= 55 AND ROUND(DATEDIFF( "'.date('Y-m-d h:i:s',strtotime($this->input->post('date_asof'))).'", u.birth_date ) / 365.25) <= 59 ) AND ROUND(DATEDIFF( "'.date('Y-m-d h:i:s',strtotime($this->input->post('date_asof'))).'", '.$this->db->dbprefix($this->module_table).'.employed_date ) / 365.25) >= 20 )';
						}else{
							$type_condition[] ='( ( ROUND(DATEDIFF( NOW(), u.birth_date ) / 365.25) >= 55 AND ROUND(DATEDIFF( NOW(), u.birth_date ) / 365.25) <= 59 ) AND ROUND(DATEDIFF( NOW(), '.$this->db->dbprefix($this->module_table).'.employed_date ) / 365.25) >= 20 )';
						}

						if( $this->input->post('years') && $this->input->post('years') != 'null' && $this->input->post('years') > 1  ){

							$year_ctr = 2;
							$iyear_ctr = 1;

							while( $year_ctr <= $this->input->post('years') ){

								if( $this->input->post('date_asof') && $this->input->post('date_asof') != 'null' ){
									$type_condition[] = '( ( ROUND(DATEDIFF( DATE_ADD("'.date('Y-m-d h:i:s',strtotime($this->input->post('date_asof'))).'", INTERVAL '.$iyear_ctr.' YEAR), u.birth_date ) / 365.25) >= 55 AND ROUND(DATEDIFF( DATE_ADD("'.date('Y-m-d h:i:s',strtotime($this->input->post('date_asof'))).'", INTERVAL '.$iyear_ctr.' YEAR), u.birth_date ) / 365.25) <= 59 ) AND ROUND(DATEDIFF( DATE_ADD("'.date('Y-m-d h:i:s',strtotime($this->input->post('date_asof'))).'", INTERVAL '.$iyear_ctr.' YEAR), '.$this->db->dbprefix($this->module_table).'.employed_date ) / 365.25) >= 20  )'; 
								}else{
									$type_condition[] = '( ( ROUND(DATEDIFF( DATE_ADD(NOW(), INTERVAL '.$iyear_ctr.' YEAR), u.birth_date ) / 365.25) >= 55 AND ROUND(DATEDIFF( DATE_ADD(NOW(), INTERVAL '.$iyear_ctr.' YEAR), u.birth_date ) / 365.25) <= 59 ) AND ROUND(DATEDIFF( DATE_ADD(NOW(), INTERVAL '.$iyear_ctr.' YEAR), '.$this->db->dbprefix($this->module_table).'.employed_date ) / 365.25) >= 20  )'; 
								}

								$iyear_ctr++;
								$year_ctr++;

							}

						}

					break;
					case 3:

						if( $this->input->post('date_asof') && $this->input->post('date_asof') != 'null' ){
							$type_condition[] ='( ( ROUND(DATEDIFF( "'.date('Y-m-d h:i:s',strtotime($this->input->post('date_asof'))).'", u.birth_date ) / 365.25) >= 50 AND ROUND(DATEDIFF( "'.date('Y-m-d h:i:s',strtotime($this->input->post('date_asof'))).'", u.birth_date ) / 365.25) <= 54 ) AND ROUND(DATEDIFF( "'.date('Y-m-d h:i:s',strtotime($this->input->post('date_asof'))).'", '.$this->db->dbprefix($this->module_table).'.employed_date ) / 365.25) >= 25 )';
						}else{
							$type_condition[] ='( ( ROUND(DATEDIFF( NOW(), u.birth_date ) / 365.25) >= 50 AND ROUND(DATEDIFF( NOW(), u.birth_date ) / 365.25) <= 54 ) AND ROUND(DATEDIFF( NOW(), '.$this->db->dbprefix($this->module_table).'.employed_date ) / 365.25) >= 25 )';
						}
					
						if( $this->input->post('years') && $this->input->post('years') != 'null' && $this->input->post('years') > 1  ){

							$year_ctr = 2;
							$iyear_ctr = 1;

							while( $year_ctr <= $this->input->post('years') ){


								if( $this->input->post('date_asof') && $this->input->post('date_asof') != 'null' ){
									$type_condition[] = '( ( ROUND(DATEDIFF( DATE_ADD("'.date('Y-m-d h:i:s',strtotime($this->input->post('date_asof'))).'", INTERVAL '.$iyear_ctr.' YEAR), u.birth_date ) / 365.25) >= 50 AND ROUND(DATEDIFF( DATE_ADD("'.date('Y-m-d h:i:s',strtotime($this->input->post('date_asof'))).'", INTERVAL '.$iyear_ctr.' YEAR), u.birth_date ) / 365.25) <= 54 ) AND ROUND(DATEDIFF( DATE_ADD("'.date('Y-m-d h:i:s',strtotime($this->input->post('date_asof'))).'", INTERVAL '.$iyear_ctr.' YEAR), '.$this->db->dbprefix($this->module_table).'.employed_date ) / 365.25) >= 25  )'; 
								}else{
									$type_condition[] = '( ( ROUND(DATEDIFF( DATE_ADD(NOW(), INTERVAL '.$iyear_ctr.' YEAR), u.birth_date ) / 365.25) >= 50 AND ROUND(DATEDIFF( DATE_ADD(NOW(), INTERVAL '.$iyear_ctr.' YEAR), u.birth_date ) / 365.25) <= 54 ) AND ROUND(DATEDIFF( DATE_ADD(NOW(), INTERVAL '.$iyear_ctr.' YEAR), '.$this->db->dbprefix($this->module_table).'.employed_date ) / 365.25) >= 25  )'; 
								}

								$iyear_ctr++;
								$year_ctr++;

							}
						}

					break;
				}
			}

			$type_condition = implode(' OR ',$type_condition);

			$this->db->where('( '.$type_condition.' )');

		}
		else{

			$type_condition = array();

			if( $this->input->post('date_asof') && $this->input->post('date_asof') != 'null' ){
				$type_condition[] = '( ROUND(DATEDIFF( "'.date('Y-m-d h:i:s',strtotime($this->input->post('date_asof'))).'", u.birth_date ) / 365.25) >= 60 )';
				$type_condition[] ='( ( ROUND(DATEDIFF( "'.date('Y-m-d h:i:s',strtotime($this->input->post('date_asof'))).'", u.birth_date ) / 365.25) >= 55 AND ROUND(DATEDIFF( "'.date('Y-m-d h:i:s',strtotime($this->input->post('date_asof'))).'", u.birth_date ) / 365.25) <= 59 ) AND ROUND(DATEDIFF( "'.date('Y-m-d h:i:s',strtotime($this->input->post('date_asof'))).'", '.$this->db->dbprefix($this->module_table).'.employed_date ) / 365.25) >= 20 )';
				$type_condition[] ='( ( ROUND(DATEDIFF( "'.date('Y-m-d h:i:s',strtotime($this->input->post('date_asof'))).'", u.birth_date ) / 365.25) >= 50 AND ROUND(DATEDIFF( "'.date('Y-m-d h:i:s',strtotime($this->input->post('date_asof'))).'", u.birth_date ) / 365.25) <= 54 ) AND ROUND(DATEDIFF( "'.date('Y-m-d h:i:s',strtotime($this->input->post('date_asof'))).'", '.$this->db->dbprefix($this->module_table).'.employed_date ) / 365.25) >= 25 )';
			}else{
				$type_condition[] = '( ROUND(DATEDIFF( NOW(), u.birth_date ) / 365.25) >= 60 )';
				$type_condition[] ='( ( ROUND(DATEDIFF( NOW(), u.birth_date ) / 365.25) >= 55 AND ROUND(DATEDIFF( NOW(), u.birth_date ) / 365.25) <= 59 ) AND ROUND(DATEDIFF( NOW(), '.$this->db->dbprefix($this->module_table).'.employed_date ) / 365.25) >= 20 )';
				$type_condition[] ='( ( ROUND(DATEDIFF( NOW(), u.birth_date ) / 365.25) >= 50 AND ROUND(DATEDIFF( NOW(), u.birth_date ) / 365.25) <= 54 ) AND ROUND(DATEDIFF( NOW(), '.$this->db->dbprefix($this->module_table).'.employed_date ) / 365.25) >= 25 )';
			}

			$type_condition = implode(' OR ',$type_condition);

			$this->db->where('( '.$type_condition.' )');

		}


		$this->db->order_by('u.birth_date','ASC');

		$query = $this->db->get();


		$fields = $query->list_fields();

		$this->_fields = $fields;
		$this->_export = $export;
		$this->_query  = $query;

		$this->_excel_export();
	}
	
	private function _excel_export()
	{

		$query  = $this->_query;
		$fields = $this->_fields;
		$export = $this->_export;

		$years = $this->input->post('years');

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
		$year_ctr = 0;
		$acolumn_ctr = 1;
		$tcolumn_ctr = 1;

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


		foreach ($fields as $field) {
			
			if ($alpha_ctr >= count($alphabet)) {
				$alpha_ctr = 0;
				$sub_ctr++;
			}

			if ($sub_ctr > 0) {
				$xcoor = $alphabet[$sub_ctr - 1] . $alphabet[$alpha_ctr];
			} else {
				$xcoor = $alphabet[$alpha_ctr];
			}

			$activeSheet->setCellValueExplicit($xcoor . '6', $field, PHPExcel_Cell_DataType::TYPE_STRING);


			if( $field == 'age'.$acolumn_ctr || $field == 'Age' ){

                if( $this->input->post('date_asof') && $this->input->post('date_asof') != 'null' ){
			        $date = date('m-d-Y',strtotime('+'.$year_ctr.' year',strtotime(date('Y-m-d',strtotime($this->input->post('date_asof'))))));
			    }else{
			    	$date = date('m-d-Y',strtotime('+'.$year_ctr.' year',strtotime(date('Y-m-d'))));
			    }

				$objPHPExcel->getActiveSheet()->getStyle($alphabet[$alpha_ctr].'5')->applyFromArray($styleHeader);
				$objPHPExcel->getActiveSheet()->getStyle($alphabet[$alpha_ctr+1].'5')->applyFromArray($styleHeader);
				$objPHPExcel->getActiveSheet()->mergeCells($alphabet[$alpha_ctr].'5:'.$alphabet[$alpha_ctr+1].'5');

				$activeSheet->setCellValueExplicit($xcoor . '5', 'As of '.$date, PHPExcel_Cell_DataType::TYPE_STRING);
				$activeSheet->setCellValueExplicit($xcoor . '6', 'Age', PHPExcel_Cell_DataType::TYPE_STRING);
				
				$year_ctr++;
				$acolumn_ctr++;

			}
			elseif( $field == 'tenure'.$tcolumn_ctr || $field == 'Tenure' ){

				$activeSheet->setCellValueExplicit($xcoor . '6', 'Tenure', PHPExcel_Cell_DataType::TYPE_STRING); 
				$tcolumn_ctr++;

			}

			$objPHPExcel->getActiveSheet()->getStyle($xcoor . '6')->applyFromArray($styleHeader);

			$alpha_ctr++;
		}


		for($ctr=1; $ctr<5; $ctr++){


			$objPHPExcel->getActiveSheet()->mergeCells($alphabet[0].$ctr.':'.$alphabet[$alpha_ctr - 1].$ctr);

		}


		//$activeSheet->getHeaderFooter()->setOddHeader('&C&HPlease treat this document as confidential!');
		$activeSheet->getHeaderFooter()->setOddFooter('&L&BPrinted By:'.$userinfo->firstname.' '.$userinfo->lastname.' &RPage &P of &N');

        if($this->input->post('date_asof')){
			$activeSheet->setCellValueExplicit('A2',  'Retireable Employee as of '.date($this->config->item('display_date_format'),strtotime($this->input->post('date_asof'))), PHPExcel_Cell_DataType::TYPE_STRING);
		}
		else{
			$activeSheet->setCellValueExplicit('A2',  'Retireable Employee as of '.date($this->config->item('display_date_format')), PHPExcel_Cell_DataType::TYPE_STRING);
		}


		$objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArray);
		$objPHPExcel->getActiveSheet()->getStyle('A2')->applyFromArray($styleArray);
		$objPHPExcel->getActiveSheet()->getStyle('A3')->applyFromArray($styleArray);

		// contents.
		$line = 7;

		//assigned column style
		$default_border = array(
		    'style' => PHPExcel_Style_Border::BORDER_THIN,
		    'color' => array('rgb'=>'000000')
		);

		$default_style = array(
		 'borders' => array(
		  'bottom' => $default_border,
		   'left' => $default_border,
		    'top' => $default_border,
		     'right' => $default_border,
		   )
		 );

		$resigned_style = array(
		 'borders' => array(
		  'bottom' => $default_border,
		   'left' => $default_border,
		    'top' => $default_border,
		     'right' => $default_border,
		   ),
		 'fill' => array(
		  'type' => PHPExcel_Style_Fill::FILL_SOLID,
		   'color' => array(
		   	'rgb'=>'99CCFF'
		   	), 
		   )
		 );

		$ea1_style = array(
		 'borders' => array(
		  'bottom' => $default_border,
		   'left' => $default_border,
		    'top' => $default_border,
		     'right' => $default_border,
		   ),
		 'fill' => array(
		  'type' => PHPExcel_Style_Fill::FILL_SOLID,
		   'color' => array(
		   	'rgb'=>'FFFF99'
		   	), 
		   )
		 );

		$ea2_style = array(
		 'borders' => array(
		  'bottom' => $default_border,
		   'left' => $default_border,
		    'top' => $default_border,
		     'right' => $default_border,
		   ),
		 'fill' => array(
		  'type' => PHPExcel_Style_Fill::FILL_SOLID,
		   'color' => array(
		   	'rgb'=>'CCFFCC'
		   	), 
		   )
		 );


		foreach ($query->result() as $row) {
			$sub_ctr   = 0;
			$alpha_ctr = 0;		
			$acolumn_ctr = 1;
			$tcolumn_ctr = 1;
			$last_field_key = count($fields);
			$last_field_key_ctr = 1;
			$last_resigned_type = 0;
			
			foreach ($fields as $field) {

				if ($alpha_ctr >= count($alphabet)) {
					$alpha_ctr = 0;
					$sub_ctr++;
				}

				if ($sub_ctr > 0) {
					$xcoor = $alphabet[$sub_ctr - 1] . $alphabet[$alpha_ctr];
				} else {
					$xcoor = $alphabet[$alpha_ctr];
				}



				if( $field == 'age'.$acolumn_ctr || $field == 'Age' ){

                    $bdate = $row->{$field};

                    if( $this->input->post('date_asof') && $this->input->post('date_asof') != 'null' ){
                        $date_asof = $this->input->post('date_asof');
                    }
                    else{
                        $date_asof = date('Y-m-d');
                    }

                        $obdate = new DateTime( date('Y-m-d', strtotime($bdate) ) );
                        $odate_asof = new DateTime( date('Y-m-d', strtotime($date_asof) ) );
						$diff = $odate_asof->diff($obdate);
								
                    if( $field == 'Age' ){
                    	$sum_year = $diff->y;
						$value = $diff->y.' year(s) '.$diff->m.' month(s)';
					}
					else{
                        $no_y = substr($field,3) - 1;
                        $sum_year = $diff->y + $no_y;
                        $value = $diff->y + $no_y.' year(s) '.$diff->m.' month(s)';
					}

					$objPHPExcel->getActiveSheet()->setCellValueExplicit($xcoor . $line, $value, PHPExcel_Cell_DataType::TYPE_STRING); 

					if( $sum_year >= 60 ){

						$objPHPExcel->getActiveSheet()->getStyle($xcoor . $line)->applyFromArray($resigned_style);
						$objPHPExcel->getActiveSheet()->getStyle(($alphabet[$alpha_ctr+1]).$line)->applyFromArray($resigned_style);

						$last_resigned_type = 1;

					}
					elseif( $sum_year >= 55 && $sum_year <= 59 ){

						$objPHPExcel->getActiveSheet()->getStyle($xcoor . $line)->applyFromArray($ea1_style);
						$objPHPExcel->getActiveSheet()->getStyle(($alphabet[$alpha_ctr+1]).$line)->applyFromArray($ea1_style);

						$last_resigned_type = 2;

					}
					elseif( $sum_year >= 50 && $sum_year <= 54 ){

						$objPHPExcel->getActiveSheet()->getStyle($xcoor . $line)->applyFromArray($ea2_style);
						$objPHPExcel->getActiveSheet()->getStyle(($alphabet[$alpha_ctr+1]).$line)->applyFromArray($ea2_style);

						$last_resigned_type = 3;

					}
					else{

						$objPHPExcel->getActiveSheet()->getStyle($xcoor . $line)->applyFromArray($default_style);

						$last_resigned_type = 0;

					}

					
					$year_ctr++;
					$acolumn_ctr++;

				}
				else{

					if( $field == 'tenure'.$tcolumn_ctr || $field == 'Tenure' ){

                        $edate = $row->{$field};

                        if( $this->input->post('date_asof') && $this->input->post('date_asof') != 'null' ){
                            $date_asof = $this->input->post('date_asof');
                        }
                        else{
                            $date_asof = date('Y-m-d');
                        }

                            $oedate = new DateTime( date('Y-m-d', strtotime($edate) ) );
                            $odate_asof = new DateTime( date('Y-m-d', strtotime($date_asof) ) );
					    	$diff = $odate_asof->diff($oedate);
								
                        if( $field == 'Tenure' ){
                        	$sum_year = $diff->y;
					    	$value = $diff->y.' year(s) '.$diff->m.' month(s)';
					    }
				    	else{
                            $no_y = substr($field,6) - 1;
                            $sum_year = $diff->y + $no_y;
                            $value = $diff->y + $no_y.' year(s) '.$diff->m.' month(s)';
				    	}

				    	$objPHPExcel->getActiveSheet()->setCellValueExplicit($xcoor . $line, $value, PHPExcel_Cell_DataType::TYPE_STRING); 

						$tcolumn_ctr++;

					}
					else{

						$objPHPExcel->getActiveSheet()->setCellValueExplicit($xcoor . $line, $row->{$field}, PHPExcel_Cell_DataType::TYPE_STRING); 

					}

					$objPHPExcel->getActiveSheet()->getStyle($xcoor . $line)->applyFromArray($default_style);

					if( $last_field_key == $last_field_key_ctr ){

						if( $last_resigned_type == 1 ){

							$objPHPExcel->getActiveSheet()->getStyle(($alphabet[0]).$line)->applyFromArray($resigned_style);
							$objPHPExcel->getActiveSheet()->getStyle(($alphabet[1]).$line)->applyFromArray($resigned_style);
							$objPHPExcel->getActiveSheet()->getStyle(($alphabet[2]).$line)->applyFromArray($resigned_style);

						}
						elseif( $last_resigned_type == 2 ){

							$objPHPExcel->getActiveSheet()->getStyle(($alphabet[0]).$line)->applyFromArray($ea1_style);
							$objPHPExcel->getActiveSheet()->getStyle(($alphabet[1]).$line)->applyFromArray($ea1_style);
							$objPHPExcel->getActiveSheet()->getStyle(($alphabet[2]).$line)->applyFromArray($ea1_style);


						}
						elseif( $last_resigned_type == 3 ){

							$objPHPExcel->getActiveSheet()->getStyle(($alphabet[0]).$line)->applyFromArray($ea2_style);
							$objPHPExcel->getActiveSheet()->getStyle(($alphabet[1]).$line)->applyFromArray($ea2_style);
							$objPHPExcel->getActiveSheet()->getStyle(($alphabet[2]).$line)->applyFromArray($ea2_style);


						}

					}

				}


				$last_field_key_ctr++;
				$alpha_ctr++;
			}

			$line++;
		}

		$resigned_style = array(
		 'fill' => array(
		  'type' => PHPExcel_Style_Fill::FILL_SOLID,
		   'color' => array(
		   	'rgb'=>'99CCFF'
		   	), 
		   )
		 );

		$ea1_style = array(
		 'fill' => array(
		  'type' => PHPExcel_Style_Fill::FILL_SOLID,
		   'color' => array(
		   	'rgb'=>'FFFF99'
		   	), 
		   )
		 );

		$ea2_style = array(
		 'fill' => array(
		  'type' => PHPExcel_Style_Fill::FILL_SOLID,
		   'color' => array(
		   	'rgb'=>'CCFFCC'
		   	), 
		   )
		 );

		$lctr = 1;

		for($ctr=$line+1; $ctr<$line+6; $ctr++){

			$objPHPExcel->getActiveSheet()->mergeCells($alphabet[0].$ctr.':'.$alphabet[5].$ctr);

			switch($lctr){

				case 1:
					$objPHPExcel->getActiveSheet()->setCellValueExplicit($alphabet[0].$ctr, 'Legend', PHPExcel_Cell_DataType::TYPE_STRING); 

				break;
				case 2:
					$objPHPExcel->getActiveSheet()->setCellValueExplicit($alphabet[0].$ctr, 'Blue shade - qualified for normal retirement due to at least 60 years of age and 10 years of service', PHPExcel_Cell_DataType::TYPE_STRING); 
					$objPHPExcel->getActiveSheet()->getStyle($alphabet[0].$ctr)->applyFromArray($resigned_style);
				break;
				case 3:
					$objPHPExcel->getActiveSheet()->setCellValueExplicit($alphabet[0].$ctr, 'Yellow shade - qualified for early retirement due to at least 55 years of age and 20 years of service', PHPExcel_Cell_DataType::TYPE_STRING); 
					$objPHPExcel->getActiveSheet()->getStyle($alphabet[0].$ctr)->applyFromArray($ea1_style);
				break;
				case 4:
					$objPHPExcel->getActiveSheet()->setCellValueExplicit($alphabet[0].$ctr, 'Green shade - qualified for early retirement due to at least 50 years of age and 25 years of service', PHPExcel_Cell_DataType::TYPE_STRING);
					$objPHPExcel->getActiveSheet()->getStyle($alphabet[0].$ctr)->applyFromArray($ea2_style);
				break;
				case 5:
					$objPHPExcel->getActiveSheet()->setCellValueExplicit($alphabet[0].$ctr, '* with cumulative length of service', PHPExcel_Cell_DataType::TYPE_STRING); 
				break;

			}

			$lctr++;

		}


		// Save it as an excel 2003 file
		$objWriter = IOFactory::createWriter($objPHPExcel, 'Excel5');

		header('Pragma: public');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Content-Type: application/force-download');
		header('Content-Type: application/octet-stream');
		header('Content-Type: application/download');
		header('Content-Disposition: attachment;filename=' . date('Y-m-d') . ' ' .url_title('Retireable Employee') . '.xls');
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