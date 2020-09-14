<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

abstract class Preemployment_Controller extends MY_Controller {

    protected $_template_id;

    function __construct() {
        // This class must only be inherited from.
        if (!is_subclass_of($this, 'Preemployment_Controller')) {
            show_404();
        }

        parent::__construct();

        //set module variable values
        $this->grid_grouping = "";
        $this->related_table = array(); //table => field format

        $this->listview_title = $this->module_name;
        $this->listview_description = $this->module_name;
        $this->jqgrid_title = "";
        $this->detailview_title = $this->module_name;
        $this->detailview_description = $this->module_name;
        $this->editview_title = 'Add/Edit ' . $this->module_name;
        $this->editview_description = '';

        $this->load->helper('preemployment');

        $this->load->vars(array('module_filters' => preemployment_filters()));

        // $this->filter = $this->db->dbprefix."recruitment_candidate_status.candidate_status_id = 13";
        // $this->filter = $this->module_table.".applicant_id = 3";    
    }

    // START - default module functions
    // default jqgrid controller method

    function index() {
        $data['scripts'][] = jqgrid_listview(); // load jqgrid js and default grid js
        $data['scripts'][] = '<script type="text/javascript" src="' . site_url('lib/modules/recruitment/preemployment_sub_listview.js') . '"></script>';
        $data['content'] = 'recruitment/preemployment/listview';

        if ($this->session->flashdata('flashdata')) {
            $info['flashdata'] = $this->session->flashdata('flashdata');
            $data['flashdata'] = $this->load->view($this->userinfo['rtheme'] . '/template/flashdata', $info, true);
        }

        //set default columnlist
        $this->_set_listview_query();

        //set grid buttons
        $data['jqg_buttons'] = $this->_default_grid_buttons();

        //set load jqgrid loadComplete callback
        $data['jqgrid_loadComplete'] = "";

        $this->db->where('module_id', $this->module_id);
        $result = $this->db->get('module');

        $module = $result->row();

        // Get children modules and prepare the checklist data.                
        $module_children = $this->hdicore->get_module_child($module->parent_id);

        foreach ($module_children as $checklist) {
            $data['checklists'][] = get_checklist_data($checklist);
        }

        //load variables to env
        $this->load->vars($data);

        //load the final view
        //load header
        $this->load->view($this->userinfo['rtheme'] . '/template/header');
        $this->load->view($this->userinfo['rtheme'] . '/template/header-nav');

        //load page content
        $this->load->view($this->userinfo['rtheme'] . '/template/page-content');

        //load footer
        $this->load->view($this->userinfo['rtheme'] . '/template/footer');
    }

    function detail() {
        parent::detail();

        //additional module detail routine here
        $data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/js/detailview.js"></script>';

        $data['content'] = 'detailview';

        //other views to load
        $data['views'] = array();

        if (!empty($this->module_wizard_form) || $this->input->post('record_id') == '-1') {
            $data['show_wizard_control'] = true;
        }

        //load variables to env
        $this->load->vars($data);

        //load the final view
        //load header
        $this->load->view($this->userinfo['rtheme'] . '/template/header');
        $this->load->view($this->userinfo['rtheme'] . '/template/header-nav');

        //load page content
        $this->load->view($this->userinfo['rtheme'] . '/template/page-content');

        //load footer
        $this->load->view($this->userinfo['rtheme'] . '/template/footer');
    }

