<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Email_queue extends MY_Controller
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
		parent::ajax_save();

		//additional module save routine here
	}

	function delete(){
		parent::delete();

		//additional module delete routine here
	}

	function _default_grid_actions( $module_link = "",  $container = "", $record = array() )
	{
		// set default
		if($module_link == "") $module_link = $this->module_link;
		if($container == "") $container = "jqgridcontainer";

		$actions = '<span class="icon-group">';

        if ($this->user_access[$this->module_id]['configure']) {
        	$actions .= '<a class="icon-button icon-16-document-view" module_link="'.$module_link.'" tooltip="View Body" href="javascript:view_body(\''.$record['id'].'\')"></a>';
        	$actions .= '<a class="icon-button icon-16-mail" module_link="'.$module_link.'" tooltip="Send Email" href="javascript:send(\''.$record['id'].'\')"></a>';
        	$actions .= '<a class="icon-button icon-16-mail" module_link="'.$module_link.'" tooltip="Send Email Yahoo" href="javascript:send_yahoo(\''.$record['id'].'\')"></a>';
        }
        
		$actions .= '</span>';

		return $actions;
	}

	function get_body(){
		if( !IS_AJAX) {
			$this->session->set_flashdata('flashdata', 'System does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url().$this->module_link);
		}

		if( !$this->user_access[$this->module_id]['configure']) {
			$response->msg = "You do not have enough privilege to execute the requested action.<br/>Please contact the System Administrator.";
			$response->msg_type = "attention";

			$this->load->view('template/ajax', array('json' => $response));
			return;
		}

		$email_id = $this->input->post('email_id');
		$response->email = $this->db->get_where('email_queue', array('id' => $email_id))->row();
		$this->load->view('template/ajax', array('json' => $response));

	}

	function send(){
		if( !IS_AJAX) {
			$this->session->set_flashdata('flashdata', 'System does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url().$this->module_link);
		}

		if( !$this->user_access[$this->module_id]['configure']) {
			$response->msg = "You do not have enough privilege to execute the requested action.<br/>Please contact the System Administrator.";
			$response->msg_type = "attention";

			$this->load->view('template/ajax', array('json' => $response));
			return;
		}	

		$email_id = $this->input->post('email_id');
		$mail = $this->db->get_where('email_queue', array('id' => $email_id))->row();
		$this->load->model('template');
		$this->template->change_status($mail->id, 'sending');			

		$mail_config = $this->hdicore->_get_config('outgoing_mailserver');
		$meta = $this->hdicore->_get_meta();

		$this->load->library('email', $mail_config);
		$this->email->set_newline("\r\n");
		$this->email->from($mail_config['smtp_user'], $meta['title']);

		if (trim($mail->to) == '' && trim($mail->cc) == '') {
			$this->email->to($mail_config['smtp_user']);
		} else {
			$this->email->to($mail->to);
			$this->email->cc($mail->cc);
		}

		$this->email->subject($mail->subject);
		$this->email->message($mail->body);
		if ( !$this->email->send() )
		{
			log_message('error', $this->email->print_debugger());
			$this->template->change_status($mail->id, 'queued');

			$response->msg =  $this->email->print_debugger();
			$response->msg_type = "error";
		}
		else{
			$this->template->delete_from_queue( $mail->id);
			$this->template->change_status($mail->id, 'queued');

			$response->msg = 'Message sent';
			$response->msg_type = "success";
		}

		$this->load->view('template/ajax', array('json' => $response));
	}

	public function send_mail() { 

		$from_email = "hris.ortigas.com.ph"; 
		$to_email = 'tjkgarcia@yahoo.com'; 

		$email_id = $this->input->post('email_id');
		$mail = $this->db->get_where('email_queue', array('id' => $email_id))->row();
		$this->load->model('template');
		$this->template->change_status($mail->id, 'sending');

		//Load email library 
		$this->load->library('email'); 

		$this->email->set_mailtype("html");
		$this->email->from($from_email, 'Administrator'); 
		$this->email->to($to_email);
		$this->email->subject($mail->subject); 
		$this->email->message($mail->body); 

		$this->response = new stdClass();

		if($this->email->send()) {
			$response->msg = 'Message sent';
			$response->msg_type = "success";
		}

		$this->load->view('template/ajax', array('json' => $response));		
	}	
	// END - default module functions

	// START custom module funtions

	// END custom module funtions

}

/* End of file */
/* Location: system/application */