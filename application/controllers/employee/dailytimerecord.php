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

class Dailytimerecord extends MY_Controller {

    function __construct() {
        parent::__construct();

        //set module variable values
        $this->grid_grouping = 'employee_dtr.month';
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
        if ($this->user_access[$this->module_id]['edit'] == 1) {
            $this->load->helper('form');

            parent::edit();

            //additional module edit routine here
            $data['show_wizard_control'] = false;
            $data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/js/editview.js"></script>';

            if (!empty($this->module_wizard_form) && $this->input->post('record_id') == '-1') {
                $data['show_wizard_control'] = true;
                $data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/js/editview-wizard-form.js"></script>';
            }
            $data['content'] = 'editview';

            //other views to load
            $data['views'] = array();
            $data['views_outside_record_form'] = array();

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
        // Validate.
        $validate = $this->_validate();
        if ($validate === true) {
            $this->load->helper('date');

            if ($this->input->post('record_id') == '-1') {
                $data['date_created'] = date('Y-m-d H:i:s', now());
                $data['created_by'] = $this->userinfo['user_id'];
            }

            parent::ajax_save();

            if ($this->key_field_val) {
                // Set dates.
                $data['date_updated'] = date('Y-m-d H:i:s', now());
                $data['updated_by'] = $this->userinfo['user_id'];

                $this->db->where($this->key_field, $this->key_field_val);
                $this->db->update($this->module_table, $data);
                
                // Set workshift_id based on selected date.
            }
        } else {
            $data['json'] = $validate;
            $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
        }
    }

    function delete() {
        parent::delete();
    }

    // END - default module functions
    // START custom module funtions

    function _set_listview_query($listview_id = '', $view_actions = true) {
        parent::_set_listview_query($listview_id, $view_actions);
                
        $this->listview_qry = str_replace('employee_dtr.month', '', $this->listview_qry);
        $this->listview_qry = str_replace('employee_dtr.ot_hours', '', $this->listview_qry);
        $this->listview_qry = str_replace('employee_dtr.workshift', '', $this->listview_qry);
        $this->listview_qry = str_replace('employee_dtr.under_min', '', $this->listview_qry);
                
        $this->listview_qry .= ',DATE_FORMAT(' . $this->db->dbprefix . 'employee_dtr.date, "%M") as month';
        $this->listview_qry .= ',(time_to_sec( timediff( oot.to_datetime, oot.from_datetime ) ) /3600) AS ot_hours';
        $this->listview_qry .= ',ws.name AS workshift';
        //$this->listview_qry .= ',(time_to_sec( timediff( oot.to_datetime, oot.from_datetime ) ) /3600/60) AS under_min';
    }

    function _set_left_join() {
        parent::_set_left_join();
        $join_ot = 'oot.employee_id = employee_dtr.employee_id'; 
        $join_ot .= ' AND oot.from_datetime LIKE CONCAT( ' . $this->db->dbprefix . 'employee_dtr.date, "%") AND oot.approved = 1';        
        $this->db->join('employee_oot oot', $join_ot, 'left');
        
        $join_ws = 'employee_dtr.employee_id = workschedule_employee.employee_id AND ';
        $join_ws .= $this->db->dbprefix . 'workschedule_employee.date = ' . $this->db->dbprefix . 'employee_dtr.date';
        $this->db->join('employee_workschedule', $join_ws, 'left');
        
        $this->db->join('workshift ws', 'employee_workschedule.shift_id = ws.workshift_id', 'left');
    }

    function listview() {
        $response->msg = "";

        $page = $this->input->post('page');
        $limit = $this->input->post('rows'); // get how many rows we want to have into the grid
        $sidx = $this->input->post('sidx'); // get index row - i.e. user click to sort
        $sord = $this->input->post('sord'); // get the direction
        $related_module = ( $this->input->post('related_module') ? true : false );

        $view_actions = (isset($_POST['view']) && $_POST['view'] == 'detail') ? false : true;

        //set columnlist and select qry
        $this->_set_listview_query('', $view_actions);

        //set Search Qry string
        if ($this->input->post('_search') == "true")
            $search = $this->input->post('searchField') == "all" ? $this->_set_search_all_query() : $this->_set_specific_search_query();
        else
            $search = 1;

        if ($this->module == "user" && (!$this->is_admin && !$this->is_superadmin))
            $search .= ' AND ' . $this->db->dbprefix . 'user.user_id NOT IN (1,2)';

        /* count query */
        //build query
        $this->_set_left_join();
        $this->db->select($this->listview_qry, false);
        $this->db->from($this->module_table);
        $this->db->where($this->module_table . '.deleted = 0 AND ' . $search);


        //get list
        $result = $this->db->get();
        //dbug($this->db->last_query());
        if ($this->db->_error_message() != "") {
            $response->msg = $this->db->_error_message();
            $response->msg_type = "error";
        } else {
            $total_pages = $result->num_rows() > 0 ? ceil($result->num_rows() / $limit) : 0;
            $response->page = $page > $total_pages ? $total_pages : $page;
            $response->total = $total_pages;
            $response->records = $result->num_rows();

            /* record query */
            //build query
            $this->_set_left_join();
            $this->db->select($this->listview_qry, false);
            $this->db->from($this->module_table);

            $this->db->where($this->module_table . '.deleted = 0 AND ' . $search);

            if ($sidx != "") {
                $this->db->order_by($sidx, $sord);
            } else {
                if (is_array($this->default_sort_col)) {
                    $sort = implode(', ', $this->default_sort_col);
                    $this->db->order_by($sort);
                }
            }
            $start = $limit * $page - $limit;
            $this->db->limit($limit, $start);

            $result = $this->db->get();
            //dbug($this->db->last_query());
            //check what column to add if this is a related module
            if ($related_module) {
                foreach ($this->listview_columns as $column) {
                    if ($column['name'] != "action") {
                        $temp = explode('.', $column['name']);
                        if (strpos($this->input->post('column'), ',')) {
                            $column_lists = explode(',', $this->input->post('column'));
                            if (sizeof($temp) > 1 && in_array($temp[1], $column_lists))
                                $column_to_add[] = $column['name'];
                        }
                        else {
                            if (sizeof($temp) > 1 && $temp[1] == $this->input->post('column'))
                                $this->related_module_add_column = $column['name'];
                        }
                    }
                }
                //in case specified related column not in listview columns, default to 1st column
                if (!isset($this->related_module_add_column)) {
                    if (sizeof($column_to_add) > 0)
                        $this->related_module_add_column = implode('~', $column_to_add);
                    else
                        $this->related_module_add_column = $this->listview_columns[0]['name'];
                }
            }

            if ($this->db->_error_message() != "") {
                $response->msg = $this->db->_error_message();
                $response->msg_type = "error";
            } else {
                $response->rows = array();
                if ($result->num_rows() > 0) {
                    $columns_data = $result->field_data();
                    $column_type = array();
                    foreach ($columns_data as $column_data) {
                        $column_type[$column_data->name] = $column_data->type;
                    }
                    $this->load->model('uitype_listview');
                    $ctr = 0;
                    foreach ($result->result_array() as $row) {
                        $cell = array();
                        $cell_ctr = 0;
                        foreach ($this->listview_columns as $column => $detail) {
                            if (preg_match('/\./', $detail['name'])) {
                                $temp = explode('.', $detail['name']);
                                $detail['name'] = $temp[1];
                            }

                            if ($detail['name'] == 'action') {
                                if ($view_actions) {                                     
                                    $cell[$cell_ctr] = ( $related_module ? $this->_default_field_module_link_actions($row[$this->key_field], $this->input->post('container'), $this->input->post('fmlinkctr')) : $this->_default_grid_actions($this->module_link, $this->input->post('container'), $row[$this->key_field]) );
                                    $cell_ctr++;
                                }
                            } else {
                                if (in_array($this->listview_fields[$cell_ctr]['uitype_id'], array(2, 5, 4, 11, 12, 17, 19, 21, 24, 27, 33))) {
                                    $this->listview_fields[$cell_ctr]['value'] = $row[$detail['name']];
                                    $cell[$cell_ctr] = $this->uitype_listview->fieldValue($this->listview_fields[$cell_ctr]);
                                } else if (in_array($this->listview_fields[$cell_ctr]['uitype_id'], array(3)) && ( isset($this->listview_fields[$cell_ctr]['other_info']['picklist_type']) && $this->listview_fields[$cell_ctr]['other_info']['picklist_type'] == 'Query' )) {
                                    $cell[$cell_ctr] = "";
                                    foreach ($this->listview_fields[$cell_ctr]['other_info']['picklistvalues'] as $picklist_val) {
                                        if ($row[$detail['name']] == $picklist_val['id'])
                                            $cell[$cell_ctr] = $picklist_val['value'];
                                    }
                                }
                                else {
                                    $cell[$cell_ctr] = (is_numeric($row[$detail['name']]) && ($column_type[$detail['name']] != "253" && $column_type[$detail['name']] != "varchar") ) ? number_format($row[$detail['name']], 2, '.', ',') : $row[$detail['name']];
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

        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
    }

    private function _validate() {
        if ($this->input->post('time_in_am') != '' && $this->input->post('time_out_pm') != '') {
            if (strtotime($this->input->post('time_in_am')) > strtotime($this->input->post('time_out_pm'))) {
                $response->msg = "Time in is later than time out.";
                $response->msg_type = 'error';

                return $response;
            }
        }

        return true;
    }

}

/* End of file */
/* Location: system/application */