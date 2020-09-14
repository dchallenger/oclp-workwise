<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Import_dialed_hours extends MY_Controller
{
	function __construct()
    {
        parent::__construct();

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format	
		
		$this->listview_title = '';
		$this->listview_description = 'This module lists all defined (s).';
		$this->jqgrid_title = " List";
		$this->detailview_title = ' Info';
		$this->detailview_description = 'This page shows detailed information about a particular ';
		$this->editview_title = ' Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about ';	
    }

	// START - default module functions
	// default jqgrid controller method
	function index()
    {
    	$data['scripts'][] = multiselect_script();
		$data['scripts'][] = jqgrid_listview();	// load jqgrid js and default grid js
		$data['content'] = 'employee/Import_dialed_hours/listview';

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

		$data['department'] = $this->db->get('user_company_department')->result_array();

		if (!$this->superadmin){
			$this->db->where('reporting_to', $this->userinfo['position_id']);
			$this->db->where('deleted', 0);
			$result	= $this->db->get('user_position');			
			if ($result){
				$subordinates = $result->num_rows();
			}
		}
		else{
			$subordinates = 1;
		}

		$data['w_subordinates'] = $subordinates;

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
	// END - default module functions
	
	// START custom module funtions

	function listview()
	{
		$this->load->helper('time_upload');

        $page = $this->input->post('page');
        $limit = $this->input->post('rows'); // get how many rows we want to have into the grid
        $sidx = $this->input->post('sidx'); // get index row - i.e. user click to sort
        $sord = $this->input->post('sord'); // get the direction        
		
		if($this->input->post('_search') == "true")
			$search = $this->input->post('searchField') == "all" ? $this->_set_search_all_query() : $this->_set_specific_search_query();
		else
			$search = 1;		

		if ($this->input->post('date') != ''){
			$date = date('Y-m-d',strtotime($this->input->post('date')));
		}
		else{
			$date = date('Y-m-d');
		}

		$this->db->select('CONCAT(' . $this->db->dbprefix . 'user.firstname, " ",user.lastname) as "Agent Name"', false);
		$this->db->select(''.$this->db->dbprefix('dialed_hours'). '.date'.' as "Date",'.$this->db->dbprefix('dialed_hours'). '.dialed_hours'.' as "Dialed Hours",'.$this->db->dbprefix('dialed_hours'). '.remarks'.' as "Remarks",'.$this->db->dbprefix('dialed_hours'). '.oe_for_discrepancy'.' as "OPERATIONS EXPLANATION FOR DISCREPANCY"');		
		$this->db->from('user');
		$this->db->join($this->db->dbprefix('dialed_hours'),$this->db->dbprefix('user').'.employee_id = '.$this->db->dbprefix('dialed_hours').'.employee_id');
		$this->db->where('user.deleted = 0 AND '.$search);	

        $result = $this->db->get();   

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

	        $response->msg = "";

			$this->db->select('CONCAT(' . $this->db->dbprefix . 'user.firstname, " ",user.lastname) as "Agent Name"', false);
			$this->db->select(''.$this->db->dbprefix('dialed_hours'). '.dialed_hours_id'.' as "id",'.$this->db->dbprefix('dialed_hours'). '.date'.' as "Date",'.$this->db->dbprefix('dialed_hours'). '.dialed_hours'.' as "Dialed Hours",'.$this->db->dbprefix('dialed_hours'). '.remarks'.' as "Remarks",'.$this->db->dbprefix('dialed_hours'). '.oe_for_discrepancy'.' as "OPERATIONS EXPLANATION FOR DISCREPANCY"');		
			$this->db->from('user');
			$this->db->join($this->db->dbprefix('dialed_hours'),$this->db->dbprefix('user').'.employee_id = '.$this->db->dbprefix('dialed_hours').'.employee_id');
			$this->db->where('user.deleted = 0 AND '.$search);	

	        if ($this->input->post('sidx')) {
	            $sidx = $this->input->post('sidx');
	            $sord = $this->input->post('sord');
	            $this->db->order_by($sidx . ' ' . $sord);
	        }
	        else{
				$this->db->order_by('firstname ASC');
	        } 

	        $start = $limit * $page - $limit;
	        $this->db->limit($limit, $start);        
	        
	        $result = $this->db->get();
	        $ctr = 0;	        
	        foreach ($result->result() as $row) {
	        	$response->rows[$ctr]['id'] = $row->id;
	            $response->rows[$ctr]['cell'][0] = $row->{'Agent Name'};
	            $response->rows[$ctr]['cell'][1] = $row->{'Date'};
	            $response->rows[$ctr]['cell'][2] = gmdate("H:i:s", $row->{'Dialed Hours'} * 60 * 60);
	            $response->rows[$ctr]['cell'][3] = $row->{'Remarks'};
	            $response->rows[$ctr]['cell'][4] = $row->{'OPERATIONS EXPLANATION FOR DISCREPANCY'};
	            $ctr++;
	        }
	    }
        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', array('json' => $response));
	}

    function _set_listview_query($listview_id = '', $view_actions = true) {
		$this->listview_column_names = array('Agent Name', 'Date', 'Dialed Hours', 'Remarks', 'OE for Discrepancy'); //, 'Work Shift'

		$this->listview_columns = array(
				array('name' => 'agent_name', 'width' => '180','align' => 'center'),				
				array('name' => 'date'),
				array('name' => 'dialed_hours'),
				array('name' => 'remarks'),
				array('name' => 'oe_for_discrepancy')
			);                                     
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
		$search_string[] = $this->db->dbprefix .'user.firstname LIKE "%' . $value . '%"';
		$search_string[] = $this->db->dbprefix .'user.lastname LIKE "%' . $value . '%"';
		$search_string = '('. implode(' OR ', $search_string) .')';
		return $search_string;
	}

	function _default_grid_buttons( $module_link = "", $related_field = "", $related_field_value = "", $addtext = "", $deltext = "", $container = "" )
	{
		// set default
        $buttons .= "<div class='icon-label'><a class='icon-16-import module-import-dialed' container='".$container."' module_link='".$module_link."' href='javascript:void(0)'><span>Import</span></a></div>";
        
        $buttons .= "</div>";
                
		return $buttons;
	}	

	function module_import_options()
	{
		$this->load->helper('form');

		$data['content'] = 'employee/import_dialed_hours/import_boxy_dialed';
		$data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/js/editview.js"></script>';
		$data['module_id'] = $this->input->post('module_id');		

		if (IS_AJAX) {	
			$response['json']['html'] = $this->load->view($data['content'], $data, TRUE);
			$this->load->view('template/ajax', $response);
		}		
	}	

	function validate_file()
	{
		$module_id = $this->input->post('module_id');

		$config['upload_path'] 	 = 'uploads/system';
		$config['allowed_types'] = 'xls|xlsx|ods';
		$config['encrypt_name']  = TRUE;
		$config['max_size']		 = '2000';

		$this->load->library('upload', $config);

		// Upload the file.
		if ( ! $this->upload->do_upload('import_file'))
		{			
			$module = $this->hdicore->get_module($module_id);

			$this->session->set_flashdata('flashdata', $this->upload->display_errors());

			redirect (site_url($module->class_path));
		}
		else
		{
			$this->import_from_excel();
		}
	}	

	function import_from_excel()
	{
		$file = $this->upload->data();
		
		$this->load->library('PHPExcel');

		$objReader = $this->_get_reader($file['file_ext']);

		if (!$objReader) {
			show_error('Could not get reader.');
		}

		$objReader->setReadDataOnly(true);
		$objPHPExcel = $objReader->load( $file['full_path'] );
		$rowIterator = $objPHPExcel->getActiveSheet()->getRowIterator();

		$ctr = 0;	
		$import_data = array();

		foreach($rowIterator as $row){
			$cellIterator = $row->getCellIterator();
			$cellIterator->setIterateOnlyExistingCells(false); // Loop all cells, even if it is not set
			
			$rowIndex = $row->getRowIndex();

			// Build the array to insert and check for validation errors as well.
			foreach ($cellIterator as $cell) {			
				$import_data[$ctr][] = $cell->getCalculatedValue();
			}

			if ($rowIndex == 1) {
				unset($import_data[$ctr]);
			}

			$ctr++;
		}

		foreach ($import_data as $row) {
			$date = '';
			if ($row[1] != ''){	
				$date = PHPExcel_Shared_Date::ExcelToPHP($row[1]);
				$date = date('Y-m-d',$date);
			}
			$time = '';
			if ($row[2] != ''){	
				$time = PHPExcel_Style_NumberFormat::toFormattedString($row[2], 'hh:mm:ss');
			}	

			$biometric_id = preg_replace( '/\s+/', ' ', $row[0] );
			$biometric_id = str_replace('-', '', $biometric_id);

			$this->db->where('biometric_id',$biometric_id);
			$result = $this->db->get('employee');

			if ($result){
				if($result->num_rows() > 0){
					$employee_id = $result->row()->employee_id;
					$result_a = $this->db->get_where("dialed_hours",array("employee_id"=>$employee_id,"date"=>$date));
					if ($result_a->num_rows() > 0){
						$this->db->where(array("employee_id"=>$employee_id,"date"=>$date));
						$this->db->delete('dialed_hours');
					}
					$info = array("employee_id"=>$employee_id,"date"=>$date,"dialed_hours"=>$time,"remarks"=>$row[3],"oe_for_discrepancy"=>$row[4]);
					$this->db->insert('dialed_hours',$info);
				}
			}
		}

		unlink($file['full_path']);

		$module = $this->hdicore->get_module($this->input->post('module_id'));
		redirect (site_url($module->class_path));		
	}	

	/**
	 * Determine which excel reader class to use based on file type
	 * @param  string $ext
	 * @return object
	 */
	private function _get_reader($ext)
	{
		switch ($ext) {
			case '.xlsx': 
				$class = 'PHPExcel_Reader_Excel2007';
				break;
			case '.xls':
				$class = 'PHPExcel_Reader_Excel5';
				break;
			case '.ods':
				$class = 'PHPExcel_Reader_OOCalc';
				break;
			default:
				return FALSE;
		}

		return new $class();
	}
	// END custom module funtions
	
}

/* End of file */
/* Location: system/application */
?>