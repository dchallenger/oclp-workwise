<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require(APPPATH.'libraries/Formsmain.php');

class Out extends Formsmain {

    function __construct() {
        parent::__construct();
    }

    function get_worksched(){
        if(!IS_AJAX){
            $this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
            redirect(base_url().$this->module_link);
        }

        $response->shift = $this->system->get_employee_worksched(  $this->input->post('employee_id'), date('Y-m-d', strtotime( $this->input->post('date') ) ), true );  
        $response->shift->shifttime_start = date('g:i a', strtotime($response->shift->shifttime_start));
        $response->shift->shifttime_end = date('g:i a', strtotime($response->shift->shifttime_end));

        $data['json'] = $response;
        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
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
        $date = date('Y-m-d',strtotime($this->input->post('date')));    
        $time_start = $this->input->post('time_start');    
        $time_end = $this->input->post('time_end');
        $array_stack = $this->system->get_employee_rest_day($employee_id, $date_late);  
        $day = date('D',strtotime($date));

        $policy_setup = $this->system->get_policy_form_setup($employee_id,10,$date);

        $shit_sched = $this->system->get_employee_worksched($employee_id,date('Y-m-d', strtotime($this->input->post('date'))), true);
        $shift_start = $shit_sched->shifttime_start;
        $shift_end = $shit_sched->shifttime_end;

        $diff =  $this->hdicore->get_time_difference( $this->input->post('date'), date('Y-m-d H:i:s') );

        //get next working day
        $days_to_apply = $policy_setup['max_no_days_to_avail'];
        $working_days = 0;
        $next_working_day = $date;
        while($working_days < $days_to_apply){
            if( $policy_setup['calendar_days'] == 0 ){
                $next_working_day = $this->system->get_next_working_day($this->input->post('employee_id'), $next_working_day);
            }
            else{
                $next_working_day = date('Y-m-d', strtotime( '+1 day' . $next_working_day));
            }

            $working_days++;
        }
        
        $last_day_to_file = $next_working_day . ' 23:59:59';
        $now = date('Y-m-d H:i:s');


        if ($this->system->check_cutoff_policy_forms($employee_id,$this->input->post('form_status_id'),10,$date) == 1){
            $response->msg = "Next payroll cutoff not yet created in processing, please contact HRA.";
            $response->msg_type = "error";
            $data['json'] = $response;
            $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);   
        }
        else{
            if ($this->system->check_cutoff_policy_forms($employee_id,$this->input->post('form_status_id'),10,$date) == 2){
                $response->msg = 'Your Undertime application is no longer within the allowable time to apply.';
                $response->msg_type = "error";
                $data['json'] = $response;
                $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
            }
            else{

                if( $now >  $last_day_to_file ){
                    $response->msg = "Your Undertime application is past its due date.";
                    $response->msg_type = "error";
                    $data['json'] = $response;
                    $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
                }else{ 

                    $time_start = date('H:i:s', strtotime($_POST['time_start']));
                    $time_end = date('H:i:s', strtotime($_POST['time_end']));

                    $datetime_start = date('Y-m-d H:i:s', strtotime($date." ".$_POST['time_start']));
                    $datetime_end = date('Y-m-d H:i:s', strtotime($date." ".$_POST['time_end']));

                    //Check if different meridian
                    if( ( date('a', strtotime($shift_start) ) != date('a', strtotime($shift_end) ) ) 
                        && ( date('a', strtotime($_POST['time_start']) ) != date('a', strtotime($_POST['time_end']) ) )  ){

                        $datetime_end = date('Y-m-d H:i:s', strtotime('+1 day', strtotime($date." ".$_POST['time_end'])));

                    }

                    if( $datetime_end <=  $datetime_start ){
                        $response->msg = "Invalid undertime schedule. Kindly check date and hours work applied.";
                        $response->msg_type = "error";
                        $data['json'] = $response;
                        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
                    }
                    else{
                        if ( $policy_setup['filling_only_within_shift'] == 1 && ( $time_start < $shift_start || $time_end != $shift_end ) ){
                            $response->msg = "The time start or end should be in between your shift schedule.";
                            $response->msg_type = "error";
                            $data['json'] = $response;
                            $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
                        }
                        else{
                            if ( $policy_setup['working_days_only'] == 1 && ( $this->system->check_date_range_if_holiday($date,$date,$employee_id) ) ){
                                $response->msg = "Undertime schedule falls on a holiday. Kindly check date applied.";
                                $response->msg_type = "error";
                                $data['json'] = $response;
                                $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
                            }
                            else{
                                if ( $policy_setup['working_days_only'] == 1 && ( in_array($day, $array_stack) ) ){
                                    $response->msg = "Invalid undertime schedule. Please check date and hours work applied.";
                                    $response->msg_type = "error";
                                    $data['json'] = $response;
                                    $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);                                     
                                }
                                else{                        
                                    if ($this->input->post('record_id') <> -1){
                                        parent::ajax_save(); 
                                    }
                                    else{                
                                        //only approved application
                                        $qry = "SELECT *
                                        FROM {$this->db->dbprefix}employee_out
                                        WHERE deleted = 0 AND employee_id = '{$employee_id }' AND form_status_id=3 AND
                                        ( 
                                            ('{$time_start}' BETWEEN time_start AND time_end) OR
                                            ('{$time_end}' BETWEEN time_start AND time_end)                    
                                        ) AND
                                        date = '{$date}'";
                                        $result = $this->db->query( $qry ); 
                                        if ($result->num_rows() > 0):                    
                                            $response->msg = "Undertime application has already been filed.";
                                            $response->msg_type = "error";
                                            $data['json'] = $response;
                                            $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
                                        else:
                                            parent::ajax_save();
                                        endif;  
                                    } 
                                }  
                            }
                        }                           
                    }
                }
            }
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

