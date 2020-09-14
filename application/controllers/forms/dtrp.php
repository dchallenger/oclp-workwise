<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require(APPPATH.'libraries/Formsmain.php');

class Dtrp extends Formsmain {

    function __construct() {
        parent::__construct();
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
        $date = date('Y-m-d',strtotime($this->input->post('date'))); 
        $time = date('Y-m-d',strtotime($this->input->post('time'))); 
        
/*        if( strtotime($date) > strtotime($time) ){
            $response->msg = "Date and time of transaction should be past beyond the date of transaction. Kindly check.";
            $response->msg_type = "error";
            $data['json'] = $response;
            $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
            return;
        }*/

        $employee_id = $this->input->post('employee_id');
        $timeset = $this->input->post('time_set_id');
        $to_check = true;
        $date_apply = date('Y-m-d',strtotime($this->input->post('date')));
        $get_policy = $this->system->get_policy_form_setup($employee_id,11,$date_apply);
/*        if (date('Y-m-d')  !=  $date_apply ) {
            //late filling
            if(date('Y-m-d') > $date_apply) {
                if(!empty($get_policy['filing_before_days_or_month']) && $get_policy['filing_before_days_or_month'] > 0) {
                    $datediff = strtotime(date('Y-m-d')) - strtotime($date_apply);
                    $day =  floor($datediff/3600/24);
                    if($day > $get_policy['filing_before_days_or_month']) {
                        $response->msg = "Daily time record problem can not be created in late.";
                        $response->msg_type = "error";
                        $data['json'] = $response;
                        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
                        $to_check = false;
                    }
                }
            } else { //advance filling                
                if(!empty($get_policy['filing_after_days_or_month']) && $get_policy['filing_after_days_or_month'] > 0) {
                    $datediff = strtotime($date_apply) - strtotime(date('Y-m-d'));
                    $day =  floor($datediff/3600/24);
                    if($day > $get_policy['filing_after_days_or_month']) {
                        $response->msg = "Daily time record problem can not be created in advance.";
                        $response->msg_type = "error";
                        $data['json'] = $response;
                        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
                        $to_check = false;
                    }
                }
            }
        }*/

        if (!empty($get_policy['max_no_of_filling']) && $get_policy['max_no_of_filling'] > 0) {
            if ($to_check){
                $date = date('Y-m-',strtotime($date));
                $qry = "SELECT *
                FROM {$this->db->dbprefix}employee_dtrp
                WHERE deleted = 0 AND form_status_id IN (3,2) AND employee_id = '{$employee_id}' AND date LIKE '%{$date}%'";
                $result = $this->db->query( $qry );
                if ($result->num_rows() >= $get_policy['max_no_of_filling']) {
                    $response->msg = "Allowed number of DTRP per month already filed.";
                    $response->msg_type = "error";
                    $data['json'] = $response;
                    $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
                    $to_check = false;
                }
            }
        }
        if ($to_check) {
            if ($this->system->check_cutoff_policy_forms($employee_id,$this->input->post('form_status_id'),11,$date_apply) == 1) {
                $response->msg = "Next payroll cutoff not yet created in processing, please contact admin.";
                $response->msg_type = "error";
                $data['json'] = $response;
                $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
                $to_check = false;
            } elseif ($this->system->check_cutoff_policy_forms($employee_id,$this->input->post('form_status_id'),11,$date_apply) == 2) {
                $response->msg = "Your DTRP application is no longer within the allowable time to apply.";
                $response->msg_type = "error";
                $data['json'] = $response;
                $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
                $to_check = false;
            }
        }

        if ($to_check) {
            if ($this->input->post('record_id') <> -1) {
                parent::ajax_save(); 
            } else {
                $qry = "SELECT *
                FROM {$this->db->dbprefix}employee_dtrp
                WHERE deleted = 0 AND form_status_id=3 AND employee_id = '{$employee_id }' AND date = '{$date}' AND time_set_id = '{$timeset}'";
                $result = $this->db->query( $qry );
                if ($result->num_rows() > 0) {                   
                    $response->msg = "Daily Time Record problem already filed.";
                    $response->msg_type = "error";
                    $data['json'] = $response;
                    $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
                } else {
                    parent::ajax_save();
                }
            }
        }
    }

