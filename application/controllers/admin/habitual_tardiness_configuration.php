<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Habitual_tardiness_configuration extends MY_Controller
{
	function __construct()
    {
        parent::__construct();

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format

		$this->listview_title = '';
		$this->listview_description = '';
		$this->jqgrid_title = "List";
		$this->detailview_title = ' Info';
		$this->detailview_description = '';
		$this->editview_title = '';
		$this->editview_description = '';

    }

	// START - default module functions
	// default jqgrid controller method
	function index()
    {
		$data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/js/detailview.js"></script>';
		$data['content'] = $this->module_link.'/detailview';
		
		//other views to load
		$data['views'] = array();
		
		$data['data'] = $this->hdicore->_get_config('habitual_tardiness_configuration');

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
			$this->load->helper('form');
				
			//additional module edit routine here
			$data['show_wizard_control'] = false;
			$data['scripts'][] = '<script type="text/javascript" src="'.base_url().'lib/js/editview.js"></script>';
			if( !empty($this->module_wizard_form) && $this->input->post('record_id') == '-1' ){
				$data['show_wizard_control'] = true;
				$data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/js/editview-wizard-form.js"></script>';
			}
			$data['scripts'][] = chosen_script();
			$data['content'] = $this->module_link.'/editview';	
			
			
			//other views to load
			$data['views'] = array();
			$data['views_outside_record_form'] = array();
			
			$data['data'] = $this->hdicore->_get_config('habitual_tardiness_configuration');
			
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
	}

	function ajax_save()
	{
		if(IS_AJAX){
			if($this->user_access[$this->module_id]['edit'] == 1){
				$response->msg = 'Data has been successfully saved.';
				$response->msg_type = 'success';	
				$response->record_id = '';
				
				$htc = array(
					'instances' => $this->input->post('instances'),
					'minutes_tardy' => $this->input->post('minutes_tardy'),
					'months_within' => $this->input->post('months_within'),
					'consec_days_awol' => $this->input->post('consec_days_awol')
				);

				$htc = base64_encode( serialize($htc) );

				$this->hdicore->_update_config('habitual_tardiness_configuration', $htc);

				if( $this->db->_error_message() != "" ){
					$response->msg = $this->db->_error_message();
					$response->msg_type = 'error';
				} 
			}
			else{
				$response->msg = "You dont have sufficient privilege to execute this action! Please contact the System Administrator.";
				$response->msg_type = 'attention';
			}		
			
			$data['json'] = $response;
			$this->load->view($this->userinfo['rtheme'].'/template/ajax', $data);
		}
		else{
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url().$this->module_link);
		}
	}
	// END - default module functions

	// START custom module funtions

	// END custom module funtions

}

/* End of file */
/* Location: system/application */