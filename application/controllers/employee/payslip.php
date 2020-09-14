<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Payslip extends MY_Controller
{
	function __construct(){
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
	function index(){
		if($this->user_access[$this->module_id]['list'] != 1){
			$this->session->set_flashdata('flashdata', 'You dont have sufficient privilege to execute the list action of '.$this->module_name.'! Please contact the System Administrator.');
			redirect( base_url() );
		}
		$data['scripts'][] = jqgrid_listview();	// load jqgrid js and default grid js
		$data['scripts'][] = multiselect_script();
		$data['content'] = $this->module_link. '/listview';
		$this->load->helper('form');
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

	// END - default module functions

	// START custom module funtions
	function listview()
	{
		$response->msg = "";

		$page = $this->input->post('page');
		$limit = $this->input->post('rows'); // get how many rows we want to have into the grid
		$sidx = $this->input->post('sidx'); // get index row - i.e. user click to sort
		$sord = $this->input->post('sord'); // get the direction

		$load_folder = array( $this->user->user_id );
		if( isset($this->user_access[$this->module_id]['post']) && isset($this->user_access[$this->module_id]['post']) == 1 ){
			if( $this->input->post('employee_id') ){
				$load_folder = $this->input->post('employee_id');
			}
		}
		
		$this->load->helper('directory');
		$this->load->helper('file');
		$total_payslip = 0;
		$payslips = array();
		foreach( $load_folder as $employee_id ){
			$employee = $this->db->get_where('user', array('user_id' => $employee_id))->row();
			$directory = 'uploads/payslip/'.$employee->login;
			$employee_payslips = directory_map($directory);
			foreach( $employee_payslips as $payslip ){
				$pslip = explode('.', $payslip);
				$payroll_date = $pslip[0];
				$file = get_file_info($directory.'/'.$payslip);
				$payslips[$employee_id][$payroll_date] = array(
					'0' => $employee_id.'~'.$payroll_date,
					'1' => $employee->lastname.', '. $employee->firstname,
					'2' => $employee->login,
					'3' => date($this->config->item('display_date_format'), strtotime($payroll_date)),
					'4' => date($this->config->item('display_date_format'), $file['date']),
					'5' => $this->_default_grid_actions( $directory.'/'.$payslip )
				);
				$total_payslip++;
			}
		}

		$total_pages = $total_payslip > 0 ? ceil($total_payslip/$limit) : 0;
		$response->page = $page > $total_pages ? $total_pages : $page;
		$response->total = $total_pages;
		$response->records = $total_payslip;

		$start = $limit * $page - $limit;
		$pslip_ctr = 0;
		$ctr = 0;
		foreach($payslips as $employee_id => $employee_payslip){
			krsort($employee_payslip);
			foreach($employee_payslip as $payroll_date => $payslip){
				if($ctr > $limit) break;
				if($pslip_ctr >= $start){
					$response->rows[$ctr]['id'] = $employee_id.'~'.$payroll_date;
					$response->rows[$ctr]['cell'] = $payslip;
					$ctr++;
				}
				$pslip_ctr++;
			}
		}

		$data['json'] = $response;                		
		$this->load->view($this->userinfo['rtheme'].'/template/ajax', $data);

	}

	function _default_grid_actions( $filepath )
	{
		$actions = '<span class="icon-group">';
                
        if ($this->user_access[$this->module_id]['view']) {
            $actions .= '<a class="icon-button icon-16-document-view" tooltip="View/Download" href="'.base_url($filepath).'"></a>';
        }
        
        $actions .= '</span>';

		return $actions;
	}
	// END custom module funtions

}

/* End of file */
/* Location: system/application */