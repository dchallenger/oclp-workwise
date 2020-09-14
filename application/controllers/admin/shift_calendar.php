<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Shift_Calendar extends MY_Controller
{
	function __construct()
    {
        parent::__construct();

		//set  variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format	
		
		$this->listview_title = 'Shift Calendar';
		$this->listview_description = 'This lists all defined Shift Calendar(s).';
		$this->jqgrid_title = "Shift Calendar(s) List";
		$this->detailview_title = 'Shift Info';
		$this->detailview_description = 'This page shows detailed information about a particular shift calendar';
		$this->editview_title = ' Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about shift calendar(s)';
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
	
	function ajax_save()
	{	
		parent::ajax_save();
		//additional module save routine here

		if (CLIENT_DIR == 'firstbalfour'){
			$this->db->where('shift_calendar_id',$this->key_field_val);
			$this->db->delete('timekeeping_shift_calendar_override');

			$array_info = array(
					'shift_calendar_id' => $this->key_field_val,
					'sunday_shift_id' => $this->input->post('sunday_shift_id_ch'),
					'monday_shift_id' => $this->input->post('monday_shift_id_ch'),
					'tuesday_shift_id' => $this->input->post('tuesday_shift_id_ch'),
					'wednesday_shift_id' => $this->input->post('wednesday_shift_id_ch'),
					'thursday_shift_id' => $this->input->post('thursday_shift_id_ch'),
					'friday_shift_id' => $this->input->post('friday_shift_id_ch'),
					'saturday_shift_id' => $this->input->post('saturday_shift_id_ch')
				);

			$this->db->insert('timekeeping_shift_calendar_override',$array_info);	
		}			
	}	

	function get_shift_schedule_override(){
		$this->db->where('shift_calendar_id',$this->input->post('record_id'));
		$result = $this->db->get('timekeeping_shift_calendar_override');	

		$data['json'] = $result->row();
		$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);		
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