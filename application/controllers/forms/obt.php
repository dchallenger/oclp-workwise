<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require(APPPATH.'libraries/Formsmain.php');

class Obt extends Formsmain {

    function __construct() {
        parent::__construct();
        $this->load->model('forms/obt_model', 'obt');
    }

    function detail()
    {
        my_controller::detail();

        //additional module detail routine here
        $data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/js/detailview.js"></script>';
        $data['content'] = 'forms/detailview_obt';

        $this->db->select('date,cancelled,time_start, time_end, date_cancelled,remarks');
        $this->db->where( array( $this->key_field => $this->key_field_val ) );
        $result = $this->db->get('employee_obt_date');
        
        if ($result->num_rows() > 0) {
            $data['dates_affected'] = $result->result_array();
        }
        //other views to load
        $data['views'] = array();
        $data['rec'] = $rec = $this->db->get_where( $this->module_table, array( $this->key_field => $this->input->post('record_id') ) )->row();
        if( $rec->form_status_id == 2 ){
            if( $rec->employee_id == $this->user->user_id ){
                $data['buttons'] = 'template/detail-no-buttons';
            }
            else if($this->user_access[$this->module_id]['post'] == 1){
                // check if hr is approver
                $approver = $this->db->get_where('form_approver', array('approver' => $this->user->user_id, 'record_id' => $this->key_field_val));
                $approver = $approver->row();
                if( $approver->status == 2 ){
                    $data['buttons'] = 'forms/approve-button';
                }
                else{
                    if (CLIENT_DIR == 'firstbalfour'){
                        $data['buttons'] = 'forms/approve-button';
                    }
                }
            } else if($this->user_access[$this->module_id]['publish'] == 1) {
                $data['buttons'] = 'template/detail-no-buttons';
                // check if user(publish) is approver
                $approver = $this->db->get_where('form_approver', array('approver' => $this->user->user_id, 'record_id' => $this->key_field_val));
                $approver = $approver->row();
                if( $approver->status == 2 ){
                    $data['buttons'] = 'forms/approve-button';
                }
            } else{
                //check for approver buttons
                $approver = $this->db->get_where('form_approver', array('approver' => $this->user->user_id, 'record_id' => $this->key_field_val, 'status' => 2));

                if( $approver->num_rows() == 0 && !($this->is_admin || $this->is_superadmin)){
                    $this->session->set_flashdata( 'flashdata', 'You do not have sufficient privilege to view the requested record! Please contact the System Administrator.***' );
                    redirect( base_url().$this->module_link );  
                }

                $approver = $approver->row();
                if( $approver->status == 2 ){
                    $data['buttons'] = 'forms/approve-button';
                }
                else{
                    $data['buttons'] = 'template/detail-no-buttons';
                }
            }
        }
        $this->db->order_by('sequence', 'asc');
        $approvers = $this->db->get_where( 'form_approver', array('module_id' => $this->module_id, 'record_id' => $this->key_field_val));
        foreach( $approvers->result() as $row ){
            $data['approvers'][] = array(
                'approver' => $row->approver,
                'sequence' => $row->sequence,
                'condition' => $row->condition,
                'focus' => $row->focus,
                'status' => $row->status
            );
        }
        // to prevent edit button on employee's with publish
        if( $rec->form_status_id == 3 && $this->user_access[$this->module_id]['publish']){
            $data['buttons'] = 'template/detail-no-buttons';
        }

        if( $rec->form_status_id == 3 && $rec->employee_id != $this->user->user_id && $this->user_access[$this->module_id]['cancel'] == 1 ){
            $data['buttons'] = 'forms/cancel-button';
        }

        if( $rec->form_status_id == 3 && $rec->employee_id == $this->user->user_id ){
            $data['buttons'] = 'template/detail-no-buttons';
        }

        if( $rec->form_status_id > 3 || ( $rec->form_status_id == 1 && $rec->employee_id != $this->user->user_id ) ) $data['buttons'] = 'template/detail-no-buttons';

        if( $rec->form_status_id == 6 && $rec->employee_id != $this->user->user_id && ($this->user_access[$this->module_id]['post'] == 1 || $this->user_access[$this->module_id]['hr_health'] == 1) ){
            $data['buttons'] = 'forms/validate-buttons';
        }
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

    function _default_grid_actions($module_link = "", $container = "", $row = array()) {

        $rec = $this->db->get_where( $this->module_table, array( $this->key_field => $row[$this->key_field] ) )->row();

        // set default
        if ($module_link == "")
            $module_link = $this->module_link;
        if ($container == "")
            $container = "jqgridcontainer";

        // Right align action buttons.
        $actions = '<span class="icon-group">';

        if ($this->_can_approve( $rec )) {
            $actions .= '<a class="icon-button icon-16-approve approve-single" tooltip="Approve" container="' . $container . '" module_link="' . $module_link . '" href="javascript:void(0)"></a>';
        }

        if ( $this->_can_decline( $rec ) ) {
            $actions .= '<a class="icon-button '.(CLIENT_DIR == 'hdi' || CLIENT_DIR == 'basf' ? 'icon-16-disapprove' : 'icon-16-cancel').' decline-single" tooltip="Disapprove" container="' . $container . '" module_link="' . $module_link . '" href="javascript:void(0)"></a>';
        }

        if ( $this->_can_cancel( $rec ) && $rec->employee_id != $this->user->user_id ) {
            $qry = $this->db->query("SELECT * FROM {$this->db->dbprefix}employee_obt_date WHERE employee_obt_id = '{$rec->employee_obt_id}'");
            if($qry->num_rows() > 1) {
                $actions .= '<a class="icon-button icon-16-cancel cancel-single_obt" form_status="many" tooltip="Cancel" container="' . $container . '" module_link="' . $module_link . '" href="javascript:void(0)"></a>';
            } else {
                $actions .= '<a class="icon-button icon-16-cancel cancel-single_obt" form_status="one" tooltip="Cancel" container="' . $container . '" module_link="' . $module_link . '" href="javascript:void(0)"></a>';
            }
        }

        if ($this->user_access[$this->module_id]['view']) {
            $actions .= '<a class="icon-button icon-16-info" module_link="' . $module_link . '" tooltip="View" href="javascript:void(0)"></a>';
        }

        if ($this->user_access[$this->module_id]['print']) {
            $actions .= '<a class="icon-button icon-16-print print-record" tooltip="Print" href="javascript:void(0)" module_link="' . $module_link . '" ></a>';
        }
        if ($this->user_access[$this->module_id]['edit'] && $rec->form_status_id == 1 && $rec->employee_id == $this->user->user_id) {
            $actions .= '<a class="icon-button icon-16-edit" tooltip="Edit" href="javascript:void(0)" module_link="' . $module_link . '" ></a>';
        }

        if (CLIENT_DIR == 'hdi'){
            if ($this->user_access[$this->module_id]['delete']  && in_array( $rec->form_status_id, array(1) ) ) {
                $actions .= '<a class="icon-button icon-16-delete delete-single" tooltip="Delete" container="' . $container . '" module_link="' . $module_link . '" href="javascript:void(0)"></a>';
            }
        }
        else{
            if ($this->user_access[$this->module_id]['delete']  && in_array( $rec->form_status_id, array(1,2) ) ) {
                $actions .= '<a class="icon-button icon-16-delete delete-single" tooltip="Delete" container="' . $container . '" module_link="' . $module_link . '" href="javascript:void(0)"></a>';
            }
        }

        $actions .= '</span>';

        return $actions;
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
        $employee_id = $this->input->post("employee_id");
        $form_status_id = $this->input->post("form_status_id");
        $date_from = date('Y-m-d',strtotime($this->input->post('date_from')));
        $date_to = date('Y-m-d',strtotime($this->input->post('date_to')));            
        // $time_start = $this->input->post('time_start');
        // $time_end = $this->input->post('time_end');
        // $time_start_hh_mm = date('H:i:s',strtotime($time_start));
        // $time_end_hh_mm = date('H:i:s',strtotime($time_end));

        $to_check = true;

        // if (strtotime($date_from) < strtotime(date('Y-m-d'))){
            if ($this->system->check_cutoff_policy_forms($employee_id,$form_status_id,8,$date_from, $date_to) == 1):
                $response->msg = "Next payroll cutoff not yet created in processing, please contact HRA.";
                $response->msg_type = "error";
                $data['json'] = $response;
                $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
                $to_check = false;
            elseif ($this->system->check_cutoff_policy_forms($employee_id,$form_status_id,8,$date_from, $date_to) == 2):
                $response->msg = 'Your business trip application is no longer within the allowable time to apply.';
                $response->msg_type = "error";
                $data['json'] = $response;
                $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
                $to_check = false;
            endif;

        // }


        $err = $this->system->check_same_erlier_date_time($date_from.' '.$time_start,$date_to.' '.$time_end);
/*        if ($err == 1){
            $response->msg = "Your Official Business application are same date and time.";
            $response->msg_type = "error";
            $data['json'] = $response;
            $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
            $to_check = false;
        }*/
        if ($err == 2){
            $response->msg = "Your Official Business application date to is set as earlier against date from.";
            $response->msg_type = "error";
            $data['json'] = $response;
            $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
            $to_check = false;
        }    
        elseif ($err == 3){
            $response->msg = "Your Official Business application time to is set as earlier against time from.";
            $response->msg_type = "error";
            $data['json'] = $response;
            $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
            $to_check = false;
        }              

        if ($to_check){
            if ($this->input->post('record_id') <> -1){
                parent::ajax_save(); 
            }
            else{
                if (trim($employee_id) == ""){
                    $response->msg = "Employee - This field is mandatory";
                    $response->msg_type = "error";
                    $data['json'] = $response;
                    $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
                }
                else{
                    $numrows = $this->db->query("SELECT * FROM {$this->db->dbprefix}employee_obt WHERE employee_id = ".$employee_id."
                                                 AND  deleted = 0 AND form_status_id=3
                                                 AND (((date_from <= '".$date_from."' AND date_to >= '".$date_from."')
                                                 OR (date_from <= '".$date_to."' AND date_to >= '".$date_to."')))")->num_rows();
                                                 // AND ((time_start <= '".$time_start_hh_mm."' AND time_end >= '".$time_start_hh_mm."') 
                                                 // OR (time_start <= '".$time_end_hh_mm."' AND time_end >= '".$time_end_hh_mm."'))) ")->num_rows();
                    if($numrows > 0){
                        $response->msg = "Official business application has already been filed.";
                        $response->msg_type = "error";
                        $data['json'] = $response;
                        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
                    }
                    else{
                        parent::ajax_save();
                    }
                }
            }         
        }
    }

    function after_ajax_save(){
        if ($this->get_msg_type() == 'success') {
            $this->db->delete('employee_obt_date', array('employee_obt_id' => $this->key_field_val));
            $dates = $_POST['dates'];
            $time_start = $_POST['time_start'];
            $time_end = $_POST['time_end'];
            foreach($dates as $index => $dstart) {
                $this->db->insert('employee_obt_date', array('employee_obt_id' => $this->key_field_val, 'date' => date('Y-m-d', strtotime($dstart)),'time_start'=>date('H:i:s',strtotime($time_start[$index])),'time_end'=>date('H:i:s',strtotime($time_end[$index]))));
            }
        }

        parent::after_ajax_save();   
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
    function send_email($record_id=false) {
        if(!$record_id) {
            $record_id = $this->input->post('record_id');
        }
        $this->db->join('user','user.employee_id=employee_obt.employee_id');
        $this->db->join('form_status','form_status.form_status_id=employee_obt.form_status_id');
        $this->db->join('user_company','user_company.company_id=user.company_id');
        $this->db->where('employee_obt.employee_obt_id', $record_id);
        $request = $this->db->get('employee_obt');

        if (IS_AJAX && !is_null($request) && $request->num_rows() > 0) {
            $mail_config = $this->hdicore->_get_config('outgoing_mailserver');
            if ($mail_config) {
                $recepients = array();
                $request = $request->row_array();

                $this->db->where('record_id', $record_id);
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

                $this->db->where("('".$request['date_from']."' >= date_from && '".$request['date_to']."' <= date_to)");
                // $this->db->where($request['date_to'].' BETWEEN date_from AND date_to');
                $tkp = $this->db->get('timekeeping_period');

                if($tkp->num_rows() > 0 && $tkp)
                    $request['payroll_cutoff'] = date($this->config->item('display_date_format_email'), strtotime($tkp->row()->period_cutoff));
                else
                    $request['payroll_cutoff'] = 'Cutoff not defined';

                $request['cutoff'] = $this->_get_cutoff($tkp);

                // $request['here'] = base_url().'forms/obt/detail/'.$request['employee_obt_id'];
                $request['here'] = base_url();
                $pieces=explode(" ",$request['date_created']);
                if (CLIENT_DIR == 'firstbalfour'){
                    $request['date_created']=date($this->config->item('display_date_format_email_fb'),strtotime($pieces[0]));
                    $request['date_from']= date($this->config->item('display_date_format_email_fb'),strtotime($request['date_from']));
                    $request['date_to']= date($this->config->item('display_date_format_email_fb'),strtotime($request['date_to']));
                }
                else{
                    $request['date_created']=date($this->config->item('display_date_format_email'),strtotime($pieces[0]));
                    $request['date_from']= date($this->config->item('display_date_format_email'),strtotime($request['date_from']));
                    $request['date_to']= date($this->config->item('display_date_format_email'),strtotime($request['date_to']));
                }
                $request['time_start']= date($this->config->item('display_time_format_email'),strtotime($request['time_start']));
                $request['time_end']= date($this->config->item('display_time_format_email'),strtotime($request['time_end']));
                
                // $request['url'] = base_url().'forms/obt/detail/'.$record_id;
                $request['url'] = '<a href="'.base_url().'">'.base_url().'</a>';

                $date_time_html = '';
                $obt_date_time_result = $this->db->get_where('employee_obt_date',array("employee_obt_id"=>$record_id));
                if ($obt_date_time_result && $obt_date_time_result->num_rows() > 0){
                    foreach ($obt_date_time_result->result() as $row) {
                        $date_time_html .= date($this->config->item('display_date_format_email'),strtotime($row->date)) . ' ';
                        $date_time_html .= date($this->config->item('display_time_format_email'),strtotime($row->time_start)) . ' to ';
                        $date_time_html .= date($this->config->item('display_date_format_email'),strtotime($row->date)) . ' ';
                        $date_time_html .= date($this->config->item('display_time_format_email'),strtotime($row->time_end));
                        $date_time_html .= '</br>';                        
                    }
                }
                $request['date_time'] = $date_time_html;
                // Load the template.            
                $this->load->model('template');
                $template = $this->template->get_module_template($this->module_id, 'obt_request');
                $message = $this->template->prep_message($template['body'], $request);

                if( is_array( $app_array ) && sizeof($app_array) > 0 ){
                    $this->db->where_in('user_id', $app_array);
                    $result = $this->db->get('user');

                    $result = $result->result_array();

                    foreach ($result as $row) {
                        $recepients[] = trim( $row['email'] );
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
                    $this->db->where(array('module_id' => $this->module_id, 'record_id' => $record_id));     
                    $this->db->update('form_approver', array('status' => 2) );
                    
                }
            }
        } else {
            $this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
            redirect(base_url() . $this->module_link);
        }
    }



    function send_status_email( $record_id, $status_id, $decline_remarks = false ){

        if(empty($record_id)) {
            $employee_obt_id_post = $this->input->post('record_id');
        } else {
            $employee_obt_id_post = $record_id;
        }
        $this->db->join('user','user.employee_id=employee_obt.employee_id');
        $this->db->join('form_status','form_status.form_status_id=employee_obt.form_status_id');
        $this->db->join('user_company','user_company.company_id=user.company_id');
        $this->db->where('employee_obt.employee_obt_id', $employee_obt_id_post);
        $request = $this->db->get('employee_obt');

        if (IS_AJAX && !is_null($request) && $request->num_rows() > 0) {

             $mail_config = $this->hdicore->_get_config('outgoing_mailserver');
            if ($mail_config) {
                $recepients = array();
                $request = $request->row_array();

                $this->db->where("('".$request['date_from']."' >= date_from && '".$request['date_to']."' <= date_to)");
                // $this->db->where($request['date_to'].' BETWEEN date_from AND date_to');
                $tkp = $this->db->get('timekeeping_period');

                if($tkp->num_rows() > 0 && $tkp)
                    $request['payroll_cutoff'] = date($this->config->item('display_date_format_email'), strtotime($tkp->row()->period_cutoff));
                else
                    $request['payroll_cutoff'] = 'Cutoff not defined';

                // $request['here']=base_url().'forms/obt/detail/'.$request['employee_obt_id'];
                $request['here']=base_url();
                $request['url'] = '<a href="'.base_url().'">'.base_url().'</a>';
                $pieces=explode(" ",$request['date_created']);
                if (CLIENT_DIR == 'firstbalfour'){
                    $request['date_created']=date($this->config->item('display_date_format_email_fb'),strtotime($pieces[0]));
                    $request['date_from']= date($this->config->item('display_date_format_email_fb'),strtotime($request['date_from']));
                    $request['date_to']= date($this->config->item('display_date_format_email_fb'),strtotime($request['date_to']));                    
                }
                else{
                    $request['date_created']=date($this->config->item('display_date_format_email'),strtotime($pieces[0]));
                    $request['date_from']= date($this->config->item('display_date_format_email'),strtotime($request['date_from']));
                    $request['date_to']= date($this->config->item('display_date_format_email'),strtotime($request['date_to']));
                }
                $request['time_start'] = '';
                $request['time_end'] = '';
                // $request['time_start']= date($this->config->item('display_time_format_email'),strtotime($request['time_start']));
                // $request['time_end']= date($this->config->item('display_time_format_email'),strtotime($request['time_end']));

                switch($status_id){
                    case 3:
                        $request['status'] = "approved";
                    break;
                    case 4:
                        $request['status'] = "disapproved";
                    break;
                    case 5:
                        $request['status'] = "cancelled";
                        $employee_obt_id = $request['employee_obt_id'];
                        $obt_dates_detail = $this->db->get_where('employee_obt_date', array('deleted' => 0, 'employee_obt_id' => $request['employee_obt_id']))->result();
                        $dqry = $this->db->query("SELECT a.date, a.time_start, a.time_end, a.employee_obt_date_id, a.remarks, a.date_cancelled, a.cancelled FROM {$this->db->dbprefix}employee_obt_date a 
                                        WHERE a.employee_obt_id = '{$employee_obt_id}'")->result();
                        $html_detail = '<p>Inclusive Date/s Detail :</p>
                                        <table style="width:100%;border:1px solid gray;">
                                        <tr>
                                            <td style="width:20%;text-align:center;border-left:1px solid gray;border-right:1px solid gray;border-bottom:1px solid gray;border-top:1px solid gray;">Date</td>
                                            <td style="width:15%;text-align:center;border-left:1px solid gray;border-right:1px solid gray;border-bottom:1px solid gray;border-top:1px solid gray;">Time</td>
                                            <td style="width:15%;text-align:center;border-left:1px solid gray;border-right:1px solid gray;border-bottom:1px solid gray;border-top:1px solid gray;">Status</td>
                                            <td style="width:20%;text-align:center;border-left:1px solid gray;border-right:1px solid gray;border-bottom:1px solid gray;border-top:1px solid gray;">Date Cancelled</td>
                                            <td style="width:30%;text-align:center;border-left:1px solid gray;border-right:1px solid gray;border-bottom:1px solid gray;border-top:1px solid gray;">Remarks</td>
                                        </tr>';
                        foreach ($dqry as $date_affected) {   
                            if($date_affected->cancelled == 0) {                         
                                $html_detail .= '<tr><td style="text-align:center;border-left:1px solid gray;border-right:1px solid gray;border-bottom:1px solid gray;border-top:1px solid gray;">'.date($this->config->item('display_date_format_email'),strtotime($date_affected->date)) . '</td><td colspan="4" style="text-align:center;border-left:1px solid gray;border-right:1px solid gray;border-bottom:1px solid gray;border-top:1px solid gray;">' . date('h:i a',strtotime($date_affected->time_start)).' to '.date('h:i a',strtotime($date_affected->time_end)) . '</td></tr>';
                            } else {
                                $html_detail .= '<tr><td style="text-align:center;border-left:1px solid gray;border-right:1px solid gray;border-bottom:1px solid gray;border-top:1px solid gray;">'.date($this->config->item('display_date_format_email'),strtotime($date_affected->date)) . '</td><td style="text-align:center;border-left:1px solid gray;border-right:1px solid gray;border-bottom:1px solid gray;border-top:1px solid gray;">' . date('h:i a',strtotime($date_affected->time_start)).' to '.date('h:i a',strtotime($date_affected->time_end)) . '</td><td style="text-align:center;border-left:1px solid gray;border-right:1px solid gray;border-bottom:1px solid gray;border-top:1px solid gray;"><span class="red">Cancelled</span></td><td style="text-align:center;border-left:1px solid gray;border-right:1px solid gray;border-bottom:1px solid gray;border-top:1px solid gray;"><span class="blue"><i>Date : '.date($this->config->item('display_date_format_email'),strtotime($date_affected->date_cancelled)).'</i></span></td><td style="text-align:left;border-left:1px solid gray;border-right:1px solid gray;border-bottom:1px solid gray;border-top:1px solid gray;">'.nl2br($date_affected->remarks).'</td></tr>';
                            }
                        } 
                        $html_detail .= '</table>';
                        $request['detail_cancel'] = $html_detail;
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
                $template = $this->template->get_module_template($this->module_id, 'obt_status_email');
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
        $this->obt->_validation_check();    
    }

    function _set_search_all_query()
    {
        $value =  $this->input->post('searchString');
        $search_string = array();

        foreach($this->search_columns as $search)
        {
            $column = strtolower( $search['column'] );
            if(sizeof(explode(' as ', $column)) > 1){
                $as_part = explode(' as ', $column);
                $search['column'] = strtolower( trim( $as_part[0] ) );
            }

            if( $search['column'] == $this->db->dbprefix.'employee_obt.employee_id' ){
                $search['column'] = 'CONCAT('.$this->db->dbprefix.'user.firstname'.'," ",'.$this->db->dbprefix.'user.lastname'.')';
            }

            if( $search['column'] == 'CONCAT(time_start, " to ", time_end)' ){
                $search['column'] = 'CONCAT(DATE_FORMAT(time_start, "%I:%i %p"), " to ", DATE_FORMAT(time_end, "%I:%i %p"))';
            }

            if( $search['column'] == 'CONCAT(hr_employee_obt.date_from, " to ", hr_employee_obt.date_to)' ){
                 $search['column'] = 'CONCAT(DATE_FORMAT('.$this->db->dbprefix.'employee_obt.date_from,"%d %M %Y"), " to ", DATE_FORMAT('.$this->db->dbprefix.'employee_obt.date_to,"%d %M %Y"))';
            }

            $search_string[] = $search['column'] . ' LIKE "%'. $value .'%"' ;

        }
        $search_string = '('. implode(' OR ', $search_string) .')';

        return $search_string;
    }

    protected function _append_to_select()
    {
        $this->listview_qry .= ',user.firstname as t0firstnamemiddleinitiallastnameaux';
    }

    function _set_specific_search_query()
    {
        $field = $this->input->post('searchField');
        $operator =  $this->input->post('searchOper');
        $value =  $this->input->post('searchString');

        foreach( $this->search_columns as $search )
        {
            if($search['jq_index'] == $field) $field = $search['column'];
        }

        $field = strtolower( $field );
        if(sizeof(explode(' as ', $field)) > 1){
            $as_part = explode(' as ', $field);
            $field = strtolower( trim( $as_part[0] ) );
        }

        if( $field == $this->db->dbprefix.'employee_obt.employee_id' ){
            $field = 'CONCAT('.$this->db->dbprefix.'user.firstname'.'," ",'.$this->db->dbprefix.'user.lastname'.')';
        }

        if( $field == 'concat(time_start, " to ", time_end)' ){
            $field = 'CONCAT(DATE_FORMAT(time_start, "%I:%i %p"), " to ", DATE_FORMAT(time_end, "%I:%i %p"))';
        }

        if( $field == 'employee_obt.date_from' ){
             $field = 'CONCAT(DATE_FORMAT('.$this->db->dbprefix.'employee_obt.date_from,"%d %M %Y"), " to ", DATE_FORMAT('.$this->db->dbprefix.'employee_obt.date_to,"%d %M %Y"))';
        }

        if( $field == 't4form_status' ){
            $field = 't4.form_status';
        }

        switch ($operator) {
            case 'eq':
                return $field . ' = "'.$value.'"';
                break;
            case 'ne':
                return $field . ' != "'.$value.'"';
                break;
            case 'lt':
                return $field . ' < "'.$value.'"';
                break;
            case 'le':
                return $field . ' <= "'.$value.'"';
                break;
            case 'gt':
                return $field . ' > "'.$value.'"';
                break;
            case 'ge':
                return $field . ' >= "'.$value.'"';
                break;
            case 'bw':
                return $field . ' REGEXP "^'. $value .'"';
                break;
            case 'bn':
                return $field . ' NOT REGEXP "^'. $value .'"';
                break;
            case 'in':
                return $field . ' IN ('. $value .')';
                break;
            case 'ni':
                return $field . ' NOT IN ('. $value .')';
                break;
            case 'ew':
                return $field . ' LIKE "%'. $value  .'"';
                break;
            case 'en':
                return $field . ' NOT LIKE "%'. $value  .'"';
                break;
            case 'cn':
                return $field . ' LIKE "%'. $value .'%"';
                break;
            case 'nc':
                return $field . ' NOT LIKE "%'. $value .'%"';
                break;
            default:
                return $field . ' LIKE %'. $value .'%';
        }
    }

    function quick_edit(){
        parent::quick_edit( $this->module_link );
    }

    function get_employees()
    {
        if (is_null($this)) { 
            $ci =& get_instance();
            $ci->db->where('user.deleted', 0);
            $ci->db->where('resigned', 0);
            $ci->db->where('role_id <>', 1);
            $ci->db->join('employee', 'employee.user_id = user.user_id'); 
            $ci->db->order_by('user.firstname', 'ASC');
            return $ci->db->get('user')->result_array();
        }
                
        $this->db->where('user.deleted', 0);
        $this->db->where('resigned', 0);
        $this->db->where('role_id <>', 1);
        $this->db->join('employee', 'employee.user_id = user.user_id'); 
        $this->db->order_by('user.firstname', 'ASC');
        return $this->db->get('user')->result_array();
    }

    function get_affected_dates(){
        if (IS_AJAX) {
            $start_date = date('Y-m-d', strtotime($this->input->post('date_from')));
            $end_date = date('Y-m-d', strtotime($this->input->post('date_to')));
            $days = array();
            $days_ctr = 0;

            $userinfo = $this->db->get_where('employee',array('employee_id'=>$this->input->post('employee_id')))->row();

            while( $start_date <= $end_date ){
                //check if holidays
                $on_holiday = false;
                $holiday = $this->system->holiday_check( $start_date, $this->input->post('employee_id'), true);

                if( $holiday && $record->application_form_id != 5 ){
                    //check wether holiday applies to employee
                    
                    foreach( $holiday as $day ){
                        $on_holiday = true;

                        $where = array('employee_id' => $this->input->post('employee_id'), 'holiday_id' => $day['holiday_id']);
                        $emp_holiday = $this->db->get_where('holiday_employee', $where);
                        if( $emp_holiday->num_rows() > 0){
                            $on_holiday = true;
                        }

                        //additional checking for not legal holiday and base on location inputted //tirso
                        if (!$day['legal_holiday'] && $day['location_id'] <> ''){
                            $location_array = explode(',',$day['location_id']);
                            if (in_array($userinfo->location_id, $location_array)){
                                $on_holiday = true;
                            }
                            else{
                                $on_holiday = false;    
                            }
                        }
                    }
                }

                if( !$on_holiday ){
                    //get the work sched
                    $worksched = $this->system->get_employee_worksched(  $this->input->post('employee_id'), $start_date);
                    
                    if((isset($worksched->has_cws) && $worksched->has_cws)){
                        $days[$days_ctr]['date'] = date('m/d/Y', strtotime($start_date));
                        $days[$days_ctr]['date2'] = date('Y-m-d',strtotime($start_date));
                        $days[$days_ctr]['employee_leave_date_id'] = '0';

                        if( $this->input->post('record_id') != "-1" ){
                            $this->db->join('employee_obt_date','employee_obt.employee_obt_id = employee_obt_date.employee_obt_id','left');
                            $this->db->where('employee_obt_date.date',date('Y-m-d', strtotime($start_date)));
                            $this->db->where('employee_obt.employee_obt_id',$this->input->post('record_id'));
                            $leave_date_result = $this->db->get('employee_obt');

                            if( $leave_date_result->num_rows() > 0 ){
                                $leave_date_record = $leave_date_result->row();
                                $days[$days_ctr]['time_range'] = '<input id="time_start-'.$days[$days_ctr]['date2'].'" class="input-text" type="text" readonly="" style="width:20%" value="'.date("h:i a",strtotime($leave_date_record->time_start)).'" name="time_start[]">
                                                                    &nbsp;&nbsp;to&nbsp;&nbsp;
                                                                    <input id="time_end-'.$days[$days_ctr]['date2'].'" class="input-text" type="text" readonly="" style="width:20%" value="'.date("h:i a",strtotime($leave_date_record->time_end)).'" name="time_end[]">';
                            }
                            else{
                                 $days[$days_ctr]['time_range'] = '<input id="time_start-'.$days[$days_ctr]['date2'].'" class="input-text" type="text" readonly="" style="width:20%" value="8:00 am" name="time_start[]">
                                                                    &nbsp;&nbsp;to&nbsp;&nbsp;
                                                                    <input id="time_end-'.$days[$days_ctr]['date2'].'" class="input-text" type="text" readonly="" style="width:20%" value="8:00 pm" name="time_end[]">';
                            }
                        }
                        else{
                             $days[$days_ctr]['time_range'] = '<input id="time_start-'.$days[$days_ctr]['date2'].'" class="input-text" type="text" readonly="" style="width:20%" value="8:00 am" name="time_start[]">
                                                                    &nbsp;&nbsp;to&nbsp;&nbsp;
                                                                    <input id="time_end-'.$days[$days_ctr]['date2'].'" class="input-text" type="text" readonly="" style="width:20%" value="8:00 pm" name="time_end[]">';
                        }

                        $days_ctr++;
                    } else if(isset($worksched->has_cal_shift) && $worksched->has_cal_shift) {
                        if(!empty($worksched->shift_id) && $worksched->shift_id != 1)
                        {
                            $days[$days_ctr]['date'] = date('m/d/Y', strtotime($start_date));
                            $days[$days_ctr]['date2'] = date('Y-m-d',strtotime($start_date));
                            $days[$days_ctr]['employee_leave_date_id'] = '0';

                            if( $this->input->post('record_id') != "-1" ){
                                $this->db->join('employee_obt_date','employee_obt.employee_obt_id = employee_obt_date.employee_obt_id','left');
                                $this->db->where('employee_obt_date.date',date('Y-m-d', strtotime($start_date)));
                                $this->db->where('employee_obt.employee_obt_id',$this->input->post('record_id'));
                                $leave_date_result = $this->db->get('employee_obt');

                                if( $leave_date_result->num_rows() > 0 ){
                                    $leave_date_record = $leave_date_result->row();
                                    $days[$days_ctr]['time_range'] = '<input id="time_start-'.$days[$days_ctr]['date2'].'" class="input-text" type="text" readonly="" style="width:20%" value="'.date("h:i a",strtotime($leave_date_record->time_start)).'" name="time_start[]">
                                                                    &nbsp;&nbsp;to&nbsp;&nbsp;
                                                                    <input id="time_end-'.$days[$days_ctr]['date2'].'" class="input-text" type="text" readonly="" style="width:20%" value="'.date("h:i a",strtotime($leave_date_record->time_end)).'" name="time_end[]">';
                                }
                                else{
                                     $days[$days_ctr]['time_range'] = '<input id="time_start-'.$days[$days_ctr]['date2'].'" class="input-text" type="text" readonly="" style="width:20%" value="8:00 am" name="time_start[]">
                                                                    &nbsp;&nbsp;to&nbsp;&nbsp;
                                                                    <input id="time_end-'.$days[$days_ctr]['date2'].'" class="input-text" type="text" readonly="" style="width:20%" value="8:00 pm" name="time_end[]">';
                                }
                            }
                            else{
                                 $days[$days_ctr]['time_range'] = '<input id="time_start-'.$days[$days_ctr]['date2'].'" class="input-text" type="text" readonly="" style="width:20%" value="8:00 am" name="time_start[]">
                                                                    &nbsp;&nbsp;to&nbsp;&nbsp;
                                                                    <input id="time_end-'.$days[$days_ctr]['date2'].'" class="input-text" type="text" readonly="" style="width:20%" value="8:00 pm" name="time_end[]">';
                            }

                            $days_ctr++;
                        }
                    } else{
                        //check shift base on work sched to remove days which fall on rests days

                        switch( date('N', strtotime( $start_date )) ){
                            case 1:
                                if( !empty( $worksched->monday_shift_id ) && $worksched->monday_shift_id != 1 ){
                                    $days[$days_ctr]['date'] = date('m/d/Y', strtotime($start_date));
                                    $days[$days_ctr]['date2'] = date('Y-m-d',strtotime($start_date));
                                    $days[$days_ctr]['employee_leave_date_id'] = '0';

                                    if( $this->input->post('record_id') != "-1" ){
                                        $this->db->join('employee_obt_date','employee_obt.employee_obt_id = employee_obt_date.employee_obt_id','left');
                                        $this->db->where('employee_obt_date.date',date('Y-m-d', strtotime($start_date)));
                                        $this->db->where('employee_obt.employee_obt_id',$this->input->post('record_id'));
                                        $leave_date_result = $this->db->get('employee_obt');

                                        if( $leave_date_result->num_rows() > 0 ){
                                            $leave_date_record = $leave_date_result->row();
                                            $days[$days_ctr]['time_range'] = '<input id="time_start-'.$days[$days_ctr]['date2'].'" class="input-text" type="text" readonly="" style="width:20%" value="'.date("h:i a",strtotime($leave_date_record->time_start)).'" name="time_start[]">
                                                                    &nbsp;&nbsp;to&nbsp;&nbsp;
                                                                    <input id="time_end-'.$days[$days_ctr]['date2'].'" class="input-text" type="text" readonly="" style="width:20%" value="'.date("h:i a",strtotime($leave_date_record->time_end)).'" name="time_end[]">';
                                        }
                                        else{
                                             $days[$days_ctr]['time_range'] = '<input id="time_start-'.$days[$days_ctr]['date2'].'" class="input-text" type="text" readonly="" style="width:20%" value="8:00 am" name="time_start[]">
                                                                    &nbsp;&nbsp;to&nbsp;&nbsp;
                                                                    <input id="time_end-'.$days[$days_ctr]['date2'].'" class="input-text" type="text" readonly="" style="width:20%" value="8:00 pm" name="time_end[]">';
                                        }
                                    }
                                    else{
                                         $days[$days_ctr]['time_range'] = '<input id="time_start-'.$days[$days_ctr]['date2'].'" class="input-text" type="text" readonly="" style="width:20%" value="8:00 am" name="time_start[]">
                                                                    &nbsp;&nbsp;to&nbsp;&nbsp;
                                                                    <input id="time_end-'.$days[$days_ctr]['date2'].'" class="input-text" type="text" readonly="" style="width:20%" value="8:00 pm" name="time_end[]">';
                                    }

                                    // for firstbalfour 
                                    if ($worksched->considered_halfday){
                                        $days[$days_ctr]['considered_restday'] = 1; 
                                         $days[$days_ctr]['time_range'] = '<input id="time_start-'.$days[$days_ctr]['date2'].'" class="input-text" type="text" readonly="" style="width:20%" value="8:00 am" name="time_start[]">
                                                                    &nbsp;&nbsp;to&nbsp;&nbsp;
                                                                    <input id="time_end-'.$days[$days_ctr]['date2'].'" class="input-text" type="text" readonly="" style="width:20%" value="8:00 pm" name="time_end[]">';
                                    }

                                    $days_ctr++;

                                }
                                break;
                            case 2:
                                if( !empty( $worksched->tuesday_shift_id ) && $worksched->tuesday_shift_id != 1 ){
                                    $days[$days_ctr]['date'] = date('m/d/Y', strtotime($start_date));
                                    $days[$days_ctr]['date2'] = date('Y-m-d',strtotime($start_date));
                                    $days[$days_ctr]['employee_leave_date_id'] = '0';

                                    if( $this->input->post('record_id') != "-1" ){
                                        $this->db->join('employee_obt_date','employee_obt.employee_obt_id = employee_obt_date.employee_obt_id','left');
                                        $this->db->where('employee_obt_date.date',date('Y-m-d', strtotime($start_date)));
                                        $this->db->where('employee_obt.employee_obt_id',$this->input->post('record_id'));
                                        $leave_date_result = $this->db->get('employee_obt');

                                        if( $leave_date_result->num_rows() > 0 ){
                                            $leave_date_record = $leave_date_result->row();
                                            $days[$days_ctr]['time_range'] = '<input id="time_start-'.$days[$days_ctr]['date2'].'" class="input-text" type="text" readonly="" style="width:20%" value="'.date("h:i a",strtotime($leave_date_record->time_start)).'" name="time_start[]">
                                                                    &nbsp;&nbsp;to&nbsp;&nbsp;
                                                                    <input id="time_end-'.$days[$days_ctr]['date2'].'" class="input-text" type="text" readonly="" style="width:20%" value="'.date("h:i a",strtotime($leave_date_record->time_end)).'" name="time_end[]">';
                                        }
                                        else{
                                             $days[$days_ctr]['time_range'] = '<input id="time_start-'.$days[$days_ctr]['date2'].'" class="input-text" type="text" readonly="" style="width:20%" value="8:00 am" name="time_start[]">
                                                                    &nbsp;&nbsp;to&nbsp;&nbsp;
                                                                    <input id="time_end-'.$days[$days_ctr]['date2'].'" class="input-text" type="text" readonly="" style="width:20%" value="8:00 pm" name="time_end[]">';
                                        }
                                    }
                                    else{
                                         $days[$days_ctr]['time_range'] = '<input id="time_start-'.$days[$days_ctr]['date2'].'" class="input-text" type="text" readonly="" style="width:20%" value="8:00 am" name="time_start[]">
                                                                    &nbsp;&nbsp;to&nbsp;&nbsp;
                                                                    <input id="time_end-'.$days[$days_ctr]['date2'].'" class="input-text" type="text" readonly="" style="width:20%" value="8:00 pm" name="time_end[]">';
                                    }

                                    // for firstbalfour 
                                    if ($worksched->considered_halfday){
                                        $days[$days_ctr]['considered_restday'] = 1; 
                                         $days[$days_ctr]['time_range'] = '<input id="time_start-'.$days[$days_ctr]['date2'].'" class="input-text" type="text" readonly="" style="width:20%" value="8:00 am" name="time_start[]">
                                                                    &nbsp;&nbsp;to&nbsp;&nbsp;
                                                                    <input id="time_end-'.$days[$days_ctr]['date2'].'" class="input-text" type="text" readonly="" style="width:20%" value="8:00 pm" name="time_end[]">';
                                    }

                                    $days_ctr++;
                                }
                                break;
                            case 3:
                                if( !empty( $worksched->wednesday_shift_id ) && $worksched->wednesday_shift_id != 1 ){
                                    $days[$days_ctr]['date'] = date('m/d/Y', strtotime($start_date));
                                    $days[$days_ctr]['date2'] = date('Y-m-d',strtotime($start_date));
                                    $days[$days_ctr]['employee_leave_date_id'] = '0';

                                    if( $this->input->post('record_id') != "-1" ){
                                        $this->db->join('employee_obt_date','employee_obt.employee_obt_id = employee_obt_date.employee_obt_id','left');
                                        $this->db->where('employee_obt_date.date',date('Y-m-d', strtotime($start_date)));
                                        $this->db->where('employee_obt.employee_obt_id',$this->input->post('record_id'));
                                        $leave_date_result = $this->db->get('employee_obt');

                                        if( $leave_date_result->num_rows() > 0 ){
                                            $leave_date_record = $leave_date_result->row();
                                            $days[$days_ctr]['time_range'] = '<input id="time_start-'.$days[$days_ctr]['date2'].'" class="input-text" type="text" readonly="" style="width:20%" value="'.date("h:i a",strtotime($leave_date_record->time_start)).'" name="time_start[]">
                                                                    &nbsp;&nbsp;to&nbsp;&nbsp;
                                                                    <input id="time_end-'.$days[$days_ctr]['date2'].'" class="input-text" type="text" readonly="" style="width:20%" value="'.date("h:i a",strtotime($leave_date_record->time_end)).'" name="time_end[]">';
                                        }
                                        else{
                                             $days[$days_ctr]['time_range'] = '<input id="time_start-'.$days[$days_ctr]['date2'].'" class="input-text" type="text" readonly="" style="width:20%" value="8:00 am" name="time_start[]">
                                                                    &nbsp;&nbsp;to&nbsp;&nbsp;
                                                                    <input id="time_end-'.$days[$days_ctr]['date2'].'" class="input-text" type="text" readonly="" style="width:20%" value="8:00 pm" name="time_end[]">';
                                        }
                                    }
                                    else{
                                         $days[$days_ctr]['time_range'] = '<input id="time_start-'.$days[$days_ctr]['date2'].'" class="input-text" type="text" readonly="" style="width:20%" value="8:00 am" name="time_start[]">
                                                                    &nbsp;&nbsp;to&nbsp;&nbsp;
                                                                    <input id="time_end-'.$days[$days_ctr]['date2'].'" class="input-text" type="text" readonly="" style="width:20%" value="8:00 pm" name="time_end[]">';
                                    }

                                    // for firstbalfour 
                                    if ($worksched->considered_halfday){
                                        $days[$days_ctr]['considered_restday'] = 1; 
                                         $days[$days_ctr]['time_range'] = '<input id="time_start-'.$days[$days_ctr]['date2'].'" class="input-text" type="text" readonly="" style="width:20%" value="8:00 am" name="time_start[]">
                                                                    &nbsp;&nbsp;to&nbsp;&nbsp;
                                                                    <input id="time_end-'.$days[$days_ctr]['date2'].'" class="input-text" type="text" readonly="" style="width:20%" value="8:00 pm" name="time_end[]">';
                                    }

                                    $days_ctr++;

                                }
                                break;
                            case 4:
                                if( !empty( $worksched->thursday_shift_id ) && $worksched->thursday_shift_id != 1 ){
                                    $days[$days_ctr]['date'] = date('m/d/Y', strtotime($start_date));
                                    $days[$days_ctr]['date2'] = date('Y-m-d',strtotime($start_date));
                                    $days[$days_ctr]['employee_leave_date_id'] = '0';

                                    if( $this->input->post('record_id') != "-1" ){
                                        $this->db->join('employee_obt_date','employee_obt.employee_obt_id = employee_obt_date.employee_obt_id','left');
                                        $this->db->where('employee_obt_date.date',date('Y-m-d', strtotime($start_date)));
                                        $this->db->where('employee_obt.employee_obt_id',$this->input->post('record_id'));
                                        $leave_date_result = $this->db->get('employee_obt');

                                        if( $leave_date_result->num_rows() > 0 ){
                                            $leave_date_record = $leave_date_result->row();
                                            $days[$days_ctr]['time_range'] = '<input id="time_start-'.$days[$days_ctr]['date2'].'" class="input-text" type="text" readonly="" style="width:20%" value="'.date("h:i a",strtotime($leave_date_record->time_start)).'" name="time_start[]">
                                                                    &nbsp;&nbsp;to&nbsp;&nbsp;
                                                                    <input id="time_end-'.$days[$days_ctr]['date2'].'" class="input-text" type="text" readonly="" style="width:20%" value="'.date("h:i a",strtotime($leave_date_record->time_end)).'" name="time_end[]">';
                                        }
                                        else{
                                             $days[$days_ctr]['time_range'] = '<input id="time_start-'.$days[$days_ctr]['date2'].'" class="input-text" type="text" readonly="" style="width:20%" value="8:00 am" name="time_start[]">
                                                                    &nbsp;&nbsp;to&nbsp;&nbsp;
                                                                    <input id="time_end-'.$days[$days_ctr]['date2'].'" class="input-text" type="text" readonly="" style="width:20%" value="8:00 pm" name="time_end[]">';
                                        }
                                    }
                                    else{
                                         $days[$days_ctr]['time_range'] = '<input id="time_start-'.$days[$days_ctr]['date2'].'" class="input-text" type="text" readonly="" style="width:20%" value="8:00 am" name="time_start[]">
                                                                    &nbsp;&nbsp;to&nbsp;&nbsp;
                                                                    <input id="time_end-'.$days[$days_ctr]['date2'].'" class="input-text" type="text" readonly="" style="width:20%" value="8:00 pm" name="time_end[]">';
                                    }

                                    // for firstbalfour 
                                    if ($worksched->considered_halfday){
                                        $days[$days_ctr]['considered_restday'] = 1; 
                                        $days[$days_ctr]['time_range'] = '<input id="time_start-'.$days[$days_ctr]['date2'].'" class="input-text" type="text" readonly="" style="width:20%" value="8:00 am" name="time_start[]">
                                                                    &nbsp;&nbsp;to&nbsp;&nbsp;
                                                                    <input id="time_end-'.$days[$days_ctr]['date2'].'" class="input-text" type="text" readonly="" style="width:20%" value="8:00 pm" name="time_end[]">';
                                    }

                                    $days_ctr++;                                    

                                }
                                break;
                            case 5:
                                if( !empty( $worksched->friday_shift_id ) && $worksched->friday_shift_id != 1 ){
                                    $days[$days_ctr]['date'] = date('m/d/Y', strtotime($start_date));
                                    $days[$days_ctr]['date2'] = date('Y-m-d',strtotime($start_date));
                                    $days[$days_ctr]['employee_leave_date_id'] = '0';

                                    if( $this->input->post('record_id') != "-1" ){
                                        $this->db->join('employee_obt_date','employee_obt.employee_obt_id = employee_obt_date.employee_obt_id','left');
                                        $this->db->where('employee_obt_date.date',date('Y-m-d', strtotime($start_date)));
                                        $this->db->where('employee_obt.employee_obt_id',$this->input->post('record_id'));
                                        $leave_date_result = $this->db->get('employee_obt');

                                        if( $leave_date_result->num_rows() > 0 ){
                                            $leave_date_record = $leave_date_result->row();
                                            $days[$days_ctr]['time_range'] = '<input id="time_start-'.$days[$days_ctr]['date2'].'" class="input-text" type="text" readonly="" style="width:20%" value="'.date("h:i a",strtotime($leave_date_record->time_start)).'" name="time_start[]">
                                                                    &nbsp;&nbsp;to&nbsp;&nbsp;
                                                                    <input id="time_end-'.$days[$days_ctr]['date2'].'" class="input-text" type="text" readonly="" style="width:20%" value="'.date("h:i a",strtotime($leave_date_record->time_end)).'" name="time_end[]">';
                                        }
                                        else{
                                             $days[$days_ctr]['time_range'] = '<input id="time_start-'.$days[$days_ctr]['date2'].'" class="input-text" type="text" readonly="" style="width:20%" value="8:00 am" name="time_start[]">
                                                                    &nbsp;&nbsp;to&nbsp;&nbsp;
                                                                    <input id="time_end-'.$days[$days_ctr]['date2'].'" class="input-text" type="text" readonly="" style="width:20%" value="8:00 pm" name="time_end[]">';
                                        }
                                    }
                                    else{
                                         $days[$days_ctr]['time_range'] = '<input id="time_start-'.$days[$days_ctr]['date2'].'" class="input-text" type="text" readonly="" style="width:20%" value="8:00 am" name="time_start[]">
                                                                    &nbsp;&nbsp;to&nbsp;&nbsp;
                                                                    <input id="time_end-'.$days[$days_ctr]['date2'].'" class="input-text" type="text" readonly="" style="width:20%" value="8:00 pm" name="time_end[]">';
                                    }

                                    // for firstbalfour 
                                    if ($worksched->considered_halfday){
                                        $days[$days_ctr]['considered_restday'] = 1; 
                                         $days[$days_ctr]['time_range'] = '<input id="time_start-'.$days[$days_ctr]['date2'].'" class="input-text" type="text" readonly="" style="width:20%" value="8:00 am" name="time_start[]">
                                                                    &nbsp;&nbsp;to&nbsp;&nbsp;
                                                                    <input id="time_end-'.$days[$days_ctr]['date2'].'" class="input-text" type="text" readonly="" style="width:20%" value="8:00 pm" name="time_end[]">';
                                    }

                                    $days_ctr++;

                                }
                                break;
                            case 6:
                                if( !empty( $worksched->saturday_shift_id ) && $worksched->saturday_shift_id != 1 ){
                                    $days[$days_ctr]['date'] = date('m/d/Y', strtotime($start_date));
                                    $days[$days_ctr]['date2'] = date('Y-m-d',strtotime($start_date));
                                    $days[$days_ctr]['employee_leave_date_id'] = '0';

                                    if( $this->input->post('record_id') != "-1" ){
                                        $this->db->join('employee_obt_date','employee_obt.employee_obt_id = employee_obt_date.employee_obt_id','left');
                                        $this->db->where('employee_obt_date.date',date('Y-m-d', strtotime($start_date)));
                                        $this->db->where('employee_obt.employee_obt_id',$this->input->post('record_id'));
                                        $leave_date_result = $this->db->get('employee_obt');

                                        if( $leave_date_result->num_rows() > 0 ){
                                            $leave_date_record = $leave_date_result->row();
                                            $days[$days_ctr]['time_range'] = '<input id="time_start-'.$days[$days_ctr]['date2'].'" class="input-text" type="text" readonly="" style="width:20%" value="'.date("h:i a",strtotime($leave_date_record->time_start)).'" name="time_start[]">
                                                                    &nbsp;&nbsp;to&nbsp;&nbsp;
                                                                    <input id="time_end-'.$days[$days_ctr]['date2'].'" class="input-text" type="text" readonly="" style="width:20%" value="'.date("h:i a",strtotime($leave_date_record->time_end)).'" name="time_end[]">';
                                        }
                                        else{
                                             $days[$days_ctr]['time_range'] = '<input id="time_start-'.$days[$days_ctr]['date2'].'" class="input-text" type="text" readonly="" style="width:20%" value="8:00 am" name="time_start[]">
                                                                    &nbsp;&nbsp;to&nbsp;&nbsp;
                                                                    <input id="time_end-'.$days[$days_ctr]['date2'].'" class="input-text" type="text" readonly="" style="width:20%" value="8:00 pm" name="time_end[]">';
                                        }
                                    }
                                    else{
                                         $days[$days_ctr]['time_range'] = '<input id="time_start-'.$days[$days_ctr]['date2'].'" class="input-text" type="text" readonly="" style="width:20%" value="8:00 am" name="time_start[]">
                                                                    &nbsp;&nbsp;to&nbsp;&nbsp;
                                                                    <input id="time_end-'.$days[$days_ctr]['date2'].'" class="input-text" type="text" readonly="" style="width:20%" value="8:00 pm" name="time_end[]">';
                                    }

                                    // for firstbalfour 
                                    if ($worksched->considered_halfday){
                                        $days[$days_ctr]['considered_restday'] = 1; 
                                         $days[$days_ctr]['time_range'] = '<input id="time_start-'.$days[$days_ctr]['date2'].'" class="input-text" type="text" readonly="" style="width:20%" value="8:00 am" name="time_start[]">
                                                                    &nbsp;&nbsp;to&nbsp;&nbsp;
                                                                    <input id="time_end-'.$days[$days_ctr]['date2'].'" class="input-text" type="text" readonly="" style="width:20%" value="8:00 pm" name="time_end[]">';
                                    }

                                    $days_ctr++;

                                }
                                break;
                            case 7:
                                if( !empty( $worksched->sunday_shift_id ) && $worksched->sunday_shift_id != 1 ){
                                    $days[$days_ctr]['date'] = date('m/d/Y', strtotime($start_date));
                                    $days[$days_ctr]['date2'] = date('Y-m-d',strtotime($start_date));
                                    $days[$days_ctr]['employee_leave_date_id'] = '0';

                                    if( $this->input->post('record_id') != "-1" ){
                                        $this->db->join('employee_obt_date','employee_obt.employee_obt_id = employee_obt_date.employee_obt_id','left');
                                        $this->db->where('employee_obt_date.date',date('Y-m-d', strtotime($start_date)));
                                        $this->db->where('employee_obt.employee_obt_id',$this->input->post('record_id'));
                                        $leave_date_result = $this->db->get('employee_obt');

                                        if( $leave_date_result->num_rows() > 0 ){
                                            $leave_date_record = $leave_date_result->row();
                                            $days[$days_ctr]['time_range'] = '<input id="time_start-'.$days[$days_ctr]['date2'].'" class="input-text" type="text" readonly="" style="width:20%" value="'.date("h:i a",strtotime($leave_date_record->time_start)).'" name="time_start[]">
                                                                    &nbsp;&nbsp;to&nbsp;&nbsp;
                                                                    <input id="time_end-'.$days[$days_ctr]['date2'].'" class="input-text" type="text" readonly="" style="width:20%" value="'.date("h:i a",strtotime($leave_date_record->time_end)).'" name="time_end[]">';
                                        }
                                        else{
                                             $days[$days_ctr]['time_range'] = '<input id="time_start-'.$days[$days_ctr]['date2'].'" class="input-text" type="text" readonly="" style="width:20%" value="8:00 am" name="time_start[]">
                                                                    &nbsp;&nbsp;to&nbsp;&nbsp;
                                                                    <input id="time_end-'.$days[$days_ctr]['date2'].'" class="input-text" type="text" readonly="" style="width:20%" value="8:00 pm" name="time_end[]">';
                                        }
                                    }
                                    else{
                                         $days[$days_ctr]['time_range'] = '<input id="time_start-'.$days[$days_ctr]['date2'].'" class="input-text" type="text" readonly="" style="width:20%" value="8:00 am" name="time_start[]">
                                                                    &nbsp;&nbsp;to&nbsp;&nbsp;
                                                                    <input id="time_end-'.$days[$days_ctr]['date2'].'" class="input-text" type="text" readonly="" style="width:20%" value="8:00 pm" name="time_end[]">';
                                    }

                                    // for firstbalfour 
                                    if ($worksched->considered_halfday){
                                        $days[$days_ctr]['considered_restday'] = 1; 
                                        $days[$days_ctr]['time_range'] = '<input id="time_start-'.$days[$days_ctr]['date2'].'" class="input-text" type="text" readonly="" style="width:20%" value="8:00 am" name="time_start[]">
                                                                    &nbsp;&nbsp;to&nbsp;&nbsp;
                                                                    <input id="time_end-'.$days[$days_ctr]['date2'].'" class="input-text" type="text" readonly="" style="width:20%" value="8:00 pm" name="time_end[]">';
                                    }

                                    $days_ctr++;
                                    
                                }
                            break;  
                        }

                        
                    }
                }

                $start_date = date('Y-m-d', strtotime($start_date . ' +1 day') );

            }
            $response['dates'] = $days;
            $response['type'] = 'success';
            $response['client_no'] = $this->config->item('client_no');
            $data['json'] = $response;

            $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
        } else {
            $this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
            redirect(base_url() . $this->module_link);
        }
    }

    function _can_approve( $rec ) {
        
        if( $rec->form_status_id == 2 && $this->user_access[$this->module_id]['approve'] == 1){
            $key_field = $this->key_field;

            $approver = $this->db->get_where('form_approver', array('module_id' => $this->module_id, 'record_id' => $rec->$key_field, 'approver' => $this->user->user_id));
            
            if( $approver->num_rows() == 1 ){
                $approver = $approver->row();

                if( $approver->status == 2 ){
                    return true;
                }
            }

            if (CLIENT_DIR == 'firstbalfour'){
                if ($this->user_access[$this->module_id]['post'] == 1){
                    return true;
                }
            }

            return false;
        }
        return false;
    }

    private function _can_decline( $rec ) {
        if( $rec->form_status_id == 2 && $this->user_access[$this->module_id]['decline'] == 1){
            $key_field = $this->key_field;
            $approver = $this->db->get_where('form_approver', array('module_id' => $this->module_id, 'record_id' => $rec->$key_field, 'approver' => $this->user->user_id));
            if( $approver->num_rows() == 1 ){
                $approver = $approver->row();
                if( $approver->status == 2 ){
                    return true;
                }
            }

            if (CLIENT_DIR == 'firstbalfour'){
                if ($this->user_access[$this->module_id]['post'] == 1){
                    return true;
                }
            }

            return false;
        }
        return false;
    }

    private function _can_cancel( $rec ) {
        if( $rec->form_status_id == 3 && $this->user_access[$this->module_id]['cancel'] == 1){
            return true;
        }
        return false;
    }

    private function _render_view()
    {
        if ($this->_live) {
            return $this->load->get_vars();
        }

        //load header
        $this->load->view($this->userinfo['rtheme'] . '/template/header');
        $this->load->view($this->userinfo['rtheme'] . '/template/header-nav');

        //load page content
        $this->load->view($this->userinfo['rtheme'] . '/template/page-content');

        //load footer
        $this->load->view($this->userinfo['rtheme'] . '/template/footer');
    }

    function get_boxy_single_cancel() {
        if(IS_AJAX)
        {
            $response->msg = "";
            $employee_obt_id = $this->input->post('record_id');           
            $response->boxy_div = '<form class="style2 edit-view" name="single_cancel" id="single_cancel" method="post" enctype="multipart/form-data">
                                    <div id="form-div">
                                    <input type="hidden" name="employee_obt_id" id="employee_obt_id" value="'.$employee_obt_id.'">
                                        <div class="col-1-form">      
                                            <div class="form-item">
                                                <label for="date_range" class="label-desc gray">Cancel Remarks : <span class="red">*</span></label>         
                                                <div class="textarea-input-wrap">
                                                    <textarea name="cancel_remarks" id="cancel_remarks"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-submit-btn">
                                        <div class="icon-label-group">
                                            <div class="icon-label">
                                                <a onclick="save_cancel_single()" href="javascript:void(0);" class="icon-16-add">
                                                    <span>Save</span>
                                                 </a>            
                                            </div>
                                        </div>
                                        <div class="or-cancel">
                                            <span class="or">or</span>
                                            <a href="javascript:void(0)" class="cancel" onclick="Boxy.get(this).hide().unload();">Cancel</a>
                                        </div>
                                    </div>
                                    </form>';               
            $data['json'] = $response;
            $this->load->view('template/ajax', $data);
        } else {
            $this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
            redirect(base_url().$this->module_link);
        }
    }

    function get_boxy_multiple_cancel() {
        if(IS_AJAX)
        {
            $response->msg = "";
            $employee_obt_id = $this->input->post('record_id');
            $hqry = $this->db->query("SELECT * FROM {$this->db->dbprefix}employee_obt WHERE employee_obt_id = '{$employee_obt_id}'")->row();         
            $dqry = $this->db->query("SELECT a.date, a.time_start, a.time_end, a.employee_obt_date_id, a.date_cancelled, a.cancelled FROM {$this->db->dbprefix}employee_obt_date a 
                                        WHERE a.employee_obt_id = '{$employee_obt_id}'")->result();
            $response->boxy_div = '<form class="style2 edit-view" name="multiple_cancel" id="multiple_cancel" method="post" enctype="multipart/form-data">
                                    <div id="form-div">
                                    <input type="hidden" name="employee_obt_id" id="employee_obt_id" value="'.$employee_obt_id.'">
                                        <div class="col-1-form">      
                                            <div class="form-item">
                                                <label for="date_range" class="label-desc gray">Dates</label>           
                                                <div class="text-input-wrap">
                                                    <input id="date-temp-from" class="input-text datepicker disabled hasDatepicker" type="text" disabled="disabled" value="'.date("m/d/y",strtotime($hqry->date_from)).'" name="date-temp-from">
                                                    <img class="ui-datepicker-trigger" src="'.base_url().'themes/slategray/icons/calendar-month.png" alt="" title="">
                                                    &nbsp;&nbsp;<span class="to">to</span>&nbsp;&nbsp;
                                                    <input id="date-temp-to" class="input-text datepicker disabled hasDatepicker" type="text" disabled="disabled" value="'.date("m/d/y",strtotime($hqry->date_to)).'" name="date-temp-to">
                                                    <img class="ui-datepicker-trigger" src="'.base_url().'themes/slategray/icons/calendar-month.png" alt="" title="">
                                                </div>
                                            </div>
                                            <div class="clear"></div>';
                    $response->boxy_div .= '<div class="form-item" style="height:127px;overflow-y: auto;">
                                                <label for="inclusive_date" class="label-desc gray">Inclusive Dates :</label>';
                                                foreach ($dqry as $key => $value) {
                                                    if($value->cancelled == 1) {
                                                        $check = '<input id="chk_can2" d_id="" checked disabled type="checkbox" value="'.$value->employee_obt_date_id.'" name="chk_can[]">&nbsp;<span class="red">Cancelled</span>&nbsp<span class="blue"><i>Date : '.date("m/d/y",strtotime($value->date_cancelled)).'</i></span>';
                                                    } else {
                                                        $check = '<input id="chk_can" d_id="'.$value->employee_obt_date_id.'" type="checkbox" value="'.$value->employee_obt_date_id.'" name="chk_can[]">';
                                                    }
                                    $response->boxy_div .= '<div class="text-input-wrap">
                                                    '.date("m/d/y",strtotime($value->date)).' - 
                                                    &nbsp&nbsp'.date('h:i a',strtotime($value->time_start)).'&nbsp;&nbsp;to&nbsp;&nbsp;'.date('h:i a',strtotime($value->time_end)).'
                                                    '.$check.'
                                                    </div>';
                                                }                                               
                    $response->boxy_div .= '</div>
                                            <div class="clear"></div>
                                            <div class="form-item">
                                                <label for="reason" class="label-desc gray">Reason :</label>
                                                <div class="textarea-input-wrap">
                                                    <textarea name="cancel_reason" id="cancel_reason" readonly>'.$hqry->reason.'</textarea>
                                                </div>
                                            </div>
                                            <div class="clear"></div>
                                            <div class="form-item">
                                                <label for="remarks" class="label-desc gray">Remarks : <span class="red">*</span></label>
                                                <div class="textarea-input-wrap">
                                                    <textarea name="cancel_remarks" id="cancel_remarks"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-submit-btn">
                                        <div class="icon-label-group">
                                            <div class="icon-label">
                                                <a onclick="save_cancel_multiple()" href="javascript:void(0);" class="icon-16-add">
                                                    <span>Save</span>
                                                 </a>            
                                            </div>
                                        </div>
                                        <div class="or-cancel">
                                            <span class="or">or</span>
                                            <a href="javascript:void(0)" class="cancel" onclick="Boxy.get(this).hide().unload();">Cancel</a>
                                        </div>
                                    </div>
                                    </form>';               
            $data['json'] = $response;
            $this->load->view('template/ajax', $data);
        } else {
            $this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
            redirect(base_url().$this->module_link);
        }
    }

    function save_cancel_multiple() {
        if(IS_AJAX) {
            $employee_obt_id = $this->input->post('employee_obt_id');
            $chk_can = $this->input->post('chk_can');
            $cancel_remarks = $this->input->post('cancel_remarks');
            $tag = 0;
            $message = '';
            if(!isset($_POST['chk_can'])) {
                $tag = 1;
                $message = 'No Inclusive Date Selected.';
                $msg_type = 'info';
            }
            if(!$tag) {
                $hqry = $this->db->query("SELECT * FROM {$this->db->dbprefix}employee_obt WHERE employee_obt_id = '{$employee_obt_id}'")->row();
                foreach($chk_can as $index => $employee_obt_date_id)
                {
                    $this->db->update('employee_obt_date', array('cancelled'=>1,'deleted'=>1,'remarks'=>$cancel_remarks,'date_cancelled'=>date('Y-m-d H:i:s')), array('employee_obt_date_id' => $employee_obt_date_id));
                }
                $dqry = $this->db->query("SELECT * FROM {$this->db->dbprefix}employee_obt_date WHERE employee_obt_id = '{$employee_obt_id}' AND cancelled = 0");
                if($dqry->num_rows() == 0) {
                    $data['date_approved'] = date('Y-m-d H:i:s');
                    $data['form_status_id'] = 5;
                    $this->db->where('employee_obt_id', $employee_obt_id);
                    $this->db->update('employee_obt', $data);
                }
                $this->send_status_email($employee_obt_id,5);
                $message = 'Cancelled Successfully.';
                $msg_type = 'success';
            }
            $response->tag = $tag;
            $response->msg_msg = $message;
            $response->msg_type = $msg_type;
            $data['json'] = $response;
            $this->load->view('template/ajax', $data);
        } else {
            $this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
            redirect(base_url().$this->module_link);
        }
    }

    function save_cancel_single() {
        if(IS_AJAX) {
            $employee_obt_id = $this->input->post('employee_obt_id');
            $cancel_remarks = $this->input->post('cancel_remarks');
            $message = '';

            $this->db->update('employee_obt_date', array('cancelled'=>1,'deleted'=>1,'remarks'=>$cancel_remarks,'date_cancelled'=>date('Y-m-d H:i:s')), array('employee_obt_id' => $employee_obt_id));

            $dqry = $this->db->query("SELECT * FROM {$this->db->dbprefix}employee_obt_date WHERE employee_obt_id = '{$employee_obt_id}' AND cancelled = 0");
            if($dqry->num_rows() == 0) {
                $data['date_approved'] = date('Y-m-d H:i:s');
                $data['form_status_id'] = 5;
                $this->db->where('employee_obt_id', $employee_obt_id);
                $this->db->update('employee_obt', $data);
            }
            $this->send_status_email($employee_obt_id,5);
            $message = 'Cancelled Successfully.';
            $msg_type = 'success';

            $response->tag = $tag;
            $response->msg_msg = $message;
            $response->msg_type = $msg_type;
            $data['json'] = $response;
            $this->load->view('template/ajax', $data);
        } else {
            $this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
            redirect(base_url().$this->module_link);
        }
    }    
}

/* End of file */
/* Location: system/application */
