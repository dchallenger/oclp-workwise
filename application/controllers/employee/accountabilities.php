<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Accountabilities extends MY_Controller
{
	function __construct()
    {
        parent::__construct();

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format

		$this->listview_title = 'Employee Accountabilities';
		$this->listview_description = 'This module lists Employees Accountabilities';
		$this->jqgrid_title = "List";
		$this->detailview_title = ' Info';
		$this->detailview_description = '';
		$this->editview_title = ' Add/Edit';
		$this->editview_description = '';

		if(!$this->user_access[$this->module_id]['post'])
			$this->filter = $this->module_table.'.created_by = '.$this->userinfo['user_id'];
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

		//$data['buttons'] = $this->module_link . '/detail-buttons';	

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

			//$data['buttons'] = $this->module_link . '/edit-buttons';

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

	// function _default_grid_buttons( $module_link = "", $related_field = "", $related_field_value = "", $addtext = "", $deltext = "", $container = "" )
	// {
	// 	// set default
	// 	if($module_link == "") $module_link = $this->module_link;
	// 	if($addtext == "") $addtext = "Add";
	// 	if($deltext == "") $deltext = "Delete";
	// 	if($container == "") $container = "jqgridcontainer";

	// 	$buttons = "<div class='icon-label-group'>";                    
                            
 //        if ($this->user_access[$this->module_id]['add']) {
 //            $buttons .= "<div class='icon-label'>";
 //            $buttons .= "<a class='icon-16-add icon-16-add-listview' related_field='".$related_field."' related_field_value='".$related_field_value."' module_link='".$module_link."' href='javascript:void(0)'>";
 //            $buttons .= "<span>".$addtext."</span></a></div>";
 //        }
         
 //        if ($this->user_access[$this->module_id]['delete']) {
 //            $buttons .= "<div class='icon-label'><a class='icon-16-delete delete-array' container='".$container."' module_link='".$module_link."' href='javascript:void(0)'><span>".$deltext."</span></a></div>";                            
 //        }
       
 //        $buttons .= "</div>";
                
	// 	return $buttons;
	// }

	function ajax_save()
	{
		parent::ajax_save();

		//additional module save routine here

	}

	function delete()
	{
		parent::delete();

		//additional module delete routine here
	}
	// END - default module functions

	// START custom module funtions
	function after_ajax_save()
	{
		if ($this->get_msg_type() == 'success') {
			$data['updated_by']   = $this->userinfo['user_id'];
			$data['date_updated'] = date('Y-m-d H:i:s');

			if ($this->input->post('record_id') == '-1') {
				$data['created_by']   = $this->userinfo['user_id'];
				$data['date_created'] = date('Y-m-d H:i:s');
			}

			$this->db->where($this->key_field, $this->key_field_val);
			$this->db->update($this->module_table, $data);
		}

		parent::after_ajax_save();
	}

	// function get_dropdown()
	// {
	// 	$emp = $this->db->get_Where('employee', array('employee_id' => $this->user->user_id ))->row();

	// 	$subordinates = $this->hdicore->get_subordinates($this->userinfo['position_id'], $emp->rank_id, $this->user->user_id);

	// 	$subordinate_id = array(0);

	// 	if( count($subordinates) > 0 ){

	// 		$subordinate_id = array();

	// 		foreach ($subordinates as $subordinate) {
	// 				$subordinate_id[] = $subordinate['user_id'];
	// 		}
	// 	}

	// 	$subordinate_list = implode(',', $subordinate_id);

	// 	$qry = "SELECT *
	// 			FROM {$this->db->dbprefix}user
	// 			WHERE deleted = 0
	// 			AND inactive = 0
	// 			";

	// 	if( $subordinate_list != "" )
	// 		$qry .=	"AND employee_id IN (".$subordinate_list.")";
	// 	else
	// 		$qry .=	"AND employee_id IN (0)";

	// 	$subs = $this->db->query($qry);

	// 	foreach($subs->result_array() as $sub)
	// 		$html .= '<option value="'.$sub["employee_id"].'">'.$sub["firstname"].' '.$sub["middlename"].' '.$sub["lastname"].'</option>';

 //        $data['html'] = $html;

 //        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);			
	// }

	// END custom module funtions

}

/* End of file */
/* Location: system/application */