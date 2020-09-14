<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Habitual_tardiness_configuration extends MY_Controller
{
	function __construct()
    {
        parent::__construct();

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format

		$this->listview_title = 'Habitual Tardiness Configuration';
		$this->listview_description = 'This module lists no call no show';
		$this->jqgrid_title = "List";
		$this->detailview_title = ' Info';
		$this->detailview_description = '';
		$this->editview_title = ' Add/Edit';
		$this->editview_description = '';

    }

	// START - default module functions
	// default jqgrid controller method
	function index()
    {
		$data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/js/detailview.js"></script>';
		$data['content'] = $this->module_link.'/editview';
		
		//other views to load
		$data['views'] = array();
		
		$data['habitual_tardiness_configuration'] = $this->hdicore->_get_config('habitual_tardiness_configuration');

		// $emailto_list = array();
		// $emailcc_list = array();

		// foreach( $data['habitual_tardiness_configuration']['email_to'] as $email_to_settings ){

		// 	$result = $this->db->get_where('user', array('user_id'=>$email_to_settings['user_id']) )->row();
		// 	$emailto_list[] = $result->firstname." ".$result->lastname;

		// }

		// foreach( $data['habitual_tardiness_configuration']['email_cc'] as $email_cc_settings ){

		// 	$result = $this->db->get_where('user', array('user_id'=>$email_cc_settings['user_id']) )->row();
		// 	$emailcc_list[] = $result->firstname." ".$result->lastname;
		// }

		// $data['email_to_list'] = implode(', ',$emailto_list);
		// $data['email_cc_list'] = implode(', ',$emailcc_list);

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
			$data['scripts'][] = chosen_script();
			$data['content'] = $this->module_link.'/editview';	
			
			
			//other views to load
			$data['views'] = array();
			$data['views_outside_record_form'] = array();
			
			$data['habitual_tardiness_configuration'] = $this->hdicore->_get_config('habitual_tardiness_configuration');
			
			// $employee_list = $this->db->get('user')->result_array();

			// $emailto_user_id_list = array();
			// $emailcc_user_id_list = array();

			// foreach( $data['habitual_tardiness_configuration']['email_to'] as $email_to_settings ){

			// 	$emailto_user_id_list[] = $email_to_settings['user_id'];
					
			// }

			// foreach( $data['habitual_tardiness_configuration']['email_cc'] as $email_to_settings ){

			// 	$emailcc_user_id_list[] = $email_to_settings['user_id'];
					
			// }

			// foreach($employee_list as $key => $val){

			// 	if( in_array($val['user_id'],$emailto_user_id_list) ){
			// 		$employee_list[$key]['email_to_selected'] = "selected";
			// 	}
			// 	else{
			// 		$employee_list[$key]['email_to_selected'] = "";
			// 	}

			// 	if( in_array($val['user_id'],$emailcc_user_id_list) ){
			// 		$employee_list[$key]['email_cc_selected'] = "selected";
			// 	}
			// 	else{
			// 		$employee_list[$key]['email_cc_selected'] = "";
			// 	}

			// }

			// $data['employee_list'] = $employee_list;


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

				$instances = $this->input->post('instances');
				// $email_cc = $this->input->post('email_cc');

				// $email_to_users = array();
				// $email_cc_users = array();

				foreach( $email_to as $user_id ){

					$result = $this->db->get_where('user',array('user_id'=>$user_id))->row();

					$email_to_users[] = array(
						'user_id' => $result->user_id,
						'email' => $result->email
					);

				}

				foreach( $email_cc as $user_id ){

					$result = $this->db->get_where('user',array('user_id'=>$user_id))->row();

					$email_cc_users[] = array(
						'user_id' => $result->user_id,
						'email' => $result->email
					);

				}
				
				$config = array(
					'habitual_tardiness_configuration' => array(
						'instances' => $instances
						// 'email_cc' => $email_cc_users
					)
				);
				
				// foreach( $config as $config_key => $items ){
					// foreach( $items as $index => $value ) $items[$index] = $value;

					$config_value = base64_encode( serialize($instances) );
					$this->hdicore->_update_config($config_key, $config_value);
				// }

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

		//additional module save routine here

	}
	// END - default module functions

	// START custom module funtions

	// END custom module funtions

}

/* End of file */
/* Location: system/application */