    function edit() {
        if ($this->user_access[$this->module_id]['edit'] == 1) {
            $this->load->helper('form');

            parent::edit();

            //additional module edit routine here
            $data['show_wizard_control'] = false;
            $data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/js/editview.js"></script>';
            $data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/modules/recruitment/preemployment.js"></script>';

            if (!empty($this->module_wizard_form) && $this->input->post('record_id') == '-1') {
                $data['show_wizard_control'] = true;
                $data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/js/editview-wizard-form.js"></script>';
            }

            $data['content'] = 'editview';
            $data['buttons'] = 'recruitment/preemployment/template/editview-buttons';

            $this->db->where($this->key_field, $this->input->post('record_id'));
            $this->db->where($this->module_table . '.deleted', 0);
            $this->db->join('recruitment_preemployment', 'recruitment_preemployment.preemployment_id = ' . $this->module_table . '.preemployment_id');
            $this->db->join('recruitment_manpower_candidate mc', 'mc.candidate_id = ' . 'recruitment_preemployment.candidate_id', 'left');
            $this->db->join('recruitment_applicant t0', 't0.applicant_id = mc.applicant_id', 'left');
            $this->db->join('recruitment_candidate_job_offer jo', 'jo.candidate_id = mc.candidate_id', 'left');
            $this->db->join('recruitment_manpower', 'recruitment_manpower.request_id = mc.mrf_id', 'left');
            $this->db->join('user_position t1', 't1.position_id = recruitment_manpower.position_id', 'left');
            $this->db->join('user_company_department', 'user_company_department.department_id = recruitment_manpower.department_id', 'left');
            $this->db->join('user_company', 'user_company.company_id = recruitment_manpower.company_id', 'left');            

            $this->db->select($this->module_table . '.' . '*' 
                    . ','.$this->module_table . '.' . $this->key_field
                    . ',is_internal'
                    . ', recruitment_preemployment.preemployment_id'
                    . ', IF (t0.firstname != "",CONCAT(t0.firstname, " ", t0.lastname),CONCAT(cu.firstname, " ", cu.lastname)) as applicant_name'
                    . ', CONCAT(rb.firstname, " ", rb.lastname) as requested_by'
                    . ', department, company, date_needed, jo.date_from', false);

            $this->db->join('user rb', 'rb.user_id = recruitment_manpower.requested_by');
            $this->db->join('user cu', 'cu.user_id = mc.employee_id','left');

            $result = $this->db->get($this->module_table);

            $data['raw_data'] = array();
            if ($result && $result->num_rows() > 0) {
                $data['raw_data'] = $result->row_array();
            }
            //other views to load
            $data['views'] = array();
            $data['views_outside_record_form'] = array();

            $data['column_fields'] = $this->get_fields();
            //load variables to env
            $this->load->vars($data);

            $vars = $this->load->get_vars();

            foreach ($vars['fieldgroups'] as &$fieldgroup) {
                foreach ($fieldgroup['fields'] as &$field) {                    
                    if ($field['table'] == 'recruitment_preemployment_checklist' 
                        && $field['column'] == 'start_date') {                        
                            $field['value'] = $result->row()->date_from;                        
                            $this->load->reload_vars($vars);                        
                        break;
                    }                   
                }
            }            

            //load the final view
            //load header
            $this->load->view($this->userinfo['rtheme'] . '/template/header');
            $this->load->view($this->userinfo['rtheme'] . '/template/header-nav');

            //load page content
            $this->load->view($this->userinfo['rtheme'] . '/template/page-content');

            //load footer
            $this->load->view($this->userinfo['rtheme'] . '/template/footer');
        } else {
            $this->session->set_flashdata('flashdata', 'You dont have sufficient privilege to execute the edit action! Please contact the System Administrator.');
            redirect(base_url() . $this->module_link);
        }
    }

    function ajax_save() {
        $this->db->trans_start();

        parent::ajax_save();

        $this->db->where($this->key_field, $this->key_field_val);
        $result = $this->db->get($this->module_table);

        $record = $result->row_array();

        if ($record['date_created'] == '0000-00-00 00:00:00') {
            $data['date_created'] = date('Y-m-d H:i:s');
        }

        $data['date_updated'] = date('Y-m-d H:i:s');
        $data['updated_by'] = $this->userinfo['user_id'];
        
        if ($this->input->post('completed') == 1) {
            $data['date_complete'] = date('Y-m-d H:i:s');
            $data['completed'] = 1;
            $data['completed_by'] = $this->userinfo['user_id'];
        }

        $this->db->where($this->key_field, $this->key_field_val);
        $this->db->update($this->module_table, $data);        

        $this->db->trans_complete();
    }

    function delete() {
        parent::delete();
    }

    // END - default module functions
    // START custom module funtions

