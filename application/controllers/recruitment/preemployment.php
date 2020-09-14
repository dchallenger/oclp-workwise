<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Preemployment extends MY_Controller {

    function __construct() {
        parent::__construct();

        //set module variable values
        $this->grid_grouping = "";
        $this->related_table = array(); //table => field format

        $this->listview_title = $this->module_name;
        $this->listview_description = $this->module_name;
        $this->jqgrid_title = "";
        $this->detailview_title = $this->module_name;
        $this->detailview_description = $this->module_name;
        $this->editview_title = 'Add/Edit';
        $this->editview_description = '';

        $this->load->helper('preemployment');

        if (!isset($_POST['record_id']) && $this->uri->rsegment(3)) {
            $_POST['record_id'] = $this->uri->rsegment(3);
        }

        $orientation = array("label"=>"New Employee Orientation","table"=>"recruitment_manpower_candidates_scheduler","link"=>"recruitment/scheduler","module_id"=>271,"code"=>"","extra"=>1,"type"=>1);
        $medical = array("label"=>"Medical","table"=>"recruitment_manpower_candidates_scheduler","link"=>"recruitment/scheduler","module_id"=>271,"code"=>"","extra"=>1,"type"=>2);        

        $data['checklists'][0] = $medical;
        $data['checklists'][1] = $orientation;
       
        $checklists = preemployment_filters();
        $cnt = 2;
      
        foreach ($checklists as $key => $checklist)
        {
          $data['checklists'][$cnt] = $checklist;
          $cnt++;
        }   

        $module_filters = $data['checklists'];
        unset($module_filters[0]);
        unset($module_filters[1]);

        $data['module_filters'] = $module_filters;
                
        ksort($data['checklists']);

        $this->load->vars($data);
		$this->load->library('encrypt');
		
        if( $this->method == "filter" && $this->record_id == "for_201" ) {
            $this->filter = $this->db->dbprefix.'recruitment_preemployment.has_201 = 0';
        }  
        elseif($this->record_id == "pre_complete" ) {
            for ($i=0; $i <= count($data['checklists']) ; $i++) { 
                if($data['checklists'][$i]['code'] == 'pre_complete'){
                    $this->filter = $data['checklists'][$i]['where_complete'];
                }
            }
        } 
    }

    // START - default module functions
    // default jqgrid controller method
    function index($filter = '') {
        $data['scripts'][] = jqgrid_listview(); // load jqgrid js and default grid js
        $data['content'] = 'recruitment/preemployment/listview';

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

        // Get children modules and prepare the checklist data.
        $module_children = $this->hdicore->get_module_child($this->module_id);

        foreach ($module_children as $checklist) {
            $data['checklists'][] = get_checklist_data($checklist);
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
		
	function filter( $filter = "" ){
		if(empty($filter)) {
			$this->session->set_flashdata('flashdata', 'Insufficient data supplied!<br/>Please contact the System Administrator.');
            redirect(base_url() . $this->module_link);
		}

		$this->index($filter);
	}
		

    function detail() {
        //parent::detail();

        if (!isset($_POST['record_id']) && $this->uri->rsegment(3))
            $_POST['record_id'] = $this->uri->rsegment(3);

        if ($this->input->post('record_id')) {
            //additional module detail routine here
            $data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/js/detailview.js"></script>';
            $data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/js/editview.js"></script>';
            $data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/modules/recruitment/preemployment_detailview.js"></script>';
            $data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/modules/employee/employee_quickedit.js"></script>';     

            $data['content'] = 'recruitment/preemployment/detailview';
            
            $data['scripts'][] = jqgrid_listview(); // load jqgrid js and default grid js
            
            $data['method'] = 'edit';
            //other views to load
            $data['views'] = array();

            if (!empty($this->module_wizard_form) || $this->input->post('record_id') == '-1') {
                $data['show_wizard_control'] = true;
            }

            $this->db->select($this->module_table . '.' . $this->key_field
                    . ',is_internal'
                    . ',t0.firstname, t0.lastname, t0.birth_date, t0.sex'
                    . ', t1.position_id, t0.sss, t0.philhealth, t0.pagibig, t0.tin, t0.civil_status_id'
                    . ', user_company.company_id'
                    . ', user_company_department.department_id'
                    . ', mc.applicant_id,  mc.candidate_status_id, mc.employee_id,recruitment_manpower.status_id, recruitment_manpower.request_id'
                    . ', IF (t0.firstname != "",CONCAT(t0.firstname, " ", t0.lastname),CONCAT(cu.firstname, " ", cu.lastname)) as applicant_name'
                    . ', CONCAT(rb.firstname, " ", rb.lastname) as requested_by'
                    . ', department, company, date_needed', false);

            $this->db->where($this->key_field, $this->input->post('record_id'));
            $this->db->where($this->module_table . '.deleted', 0);
            $this->_set_left_join();
            $this->db->join('user rb', 'rb.user_id = recruitment_manpower.requested_by');
            $this->db->join('user cu', 'cu.user_id = mc.employee_id','left');

            $result = $this->db->get($this->module_table);

            $data['raw_data'] = array();
            if ($result && $result->num_rows() > 0) {
                $data['raw_data'] = $result->row_array();
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
        } else {
            $this->session->set_flashdata('flashdata', 'Insufficient data supplied!<br/>Please contact the System Administrator.');
            redirect(base_url() . $this->module_link);
        }
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
        $this->session->set_flashdata('flashdata', 'No save action.');
        redirect(base_url() . $this->module_link);
        //parent::ajax_save();
    }

    function delete() {        
        $this->db->where($this->key_field, $this->input->post('record_id'));
        $this->db->where('deleted', 0);

        $result = $this->db->get($this->module_table);

        if ($result && $result->num_rows() > 0) {
            // Have to revert job offer and candidate status.
            $candidate_id = $result->row()->candidate_id;

            $this->db->where('candidate_id', $candidate_id);
            $this->db->where('deleted', 0);
            $this->db->update('recruitment_manpower_candidate', array('candidate_status_id' => 12));

            $this->db->where('candidate_id', $candidate_id);
            $this->db->where('deleted', 0);
            $this->db->update('recruitment_candidate_job_offer', array('job_offer_status_id' => 2, 'accepted' => 1));
        }

        parent::delete();        
    }    

    // END - default module functions
    // START custom module funtions

    private function _append_to_select()
    {
        $this->listview_qry .= ', jo.job_offer_id,mc.applicant_id';
    }

    function listview() {
        $response->msg = "";

        $page  = $this->input->post('page');
        $limit = $this->input->post('rows'); // get how many rows we want to have into the grid
        $sidx  = $this->input->post('sidx'); // get index row - i.e. user click to sort
        $sord  = $this->input->post('sord'); // get the direction
        $related_module = ( $this->input->post('related_module') ? true : false );

        $view_actions = (isset($_POST['view']) && $_POST['view'] == 'detail') ? false : true;

        //set columnlist and select qry
        $this->_set_listview_query('', $view_actions);
        $this->listview_qry .= ',IF(mc.is_internal = 0, CONCAT( t0.firstname, " ",REPLACE(CONCAT(UCASE(LEFT(t0.middlename , 1))," .")," ", ""), " ", t0.lastname ), CONCAT( ' . $this->db->dbprefix . 'user.firstname, " ", ' . $this->db->dbprefix . 'user.middleinitial, " ", ' . $this->db->dbprefix . 'user.lastname )) t0firstnamelastname';
        $this->listview_qry .= ',mc.is_internal';       
        //set Search Qry string
        if ($this->input->post('_search') == "true")
            $search = $this->input->post('searchField') == "all" ? $this->_set_search_all_query() : $this->_set_specific_search_query();
        else
            $search = 1;

        if ($this->module == "user" && (!$this->is_admin && !$this->is_superadmin))
            $search .= ' AND ' . $this->db->dbprefix . 'user.user_id NOT IN (1,2)';

        if (method_exists($this, '_append_to_select')) {
            // Append fields to the SELECT statement via $this->listview_qry
            $this->_append_to_select();
        }

        /* count query */
        //build query
        $this->_set_left_join();
        $this->db->select($this->listview_qry, false);
        $this->db->from($this->module_table);
        $this->db->where($this->db->dbprefix.'recruitment_candidate_status.rejected_flag', 0 );
        $this->db->where($this->module_table . '.deleted = 0 AND ' . $search);
		
        if(!empty( $this->filter ) ) 	$this->db->where( $this->filter );
		//if( $this->input->post('filter') ) 	$this->db->where( $this->encrypt->decode( $this->input->post('filter') ) );

        if (method_exists($this, '_set_filter')) {
            $this->_set_filter();
        }

        //get list
        $result = $this->db->get();

        $response->last_query = $this->db->last_query();
        if ($this->db->_error_message() != "") {
            $response->msg = $this->db->_error_message();
            $response->msg_type = "error";
        } else {
            $total_pages = $result->num_rows() > 0 ? ceil($result->num_rows() / $limit) : 0;
            
            $response->page    = $page > $total_pages ? $total_pages : $page;
            $response->total   = $total_pages;
            $response->records = $result->num_rows();

            /* record query */
            //build query
            $this->_set_left_join();
            $this->db->select($this->listview_qry, false);
            $this->db->from($this->module_table);
            $this->db->where($this->db->dbprefix.'recruitment_candidate_status.rejected_flag', 0 );
            $this->db->where($this->module_table . '.deleted = 0 AND ' . $search);
			if(!empty( $this->filter ) ) 	$this->db->where( $this->filter );
			if( $this->input->post('filter') ) 	$this->db->where( $this->encrypt->decode( $this->input->post('filter') ) );

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
                                    $cell[$cell_ctr] = ( $related_module ? $this->_default_field_module_link_actions($row[$this->key_field], $this->input->post('container'), $this->input->post('fmlinkctr')) : $this->_default_grid_actions($this->module_link, $this->input->post('container'), $row) );
                                    $cell_ctr++;
                                }
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

    function _default_grid_buttons($module_link = "", $related_field = "", $related_field_value = "", $addtext = "", $deltext = "", $container = "") {
        return '';
    }

    function _set_left_join() {
        //parent::_set_left_join();return;
        $this->db->join('recruitment_manpower_candidate mc', 'mc.candidate_id = ' . $this->module_table . '.candidate_id', 'left');
        $this->db->join('recruitment_candidate_status', 'recruitment_candidate_status.candidate_status_id = mc.candidate_status_id', 'left');
        $this->db->join('recruitment_applicant t0', 't0.applicant_id = mc.applicant_id', 'left');
        $this->db->join('recruitment_manpower', 'recruitment_manpower.request_id = mc.mrf_id', 'left');
        $this->db->join('user_position t1', 't1.position_id = recruitment_manpower.position_id', 'left');
        $this->db->join('user_company_department', 'user_company_department.department_id = recruitment_manpower.department_id', 'left');
        $this->db->join('user_company', 'user_company.company_id = recruitment_manpower.company_id', 'left');
        $this->db->join('recruitment_candidate_job_offer jo', 'mc.candidate_id = jo.candidate_id', 'left');
        $this->db->join('user', 'user.employee_id = mc.employee_id', 'left');        
        //$this->db->wjh
    }
		
	function _default_grid_actions( $module_link = "",  $container = "", $row = array() )
	{
		// set default
		if($module_link == "") $module_link = $this->module_link;
		if($container == "") $container = "jqgridcontainer";

		$actions = '<span class="icon-group">';
        
        if($this->encrypt->decode($this->input->post('filter')) == $this->db->dbprefix."recruitment_preemployment.has_201 = 0"){
			if($this->input->post('filter')){				
				$actions .= '<a class="icon-button icon-16-201" tooltip="Create 201" href="javascript:new_201('.$row['applicant_id'].')" module_link="'.$module_link.'" ></a>';
			}
		}
		else{
			if ($this->user_access[$this->module_id]['view']) {
					$actions .= '<a class="icon-button icon-16-info" module_link="'.$module_link.'" tooltip="View" href="javascript:void(0)"></a>';
			}
			
			if ($this->user_access[$this->module_id]['print'] && method_exists($this, 'print_record')) {
					$actions .= '<a class="icon-button icon-16-print print-record" tooltip="Print" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
			}        						
            
            if ($this->user_access[$this->module_id]['edit']) {
                $actions .= '<a class="icon-button icon-16-edit" tooltip="Edit" href="javascript:void(0)" module_link="'.$module_link.'" ></a>';
            }

            if ($this->is_recruitment()) {
                $actions .= '<a class="icon-button icon-16-doc-text button-joboffer" tooltip="Joboffer" href="javascript:void(0)" module_link="recruitment/candidate_joboffer" joboffer_id="' . $row['job_offer_id'] . '"></a>';            
            }
            
            if ($this->is_recruitment() && $this->_can_print_jo()) {
                $actions .= '<a class="icon-button icon-16-document-stack" tooltip="Print Contract" onclick="return false;" href="javascript:void(0)" module_link="' . $module_link . '" joboffer_id="' . $row['job_offer_id'] . '" candidate_status="'.$row['candidate_status'].'" ></a>';
            }

            if ($this->user_access[$this->module_id]['delete']) {
					$actions .= '<a class="icon-button icon-16-delete delete-single" tooltip="Delete" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
			}
		}
		$actions .= '</span>';

		return $actions;
	}

    /**
     * Gets the form id for a checklist, if none exists a record is created for
     * this preemployment.
     * 
     * @return void
     */
    function fetch_form_id() {
        if (IS_AJAX) {
            $record_id = $this->input->post('record_id');
            $exist = $this->_record_exist($record_id);

            if ($exist->exist == TRUE) {
                // Use 'rel' to get the module relating to class_path,
                // getting the module gives us the respective table to work with.
                $rel = $this->input->post('rel');
                // Get table info.
                $this->db->where('class_path', $rel);
                $this->db->limit(1);
                $result = $this->db->get('module');

                if ($result && $result->num_rows() > 0) {
                    $module = $result->row_array();
                    $this->db->where('preemployment_id', $record_id);
		            $this->db->where('deleted', 0);
                    $result = $this->db->get($module['table']);

                    // Check if a record exists, return that record id. If none,
                    // create a new record based on the module.
                    if ($result && $result->num_rows() > 0) {
                        $record = $result->row_array();
                        $record_id = $record[$module['key_field']];
                    } else {
                        $this->db->insert($module['table'], array(
                            'preemployment_id' => $record_id,
                            'date_created' => date('Y-m-d h:i:s'),
                            'date_updated' => date('Y-m-d h:i:s')
                                )
                        );
                        $record_id = $this->db->insert_id();
                    }

                    $response['type']      = 'success';
                    $response['record_id'] = $record_id;
                } else {
                    $response['type']    = 'error';
                    $response['message'] = 'Class not found';
                }
            } else {
                $response['type']    = 'error';
                $response['message'] = 'No preemployment record found';
            }

            $data['json'] = $response;

            $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
        } else {
            $this->session->set_flashdata('flashdata', 'You dont have sufficient privilege to execute the edit action! Please contact the System Administrator.');
            redirect(base_url() . $this->module_link);
        }
    }

    function get_employee_id_no(){
        $last_series_no = '';
        $result_format = $this->db->get('employee_id_number_format')->row();
        if ($result_format->id_number_format_series != ''){
            $length = strlen($result_format->id_number_format_series);
            $last_series_no = $result_format->id_number_format_series + 1;  
            $last_series_no = str_pad($last_series_no, $length, "0", STR_PAD_LEFT);
        }

        $result = $this->db->get('employee_id_number_config');
        if ($result && $result->num_rows() > 0){
            $str = '';
            $ctr = 1;
            $series = '';                   
            foreach ($result->result() as $row) {
                if ($row->employee_id_number_config_value != ''){
                    if ($row->employee_id_number_config_type_id == 5){
                        $str .= $last_series_no;
                    }  
                    else{
                        $str .= $row->employee_id_number_config_value;
                    }                   
                }
                if ($ctr < $result->num_rows() && $row->employee_id_number_config_value!= ''){
                    $str .= '';   
                }
                $ctr++;
            }

            $response->employee_id_last_series = $str;    
            $response->last_series = $last_series_no;
            $response->msg_type = 'success';
            $response->msg      = 'Employee id config already setup';                           
        }
        else{
            $response->msg_type = 'error';
            $response->msg      = 'Employee id config has not be setup already';            
        } 
        $this->load->view('template/ajax', array('json' => $response));                     
    }   
    
    private $_can_print_jo = null;

    private function _can_print_jo() {
        if (is_null($this->_can_print_jo)) {
            $module = $this->hdicore->get_module('job_offer');

            if ($module) {
                $this->_can_print_jo = $this->user_access[$module->module_id]['print'];
            } else {
                $this->_can_print_jo = false;
            }
        }

        return $this->_can_print_jo;
    }    
}

/* End of file */
    /* Location: system/application */