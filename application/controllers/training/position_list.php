<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Position_list extends MY_Controller
{
	function __construct()
    {
        parent::__construct();

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format	
		
		$this->listview_title = 'Position List';
		$this->listview_description = 'This module lists all defined position list(s).';
		$this->jqgrid_title = "Position List List";
		$this->detailview_title = 'Position List Info';
		$this->detailview_description = 'This page shows detailed information about a particular position list.';
		$this->editview_title = 'Position List Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about position list(s).';
    

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
	
	function detail() {        
        show_error('Function does not exist.');
    }

    function edit() {
        show_error('Function does not exist.');
    }	
	
	function ajax_save() {
        show_error('Function does not exist.');
    }

    function delete() {
        show_error('Function does not exist.');
    }

	// END - default module functions
	
	// START custom module funtions
	
	function _default_grid_buttons( $module_link = "", $related_field = "", $related_field_value = "", $addtext = "", $deltext = "", $container = "" )
	{
		// set default
		if($module_link == "") $module_link = $this->module_link;
		if($addtext == "") $addtext = "Add";
		if($deltext == "") $deltext = "Delete";
		if($container == "") $container = "jqgridcontainer";

		$buttons = "<div class='icon-label-group'>";                    
                          
        
        $buttons .= "</div>";
                
		return $buttons;
	}


	function _default_grid_actions($module_link = "", $container = "", $record = array()) {


		$training_calendar = $this->db->get_where($this->module_table, array($this->key_field => $record['training_calendar_id']));

		if( $training_calendar->num_rows() == 1 ){
			$training_calendar = $training_calendar->row();
		}

		// set default
		if ($module_link == "")
			$module_link = $this->module_link;
		if ($container == "")
			$container = "jqgridcontainer";

		$training_feedback = $this->db->get_where('training_feedback',array('training_calendar_id'=>$training_calendar->training_calendar_id, 'employee_id'=>$this->userinfo['user_id']));
		$feedback_participant = false;

		if( $training_feedback->num_rows() > 0 ){
			$feedback_participant = true;
		}

		$actions = '<span class="icon-group">';

		if ($this->user_access[$this->module_id]['post']) {
			$actions .= '<a class="icon-button icon-16-search search_skills" tooltip="Search Skill Set" module_link="'.$module_link.'" onclick="" href="javascript:void(0)"></a>';
		}

		$actions .= '</span>';

		return $actions;
	}

	// END custom module funtions
	
}

/* End of file */
/* Location: system/application */
?>