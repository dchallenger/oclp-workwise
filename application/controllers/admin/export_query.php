<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Export_query extends MY_Controller
{
	private $_fields, $_export, $_query;

	function __construct()
    {
        parent::__construct();

        $this->load->helper('form');

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format

		$this->listview_title = '';
		$this->listview_description = 'This module lists export queries.';
		$this->jqgrid_title = " List";
		$this->detailview_title = ' Info';
		$this->detailview_description = 'This page shows detailed information about an export query.';
		$this->editview_title = ' Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about an export query.';
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
		$query  = str_replace('{dbprefix}', $this->db->dbprefix, $this->input->post('query_string'));
		$result = $this->db->simple_query($query);

		if ($this->db->_error_message() != '') {
			$response->msg = $this->db->_error_message();
			$response->msg_type = 'error';

			$this->load->view('template/ajax', array('json' => $response));
		} else {
			parent::ajax_save();

			$result = $this->db->query($query);
			$fields = $result->list_fields();			

			$this->db->where('export_query_id', $this->key_field_val);
			$this->db->delete('export_query_fields');

			$export_fields = array();
			$ctr = 1;

			foreach ($fields as $field) {
				$export_fields[$ctr]['export_query_id'] = $this->key_field_val;
				$export_fields[$ctr]['field']			= $field;

				$ctr++;
			}

			$this->db->insert_batch('export_query_fields', $export_fields);			
		}

		//additional module save routine here

	}

	function delete()
	{
		parent::delete();

		//additional module delete routine here
	}
	// END - default module functions

	// START custom module funtions

	function export() {	
		$query_id = $this->input->post($this->key_field);

		if (!$query_id || $query_id < 0) {
			show_error('No ID specified');
		}

		$this->db->where($this->key_field, $query_id);

		$result = $this->db->get($this->module_table);
		$export = $result->row();

		$query  = $this->db->query(str_replace('{dbprefix}', $this->db->dbprefix, $export->query_string));

		if (count($this->input->post('fields')) == 0) {		
			$fields = $query->list_fields();
		} else {
			$fields = $this->input->post('fields');
		}

		$this->_fields = $fields;
		$this->_export = $export;
		$this->_query  = $query;

		switch ($this->input->post('export_type')) {
			case 'excel': $this->_excel_export(); break;
			case 'html' : $this->_html_export(); break;
			case 'pdf' : $this->_pdf_export(); break;
		}
	}

	function module_export_options() {				
		$data['content'] = 'export_boxy';
		$data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/js/editview.js"></script>';
		$data['module_id'] = $this->input->post('module_id');		

		if (IS_AJAX) {	
			$response['json']['html'] = $this->load->view($data['content'], $data, TRUE);
			$this->load->view('template/ajax', $response);
		}
	}

	function get_query_fields($record_id = 0) {
		if ($record_id == 0) {
			$record_id = $this->input->post($this->key_field);
		}

		$this->db->where($this->key_field, $record_id);
		$this->db->limit(1);
		$export_query = $this->db->get($this->module_table)->row_array();		

		$this->db->where($this->key_field, $record_id);
		$result = $this->db->get('export_query_fields')->result();

		$fields = array();
		foreach ($result as $field) {
			$fields[$field->field] = $field->field;
		}

		if (!IS_AJAX) {
			return 	array(					
					'fields' 		 => $fields,
					'description'    => $export_query['description'],
					$this->key_field => $export_query[$this->key_field]
				);
		} else {
			$data['html'] = $this->load->view(
								'export_fields', 
								array(
									'fields' 		 => $fields, 
									'description'    => $export_query['description'],
									$this->key_field => $export_query[$this->key_field]
									), 
								TRUE
							);

			$this->load->view('template/ajax', array('json' => $data));
		}
	}

	function options($record_id = 0) {			
		if ($record_id == 0) {
			$record_id = $this->input->post($this->key_field);
		}

		$data['content'] = 'export';
		$data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/js/editview.js"></script>';

		$data = array_merge($data, $this->get_query_fields($record_id));

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

	private function _html_export()
	{
		$this->load->view('template/export_table', array('table' => $this->_get_html()));
	}

	private function _pdf_export()
	{
		$export = $this->_export;

		$this->load->library('pdf');
		$html = $this->_get_html();

		// Prepare and output the PDF.
		$this->pdf->addPage();
		$this->pdf->writeHTML($html, true, false, true, false, '');
		$this->pdf->Output(date('Y-m-d').' '.$export->description . '.pdf', 'D');
	}

	private function _get_html()
	{
		$this->load->library('table');

		$query  = $this->_query;
		$fields = $this->_fields;
		$export = $this->_export;

		// Define table heading.
		$this->table->set_heading($fields);

		$results = $query->result();

		foreach ($results as $data) {
			$row = array();

			foreach ($fields as $field) {
				$row[] = $data->{$field};
			}

			$this->table->add_row($row);
		}
		
		$tmpl = array ( 
			'table_open'  => '<table cellpadding="5" border="0" width="100%" class="simple-table">',
			'heading_cell_start' => '<th bgcolor="#CCCCCC" scope="col">'
			);

		$this->table->set_template($tmpl);		

		return $this->table->generate();
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

			$activeSheet->setCellValue($xcoor . '1', $field);
			
			$alpha_ctr++;
		}
		
		// contents.
		$line = 2;
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
					
				$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, $row->{$field});
				
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
		header('Content-Disposition: attachment;filename=' . date('Y-m-d') . ' ' .url_title($export->description) . '.xls');
		header('Content-Transfer-Encoding: binary');
		
		$objWriter->save('php://output');		
	}

	function export2() {	
		$query_id = $this->input->post($this->key_field);

		if (!$query_id || $query_id < 0) {
			show_error('No ID specified');
		}

		$dbprefix = $this->db->dbprefix;

		$this->db->where($this->key_field, $query_id);

		$result = $this->db->get($this->module_table);
		$export = $result->row();
		$query = $export->query_string;
		$query = str_replace("{dbprefix}", $dbprefix, $query);

		switch ($this->input->post('criteria')) {
			case 'Active':
					$query .= " WHERE b.inactive = 0 AND a.resigned = 0";
				break;			
			case 'Inactive':
					$query .= " WHERE b.inactive = 1 AND a.resigned = 0";
				break;			
			case 'Resigned':
					$query .= " WHERE a.resigned = 1";
				break;											
		}
		$query  = $this->db->query($query);

		if (count($this->input->post('fields')) == 0) {		
			$fields = $query->list_fields();
		} else {
			$fields = $this->input->post('fields');
		}

		$this->_fields = $fields;
		$this->_export = $export;
		$this->_query  = $query;

		switch ($this->input->post('export_type')) {
			case 'excel': $this->_excel_export(); break;
			case 'html' : $this->_html_export(); break;
			case 'pdf' : $this->_pdf_export(); break;
		}
	}

	function get_query_fields2($record_id = 0) {
		if ($record_id == 0) {
			$record_id = $this->input->post($this->key_field);
		}

		$this->db->where('parent_module_id', $record_id);
		$this->db->where('deleted', 0);
		$result = $this->db->get('export_query');		

		if ($result->num_rows() > 0) {
			$export_query_id = $result->row()->export_query_id;
		}

		$this->db->where($this->key_field, $export_query_id);
		$this->db->limit(1);
		$export_query = $this->db->get($this->module_table)->row_array();		

		$this->db->where($this->key_field, $export_query_id);
		$result = $this->db->get('export_query_fields')->result();

		$fields = array();
		foreach ($result as $field) {
			$fields[$field->field] = $field->field;
		}

		if (!IS_AJAX) {
			return 	array(					
					'fields' 		 => $fields,
					'description'    => $export_query['description'],
					$this->key_field => $export_query[$this->key_field]
				);
		} else {
			$data['html'] = $this->load->view(
								'export_fields', 
								array(
									'fields' 		 => $fields, 
									'description'    => ucfirst($this->input->post('criteria')),
									$this->key_field => $export_query[$this->key_field]
									), 
								TRUE
							);

			$this->load->view('template/ajax', array('json' => $data));
		}
	}	
	// END custom module funtions

}

/* End of file */
/* Location: system/application */