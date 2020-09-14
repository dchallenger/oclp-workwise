<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Postedjobs extends MY_Controller {
    const DEPARTMENT_TABLE = 'user_company_department';
    const POSITIONS_TABLE = 'user_position';
    const MANPOWER_REQUEST_TABLE = 'recruitment_manpower';
    const EMAIL_TEMPLATE_ID = 4;
    const PDF_TEMPLATE_ID = 5;
    const APPROVAL_TEMPLATE_ID = 6; // Change this.

    private $_employment_statuses;

    function __construct() {
        parent::__construct();

        $this->load->helper('candidates');
        
        //set module variable values
        $this->grid_grouping = "";
        $this->related_table = array(); //table => field format

        $this->listview_title = 'Posted Jobs';
        $this->listview_description = 'Lists all approved manpower requests';
        $this->listview_image = 'icons/vcard.png';
        $this->jqgrid_title = "Manpower Request";
        $this->detailview_title = 'Manpower Request';
        $this->detailview_description = 'This page shows detailed information about a Manpower Request';
        $this->editview_title = 'Manpower Request Add/Edit';
        $this->editview_description = 'This page allows saving/editing information about a Manpower Request';

        $this->_employment_statuses = array('Regular', 'Probationary', 'Contractual');

        if (method_exists($this, 'print_record')) {
            $data['show_print'] = true;
        } else {
            $data['show_print'] = false;
        }
                
        $statuses = get_candidate_statuses();

        foreach ($statuses as $index => $status) {
            if ($status['default'] == 1) {
                $this->_default_status_id = $status['candidate_status_id'];
            }

            if ($status['hired_flag'] == 1) {
                $this->_hired_status_id = $status['candidate_status_id'];
                unset($statuses[$index]);
            }

            if ($status['rejected_flag'] == 1) {
                $this->_rejected_status_id = $status['candidate_status_id'];                
            }

            if ($status['interview_flag'] == 1) {
                $this->_interview_status_id = $status['candidate_status_id'];
            }

            if ($status['evaluation_flag'] == 1) {
                $this->_evaluation_status_id = $status['candidate_status_id'];
            }            

            if ($status['joboffer_flag'] == 1) {
                $this->_joboffer_status_id = $status['candidate_status_id'];
            }               
        }

        $data['module_filters']      = get_candidate_filters($statuses);
        $data['module_filter_title'] = 'Candidates';
        
        $this->load->vars($data);
    }

    // START - default module functions
    // default jqgrid controller method
    function index() {
        $data['scripts'][] = jqgrid_listview(); // load jqgrid js and default grid js
        $data['content'] = 'recruitment/manpower/candidates/listview';
        $data['listview'] = 'recruitment/postedjobs/listview';
        $data['jqgrid'] = 'recruitment/manpower/candidates/template/jqgrid';

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
        show_error('Function does not exist.');
    }

    function edit() {
        show_error('Function does not exist.');
    }

    function ajax_save() {
        show_error('Function does not exist.');
    }

    function delete() {
        show_error('Function does not exist.');
    }

    // END - default module functions
    // START custom module funtions

    private function _additional_listview_query() {
        if (!$this->is_recruitment() && !$this->is_superadmin) {
            $this->db->where('(approved_by = ' . $this->userinfo['user_id'] . ' OR requested_by = ' . $this->userinfo['user_id'] . ')');
        }
        
        $this->db->where_in('status', array('Approved', 'In-Process'));
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

        if (method_exists($this, '_additional_listview_query')) {            
            $this->_additional_listview_query();
        }        
        
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
        //dbug($this->db->last_query());exit();
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

            if (method_exists($this, '_additional_listview_query')) {
                $this->_additional_listview_query();
            }

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
                                    $cell[$cell_ctr] = ( $related_module ? $this->_default_field_module_link_actions($row[$this->key_field], $this->input->post('container'), $this->input->post('fmlinkctr')) : $this->_default_grid_actions($this->module_link, $this->input->post('container'), $row) );
                                    $cell_ctr++;
                                }
                            } elseif ($detail['name'] == 'date_needed') {
                                $this->listview_fields[$cell_ctr]['value'] = $row[$detail['name']];
                                $date = $this->uitype_listview->fieldValue($this->listview_fields[$cell_ctr]);

                                $timespan = timespan(time(), strtotime($date), TRUE);

                                if ($timespan['text'] == '') {
                                    if ($detail['status'] != 'Closed') {                                    
                                        $timespan = '<span class="red"><small>Deadline missed.</small></span>';
                                    }
                                } else {                                                                        
                                    $timespan = '<span class="timespan '. (($timespan['days'] <= 7) ? 'blue' : 'green') .'"><small>' . $timespan['text'] . ' left.</small></span>';
                                }

                                $cell[$cell_ctr] = $date . '<br />' . $timespan;
                                $cell_ctr++;                                                                                        
                            } else {
                                if( in_array( $this->listview_fields[$cell_ctr]['uitype_id'], array( 2, 5, 4, 11, 12, 17, 19, 21, 24, 27, 32, 33) ) ){
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
    
    function _default_grid_actions($module_link = "", $container = "", $row = array()) {

        // set default
        if ($module_link == "")
            $module_link = $this->module_link;
        if ($container == "")
            $container = "jqgridcontainer";

        // Right align action buttons.
        $actions = '<span class="icon-group">';
        $actions .= '<a class="icon-button icon-16-search search-candidates" module_link="' . $module_link . '" tooltip="Candidates" href="javascript:void(0)"></a>';
        $actions .= '<a class="icon-button icon-16-info" module_link="' . $module_link . '" tooltip="View" href="javascript:void(0)"></a>';
        $actions .= '<a class="icon-button icon-16-print print-record" tooltip="Print" href="javascript:void(0)" module_link="' . $module_link . '" ></a>';
        $actions .= '</span>';

        return $actions;
    }
    
    function print_record($record_id = 0) {
        // Get from $_POST when the URI is not present.
        if ($record_id == 0) {
            $record_id = $this->input->post('record_id');
        }

        redirect(site_url('recruitment/manpower/print_record/' . $record_id));
    }    
    
    function _default_grid_buttons($module_link = "", $related_field = "", $related_field_value = "", $addtext = "", $deltext = "", $container = "") {
        return "<div class='icon-label-group'></div>";
    }
}

/* End of file */
/* Location: system/application */