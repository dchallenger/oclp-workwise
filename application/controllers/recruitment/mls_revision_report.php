<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Mls_revision_report extends MY_Controller
{
	function __construct()
    {
        parent::__construct();

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format	
		
		$this->listview_title = '';
		$this->listview_description = '';
		$this->jqgrid_title = "";
		$this->detailview_title = '';
		$this->detailview_description = '';
		$this->editview_title = '';
		$this->editview_description = '';
		$this->module_table = 'manpower_loading_schedule';
    }

	// START - default module functions
	// default jqgrid controller method
	function index(){
		if($this->user_access[$this->module_id]['list'] != 1){
			$this->session->set_flashdata('flashdata', 'You dont have sufficient privilege to execute the list action of '.$this->module_name.'! Please contact the System Administrator.');
			redirect( base_url() );
		}
		$data['scripts'][] = jqgrid_listview();	// load jqgrid js and default grid js
		$data['content'] = 'recruitment/mls_revision_report_listview';
		
		if($this->session->flashdata('flashdata')){
			$info['flashdata'] = $this->session->flashdata('flashdata');
			$data['flashdata'] = $this->load->view($this->userinfo['rtheme'].'/template/flashdata', $info, true);
		}

		$this->load->model('uitype_edit');
		
		$this->db->select('DISTINCT(control_code)');
		$data['msl'] = $this->db->get_where($this->module_table, array('deleted' => 0 ));

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
      
        $total_pages = 12 > 0 ? ceil(12/$limit) : 0;
        $response->page = $page > $total_pages ? $total_pages : $page;
        $response->total = $total_pages;
        $response->records = 0;                      

        $response->msg = "";

        $start = $limit * $page - $limit;
        $this->db->limit($limit, $start); 

        $control_code = $this->input->post('control_code');

        $this->db->select('project_name.project_name, manpower_loading_schedule.date_created, CONCAT(firstname, " " ,lastname) as division_head, cost_code.cost_code, date_changes, version', false);
        $this->db->join('project_name', 'project_name.project_name_id = manpower_loading_schedule.project_name_id');
        $this->db->join('user', 'user.user_id = manpower_loading_schedule.division_head_id');
        $this->db->join('cost_code', 'cost_code.cost_code_id = manpower_loading_schedule.cost_code_id');
        $this->db->where( $this->module_table.'.deleted' , 0);
        $this->db->where( 'control_code' , $control_code);
        $mls = $this->db->get('manpower_loading_schedule');

        foreach ($mls->result() as $key => $value) {
        	$response->rows[$key]['cell'][0] = $value->project_name;
        	$response->rows[$key]['cell'][1] = date('d M Y' , strtotime($value->date_created));
        	$response->rows[$key]['cell'][2] = $value->division_head;
        	$response->rows[$key]['cell'][3] = $value->cost_code;
        	$response->rows[$key]['cell'][4] = date('d M Y' , strtotime($value->date_changes));
        	$response->rows[$key]['cell'][5] = $value->version;
        }

        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', array('json' => $response));
	}

	function export() {	
		$this->_excel_export();
	}

	private function _excel_export($record_id = 0)
	{
		$record_id = $this->input->post('control_code');

		$fields = array('Project','Date Created','Division Head','Cost Code','Date Changes','Version');

        $this->db->select('project_name.project_name, manpower_loading_schedule.date_created, CONCAT(firstname, " " ,lastname) as division_head, cost_code.cost_code, date_changes, version', false);
        $this->db->join('project_name', 'project_name.project_name_id = manpower_loading_schedule.project_name_id');
        $this->db->join('user', 'user.user_id = manpower_loading_schedule.division_head_id');
        $this->db->join('cost_code', 'cost_code.cost_code_id = manpower_loading_schedule.cost_code_id');
        $this->db->where( $this->module_table.'.deleted' , 0);
        $this->db->where( 'control_code' , $record_id);
        $mls = $this->db->get('manpower_loading_schedule');

		$this->load->library('PHPExcel');		
		$this->load->library('PHPExcel/IOFactory');

		$objPHPExcel = new PHPExcel();

		$objPHPExcel->getProperties()->setTitle("Manpower Loading Schedule Revision Report")
		            ->setDescription("Manpower Loading Schedule Revision Report");
		               
		// Assign cell values
		$objPHPExcel->setActiveSheetIndex(0);
		$activeSheet = $objPHPExcel->getActiveSheet();

		//header
		$alphabet  = range('A','Z');
		$alpha_ctr = 0;
		$sub_ctr   = 0;

		//Default column width
		$objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(15);
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);	

		//Initialize style
		$styleArray = array(
			'font' => array(
				'bold' => true,
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			)
		);

		$styleHeader = array(
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
			),
			'font' => array(
				'bold' => true,
			),
		);

		$fontBold = array(
			'font' => array(
				'bold' => true,
			)
		);

		$HorizontalLeft = array(
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
			)
		);				

		$HorizontalRight = array(
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
			)
		);	

		$HorizontalCenter = array(
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			)
		);				

		$styleArrayBorder = array(
		  	'borders' => array(
		    	'allborders' => array(
		      		'style' => PHPExcel_Style_Border::BORDER_THIN
		    	)
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

			$activeSheet->setCellValue($xcoor . '4', $field);

			$objPHPExcel->getActiveSheet()->getStyle($xcoor . '4')->applyFromArray($styleArray);
			// $objPHPExcel->getActiveSheet()->getStyle($xcoor . '6')->applyFromArray($styleArray);
			
			$alpha_ctr++;
		}


		$activeSheet->setCellValue('A1', 'Manpower Loading Revision Report');
		$activeSheet->setCellValue('A2', date('F d,Y'));

		$objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArray);
		$objPHPExcel->getActiveSheet()->getStyle('A2')->applyFromArray($styleArray);


		$line = 5;

		foreach ($mls->result() as $row) {
			$objPHPExcel->getActiveSheet()->setCellValue('A' . $line, $row->project_name);
			$objPHPExcel->getActiveSheet()->setCellValue('B' . $line, date('d M Y' , strtotime($row->date_created)));
			$objPHPExcel->getActiveSheet()->setCellValue('C' . $line, $row->division_head);
			$objPHPExcel->getActiveSheet()->setCellValue('D' . $line, $row->cost_code);
			$objPHPExcel->getActiveSheet()->setCellValue('E' . $line, date('d M Y' , strtotime($row->date_changes)));
			$objPHPExcel->getActiveSheet()->setCellValue('F' . $line, $row->version);
		$line++;	
		}

		$objWriter = IOFactory::createWriter($objPHPExcel, 'Excel5');

		header('Pragma: public');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Content-Type: application/force-download');
		header('Content-Type: application/octet-stream');
		header('Content-Type: application/download');
		header('Content-Disposition: attachment;filename=' . date('Y-m-d') . ' ' .url_title("MLS Revision Report") . '.xls');
		header('Content-Transfer-Encoding: binary');
		
		$objWriter->save('php://output');	

	}
// End

	/*SELECT user_id, as user FROM hr_user ORDER BY user*/
}