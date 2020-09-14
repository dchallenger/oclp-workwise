<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class Clearance_exit_interview_category extends MY_Controller
{
	function __construct()
    {
        parent::__construct();

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format	
		
		$this->listview_title = 'Exit Interview Questionnaire';
		$this->listview_description = 'This module lists all defined exiti interview category(s).';
		$this->jqgrid_title = "Exit Interview Category List";
		$this->detailview_title = 'Exit Interview Category Info';
		$this->detailview_description = 'This page shows detailed information about a particular Exit Interview category.';
		$this->editview_title = 'Exit Interview Category Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about exit interview category(s).';
    	$this->detail_type = array('item');	

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

		$this->db->order_by('exit_interview_score_type');
		$item_result = $this->db->get('clearance_exit_interview_score_type');
		$item_list = $item_result->result_array();
		$item_list['count'] = $item_result->num_rows();
		$data['item_score_type_list'] = $item_list;
		$data['item_count'] = $this->input->post('item_count');

		foreach( $this->detail_type as $detail ){
			$data[$detail] = $this->_get_exit_interview_detail($this->input->post('record_id'),$detail);
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
			$data['scripts'][] = chosen_script();

			$data['buttons'] = $this->module_link . '/edit-buttons';
			
			//other views to load
			$data['views'] = array();
			$data['views_outside_record_form'] = array();


			foreach( $this->detail_type as $detail ){
				$data[$detail] = $this->_get_exit_interview_detail($this->input->post('record_id'),$detail);

				if( $detail == 'item' ){

					foreach( $data[$detail] as $key => $val ){

						$clearance_exit_interview_score = $this->db->get_where('clearance_exit_interview_item_score',array('clearance_exit_interview_item_id'=>$data[$detail][$key]['clearance_exit_interview_item_id']));

						if( $clearance_exit_interview_score->num_rows() > 0 ){
							$data[$detail][$key]['used'] = 1;
						}
						else{
							$data[$detail][$key]['used'] = 0;
						}
					}

				}

			}

			$this->db->order_by('exit_interview_score_type');
			$item_result = $this->db->get('clearance_exit_interview_score_type');
			$item_list = $item_result->result_array();
			$item_list['count'] = $item_result->num_rows();
			$data['item_score_type_list'] = $item_list;
			$data['item_count'] = $this->input->post('item_count');


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
		if($this->input->post('active') == 1){
			//set all category to inactive; only one should be active
			$this->db->update($this->module_table,array('active'=>0));
		}

		parent::ajax_save();

		foreach( $this->detail_type as $detail ){

			$table = 'clearance_exit_interview_'.$detail;

			if ($this->db->table_exists($table)) {
				$post = $this->input->post($detail);

				if (!is_null($post) && is_array($post)) {
					// Handle the dates.
					foreach ($post as $key => $value) {
						
						$key_string_segments = explode('_', $key);

					}

					if( $detail == 'item' ){

						//remove unecessary variables not use in saving data
						unset($post['item_rand']);

					}

					$data = $this->_rebuild_array($post, $this->key_field_val);

					
					if( $detail == 'item' ){

						$clearance_exit_interview_item_list = $this->db->get_where($table,array('clearance_exit_interview_category_id'=>$this->key_field_val));

						if( $clearance_exit_interview_item_list > 0 ){

							$clearance_exit_interview_item_list = $clearance_exit_interview_item_list->result();

							foreach( $clearance_exit_interview_item_list as $clearance_exit_interview_item_list_info ){

								$this->db->where('clearance_exit_interview_item_id',$clearance_exit_interview_item_list_info->clearance_exit_interview_item_id);
								$clearance_exit_interview_score_list = $this->db->get('clearance_exit_interview_item_score');

								if( $clearance_exit_interview_score_list->num_rows() == 0 ){

									$exit_interview_item_count = 0;

									foreach( $data as $exit_interview_item_info ){
										if( $exit_interview_item_info['clearance_exit_interview_item_id'] == $clearance_exit_interview_item_list_info->clearance_exit_interview_item_id ){
											$exit_interview_item_count++;
										}
									}

									if( $exit_interview_item_count == 0 ){

										$this->db->delete($table,array('clearance_exit_interview_item_id'=>$clearance_exit_interview_item_list_info->clearance_exit_interview_item_id));

									}

								}

							}
						}
				
						foreach( $data as $clearance_exit_interview_item_info ){

							if( $clearance_exit_interview_item_info['clearance_exit_interview_item_id'] == 0 ){
								unset($clearance_exit_interview_item_info['clearance_exit_interview_item_id']);
								$this->db->insert($table,$clearance_exit_interview_item_info);
							}
							else{

								$clearance_exit_interview_item = $this->db->get_where($table,array('clearance_exit_interview_item_id'=>$clearance_exit_interview_item_info['clearance_exit_interview_item_id']))->num_rows();

								if( $clearance_exit_interview_item > 0 ){

									$clearance_exit_interview_item_id = $clearance_exit_interview_item_info['clearance_exit_interview_item_id'];
									unset($clearance_exit_interview_item_info['clearance_exit_interview_item_id']);

									$this->db->update($table,$clearance_exit_interview_item_info,array('clearance_exit_interview_item_id'=>$clearance_exit_interview_item_id));

								}

							}

						}

					}	
				}
			}

		}


		//additional module save routine here
				
	}
	
	function delete()
	{

		$validate_error = 0;
		$record_id_list = explode(',', $this->input->post('record_id'));

		foreach( $record_id_list as $record_id ){

			$exit_interview_items_result = $this->db->get_where('clearance_exit_interview_item',array('clearance_exit_interview_category_id'=>$record_id));

			if( $exit_interview_items_result->num_rows() > 0 ){

				$clearance_exit_interview_items_list = $exit_interview_items_result->result();

				foreach( $clearance_exit_interview_items_list as $clearance_exit_interview_items_info ){

					$clearance_exit_interview_score_result = $this->db->get_where('clearance_exit_interview_item_score',array('clearance_exit_interview_item_id'=>$clearance_exit_interview_items_info->clearance_exit_interview_item_id));

					if( $clearance_exit_interview_score_result->num_rows() > 0 ){

						$validate_error = 1;

					}

				}
			}
		}

		if( $validate_error > 0 ){

			$response->msg = "Cannot delete Exit Interview Category. There are items in selected Exit Interview Questionnaire that is already in use.";
			$response->msg_type = 'error';

		}
		else{

			foreach( $record_id_list as $record_id ){

				$exit_interview_items_result = $this->db->get_where('clearance_exit_interview_item',array('clearance_exit_interview_category_id'=>$record_id));

				if( $exit_interview_items_result->num_rows() > 0 ){

					$clearance_exit_interview_items_list = $exit_interview_items_result->result();

					foreach( $clearance_exit_interview_items_list as $clearance_exit_interview_items_info ){

						$clearance_exit_interview_score_result = $this->db->get_where('clearance_exit_interview_item_score',array('clearance_exit_interview_item_id'=>$clearance_exit_interview_items_info->clearance_exit_interview_item_id));

						if( $clearance_exit_interview_score_result->num_rows() == 0 ){

							$this->db->delete('clearance_exit_interview_item',array('clearance_exit_interview_category_id'=>$record_id));

						}

					}

				}

			}

			parent::delete();

		}

		$data['json'] = $response;
		$this->load->view($this->userinfo['rtheme'].'/template/ajax', $data);

		

		//parent::delete();
		
		//additional module delete routine here
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
        
        $buttons .= "</div>";
                
		return $buttons;
	}

	function _default_grid_actions( $module_link = "",  $container = "", $record = array() )
	{
		// set default
		if($module_link == "") $module_link = $this->module_link;
		if($container == "") $container = "jqgridcontainer";

		$actions = '<span class="icon-group">';
                
        if ($this->user_access[$this->module_id]['view']) {
            $actions .= '<a class="icon-button icon-16-info" module_link="'.$module_link.'" tooltip="View" href="javascript:void(0)"></a>';
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


	function print_record(){


		
	}
	// END - default module functions
	
	// START custom module funtions
	

	function get_form($type) {
		if (IS_AJAX) {
			if ($type == '') {
				show_error("Insufficient data supplied.");
			} else {

				if( $type == 'item' ){

					$this->db->order_by('exit_interview_score_type');
					$item_result = $this->db->get('clearance_exit_interview_score_type');

					$item_list = $item_result->result_array();
					$data['item_score_type_list'] = $item_list;
					$data['item_count'] = $this->input->post('item_count');
					$data['item_rand'] = $rand = rand(1,10000000);

				}


				$response = $this->load->view($this->userinfo['rtheme'] . '/employee/clearance_exit_interview_category/'.$type.'_form', $data);

				$data['html'] = $response;

				$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
			}
		} else {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
	}

	private function _rebuild_array($array, $fkey = null) {
		if (!is_array($array)) {
			return array();
		}

		$new_array = array();

		$count = count(end($array));
		$index = 0;

		while ($count >= $index) {
			foreach ($array as $key => $value) {

				if( isset( $array[$key][$index] ) ){

					$new_array[$index][$key] = $array[$key][$index];
					if (!is_null($fkey)) {
						$new_array[$index][$this->key_field] = $fkey;
					}

				}
				else{

					continue;

				}
			}

			$index++;
		}

		return $new_array;
	}

	private function _get_exit_interview_detail($record_id = 0, $detail_type = "") {
		if ($record_id == 0) {
			$record_id = $this->input->post('record_id');
		}

		$response = array();

		$table = 'clearance_exit_interview_'.$detail_type;
		$this->db->where('clearance_exit_interview_category_id', $record_id);

		if( $detail_type == 'item' ){
			$this->db->order_by('clearance_exit_interview_item_no', 'ASC');
		}

		$result = $this->db->get($table);

		if ($result){
			$response = $result->result_array();
		}
		

		return $response;
	}

	// END custom module funtions
	
}

/* End of file */
/* Location: system/application */
?>