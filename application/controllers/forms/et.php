<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require(APPPATH.'libraries/Formsmain.php');

class Et extends Formsmain {

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
        $date_late = date('Y-m-d',strtotime($this->input->post('datelate'))); 
        $time_late = date('H:i:s',strtotime($this->input->post('time')));         
        $employee_id = $this->input->post('employee_id');
        $policy_setup = $this->system->get_policy_form_setup($employee_id,15,$date_late);


        $diff =  $this->hdicore->get_time_difference($date_late, date('Y-m-d H:i:s') );
        $row_work_sched = $this->system->get_employee_worksched_shift($employee_id, $date_late); 

        //get next working day
        $days_to_apply = $policy_setup['max_no_days_to_avail'];
        $working_days = 0;
        $next_working_day =  $date_late;
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

        
        if ($this->system->check_cutoff_policy_forms($employee_id,1,15,$date_late) == 1){
            $response->msg = "Next payroll cutoff not yet created in processing, please contact HRA.";
            $response->msg_type = "error";
            $data['json'] = $response;
            $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);   
        }
        else{
            if ($this->system->check_cutoff_policy_forms($employee_id,1,15,$date_late) == 2){
                $response->msg = 'Your Excuse Tardiness application is no longer within the allowable time to apply.';
                $response->msg_type = "error";
                $data['json'] = $response;
                $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
            }
            else{

                if( $now > $last_day_to_file ){
                    $response->msg = "Your Excused Tardiness application is past its due date.";
                    $response->msg_type = "attention";
                    $data['json'] = $response;
                    $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
                }else{
                    if (  $policy_setup['filling_only_within_shift'] == 1 && ( $time_late < $row_work_sched->shifttime_start || $time_late > $row_work_sched->shifttime_end ) ){
                        $response->msg = "Invalid excused tardiness schedule. Kindly check hours work applied.";
                        $response->msg_type = "attention";
                        $data['json'] = $response;
                        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);                                       
                    }
                    else{            
                        $result = $this->system->holiday_check($date_late,$employee_id);
                        if ( $policy_setup['working_days_only'] == 1 && !empty($result)){
                            $response->msg = "Invalid date. This is a holiday schedule.";
                            $response->msg_type = "attention";
                            $data['json'] = $response;
                            $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
                        }
                        else{
                            if( $this->input->post('time_difference') > $policy_setup['max_no_hrs_to_avail']){
                                $response->msg = "Time applied for excused tardiness exceeds 4 hours, Please apply for half day leave instead.";
                                $response->msg_type = "error";
                                $data['json'] = $response;
                                $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);                        
                            }
                            else{
                                if ($this->input->post('record_id') <> -1){
                                    parent::ajax_save(); 
                                }
                                else{
                                    $qry = "SELECT *
                                    FROM {$this->db->dbprefix}employee_et
                                    WHERE deleted = 0 AND form_status_id=3 AND employee_id = '{$employee_id }' AND datelate = '{$date_late}'";
                                    $result = $this->db->query( $qry );
                                    if ($result->num_rows() > 0):                    
                                        $response->msg = "Request for Excused Tardiness has already been filed.";
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

    /**
     * Send the email to approvers.
     */
    function send_email() {
        $this->db->join('user','user.employee_id=employee_et.employee_id');
        $this->db->join('form_status','form_status.form_status_id=employee_et.form_status_id');
        $this->db->join('user_company','user_company.company_id=user.company_id');
        $this->db->where('employee_et.employee_et_id', $this->input->post('record_id'));
        $request = $this->db->get('employee_et');

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

                // $request['here']=base_url().'forms/et/detail/'.$request['employee_et_id'];
                $request['here']=base_url();

                $date_late = $request['datelate'] . ' ' . $request['time'];
                $shift_row = $this->system->get_employee_worksched($request['employee_id'],$request['datelate']);

                $pieces=explode(" ",$request['date_created']);
                if (CLIENT_DIR == 'firstbalfour'){
                    $request['date_created']=date($this->config->item('display_date_format_email_fb'),strtotime($pieces[0]));
                    $request['datelate']= date($this->config->item('display_datetime_format_email_fb'), strtotime($date_late));                                    
                }
                else{
                    $request['date_created']=date($this->config->item('display_date_format_email'),strtotime($pieces[0]));
                    $request['datelate']= date($this->config->item('display_datetime_format_email'), strtotime($date_late));                
                }
                $request['current_shift'] = $shift_row->shift_calendar;
                $this->db->where("'".date('Y-m-d', strtotime($request['datelate']))."' BETWEEN date_from AND date_to ", '', false);
                // $this->db->where($request['date_to'].' BETWEEN date_from AND date_to');
                $tkp = $this->db->get('timekeeping_period');

                if($tkp && $tkp->num_rows() > 0)
                    $request['payroll_cutoff'] = date($this->config->item('display_date_format_email'), strtotime($tkp->row()->period_cutoff));
                else
                    $request['payroll_cutoff'] = 'Cutoff not defined';

                $request['cutoff'] = $this->_get_cutoff($tkp);

                // $request['url'] = base_url().'forms/et/detail/'.$this->input->post( 'record_id' );
                $request['url'] = '<a href="'.base_url().'">'.base_url().'</a>';
                                    
                // Load the template.            
                $this->load->model('template');
                $template = $this->template->get_module_template($this->module_id, 'et_request');
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

        $this->db->join('user','user.employee_id=employee_et.employee_id');
        $this->db->join('form_status','form_status.form_status_id=employee_et.form_status_id');
        $this->db->join('user_company','user_company.company_id=user.company_id');
        $this->db->where('employee_et.employee_et_id', $this->input->post('record_id'));
        $request = $this->db->get('employee_et');

        if (IS_AJAX && !is_null($request) && $request->num_rows() > 0) {

             $mail_config = $this->hdicore->_get_config('outgoing_mailserver');
            if ($mail_config) {
                $recepients = array();
                $request = $request->row_array();

                // $request['here']=base_url().'forms/et/detail/'.$request['employee_et_id'];
                $request['here']=base_url();
                $request['url'] = '<a href="'.base_url().'">'.base_url().'</a>';
                $date_late = $request['datelate'] . ' ' . $request['time'];
                $shift_row = $this->system->get_employee_worksched($request['employee_id'],$request['datelate']);

                $pieces=explode(" ",$request['date_created']);
                if (CLIENT_DIR == 'firstbalfour'){
                    $request['date_created']=date($this->config->item('display_date_format_email_fb'),strtotime($pieces[0]));
                    $request['datelate']= date($this->config->item('display_datetime_format_email_fb'), strtotime($date_late));                                    
                }
                else{
                    $request['date_created']=date($this->config->item('display_date_format_email'),strtotime($pieces[0]));
                    $request['datelate']= date($this->config->item('display_datetime_format_email'), strtotime($date_late));                
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
                $template = $this->template->get_module_template($this->module_id, 'et_status_email');
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
        $date_late = date('Y-m-d',strtotime($this->input->post('datelate'))); 
        $time_late = date('H:i:s',strtotime($this->input->post('timelate'))); 
        $employee_id = $this->input->post('employee_id');        
        $row_work_sched = $this->system->get_employee_worksched($employee_id, $date_late, true); 

        $policy_setup = $this->system->get_policy_form_setup($employee_id,15,$date_late);

        if ($employee_id == ""):
            $err = true;
            $msg = "Please select employee.";
        else:
            if ( $policy_setup['working_days_only'] == 1 && ( $row_work_sched->shift_id == 0 || $row_work_sched->shift_id == 1) ):
                $err = true;
                $msg = "Invalid date. This is a rest day schedule.";            
            else:
                $diff =  $this->hdicore->get_time_difference($date_late, date('Y-m-d H:i:s') );
                
                //get next working day
                $days_to_apply = $policy_setup['max_no_days_to_avail'];
                $working_days = 0;
                $next_working_day =  $date_late;
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

                if( $now >  $last_day_to_file ):
                    $err = true;
                    $msg = "Your Excused Tardiness application is past its due date.";
                else:                 
                    $result = $this->system->holiday_check($date_late,$employee_id);                   
                    if (  $policy_setup['working_days_only'] == 1 && ( !empty($result) ) ):
                        $err = true;
                        $msg = "Invalid date. This is a holiday schedule.";
                    else:                    
                        $qry = "SELECT *
                        FROM {$this->db->dbprefix}employee_et
                        WHERE deleted = 0 AND form_status_id=3 AND employee_id = '{$employee_id }' AND datelate = '{$date_late}'";
                        $result = $this->db->query( $qry );
                        if ($result->num_rows() > 0):
                            $err = true;
                            $msg = "Request for Excused Tardiness has already been filed.";
                        else:
                            $err = false;
                            $msg = "";
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

    function get_worksched(){
        $date = date('Y-m-d',strtotime($this->input->post('date'))); 
        $employee_id = $this->input->post('employee_id');         
        $emp = $this->system->get_employee_worksched($employee_id, $date, true);        
        $where = array('shift_id' => $emp->shift_id);

        $shift = $this->db->get_where('timekeeping_shift', $where);
        $shift = $shift->row();

        if ($this->input->post('time')):
            $time_diff = ((strtotime($this->input->post('time')) - strtotime($shift->shifttime_start)) / 60) / 60;
        else:
            $time_diff = 0;
        endif;

        $this->load->view('template/ajax', 
            array('json' => 
                array('shift' => $shift->shift, 'start_shift' => $shift->shifttime_start, 'end_shift' => $shift->shifttime_end, 'time_diff' => $time_diff)
            )
        );       
        //$response->shift->shifttime_end = date('g:i a', strtotime($response->shift->shifttime_end));
    }

    function get_worksched_detail(){      
        $employee_et_id  = $this->input->post('record_id');
        $record = $this->db->get_where('employee_et', array('employee_et_id'=>$this->input->post('record_id')))->row();
        $date = date('Y-m-d',strtotime($record->datelate)); 
        $employee_id = $record->employee_id;  

        $emp = $this->system->get_employee_worksched($employee_id, $date, true);        
        $where = array('shift_id' => $emp->shift_id);

        $shift = $this->db->get_where('timekeeping_shift', $where);
        $shift = $shift->row();

        $this->load->view('template/ajax', 
            array('json' => 
                array('shift' => $shift->shift, 'start_shift' => $shift->shifttime_start, 'end_shift' => $shift->shifttime_end, 'time_diff' => $time_diff)
            )
        );   
    }  

     function delete() {

        $record = $this->db->get_where('employee_et', array('employee_et_id'=>$this->input->post('record_id')))->row();

        if( $record->form_status_id != 3 ){

            parent::delete();

        }
        else{

            $response['msg'] = 'Cannot delete approved form';
            $response['msg_type'] = 'attention';        
            $data['json'] = $response;
            $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);

        }
    }
}

/* End of file */
/* Location: system/application */
