<?php
require_once(APPPATH . 'controllers/includes/Preemployment_Controller.php');

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Preemployment_background extends Preemployment_Controller {
 
    protected $_template_id = 'background';
    
    function __construct() {
        parent::__construct();
    }    
    
    protected function _get_vars($record_id) {
 
        $this->db->where_in('option_id', array(1,2,3));
        $result = $this->db->get('dropdown_options');
        $options = $result->result_array();
        
        // Add applicant and job detail to vars.
        $this->db->select($this->module_table . '.' . $this->key_field
                . ', recruitment_preemployment.preemployment_id'
                . ', CONCAT(t0.firstname, " ", t0.lastname) as applicant_name'
                . ', CONCAT(rb.firstname, " ", rb.lastname) as requested_by'
                . ', department, company, date_needed'
                . ', CONCAT(cb.firstname, " ", cb.lastname) as completed_by', false);

        $this->db->where($this->key_field, $record_id);
        $this->db->where($this->module_table . '.deleted', 0);
        $this->db->join('recruitment_preemployment', 'recruitment_preemployment.preemployment_id = ' . $this->module_table . '.preemployment_id');
        $this->db->join('recruitment_manpower_candidate mc', 'mc.candidate_id = ' . 'recruitment_preemployment.candidate_id', 'left');
        $this->db->join('recruitment_applicant t0', 't0.applicant_id = mc.applicant_id', 'left');
        $this->db->join('recruitment_manpower', 'recruitment_manpower.request_id = mc.mrf_id', 'left');
        $this->db->join('user_position t1', 't1.position_id = recruitment_manpower.position_id', 'left');
        $this->db->join('user_company_department', 'user_company_department.department_id = recruitment_manpower.department_id', 'left');
        $this->db->join('user_company', 'user_company.company_id = recruitment_manpower.company_id', 'left');
        $this->db->join('user rb', 'rb.user_id = recruitment_manpower.requested_by', 'left');
        $this->db->join('user cb', 'cb.user_id = ' . $this->module_table . '.completed_by', 'left');
        
        $result = $this->db->get($this->module_table);       

        $vars = $result->row_array();

        $record_details = $this->_record_detail($record_id);
        
        if ($record_details && count($record_details) > 0) {
            foreach ($record_details as $fieldgroup) {
                if (count($fieldgroup['fields']) > 0) {
                    foreach ($fieldgroup['fields'] as $field) {                                                                            
                        $value = $this->uitype_detail->getFieldValue($field);                        

                        if ($field['uitype_id'] == 30) {
                            if ($value == 'Yes') {
                                $value = 'recruitment/check.jpg';
                            } else {
                                $value = 'recruitment/uncheck.jpg';
                            }
                        }
                                                                        
                        if ($field['uitype_id'] == 3) {
                            foreach ($options as $option) {
                                if ($value == $option['value']) {
                                    $vars[$field['column']][$option['option_id']]['image'] = 'recruitment/check.jpg';
                                } else {
                                    $vars[$field['column']][$option['option_id']]['image'] = 'recruitment/uncheck.jpg';
                                }
                            }
                        } else {
                            if (is_null($value) || $value == '&nbsp;') {
                                $value = '';
                            }
                            $vars[$field['column']] = $value;
                        }                                                
                    }
                }
            }
        }
        
        return $vars;
    }  

    function print_record(){
        dbug("Waiting format");
        return;
    }  
}

/* End of file */
/* Location: system/application */