    /**
     * Send the email to approvers.
     */
    function send_email() {
        $this->db->join('user','user.employee_id=employee_dtrp.employee_id');
        $this->db->join('form_status','form_status.form_status_id=employee_dtrp.form_status_id');
        $this->db->join('user_company','user_company.company_id=user.company_id');
        $this->db->join('time_set','employee_dtrp.time_set_id=time_set.time_set_id');
        $this->db->where('employee_dtrp.employee_dtrp_id', $this->input->post('record_id'));
        $request = $this->db->get('employee_dtrp');

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

                $this->db->where("('".$request['date']."' >= date_from && '".$request['date']."' <= date_to)");
                // $this->db->where($request['date_to'].' BETWEEN date_from AND date_to');
                $tkp = $this->db->get('timekeeping_period');

                if($tkp->num_rows() > 0 && $tkp)
                    $request['payroll_cutoff'] = date($this->config->item('display_date_format_email'), strtotime($tkp->row()->period_cutoff));
                else
                    $request['payroll_cutoff'] = 'Cutoff not defined';

                $request['cutoff'] = $this->_get_cutoff($tkp);

                $shift_row = $this->system->get_employee_worksched($request['employee_id'],date('Y-m-d',strtotime($request['datetime_from'])));

                // $request['here']=base_url().'forms/dtrp/detail/'.$request['employee_dtrp_id'];
                $request['here']=base_url();
                $pieces=explode(" ",$request['date_created']);

                if (CLIENT_DIR == 'firstbalfour'){
                    $request['date_created']=date($this->config->item('display_date_format_email_fb'),strtotime($pieces[0]));                    
                }
                else{
                    $request['date_created']=date($this->config->item('display_date_format_email'),strtotime($pieces[0]));
                }

                $request['current_shift'] = $shift_row->shift_calendar;
                $request['date']=date($this->config->item('display_date_format_email'),strtotime($request['date']));
                $request['time']= date($this->config->item('display_time_format_email'),strtotime($request['time']));
                $request['timeset']= $request['time_set'];

                // $request['url'] = base_url().'forms/dtrp/detail/'.$this->input->post( 'record_id' );
                 $request['url'] = '<a href="'.base_url().'">'.base_url().'</a>';

                // Load the template.            
                $this->load->model('template');
                $template = $this->template->get_module_template($this->module_id, 'dtrp_request');
                $message = $this->template->prep_message($template['body'], $request);

                if( is_array( $app_array ) && sizeof($app_array) > 0 ){
                    $this->db->where_in('user_id', $app_array);
                    $result = $this->db->get('user');

                    $result = $result->result_array();

                    foreach ($result as $row) {
                        $recepients[] = $row['email'];
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
                        $this->template->queue($row['email'], '', $template['subject']." : ".$request['firstname']." ".$request['middleinitial']." ".$request['lastname'], $message);
                    }

                    $recepients = array_unique($recepients);
                    
                    $data['form_status_id'] = 2;
                    $data['email_sent'] = '1';
                    $data['date_sent'] = date('Y-m-d G:i:s');                    
                    $this->db->where($this->key_field, $request[$this->key_field]);
                    $this->db->update($this->module_table, $data);
                    
                    $this->db->where_in('approver', $app_array);
                    $this->db->where(array('module_id' => $this->module_id, 'record_id' => $this->input->post('record_id')));  
                    $this->db->update('form_approver', array('status' => 2) );

                    $response->msg = 'Success';
                    $response->msg_type = 'success';

                    $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
                }
            }
        } else {
            $this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
            redirect(base_url() . $this->module_link);
        }
    }

    function send_status_email( $record_id, $status_id, $decline_remarks = false ){

        $this->db->join('user','user.employee_id=employee_dtrp.employee_id');
        $this->db->join('form_status','form_status.form_status_id=employee_dtrp.form_status_id');
        $this->db->join('user_company','user_company.company_id=user.company_id');
        $this->db->join('time_set','employee_dtrp.time_set_id=time_set.time_set_id');
        $this->db->where('employee_dtrp.employee_dtrp_id', $this->input->post('record_id'));
        $request = $this->db->get('employee_dtrp');

        if (IS_AJAX && !is_null($request) && $request->num_rows() > 0) {

             $mail_config = $this->hdicore->_get_config('outgoing_mailserver');
            if ($mail_config) {
                $recepients = array();
                $request = $request->row_array();

                $shift_row = $this->system->get_employee_worksched($request['employee_id'],date('Y-m-d',strtotime($request['datetime_from'])));

                $this->db->where("('".$request['date']."' >= date_from && '".$request['date']."' <= date_to)");
                // $this->db->where($request['date_to'].' BETWEEN date_from AND date_to');
                $tkp = $this->db->get('timekeeping_period');

                if($tkp->num_rows() > 0 && $tkp)
                    $request['payroll_cutoff'] = date($this->config->item('display_date_format_email'), strtotime($tkp->row()->period_cutoff));
                else
                    $request['payroll_cutoff'] = 'Cutoff not defined';

                // $request['here']=base_url().'forms/dtrp/detail/'.$request['employee_dtrp_id'];
                $request['here']=base_url();
                $request['url'] = '<a href="'.base_url().'">'.base_url().'</a>';
                $pieces=explode(" ",$request['date_created']);
                
                if (CLIENT_DIR == 'firstbalfour'){
                    $request['date_created']=date($this->config->item('display_date_format_email_fb'),strtotime($pieces[0]));                    
                }
                else{
                    $request['date_created']=date($this->config->item('display_date_format_email'),strtotime($pieces[0]));
                }

                $request['current_shift'] = $shift_row->shift_calendar;
                $request['date']=date($this->config->item('display_date_format_email'),strtotime($request['date']));
                $request['time']= date($this->config->item('display_time_format_email'),strtotime($request['time']));
                $request['timeset']= $request['time_set'];

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
                $template = $this->template->get_module_template($this->module_id, 'dtrp_status_email');
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

    function validation_check(){
        $err = false;
        $msg = "";           
        $date = date('Y-m-d',strtotime($this->input->post('date'))); 
        $employee_id = $this->input->post('employee_id');
        $time = date('Y-m-d G:i:s',strtotime($this->input->post('time')));
        $timeset = $this->input->post('time_set_id');
        $to_check = false;

        $qry = "SELECT *
        FROM {$this->db->dbprefix}employee_dtrp
        WHERE deleted = 0 AND form_status_id=3 AND employee_id = '{$employee_id }' AND date = '{$date}' AND time = '{$time}' AND time_set_id = '{$timeset}'";
        $result = $this->db->query( $qry );
        if ($result->num_rows() > 0) {
            $err = true;
            $msg = "Daily Time Record problem already filed.";
        } else {
            $to_check = true;
            $err = false;
            $msg = "";    
        }

        $date_apply = date('Y-m-d',strtotime($this->input->post('date')));
        $get_policy = $this->system->get_policy_form_setup($employee_id,11,$date_apply);
/*        if (date('Y-m-d')  !=  $date_apply ) {
            //late filling
            if(date('Y-m-d') > $date_apply){
                if(!empty($get_policy['filing_before_days_or_month']) && $get_policy['filing_before_days_or_month'] > 0) {
                    $datediff = strtotime(date('Y-m-d')) - strtotime($date_apply);
                    $day =  floor($datediff/3600/24);
                    if($day > $get_policy['filing_before_days_or_month']) {
                        $err = true;
                        $msg = "Daily time record problem can not be created in late.";  
                        $to_check = false;
                    }
                }
            } else { //advance filling                
                if(!empty($get_policy['filing_after_days_or_month']) && $get_policy['filing_after_days_or_month'] > 0) {
                    $datediff = strtotime($date_apply) - strtotime(date('Y-m-d'));
                    $day =  floor($datediff/3600/24);
                    if($day > $get_policy['filing_after_days_or_month']) {
                        $err = true;
                        $msg = "Daily time record problem can not be created in advance.";  
                        $to_check = false;
                    }
                }
            }
        }*/

        if (!empty($get_policy['max_no_of_filling']) && $get_policy['max_no_of_filling'] > 0) {
            if ($to_check){
                $date = date('Y-m-',strtotime($date));
                $qry = "SELECT *
                FROM {$this->db->dbprefix}employee_dtrp
                WHERE deleted = 0 AND form_status_id IN (3,2) AND employee_id = '{$employee_id}' AND date LIKE '%{$date}%'";
                $result = $this->db->query( $qry );
                if ($result->num_rows() >= $get_policy['max_no_of_filling']) {
                    $err = true;
                    $msg = "Allowed number of DTRP per month already filed.";  
                    $to_check = false;
                }
            }
        }

        $this->load->view('template/ajax', 
            array('json' => 
                array('err' => $err, 'msg_type' => $msg)
            )
        );          
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
}

/* End of file */
/* Location: system/application */
