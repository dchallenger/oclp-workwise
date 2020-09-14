<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Appraisal_template extends MY_Controller
{
	function __construct()
    {
        parent::__construct();

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format

		$this->listview_title = '';
		$this->listview_description = 'This module lists appraisal templates.';
		$this->jqgrid_title = "List";
		$this->detailview_title = ' Info';
		$this->detailview_description = 'This page shows detailed information about an appraisal template.';
		$this->editview_title = ' Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about an appraisal template.';
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
		$company_id = $this->input->post('company_id');

        $this->db->where('deleted',0);
        $this->db->where('employee_appraisal_template_company_id !=',$this->input->post('record_id'));
		$result = $this->db->get_where('employee_appraisal_template_company',array('company_id' => $company_id));

		if($result && $result->num_rows > 0){
			$response->msg_type = 'error';
 			$response->msg 		= 'Duplicate entry is not allowed. / Appraisal Template is already created.';
            $data['json'] = $response;
            $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
            return;
		}
		else{
			parent::ajax_save();
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

/*	protected function after_ajax_save()
	{
		if ($this->get_msg_type() == 'success') {
			if ($this->input->post('record_id') == '-1') {
				$update['created_date'] = date('Y-m-d H:i:s');
				$update['created_by']   = $this->userinfo['user_id'];
				$update['status']		= 1;
			}

			$update['updated_date'] = date('Y-m-d H:i:s');
			$update['updated_by']   = $this->userinfo['user_id'];

			$this->db->where($this->key_field, $this->key_field_val);
			$this->db->update($this->module_table, $update);

			$this->db->where($this->key_field, $this->key_field_val);
			$criterias = $this->db->get('employee_appraisal_criteria');

			if ($criterias && $criterias->num_rows() > 0) {
				foreach ($criterias->result() as $criteria) {
					$this->db->where('employee_appraisal_criteria_id', $criteria->employee_appraisal_criteria_id);
					$this->db->where('deleted', 0);
					$this->db->from('employee_appraisal_criteria_question');

					$questions = $this->db->count_all_results();
					$scale     = explode(',', $criteria->option_values);

					$this->db->set('mws', 'multiplier * ' . $questions . '*' . $scale[count($scale) - 1], FALSE);

					$this->db->where('employee_appraisal_criteria_id', $criteria->employee_appraisal_criteria_id);
					$this->db->update('employee_appraisal_criteria');					
				}
			}		
		}

		parent::after_ajax_save();
	}*/

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
				
        $actions .= '<a class="icon-button icon-16-document-view" tooltip="View Rank" container="'.$container.'" module_link="admin/appraisal_template_position" href="javascript:void(0)"></a>';
        
        if ($this->user_access[$this->module_id]['delete']) {
            $actions .= '<a class="icon-button icon-16-delete delete-single" tooltip="Delete" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
        }

        $actions .= '</span>';

		return $actions;
	}	

	/**
	 * Return the view file for template criterias in json format.
	 * 	 
	 * @return json
	 */
	function get_template_criterias()
	{
		$record_id = $this->input->post('record_id');		

		if (!$record_id || $record_id <= 0) {
			$response->msg 		= 'Invalid ID.';
			$response->msg_type = 'error';			
		} else {
			$response->msg_type = 'success';

			$this->db->where($this->key_field, $record_id);
			$this->db->where('deleted', 0);			

			$criterias = $this->db->get('employee_appraisal_criteria');
			
			$response->html = $this->_get_criteria_html($criterias);			
		}

		$this->load->view('template/ajax', array('json' => $response));
	}

	/**
	 * Return the view file for template criterias.
	 * 
	 * @param  object CI_DB_mysql_result $criterias
	 * @return string
	 */
	private function _get_criteria_html($criterias)
	{
		if (!$criterias || $criterias->num_rows() == 0) {
			return '';
		} else {
			$criterias = $criterias->result();
			$view = '';
			foreach ($criterias as $criteria) {
				$data['questions'] = array();
				$data['criteria']  = $criteria;

				$this->db->where('employee_appraisal_criteria_id', $criteria->employee_appraisal_criteria_id);
				$this->db->where('deleted', 0);

				$question_o = $this->db->get('employee_appraisal_criteria_question');
				
				if ($question_o->num_rows() > 0) {
					$data['questions'] = $question_o->result();
				}

				$view .= $this->load->view('employees/appraisal/criteria', $data, TRUE, FALSE);
			}
		}

		return $view;
	}
	// END custom module funtions

}

/* End of file */
/* Location: system/application */