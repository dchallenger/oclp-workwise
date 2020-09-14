<?php if (! defined('BASEPATH')) exit ('No direct script access allowed');

include (APPPATH . 'controllers/dtr/leave_balance.php');

class Firstbalfour_leave_balance extends Leave_balance{

    // START - default module functions
    // default jqgrid controller method
    function index(){
        if($this->user_access[$this->module_id]['list'] != 1){
            $this->session->set_flashdata('flashdata', 'You dont have sufficient privilege to execute the list action of '.$this->module_name.'! Please contact the System Administrator.');
            redirect( base_url() );
        }
        $data['scripts'][] = jqgrid_listview(); // load jqgrid js and default grid js
        $data['content'] = 'listview';

        //Tabs for Listview
        $tabs = array();
        $emp = $this->db->get_Where('employee', array('employee_id' => $this->user->user_id ))->row();
        $subordinates = $this->hdicore->get_subordinates($this->userinfo['position_id'], $emp->rank_id, $this->user->user_id);
        // $subordinates = $this->hdicore->get_subordinates($this->userinfo['position_id']);

        if( ( $this->is_superadmin || $this->is_admin ) ||  $this->user_access[$this->module_id]['post'] == 1 ){
            $data['filter'] = 'all';
            $tabs[] = '<li class="active" filter="all"><a href="javascript:void(0)">All</li>';
            $tabs[] = '<li filter="personal"><a href="javascript:void(0)">Personal</li>';
        } else if (count($subordinates) > 0) {
            $data['filter'] = 'subordinates';
            $tabs[] = '<li class="active" filter="personal"><a href="javascript:void(0)">Personal</li>';
            $tabs[] = '<li filter="subordinates"><a href="javascript:void(0)">Subordinates</li>';
        } elseif (!$this->is_superadmin || !$this->is_admin  && $this->user_access[$this->module_id]['project_hr'] == 1) {
            $data['filter'] = 'project_subordinates';
            $tabs[] = '<li class="active" filter="personal"><a href="javascript:void(0)">Personal</li>';
            $tabs[] = '<li filter="project_subordinates"><a href="javascript:void(0)">Employees</li>';
        } else{
            $data['filter'] = 'personal';
            $tabs[] = '<li class="active" filter="personal"><a href="javascript:void(0)">Personal</li>';
        }
        $approver  = $this->system->is_module_approver( $this->module_id, $this->userinfo['position_id'] );
        if( $approver ){
            $subs = array();
            foreach( $approver as $row ){
                $subordinates = $this->system->get_supervised( $this->user->user_id, $row->position_id  );
                foreach( $subordinates as $subordinate ){
                    $subs[] = $subordinate['user_id'];
                }   
            }
            if( sizeof($subs) > 0 ){
                $tabs[] = '<li filter="for_approval"><a href="javascript:void(0)">For Approval</li>';
            }
        }
        if( sizeof( $tabs ) > 1 ) $data['tab'] = addslashes('<ul id="grid-filter">'. implode('', $tabs) .'</ul>');
        
        if($this->session->flashdata('flashdata')){
            $info['flashdata'] = $this->session->flashdata('flashdata');
            $data['flashdata'] = $this->load->view($this->userinfo['rtheme'].'/template/flashdata', $info, true);
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

    // START custom module funtions
    function listview()
    {
        $this->load->helper('time_upload');     
        $page = $this->input->post('page');
        $limit = $this->input->post('rows'); // get how many rows we want to have into the grid
        $sidx = $this->input->post('sidx'); // get index row - i.e. user click to sort
        $sord = $this->input->post('sord'); // get the direction        

        //set Search Qry string
        if($this->input->post('_search') == "true")
            $search = $this->input->post('searchField') == "all" ? $this->_set_search_all_query() : $this->_set_specific_search_query();
        else
            $search = 1;
        
        if( $this->module == "user" && (!$this->is_admin && !$this->is_superadmin) ) $search .= ' AND '.$this->db->dbprefix.'user.user_id NOT IN (1,2)';

        $this->db->where($this->module_table.'.deleted = 0 AND '.$search);
        $this->db->where('user.deleted',0);
        if(!empty( $this->filter ) ) $this->db->where( $this->filter );

        if (method_exists($this, '_set_filter')) {
            $this->_set_filter();
        }       
        $this->db->join($this->db->dbprefix('user'),$this->db->dbprefix('user').'.employee_id = '.$this->db->dbprefix($this->module_table).'.employee_id');
        $result = $this->db->get($this->module_table);   

        if( $this->db->_error_message() != "" ){
            $response->msg = $this->db->_error_message();
            $response->msg_type = "error";
        }
        else{        
            $total_pages = $result->num_rows() > 0 ? ceil($result->num_rows()/$limit) : 0;
            $response->page = $page > $total_pages ? $total_pages : $page;
            $response->total = $total_pages;
            $response->records = $result->num_rows();                        

            $response->msg = "";

            if ($this->input->post('sidx')) {
                $sidx = $this->input->post('sidx');
                $sord = $this->input->post('sord');

                if ($sidx == 'employee_leave_balance.employee_id') {
                    $sidx = 'user.lastname';
                }

                $this->db->order_by($sidx . ' ' . $sord);
            }
            else{
                $this->db->order_by('user.lastname,year desc');
            }

            $start = $limit * $page - $limit;
            $this->db->limit($limit, $start);        
            
            $this->db->where($this->module_table.'.deleted = 0 AND '.$search);
            $this->db->where('user.deleted',0);
            if(!empty( $this->filter ) ) $this->db->where( $this->filter );

            if (method_exists($this, '_set_filter')) {
                $this->_set_filter();
            }           
            $this->db->join($this->db->dbprefix('user'),$this->db->dbprefix('user').'.employee_id = '.$this->db->dbprefix($this->module_table).'.employee_id');
            $result = $this->db->get($this->module_table); 

            $ctr = 0;
            foreach ($result->result() as $row) {
                if($this->config->item('remove_el_leave_balance_viewing') == 1)
                {
                    $response->rows[$ctr]['id'] = $row->leave_balance_id;
                    $response->rows[$ctr]['cell'][0] = $row->firstname.' '.$row->middleinitial.' '.$row->lastname.' '.$row->aux;
                    $response->rows[$ctr]['cell'][1] = $row->year;
                    $response->rows[$ctr]['cell'][2] = ($this->config->item('show_with_carried') == 0 ? number_format($row->vl - $row->vl_used,2,'.',',') : number_format(($row->vl + $row->carried_vl) - $row->vl_used,2,'.',','));
                    $response->rows[$ctr]['cell'][3] = ($this->config->item('show_with_carried') == 0 ? number_format($row->sl - $row->sl_used,2,'.',',') : number_format(($row->sl + $row->carried_sl) - $row->sl_used,2,'.',','));
                    //$response->rows[$ctr]['cell'][4] = number_format($row->bl - $row->bl_used,2,'.',',');                    
                    $response->rows[$ctr]['cell'][4] = number_format($row->mpl - $row->mpl_used,2,'.',',');
                    $response->rows[$ctr]['cell'][5] = $this->_default_grid_actions( $this->module_link, $this->input->post('container'), $row);
                } else {
                    $response->rows[$ctr]['id'] = $row->leave_balance_id;
                    $response->rows[$ctr]['cell'][0] = $row->firstname.' '.$row->middleinitial.' '.$row->lastname.' '.$row->aux;
                    $response->rows[$ctr]['cell'][1] = $row->year;
                    $response->rows[$ctr]['cell'][2] = ($this->config->item('show_with_carried') == 0 ? number_format($row->vl - ($row->vl_used+$row->el_used),2,'.',',') : number_format(($row->vl + $row->carried_vl) - ($row->vl_used+$row->el_used),2,'.',','));
                    $response->rows[$ctr]['cell'][3] = ($this->config->item('show_with_carried') == 0 ? number_format($row->sl - $row->sl_used,2,'.',',') : number_format(($row->sl + $row->carried_sl) - $row->sl_used,2,'.',','));
                    //$response->rows[$ctr]['cell'][4] = number_format($row->bl - $row->bl_used,2,'.',',');                    
                    //$response->rows[$ctr]['cell'][5] = number_format($row->el - $row->el_used,2,'.',',');
                    $response->rows[$ctr]['cell'][4] = number_format($row->mpl - $row->mpl_used,2,'.',',');
                    $response->rows[$ctr]['cell'][5] = $this->_default_grid_actions( $this->module_link, $this->input->post('container'), $row);
                }
                $ctr++;
            }
        }
        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', array('json' => $response));
    }  

    function _set_listview_query( $listview_id = '', $view_actions = true ) {
        MY_Controller::_set_listview_query($listview_id, $view_actions);

        $emp = $this->hdicore->_get_userinfo($this->user->user_id);
        if(!( $this->is_superadmin || $this->is_admin ) && $this->user_access[$this->module_id]['post'] != 1){
            if ($emp->sex == "male"){
                $cname = "PL Balance";
            }
            else{
                $cname = "ML Balance";          
            }           
        }
        else{
            $cname = "PL/ML Balance";
        }
        if($this->config->item('remove_el_leave_balance_viewing') == 1)
        {
            $this->listview_column_names[2] = "VL Balance";
            $this->listview_column_names[3] = "SL Balance";
            if (CLIENT_DIR == 'firstbalfour'){
                //$this->listview_column_names[4] = "BL Balance";         
            }           
            $this->listview_column_names[4] = $cname;
        } else {
            $this->listview_column_names[2] = "VL Balance";
            $this->listview_column_names[3] = "SL Balance";
            if (CLIENT_DIR == 'firstbalfour'){
                //$this->listview_column_names[4] = "BL Balance";     
            }           
            //$this->listview_column_names[5] = "EL Balance";
            $this->listview_column_names[4] = $cname;
        }

        $this->listview_qry .= ',user.firstname, user.lastname';        
    }        
} 