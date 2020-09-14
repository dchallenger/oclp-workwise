<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require(APPPATH.'libraries/Formsmain.php');

class Cws extends Formsmain {

    function __construct() {
        parent::__construct();

        $this->load->model('forms/cws_model', 'cws');

        $this->default_sort_col = array('t0firstnamelastname asc');

    }

    function get_employee_sched( $return = false ){
        $this->cws->_get_employee_sched( $return );
    }

    function get_employee_sched_current( ){
        $response->emp = $this->system->get_employee_worksched(  $this->input->post('employee_id'), date('Y-m-d'),true);       

        $data['json'] = $response;
        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);        
    }

    function ajax_save() {

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
        
        //check if you already have an approved CWS on a specified date
        $dates_affected = $this->system->get_affected_dates( $this->input->post('employee_id'), $this->input->post('date_from'), $this->input->post('date_to'), true );
        
        $this->db->where('employee_id',$this->input->post('employee_id'));
        $this->db->where('form_status_id','3');
        $cws_id=$this->db->get('employee_cws');
        $flag=0;
        if($cws_id->num_rows()>0){
            foreach($cws_id->result_array() as $approved_cws_id){
            $this->db->where('employee_cws_id',$approved_cws_id['employee_cws_id']);
            $date_allowed=$this->db->get('employee_cws_dates')->result_array();
                foreach($date_allowed as $date_checking){
                    foreach($dates_affected as $date){
                        $date_checking_val=date('Y-m-d', strtotime($date_checking['date']));
                        $date_affected_val=date('Y-m-d', strtotime($date['date']));
                        if($date_checking_val==$date_affected_val) $flag=1;
                    }
                }
            }
        }

        $to_check = true;

        if ($this->config->item('allow_cws_late_filing') == 0){
            if ($this->config->item('allowable_time_to_apply_cws') && $this->config->item('allowable_time_to_apply_cws') != '' && $this->config->item('allowable_time_to_apply_cws') != 0){
                // if (date('Y-m-d') >= date('Y-m-d',strtotime($this->input->post('date_to') . '+' . $this->config->item('allowable_time_to_apply_cws') .'days'))){
                   
                    $dates_affected = $this->system->get_affected_dates( $this->input->post('employee_id'), $this->input->post('date_from'), date('Y-m-d'), true, true );

                    if (count($dates_affected) > $this->config->item('allowable_time_to_apply_cws')){
                    $err = true;
                    $msg =  "Your application exceeded with the allowable time to apply."; 
                    $to_check = false;                      
                }
            }
            else{            
                if (date('Y-m-d') >= date('Y-m-d',strtotime($this->input->post('date_to')))){
                    $response->msg = "Your application exceeded the grace period.";
                    $response->msg_type = "error";
                    $data['json'] = $response;
                    $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
                    $to_check = false;
                }
            }
        }
        else {
            if (date('Y-m-d') > date('Y-m-d',strtotime($this->input->post('date_to')))):
                if ($this->config->item('allowable_time_to_apply_cws') && $this->config->item('allowable_time_to_apply_cws') != '' && $this->config->item('allowable_time_to_apply_cws') != 0){
                    $dates_affected = $this->system->get_affected_dates( $this->input->post('employee_id'), $this->input->post('date_from'), date('Y-m-d'), true, true );

                    if (count($dates_affected) > $this->config->item('allowable_time_to_apply_cws')){
                        $response->msg = "Your application exceeded with the allowable time to apply.";
                        $response->msg_type = "error";
                        $data['json'] = $response;
                        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
                        $to_check = false;                     
                    }
                } 
                else{                
                    if ($this->system->check_in_cutoff($this->input->post('date_to')) == 1):
                        $response->msg = "Next payroll cutoff not yet created in processing, please contact admin.";
                        $response->msg_type = "error";
                        $data['json'] = $response;
                        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
                        $to_check = false;
                    elseif ($this->system->check_in_cutoff($this->input->post('date_to')) == 2):
                        $response->msg = "Your Change Work Schedule application is no longer within the allowable time to apply.";
                        $response->msg_type = "error";
                        $data['json'] = $response;
                        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
                        $to_check = false;                
                    endif;
                }
            endif;
        }

        //Check if filling is exceeded the maximum number of cws
        if( $this->config->item('max_cws') > 0 ){

            $day_count = 0;

            $fmonth = date('m',strtotime($this->input->post('date_from')));
            $fyear = date('Y',strtotime($this->input->post('date_from')));
            $flast_day = date('t',strtotime($this->input->post('date_from')));

            $fstart_date = date('Y-m-d',strtotime($fyear.'-'.$fmonth.'-1'));
            $fend_date = date('Y-m-d',strtotime($fyear.'-'.$fmonth.'-'.$flast_day));

            $date_from = date('Y-m-d',strtotime($this->input->post('date_from')));
            $date_to = date('Y-m-d',strtotime($this->input->post('date_to')));


            //Check number of filed days in cws of the employee that is currently for approval and approve
            $this->db->where('employee_id',$this->input->post('employee_id'));
            $this->db->where_in('form_status_id',array(2,3));
            $this->db->where('( date_from >= "'.$fstart_date.'" AND date_to <= "'.$fend_date.'" )');
            $employee_cws = $this->db->get('employee_cws');

            foreach( $employee_cws->result() as $cws_info ){

                $cws_date_from = date('Y-m-d',strtotime($cws_info->date_from));
                $cws_date_to = date('Y-m-d',strtotime($cws_info->date_to));

                if( $cws_date_from <= $cws_date_to ){

                    while( $cws_date_from <= $cws_date_to ){

                        $cws_date_from = date('Y-m-d',strtotime('+1 day',strtotime($cws_date_from)));
                        $day_count++;

                    }

                }

            }

            //Check number of filed days of the current cws
            if( $date_from <= $date_to ){

                while( $date_from <= $date_to ){

                    $date_from = date('Y-m-d',strtotime('+1 day',strtotime($date_from)));
                    $day_count++;

                }

            }

            if( $day_count > $this->config->item('max_cws') ){

                $response->msg = "Your application exceeded the maximum number of cws.";
                $response->msg_type = "error";
                $data['json'] = $response;
                $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
                $to_check = false;

            }

        }

        if($this->config->item('cws_within_cutoff'))
        {
            if ($this->config->item('allowable_time_to_apply_cws') && $this->config->item('allowable_time_to_apply_cws') != '' && $this->config->item('allowable_time_to_apply_cws') != 0){
                // if (date('Y-m-d') >= date('Y-m-d',strtotime($this->input->post('date_to') . '+' . $this->config->item('allowable_time_to_apply_cws') .'days'))){
                    
                    $dates_affected = $this->system->get_affected_dates( $this->input->post('employee_id'), $this->input->post('date_from'), date('Y-m-d'), true, true );

                    if (count($dates_affected) > $this->config->item('allowable_time_to_apply_cws')){
                    $err = true;
                    $msg =  "Your application exceeded with the allowable time to apply."; 
                    $to_check = false;                       
                }
            } 
            else{             
                $d_to = date('Y-m-d',strtotime($this->input->post('date_to')));

                $p_period = $this->system->get_in_cut_off(date('Y-m-d'));

                if (empty($p_period)):
                    $response->msg = "Payroll cutoff not yet created in processing, please contact admin.";
                    $response->msg_type = "error";
                    $data['json'] = $response;
                    $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
                    $to_check = false;   
                else:

                    $period_date_from = date('Y-m-d',strtotime($p_period[0]['date_from']));        
                    $period_date_to = date('Y-m-d',strtotime($p_period[0]['date_to']));        

                    // if (($d_to > $period_date_from && $d_to > $period_date_to) || ($d_to < $period_date_from && $d_to < $period_date_to)):
                    if($period_date_from > $d_to && $period_date_to > $d_to):
                        $response->msg = "Your CWS application is no longer within the allowable time to apply.";
                        $response->msg_type = "error";
                        $data['json'] = $response;
                        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
                        $to_check = false; 
                    endif;                  

                endif;
            }
        }

        if ($this->system->check_if_range_all_holiday($this->input->post('date_from'),$this->input->post('date_to'),$this->input->post('employee_id'), true)){
            $response->msg = "Your CWS application falls on a holiday. Kindly check date applied.";
            $response->msg_type = "error";
            $data['json'] = $response;
            $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
            $to_check = false;
        }

        if ($to_check){
            if($flag==0)
            {
                $employee_id = $this->input->post("employee_id");
                $date_from = date('Y-m-d',strtotime($this->input->post('date_from')));
                $date_to = date('Y-m-d',strtotime($this->input->post('date_to')));                        
                //check number of filed CWS
                $dates_affected = $this->system->get_affected_dates( $this->input->post('employee_id'), $this->input->post('date_from'), $this->input->post('date_to') );
                // to change the schedule of rest day. this will only work if all dates affected of date_from and date_to is rest day.
                if(!$dates_affected)
                    $dates_affected = $this->_get_affected_restday($this->input->post('date_from'), $this->input->post('date_to'));

                $month = date('m', strtotime($this->input->post('date_from')));

/*                $qry = "SELECT *
                FROM {$this->db->dbprefix}employee_cws_dates a
                LEFT JOIN {$this->db->dbprefix}employee_cws b on a.employee_cws_id = b.employee_cws_id
                WHERE b.form_status_id = 3 AND b.deleted = 0 AND MONTH(a.date) = {$month}";
                $result = $this->db->query( $qry );
                $cws_filed = $result->num_rows();*/
                
                if ($date_from == "" && $date_to == ""){
                    $response->msg = "Date - This field is mandatory.";
                    $response->msg_type = "error";
                    $data['json'] = $response;
                    $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);                
                }
                else{
                    if ($this->input->post('record_id') <> -1){
                        parent::ajax_save();
                        $this->db->delete('employee_cws_dates', array( $this->key_field => $this->key_field_val ));
                        foreach( $dates_affected as $date ){
                            $data = array(
                                $this->key_field => $this->key_field_val,
                                'date' => date('Y-m-d', strtotime( $date['date'] ) )
                            );
                            $this->db->insert('employee_cws_dates', $data);
                        }                         
                    }
                    else{                
                        $numrows = $this->db->query("SELECT * FROM {$this->db->dbprefix}employee_cws WHERE employee_id = ".$employee_id."
                                                     AND  deleted = 0 AND form_status_id=3
                                                     AND ((date_from <= '".$date_from."' AND date_to >= '".$date_from."')
                                                     OR (date_from <= '".$date_to."' AND date_to >= '".$date_to."'))")->num_rows();
                        
                        if($numrows > 0){
                            $response->msg = "New work schedule has already been filed.";
                            $response->msg_type = "error";
                            $data['json'] = $response;
                            $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
                        }
                        else{
                            $emp = $this->system->get_employee_worksched(  $this->input->post('employee_id'), date('Y-m-d'));                           
                            $_POST['current_shift_calendar_id'] = $emp->shift_calendar_id;                            
                            parent::ajax_save();
                            $this->db->delete('employee_cws_dates', array( $this->key_field => $this->key_field_val ));
                            foreach( $dates_affected as $date ){
                                $data = array(
                                    $this->key_field => $this->key_field_val,
                                    'date' => date('Y-m-d', strtotime( $date['date'] ) )
                                );
                                $this->db->insert('employee_cws_dates', $data);
                            }                            
                        }
                    }
                }         
            }
            else{
                $response->msg = "New work schedule has already been filed.";
                $response->msg_type = "error";
                $data['json'] = $response;
                $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
            }        
        }


    }

    /**
     * Send the email to approvers.
     */
    function send_email() {
        $data['form_status_id'] = 2;
        $this->db->where('employee_cws_id', $this->input->post('record_id'));
        $this->db->update('employee_cws', $data);

        $this->db->join('user','user.employee_id=employee_cws.employee_id');
        $this->db->join('form_status','form_status.form_status_id=employee_cws.form_status_id');
        $this->db->join('user_company','user_company.company_id=user.company_id');
        $this->db->where('employee_cws.employee_cws_id', $this->input->post('record_id'));
        $request = $this->db->get('employee_cws');


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


                $this->db->where('shift_id',$request['shift_id']);
                $change_me=$this->db->get('timekeeping_shift')->row_array();
                $request['shift_id']=$change_me['shift'];

                $request['shift_id'] = ($request['shift_id'] != "" ? $request['shift_id'] : 'Rest Day');

                $this->db->where('shift_calendar_id',$request['current_shift_calendar_id']);
                $change_me=$this->db->get('timekeeping_shift_calendar')->row_array();
                $request['current_shift_calendar_id']=$change_me['shift_calendar'];

                // $request['here']=base_url().'forms/cws/detail/'.$request['employee_cws_id'];
                $request['here']=base_url();
                $pieces=explode(" ",$request['date_created']);

                if (CLIENT_DIR == 'firstbalfour'){
                    $request['date_created'] = date('M d, Y');
                    $request['date_from']= date($this->config->item('display_date_format_email_fb'),strtotime($request['date_from']));
                    $request['date_to']= date($this->config->item('display_date_format_email_fb'),strtotime($request['date_to']));                    
                }
                else{
                    $request['date_created'] = date('F d, Y');
                    $request['date_from']= date($this->config->item('display_date_format_email'),strtotime($request['date_from']));
                    $request['date_to']= date($this->config->item('display_date_format_email'),strtotime($request['date_to']));
                }

                $request['date_from_less'] = date($this->config->item('display_date_format_email'),strtotime('- 1 day',strtotime($request['date_from'])));              
                $this->db->where("'".date('Y-m-d', strtotime($request['date_from']))."' BETWEEN date_from AND date_to ", '', false);
                // $this->db->where($request['date_to'].' BETWEEN date_from AND date_to');
                $tkp = $this->db->get('timekeeping_period');

                if($tkp && $tkp->num_rows() > 0)
                    $request['payroll_cutoff'] = date($this->config->item('display_date_format_email'), strtotime($tkp->row()->period_cutoff));
                else
                    $request['payroll_cutoff'] = 'Cutoff not defined';

                $request['cutoff'] = $this->_get_cutoff($tkp);

                // $request['url'] = base_url().'forms/cws/detail/'.$this->input->post( 'record_id' );
                $request['url'] = '<a href="'.base_url().'">'.base_url().'</a>';
                
                // Load the template.            
                $this->load->model('template');
                $template = $this->template->get_module_template($this->module_id, 'new_cws_request');
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

        $this->db->join('user','user.employee_id=employee_cws.employee_id');
        $this->db->join('form_status','form_status.form_status_id=employee_cws.form_status_id');
        $this->db->join('user_company','user_company.company_id=user.company_id');
        $this->db->where('employee_cws.employee_cws_id', $record_id);
        $request = $this->db->get('employee_cws');

        if (IS_AJAX && !is_null($request) && $request->num_rows() > 0) {

             $mail_config = $this->hdicore->_get_config('outgoing_mailserver');
            if ($mail_config) {
                $recepients = array();
                $request = $request->row_array();

                $this->db->where('shift_id',$request['shift_id']);
                $change_me=$this->db->get('timekeeping_shift')->row_array();
                $request['shift_id']=$change_me['shift'];

                $request['shift_id'] = ($request['shift_id'] != "" ? $request['shift_id'] : 'Rest Day');

                $this->db->where('shift_calendar_id',$request['current_shift_calendar_id']);
                $change_me=$this->db->get('timekeeping_shift_calendar')->row_array();
                $request['current_shift_calendar_id']=$change_me['shift_calendar'];

                // $request['here']=base_url().'forms/cws/detail/'.$request['employee_cws_id'];
                $request['here']=base_url();
                $request['url'] = '<a href="'.base_url().'">'.base_url().'</a>';
                $pieces=explode(" ",$request['date_created']);
                if (CLIENT_DIR == 'firstbalfour'){
                    $request['date_created'] = date('M d, Y',strtotime($request['date_created']));
                    $request['date_from']= date($this->config->item('display_date_format_email_fb'),strtotime($request['date_from']));
                    $request['date_to']= date($this->config->item('display_date_format_email_fb'),strtotime($request['date_to']));                    
                }
                else{
                    $request['date_created'] = date('M d, Y',strtotime($request['date_created']));
                    $request['date_from']= date($this->config->item('display_date_format_email'),strtotime($request['date_from']));
                    $request['date_to']= date($this->config->item('display_date_format_email'),strtotime($request['date_to']));                    
                }

                $request['date_from_less'] = date($this->config->item('display_date_format_email'),strtotime('- 1 day',strtotime($request['date_from'])));              
                $this->db->where("'".date('Y-m-d', strtotime($request['date_from']))."' BETWEEN date_from AND date_to ", '', false);
                // $this->db->where($request['date_to'].' BETWEEN date_from AND date_to');
                $tkp = $this->db->get('timekeeping_period');

                if($tkp && $tkp->num_rows() > 0)
                    $request['payroll_cutoff'] = date($this->config->item('display_date_format_email'), strtotime($tkp->row()->period_cutoff));
                else
                    $request['payroll_cutoff'] = 'Cutoff not defined';

                $request['decline_remarks'] = $decline_remarks;

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

                if (CLIENT_DIR  == 'firstbalfour'){
                    $request['employee'] = $request['salutation']." ".$request['firstname']." ".$request['lastname'];
                    if ($request['aux'] != ''){
                        $request['employee'] = $request['salutation']." ".$request['firstname']." ".$request['lastname']." ".$request['aux'];
                    }
                }
                // Load the template.            
                $this->load->model('template');
                $template = $this->template->get_module_template($this->module_id, 'cws_status_email');
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
        $dates_affected = $this->system->get_affected_dates( $this->input->post('employee_id'), $this->input->post('date_from'), $this->input->post('date_to') );
        $this->db->where('employee_id',$this->input->post('employee_id'));
        $this->db->where('form_status_id','3');
        $cws_id=$this->db->get('employee_cws');
        $flag=0;
        if($cws_id->num_rows()>0){
            foreach($cws_id->result_array() as $approved_cws_id){
            $this->db->where('employee_cws_id',$approved_cws_id['employee_cws_id']);
            $date_allowed=$this->db->get('employee_cws_dates')->result_array();
                foreach($date_allowed as $date_checking){
                    foreach($dates_affected as $date){
                        $date_checking_val=date('Y-m-d', strtotime($date_checking['date']));
                        $date_affected_val=date('Y-m-d', strtotime($date['date']));
                        if($date_checking_val==$date_affected_val) $flag=1;
                    }
                }
            }
        }

        $to_check = true;
        if ($this->config->item('allow_cws_late_filing') == 0){
            if ($this->config->item('allowable_time_to_apply_cws') && $this->config->item('allowable_time_to_apply_cws') != '' && $this->config->item('allowable_time_to_apply_cws') != 0){
                // if (date('Y-m-d') >= date('Y-m-d',strtotime($this->input->post('date_to') . '+' . $this->config->item('allowable_time_to_apply_cws') .'days'))){
                  
                    $dates_affected = $this->system->get_affected_dates( $this->input->post('employee_id'), $this->input->post('date_from'), date('Y-m-d'), true, true );

                    if (count($dates_affected) > $this->config->item('allowable_time_to_apply_cws')){
                    $err = true;
                    $msg =  "Your application exceeded with the allowable time to apply."; 
                    $to_check = false;                      
                }
            }
            else{
                if (date('Y-m-d') >= date('Y-m-d',strtotime($this->input->post('date_to')))){
                    $err = true;
                    $msg = "Your application exceeded the grace period."; 
                    $to_check = false; 
        /*            if (!$this->system->check_in_current_cutoff($this->input->post('date_to'))){
                        $err = true;
                        $msg = "Your CWS application is no longer within the allowable time to apply.";
                    }*/                    
                }
            }
        }
        else{
            if ($this->config->item('allowable_time_to_apply_cws') && $this->config->item('allowable_time_to_apply_cws') != '' && $this->config->item('allowable_time_to_apply_cws') != 0){
                // if (date('Y-m-d') >= date('Y-m-d',strtotime($this->input->post('date_to') . '+' . $this->config->item('allowable_time_to_apply_cws') .'days'))){
 
                    $dates_affected = $this->system->get_affected_dates( $this->input->post('employee_id'), $this->input->post('date_from'), date('Y-m-d'), true, true );

                    if (count($dates_affected) > $this->config->item('allowable_time_to_apply_cws')){
                    $err = true;
                    $msg =  "Your application exceeded with the allowable time to apply."; 
                    $to_check = false;       
                }
            }            
        }

        if ($to_check){
            if($flag==0)
            {
                $employee_id = $this->input->post("employee_id");
                $date_from = date('Y-m-d',strtotime($this->input->post('date_from')));
                $date_to = date('Y-m-d',strtotime($this->input->post('date_to')));                        
                //check number of filed CWS
                $dates_affected = $this->system->get_affected_dates( $this->input->post('employee_id'), $this->input->post('date_from'), $this->input->post('date_to') );
                $month = date('m', strtotime($this->input->post('date_from')));

/*                $qry = "SELECT *
                FROM {$this->db->dbprefix}employee_cws_dates a
                LEFT JOIN {$this->db->dbprefix}employee_cws b on a.employee_cws_id = b.employee_cws_id
                WHERE b.form_status_id = 3 AND b.deleted = 0 AND MONTH(a.date) = {$month}";
                $result = $this->db->query( $qry );
                $cws_filed = $result->num_rows();*/
                
                if ($date_from == "" && $date_to == ""){
                    $err = true;
                    $msg = "Date - This field is mandatory.";
                }
                else{
                    $numrows = $this->db->query("SELECT * FROM {$this->db->dbprefix}employee_cws WHERE employee_id = ".$employee_id."
                                                 AND  deleted = 0 AND form_status_id=3
                                                 AND ((date_from <= '".$date_from."' AND date_to >= '".$date_from."')
                                                 OR (date_from <= '".$date_to."' AND date_to >= '".$date_to."'))")->num_rows();
                    
                    if($numrows > 0){
                        $err = true;
                        $msg = "New work schedule has already been filed.";
                    }
                    else{
                        $err = false;
                        $msg = "";
                    }
                }        
            }
            else{
                $err = true;
                $msg = "New work schedule has already been filed.";
            } 
        } 

        $this->load->view('template/ajax', 
            array('json' => 
                array('err' => $err, 'msg_type' => $msg)
            )
        );          
    }    

    private function _get_affected_restday($date_from, $date_to)
    {
        $start_date = date('Y-m-d', strtotime($date_from));
        $end_date = date('Y-m-d', strtotime($date_to));
        $days = array();
        $days_ctr = 0;

        while( $start_date <= $end_date ){
            $days[$days_ctr]['date'] = date('m/d/Y', strtotime($start_date));
            //added just to make sure that everything will still work on get_affected_dates
            // $days[$days_ctr]['employee_leave_date_id'] = '0';
            $days_ctr++;
            $start_date = date('Y-m-d', strtotime($start_date . ' +1day') );
        }
        return $days;
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

    function quick_edit(){
        parent::quick_edit( $this->module_link );
    }
}

/* End of file */
/* Location: system/application */
