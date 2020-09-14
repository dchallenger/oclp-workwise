<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Comments extends MY_Controller
{
	function __construct()
    {
        parent::__construct();

		//set  variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format	
		
		$this->listview_title = 'Comments';
		$this->listview_description = 'This  lists all submitted comments.';
		$this->jqgrid_title = "Comments List";
		$this->detailview_title = 'Review Comment';
		$this->detailview_description = 'This page shows detailed information about a particular comment.';
		$this->editview_title = 'Add/Edit Comment';
		$this->editview_description = 'This page allows control over a comment.';

		$this->load->model('portlet');

		$this->default_sort_col = array('t0firstnamemiddleinitiallastnameaux');
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
		if ($this->input->post('type') == 'group')	{
			$_POST['comment_group_identifier'] = $this->input->post('identifier');
		}

		if ($this->input->post('record_id') == "-1") {
			$_POST['date_created'] = date('Y-m-d H:i:s');
		}

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
	
	/**
	 * Place these abstractions so that the ajax_save response message can be altered by other controllers.
	 */
	public function after_ajax_save() {		
		$response = $this->get_message();

		$response->comment      = $this->input->post('comment');
		$response->created_date = date(
									$this->config->item('display_datetime_format'), 
									strtotime($this->input->post('date_created'))
								);

		$this->db->where('user_id', $this->input->post('user_id'));

		$user = $this->db->get('user')->row();

		$response->name = $user->firstname . ' ' . $user->lastname;

		$this->load->vars(array('json' => $response));
		$this->load->view($this->userinfo['rtheme'].'/template/ajax');		
	}

	function get_comments()
	{		
		$identifier = $this->input->post('identifier');
		$type	    = $this->input->post('type');
		$callback	    = $this->input->post('callback');

		$response->msg = '';

		switch ($type)
		{
			case 'group': 
				$comments = $this->portlet->fetch_comment_group($identifier);
				break;
			case 'user':
				$comments = $this->portlet->fetch_user_comments($identifier);
				break;
			default:
				$comments = $this->portlet->fetch_all();
		}

		$response->comments = $comments;

		if ($this->input->post('boxy') == 1)
		{
			$data = array('comments' => $comments, 'identifier' => $identifier, 'type' => $type, 'callback' => $callback);
			$response->comment_box = $this->load->view($this->userinfo['rtheme'].'/comments_boxy', $data, TRUE);
		}

		$data['json'] = $response;

		$this->load->view('template/ajax', $data);
	}
	// END custom module funtions
	
}

/* End of file */
/* Location: system/application */
?>