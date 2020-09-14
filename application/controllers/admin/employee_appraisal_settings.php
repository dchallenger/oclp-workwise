<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Employee_Appraisal_Settings extends MY_Controller
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
		
		$data['employee_appraisal_settings'] = $this->hdicore->_get_config('employee_appraisal_settings');

		$data['appraisal_status'] = $this->db->get_where('employee_appraisal_status',array('deleted' => 0 ))->result();
		$data['appraisal_scale'] = $this->db->get_where('employee_appraisal_scale',array('deleted' => 0 ))->result();
		$data['appraisal_group'] = $this->db->get_where('employee_appraisal_group',array('deleted' => 0 ))->result();

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
			
			$data['employee_appraisal_settings'] = $this->hdicore->_get_config('employee_appraisal_settings');

			$data['appraisal_status'] = $this->db->get_where('employee_appraisal_status',array('deleted' => 0 ))->result();
			$data['appraisal_scale'] = $this->db->get_where('employee_appraisal_scale',array('deleted' => 0 ))->result();
			$data['appraisal_group'] = $this->db->get_where('employee_appraisal_group',array('deleted' => 0 ))->result();

		
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

				//status
				$status_info = $this->input->post('status');
				$status = array();

				foreach( $status_info['status_id'] as $key => $value ){

					$status[$key]['status_id'] = $status_info['status_id'][$key];
					$status[$key]['status'] = $status_info['status'][$key];

				}

				//scale
				$scale_info = $this->input->post('scale');
				$scale = array();

				foreach( $scale_info['scale_id'] as $key => $value ){

					$scale[$key]['scale_id'] = $scale_info['scale_id'][$key];
					$scale[$key]['scale'] = $scale_info['scale'][$key];
					$scale[$key]['scale_times'] = $scale_info['scale_times'][$key];

				}

				//group
				$group_info = $this->input->post('group');
				$group = array();

				foreach( $group_info['group_id'] as $key => $value ){

					$group[$key]['group_id'] = $group_info['group_id'][$key];
					$group[$key]['group'] = $group_info['group'][$key];

				}


				$current_status_list = $this->db->get('employee_appraisal_status')->result_array();

				//Delete status
				foreach( $current_status_list as $current_status ){

					$cnt = 0;

					foreach( $status as $status_list ){

						if( ( $status_list['status_id'] == $current_status['appraisal_status_id'] ) ){
							$cnt++;
						}
					}

					if( $cnt == 0 ){

						$this->db->update('employee_appraisal_status',array( 'deleted' => 1 ),array('appraisal_status_id' => $current_status['appraisal_status_id'] ));
					
					}

				}


				foreach( $status as $status_list ){

					if( $status_list['status_id'] == -1 ){

						$data = array(
							'appraisal_status' => $status_list['status']
						);

						$this->db->insert('employee_appraisal_status',$data);

					}
					else{

						$data = array(
							'appraisal_status' => $status_list['status']
						);

						$this->db->update('employee_appraisal_status', $data, array( 'appraisal_status_id' => $status_list['status_id'] ));
					}
				}


				$current_group_list = $this->db->get('employee_appraisal_group')->result_array();

				//Delete status
				foreach( $current_group_list as $current_group ){

					$cnt = 0;

					foreach( $group as $group_list ){

						if( ( $group_list['group_id'] == $current_group['appraisal_group_id'] ) ){
							$cnt++;
						}
					}

					if( $cnt == 0 ){

						$this->db->update('employee_appraisal_group',array( 'deleted' => 1 ),array('appraisal_group_id' => $current_group['appraisal_group_id'] ));
					
					}

				}


				foreach( $group as $group_list ){

					if( $group_list['group_id'] == -1 ){

						$data = array(
							'appraisal_group' => $group_list['group']
						);

						$this->db->insert('employee_appraisal_group',$data);

					}
					else{

						$data = array(
							'appraisal_group' => $group_list['group']
						);

						$this->db->update('employee_appraisal_group', $data, array( 'appraisal_group_id' => $group_list['group_id'] ));
					}
				}

				$current_scale_list = $this->db->get('employee_appraisal_scale')->result_array();

				//Delete status
				foreach( $current_scale_list as $current_scale ){

					$cnt = 0;

					foreach( $scale as $scale_list ){

						if( ( $scale_list['scale_id'] == $current_scale['appraisal_scale_id'] ) ){
							$cnt++;
						}
					}

					if( $cnt == 0 ){

						$this->db->update('employee_appraisal_scale',array( 'deleted' => 1 ),array( 'appraisal_scale_id'  => $current_scale['appraisal_scale_id'] ));

					}

				}


				foreach( $scale as $scale_list ){

					if( $scale_list['scale_id'] == -1 ){

						$data = array(
							'appraisal_scale' => $scale_list['scale'],
							'appraisal_scale_times' => $scale_list['scale_times']
						);

						$this->db->insert('employee_appraisal_scale',$data);

					}
					else{

						$data = array(
							'appraisal_scale' => $scale_list['scale'],
							'appraisal_scale_times' => $scale_list['scale_times']
						);

						$this->db->update('employee_appraisal_scale', $data, array( 'appraisal_scale_id' => $scale_list['scale_id'] ));
					}
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
	

	function add_status_form(){

		$data = array(
			'status_name' => $this->input->post('status_name'),
			'status_id' => -1
		);

		$response->status_form = $this->load->view( $this->userinfo['rtheme'].'/admin/employee_appraisal_settings/status_form',$data, true );
		$this->load->view('template/ajax', array('json' => $response));

	}

	function add_scale_form(){

		$data = array(
			'scale_name' => $this->input->post('scale_name'),
			'scale_times' => number_format($this->input->post('scale_times')),
			'scale_id' => -1
		);

		$response->scale_form = $this->load->view( $this->userinfo['rtheme'].'/admin/employee_appraisal_settings/scale_form',$data, true );
		$this->load->view('template/ajax', array('json' => $response));

	}

	function add_group_form(){

		$data = array(
			'group_name' => $this->input->post('group_name'),
			'group_id' => -1
		);

		$response->group_form = $this->load->view( $this->userinfo['rtheme'].'/admin/employee_appraisal_settings/group_form',$data, true );
		$this->load->view('template/ajax', array('json' => $response));

	}

	// END custom module funtions
	
}

/* End of file */
/* Location: system/application */
?>