    // Create separate join for listview because listview is an entirely different query from edit and detail.
    // This module's listview filters data from the parent's table and joins it's module_table.
    function _set_listview_join() {
        $this->db->join($this->module_table . ' mt', 'recruitment_preemployment.preemployment_id = mt.preemployment_id', 'left');
        $this->db->join('recruitment_manpower_candidate rmp', 'recruitment_preemployment.candidate_id = rmp.candidate_id', 'left');
        $this->db->join('recruitment_applicant t0', 't0.applicant_id = rmp.applicant_id', 'left');
        $this->db->where("rmp.candidate_status_id = 13");
        $this->db->where('t0.deleted', 0);

    }

    function _set_left_join() {
        $this->db->join('recruitment_preemployment rp', 'rp.preemployment_id = ' . $this->module_table . '.preemployment_id');
        $this->db->join('recruitment_manpower_candidate', 'rp.candidate_id = recruitment_manpower_candidate.candidate_id');
        $this->db->join('recruitment_applicant t0', 't0.applicant_id = recruitment_manpower_candidate.applicant_id',"left");
        $this->db->join('user t1', 't1.user_id = recruitment_manpower_candidate.employee_id',"left");
    }

    function _default_grid_buttons($module_link = "", $related_field = "", $related_field_value = "", $addtext = "", $deltext = "", $container = "") {
        return '';
    }

