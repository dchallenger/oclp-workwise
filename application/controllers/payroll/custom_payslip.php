<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class custom_payslip extends my_controller
{
    function __construct(){
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

    function index(){
        $data['scripts'][] = '<script type="text/javascript" src="'.base_url().'lib/js/editview.js"></script>'.uploadify_script();
        $data['content'] = 'slategray/payroll/report/report_view';  

        $data['scripts'][] = chosen_script();
        
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
        $paycode = $this->db->query("SELECT paycode_id, paycode FROM {$this->db->dbprefix}payroll_paycode WHERE deleted = 0")->result_array();
        $paycode_html = '<select id="paycode_id" multiple="multiple" class="multi-select" name="paycode_id[]">';
        foreach($paycode as $paycode_record){
            $paycode_html .= '<option value="'.$paycode_record["paycode_id"].'">'.$paycode_record["paycode"].'</option>';
        }
        $paycode_html .= '</select>';        

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
        $company_id = '';
        if(isset($_POST['company_id']))
        {
            $company_arr = array();
            foreach ($_POST['company_id'] as $value) 
            {
                $company_arr[] = $value;    
            }
            $company_id = implode(',', $company_arr);
        }
        $employee_id = ''; 
        if(isset($_POST['employee_id']))
        {
            $employee_arr = array();
            foreach ($_POST['employee_id'] as $value2) 
            {
                $employee_arr[] = $value2;    
            }
            $employee_id = implode(',', $employee_arr);
        }

        $project_name_id = $_POST['project_name_id'];

        $payroll_date = date("Y-m-d",strtotime($_POST['user_id']));  

        $this->load->library('pdf');
        $html = $this->export_custom_payslip($project_name_id, $paycode_id, $company_id, $employee_id, $payroll_date, "Custom Payslip");        
        $title = "Custom Payslip";

        $this->pdf->Output(ucwords(str_replace(" ","_",$title))."_" . date('dmYHis') . '.pdf', 'I');
    }
        
    function export_custom_payslip($project_name_id, $paycode_id, $company_id, $employee_id, $payroll_date, $title) {
        ini_set("memory_limit", "1024M");
        $company = $this->db->query('SELECT * FROM '.$this->db->dbprefix('user_company').' WHERE company_id = "'.$company_id.'"')->row();        
        $this->db->where('payroll_date',$payroll_date);
        $period = $this->db->get('payroll_period')->row();
        $date_period = 'FROM '.date("m/d/Y",strtotime($period->date_from)).' TO '.date("m/d/Y",strtotime($period->date_to));
        $date_period2 = date("m/d/Y",strtotime($period->date_from)).' TO '.date("m/d/Y",strtotime($period->date_to));
        $emp_separate = explode(',', $employee_id);        
        $category_id = $this->input->post('category_id');

        $cnt = 1;
        if(!empty($company_id)) {
            $company_qry = " AND company_id IN ($company_id)";
        }
        if(!empty($employee_id)) {            
            $employee_qry = " AND p.employee_id IN ($employee_id)";   
        }

        if(!empty($paycode_id)) {
            $paycode_qry = " AND e.paycode_id IN ($paycode_id)";
        }

        if(!empty($project_name_id)) {
            $project_name_qry = " AND project_name_id IN ($project_name_id)";
        }
        $additional_qry = "AND 1";
        switch($category_id) {   
          case '1'://division
            $division_id = '';
            if(isset($_POST['division_id']))
            {
                $division_arr = array();
                foreach ($_POST['division_id'] as $value) 
                {
                    $division_arr[] = $value;    
                }
                $division_id = implode(',', $division_arr);
            }
            if(!empty($division_id)) {
              $additional_qry = " AND d.division_id IN ($division_id)";
            }
          break;
          case '2'://project
             $project_name_id = '';
            if(isset($_POST['project_name_id']))
            {
                $project_name_arr = array();
                foreach ($_POST['project_name_id'] as $value) 
                {
                    $project_name_arr[] = $value;    
                }
                $project_name_id = implode(',', $project_name_arr);
            }
            if(!empty($project_name_id)) {
              $additional_qry = " AND d.project_name_id IN ($project_name_id)";
            }
          break;
          case '3'://group
            $group_name_id = '';
            if(isset($_POST['group_name_id']))
            {
                $group_name_arr = array();
                foreach ($_POST['group_name_id'] as $value) 
                {
                    $group_name_arr[] = $value;    
                }
                $group_name_id = implode(',', $group_name_arr);
            }
            if(!empty($group_name_id)) {
              $additional_qry = " AND d.group_name_id IN ($group_name_id)";
            }
          break;
          case '4'://dept
             $department_id = '';
            if(isset($_POST['department_id']))
            {
                $department_arr = array();
                foreach ($_POST['department_id'] as $value) 
                {
                    $department_arr[] = $value;    
                }
                $department_id = implode(',', $department_arr);
            }
            if(!empty($department_id)) {
              $additional_qry = " AND d.department_id IN ($department_id)";
            }
          break;
        }
        $qry = "SELECT e.employee_id,salary FROM {$this->db->dbprefix}payroll_closed_transaction p
                LEFT JOIN {$this->db->dbprefix}employee_payroll e ON e.employee_id = p.employee_id
                LEFT JOIN {$this->db->dbprefix}user u ON u.employee_id = p.employee_id
                LEFT JOIN {$this->db->dbprefix}employee_work_assignment d ON e.employee_id = d.employee_id
                WHERE payroll_date = '{$payroll_date}' AND transaction_code = 'NETPAY' 
                      AND d.employee_work_assignment_category_id = {$category_id} AND d.assignment = 1 
                     $company_qry $employee_qry $paycode_qry $additional_qry ORDER BY u.lastname";
        $res = $this->db->query($qry)->result();
        foreach ($res as $key => $value) {
            $html = '';
            $employee_id = $value->employee_id;
            $employee_qry = "SELECT 
                              e.id_number,
                              u.lastname,
                              u.firstname,
                              u.middlename,
                              p.salary,
                              t.taxcode,
                              ewa.cost_code,
                              ps.payroll_schedule,
                              IF(
                                p.payment_type_id = 1,
                                IF(
                                  p.bank_id = 0,
                                  'CASH',
                                  IF(
                                    p.bank_id IS NULL,
                                    'CASH',
                                    b.bank_code_alpha
                                  )
                                ),
                                'CASH'
                              ) AS bankaccount,
                              p.bank_acct,
                              'STANDARD' AS tax_type
                            FROM
                              {$this->db->dbprefix}user u 
                              LEFT JOIN {$this->db->dbprefix}user_company_department d 
                                ON d.department_id = u.department_id 
                              LEFT JOIN {$this->db->dbprefix}employee e 
                                ON u.employee_id = e.employee_id 
                              LEFT JOIN {$this->db->dbprefix}employee_payroll p 
                                ON u.employee_id = p.employee_id 
                              LEFT JOIN {$this->db->dbprefix}taxcode t 
                                ON p.`taxcode_id` = t.`taxcode_id` 
                              LEFT JOIN {$this->db->dbprefix}employee_work_assignment ewa 
                                ON u.`employee_id` = ewa.`employee_id` 
                              LEFT JOIN {$this->db->dbprefix}bank b 
                                ON p.`bank_id` = b.`bank_id` 
                              LEFT JOIN {$this->db->dbprefix}payroll_schedule ps
                                ON p.`payroll_schedule_id` = ps.`payroll_schedule_id`
                             WHERE u.employee_id = {$employee_id}";
            $employee_res = $this->db->query($employee_qry)->row();
            $monthly_rate = $this->encrypt->decode($value->salary);
            $line = '';
            // $html .= '<table style="width:100%;'.$line.';border:1px black solid;">';            
            $html .= '<table style="width:100%;'.$line.'">';            
               $html .= '<tr>';
                    $html .= '<td style="width:8%;"></td>';
                    $html .= '<td style="width:8%;"></td>';
                    $html .= '<td style="width:10%;"></td>';
                    $html .= '<td style="width:12%;"></td>';
                    $html .= '<td style="width:10%;"></td>';
                    $html .= '<td style="width:12%;"></td>';
                    $html .= '<td style="width:15%;"></td>';
                    $html .= '<td style="width:15%;text-align:center;">'.str_pad($cnt, 4, '0', STR_PAD_LEFT).'</td>';
                $html .= '</tr>';
                $fullname = $employee_res->lastname.', '.$employee_res->firstname.' '.$employee_res->middlename;
                $font = '';
                if(strlen($fullname) > 30) {
                  $font = 'font-size:60px;';
                }
                $html .= '<tr>';
                    $html .= '<td></td>';
                    $html .= '<td></td>';
                    $html .= '<td style="width:10%;">Employee Name</td>';
                    $html .= '<td style="width:15%;'.$font.'">'.$fullname.'</td>';
                    $html .= '<td>Cost Center</td>';
                    $html .= '<td>'.$employee_res->cost_code.'</td>';
                    $html .= '<td style="text-align:left;" colspan="2">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$employee_res->lastname.', '.$employee_res->firstname.' '.$employee_res->middlename.'</td>';
                   
                $html .= '</tr>';
                $html .= '<tr>';
                    $html .= '<td>Pay Slip No.</td>';
                    $html .= '<td></td>';
                    $html .= '<td>Employee ID#</td>';
                    $html .= '<td>'.$employee_res->id_number.'</td>';
                    $html .= '<td colspan="2">'.$date_period.'</td>';  
                    $html .= '<td></td>';
                    $html .= '<td></td>';                  
                $html .= '</tr>';
                $html .= '<tr>';
                    $html .= '<td>Payroll Type</td>';
                    $html .= '<td>'.$employee_res->payroll_schedule.'</td>';
                    $html .= '<td>Bank Acct#</td>';
                    $html .= '<td>'.$employee_res->bankaccount.' - '.$employee_res->bank_acct.'</td>';
                    $html .= '<td>Tax Type</td>';
                    $html .= '<td>'.$employee_res->tax_type.'</td>';
                    $html .= '<td></td>';
                    $html .= '<td></td>';
                $html .= '</tr>';
                $html .= '<tr>';
                    $html .= '<td></td>';
                    $html .= '<td></td>';
                    $html .= '<td>TAX STAT</td>';
                    $html .= '<td>'.$employee_res->taxcode.'</td>';
                    $html .= '<td></td>';
                    $html .= '<td></td>';
                    $html .= '<td style="text-align:right;" colspan="2">'.$date_period2.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>';
                $html .= '</tr>';
                $earnings_taxable = $this->db->query(" SELECT pct.transaction_id, pct.transaction_code, sum(pct.amount) as amount 
                                                FROM {$this->db->dbprefix}payroll_closed_transaction pct
                                                LEFT JOIN {$this->db->dbprefix}payroll_transaction pt on pct.transaction_id = pt.transaction_id
                                                WHERE payroll_date = '{$payroll_date}' and employee_id = {$employee_id} 
                                                    AND pct.transaction_type_id IN (1,8) and pct.transaction_code NOT IN ('SALARY') AND pt.transaction_class_id != 10
                                                GROUP BY pct.transaction_id, pct.transaction_code");
                $earnings_nontax = $this->db->query(" SELECT transaction_id, transaction_code, sum(amount) as amount 
                                                FROM {$this->db->dbprefix}payroll_closed_transaction 
                                                WHERE payroll_date = '{$payroll_date}' and employee_id = {$employee_id} 
                                                    AND transaction_type_id IN (2,7) 
                                                GROUP BY transaction_id, transaction_code");
               

                $pay_salary = $this->db->query("SELECT amount FROM {$this->db->dbprefix}payroll_closed_transaction WHERE employee_id = '$employee_id' AND payroll_date = '{$payroll_date}' AND transaction_code = 'SALARY'")->row();
                $pay_ot_qry = "SELECT SUM(amount) AS amount FROM {$this->db->dbprefix}payroll_closed_transaction pct LEFT JOIN {$this->db->dbprefix}payroll_transaction pt  ON pct.transaction_id = pt.`transaction_id` WHERE employee_id = '{$employee_id}' AND payroll_date = '{$payroll_date}' AND pt.`transaction_class_id` = '10' ";
                $pay_ot_res = $this->db->query($pay_ot_qry)->row();
                $other_income_qry = "SELECT SUM(amount) AS amount FROM {$this->db->dbprefix}payroll_closed_transaction pct LEFT JOIN {$this->db->dbprefix}payroll_transaction pt ON pct.transaction_id = pt.`transaction_id` WHERE employee_id = '{$employee_id}' AND payroll_date = '{$payroll_date}' AND pt.`transaction_class_id` != 10  AND pct.`transaction_type_id` = 1 AND pct.`transaction_code` != 'SALARY'";
                $other_income_res = $this->db->query($other_income_qry)->row();
                $pay_adjustment = $this->db->query("SELECT amount FROM {$this->db->dbprefix}payroll_closed_transaction WHERE employee_id = '$employee_id' AND payroll_date = '{$payroll_date}' AND transaction_code = 'ALLOWANCE_BATCH'")->row();
                $pay_absences = $this->db->query("SELECT sum(amount) as amount FROM {$this->db->dbprefix}payroll_closed_transaction WHERE employee_id = '$employee_id' AND payroll_date = '{$payroll_date}' AND transaction_code IN ('ABSENCES', 'LWOP')")->row();
                $pay_tardy = $this->db->query("SELECT amount FROM {$this->db->dbprefix}payroll_closed_transaction WHERE employee_id = '$employee_id' AND payroll_date = '{$payroll_date}' AND (transaction_code = 'DEDUCTION_LATE' OR transaction_code = 'DEDUCTION_UNDERTIME')")->row();
                $pay_gross_income_qry = "SELECT SUM(amount) AS amount FROM {$this->db->dbprefix}payroll_closed_transaction pct LEFT JOIN {$this->db->dbprefix}payroll_transaction pt  ON pct.transaction_id = pt.`transaction_id` WHERE employee_id = '{$employee_id}' AND payroll_date = '{$payroll_date}' AND (pct.`transaction_type_id` = 1)";
                $pay_gross_income_res = $this->db->query($pay_gross_income_qry)->row();

                $gross_income_amt = $pay_gross_income_res->amount - $pay_absences->amount - $pay_tardy->amount;

                $other_ded = $this->db->query("SELECT pct.transaction_code, amount FROM {$this->db->dbprefix}payroll_closed_transaction pct
                                              LEFT JOIN {$this->db->dbprefix}payroll_transaction pt on pct.transaction_id = pt.transaction_id
                                              WHERE employee_id = '$employee_id' AND payroll_date = '{$payroll_date}'
                                                AND pt.transaction_code != 'WHTAX' AND pt.transaction_type_id = 3 AND pt.transaction_class_id != 26 ORDER BY pct.transaction_code")->result();
                
                $oth_ded = $this->db->query(" SELECT sum(amount) as amount FROM {$this->db->dbprefix}payroll_closed_transaction pct
                                              LEFT JOIN {$this->db->dbprefix}payroll_transaction pt on pct.transaction_id = pt.transaction_id
                                              WHERE employee_id = '$employee_id' AND payroll_date = '{$payroll_date}'
                                                AND pt.transaction_code != 'WHTAX' AND pt.transaction_type_id = 3 AND pt.transaction_class_id !=26")->row();

                // {$this->db->dbprefix}timekeeping_period_summary-------------
                $absences = $this->db->query("SELECT IF(absences is NULL, 0, absences) + IF(lwop is NULL, 0, lwop/8) as absences FROM {$this->db->dbprefix}timekeeping_period_summary WHERE employee_id = '$employee_id' AND payroll_date = '{$payroll_date}'")->row();
                $ot = $this->db->query("SELECT IF(reg_ot IS NULL, 0, reg_ot)  + IF(reg_nd IS NULL, 0, reg_nd)  + IF(reg_ndot IS NULL, 0, reg_ndot)  + IF(rd_ot IS NULL, 0, rd_ot)  + IF(rd_ot_excess IS NULL, 0, rd_ot_excess)  + IF(rd_ndot IS NULL, 0, rd_ndot)  + IF(rd_ndot_excess IS NULL, 0, rd_ndot_excess)  + IF(leg_ot IS NULL, 0, leg_ot)  + IF(leg_ot_excess IS NULL, 0, leg_ot_excess)  + IF(leg_ndot IS NULL, 0, leg_ndot)  + IF(leg_ndot_excess IS NULL, 0, leg_ndot_excess)  + IF(spe_ot IS NULL, 0, spe_ot)  + IF(spe_ot_excess IS NULL, 0, spe_ot_excess)  + IF(spe_ndot IS NULL, 0, spe_ndot)  + IF(spe_ndot_excess IS NULL, 0, spe_ndot_excess)  +  IF(legrd_ot IS NULL, 0, legrd_ot)  +  IF(legrd_ot_excess IS NULL, 0, legrd_ot_excess)  +  IF(legrd_ndot IS NULL, 0, legrd_ndot)  +  IF(legrd_ndot_excess IS NULL, 0, legrd_ndot_excess)  +  IF(sperd_ot IS NULL, 0, sperd_ot)  +  IF(sperd_ot_excess IS NULL, 0, sperd_ot_excess)  + IF(sperd_ndot IS NULL, 0, sperd_ndot)  +  IF(sperd_ndot_excess IS NULL, 0, sperd_ndot_excess)  +  IF(dob_ot IS NULL, 0, dob_ot)  +  IF(dob_ot_excess IS NULL, 0, dob_ot_excess)  +  IF(dob_ndot IS NULL, 0, dob_ndot)  +  IF(dob_ndot_excess IS NULL, 0, dob_ndot_excess)  +  IF(dobrd_ot IS NULL, 0, dobrd_ot)  +  IF(dobrd_ot_excess IS NULL, 0, dobrd_ot_excess)  + IF(dobrd_ndot IS NULL, 0, dobrd_ndot)  + IF(dobrd_ndot_excess IS NULL, 0, dobrd_ndot_excess)   AS overtime  FROM {$this->db->dbprefix}timekeeping_period_summary WHERE employee_id = '$employee_id' AND payroll_date = '{$payroll_date}'")->row();
                $tardy = $this->db->query("SELECT IF(lates IS NULL, 0, lates)  + IF(undertime IS NULL, 0, undertime)   AS tardy  FROM {$this->db->dbprefix}timekeeping_period_summary WHERE employee_id = '$employee_id' AND payroll_date = '{$payroll_date}'")->row();

                $loan_qry = "SELECT SUM(amount) AS amount FROM {$this->db->dbprefix}payroll_closed_transaction pct LEFT JOIN {$this->db->dbprefix}payroll_transaction pt ON pct.transaction_id = pt.`transaction_id` WHERE employee_id = '{$employee_id}' AND payroll_date = '{$payroll_date}' AND pt.`transaction_class_id` = 26";
                $loan_res = $this->db->query($loan_qry)->row();

                $allowance_qry = "SELECT SUM(amount) AS amount FROM {$this->db->dbprefix}payroll_closed_transaction pct LEFT JOIN {$this->db->dbprefix}payroll_transaction pt  ON pct.transaction_id = pt.`transaction_id` WHERE employee_id = '{$employee_id}' AND payroll_date = '{$payroll_date}' AND (pct.`transaction_type_id` = 2)";
                $allowance_res = $this->db->query($allowance_qry)->row();

                $pay_netpay = $this->db->query("SELECT amount FROM {$this->db->dbprefix}payroll_closed_transaction WHERE employee_id = '$employee_id' AND payroll_date = '{$payroll_date}' AND transaction_code = 'NETPAY'")->row();


                $pay_sss = $this->db->query("SELECT amount FROM {$this->db->dbprefix}payroll_closed_transaction WHERE employee_id = $employee_id AND payroll_date = '{$payroll_date}' 
                                            AND transaction_code = 'SSS_EMP'")->row();
                $pay_hdmf = $this->db->query("SELECT amount FROM {$this->db->dbprefix}payroll_closed_transaction WHERE employee_id = $employee_id AND payroll_date = '{$payroll_date}' 
                                                AND transaction_code = 'HDMF_EMP'")->row();
                $pay_phic = $this->db->query("SELECT amount FROM {$this->db->dbprefix}payroll_closed_transaction WHERE employee_id = $employee_id AND payroll_date = '{$payroll_date}' 
                                                AND transaction_code = 'PHIC_EMP'")->row();
                $pay_tax = $this->db->query("SELECT amount FROM {$this->db->dbprefix}payroll_closed_transaction WHERE employee_id = $employee_id AND payroll_date = '{$payroll_date}' 
                                                AND transaction_code = 'WHTAX'")->row();

                //YTD
                $ytd_income = $this->db->query("SELECT sum(amount) as amount FROM {$this->db->dbprefix}payroll_closed_transaction WHERE employee_id = $employee_id AND transaction_type_id = 1")->row();
                $ytd_ded = $this->db->query("SELECT sum(amount) as amount FROM {$this->db->dbprefix}payroll_closed_transaction WHERE employee_id = $employee_id AND transaction_code IN ('LWOP','ABSENCES','DEDUCTION_LATE','DEDUCTION_UNDERTIME','TAXABLE_DEDUCTION')")->row();
                $ytd_gross = $ytd_income->amount - $ytd_ded->amount;


                $ytd_tax = $this->db->query("SELECT sum(amount) as amount FROM {$this->db->dbprefix}payroll_closed_transaction WHERE employee_id = $employee_id AND transaction_code = 'WHTAX'")->row();


                $loan_trans_qry = "SELECT 
                                      pt.`transaction_code`,
                                      el.`running_balance`,
                                      elp.`amount` 
                                    FROM
                                      {$this->db->dbprefix}employee_loan_payment elp 
                                      LEFT JOIN {$this->db->dbprefix}employee_loan el 
                                        ON elp.`employee_loan_id` = el.`employee_loan_id` 
                                      LEFT JOIN {$this->db->dbprefix}payroll_loan pl 
                                        ON el.`loan_id` = pl.`loan_id` 
                                      LEFT JOIN {$this->db->dbprefix}payroll_transaction pt 
                                        ON pl.`amortization_transid` = pt.`transaction_id` 
                                    WHERE elp.`payroll_date` = '{$payroll_date}' AND el.`employee_id` = '{$employee_id}'";
                $loan_trans_res = $this->db->query($loan_trans_qry)->result();
                $html .= '<tr>';
                    $html .= '<td colspan="2">';
                        $html .= '<table style="">';
                            $html .= '<tr>';
                                $html .= '<td>Latest Basic Salary</td>';
                                $html .= '<td style="text-align:right;">'.number_format($monthly_rate, 2, '.', ',').'</td>';
                            $html .= '</tr>';
                            $html .= '<tr>';
                                $html .= '<td>Income Details</td>';
                                $html .= '<td></td>';
                            $html .= '</tr>';
                            $html .= '<tr>';
                                $html .= '<td></td>';
                                $html .= '<td></td>';
                            $html .= '</tr>';
                            $html .= '<tr>';
                                $html .= '<td>REGULAR PAY</td>';
                                $html .= '<td style="text-align:right;">'.( $pay_salary->amount != "" ? number_format($pay_salary->amount,2,'.',',') : "0.00" ).'</td>';
                            $html .= '</tr>';
                            $html .= '<tr>';
                                $html .= '<td>OVERTIME</td>';
                                $html .= '<td style="text-align:right;">'.( $pay_ot_res->amount != "" ? number_format($pay_ot_res->amount,2,'.',',') : "0.00" ).'</td>';
                            $html .= '</tr>';
                            $html .= '<tr>';
                                $html .= '<td>OTHER INCOME</td>';
                                $html .= '<td style="text-align:right;">'.( $other_income_res->amount != "" ? number_format($other_income_res->amount,2,'.',',') : "0.00" ).'</td>';
                            $html .= '</tr>';
                            $html .= '<tr>';
                                $html .= '<td>ADJUSTMENT</td>';
                                $html .= '<td style="text-align:right;">'.( $pay_adjustment->amount != "" ? number_format($pay_adjustment->amount,2,'.',',') : "0.00" ).'</td>';
                            $html .= '</tr>';
                            $html .= '<tr>';
                                $html .= '<td>ABSENCES</td>';
                                $html .= '<td style="text-align:right;">'.( $pay_absences->amount != "" ? number_format($pay_absences->amount,2,'.',',') : "0.00" ).'</td>';
                            $html .= '</tr>';
                            $html .= '<tr>';
                                $html .= '<td>TARDY/UT</td>';
                                $html .= '<td style="text-align:right;">'.( $pay_tardy->amount != "" ? number_format($pay_tardy->amount,2,'.',',') : "0.00" ).'</td>';
                            $html .= '</tr>';
                            $html .= '<tr>';
                                $html .= '<td>GROSS INCOME</td>';
                                $html .= '<td style="text-align:right;">'.( $gross_income_amt != "" ? number_format($gross_income_amt,2,'.',',') : "0.00" ).'</td>';
                            $html .= '</tr>';
                            $html .= '<tr>';
                                $html .= '<td></td>';
                                $html .= '<td></td>';
                            $html .= '</tr>';
                            $html .= '<tr>';
                                $html .= '<td>YEAR-TO-DATE</td>';
                                $html .= '<td></td>';
                            $html .= '</tr>';
                            $html .= '<tr>';
                                $html .= '<td>GROSS INC.:</td>';
                                $html .= '<td style="text-align:right;">'.number_format($ytd_gross, 2, '.', ',').'</td>';
                            $html .= '</tr>';
                            $html .= '<tr>';
                                $html .= '<td>W/HOLDING :</td>';
                                $html .= '<td style="text-align:right;">'.number_format($ytd_tax->amount, 2, '.', ',').'</td>';
                            $html .= '</tr>';
                        $html .= '</table>';
                    $html .= '</td>';
                    $html .= '<td colspan="2">';
                        $html .= '<table style="">';
                             $html .= '<tr>';
                                $html .= '<td>Days Absent</td>';
                                $html .= '<td style="text-align:right;">'.number_format($absences->absences, 2, '.', ',').'</td>';
                            $html .= '</tr>';
                            $html .= '<tr>';
                                $html .= '<td>Overtime Hrs</td>';
                                $html .= '<td style="text-align:right;">'.number_format($ot->overtime, 2, '.', ',').'</td>';
                            $html .= '</tr>';
                            $html .= '<tr>';
                                $html .= '<td>Tardy/UT Hrs</td>';
                                $html .= '<td style="text-align:right;">'.number_format($tardy->tardy, 2, '.', ',').'</td>';
                            $html .= '</tr>';
                            $html .= '<tr>';
                                $html .= '<td></td>';
                                $html .= '<td></td>';
                            $html .= '</tr>';
                            $html .= '<tr>';
                                $html .= '<td>W/TAX</td>';
                                $html .= '<td style="text-align:right;">'.number_format($pay_tax->amount, 2, '.', ',').'</td>';
                            $html .= '</tr>';
                            $html .= '<tr>';
                                $html .= '<td colspan="2">LESS OTHER DEDUCTIONS</td>';
                            $html .= '</tr>';
                            $html .= '<tr>';
                                $html .= '<td>EMPLOYEE SSS</td>';
                                $html .= '<td style="text-align:right;">'.number_format($pay_sss->amount, 2, '.', ',').'</td>';
                            $html .= '</tr>';
                            $html .= '<tr>';
                                $html .= '<td>EMPLOYEE PHEALTH</td>';
                                $html .= '<td style="text-align:right;">'.number_format($pay_phic->amount, 2, '.', ',').'</td>';
                            $html .= '</tr>';
                            $html .= '<tr>';
                                $html .= '<td>EMPLOYEE PAG-IBIG</td>';
                                $html .= '<td style="text-align:right;">'.number_format($pay_hdmf->amount, 2, '.', ',').'</td>';
                            $html .= '</tr>';
                            $html .= '<tr>';
                                $html .= '<td>LOAN PAYMENTS</td>';
                                $html .= '<td style="text-align:right;">'.number_format($loan_res->amount, 2, '.', ',').'</td>';
                            $html .= '</tr>';                            
                            $html .= '<tr>';
                                $html .= '<td>OTH DEDUCTIONS</td>';
                                $html .= '<td style="text-align:right;">'.number_format($oth_ded->amount, 2, '.', ',').'</td>';
                            $html .= '</tr>';
                            $html .= '<tr>';
                                $html .= '<td>ADD ALLOWANCE (NT)</td>';
                                $html .= '<td style="text-align:right;">'.number_format($allowance_res->amount, 2, '.', ',').'</td>';
                            $html .= '</tr>';
                            $html .= '<tr>';
                                $html .= '<td></td>';
                                $html .= '<td></td>';
                            $html .= '</tr>';
                            $html .= '<tr>';
                                $html .= '<td>NET SALARY</td>';
                                $html .= '<td style="text-align:right;">'.number_format($pay_netpay->amount, 2, '.', ',').'</td>';
                            $html .= '</tr>';                            
                        $html .= '</table>';
                    $html .= '</td>';
                    $html .= '<td colspan="2">';
                        $html .= '<table style="">';
                             $html .= '<tr>';
                                $html .= '<td colspan="3">Loan Payments</td>';
                            $html .= '</tr>';
                            $html .= '<tr>';
                                $html .= '<td style="text-align:center;">CODE</td>';
                                $html .= '<td style="text-align:center;">BAL</td>';
                                $html .= '<td style="text-align:center;">AMT</td>';
                            $html .= '</tr>';
                            foreach ($loan_trans_res as $key => $loans) {
                                $html .= '<tr>';
                                    $html .= '<td style="text-align:center;">'.$loans->transaction_code.'</td>';
                                    $html .= '<td style="text-align:right;">'.number_format($loans->running_balance, 2, '.', ',').'</td>';
                                    $html .= '<td style="text-align:right;">'.number_format($loans->amount, 2, '.', ',').'</td>';
                                $html .= '</tr>';
                            }
                             $html .= '<tr>';
                                $html .= '<td></td>';
                            $html .= '</tr>';
                            $html .= '<tr>';
                                $html .= '<td colspan="3">Other Deductions</td>';
                            $html .= '</tr>';
                            foreach ($other_ded as $key => $ded) {
                              $html .= '<tr>';
                                $html .= '<td colspan="2">'.$ded->transaction_code.'</td>';
                                $html .= '<td style="text-align:right;">'.number_format($ded->amount, 2, '.', ',').'</td>';
                              $html .= '</tr>';
                            }
                            $html .= '<tr>';
                                $html .= '<td colspan="3">Other Taxable Income</td>';
                            $html .= '</tr>';
                            foreach ($earnings_taxable->result() as $key => $earn_tax) {
                                $html .= '<tr>';
                                    $html .= '<td colspan="2">'.$earn_tax->transaction_code.'</td>';
                                    $html .= '<td style="text-align:right;">'.number_format($earn_tax->amount, 2, '.', ',').'</td>';
                                $html .= '</tr>';
                            }
                            $html .= '<tr>';
                                $html .= '<td></td>';
                            $html .= '</tr>';
                            $html .= '<tr>';
                                $html .= '<td colspan="3">Other Income Non Taxable</td>';
                            $html .= '</tr>';
                            foreach ($earnings_nontax->result() as $key => $earn_nontax) {
                                $html .= '<tr>';
                                    $html .= '<td colspan="2">'.$earn_nontax->transaction_code.'</td>';
                                    $html .= '<td style="text-align:right;">'.number_format($earn_nontax->amount, 2, '.', ',').'</td>';
                                $html .= '</tr>';
                            }                        
                        $html .= '</table>';
                    $html .= '</td>';
                $html .= '</tr>';
                $html .= '<tr><td></td></tr>';
            $html .= '</table>';
            $cnt++;            
            $this->pdf->SetMargins(2, 6, 5, 5);
            $this->pdf->addPage('L', 'A13', true);
            $this->pdf->SetFontSize( 7 );
            $this->pdf->writeHTML($html, true, false, true, false, '');
        }        
    }
}
/* End of file */
/* Location: system/application */
?>
