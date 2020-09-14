<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Scheduler extends MY_Controller
{
    function __construct()
    {
        parent::__construct();

        //set module variable values
        $this->grid_grouping = "";
        $this->related_table = array(); //table => field format

        $this->listview_title = 'Scheduler';
        $this->listview_description = 'Lists all candidates';
        $this->jqgrid_title = "Scheduler";
        $this->detailview_title = 'Scheduler';
        $this->detailview_description = 'This page shows detailed information about a candidate list';
        $this->editview_title = 'Scheduler Add/Edit';
        $this->editview_description = 'This page allows saving/editing information about a candidate';      
    }

    // START - default module functions
    // default jqgrid controller method
    function index()
    {
        $data['scripts'][] = jqgrid_listview(); // load jqgrid js and default grid js
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
        if ($this->input->post('from_preemployment') == 1){
            if ($this->input->post('applicant_id') != 0){
                $result_app_emp = $this->db->get_where('recruitment_applicant',array("applicant_id"=>$this->input->post('applicant_id')));
            }
            elseif ($this->input->post('employee_id') != 0){
                $result_app_emp = $this->db->get_where('user',array("user_id"=>$this->input->post('employee_id')));
            }

            $this->db->join($this->module_table,$this->module_table.'.candidate_id = recruitment_manpower_candidate.candidate_id');
            $this->db->join('recruitment_preemployment','recruitment_preemployment.candidate_id = recruitment_manpower_candidate.candidate_id');
            $this->db->where('recruitment_preemployment.preemployment_id',$this->input->post('candidate_id'));
            $this->db->where('schedule_type_id',$this->input->post('schedule_type'));
            $result = $this->db->get('recruitment_manpower_candidate');

            if (!$result || $result->num_rows() < 1){
                $_POST['record_id'] = -1;
            }
            else{
                $row = $result->row();
                $_POST['record_id'] = $row->scheduler_id;  
                $data['email_sent'] = $row->email_sent;
            }

            if ($result_app_emp && $result_app_emp->num_rows() > 0){
                $row = $result_app_emp->row();
                $data['candidate_id'] = $this->input->post('candidate_id');
                $data['candidate_name'] = $row->firstname .' '. $row->lastname;
                $data['schedule_type'] = $this->input->post('schedule_type');
                $data['schedule_name'] = ($this->input->post('schedule_type') == 1 ? "Orientation" : "Medical");
                $data['schedule_name'] = ($this->input->post('schedule_type') == 1 ? "Orientation" : "Medical");
                
            }
            $data['buttons'] = 'recruitment/scheduler/editview-buttons';            
        }

        if($this->user_access[$this->module_id]['edit'] == 1){
            parent::edit();

            //additional module edit routine here
            $data['show_wizard_control'] = false;
            $data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/js/editview.js"></script>';
            if( !empty($this->module_wizard_form) && $this->input->post('record_id') == '-1' ){
                $data['show_wizard_control'] = true;
                $data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/js/editview-wizard-form.js"></script>';
            }

            if ($this->input->post('from_preemployment') == 1){
                $data['content'] = '/recruitment/scheduler/editview';
            }
            else{
                $data['content'] = 'editview';
            }

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

        // $_POST['candidate_id'] = $this->input->post('candidate_id_fixed');        
        $candidate = $this->db->get_where('recruitment_preemployment', array('preemployment_id' => $this->input->post('candidate_id_fixed')))->row();        
        $_POST['candidate_id'] = $candidate->candidate_id;

        parent::ajax_save();

        if ($this->input->post('clinic_name') == 'n/a'){
            $this->db->where('scheduler_id',$this->key_field_val);
            $this->db->update($this->module_table,array('clinic_name' => ""));
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

    function send_email() {
        
        $recepients = array();

        $this->db->join('recruitment_manpower_candidate', $this->module_table.'.candidate_id=recruitment_manpower_candidate.candidate_id');
        $result = $this->db->get_where($this->module_table , array('scheduler_id'=>$this->input->post('record_id')));

        $this->load->model('template');
        $template = $this->template->get_module_template('', 'preemployment_orientation');
        if ($result && $result->num_rows() > 0){
            $request = $result->row_array();

            $applicant = $this->db->get_where('recruitment_applicant', array('applicant_id' => $request['applicant_id']))->row_array();

            $request['lastname'] = $applicant['lastname'];
            $recepients[] = $applicant['email'];

            $this->db->join('user_position', 'user_position.position_id=recruitment_manpower.position_id');
            $this->db->join('user_rank', 'user_rank.job_rank_id=recruitment_manpower.job_rank_id');
            $this->db->join('user_company_department', 'user_company_department.department_id=recruitment_manpower.department_id');
            $this->db->join('user_company_division', 'user_company_division.division_id=recruitment_manpower.division_id');
            $mrf = $this->db->get_where('recruitment_manpower', array('request_id' => $request['mrf_id'] ))->row();

            $request['start_date'] = date('F d, Y' , strtotime($request['schedule_date']));
            $request['date'] = date('F d, Y' , strtotime($request['schedule_date']));
            $request['time'] = date('g:i A' , strtotime($request['schedule_date']));
            $request['position'] = $mrf->position;
            $request['department'] = $mrf->department;
            $request['division'] = $mrf->division;
            $request['level'] = $mrf->job_rank;
            $request['venue'] = '';
           
            $response->msg_type = 'success';
            $response->msg = 'Scheduler Sent.';

        }else{
            $response->type = 'notice';
            $response->msg = 'Sending failed.';
            $response->record_id = $this->input->post('record_id');
        }

        $this->load->model('template');
        $mail_config = $this->hdicore->_get_config('outgoing_mailserver');
        if ($mail_config) {
            
            // Load the template. 

            $message = $this->template->prep_message($template['body'], $request);
            $this->template->queue(implode(',', $recepients), '', $template['subject'], $message);

            $this->db->where('scheduler_id', $request['scheduler_id']);
            $this->db->update($this->module_table , array('email_sent'=>1));

            if ($request['result']) {
                $this->db->where('candidate_id', $request['candidate_id']);
                $this->db->update('recruitment_manpower_candidate' , array('candidate_status_id'=>12));
            }else{
                $this->db->where('candidate_id', $request['candidate_id']);
                $this->db->update('recruitment_manpower_candidate' , array('candidate_status_id'=>22)); 

                $this->db->where('candidate_id', $request['candidate_id']);
                $this->db->update('recruitment_candidate_job_offer', array('job_offer_status_id' => 4));
            }
        }

        $data['json'] = $response;          
        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
    }

/*    function _default_grid_actions( $module_link = "",  $container = "", $row = array())
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
                
        if ($this->user_access[$this->module_id]['print'] && method_exists($this, 'print_record')) {
            $actions .= '<a class="icon-button icon-16-print print-record" tooltip="Print" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
        }        
        
        if ($this->user_access[$this->module_id]['delete']) {
            $actions .= '<a class="icon-button icon-16-delete delete-single" tooltip="Delete" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
        }

        $actions .= '<a class="icon-button icon-16-document-stack" applicant_id="'.$row['scheduler_id'].'" module_link="' . $module_link . '" tooltip="Applicant Details" href="javascript:void(0)"></a>';        

        $actions .= '</span>';

        return $actions;
    }*/

    // END custom module funtions

}

/* End of file */
/* Location: system/application */