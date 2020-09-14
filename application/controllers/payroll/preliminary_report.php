<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class preliminary_report extends MY_Controller
{
    function __construct()
    {
        parent::__construct();

        //set module variable values
        $this->grid_grouping = "";
        $this->related_table = array(); //table => field format 
        
        $this->listview_title = '';
        $this->listview_description = 'This module lists all defined (s).';
        $this->jqgrid_title = " List";
        $this->detailview_title = ' Info';
        $this->detailview_description = 'This page shows detailed information about a particular ';
        $this->editview_title = ' Add/Edit';
        $this->editview_description = 'This page allows saving/editing information about '; 
    }

    // START - default module functions
    // default jqgrid controller method
    function index()
    {       
        $data['scripts'][] = '<script type="text/javascript" src="'.base_url().'lib/js/editview.js"></script>'.uploadify_script();
        $data['content'] = 'slategray/payroll/report/report_view';  
        
        //other views to load
        $data['views'] = array();

        $this->load->model( 'uitype_edit' );
        $data['fieldgroups'] = $this->_record_detail( '-1' );
        
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

    function get_parameters() {

        $report_type = array("Summary","Summary Total");
        $report_type_html = '<select id="report_type_id" name="report_type_id">';
            foreach($report_type as $report_type_id => $report_type_value){
                $report_type_html .= '<option value="'.$report_type_id.'">'.$report_type_value.'</option>';
            }
        $report_type_html .= '</select>'; 

        $code_status = $this->db->query("SELECT code_status_id, code_status FROM {$this->db->dbprefix}code_status WHERE deleted = 0")->result_array();
        $code_status_html = '<select id="code_status_id" multiple="multiple" class="multi-select" name="code_status_id[]">';
        foreach($code_status as $code_status_record){
            $code_status_html .= '<option value="'.$code_status_record["code_status_id"].'">'.$code_status_record["code_status"].'</option>';
        }
        $code_status_html .= '</select>';        

        $paycode = $this->db->query("SELECT paycode_id, paycode FROM {$this->db->dbprefix}payroll_paycode WHERE deleted = 0")->result_array();
        $paycode_html = '<select id="paycode_id" multiple="multiple" class="multi-select" name="paycode_id[]">';
        foreach($paycode as $paycode_record){
            $paycode_html .= '<option value="'.$paycode_record["paycode_id"].'">'.$paycode_record["paycode"].'</option>';
        }
        $paycode_html .= '</select>';        

        $response->report_type_html = $report_type_html;
        $response->code_status_html = $code_status_html;
        $response->paycode_html = $paycode_html;
        $data['json'] = $response;
        $this->load->view('template/ajax', $data);  
    }

    function get_paycode() {
      $category = array("0"=>"Select","5"=>"By Company","1"=>"By Division","4"=>"By Department","2"=>"By Project","3"=>"By Group","6"=>"By Employee");
      $category_html = '<select id="category_id" class="select" name="category_id" style="width:50%;">';
      foreach($category as $category_key => $category_record){
          $category_html .= '<option value="'.$category_key.'">'.$category_record.'</option>';
      }
      $category_html .= '</select>';
      $paycode_id = $this->input->post('paycode_id');
      if(!empty($paycode_id)) {
        $response->flag = true;
      } else {
        $response->flag = false;  
      }
      $response->category_html = $category_html;
      $data['json'] = $response;
      $this->load->view('template/ajax', $data);
    }

    function get_company() {
      $paycode = 'AND 1'; 
      if(isset($_POST['paycode_id']))
      {
      $paycode_arr = array();
      foreach ($_POST['paycode_id'] as $value) 
      {
      $paycode_arr[] = $value;    
      }
      $paycode = implode(',', $paycode_arr);
      }
      if(!empty($paycode)){
      $paycode = ' AND c.paycode_id IN ('.$paycode.')';
      }
      $company = $this->db->query("SELECT
                                          d.company_id,
                                          d.company
                                        FROM {$this->db->dbprefix}employee a
                                          LEFT JOIN {$this->db->dbprefix}user b
                                            ON a.employee_id = b.employee_id
                                          LEFT JOIN {$this->db->dbprefix}employee_payroll c
                                            ON a.employee_id = c.employee_id 
                                          LEFT JOIN {$this->db->dbprefix}user_company d
                                            ON b.company_id = d.company_id
                                        WHERE b.company_id IS NOT NULL $paycode
                                        GROUP BY d.company")->result_array();
      $company_html = '<select id="company_id" multiple="multiple" class="multi-select" name="company_id[]">';
      foreach($company as $company_record){
          $company_html .= '<option value="'.$company_record["company_id"].'">'.$company_record["company"].'</option>';
      }
      $company_html .= '</select>';

      $location = $this->db->query("SELECT
                                          d.location_id,
                                          d.location
                                        FROM {$this->db->dbprefix}employee a
                                          LEFT JOIN {$this->db->dbprefix}user b
                                            ON a.employee_id = b.employee_id
                                          LEFT JOIN {$this->db->dbprefix}employee_payroll c
                                            ON a.employee_id = c.employee_id 
                                          LEFT JOIN {$this->db->dbprefix}user_location d
                                            ON a.location_id = d.location_id 
                                        WHERE d.deleted = 0 $paycode
                                        GROUP BY d.location")->result_array();
      $location_html = '<select id="location_id" multiple="multiple" class="multi-select" name="location_id[]">';
      foreach($location as $location_record){
          $location_html .= '<option value="'.$location_record["location_id"].'">'.$location_record["location"].'</option>';
      }
      $location_html .= '</select>';

      $employee = $this->db->query("SELECT
                                        b.firstname,
                                        b.middlename,
                                        b.lastname,
                                        b.aux,
                                          b.middleinitial,
                                        b.company_id,
                                        c.payroll_schedule_id,
                                        a.status_id,
                                        a.employee_id
                                      FROM {$this->db->dbprefix}employee a
                                        LEFT JOIN {$this->db->dbprefix}user b
                                          ON a.employee_id = b.employee_id
                                        LEFT JOIN {$this->db->dbprefix}employee_payroll c
                                          ON a.employee_id = c.employee_id 
                                      WHERE b.company_id IS NOT NULL $paycode AND a.deleted = 0
                                      ORDER BY b.lastname")->result_array();
      $employee_html = '<select id="employee_id" multiple="multiple" class="multi-select" name="employee_id[]">';
      foreach($employee as $employee_record){
          $employee_html .= '<option value="'.$employee_record["employee_id"].'">'.$employee_record["lastname"].' '.$employee_record["firstname"].' '.$employee_record["aux"].' '.$employee_record["middleinitial"].'</option>';
      }
      $employee_html .= '</select>'; 

      $response->company_html = $company_html;
      $response->location_html = $location_html;
      $response->employee_html = $employee_html;
      $data['json'] = $response;
      $this->load->view('template/ajax', $data);  
    }

    function get_division() {
      $paycode = 'AND 1'; 
      if(isset($_POST['paycode_id']))
      {
      $paycode_arr = array();
      foreach ($_POST['paycode_id'] as $value) 
      {
      $paycode_arr[] = $value;    
      }
      $paycode = implode(',', $paycode_arr);
      }
      if(!empty($paycode)){
      $paycode = ' AND c.paycode_id IN ('.$paycode.')';
      }
      $category_id = $this->input->post('category_id');
      $division = $this->db->query("SELECT 
                                    e.division_id,
                                    e.division
                                  FROM
                                    {$this->db->dbprefix}employee a 
                                    LEFT JOIN {$this->db->dbprefix}user b 
                                      ON a.employee_id = b.employee_id 
                                    LEFT JOIN {$this->db->dbprefix}employee_payroll c 
                                      ON a.employee_id = c.employee_id 
                                    LEFT JOIN {$this->db->dbprefix}employee_work_assignment d 
                                      ON a.employee_id = d.employee_id
                                    LEFT JOIN {$this->db->dbprefix}user_company_division e
                                    ON d.division_id = e.division_id
                                  WHERE d.employee_work_assignment_category_id = {$category_id} 
                                    AND d.assignment = 1 $paycode
                                    GROUP BY e.division")->result_array();
      $division_html = '<select id="division_id" multiple="multiple" class="multi-select" name="division_id[]">';
      foreach($division as $division_record){
          $division_html .= '<option value="'.$division_record["division_id"].'">'.$division_record["division"].'</option>';
      }
      $division_html .= '</select>';

      $location = $this->db->query("SELECT 
                                    e.location_id,
                                    e.location 
                                  FROM
                                    {$this->db->dbprefix}employee a 
                                    LEFT JOIN {$this->db->dbprefix}user b 
                                      ON a.employee_id = b.employee_id 
                                    LEFT JOIN {$this->db->dbprefix}employee_payroll c 
                                      ON a.employee_id = c.employee_id 
                                    LEFT JOIN {$this->db->dbprefix}user_location e 
                                      ON a.location_id = e.location_id 
                                    LEFT JOIN {$this->db->dbprefix}employee_work_assignment d 
                                      ON a.employee_id = d.employee_id 
                                  WHERE d.employee_work_assignment_category_id = {$category_id} 
                                    AND d.assignment = 1 AND e.deleted = 0 $paycode
                                  GROUP BY e.location ")->result_array();
      $location_html = '<select id="location_id" multiple="multiple" class="multi-select" name="location_id[]">';
      foreach($location as $location_record){
          $location_html .= '<option value="'.$location_record["location_id"].'">'.$location_record["location"].'</option>';
      }
      $location_html .= '</select>';

      $employee = $this->db->query("SELECT 
                                    b.firstname,
                                    b.middlename,
                                    b.lastname,
                                    b.aux,
                                          b.middleinitial,
                                    b.company_id,
                                    c.payroll_schedule_id,
                                    a.status_id,
                                    a.employee_id 
                                  FROM
                                    {$this->db->dbprefix}employee a 
                                    LEFT JOIN {$this->db->dbprefix}user b 
                                      ON a.employee_id = b.employee_id 
                                    LEFT JOIN {$this->db->dbprefix}employee_payroll c 
                                      ON a.employee_id = c.employee_id 
                                    LEFT JOIN {$this->db->dbprefix}employee_work_assignment d 
                                      ON a.employee_id = d.employee_id 
                                  WHERE d.employee_work_assignment_category_id = {$category_id} 
                                    AND d.assignment = 1 
                                    AND b.company_id IS NOT NULL $paycode AND a.deleted = 0
                                  ORDER BY b.lastname ")->result_array();
      $employee_html = '<select id="employee_id" multiple="multiple" class="multi-select" name="employee_id[]">';
      foreach($employee as $employee_record){
          $employee_html .= '<option value="'.$employee_record["employee_id"].'">'.$employee_record["lastname"].' '.$employee_record["firstname"].' '.$employee_record["aux"].' '.$employee_record["middleinitial"].'</option>';
      }
      $employee_html .= '</select>'; 

      $response->division_html = $division_html;
      $response->location_html = $location_html;
      $response->employee_html = $employee_html;
      $data['json'] = $response;
      $this->load->view('template/ajax', $data);  
    }

    function get_dept() {
      $paycode = 'AND 1'; 
      if(isset($_POST['paycode_id']))
      {
      $paycode_arr = array();
      foreach ($_POST['paycode_id'] as $value) 
      {
      $paycode_arr[] = $value;    
      }
      $paycode = implode(',', $paycode_arr);
      }
      if(!empty($paycode)){
      $paycode = ' AND c.paycode_id IN ('.$paycode.')';
      }
      $category_id = $this->input->post('category_id');
      $department = $this->db->query("SELECT 
                                    e.department_id,
                                    e.department
                                  FROM
                                    {$this->db->dbprefix}employee a 
                                    LEFT JOIN {$this->db->dbprefix}user b 
                                      ON a.employee_id = b.employee_id 
                                    LEFT JOIN {$this->db->dbprefix}employee_payroll c 
                                      ON a.employee_id = c.employee_id 
                                    LEFT JOIN {$this->db->dbprefix}employee_work_assignment d 
                                      ON a.employee_id = d.employee_id
                                    LEFT JOIN {$this->db->dbprefix}user_company_department e
                                    ON d.department_id = e.department_id
                                  WHERE d.employee_work_assignment_category_id = {$category_id} 
                                    AND d.assignment = 1 AND e.department_id IS NOT NULL $paycode
                                    GROUP BY e.department")->result_array();
      $department_html = '<select id="department_id" multiple="multiple" class="multi-select" name="department_id[]">';
      foreach($department as $department_record){
          $department_html .= '<option value="'.$department_record["department_id"].'">'.$department_record["department"].'</option>';
      }
      $department_html .= '</select>';

      $location = $this->db->query("SELECT 
                                    e.location_id,
                                    e.location 
                                  FROM
                                    {$this->db->dbprefix}employee a 
                                    LEFT JOIN {$this->db->dbprefix}user b 
                                      ON a.employee_id = b.employee_id 
                                    LEFT JOIN {$this->db->dbprefix}employee_payroll c 
                                      ON a.employee_id = c.employee_id 
                                    LEFT JOIN {$this->db->dbprefix}user_location e 
                                      ON a.location_id = e.location_id 
                                    LEFT JOIN {$this->db->dbprefix}employee_work_assignment d 
                                      ON a.employee_id = d.employee_id 
                                  WHERE d.employee_work_assignment_category_id = {$category_id} 
                                    AND d.assignment = 1 AND e.deleted = 0 $paycode
                                  GROUP BY e.location ")->result_array();
      $location_html = '<select id="location_id" multiple="multiple" class="multi-select" name="location_id[]">';
      foreach($location as $location_record){
          $location_html .= '<option value="'.$location_record["location_id"].'">'.$location_record["location"].'</option>';
      }
      $location_html .= '</select>';

      $employee = $this->db->query("SELECT 
                                    b.firstname,
                                    b.middlename,
                                    b.lastname,
                                    b.aux,
                                          b.middleinitial,
                                    b.company_id,
                                    c.payroll_schedule_id,
                                    a.status_id,
                                    a.employee_id 
                                  FROM
                                    {$this->db->dbprefix}employee a 
                                    LEFT JOIN {$this->db->dbprefix}user b 
                                      ON a.employee_id = b.employee_id 
                                    LEFT JOIN {$this->db->dbprefix}employee_payroll c 
                                      ON a.employee_id = c.employee_id 
                                    LEFT JOIN {$this->db->dbprefix}employee_work_assignment d 
                                      ON a.employee_id = d.employee_id 
                                  WHERE d.employee_work_assignment_category_id = {$category_id} 
                                    AND d.assignment = 1 
                                    AND b.company_id IS NOT NULL $paycode AND a.deleted = 0
                                  ORDER BY b.lastname ")->result_array();
      $employee_html = '<select id="employee_id" multiple="multiple" class="multi-select" name="employee_id[]">';
      foreach($employee as $employee_record){
          $employee_html .= '<option value="'.$employee_record["employee_id"].'">'.$employee_record["lastname"].' '.$employee_record["firstname"].' '.$employee_record["aux"].' '.$employee_record["middleinitial"].'</option>';
      }
      $employee_html .= '</select>'; 

      $response->department_html = $department_html;
      $response->location_html = $location_html;
      $response->employee_html = $employee_html;
      $data['json'] = $response;
      $this->load->view('template/ajax', $data);  
    }

    function get_proj() {
      $paycode = 'AND 1'; 
      if(isset($_POST['paycode_id']))
      {
      $paycode_arr = array();
      foreach ($_POST['paycode_id'] as $value) 
      {
      $paycode_arr[] = $value;    
      }
      $paycode = implode(',', $paycode_arr);
      }
      if(!empty($paycode)){
      $paycode = ' AND c.paycode_id IN ('.$paycode.')';
      }
      $category_id = $this->input->post('category_id');
      $project_name = $this->db->query("SELECT 
                                    e.project_name_id,
                                    e.project_name
                                  FROM
                                    {$this->db->dbprefix}employee a 
                                    LEFT JOIN {$this->db->dbprefix}user b 
                                      ON a.employee_id = b.employee_id 
                                    LEFT JOIN {$this->db->dbprefix}employee_payroll c 
                                      ON a.employee_id = c.employee_id 
                                    LEFT JOIN {$this->db->dbprefix}employee_work_assignment d 
                                      ON a.employee_id = d.employee_id
                                    LEFT JOIN {$this->db->dbprefix}project_name e
                                    ON d.project_name_id = e.project_name_id
                                  WHERE d.employee_work_assignment_category_id = {$category_id} 
                                    AND d.assignment = 1 AND e.project_name_id IS NOT NULL $paycode
                                    GROUP BY e.project_name")->result_array();
      $project_name_html = '<select id="project_name_id" multiple="multiple" class="multi-select" name="project_name_id[]">';
      foreach($project_name as $project_name_record){
          $project_name_html .= '<option value="'.$project_name_record["project_name_id"].'">'.$project_name_record["project_name"].'</option>';
      }
      $project_name_html .= '</select>';

      $location = $this->db->query("SELECT 
                                    e.location_id,
                                    e.location 
                                  FROM
                                    {$this->db->dbprefix}employee a 
                                    LEFT JOIN {$this->db->dbprefix}user b 
                                      ON a.employee_id = b.employee_id 
                                    LEFT JOIN {$this->db->dbprefix}employee_payroll c 
                                      ON a.employee_id = c.employee_id 
                                    LEFT JOIN {$this->db->dbprefix}user_location e 
                                      ON a.location_id = e.location_id 
                                    LEFT JOIN {$this->db->dbprefix}employee_work_assignment d 
                                      ON a.employee_id = d.employee_id 
                                  WHERE d.employee_work_assignment_category_id = {$category_id} 
                                    AND d.assignment = 1 AND e.deleted = 0 $paycode
                                  GROUP BY e.location ")->result_array();
      $location_html = '<select id="location_id" multiple="multiple" class="multi-select" name="location_id[]">';
      foreach($location as $location_record){
          $location_html .= '<option value="'.$location_record["location_id"].'">'.$location_record["location"].'</option>';
      }
      $location_html .= '</select>';

      $employee = $this->db->query("SELECT 
                                    b.firstname,
                                    b.middlename,
                                    b.lastname,
                                    b.aux,
                                          b.middleinitial,
                                    b.company_id,
                                    c.payroll_schedule_id,
                                    a.status_id,
                                    a.employee_id 
                                  FROM
                                    {$this->db->dbprefix}employee a 
                                    LEFT JOIN {$this->db->dbprefix}user b 
                                      ON a.employee_id = b.employee_id 
                                    LEFT JOIN {$this->db->dbprefix}employee_payroll c 
                                      ON a.employee_id = c.employee_id 
                                    LEFT JOIN {$this->db->dbprefix}employee_work_assignment d 
                                      ON a.employee_id = d.employee_id 
                                  WHERE d.employee_work_assignment_category_id = {$category_id} 
                                    AND d.assignment = 1 
                                    AND b.company_id IS NOT NULL $paycode AND a.deleted = 0
                                  ORDER BY b.lastname ")->result_array();
      $employee_html = '<select id="employee_id" multiple="multiple" class="multi-select" name="employee_id[]">';
      foreach($employee as $employee_record){
          $employee_html .= '<option value="'.$employee_record["employee_id"].'">'.$employee_record["lastname"].' '.$employee_record["firstname"].' '.$employee_record["aux"].' '.$employee_record["middleinitial"].'</option>';
      }
      $employee_html .= '</select>'; 

      $response->project_name_html = $project_name_html;
      $response->location_html = $location_html;
      $response->employee_html = $employee_html;
      $data['json'] = $response;
      $this->load->view('template/ajax', $data);  
    }

    function get_group() {
      $paycode = 'AND 1'; 
      if(isset($_POST['paycode_id']))
      {
      $paycode_arr = array();
      foreach ($_POST['paycode_id'] as $value) 
      {
      $paycode_arr[] = $value;    
      }
      $paycode = implode(',', $paycode_arr);
      }
      if(!empty($paycode)){
      $paycode = ' AND c.paycode_id IN ('.$paycode.')';
      }
      $category_id = $this->input->post('category_id');
      $group_name = $this->db->query("SELECT 
                                    e.group_name_id,
                                    e.group_name
                                  FROM
                                    {$this->db->dbprefix}employee a 
                                    LEFT JOIN {$this->db->dbprefix}user b 
                                      ON a.employee_id = b.employee_id 
                                    LEFT JOIN {$this->db->dbprefix}employee_payroll c 
                                      ON a.employee_id = c.employee_id 
                                    LEFT JOIN {$this->db->dbprefix}employee_work_assignment d 
                                      ON a.employee_id = d.employee_id
                                    LEFT JOIN {$this->db->dbprefix}group_name e
                                    ON d.group_name_id = e.group_name_id
                                  WHERE d.employee_work_assignment_category_id = {$category_id} 
                                    AND d.assignment = 1 AND e.group_name_id IS NOT NULL $paycode
                                    GROUP BY e.group_name")->result_array();

      $group_name_html = '<select id="group_name_id" multiple="multiple" class="multi-select" name="group_name_id[]">';
      foreach($group_name as $group_name_record){
          $group_name_html .= '<option value="'.$group_name_record["group_name_id"].'">'.$group_name_record["group_name"].'</option>';
      }
      $group_name_html .= '</select>';

      $location = $this->db->query("SELECT 
                                    e.location_id,
                                    e.location 
                                  FROM
                                    {$this->db->dbprefix}employee a 
                                    LEFT JOIN {$this->db->dbprefix}user b 
                                      ON a.employee_id = b.employee_id 
                                    LEFT JOIN {$this->db->dbprefix}employee_payroll c 
                                      ON a.employee_id = c.employee_id 
                                    LEFT JOIN {$this->db->dbprefix}user_location e 
                                      ON a.location_id = e.location_id 
                                    LEFT JOIN {$this->db->dbprefix}employee_work_assignment d 
                                      ON a.employee_id = d.employee_id 
                                  WHERE d.employee_work_assignment_category_id = {$category_id} 
                                    AND d.assignment = 1 AND e.deleted = 0 $paycode
                                  GROUP BY e.location ")->result_array();
      $location_html = '<select id="location_id" multiple="multiple" class="multi-select" name="location_id[]">';
      foreach($location as $location_record){
          $location_html .= '<option value="'.$location_record["location_id"].'">'.$location_record["location"].'</option>';
      }
      $location_html .= '</select>';

      $employee = $this->db->query("SELECT 
                                    b.firstname,
                                    b.middlename,
                                    b.lastname,
                                    b.aux,
                                          b.middleinitial,
                                    b.company_id,
                                    c.payroll_schedule_id,
                                    a.status_id,
                                    a.employee_id 
                                  FROM
                                    {$this->db->dbprefix}employee a 
                                    LEFT JOIN {$this->db->dbprefix}user b 
                                      ON a.employee_id = b.employee_id 
                                    LEFT JOIN {$this->db->dbprefix}employee_payroll c 
                                      ON a.employee_id = c.employee_id 
                                    LEFT JOIN {$this->db->dbprefix}employee_work_assignment d 
                                      ON a.employee_id = d.employee_id 
                                  WHERE d.employee_work_assignment_category_id = {$category_id} 
                                    AND d.assignment = 1 
                                    AND b.company_id IS NOT NULL $paycode AND a.deleted = 0
                                  ORDER BY b.lastname ")->result_array();
      $employee_html = '<select id="employee_id" multiple="multiple" class="multi-select" name="employee_id[]">';
      foreach($employee as $employee_record){
          $employee_html .= '<option value="'.$employee_record["employee_id"].'">'.$employee_record["lastname"].' '.$employee_record["firstname"].' '.$employee_record["aux"].' '.$employee_record["middleinitial"].'</option>';
      }
      $employee_html .= '</select>'; 

      $response->group_name_html = $group_name_html;
      $response->location_html = $location_html;
      $response->employee_html = $employee_html;
      $data['json'] = $response;
      $this->load->view('template/ajax', $data);  
    }

    function get_emp() {
      $paycode = 'AND 1'; 
      if(isset($_POST['paycode_id']))
      {
        $paycode_arr = array();
        foreach ($_POST['paycode_id'] as $value) 
        {
          $paycode_arr[] = $value;    
        }
        $paycode = implode(',', $paycode_arr);
      }
      if(!empty($paycode)) {
        $paycode = ' AND c.paycode_id IN ('.$paycode.')';
      }
      $category_id = $this->input->post('category_id');
      $employee = $this->db->query("SELECT 
                                    b.firstname,
                                    b.middlename,
                                    b.lastname,
                                    b.aux,
                                          b.middleinitial,
                                    b.company_id,
                                    c.payroll_schedule_id,
                                    a.status_id,
                                    a.employee_id 
                                  FROM
                                    {$this->db->dbprefix}employee a 
                                    LEFT JOIN {$this->db->dbprefix}user b 
                                      ON a.employee_id = b.employee_id 
                                    LEFT JOIN {$this->db->dbprefix}employee_payroll c 
                                      ON a.employee_id = c.employee_id 
                                    WHERE b.company_id IS NOT NULL  $paycode AND a.deleted = 0
                                  ORDER BY b.lastname ")->result_array();
      $employee_html = '<select id="employee_id" multiple="multiple" class="multi-select" name="employee_id[]">';
      foreach($employee as $employee_record){
          $employee_html .= '<option value="'.$employee_record["employee_id"].'">'.$employee_record["lastname"].' '.$employee_record["firstname"].' '.$employee_record["aux"].' '.$employee_record["middleinitial"].'</option>';
      }
      $employee_html .= '</select>'; 
      $response->employee_html = $employee_html;
      $data['json'] = $response;
      $this->load->view('template/ajax', $data);  
    }

    function get_2nd_layer() {
      $paycode = 'AND 1'; 
      if(isset($_POST['paycode_id']))
      {
        $paycode_arr = array();
        foreach ($_POST['paycode_id'] as $value) 
        {
          $paycode_arr[] = $value;    
        }
        $paycode = implode(',', $paycode_arr);
      }
      if(!empty($paycode)){
        $paycode = ' AND c.paycode_id IN ('.$paycode.')';
      }
      $category_id = $this->input->post('category_id');
      $added_qry = ' AND 1 ';
      switch($category_id) {
          case '5'://company  
                if(isset($_POST['company_id']))
                {
                  $company_arr = array();
                  foreach ($_POST['company_id'] as $value) 
                  {
                    $company_arr[] = $value;    
                  }
                  $company = implode(',', $company_arr);
                }
                if(!empty($company)){                          
                  $added_qry = ' AND b.company_id IN ('.$company.')';
                }
              break;
          case '1'://division
                if(isset($_POST['division_id']))
                {
                  $division_arr = array();
                  foreach ($_POST['division_id'] as $value) 
                  {
                    $division_arr[] = $value;    
                  }
                  $division = implode(',', $division_arr);
                }
                if(!empty($division)){
                  $added_qry = ' AND d.division_id IN ('.$division.') AND d.employee_work_assignment_category_id = '.$category_id.' AND d.assignment = 1';
                }
              break;
          case '4'://department
                if(isset($_POST['department_id']))
                {
                  $department_arr = array();
                  foreach ($_POST['department_id'] as $value) 
                  {
                    $department_arr[] = $value;    
                  }
                  $department = implode(',', $department_arr);
                }
                if(!empty($department)){
                  $added_qry = ' AND d.department_id IN ('.$department.') AND d.employee_work_assignment_category_id = '.$category_id.' AND d.assignment = 1';
                }
              break;
          case '2'://project
                if(isset($_POST['project_name_id']))
                {
                  $project_name_arr = array();
                  foreach ($_POST['project_name_id'] as $value) 
                  {
                    $project_name_arr[] = $value;    
                  }
                  $project_name = implode(',', $project_name_arr);
                }
                if(!empty($project_name)){
                  $added_qry = ' AND d.project_name_id IN ('.$project_name.') AND d.employee_work_assignment_category_id = '.$category_id.' AND d.assignment = 1';
                } 
              break;
          case '3'://group
              if(isset($_POST['group_name_id']))
                {
                  $group_name_arr = array();
                  foreach ($_POST['group_name_id'] as $value) 
                  {
                    $group_name_arr[] = $value;    
                  }
                  $group_name = implode(',', $group_name_arr);
                }
                if(!empty($group_name)){
                  $added_qry = ' AND d.group_name_id IN ('.$group_names.') AND d.employee_work_assignment_category_id = '.$category_id.' AND d.assignment = 1';
                }   
              break;
      }
      $location = $this->db->query("SELECT 
                                    e.location_id,
                                    e.location 
                                  FROM
                                    {$this->db->dbprefix}employee a 
                                    LEFT JOIN {$this->db->dbprefix}user b 
                                      ON a.employee_id = b.employee_id 
                                    LEFT JOIN {$this->db->dbprefix}employee_payroll c 
                                      ON a.employee_id = c.employee_id 
                                    LEFT JOIN {$this->db->dbprefix}user_location e 
                                      ON a.location_id = e.location_id 
                                    LEFT JOIN {$this->db->dbprefix}employee_work_assignment d 
                                      ON a.employee_id = d.employee_id 
                                  WHERE e.deleted = 0 $paycode $added_qry
                                  GROUP BY e.location ")->result_array();

      $location_html = '<select id="location_id" multiple="multiple" class="multi-select" name="location_id[]">';
      foreach($location as $location_record){
          $location_html .= '<option value="'.$location_record["location_id"].'">'.$location_record["location"].'</option>';
      }
      $location_html .= '</select>';

      $employee = $this->db->query("SELECT 
                                    b.firstname,
                                    b.middlename,
                                    b.lastname,
                                    b.aux,
                                          b.middleinitial,
                                    b.company_id,
                                    c.payroll_schedule_id,
                                    a.status_id,
                                    a.employee_id 
                                  FROM
                                    {$this->db->dbprefix}employee a 
                                    LEFT JOIN {$this->db->dbprefix}user b 
                                      ON a.employee_id = b.employee_id 
                                    LEFT JOIN {$this->db->dbprefix}employee_payroll c 
                                      ON a.employee_id = c.employee_id 
                                    LEFT JOIN {$this->db->dbprefix}employee_work_assignment d 
                                      ON a.employee_id = d.employee_id 
                                  WHERE b.company_id IS NOT NULL $paycode $added_qry AND a.deleted = 0
                                  ORDER BY b.lastname ")->result_array();
      $employee_html = '<select id="employee_id" multiple="multiple" class="multi-select" name="employee_id[]">';
      foreach($employee as $employee_record){
          $employee_html .= '<option value="'.$employee_record["employee_id"].'">'.$employee_record["lastname"].' '.$employee_record["firstname"].' '.$employee_record["aux"].' '.$employee_record["middleinitial"].'</option>';
      }
      $employee_html .= '</select>'; 

      $response->location_html = $location_html;
      $response->employee_html = $employee_html;
      $data['json'] = $response;
      $this->load->view('template/ajax', $data); 
    }

    function get_emp_location () {
      $paycode = 'AND 1'; 
      if(isset($_POST['paycode_id']))
      {
        $paycode_arr = array();
        foreach ($_POST['paycode_id'] as $value) 
        {
          $paycode_arr[] = $value;    
        }
        $paycode = implode(',', $paycode_arr);
      }
      if(!empty($paycode)){
        $paycode = ' AND c.paycode_id IN ('.$paycode.')';
      }
      $location_qry = 'AND 1'; 
      if(isset($_POST['location_id']))
      {
        $location_arr = array();
        foreach ($_POST['location_id'] as $value) 
        {
          $location_arr[] = $value;    
        }
        $location = implode(',', $location_arr);
      }
      if(!empty($location)){
        $location_qry = ' AND a.location_id IN ('.$location.')';
      }
      $category_id = $this->input->post('category_id');
      $added_qry = ' AND 1 ';
      switch($category_id) {
          case '5'://company  
                if(isset($_POST['company_id']))
                {
                  $company_arr = array();
                  foreach ($_POST['company_id'] as $value) 
                  {
                    $company_arr[] = $value;    
                  }
                  $company = implode(',', $company_arr);
                }
                if(!empty($company)){                          
                  $added_qry = ' AND b.company_id IN ('.$company.')';
                }
              break;
          case '1'://division
                if(isset($_POST['division_id']))
                {
                  $division_arr = array();
                  foreach ($_POST['division_id'] as $value) 
                  {
                    $division_arr[] = $value;    
                  }
                  $division = implode(',', $division_arr);
                }
                if(!empty($division)){
                  $added_qry = ' AND d.division_id IN ('.$division.') AND d.employee_work_assignment_category_id = '.$category_id.' AND d.assignment = 1';
                }
              break;
          case '4'://department
                if(isset($_POST['department_id']))
                {
                  $department_arr = array();
                  foreach ($_POST['department_id'] as $value) 
                  {
                    $department_arr[] = $value;    
                  }
                  $department = implode(',', $department_arr);
                }
                if(!empty($department)){
                  $added_qry = ' AND d.department_id IN ('.$department.') AND d.employee_work_assignment_category_id = '.$category_id.' AND d.assignment = 1';
                }
              break;
          case '2'://project
                if(isset($_POST['project_name_id']))
                {
                  $project_name_arr = array();
                  foreach ($_POST['project_name_id'] as $value) 
                  {
                    $project_name_arr[] = $value;    
                  }
                  $project_name = implode(',', $project_name_arr);
                }
                if(!empty($project_name)){
                  $added_qry = ' AND d.project_name_id IN ('.$project_name.') AND d.employee_work_assignment_category_id = '.$category_id.' AND d.assignment = 1';
                } 
              break;
          case '3'://group
              if(isset($_POST['group_name_id']))
                {
                  $group_name_arr = array();
                  foreach ($_POST['group_name_id'] as $value) 
                  {
                    $group_name_arr[] = $value;    
                  }
                  $group_name = implode(',', $group_name_arr);
                }
                if(!empty($group_name)){
                  $added_qry = ' AND d.group_name_id IN ('.$group_names.') AND d.employee_work_assignment_category_id = '.$category_id.' AND d.assignment = 1';
                }   
              break;
      }

      $employee = $this->db->query("SELECT 
                                    b.firstname,
                                    b.middlename,
                                    b.lastname,
                                    b.aux,
                                          b.middleinitial,
                                    b.company_id,
                                    c.payroll_schedule_id,
                                    a.status_id,
                                    a.employee_id 
                                  FROM
                                    {$this->db->dbprefix}employee a 
                                    LEFT JOIN {$this->db->dbprefix}user b 
                                      ON a.employee_id = b.employee_id 
                                    LEFT JOIN {$this->db->dbprefix}employee_payroll c 
                                      ON a.employee_id = c.employee_id 
                                    LEFT JOIN {$this->db->dbprefix}employee_work_assignment d 
                                      ON a.employee_id = d.employee_id 
                                  WHERE b.company_id IS NOT NULL $paycode $added_qry $location_qry AND a.deleted = 0
                                  ORDER BY b.lastname ")->result_array();
      $employee_html = '<select id="employee_id" multiple="multiple" class="multi-select" name="employee_id[]">';
      foreach($employee as $employee_record){
          $employee_html .= '<option value="'.$employee_record["employee_id"].'">'.$employee_record["lastname"].' '.$employee_record["firstname"].' '.$employee_record["aux"].' '.$employee_record["middleinitial"].'</option>';
      }
      $employee_html .= '</select>'; 

      $response->employee_html = $employee_html;
      $data['json'] = $response;
      $this->load->view('template/ajax', $data); 
    }

    function employee_multiple() {
        $paycode = '1'; 
        if(isset($_POST['paycode_id']))
        {
            $paycode_arr = array();
            foreach ($_POST['paycode_id'] as $value) 
            {
                $paycode_arr[] = $value;    
            }
            $paycode = implode(',', $paycode_arr);
        }
        if(!empty($paycode)){
            $paycode = 'c.paycode_id IN ('.$paycode.')';
        }
        $company_cd = '1'; 
        if(isset($_POST['company_id']))
        {
            $company_arr = array();
            foreach ($_POST['company_id'] as $value) 
            {
                $company_arr[] = $value;    
            }
            $company_id = implode(',', $company_arr);
        }
        if(!empty($company_id)){
            $company_cd = 'b.company_id IN ('.$company_id.')';
        }
        $employee = $this->db->query("SELECT
                                          b.firstname,
                                          b.middlename,
                                          b.lastname,
                                          b.aux,
                                          b.middleinitial,
                                          b.company_id,
                                          c.payroll_schedule_id,
                                          a.status_id,
                                          a.employee_id
                                        FROM {$this->db->dbprefix}employee a
                                          LEFT JOIN {$this->db->dbprefix}user b
                                            ON a.employee_id = b.employee_id
                                          LEFT JOIN {$this->db->dbprefix}employee_payroll c
                                            ON a.employee_id = c.employee_id 
                                        WHERE b.company_id IS NOT NULL AND {$paycode} AND {$company_cd}
                                        ORDER BY b.lastname")->result_array();
        $employee_html = '<select id="employee_id" multiple="multiple" class="multi-select" name="employee_id[]">';
        foreach($employee as $employee_record){
            $employee_html .= '<option value="'.$employee_record["employee_id"].'">'.$employee_record["lastname"].' '.$employee_record["firstname"].' '.$employee_record["aux"].' '.$employee_record["middleinitial"].'</option>';
        }
        $employee_html .= '</select>';        

        $response->employee_html = $employee_html;
        $data['json'] = $response;
        $this->load->view('template/ajax', $data);  
    }

    function export_report(){
        ini_set("memory_limit", "512M");

        $paycode_id = '';
        if(isset($_POST['paycode_id']))
        {
            $paycode_arr = array();
            foreach ($_POST['paycode_id'] as $value) 
            {
                $paycode_arr[] = $value;    
            }
            $paycode_id = implode(',', $paycode_arr);
        }

        $code_status_id = '';
        if(isset($_POST['code_status_id']))
        {
            $code_status_arr = array();
            foreach ($_POST['code_status_id'] as $value) 
            {
                $code_status_arr[] = $value;    
            }
            $code_status_id = implode(',', $code_status_arr);
        }

        $payroll_date = date("Y-m-d",strtotime($_POST['payroll_date']));  

        $this->load->library('pdf');
        switch ($_POST['report_type_id']){
            //summary
            case '0':
                $html = $this->export_summary($paycode_id, $code_status_id, $payroll_date, "PRELIMINARY REGISTER" );
                break;            
            //summary total
            case '1':
                $html = $this->export_summary_total($paycode_id, $code_status_id, $payroll_date, "PRELIMINARY REGISTER TOTAL" );
                break;
        }

        $this->pdf->Output(ucwords(str_replace(" ","_",$title))."_" . date('dmYHis') . '.pdf', 'I');
    }
    function export_summary($paycode_id, $code_status_id, $payroll_date, $title){
        
        $this->db->where('payroll_date',$payroll_date);
        $period = $this->db->get('payroll_period')->row();
        $date_period = date("m/d/Y",strtotime($period->date_from)).' TO '.date("m/d/Y",strtotime($period->date_to));

        switch ($code_status_id) {
            case '1':
                $status_code = '63010';
                break;
            case '2':
                $status_code = '13010';
                break;
            case '3':
                $status_code = '13020';
                break;
            
        }
        $category_id = $this->input->post('category_id');
        
        switch($category_id) {   
            case '1'://division
                $division_id = '';
                if(isset($_POST['division_id'])) {

                    $division_arr = array();
                    foreach ($_POST['division_id'] as $value) 
                    {
                        $division_arr[] = $value;    
                    }
                    $division_id = implode(',', $division_arr);
                }

                if(!empty($division_id)) {

                    $additional_qry = " AND pct.division_id IN ($division_id)";
                    $variable = 'pct.division_id';
                }

                break;

            case '2'://project
                $project_name_id = '';
                if(isset($_POST['project_name_id'])){

                    $project_name_arr = array();
                    foreach ($_POST['project_name_id'] as $value) 
                    {
                        $project_name_arr[] = $value;    
                    }
                    $project_name_id = implode(',', $project_name_arr);
                }

                if(!empty($project_name_id)) {

                     $additional_qry = " AND pct.project_name_id IN ($project_name_id)";
                    $variable = 'pct.project_name_id';
                }
                break;

            case '3'://group
                $group_name_id = '';
                if(isset($_POST['group_name_id'])){

                    $group_name_arr = array();
                    foreach ($_POST['group_name_id'] as $value) 
                    {
                        $group_name_arr[] = $value;    
                    }
                    $group_name_id = implode(',', $group_name_arr);
                }

                if(!empty($group_name_id)) {
                
                    $additional_qry = " AND pct.group_name_id IN ($group_name_id)";
                    $variable = 'pct.group_name_id';
                }
                break;
            
            case '4'://dept
                $department_id = '';
                if(isset($_POST['department_id'])){
                    
                    $department_arr = array();
                    foreach ($_POST['department_id'] as $value) 
                    {
                        $department_arr[] = $value;    
                    }
                    $department_id = implode(',', $department_arr);
                }
                
                if(!empty($department_id)) {
                
                    $additional_qry = " AND pct.department_id IN ($department_id)";
                    $variable = 'pct.department_id';
                }
                
                break;

            case '5'://Company
                $company_id = '';
                if(isset($_POST['company_id'])){
                    
                    $company_arr = array();
                    foreach ($_POST['company_id'] as $value) 
                    {
                        $company_arr[] = $value;    
                    }
                    $company_id = implode(',', $company_arr);
                }
                
                if(!empty($company_id)) {
                
                    $additional_qry = " AND u.company_id IN ($company_id)";
                    $variable = 'u.company_id';
                }
                
                break;

            case '6'://Employee
                $employee_id = '';
                if(isset($_POST['employee_id'])){
                    
                    $employee_arr = array();
                    foreach ($_POST['employee_id'] as $value) 
                    {
                        $employee_arr[] = $value;    
                    }
                    $employee_id = implode(',', $employee_arr);
                }
                
                if(!empty($employee_id)) {
                
                    $additional_qry = " AND u.employee_id IN ($employee_id)";
                    $variable = 'pct.employee_id';
                }
                
                break;

        }

        if(!empty($paycode_id)){
            $pay_code = 'AND pct.paycode_id = '.$paycode_id;
        }
        if(!empty($code_status_id)){
            $code_status = 'AND pct.code_status_id = '.$code_status_id;   
        }
        else{
            $code_status = 'AND pct.code_status_id = 0'; 
        }

        $paycode = $this->db->query("select paycode from {$this->db->dbprefix}payroll_paycode where paycode_id = $paycode_id")->row();

        $tot_no_emp = $this->db->query("SELECT distinct pct.employee_id FROM {$this->db->dbprefix}user u
                                    LEFT JOIN {$this->db->dbprefix}payroll_current_transaction pct on u.employee_id = pct.employee_id
                                    WHERE 1 AND payroll_date = '{$payroll_date}' AND pct.on_hold = 0 AND pct.transaction_code = 'netpay'
                                        $pay_code $additional_qry $code_status")->num_rows();

        $res = "SELECT distinct pct.cost_code FROM {$this->db->dbprefix}user u
                                    LEFT JOIN {$this->db->dbprefix}payroll_current_transaction pct on u.employee_id = pct.employee_id
                                    WHERE 1 AND payroll_date = '{$payroll_date}' AND pct.on_hold = 0 AND pct.transaction_code = 'netpay'
                                        $pay_code $additional_qry $code_status
                                    ORDER BY pct.cost_code";
        
        $proj_qry = $this->db->query($res);
        $proj_cnt = $proj_qry->num_rows();
        $proj_record = $proj_qry->result();

        $emp_count = 0;

        if( $proj_cnt > 0 ){
            
            $cnt = 1;

            $g_salary = 0;
            $g_ovrtme = 0;
            $g_nd = 0;
            $g_non_tax = 0;
            $g_taxable = 0;
            $g_gross = 0;
            $g_inc = 0;
            $g_whtax = 0;
            $g_sss = 0;
            $g_philhealth = 0;
            $g_pagibig = 0;
            $g_dedtn = 0;
            $g_loan = 0;
            $g_ded = 0;
            $g_netpay = 0;

            foreach ($proj_record as $key => $proj) {

                
                $qry = $this->db->query("SELECT distinct u.employee_id
                                    FROM {$this->db->dbprefix}user u
                                    LEFT JOIN {$this->db->dbprefix}employee_payroll e ON u.employee_id = e.employee_id
                                    LEFT JOIN {$this->db->dbprefix}employee ee ON u.employee_id = ee.employee_id
                                    LEFT JOIN {$this->db->dbprefix}payroll_current_transaction pct on u.employee_id = pct.employee_id
                                    WHERE pct.cost_code = '{$proj->cost_code}' AND payroll_date = '{$payroll_date}' AND pct.on_hold = 0 AND pct.deleted = 0
                                        AND pct.transaction_code = 'netpay' $pay_code $additional_qry $code_status
                                    ORDER BY lastname, firstname, middleinitial");

                $mdate = getdate(date("U"));
                $mdate = "$mdate[weekday], $mdate[month] $mdate[mday], $mdate[year]";

                $total_no_employees = $qry->num_rows();
                $allowed_count_per_page = 20;
                $page_with = $total_no_employees/$allowed_count_per_page;
                $page_floor = floor($page_with);

                $number_of_page = $page_floor;

                if($page_with > $page_floor){

                    $number_of_page = $page_floor + 1;
                }  
                
                if($total_no_employees > 0){
                    
                    for($i=1;$i<=$number_of_page; $i++){  
                        $limit = ($i - 1) * $allowed_count_per_page;
                        $dtl = $this->db->query("SELECT distinct u.employee_id, id_number,  lastname, firstname, middleinitial, aux, salary, bank_acct
                                    FROM {$this->db->dbprefix}user u
                                    LEFT JOIN {$this->db->dbprefix}employee_payroll e ON u.employee_id = e.employee_id
                                    LEFT JOIN {$this->db->dbprefix}employee ee ON u.employee_id = ee.employee_id
                                    LEFT JOIN {$this->db->dbprefix}payroll_current_transaction pct on u.employee_id = pct.employee_id
                                    WHERE pct.cost_code = '{$proj->cost_code}'AND payroll_date = '{$payroll_date}' AND pct.on_hold = 0 AND pct.deleted = 0
                                        $pay_code $additional_qry $code_status
                                    ORDER BY lastname, firstname, middleinitial
                                    LIMIT {$limit},{$allowed_count_per_page}");
                        
                        $this->pdf->SetMargins(10, 10, 10, true);   
                        $this->pdf->SetAutoPageBreak(TRUE);
                        $this->pdf->addPage('L', 'LEGAL', true);    
                        $this->pdf->SetFontSize( 6.5);            

                        $xcel_hed ='
                            <table>
                                <tr>
                                    <td style=" width:50%  ; text-align:left   ; font-size: 12 ; color:blue ; "><b>First Balfour Inc.</b></td>
                                    <td style=" width:50%  ; text-align:right  ; font-size:7  ; ">Run Date: '.date("m/d/Y").'</td>
                                </tr>
                                <tr>
                                    <td style=" width:50% ; text-align:left ; font-size: 9 ; ">'.$title.' #'.str_pad($period->payroll_period_id, 4,"0",STR_PAD_LEFT).'</td>
                                    <td style=" width:50%  ; text-align:right  ; font-size:7  ; ">Page : '.$cnt.'</td>
                                </tr>
                                <tr>
                                    <td style=" width:100% ; text-align:left ; font-size: 9 ; ">FROM '.$date_period.'</td>
                                </tr>
                                <tr>
                                    <td style=" width:100% ; text-align:left ; font-size: 9 ; ">PAYCODE : '.$paycode->paycode.'</td>
                                </tr>
                                <tr>
                                    <td style=" width:100% ; text-align:left ; font-size: 9 ; "></td>
                                </tr>
                                <tr>
                                    <td style=" width:15% ; text-align:left ; border-top-width:1px ; border-right-width:1px ; border-left-width:1px ; ">  NAME ID# </td>
                                    <td style=" width: 4% ; text-align:center ; border-top-width:1px ; border-right-width:1px ; ">HRS </td>
                                    <td style=" width: 5% ; text-align:center ; border-top-width:1px ; border-right-width:1px ; ">SALARY </td>
                                    <td style=" width: 6% ; text-align:center ; border-top-width:1px ; border-right-width:1px ; ">OT </td>
                                    <td style=" width: 4% ; text-align:center ; border-top-width:1px ; border-right-width:1px ; ">ND </td>
                                    <td style=" width: 5% ; text-align:center ; border-top-width:1px ; border-right-width:1px ; ">INC(NT) </td>
                                    <td style=" width: 5% ; text-align:center ; border-top-width:1px ; border-right-width:1px ; ">INC </td>
                                    <td style=" width: 5% ; text-align:center ; border-top-width:1px ; border-right-width:1px ; ">GROSS </td>
                                    <td style=" width: 5% ; text-align:center ; border-top-width:1px ; border-right-width:1px ; ">TOTAL </td>
                                    <td style=" width: 5% ; text-align:center ; border-top-width:1px ; border-right-width:1px ; ">W/TAX </td>
                                    <td style=" width: 5% ; text-align:center ; border-top-width:1px ; border-right-width:1px ; ">SSS </td>
                                    <td style=" width: 5% ; text-align:center ; border-top-width:1px ; border-right-width:1px ; ">MCR </td>
                                    <td style=" width: 5% ; text-align:center ; border-top-width:1px ; border-right-width:1px ; ">HDMF </td>   
                                    <td style=" width: 5% ; text-align:center ; border-top-width:1px ; border-right-width:1px ; ">DEDTN </td>
                                    <td style=" width: 5% ; text-align:center ; border-top-width:1px ; border-right-width:1px ; ">LOANS </td>
                                    <td style=" width: 5% ; text-align:center ; border-top-width:1px ; border-right-width:1px ; ">TOTAL </td>
                                    <td style=" width: 5% ; text-align:center ; border-top-width:1px ; border-right-width:1px ; ">NET </td>
                                    <td style=" width: 7% ; text-align:center ; border-top-width:1px ; border-right-width:1px ; ">BANK </td>
                                </tr>
                                <tr>
                                    <td style=" width:15% ; text-align:center ; border-bottom-width:1px ; border-right-width:1px ; border-left-width:1px ; "> </td>
                                    <td style=" width: 4% ; text-align:center ; border-bottom-width:1px ; border-right-width:1px ; ">WRK </td>
                                    <td style=" width: 5% ; text-align:center ; border-bottom-width:1px ; border-right-width:1px ; "> </td>
                                    <td style=" width: 6% ; text-align:center ; border-bottom-width:1px ; border-right-width:1px ; "> </td>
                                    <td style=" width: 4% ; text-align:center ; border-bottom-width:1px ; border-right-width:1px ; "> </td>
                                    <td style=" width: 5% ; text-align:center ; border-bottom-width:1px ; border-right-width:1px ; "> </td>
                                    <td style=" width: 5% ; text-align:center ; border-bottom-width:1px ; border-right-width:1px ; ">(TXBLE) </td>
                                    <td style=" width: 5% ; text-align:center ; border-bottom-width:1px ; border-right-width:1px ; ">INCOME </td>
                                    <td style=" width: 5% ; text-align:center ; border-bottom-width:1px ; border-right-width:1px ; ">INCOME </td>
                                    <td style=" width: 5% ; text-align:center ; border-bottom-width:1px ; border-right-width:1px ; "> </td>
                                    <td style=" width: 5% ; text-align:center ; border-bottom-width:1px ; border-right-width:1px ; "> </td>
                                    <td style=" width: 5% ; text-align:center ; border-bottom-width:1px ; border-right-width:1px ; "> </td>
                                    <td style=" width: 5% ; text-align:center ; border-bottom-width:1px ; border-right-width:1px ; "> </td>
                                    <td style=" width: 5% ; text-align:center ; border-bottom-width:1px ; border-right-width:1px ; "> </td>
                                    <td style=" width: 5% ; text-align:center ; border-bottom-width:1px ; border-right-width:1px ; "> </td>
                                    <td style=" width: 5% ; text-align:center ; border-bottom-width:1px ; border-right-width:1px ; ">DED. </td>
                                    <td style=" width: 5% ; text-align:center ; border-bottom-width:1px ; border-right-width:1px ; ">SALARY </td>
                                    <td style=" width: 7% ; text-align:center ; border-bottom-width:1px ; border-right-width:1px ; ">ACCT# </td>
                                </tr>';
                                
                        $check_cost_code = $this->db->query("SELECT * FROM hr_user_company_department WHERE department_code = '{$proj->cost_code}'")->num_rows();
                        if( $check_cost_code > 0 ){
                            $xcel_hed .='
                                <tr>
                                    <td style=" width:100% ; font-size:2 ; "></td>
                                </tr>
                                <tr>
                                    <td style=" width:100% ; text-align:left ; font-size: 9 ; ">'.$proj->cost_code.'</td>
                                </tr>
                                <tr>
                                    <td style=" width:100% ; font-size:2 ; "></td>
                                </tr>';
                        }
                        else {
                            $xcel_hed .='

                                <tr>
                                    <td style=" width:100% ; font-size:2 ; "></td>
                                </tr>
                                <tr>
                                    <td style=" width:100% ; text-align:left ; font-size: 9 ; ">'.$proj->cost_code.'-0-'.$status_code.'</td>
                                </tr>
                                <tr>
                                    <td style=" width:100% ; font-size:2 ; "></td>
                                </tr>';
                        } 

                        $total_salary = 0;
                        $total_ovrtme = 0;
                        $total_nd = 0;
                        $total_non_tax = 0;
                        $total_taxable = 0;
                        $total_gross = 0;
                        $total_inc = 0;
                        $total_whtax = 0;
                        $total_sss = 0;
                        $total_philhealth = 0;
                        $total_pagibig = 0;
                        $total_dedtn = 0;
                        $total_loan = 0;
                        $total_ded = 0;
                        $total_netpay = 0;
                        $dtl_emp_cnt = 0;

                        foreach ($dtl->result() as $key => $dtl_res) {

                            $hrs_wrk = $this->db->query("SELECT hours_worked FROM {$this->db->dbprefix}timekeeping_period_summary 
                                                WHERE payroll_date = '{$payroll_date}' AND employee_id = $dtl_res->employee_id")->row();
                            
                            $salary = $this->db->query(" SELECT sum(amount) as amount FROM {$this->db->dbprefix}payroll_current_transaction pct
                                                WHERE payroll_date = '{$payroll_date}' AND transaction_code IN ('SALARY') AND employee_id = $dtl_res->employee_id and pct.on_hold = 0 AND pct.deleted = 0")->row();

                            $ovrtme = $this->db->query("SELECT  SUM(amount) as amount
                                                FROM {$this->db->dbprefix}payroll_current_transaction pct
                                                LEFT JOIN {$this->db->dbprefix}payroll_transaction pt ON pct.transaction_id = pt.transaction_id
                                                WHERE payroll_date = '{$payroll_date}' AND pct.transaction_type_id IN (1,2) AND pct.transaction_code != 'salary' 
                                                    AND pt.transaction_class_id = 10 AND pt.transaction_code NOT LIKE ('%ND%') AND employee_id = $dtl_res->employee_id and pct.on_hold = 0 AND pct.deleted = 0")->row();

                            $nd = $this->db->query("SELECT  SUM(amount) as amount
                                                FROM {$this->db->dbprefix}payroll_current_transaction pct
                                                LEFT JOIN {$this->db->dbprefix}payroll_transaction pt ON pct.transaction_id = pt.transaction_id
                                                WHERE payroll_date = '{$payroll_date}' AND pct.transaction_type_id IN (1,2) AND pct.transaction_code != 'salary' 
                                                    AND pt.transaction_class_id = 10 AND pt.transaction_code LIKE ('%ND%') AND employee_id = $dtl_res->employee_id  and pct.on_hold = 0 AND pct.deleted = 0")->row();

                            $non_tax = $this->db->query("SELECT  SUM(amount) as amount
                                                FROM {$this->db->dbprefix}payroll_current_transaction pct
                                                LEFT JOIN {$this->db->dbprefix}payroll_transaction pt ON pct.transaction_id = pt.transaction_id
                                                WHERE payroll_date = '{$payroll_date}' AND pct.transaction_type_id IN (2,6,7) AND employee_id = $dtl_res->employee_id  and pct.on_hold = 0 AND pct.deleted = 0")->row();

                            $taxable = $this->db->query("SELECT  SUM(amount) as amount
                                                FROM {$this->db->dbprefix}payroll_current_transaction pct
                                                LEFT JOIN {$this->db->dbprefix}payroll_transaction pt ON pct.transaction_id = pt.transaction_id
                                                WHERE payroll_date = '{$payroll_date}' AND pct.transaction_type_id IN (1) AND pct.transaction_code != 'salary' 
                                                    AND pt.transaction_class_id != 10 AND employee_id = $dtl_res->employee_id  and pct.on_hold = 0 AND pct.deleted = 0")->row();

                            $attend_ded = $this->db->query("SELECT  SUM(amount) as amount
                                                FROM {$this->db->dbprefix}payroll_current_transaction pct
                                                LEFT JOIN {$this->db->dbprefix}payroll_transaction pt ON pct.transaction_id = pt.transaction_id
                                                WHERE payroll_date = '{$payroll_date}' AND pct.transaction_code IN ('LWOP','ABSENCES','DEDUCTION_LATE','DEDUCTION_UNDERTIME')
                                                AND employee_id = $dtl_res->employee_id AND pct.on_hold = 0 AND pct.deleted = 0")->row();
                            
                            $gross  = ( $salary->amount + $ovrtme->amount + $nd->amount + $taxable->amount ) - $attend_ded->amount;

                            $tot_inc = $gross + $non_tax->amount;

                            $whtax = $this->db->query(" SELECT sum(amount) as amount FROM {$this->db->dbprefix}payroll_current_transaction pct
                                                        WHERE payroll_date = '{$payroll_date}' AND transaction_code IN ('WHTAX') AND employee_id = $dtl_res->employee_id  and pct.on_hold = 0 AND pct.deleted = 0")->row();

                            $sss = $this->db->query("   SELECT sum(amount) as amount FROM {$this->db->dbprefix}payroll_current_transaction pct
                                                        WHERE payroll_date = '{$payroll_date}' AND transaction_code IN ('SSS_EMP') AND employee_id = $dtl_res->employee_id  and pct.on_hold = 0 AND pct.deleted = 0")->row();

                            $philhealth = $this->db->query("SELECT sum(amount) as amount FROM {$this->db->dbprefix}payroll_current_transaction pct
                                                        WHERE payroll_date = '{$payroll_date}' AND transaction_code IN ('PHIC_EMP') AND employee_id = $dtl_res->employee_id  and pct.on_hold = 0 AND pct.deleted = 0")->row();

                            $pagibig = $this->db->query("SELECT sum(amount) as amount FROM {$this->db->dbprefix}payroll_current_transaction pct
                                                        WHERE payroll_date = '{$payroll_date}' AND transaction_code IN ('HDMF_EMP') AND employee_id = $dtl_res->employee_id  and pct.on_hold = 0 AND pct.deleted = 0")->row();

                            $dedtn = $this->db->query("SELECT sum(amount) as amount FROM {$this->db->dbprefix}payroll_current_transaction pct
                                                LEFT JOIN {$this->db->dbprefix}payroll_transaction pt on pct.transaction_id = pt.transaction_id
                                                WHERE payroll_date = '{$payroll_date}' AND transaction_class_id IN (24,25,28)
                                                    AND employee_id = $dtl_res->employee_id  and pct.on_hold = 0 AND pct.deleted = 0")->row();

                            $loan = $this->db->query("SELECT sum(amount) as amount FROM {$this->db->dbprefix}payroll_current_transaction pct
                                                LEFT JOIN {$this->db->dbprefix}payroll_transaction pt on pct.transaction_id = pt.transaction_id
                                                WHERE payroll_date = '{$payroll_date}' AND pt.transaction_type_id IN (3,4,5) AND pt.transaction_class_id = 26 AND employee_id = $dtl_res->employee_id  and pct.on_hold = 0 AND pct.deleted = 0")->row();

                            $tot_ded = $loan->amount + $dedtn->amount + $whtax->amount + $philhealth->amount + $sss->amount + $pagibig->amount;

                            $netpay = $this->db->query("SELECT sum(amount) as amount FROM {$this->db->dbprefix}payroll_current_transaction pct
                                                WHERE payroll_date = '{$payroll_date}' AND transaction_code IN ('NETPAY') AND employee_id = $dtl_res->employee_id  and pct.on_hold = 0 AND pct.deleted = 0")->row();
                            if(!empty($dtl_res->aux)){
                                $emp_name = $dtl_res->id_number.' '.$dtl_res->lastname.', '.$dtl_res->firstname.' '.$dtl_res->aux.', '.$dtl_res->middleinitial;
                            }
                            else{
                                $emp_name = $dtl_res->id_number.' '.$dtl_res->lastname.', '.$dtl_res->firstname.' '.$dtl_res->middleinitial;
                            }
                            $xcel_hed .= ' 
                                <tr>
                                    <td style=" width:15% ; text-align:left ; border-top-width:1px ; border-right-width:1px ; border-left-width:1px ; ">  '.$emp_name.'</td>
                                    <td style=" width: 4% ; text-align:right ; border-top-width:1px ; border-right-width:1px ; ">'.number_format($hrs_wrk->hours_worked,2,'.',',').'  </td>
                                    <td style=" width: 5% ; text-align:right ; border-top-width:1px ; border-right-width:1px ; ">'.number_format($salary->amount,2,'.',',').'  </td>
                                    <td style=" width: 6% ; text-align:right ; border-top-width:1px ; border-right-width:1px ; ">'.number_format($ovrtme->amount,2,'.',',').'  </td>
                                    <td style=" width: 4% ; text-align:right ; border-top-width:1px ; border-right-width:1px ; ">'.number_format($nd->amount,2,'.',',').'  </td>
                                    <td style=" width: 5% ; text-align:right ; border-top-width:1px ; border-right-width:1px ; ">'.number_format($non_tax->amount,2,'.',',').'  </td>
                                    <td style=" width: 5% ; text-align:right ; border-top-width:1px ; border-right-width:1px ; ">'.number_format($taxable->amount,2,'.',',').'  </td>
                                    <td style=" width: 5% ; text-align:right ; border-top-width:1px ; border-right-width:1px ; ">'.number_format($gross,2,'.',',').'  </td>
                                    <td style=" width: 5% ; text-align:right ; border-top-width:1px ; border-right-width:1px ; ">'.number_format($tot_inc,2,'.',',').'  </td>
                                    <td style=" width: 5% ; text-align:right ; border-top-width:1px ; border-right-width:1px ; ">'.number_format($whtax->amount,2,'.',',').'  </td>
                                    <td style=" width: 5% ; text-align:right ; border-top-width:1px ; border-right-width:1px ; ">'.number_format($sss->amount,2,'.',',').'  </td>
                                    <td style=" width: 5% ; text-align:right ; border-top-width:1px ; border-right-width:1px ; ">'.number_format($philhealth->amount,2,'.',',').'  </td>
                                    <td style=" width: 5% ; text-align:right ; border-top-width:1px ; border-right-width:1px ; ">'.number_format($pagibig->amount,2,'.',',').'  </td>
                                    <td style=" width: 5% ; text-align:right ; border-top-width:1px ; border-right-width:1px ; ">'.number_format($dedtn->amount,2,'.',',').'  </td>
                                    <td style=" width: 5% ; text-align:right ; border-top-width:1px ; border-right-width:1px ; ">'.number_format($loan->amount,2,'.',',').'  </td>
                                    <td style=" width: 5% ; text-align:right ; border-top-width:1px ; border-right-width:1px ; ">'.number_format($tot_ded,2,'.',',').'  </td>
                                    <td style=" width: 5% ; text-align:right ; border-top-width:1px ; border-right-width:1px ; ">'.number_format($netpay->amount,2,'.',',').'  </td>
                                    <td style=" width: 7% ; text-align:left ; border-top-width:1px ; border-right-width:1px ; ">'.$dtl_res->bank_acct.' </td>
                                </tr>
                                <tr>
                                    <td style=" width:15% ; text-align:center ; border-bottom-width:1px ; border-right-width:1px ; border-left-width:1px ; ">'.number_format($this->encrypt->decode($dtl_res->salary),2,'.',',').'</td>
                                    <td style=" width: 4% ; text-align:center ; border-bottom-width:1px ; border-right-width:1px ; "> </td>
                                    <td style=" width: 5% ; text-align:center ; border-bottom-width:1px ; border-right-width:1px ; "> </td>
                                    <td style=" width: 6% ; text-align:center ; border-bottom-width:1px ; border-right-width:1px ; "> </td>
                                    <td style=" width: 4% ; text-align:center ; border-bottom-width:1px ; border-right-width:1px ; "> </td>
                                    <td style=" width: 5% ; text-align:center ; border-bottom-width:1px ; border-right-width:1px ; "> </td>
                                    <td style=" width: 5% ; text-align:center ; border-bottom-width:1px ; border-right-width:1px ; "> </td>
                                    <td style=" width: 5% ; text-align:center ; border-bottom-width:1px ; border-right-width:1px ; "> </td>
                                    <td style=" width: 5% ; text-align:center ; border-bottom-width:1px ; border-right-width:1px ; "> </td>
                                    <td style=" width: 5% ; text-align:center ; border-bottom-width:1px ; border-right-width:1px ; "> </td>
                                    <td style=" width: 5% ; text-align:center ; border-bottom-width:1px ; border-right-width:1px ; "> </td>
                                    <td style=" width: 5% ; text-align:center ; border-bottom-width:1px ; border-right-width:1px ; "> </td>
                                    <td style=" width: 5% ; text-align:center ; border-bottom-width:1px ; border-right-width:1px ; "> </td>
                                    <td style=" width: 5% ; text-align:center ; border-bottom-width:1px ; border-right-width:1px ; "> </td>
                                    <td style=" width: 5% ; text-align:center ; border-bottom-width:1px ; border-right-width:1px ; "> </td>
                                    <td style=" width: 5% ; text-align:center ; border-bottom-width:1px ; border-right-width:1px ; "> </td>
                                    <td style=" width: 5% ; text-align:center ; border-bottom-width:1px ; border-right-width:1px ; "> </td>
                                    <td style=" width: 7% ; text-align:center ; border-bottom-width:1px ; border-right-width:1px ; "> </td>
                                </tr>';
                            
                            $total_salary += $salary->amount;
                            $total_ovrtme += $ovrtme->amount;
                            $total_nd += $nd->amount;
                            $total_non_tax += $non_tax->amount;
                            $total_taxable += $taxable->amount;
                            $total_gross += $gross;
                            $total_inc += $tot_inc;
                            $total_whtax += $whtax->amount;
                            $total_sss += $sss->amount;
                            $total_philhealth += $philhealth->amount;
                            $total_pagibig += $pagibig->amount;
                            $total_dedtn += $dedtn->amount;
                            $total_loan += $loan->amount;
                            $total_ded += $tot_ded;
                            $total_netpay += $netpay->amount;

                            $g_salary += $salary->amount;
                            $g_ovrtme += $ovrtme->amount;
                            $g_nd += $nd->amount;
                            $g_non_tax += $non_tax->amount;
                            $g_taxable += $taxable->amount;
                            $g_gross += $gross;
                            $g_inc += $tot_inc;
                            $g_whtax += $whtax->amount;
                            $g_sss += $sss->amount;
                            $g_philhealth += $philhealth->amount;
                            $g_pagibig += $pagibig->amount;
                            $g_dedtn += $dedtn->amount;
                            $g_loan += $loan->amount;
                            $g_ded += $tot_ded;
                            $g_netpay += $netpay->amount;

                            $dtl_emp_cnt ++;
                            $emp_count++;
                        }
                        
                        $xcel_hed .= ' 
                                <tr><td></td></tr>
                                <tr>
                                    <td style=" width:15% ; font-size: 2;  border-top-width:3px ; border-left-width:3px ; "> </td>
                                    <td style=" width: 4% ; font-size: 2;  border-top-width:3px ; border-right-width:3px ; "> </td>
                                    <td style=" width: 5% ; font-size: 2;  border-top-width:3px ; border-right-width:3px ; "> </td>
                                    <td style=" width: 6% ; font-size: 2;  border-top-width:3px ; border-right-width:3px ; "> </td>
                                    <td style=" width: 4% ; font-size: 2;  border-top-width:3px ; border-right-width:3px ; "> </td>
                                    <td style=" width: 5% ; font-size: 2;  border-top-width:3px ; border-right-width:3px ; "> </td>
                                    <td style=" width: 5% ; font-size: 2;  border-top-width:3px ; border-right-width:3px ; "> </td>
                                    <td style=" width: 5% ; font-size: 2;  border-top-width:3px ; border-right-width:3px ; "> </td>
                                    <td style=" width: 5% ; font-size: 2;  border-top-width:3px ; border-right-width:3px ; "> </td>
                                    <td style=" width: 5% ; font-size: 2;  border-top-width:3px ; border-right-width:3px ; "> </td>
                                    <td style=" width: 5% ; font-size: 2;  border-top-width:3px ; border-right-width:3px ; "> </td>
                                    <td style=" width: 5% ; font-size: 2;  border-top-width:3px ; border-right-width:3px ; "> </td>
                                    <td style=" width: 5% ; font-size: 2;  border-top-width:3px ; border-right-width:3px ; "> </td>
                                    <td style=" width: 5% ; font-size: 2;  border-top-width:3px ; border-right-width:3px ; "> </td>
                                    <td style=" width: 5% ; font-size: 2;  border-top-width:3px ; border-right-width:3px ; "> </td>
                                    <td style=" width: 5% ; font-size: 2;  border-top-width:3px ; border-right-width:3px ; "> </td>
                                    <td style=" width: 5% ; font-size: 2;  border-top-width:3px ; border-right-width:3px ; "> </td>
                                    <td style=" width: 7% ; font-size: 2;  border-top-width:3px ; border-right-width:3px ; "> </td>
                                </tr>
                                <tr>
                                    <td style=" width:10% ; text-align:right ; border-left-width:3px  ; ">TOTAL / DEPT </td>
                                    <td style=" width: 9% ; text-align:center; border-right-width:3px ; ">'.$dtl_emp_cnt.'</td>
                                    <td style=" width: 5% ; text-align:right ; border-right-width:3px ; ">'.number_format($total_salary,2,'.',',').'  </td>
                                    <td style=" width: 6% ; text-align:right ; border-right-width:3px ; ">'.number_format($total_ovrtme,2,'.',',').'  </td>
                                    <td style=" width: 4% ; text-align:right ; border-right-width:3px ; ">'.number_format($total_nd,2,'.',',').'  </td>
                                    <td style=" width: 5% ; text-align:right ; border-right-width:3px ; ">'.number_format($total_non_tax,2,'.',',').'  </td>
                                    <td style=" width: 5% ; text-align:right ; border-right-width:3px ; ">'.number_format($total_taxable,2,'.',',').'  </td>
                                    <td style=" width: 5% ; text-align:right ; border-right-width:3px ; ">'.number_format($total_gross,2,'.',',').'  </td>
                                    <td style=" width: 5% ; text-align:right ; border-right-width:3px ; ">'.number_format($total_inc,2,'.',',').'  </td>
                                    <td style=" width: 5% ; text-align:right ; border-right-width:3px ; ">'.number_format($total_whtax,2,'.',',').'  </td>
                                    <td style=" width: 5% ; text-align:right ; border-right-width:3px ; ">'.number_format($total_sss,2,'.',',').'  </td>
                                    <td style=" width: 5% ; text-align:right ; border-right-width:3px ; ">'.number_format($total_philhealth,2,'.',',').'  </td>
                                    <td style=" width: 5% ; text-align:right ; border-right-width:3px ; ">'.number_format($total_pagibig,2,'.',',').'  </td>
                                    <td style=" width: 5% ; text-align:right ; border-right-width:3px ; ">'.number_format($total_dedtn,2,'.',',').'  </td>
                                    <td style=" width: 5% ; text-align:right ; border-right-width:3px ; ">'.number_format($total_loan,2,'.',',').'  </td>
                                    <td style=" width: 5% ; text-align:right ; border-right-width:3px ; ">'.number_format($total_ded,2,'.',',').'  </td>
                                    <td style=" width: 5% ; text-align:right ; border-right-width:3px ; ">'.number_format($total_netpay,2,'.',',').'  </td>
                                    <td style=" width: 7% ; text-align:right ; border-right-width:3px ; "></td>
                                </tr>
                                <tr>
                                    <td style=" width:15% ; font-size: 2;  border-bottom-width:3px ; border-left-width:3px ; "> </td>
                                    <td style=" width: 4% ; font-size: 2;  border-bottom-width:3px ; border-right-width:3px ; "> </td>
                                    <td style=" width: 5% ; font-size: 2;  border-bottom-width:3px ; border-right-width:3px ; "> </td>
                                    <td style=" width: 6% ; font-size: 2;  border-bottom-width:3px ; border-right-width:3px ; "> </td>
                                    <td style=" width: 4% ; font-size: 2;  border-bottom-width:3px ; border-right-width:3px ; "> </td>
                                    <td style=" width: 5% ; font-size: 2;  border-bottom-width:3px ; border-right-width:3px ; "> </td>
                                    <td style=" width: 5% ; font-size: 2;  border-bottom-width:3px ; border-right-width:3px ; "> </td>
                                    <td style=" width: 5% ; font-size: 2;  border-bottom-width:3px ; border-right-width:3px ; "> </td>
                                    <td style=" width: 5% ; font-size: 2;  border-bottom-width:3px ; border-right-width:3px ; "> </td>
                                    <td style=" width: 5% ; font-size: 2;  border-bottom-width:3px ; border-right-width:3px ; "> </td>
                                    <td style=" width: 5% ; font-size: 2;  border-bottom-width:3px ; border-right-width:3px ; "> </td>
                                    <td style=" width: 5% ; font-size: 2;  border-bottom-width:3px ; border-right-width:3px ; "> </td>
                                    <td style=" width: 5% ; font-size: 2;  border-bottom-width:3px ; border-right-width:3px ; "> </td>
                                    <td style=" width: 5% ; font-size: 2;  border-bottom-width:3px ; border-right-width:3px ; "> </td>
                                    <td style=" width: 5% ; font-size: 2;  border-bottom-width:3px ; border-right-width:3px ; "> </td>
                                    <td style=" width: 5% ; font-size: 2;  border-bottom-width:3px ; border-right-width:3px ; "> </td>
                                    <td style=" width: 5% ; font-size: 2;  border-bottom-width:3px ; border-right-width:3px ; "> </td>
                                    <td style=" width: 7% ; font-size: 2;  border-bottom-width:3px ; border-right-width:3px ; "> </td>
                                </tr>';

                        $xcel_hed .='</table>';

                        if($tot_no_emp == $emp_count){

                            $xcel_hed .= '
                                <table> 
                                <tr><td></td></tr>
                                <tr>
                                    <td style=" width:15% ; font-size: 2;  border-top-width:3px ; border-left-width:3px ; "> </td>
                                    <td style=" width: 4% ; font-size: 2;  border-top-width:3px ; border-right-width:3px ; "> </td>
                                    <td style=" width: 5% ; font-size: 2;  border-top-width:3px ; border-right-width:3px ; "> </td>
                                    <td style=" width: 6% ; font-size: 2;  border-top-width:3px ; border-right-width:3px ; "> </td>
                                    <td style=" width: 4% ; font-size: 2;  border-top-width:3px ; border-right-width:3px ; "> </td>
                                    <td style=" width: 5% ; font-size: 2;  border-top-width:3px ; border-right-width:3px ; "> </td>
                                    <td style=" width: 5% ; font-size: 2;  border-top-width:3px ; border-right-width:3px ; "> </td>
                                    <td style=" width: 5% ; font-size: 2;  border-top-width:3px ; border-right-width:3px ; "> </td>
                                    <td style=" width: 5% ; font-size: 2;  border-top-width:3px ; border-right-width:3px ; "> </td>
                                    <td style=" width: 5% ; font-size: 2;  border-top-width:3px ; border-right-width:3px ; "> </td>
                                    <td style=" width: 5% ; font-size: 2;  border-top-width:3px ; border-right-width:3px ; "> </td>
                                    <td style=" width: 5% ; font-size: 2;  border-top-width:3px ; border-right-width:3px ; "> </td>
                                    <td style=" width: 5% ; font-size: 2;  border-top-width:3px ; border-right-width:3px ; "> </td>
                                    <td style=" width: 5% ; font-size: 2;  border-top-width:3px ; border-right-width:3px ; "> </td>
                                    <td style=" width: 5% ; font-size: 2;  border-top-width:3px ; border-right-width:3px ; "> </td>
                                    <td style=" width: 5% ; font-size: 2;  border-top-width:3px ; border-right-width:3px ; "> </td>
                                    <td style=" width: 5% ; font-size: 2;  border-top-width:3px ; border-right-width:3px ; "> </td>
                                    <td style=" width: 7% ; font-size: 2;  border-top-width:3px ; border-right-width:3px ; "> </td>
                                </tr>
                                <tr>
                                    <td style=" width:10% ; text-align:right ; border-left-width:3px  ; ">TOTAL / DEPT </td>
                                    <td style=" width: 9% ; text-align:center; border-right-width:3px ; ">'.$tot_no_emp.'</td>
                                    <td style=" width: 5% ; text-align:right ; border-right-width:3px ; ">'.number_format($g_salary,2,'.',',').'  </td>
                                    <td style=" width: 6% ; text-align:right ; border-right-width:3px ; ">'.number_format($g_ovrtme,2,'.',',').'  </td>
                                    <td style=" width: 4% ; text-align:right ; border-right-width:3px ; ">'.number_format($g_nd,2,'.',',').'  </td>
                                    <td style=" width: 5% ; text-align:right ; border-right-width:3px ; ">'.number_format($g_non_tax,2,'.',',').'  </td>
                                    <td style=" width: 5% ; text-align:right ; border-right-width:3px ; ">'.number_format($g_taxable,2,'.',',').'  </td>
                                    <td style=" width: 5% ; text-align:right ; border-right-width:3px ; ">'.number_format($g_gross,2,'.',',').'  </td>
                                    <td style=" width: 5% ; text-align:right ; border-right-width:3px ; ">'.number_format($g_inc,2,'.',',').'  </td>
                                    <td style=" width: 5% ; text-align:right ; border-right-width:3px ; ">'.number_format($g_whtax,2,'.',',').'  </td>
                                    <td style=" width: 5% ; text-align:right ; border-right-width:3px ; ">'.number_format($g_sss,2,'.',',').'  </td>
                                    <td style=" width: 5% ; text-align:right ; border-right-width:3px ; ">'.number_format($g_philhealth,2,'.',',').'  </td>
                                    <td style=" width: 5% ; text-align:right ; border-right-width:3px ; ">'.number_format($g_pagibig,2,'.',',').'  </td>
                                    <td style=" width: 5% ; text-align:right ; border-right-width:3px ; ">'.number_format($g_dedtn,2,'.',',').'  </td>
                                    <td style=" width: 5% ; text-align:right ; border-right-width:3px ; ">'.number_format($g_loan,2,'.',',').'  </td>
                                    <td style=" width: 5% ; text-align:right ; border-right-width:3px ; ">'.number_format($g_ded,2,'.',',').'  </td>
                                    <td style=" width: 5% ; text-align:right ; border-right-width:3px ; ">'.number_format($g_netpay,2,'.',',').'  </td>
                                    <td style=" width: 7% ; text-align:right ; border-right-width:3px ; "></td>
                                </tr>
                                <tr>
                                    <td style=" width:15% ; font-size: 2;  border-bottom-width:3px ; border-left-width:3px ; "> </td>
                                    <td style=" width: 4% ; font-size: 2;  border-bottom-width:3px ; border-right-width:3px ; "> </td>
                                    <td style=" width: 5% ; font-size: 2;  border-bottom-width:3px ; border-right-width:3px ; "> </td>
                                    <td style=" width: 6% ; font-size: 2;  border-bottom-width:3px ; border-right-width:3px ; "> </td>
                                    <td style=" width: 4% ; font-size: 2;  border-bottom-width:3px ; border-right-width:3px ; "> </td>
                                    <td style=" width: 5% ; font-size: 2;  border-bottom-width:3px ; border-right-width:3px ; "> </td>
                                    <td style=" width: 5% ; font-size: 2;  border-bottom-width:3px ; border-right-width:3px ; "> </td>
                                    <td style=" width: 5% ; font-size: 2;  border-bottom-width:3px ; border-right-width:3px ; "> </td>
                                    <td style=" width: 5% ; font-size: 2;  border-bottom-width:3px ; border-right-width:3px ; "> </td>
                                    <td style=" width: 5% ; font-size: 2;  border-bottom-width:3px ; border-right-width:3px ; "> </td>
                                    <td style=" width: 5% ; font-size: 2;  border-bottom-width:3px ; border-right-width:3px ; "> </td>
                                    <td style=" width: 5% ; font-size: 2;  border-bottom-width:3px ; border-right-width:3px ; "> </td>
                                    <td style=" width: 5% ; font-size: 2;  border-bottom-width:3px ; border-right-width:3px ; "> </td>
                                    <td style=" width: 5% ; font-size: 2;  border-bottom-width:3px ; border-right-width:3px ; "> </td>
                                    <td style=" width: 5% ; font-size: 2;  border-bottom-width:3px ; border-right-width:3px ; "> </td>
                                    <td style=" width: 5% ; font-size: 2;  border-bottom-width:3px ; border-right-width:3px ; "> </td>
                                    <td style=" width: 5% ; font-size: 2;  border-bottom-width:3px ; border-right-width:3px ; "> </td>
                                    <td style=" width: 7% ; font-size: 2;  border-bottom-width:3px ; border-right-width:3px ; "> </td>
                                </tr>
                            </table>';
                        }

                        $this->pdf->writeHTML($xcel_hed, true, false, true, false, '');
                        $cnt++;    
                    }
                }
            }
        }
        else{
            $this->pdf->SetMargins(10, 10, 10, true);   
            $this->pdf->SetAutoPageBreak(TRUE);
            $this->pdf->addPage('P', 'A4', true);    
            $this->pdf->SetFontSize( 8); 
            $this->pdf->writeHTML("No Record Found!", true, false, true, false, '');
        }
    }
    function export_summary_total($paycode_id, $code_status_id, $payroll_date, $title){
        
        $this->db->where('payroll_date',$payroll_date);
        $period = $this->db->get('payroll_period')->row();
        $date_period = date("m/d/Y",strtotime($period->date_from)).' TO '.date("m/d/Y",strtotime($period->date_to));

        $company_setting_res = $this->db->query('SELECT * FROM '.$this->db->dbprefix('user_company').' WHERE company_id = "'.$company_id.'"')->row();

        // check if employee_id is null or empty
        if(!empty($company_id)){

            $company = " AND company_id IN ($company_id)";
        }
        if(!empty($employee_id)){
            
            $employee = " AND u.employee_id IN ($employee_id)";   
        }

        if(!empty($paycode_id)){
            $pay_code = 'AND e.paycode_id = '.$paycode_id;
        }

        if(!empty($cost_code)){
            $costcode = 'AND cc.cost_code_id = '.$cost_code;
        }

        $paycode = $this->db->query("select paycode from {$this->db->dbprefix}payroll_paycode where paycode_id = $paycode_id")->row();


        $cnt=1;
        $total_no_employees = $this->db->query("SELECT distinct pct.employee_id 
                    FROM {$this->db->dbprefix}payroll_current_transaction pct
                    LEFT JOIN {$this->db->dbprefix}user u on u.employee_id = pct.employee_id
                    WHERE payroll_date = '{$payroll_date}' AND paycode_id = $paycode_id AND u.inactive = 0 and pct.transaction_code = 'netpay' ")->num_rows();
        $mdate = getdate(date("U"));
        $mdate = "$mdate[weekday], $mdate[month] $mdate[mday], $mdate[year]";

        $project_name = $this->db->query("select * from {$this->db->dbprefix}project_name where cost_code = '{$proj->cost_code}'")->row();

        if(!empty($project_name->cost_code))
            $project = $project_name->cost_code.' - '.$project_name->project_name;
        else
            $project = $proj->cost_code;

        $this->pdf->SetMargins(10, 10, 10, true);   
        $this->pdf->SetAutoPageBreak(TRUE);
        $this->pdf->addPage('L', 'LEGAL', true);    
        $this->pdf->SetFontSize( 7);            

        $xcel_hed ='
            <table>
                <tr>
                    <td style=" width:50%  ; text-align:left   ; font-size: 12 ; color:blue ; "><b>'.$company_setting_res->company.'</b></td>
                    <td style=" width:50%  ; text-align:right  ; font-size:7  ; ">Run Date: '.date("m/d/Y").'</td>
                </tr>
                <tr>
                    <td style=" width:50% ; text-align:left ; font-size: 9 ; ">'.$title.' #'.str_pad($period->payroll_period_id, 4,"0",STR_PAD_LEFT).'</td>
                    <td style=" width:50%  ; text-align:right  ; font-size:7  ; ">Page : '.$cnt.'</td>
                </tr>
                <tr>
                    <td style=" width:100% ; text-align:left ; font-size: 9 ; ">FROM '.$date_period.'</td>
                </tr>
                <tr>
                    <td style=" width:100% ; text-align:left ; font-size: 9 ; ">PAYCODE : '.$paycode->paycode.'</td>
                </tr>
                <tr>
                    <td style=" width:100% ; text-align:left ; font-size: 9 ; "></td>
                </tr>
                <tr>
                    <td style=" width:15% ; text-align:left ; border-top-width:1px ; border-right-width:1px ; border-left-width:1px ; ">  NAME ID# </td>
                    <td style=" width: 4% ; text-align:center ; border-top-width:1px ; border-right-width:1px ; ">HRS </td>
                    <td style=" width: 5% ; text-align:center ; border-top-width:1px ; border-right-width:1px ; ">SALARY </td>
                    <td style=" width: 6% ; text-align:center ; border-top-width:1px ; border-right-width:1px ; ">OT </td>
                    <td style=" width: 4% ; text-align:center ; border-top-width:1px ; border-right-width:1px ; ">ND </td>
                    <td style=" width: 5% ; text-align:center ; border-top-width:1px ; border-right-width:1px ; ">INC(NT) </td>
                    <td style=" width: 5% ; text-align:center ; border-top-width:1px ; border-right-width:1px ; ">INC </td>
                    <td style=" width: 5% ; text-align:center ; border-top-width:1px ; border-right-width:1px ; ">GROSS </td>
                    <td style=" width: 5% ; text-align:center ; border-top-width:1px ; border-right-width:1px ; ">TOTAL </td>
                    <td style=" width: 5% ; text-align:center ; border-top-width:1px ; border-right-width:1px ; ">W/TAX </td>
                    <td style=" width: 5% ; text-align:center ; border-top-width:1px ; border-right-width:1px ; ">SSS </td>
                    <td style=" width: 5% ; text-align:center ; border-top-width:1px ; border-right-width:1px ; ">MCR </td>
                    <td style=" width: 5% ; text-align:center ; border-top-width:1px ; border-right-width:1px ; ">HDMF </td>   
                    <td style=" width: 5% ; text-align:center ; border-top-width:1px ; border-right-width:1px ; ">DEDTN </td>
                    <td style=" width: 5% ; text-align:center ; border-top-width:1px ; border-right-width:1px ; ">LOANS </td>
                    <td style=" width: 5% ; text-align:center ; border-top-width:1px ; border-right-width:1px ; ">TOTAL </td>
                    <td style=" width: 5% ; text-align:center ; border-top-width:1px ; border-right-width:1px ; ">NET </td>
                    <td style=" width: 7% ; text-align:center ; border-top-width:1px ; border-right-width:1px ; ">BANK </td>
                </tr>
                <tr>
                    <td style=" width:15% ; text-align:center ; border-bottom-width:1px ; border-right-width:1px ; border-left-width:1px ; "> </td>
                    <td style=" width: 4% ; text-align:center ; border-bottom-width:1px ; border-right-width:1px ; ">WRK </td>
                    <td style=" width: 5% ; text-align:center ; border-bottom-width:1px ; border-right-width:1px ; "> </td>
                    <td style=" width: 6% ; text-align:center ; border-bottom-width:1px ; border-right-width:1px ; "> </td>
                    <td style=" width: 4% ; text-align:center ; border-bottom-width:1px ; border-right-width:1px ; "> </td>
                    <td style=" width: 5% ; text-align:center ; border-bottom-width:1px ; border-right-width:1px ; "> </td>
                    <td style=" width: 5% ; text-align:center ; border-bottom-width:1px ; border-right-width:1px ; ">(TXBLE) </td>
                    <td style=" width: 5% ; text-align:center ; border-bottom-width:1px ; border-right-width:1px ; ">INCOME </td>
                    <td style=" width: 5% ; text-align:center ; border-bottom-width:1px ; border-right-width:1px ; ">INCOME </td>
                    <td style=" width: 5% ; text-align:center ; border-bottom-width:1px ; border-right-width:1px ; "> </td>
                    <td style=" width: 5% ; text-align:center ; border-bottom-width:1px ; border-right-width:1px ; "> </td>
                    <td style=" width: 5% ; text-align:center ; border-bottom-width:1px ; border-right-width:1px ; "> </td>
                    <td style=" width: 5% ; text-align:center ; border-bottom-width:1px ; border-right-width:1px ; "> </td>
                    <td style=" width: 5% ; text-align:center ; border-bottom-width:1px ; border-right-width:1px ; "> </td>
                    <td style=" width: 5% ; text-align:center ; border-bottom-width:1px ; border-right-width:1px ; "> </td>
                    <td style=" width: 5% ; text-align:center ; border-bottom-width:1px ; border-right-width:1px ; ">DED. </td>
                    <td style=" width: 5% ; text-align:center ; border-bottom-width:1px ; border-right-width:1px ; ">SALARY </td>
                    <td style=" width: 7% ; text-align:center ; border-bottom-width:1px ; border-right-width:1px ; ">ACCT# </td>
                </tr>
                <tr>
                    <td style=" width:100% ; font-size:2 ; "></td>
                </tr>
                <tr>
                    <td style=" width:100% ; text-align:left ; font-size: 9 ; ">'.$project.'</td>
                </tr>
                <tr>
                    <td style=" width:100% ; font-size:2 ; "></td>
                </tr>';
       
        $total_salary = $this->db->query(" SELECT sum(amount) as amount FROM {$this->db->dbprefix}payroll_current_transaction pct
                            LEFT JOIN {$this->db->dbprefix}employee_work_assignment w on pct.employee_id = w.employee_id
                            LEFT JOIN {$this->db->dbprefix}employee_payroll e on pct.employee_id = e.employee_id
                            LEFT JOIN {$this->db->dbprefix}user u on u.employee_id = pct.employee_id
                            WHERE payroll_date = '{$payroll_date}' AND transaction_code IN ('SALARY') AND u.inactive = 0  and pct.on_hold = 0 and w.assignment = 1 $pay_code")->row();

        $total_ovrtme = $this->db->query("SELECT  SUM(amount) as amount
                            FROM {$this->db->dbprefix}payroll_current_transaction pct
                            LEFT JOIN {$this->db->dbprefix}payroll_transaction pt ON pct.transaction_id = pt.transaction_id
                            LEFT JOIN {$this->db->dbprefix}employee_work_assignment w on pct.employee_id = w.employee_id
                            LEFT JOIN {$this->db->dbprefix}employee_payroll e on pct.employee_id = e.employee_id
                            LEFT JOIN {$this->db->dbprefix}user u on u.employee_id = pct.employee_id
                            WHERE payroll_date = '{$payroll_date}' AND pct.transaction_type_id IN (1,2) AND pct.transaction_code != 'salary' 
                                AND pt.transaction_class_id = 10 AND pt.transaction_code NOT LIKE ('%ND%') AND u.inactive = 0  and pct.on_hold = 0 and w.assignment = 1 $pay_code")->row();

        $total_nd = $this->db->query("SELECT  SUM(amount) as amount
                            FROM {$this->db->dbprefix}payroll_current_transaction pct
                            LEFT JOIN {$this->db->dbprefix}payroll_transaction pt ON pct.transaction_id = pt.transaction_id
                            LEFT JOIN {$this->db->dbprefix}employee_work_assignment w on pct.employee_id = w.employee_id
                            LEFT JOIN {$this->db->dbprefix}employee_payroll e on pct.employee_id = e.employee_id
                            LEFT JOIN {$this->db->dbprefix}user u on u.employee_id = pct.employee_id
                            WHERE payroll_date = '{$payroll_date}' AND pct.transaction_type_id IN (1,2) AND pct.transaction_code != 'salary' 
                                AND pt.transaction_class_id = 10 AND pt.transaction_code LIKE ('%ND%') AND u.inactive = 0 and pct.on_hold = 0 and w.assignment = 1 $pay_code")->row();

        $total_non_tax = $this->db->query("SELECT  SUM(amount) as amount
                            FROM {$this->db->dbprefix}payroll_current_transaction pct
                            LEFT JOIN {$this->db->dbprefix}payroll_transaction pt ON pct.transaction_id = pt.transaction_id
                            LEFT JOIN {$this->db->dbprefix}employee_work_assignment w on pct.employee_id = w.employee_id
                            LEFT JOIN {$this->db->dbprefix}employee_payroll e on pct.employee_id = e.employee_id
                            LEFT JOIN {$this->db->dbprefix}user u on u.employee_id = pct.employee_id
                            WHERE payroll_date = '{$payroll_date}' AND pct.transaction_type_id IN (2,6,7) AND u.inactive = 0 and pct.on_hold = 0 and w.assignment = 1 $pay_code")->row();

        $total_taxable = $this->db->query("SELECT  SUM(amount) as amount
                            FROM {$this->db->dbprefix}payroll_current_transaction pct
                            LEFT JOIN {$this->db->dbprefix}payroll_transaction pt ON pct.transaction_id = pt.transaction_id
                            LEFT JOIN {$this->db->dbprefix}employee_work_assignment w on pct.employee_id = w.employee_id
                            LEFT JOIN {$this->db->dbprefix}employee_payroll e on pct.employee_id = e.employee_id
                            LEFT JOIN {$this->db->dbprefix}user u on u.employee_id = pct.employee_id
                            WHERE payroll_date = '{$payroll_date}' AND pct.transaction_type_id IN (1) AND pct.transaction_code != 'salary' 
                                AND pt.transaction_class_id != 10 and pct.on_hold = 0 and w.assignment = 1 AND u.inactive = 0 $pay_code")->row();

        $tot_attend_ded = $this->db->query("SELECT  SUM(amount) as amount
                            FROM {$this->db->dbprefix}payroll_current_transaction pct
                            LEFT JOIN {$this->db->dbprefix}payroll_transaction pt ON pct.transaction_id = pt.transaction_id
                            LEFT JOIN {$this->db->dbprefix}employee_work_assignment w on pct.employee_id = w.employee_id
                            LEFT JOIN {$this->db->dbprefix}employee_payroll e on pct.employee_id = e.employee_id
                            LEFT JOIN {$this->db->dbprefix}user u on u.employee_id = pct.employee_id
                            WHERE payroll_date = '{$payroll_date}' AND pct.transaction_code IN ('LWOP','ABSENCES','DEDUCTION_LATE','DEDUCTION_UNDERTIME')
                                and pct.on_hold = 0 and w.assignment = 1 AND u.inactive = 0 $pay_code")->row();

        $total_gross = ( $total_salary->amount + $total_ovrtme->amount + $total_nd->amount + $total_taxable->amount ) - $tot_attend_ded->amount;

        $total_inc = $total_gross + $total_non_tax->amount;

        $total_whtax = $this->db->query(" SELECT sum(amount) as amount FROM {$this->db->dbprefix}payroll_current_transaction pct
                                    LEFT JOIN {$this->db->dbprefix}employee_work_assignment w on pct.employee_id = w.employee_id
                                    LEFT JOIN {$this->db->dbprefix}employee_payroll e on pct.employee_id = e.employee_id
                                    LEFT JOIN {$this->db->dbprefix}user u on u.employee_id = pct.employee_id
                                    WHERE payroll_date = '{$payroll_date}' AND transaction_code IN ('WHTAX') and pct.on_hold = 0 AND u.inactive = 0 and w.assignment = 1 $pay_code")->row();

        $total_sss = $this->db->query("   SELECT sum(amount) as amount FROM {$this->db->dbprefix}payroll_current_transaction pct
                                    LEFT JOIN {$this->db->dbprefix}employee_work_assignment w on pct.employee_id = w.employee_id
                                    LEFT JOIN {$this->db->dbprefix}employee_payroll e on pct.employee_id = e.employee_id
                                    LEFT JOIN {$this->db->dbprefix}user u on u.employee_id = pct.employee_id
                                    WHERE payroll_date = '{$payroll_date}' AND transaction_code IN ('SSS_EMP') and pct.on_hold = 0 AND u.inactive = 0 and w.assignment = 1 $pay_code")->row();

        $total_philhealth = $this->db->query("SELECT sum(amount) as amount FROM {$this->db->dbprefix}payroll_current_transaction pct
                                    LEFT JOIN {$this->db->dbprefix}employee_work_assignment w on pct.employee_id = w.employee_id
                                    LEFT JOIN {$this->db->dbprefix}employee_payroll e on pct.employee_id = e.employee_id
                                    LEFT JOIN {$this->db->dbprefix}user u on u.employee_id = pct.employee_id
                                    WHERE payroll_date = '{$payroll_date}' AND transaction_code IN ('PHIC_EMP') and pct.on_hold = 0 AND u.inactive = 0 and w.assignment = 1 $pay_code")->row();

        $total_pagibig = $this->db->query("SELECT sum(amount) as amount FROM {$this->db->dbprefix}payroll_current_transaction pct
                                    LEFT JOIN {$this->db->dbprefix}employee_work_assignment w on pct.employee_id = w.employee_id
                                    LEFT JOIN {$this->db->dbprefix}employee_payroll e on pct.employee_id = e.employee_id
                                    LEFT JOIN {$this->db->dbprefix}user u on u.employee_id = pct.employee_id
                                    WHERE payroll_date = '{$payroll_date}' AND transaction_code IN ('HDMF_EMP') and pct.on_hold = 0 AND u.inactive = 0 and w.assignment = 1 $pay_code")->row();

        $total_dedtn = $this->db->query("SELECT sum(amount) as amount FROM {$this->db->dbprefix}payroll_current_transaction pct
                            LEFT JOIN {$this->db->dbprefix}payroll_transaction pt on pct.transaction_id = pt.transaction_id 
                            LEFT JOIN {$this->db->dbprefix}employee_work_assignment w on pct.employee_id = w.employee_id
                            LEFT JOIN {$this->db->dbprefix}employee_payroll e on pct.employee_id = e.employee_id
                            LEFT JOIN {$this->db->dbprefix}user u on u.employee_id = pct.employee_id
                            WHERE payroll_date = '{$payroll_date}' AND pt.transaction_type_id IN (3,4,5) AND pt.transaction_class_id != 26 AND u.inactive = 0 
                                AND pt.transaction_code IN ('TAXABLE_DEDUCTION') and pct.on_hold = 0 and w.assignment = 1 and w.assignment = 1 $pay_code")->row();

        $total_loan = $this->db->query("SELECT sum(amount) as amount FROM {$this->db->dbprefix}payroll_current_transaction pct
                            LEFT JOIN {$this->db->dbprefix}payroll_transaction pt on pct.transaction_id = pt.transaction_id
                            LEFT JOIN {$this->db->dbprefix}employee_work_assignment w on pct.employee_id = w.employee_id
                            LEFT JOIN {$this->db->dbprefix}employee_payroll e on pct.employee_id = e.employee_id
                            LEFT JOIN {$this->db->dbprefix}user u on u.employee_id = pct.employee_id
                            WHERE payroll_date = '{$payroll_date}' AND pt.transaction_type_id IN (3,4,5) AND pt.transaction_class_id = 26 
                             and pct.on_hold = 0 and w.assignment = 1 AND u.inactive = 0 $pay_code")->row();

        $total_ded = $total_loan->amount + $total_dedtn->amount;

        $total_netpay = $this->db->query("SELECT sum(amount) as amount FROM {$this->db->dbprefix}payroll_current_transaction pct
                            LEFT JOIN {$this->db->dbprefix}employee_work_assignment w on pct.employee_id = w.employee_id
                            LEFT JOIN {$this->db->dbprefix}employee_payroll e on pct.employee_id = e.employee_id
                            LEFT JOIN {$this->db->dbprefix}user u on u.employee_id = pct.employee_id
                            WHERE payroll_date = '{$payroll_date}' AND transaction_code IN ('NETPAY') and pct.on_hold = 0 and w.assignment = 1 AND u.inactive = 0 $pay_code")->row();
        $xcel_hed .= ' 
                <tr><td></td></tr>
                <tr>
                    <td style=" width:15% ; font-size: 2;  border-top-width:3px ; border-left-width:3px ; "> </td>
                    <td style=" width: 4% ; font-size: 2;  border-top-width:3px ; border-right-width:3px ; "> </td>
                    <td style=" width: 5% ; font-size: 2;  border-top-width:3px ; border-right-width:3px ; "> </td>
                    <td style=" width: 6% ; font-size: 2;  border-top-width:3px ; border-right-width:3px ; "> </td>
                    <td style=" width: 4% ; font-size: 2;  border-top-width:3px ; border-right-width:3px ; "> </td>
                    <td style=" width: 5% ; font-size: 2;  border-top-width:3px ; border-right-width:3px ; "> </td>
                    <td style=" width: 5% ; font-size: 2;  border-top-width:3px ; border-right-width:3px ; "> </td>
                    <td style=" width: 5% ; font-size: 2;  border-top-width:3px ; border-right-width:3px ; "> </td>
                    <td style=" width: 5% ; font-size: 2;  border-top-width:3px ; border-right-width:3px ; "> </td>
                    <td style=" width: 5% ; font-size: 2;  border-top-width:3px ; border-right-width:3px ; "> </td>
                    <td style=" width: 5% ; font-size: 2;  border-top-width:3px ; border-right-width:3px ; "> </td>
                    <td style=" width: 5% ; font-size: 2;  border-top-width:3px ; border-right-width:3px ; "> </td>
                    <td style=" width: 5% ; font-size: 2;  border-top-width:3px ; border-right-width:3px ; "> </td>
                    <td style=" width: 5% ; font-size: 2;  border-top-width:3px ; border-right-width:3px ; "> </td>
                    <td style=" width: 5% ; font-size: 2;  border-top-width:3px ; border-right-width:3px ; "> </td>
                    <td style=" width: 5% ; font-size: 2;  border-top-width:3px ; border-right-width:3px ; "> </td>
                    <td style=" width: 5% ; font-size: 2;  border-top-width:3px ; border-right-width:3px ; "> </td>
                    <td style=" width: 7% ; font-size: 2;  border-top-width:3px ; border-right-width:3px ; "> </td>
                </tr>
                <tr>
                    <td style=" width:10% ; text-align:right ; border-left-width:3px  ; ">TOTAL </td>
                    <td style=" width: 9% ; text-align:center; border-right-width:3px ; ">'.$total_no_employees.'</td>
                    <td style=" width: 5% ; text-align:right ; border-right-width:3px ; ">'.number_format($total_salary->amount,2,'.',',').'  </td>
                    <td style=" width: 6% ; text-align:right ; border-right-width:3px ; ">'.number_format($total_ovrtme->amount,2,'.',',').'  </td>
                    <td style=" width: 4% ; text-align:right ; border-right-width:3px ; ">'.number_format($total_nd->amount,2,'.',',').'  </td>
                    <td style=" width: 5% ; text-align:right ; border-right-width:3px ; ">'.number_format($total_non_tax->amount,2,'.',',').'  </td>
                    <td style=" width: 5% ; text-align:right ; border-right-width:3px ; ">'.number_format($total_taxable->amount,2,'.',',').'  </td>
                    <td style=" width: 5% ; text-align:right ; border-right-width:3px ; ">'.number_format($total_gross,2,'.',',').'  </td>
                    <td style=" width: 5% ; text-align:right ; border-right-width:3px ; ">'.number_format($total_inc,2,'.',',').'  </td>
                    <td style=" width: 5% ; text-align:right ; border-right-width:3px ; ">'.number_format($total_whtax->amount,2,'.',',').'  </td>
                    <td style=" width: 5% ; text-align:right ; border-right-width:3px ; ">'.number_format($total_sss->amount,2,'.',',').'  </td>
                    <td style=" width: 5% ; text-align:right ; border-right-width:3px ; ">'.number_format($total_philhealth->amount,2,'.',',').'  </td>
                    <td style=" width: 5% ; text-align:right ; border-right-width:3px ; ">'.number_format($total_pagibig->amount,2,'.',',').'  </td>
                    <td style=" width: 5% ; text-align:right ; border-right-width:3px ; ">'.number_format($total_dedtn->amount,2,'.',',').'  </td>
                    <td style=" width: 5% ; text-align:right ; border-right-width:3px ; ">'.number_format($total_loan->amount,2,'.',',').'  </td>
                    <td style=" width: 5% ; text-align:right ; border-right-width:3px ; ">'.number_format($total_ded,2,'.',',').'  </td>
                    <td style=" width: 5% ; text-align:right ; border-right-width:3px ; ">'.number_format($total_netpay->amount,2,'.',',').'  </td>
                    <td style=" width: 7% ; text-align:right ; border-right-width:3px ; "></td>
                </tr>
                <tr>
                    <td style=" width:15% ; font-size: 2;  border-bottom-width:3px ; border-left-width:3px ; "> </td>
                    <td style=" width: 4% ; font-size: 2;  border-bottom-width:3px ; border-right-width:3px ; "> </td>
                    <td style=" width: 5% ; font-size: 2;  border-bottom-width:3px ; border-right-width:3px ; "> </td>
                    <td style=" width: 6% ; font-size: 2;  border-bottom-width:3px ; border-right-width:3px ; "> </td>
                    <td style=" width: 4% ; font-size: 2;  border-bottom-width:3px ; border-right-width:3px ; "> </td>
                    <td style=" width: 5% ; font-size: 2;  border-bottom-width:3px ; border-right-width:3px ; "> </td>
                    <td style=" width: 5% ; font-size: 2;  border-bottom-width:3px ; border-right-width:3px ; "> </td>
                    <td style=" width: 5% ; font-size: 2;  border-bottom-width:3px ; border-right-width:3px ; "> </td>
                    <td style=" width: 5% ; font-size: 2;  border-bottom-width:3px ; border-right-width:3px ; "> </td>
                    <td style=" width: 5% ; font-size: 2;  border-bottom-width:3px ; border-right-width:3px ; "> </td>
                    <td style=" width: 5% ; font-size: 2;  border-bottom-width:3px ; border-right-width:3px ; "> </td>
                    <td style=" width: 5% ; font-size: 2;  border-bottom-width:3px ; border-right-width:3px ; "> </td>
                    <td style=" width: 5% ; font-size: 2;  border-bottom-width:3px ; border-right-width:3px ; "> </td>
                    <td style=" width: 5% ; font-size: 2;  border-bottom-width:3px ; border-right-width:3px ; "> </td>
                    <td style=" width: 5% ; font-size: 2;  border-bottom-width:3px ; border-right-width:3px ; "> </td>
                    <td style=" width: 5% ; font-size: 2;  border-bottom-width:3px ; border-right-width:3px ; "> </td>
                    <td style=" width: 5% ; font-size: 2;  border-bottom-width:3px ; border-right-width:3px ; "> </td>
                    <td style=" width: 7% ; font-size: 2;  border-bottom-width:3px ; border-right-width:3px ; "> </td>
                </tr>';

        $xcel_hed .='</table>';

        $this->pdf->writeHTML($xcel_hed, true, false, true, false, '');
        $cnt++;    

    }
}

/* End of file */
/* Location: system/application */
?>