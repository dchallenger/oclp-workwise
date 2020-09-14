<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Balance
 *
 * @author jconsador
 */
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Balance extends MY_Controller {

    function __construct() {
        parent::__construct();

        //set module variable values
        $this->grid_grouping = "";
        $this->related_table = array(); //table => field format

        $this->listview_title = '';
        $this->listview_description = '';
        $this->jqgrid_title = "";
        $this->detailview_title = '';
        $this->detailview_description = '';
        $this->editview_title = 'Add/Edit';
        $this->editview_description = '';
    }

    // START - default module functions
    // default jqgrid controller method
    function index() {
        $data['scripts'][] = jqgrid_listview(); // load jqgrid js and default grid js
        $data['content'] = 'listview';
        $data['jqgrid'] = 'leaves/balance/jqgrid';

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

    function _set_listview_query($listview_id = '', $view_actions = true) {
        $this->listview_column_names = array('Employee', 'Leave', 'Consumed');
        $this->listview_columns[] = array(
            'name' => 'employee_name',
            'index' => 'employee_name',
            'width' => '100'
        );
        $this->listview_columns[] = array(
            'name' => 'leave_type_id',
            'index' => 'leave_type_id',
            'width' => '100'
        );
        $this->listview_columns[] = array(
            'name' => 'count',
            'index' => 'count',
            'width' => '100'
        );
    }

    function _default_grid_buttons() {
        return '';
    }

    function listview() {
        $page = $this->input->post('page');
        $limit = $this->input->post('rows'); // get how many rows we want to have into the grid
        $sidx = $this->input->post('sidx'); // get index row - i.e. user click to sort
        $sord = $this->input->post('sord'); // get the direction        

        if ($this->input->post('sidx')) {
            $sidx = $this->input->post('sidx');
            $sord = $this->input->post('sord');
            $this->db->order_by($sidx . ' ' . $sord);
        }

        $this->db->where('eld.deleted', 0, false);
        $this->db->where('employee_leaves.deleted', 0);
        $this->db->select('CONCAT(' . $this->db->dbprefix . 'employee.firstname, " ",'. $this->db->dbprefix . '.employee.lastname) as employee_name', false);
        $this->db->select('count( eld.employee_leave_id ) AS count, leave_type_id');
        $this->db->join('employee_leaves_dates eld', 'employee_leaves.employee_leave_id = eld.employee_leave_id');
        $this->db->join('employee', 'employee_leaves.employee_id = employee.employee_id');
        $this->db->group_by('employee_leaves.leave_type_id');
        $this->db->from('employee_leaves');

        $result = $this->db->get();                
die($this->db->last_query());        
        $total_pages = $result->num_rows() > 0 ? ceil($result->num_rows()/$limit) : 0;
        $response->page = $page > $total_pages ? $total_pages : $page;
        $response->total = $total_pages;
        $response->records = $result->num_rows();                        

        $response->msg = "";

        $this->db->where('eld.deleted', 0, false);
        $this->db->where('employee_leaves.deleted', 0);
        $this->db->select('CONCAT(' . $this->db->dbprefix . '.employee.firstname, " ",'. $this->db->dbprefix . '.employee.lastname) as employee_name', false);
        $this->db->select('count( eld.employee_leave_id ) AS count, leave_type_id');        
        $this->db->join('employee_leaves_dates eld', 'employee_leaves.employee_leave_id = eld.employee_leave_id');
        $this->db->join('employee', 'employee_leaves.employee_id = employee.employee_id');
        $this->db->group_by('employee_leaves.leave_type_id');
        $this->db->from('employee_leaves');
        
        $start = $limit * $page - $limit;
        $this->db->limit($limit, $start);        
        
        $ctr = 0;
        foreach ($result->result() as $row) {
            foreach ($row as $key => $data) {
                $response->rows[$ctr]['cell'][] = $data;
            }
            $ctr++;
        }

        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', array('json' => $response));
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
        return null;
    }

    function ajax_save() {
        return null;
    }

    function delete() {
        return null;
    }

    // END - default module functions
    // START custom module funtions
}

/* End of file */
/* Location: system/application */
