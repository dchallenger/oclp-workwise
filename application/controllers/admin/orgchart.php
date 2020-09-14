<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Orgchart extends MY_Controller
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

	function edit(){
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

	function ajax_save(){
		if($this->input->post('multiple_desc') != null)
		{
			$mult_desc = $this->input->post('multiple_desc');
			$tobe_save = array();
			$ctr = 0;
			foreach($mult_desc AS $key => $desc)
			{
				
				$tobe_save[$ctr]['upload_id'] = $key;
				$tobe_save[$ctr]['description'] = $desc;
				$ctr++;
			}
			$this->db->update_batch('file_upload', $tobe_save, 'upload_id');
		}

		parent::ajax_save();

		//additional module save routine here
	}

	function delete(){
		parent::delete();

		//additional module delete routine here
	}
	// END - default module functions

	// START custom module funtions
	
	/*
	 * Get the org chart
	 * @return json
	 */
	function get_orgchart(){
		if( !IS_AJAX ){
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url().$this->module_link);
		}

		$response->has_top_level = false;
		//check top level of orgchart
		$orgchart_id = $this->input->post('record_id');
		$orgchat = $this->db->get_where('orgchart', array('orgchart_id' => $orgchart_id ))->row();
		$this->db->where("parent_ocd_id is null OR parent_ocd_id = ''");
		$show_action = $this->input->post('show_action');
		$show_action = $show_action == "true" ?  true : false;
		$toplevel = $this->db->get_where('orgchart_detail', array('deleted' => 0, 'orgchart_id' => $orgchart_id));
		if( $toplevel->num_rows() == 1 ){
			$response->has_top_level = true;
			$toplevel = $toplevel->row();
			//$orgchart = $this->_build_orgchart_top( $toplevel->employee_id );
			$response->orgchart = '<ul id="org" style="display:none">';
			$user = $this->hdicore->_get_userinfo( $toplevel->employee_id );
			$response->orgchart .= '<li><a href="javascript: get_userdetail('. $user->user_id .')">'.$user->position.'<br/>';
			$avatar = (!empty($user->photo) && file_exists($user->photo) ) ? $user->photo : $this->userinfo['theme'].'/images/no-photo.jpg';
			$response->orgchart .= '<img height="70px" src="'.base_url().$avatar.'"><br/>';
			$response->orgchart .= $user->lastname.', '.$user->firstname.'</a>';
			if( $show_action ) $response->orgchart .= '<br/><a href="javascript: edit_orgchart_item(\'-1\', '.$orgchart_id.','.$toplevel->ocd_id.')">Add</a> &bull; <a href="javascript: edit_orgchart_item('. $toplevel->ocd_id .', '.$orgchart_id.',\'\')">Edit</a>';
			if( $toplevel->build_on_position == 1 ){
				$response->orgchart .= $this->_build_on_position( $user->user_id, $user->position_id, $orgchart->company_id );
			}
			else{
				$response->orgchart .= $this->_build_on_level( $orgchart_id, $toplevel->ocd_id, $show_action );
			}
			$response->orgchart .= '</li>';
			$response->orgchart .= '</ul>';
		}
		
		$data['json'] = $response;
		$this->load->view($this->userinfo['rtheme'].'/template/ajax', $data);
	}
	
	/*
	 * Build the org chart base on level
	 *
	 * @return html
	 */
	function _build_on_level( $orgchart_id = 0, $parent_ocd_id = 0, $show_action = false){
		$higherlevel = $this->db->get_where('orgchart_detail', array('deleted' => 0, 'ocd_id' => $parent_ocd_id))->row();
		$html = "";
		//get sub level
		$sublevels = $this->db->get_where('orgchart_detail', array('deleted' => 0, 'orgchart_id' => $orgchart_id, 'parent_ocd_id' => $parent_ocd_id));
		if( $sublevels->num_rows() > 0 ){
			foreach( $sublevels->result() as $ocd ){
				$user = $this->hdicore->_get_userinfo( $ocd->employee_id );
				$html .= '<li><a href="javascript: get_userdetail('. $user->user_id .')">'.$user->position.'<br/>';
				$avatar = (!empty($user->photo) && file_exists($user->photo) ) ? $user->photo : $this->userinfo['theme'].'/images/no-photo.jpg';
				$html .= '<img height="70px" src="'.base_url().$avatar.'"><br/>';
				$html .= $user->lastname.', '.$user->firstname.'</a>';
				if( $show_action ) $html .= '<br/><a href="javascript: edit_orgchart_item(\'-1\', '.$orgchart_id.','.$ocd->ocd_id.')">Add</a> &bull; <a href="javascript: edit_orgchart_item('. $ocd->ocd_id .', '.$orgchart_id.', '.$parent_ocd_id.')">Edit</a> &bull; <a href="javascript: delete_orgchart_item('. $ocd->ocd_id .')">Del</a>';
				$html .= $this->_build_on_level( $orgchart_id, $ocd->ocd_id, $show_action );
				$html .= '</li>';
			}
		}
		
		//check if build on position
		if( $higherlevel->build_on_position == 1 ){
			$user = $this->hdicore->_get_userinfo( $higherlevel->employee_id );
			$html .= $this->_build_on_position( $user->user_id, $user->position_id, $user->company_id  );
		}
		
		return !empty( $html ) ? '<ul>'. $html .'</ul>' : '';
	}
	
	/*
	 * Build the org chart base on position
	 *
	 * @return html
	 */
	function _build_on_position( $user_id, $position_id = 0, $company_id ){
		$html = "";
		

		$position = $this->db->get_where('user_position', array('position_id' => $position_id, 'company_id' => $company_id))->row();
		
		//get position reporting to this position
		$reporting_to = $this->db->get_where('user_position', array('deleted' => 0, 'reporting_to' => $position_id));
		if($reporting_to->num_rows() > 0){
			foreach($reporting_to->result() as $row){
				$position = $this->db->get_where('user_position', array('position_id' => $row->position_id))->row();
				//get all the people in this position from same company
				$users = $this->db->get_where('user', array('deleted' => 0, 'inactive' => 0, 'company_id' => $position->company_id, 'position_id' => $position->position_id ));
				if( $users->num_rows() > 0 ){
					foreach( $users->result() as $user ){
						$userdata = $this->hdicore->_get_userinfo( $user->user_id );
						$html .= '<li><a href="javascript: get_userdetail('. $userdata->user_id .')">'.$userdata->position.'<br/>';
						$avatar = (!empty($userdata->photo) && file_exists($userdata->photo) ) ? $userdata->photo : $this->userinfo['theme'].'/images/no-photo.jpg';
						$html .= '<img height="70px" src="'.base_url().$avatar.'"><br/>';
						$html .= $userdata->lastname.', '.$userdata->firstname.'</a>';
						$build_position = $this->_build_on_position( $userdata->user_id, $userdata->position_id, $userdata->company_id );
						if( !empty( $build_position ) ) $html .= $build_position;
						$html .= '</li>';
					}
				}
			}
		}

		$position = $this->db->get_where('user_position', array('position_id' => $position_id))->row();
		//get position supervised
		$supervises = !empty( $position->supervises ) ? explode(',', $position->supervises) : array();
		foreach( $supervises as $position_id ){
			$position = $this->db->get_where('user_position', array('position_id' => $position_id))->row();
			//get all the people in this position from same company
			$users = $this->db->get_where('user', array('deleted' => 0, 'inactive' => 0, 'company_id' => $position->company_id, 'position_id' => $position_id ));
			if( $users->num_rows() > 0 ){
				foreach( $users->result() as $user ){
					$userdata = $this->hdicore->_get_userinfo( $user->user_id );
					$html .= '<li><a href="javascript: get_userdetail('. $userdata->user_id .')">'.$userdata->position.'<br/>';
					$avatar = (!empty($userdata->photo) && file_exists($userdata->photo) ) ? $userdata->photo : $this->userinfo['theme'].'/images/no-photo.jpg';
					$html .= '<img height="70px" src="'.base_url().$avatar.'"><br/>';
					$html .= $userdata->lastname.', '.$userdata->firstname.'</a>';
					$build_position = $this->_build_on_position( $userdata->user_id, $userdata->position_id, $userdata->company_id );
					if( !empty( $build_position ) ) $html .= $build_position;
					$html .= '</li>';
				}
			}
		}
		
		return empty($html) ? '' : '<ul>'.$html.'</ul>';
	}
	
	/*
	 * Build Org Chart depending on specified Company
	 *
	 * @return void
	 */
	function company( ){
		$this->listview_title = 'Table of Organization';
		$data['scripts'][] = jOrgChart_script();	// load jqgrid js and default grid js
		$data['content'] = $this->module_link .'/company_orgchart';
		
		if($this->session->flashdata('flashdata')){
			$info['flashdata'] = $this->session->flashdata('flashdata');
			$data['flashdata'] = $this->load->view($this->userinfo['rtheme'].'/template/flashdata', $info, true);
		}
		
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
	/*
	 * Get user overview when org chart is clicked
	 * @return json
	 */
	function get_userdetail(){
		if( !IS_AJAX ){
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url().$this->module_link);
		}
		
		$data['userdata'] = $this->hdicore->_get_userinfo( $this->input->post('user_id') );
		$response->user_detail = $this->load->view( $this->userinfo['rtheme'].'/'.$this->module_link.'/user_detail', $data, true );
		$data['json'] = $response;
		$this->load->view($this->userinfo['rtheme'].'/template/ajax', $data);
	}


	function get_docs(){
		if( !IS_AJAX ){
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url().$this->module_link);
		}
		
		$org = $this->db->get_where('orgchart', array('orgchart_id' => $this->input->post('record_id')));
		// dbug($this->db->last_query()); exit();
		// dbug($org); exit();
		if($org && $org->num_rows() > 0)
		{
			$uploaded_docs = $org->row();
			$docs_id = explode(',', $uploaded_docs->uploaded_files);
			$this->db->where_in('upload_id', $docs_id);
			$uploaded = $this->db->get('file_upload');
		}

		if (!$uploaded || $uploaded->num_rows() == 0) {
			$response->msg_type = 'error';
			$response->msg 		= 'Employee not found.';
		} else {
			$response->msg_type = 'success';

			$response->data = $uploaded->result_array();
		}			

		$this->load->view('template/ajax', array('json' => $response));
	}


	function get_old_doc_value()
	{
		if (!IS_AJAX) {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		} else if ($this->user_access[$this->module_id]['view'] != 1) {
			$response->msg_type = 'error';
			$response->msg 		= 'You do not have access to the selected module.';
		} else {
			
			$document = $this->db->get_where('file_upload', array("upload_id" => $this->input->post('doc_id')));

			if (!$document || $document->num_rows() == 0) {
				$response->data = "";
			} else {
				$document = $document->row();
				$doc_val = $document->description;
				$response->msg_type = 'success';

				$response->data = $doc_val;
			}			
			
			$this->load->view('template/ajax', array('json' => $response));
		}
	}
	// END custom module funtions

}

/* End of file */
/* Location: system/application */