<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Alert_frequency extends MY_Controller
{
	function __construct()
    {
        parent::__construct();

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format	
		
		$this->listview_title = 'Alert Frequency';
		$this->listview_description = 'This module lists all defined alert frequency(s).';
		$this->jqgrid_title = "Alert Frequency List";
		$this->detailview_title = 'Alert Frequency Info';
		$this->detailview_description = 'This page shows detailed information about a particular alert frequency.';
		$this->editview_title = 'Alert Frequency Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about alert frequency(s).';
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

		$this->db->delete('alert_frequency_variable',array($this->key_field => $this->key_field_val));

		foreach( $this->input->post('variable') as $key => $val ){

			$data = array(
				'alert_frequency_id' => $this->key_field_val,
				'crontask_variable_id' => $key,
				'value' => $val
			);

			$this->db->insert('alert_frequency_variable',$data);

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

	function get_record_info(){

		$alert_frequency_info = $this->db->get_where('alert_frequency',array( $this->key_field => $this->input->post('record_id') ))->row();

		$response->implement_type = $alert_frequency_info->hour_implement_type_id;

		$data['json'] = $response;
	    $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);

	}

	function get_function_variables(){

		$html = '';

		if( $this->input->post('view') == 'edit' ){

			$function = $this->input->post('function');

		}
		elseif( $this->input->post('view') == 'detail' ){

			$alert_frequency_info = $this->db->get_where('alert_frequency',array( $this->key_field => $this->input->post('record_id') ))->row();

			$function = $alert_frequency_info->crontask_function_id;

		}

		$this->db->where('crontask_function.crontask_function_id',$function);
		$this->db->join('crontask_function','crontask_function.crontask_function_id = crontask_variable.crontask_function_id','left');
		$result = $this->db->get('crontask_variable');

		if( $result->num_rows() > 0 ){

			foreach( $result->result() as $variable_info ){

				$variable_value = $variable_info->default_value;

				$this->db->where('alert_frequency_id',$this->input->post('record_id'));
				$this->db->where('crontask_variable_id',$variable_info->crontask_variable_id);
				$alert_frequency_variable_info = $this->db->get('alert_frequency_variable');

				if( $alert_frequency_variable_info->num_rows() > 0 ){
					$variable_value = $alert_frequency_variable_info->row()->value;
				}

				if( $this->input->post('view') == 'edit' ){

					$html.='
					<div class="form-item odd">
	                    <label class="label-desc gray" for="variable['.$variable_info->crontask_variable_id.']">
	                        '.$variable_info->crontask_variable.'
	                    </label>
	                    <div class="text-input-wrap">
	                        <input type="text" class="input-text" value="'.$variable_value.'" name="variable['.$variable_info->crontask_variable_id.']">
	                    </div>
	                </div>';

            	}
            	else{

            		$html .= '
            		<div class="form-item view odd ">
                    	<label class="label-desc view gray" for="template_id">'.$variable_info->crontask_variable.':</label>
                    	<div class="text-input-wrap">'.$variable_value.'</div>		
                	</div>';

            	}


			}


		}
		else{

			$html = "<div style='text-align:center;'>No variables available</div>";

		}

		$data['html'] = $html;
        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);

	}

	// END custom module funtions
	
}

/* End of file */
/* Location: system/application */
?>