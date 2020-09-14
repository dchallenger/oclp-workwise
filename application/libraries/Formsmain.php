<?php

/**
 * Description of Leaves
 *
 * @author jconsador
 */
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Formsmain extends MY_Controller {

    private $_live;

    function __construct($live = false, $params = array()) {
        parent::__construct();

        $this->_live = $live;

        if ($live) {            
            foreach ($params as $key => $p) {
                $_POST[$key] = $p;
            }
        }

        //set module variable values
        $this->grid_grouping = "";
        $this->related_table = array(); //table => field format

        $this->listview_title = $this->module_name;
        $this->listview_description = '';
        $this->jqgrid_title = "";
        $this->detailview_title = '';
        $this->detailview_description = '';
        $this->editview_title = 'Add/Edit ' . $this->module_name;;
        $this->editview_description = '';

        if(!( $this->is_superadmin || $this->is_admin ) && $this->user_access[$this->module_id]['post'] != 1 && $this->user_access[$this->module_id]['publish'] != 1){
            $this->filter = $this->module_table.".employee_id = {$this->user->user_id}";
        }

        //for approval
        $forms_to_approve = $this->system->get_forms_to_approve( $this->user->user_id, '!= 1', 'in (0,1)', (isset($this->user_access[$this->module_id]['project_hr']) ? $this->user_access[$this->module_id]['project_hr'] : 0) );
        if( $forms_to_approve ){
            if( $this->input->post('filter') && $this->input->post('filter') == "for_approval" ){
                $forms_to_approve = $this->system->get_forms_to_approve( $this->user->user_id, '!= 1', '= 1', $this->user_access[$this->module_id]['project_hr'] );
                $this->filter = $this->db->dbprefix.$this->module_table.".{$this->key_field} IN (".implode(',', $forms_to_approve).") AND ".$this->db->dbprefix.$this->module_table.".form_status_id = 2";  
            }

            if( $this->input->post('filter') && $this->input->post('filter') == "approved" ){
                $this->filter = $this->db->dbprefix.$this->module_table.".{$this->key_field} IN (".implode(',', $forms_to_approve).") AND ".$this->db->dbprefix.$this->module_table.".form_status_id = 3";  
            }

            if( $this->input->post('filter') && $this->input->post('filter') == "disapproved" ){
                $this->filter = $this->db->dbprefix.$this->module_table.".{$this->key_field} IN (".implode(',', $forms_to_approve).") AND ".$this->db->dbprefix.$this->module_table.".form_status_id = 4";  
            }

            if($this->input->post('filter') && $this->input->post('filter') == "subordinates" && $this->user_access[$this->module_id]['post'] == 1){
                $pos_subordinates = $this->db->get_where('user_position', array("reporting_to" => $this->userinfo['position_id']));
                if($pos_subordinates && $pos_subordinates->num_rows() > 0)
                {
                    $pos_subordinates = $pos_subordinates->result();
                    foreach($pos_subordinates as $pos_sub)
                    {
                        $sub_ids = $this->db->get_where('user', array("position_id" => $pos_sub->position_id))->result();
                        foreach ($sub_ids as $sub_id) 
                            $subs[] = $sub_id->employee_id;
                    }
                    if (count($subs) > 0){
                        $this->filter = $this->module_table.".employee_id IN (". implode(',', $subs) .")";
                    }
                }
            }

            if( $this->input->post('filter') && $this->input->post('filter') == "cancelled" ){
                $this->filter = $this->db->dbprefix.$this->module_table.".{$this->key_field} IN (".implode(',', $forms_to_approve).") AND ".$this->db->dbprefix.$this->module_table.".form_status_id = 5";  
            }
        }

        if( $this->input->post('filter') && $this->input->post('filter') == "personal" ){
            $this->filter = $this->module_table.".employee_id = {$this->user->user_id}";    
        }

        if (CLIENT_DIR == 'firstbalfour'){
            if ((!$this->input->post('filter') || ($this->input->post('filter') == "all")) && !( $this->is_superadmin || $this->is_admin ) && $this->user_access[$this->module_id]['project_hr'] == 1){

                if( $forms_to_approve ){
                    $this->filter = $this->db->dbprefix.$this->module_table.".{$this->key_field} IN (".implode(',', $forms_to_approve).")";  
                }else{
                    $subordinates = $this->system->get_subordinates_by_project($this->user->user_id);
                   
                     if (count($subordinates) > 0 && $subordinates != false )
                     {
                        foreach ($subordinates as $subordinate) {
                         $subordinate_id[] = $subordinate['user_id'];
                        }

                        $this->filter = $this->db->dbprefix.$this->module_table.".employee_id IN (".implode(',', $subordinate_id).")"; 
                     }
                     else
                     {
                         $this->filter = $this->module_table.".employee_id = {$this->user->user_id}";
                     }

                }
            }
        
        } 
        $this->default_sort_col = array('t0firstnamemiddleinitiallastnameaux');
    }

    // START - default module functions
    // default jqgrid controller method
    function index() {
        $data['scripts'][] = jqgrid_listview(); // load jqgrid js and default grid js
       
        if( CLIENT_DIR != "" && file_exists(FCPATH . "lib\modules\client\\". CLIENT_DIR ."\\forms\\formsmain.js")){
            $data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/modules/client/'. CLIENT_DIR .'/forms/formsmain.js"></script>';
        }
        else{ 
            $data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/modules/forms/formsmain.js"></script>';
        } 

        $data['content'] = 'forms/listview';
        $data['jqgrid'] = 'forms/jqgrid';

        //Tabs for Listview
        $tabs = array();
        if( ( $this->is_superadmin || $this->is_admin ) ||  $this->user_access[$this->module_id]['post'] == 1 ){
            $data['filter'] = 'all';
            $tabs[] = '<li class="active" filter="all"><a href="javascript:void(0)">All</li>';
            $tabs[] = '<li filter="personal"><a href="javascript:void(0)">Personal</li>';   
            $pos_subordinates = $this->db->get_where('user_position', array("reporting_to" => $this->userinfo['position_id']));
            if($pos_subordinates && $pos_subordinates->num_rows() > 0)
                $tabs[] = '<li filter="subordinates"><a href="javascript:void(0)">Subordinates</li>';
        }
        else{
            $data['filter'] = 'personal';
            if($this->user_access[$this->module_id]['publish'] == 1) {
                $tabs[] = '<li filter="personal"><a href="javascript:void(0)">Personal</li>';
                $tabs[] = '<li class="active" filter="all"><a href="javascript:void(0)">All</li>';
            } else {
                $tabs[] = '<li class="active" filter="personal"><a href="javascript:void(0)">Personal</li>';
            }
            
        }
        
        //for approval
        $forms_to_approve = $this->system->get_forms_to_approve( $this->user->user_id, '!= 1', 'in (0,1)', $this->user_access[$this->module_id]['project_hr'] );
        if( $forms_to_approve ){
            $forms_to_approve = $this->system->get_forms_to_approve( $this->user->user_id, '= 2', '= 1', $this->user_access[$this->module_id]['project_hr'] );
            $forms_to_approve_count = 0;
            if ($forms_to_approve){
                $this->db->where('form_status_id', 2);
                $this->db->where_in($this->key_field, $forms_to_approve);
                $for_approval = $this->db->get( $this->module_table );
                if ($for_approval && $for_approval->num_rows(0 > 0)){
                    $forms_to_approve_count = $for_approval->num_rows();
                }
            }
/*            $this->db->where('form_status_id', 2);
            $this->db->where_in($this->key_field, $forms_to_approve);
            $for_approval = $this->db->get( $this->module_table );*/
            $approval_counter = "";
            //if(  $for_approval->num_rows() > 0 ) $approval_counter = '<span class="bg-orange ctr-inline">' . $for_approval->num_rows() . '</span>';
            if(  $forms_to_approve_count > 0 ) $approval_counter = '<span class="bg-orange ctr-inline">' . $forms_to_approve_count . '</span>';
            $tabs[] = '<li filter="for_approval"><a href="javascript:void(0)">For Approval '. $approval_counter .'</li>';
            $tabs[] = '<li filter="approved"><a href="javascript:void(0)">Approved</li>';
            $tabs[] = '<li filter="disapproved"><a href="javascript:void(0)">Disapproved</li>';
            $tabs[] = '<li filter="cancelled"><a href="javascript:void(0)">Cancelled</li>'; 
        }

        if( sizeof( $tabs ) > 1 ) $data['tab'] = addslashes('<ul id="grid-filter">'. implode('', $tabs) .'</ul>');

        if ($this->session->flashdata('flashdata')) {
            $info['flashdata'] = $this->session->flashdata('flashdata');
            $data['flashdata'] = $this->load->view($this->userinfo['rtheme'] . '/template/flashdata', $info, true);
        }
        
        //set default columnlist
        $this->_set_listview_query();

        //set grid buttons
        $data['jqg_buttons'] = $this->_default_grid_buttons();

        //set load jqgrid loadComplete callback
        $data['jqgrid_loadComplete'] = 'init_filter_tabs();';

        //load variables to env
        $this->load->vars($data);

        //load the final view
        $this->_render_view();
    }

    function detail() {
        parent::detail();

        //additional module detail routine here
        $data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/js/detailview.js"></script>';

        if( CLIENT_DIR != "" && file_exists(FCPATH . "lib\modules\client\\". CLIENT_DIR ."\\forms\\formsmain.js")){
            $data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/modules/client/'. CLIENT_DIR .'/forms/formsmain.js"></script>';
        }
        else{ 
            $data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/modules/forms/formsmain.js"></script>';
        } 

        $data['content'] = 'detailview';

        //other views to load
        $data['views'] = array();

        if (!empty($this->module_wizard_form) || $this->input->post('record_id') == '-1') {
            $data['show_wizard_control'] = true;
        }

        //check status
        $rec = $this->db->get_where( $this->module_table, array( $this->key_field => $this->input->post('record_id') ) )->row();

        if( $rec->form_status_id == 2 ){
            if( $rec->employee_id == $this->user->user_id )
                $data['buttons'] = 'template/detail-no-buttons';
            else{
                
                //check for approver buttons
                $approver = $this->db->get_where('form_approver', array('module_id' => $this->module_id,'approver' => $this->user->user_id, 'record_id' => $this->key_field_val));
               
                if( $approver->num_rows() == 0 && !( $this->user_access[$this->module_id]['post'] == 1 || $this->is_admin || $this->is_superadmin)){
                    $this->session->set_flashdata( 'flashdata', 'You do not have sufficient privilege to view the requested record! Please contact the System Administrator.' );
                    redirect( base_url().$this->module_link );  
                }

                $approver = $approver->row();
                if( $approver->status == 2 ){
                    $data['buttons'] = 'leaves/approve-button';
                }
                else{
                    if (CLIENT_DIR == 'firstbalfour'){
                        $data['buttons'] = 'leaves/approve-button';
                    }
                    else{
                        $data['buttons'] = 'template/detail-no-buttons';
                    }
                }
            }
        }

        if( $rec->form_status_id == 3 && $rec->employee_id != $this->user->user_id && $this->user_access[$this->module_id]['cancel'] == 1 ){
            $data['buttons'] = 'forms/cancel-button';
        }

        if( $rec->form_status_id == 3 && $rec->employee_id == $this->user->user_id ) {
            $data['buttons'] = 'template/detail-no-buttons';
        }

        if( $rec->form_status_id > 3 || ( $rec->form_status_id == 1 && $rec->employee_id != $this->user->user_id ) ) $data['buttons'] = 'template/detail-no-buttons';
        
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

        //load variables to env
        $this->load->vars($data);

        //load the final view
        $this->_render_view();
    }

    function edit() {
        
        if ($this->user_access[$this->module_id]['edit'] == 1) {
            $this->load->helper('form');

            if( $this->input->post('record_id') != -1 ){
                //check status
                $rec = $this->db->get_where( $this->module_table, array( $this->key_field => $this->input->post('record_id') ) )->row();
                if( $rec->form_status_id != 1 ){
                    $this->session->set_flashdata( 'flashdata', 'Data is locked for editing, please call the Administrator.' );
                    redirect( base_url().$this->module_link.'/detail/'. $this->input->post('record_id') );
                }
            }

            parent::edit();

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

    function ajax_save() {
        $restricted = false;

        if(!( $this->is_superadmin || $this->is_admin ) && $this->user_access[$this->module_id]['post'] != 1){
            if( $this->input->post("employee_id") != $this->user->user_id ){
                $restricted = true;
            }
        }

        if( !$restricted ){
            $this->load->helper('date');
            if( empty($_POST['form_status_id']) ) $data['form_status_id'] = 1;
            if ($this->input->post('record_id') == '-1') {
                $data['date_created'] = date('Y-m-d H:i:s');
                if( $this->user_access[$this->module_id]['post'] == 1 && $_POST['filter'] != 'personal' ){
                    $data['form_status_id'] = 3;
                    $data['date_approved'] = date('Y-m-d H:i:s');
                }
            }

            parent::ajax_save();

            if ($this->key_field_val) {
                $this->db->where('record_id', $this->input->post('record_id'));
                $this->db->where('module_id', $this->module_id);
                // $this->db->where('focus', 1);
                $this->db->order_by('sequence', 'desc');
                $approver_user = $this->db->get('form_approver');
                
                if ($this->input->post('record_id') == '-1' && !( $approver_user && $approver_user->num_rows() > 0 )) {
                    $approvers = $this->system->get_approvers_and_condition( $this->input->post('employee_id'), $this->module_id );
                    
                    $id_in_approver = false;
                    foreach($approvers as $approver){
                        $approver['record_id'] = $this->key_field_val;
                        $approver['module_id'] = $this->module_id;
                        if(CLIENT_DIR == "firstbalfour"){
                            //just display approver's name who filed the form
                            if( $this->user_access[$this->module_id]['post'] == 1 && $_POST['filter'] != 'personal' && $this->user->user_id == $approver['approver']){
                                $approver['status'] = 3;
                                $id_in_approver = true;
                            }
                        }else{
                            if( $this->user_access[$this->module_id]['post'] == 1 && $_POST['filter'] != 'personal' ){
                                $approver['status'] = 3;
                            }
                        }
                        
                        $this->db->insert('form_approver', $approver);
                    }

                    if(CLIENT_DIR == "firstbalfour"){
                        //insert approver not in employee approvers' list but has admin rights
                        if( (!$id_in_approver) && $this->input->post("employee_id") != $this->user->user_id){                   
                            $filed_by_admin_rights = array(
                                'approver' => $this->user->user_id,
                                'sequence' => 1,
                                'condition' => 2,
                                'focus' => 1,
                                'status' => 3
                            );
                            $filed_by_admin_rights['record_id'] = $this->key_field_val;
                            $filed_by_admin_rights['module_id'] = $this->module_id;

                            $this->db->insert('form_approver', $filed_by_admin_rights);
                        }
                    }
                }

                // Set dates.
                $data['date_updated'] = date('Y-m-d H:i:s');

                $this->db->where($this->key_field, $this->key_field_val);
                $this->db->update($this->module_table, $data);
            }
        }
        else{
            $response->msg = "You dont have sufficient privilege to execute the action requested! Please contact the System Administrator.";
            $response->msg_type = 'error'; 
            $this->load->vars(array('json' => $response));
            $this->load->view($this->userinfo['rtheme'].'/template/ajax');  
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

    function quick_edit( $customview = "" )
    {
        if( IS_AJAX ){
            $response->msg = "";
            if( !isset($_POST['record_id']) && $this->uri->rsegment(3) ) $_POST['record_id'] = $this->uri->rsegment(3);
            if( $this->input->post( 'record_id' ) ){
                
                if( ($this->input->post( 'record_id' ) == "-1" && $this->user_access[$this->module_id]['add'] == 1) || ($this->input->post( 'record_id' ) != "-1" && $this->user_access[$this->module_id]['edit'] == 1) ){
                    $data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/modules/'.$this->module_link.'.js"></script>';
                    $data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/jquery/jquery.maskedinput-1.3.min.js"></script>';
                    $data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/js/editview.js"></script>';
                    $data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/modules/forms/formsmain.js"></script>';
                    
                    $data['module'] = $this->module;
                    $data['module_link'] = $this->module_link;
                    $data['fmlinkctr'] = $this->input->post( 'fmlinkctr' );

                    $this->load->model( 'uitype_edit' );
                    $data['fieldgroups'] = $this->_record_detail( $this->input->post('record_id' ), true);

                    $data['show_wizard_control'] = false;
                    if( !empty($this->module_wizard_form) && $this->input->post('record_id') == '-1' ){
                        $data['show_wizard_control'] = true;
                        $data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/js/editview-wizard-form.js"></script>';
                    }

                    //other views to load
                    $data['views'] = array();

                    //load the final view
                    $this->load->vars($data);
                    if( isset($_POST['mode']) && $_POST['mode'] == 'copy' ) $_POST['record_id'] = -1;
                    $response->quickedit_form = $this->load->view( $this->userinfo['rtheme']. $customview ."/quickedit" , "", true );
                }
                else{
                    $response->msg = "You dont have sufficient privilege to execute the action! Please contact the System Administrator.";
                    $response->msg_type = 'attention';  
                }
            }
            else{
                $response->msg = "Insufficient data supplied.";
                $response->msg_type = 'attention';
            }
            
            $data['json'] = $response;
            $this->load->view($this->userinfo['rtheme'].'/template/ajax', $data);
        }
        else{
            $this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
            redirect(base_url().$this->module_link);
        }
    }

    // END - default module functions
    // START custom module funtions

    function change_status_multiple($record_id = 0){

        $form_status_id = $this->input->post('form_status_id');
        $record_id = explode(',',$this->input->post('record_id'));

        $this->db->where_in($this->key_field, $record_id);
        $result = $this->db->get($this->module_table);

        $err_ctr = 0;
        $err_msg = array();
        $success_ctr = 0;
        $status_record = "";
        $total_ctr = 0;

        $status = "";
        switch($form_status_id){
            case 3:
            $status = "Approved";
            break;
            case 4:
            $status = "Disapproved";
            break;
        }

        $response['sequence'] ="";

        foreach( $result->result_array() as $record ){

            $response['sequence'] .= ','.$record[$this->key_field];

            $rec = $this->db->get_where( $this->module_table, array( $this->key_field => $record[$this->key_field] ) )->row();

            if( $this->_can_approve($rec) || $this->_can_decline($rec)  ){
                $status_result = $this->change_status($record[$this->key_field],1);

                if( $status_result['json']['type'] == 'error' ){
                    $err_ctr++;
                }
                else{
                    $success_ctr++;
                }

            }else{
                $err_ctr++;
            }

            $total_ctr++;
            
        }

        if( $err_ctr == 0 ){

            if( $success_ctr > 1 ){
                $response['message'] = $success_ctr.' out of '.$total_ctr.' Form Application(s) have been '.$status;
            }
            else{
                $response['message'] = $success_ctr.' out of '.$total_ctr.' Form Application(s) has been '.$status;
            }
                                            
            $response['type'] = 'success';
            $data['json'] = $response;

            $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);

        }
        else{

            if( $success_ctr > 1 ){
                $response['message'] = $success_ctr.' out of '.$total_ctr.' Form Application(s) have been '.$status.'<br /> Please check on those not approved <br />'; 
            }
            else{
                $response['message'] = $success_ctr.' out of '.$total_ctr.' Form Application(s) has been '.$status.'<br /> Please check on those not approved <br />'; 
            }                                           

            $response['type'] = 'error';
            $data['json'] = $response;

            $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
        }

    }

    function change_status($record_id = 0, $non_ajax = 0) {
        if ( $this->input->post('record_id') && $non_ajax == 0 ) {
            $record_id = $this->input->post('record_id');
        }
        $form_status_id = $this->input->post('form_status_id');
        $this->db->where($this->key_field, $record_id);
        $result = $this->db->get($this->module_table);
        $request = $result->row_array();

        $approver = $this->db->get_where('form_approver', array('approver' => $this->user->user_id, 'module_id' => $this->module_id, 'record_id' => $record_id));

        $to_check = true;
        
        switch ($this->module_table) {
            case 'employee_oot':
                    $this->db->where('application_code','OT');    
                    $date = $request['datetime_from'];                       
                break;
            case 'employee_obt':
                    $this->db->where('application_code','OBT');   
                    $date = $request['date_from'];                 
                break;
            case 'employee_et':
                    $this->db->where('application_code','ET');  
                    $date = $request['datelate'];                   
                break;
            case 'employee_out':
                    $this->db->where('application_code','UT');     
                    $date = $request['date'];                    
                break; 
            case 'employee_cws':
                    $this->db->where('application_code','CWS');
                    $date = $request['date_to'];                           
                break;  
            case 'employee_dtrp':
                    $this->db->where('application_code','DTRP');    
                    $date = $request['date'];                      
            break;                                                                                                                            
        }

        $form_info = $this->db->get('employee_form_type')->row();

        //Validation for approval and 
        if ($this->system->check_cutoff_policy_forms($request['employee_id'],$form_status_id,$form_info->application_form_id,$date,$date) == 1):
            $response->msg = "Next payroll cutoff not yet created in processing, please contact HRA.";
            $response->msg_type = "error";
            $data['json'] = $response;
            $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
            $to_check = false;
        elseif ($this->system->check_cutoff_policy_forms($request['employee_id'],$form_status_id,$form_info->application_form_id,$date,$date) == 2):
            if( $form_status_id == 3 ){
                $response->message = "Approval/Disapproval is not within the allowable time.";
            }
            elseif( $form_status_id == 4 ){
                $response->message = "Approval/Disapproval is not within the allowable time.";
            }
            elseif( $form_status_id == 5 ){
                $response->message = "Cancellation is not within the allowable time.";
            }
            $response->type = "error";
            $data['json'] = $response;
            $to_check = false;
        endif;

        // Check if current user is part of approvers.
        if ( IS_AJAX ) {
            if( $approver->num_rows() == 1 || ($this->is_admin || $this->is_superadmin) || ($this->user_access[$this->module_id]['post'] == 1 && CLIENT_DIR == 'firstbalfour')){

                $approver = $approver->row();
                $this->load->helper('date');

                if ($this->input->post('form_status_id') >= 3 && $this->input->post('form_status_id') <= 5){                
                    //next cutoff validation -tirso
                    $type = ""; 
                    switch ($this->module_table) {
                        case 'employee_oot':
                                $type = "oot";
                                $date = $request['datetime_from'];                              
                            break;
                        case 'employee_obt':
                                $date = $request['date_from'];
                                $type = "obt";                           
                            break;
                        case 'employee_et':
                                $date = $request['datelate'];
                                $type = "et";                      
                            break;
                        case 'employee_out':
                                $date = $request['date'];
                                $type = "ut";                          
                            break; 
                        case 'employee_cws':
                                $date = $request['date_to'];
                                $type = "cws";                            
                            break;  
                        case 'employee_dtrp':
                                $date = $request['date'];
                                $type = "dtrp";                           
                            break;                                                                                                                            
                    }   
                    
                    $type_message = $form_info->application_form;

                    if ($this->input->post('form_status_id') == 3){
                        switch ($this->module_table) {
                            case 'employee_oot':
                                    //check if there is already approved application with the same date
                                    $this->db->where('deleted',0);
                                    $this->db->where('employee_id',$request['employee_id']);
                                    $this->db->where('form_status_id',3);
                                    //$this->db->where('employee_oot_id',$record_id);
                                    $this->db->where(
                                        '(
                                            ("'. $request['datetime_from'] .'" > datetime_from AND "'. $request['datetime_from'] .'" < datetime_to)
                                            OR
                                            ("'. $request['datetime_to'] .'" > datetime_from AND "'. $request['datetime_to'] .'" < datetime_to)
                                         )',
                                        '', 
                                        false
                                        );                                
                                    $result = $this->db->get('employee_oot');
                                    //$response['q'] = $this->db->last_query();
                                    if( $result->num_rows > 0  ){
                                        $to_check = false;                                      
                                        $response['record_id'] = $this->input->post('record_id'); 
                                        $response['message'] = $type_message . " application has already been approved.";                 
                                        $response['type'] = 'error';
                                        $data['json'] = $response;  
                                    }                               
                                break;
                            case 'employee_obt':
                                    //check if there is already approved application with the same date
                                    $result = $this->db->query("SELECT * FROM {$this->db->dbprefix}employee_obt WHERE employee_id = ".$request['employee_id']."
                                                                 AND  deleted = 0 AND form_status_id=3
                                                                 AND (((date_from < '".$request['date_from']."' AND date_to > '".$request['date_from']."')
                                                                 OR (date_from < '".$request['date_to']."' AND date_to > '".$request['date_to']."'))
                                                                 AND ((time_start <= '".$request['time_start']."' AND time_end >= '".$request['time_start']."') 
                                                                 OR (time_start <= '".$request['time_end']."' AND time_end >= '".$request['time_end']."'))) ");
                                    if( $result->num_rows > 0  ){
                                        $to_check = false;                                      
                                        $response['record_id'] = $this->input->post('record_id'); 
                                        $response['message'] = $type_message . " application has already been approved.";                 
                                        $response['type'] = 'error';
                                        $data['json'] = $response;  
                                    }                             
                                break;
                            case 'employee_et':
                                    //check if there is already approved application with the same date
                                    $qry = "SELECT *
                                    FROM {$this->db->dbprefix}employee_et
                                    WHERE deleted = 0 AND form_status_id=3 AND employee_id = '{$request['employee_id']}' AND datelate = '{$date}'"; //AND employee_et_id = '{$record_id}'
                                    $result = $this->db->query( $qry );
                                    if( $result->num_rows > 0  ){
                                        $to_check = false;                                      
                                        $response['record_id'] = $this->input->post('record_id'); 
                                        $response['message'] = $type_message . " application has already been approved.";                 
                                        $response['type'] = 'error';
                                        $data['json'] = $response;  
                                    }                             
                                break;
                            case 'employee_out':
                                    //check if there is already approved application with the same date
                                    $qry = "SELECT *
                                    FROM {$this->db->dbprefix}employee_out
                                    WHERE deleted = 0 AND employee_id = '{$request['employee_id']}' AND form_status_id=3 AND
                                    ( 
                                        ('{$request['time_start']}' BETWEEN time_start AND time_end) OR
                                        ('{$request['time_end']}' BETWEEN time_start AND time_end)                    
                                    ) AND
                                    date = '{$date}'"; //AND employee_out_id = '{$record_id}' 
                                    $result = $this->db->query( $qry ); 
                                    if( $result->num_rows > 0  ){
                                        $to_check = false;                                      
                                        $response['record_id'] = $this->input->post('record_id'); 
                                        $response['message'] = $type_message . " application has already been approved.";                 
                                        $response['type'] = 'error';
                                        $data['json'] = $response;  
                                    }                              
                                break; 
                            case 'employee_cws':
                                    $result = $this->db->query("SELECT * FROM {$this->db->dbprefix}employee_cws WHERE employee_id = ".$request['employee_id']."
                                                                 AND  deleted = 0 AND form_status_id=3
                                                                 AND ((date_from <= '".$request['date_from']."' AND date_to >= '".$request['date_from']."')
                                                                 OR (date_from <= '".$request['date_to']."' AND date_to >= '".$request['date_to']."'))"); // AND employee_cws_id = '{$record_id}
                                    if( $result->num_rows > 0  ){
                                        $to_check = false;                                      
                                        $response['record_id'] = $this->input->post('record_id'); 
                                        $response['message'] = $type_message . " application has already been approved.";                 
                                        $response['type'] = 'error';
                                        $data['json'] = $response;  
                                    }                              
                                break;  
                            case 'employee_dtrp':
                                    //check if there is already approved application with the same date
                                    $qry = "SELECT *
                                    FROM {$this->db->dbprefix}employee_dtrp
                                    WHERE deleted = 0 AND form_status_id=3 AND employee_id = '{$request['employee_id']}'  AND date = '{$date}' AND time_set_id ={$request['time_set_id']}"; //AND employee_dtrp_id = '{$record_id}'
                                    $result = $this->db->query( $qry );
                                    if( $result->num_rows > 0  ){
                                        $to_check = false;                                      
                                        $response['record_id'] = $this->input->post('record_id'); 
                                        $response['message'] = $type_message . " application has already been approved.";                 
                                        $response['type'] = 'error';
                                        $data['json'] = $response;  
                                    }                             
                                break;                                                                                                                            
                        }
                    }

                    switch ($this->input->post('form_status_id')) {
                        case 3:
                                $msg = 'approval';
                            break;
                        case 4:
                                $msg = 'disapproval';
                            break;
                        case 5:
                                $msg = 'cancellation';
                            break;                                        
                    }

                    // allowable time config
                    if($this->config->item('allowable_time_to_approve_cws') && $this->config->item('allowable_time_to_approve_cws') != '' && $this->config->item('allowable_time_to_approve_cws') > 0 && $type == 'cws') {
                        $cws = $this->db->get_where('employee_cws', array('employee_cws_id' => $record_id))->row();
                        $dates_affected = $this->system->get_affected_dates( $request['employee_id'], $cws->date_created, date('Y-m-d'), true, true );

                        if (count($dates_affected) > $this->config->item('allowable_time_to_approve_cws')){
                            $response['type'] = 'error';
                            if(CLIENT_DIR == 'oams'){
                            $response['message'] = 'Allowable time for approval / disapproval has already passed.';
                            }else{
                            $response['message'] = $this->config->item('allowable_time_to_approve_cws').' days have passed, no longer within the allowable time to processed the application.';
                            }
                            $to_check = false;
                        }
                    }
                    elseif($this->config->item('maxtime_to_approve_overtime') && $this->config->item('maxtime_to_approve_overtime') != '' && $this->config->item('maxtime_to_approve_overtime') > 0 && $type == 'oot') {
                        $oot = $this->db->get_where('employee_oot', array('employee_oot_id' => $record_id))->row();
                        $dates_affected = $this->system->get_affected_dates( $request['employee_id'], $oot->date_created, date('Y-m-d'), true, true );

                        if (count($dates_affected) > $this->config->item('maxtime_to_approve_overtime')){
                            $response['type'] = 'error';
                            if(CLIENT_DIR == 'oams'){
                            $response['message'] = 'Allowable time for approval / disapproval has already passed.';
                            }else{
                            $response['message'] = $this->config->item('maxtime_to_approve_overtime').' days have passed, no longer within the allowable time to processed the application.';
                            }
							$to_check = false;
                        }
                    }  
                    elseif($this->config->item('maxtime_to_approve_dtrp') && $this->config->item('maxtime_to_approve_dtrp') != '' && $this->config->item('maxtime_to_approve_dtrp') > 0 && $type == 'dtrp') {
                        $dtrp = $this->db->get_where('employee_dtrp', array('employee_dtrp_id' => $record_id))->row();
                        $dates_affected = $this->system->get_affected_dates( $request['employee_id'], $dtrp->date_created, date('Y-m-d'), true, true );
                        
                        if (count($dates_affected) > $this->config->item('maxtime_to_approve_dtrp')){
                            $response['type'] = 'error';
                            if(CLIENT_DIR == 'oams'){
                            $response['message'] = 'Allowable time for approval / disapproval has already passed.';
                            }else{
                            $response['message'] = $this->config->item('maxtime_to_approve_dtrp').' days have passed, no longer within the allowable time to processed the application.';
                            }
							$to_check = false;
                        }
                    }
                    else {
                        if (date('Y-m-d') > date('Y-m-d',strtotime($date))):
                            if ($this->module_table != 'employee_cws'):
                                // if ($this->system->check_in_cutoff($date) == 1):
                                //     $response['type'] = 'error';
                                //     $response['message'] = 'Next payroll cutoff not yet created in processing, please contact admin.';                              
                                //     $to_check = false;
                                // elseif ($this->system->check_in_cutoff($date) == 2):
                                //     $response['type'] = 'error';
                                //     // $response['message'] = 'You can not approve '. $type .' exceeded within the next payroll cuttoff.<br/>Please call the Administrator.';                              
                                //     $response['message'] = 'Sorry, your '.$msg.' can no longer be processed. It exceeded the grace period.';
                                //     $to_check = false;                            
                                // endif;
                            else:
                                //if ($this->config->item("client_no") == 1){   //tirso - modify it for hdi
                                
                                $setup = $this->system->check_cutoff_policy_forms($request['employee_id'],$this->input->post('form_status_id'),12,$request['date_from'],$date);
                              
                                if ($setup == 2) {
                                    if ($this->module_table == 'employee_cws'):
                                        $response['type'] = 'error';
                                        $response['message'] = 'Sorry, your '.$msg.' can no longer be processed. It exceeded the grace period.';
                                        $to_check = false;
                                    endif;
                                }elseif ($setup == 1) {
                                    $response->type = 'error';
                                    $response->message = 'Next payroll cutoff not yet created in processing, please contact admin.';                              
                                    $to_check = false;
                                }

                                // if (CLIENT_DIR != "hdi"){
                                //     if ($this->module_table == 'employee_cws'):
                                //         $response['type'] = 'error';
                                //         $response['message'] = 'Sorry, your '.$msg.' can no longer be processed. It exceeded the grace period.';
                                //         $to_check = false;
                                //     endif;
                                // }
                                // elseif ($this->config->item("client_no") == 2){
                                //     if ($this->system->check_in_cutoff($date) == 1):
                                //         $response['type'] = 'error';
                                //         $response['message'] = 'Next payroll cutoff not yet created in processing, please contact admin.';                              
                                //         $to_check = false;
                                //     elseif ($this->system->check_in_cutoff($date) == 2):
                                //         $response['type'] = 'error';
                                //         $response['message'] = 'Sorry, your '.$msg.' can no longer be processed. It exceeded the grace period.';
                                //         $to_check = false;                            
                                //     endif;                                
                                // }   
                            endif;                        
                        endif;

                    }
                }
                
                if ($to_check){
                    switch( $this->input->post('form_status_id') ){
                        case 3:
                            $returnstatus = 'approved';
                            break;
                        case 4: 
                            $returnstatus = 'disapproved';
                            break;
                        case 5: 
                            $returnstatus = 'cancelled';
                            break;  
                    }
                    $response['message'] = 'Request ' . $returnstatus;

                    if (CLIENT_DIR == 'firstbalfour' && $this->user_access[$this->module_id]['post'] == 1){
                        // commented to just display approver's name who approved the form
                        // $this->db->update('form_approver', array('status' => $this->input->post('form_status_id')), array('module_id' => $this->module_id, 'record_id' => $record_id));
                        $this->db->update('form_approver', array('status' => $this->input->post('form_status_id')), array('approver' => $this->user->user_id, 'module_id' => $this->module_id, 'record_id' => $record_id));

                        switch( $this->input->post('form_status_id') ){
                            case 3:
                                $data['form_status_id'] = 3;
                                $data['date_approved'] = date('Y-m-d H:i:s');
                                $this->db->where($this->key_field, $record_id);
                                $this->db->update($this->module_table, $data);  
                                $this->send_status_email($record_id,3);
                                break;
                            case 4:
                                $data['decline_remarks'] = $this->input->post('decline_remarks');
                                $data['date_approved'] = date('Y-m-d H:i:s');
                                $data['form_status_id'] = 4;
                                $this->db->where($this->key_field, $record_id);
                                $this->db->update($this->module_table, $data);
                                $this->send_status_email($record_id,4, $this->input->post('decline_remarks'));
                                break;
                            case 5:
                                $data['date_approved'] = date('Y-m-d H:i:s');
                                $data['form_status_id'] = 5;
                                $this->db->where($this->key_field, $record_id);
                                $this->db->update($this->module_table, $data);
                                $this->send_status_email($record_id,5);
                                break;
                        }
                    }
                    else{
                        $this->db->update('form_approver', array('status' => $this->input->post('form_status_id')), array('approver' => $this->user->user_id, 'module_id' => $this->module_id, 'record_id' => $record_id));
                                            
                        switch( $this->input->post('form_status_id') ){
                            case 3:
                                switch( $approver->condition ){
                                    case 1: //by level
                                        //get next approver
                                        $next_approver = $this->db->get_where('form_approver', array('sequence' => ($approver->sequence+1), 'module_id' => $this->module_id,'record_id' => $record_id));
                                        if( $next_approver->num_rows() == 1 ){
                                            $next_approver = $next_approver->row();
                                            $this->db->update('form_approver', array('focus' => 1, 'status' => 2), array('sequence' => $next_approver->sequence, 'module_id' => $this->module_id,'record_id' => $record_id));
                                            //email next approver
                                            $this->send_email();
                                        }
                                        else{
                                            //this is last approver
                                            $data['form_status_id'] = 3;
                                            $data['date_approved'] = date('Y-m-d H:i:s');
                                            $this->db->where($this->key_field, $record_id);
                                            $this->db->update($this->module_table, $data);
                                            $this->send_status_email($record_id,3);
                                        }
                                        break;
                                    case 2: // Either
                                        $data['form_status_id'] = 3;
                                        $data['date_approved'] = date('Y-m-d H:i:s');
                                        $this->db->where($this->key_field, $record_id);
                                        $this->db->update($this->module_table, $data);
                                        $this->send_status_email($record_id,3);
                                        break;
                                    case 3: // All
                                        $qry = "SELECT * FROM {$this->db->dbprefix}form_approver where module_id = {$this->module_id} AND record_id = {$record_id} and status != 3";
                                        $all_approvers = $this->db->query( $qry );
                                        if( $all_approvers->num_rows() == 0 ){
                                            $data['form_status_id'] = 3;
                                            $data['date_approved'] = date('Y-m-d H:i:s');
                                            $this->db->where($this->key_field, $record_id);
                                            $this->db->update($this->module_table, $data);  
                                            $this->send_status_email($record_id,3);
                                        }
                                        break;  
                                }
                                break;
                            case 4:
                                $data['decline_remarks'] = $this->input->post('decline_remarks');
                                $data['date_approved'] = date('Y-m-d H:i:s');
                                $data['form_status_id'] = 4;
                                $this->db->where($this->key_field, $record_id);
                                $this->db->update($this->module_table, $data);
                                $this->send_status_email($record_id,4, $this->input->post('decline_remarks'));
                                break;
                            case 5:
                                $data['date_approved'] = date('Y-m-d H:i:s');
                                $data['form_status_id'] = 5;
                                $this->db->where($this->key_field, $record_id);
                                $this->db->update($this->module_table, $data);
                                $this->send_status_email($record_id,5);
                                break;
                        }
                    }
                                
                    $response['type'] = 'success';
                }
            }
            else if( $this->user_access[$this->module_id]['post'] == 1 && $form_status_id == 5 ){
                $response['message'] = 'Request cancelled';
                $response['type'] = 'success';
                $data['date_approved'] = date('Y-m-d H:i:s');
                $data['form_status_id'] = 5;
                $this->db->where($this->key_field, $record_id);
                $this->db->update($this->module_table, $data);
                $this->send_status_email($record_id,5);
            }
            else{
                $response['type'] = 'error';
                $response['message'] = 'You do not have sufficient privilege to execute this operation.<br/>Please call the Administrator.';  
            }
            $data['json'] = $response;

            if( $non_ajax == 0 ){
                $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
            }
            else{
                return $data;
            }
        } else {
            $this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
            redirect(base_url() . $this->module_link);
        }
    }

    function _default_grid_buttons( $module_link = "", $related_field = "", $related_field_value = "", $addtext = "", $deltext = "", $container = "" )
    {
        // set default
        if($module_link == "") $module_link = $this->module_link;
        if($addtext == "") $addtext = "Add";
        if($deltext == "") $deltext = "Delete";
        if($container == "") $container = "jqgridcontainer";

        $buttons = "<div class='icon-label-group'>";                    
                            
        if ($this->user_access[$this->module_id]['add']) {
            $buttons .= "<div class='icon-label'>";
            $buttons .= "<a class='icon-16-add icon-16-add-listview' related_field='".$related_field."' related_field_value='".$related_field_value."' module_link='".$module_link."' href='javascript:void(0)'>";
            $buttons .= "<span>".$addtext."</span></a></div>";
        }
         
        if ($this->user_access[$this->module_id]['delete']) {
            $buttons .= "<div class='icon-label'><a class='icon-16-delete delete-array' container='".$container."' module_link='".$module_link."' href='javascript:void(0)'><span>".$deltext."</span></a></div>";                            
        }

        if ( get_export_options( $this->module_id ) ) {
            $buttons .= "<div class='icon-label'><a class='icon-16-export module-export' container='".$container."' module_link='".$module_link."' href='javascript:void(0)'><span>Export</span></a></div>";
            $buttons .= "<div class='icon-label'><a class='icon-16-import module-import' container='".$container."' module_link='".$module_link."' href='javascript:void(0)'><span>Import</span></a></div>";
        }

        if ($this->user_access[$this->module_id]['approve']) {
            $buttons .= "<div class='icon-label'><a class='icon-16-approve approve-array status-buttons' container='".$container."' module_link='".$module_link."' href='javascript:void(0)'><span>Approve</span></a></div>";
        }

        if ($this->user_access[$this->module_id]['decline']) {
            $buttons .= "<div class='icon-label'><a class='".(CLIENT_DIR == 'hdi' || CLIENT_DIR == 'basf' ? 'icon-16-disapprove' : 'icon-16-cancel')." disapprove-array status-buttons' container='".$container."' module_link='".$module_link."' href='javascript:void(0)'><span>Disapprove</span></a></div>";
        } 
        
        $buttons .= "</div>";
                
        return $buttons;
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
            $actions .= '<a class="icon-button icon-16-cancel cancel-single" tooltip="Cancel" container="' . $container . '" module_link="' . $module_link . '" href="javascript:void(0)"></a>';
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
        if ($this->module_table == 'employee_oot' && $this->user_access[$this->module_id]['edit'] && in_array( $rec->form_status_id, array(2,3,6,7) )) {
            $actions .= '<a class="icon-button icon-16-clock-extend" tooltip="Extend" href="javascript:void(0)" module_link="' . $module_link . '" ></a>';
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

    private function _can_approve( $rec ) {
        
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

    function listview()
    {
        $response->msg = "";

        $page = $this->input->post('page');
        $limit = $this->input->post('rows'); // get how many rows we want to have into the grid
        $sidx = $this->input->post('sidx'); // get index row - i.e. user click to sort
        $sord = $this->input->post('sord'); // get the direction
        $related_module = ( $this->input->post('related_module') ? true : false );

        $view_actions = (isset($_POST['view']) && $_POST['view'] == 'detail') ? false : true ;

        //set columnlist and select qry
        $this->_set_listview_query( '', $view_actions );

        //set Search Qry string
        if($this->input->post('_search') == "true")
            $search = $this->input->post('searchField') == "all" ? $this->_set_search_all_query() : $this->_set_specific_search_query();
        else
            $search = 1;

        if( $this->module == "user" && (!$this->is_admin && !$this->is_superadmin) ) $search .= ' AND '.$this->db->dbprefix.'user.user_id NOT IN (1,2)';


        if (method_exists($this, '_append_to_select')) {
            // Append fields to the SELECT statement via $this->listview_qry
            $this->_append_to_select();
        }

        if (method_exists($this, '_custom_join')) {
            $this->_custom_join();
        }

        /* count query */
        //build query
        $this->_set_left_join();
        $this->db->select($this->listview_qry, false);
        $this->db->from($this->module_table);
        $this->db->join('user','user.user_id = '.$this->module_table.'.employee_id','left');
        $this->db->where($this->module_table.'.deleted = 0 AND '.$search);
        if(!empty( $this->filter ) )    $this->db->where( $this->filter );

        if (method_exists($this, '_set_filter')) {
            $this->_set_filter();
        }

        //get list
        $result = $this->db->get();
        $response->last_query = $this->db->last_query();
        if( $this->db->_error_message() != "" ){
            $response->msg = $this->db->_error_message();
            $response->msg_type = "error";
        }
        else{
            $total_pages = $result->num_rows() > 0 ? ceil($result->num_rows()/$limit) : 0;
            $response->page = $page > $total_pages ? $total_pages : $page;
            $response->total = $total_pages;
            $response->records = $result->num_rows();

            /* record query */
            //build query
            $this->_set_left_join();
            $this->db->select($this->listview_qry, false);
            $this->db->from($this->module_table);
            $this->db->join('user','user.user_id = '.$this->module_table.'.employee_id','left');
            $this->db->where($this->module_table.'.deleted = 0 AND '.$search);

            if(!empty( $this->filter ) )    $this->db->where( $this->filter );
            
            if (method_exists($this, '_set_filter')) {
                $this->_set_filter();
            }

            if (method_exists($this, '_custom_join')) {
                // Append fields to the SELECT statement via $this->listview_qry
                $this->_custom_join();
            }
            
            if($sidx != ""){
                
                $this->db->order_by($sidx, $sord);
            }
            else{
                if( is_array($this->default_sort_col) ){
                    $sort = implode(', ', $this->default_sort_col);
                    $this->db->order_by($sort);
                }
            }
            $start = $limit * $page - $limit;
            $this->db->limit($limit, $start);
            
            $result = $this->db->get();

            //$response->last_query = $this->db->last_query();

            //check what column to add if this is a related module
            if($related_module){
                foreach($this->listview_columns as $column){                                    
                    if($column['name'] != "action"){
                        $temp = explode('.', $column['name']);
                        if(strpos($this->input->post('column'), ',')){
                            $column_lists = explode( ',', $this->input->post('column'));
                            if( sizeof($temp) > 1 && in_array($temp[1], $column_lists ) ) $column_to_add[] = $column['name'];
                        }
                        else{
                            if( sizeof($temp) > 1  && $temp[1] == $this->input->post('column')) $this->related_module_add_column = $column['name'];
                        }
                    }
                }
                //in case specified related column not in listview columns, default to 1st column
                if( !isset($this->related_module_add_column) ){
                    if(sizeof($column_to_add) > 0)
                        $this->related_module_add_column = implode('~', $column_to_add );
                    else
                        $this->related_module_add_column = $this->listview_columns[0]['name'];
                }
            }

            if( $this->db->_error_message() != "" ){
                $response->msg = $this->db->_error_message();
                $response->msg_type = "error";
            }
            else{
                $response->rows = array();
                if($result->num_rows() > 0){
                    $columns_data = $result->field_data();
                    $column_type = array();
                    foreach($columns_data as $column_data){
                        $column_type[$column_data->name] = $column_data->type;
                    }
                    $this->load->model('uitype_listview');
                    $ctr = 0;
                    foreach ($result->result_array() as $row){
                        $cell = array();
                        $cell_ctr = 0;
                        foreach($this->listview_columns as $column => $detail){
                            if( preg_match('/\./', $detail['name'] ) ) {
                                $temp = explode('.', $detail['name']);
                                $detail['name'] = $temp[1];
                            }
                            
                            if(sizeof(explode(' AS ', $detail['name'])) > 1 ){
                                $as_part = explode(' AS ', $detail['name']);
                                $detail['name'] = strtolower( trim( $as_part[1] ) );
                            }
                            else if(sizeof(explode(' as ', $detail['name'])) > 1 ){
                                $as_part = explode(' as ', $detail['name']);
                                $detail['name'] = strtolower( trim( $as_part[1] ) );
                            }
                            
                            if( $detail['name'] == 'action'  ){
                                if( $view_actions ){
                                    $cell[$cell_ctr] = ( $related_module ? $this->_default_field_module_link_actions( $row[$this->key_field], $this->input->post('container'), $this->input->post('fmlinkctr'), $row ) : $this->_default_grid_actions( $this->module_link, $this->input->post('container'), $row ) );
                                    $cell_ctr++;
                                }
                            }else{
                                if( in_array( $this->listview_fields[$cell_ctr]['uitype_id'], array( 2, 5, 4, 11, 12, 17, 19, 21, 24, 27, 32, 33, 35, 36, 37, 39, 40) ) ){
                                    $this->listview_fields[$cell_ctr]['value'] = $row[$detail['name']];
                                    $cell[$cell_ctr] = $this->uitype_listview->fieldValue( $this->listview_fields[$cell_ctr] );
                                }
                                else if( in_array( $this->listview_fields[$cell_ctr]['uitype_id'], array( 3 ) ) && ( isset( $this->listview_fields[$cell_ctr]['other_info']['picklist_type'] ) && $this->listview_fields[$cell_ctr]['other_info']['picklist_type'] == 'Query' ) ){
                                    $cell[$cell_ctr] = "";
                                    foreach($this->listview_fields[$cell_ctr]['other_info']['picklistvalues'] as $picklist_val)
                                    {
                                        if($row[$detail['name']] == $picklist_val['id']) $cell[$cell_ctr] = $picklist_val['value'];
                                    }
                                }
                                else{
                                    $cell[$cell_ctr] = in_array('I', $this->listview_fields[$cell_ctr]['datatype']) || in_array('F', $this->listview_fields[$cell_ctr]['datatype']) ? number_format($row[$detail['name']], 2, '.', ',') : $row[$detail['name']];
                                }

                                if( $detail['name'] == "t4form_status" || $detail['name'] == "t3form_status" || $detail['name'] == "t2form_status"){
                                    
                                    if( $row[$detail['name']] == "Approved" ){

                                        $employee_form_info = $this->db->get_where($this->module_table,array($this->key_field => $row[$this->key_field] ))->row();

                                        if( $employee_form_info->date_approved != '0000-00-00 00:00:00'){

                                            $cell[$cell_ctr] .= "<br /><p class='blue small'>As of ".date($this->config->item('display_date_format'),strtotime($employee_form_info->date_approved))."</p>";

                                        }

                                    }

                                    $record_id =$row[$this->key_field];
                                    //get approver and status
                                    $qry = "SELECT a.*, a.status, b.form_status, CONCAT(c.firstname, ' ', c.lastname) as name
                                    FROM {$this->db->dbprefix}form_approver a
                                    LEFT JOIN {$this->db->dbprefix}form_status b on b.form_status_id = a.status
                                    LEFT JOIN {$this->db->dbprefix}user c on c.user_id = a.approver
                                    WHERE a.record_id = {$record_id} AND a.module_id = {$this->module_id} ORDER BY sequence ASC";
                                    $approvers = $this->db->query( $qry );
                                    $form = $this->db->get_where($this->module_table, array($this->key_field => $row[$this->key_field]))->row();
                                    if($approvers->num_rows() > 1){
                                        foreach($approvers->result() as $approver){
                                            $add_status = false;
                                            switch( $form->form_status_id ){
                                                case 2: // for approval
                                                case 7: // fit to work
                                                    if($approver->condition == 1){
                                                        if($approver->focus == 0) $approver->form_status = "Waiting...";
                                                        $add_status = true;
                                                    }
                                                    if($approver->condition == 2 && $approver->status == 3) $add_status = true;
                                                    if($approver->condition == 3){
                                                        if($approver->status == 2) $approver->form_status = "Waiting approval";
                                                        $add_status = true;
                                                    }
                                                    break;
                                                case 3: // approved
                                                //commented since as per ticket #1561 : remove the approver names on the list view once the application form was already approve
                                                    // if(CLIENT_DIR == "firstbalfour"){
                                                    //     if($approver->condition == 2){
                                                    //         $add_status = true;
                                                    //     }
                                                    // }else{
                                                    //     if($approver->condition == 2 && $approver->status == 3 ){
                                                    //         $add_status = true;
                                                    //     }
                                                    // }
                                                    break;
                                                case 4: // Declined
                                                    if($approver->status == 4) $add_status = true;
                                                    break;
                                                case 5: // Declined
                                                    if($approver->status == 5) $add_status = true;
                                                    break;  
                                                
                                            }


                                            if( $add_status ){
                                                $cell[$cell_ctr] .= '<br/><em class="small">';
                                                $cell[$cell_ctr] .= $approver->name .': ';
                                                switch($approver->status){
                                                    case 2:
                                                    case 6:  
                                                        $class = 'orange';
                                                        break;
                                                    case 3: 
                                                        $class = 'green';
                                                        break;  
                                                    case 4:
                                                    case 5: 
                                                        $class = 'red';
                                                        break;  
                                                }
                                                if(CLIENT_DIR == "firstbalfour"){
                                                    if($approver->status > 2){
                                                        $cell[$cell_ctr] .= '<span class="'.$class.'">'. $approver->form_status .'</span>';
                                                    }
                                                }else{
                                                        $cell[$cell_ctr] .= '<span class="'.$class.'">'. $approver->form_status .'</span>';
                                                }
                                                $cell[$cell_ctr] .= '</em>';
                                            }
                                        }
                                    }
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
        $this->load->view($this->userinfo['rtheme'].'/template/ajax', $data);
    }

    function get_approvers(){
        $data['approvers'] = $this->system->get_approvers_and_condition( $this->input->post('employee_id'), $this->module_id );
        $response->approvers = $this->load->view($this->userinfo['rtheme'].'/forms/approvers', $data, true);
        $this->load->view('template/ajax', array('json' => $response));
    }

    protected function _get_cutoff($tkp)
    {
        if($tkp->num_rows() > 0 && $tkp)
            return date($this->config->item('display_date_format_email'), strtotime($tkp->row()->cutoff));
        else
            return 'Cutoff not defined';
    }

}

/* End of file */
/* Location: system/application */