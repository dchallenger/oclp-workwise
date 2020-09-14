<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require(APPPATH.'libraries/Formsmain.php');

class Oot extends Formsmain {

    function __construct() {
        parent::__construct(); 

        $this->load->model('forms/oot_model', 'oot');       
    }

    function detail(){
        
        parent::detail();

    }

    function edit() {
        
        if ($this->user_access[$this->module_id]['edit'] == 1) {
            $this->load->helper('form');

            if( $this->input->post('record_id') != -1){
                //check status
                $rec = $this->db->get_where( $this->module_table, array( $this->key_field => $this->input->post('record_id') ) )->row();
                if( $rec->form_status_id != 1 ){
                    $this->session->set_flashdata( 'flashdata', 'Data is locked for editing, please call the Administrator.' );
                    redirect( base_url().$this->module_link.'/detail/'. $this->input->post('record_id') );
                }
            }

            // My_Controller::edit();

            //check if OT extension
            $related_oot_id = $this->input->post('related_id');
            $record_id = $this->input->post('record_id');
            if($related_oot_id > 0){
                // if( !isset($_POST['record_id']) && $this->uri->rsegment(3) ) $_POST['record_id'] = $this->uri->rsegment(3);
        
                if( !$this->input->post( 'record_id' ) ){
                    $this->session->set_flashdata( 'flashdata', 'Insufficient data supplied!<br/>Please contact the System Administrator.' );
                    redirect( base_url().$this->module_link );
                }
                
                if( $this->input->post( 'record_id' ) == "-1" && $this->user_access[$this->module_id]['add'] != 1 ){
                    $this->session->set_flashdata('flashdata', 'You dont have sufficient privilege to execute the add action! Please contact the System Administrator.');
                    redirect(base_url().$this->module_link);
                }
                
                if( $this->input->post( 'record_id' ) != "-1" && $this->user_access[$this->module_id]['edit'] != 1 ){
                    $this->session->set_flashdata('flashdata', 'You dont have sufficient privilege to execute the edit action! Please contact the System Administrator.');
                    redirect(base_url().$this->module_link);
                }
                
                $this->load->model( 'uitype_edit' );
                $this->key_field_val = $related_oot_id;
                if($this->key_field_val != '-1') $check_record = $this->_record_exist( $related_oot_id );
                if( (isset($check_record) && $check_record->exist) || $this->key_field_val == "-1" ){
                    $data['fieldgroups'] = $this->_record_detail( $related_oot_id );
                    $this->session->set_userdata('before_edit', $data['fieldgroups']);
                }
                else{
                    $data['error'] = $check_record->error_message;
                    $data['error2'] = $check_record->error_message2;
                }

                if( $this->input->post('duplicate') ) $data['duplicate'] = TRUE;
                
                $this->load->vars($data);
            }else{
                if( !isset($_POST['record_id']) && $this->uri->rsegment(3) ) $_POST['record_id'] = $this->uri->rsegment(3);
        
                if( !$this->input->post( 'record_id' ) ){
                    $this->session->set_flashdata( 'flashdata', 'Insufficient data supplied!<br/>Please contact the System Administrator.' );
                    redirect( base_url().$this->module_link );
                }
                
                if( $this->input->post( 'record_id' ) == "-1" && $this->user_access[$this->module_id]['add'] != 1 ){
                    $this->session->set_flashdata('flashdata', 'You dont have sufficient privilege to execute the add action! Please contact the System Administrator.');
                    redirect(base_url().$this->module_link);
                }
                
                if( $this->input->post( 'record_id' ) != "-1" && $this->user_access[$this->module_id]['edit'] != 1 ){
                    $this->session->set_flashdata('flashdata', 'You dont have sufficient privilege to execute the edit action! Please contact the System Administrator.');
                    redirect(base_url().$this->module_link);
                }
                
                $this->load->model( 'uitype_edit' );
                $this->key_field_val = $this->input->post( 'record_id' );
                if($this->key_field_val != '-1') $check_record = $this->_record_exist( $this->input->post( 'record_id' ) );
                if( (isset($check_record) && $check_record->exist) || $this->key_field_val == "-1" ){
                    $data['fieldgroups'] = $this->_record_detail( $this->input->post( 'record_id' ) );
                    $this->session->set_userdata('before_edit', $data['fieldgroups']);
                }
                else{
                    $data['error'] = $check_record->error_message;
                    $data['error2'] = $check_record->error_message2;
                }

                if( $this->input->post('duplicate') ) $data['duplicate'] = TRUE;
                
                $this->load->vars($data);
            }

            //additional module edit routine here
            $data['show_wizard_control'] = false;
            $data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/js/editview.js"></script>';
            $data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/modules/forms/formsmain.js"></script>';
            $data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/js/date.js"></script>';

            if (!empty($this->module_wizard_form) && $this->input->post('record_id') == '-1') {
                $data['show_wizard_control'] = true;
                $data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/js/editview-wizard-form.js"></script>';
            }
            $data['content'] = 'leaves/editview';
            
            if( $_POST['filter'] == "personal" || $_POST['filter'] == "for_approval" || $rec->form_status_id == 1 || $this->user_access[$this->module_id]['post'] == 1) {
                $data['buttons'] = 'leaves/template/send-request';
            }

            //other views to load
            $data['views'] = array();
            $data['views_outside_record_form'] = array();

            if ($this->input->post('record_id') != '-1') {
                $this->db->where($this->key_field, $this->input->post('record_id'));
                $record = $this->db->get($this->module_table)->row_array();

                $data['email_sent'] = $record['email_sent'];
            }

            $approver = $this->db->get_where('form_approver', array('module_id' => $this->module_id, 'record_id' => $record_id));
           
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

    function ajax_save(){

        //check is there's approver
        if ($this->input->post('employee_id') != 1){
            $data['approvers'] = $this->system->get_approvers_and_condition( $this->input->post('employee_id'), $this->module_id );
            if (empty($data['approvers'])){
                $response->msg = "Please contact HR Admin. Approver has not been set.";
                $response->msg_type = "error";
                $data['json'] = $response;
                $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
                $to_check = false;
                return;
            }
        }
        
        //validation
        $employee_id = $this->input->post('employee_id');

        // Verify if supervisor and up.        
        $this->db->select('user_position.position_level_id, position_level');
        $this->db->where('employee_id', $employee_id);
        $this->db->join('user_position', 'user_position.position_id = user.position_id', 'left');
        $this->db->join('user_position_level', 'user_position_level.position_level_id = user_position.position_level_id', 'left');
        $employee = $this->db->get('user')->row();

        if(!($this->input->post('related_id') > 0)){
            $date_time_from = date('Y-m-d G:i:s',strtotime($this->input->post('datetime_from')));
        }else{
            $date_time_from = date('Y-m-d G:i:s',strtotime("+1 minutes", strtotime($this->input->post('datetime_from'))));
        }
        $date = date('Y-m-d',strtotime($this->input->post('date')));
        $date_to = date('Y-m-d',strtotime($date_time_from));
        $date_time_to = date('Y-m-d G:i:s',strtotime($this->input->post('datetime_to')));
        $time_from = date('G:i:s',strtotime($date_time_from));
        $time_to = date('G:i:s',strtotime($this->input->post('datetime_to')));

        $date_sched = $date_time_from;
        
        $policy_setup = $this->system->get_policy_form_setup($employee_id,9,$date);

        //check if undertime blanket filed
        $employee_out_blanket = 0;
        $this->db->where('date', $date);
        $this->db->where('employee_id', $employee_id);
        $this->db->where('outblanket_id > 0');
        $employee_out_blanket = $this->db->get('employee_out')->num_rows();

        $employee_emergency_blanket = 0;
        $this->db->where('date_from >= "'.$date.'" AND date_to <= "'.$date.'"');
        $this->db->where('employee_id', $employee_id);
        $this->db->where('blanket_id > 0');
        $employee_emergency_blanket = $this->db->get('employee_leaves')->num_rows();

        if (CLIENT_DIR == 'oams')
            $date_sched = $this->input->post('date');
        
        $shit_sched = $this->system->get_employee_worksched_shift($employee_id,$date_sched);

        $shift_start = $shit_sched->shifttime_start;
        $shift_end = $shit_sched->shifttime_end;
        $shift_id = $shit_sched->shift_id;
        $considered_rd = $shit_sched->considered_halfday;
        $holiday = false;

        //check for holiday         
        while ( strtotime($date) <= strtotime($date_to) ) {           
            $holiday_check = $this->system->holiday_check(date('Y-m-d',strtotime($date)), $employee_id);
            if( $holiday_check ){
                $holiday = true;
            }              
            $date = date ("Y-m-d", strtotime("+1 day", strtotime($date)));                                                             
        }  

        $to_check = true;

        if ($policy_setup['max_no_days_to_avail'] && $policy_setup['max_no_days_to_avail'] != '' && $policy_setup['max_no_days_to_avail'] != 0){
            $dates_affected = $this->system->get_affected_dates( $this->input->post('employee_id'), $this->input->post('date'), date('Y-m-d'), true, true );

            if (count($dates_affected) > $policy_setup['max_no_days_to_avail']){
                $response->msg = "Your application exceeded with the allowable time to apply.";
                $response->msg_type = "error";
                $to_check = false;                     
            }
        }
        if( $policy_setup['max_no_hrs_to_avail'] && $policy_setup['max_no_hrs_to_avail'] != '' && $policy_setup['max_no_hrs_to_avail'] != 0 ){

            $total_hrs =  strtotime($this->input->post('datetime_to')) - strtotime($this->input->post('datetime_from')) /60/60;

            if( $total_hrs > $policy_setup['max_no_hrs_to_avail'] ){
                $response->msg = "Total overtime hours must be less than or equal to ".$policy_setup['max_no_hrs_to_avail']." hour(s).";
                $response->msg_type = "error";
                $to_check = false;    
            }

        }
        if( $policy_setup['min_no_hrs_to_avail'] && $policy_setup['min_no_hrs_to_avail'] != '' && $policy_setup['min_no_hrs_to_avail'] != 0 ){

            $total_hrs =  ( strtotime($this->input->post('datetime_to')) - strtotime($this->input->post('datetime_from')) ) /60/60;

            if( $total_hrs < $policy_setup['min_no_hrs_to_avail'] ){
                $response->msg = "Total overtime hours must be greater than or equal to ".$policy_setup['min_no_hrs_to_avail']." hour(s).";
                $response->msg_type = "error";
                $to_check = false;    
            }

        }

        if( $policy_setup['min_no_hrs_to_avail_weekdays'] && $policy_setup['min_no_hrs_to_avail_weekdays'] != '' && $policy_setup['min_no_hrs_to_avail_weekdays'] != 0 ){

            $total_hrs =  ( strtotime($this->input->post('datetime_to')) - strtotime($this->input->post('datetime_from')) ) /60/60;

            if( $total_hrs < $policy_setup['min_no_hrs_to_avail_weekdays'] && !$this->system->check_weekend($date)){
                $response->msg = "Total overtime hours must be greater than or equal to ".$policy_setup['min_no_hrs_to_avail_weekdays']." hour(s).";
                $response->msg_type = "error";
                $to_check = false;    
            }

        }

        if( $policy_setup['min_no_hrs_to_avail_weekend'] && $policy_setup['min_no_hrs_to_avail_weekend'] != '' && $policy_setup['min_no_hrs_to_avail_weekend'] != 0 ){

            $total_hrs =  ( strtotime($this->input->post('datetime_to')) - strtotime($this->input->post('datetime_from')) ) /60/60;

            if( $total_hrs < $policy_setup['min_no_hrs_to_avail_weekend'] && $this->system->check_weekend($date) ){
                $response->msg = "Total overtime hours must be greater than or equal to ".$policy_setup['min_no_hrs_to_avail_weekend']." hour(s).";
                $response->msg_type = "error";
                $to_check = false;    
            }

        }
                        
        if ($this->system->check_cutoff_policy_forms($employee_id,$this->input->post('form_status_id'),9,date('Y-m-d',strtotime($this->input->post('date')))) == 1):
            $response->msg = "Next payroll cutoff not yet created in processing, please contact admin.";
            $response->msg_type = "error";
            $to_check = false;
        elseif ($this->system->check_cutoff_policy_forms($employee_id,$this->input->post('form_status_id'),9,date('Y-m-d',strtotime($this->input->post('date')))) == 2):
            if( !($this->input->post('related_id') > 0) ){
                $response->msg = "Your Overtime application is no longer within the allowable time to apply.";
                $response->msg_type = "error";
                $to_check = false;                
            }
        endif;
                    


        $date = date('Y-m-d',strtotime($date_time_from));
        if ($to_check){
            $qry = "SELECT *
            FROM {$this->db->dbprefix}employee_dtr
            WHERE deleted = 0 AND employee_id = '{$employee_id }' AND date = '{$date}'";
            $result = $this->db->query( $qry );                       
            if ($result->num_rows() > 0):                                   
                //check if overtime application is within the next cutoff         
                //if ( ( $time_from > $shift_start AND $time_from < $shift_end ) && ( ( !$holiday ) && ( $shift_id != 0 ) ) ):  //temporary comment since it could not file ot during restday
                if ( $employee_out_blanket == 0 && $employee_emergency_blanket == 0 && ( strtotime($time_from) > strtotime($shift_start) AND strtotime($time_from) < strtotime($shift_end) ) && ( ( !$holiday ) && ( $shift_id > 1 && $considered_rd == 0) ) ):                                        
                    $response->msg = "Invalid overtime schedule. Kindly check date and hours of overtime work applied.";
                    $response->msg_type = "error";
                    $data['json'] = $response;
                    $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);                    
                else:
                    if ($this->input->post('record_id') <> -1){
                        parent::ajax_save(); 
                    }
                    else{                    
                        $qry = "SELECT *
                        FROM {$this->db->dbprefix}employee_oot
                        WHERE deleted = 0 AND form_status_id=3 AND employee_id = '{$employee_id }' AND
                        ( 
                            ('{$date_time_from}' >= datetime_from AND '{$date_time_from}' < datetime_to) OR
                            ('{$date_time_to}' > datetime_from AND '{$date_time_to}' <= datetime_to)                    
                        )";
                        $result = $this->db->query( $qry );                
                        if ($result->num_rows() > 0):                    
                            $response->msg = "Overtime application has already been filed.";
                            $response->msg_type = "error";
                            $data['json'] = $response;
                            $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
                        else:
                            parent::ajax_save();
                        endif;
                    }
                endif;                
            else:                  
                //if ( ( $time_from > $shift_start AND $time_from < $shift_end ) && ( ( !$holiday ) && ( $shift_id != 0 ) ) ):
                if ( $employee_out_blanket == 0 && $employee_emergency_blanket == 0 && ( strtotime($time_from) > strtotime($shift_start) AND strtotime($time_from) < strtotime($shift_end) ) && ( ( !$holiday ) && ( $shift_id > 1 && $considered_rd == 0 ) ) ):                    
                    $response->msg = "Invalid overtime schedule. Kindly check date and hours of overtime work applied.";
                    $response->msg_type = "error";
                    $data['json'] = $response;
                    $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);    
                else:   
                    if ($this->input->post('record_id') <> -1){
                        parent::ajax_save(); 
                    }
                    else{
                        //only approved application
                        $qry = "SELECT *
                        FROM {$this->db->dbprefix}employee_oot
                        WHERE deleted = 0 AND employee_id = '{$employee_id }' AND (form_status_id=2 OR form_status_id=3) AND
                        ( 
                            ('{$date_time_from}' >= datetime_from AND '{$date_time_from}' < datetime_to) OR
                            ('{$date_time_to}' > datetime_from AND '{$date_time_to}' <= datetime_to)                     
                        )";
                        $result = $this->db->query( $qry );            
                        if ($result->num_rows() > 0):                    
                            $response->msg = "Overtime application has already been filed.";
                            $response->msg_type = "error";
                            $data['json'] = $response;
                            $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
                        else:                           
                            parent::ajax_save();
                        endif;
                    }                
                endif;             
            endif;
        } else {
            $data['json'] = $response;
            $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);      
        }
    }

     function delete() {

        $record_id = explode(',', $this->input->post('record_id'));

        $this->db->where_in($this->key_field,$record_id);
        $result = $this->db->get($this->module_table);

        if( $result->num_rows() > 0 ){

            $status_count = 0;

            foreach( $result->result() as $record ){

                if( $record->form_status_id != 1 && $record->form_status_id != 2 ){
                    $status_count++;
                }

            }

            if( $status_count > 0 ){

                $response['msg'] = 'Only draft and for approval application can be deleted.';
                $response['msg_type'] = 'attention';        
                $data['json'] = $response;
                $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);

            }
            else{

                parent::delete();

            }

        }
        else{
            $response['msg'] = 'No record found.';
            $response['msg_type'] = 'attention';        
            $data['json'] = $response;
            $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
        }


    }

    /**
     * Send the email to approvers.
     */
    function send_email() {
        $this->db->join('user','user.employee_id=employee_oot.employee_id');
        $this->db->join('form_status','form_status.form_status_id=employee_oot.form_status_id');
        $this->db->join('user_company','user_company.company_id=user.company_id');
        $this->db->where('employee_oot.employee_oot_id', $this->input->post('record_id'));
        $request = $this->db->get('employee_oot');

        if (IS_AJAX && !is_null($request) && $request->num_rows() > 0) {
            $mail_config = $this->hdicore->_get_config('outgoing_mailserver');
            if ($mail_config) {
                $recepients = array();
                $request = $request->row_array();

                $this->db->where('record_id', $this->input->post('record_id'));
                $this->db->where('module_id', $this->module_id);
                $this->db->where('focus', 1);
                $this->db->order_by('sequence', 'desc');
                $approver_user = $this->db->get('form_approver'); 
                if( $approver_user && $approver_user->num_rows() > 0  ){
                    foreach ($approver_user->result() as $a) {
                        switch($a->condition){
                            case 1:
                                if(!isset( $app_array) ) $app_array[] = $a->approver;
                                break;
                            case 2:
                            case 3:
                                $app_array[] = $a->approver;
                                break;
                        }
                    }

                    $app_array = array_unique($app_array);

                    $this->db->where_in('user_id', $app_array);
                    $result = $this->db->get('user');
                    $result = $result->result_array();
                    foreach ($result as $row) {
                        if (CLIENT_DIR  == 'firstbalfour'){
                            $request['approver_user'] = $row['salutation']." ".$row['firstname']." ".$row['lastname'];
                            if ($row['aux'] != ''){
                                $request['approver_user'] = $row['salutation']." ".$row['firstname']." ".$row['lastname']." ".$row['aux'];
                            }
                        }
                        else{
                            $request['approver_user'] = $row['salutation']." ".$row['lastname'];
                        }                         
                    }
                }

                $shift_row = $this->system->get_employee_worksched($request['employee_id'],date('Y-m-d',strtotime($request['datetime_from'])));

                // $request['here']=base_url().'forms/oot/detail/'.$request['employee_oot_id'];
                $request['here']=base_url();
                $pieces=explode(" ",$request['date_created']);
                if (CLIENT_DIR == 'firstbalfour'){
                    $request['date_created']=date($this->config->item('display_date_format_email_fb'),strtotime($pieces[0]));
                    $request['datetime_from']=date($this->config->item('display_datetime_format_email_fb'),strtotime($request['datetime_from']));
                    $request['datetime_to']=date($this->config->item('display_datetime_format_email_fb'),strtotime($request['datetime_to']));                    
                }
                else{
                    $request['date_created']=date($this->config->item('display_date_format_email'),strtotime($pieces[0]));
                    $request['datetime_from']=date($this->config->item('display_datetime_format_email'),strtotime($request['datetime_from']));
                    $request['datetime_to']=date($this->config->item('display_datetime_format_email'),strtotime($request['datetime_to']));
                }
                $request['current_shift'] = $shift_row->shift_calendar;
                $this->db->where("'".date('Y-m-d', strtotime($request['datetime_from']))."' BETWEEN date_from AND date_to ", '', false);
                $tkp = $this->db->get('timekeeping_period');

                if($tkp && $tkp->num_rows() > 0){
                    $request['pcof'] = date($this->config->item('display_date_format_email'), strtotime($tkp->row()->period_cutoff));
                }
                else{
                    $request['pcof'] = 'Cutoff not defined';                
                }

                $request['cutoff'] = $this->_get_cutoff($tkp);

                // $request['url'] = base_url().'forms/oot/detail/'.$this->input->post( 'record_id' );
                $request['url'] = '<a href="'.base_url().'">'.base_url().'</a>';

                // Load the template.            
                $this->load->model('template');
                $template = $this->template->get_module_template($this->module_id, 'oot_request');
                $message = $this->template->prep_message($template['body'], $request);

                if( is_array( $app_array ) && sizeof($app_array) > 0 ){
                    $this->db->where_in('user_id', $app_array);
                    $result = $this->db->get('user');

                    $result = $result->result_array();

                    foreach ($result as $row) {
                        $recepients[] = trim($row['email']);
                        if (CLIENT_DIR  == 'firstbalfour'){
                            $request['approver_user'] = $row['salutation']." ".$row['firstname']." ".$row['lastname'];
                            if ($row['aux'] != ''){
                                $request['approver_user'] = $row['salutation']." ".$row['firstname']." ".$row['lastname']." ".$row['aux'];
                            }
                        }
                        else{
                            $request['approver_user'] = $row['salutation']." ".$row['lastname'];
                        }                         
                        $message = $this->template->prep_message($template['body'], $request);
                        $this->template->queue(trim($row['email']), '', $template['subject']." : ".$request['firstname']." ".$request['middleinitial']." ".$request['lastname'], $message);
                    }

                    $recepients = array_unique($recepients);

                    $data['form_status_id'] = 2;
                    $data['email_sent'] = '1';
                    $data['date_sent'] = date('Y-m-d G:i:s');                    
                    $this->db->where($this->key_field, $request[$this->key_field]);
                    $this->db->update($this->module_table, $data);

                    $this->db->where_in('approver', $app_array);
                    $this->db->where(array('module_id' => $this->module_id, 'record_id' => $this->input->post('record_id')));   
                    $this->db->update('form_approver', array('status' => 2));
                    
                }
            }
        } else {
            $this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
            redirect(base_url() . $this->module_link);
        }
    }

     function send_status_email( $record_id, $status_id, $decline_remarks = false ){

        $this->db->join('user','user.employee_id=employee_oot.employee_id');
        $this->db->join('form_status','form_status.form_status_id=employee_oot.form_status_id');
        $this->db->join('user_company','user_company.company_id=user.company_id');
        $this->db->where('employee_oot.employee_oot_id', $this->input->post('record_id'));
        $request = $this->db->get('employee_oot');

        if (IS_AJAX && !is_null($request) && $request->num_rows() > 0) {

             $mail_config = $this->hdicore->_get_config('outgoing_mailserver');
            if ($mail_config) {
                $recepients = array();
                $request = $request->row_array();

             $shift_row = $this->system->get_employee_worksched($request['employee_id'],date('Y-m-d',strtotime($request['datetime_from'])));

                // $request['here']=base_url().'forms/oot/detail/'.$request['employee_oot_id'];
                $request['here']=base_url();                
                $request['url'] = '<a href="'.base_url().'">'.base_url().'</a>';
                $pieces=explode(" ",$request['date_created']);
                if (CLIENT_DIR == 'firstbalfour'){
                    $request['date_created']=date($this->config->item('display_date_format_email_fb'),strtotime($pieces[0]));
                    $request['datetime_from']=date($this->config->item('display_datetime_format_email_fb'),strtotime($request['datetime_from']));
                    $request['datetime_to']=date($this->config->item('display_datetime_format_email_fb'),strtotime($request['datetime_to']));                    
                }
                else{
                    $request['date_created']=date($this->config->item('display_date_format_email'),strtotime($pieces[0]));
                    $request['datetime_from']=date($this->config->item('display_datetime_format_email'),strtotime($request['datetime_from']));
                    $request['datetime_to']=date($this->config->item('display_datetime_format_email'),strtotime($request['datetime_to']));
                }
                $request['current_shift'] = $shift_row->shift_calendar;

                switch($status_id){
                    case 3:
                        $request['status'] = "approved";
                    break;
                    case 4:
                        $request['status'] = "disapproved";
                    break;
                    case 5:
                        $request['status'] = "cancelled";
                    break;
                }

                $request['decline_remarks'] = $decline_remarks;

                if (CLIENT_DIR  == 'firstbalfour'){
                    $request['employee'] = $request['salutation']." ".$request['firstname']." ".$request['lastname'];
                    if ($request['aux'] != ''){
                        $request['employee'] = $request['salutation']." ".$request['firstname']." ".$request['lastname']." ".$request['aux'];
                    }
                }
                // Load the template.            
                $this->load->model('template');
                $template = $this->template->get_module_template($this->module_id, 'ot_status_email');
                $message = $this->template->prep_message($template['body'], $request);

                $cc_copy = $this->system->get_approvers_and_condition($request['employee_id'], $this->module_id, 'email');
                if( ( is_array( $cc_copy  ) && sizeof($cc_copy) > 0 ) ){
                    foreach( $cc_copy as $cc_user ){
                        $condition = $cc_user['condition'];
                        $cc_user = $this->db->get_where('user',array('user_id'=> $cc_user['approver']))->row();
                            if( !in_array(trim($cc_user->email), $cc ) && $cc_user->user_id != $this->userinfo['user_id'] && $condition == 2)  $cc[] = trim( $cc_user->email );
                    }
                }
           
                $cc_copy = '';
                if(isset($cc)) $cc_copy = implode(',', $cc);

                // $this->template->queue($request['email'], $cc_copy, $template['subject']." : ".$request['firstname']." ".$request['middleinitial']." ".$request['lastname'], $message);
                $this->template->queue($request['email'], "", $template['subject']." : ".$request['firstname']." ".$request['middleinitial']." ".$request['lastname'], $message);

            }

        } else {
            $this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
            redirect(base_url() . $this->module_link);
        }

    }

    function get_worksched($employee_id = FALSE,$date = FALSE){
        $emp = $this->system->get_employee_worksched($employee_id, date('Y-m-d', strtotime($date)));  
        switch( date('N', strtotime( $this->input->post('date') )) ){
            case 1:
                $where = array('shift_id' => $emp->monday_shift_id);
                break;
            case 2:
                $where = array('shift_id' => $emp->tuesday_shift_id);
                break;
            case 3:
                $where = array('shift_id' => $emp->wednesday_shift_id);
                break;
            case 4:
                $where = array('shift_id' => $emp->thursday_shift_id);
                break;
            case 5:
                $where = array('shift_id' => $emp->friday_shift_id);
                break;
            case 6:
                $where = array('shift_id' => $emp->saturday_shift_id);
                break;
            case 7:
                $where = array('shift_id' => $emp->sunday_shift_id);
                break;    
        }

        $shift = $this->db->get_where('timekeeping_shift', $where);
        $shift = $shift->row();
        return $shift;
        //$response->shift->shifttime_end = date('g:i a', strtotime($response->shift->shifttime_end));
    }  

    function validation_check(){
        $err = false;
        $msg = "";          
        $employee_id = $this->input->post('employee_id');
        $date_f = date('Y-m-d',strtotime($this->input->post('datetime_from')));
        $date_time_from = date('Y-m-d G:i:s',strtotime($this->input->post('datetime_from')));
        $date_time_to = date('Y-m-d G:i:s',strtotime($this->input->post('datetime_to')));
        $time_from = date('G:i:s',strtotime($this->input->post('datetime_from')));
        $time_to = date('G:i:s',strtotime($this->input->post('datetime_to')));

        $shit_sched = $this->system->get_employee_worksched_shift($employee_id,$this->input->post('datetime_from'));
        $shift_start = $shit_sched->shifttime_start;
        $shift_end = $shit_sched->shifttime_end;
        $shift_id = $shit_sched->shift_id;
        $considered_rd = $shit_sched->considered_halfday;
        $holiday = false;

        //check for holiday
        $date = date('Y-m-d',strtotime($this->input->post('datetime_from')));
        $date_to = date('Y-m-d',strtotime($this->input->post('datetime_to')));

        while ( strtotime($date) <= strtotime($date_to) ) {           
            $holiday_check = $this->system->holiday_check(date('Y-m-d',$date), $employee_id);
            if( $holiday_check ){
                $holiday = true;
            }                                            
            $date = date('Y-m-d' , mktime(0,0,0,date('m',$date),date('d',$date)+1,date('Y',$date)));                           
            $date = strtotime($date);
        } 

        if ($employee_id == ""):
            $err = true;
            $msg = "Please select employee.";
        else:
            $qry = "SELECT *
            FROM {$this->db->dbprefix}employee_dtr
            WHERE deleted = 0 AND employee_id = '{$employee_id }' AND date = '{$date_f}'";
            $result = $this->db->query( $qry );

            if ($result->num_rows() > 0):
                $row = $result->row();
                //check if overtime application is within the next cutoff
                $to_check = true; 

                if ($to_check):
                    if (date('Y-m-d') > date('Y-m-d',strtotime($date_time_from))):
                        if ($this->system->check_in_cutoff($date_time_from) == 0):
                            //if ( ( $time_from > $shift_start AND $time_from < $shift_end ) && ( ( !$holiday ) && ( $shift_id != 0 ) ) ):
                            if ( ( $time_from > $shift_start AND $time_from < $shift_end ) && ( ( !$holiday ) && ( $shift_id > 1 && $considered_rd == 0) ) ):
                                $err = true;
                                $msg = "Invalid overtime schedule. Kindly check date and hours of overtime work applied.";
                                $response = $holiday." ".$shift_id;
                            else:
                                $qry = "SELECT *
                                FROM {$this->db->dbprefix}employee_oot
                                WHERE deleted = 0 AND form_status_id=3 AND employee_id = '{$employee_id }' AND
                                ( 
                                    ('{$date_time_from}' BETWEEN datetime_from AND datetime_to) OR
                                    ('{$date_time_to}' BETWEEN datetime_from AND datetime_to)                    
                                )";
                                $result = $this->db->query( $qry );                
                                if ($result->num_rows() > 0):                    
                                    $err = true;
                                    $msg = "Overtime application has already been filed.";
                                else:
                                    $err = false;
                                    $msg = "";
                                endif;
                            endif;              
                        elseif ($this->system->check_in_cutoff($date_time_from) == 1):
                            $err = true;
                            $msg = "Next payroll cutoff not yet created in processing, please contact admin.";
                        elseif ($this->system->check_in_cutoff($date_time_from) == 2):
                            $err = true;
                            $msg = "Your Overtime application is no longer within the allowable time.";                        
                        endif;
                    else:
                        //if ( ( $time_from > $shift_start AND $time_from < $shift_end ) && ( ( !$holiday ) && ( $shift_id != 0 ) ) ):
                        if ( ( $time_from > $shift_start AND $time_from < $shift_end ) && ( ( !$holiday ) && ( $shift_id > 1 && $considered_rd == 0 ) ) ):
                            $err = true;
                            $msg = "Invalid overtime schedule. Kindly check date and hours of overtime work applied.";
                            $response = $holiday." ".$shift_id;
                        else:
                            $qry = "SELECT *
                            FROM {$this->db->dbprefix}employee_oot
                            WHERE deleted = 0 AND form_status_id=3 AND employee_id = '{$employee_id }' AND
                            ( 
                                ('{$date_time_from}' BETWEEN datetime_from AND datetime_to) OR
                                ('{$date_time_to}' BETWEEN datetime_from AND datetime_to)                    
                            )";
                            $result = $this->db->query( $qry );                
                            if ($result->num_rows() > 0):                    
                                $err = true;
                                $msg = "Overtime application has already been filed.";
                            else:
                                $err = false;
                                $msg = "";
                            endif;
                        endif; 
                    endif;
                endif;
            else:   
                //if ( ( $time_from > $shift_start AND $time_from < $shift_end ) && ( ( !$holiday ) && ( $shift_id != 0 ) ) ):
                if ( ( $time_from > $shift_start AND $time_from < $shift_end ) && ( ( !$holiday ) && ( $shift_id > 1 && $considered_rd == 0) ) ):                    
                    $err = true;
                    $msg = "Invalid overtime schedule. Kindly check date and hours of overtime work applied.";
                    $response = $holiday." ".$shift_id;
                else:
                    //only approved application
                    $qry = "SELECT *
                    FROM {$this->db->dbprefix}employee_oot
                    WHERE deleted = 0 AND employee_id = '{$employee_id }' AND form_status_id=3 AND
                    ( 
                        ('{$date_time_from}' BETWEEN datetime_from AND datetime_to) OR
                        ('{$date_time_to}' BETWEEN datetime_from AND datetime_to)                    
                    )";
                    $result = $this->db->query( $qry );                
                    if ($result->num_rows() > 0):                    
                        $err = true;
                        $msg = "Overtime application has already been filed.";
                    else:
                        $err = false;
                        $msg = "";
                    endif;               
                endif;             
            endif;        
        endif;

        //validate if within allowable time upon selecting of date
        if ($this->config->item('maxtime_to_apply_overtime') && $this->config->item('maxtime_to_apply_overtime') != '' && $this->config->item('maxtime_to_apply_overtime') != 0):
            $dates_affected = $this->system->get_affected_dates( $this->input->post('employee_id'), $this->input->post('date'), date('Y-m-d'), true, true );
                if (count($dates_affected) > $this->config->item('maxtime_to_apply_overtime')):
                    $err = true;
                    $msg = "Your application exceeded with the allowable time to apply."; 
                    $to_check = false; 
                endif;                  
        endif;

        $this->load->view('template/ajax', 
            array('json' => 
                array('err' => $err, 'msg_type' => $msg)
            )
        );   
    }  

    function get_inclusive_worksched(){
        $this->oot->_get_inclusive_worksched();
    }

    function get_inclusive_undertime_blanket(){
        $employee_id = $this->input->post('employee_id');
        $date = date('Y-m-d', strtotime($this->input->post('date')));
        $data->employee_out_blanket = 0;

        $this->db->where('date', $date);
        $this->db->where('employee_id', $employee_id);
        $this->db->where('outblanket_id > 0');
        $employee_out_blanket = $this->db->get('employee_out')->num_rows();

        $data->employee_out_blanket = $employee_out_blanket;
        $this->load->view('template/ajax', array('json' => $data));
    }

    function get_inclusive_emergency_blanket(){
        $employee_id = $this->input->post('employee_id');
        $date = date('Y-m-d', strtotime($this->input->post('date')));
        $data->employee_emergency_blanket = 0;

        $this->db->where('date_from >= "'.$date.'" AND date_to <= "'.$date.'"');
        $this->db->where('employee_id', $employee_id);
        $this->db->where('blanket_id > 0');
        $employee_emergency_blanket = $this->db->get('employee_leaves')->num_rows();

        $data->employee_emergency_blanket = $employee_emergency_blanket;
        $this->load->view('template/ajax', array('json' => $data));
    }

    function quick_edit(){
        parent::quick_edit( $this->module_link );
    }

}

/* End of file */
/* Location: system/application */