        $this->db->join('user','user.employee_id=employee_out.employee_id');
        $this->db->join('form_status','form_status.form_status_id=employee_out.form_status_id');
        $this->db->join('user_company','user_company.company_id=user.company_id');
        $this->db->where('employee_out.employee_out_id', $this->input->post('record_id'));
        $request = $this->db->get('employee_out');

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

                $shift_row = $this->system->get_employee_worksched($request['employee_id'],date('Y-m-d',strtotime($request['date'])));

                
                $this->db->where("('".$request['date']."' >= date_from && '".$request['date']."' <= date_to)");
                // $this->db->where($request['date_to'].' BETWEEN date_from AND date_to');
                $tkp = $this->db->get('timekeeping_period');

                if($tkp->num_rows() > 0 && $tkp)
                    $request['payroll_cutoff'] = date($this->config->item('display_date_format_email'), strtotime($tkp->row()->period_cutoff));
                else
                    $request['payroll_cutoff'] = 'Cutoff not defined';

                $request['cutoff'] = $this->_get_cutoff($tkp);

                // $request['here']=base_url().'forms/out/detail/'.$request['employee_out_id'];
                $request['here']=base_url();
                $pieces=explode(" ",$request['date_created']);
                if (CLIENT_DIR == 'firstbalfour'){
                    $request['date_created']=date($this->config->item('display_date_format_email_fb'),strtotime($pieces[0]));
                    $request['date']= date($this->config->item('display_date_format_email_fb'),strtotime($request['date']));                    
                }
                else{
                    $request['date_created']=date($this->config->item('display_date_format_email'),strtotime($pieces[0]));
                    $request['date']= date($this->config->item('display_date_format_email'),strtotime($request['date']));
                }
                $request['time_start']= date($this->config->item('display_time_format_email'),strtotime($request['time_start']));
                $request['time_end']= date($this->config->item('display_time_format_email'),strtotime($request['time_end']));                
                $request['current_shift'] = $shift_row->shift_calendar;

                // $request['url'] = base_url().'forms/out/detail/'.$this->input->post( 'record_id' );
                $request['url'] = '<a href="'.base_url().'">'.base_url().'</a>';

                // Load the template.            
                $this->load->model('template');
                $template = $this->template->get_module_template($this->module_id, 'out_request');
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

