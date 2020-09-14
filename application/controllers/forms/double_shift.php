<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Double_shift extends MY_Controller
{
    function __construct()
    {
        parent::__construct();

        //set module variable values
        $this->grid_grouping = "";
        $this->related_table = array(); //table => field format

        $this->listview_title = 'Double Shift';
        $this->listview_description = '';
        $this->jqgrid_title = "List";
        $this->detailview_title = ' Info';
        $this->detailview_description = '';
        $this->editview_title = ' Add/Edit';
        $this->editview_description = '';


        $approvers = $this->system->get_approvers_and_condition($this->userinfo['user_id'], 280);

        if(!$this->user_access[$this->module_id]['post'] && !$this->user_access[$this->module_id]['publish'])
            $this->filter = $this->module_table.'.created_by = '.$this->userinfo['user_id'];

        $emp_approver = $this->_is_employee_approver($this->userinfo['user_id'], $this->module_id);
        if($emp_approver && count($emp_approver) > 0) {
            foreach($emp_approver as $row) {
                $subs[] = $row['employee_id'];
            }
        }

        $approver  = $this->system->is_module_approver( $this->module_id, $this->userinfo['position_id'] );
        if($approver && count($approver) > 0){
            foreach( $approver as $row ){
                $this->db->where('position_id',$row->position_id);
                $emp_id=$this->db->get('user')->result_array();
                foreach($emp_id as $id)
                {
                    $have_emp_approver = $this->_have_employee_approver($id['employee_id'], $this->module_id);
                    if(!$have_emp_approver)
                        $subs[] = $id['employee_id'];
                }
            }
        }
    
        if($this->input->post('filter') && $this->input->post('filter') == "for_approval") {
            $this->filter = $this->module_table.".employee_id IN (". implode(',', $subs) .") AND ".$this->db->dbprefix.$this->module_table.".form_status_id = 2";
        }

        if($this->input->post('filter') && $this->input->post('filter') == "approved"){
            $this->filter = $this->module_table.".employee_id IN (". implode(',', $subs) .") AND ".$this->db->dbprefix.$this->module_table.".form_status_id = 3";
        }

        if($this->input->post('filter') && $this->input->post('filter') == "personal") {
            $this->filter = $this->module_table.".employee_id = ".$this->userinfo['user_id'];
        }
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

        if(($this->is_superadmin || $this->is_admin ) ||  $this->user_access[$this->module_id]['post'] || $this->user_access[$this->module_id]['publish']) {
            $tabs[] = '<li class="active" filter="all"><a href="javascript:void(0)">All</li>';
        }

        $approver = $this->system->is_module_approver($this->module_id, $this->userinfo['position_id']);
        $emp_approver = $this->_is_employee_approver($this->userinfo['user_id'], $this->module_id);
        if($approver || $emp_approver){
            $tabs[] = '<li id="approve_tab" filter="for_approval"><a href="javascript:void(0)">For Approval</li>';
            $tabs[] = '<li id="approve_tab" filter="approved"><a href="javascript:void(0)">Approved</li>';
        }

        if(sizeof($tabs) > 0) {
            $tabs[] = '<li filter="personal"><a href="javascript:void(0)">Personal</li>';
            $data['tab'] = addslashes('<ul id="grid-filter">'. implode('', $tabs) .'</ul>');
        }

        //set default columnlist
        $this->_set_listview_query();

        //set grid buttons
        $data['jqg_buttons'] = $this->_default_grid_buttons();

        //set load jqgrid loadComplete callback
        $data['jqgrid_loadComplete'] = 'init_filter_tabs();';

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

        //$data['buttons'] = $this->module_link . '/detail-buttons';    

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
        if($this->user_access[$this->module_id]['edit'] == 1){
            parent::edit();

            //additional module edit routine here
            $data['show_wizard_control'] = false;
            $data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/js/editview.js"></script>';
            if( !empty($this->module_wizard_form) && $this->input->post('record_id') == '-1' ){
                $data['show_wizard_control'] = true;
                $data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/js/editview-wizard-form.js"></script>';
            }
            $data['content'] = 'editview';

            //other views to load
            $data['views'] = array();
            $data['views_outside_record_form'] = array();

            //$data['buttons'] = $this->module_link . '/edit-buttons';

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


    function _default_grid_actions( $module_link = "",  $container = "", $record = array() )
    {
        // set default
        if($module_link == "") $module_link = $this->module_link;
        if($container == "") $container = "jqgridcontainer";

        $actions = '<span class="icon-group">';
                
        if ($this->user_access[$this->module_id]['view']) {
            $actions .= '<a class="icon-button icon-16-info" module_link="'.$module_link.'" tooltip="View" href="javascript:void(0)"></a>';
        }
                
        if ($this->user_access[$this->module_id]['edit'] && $record['t2employee_update_status'] == 'For Approval') {
            $actions .= '<a class="icon-button icon-16-edit" tooltip="Edit" href="javascript:void(0)" module_link="'.$module_link.'" ></a>';
        }

        if ( $record['t2form_status'] == 'For Approval' && $this->input->post('filter') == "for_approval") {
            $actions .= '<a class="icon-button icon-16-approve" tooltip="Approve" href="javascript:void(0)"></a>';
        }

        if ( $record['t2form_status'] == 'For Approval' && $this->input->post('filter') == "for_approval") {
            $actions .= '<a class="icon-button icon-16-disapprove" tooltip="Decline" href="javascript:void(0)"></a>';
        }                
        
        if ($this->user_access[$this->module_id]['delete'] && $record['t2form_status'] == 'For Approval') {
            $actions .= '<a class="icon-button icon-16-delete delete-single" tooltip="Delete" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
        }

        $actions .= '</span>';

        return $actions;
    }

    // function _default_grid_buttons( $module_link = "", $related_field = "", $related_field_value = "", $addtext = "", $deltext = "", $container = "" )
    // {
    //   // set default
    //   if($module_link == "") $module_link = $this->module_link;
    //   if($addtext == "") $addtext = "Add";
    //   if($deltext == "") $deltext = "Delete";
    //   if($container == "") $container = "jqgridcontainer";

    //   $buttons = "<div class='icon-label-group'>";                    
                              
    //   if (!$this->user_access[$this->module_id]['post']) {
    //       $buttons .= "<div class='icon-label'>";
    //       $buttons .= "<a class='icon-16-add icon-16-add-listview' related_field='".$related_field."' related_field_value='".$related_field_value."' module_link='".$module_link."' href='javascript:void(0)'>";
    //       $buttons .= "<span>".$addtext."</span></a></div>";
    //   }

    //   // $approver = $this->system->is_module_approver( $this->module_id, $this->userinfo['position_id'] );
    //   // $emp_approver = $this->_is_employee_approver($this->userinfo['user_id'], $this->module_id);
    //   // if($approver || $emp_approver){
    //   //   $buttons .= "<div class='icon-label'><a class='icon-16-approve approve-array status-buttons' container='".$container."' module_link='".$module_link."' href='javascript:void(0)'><span>Approve</span></a></div>";
    //   //   $buttons .= "<div class='icon-label'><a class='icon-16-disapprove disapprove-array status-buttons' container='".$container."' module_link='".$module_link."' href='javascript:void(0)'><span>Declined</span></a></div>";    
    //   // }
      
    //   $buttons .= "</div>";
                  
    //   return $buttons;
    // }

    function change_status($record_id = 0, $status = "", $non_ajax = 0) 
    {

        if( $non_ajax == 0 ){
            $status    = $this->input->post('status');
            $record_id = $this->input->post('record_id');
        }

        switch ($status) {
            case 'approve':                 
                    $data['form_status_id'] = 3;
                    $data['date_approved'] = date('Y-m-d H:i:s');

                    $this->db->where($this->key_field, $record_id);

                    if (!$this->db->update($this->module_table, $data)) {
                        $response->msg_type = 'error';
                        $response->msg      = 'Update failed. Contact the Administrator';
                    } else {
                        $this->db->where($this->key_field, $record_id);                     
                        $this->db->update($this->module_table, $data);

                        $response->msg_type = 'success';                        
                        $response->msg      = 'Double-Shift request approved. Timekeeping record updated';
                    }                                   
                break;
            case 'decline':                 
                    $data['form_status_id'] = 4;
                    $data['date_approved'] = date('Y-m-d H:i:s');

                    $this->db->where($this->key_field, $record_id);
                    
                    if (!$this->db->update($this->module_table, $data)) {
                        $response->msg_type = 'error';
                        $response->msg      = 'Update failed. Contact the Administrator';
                    } else {
                        $response->msg_type = 'success';
                        $response->msg      = 'Double-Shift request denied.';
                    }
            break;
        }

        if( $non_ajax == 0 ){
            $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $response);
        }
        else{
            return $response;
        }
    }

    function ajax_save()
    {
        $datetime_from = date('Y-m-d', strtotime($this->input->post('datetime_from')));
        $date = date('Y-m-d', strtotime($this->input->post('date')));
        $error = false;

        if($datetime_from <= $date)
        {
            $error = true;
            $msg = "Date Time From and Date should be the same day.";
        }

        $result = $this->db->get_where('employee_ds', array('employee_id' => $this->input->post('employee_id'), 'date' => date('Y-m-d', strtotime($this->input->post('date')))));

        if($result->num_rows() == 0 && !$error)
        {
            $_POST['form_status_id'] = '2';

            parent::ajax_save();            

        } 

        if($result->num_rows() > 0)  {
            $error = true;
            $msg = "You already have a double-shift filed on that day";
        }

        if($error) {
            $response['msg_type'] = 'error';
            $response['msg']      = $msg;
            $data['json'] = $response;
            $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);

        }

        //additional module save routine here

    }

    function delete()
    {
        parent::delete();

        //additional module delete routine here
    }

    function after_ajax_save()
    {
        if ($this->get_msg_type() == 'success') {

            $data['created_by']   = $this->userinfo['user_id'];
            $data['created_date'] = date('Y-m-d H:i:s');

            $this->db->where($this->key_field, $this->key_field_val);
            $this->db->update($this->module_table, $data);
        }

        parent::after_ajax_save();
    }

    private function _have_employee_approver($employee_id, $module_id)
    {
        $result = $this->db->get_where('employee_approver', array('module_id' => $module_id, 'employee_id' => $employee_id, 'deleted' => 0));
        if($result->num_rows() > 0)
            return true;
        else
            return false;
    }

    private function _is_employee_approver($employee_id, $module_id)
    {
        $this->db->select('employee_id');
        $result = $this->db->get_where('employee_approver', array('module_id' => $module_id, 'approver_employee_id' => $employee_id, 'deleted' => 0));
        if($result->num_rows() > 0)
            return $result->result_array();
        else
            return false;
    }

    function generate_date_used()
    {

        $date = $this->input->post('date');
        $e_id = $this->input->post('employee_id');

        if($date && $e_id)
        {
            $day_ctr = 1;

            do {
                $holiday = false;

                $date_used = date('Y-m-d', strtotime('+ '.$day_ctr.' day '.$date));

                $day = strtolower(date('l', strtotime($date_used)));

                $is_rd = $this->system->get_employee_worksched($e_id, $date_used);

                if(isset($is_rd->has_cws) || isset($is_rd->has_cal_shift))
                    $day_shift_id = $is_rd->shift_id;
                else
                    $day_shift_id = $is_rd->{$day . '_shift_id'};

                $holiday = $this->system->holiday_check($date, $e_id);

                $day_ctr++;

            } while($day_shift_id == 0 || $day_shift_id == 1 || $holiday);

            $response->date_used = $date_used;
            $response->msg = "success";
            $data['json'] = $response;
            $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);

        } else {

            $response->msg = "error";
            $response->msg_type = "No employee or date set";
            $data['json'] = $response;
            $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);

        }

    }

    // END - default module functions

    // START custom module funtions

    // END custom module funtions

}

/* End of file */
/* Location: system/application */