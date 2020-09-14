<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Promotion_worksheet extends MY_Controller
{
	function __construct()
    {
        parent::__construct();

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format	
		
		$this->listview_title = 'Promotion Worksheet';
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
		$data['content'] = 'employee/movement/promotion_listview';

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

		$this->_set_listview_query();

		if($this->input->post('company') != "")
			$this->db->like('employee_movement.hidden_id_current', 'company_id = '.$this->input->post('company'));

		if($this->input->post('date_year') != "")
			$this->db->where('employee_movement.transfer_effectivity_date <=', $this->input->post('date_year').'-12-31');

		$result = $this->db->get_where('employee_movement', array("deleted" => 0, "employee_movement_type_id" => 3));

		if( $this->db->_error_message() != "" ){
			$response->msg = $this->db->_error_message();
			$response->msg_type = "error";
		}
		else{        
	        $total_pages = $result->num_rows() > 0 ? ceil($result->num_rows()/$limit) : 0;
	        $response->page = $page > $total_pages ? $total_pages : $page;
	        $response->total = $total_pages;
	        $response->records = $total_num_rows;                        

	        $sql_string = "";
	        $sql_limit = "";

	        $response->msg = "";

	        if($this->input->post('company') != "")
				$this->db->like('employee_movement.hidden_id_current', 'company_id = '.$this->input->post('company'));

			if($this->input->post('date_year') != "")
				$this->db->where('employee_movement.transfer_effectivity_date <=', $this->input->post('date_year').'-12-31');

			$result = $this->db->get_where('employee_movement', array("deleted" => 0, "employee_movement_type_id" => 3))->result();

			$ctr = 0;
			foreach($result as $movement_data)
			{
				// $this->db->select('a_name.firstname, a_name.middlename, a_name.lastname, user_position.position, user_rank_code.job_rank_code, user_rank.job_rank_short_code, user_rank_range.job_rank_range, employee.employed_date, employee_movement.transfer_effectivity_date');
				$this->db->join('employee', 'employee_movement.employee_id = employee.employee_id', 'left');
				$this->db->join('user', 'employee_movement.employee_id = user.employee_id', 'left');
				if($movement_data->new_position_id != 0) {
					$this->db->join('user_position', 'employee_movement.new_position_id = user_position.position_id', 'left');
				} else {
					$this->db->join('user_position', 'employee_movement.current_position_id =  user_position.position_id', 'left');
				}
				if($movement_data->rank_code != 0)
					$this->db->join('user_rank_code', 'employee_movement.rank_code = user_rank_code.job_rank_code_id', 'left');

				if($movement_data->rank_id != 0)
					$this->db->join('user_rank', 'employee_movement.rank_id = user_rank.job_rank_id', 'left');
				if($movement_data->range_of_rank != 0)
					$this->db->join('user_rank_range', 'employee_movement.range_of_rank = user_rank_range.job_rank_range_id', 'left');

				$this->db->where('employee_movement.deleted', 0);
				$this->db->where('employee_movement.employee_movement_type_id', 3);

				if($movement_data->employee_type == 0)
				{
					if($this->input->post('employee_type') == 1)
					{
						$this->db->where('employee_movement.current_employee_type_dummy', 'Rank & File');
					}
					if($this->input->post('employee_type') == 2)
					{
						$where = "(".$this->db->dbprefix."employee_movement.current_employee_type_dummy = 'Supervisor' OR ".$this->db->dbprefix."employee_movement.current_employee_type_dummy = 'Officer')";
						$this->db->where($where);
					}
				} else {
					if($this->input->post('employee_type') == 1)
					{
						$this->db->where('employee_movement.employee_type', 3);
					}
					if($this->input->post('employee_type') == 2)
					{
						$where = "(".$this->db->dbprefix."employee_movement.employee_type = 1 OR ".$this->db->dbprefix."employee_movement.employee_type = 2)";
						$this->db->where($where);
					}
				}

				$result = $this->db->get('employee_movement');
		        // dbug($this->db->last_query());
		        if($result->num_rows() > 0)
		        {
			        foreach($result->result() as $data)
			        {
			        	$response->rows[$ctr]['cell'][0] = $data->firstname." ".$data->middlename." ".$data->lastname;
			        	$response->rows[$ctr]['cell'][1] = $data->position; // ($data->job_title_name == "" ? $data->position : $data->job_title_name);
			        	$response->rows[$ctr]['cell'][2] = $data->current_rank_code_dummy;
			        	$response->rows[$ctr]['cell'][3] = $data->current_rank_dummy;
			        	$response->rows[$ctr]['cell'][4] = $data->current_range_of_rank_dummy;
			        	$response->rows[$ctr]['cell'][5] = date($this->config->item('display_date_format'), strtotime($data->employed_date));
			        	$response->rows[$ctr]['cell'][6] = date($this->config->item('display_date_format'), strtotime($data->transfer_effectivity_date));

			        	$ctr++;
			    	}
			    }
			}
	    }
        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', array('json' => $response));
	}

	function _set_listview_query($listview_id = '', $view_actions = true, $msg = "") {
		$this->listview_column_names = array('Incumbent', 'Position Title', 'Rank Code', 'Rank', 'Range of Ranks', 'Date Hired', 'Date of Last Promotion');

		$this->listview_columns = array(
				array('name' => 'Incumbent', 'width' => '180','align' => 'center'),
				array('name' => 'Position Title'.$msg, 'width' => '180','align' => 'center'),
				array('name' => 'Rank Code', 'width' => '180','align' => 'center'),
				array('name' => 'Rank', 'width' => '180','align' => 'center'),
				array('name' => 'Range of Ranks', 'width' => '180','align' => 'center'),
				array('name' => 'Date Hired', 'width' => '180','align' => 'center'),
				array('name' => 'Date of Last Promotion', 'width' => '180','align' => 'center')
			);                                     
    }

    function promotion_export()
    {
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
		// $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(15);
		$objPHPExcel->getActiveSheet()->getDefaultColumnDimension('A')->setWidth(25);


		//Initialize style
		$singlerightborder = array(
			'borders' => array(
			    'right' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THIN
			    )
			  ),

			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
			)
		);

		$bottomstyle = array(
			'borders' => array(
			    'bottom' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THIN
			    )
			  ),

			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
			)
		);

		$styleArray = array(
			'borders' => array(
			    'allborders' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THIN
			    )
			  ),

			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
			)
		);

		$headerarray = array(
			'font' => array(
				'bold' => true,
			)
		);

		$cellarray = array(
			// 'fill' => array(
   //                  'type' => PHPExcel_Style_Fill::FILL_SOLID,
   //                  'color' => array('rgb'=>'CCC'),
   //          ),
			'borders' => array(
			    'allborders' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THIN
			    )
			  ),

			'font' => array(
				'bold' => true,
			),	

			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
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

		$topborder = array(
			'borders' => array(
				'top' => array(
					'style' => PHPExcel_Style_Border::BORDER_THICK)
				)
			);
		$leftborder = array(
			'borders' => array(
				'left' => array(
					'style' => PHPExcel_Style_Border::BORDER_THICK)
				)
			);
		$rightborder = array(
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
				),
			'borders' => array(
				'right' => array(
					'style' => PHPExcel_Style_Border::BORDER_THICK)
				)
			);
		$btmborder = array(
			// 'font' => array(
			// 	'bold' => true,
			// 	),
			'borders' => array(
				'bottom' => array(
					'style' => PHPExcel_Style_Border::BORDER_THICK)
				)
			);

		$title_for_all = "PIONEER GROUP";
		$activeSheet->setCellValueExplicit('A1', date('Y').' PROMOTION WORKSHEET', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('A2', ($this->input->post('company') == "" ? $title_for_all : $this->db->get_where('user_company', array("company_id" => $this->input->post('company')))->row()->company), PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('A3', ($this->input->post('employee_type') == 1 ? "NON-SUPERVISORY JOBS" : "MANAGER-SUPERVISOR JOBS"), PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('A4', "As of 31 December ".$this->input->post('date_year'), PHPExcel_Cell_DataType::TYPE_STRING);
		$objPHPExcel->getActiveSheet()->getStyle('A1:A4')->applyFromArray($headerarray);

		$activeSheet->setCellValueExplicit('A6', 'Incumbent', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('B6', 'Position Title as of December 31, '.$this->input->post('date_year'), PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('C6', 'Rank Code', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('D6', 'Rank', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('E6', "Range of Ranks", PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('F6', "Date Hired", PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('G6', "Date of Last Promotion", PHPExcel_Cell_DataType::TYPE_STRING);
		$objPHPExcel->getActiveSheet()->getStyle('A6:G6')->applyFromArray($styleArray);


		if($this->input->post('company') != "")
			$this->db->like('employee_movement.hidden_id_current', 'company_id = '.$this->input->post('company'));

		if($this->input->post('date_year') != "")
			$this->db->where('employee_movement.transfer_effectivity_date <=', $this->input->post('date_year').'-12-31');

		$result = $this->db->get_where('employee_movement', array("deleted" => 0, "employee_movement_type_id" => 3));
		if($result && $result->num_rows() > 0)
		{
			$ctr = 7;
			foreach($result->result() as $movement_data)
			{
				// $this->db->select('a_name.firstname, a_name.middlename, a_name.lastname, user_position.position, user_rank_code.job_rank_code, user_rank.job_rank_short_code, user_rank_range.job_rank_range, employee.employed_date, employee_movement.transfer_effectivity_date');
				$this->db->join('employee', 'employee_movement.employee_id = employee.employee_id', 'left');
				$this->db->join('user', 'employee_movement.employee_id = user.employee_id', 'left');
				if($movement_data->new_position_id != 0) {
					$this->db->join('user_position', 'employee_movement.new_position_id = user_position.position_id', 'left');
				} else {
					$this->db->join('user_position', 'employee_movement.current_position_id =  user_position.position_id', 'left');
				}
				if($movement_data->rank_code != 0)
					$this->db->join('user_rank_code', 'employee_movement.rank_code = user_rank_code.job_rank_code_id', 'left');

				if($movement_data->rank_id != 0)
					$this->db->join('user_rank', 'employee_movement.rank_id = user_rank.job_rank_id', 'left');
				if($movement_data->range_of_rank != 0)
					$this->db->join('user_rank_range', 'employee_movement.range_of_rank = user_rank_range.job_rank_range_id', 'left');

				$this->db->where('employee_movement.deleted', 0);
				$this->db->where('employee_movement.employee_movement_type_id', 3);

				if($movement_data->employee_type == 0)
				{
					if($this->input->post('employee_type') == 1)
					{
						$this->db->where('employee_movement.current_employee_type_dummy', 'Rank & File');
					}
					if($this->input->post('employee_type') == 2)
					{
						$where = "(".$this->db->dbprefix."employee_movement.current_employee_type_dummy = 'Supervisor' OR ".$this->db->dbprefix."employee_movement.current_employee_type_dummy = 'Officer')";
						$this->db->where($where);
					}
				} else {
					if($this->input->post('employee_type') == 1)
					{
						$this->db->where('employee_movement.employee_type', 3);
					}
					if($this->input->post('employee_type') == 2)
					{
						$where = "(".$this->db->dbprefix."employee_movement.employee_type = 1 OR ".$this->db->dbprefix."employee_movement.employee_type = 2)";
						$this->db->where($where);
					}
				}

				$result = $this->db->get('employee_movement');
		        // dbug($this->db->last_query());
		        if($result->num_rows() > 0)
		        {
			        foreach($result->result() as $data)
			        {
			        	$activeSheet->setCellValueExplicit('A'.$ctr, $data->firstname." ".$data->middlename." ".$data->lastname, PHPExcel_Cell_DataType::TYPE_STRING);
			        	$activeSheet->setCellValueExplicit('B'.$ctr, $data->position, PHPExcel_Cell_DataType::TYPE_STRING);
			        	$activeSheet->setCellValueExplicit('C'.$ctr, $data->current_rank_code_dummy, PHPExcel_Cell_DataType::TYPE_STRING);
			        	$activeSheet->setCellValueExplicit('D'.$ctr, $data->current_rank_dummy, PHPExcel_Cell_DataType::TYPE_STRING);
			        	$activeSheet->setCellValueExplicit('E'.$ctr, $data->current_range_of_rank_dummy, PHPExcel_Cell_DataType::TYPE_STRING);
			        	$activeSheet->setCellValueExplicit('F'.$ctr, date($this->config->item('display_date_format'), strtotime($data->employed_date)), PHPExcel_Cell_DataType::TYPE_STRING);
			        	$activeSheet->setCellValueExplicit('G'.$ctr, date($this->config->item('display_date_format'), strtotime($data->transfer_effectivity_date)), PHPExcel_Cell_DataType::TYPE_STRING);

			        	$ctr++;
			    	}
			    }
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
		header('Content-Disposition: attachment;filename=promotion_worksheet'.date('Y-m-d').'.xls');
		header('Content-Transfer-Encoding: binary');

		$path = 'uploads/dtr_summary/promotion_worksheet-'.strtotime(date('Y-m-d g:i:s')).'.xls';
		
		$objWriter->save($path);

		$response->msg_type = 'success';
		$response->data = $path;
		
		$this->load->view('template/ajax', array('json' => $response));
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