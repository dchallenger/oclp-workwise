<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Training_position_subject extends MY_Controller
{
	function __construct()
    {
        parent::__construct();

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format	
		
		$this->listview_title = 'Training Position Subject';
		$this->listview_description = 'This module lists all defined training position subject(s).';
		$this->jqgrid_title = "Training Position Subject List";
		$this->detailview_title = 'Training Position Subject Info';
		$this->detailview_description = 'This page shows detailed information about a particular training position subject.';
		$this->editview_title = 'Training Position Subject Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about training position subject(s).';
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
		
		$position_info = $this->db->get_where('user_position',array('position_id'=>$this->input->post('record_id')))->row();
		$data['position_title'] = $position_info->position;

		$this->db->order_by('training_subject');
		$training_subject = $this->db->get('training_subject');

		if( $training_subject->num_rows() > 0 ){
			$data['training_subject_count'] = $training_subject->num_rows();
			$training_subject_list = $training_subject->result_array();

			foreach( $training_subject_list as $key => $val ){

				$training_position_subject = $this->db->get_where('training_position_subject',array('position_id'=>$this->input->post('record_id'),'training_subject_id' => $training_subject_list[$key]['training_subject_id']));

				if( $training_position_subject->num_rows() > 0 ){
					$training_subject_list[$key]['checked'] = 1;
				}

			}

			$data['training_subject_list'] = $training_subject_list;

		}
		else{
			$data['training_subject_count'] = 0;
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
			
			$position_info = $this->db->get_where('user_position',array('position_id'=>$this->input->post('record_id')))->row();
			$data['position_title'] = $position_info->position;

			$this->db->order_by('training_subject');
			$training_subject = $this->db->get('training_subject');

			if( $training_subject->num_rows() > 0 ){
				$data['training_subject_count'] = $training_subject->num_rows();
				$training_subject_list = $training_subject->result_array();

				foreach( $training_subject_list as $key => $val ){

					$training_position_subject = $this->db->get_where('training_position_subject',array('position_id'=>$this->input->post('record_id'),'training_subject_id' => $training_subject_list[$key]['training_subject_id']));

					if( $training_position_subject->num_rows() > 0 ){
						$training_subject_list[$key]['checked'] = 1;
					}

					$this->db->join('training_evaluation','training_evaluation.training_evaluation_id = training_evaluation_subject_list.training_evaluation_id','left');
					$this->db->join('user','user.employee_id = training_evaluation.employee_id','left');
					$this->db->where('user.position_id',$this->input->post('record_id'));
					$this->db->where('training_evaluation_subject_list.training_subject_id',$training_subject_list[$key]['training_subject_id']);
					$training_evaluation_subject_list = $this->db->get('training_evaluation_subject_list');

					if( $training_evaluation_subject_list->num_rows() > 0 ){
						$training_subject_list[$key]['used'] = 1;
					}

				}

				$data['training_subject_list'] = $training_subject_list;

			}
			else{
				$data['training_subject_count'] = 0;
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

		//Delete Process
		$position_training_list = $this->input->post('training_list');

		$training_position_subject_list = $this->db->get_where('training_position_subject',array('position_id' => $this->input->post('record_id')));

		if( $training_position_subject_list->num_rows() > 0 ){

			foreach( $training_position_subject_list->result() as $training_position_subject_info ){

				$this->db->join('training_evaluation','training_evaluation.training_evaluation_id = training_evaluation_subject_list.training_evaluation_id','left');
				$this->db->join('user','user.employee_id = training_evaluation.employee_id','left');
				$this->db->where('user.position_id',$this->input->post('record_id'));
				$this->db->where('training_evaluation_subject_list.training_subject_id',$training_position_subject_info->training_subject_id);
				$training_evaluation_subject_list = $this->db->get('training_evaluation_subject_list');

				if( $training_evaluation_subject_list->num_rows() == 0 ){
					if( !in_array($training_position_subject_info->training_subject_id, $position_training_list) ){

						$this->db->delete('training_position_subject',array('position_id' => $this->input->post('record_id'), 'training_subject_id' => $training_position_subject_info->training_subject_id));

					}
				}
			}
		}

		//Insert Process
		foreach( $position_training_list as $position_training_info ){

			$training_position_subject = $this->db->get_where('training_position_subject',array('position_id'=>$this->input->post('record_id'),'training_subject_id'=>$position_training_info));

			if( $training_position_subject->num_rows() == 0 ){

				$data = array(
					'position_id' => $this->input->post('record_id'),
					'training_subject_id' => $position_training_info
				);

				$this->db->insert('training_position_subject',$data);

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
                            

             
        $buttons .= "</div>";
                
		return $buttons;
	}
	// END custom module funtions
	
}

/* End of file */
/* Location: system/application */
?>