<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Import extends MY_Controller
{
	function __construct()
    {
        parent::__construct();

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format

		$this->listview_title = '';
		$this->listview_description = 'This module lists .';
		$this->jqgrid_title = "List";
		$this->detailview_title = ' Info';
		$this->detailview_description = 'This page shows detailed information about a ';
		$this->editview_title = ' Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about a ';
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
	function module_import_options()
	{
		$this->load->helper('form');

		$data['content'] = 'import_boxy';
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
			$this->process_file();
		}
	}

	function process_file()
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
				if (!$validations = $this->_get_fields($import_data[$ctr], $this->input->post('module_id'))) {
					$this->session->set_flashdata('flashdata', 'No matching field found on import file.');

					redirect (site_url($module->class_path));
				} else {
					// Get the key of the fields from the row so we know which cells to use.
					$valid_fields = $validations->result();
					foreach ($valid_fields as $field) {						
						$valid_cells[] = array_search($field->column, $import_data[$ctr]);
						$fields[] = $field->column;
					}

					$valid_fields = array_combine($fields, $valid_fields);
				}

				unset($import_data[$ctr]);
			}

			$ctr++;
		}

		$ctr = 0;

		// Remove non-matching cells.
		foreach ($import_data as $row) {		
			foreach ($row as $cell => $value) {
				$new_key = array_search($cell, $valid_cells);
				// Use !== absolutely false, sometimes $new_key == 0 which is also accepted.
				if ($new_key !== FALSE) {
					if($fields[$new_key] == 'payroll_date') {
						$validate_data[$ctr][$fields[$new_key]] = date ( 'Y-m-d', PHPExcel_Shared_Date::ExcelToPHP($value));
					} else {
						$validate_data[$ctr][$fields[$new_key]] = $value;
					}

					if($fields[$new_key] == 'id_number') {
						$employee_res = $this->db->query('SELECT * FROM '.$this->db->dbprefix('employee').' WHERE id_number = "'.$value.'"');

						if($employee_res->num_rows() > 0) {
							$employee_res2 = $employee_res->row();
							$validate_data[$ctr]['employee_id'] = $employee_res2->employee_id;
						} else {
							$validate_data[$ctr]['employee_id'] = '';
						}
					}
				}
			}

			$ctr++;
		}
		$valid_data = $this->_validate($validate_data, $valid_fields);
		$this->db->trans_begin();
		foreach ($valid_data as $table => $rows)
		{
			$this->db->insert_batch($table, $rows);
		}
		$this->db->trans_complete();
		if ($this->db->trans_status() === FALSE) {
		    $this->db->trans_rollback();
		} else {
		    $this->db->trans_commit();
		    
		}
		$this->session->set_flashdata('msg_type', 'success');
		$this->session->set_flashdata('flashdata', 'Import success.');
		unlink($file['full_path']);

		$module = $this->hdicore->get_module($this->input->post('module_id'));
		redirect (site_url($module->class_path));		
	}

	/**
	 * 'Sanitize' the data for insert.
	 * @param  array $data        
	 * @param  array $validations 
	 * @return array Multi-dimensional 
	 *         array => {table} => array => {fields}
	 */
	private function _validate($data, $validations)
	{
		$this->load->library('form_validation');

		$sanitized = array();
		
		foreach ($data as $key => $row) {
			foreach ($row as $column => $cell) {
				$field = $validations[$column];
				
				// Define server side validation. JMC
				$datatypes = explode('~', $field->datatype);
				$type = array();
				
				foreach ($datatypes as $datatype) {
					switch ($datatype) {
						case 'M':
							$type[] = 'required';
							break;
						case 'E':
							$type[] = 'valid_email';
							break;
						case 'N':
							$type[] = 'numeric';
							break;
						default:
							break;
					}
				}

				if (sizeof( $type ) > 0 ) {
					$type = 'trim|' . implode('|', $type) . '|xss_clean';
				} else {
					$type = 'trim|xss_clean';
				}

				$validate_fields[] = array('field' => $field->column, 'rules' => $type);

				$_POST[$field->column] = $cell;
			}
			
			$this->form_validation->set_rules($validate_fields);

			if ($this->form_validation->run()) {
				$sanitized[$field->table][]   = $row;				
			}			
		}

		return $sanitized;
	}

	/**
	 * Returns field types and validation using available fields from excel file and module.
	 * @param  array $fields
	 * @param  int $module_id 
	 * @return object
	 */
	private function _get_fields($fields, $module_id)
	{
		$this->db->select('column, table, datatype');
		$this->db->where('module_id', $module_id);
		$this->db->where_in('column', $fields);
		$this->db->where('deleted', 0);

		$fields = $this->db->get('field');

		if (!$fields && $fields->num_rows() > 0) {
			return FALSE;
		} else {
			return $fields;
		}
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