                        $this->template->queue(trim($row['email']), '', $template['subject'].' : '.$request['firstname']." ".$request['lastname'], $message);
                    }

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

        $this->db->join('user','user.employee_id=employee_out.employee_id');
        $this->db->join('form_status','form_status.form_status_id=employee_out.form_status_id');
        $this->db->join('user_company','user_company.company_id=user.company_id');
        $this->db->where('employee_out.employee_out_id', $this->input->post('record_id'));
        $request = $this->db->get('employee_out');

        if (IS_AJAX && !is_null($request) && $request->num_rows() > 0) {

             $mail_config = $this->hdicore->_get_config('outgoing_mailserver');
            if ($mail_config) {
                $recepients = array();
                $request = $request->row_array();

                $shift_row = $this->system->get_employee_worksched($request['employee_id'],date('Y-m-d',strtotime($request['date'])));

                $this->db->where("('".$request['date']."' >= date_from && '".$request['date']."' <= date_to)");
                // $this->db->where($request['date_to'].' BETWEEN date_from AND date_to');
                $tkp = $this->db->get('timekeeping_period');

                if($tkp->num_rows() > 0 && $tkp)
                    $request['payroll_cutoff'] = date($this->config->item('display_date_format_email'), strtotime($tkp->row()->period_cutoff));
                else
                    $request['payroll_cutoff'] = 'Cutoff not defined';

                // $request['here']=base_url().'forms/out/detail/'.$request['employee_out_id'];
                $request['here']=base_url();                
                $request['url'] = '<a href="'.base_url().'">'.base_url().'</a>';
                $pieces=explode(" ",$request['date_created']);
                if (CLIENT_DIR == 'firstbalfour'){
                    $request['date_created']=date($this->config->item('display_date_format_email_fb'),strtotime($pieces[0]));
                    $request['date']= date($this->config->item('display_date_format_email_fb'),strtotime($request['date']));                    
                }
                else{
                    $request['date_created']=date($this->config->item('display_date_format_email'),strtotime($pieces[0]));
                    $request['date']= date($this->config->item('display_date_format_email'),strtotime($request['date']));
                }
                $request['time_start']= date($this->config->item('display_time_format_email'),strtotime($request['time_start']));
                $request['time_end']= date($this->config->item('display_time_format_email'),strtotime($request['time_end']));                
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
                $template = $this->template->get_module_template($this->module_id, 'ut_status_email');
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
        $time_start = date('H:i:s', strtotime($this->input->post('time_start')));
        $time_end = date('H:i:s', strtotime($this->input->post('time_end')));        
        $date = date('Y-m-d',strtotime($this->input->post('date'))); 
        $employee_id = $this->input->post('employee_id');   
        $array_stack = $this->system->get_employee_rest_day($employee_id, $date_late); 

        $shit_sched = $this->system->get_employee_worksched($employee_id,date('Y-m-d', strtotime( $this->input->post('date') ) ), true );
       
        $shift_start = $shit_sched->shifttime_start;
        $shift_end = $shit_sched->shifttime_end;

        $day = date('D',strtotime($this->input->post('date')));

        if ($employee_id == ""):
            $err = true;
            $msg = "Please select employee.";
        else:
            if ( $policy_setup['working_days_only'] == 1 && ( in_array($day, $array_stack) ) ):
                $err = true;
                $msg = "Invalid undertime schedule. Please check date and hours work applied."; 
            else:
                $diff =  $this->hdicore->get_time_difference($date, date('Y-m-d H:i:s') );
                
                //get next working day
                $days_to_apply = $policy_setup['max_no_days_to_avail'];
                $working_days = 0;
                $next_working_day = $date;
                while($working_days < $days_to_apply){

                    if( $policy_setup['calendar_days'] == 0 ){
                        $next_working_day = $this->system->get_next_working_day($this->input->post('employee_id'), $next_working_day);
                    }
                    else{
                        $next_working_day = date('Y-m-d', strtotime( '+1 day' . $next_working_day));
                    }

                    $working_days++;
                }
                
                $last_day_to_file = $next_working_day . ' 23:59:59';
                $now = date('Y-m-d H:i:s');    
               
                if( $now > $last_day_to_file ):
                    $err = true;
                    $msg = "Your Undertime application is past its due date.";
                else:

                    $datetime_start = date('Y-m-d H:i:s', strtotime($date." ".$_POST['time_start']));
                    $datetime_end = date('Y-m-d H:i:s', strtotime($date." ".$_POST['time_end']));

                    //Check if different meridian
                    if( ( date('a', strtotime($shift_start) ) != date('a', strtotime($shift_end) ) ) 
                        && ( date('a', strtotime($_POST['time_start']) ) != date('a', strtotime($_POST['time_end']) ) )  ){
                        $datetime_end = date('Y-m-d H:i:s', strtotime('+1 day', strtotime($date." ".$_POST['time_end'])));
                    }

                    if( $datetime_end <=  $datetime_start ):
                        $err = true;
                        $msg = "Invalid undertime schedule. Kindly check date and hours work applied.";
                    else:
                        $working_hours =  $this->hdicore->get_time_difference($shift_start, $shift_end);
                        $half_day_hours = ($working_hours - 1) / 2;
                        $undertime_hours = $this->hdicore->get_time_difference($time_start, $time_end);
                       
                        if ( $policy_setup['filling_only_within_shift'] == 1 && ( $time_start < $shift_start || $time_end != $shift_end ) ):
                            $err = true;
                            $msg = "The time start or end should be in between your shift schedule.";
                        else:
                            //only approved application
                            $qry = "SELECT *
                            FROM {$this->db->dbprefix}employee_out
                            WHERE deleted = 0 AND employee_id = '{$employee_id }' AND form_status_id=3 AND
                            ( 
                                ('{$time_start}' BETWEEN time_start AND time_end) OR
                                ('{$time_end}' BETWEEN time_start AND time_end)                    
                            ) AND
                            date = '{$date}'";
                            $result = $this->db->query( $qry ); 
                            if ($result->num_rows() > 0):                    
                                $err = true;
                                $msg = "Undertime application has already been filed.";
                            else:
                                $err = false;
                                $msg = "";
                            endif; 
                        endif;   
                    endif;              
                endif;
            endif;
        endif;

        $this->load->view('template/ajax', 
            array('json' => 
                array('err' => $err, 'msg_type' => $msg)
            )
        );           
    }
}

/* End of file */
/* Location: system/application */