    /**
     * Custom listview.
     */
    function listview() {
        $response->msg = "";

        $page = $this->input->post('page');
        $limit = $this->input->post('rows'); // get how many rows we want to have into the grid
        $sidx = $this->input->post('sidx'); // get index row - i.e. user click to sort
        $sord = $this->input->post('sord'); // get the direction
        $related_module = ( $this->input->post('related_module') ? true : false );

        $view_actions = (isset($_POST['view']) && $_POST['view'] == 'detail') ? false : true;

        //set columnlist and select qry
        $this->_set_listview_query('', $view_actions);

        //set Search Qry string
        if ($this->input->post('_search') == "true")
            $search = $this->input->post('searchField') == "all" ? $this->_set_search_all_query() : $this->_set_specific_search_query();
        else
            $search = 1;

        $search .= ' AND '.$this->db->dbprefix.'recruitment_preemployment.preemployment_id NOT IN 
                        (SELECT preemployment_id 
                            FROM ' . $this->db->dbprefix . $this->module_table . ' x
                            WHERE x.completed = 1)';
        /* count query */

        $this->listview_qry = "recruitment_preemployment.preemployment_id as checklist_id, CONCAT( t0.firstname, ' ', t0.lastname ) as t0firstnamelastname";

        //build query
        $this->_set_listview_join();
        $this->db->select($this->listview_qry, false);
        $this->db->from('recruitment_preemployment');
        $this->db->where($this->db->dbprefix.'recruitment_preemployment.deleted = 0 AND ' . $search);

        //get list
        $result = $this->db->get();
        //dbug($this->db->last_query());exit();
        if ($this->db->_error_message() != "") {
            $response->msg = $this->db->_error_message();
            $response->msg_type = "error";
        } else {
            $total_pages = $result->num_rows() > 0 ? ceil($result->num_rows() / $limit) : 0;
            $response->page = $page > $total_pages ? $total_pages : $page;
            $response->total = $total_pages;
            $response->records = $result->num_rows();

            /* record query */
            //build query
            $this->_set_listview_join();
            $this->db->select($this->listview_qry, false);
            $this->db->from('recruitment_preemployment');
            $this->db->where($this->db->dbprefix.'recruitment_preemployment.deleted = 0 AND ' . $search);

            if ($sidx != "") {
                $this->db->order_by($sidx, $sord);
            } else {
                if (is_array($this->default_sort_col)) {
                    $sort = implode(', ', $this->default_sort_col);
                    $this->db->order_by($sort);
                }
            }
            $start = $limit * $page - $limit;
            $this->db->limit($limit, $start);

            $result = $this->db->get();
            //dbug($this->db->last_query());
            //check what column to add if this is a related module
            if ($related_module) {
                foreach ($this->listview_columns as $column) {
                    if ($column['name'] != "action") {
                        $temp = explode('.', $column['name']);
                        if (strpos($this->input->post('column'), ',')) {
                            $column_lists = explode(',', $this->input->post('column'));
                            if (sizeof($temp) > 1 && in_array($temp[1], $column_lists))
                                $column_to_add[] = $column['name'];
                        }
                        else {
                            if (sizeof($temp) > 1 && $temp[1] == $this->input->post('column'))
                                $this->related_module_add_column = $column['name'];
                        }
                    }
                }
                //in case specified related column not in listview columns, default to 1st column
                if (!isset($this->related_module_add_column)) {
                    if (sizeof($column_to_add) > 0)
                        $this->related_module_add_column = implode('~', $column_to_add);
                    else
                        $this->related_module_add_column = $this->listview_columns[0]['name'];
                }
            }

            if ($this->db->_error_message() != "") {
                $response->msg = $this->db->_error_message();
                $response->msg_type = "error";
            } else {
                $response->rows = array();
                if ($result->num_rows() > 0) {
                    $columns_data = $result->field_data();
                    $column_type = array();
                    foreach ($columns_data as $column_data) {
                        $column_type[$column_data->name] = $column_data->type;
                    }
                    $this->load->model('uitype_listview');
                    $ctr = 0;
                    foreach ($result->result_array() as $row) {
                        $cell = array();
                        $cell_ctr = 0;
                        foreach ($this->listview_columns as $column => $detail) {
                            if (preg_match('/\./', $detail['name'])) {
                                $temp = explode('.', $detail['name']);
                                $detail['name'] = $temp[1];
                            }

                            if ($detail['name'] == 'action') {
                                if ($view_actions) {
                                    $cell[$cell_ctr] = ( $related_module ? $this->_default_field_module_link_actions($row[$this->key_field], $this->input->post('container'), $this->input->post('fmlinkctr')) : $this->_default_grid_actions($this->module_link, $this->input->post('container'), $row) );
                                    $cell_ctr++;
                                }
                            } else {
                                if( in_array( $this->listview_fields[$cell_ctr]['uitype_id'], array( 2, 5, 4, 11, 12, 17, 19, 21, 24, 27, 32, 33) ) ){
                                    $this->listview_fields[$cell_ctr]['value'] = $row[$detail['name']];
                                    $cell[$cell_ctr] = $this->uitype_listview->fieldValue($this->listview_fields[$cell_ctr]);
                                } else if (in_array($this->listview_fields[$cell_ctr]['uitype_id'], array(3)) && ( isset($this->listview_fields[$cell_ctr]['other_info']['picklist_type']) && $this->listview_fields[$cell_ctr]['other_info']['picklist_type'] == 'Query' )) {
                                    $cell[$cell_ctr] = "";
                                    foreach ($this->listview_fields[$cell_ctr]['other_info']['picklistvalues'] as $picklist_val) {
                                        if ($row[$detail['name']] == $picklist_val['id'])
                                            $cell[$cell_ctr] = $picklist_val['value'];
                                    }
                                }
                                else {
                                    $cell[$cell_ctr] = (is_numeric($row[$detail['name']]) && ($column_type[$detail['name']] != "253" && $column_type[$detail['name']] != "varchar") ) ? number_format($row[$detail['name']], 2, '.', ',') : $row[$detail['name']];
                                }
                                $cell_ctr++;
                            }
                        }
                        $response->rows[$ctr]['id'] = $row[$this->key_field];
                        $response->rows[$ctr]['cell'] = $cell;
                        $ctr++;
                    }
                }
            }
        }
        $data['json'] = $response;

        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
    }

