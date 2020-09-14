<?php
require_once(APPPATH . 'controllers/includes/Preemployment_Controller.php');

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Preemployment_onboarding extends Preemployment_Controller {
    
    protected $_template_id = 'new_employee_onboarding';
    
    function __construct() {
        parent::__construct();
    }    

    protected function after_ajax_save()
    {

    	$onboarding['arrival'] = json_encode($this->input->post('arrival'));
    	$onboarding['fourth_month'] = json_encode($this->input->post('fourth_month'));
    	$onboarding['fifth_month'] = json_encode($this->input->post('fifth_month'));
    	$onboarding['regularization'] = json_encode($this->input->post('regularization'));
    	$onboarding['termination'] = json_encode($this->input->post('termination'));
    	$onboarding['promotion'] = json_encode($this->input->post('promotion'));

		$this->db->where($this->key_field, $this->key_field_val);
        $this->db->update($this->module_table, $onboarding);  	
    	parent::after_ajax_save();

    }
}

/* End of file */
/* Location: system/application */