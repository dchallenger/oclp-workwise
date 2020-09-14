<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of dailytimerecord
 *
 * @author jconsador
 */
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Employee_dtr_master extends MY_Controller {

    function __construct() {
        parent::__construct();

        //set module variable values
        $this->grid_grouping = '';
        $this->related_table = array(); //table => field format

        $this->listview_title = '';
        $this->listview_description = '';
        $this->jqgrid_title = "";
        $this->detailview_title = '';
        $this->detailview_description = '';
        $this->editview_title = 'Add/Edit';
        $this->editview_description = '';

        if( $this->user_access[$this->module_id]['post'] != 1){
            $this->filter = $this->db->dbprefix.'employee_dtr.employee_id = '.$this->userinfo['user_id'];
        }
        if (CLIENT_DIR == "firstbalfour") {
            if (!( $this->is_superadmin || $this->is_admin ) && $this->user_access[$this->module_id]['project_hr'] == 1){
                $subordinates = $this->system->get_subordinates_by_project($this->user->user_id);
                if ($subordinates){
                    foreach ($subordinates as $key => $value){
                        $subs[] = $value['employee_id'];            
                    }
                    $this->filter = $this->db->dbprefix."employee_dtr.employee_id IN (".implode(',', $subs).")";                 
                }else{
                    $this->filter = $this->db->dbprefix.'employee_dtr.employee_id = '.$this->userinfo['user_id'];
                }
            } 
        }   
        //$this->filter = $this->db->dbprefix.'employee_dtr.employee_id = '.$this->userinfo['user_id'];

        $this->default_sort_col = array('t0firstnamemiddleinitiallastnameaux');

    }

    // START - default module functions
    // default jqgrid controller method
    function index() {
        $data['scripts'][] = jqgrid_listview(); // load jqgrid js and default grid js
        $data['content'] = 'listview';

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
        if($this->user_access[$this->module_id]['edit'] == 1){
            parent::edit();

            //additional module edit routine here
            $data['show_wizard_control'] = false;
            $data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/js/editview.js"></script>';
            $data['scripts'][] ='<script type="text/javascript" src="' . base_url() . 'lib/jquery/jquery.maskedinput-1.3.min.js"></script>';
            $data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/modules/dtr/get_sched.js"></script>';            
            //$data['scripts'][] = '<script>$(document).ready(function () { $(document).ready(function() { $(\'#time_in1\').bind(\'change\', function() { alert($(this).val()); }); }); });</script>';                 
            if( !empty($this->module_wizard_form) && $this->input->post('record_id') == '-1' ){
                $data['show_wizard_control'] = true;
                $data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/js/editview-wizard-form.js"></script>';
            }
            $data['content'] = 'editview';

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

    function get_workshift() {
        if (!IS_AJAX) {
            $this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
            redirect(base_url() . $this->module_link);
        } else if ($this->user_access[$this->module_id]['view'] != 1) {
            $response->msg_type = 'error';
            $response->msg      = 'You do not have access to the selected module.';
        } else {
            $this->db->select('workschedule_employee.*, workshift.*');
            $this->db->join('workshift', 'workschedule_employee.shift_calendar_id = workshift.workshift_id', 'left');
            //$this->db->order_by("name", "asc");
            //$this->db->order_by("birth_date", "asc");
            $this->db->where('workschedule_employee.employee_id', $this->input->post('employee_id'));
            $this->db->where('workschedule_employee.effectivity_date <=', $this->input->post('date'));
            $this->db->where('workschedule_employee.deleted', 0);

            $employee = $this->db->get('workschedule_employee');
            
            // if($employee->num_rows() == 0)
            //     $response->data=$employee="no";
            //dbug($employee);

            //$final=;
            if (!$employee || $employee->num_rows() == 0) {
                $response->msg_type = 'no';
                $response->msg      = 'No Workschedule';
            } else {
                $response->msg_type = 'success';

                $response->data = $employee->result_array();
            }           
        }
        $this->load->view('template/ajax', array('json' => $response));
    }

    function regularworkshift() {
        if (!IS_AJAX) {
            $this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
            redirect(base_url() . $this->module_link);
        } else if ($this->user_access[$this->module_id]['view'] != 1) {
            $response->msg_type = 'error';
            $response->msg      = 'You do not have access to the selected module.';
        } else {
            //join workshift to convert to name
            $this->db->select('employee.*, workshift.*');
            $this->db->join('workshift','employee.shift_id = workshift.workshift_id','left');
            $this->db->where('employee_id', $this->input->post('employee_id'));

            $employee = $this->db->get('employee');
            
            // if($employee->num_rows() == 0)
            //     $response->data=$employee="no";
            //dbug($employee);

            //$final=;
            if (!$employee || $employee->num_rows() == 0) {
                $response->msg_type = 'error';
                $response->msg      = 'No Regular Workshift';
            } else {
                $response->msg_type = 'success';

                $response->data = $employee->result_array();
            }           
        }
        $this->load->view('template/ajax', array('json' => $response));
    }

    function get_work_schedule(){
        $this->db->select('shift_calendar');
        $this->db->join('timekeeping_shift_calendar','workschedule_employee.shift_calendar_id = timekeeping_shift_calendar.shift_calendar_id','left');
        $this->db->where('employee_id', $this->input->post('employee_id'));
        $this->db->where('workschedule_employee.deleted', 0);
        $this->db->where('date_from >', $this->input->post('date_in'));        
        $this->db->where('date_to <', $this->input->post('date_in'));        
        $shift_schedule = $this->db->get('workschedule_employee');

        if ($shift_schedule->num_rows() == 0){
            
            $this->db->join('timekeeping_shift_calendar','timekeeping_shift_calendar.shift_calendar_id = employee_dtr_setup.shift_calendar_id','left');
            $this->db->where('employee_dtr_setup.employee_id',$this->input->post('employee_id'));
            $default_workshift = $this->db->get('employee_dtr_setup');

            if( $default_workshift->num_rows() > 0 ){
                $shift_schedule = $default_workshift->row()->shift_calendar;
            }
            else{
                $shift_schedule = "No Work Schedule";
            }

        }
        else{
            $shift_schedule = $shift_schedule->row()->shift_calendar;
        }

        $this->db->select('*');
        $this->db->where('employee_id', $this->input->post('employee_id'));
        $this->db->where('deleted', 0);
        $this->db->where('date', $this->input->post('date_in'));              

        $data = $this->db->get('employee_dtr');
        $row = $data->row();

        $timein = ($row->time_in1 && $row->time_in1 <> "0000-00-00 00:00:00" ? date('m/d/Y h:i a',strtotime($row->time_in1)) : "" );
        $timeout = ($row->time_out1 && $row->time_out1 <> "0000-00-00 00:00:00" ? date('m/d/Y h:i a',strtotime($row->time_out1)) : "");
        $breakin = ($row->time_in2 && $row->time_in2 <> "0000-00-00 00:00:00" ? date('m/d/Y h:i a',strtotime($row->time_in2)) : "");
        $breakout = ($row->time_out2 && $row->time_out2 <> "0000-00-00 00:00:00" ? date('m/d/Y h:i a',strtotime($row->time_out2)) : "");

        $js = array("shift_schedule"=>$shift_schedule,"timein"=>$timein,"timeout"=>$timeout,"breakin"=>$breakin,"breakout"=>$breakout);

        echo json_encode($js);
    }

    function get_detail_workschedule(){

        $dtr_result = $this->db->get_where('employee_dtr',array('id'=>$this->input->post('record_id')))->row();

        $this->db->select('shift_calendar');
        $this->db->join('timekeeping_shift_calendar','workschedule_employee.shift_calendar_id = timekeeping_shift_calendar.shift_calendar_id','left');
        $this->db->where('employee_id', $dtr_result->employee_id);
        $this->db->where('workschedule_employee.deleted', 0);
        $this->db->where('date_from >', $dtr_result->date);        
        $this->db->where('date_to <', $dtr_result->date);        
        $shift_schedule = $this->db->get('workschedule_employee');

        if ($shift_schedule->num_rows() == 0){
            
            $this->db->join('timekeeping_shift_calendar','timekeeping_shift_calendar.shift_calendar_id = employee_dtr_setup.shift_calendar_id','left');
            $this->db->where('employee_dtr_setup.employee_id',$dtr_result->employee_id);
            $default_workshift = $this->db->get('employee_dtr_setup');

            if( $default_workshift->num_rows() > 0 ){
                $shift_schedule = $default_workshift->row()->shift_calendar;
            }
            else{
                $shift_schedule = "No Work Schedule";
            }

        }
        else{
            $shift_schedule = $shift_schedule->row()->shift_calendar;
        }

        $response->shift_schedule = $shift_schedule;

        $this->load->view('template/ajax', array('json' => $response));

    }

    function ajax_save(){
        $where = array(
                        'deleted' => 0,
                        'employee_id' => $this->input->post('employee_id'),
                        'date' => date('Y-m-d', strtotime($this->input->post('date')))
                        );

            $rec = $this->db->get_where($this->module_table, $where);

            if($rec && $rec->num_rows() > 0){
                $rec = $rec->row();
                $_POST['record_id'] = $rec->id; //original $rec->leave_setup_id
            }

        parent::ajax_save();

        if ($this->key_field_val){
            $this->db->where($this->key_field, $this->key_field_val);
            $this->db->update($this->module_table, array("processed" =>1));
        }
    }

    function get_leave_and_other_application(){

        $html = '';

        $this->db->select('employee_leaves.*,form_status.*,employee_form_type.*');
        $this->db->from('employee_leaves');
        $this->db->join('form_status', 'form_status.form_status_id = employee_leaves.form_status_id', 'left');
        $this->db->join('employee_form_type', 'employee_form_type.application_form_id = employee_leaves.application_form_id', 'left');
        $this->db->where('employee_leaves.deleted', 0);
        //note record_id is employee_id
          
        $this->db->where('employee_leaves.employee_id', $this->input->post('employee_id'));
        $this->db->where('employee_leaves.date_from <=', $this->input->post('date'));
        $this->db->where('employee_leaves.date_to >=', $this->input->post('date'));
        $arr=$this->db->get()->result_array();

        foreach($arr as $fields=>$fieldval){

            $pieces=explode(' ',$fieldval['date_created']);
            $from = strtotime($pieces[0]);
            $to = strtotime($pieces[1]);
            $nod = $to - $from;
            $floor = floor($nod/(60*60*24));

            $newdate=date($this->config->item('display_datetime_format'), strtotime($fieldval['date_created']));

            $html .= '
            <div>
                <div class="form-item view odd ">
                   <label for="perm_address1" class="label-desc view gray"> Form Type: </label>
                   <div class="text-input-wrap">'.$fieldval['application_form'].'
                   </div>        
                </div>
                <div class="form-item view even ">
                   <label for="perm_address1" class="label-desc view gray"> Effective Dates: </label>
                   <div class="text-input-wrap">'.date($this->config->item('display_date_format'), strtotime($fieldval['date_from'])).' To '.date($this->config->item('display_date_format'), strtotime($fieldval['date_to'])).'
                   </div>        
                </div>
                <div class="form-item view even ">
                   <label for="perm_address1" class="label-desc view gray"> Number of Day(s): </label>
                   <div class="text-input-wrap">'.$floor.'
                   </div>        
                </div>
                <div class="form-item view odd ">
                   <label for="perm_address1" class="label-desc view gray"> Application Date: </label>
                   <div class="text-input-wrap">'.$newdate.'</div>        
                </div>
                <div class="form-item view even ">
                   <label for="perm_address1" class="label-desc view gray"> Reason: </label>
                   <div class="text-input-wrap">'.$fieldval['reason'].'</div>        
                </div>
                <div class="form-item view even ">
                   <label for="perm_address1" class="label-desc view gray"> Status: </label>
                   <div class="text-input-wrap">'.$fieldval['form_status'].'
                   </div>        
                </div>
                <br clear="all"/><br />
            </div>';
        }

        // for dtrp
        $this->db->join('form_status', 'form_status.form_status_id = employee_dtrp.form_status_id', 'left');
        $this->db->where('employee_dtrp.employee_id', $this->input->post('employee_id'));
        $this->db->where('employee_dtrp.date', $this->input->post('date'));
        $result_dtrp = $this->db->get('employee_dtrp');

        if ($result_dtrp && $result_dtrp->num_rows() > 0){
            foreach ($result_dtrp->result() as $row) {
                $html .= '
                <div class="form-item view odd ">
                   <label for="perm_address1" class="label-desc view gray"> Form Type: </label>
                   <div class="text-input-wrap">DTRP</div>        
                </div>
                <div class="form-item view even ">
                   <label for="perm_address1" class="label-desc view gray"> Dates: </label>
                   <div class="text-input-wrap">'.date($this->config->item('display_date_format'), strtotime($row->date)).'</div>        
                </div>
                <div class="form-item view odd ">
                   <label for="perm_address1" class="label-desc view gray"> Set: </label>
                   <div class="text-input-wrap">'.($row->time_set_id == 1 ? "Time In" : "Time Out").'
                   </div>        
                </div>
                <div class="form-item view even ">
                   <label for="perm_address1" class="label-desc view gray"> Reason: </label>
                   <div class="text-input-wrap">'.$row->reason.'</div>        
                </div>
                <div class="form-item view even ">
                   <label for="perm_address1" class="label-desc view gray"> Status: </label>
                   <div class="text-input-wrap">'.$row->form_status.'</div>        
                </div>';                
            }
        }

        // for undertime
        $this->db->join('form_status', 'form_status.form_status_id = employee_out.form_status_id', 'left');
        $this->db->where('employee_out.employee_id', $this->input->post('employee_id'));
        $this->db->where('employee_out.date', $this->input->post('date'));
        $result_out = $this->db->get('employee_out');

        if ($result_out && $result_out->num_rows() > 0){
            foreach ($result_out->result() as $row) {
                $html .= '
                <div class="form-item view odd ">
                   <label for="perm_address1" class="label-desc view gray"> Form Type: </label>
                   <div class="text-input-wrap">Official Undertime</div>        
                </div>
                <div class="form-item view even ">
                   <label for="perm_address1" class="label-desc view gray"> Dates: </label>
                   <div class="text-input-wrap">'.date($this->config->item('display_date_format'), strtotime($row->date)).'</div>        
                </div>
                <div class="form-item view odd ">
                   <label for="perm_address1" class="label-desc view gray"> Time Start: </label>
                   <div class="text-input-wrap">'.$row->time_start.'</div>        
                </div>
                <div class="form-item view even ">
                   <label for="perm_address1" class="label-desc view gray"> Time End: </label>
                   <div class="text-input-wrap">'.$row->time_end.'</div>        
                </div>                
                <div class="form-item view odd ">
                   <label for="perm_address1" class="label-desc view gray"> Reason: </label>
                   <div class="text-input-wrap">'.$row->reason.'</div>        
                </div>
                <div class="form-item view even ">
                   <label for="perm_address1" class="label-desc view gray"> Status: </label>
                   <div class="text-input-wrap">'.$row->form_status.'
                   </div>        
                </div>';                
            }
        }

        // for obt
        $this->db->join('form_status', 'form_status.form_status_id = employee_obt.form_status_id', 'left');
        $this->db->where('employee_obt.employee_id', $this->input->post('employee_id'));
        $this->db->where('employee_obt.date_from <=', $this->input->post('date'));
        $this->db->where('employee_obt.date_to >=', $this->input->post('date'));
        $result_obt = $this->db->get('employee_obt');

        if ($result_obt && $result_obt->num_rows() > 0){
            foreach ($result_obt->result() as $row) {
                $html .= '
                <div class="form-item view odd ">
                   <label for="perm_address1" class="label-desc view gray"> Form Type: </label>
                   <div class="text-input-wrap">Official Business Trip
                   </div>        
                </div>
                <div class="form-item view odd ">
                   <label for="perm_address1" class="label-desc view gray"> From Date: </label>
                   <div class="text-input-wrap">'.date($this->config->item('display_date_format'), strtotime($row->date_from)).'</div>        
                </div>
                <div class="form-item view even ">
                   <label for="perm_address1" class="label-desc view gray"> To Date: </label>
                   <div class="text-input-wrap">'.date($this->config->item('display_date_format'), strtotime($row->date_to)).'</div>        
                </div>                
                <div class="form-item view odd ">
                   <label for="perm_address1" class="label-desc view gray"> Reason: </label>
                   <div class="text-input-wrap">'.$row->reason.'</div>        
                </div>
                <div class="form-item view even ">
                   <label for="perm_address1" class="label-desc view gray"> Status: </label>
                   <div class="text-input-wrap">'.$row->form_status.'
                   </div>        
                </div>';                
            }
        }

        $data['html'] = $html;
        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data); 

    }


    function get_sub_by_project()
    {
        $response->is_projecthr = false;
        
        if (CLIENT_DIR == 'firstbalfour') {
            $subordinates = $this->system->get_subordinates_by_project($this->input->post('project_hr'));
            $response->subordinates = '';
            if (count($subordinates)>0 && $subordinates != false && $this->user_access[$this->module_id]['project_hr']) {
                $response->subordinates .= '<option value=" "> </option>';
                foreach ($subordinates as $sub) {
                    $response->subordinates .= '<option value="'.$sub['employee_id'].'">'.$sub['firstname'] .' '. $sub['lastname'].'</option>';
                }
                $response->sub_count = count($subordinates);
                $response->is_projecthr = true;
            }
        }
        
        $data['json'] = $response;                      
        $this->load->view($this->userinfo['rtheme'].'/template/ajax', $data);

    }

}

/* End of file */
/* Location: system/application */