    function print_record($record_id = 0) {
        if ($this->user_access[$this->module_id]['print'] == 1) {

            // Get from $_POST when the URI is not present.
            if ($record_id == 0) {
                $record_id = $this->input->post('record_id');
            }

            // Validations.
            $exist = $this->_record_exist($record_id);
            if (!$exist->exist) {
                $this->session->set_flashdata('flashdata', 'Record does not exist.');
                redirect(base_url() . $this->module_link);
            }

            if (is_null($this->_template_id)) {
                $this->session->set_flashdata('flashdata', 'No template defined for this module.');
                redirect(base_url() . $this->module_link);
            }

            $this->load->model(array('uitype_detail', 'template'));

            $template = $this->template->get_module_template($this->module_id, $this->_template_id);

            if (!$template) {
                $this->session->set_flashdata('flashdata', 'The defined template does not exist.');
                redirect(base_url() . $this->module_link);
            }

            $this->load->library('pdf');

            $vars = $this->_get_vars($record_id);
            $vars['box'] = 'recruitment/uncheck.jpg';
            $vars['current_date'] = date('F d, Y');
           
            // $logo_2 = get_branding();
    
            // if(!empty($vars['company_logo'])) {
            $company_logo = '<img alt=""  src="./'.$vars['company_logo'].'">';
            // }
            $vars['logo_2'] = '<table style="width:100%"><tr><td>'.$company_logo.'</td></tr></table>';

            $html = $this->template->prep_message($template['body'], $vars, false);

            // Prepare and output the PDF.
            $this->pdf->addPage();
            $this->pdf->SetFontSize(11);
            $this->pdf->SetFont('Calibri');
            $this->pdf->writeHTML($html, true, false, true, false, '');
            $this->pdf->Output(date('Y-m-d').' '.$this->module_name . '.pdf', 'D');

        } else {
            $this->session->set_flashdata('flashdata', 'You dont have sufficient access to the requested module. <span class="red">Please contact the System Administrator.</span>');
            redirect(base_url());
        }
    }

    protected function _get_vars($record_id) {

        // Add applicant and job detail to vars.
        $this->db->select($this->module_table . '.' . $this->key_field
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

        return $vars;
    }

    function get_fields()
    {
        $fields = array();
        /*Pre-employment Checklist*/
        $this->db->order_by('sequence_no', 'ASC');
        $government     = $this->db->get_where('recruitment_preemployement_government_forms', array('deleted'=> 0));
        $this->db->order_by('sequence_no', 'ASC');
        $documents      = $this->db->get_where('recruitment_preemployement_documents_forms', array('deleted'=> 0));
        $this->db->order_by('sequence_no', 'ASC');
        $company        = $this->db->get_where('recruitment_preemployement_company_forms', array('deleted'=> 0));

        $fields['checklist']['company']     = ($company && $company->num_rows() > 0) ? $company->result() : array();
        $fields['checklist']['government']  = ($government && $government->num_rows() > 0) ? $government->result() : array();
        $fields['checklist']['documents']   = ($documents && $documents->num_rows() > 0) ? $documents->result() : array();
        /*Pre-employment Checklist*/

        /*Onboarding Checklist*/
        $this->db->order_by('sequence_no', 'ASC');
        $arrival = $this->db->get_where('recruitment_onboarding_arrival', array('deleted'=> 0));
        $this->db->order_by('sequence_no', 'ASC');
        $fifth_month = $this->db->get_where('recruitment_onboarding_fifth_month', array('deleted'=> 0));
        $this->db->order_by('sequence_no', 'ASC');
        $fourth_month = $this->db->get_where('recruitment_onboarding_fourth_month ', array('deleted'=> 0));
        $this->db->order_by('sequence_no', 'ASC');
        $promotion = $this->db->get_where('recruitment_onboarding_promotion', array('deleted'=> 0));
        $this->db->order_by('sequence_no', 'ASC');
        $regularization = $this->db->get_where('recruitment_onboarding_regularization', array('deleted'=> 0));
        $this->db->order_by('sequence_no', 'ASC');
        $termination = $this->db->get_where('recruitment_onboarding_termination', array('deleted'=> 0));

        $fields['onboarding']['arrival']        = ($arrival && $arrival->num_rows() > 0) ? $arrival->result() : array();
        $fields['onboarding']['fifth_month']    = ($fifth_month && $fifth_month->num_rows() > 0) ? $fifth_month->result() : array();
        $fields['onboarding']['fourth_month']   = ($fourth_month && $fourth_month->num_rows() > 0) ? $fourth_month->result() : array();
        $fields['onboarding']['promotion']      = ($promotion && $promotion->num_rows() > 0) ? $promotion->result() : array();
        $fields['onboarding']['regularization'] = ($regularization && $regularization->num_rows() > 0) ? $regularization->result() : array();
        $fields['onboarding']['termination']    = ($termination && $termination->num_rows() > 0) ? $termination->result() : array();
        /*Onboarding Checklist*/

        return $fields;
    }
}

/* End of file */
/* Location: system/application */