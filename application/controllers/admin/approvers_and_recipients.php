<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Approvers_and_recipients extends MY_Controller
{
	function __construct()
    {
        parent::__construct();

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format	
		
		$this->listview_title = '';
		$this->listview_description = 'This module lists all defined (s).';
		$this->jqgrid_title = "";
		$this->detailview_title = '';
		$this->detailview_description = '<span class="red">Warning! Editing ANY of these Configuration may cause the whole system to malfunction.</span>';
		$this->editview_title = 'Edit System Configuration';
		$this->editview_description = '<span class="red">Warning! Editing ANY of these Configuration may cause the whole system to malfunction.</span>';
    }

	// START - default module functions
	// default jqgrid controller method
	function index()
    {
		//additional module detail routine here
		$data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/js/detailview.js"></script>';
		$data['content'] = $this->module_link.'/detailview';
		
		//other views to load
		$data['views'] = array();
		
		$data['mrf_settings'] = $this->hdicore->_get_config('mrf_settings');
		$data['ir_settings'] = $this->hdicore->_get_config('ir_settings');
		
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
			$data['scripts'][] = '<script type="text/javascript" src="'.base_url().'lib/js/editview.js"></script>'.uploadify_script();
			if( !empty($this->module_wizard_form) && $this->input->post('record_id') == '-1' ){
				$data['show_wizard_control'] = true;
				$data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/js/editview-wizard-form.js"></script>';
			}
			$data['content'] = $this->module_link.'/editview';	
			
			
			//other views to load
			$data['views'] = array();
			$data['views_outside_record_form'] = array();
			
			$data['mrf_settings'] = $this->hdicore->_get_config('mrf_settings');
			$data['ir_settings'] = $this->hdicore->_get_config('ir_settings');
			
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
		if(IS_AJAX){
			if($this->user_access[$this->module_id]['edit'] == 1){
				$response->msg = 'Data has been successfully saved.';
				$response->msg_type = 'success';	
				$response->record_id = '';	
				
				
				$config = $this->input->post('config');
				foreach( $config as $config_key => $items ){
					foreach( $items as $index => $value ) $items[$index] = trim( $value );
					$config_value = base64_encode( serialize($items) );
					$this->hdicore->_update_config($config_key, $config_value);
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
?>