<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Holiday extends MY_Controller
{
	function __construct()
    {
        parent::__construct();

		//set  variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format	
		
		$this->listview_title = 'Holiday';
		$this->listview_description = 'This  lists all defined holiday(s).';
		$this->jqgrid_title = "Holiday(s) List";
		$this->detailview_title = 'Holiday Info';
		$this->detailview_description = 'This page shows detailed information about a particular holiday';
		$this->editview_title = ' Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about holiday(s)';
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
		
		//extract employees included in holiday
		$this->db->delete('holiday_employee', array($this->key_field => $this->key_field_val));
		$employees = $this->input->post('employee_id');
		if( !empty( $employees ) ){
			$employees = explode(',', $employees);
			foreach( $employees as $employee_id ){
				$data = array(
					$this->key_field => $this->key_field_val,
					'employee_id' => $employee_id
				);
				$this->db->insert('holiday_employee', $data);
			}
		}
						
	}
	
	function delete()
	{
		$holiday = $this->db->get_where('holiday', array('holiday_id' => $this->input->post('record_id')))->row();

		$holiday_name = $holiday->holiday.'_'.$holiday->holiday_id;
		$this->db->set('holiday',$holiday_name);
		$this->db->where('holiday_id',$holiday->holiday_id);
		$this->db->update('holiday');

		parent::delete();
		
		//additional module delete routine here
	}
	// END - default module functions
	
	// START custom module funtions
	function _default_grid_actions( $module_link = "",  $container = "", $record = array() )
	{
		// set default
		if($module_link == "") $module_link = $this->module_link;
		if($container == "") $container = "jqgridcontainer";

	$actions = '<span class="icon-group">';

        if ($this->user_access[$this->module_id]['view']) {
            $actions .= '<a class="icon-button icon-16-info" module_link="'.$module_link.'" tooltip="View" href="javascript:void(0)"></a>';
        }

        if ( $this->user_access[$this->module_id]['add'] ) {
            $actions .= '<a class="icon-button icon-16-document-stack" tooltip="Duplicate" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
        } 
        
				if ( $this->user_access[$this->module_id]['edit'] ) {
            $actions .= '<a class="icon-button icon-16-edit" tooltip="Edit" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
        } 
				
        if ($this->user_access[$this->module_id]['print'] && method_exists($this, 'print_record')) {
            $actions .= '<a class="icon-button icon-16-print print-record" tooltip="Print" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
        }        
        
        if ($this->user_access[$this->module_id]['delete']) {
            $actions .= '<a class="icon-button icon-16-delete delete-single" tooltip="Delete" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
        }

        $actions .= '</span>';

		return $actions;
	}


	function _default_grid_buttons( $module_link = "", $related_field = "", $related_field_value = "", $addtext = "", $deltext = "", $container = "" )
	{
		// set default
		if($module_link == "") $module_link = $this->module_link;
		if($addtext == "") $addtext = "Add";
		if($deltext == "") $deltext = "Delete";
		if($container == "") $container = "jqgridcontainer";

		$buttons = "<div class='icon-label-group'>";                    
                            
        if ($this->user_access[$this->module_id]['add']) {
            $buttons .= "<div class='icon-label'>";
            $buttons .= "<a class='icon-16-add icon-16-add-listview' related_field='".$related_field."' related_field_value='".$related_field_value."' module_link='".$module_link."' href='javascript:void(0)'>";
            $buttons .= "<span>".$addtext."</span></a></div>";
        }
         
        if ($this->user_access[$this->module_id]['delete']) {
            $buttons .= "<div class='icon-label'><a class='icon-16-delete delete-array' container='".$container."' module_link='".$module_link."' href='javascript:void(0)'><span>".$deltext."</span></a></div>";                            
        }

        if ( get_export_options( $this->module_id ) ) {
            $buttons .= "<div class='icon-label'><a class='icon-16-export module-export' container='".$container."' module_link='".$module_link."' href='javascript:void(0)'><span>Export</span></a></div>";
            $buttons .= "<div class='icon-label'><a class='icon-16-import module-import' container='".$container."' module_link='".$module_link."' href='javascript:void(0)'><span>Import</span></a></div>";
        }        

		$buttons .= "<div class='icon-label'>";
        $buttons .= "<a class='' onClick='populate_boxy()' related_field='".$related_field."' related_field_value='".$related_field_value."' module_link='".$module_link."' href='javascript:void(0)'>";
        $buttons .= "<span>Populate Holiday</span></a></div>";
        $buttons .= "</div>";
                
		return $buttons;
	}

	function populate_year()
	{
		if (!IS_AJAX) {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		} else if ($this->user_access[$this->module_id]['view'] != 1) {
			$response->msg_type = 'error';
			$response->msg 		= 'You do not have access to the selected module.';
		} else {
			$this->db->select_max('date_set');
			$this->db->where('inactive', 0);
			$this->db->where('deleted', 0);
			$this->db->where('annual', 1);
			$annual_holiday = $this->db->get('holiday')->row();
			$year_query = date('Y', strtotime($annual_holiday->date_set));

			if(date('Y', strtotime($annual_holiday->date_set)) > $this->input->post('year')){
				$year = date('Y', strtotime($annual_holiday->date_set)) - $this->input->post('year');
				$to_do = 0;
			} else {
				$year = $this->input->post('year') - date('Y', strtotime($annual_holiday->date_set));
				$to_do = 1;
			}

			$query = "INSERT INTO ".$this->db->dbprefix."holiday (holiday, date_set, annual, legal_holiday, employee_id, location_id)
					  SELECT holiday, ".($to_do == 1 ? 'DATE_ADD' : 'DATE_SUB')."(date_set, INTERVAL ".$year." YEAR), annual, legal_holiday, employee_id, location_id
					  FROM ".$this->db->dbprefix."holiday
					  WHERE deleted = 0
					  AND inactive = 0
					  AND annual = 1
					  AND date_set LIKE '%".$year_query."%'";

			$this->db->query($query);		
		}
		$this->load->view('template/ajax', array('json' => $response));
	}

	function get_last_year()
	{
		if (!IS_AJAX) {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		} else if ($this->user_access[$this->module_id]['view'] != 1) {
			$response->msg_type = 'error';
			$response->msg 		= 'You do not have access to the selected module.';
		} else {
			$date_send = '';
			$this->db->select_max('date_set');
			$this->db->where('inactive', 0);
			$this->db->where('deleted', 0);
			$this->db->where('annual', 1);
			$get_last_year = $this->db->get('holiday')->row();
			$date_send = date('Y', strtotime('+1 years', strtotime($get_last_year->date_set)));

			if ($date_send == '') {
				$response->msg_type = 'Error';
				$response->msg 		= 'No Saved Holidays';
			} else {
				$response->msg_type = 'success';
				$response->data = $date_send;
			}			
		}
		$this->load->view('template/ajax', array('json' => $response));
	}
	// END custom module funtions
	
}

/* End of file */
/* Location: system/application */
?>
