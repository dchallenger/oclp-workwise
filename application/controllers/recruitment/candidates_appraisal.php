<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Candidates_appraisal extends MY_Controller {

    private $_candidate_status, 
            $_screener_criteria, 
            $_final_criteria,
            $_ias_raw_score,
            $_ias_percentile,
            $_position_hierarchy = array();
    
    function __construct() {
        parent::__construct();

        //set module variable values
        $this->grid_grouping = "";
        $this->related_table = array(); //table => field format

        $this->listview_title = 'Candidate Appraisal';
        $this->listview_description = 'Lists all';
        $this->jqgrid_title = "List";
        $this->detailview_title = 'Info';
        $this->detailview_description = 'Candidate Appraisal';
        $this->editview_title = 'Interviewer\'s Appraisal Sheet';
        $this->editview_description = 'Candidate Appraisal';
                
        $this->_screener_criteria = array ('screener_communication_skills', 'screener_computer_knowledge', 'screener_leadership_traits', 'screener_professional_outlook', 'screener_work_experience');      
        
        $this->_final_criteria = array ('final_communication_skills', 'final_computer_knowledge', 'final_leadership_traits', 'final_professional_outlook', 'final_work_experience');   

        $this->_ias_raw_score = array ('exam_var_raw_score', 'exam_fvas_raw_score', 'exam_aispuvc_raw_score', 'exam_majca_raw_score');      

        $this->_ias_percentile = array ('exam_var_percentile', 'exam_fvas_percentile', 'exam_aispuvc_percentile', 'exam_majca_percentile');      

        $this->load->helper('recruitment');
        $statuses = get_candidate_statuses();

        foreach ($statuses as $index => $status) {
            if ($status['default'] == 1) {
                $this->_default_status_id = $status['candidate_status_id'];
            }

            if ($status['hired_flag'] == 1) {
                $this->_hired_status_id = $status['candidate_status_id'];
                unset($statuses[$index]);
            }

            if ($status['rejected_flag'] == 1) {
                $this->_rejected_status_id = $status['candidate_status_id'];
                unset($statuses[$index]);
            }

            if ($status['interview_flag'] == 1) {
                $this->_interview_status_id = $status['candidate_status_id'];
            }

            if ($status['evaluation_flag'] == 1) {
                $this->_evaluation_status_id = $status['candidate_status_id'];
            }            

            if ($status['joboffer_flag'] == 1) {
                $this->_joboffer_status_id = $status['candidate_status_id'];
            }               
        }

        $data['module_filters'] = get_candidate_filters($statuses);
        $this->load->vars($data);        
    }

    // START - default module functions
    // default jqgrid controller method
    function index() {
        // Redirect to candidates because there would be no listing for appraisals. ?
        redirect ('recruitment/candidates');
    }

    function detail() {
        parent::detail();

        //additional module detail routine here
        $data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/js/detailview.js"></script>';

        //other views to load
        $data['views'] = array();

        $data['content'] = 'detailview';

        $record_id = $this->input->post('record_id');

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
        $this->db->where('candidate_id', $this->input->post('candidate_id'));
        $result = $this->db->get($this->module_table);

        if (!is_null($result) && $result->num_rows() > 0) {
            $row = $result->row_array();
            $_POST['record_id'] = $record_id = $row['appraisal_id'];
				} elseif (($this->is_admin || $this->is_superadmin || $this->is_recruitment()) && $this->input->post('candidate_id')) {
            // Create a new appraisal record if there is none for this candidate.
            $data['candidate_id'] = $this->input->post('candidate_id');
            $data['appraisal_date'] = date('Y-m-d');            

            $this->db->insert($this->module_table, $data);
            $record_id = $this->db->insert_id();
            $_POST['record_id'] = $record_id;
        } else {
            $this->session->set_flashdata('flashdata', 'You dont have sufficient privilege to start the appraisal! Please contact the System Administrator.');
            redirect(site_url('recruitment/candidates'));            
        }
        
        if ($this->user_access[$this->module_id]['edit'] == 1) {            
            parent::edit();

            //additional module edit routine here
            $data['show_wizard_control'] = false;
            $data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/js/editview.js"></script>';

            if (!empty($this->module_wizard_form) && $this->input->post('record_id') == '-1') {
                $data['show_wizard_control'] = true;
            }

            $data['content'] = 'editview';

            if ($this->config->item('client_no') == 1){
                $data['buttons'] = 'recruitment/candidates/appraisal/edit-buttons-ias';
            }
            elseif ($this->config->item('client_no') == 2){
                $data['buttons'] = 'recruitment/candidates/appraisal/edit-buttons';
            }

            //other views to load
            $data['views'] = array();

            if ($this->config->item('client_no') == 1){
                $data['views_outside_record_form'] = array('recruitment/candidates/appraisal/editview-ias');
            }
            elseif ($this->config->item('client_no') == 2){
                $data['views_outside_record_form'] = array('recruitment/candidates/appraisal/editview');
            }

            // Load candidate details.
            $candidate = $this->_get_candidate( $record_id );
						
            if ($candidate) {
                $data = array_merge($data, $candidate->row_array());
            }            

            if ($this->config->item("client_no") == 2){
                if ($this->_interview_status_id == $data['candidate_status_id']) {
                    $this->db->where('position_id', $data['mrf_pos_id']);
                    $mrf_pos = $this->db->get('user_position')->row();
                    
                    $this->db->where('position_id', $mrf_pos->position_id);
                    $this->db->where('deleted', 0);
                    $this->db->limit(1);

                    $position = $this->db->get('user_position');

                    $position_hierarchy = $this->hdicore->get_approvers($position->row()->position_id, $this->module_id);

                    foreach ($position_hierarchy['admins'] as $user_position) {
                        $data['position_hierarchy'][$user_position['position']][$user_position['user_id']] = $user_position['firstname'] . ' ' . $user_position['lastname'];
                    }

                }
            }
            else{
                $this->db->where('position_id', $data['mrf_pos_id']);
                $mrf_pos = $this->db->get('user_position')->row();
                
                $this->db->where('position_id', $mrf_pos->position_id);
                $this->db->where('deleted', 0);
                $this->db->limit(1);

                $position = $this->db->get('user_position');

                $position_hierarchy = $this->hdicore->get_approvers($position->row()->position_id, $this->module_id);

                foreach ($position_hierarchy['admins'] as $user_position) {
                    $data['position_hierarchy'][$user_position['position']][$user_position['user_id']] = $user_position['firstname'] . ' ' . $user_position['lastname'];
                }                
            }

            $this->db->where('deleted', 0);

            foreach ($this->db->get('user_position')->result() as $position) {
                $data['positions'][$position->position_id] = $position->position;
            }

            $data['min_score'] = $this->config->item("MIN_APPRAISAL_SCORE");

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
        } else {
            $this->session->set_flashdata('flashdata', 'You dont have sufficient privilege to execute the edit action! Please contact the System Administrator.');
            redirect(base_url() . $this->module_link);
        }
    }

    function ajax_save() { 
        parent::ajax_save();    

        $screener_total = 0;
        $final_total = 0;        

        if ($this->config->item("client_no") == 2){
            if ($this->input->post('screener_id')) {            
                foreach ($this->_screener_criteria as $criteria) {
                    $data['screener_total'] = $screener_total += $this->input->post($criteria);
                }            
            }

            if ($this->input->post('final_id')) {            
                foreach ($this->_final_criteria as $criteria) {
                    $data['final_total'] = $final_total += $this->input->post($criteria);
                }        
            }

            if (count($data) > 0) {                
                $this->db->where($this->key_field, $this->key_field_val);
                $this->db->update($this->module_table, $data);
            }        
            
            $this->db->where($this->key_field, $this->key_field_val);
            $this->db->join(
                'recruitment_manpower_candidate', 
                'recruitment_manpower_candidate.candidate_id = recruitment_candidates_appraisal.candidate_id'
                );

            $candidate = $this->db->get($this->module_table)->row();

            if ($candidate->screener_total >= $this->config->item("MIN_APPRAISAL_SCORE") 
                && ($candidate->candidate_status_id == $this->_interview_status_id
                    || $candidate->candidate_status_id == $this->_evaluation_status_id
                    || $candidate->candidate_status_id == $this->_joboffer_status_id
                    )
                ) {            
                
                $status = $this->_evaluation_status_id;

                if ($candidate->final_total >= $this->config->item("MIN_APPRAISAL_SCORE") 
                    && ($candidate->candidate_status_id == $this->_evaluation_status_id
                        || $candidate->candidate_status_id == $this->_joboffer_status_id
                        )
                    ) {
                    $status = $this->_joboffer_status_id;
                } elseif ($candidate->final_total < $this->config->item("MIN_APPRAISAL_SCORE") && $this->input->post('final_id')){
                    $status = $this->_rejected_status_id;    
                }
            } else {
                $status = $this->_rejected_status_id;
            }
            
            if ($this->input->post('final_interviewer_id')) {   
                $candidate_data['final_interviewer_id'] = $this->input->post('final_interviewer_id');
                $candidate_data['final_interview_date'] = date('Y-m-d H:i:s', strtotime($this->input->post('final_datetime')));
            }

            $candidate_data['candidate_status_id'] = $status;

            $this->db->where('candidate_id', $candidate->candidate_id);
            $this->db->update('recruitment_manpower_candidate', $candidate_data);

            if ($status == $this->_rejected_status_id) {
                $this->db->where('applicant_id', $candidate->applicant_id);
                $this->db->update('recruitment_applicant', array('application_status_id' => 7));

                $this->db->update('recruitment_applicant_application', array( 'status' => 7 ), array( 'applicant_id' => $candidate->applicant_id, 'mrf_id' => $candidate->mrf_id, 'lstatus' => 0 ));
            }
        }

        if ($this->config->item("client_no") == 1){
            
            $this->db->where($this->key_field, $this->key_field_val);
            $this->db->join(
                'recruitment_manpower_candidate', 
                'recruitment_manpower_candidate.candidate_id = recruitment_candidates_appraisal.candidate_id'
                );

            $candidate = $this->db->get($this->module_table)->row();

            if ($candidate->candidate_status_id == 2){
                $raw_score = 0;
                $raw_score_total = 0;
                $percentile_score = 0;
                $percentile_score_total = 0;

                foreach ($this->_ias_raw_score as $raw_scode) {
                    $raw_score += $this->input->post($raw_scode);
                } 
                
                $raw_score_total = $raw_score / count($this->_ias_raw_score);

                foreach ($this->_ias_percentile as $percentile) {
                    $percentile_score += $this->input->post($percentile);
                }             

                $percentile_score_total = $percentile_score / count($this->_ias_percentile);

                $interview_action_sheet = array(
                        "appraisal_id"=>$this->key_field_val,
                        "exam_var_raw_score"=>$this->input->post("exam_var_raw_score"),
                        "exam_var_percentile"=>$this->input->post("exam_var_percentile"),
                        "exam_var_remarks"=>$this->input->post("exam_var_remarks"),
                        "exam_fvas_raw_score"=>$this->input->post("exam_fvas_raw_score"),
                        "exam_fvas_percentile"=>$this->input->post("exam_fvas_percentile"),
                        "exam_fvas_remarks"=>$this->input->post("exam_fvas_remarks"),
                        "exam_aispuvc_raw_score"=>$this->input->post("exam_aispuvc_raw_score"),
                        "exam_aispuvc_percentile"=>$this->input->post("exam_aispuvc_percentile"),
                        "exam_aispuvc_remarks"=>$this->input->post("exam_aispuvc_remarks"),
                        "exam_majca_raw_score"=>$this->input->post("exam_majca_raw_score"),
                        "exam_majca_percentile"=>$this->input->post("exam_majca_percentile"),
                        "exam_majca_remarks"=>$this->input->post("exam_majca_remarks"),
                        "exam_raw_score_total"=>$raw_score_total,
                        "exam_percentile_total"=>$percentile_score_total,
                        "recommendation"=>$this->input->post("recommendation")
                    );

                $this->db->delete('recruitment_candidates_appraisal_exams', array('appraisal_id' => $this->key_field_val));
                $this->db->insert('recruitment_candidates_appraisal_exams', $interview_action_sheet);

                if ($this->input->post('screening_datetime')){
                    $data['screening_datetime'] = date('Y-m-d H:i:s', strtotime($this->input->post('screening_datetime')));
                }

                if ($this->input->post('interviewer_id')){
                    $data['screener_id'] = $this->input->post('interviewer_id');
                }        

                if (count($data) > 0) {                
                    $this->db->where($this->key_field, $this->key_field_val);
                    $this->db->update($this->module_table, $data);         
                }        

                $this->db->where('recruitment_candidates_appraisal.'.$this->key_field, $this->key_field_val);
                $this->db->join(
                    'recruitment_manpower_candidate', 
                    'recruitment_manpower_candidate.candidate_id = recruitment_candidates_appraisal.candidate_id'
                    );
                $this->db->join(
                    'recruitment_candidates_appraisal_exams', 
                    'recruitment_candidates_appraisal_exams.appraisal_id = recruitment_candidates_appraisal.appraisal_id'
                    );            

                $candidate = $this->db->get($this->module_table)->row();

                if ($candidate->exam_percentile_total >= $this->config->item("MIN_APPRAISAL_SCORE") 
                    && ($candidate->candidate_status_id == $this->_interview_status_id
                        || $candidate->candidate_status_id == $this->_evaluation_status_id
                        || $candidate->candidate_status_id == $this->_joboffer_status_id
                        )
                    ) {            
                    
                    $status = $this->_evaluation_status_id;

                    if ($candidate->exam_percentile_total >= $this->config->item("MIN_APPRAISAL_SCORE") 
                        && ($candidate->candidate_status_id == $this->_evaluation_status_id
                            || $candidate->candidate_status_id == $this->_joboffer_status_id
                            )
                        ) {
                        $status = $this->_joboffer_status_id;
                    } elseif ($candidate->exam_percentile_total < $this->config->item("MIN_APPRAISAL_SCORE") && $this->input->post('final_id')){
                        $status = $this->_rejected_status_id;    
                    }
                } else {
                    $status = $this->_rejected_status_id;
                }

                if ($this->input->post('final_interviewer_id')) {   
                    $candidate_data['final_interviewer_id'] = $this->input->post('final_interviewer_id');
                    $candidate_data['final_interview_date'] = date('Y-m-d H:i:s', strtotime($this->input->post('final_datetime')));
                }

                $candidate_data['candidate_status_id'] = $status;

                $this->db->where('candidate_id', $candidate->candidate_id);
                $this->db->update('recruitment_manpower_candidate', $candidate_data);

                if ($status == $this->_rejected_status_id) {
                    $this->db->where('applicant_id', $candidate->applicant_id);
                    $this->db->update('recruitment_applicant', array('application_status_id' => 7));

                    $this->db->update('recruitment_applicant_application', array( 'status' => 7 ), array( 'applicant_id' => $candidate->applicant_id, 'mrf_id' => $candidate->mrf_id, 'lstatus' => 0 ));
                }                   
            }
            elseif ($candidate->candidate_status_id == 3) {
                if ($this->input->post('screening_datetime')){
                    $data['final_datetime'] = date('Y-m-d H:i:s', strtotime($this->input->post('screening_datetime')));
                }

                if ($this->input->post('interviewer_id')){
                    $data['final_final_interviewer_id'] = $this->input->post('interviewer_id');
                }        

                if (count($data) > 0) {                
                    $this->db->where($this->key_field, $this->key_field_val);
                    $this->db->update($this->module_table, $data);         
                }        

                $interview_comments = array(
                        "appraisal_id"=>$this->key_field_val,
                        "strength"=>$this->input->post("strength"),
                        "areas_improvement"=>$this->input->post("areas_improvement"),
                        "job_fit"=>$this->input->post("job_fit")
                    );

                $this->db->delete('recruitment_candidates_appraisal_comments', array('appraisal_id' => $this->key_field_val));
                $this->db->insert('recruitment_candidates_appraisal_comments', $interview_comments);

                $this->db->where('recruitment_candidates_appraisal.'.$this->key_field, $this->key_field_val);
                $this->db->join(
                    'recruitment_manpower_candidate', 
                    'recruitment_manpower_candidate.candidate_id = recruitment_candidates_appraisal.candidate_id'
                    );
                $this->db->join(
                    'recruitment_candidates_appraisal_exams', 
                    'recruitment_candidates_appraisal_exams.appraisal_id = recruitment_candidates_appraisal.appraisal_id'
                    );            

                $candidate = $this->db->get($this->module_table)->row();

                if ($candidate->exam_percentile_total >= $this->config->item("MIN_APPRAISAL_SCORE") 
                    && ($candidate->candidate_status_id == $this->_interview_status_id
                        || $candidate->candidate_status_id == $this->_evaluation_status_id
                        || $candidate->candidate_status_id == $this->_joboffer_status_id
                        )
                    ) {            
                    
                    $status = $this->_evaluation_status_id;

                    if ($candidate->exam_percentile_total >= $this->config->item("MIN_APPRAISAL_SCORE") 
                        && ($candidate->candidate_status_id == $this->_evaluation_status_id
                            || $candidate->candidate_status_id == $this->_joboffer_status_id
                            )
                        ) {
                        $status = $this->_joboffer_status_id;
                    } elseif ($candidate->exam_percentile_total < $this->config->item("MIN_APPRAISAL_SCORE") && $this->input->post('final_id')){
                        $status = $this->_rejected_status_id;    
                    }
                } else {
                    $status = $this->_rejected_status_id;
                }

                $candidate_data['candidate_status_id'] = $status;

                $this->db->where('candidate_id', $candidate->candidate_id);
                $this->db->update('recruitment_manpower_candidate', $candidate_data);

                if ($status == $this->_rejected_status_id) {
                    $this->db->where('applicant_id', $candidate->applicant_id);
                    $this->db->update('recruitment_applicant', array('application_status_id' => 7));

                    $this->db->update('recruitment_applicant_application', array( 'status' => 7 ), array( 'applicant_id' => $candidate->applicant_id, 'mrf_id' => $candidate->mrf_id, 'lstatus' => 0 ));
                }   
            }
        }
    }

	function after_ajax_save(){
        $response = parent::get_message();      
        $response->page_refresh = "true";

        parent::set_message($response);
        parent::after_ajax_save();
	}

    function delete() {
        parent::delete();
    }

    // END - default module functions
    // START custom module funtions

    /**
     * Override. Control which fieldgroups to show depending on interviewer.
     * @param  integer $record_id
     * @param  boolean $quick_edit_flag 
     * @return array
     */
    function _record_detail( $record_id = 0, $quick_edit_flag = false ) {
        $fieldgroups = parent::_record_detail($record_id, $quick_edit_flag);

        if ($fieldgroups) {
            $this->db->select('rmc.final_interviewer_id');
            $this->db->where('candidate_id', $this->input->post('candidate_id'));
            $this->db->from('recruitment_manpower_candidate rmc');
            $this->db->join('recruitment_manpower rm', 'rm.request_id = rmc.mrf_id');

            $result = $this->db->get()->row();

            if ($result->final_interviewer_id == $this->userinfo['user_id']) {
                unset($fieldgroups[0]);
            }
            else if ($this->is_recruitment()) {
                unset($fieldgroups[1]);
            } else if (!$this->is_superadmin) {
                $this->session->set_flashdata('flashdata', 'You dont have sufficient privilege to execute the edit action! Please contact the System Administrator.');
                redirect(base_url());
            }

            return $fieldgroups;
        } else {
            return FALSE;
        }
    }

    function get_appraisal_detail() {
        $this->db->where('candidate_id', $this->input->post('record_id'));
        $this->db->where('deleted', 0);

        $result = $this->db->get($this->module_table);

        if ($result->num_rows() == 0) {
            $response = 'Nothing to display';
        } else {
            $this->load->model('uitype_detail');

            $record_id = $result->row()->{$this->key_field};
            $fieldgroups = parent::_record_detail($record_id, false);

            if ($this->input->post('type') == 'initial') {
                unset($fieldgroups[1]);
            } else {
                unset($fieldgroups[0]);
            }

            $data['fieldgroups'] = $fieldgroups;

            $response = $this->load->view('recruitment/candidates/appraisal/detailview_boxy', $data, false);            
        }

        if (IS_AJAX) {
            $this->load->view('template/ajax', $response);
        }
    }

    private function _get_candidate($record_id) {
        $this->db->where($this->key_field, $record_id);
        
        $this->db->select('recruitment_applicant.*, 
                            user_position.position,
                            recruitment_manpower.position_id as mrf_pos_id,
                            recruitment_manpower_candidate.candidate_status_id,
                            CONCAT(rb_name.firstname, " ", rb_name.lastname) as requested_by_name,
                            CONCAT(ab_name.firstname, " ", ab_name.lastname) as approved_by_name',                             
                        false);
        $this->db->join('recruitment_manpower_candidate', 'recruitment_manpower_candidate.candidate_id = ' . $this->module_table . '.candidate_id', 'left');
        $this->db->join('recruitment_applicant', 'recruitment_applicant.applicant_id = recruitment_manpower_candidate.applicant_id', 'left');
        $this->db->join('recruitment_manpower', 'recruitment_manpower.request_id = recruitment_manpower_candidate.mrf_id', 'left');
        $this->db->join('user_position', 'user_position.position_id = recruitment_manpower.position_id', 'left');
        $this->db->join('user as rb_name', 'recruitment_manpower.requested_by = rb_name.user_id', 'left');
        $this->db->join('user as ab_name', 'recruitment_manpower.approved_by = ab_name.user_id', 'left');

        $result = $this->db->get($this->module_table);

        if (!is_null($result) && $result->num_rows() > 0) {
            return $result;
        } else {
            return false;
        }
    }

    function endorse_candidate() {        
        $record_id = $this->input->post('record_id');

        if ($this->input->post('hash') != md5($this->session->userdata('session_id') . $record_id)) {
            $this->session->set_flashdata('flashdata', 'User verification failed.');
            redirect(site_url('recruitment/candidates'));            
        }

        $this->db->where($this->key_field, $record_id);
        $this->db->join('recruitment_manpower_candidate', 'recruitment_manpower_candidate.candidate_id = recruitment_candidates_appraisal.candidate_id');
        $this->db->join('recruitment_manpower', 'recruitment_manpower.request_id = recruitment_manpower_candidate.mrf_id');
        $this->db->select('recruitment_manpower.requested_by, candidate_status_id, recruitment_manpower_candidate.candidate_id, recruitment_manpower_candidate.final_interviewer_id, recruitment_manpower_candidate.mrf_id');

        $result = $this->db->get($this->module_table);

        if ($result->num_rows() > 0) {
            $row = $result->row();

            if ($this->is_recruitment() || $this->is_admin || $this->userinfo['user_id'] == $row->final_interviewer_id) {
                $data['candidate_status_id'] = $this->_joboffer_status_id;

                $this->db->where('candidate_id', $row->candidate_id);
                
                if ($this->db->update('recruitment_manpower_candidate', $data)) {
                    $flashdata = 'Candidate endorsed for job offer';
                    
                    $this->session->set_flashdata('msg_type', 'success');
                }
            } else {                
                $flashdata = 'Access denied.';
            }
        } else {            
            $flashdata = 'Invalid candidate ID specified.';            
        }

        $this->session->set_flashdata('flashdata', $flashdata);
        
        redirect(site_url('recruitment/candidates/index/'.$row->mrf_id));
    }    

    function active_file() {
        if (!IS_AJAX) {
            show_404();
        }

        $record_id = $this->input->post('record_id');
                
        $this->db->where($this->key_field, $record_id);
        $record = $this->db->get($this->module_table)->row();

        $this->db->where('candidate_id', $record->candidate_id);
        $candidate = $this->db->get('recruitment_manpower_candidate')->row();

        $this->db->where('applicant_id', $candidate->applicant_id);

        $applicant_update = array('application_status_id' => 5);
        if ($this->input->post('af_pos_id') > 0) {
            $applicant_update['af_pos_id'] = $this->input->post('af_pos_id');
        }
        $this->db->update('recruitment_applicant', $applicant_update);

        $this->db->update('recruitment_applicant_application', array('lstatus' => 1 ), array('applicant_id' => $candidate->applicant_id));

        $data = array(
            'applicant_id' => $candidate->applicant_id,
            'position_applied' => $this->input->post('af_pos_id'),
            'applied_date' => date('Y-m-d H:i:s'),
            'status' => 5,
            'mrf_id' => 0
        );

        //save aaplication
        $this->db->insert('recruitment_applicant_application',$data);

        $this->db->where('candidate_id', $record->candidate_id);
        $this->db->update('recruitment_manpower_candidate', array('candidate_status_id' => 11, 'deleted' => 1));
    }
}

/* End of file */
/* Location: system/application */