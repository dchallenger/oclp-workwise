<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Uniform_order extends MY_Controller
{
	function __construct()
    {
        parent::__construct();

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format	
		
		$this->listview_title = 'Uniform Order';
		$this->listview_description = 'This module lists all defined uniform order(s).';
		$this->jqgrid_title = "Uniform Order List";
		$this->detailview_title = 'Uniform Order Info';
		$this->detailview_description = 'This page shows detailed information about a particular uniform order.';
		$this->editview_title = 'Uniform Order Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about uniform order(s).';
    }

	// START - default module functions
	// default jqgrid controller method
	function index()
    {
    	$data['scripts'][] = multiselect_script();
		$data['scripts'][] = jqgrid_listview();	// load jqgrid js and default grid js
		$data['content'] = 'employee/uniform_order/listview';

		$data['scripts'][] = chosen_script();
		$data['jqgrid'] = 'employees/uniform_order/jqgrid';

		if($this->session->flashdata('flashdata')){
			$info['flashdata'] = $this->session->flashdata('flashdata');
			$data['flashdata'] = $this->load->view($this->userinfo['rtheme'].'/template/flashdata', $info, true);
		}

		//set default columnlist
		$this->listview_column_names = array('Name', 'Gender', 'Date', 'Remarks');

		$this->listview_columns = array(
				array('name' => 'name'),
				array('name' => 'gender'),
				array('name' => 'date'),
				array('name' => 'remarks')
		);

		//set grid buttons
		$data['jqg_buttons'] = $this->_default_grid_buttons();

		//set load jqgrid loadComplete callback
		$data['jqgrid_loadComplete'] = "";

		$company = array();
		$company[0] = "Please Select Company";

		$data['department'] = $this->db->get('user_company_department')->result_array();
        $data['company'] = $this->db->get('user_company')->result_array();
        $data['division'] = $this->db->get('user_company_division')->result_array();

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
				array('name' => 'name'),
				array('name' => 'gender'),
				array('name' => 'date'),
				array('name' => 'remarks')
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
		$this->db->select('CONCAT( u.firstname," ",u.lastname ) as "name"',FALSE);
		$this->db->select('u.sex as "gender"',FALSE);
		$this->db->select($this->module_table.'.date_ordered as "date"',FALSE);
		$this->db->select($this->module_table.'.remarks as "remarks"',FALSE);
		//$this->db->select($this->listview_qry, false);
		$this->db->from($this->module_table);
		$this->db->join('user u','u.employee_id = '.$this->db->dbprefix($this->module_table).'.employee_id','left');
		$this->db->join('user_company uc','uc.company_id = u.company_id','left');
		$this->db->join('user_company_department ucd','ucd.department_id = u.department_id','left');
		$this->db->join('user_company_division ucdv','ucdv.division_id = u.division_id','left');
		$this->db->where($this->db->dbprefix($this->module_table).'.deleted = 0 AND '.$search);

		if( $this->input->post('department') && $this->input->post('department') != 'null' ) $this->db->where_in('ucd.department_id ',$this->input->post('department'));
		if( $this->input->post('company') && $this->input->post('company') != 'null' ) $this->db->where_in('uc.company_id ',$this->input->post('company'));
		if( $this->input->post('division') && $this->input->post('division') != 'null' ) $this->db->where_in('ucdv.division_id ',$this->input->post('division'));
		if( $this->input->post('employee') && $this->input->post('employee') != 'null' ) $this->db->where_in('u.employee_id ',$this->input->post('employee'));
		if( $this->input->post('year') && $this->input->post('year') != 'null' ){
			 $this->db->where($this->module_table.'.year ',$this->input->post('year'));
		}
		else{
			$this->db->where($this->module_table.'.year ',date('Y'));
		}
		if( $this->input->post('gender') && $this->input->post('gender') != 'null' ) $this->db->where('u.sex ',$this->input->post('gender'));

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
			$this->db->select('CONCAT( u.firstname," ",u.lastname ) as "name"',FALSE);
			$this->db->select('u.sex as "gender"',FALSE);
			$this->db->select($this->module_table.'.date_ordered as "date"',FALSE);
			$this->db->select($this->module_table.'.remarks as "remarks"',FALSE);
			//$this->db->select($this->listview_qry, false);
			$this->db->from($this->module_table);
			$this->db->join('user u','u.employee_id = '.$this->db->dbprefix($this->module_table).'.employee_id','left');
			$this->db->join('user_company uc','uc.company_id = u.company_id','left');
			$this->db->join('user_company_department ucd','ucd.department_id = u.department_id','left');
			$this->db->join('user_company_division ucdv','ucdv.division_id = u.division_id','left');
			$this->db->where($this->db->dbprefix($this->module_table).'.deleted = 0 AND '.$search);

			if( $this->input->post('department') && $this->input->post('department') != 'null' ) $this->db->where_in('ucd.department_id ',$this->input->post('department'));
			if( $this->input->post('company') && $this->input->post('company') != 'null' ) $this->db->where_in('uc.company_id ',$this->input->post('company'));
			if( $this->input->post('division') && $this->input->post('division') != 'null' ) $this->db->where_in('ucdv.division_id ',$this->input->post('division'));
			if( $this->input->post('employee') && $this->input->post('employee') != 'null' ) $this->db->where_in('u.employee_id ',$this->input->post('employee'));
			if( $this->input->post('year') && $this->input->post('year') != 'null' ){
				 $this->db->where($this->module_table.'.year ',$this->input->post('year'));
			}
			else{
				$this->db->where($this->module_table.'.year ',date('Y'));
			}
			if( $this->input->post('gender') && $this->input->post('gender') != 'null' ) $this->db->where('u.sex ',$this->input->post('gender'));

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


							/*

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

							*/

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

	function get_division()
    {
        $division = $this->db->query('SELECT b.division_id, b.division FROM '.$this->db->dbprefix('user').' a LEFT JOIN  '.$this->db->dbprefix('user_company_division').' b ON a.division_id = b.division_id WHERE a.company_id IN ('.$this->input->post("div_id_delimited").') AND b.division_id IS NOT NULL GROUP BY b.division_id')->result_array();
        $html .= '<select id="division" style="width:400px;" name="division"><option value="">Please Select Division</option>';
            foreach($division as $division_record){
                $html .= '<option value="'.$division_record["division_id"].'">'.$division_record["division"].'</option>';
            }
        $html .= '</select>';   

        $data['html'] = $html;
        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);         
    }

    function get_department()
    {
        $department = $this->db->query('SELECT * FROM '.$this->db->dbprefix('user_company_department').' WHERE '.$this->db->dbprefix('user_company_department').'.division_id IN ('.$this->input->post("div_id_delimited").')')->result_array();
        $html .= '<select id="department" style="width:400px;" name="department"><option value="">Please Select Department</option>';
            foreach($department as $department_record){
                $html .= '<option value="'.$department_record["department_id"].'">'.$department_record["department"].'</option>';
            }
        $html .= '</select>';   

        $data['html'] = $html;
        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);         
    }

	

	function export() {	

		$this->db->select('CONCAT( u.firstname," ",u.lastname ) as "name"',FALSE);
		$this->db->select('u.sex as "gender"',FALSE);
		$this->db->select($this->module_table.'.date_ordered as "date"',FALSE);
		$this->db->select($this->module_table.'.remarks as "remarks"',FALSE);
		//$this->db->select($this->listview_qry, false);
		$this->db->from($this->module_table);
		$this->db->join('user u','u.employee_id = '.$this->db->dbprefix($this->module_table).'.employee_id','left');
		$this->db->join('user_company uc','uc.company_id = u.company_id','left');
		$this->db->join('user_company_department ucd','ucd.department_id = u.department_id','left');
		$this->db->join('user_company_division ucdv','ucdv.division_id = u.division_id','left');
		$this->db->where($this->db->dbprefix($this->module_table).'.deleted = 0 ');

		if( $this->input->post('department') && $this->input->post('department') != 'null' ) $this->db->where_in('ucd.department_id ',$this->input->post('department'));
		if( $this->input->post('company') && $this->input->post('company') != 'null' ) $this->db->where_in('uc.company_id ',$this->input->post('company'));
		if( $this->input->post('division') && $this->input->post('division') != 'null' ) $this->db->where_in('ucdv.division_id ',$this->input->post('division'));
		if( $this->input->post('employee') && $this->input->post('employee') != 'null' ) $this->db->where_in('u.employee_id ',$this->input->post('employee'));
		if( $this->input->post('yearpicker') && $this->input->post('year') != 'null' ){
			 $this->db->where($this->module_table.'.year ',$this->input->post('yearpicker'));
		}
		else{
			$this->db->where($this->module_table.'.year ',date('Y'));
		}
		if( $this->input->post('gender') && $this->input->post('gender') != 'null' ) $this->db->where('u.sex ',$this->input->post('gender'));

		//$this->db->order_by('u.birth_date','ASC');

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
		$styleArray = array(
			'font' => array(
				'bold' => true,
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			)
		);

		$styleTitleArray = array(
			'font' => array(
				'bold' => true,
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
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

			$activeSheet->setCellValueExplicit($xcoor . '6',  $field, PHPExcel_Cell_DataType::TYPE_STRING);


			$objPHPExcel->getActiveSheet()->getStyle($xcoor . '6')->applyFromArray($styleArray);
			
			$alpha_ctr++;
		}

		for($ctr=1; $ctr<6; $ctr++){

			$objPHPExcel->getActiveSheet()->mergeCells($alphabet[0].$ctr.':'.$alphabet[$alpha_ctr - 1].$ctr);


		}

		//$activeSheet->getHeaderFooter()->setOddHeader('&C&HPlease treat this document as confidential!');
		$activeSheet->getHeaderFooter()->setOddFooter('&L&BPrinted By:'.$userinfo->firstname.' '.$userinfo->lastname.' &RPage &P of &N');

		if($this->input->post('yearpicker')){
			$date = $this->input->post('yearpicker');
			$activeSheet->setCellValueExplicit('A1',$date.' CORPORATE ATTIRE', PHPExcel_Cell_DataType::TYPE_STRING);
		}
		else{
			$date = date('Y');
			$activeSheet->setCellValueExplicit('A1',$date.' CORPORATE ATTIRE', PHPExcel_Cell_DataType::TYPE_STRING);
		}

		if($this->input->post('gender_hidden')){
			$gender = strtoupper($this->input->post('gender_hidden'));
			$activeSheet->setCellValueExplicit('A2',$gender.' ORDER FORM', PHPExcel_Cell_DataType::TYPE_STRING);
		}
		else{
			$activeSheet->setCellValueExplicit('A2','ORDER FORM', PHPExcel_Cell_DataType::TYPE_STRING);
		}

		$comp_val = "";

		if( $this->input->post('company') ){

			$comp_id_list = $this->input->post('company');

			$comp_record = $this->db->get_where('user_company',array('company_id' =>$comp_id_list[0]))->row_array();
			$comp_val .= strtoupper($comp_record['company']);
			$activeSheet->setCellValueExplicit('A3','COMPANY : '.$comp_val, PHPExcel_Cell_DataType::TYPE_STRING);
		}
		else{
			$activeSheet->setCellValueExplicit('A3','COMPANY : ', PHPExcel_Cell_DataType::TYPE_STRING);
		}

		$div_dept = "";

		if($this->input->post('division')){

			$div_record = $this->db->get_where('user_company_division',array('division_id'=>$this->input->post('division')))->row_array();
			$div_dept .= strtoupper($div_record['division']);
		}

		if($this->input->post('department')){
			$dept_record = $this->db->get_where('user_company_department',array('department_id' => $this->input->post('department')))->row_array();
			$div_dept .= ' / '.strtoupper($dept_record['department']);
		}


		$activeSheet->setCellValueExplicit('A4','DIVISION / DEPARTMENT / BRANCH : '.$div_dept, PHPExcel_Cell_DataType::TYPE_STRING);



		//$activeSheet->setCellValue('A3', date('F d,Y',strtotime($this->input->post('date_period_start'))).' - '.date('F d,Y',strtotime($this->input->post('date_period_end'))));

		$objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($styleTitleArray);
		$objPHPExcel->getActiveSheet()->getStyle('A2')->applyFromArray($styleTitleArray);
		$objPHPExcel->getActiveSheet()->getStyle('A3')->applyFromArray($styleTitleArray);
		$objPHPExcel->getActiveSheet()->getStyle('A4')->applyFromArray($styleTitleArray);

		

		// contents.
		$line = 7;

		foreach ($query->result() as $row) {
			$sub_ctr   = 0;
			$alpha_ctr = 0;			
			
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


				if( $field == 'Department' ){

					$department_array = array();
					$department_record = "";
					$department_list = explode(',',$row->{$field});



					foreach( $department_list as $department ){
						if( $department > 0 ){
							$department_result = $this->db->query(" SELECT * FROM ".$this->db->dbprefix('user_company_department')." WHERE department_id = ".$department);
							$department_row = $department_result->row();

							array_push($department_array,$department_row->department);
						}
					}

					$department_record = implode(',',$department_array);

					$objPHPExcel->getActiveSheet()->setCellValueExplicit($xcoor . $line,  $department_record, PHPExcel_Cell_DataType::TYPE_STRING);


				}
				else if( $field == 'Company' ){

					$company_array = array();
					$company_record = "";
					$company_list = explode(',',$row->{$field});



					foreach( $company_list as $company ){
						if( $company > 0 ){
							$company_result = $this->db->query(" SELECT * FROM ".$this->db->dbprefix('user_company')." WHERE company_id = ".$company);
							$company_row = $company_result->row();

							array_push($company_array,$company_row->company);
						}
					}

					$company_record = implode(',',$company_array);

					$objPHPExcel->getActiveSheet()->setCellValueExplicit($xcoor . $line,  $company_record, PHPExcel_Cell_DataType::TYPE_STRING);

				}
				else if( $field == 'Division' ){

					$division_array = array();
					$division_record = "";
					$division_list = explode(',',$row->{$field});



					foreach( $division_list as $division ){
						if( $division > 0 ){
							$division_result = $this->db->query(" SELECT * FROM ".$this->db->dbprefix('user_company_division')." WHERE division_id = ".$division);
							$division_row = $division_result->row();

							array_push($division_array,$division_row->division);
						}
					}

					$division_record = implode(',',$division_array);

					$objPHPExcel->getActiveSheet()->setCellValueExplicit($xcoor . $line,  $division_record, PHPExcel_Cell_DataType::TYPE_STRING); 

				}
				else if( $field == 'Time In' && $row->{$field} !="" ){

					$objPHPExcel->getActiveSheet()->setCellValueExplicit($xcoor . $line, date($this->config->item('display_datetime_format'),strtotime($row->{$field})), PHPExcel_Cell_DataType::TYPE_STRING); 

				}
				else{
					$objPHPExcel->getActiveSheet()->setCellValueExplicit($xcoor . $line, $row->{$field}, PHPExcel_Cell_DataType::TYPE_STRING); 
				}

				
				

				$alpha_ctr++;
			}

			$line++;
		}

		// Save it as an excel 2003 file
		$objWriter = IOFactory::createWriter($objPHPExcel, 'Excel5');

		header('Pragma: public');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Content-Type: application/force-download');
		header('Content-Type: application/octet-stream');
		header('Content-Type: application/download');

		if($this->input->post('yearpicker')){
			header('Content-Disposition: attachment;filename=Uniform_Order_for_'.$this->input->post('yearpicker').'-'.date('Y-m-d').'.xls');
		}
		else{
			header('Content-Disposition: attachment;filename=Uniform_Order_for_'.date('Y').'-'.date('Y-m-d').'.xls');
		}


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