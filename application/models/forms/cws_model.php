<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Cws_model extends MY_Model
{
	function __construct()
	{
		parent::__construct();
	}


    function _get_employee_sched( $return = false ){
        if( !$this->input->post('employee_id') ){
            $this->session->set_flashdata('flashdata', 'Insufficient data supplied.<br/>Please contact the System Administrator.');
            redirect(base_url().$this->module_link);
        }

        $response->emp = $this->system->get_employee_worksched(  $this->input->post('employee_id'), date('Y-m-d'));        
        if( $return ){
           return $response;
        }else{
            $data['json'] = $response;
            $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
        }
    }
}