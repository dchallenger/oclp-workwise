<?php
require_once(APPPATH . 'controllers/includes/Preemployment_Controller.php');

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Preemployment_checklist extends Preemployment_Controller {
    
    protected $_template_id = 'checklist';
    
    function __construct() {
        parent::__construct();
    }    

    protected function after_ajax_save()
    {
    	
    	// $checklist['checklist_id'] 		= $this->key_field_val;
    	$checklist['company_forms'] = json_encode($this->input->post('company'));
    	$checklist['document_forms'] = json_encode($this->input->post('documents'));
    	$checklist['government_forms'] = json_encode($this->input->post('government'));

		$this->db->where($this->key_field, $this->key_field_val);
        $this->db->update($this->module_table, $checklist);  	
    	parent::after_ajax_save();

    }

    protected function _get_vars($record_id) {
        // Add applicant and job detail to vars.
        $this->db->select($this->module_table . '.' . $this->key_field
                . ', '. $this->module_table . '.company_forms, '
                . $this->module_table . '.government_forms, '
                . $this->module_table . '.document_forms, , '
                . ', recruitment_preemployment.preemployment_id'
                . ', CONCAT(t0.firstname, " ", t0.lastname) as applicant_name'
                . ', CONCAT(rb.firstname, " ", rb.lastname) as requested_by'
                . ', department, company, user_company.company_id, user_company.logo as company_logo, date_needed'
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

        // (Fieldgroups)
        $record_details = $this->_record_detail($record_id);

        if ($record_details && count($record_details) > 0) {
            foreach ($record_details as $fieldgroup) {
                if (count($fieldgroup['fields']) > 0) {
                    foreach ($fieldgroup['fields'] as $field) {
                        if (!$field['visible']) {
                            continue;
                        }

                        $value = $this->uitype_detail->getFieldValue($field);

                        if ($field['uitype_id'] == 30) {
                            if ($value == 'Yes') {
                                $value = 'recruitment/check.jpg';
                            } else {
                                $value = 'recruitment/uncheck.jpg';
                            }
                        }

                        if ($field['uitype_id'] == 3) {
                               
                        }

                        if (is_null($value) || $value == '&nbsp;') {
                            $value = '';
                        }

                        $vars[$field['column']] = $value;
                    }
                }
            }
        }


        $column_fields = $this->get_fields();
        $checklist = $column_fields['checklist'];

        $forms['company'] = json_decode($vars['company_forms'], true);
        $forms['documents'] = json_decode($vars['document_forms'], true);
        $forms['government'] = json_decode($vars['government_forms'], true);


        $vars['table'] = '<table cellpadding="10">';
        foreach ($checklist as $key => $field_val):
            $vars['table'] .= '<tr><th align="left" style=" padding-bottom: 10px;"><strong>'.ucfirst($key).'</strong></th></tr>';

            foreach ($checklist[$key] as $val => $value):
                $check = $forms[$key]['check_box'];
                $img = (in_array($val, $check)) ? "recruitment/check.jpg" : "recruitment/uncheck.jpg";
                $vars['table'] .= '<tr><td>'.
                                    '<table><tr>'.
                                        '<td style="width:5%"><img src="uploads/'.$img.'"/></td>'.
                                        '<td style="border-bottom: 1px dotted #000;width:50%;padding-bottom: 10px;">'.$value->description.'</td>'.
                                        '<td style="width:5%">&nbsp;</td>'.
                                        '<td style="width:40%">'.$forms[$key]['remarks'][$val].'</td>'.
                                    '</tr></table>'.
                                  '</td></tr>';
            endforeach;

        endforeach;


        $vars['table'] .= '</table>';

        return $vars;
    }


}

/* End of file */
/* Location: system/application */