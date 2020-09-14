<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Training_revalida_master extends MY_Controller
{
	function __construct()
    {
        parent::__construct();

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format	
		
		$this->listview_title = 'Training Revalida';
		$this->listview_description = 'This module lists all defined training type(s).';
		$this->jqgrid_title = "Training Revalida List";
		$this->detailview_title = 'Training Revalida Info';
		$this->detailview_description = 'This page shows detailed information about a particular training type.';
		$this->editview_title = 'Training Revalida Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about training revalida(s).';
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

		$item_result = $this->db->get('training_revalida_score_type');
		$item_list = $item_result->result_array();
		$data['item_score_type_list'] = $item_list;

		$data['category'] = $this->_get_revalida_detail($this->input->post('record_id'),'category');

		foreach( $data['category'] as $key => $val ){

			$category_rand = rand(1,100000000);

			$data['category'][$key]['category_rand'] = $category_rand;
			$data['category'][$key]['items'] = $this->_get_revalida_detail($data['category'][$key]['training_revalida_category_id'],'item');
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
			
			//other views to load
			$data['views'] = array();
			$data['views_outside_record_form'] = array();

			$data['buttons'] = $this->module_link . "/edit-buttons";

			$item_result = $this->db->get('training_revalida_score_type');
			$item_list = $item_result->result_array();
			$data['item_score_type_list'] = $item_list;


			$data['category'] = $this->_get_revalida_detail($this->input->post('record_id'),'category');

			foreach( $data['category'] as $key => $val ){

				$category_rand = rand(1,100000000);

				$data['category'][$key]['category_rand'] = $category_rand;
				$data['category'][$key]['items'] = $this->_get_revalida_detail($data['category'][$key]['training_revalida_category_id'],'item');
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
		else{
			$this->session->set_flashdata('flashdata', 'You dont have sufficient privilege to execute the edit action! Please contact the System Administrator.');
			redirect(base_url().$this->module_link);
		}
	}		
	
	function ajax_save()
	{	

		parent::ajax_save();

		if( $this->input->post('draft') == 1 ){
			$draft = 1;
		}
		else{
			$draft = 0;
		}

		$this->db->where('training_revalida_master_id',$this->key_field_val);
		$this->db->update('training_revalida_master',array('draft'=>$draft));

		$post = $this->input->post('category');
		$category_data = $this->_rebuild_array($post, $this->key_field_val);

		if( $this->input->post('record_id') != '-1' ){

			$category_result = $this->db->get_where('training_revalida_category',array('training_revalida_master_id'=>$this->input->post('record_id')))->result();

			foreach( $category_result as $category_info ){

				$this->db->where('training_revalida_category_id',$category_info->training_revalida_category_id);
				$this->db->delete('training_revalida_item');

			}
			
			$this->db->where('training_revalida_master_id',$this->key_field_val);
			$this->db->delete('training_revalida_category');

		}

		foreach( $category_data as $category_info ){

			$data = array(
				'training_revalida_master_id' => $this->key_field_val,
				'revalida_category' => $category_info['revalida_category'],
				'revalida_category_weight' => $category_info['revalida_category_weight'],
			);

			$this->db->insert('training_revalida_category',$data);

			$category_id = $this->db->insert_id();
			$item_data = $this->_rebuild_array($_POST['category'][$category_info['item_rand']], $this->key_field_val);

			foreach( $item_data as $item_info ){

				$item = array(
					'training_revalida_category_id'=>$category_id,
					'training_revalida_item_no'=>$item_info['training_revalida_item_no'],
					'description'=>$item_info['description'],
					'score_type'=>$item_info['score_type'],
					'item_weight'=>$item_info['item_weigth']
				);

				$this->db->insert('training_revalida_item',$item);

			}

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
	
	function get_form($type) {


		if (IS_AJAX) {
			if ($type == '') {
				show_error("Insufficient data supplied.");
			} else {

				$data = array();

				if( $type == 'item' ){

					$item_result = $this->db->get('training_revalida_score_type');

					$item_list = $item_result->result_array();
					$data['item_score_type_list'] = $item_list;
					$data['item_count'] = $this->input->post('item_count');
					$data['category_rand'] = $this->input->post('category_rand');

				}
				else{

					$data['category_rand'] = rand(1,100000000);


				}


				$response = $this->load->view($this->userinfo['rtheme'] . '/training/training_revalida_master/'.$type.'_form', $data);
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

	private function _get_revalida_detail($record_id = 0, $detail_type = "") {
		if ($record_id == 0) {
			$record_id = $this->input->post('record_id');
		}

		$response = array();

		if( $detail_type == 'category' ){
			$table = 'training_revalida_'.$detail_type;
			$this->db->where('training_revalida_master_id', $record_id);
			$this->db->order_by('training_revalida_category_id', 'ASC');
		}
		elseif( $detail_type == 'item' ){
			$table = 'training_revalida_'.$detail_type;
			$this->db->where('training_revalida_category_id', $record_id);
			$this->db->order_by('training_revalida_item_no', 'ASC');
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