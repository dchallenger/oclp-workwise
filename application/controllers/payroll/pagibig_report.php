<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class pagibig_report extends MY_Controller
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

    function get_parameters()
    {
        $report_type = array("Monthly (M1-1)", "Membership Contribution Remittance Form (MCRF)", "Quarterly", "Remittance Certification", "Short-Term Loan Remittance Form (STLRF)", "HDMF to disk","HDMF Loan to disk");
        $report_type_html = '<select id="report_type_id" name="report_type_id">';
            foreach($report_type as $report_type_id => $report_type_value){
                $report_type_html .= '<option value="'.$report_type_id.'">'.$report_type_value.'</option>';
            }
        $report_type_html .= '</select>'; 



        $company = $this->db->query('SELECT * FROM '.$this->db->dbprefix('user_company').'')->result_array();
        $company_html = '<select id="company_id" name="company_id">';
        $company_html .= '<option value="">Select...</option>';
            foreach($company as $company_record){
                 $company_html .= '<option value="'.$company_record["company_id"].'">'.$company_record["company"].'</option>';
            }
        $company_html .= '</select>'; 

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
                                            ON a.employee_id = c.employee_id ORDER BY b.lastname")->result_array();
        $employee_html = '<select id="employee_id" multiple="multiple" class="multi-select" name="employee_id[]">';
            foreach($employee as $employee_record){
                $employee_html .= '<option value="'.$employee_record["employee_id"].'">'.$employee_record["lastname"].' '.$employee_record["firstname"].' '.$employee_record["aux"].' '.$employee_record["middleinitial"].'</option>';
            }
        $employee_html .= '</select>';        

        $response->report_type_html = $report_type_html;
        $response->employee_html = $employee_html;
        $response->company_html = $company_html;
        $data['json'] = $response;
        $this->load->view('template/ajax', $data);  
    }

    function employee_multiple()
    {
        $company_id = $_POST['company_id'];
        $qry_com = " AND b.company_id IN ({$company_id})";
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
                                        WHERE 1 {$qry_com}
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

    function export_report()
    {
        $company_id = $_POST['company_id'];
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
        
        $page_hidden = true;   
        if($_POST['print_type'])
        {
            $page_hidden = false;   
        }
        $date_from = date("Y-m-d",strtotime($_POST['date_range_from']));
        $date_to = date("Y-m-d",strtotime($_POST['date_range_to']));

        $this->load->library('pdf');        
        switch ($_POST['report_type_id']) 
        {
            //Monthly (M1-1)
            case '0':
                $this->pdf->SetMargins(5, 5, 5);   
                $this->pdf->SetFontSize( 8 );             
                $this->export_pagibig_monthly($company_id, $employee_id, $date_from, $date_to, "MEMBERSHIP REGISTRATION/REMITTANCE FORM", $page_hidden);                                                
                $title = "PAGIBIG Premium Contribution";
                break;
            //MCRF
            case '1':
                $this->pdf->SetMargins(5, 5, 5);
                $this->pdf->SetFontSize( 8 );
                $html = $this->export_pagibig_MCRF($company_id, $employee_id, $date_from, $date_to, "MEMBERSHIP REGISTRATION/REMITTANCE FORM", $page_hidden);        
                $title = "PAGIBIG Premium Contribution";
                break;              
            //quarterly
            case '2':
                $this->pdf->SetMargins(5, 5, 5);    
                $this->pdf->SetFontSize( 8 );
                $html = $this->export_pagibig_quarterly($company_id, $employee_id, $date_from, $date_to, "MEMBERSHIP REGISTRATION/REMITTANCE FORM", $page_hidden);        
                $title = "PAGIBIG Premium Contribution";
                break;
            //remittance
            case '3':
                $html = $this->export_pagibig_remittance($company_id, $employee_id, $date_from, $date_to, "CERTIFICATE OF PREMIUM PAYMENT", $page_hidden);        
                $title = "CERTIFICATE OF PREMIUM PAYMENT";
                break;
            // STLRF
            case '4':
                $this->pdf->SetMargins(5, 5, 5);
                $this->pdf->SetFontSize( 8 );
                $html = $this->export_pagibig_STLRF($company_id, $employee_id, $date_from, $date_to, "SHORT-TERM LOAN REMITTANCE FORM", $page_hidden);        
                $title = "PAGIBIG Premium Contribution";
                break;              
            case '5':
                $html = $this->export_pagibig_to_disk($company_id, $employee_id, $date_from, $date_to, "Pag-Ibig to Disk", $page_hidden);        
                $title = "Pag-Ibig to Disk";
                break;
            case '6':
                $html = $this->export_pagibig_loan_to_disk($company_id, $employee_id, $date_from, $date_to, "Pag-Ibig Loans to Disk", $page_hidden);        
                $title = "Pag-Ibig Loans to Disk";
                break;
        }
        $this->pdf->Output(ucwords(str_replace(" ","_",$title))."_" . date('dmYHis') . '.pdf', 'I');
    }

    function export_pagibig_monthly($company_id, $employee_id, $date_from, $date_to, $title)
    {   

        ini_set("max_execution_time", 7200);
        ini_set("memory_limit", "1024M");

        $company_setting_res = $this->db->query('SELECT * FROM '.$this->db->dbprefix('user_company').' WHERE company_id = "'.$company_id.'"')->row();
        $monthly_detail_qry = "SELECT CONCAT(b.lastname,', ',b.firstname,' ',IF(b.aux = '','',b.aux),' ',IF(b.middleinitial IS NULL,'',b.middleinitial)) AS employee_name,
                                SUM(a.employee) AS pagibig, SUM(a.company) AS pagibig_company, IF(bb.pagibig = '',b.birth_date,bb.pagibig) pagibig_no
                                FROM {$this->db->dbprefix}payroll_period c
                                INNER JOIN {$this->db->dbprefix}employee_contribution a ON c.payroll_period_id = a.payroll_period_id
                                INNER JOIN {$this->db->dbprefix}user b ON a.employee_id = b.employee_id
                                INNER JOIN {$this->db->dbprefix}employee bb ON b.employee_id = bb.employee_id
                                WHERE 1 AND a.transaction_id = '52' AND bb.id_number IS NOT NULL AND a.payroll_date BETWEEN '{$date_from}' AND '{$date_to}'";
        if(!empty($employee_id)){ $monthly_detail_qry .= " AND b.employee_id IN ({$employee_id})"; }
        if(!empty($company_id)){ $monthly_detail_qry .= " AND b.company_id IN ({$company_id})"; }
        $monthly_detail_qry .= "GROUP BY b.employee_id
                                ORDER BY b.lastname, b.firstname, b.middleinitial";
        // dbug($monthly_detail_qry);
        $monthly_detail_res = $this->db->query($monthly_detail_qry);
        $total_no_employees = $monthly_detail_res->num_rows();
        $allowed_count_per_page = 40;
        $page_with = $total_no_employees/$allowed_count_per_page;
        $page_floor = floor($page_with);

        $number_of_page = $page_floor;
        if($page_with > $page_floor)
        {
            $number_of_page = $page_floor + 1;
        }
        $year_set = date("Y",strtotime($date_from));
        $month_set = date("F",strtotime($date_from)); 
        $company = $company_setting_res->company;
        $company_sss_no = $company_setting_res->sss_no;
        $company_address = $company_setting_res->address;
        $company_tin_no = $company_setting_res->vat_registration;
        $company_zip = $company_setting_res->zipcode;
        $company_telephone = $company_setting_res->telephone;

        //company type (private employer, local government, government controlled corp and national government agency)
        $company_type = '1';
        if($total_no_employees != 0)
        {
            $total_last_page_pagibig = 0;
            $total_last_page_pagibig_company = 0;
            $total_last_page_pagibig_total = 0;
            for($i=1;$i<=$number_of_page; $i++)
            {                
                $this->pdf->addPage('P', 'LETTER', true);
                $background = 'uploads/payroll_report/pagibig_m1-1_web.jpg';                
                $this->pdf->SetAutoPageBreak(false, 0);
                $this->pdf->Image($background, 8, 7, 199.5, 264.8, 'JPG', '', '', false, 100, '', false, false, 0, false, 0, false);
                $page_no_certified = $i;

                $m1_type = 'x';
                switch ($company_type) {
                    // case 'private employer':
                    case 1:
                        $this->pdf->SetXY(51, 16);
                        break; 
                    // case 'local government':
                    case 2:
                        $this->pdf->SetXY(51, 21.8);
                        break;
                    // case 'government controlled corp':
                    case 3:
                        $this->pdf->SetXY(92.3, 16);
                        break;
                    // case 'national government agency':
                    case 4:
                        $this->pdf->SetXY(92.3, 21.8);
                        break;
                }            
                $this->pdf->writeHTML($m1_type, true, false, true, false, '');
                $this->pdf->MultiCell(25, 20, $month_set, 0, 'C', false, 0, 154, 28, true, 0, false, false, 0, 'T', true);   
                $this->pdf->MultiCell(17, 20, $year_set, 0, 'C', false, 0, 190, 28, true, 0, false, false, 0, 'T', true);
                if(strlen($company) > 50)
                {
                    if(strlen($company) > 99)
                    {
                        $company = substr($company, 0,99).'...';
                    }
                    $this->pdf->MultiCell(89, 20, $company, 0, 'C', false, 0, 8, 36, true, 0, false, false, 0, 'T', true);   
                }
                else
                {
                    $this->pdf->MultiCell(89, 10, $company, 0, 'C', false, 0, 8, 39, true, 0, false, false, 0, 'T', true);      
                }
                $this->pdf->MultiCell(36.5, 10, $company_sss_no, 0, 'C', false, 0, 118, 39, true, 0, false, false, 0, 'T', true);
                if(strlen($company_address) > 50)
                {
                    if(strlen($company_address) > 99)
                    {
                        $company_address = substr($company_address, 0,85).'...';
                    }
                    $this->pdf->MultiCell(89, 20, $company_address, 0, 'C', false, 0, 8, 46, true, 0, false, false, 0, 'T', true);   
                }
                else
                {
                    $this->pdf->MultiCell(89, 10, $company_address, 0, 'C', false, 0, 8, 49, true, 0, false, false, 0, 'T', true);      
                }                
                $this->pdf->MultiCell(48.5, 10, $company_tin_no, 0, 'C', false, 0, 97, 49, true, 0, false, false, 0, 'T', true);
                $this->pdf->MultiCell(21, 10, $company_zip, 0, 'C', false, 0, 144.5, 49, true, 0, false, false, 0, 'T', true);
                if(strlen($company_telephone) > 25)
                {
                    if(strlen($company_telephone)  > 49)
                    {
                        $company_telephone = substr($company_telephone, 0,49).'...';
                    }
                    $this->pdf->MultiCell(43, 10, $company_telephone, 0, 'C', false, 0, 164.5, 46, true, 0, false, false, 0, 'T', true);
                }
                else
                {
                    $this->pdf->MultiCell(43, 10, $company_telephone, 0, 'C', false, 0, 164.5, 49, true, 0, false, false, 0, 'T', true);
                }

                $monthly_detail_value_qry = "SELECT CONCAT(b.lastname,', ',b.firstname,' ',IF(b.aux = '','',b.aux),' ',IF(b.middleinitial IS NULL,'',b.middleinitial)) AS employee_name,
                                            SUM(a.employee) AS pagibig, SUM(a.company) AS pagibig_company, IF(bb.pagibig = '',b.birth_date,bb.pagibig) pagibig_no
                                            FROM {$this->db->dbprefix}payroll_period c 
                                            INNER JOIN {$this->db->dbprefix}employee_contribution a ON c.payroll_period_id = a.payroll_period_id
                                            INNER JOIN {$this->db->dbprefix}user b ON a.employee_id = b.employee_id
                                            INNER JOIN {$this->db->dbprefix}employee bb ON b.employee_id = bb.employee_id
                                            WHERE 1 AND a.transaction_id = '52' AND bb.id_number IS NOT NULL AND a.payroll_date BETWEEN '{$date_from}' AND '{$date_to}'";
                if(!empty($employee_id)){ $monthly_detail_value_qry .= " AND b.employee_id IN ({$employee_id})"; }
                if(!empty($company_id)){ $monthly_detail_value_qry .= " AND b.company_id IN ({$company_id})"; }
                $monthly_detail_value_qry .= "GROUP BY b.employee_id
                                            ORDER BY b.lastname, b.firstname, b.middleinitial";
                $limit = ($i - 1) * $allowed_count_per_page;
                $monthly_detail_value_qry .= " LIMIT {$limit},{$allowed_count_per_page}";
                // dbug($monthly_detail_value_qry);
                $monthly_detail_value_res = $this->db->query($monthly_detail_value_qry);
                $value_count = 0;
                $line = 64;
                $total_per_page_pagibig = 0;
                $total_per_page_pagibig_company = 0;
                $total_per_page_pagibig_total = 0;
                foreach ($monthly_detail_value_res->result() as $key => $value) 
                {                    
                    $pagibig_no = $value->pagibig_no;
                    $this->pdf->MultiCell(45, 10, $pagibig_no, 0, 'C', false, 0, 8, $line, true, 0, false, false, 0, 'T', true); 
                    $employee_name = $value->employee_name;
                    if(strlen($employee_name) > 46)
                    {
                        $employee_name = substr($employee_name, 0,46).'...';
                    }
                    $this->pdf->MultiCell(77, 10, $employee_name, 0, 'L', false, 0, 59, $line, true, 0, false, false, 0, 'T', true); 
                    // $this->pdf->SetXY(60, $line);
                    // $this->pdf->writeHTML($employee_name, true, false, true, false, '');
                    $pagibig = $value->pagibig;
                    $this->pdf->MultiCell(25, 10, number_format($pagibig, 2, '.', ','), 0, 'R', false, 0, 135, $line, true, 0, false, false, 0, 'T', true); 
                    $pagibig_company = $value->pagibig_company;
                    $this->pdf->MultiCell(24, 10, number_format($pagibig_company, 2, '.', ','), 0, 'R', false, 0, 160, $line, true, 0, false, false, 0, 'T', true); 
                    $pagibig_total = $value->pagibig+$value->pagibig_company;      
                    $this->pdf->MultiCell(24, 10, number_format($pagibig_total, 2, '.', ','), 0, 'R', false, 0, 183, $line, true, 0, false, false, 0, 'T', true); 
                    $line = $line+4.04;
                    $value_count++;
                    $total_per_page_pagibig += $pagibig;
                    $total_per_page_pagibig_company += $pagibig_company;
                    $total_per_page_pagibig_total += $pagibig_total;
                    $total_last_page_pagibig += $pagibig;
                    $total_last_page_pagibig_company += $pagibig_company;
                    $total_last_page_pagibig_total += $pagibig_total;
                }
                $this->pdf->MultiCell(25, 10, $value_count, 0, 'C', false, 0, 28, 227, true, 0, false, false, 0, 'T', true); 
                $this->pdf->MultiCell(23, 10, number_format($total_per_page_pagibig, 2, '.', ','), 0, 'R', false, 0, 137, 227, true, 0, false, false, 0, 'T', true); 
                $this->pdf->MultiCell(22, 10, number_format($total_per_page_pagibig_company, 2, '.', ','), 0, 'R', false, 0, 162, 227, true, 0, false, false, 0, 'T', true); 
                $this->pdf->MultiCell(22, 10, number_format($total_per_page_pagibig_total, 2, '.', ','), 0, 'R', false, 0, 185, 227, true, 0, false, false, 0, 'T', true); 

                if($i == $number_of_page)
                {
                    $this->pdf->MultiCell(25, 10, $total_no_employees, 0, 'C', false, 0, 71.5, 227, true, 0, false, false, 0, 'T', true); 
                    $this->pdf->MultiCell(23, 10, number_format($total_last_page_pagibig, 2, '.', ','), 0, 'R', false, 0, 137, 236, true, 0, false, false, 0, 'T', true); 
                    $this->pdf->MultiCell(22, 10, number_format($total_last_page_pagibig_company, 2, '.', ','), 0, 'R', false, 0, 162, 236, true, 0, false, false, 0, 'T', true); 
                    $this->pdf->MultiCell(22, 10, number_format($total_last_page_pagibig_total, 2, '.', ','), 0, 'R', false, 0, 185, 236, true, 0, false, false, 0, 'T', true);                         
                }
                $this->pdf->MultiCell(13.5, 10, $i, 0, 'C', false, 0, 178, 262.5, true, 0, false, false, 0, 'T', true); 
                $this->pdf->MultiCell(16.5, 10, $number_of_page, 0, 'C', false, 0, 191, 262.5, true, 0, false, false, 0, 'T', true);
            }   
        }
        else
        {
            $this->pdf->addPage('P', 'LETTER', true);
            $this->pdf->SetXY(100, 20);
            $this->pdf->writeHTML("No Record Found!", true, false, true, false, '');
        }
    }

    function export_pagibig_MCRF($company_id, $employee_id, $date_from, $date_to, $title)
    {
        $company_setting_res = $this->db->query('SELECT * FROM '.$this->db->dbprefix('user_company').' WHERE company_id = "'.$company_id.'"')->row();
        $monthly_detail_qry = "SELECT";
        $monthly_detail_qry .= " CONCAT(b.lastname,', ',b.firstname,' ',IF(b.aux = '','',b.aux),' ',IF(b.middleinitial IS NULL,'',b.middleinitial)) AS employee_name,";
        $monthly_detail_qry .= " SUM(a.employee) AS pagibig,";
        $monthly_detail_qry .= " SUM(a.company) AS pagibig_company,";
        $monthly_detail_qry .= " IF(bb.pagibig = '',b.birth_date,bb.pagibig) pagibig_no";
        $monthly_detail_qry .= " FROM {$this->db->dbprefix}payroll_period c";        
        $monthly_detail_qry .= " LEFT JOIN {$this->db->dbprefix}employee_contribution a";  
        $monthly_detail_qry .= " ON c.payroll_period_id = a.payroll_period_id";
        $monthly_detail_qry .= " LEFT JOIN {$this->db->dbprefix}user b";
        $monthly_detail_qry .= " ON a.employee_id = b.employee_id";
        $monthly_detail_qry .= " LEFT JOIN {$this->db->dbprefix}payroll_transaction aa";
        $monthly_detail_qry .= " ON aa.transaction_id = a.transaction_id";
        $monthly_detail_qry .= " LEFT JOIN {$this->db->dbprefix}payroll_transaction_class ab";
        $monthly_detail_qry .= " ON ab.transaction_class_id = aa.transaction_class_id";
        $monthly_detail_qry .= " LEFT JOIN {$this->db->dbprefix}employee bb";
        $monthly_detail_qry .= " ON b.employee_id = bb.employee_id";
        $monthly_detail_qry .= " WHERE 1";
        $monthly_detail_qry .= " AND ab.transaction_class_id = '20'";
        $monthly_detail_qry .= " AND bb.id_number IS NOT NULL";
        $monthly_detail_qry .= " AND a.payroll_date BETWEEN '{$date_from}' AND '{$date_to}'";
        if(!empty($employee_id)){ $monthly_detail_qry .= " AND b.employee_id IN ({$employee_id})"; }
        if(!empty($company_id)){ $monthly_detail_qry .= " AND b.company_id IN ({$company_id})"; }
        $monthly_detail_qry .= " GROUP BY b.employee_id";
        $monthly_detail_qry .= " ORDER BY b.lastname, b.firstname, b.middleinitial";
        // dbug($monthly_detail_qry);
        $monthly_detail_res = $this->db->query($monthly_detail_qry);
        $total_no_employees = $monthly_detail_res->num_rows();
        $allowed_count_per_page = 30;
        $page_with = $total_no_employees/$allowed_count_per_page;
        $page_floor = floor($page_with);

        $number_of_page = $page_floor;
        if($page_with > $page_floor)
        {
            $number_of_page = $page_floor + 1;
        }
        $year_set = date("Y",strtotime($date_from));
        $month_set = date("F",strtotime($date_from)); 
        $company = $company_setting_res->company;
        $company_sss_no = $company_setting_res->sss_no;
        $company_address = $company_setting_res->address;
        $company_tin_no = $company_setting_res->vat_registration;
        $company_zip = $company_setting_res->zipcode;
        $company_telephone = $company_setting_res->telephone;
        $company_pagibig_no = $company_setting_res->pagibig_no; 

        if($total_no_employees != 0)
        {
            $total_last_page_pagibig = 0;
            $total_last_page_pagibig_company = 0;
            $total_last_page_pagibig_total = 0;
            for($i=1;$i<=$number_of_page; $i++)
            {                
                $this->pdf->addPage('P', 'LETTER', true);
                $this->pdf->SetFontSize( 10 );
                $background = 'uploads/payroll_report/mcrf_001.jpg';
                $this->pdf->SetAutoPageBreak(false, 0);
                $this->pdf->Image($background, 6.5, 6, 203, 269.5, 'JPG', '', '', false, 100, '', false, false, 0, false, 0, false);
                $page_no_certified = $i;

                // MultiCell($w, $h, $txt, $border=0, $align='J', $fill=0, $ln=1, $x='', $y='', $reseth=true, $stretch=0, $ishtml=false, $autopadding=true, $maxh=0)

                $this->pdf->MultiCell(30, 10, $company_sss_no, 0, 'L', false, 0, 145, 27, true, 0, false, false, 0, 'T', true);
                $this->pdf->MultiCell(102, 20, $company, 0, 'L', false, 0, 15, 45, true, 0, false, false, 0, 'T', true);   
                $this->pdf->MultiCell(200, 20, $company_address, 0, 'L', false, 0, 15, 57, true, 0, false, false, 0, 'T', true);   

                $monthly_detail_value_qry = "SELECT b.lastname, b.firstname, IF(b.middlename IS NULL,b.middleinitial,b.middlename) AS middlename, b.aux,
                                            SUM(a.employee) AS pagibig, SUM(a.company) AS pagibig_company,
                                            IF(bb.pagibig = '',b.birth_date,bb.pagibig) pagibig_no
                                            FROM {$this->db->dbprefix}payroll_period c
                                            LEFT JOIN {$this->db->dbprefix}employee_contribution a ON c.payroll_period_id = a.payroll_period_id
                                            LEFT JOIN {$this->db->dbprefix}user b ON a.employee_id = b.employee_id
                                            LEFT JOIN {$this->db->dbprefix}payroll_transaction aa ON aa.transaction_id = a.transaction_id
                                            LEFT JOIN {$this->db->dbprefix}payroll_transaction_class ab ON ab.transaction_class_id = aa.transaction_class_id
                                            LEFT JOIN {$this->db->dbprefix}employee bb ON b.employee_id = bb.employee_id
                                            WHERE 1 AND a.transaction_id = '52' AND bb.id_number IS NOT NULL
                                            AND a.payroll_date BETWEEN '{$date_from}' AND '{$date_to}'";
                if(!empty($employee_id)){ $monthly_detail_value_qry .= " AND b.employee_id IN ({$employee_id})"; }
                if(!empty($company_id)){ $monthly_detail_value_qry .= " AND b.company_id IN ({$company_id})"; }
                $monthly_detail_value_qry .= " GROUP BY b.employee_id
                                            ORDER BY b.lastname, b.firstname, b.middleinitial";
                $limit = ($i - 1) * $allowed_count_per_page;
                $monthly_detail_value_qry .= " LIMIT {$limit},{$allowed_count_per_page}";
                // dbug($monthly_detail_value_qry);
                $monthly_detail_value_res = $this->db->query($monthly_detail_value_qry);
                $value_count = 0;
                $line = 84.5;
                $total_per_page_pagibig = 0;
                $total_per_page_pagibig_company = 0;
                $total_per_page_pagibig_total = 0;
                foreach ($monthly_detail_value_res->result() as $key => $value) 
                {            
                
                    $this->pdf->SetFontSize( 6 );        
                    $pagibig_no = str_replace('-','',$value->pagibig_no);
                    $this->pdf->MultiCell(26, 10, $pagibig_no, 0, 'C', false, 0, 7.5, $line, true, 0, false, false, 0, 'T', true); 
                    $this->pdf->MultiCell(18, 10, substr($value->lastname, 0, 18), 0, 'L', false, 0, 57, $line, true, 0, false, false, 0, 'T', true);                     
                    $this->pdf->MultiCell(18, 10, substr($value->firstname, 0, 18), 0, 'L', false, 0, 79.5, $line, true, 0, false, false, 0, 'T', true);                     
                    $this->pdf->MultiCell(18, 10, substr($value->aux, 0, 6), 0, 'L', false, 0, 101, $line, true, 0, false, false, 0, 'T', true);                     
                    $this->pdf->MultiCell(18, 10, substr($value->middlename, 0, 12), 0, 'L', false, 0, 112.5, $line, true, 0, false, false, 0, 'T', true);                     

                    $this->pdf->MultiCell(15, 10, date("Ym",strtotime($date_from)), 0, 'C', false, 0, 127, $line, true, 0, false, false, 0, 'T', true);                     

                    $pagibig = $value->pagibig;
                    $this->pdf->MultiCell(20.5, 10, number_format($pagibig, 2, '.', ','), 0, 'R', false, 0, 150.4, $line, true, 0, false, false, 0, 'T', true); 
                    $pagibig_company = $value->pagibig_company;
                    $this->pdf->MultiCell(20, 10, number_format($pagibig_company, 2, '.', ','), 0, 'R', false, 0, 161.5, $line, true, 0, false, false, 0, 'T', true); 
                    $pagibig_total = $value->pagibig+$value->pagibig_company;      
                    $this->pdf->MultiCell(21, 10, number_format($pagibig_total, 2, '.', ','), 0, 'R', false, 0, 171, $line, true, 0, false, false, 0, 'T', true); 
                    if($value_count = 5 ){
                        $line = $line + 4.5;
                    }
                    else{
                        $line = $line + 4.3;   
                    }
                    $value_count++;
                    $total_per_page_pagibig += $pagibig;
                    $total_per_page_pagibig_company += $pagibig_company;
                    $total_per_page_pagibig_total += $pagibig_total;
                    $total_last_page_pagibig += $pagibig;
                    $total_last_page_pagibig_company += $pagibig_company;
                    $total_last_page_pagibig_total += $pagibig_total;
                }
                // $this->pdf->MultiCell(24, 10, $value_count, 0, 'C', false, 0, 25, 243, true, 0, false, false, 0, 'T', true); 
                $this->pdf->MultiCell(20.5, 10, number_format($total_per_page_pagibig, 2, '.', ','), 0, 'R', false, 0, 150.4, 219.5, true, 0, false, false, 0, 'T', true); 
                $this->pdf->MultiCell(20, 10, number_format($total_per_page_pagibig_company, 2, '.', ','), 0, 'R', false, 0, 161.5, 219.5, true, 0, false, false, 0, 'T', true); 
                $this->pdf->MultiCell(21, 10, number_format($total_per_page_pagibig_total, 2, '.', ','), 0, 'R', false, 0, 175.5, 219.5, true, 0, false, false, 0, 'T', true); 

                if($i == $number_of_page)
                {
                    // $this->pdf->MultiCell(25, 10, $total_no_employees, 0, 'C', false, 0, 73, 243, true, 0, false, false, 0, 'T', true); 
                    $this->pdf->MultiCell(20.5, 10, number_format($total_last_page_pagibig, 2, '.', ','), 0, 'R', false, 0, 150.4, 224.5, true, 0, false, false, 0, 'T', true); 
                    $this->pdf->MultiCell(20, 10, number_format($total_last_page_pagibig_company, 2, '.', ','), 0, 'R', false, 0, 161.5, 224.5, true, 0, false, false, 0, 'T', true); 
                    $this->pdf->MultiCell(21, 10, number_format($total_last_page_pagibig_total, 2, '.', ','), 0, 'R', false, 0, 175.5, 224.5, true, 0, false, false, 0, 'T', true);                         
                }
                $this->pdf->SetFontSize(9);
                $this->pdf->MultiCell(95, 10, 'MARCOS DENNIS M. MENDOZA', 0, 'C', false, 0, 5, 250, true, 0, false, false, 0, 'T', true); 
                $this->pdf->MultiCell(50, 10, 'PAYROLL SUPERVISOR', 0, 'C', false, 0, 103, 250, true, 0, false, false, 0, 'T', true);
            }   
        }
        else
        {
            $this->pdf->addPage('P', 'LETTER', true);
            $this->pdf->SetXY(100, 20);
            $this->pdf->writeHTML("No Record Found!", true, false, true, false, '');
        }
    }

    function export_pagibig_quarterly($company_id, $employee_id, $date_from, $date_to, $title)
    { 

        // $this->pdf->addPage('P', 'A4', true);
        // $background = 'uploads/payroll_report/pagibig_quarterly-1_web.jpg';
        // $this->pdf->SetAutoPageBreak(false, 0);
        // $this->pdf->Image($background, 4, 19, 202.6, 260.4, 'JPG', '', '', false, 100, '', false, false, 0, false, $page_hidden, false);

        $company_setting_res = $this->db->query('SELECT * FROM '.$this->db->dbprefix('user_company').' WHERE company_id = "'.$company_id.'"')->row();

        $month_num = date("m",strtotime($date_from));
        $array_1st = array('01','04','07','10');
        $array_2nd = array('02','05','08','11');
        $array_3rd = array('03','06','09','12');
        $position_month = '';
        if(in_array($month_num, $array_1st))
        {
            $position_month = 1;
        }
        elseif(in_array($month_num, $array_2nd))
        {
            $position_month = 2;
        }
        elseif(in_array($month_num, $array_3rd))
        {
            $position_month = 3;
        }
        $monthly_detail_qry = "SELECT";
        $monthly_detail_qry .= " CONCAT(b.lastname,', ',b.firstname,' ',IF(b.aux = '','',b.aux),' ',IF(b.middleinitial IS NULL,'',b.middleinitial)) AS employee_name,";
        $monthly_detail_qry .= " SUM(a.employee) AS pagibig,";
        $monthly_detail_qry .= " SUM(a.company) AS pagibig_company,";
        $monthly_detail_qry .= " SUM(a.employee) + SUM(a.company) AS pagibig_total,";
        $monthly_detail_qry .= " IF(bb.pagibig = '',b.birth_date,bb.pagibig) pagibig_no";
        $monthly_detail_qry .= " FROM {$this->db->dbprefix}payroll_period c";
        $monthly_detail_qry .= " LEFT JOIN {$this->db->dbprefix}employee_contribution a";  
        $monthly_detail_qry .= " ON c.payroll_period_id = a.payroll_period_id";
        $monthly_detail_qry .= " LEFT JOIN {$this->db->dbprefix}user b";
        $monthly_detail_qry .= " ON a.employee_id = b.employee_id";
        $monthly_detail_qry .= " LEFT JOIN {$this->db->dbprefix}payroll_transaction aa";
        $monthly_detail_qry .= " ON aa.transaction_id = a.transaction_id";
        $monthly_detail_qry .= " LEFT JOIN {$this->db->dbprefix}payroll_transaction_class ab";
        $monthly_detail_qry .= " ON ab.transaction_class_id = aa.transaction_class_id";
        $monthly_detail_qry .= " LEFT JOIN {$this->db->dbprefix}employee bb";
        $monthly_detail_qry .= " ON b.employee_id = bb.employee_id";
        $monthly_detail_qry .= " WHERE 1";
        $monthly_detail_qry .= " AND ab.transaction_class_id = '20'";
        $monthly_detail_qry .= " AND bb.id_number IS NOT NULL";
        $monthly_detail_qry .= " AND a.payroll_date BETWEEN '{$date_from}' AND '{$date_to}'";
        if(!empty($employee_id)){ $monthly_detail_qry .= " AND b.employee_id IN ({$employee_id})"; }
        if(!empty($company_id)){ $monthly_detail_qry .= " AND b.company_id IN ({$company_id})"; }
        $monthly_detail_qry .= " GROUP BY b.employee_id";
        $monthly_detail_qry .= " ORDER BY b.lastname, b.firstname, b.middleinitial";
        // dbug($monthly_detail_qry);
        $monthly_detail_res = $this->db->query($monthly_detail_qry);
        $cnt = 1;
        $total_no_employees = $monthly_detail_res->num_rows();
        $monthly_detail_res = $this->db->query($monthly_detail_qry);
        $total_no_employees = $monthly_detail_res->num_rows();
        $allowed_count_per_page = 62;
        $page_with = $total_no_employees/$allowed_count_per_page;
        $page_floor = floor($page_with);

        $number_of_page = $page_floor;
        if($page_with > $page_floor)
        {
            $number_of_page = $page_floor + 1;
        }
        
        if($total_no_employees != 0)
        {
            $t_pag_e_g = 0;
            $t_pag_c_g = 0;
            $t_pag_t_g = 0;
            for($i=1;$i<=$number_of_page; $i++)
            {                   
                $xcel_hed = '';
                $this->pdf->addPage('P', 'LETTER', true);
                $xcel_hed .= '<table style="width:100%;">';
                // $xcel_hed .= '<table style="width:100%;" border="1">';
                $xcel_hed .= '<tr>';
                $xcel_hed .= '<td style="width:5%;"></td>'; 
                $xcel_hed .= '<td style="width:25%;"></td>'; 
                $xcel_hed .= '<td style="width:13%;"></td>'; 
                $xcel_hed .= '<td style="width:8%;"></td>'; 
                $xcel_hed .= '<td style="width:7%;"></td>'; 
                $xcel_hed .= '<td style="width:8%;"></td>'; 
                $xcel_hed .= '<td style="width:7%;"></td>'; 
                $xcel_hed .= '<td style="width:8%;"></td>'; 
                $xcel_hed .= '<td style="width:7%;"></td>'; 
                $xcel_hed .= '<td style="width:12%;"></td>'; 
                $xcel_hed .= '</tr>';
                $xcel_hed .= '<tr><td colspan="9" style="font-size:30px;"></td><td colspan="1" style="text-align:right;font-size:50px;">Page '.$i.' of '.$number_of_page.'</td></tr>';
                $xcel_hed .= '<tr>';
                $xcel_hed .= '<td colspan="10" style="text-align:center;font-size:100px;">';
                $xcel_hed .=  $title.'<br>'.date("F",strtotime($date_from)).' '.date("Y",strtotime($date_from)).'<br>'.$company_setting_res->company;
                $xcel_hed .= '</td>';
                $xcel_hed .= '</tr>';
                $xcel_hed .= '<tr><td colspan="9" style="font-size:50px;"></td><td colspan="1" style="text-align:left;font-size:50px;">Rundate : '.date("F d, Y").'</td></tr>';
                $xcel_hed .= '<tr><td colspan="9" style="font-size:50px;"></td><td colspan="1" style="text-align:left;font-size:50px;">Runtime : '.date("h:i A").'</td></tr>';
                $xcel_hed .= '<tr><td></td></tr>';        
                $xcel_hed .= '<tr>';
                $xcel_hed .= '<td colspan="3"></td>';
                $xcel_hed .= '<td colspan="6" style="text-align:center;">PAG-IBIG CONTRIBUTION</td>';
                $xcel_hed .= '<td></td>';
                $xcel_hed .= '</tr>';
                $xcel_hed .= '<tr>';
                $xcel_hed .= '<td colspan="2" rowspan="2" style="text-align:center;background-color:#CCCCCC;background-color:#CCCCCC;border-top:1px solid black;border-left:1px solid black;border-right:1px solid black;border-bottom:1px solid black;">';
                $xcel_hed .= 'Name of Employee';
                $xcel_hed .= '</td>';
                $xcel_hed .= '<td  rowspan="2" style="text-align:center;background-color:#CCCCCC;background-color:#CCCCCC;border-top:1px solid black;border-left:1px solid black;border-right:1px solid black;border-bottom:1px solid black;">';
                $xcel_hed .= 'PAG-IBIG Number';
                $xcel_hed .= '</td>';
                $xcel_hed .= '<td colspan="2" style="text-align:center;background-color:#CCCCCC;border-top:1px solid black;border-left:1px solid black;">';
                $xcel_hed .= '1st';   
                $xcel_hed .= '</td>';
                $xcel_hed .= '<td colspan="2" style="text-align:center;background-color:#CCCCCC;border-top:1px solid black;border-left:1px solid black;">';
                $xcel_hed .= '2nd';        
                $xcel_hed .= '</td>';
                $xcel_hed .= '<td colspan="2" style="text-align:center;background-color:#CCCCCC;border-top:1px solid black;border-left:1px solid black;border-right:1px solid black;">';
                $xcel_hed .= '3rd';
                $xcel_hed .= '</td>';
                $xcel_hed .= '<td rowspan="2" style="text-align:center;background-color:#CCCCCC;background-color:#CCCCCC;border-top:1px solid black;border-left:1px solid black;border-right:1px solid black;border-bottom:1px solid black;">';
                $xcel_hed .= 'Total';
                $xcel_hed .= '</td>';
                $xcel_hed .= '</tr>';
                $xcel_hed .= '<tr>';
                //$xcel_hed .= '<td colspan="3" style="text-align:center;background-color:#CCCCCC;"></td>';
                $xcel_hed .= '<td style="text-align:center;background-color:#CCCCCC;border-top:1px solid black;border-bottom:1px solid black;border-left:1px solid black;">EE</td>';
                $xcel_hed .= '<td style="text-align:center;background-color:#CCCCCC;border-top:1px solid black;border-bottom:1px solid black;border-left:1px solid black;">ER</td>';
                $xcel_hed .= '<td style="text-align:center;background-color:#CCCCCC;border-top:1px solid black;border-bottom:1px solid black;border-left:1px solid black;">EE</td>';
                $xcel_hed .= '<td style="text-align:center;background-color:#CCCCCC;border-top:1px solid black;border-bottom:1px solid black;border-left:1px solid black;">ER</td>';
                $xcel_hed .= '<td style="text-align:center;background-color:#CCCCCC;border-top:1px solid black;border-bottom:1px solid black;border-left:1px solid black;">EE</td>';
                $xcel_hed .= '<td style="text-align:center;background-color:#CCCCCC;border-top:1px solid black;border-bottom:1px solid black;border-left:1px solid black;border-right:1px solid black;">ER</td>';
                // $xcel_hed .= '<td style="text-align:center;background-color:#CCCCCC;border-left:1px solid black;"></td>';
                $xcel_hed .= '</tr>';             
                $monthly_detail_value_qry = "SELECT";
                $monthly_detail_value_qry .= " CONCAT(b.lastname,', ',b.firstname,' ',IF(b.aux = '','',b.aux),' ',IF(b.middleinitial IS NULL,'',b.middleinitial)) AS employee_name,";
                $monthly_detail_value_qry .= " SUM(a.employee) AS pagibig,";
                $monthly_detail_value_qry .= " SUM(a.company) AS pagibig_company,";
                $monthly_detail_value_qry .= " SUM(a.employee) + SUM(a.company) AS pagibig_total,";
                $monthly_detail_value_qry .= " IF(bb.pagibig = '',b.birth_date,bb.pagibig) pagibig_no";
                $monthly_detail_value_qry .= " FROM {$this->db->dbprefix}payroll_period c";
                $monthly_detail_value_qry .= " LEFT JOIN {$this->db->dbprefix}employee_contribution a";  
                $monthly_detail_value_qry .= " ON c.payroll_period_id = a.payroll_period_id";
                $monthly_detail_value_qry .= " LEFT JOIN {$this->db->dbprefix}user b";
                $monthly_detail_value_qry .= " ON a.employee_id = b.employee_id";
                $monthly_detail_value_qry .= " LEFT JOIN {$this->db->dbprefix}payroll_transaction aa";
                $monthly_detail_value_qry .= " ON aa.transaction_id = a.transaction_id";
                $monthly_detail_value_qry .= " LEFT JOIN {$this->db->dbprefix}payroll_transaction_class ab";
                $monthly_detail_value_qry .= " ON ab.transaction_class_id = aa.transaction_class_id";
                $monthly_detail_value_qry .= " LEFT JOIN {$this->db->dbprefix}employee bb";
                $monthly_detail_value_qry .= " ON b.employee_id = bb.employee_id";
                $monthly_detail_value_qry .= " WHERE 1";
                $monthly_detail_value_qry .= " AND ab.transaction_class_id = '20'";
                $monthly_detail_value_qry .= " AND bb.id_number IS NOT NULL";
                $monthly_detail_value_qry .= " AND a.payroll_date BETWEEN '{$date_from}' AND '{$date_to}'";
                if(!empty($employee_id)){ $monthly_detail_value_qry .= " AND b.employee_id IN ({$employee_id})"; }
                if(!empty($company_id)){ $monthly_detail_value_qry .= " AND b.company_id IN ({$company_id})"; }
                $monthly_detail_value_qry .= " GROUP BY b.employee_id";
                $monthly_detail_value_qry .= " ORDER BY b.lastname, b.firstname, b.middleinitial";
                $limit = ($i - 1) * $allowed_count_per_page;
                $monthly_detail_value_qry .= " LIMIT {$limit},{$allowed_count_per_page}";
                // dbug($monthly_detail_value_qry);
                $monthly_detail_value_res = $this->db->query($monthly_detail_value_qry);
                $t_pag_e = 0;
                $t_pag_c = 0;
                $t_pag_t = 0;
                $count = 0;
                foreach ($monthly_detail_value_res->result() as $key => $value) 
                {
                    $employee_name = $value->employee_name;
                    if(strlen($employee_name) > 27)
                    {
                        $employee_name = substr($employee_name, 0,27).'...';
                    }
                    $xcel_hed .= '<tr>';
                    $xcel_hed .= '<td style="text-align:left;">'.$cnt++.'.</td>'; 
                    $xcel_hed .= '<td style="text-align:left;">'.$employee_name.'</td>'; 
                    $xcel_hed .= '<td style="text-align:left;">'.$value->pagibig_no.'</td>'; 
                    switch ($position_month) 
                    {
                        case '1':
                            $xcel_hed .= '<td style="text-align:right;">'.number_format($value->pagibig,2,'.',',').'</td>'; 
                            $xcel_hed .= '<td style="text-align:right;">'.number_format($value->pagibig_company,2,'.',',').'</td>';     
                            $xcel_hed .= '<td colspan="4"></td>'; 
                            break;
                        case '2':
                            $xcel_hed .= '<td colspan="2"></td>'; 
                            $xcel_hed .= '<td style="text-align:right;">'.number_format($value->pagibig,2,'.',',').'</td>'; 
                            $xcel_hed .= '<td style="text-align:right;">'.number_format($value->pagibig_company,2,'.',',').'</td>';     
                            $xcel_hed .= '<td colspan="2"></td>'; 
                            break;
                        case '3':
                            $xcel_hed .= '<td colspan="4"></td>'; 
                            $xcel_hed .= '<td style="text-align:right;">'.number_format($value->pagibig,2,'.',',').'</td>'; 
                            $xcel_hed .= '<td style="text-align:right;">'.number_format($value->pagibig_company,2,'.',',').'</td>';     
                            break;
                    }
                    $xcel_hed .= '<td style="text-align:right;">'.number_format($value->pagibig_total,2,'.',',').'</td>'; 
                    $xcel_hed .= '</tr>';
                    $t_pag_e += $value->pagibig;
                    $t_pag_c += $value->pagibig_company;
                    $t_pag_t += $value->pagibig_total;
                    $t_pag_e_g += $value->pagibig;
                    $t_pag_c_g += $value->pagibig_company;
                    $t_pag_t_g += $value->pagibig_total;
                    $count++;
                } 
                if($count != $allowed_count_per_page)
                {
                    for ($space=1; $space <= ($allowed_count_per_page - $count); $space++) 
                    {
                        $xcel_hed .= '<tr>';
                        $xcel_hed .= '<td>';
                        $xcel_hed .= '</td>';
                        $xcel_hed .= '</tr>';
                    }   
                }
                $xcel_hed .= '<tr>';
                $xcel_hed .= '<td colspan="3" style="text-align:left;background-color:#DDDDDD;"><strong>Page Total</strong></td>';                
                switch ($position_month) 
                {
                    case '1':
                        $xcel_hed .= '<td style="text-align:right;background-color:#DDDDDD;font-size:80px;">'.number_format($t_pag_e,2,'.',',').'</td>';       
                        $xcel_hed .= '<td style="text-align:right;background-color:#DDDDDD;font-size:80px;">'.number_format($t_pag_c,2,'.',',').'</td>';       
                        $xcel_hed .= '<td colspan="4" style="background-color:#DDDDDD;font-size:80px;"></td>'; 
                        break;
                    case '2':
                        $xcel_hed .= '<td colspan="2" style="background-color:#DDDDDD;"></td>'; 
                        $xcel_hed .= '<td style="text-align:right;background-color:#DDDDDD;font-size:80px;">'.number_format($t_pag_e,2,'.',',').'</td>';       
                        $xcel_hed .= '<td style="text-align:right;background-color:#DDDDDD;font-size:80px;">'.number_format($t_pag_c,2,'.',',').'</td>';       
                        $xcel_hed .= '<td colspan="2" style="background-color:#DDDDDD;font-size:80px;"></td>'; 
                        break;
                    case '3':                    
                        $xcel_hed .= '<td colspan="4" style="background-color:#DDDDDD;font-size:80px;"></td>'; 
                        $xcel_hed .= '<td style="text-align:right;background-color:#DDDDDD;font-size:80px;">'.number_format($t_pag_e,2,'.',',').'</td>';       
                        $xcel_hed .= '<td style="text-align:right;background-color:#DDDDDD;font-size:80px;">'.number_format($t_pag_c,2,'.',',').'</td>';       
                        break;
                }
                $xcel_hed .= '<td style="text-align:right;background-color:#DDDDDD;font-size:80px;">'.number_format($t_pag_t,2,'.',',').'</td>'; 
                $xcel_hed .= '</tr>';            
                if($i == $number_of_page)
                {
                    $xcel_hed .= '<tr>';
                    $xcel_hed .= '<td colspan="3" style="text-align:left;background-color:#DDDDDD;"><strong>Grand Total</strong></td>';                
                    switch ($position_month) 
                    {
                        case '1':
                            $xcel_hed .= '<td style="text-align:right;background-color:#DDDDDD;font-size:80px;">'.number_format($t_pag_e_g,2,'.',',').'</td>';       
                            $xcel_hed .= '<td style="text-align:right;background-color:#DDDDDD;font-size:80px;">'.number_format($t_pag_c_g,2,'.',',').'</td>';       
                            $xcel_hed .= '<td colspan="4" style="background-color:#DDDDDD;font-size:80px;"></td>'; 
                            break;
                        case '2':
                            $xcel_hed .= '<td colspan="2" style="background-color:#DDDDDD;font-size:80px;"></td>'; 
                            $xcel_hed .= '<td style="text-align:right;background-color:#DDDDDD;font-size:80px;">'.number_format($t_pag_e_g,2,'.',',').'</td>';       
                            $xcel_hed .= '<td style="text-align:right;background-color:#DDDDDD;font-size:80px;">'.number_format($t_pag_c_g,2,'.',',').'</td>';       
                            $xcel_hed .= '<td colspan="2" style="background-color:#DDDDDD;font-size:80px;"></td>'; 
                            break;
                        case '3':                    
                            $xcel_hed .= '<td colspan="4" style="background-color:#DDDDDD;font-size:80px;"></td>'; 
                            $xcel_hed .= '<td style="text-align:right;background-color:#DDDDDD;font-size:80px;">'.number_format($t_pag_e_g,2,'.',',').'</td>';       
                            $xcel_hed .= '<td style="text-align:right;background-color:#DDDDDD;font-size:80px;">'.number_format($t_pag_c_g,2,'.',',').'</td>';       
                            break;
                    }
                    $xcel_hed .= '<td style="text-align:right;background-color:#DDDDDD;font-size:80px;">'.number_format($t_pag_t_g,2,'.',',').'</td>'; 
                    $xcel_hed .= '</tr>';
                }
                $xcel_hed .= '</table>';                
                $this->pdf->writeHTML($xcel_hed, true, false, true, false, '');
            }
        }
        else
        {
            $this->pdf->addPage('P', 'LETTER', true);
            $this->pdf->SetXY(100, 20);
            $this->pdf->writeHTML("No Record Found!", true, false, true, false, '');
        }
    }

    function export_pagibig_remittance($company_id, $employee_id, $date_from, $date_to, $title)
    { 
        if(!empty($employee_id)){
            $employee_id = 'AND employee_id IN ('.$employee_id.')';
        }
        
        $employee = $this->db->query("SELECT employee_id FROM {$this->db->dbprefix}user WHERE company_id = $company_id $employee_id")->result();

        $current_date = getdate(date("U"));
        
        foreach ($employee as $key => $value) {

        $contribution_qry = "SELECT payroll_date, (sum(employee) + sum(company) ) as total_contribution
                            FROM {$this->db->dbprefix}employee_contribution
                            WHERE employee_id = ({$value->employee_id}) AND payroll_date between '{$date_from}' AND '{$date_to}' AND transaction_id = 52
                            GROUP BY MONTH(payroll_date),YEAR(payroll_date)
                            ORDER BY employee_id,transaction_id, payroll_date";
        $contribution_res = $this->db->query($contribution_qry);
        $contribution_count = $contribution_res->num_rows();
        
        if ($contribution_count != 0)
        {
            $date_from_n = date('F, Y', strtotime($date_from));
            $date_to_n = date('F, Y', strtotime($date_to));

            $user_dtl_res = $this->db->query("SELECT *,b.sss as sss_id, b.pagibig as pagibig_id FROM {$this->db->dbprefix}user a 
                                            LEFT JOIN {$this->db->dbprefix}employee b ON  b.employee_id = a.employee_id 
                                            LEFT JOIN {$this->db->dbprefix}user_company_department c ON a.department_id = c.department_id 
                                            LEFT JOIN {$this->db->dbprefix}user_position d ON a.position_id = d.position_id
                                            WHERE a.employee_id = $value->employee_id")->row();
            $salutation = $user_dtl_res->salutation;
            $name = $user_dtl_res->lastname.', '.$user_dtl_res->firstname.' '.($user_dtl_res->aux == ''?'':$user_dtl_res->aux).' '.$user_dtl_res->middleinitial;
            $emp_sss_id = $user_dtl_res->sss_id;
            if(!empty($user_dtl_res->pagibig_id)){
                $contri_for = 'Pag-Ibig';
                $gov_id = $user_dtl_res->pagibig_id;
            }
            else{
                $contri_for = 'SSS';
                $gov_id = $user_dtl_res->sss_id;
            }
                $xcel_cont = '
                        <div></div>
                        <div>
                            <table>
                                <tr>
                                    <td colspan="100%" style="text-align:right;">'."$current_date[weekday], $current_date[month] $current_date[mday], $current_date[year]".'
                                    </td>
                                </tr>
                            </table>
                            </div><div>
                        </div>
                            <table>
                                <tr>
                                    <td width="100%" style="text-align:center; font-size:16;">
                                            <b>C&nbsp;E&nbsp;R&nbsp;T&nbsp;I&nbsp;F&nbsp;I&nbsp;C&nbsp;A&nbsp;T&nbsp;I&nbsp;O&nbsp;N</b>
                                    </td>
                                </tr>
                                <tr>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td width="10%">&nbsp;</td>
                                    <td width="90%" style="text-align:left;">
                                        This is to certify that Mr/Ms. '.$name.' with '.$contri_for.' Number
                                    </td>
                                </tr>
                                <tr>
                                    <td width="100%" style="text-align:left; ">'.$gov_id.' had contributed the <b><u>Pag-Ibig Premium</u></b> to FIRST BALFOUR, INC covering below Months of Contributions.
                                    </td>
                                </tr>
                            </table>';                    
                    
                    $xcel_cont .= '<div>
                            <table >
                                <tr>
                                    <td colspan="15"></td>
                                    <td colspan="20%" style="text-align:left; "><u>MONTH</u></td>
                                    <td colspan="30%" style="text-align:center; "><u>EE/ER/EC Pag-Ibig Contribution</u></td>
                                    <td colspan="20%" style="text-align:right; "><u>YEAR</u></td>
                                    <td colspan="15%"></td>
                                </tr>
                            </table>
                            <table>';
                    
                    foreach($contribution_res->result() as $cont_key=>$cont_value) 
                    {
                        $month = date('F',strtotime($cont_value->payroll_date));
                        $year = date('Y',strtotime($cont_value->payroll_date));
                        $amount = $cont_value->total_contribution;
                        
                        $xcel_cont .='
                                
                                <tr>
                                    <td colspan="15%"></td>
                                    <td colspan="20%" style="text-align:left; ">'.$month.' </td>
                                    <td colspan="20%" style="text-align:right; ">'.($amount != "" ? number_format($amount,2,'.',',') : "0.00").'</td>
                                    <td colspan="10%">&nbsp;</td>   
                                    <td colspan="20%" style="text-align:right; ">'.$year.'</td>
                                    <td colspan="15%"></td>
                                </tr>';
                    }
                       
                    if($contribution_count < 12)
                    {
                        $counter = $contribution_count + 1;
                        while($counter <=12) 
                        {
                            $xcel_cont .= '<tr><td colspan ="100%"></td></tr>';
                            $counter = $counter + 1;
                        }
                    }

                    $xcel_cont .='</table>
                            </div>
                            <div></div><div><div>
                            <table>
                                <tr>
                                    <td colspan="100%" style="text-align:left;">Thank you,</td>
                                </tr>
                                <tr><td>&nbsp;</td></tr>
                                <tr>
                                    <td colspan="100%" style="text-align:left;">Human Resource Group</td>
                                </tr>
                            </table>
                            </div>';
                    
                    $this->pdf->addPage('F', 'LEGAL', true);
                    $this->pdf->SetFontSize( 12 );
                    $this->pdf->writeHTML($xcel_cont, true, false, true, false, '');
            }
        }
    }

    function export_pagibig_STLRF($company_id, $employee_id, $date_from, $date_to, $title)
    {
        $company_setting_res = $this->db->query('SELECT * FROM '.$this->db->dbprefix('user_company').' WHERE company_id = "'.$company_id.'"')->row();
        $monthly_detail_qry = "SELECT b.lastname, b.firstname, IF(b.middlename IS NULL,b.middleinitial,b.middlename) AS middlename, b.aux,
                                            IF(bb.pagibig = '',b.birth_date,bb.pagibig) pagibig_no, SUM(a.amount) as amount
                                            FROM hr_employee_loan c
                                            LEFT JOIN hr_employee_loan_payment a ON c.employee_loan_id = a.employee_loan_id
                                            LEFT JOIN hr_user b ON c.employee_id = b.employee_id
                                            LEFT JOIN hr_employee bb ON c.employee_id = bb.employee_id
                                            WHERE 1 AND c.loan_id = 5 AND bb.id_number IS NOT NULL
                                            AND a.payroll_date BETWEEN '{$date_from}' AND '{$date_to}'";
        if(!empty($employee_id)){ $monthly_detail_qry .= " AND b.employee_id IN ({$employee_id})"; }
        if(!empty($company_id)){ $monthly_detail_qry .= " AND b.company_id IN ({$company_id})"; }
        $monthly_detail_qry .= " GROUP BY b.employee_id";
        $monthly_detail_qry .= " ORDER BY b.lastname, b.firstname, b.middleinitial";
        // dbug($monthly_detail_qry);
        $monthly_detail_res = $this->db->query($monthly_detail_qry);
        $total_no_employees = $monthly_detail_res->num_rows();
        $allowed_count_per_page = 30;
        $page_with = $total_no_employees/$allowed_count_per_page;
        $page_floor = floor($page_with);

        $number_of_page = $page_floor;
        if($page_with > $page_floor)
        {
            $number_of_page = $page_floor + 1;
        }
        $year_set = date("Y",strtotime($date_from));
        $month_set = date("F",strtotime($date_from)); 
        $company = $company_setting_res->company;
        $company_sss_no = $company_setting_res->sss_no;
        $company_address = $company_setting_res->address;
        $company_tin_no = $company_setting_res->vat_registration;
        $company_zip = $company_setting_res->zipcode;
        $company_telephone = $company_setting_res->telephone;
        $company_pagibig_no = $company_setting_res->pagibig_no; 

        if($total_no_employees != 0)
        {
            $total_last_page_pagibig = 0;
            $total_last_page_pagibig_company = 0;
            $total_last_page_pagibig_total = 0;
            for($i=1;$i<=$number_of_page; $i++)
            {                
                $this->pdf->addPage('P', 'LETTER', true);
                $this->pdf->SetFontSize( 10 );
                $background = 'uploads/payroll_report/stlrf_001.jpg';
                $this->pdf->SetAutoPageBreak(false, 0);
                $this->pdf->Image($background, 6.5, 6, 203, 269.5, 'JPG', '', '', false, 100, '', false, false, 0, false, 0, false);
                $page_no_certified = $i;

                // MultiCell($w, $h, $txt, $border=0, $align='J', $fill=0, $ln=1, $x='', $y='', $reseth=true, $stretch=0, $ishtml=false, $autopadding=true, $maxh=0)

                $this->pdf->MultiCell(30, 10, $company_sss_no, 0, 'L', false, 0, 147, 38, true, 0, false, false, 0, 'T', true);
                $this->pdf->MultiCell(102, 20, $company, 0, 'L', false, 0, 15, 55, true, 0, false, false, 0, 'T', true);   
                $this->pdf->MultiCell(200, 20, $company_address, 0, 'L', false, 0, 15, 65, true, 0, false, false, 0, 'T', true);   
                $this->pdf->MultiCell(15, 10, date("Ym",strtotime($date_from)), 0, 'C', false, 0, 170, 65, true, 0, false, false, 0, 'T', true);   

                $monthly_detail_value_qry = "SELECT b.lastname, b.firstname, IF(b.middlename IS NULL,b.middleinitial,b.middlename) AS middlename, b.aux,
                                            IF(bb.pagibig = '',b.birth_date,bb.pagibig) pagibig_no, SUM(a.amount) as amount
                                            FROM hr_employee_loan c
                                            LEFT JOIN hr_employee_loan_payment a ON c.employee_loan_id = a.employee_loan_id
                                            LEFT JOIN hr_user b ON c.employee_id = b.employee_id
                                            LEFT JOIN hr_employee bb ON c.employee_id = bb.employee_id
                                            WHERE 1 AND c.loan_id = 5 AND bb.id_number IS NOT NULL
                                            AND a.payroll_date BETWEEN '{$date_from}' AND '{$date_to}'";
                if(!empty($employee_id)){ $monthly_detail_value_qry .= " AND b.employee_id IN ({$employee_id})"; }
                if(!empty($company_id)){ $monthly_detail_value_qry .= " AND b.company_id IN ({$company_id})"; }
                $monthly_detail_value_qry .= " GROUP BY b.employee_id
                                            ORDER BY b.lastname, b.firstname, b.middleinitial";
                $limit = ($i - 1) * $allowed_count_per_page;
                $monthly_detail_value_qry .= " LIMIT {$limit},{$allowed_count_per_page}";
                // dbug($monthly_detail_value_qry);
                $monthly_detail_value_res = $this->db->query($monthly_detail_value_qry);
                $value_count = 0;
                $line = 88.65;
                $total_mpl_amount = 0;
                foreach ($monthly_detail_value_res->result() as $key => $value) 
                {            
                
                    $this->pdf->SetFontSize( 6 );        
                    $pagibig_no = str_replace('-','',$value->pagibig_no);
                    $this->pdf->MultiCell(26, 10, $pagibig_no, 0, 'C', false, 0, 7.5, $line, true, 0, false, false, 0, 'T', true); 
                    $this->pdf->MultiCell(18, 10, substr($value->lastname, 0, 18), 0, 'L', false, 0, 60, $line, true, 0, false, false, 0, 'T', true);                     
                    $this->pdf->MultiCell(18, 10, substr($value->firstname, 0, 18), 0, 'L', false, 0, 82, $line, true, 0, false, false, 0, 'T', true);                     
                    $this->pdf->MultiCell(18, 10, substr($value->aux, 0, 6), 0, 'L', false, 0, 104, $line, true, 0, false, false, 0, 'T', true);                     
                    $this->pdf->MultiCell(18, 10, substr($value->middlename, 0, 12), 0, 'L', false, 0, 126.5, $line, true, 0, false, false, 0, 'T', true);                     

                    $this->pdf->MultiCell(25, 10, 'MPL', 0, 'C', false, 0, 145.4, $line, true, 0, false, false, 0, 'T', true); 

                    $mpl_amount = $value->amount;
                    $this->pdf->MultiCell(20, 10, number_format($mpl_amount, 2, '.', ','), 0, 'R', false, 0, 167.5, $line, true, 0, false, false, 0, 'T', true); 
                    
                    switch ($value_count) {
                        case '3':
                            $line = $line + 4;
                            break;
                        case '7': 
                            $line = $line + 3.8;
                            break;
                        case '13': 
                            $line = $line + 3.6;
                            break;
                        case '17': 
                            $line = $line + 3.6;
                            break;
                        case '25': 
                            $line = $line + 3.6;
                            break;
                        default:
                            $line = $line + 4.2;
                            break;
                    }
                    $value_count++;
                    $total_mpl_amount += $mpl_amount;
                    $total_last_mpl_amount += $mpl_amount;
                }
                // $this->pdf->MultiCell(24, 10, $value_count, 0, 'C', false, 0, 25, 243, true, 0, false, false, 0, 'T', true); 
                $this->pdf->MultiCell(20.5, 10, number_format($total_mpl_amount, 2, '.', ','), 0, 'R', false, 0, 168, 212.5, true, 0, false, false, 0, 'T', true); 

                if($i == $number_of_page)
                {
                    // $this->pdf->MultiCell(25, 10, $total_no_employees, 0, 'C', false, 0, 73, 243, true, 0, false, false, 0, 'T', true); 
                    $this->pdf->MultiCell(20.5, 10, number_format($total_last_mpl_amount, 2, '.', ','), 0, 'R', false, 0, 168, 217.5, true, 0, false, false, 0, 'T', true); 
                }
                $this->pdf->SetFontSize(9);
                $this->pdf->MultiCell(90, 10, 'MARCOS DENNIS M. MENDOZA', 0, 'C', false, 0, 5, 240, true, 0, false, false, 0, 'T', true); 
                $this->pdf->MultiCell(50, 10, 'PAYROLL SUPERVISOR', 0, 'C', false, 0, 103, 240, true, 0, false, false, 0, 'T', true);
            }   
        }
        else
        {
            $this->pdf->addPage('P', 'LETTER', true);
            $this->pdf->SetXY(100, 20);
            $this->pdf->writeHTML("No Record Found!", true, false, true, false, '');
        }
    }

    function export_pagibig_to_disk($company_id, $employee_id, $date_from, $date_to, $title){
        
        ini_set("memory_limit", "512M");
        
        $year_num = date("Y",strtotime($date_from));
        $day_num = date("d",strtotime($date_from));
        $month_num = date("m",strtotime($date_from)); 
        $payroll_date = $year_num.$month_num;

        $company_setting_res = $this->db->query('SELECT * FROM '.$this->db->dbprefix('user_company').' WHERE company_id = "'.$company_id.'"')->row();      

        $company_name = $company_setting_res->company;
        $company_add = $company_setting_res->address;
        $company_sss_id = str_replace('-', '', $company_setting_res->sss_no);
        $company_code = $company_setting_res->company_code;
        $compnay_zip = $company_setting_res->zipcode;
        $compnay_tel = str_replace('-', '', $company_setting_res->telephone);
        
        $contribution_data_qry = "  SELECT CASE WHEN e.pagibig = ' ' THEN CASE WHEN e.philhealth = ' ' THEN e.sss ELSE e.philhealth END ELSE e.pagibig END AS employee_gov_id,
                                    lastname, firstname, middlename,aux,
                                    sum(ec.employee) AS employee_contribution, sum(ec.company) AS employer_contribution,
                                    e.tin, u.birth_date
                                    FROM {$this->db->dbprefix}employee_contribution ec
                                    LEFT JOIN {$this->db->dbprefix}user u ON ec.employee_id = u.employee_id
                                    LEFT JOIN {$this->db->dbprefix}employee e ON ec.employee_id = e.employee_id
                                    WHERE ec.company_id = {$company_id}
                                    AND ec.transaction_id = 52
                                    AND EXTRACT(YEAR_MONTH FROM ec.payroll_date) = {$payroll_date}
                                    GROUP BY e.pagibig, e.philhealth, e.sss, lastname, firstname, middlename, e.tin, u.birth_date
                                    ORDER BY lastname, firstname, middleinitial";
        $contribution_data_res = $this->db->query($contribution_data_qry);
                
        // START of To Disk //

        $File = $month_num.$year_num.".txt"; 
        header('Content-Description: File Transfer');
        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename='.basename($File));
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($File));
        ob_clean();
        flush();
        
        $Handle = fopen($File, 'w');
        
        $Data_header .= 'EH01'.$year_num.$month_num.str_pad($company_sss_id,15).str_pad($company_name,103).str_pad($company_add,100).str_pad($compnay_zip,7).str_pad(substr($compnay_tel,-7),7)."\r\n";
        
        fwrite($Handle, $Data_header); 

        foreach ($contribution_data_res->result() as $key => $value) {

            $employee_gov_id = str_pad(str_replace('-','',$value->employee_gov_id ),27);//either of the following Pag-Ibig SSS PhilHealth
            
            $last_name  = str_replace(' *', '', $value->lastname);

            $lastname = str_pad($last_name, 30);
            if (strpos($lastname, 'ñ') !== false) {
                $lastname = str_pad($last_name, 31);
            }
            else     
            {
                $lastname = str_pad($last_name, 30);
            }

            if(strlen($value->firstname > 29)){
                $first_name = $value->firstname;
            }else{
                $first_name = $value->firstname.' '.$value->aux;
            }

            $firstname = str_pad($first_name, 30);
            if (strpos($firstname, 'ñ') !== false) {
                $firstname = str_pad($first_name, 31);
            }
            else     
            {
                $firstname = str_pad($first_name, 30);
            }

            $middlename = str_pad($value->middlename, 30);
            if (strpos($middlename, 'ñ') !== false) {
                $middlename = str_pad($value->middlename, 31);
            }
            else     
            {
                $middlename = str_pad($value->middlename, 30);
            }
            
            $employee_contribution = str_pad( str_replace(',','',number_format($value->employee_contribution)),13);
            $employer_contribution = str_pad( str_replace(',','',number_format($value->employer_contribution)),13);
            $tin_id = substr(str_pad(str_replace('-','',$value->tin),12,"0",STR_PAD_LEFT),0,-3);
            $birth_date = str_pad(str_replace('-','',$value->birth_date),8);

            $Data = 'DT'.$employee_gov_id.str_replace('*',' ',$lastname).$firstname.$middlename.$employee_contribution.$employer_contribution.str_pad($tin_id,15).$birth_date."\r\n"; 
            fwrite($Handle, $Data); 
            
        }
        
        fclose($Handle); 
        readfile($File);
        exit();
        // END of To Disk //
    }
    
    function export_pagibig_loan_to_disk($company_id, $employee_id, $date_from, $date_to, $title){
        ini_set("memory_limit", "512M");
        $year_num = date("Y",strtotime($date_from));
        $day_num = date("d",strtotime($date_from));
        $month_num = date("m",strtotime($date_from)); 
        $payroll_date = $year_num.$month_num;

        $company_setting_res = $this->db->query('SELECT * FROM '.$this->db->dbprefix('user_company').' WHERE company_id = "'.$company_id.'"')->row();      

        $company_name = $company_setting_res->company;
        $company_add = $company_setting_res->address;
        $company_sss_id = str_replace('-', '', $company_setting_res->sss_no);
        $company_code = $company_setting_res->company_code;
        $compnay_zip = $company_setting_res->zipcode;
        $compnay_tel = str_replace('-', '', $company_setting_res->telephone);
        
        $contribution_data_qry = "  SELECT CASE WHEN e.pagibig = ' ' THEN CASE WHEN e.philhealth = ' ' THEN e.sss ELSE e.philhealth END ELSE e.pagibig END AS employee_gov_id,
                                    u.lastname, u.firstname, u.middlename, u.aux, SUM(lp.amount) AS loan_amount, e.tin, u.birth_date
                                    FROM {$this->db->dbprefix}employee_loan el
                                    JOIN {$this->db->dbprefix}employee_loan_payment lp ON lp.employee_loan_id = el.employee_loan_id
                                    JOIN {$this->db->dbprefix}employee e ON e.employee_id = el.employee_id
                                    JOIN {$this->db->dbprefix}user u ON el.employee_id = u.employee_id
                                    JOIN {$this->db->dbprefix}payroll_loan pl ON el.loan_id = pl.loan_id
                                    WHERE EXTRACT(YEAR_MONTH FROM lp.date_paid) = {$payroll_date}
                                    GROUP BY el.employee_id
                                    ORDER BY lastname, firstname, middleinitial";
        $contribution_data_res = $this->db->query($contribution_data_qry);
                
        // START of To Disk //

        $File = $month_num.$year_num.".txt"; 
        header('Content-Description: File Transfer');
        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename='.basename($File));
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($File));
        ob_clean();
        flush();
        
        $Handle = fopen($File, 'w');
        
        $Data_header .= 'EH01'.$year_num.$month_num.str_pad($company_sss_id,15).str_pad($company_name,103).str_pad($company_add,100).str_pad($compnay_zip,7).str_pad(substr($compnay_tel,-7),7)."\r\n";
        
        fwrite($Handle, $Data_header); 

        foreach ($contribution_data_res->result() as $key => $value) {

            $employee_gov_id = str_pad(str_replace('-','',$value->employee_gov_id ),27);//either of the following Pag-Ibig SSS PhilHealth
            
            $last_name  = str_replace(' *', '', $value->lastname);

            $lastname = str_pad($last_name, 30);
            if (strpos($lastname, 'ñ') !== false) {
                $lastname = str_pad($last_name, 31);
            }
            else     
            {
                $lastname = str_pad($last_name, 30);
            }

            if(strlen($value->firstname > 29)){
                $first_name = $value->firstname;
            }else{
                $first_name = $value->firstname.' '.$value->aux;
            }

            $firstname = str_pad($first_name, 30);
            if (strpos($firstname, 'ñ') !== false) {
                $firstname = str_pad($first_name, 31);
            }
            else     
            {
                $firstname = str_pad($first_name, 30);
            }

            $middlename = str_pad($value->middlename, 30);
            if (strpos($middlename, 'ñ') !== false) {
                $middlename = str_pad($value->middlename, 31);
            }
            else     
            {
                $middlename = str_pad($value->middlename, 30);
            }
            
            $employee_contribution = str_pad( number_format($value->loan_amount,2,'.',''),13);
            $employer_contribution = 0;
            $tin_id = substr(str_replace('-','',$value->tin),0,-3);
            $birth_date = str_pad(str_replace('-','',$value->birth_date),8);

            $Data = 'DT'.$employee_gov_id.str_replace('*',' ',$lastname).$firstname.$middlename.str_replace(',','',$employee_contribution).$employer_contribution.str_pad(str_pad($tin_id,9,"0",STR_PAD_LEFT),15).$birth_date."\r\n"; 
            fwrite($Handle, $Data); 
            
        }
        
        fclose($Handle); 
        readfile($File);
        exit();
        // END of To Disk //
    }
}

/* End of file */
/* Location: system/application */
?>