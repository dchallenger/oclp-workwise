<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Shift extends MY_Controller
{
	function __construct()
    {
        parent::__construct();

		//set  variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format	
		
		$this->listview_title = 'Shift';
		$this->listview_description = 'This lists all defined Shift(s).';
		$this->jqgrid_title = "Shift(s) List";
		$this->detailview_title = 'Shift Info';
		$this->detailview_description = 'This page shows detailed information about a particular shift';
		$this->editview_title = ' Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about shift(s)';
    }

	// START - default  functions
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
		
		//additional  detail routine here
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
		if($this->user_access[$this->module_id]['edit'] == 1)
		{
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
	
	function check(){
		$fromtime = "09:00 pm";
		$totime = "01:00 pm";
		$halfday = "02:00 am";
		$min = 120;
		dbug(date('G:i',strtotime($fromtime)));
		dbug(date('G:i',strtotime($halfday)));
		dbug(date('G:i',strtotime($halfday)));

		if ((date('G:i',strtotime($fromtime)) > "18:00" && 
				date('G:i',strtotime($fromtime)) < "22:00") &&
				(strtotime($halfday) >= strtotime("00:00") && 
				strtotime($halfday) <= strtotime("06:00"))){

			$fromtime = date('Y-m-d') .' '. $fromtime;
			$halfday = date('Y-m-d',strtotime('+1 day',strtotime(date('Y-m-d')))) .' '. $halfday;

			$minimum_halfday = date('Y-m-d G:i a', strtotime('+'.$min.' minutes', strtotime($fromtime)));

			dbug($minimum_halfday);
			dbug($halfday);
		}
	}

	function ajax_save()
	{

		$shift_start = date('H:i',strtotime($this->input->post('shifttime_start')));
		$shift_end = date('H:i',strtotime($this->input->post('shifttime_end')));
		$halfday = date('H:i',strtotime($this->input->post('halfday')));
		$minimum_halfday = date('H:i', strtotime('+'.$this->input->post('minimum_halfday_minutes').' minutes', strtotime($shift_start)));

		if ((date('G:i',strtotime($shift_start)) > "18:00" && 
				date('G:i',strtotime($shift_start)) < "22:00") &&
				(strtotime($halfday) >= strtotime("00:00") && 
				strtotime($halfday) <= strtotime("06:00"))){
			$shift_start = date('Y-m-d') .' '. $shift_start;
			$halfday = date('Y-m-d',strtotime('+1 day',strtotime(date('Y-m-d')))) .' '. $halfday;
			$minimum_halfday = date('Y-m-d G:i a', strtotime('+'.$min.' minutes', strtotime($shift_start)));
		}

		$validate_shift = array();

		if($minimum_halfday > $halfday){
			$validate_shift[] = 'Minimum minutes to consider halfday must not be greater than the given halfday';
		}

		if(count($validate_shift) == 0){

			 parent::ajax_save();

		} else {
			$response['msg'] = implode('<br />', $validate_shift);
			$response['msg_type'] = 'attention';		
			$data['json'] = $response;
			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
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
	
	// END custom module funtions
	
}

/* End of file */
/* Location: system/application */
?>