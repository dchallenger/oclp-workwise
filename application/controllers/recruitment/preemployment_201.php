<?php
require_once(APPPATH . 'controllers/includes/Preemployment_Controller.php');

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Preemployment_201 extends Preemployment_Controller {
    
    //protected $_template_id = 12;
    
    function __construct() {
        parent::__construct();
    }

    // Create separate join for listview because listview is an entirely different query from edit and detail.
    // This module's listview filters data from the parent's table and joins it's module_table.
    protected function _set_listview_join() {
        $this->db->join($this->module_table . ' mt', 'recruitment_preemployment.preemployment_id = mt.preemployment_id', 'left');
        $this->db->join('recruitment_manpower_candidate rmp', 'recruitment_preemployment.candidate_id = rmp.candidate_id', 'left');
        $this->db->join('recruitment_applicant t0', 't0.applicant_id = rmp.applicant_id', 'left');
        $this->db->where('t0.deleted', 0);
    }
    
}