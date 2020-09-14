<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Uploading extends MY_Controller
{
	function __construct(){
		parent::__construct();
		
		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format
		
		$this->listview_title = '';
		$this->listview_description = 'This module lists all defined (s).';
		$this->jqgrid_title = " List";
		$this->detailview_title = ' Info';
		$this->detailview_description = 'This page shows detailed information about a particular ';
		$this->editview_title = ' Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about ';
	}

	// START - default module functions
	// default jqgrid controller method
	function index(){
		if($this->user_access[$this->module_id]['list'] != 1){
			$this->session->set_flashdata('flashdata', 'You dont have sufficient privilege to execute the list action of '.$this->module_name.'! Please contact the System Administrator.');
			redirect( base_url() );
		}
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
		
		$tabs[] = '<li><a href="' . site_url('dtr/periods') .'">Periods</li>';
		//$tabs[] = '<li><a href="' . site_url('employee/dtr/manage') .'">Manage DTR</li>';
		$tabs[] = '<li class="active"><a href="javascript:void(0)">Uploading</li>';

		$data['tab'] = addslashes('<ul id="grid-filter">'. implode('', $tabs) .'</ul>');

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

	function detail(){
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
			$data['buttons'] = 'dtr/editview-buttons';

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
		$msg = "";
		if($this->input->post('record_id') == -1)
		{
			switch( $this->input->post('location_id') ){
				case '2':
					if(substr($this->input->post('filename'),-3) != 'dat') $msg = 'Wrong Type filename should be .dat!';
					break;
				case '5':
					if(substr($this->input->post('filename'),-3) != 'xls') $msg = 'Wrong Type filename should be .xls!';
					break;					
				default:
					if(substr($this->input->post('filename'),-3) != 'txt') $msg = 'Wrong Type filename should be .txt!';
					break;
			}

			if ($msg != "") {
				$response = parent::get_message(); 
				$response->msg_type = 'error';
				$response->msg = $msg;
				parent::set_message($response);			
				parent::after_ajax_save();
				return;	
			} 
			
			$this->load->helper('time_upload');
			
			$file_name_exp = explode('_', $this->input->post('filename'));
			$file = array();
			$file_cnt = count($file_name_exp)-1;
			for ($i=1; $i<=$file_cnt ; $i++) { 
				$file[] = $file_name_exp[$i];
			}
			
			$file_name = implode('_', $file);
			$time_keeping_exist = $this->db->get_where('timekeeping_uploads', array('filename' => $file_name));
			if($time_keeping_exist->num_rows() > 0){
				$response = parent::get_message(); 
				$response->msg_type = 'info';
				$response->msg = "This file is already uploaded!";
				parent::set_message($response);			
				parent::after_ajax_save();
				return;	
			}

			parent::ajax_save();
			$id = $this->db->insert_id();
			$this->locationinfo['location_id'] = $this->input->post('location_id');
			$this->locationinfo['full_path'] = $this->input->post('filename');
			if($this->input->post('location_id') == '3'){
				$result = do_time_upload_lotus_notes();
			}
			else{
				$result = do_time_upload();
			}
			$lowest = $result['lowest'];
			$highest = $result['highest'];			
			$count = $result['count'];
			$user_id = $result['user'];
			$update_qry="UPDATE {$this->db->dbprefix}timekeeping_uploads SET `log_date_start` = '{$lowest}', `log_date_end` = '{$highest}',
						`log_count` = {$count}, `filename` = '{$file_name}', `created_by` = '{$user_id}' WHERE `upload_id` = '{$id}'";
			$this->db->query($update_qry);

			return;	
		}

		$response->msg_type = 'error';
		$response->msg = "Invalid input!";
		$this->load->view('template/ajax', array('json' => $response));
	}
	
	function delete()
	{
		show_404();
		//additional module delete routine here
	}
	// END - default module functions

	// START custom module funtions
	function _default_grid_buttons( $module_link = "", $related_field = "", $related_field_value = "", $addtext = "", $deltext = "", $container = "" )
	{
		// set default
		if($module_link == "") $module_link = $this->module_link;
		if($addtext == "") $addtext = "Upload";
		if($container == "") $container = "jqgridcontainer";

		$buttons = "<div class='icon-label-group'>";                    

        if ($this->user_access[$this->module_id]['add']) {
            $buttons .= "<div class='icon-label'>";
            $buttons .= "<a class='icon-16-add icon-16-add-listview' related_field='".$related_field."' related_field_value='".$related_field_value."' module_link='".$module_link."' href='javascript:void(0)'>";
            $buttons .= "<span>".$addtext."</span></a></div>";
        }

        $buttons .= "</div>";

		return $buttons;
	}

	function check(){
		$this->load->helper('time_upload');
		check3();
	}

	// END custom module funtions

}

/* End of file */
/* Location: system/application */