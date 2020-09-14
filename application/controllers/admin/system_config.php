<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class System_config extends MY_Controller
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
		$this->detailview_title = 'System Configuration';
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
		$data['content'] = 'admin/system_config/detailview';
		
		//other views to load
		$data['views'] = array();
		
		$data['meta_raw'] = $this->hdicore->_get_config('meta');
		$data['app_directories'] = $this->hdicore->_get_config('app_directories');
		$data['smtp'] = $this->hdicore->_get_config('outgoing_mailserver');
		
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
			$data['content'] = 'admin/system_config/editview';	
			
			
			//other views to load
			$data['views'] = array();
			$data['views_outside_record_form'] = array();
			
			$data['meta_raw'] = $this->hdicore->_get_config('meta');
			$data['app_directories'] = $this->hdicore->_get_config('app_directories');
			$data['smtp'] = $this->hdicore->_get_config('outgoing_mailserver');
			
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
				
				$meta = array(
					'company_code' => $this->input->post('company_code'),
					'logo' => $this->input->post('logo'),
					'title' => $this->input->post('title'),
					'author' => $this->input->post('author'),
					'keywords' => $this->input->post('keywords'),
					'description' => $this->input->post('description'),
					'copyright' => $this->input->post('copyright'), 
					'footer' => $this->input->post('footer'),
					'use_logo' => $this->input->post('use_logo')
				);
				
				$meta = base64_encode( serialize($meta) );
				$this->hdicore->_update_config('meta', $meta);
				if( $this->db->_error_message() != "" ){
					$response->msg = $this->db->_error_message();
					$response->msg_type = 'error';
				}
				
				$app_directories = array(
					'system_settings_dir' => $this->input->post('system_settings_dir'),
					'user_settings_dir' => $this->input->post('user_settings_dir'),
					'role_settings_dir' => $this->input->post('role_settings_dir'),
				);
				
				if(file_exists($app_directories['system_settings_dir'].'meta.php')) unlink( $app_directories['system_settings_dir'].'meta.php' );
				
				$app_directories = base64_encode( serialize($app_directories) );
				$this->hdicore->_update_config('app_directories', $app_directories);
				if( $this->db->_error_message() != "" ){
					$response->msg = $this->db->_error_message();
					$response->msg_type = 'error';
				}
				
				$smtp = array(
					'protocol' => $this->input->post('protocol'),
					'smtp_host' => $this->input->post('smtp_host'),
					'smtp_port' => $this->input->post('smtp_port'),
					'smtp_user' => $this->input->post('smtp_user'),
					'smtp_pass' => $this->input->post('smtp_pass'), 
					'mailtype' => $this->input->post('mailtype')
				);
				$smtp = base64_encode( serialize($smtp) );
				$this->hdicore->_update_config('outgoing_mailserver', $smtp);
				
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
?>