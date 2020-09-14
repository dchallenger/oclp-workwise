<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class sss_report extends MY_Controller
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
        //Select Report type:
        $report_type = array("Monthly Premium R3", "Quarterly Report R1A", "Remittance Certification", "R3 to Disk", "SSS Loan to Disk");
        $report_type_html = '<select id="report_type_id" name="report_type_id">';
            foreach($report_type as $report_type_id => $report_type_value){
                $report_type_html .= '<option value="'.$report_type_id.'">'.$report_type_value.'</option>';
            }
        $report_type_html .= '</select>'; 

        //Select Company
        $company = $this->db->query('SELECT * FROM '.$this->db->dbprefix('user_company').'')->result_array();
        $company_html = '<select id="company_id" name="company_id">';
        $company_html .= '<option value="">Select...</option>';
            foreach($company as $company_record){
                 $company_html .= '<option value="'.$company_record["company_id"].'">'.$company_record["company"].'</option>';
            }
        $company_html .= '</select>'; 

        //Select Employee's
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
            case '0':            
                $this->pdf->SetMargins(5, 5, 5);                   
                $this->pdf->SetFontSize( 8 );
                $html = $this->export_sss_monthly($company_id, $employee_id, $date_from, $date_to, "SSS Premium Contribution");                                
                $title = "SSS Premium Contribution";
                break;
            case '1':
                $this->pdf->SetMargins(5, 5, 5);   
                $this->pdf->SetFontSize( 10 ); 
                $html = $this->export_sss_quarterly($company_id, $employee_id, $date_from, $date_to, "SSS Premium Contribution");        
                $title = "SSS Premium Contribution";
                break;
            case '2':
                $html = $this->export_sss_certificate($company_id, $employee_id, $date_from, $date_to, "CERTIFICATE OF PREMIUM PAYMENT");        
                $title = "CERTIFICATE OF PREMIUM PAYMENT";
                break;
            case '3':
                $html = $this->export_sss_to_disk($company_id, $employee_id, $date_from, $date_to, "SSS R3 to Disk");            
                $title = "SSS R3 to Disk";
                break;
            case '4':
                $html = $this->export_sss_loan_to_disk($company_id, $employee_id, $date_from, $date_to, "SSS Loans to Disk");            
                $title = "SSS Loan to Disk";
                break;
        }
        $this->pdf->writeHTML($html, true, false, true, false, '');
        $this->pdf->Output(ucwords(str_replace(" ","_",$title))."_" . date('dmYHis') . '.pdf', 'I');
    }

    function export_sss_quarterly($company_id, $employee_id, $date_from, $date_to, $title)
    {
        ini_set("max_execution_time", 7200);
        ini_set("memory_limit", "1024M");
        $page_hidden = '';
        $company_setting_res = $this->db->query('SELECT * FROM '.$this->db->dbprefix('user_company').' WHERE company_id = "'.$company_id.'"')->row();
        $result = $this->get_payroll_government_counter($company_id, $employee_id, $date_from, $date_to);
        $total_no_employees = $result->num_rows();
        $this->pdf->SetFontSize( 8 );
        $this->pdf->setPrintHeader(false);
        $this->pdf->setPrintFooter(false);
        $this->pdf->SetMargins(5, 5, 5);

        $allowed_count_per_page = 15;
        $page_with = $total_no_employees/$allowed_count_per_page;
        $page_floor = floor($page_with);

        $number_of_page = $page_floor;
        if($page_with > $page_floor)
        {
            $number_of_page = $page_floor + 1;
        }

        $year_set = date("Y",strtotime($date_from));
        $month_set = date("F",strtotime($date_from)); 
        $month_num = date("m",strtotime($date_from)); 
        if($month_num < 04)
        {
            $quarter_end = '03'.$year_set;
        }
        elseif($month_num < 07)
        {
            $quarter_end = '06'.$year_set;   
        }
        elseif($month_num < 10)
        {
            $quarter_end = '09'.$year_set;   
        }
        elseif($month_num > 09)
        {
            $quarter_end = '12'.$year_set;   
        }
        
        $company = $company_setting_res->company;
        $company_sss_no = $company_setting_res->sss_no;
        $company_address = $company_setting_res->address;
        $company_tin_no = $company_setting_res->vat_registration;
        $company_zip = $company_setting_res->zipcode;
        $company_telephone = $company_setting_res->telephone;
        if($total_no_employees != 0)
        {
            $sss_grand_total_1st = 0;
            $ec_grand_total_1st = 0;
            $sss_grand_total_2nd = 0;
            $ec_grand_total_2nd = 0;
            $sss_grand_total_3rd = 0;
            $ec_grand_total_3rd = 0;
            $sss_per_total_1st = 0;
            $ec_per_total_1st = 0;
            $sss_per_total_2nd = 0;
            $ec_per_total_2nd = 0;
            $sss_per_total_3rd = 0;
            $ec_per_total_3rd = 0;
            $counter = 1;
            $page_no_certified = 1;
            $employee_id = '';
            $monthly_detail_value_res = $this->get_payroll_government($company_id, $employee_id, $date_from, $date_to);
            $this->pdf->addPage('L', 'LETTER', true);
            $background = 'uploads/payroll_report/sss_monthly-1_web.jpg';
            $this->pdf->SetAutoPageBreak(false, 0);
            $this->pdf->Image($background, 10, 10, 260, 195, 'JPG', '', '', false, 100, '', false, false, 0, false, $page_hidden, false);
            if(!empty($company_sss_no))
            {
                $company_sss_x = 7.5;
                $company_sss_no = str_replace("-", "", $company_sss_no);
                for($c_s = 0 ; $c_s <= strlen($company_sss_no); $c_s++)
                {
                    $company_sub = substr($company_sss_no, $c_s,1);
                    $this->pdf->MultiCell(10, 10, $company_sub, 0, 'C', false, 0, $company_sss_x, 37.5, true, 0, false, false, 0, 'T', true);   
                    $company_sss_x = $company_sss_x + 4.12;
                } 
            }   
            if(strlen($company) > 72)
            {
                $company = substr($company, 0,72).'...';
            }
            $this->pdf->MultiCell(148, 10, $company, 0, 'C', false, 0, 63.5, 37.5, true, 0, false, false, 0, 'T', true);
            $quarter_end_x = 242.5;
            for($q_e = 0 ; $q_e <= strlen($quarter_end); $q_e++)
            {
                $quarter_end_sub = substr($quarter_end, $q_e,1);
                $this->pdf->MultiCell(10, 10, $quarter_end_sub, 0, 'C', false, 0, $quarter_end_x, 37.5, true, 0, false, false, 0, 'T', true);   
                $quarter_end_x = $quarter_end_x + 4.12;
            }         
            if(strlen($company_telephone)  > 25)
            {
                $company_telephone = substr($company_telephone, 0,25).'...';
            }
            $this->pdf->MultiCell(54, 10, $company_telephone, 0, 'C', false, 0, 10, 48.5, true, 0, false, false, 0, 'T', true);
            if(strlen($company_address) > 72)
            {
                $company_address = substr($company_address, 0,72).'...';
            }
            $this->pdf->MultiCell(148, 10, $company_address, 0, 'C', false, 0, 63.5, 48.5, true, 0, false, false, 0, 'T', true); 
            if(!empty($employer_type_id[0]))
            {
                $m2_type = 'x';
                switch ($employer_type_id[0]) {
                    // case 'regular':
                    case 1:
                        $this->pdf->SetXY(218.5, 47.5);
                        break; 
                    // case 'household':
                    case 2:
                        $this->pdf->SetXY(243.3, 47.6);
                        break;
                }
                $this->pdf->writeHTML($m2_type, true, false, true, false, '');
            }
            switch (strlen($page_no_certified)) 
            {
                case 1:
                    $page_from_x = 257;
                    for($page_from = 0 ; $page_from <= strlen($page_no_certified); $page_from++)
                    {
                        $i_from = substr($page_no_certified, $page_from,1);
                        $this->pdf->MultiCell(10, 10, $i_from, 0, 'C', false, 0, $page_from_x, 160, true, 0, false, false, 0, 'T', true);   
                        $page_from_x = $page_from_x + 4.8;
                    }
                    break;
                case 2:
                    $page_from_x = 253;
                    for($page_from = 0 ; $page_from <= strlen($page_no_certified); $page_from++)
                    {
                        $i_from = substr($page_no_certified, $page_from,1);
                        $this->pdf->MultiCell(10, 10, $i_from, 0, 'C', false, 0, $page_from_x, 160, true, 0, false, false, 0, 'T', true);   
                        $page_from_x = $page_from_x + 4;
                    }
                    break;
                case 3:
                    $page_from_x = 249;
                    for($page_from = 0 ; $page_from <= strlen($page_no_certified); $page_from++)
                    {
                        $i_from = substr($page_no_certified, $page_from,1);
                        $this->pdf->MultiCell(10, 10, $i_from, 0, 'C', false, 0, $page_from_x, 160, true, 0, false, false, 0, 'T', true);   
                        $page_from_x = $page_from_x + 4;
                    }
                    break;
            }
            switch (strlen($number_of_page)) 
            {
                case 1:
                    $page_to_x = 257;
                    for($page_to = 0 ; $page_to <= strlen($number_of_page); $page_to++)
                    {
                        $i_to = substr($number_of_page, $page_to,1);
                        $this->pdf->MultiCell(10, 10, $i_to, 0, 'C', false, 0, $page_to_x, 169.5, true, 0, false, false, 0, 'T', true);   
                        $page_to_x = $page_to_x + 4.8;
                    }
                    break;
                case 2:
                    $page_to_x = 253;
                    for($page_to = 0 ; $page_to <= strlen($number_of_page); $page_to++)
                    {
                        $i_to = substr($number_of_page, $page_to,1);
                        $this->pdf->MultiCell(10, 10, $i_to, 0, 'C', false, 0, $page_to_x, 169.5, true, 0, false, false, 0, 'T', true);   
                        $page_to_x = $page_to_x + 4;
                    }
                    break;
                case 3:
                    $page_to_x = 249;
                    for($page_to = 0 ; $page_to <= strlen($number_of_page); $page_to++)
                    {
                        $i_to = substr($number_of_page, $page_to,1);
                        $this->pdf->MultiCell(10, 10, $i_to, 0, 'C', false, 0, $page_to_x, 169.5, true, 0, false, false, 0, 'T', true);   
                        $page_to_x = $page_to_x + 4;
                    }
                    break;
            }
            $value_count = 1;
            $value_count_2 = 1;
            foreach ($monthly_detail_value_res as $key => $value) 
            {    
                if(!empty($employee_id))
                {
                    if($employee_id != $value->employee_id)
                    {   
                        $counter++;   
                        $value_count++;  
                        $value_count_2++;           
                    }                   
                }
                if($counter > $allowed_count_per_page)
                {
                    $value_count = 1;                                       
                    $value_count_2 = 1;   
                }
                if($value_count == 1) {
                    $line = 66.5;
                } elseif($value_count == 2) {
                    $line = 72.5;
                } elseif($value_count == 3) {
                    $line = 78;
                } elseif($value_count == 4) {
                    $line = 84;
                } elseif($value_count == 5) {
                    $line = 90;
                } elseif($value_count == 6) {
                    $line = 96;
                } elseif($value_count == 7) {
                    $line = 101;
                } elseif($value_count == 8) {
                    $line = 107;
                } elseif($value_count == 9) {
                    $line = 112.5;
                } elseif($value_count == 10) {
                    $line = 118.5;
                } elseif($value_count == 11) {
                    $line = 124.5;
                } elseif($value_count == 12) {
                    $line = 131;
                } elseif($value_count == 13) {
                    $line = 138;
                } elseif($value_count == 14) {
                    $line = 144;
                } else {
                    $line = 149.5;
                }
                if($value_count_2 == 1) {
                    $line_amount = 66.5;
                } elseif($value_count_2 == 2) {
                    $line_amount = 72.5;
                } elseif($value_count_2 == 3) {
                    $line_amount = 78;
                } elseif($value_count_2 == 4) {
                    $line_amount = 84;
                } elseif($value_count_2 == 5) {
                    $line_amount = 90;
                } elseif($value_count_2 == 6) {
                    $line_amount = 96;
                } elseif($value_count_2 == 7) {
                    $line_amount = 101;
                } elseif($value_count_2 == 8) {
                    $line_amount = 107;
                } elseif($value_count_2 == 9) {
                    $line_amount = 112.5;
                } elseif($value_count_2 == 10) {
                    $line_amount = 118.5;
                } elseif($value_count_2 == 11) {
                    $line_amount = 124.5;
                } elseif($value_count_2 == 12) {
                    $line_amount = 131;
                } elseif($value_count_2 == 13) {
                    $line_amount = 138;
                } elseif($value_count_2 == 14) {
                    $line_amount = 144;
                } else {
                    $line_amount = 149.5;
                }
                if($counter > $allowed_count_per_page)
                {
                    $line_per = 154.5;
                    switch (strlen($sss_per_total_1st)) 
                    {
                        case 5:
                                $sss_x_per = 129;
                                for($sss_per = 0 ; $sss_per <= strlen($sss_per_total_1st); $sss_per++)
                                {
                                    $sss_sub_per = substr($sss_per_total_1st, $sss_per,1);
                                    $this->pdf->MultiCell(10, 10, $sss_sub_per, 0, 'C', false, 0, $sss_x_per, $line_per, true, 0, false, false, 0, 'T', true);   
                                    $sss_x_per = $sss_x_per + 4.8;
                                }
                            break;
                        case 4:
                                $sss_x_per = 134;
                                for($sss_per = 0 ; $sss_per <= strlen($sss_per_total_1st); $sss_per++)
                                {
                                    $sss_sub_per = substr($sss_per_total_1st, $sss_per,1);
                                    $this->pdf->MultiCell(10, 10, $sss_sub_per, 0, 'C', false, 0, $sss_x_per, $line_per, true, 0, false, false, 0, 'T', true);   
                                    $sss_x_per = $sss_x_per + 4.6;
                                }
                            break;
                        case 3:
                                $sss_x_per = 139;
                                for($sss_per = 0 ; $sss_per <= strlen($sss_per_total_1st); $sss_per++)
                                {
                                    $sss_sub_per = substr($sss_per_total_1st, $sss_per,1);
                                    $this->pdf->MultiCell(10, 10, $sss_sub_per, 0, 'C', false, 0, $sss_x_per, $line_per, true, 0, false, false, 0, 'T', true);   
                                    $sss_x_per = $sss_x_per + 4.4;
                                }
                            break;
                        case 2:
                                $sss_x_per = 143.5;
                                for($sss_per = 0 ; $sss_per <= strlen($sss_per_total_1st); $sss_per++)
                                {
                                    $sss_sub_per = substr($sss_per_total_1st, $sss_s,1);
                                    $this->pdf->MultiCell(10, 10, $sss_sub_per, 0, 'C', false, 0, $sss_x_per, $line_per, true, 0, false, false, 0, 'T', true);   
                                    $sss_x_per = $sss_x_per + 4.2;
                                }
                            break;
                        case 1:
                                $sss_x_per = 147.5;
                                for($sss_per = 0 ; $sss_per <= strlen($sss_per_total_1st); $sss_per++)
                                {
                                    $sss_sub_per = substr($sss_per_total_1st, $sss_per,1);
                                    $this->pdf->MultiCell(10, 10, $sss_sub_per, 0, 'C', false, 0, $sss_x_per, $line_per, true, 0, false, false, 0, 'T', true);   
                                    $sss_x_per = $sss_x_per + 4.2;
                                }
                            break;
                    }
                    switch (strlen($ec_per_total_1st)) 
                    {
                        case 3:
                                $ec_x_per = 197.5;
                                for($ec_s_per = 0 ; $ec_s_per <= strlen($ec_per_total_1st); $ec_s_per++)
                                {
                                    $ec_sub_per = substr($ec_per_total_1st, $ec_s_per,1);
                                    $this->pdf->MultiCell(10, 10, $ec_sub_per, 0, 'C', false, 0, $ec_x_per, $line_per, true, 0, false, false, 0, 'T', true);   
                                    $ec_x_per = $ec_x_per + 4.4;
                                }
                            break;
                        case 2:
                                $ec_x_per = 202;
                                for($ec_s_per = 0 ; $ec_s_per <= strlen($ec_per_total_1st); $ec_s_per++)
                                {
                                    $ec_sub_per = substr($ec_per_total_1st, $ec_s_per,1);
                                    $this->pdf->MultiCell(10, 10, $ec_sub_per, 0, 'C', false, 0, $ec_x_per, $line_per, true, 0, false, false, 0, 'T', true);   
                                    $ec_x_per = $ec_x_per + 4.2;
                                }
                            break;
                        case 1:
                                $ec_x_per = 206.5;
                                for($ec_s_per = 0 ; $ec_s_per <= strlen($ec_per_total_1st); $ec_s_per++)
                                {
                                    $ec_sub_per = substr($ec_per_total_1st, $ec_s_per,1);
                                    $this->pdf->MultiCell(10, 10, $ec_sub_per, 0, 'C', false, 0, $ec_x_per, $line_per, true, 0, false, false, 0, 'T', true);   
                                    $ec_x_per = $ec_x_per + 4.2;
                                }
                            break;
                    }
                    switch (strlen($sss_per_total_2nd)) 
                    {
                        case 5:
                                $sss_x_per = 152;
                                for($sss_s_per = 0 ; $sss_s_per <= strlen($sss_per_total_2nd); $sss_s_per++)
                                {
                                    $sss_sub_per = substr($sss_per_total_2nd, $sss_s_per,1);
                                    $this->pdf->MultiCell(10, 10, $sss_sub_per, 0, 'C', false, 0, $sss_x_per, $line_per, true, 0, false, false, 0, 'T', true);   
                                    $sss_x_per = $sss_x_per + 4.8;
                                }
                            break;
                        case 4:
                                $sss_x_per = 157;
                                for($sss_s_per = 0 ; $sss_s_per <= strlen($sss_per_total_2nd); $sss_s_per++)
                                {
                                    $sss_sub_per = substr($sss_per_total_2nd, $sss_s_per,1);
                                    $this->pdf->MultiCell(10, 10, $sss_sub_per, 0, 'C', false, 0, $sss_x_per, $line_per, true, 0, false, false, 0, 'T', true);   
                                    $sss_x_per = $sss_x_per + 4.6;
                                }
                            break;
                        case 3:
                                $sss_x_per = 162;
                                for($sss_s_per = 0 ; $sss_s_per <= strlen($sss_per_total_2nd); $sss_s_per++)
                                {
                                    $sss_sub_per = substr($sss_per_total_2nd, $sss_s_per,1);
                                    $this->pdf->MultiCell(10, 10, $sss_sub_per, 0, 'C', false, 0, $sss_x_per, $line_per, true, 0, false, false, 0, 'T', true);   
                                    $sss_x_per = $sss_x_per + 4.4;
                                }
                            break;
                        case 2:
                                $sss_x_per = 166.5;
                                for($sss_s_per = 0 ; $sss_s_per <= strlen($sss_per_total_2nd); $sss_s_per++)
                                {
                                    $sss_sub_per = substr($sss_per_total_2nd, $sss_s_per,1);
                                    $this->pdf->MultiCell(10, 10, $sss_sub_per, 0, 'C', false, 0, $sss_x_per, $line_per, true, 0, false, false, 0, 'T', true);   
                                    $sss_x_per = $sss_x_per + 4.2;
                                }
                            break;
                        case 1:
                                $sss_x_per = 170.5;
                                for($sss_s_per = 0 ; $sss_s_per <= strlen($sss_per_total_2nd); $sss_s_per++)
                                {
                                    $sss_sub_per = substr($sss_per_total_2nd, $sss_s_per,1);
                                    $this->pdf->MultiCell(10, 10, $sss_sub_per, 0, 'C', false, 0, $sss_x_per, $line_per, true, 0, false, false, 0, 'T', true);   
                                    $sss_x_per = $sss_x_per + 4.2;
                                }
                            break;
                    }
                    switch (strlen($ec_per_total_2nd)) 
                    {
                        case 3:
                                $ec_x_per = 210.5;
                                for($ec_s_per = 0 ; $ec_s_per <= strlen($ec_per_total_2nd); $ec_s_per++)
                                {
                                    $ec_sub_per = substr($ec_per_total_2nd, $ec_s_per,1);
                                    $this->pdf->MultiCell(10, 10, $ec_sub_per, 0, 'C', false, 0, $ec_x_per, $line_per, true, 0, false, false, 0, 'T', true);   
                                    $ec_x_per = $ec_x_per + 4;
                                }
                            break;
                        case 2:
                                $ec_x_per = 214;
                                for($ec_s_per = 0 ; $ec_s_per <= strlen($ec_per_total_2nd); $ec_s_per++)
                                {
                                    $ec_sub_per = substr($ec_per_total_2nd, $ec_s_per,1);
                                    $this->pdf->MultiCell(10, 10, $ec_sub_per, 0, 'C', false, 0, $ec_x_per, $line_per, true, 0, false, false, 0, 'T', true);   
                                    $ec_x_per = $ec_x_per + 4.2;
                                }
                            break;
                        case 1:
                                $ec_x_per = 218;
                                for($ec_s_per = 0 ; $ec_s_per <= strlen($ec_per_total_2nd); $ec_s_per++)
                                {
                                    $ec_sub_per = substr($ec_per_total_2nd, $ec_s_per,1);
                                    $this->pdf->MultiCell(10, 10, $ec_sub_per, 0, 'C', false, 0, $ec_x_per, $line_per, true, 0, false, false, 0, 'T', true);   
                                    $ec_x_per = $ec_x_per + 4.2;
                                }
                            break;
                    }
                    switch (strlen($sss_per_total_3rd)) 
                    {
                        case 5:
                                $sss_x_per = 175;
                                for($sss_s_per = 0 ; $sss_s_per <= strlen($sss_per_total_3rd); $sss_s_per++)
                                {
                                    $sss_sub_per = substr($sss_per_total_3rd, $sss_s_per,1);
                                    $this->pdf->MultiCell(10, 10, $sss_sub_per, 0, 'C', false, 0, $sss_x_per, $line_per, true, 0, false, false, 0, 'T', true);   
                                    $sss_x_per = $sss_x_per + 4.5;
                                }
                            break;
                        case 4:
                                $sss_x_per = 179.5;
                                for($sss_s_per = 0 ; $sss_s_per <= strlen($sss_per_total_3rd); $sss_s_per++)
                                {
                                    $sss_sub_per = substr($sss_per_total_3rd, $sss_s_per,1);
                                    $this->pdf->MultiCell(10, 10, $sss_sub_per, 0, 'C', false, 0, $sss_x_per, $line_per, true, 0, false, false, 0, 'T', true);   
                                    $sss_x_per = $sss_x_per + 4.6;
                                }
                            break;
                        case 3:
                                $sss_x_per = 184;
                                for($sss_s_per = 0 ; $sss_s_per <= strlen($sss_per_total_3rd); $sss_s_per++)
                                {
                                    $sss_sub_per = substr($sss_per_total_3rd, $sss_s_per,1);
                                    $this->pdf->MultiCell(10, 10, $sss_sub_per, 0, 'C', false, 0, $sss_x_per, $line_per, true, 0, false, false, 0, 'T', true);   
                                    $sss_x_per = $sss_x_per + 4.4;
                                }
                            break;
                        case 2:
                                $sss_x_per = 189;
                                for($sss_s_per = 0 ; $sss_s_per <= strlen($sss_per_total_3rd); $sss_s_per++)
                                {
                                    $sss_sub_per = substr($sss_per_total_3rd, $sss_s_per,1);
                                    $this->pdf->MultiCell(10, 10, $sss_sub_per, 0, 'C', false, 0, $sss_x_per, $line_per, true, 0, false, false, 0, 'T', true);   
                                    $sss_x_per = $sss_x_per + 4.2;
                                }
                            break;
                        case 1:
                                $sss_x_per = 192.8;
                                for($sss_s_per = 0 ; $sss_s_per <= strlen($sss_per_total_3rd); $sss_s_per++)
                                {
                                    $sss_sub_per = substr($sss_per_total_3rd, $sss_s_per,1);
                                    $this->pdf->MultiCell(10, 10, $sss_sub_per, 0, 'C', false, 0, $sss_x_per, $line_per, true, 0, false, false, 0, 'T', true);   
                                    $sss_x_per = $sss_x_per + 4.2;
                                }
                            break;
                    }
                    switch (strlen($ec_per_total_3rd)) 
                    {
                        case 3:
                                $ec_x_per = 222.5;
                                for($ec_s_per = 0 ; $ec_s_per <= strlen($ec_per_total_3rd); $ec_s_per++)
                                {
                                    $ec_sub_per = substr($ec_per_total_3rd, $ec_s_per,1);
                                    $this->pdf->MultiCell(10, 10, $ec_sub_per, 0, 'C', false, 0, $ec_x_per, $line_per, true, 0, false, false, 0, 'T', true);   
                                    $ec_x_per = $ec_x_per + 4;
                                }
                            break;
                        case 2:
                                $ec_x_per = 226;
                                for($ec_s = 0 ; $ec_s <= strlen($ec_per_total_3rd); $ec_s++)
                                {
                                    $ec_sub_per = substr($ec_per_total_3rd, $ec_s,1);
                                    $this->pdf->MultiCell(10, 10, $ec_sub_per, 0, 'C', false, 0, $ec_x_per, $line_per, true, 0, false, false, 0, 'T', true);   
                                    $ec_x_per = $ec_x_per + 4.2;
                                }
                            break;
                        case 1:
                                $ec_x_per = 230.3;
                                for($ec_s_per = 0 ; $ec_s_per <= strlen($ec_per_total_3rd); $ec_s_per++)
                                {
                                    $ec_sub_per = substr($ec_per_total_3rd, $ec_s_per,1);
                                    $this->pdf->MultiCell(10, 10, $ec_sub_per, 0, 'C', false, 0, $ec_x_per, $line_per, true, 0, false, false, 0, 'T', true);   
                                    $ec_x_per = $ec_x_per + 4.2;
                                }
                            break;
                    }
                    $sss_per_total_1st = 0;
                    $ec_per_total_1st = 0;
                    $sss_per_total_2nd = 0;
                    $ec_per_total_2nd = 0;
                    $sss_per_total_3rd = 0;
                    $ec_per_total_3rd = 0;
                    $page_no_certified++;               
                    $this->pdf->addPage('L', 'LETTER', true);
                    $background = 'uploads/payroll_report/sss_monthly-1_web.jpg';
                    $this->pdf->SetAutoPageBreak(false, 0);
                    $this->pdf->Image($background, 10, 10, 260, 195, 'JPG', '', '', false, 100, '', false, false, 0, false, $page_hidden, false);
                    $counter = 1;                                       
                    if(!empty($company_sss_no))
                    {
                        $company_sss_x = 7.5;
                        $company_sss_no = str_replace("-", "", $company_sss_no);
                        for($c_s = 0 ; $c_s <= strlen($company_sss_no); $c_s++)
                        {
                            $company_sub = substr($company_sss_no, $c_s,1);
                            $this->pdf->MultiCell(10, 10, $company_sub, 0, 'C', false, 0, $company_sss_x, 37.5, true, 0, false, false, 0, 'T', true);   
                            $company_sss_x = $company_sss_x + 4.12;
                        } 
                    }   
                    if(strlen($company) > 72)
                    {
                        $company = substr($company, 0,72).'...';
                    }
                    $this->pdf->MultiCell(148, 10, $company, 0, 'C', false, 0, 63.5, 37.5, true, 0, false, false, 0, 'T', true);
                    $quarter_end_x = 242.5;
                    for($q_e = 0 ; $q_e <= strlen($quarter_end); $q_e++)
                    {
                        $quarter_end_sub = substr($quarter_end, $q_e,1);
                        $this->pdf->MultiCell(10, 10, $quarter_end_sub, 0, 'C', false, 0, $quarter_end_x, 37.5, true, 0, false, false, 0, 'T', true);   
                        $quarter_end_x = $quarter_end_x + 4.12;
                    }         
                    if(strlen($company_telephone)  > 25)
                    {
                        $company_telephone = substr($company_telephone, 0,25).'...';
                    }
                    $this->pdf->MultiCell(54, 10, $company_telephone, 0, 'C', false, 0, 10, 48.5, true, 0, false, false, 0, 'T', true);
                    if(strlen($company_address) > 72)
                    {
                        $company_address = substr($company_address, 0,72).'...';
                    }
                    $this->pdf->MultiCell(148, 10, $company_address, 0, 'C', false, 0, 63.5, 48.5, true, 0, false, false, 0, 'T', true); 
                    switch (strlen($page_no_certified)) 
                    {
                        case 1:
                            $page_from_x = 257;
                            for($page_from = 0 ; $page_from <= strlen($page_no_certified); $page_from++)
                            {
                                $i_from = substr($page_no_certified, $page_from,1);
                                $this->pdf->MultiCell(10, 10, $i_from, 0, 'C', false, 0, $page_from_x, 160, true, 0, false, false, 0, 'T', true);   
                                $page_from_x = $page_from_x + 4.8;
                            }
                            break;
                        case 2:
                            $page_from_x = 253;
                            for($page_from = 0 ; $page_from <= strlen($page_no_certified); $page_from++)
                            {
                                $i_from = substr($page_no_certified, $page_from,1);
                                $this->pdf->MultiCell(10, 10, $i_from, 0, 'C', false, 0, $page_from_x, 160, true, 0, false, false, 0, 'T', true);   
                                $page_from_x = $page_from_x + 4;
                            }
                            break;
                        case 3:
                            $page_from_x = 249;
                            for($page_from = 0 ; $page_from <= strlen($page_no_certified); $page_from++)
                            {
                                $i_from = substr($page_no_certified, $page_from,1);
                                $this->pdf->MultiCell(10, 10, $i_from, 0, 'C', false, 0, $page_from_x, 160, true, 0, false, false, 0, 'T', true);   
                                $page_from_x = $page_from_x + 4;
                            }
                            break;
                    }
                    switch (strlen($number_of_page)) 
                    {
                        case 1:
                            $page_to_x = 257;
                            for($page_to = 0 ; $page_to <= strlen($number_of_page); $page_to++)
                            {
                                $i_to = substr($number_of_page, $page_to,1);
                                $this->pdf->MultiCell(10, 10, $i_to, 0, 'C', false, 0, $page_to_x, 169.5, true, 0, false, false, 0, 'T', true);   
                                $page_to_x = $page_to_x + 4.8;
                            }
                            break;
                        case 2:
                            $page_to_x = 253;
                            for($page_to = 0 ; $page_to <= strlen($number_of_page); $page_to++)
                            {
                                $i_to = substr($number_of_page, $page_to,1);
                                $this->pdf->MultiCell(10, 10, $i_to, 0, 'C', false, 0, $page_to_x, 169.5, true, 0, false, false, 0, 'T', true);   
                                $page_to_x = $page_to_x + 4;
                            }
                            break;
                        case 3:
                            $page_to_x = 249;
                            for($page_to = 0 ; $page_to <= strlen($number_of_page); $page_to++)
                            {
                                $i_to = substr($number_of_page, $page_to,1);
                                $this->pdf->MultiCell(10, 10, $i_to, 0, 'C', false, 0, $page_to_x, 169.5, true, 0, false, false, 0, 'T', true);   
                                $page_to_x = $page_to_x + 4;
                            }
                            break;
                    }   
                    $employee_id = '';                  
                }
                $eeshare = $value->sss;
                $ershare = $value->sss_company;
                $ec = floor($value->sss_ec);
                $sss = $eeshare + $ershare;
                if($employee_id != $value->employee_id)
                {
                    $user_sss_no = $value->sss_no;
                    if(!empty($user_sss_no))
                    {
                        $user_sss_no_x = 7.5;
                        $user_sss_no = str_replace("-", "", $user_sss_no);
                        for($c_s = 0 ; $c_s <= strlen($user_sss_no); $c_s++)
                        {
                            $user_sss_no_sub = substr($user_sss_no, $c_s,1);
                            $this->pdf->MultiCell(10, 10, $user_sss_no_sub, 0, 'C', false, 0, $user_sss_no_x, $line, true, 0, false, false, 0, 'T', true);   
                            $user_sss_no_x = $user_sss_no_x + 4;
                        } 
                    }   
                    $employee_name = $value->employee_name;
                    if(strlen($employee_name) > 57)
                    {
                        $employee_name = substr($employee_name, 0,57).'...';
                    }
                    $this->pdf->MultiCell(94, 10, ucwords($employee_name), 0, 'L', false, 0, 54, $line, true, 0, false, false, 0, 'T', true);                    
                }
                if(empty($employee_id))
                {
                    if(!empty($value->semister))
                    {
                        $month_num = date("m",strtotime($value->semister));
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
                        switch ($position_month) 
                        {
                            case 1:
                                switch (strlen($sss)) 
                                {
                                    case 5:
                                            $sss_x = 129;
                                            for($sss_s = 0 ; $sss_s <= strlen($sss); $sss_s++)
                                            {
                                                $sss_sub = substr($sss, $sss_s,1);
                                                $this->pdf->MultiCell(10, 10, $sss_sub, 0, 'C', false, 0, $sss_x, $line_amount, true, 0, false, false, 0, 'T', true);   
                                                $sss_x = $sss_x + 4.8;
                                            }
                                        break;
                                    case 4:
                                            $sss_x = 134;
                                            for($sss_s = 0 ; $sss_s <= strlen($sss); $sss_s++)
                                            {
                                                $sss_sub = substr($sss, $sss_s,1);
                                                $this->pdf->MultiCell(10, 10, $sss_sub, 0, 'C', false, 0, $sss_x, $line_amount, true, 0, false, false, 0, 'T', true);   
                                                $sss_x = $sss_x + 4.6;
                                            }
                                        break;
                                    case 3:
                                            $sss_x = 139;
                                            for($sss_s = 0 ; $sss_s <= strlen($sss); $sss_s++)
                                            {
                                                $sss_sub = substr($sss, $sss_s,1);
                                                $this->pdf->MultiCell(10, 10, $sss_sub, 0, 'C', false, 0, $sss_x, $line_amount, true, 0, false, false, 0, 'T', true);   
                                                $sss_x = $sss_x + 4.4;
                                            }
                                        break;
                                    case 2:
                                            $sss_x = 143.5;
                                            for($sss_s = 0 ; $sss_s <= strlen($sss); $sss_s++)
                                            {
                                                $sss_sub = substr($sss, $sss_s,1);
                                                $this->pdf->MultiCell(10, 10, $sss_sub, 0, 'C', false, 0, $sss_x, $line_amount, true, 0, false, false, 0, 'T', true);   
                                                $sss_x = $sss_x + 4.2;
                                            }
                                        break;
                                    case 1:
                                            $sss_x = 147.5;
                                            for($sss_s = 0 ; $sss_s <= strlen($sss); $sss_s++)
                                            {
                                                $sss_sub = substr($sss, $sss_s,1);
                                                $this->pdf->MultiCell(10, 10, $sss_sub, 0, 'C', false, 0, $sss_x, $line_amount, true, 0, false, false, 0, 'T', true);   
                                                $sss_x = $sss_x + 4.2;
                                            }
                                        break;
                                }
                                $sss_per_total_1st += $sss;
                                $sss_grand_total_1st += $sss;
                                switch (strlen($ec)) 
                                {
                                    case 3:
                                            $ec_x = 197.5;
                                            for($ec_s = 0 ; $ec_s <= strlen($ec); $ec_s++)
                                            {
                                                $ec_sub = substr($ec, $ec_s,1);
                                                $this->pdf->MultiCell(10, 10, $ec_sub, 0, 'C', false, 0, $ec_x, $line_amount, true, 0, false, false, 0, 'T', true);   
                                                $ec_x = $ec_x + 4.4;
                                            }
                                        break;
                                    case 2:
                                            $ec_x = 202;
                                            for($ec_s = 0 ; $ec_s <= strlen($ec); $ec_s++)
                                            {
                                                $ec_sub = substr($ec, $ec_s,1);
                                                $this->pdf->MultiCell(10, 10, $ec_sub, 0, 'C', false, 0, $ec_x, $line_amount, true, 0, false, false, 0, 'T', true);   
                                                $ec_x = $ec_x + 4.2;
                                            }
                                        break;
                                    case 1:
                                            $ec_x = 206.5;
                                            for($ec_s = 0 ; $ec_s <= strlen($ec); $ec_s++)
                                            {
                                                $ec_sub = substr($ec, $ec_s,1);
                                                $this->pdf->MultiCell(10, 10, $ec_sub, 0, 'C', false, 0, $ec_x, $line_amount, true, 0, false, false, 0, 'T', true);   
                                                $ec_x = $ec_x + 4.2;
                                            }
                                        break;
                                }
                                $ec_per_total_1st += $ec;
                                $ec_grand_total_1st += $ec;
                            break;
                            case 2:
                                switch (strlen($sss)) 
                                {
                                    case 5:
                                            $sss_x = 152;
                                            for($sss_s = 0 ; $sss_s <= strlen($sss); $sss_s++)
                                            {
                                                $sss_sub = substr($sss, $sss_s,1);
                                                $this->pdf->MultiCell(10, 10, $sss_sub, 0, 'C', false, 0, $sss_x, $line_amount, true, 0, false, false, 0, 'T', true);   
                                                $sss_x = $sss_x + 4.8;
                                            }
                                        break;
                                    case 4:
                                            $sss_x = 157;
                                            for($sss_s = 0 ; $sss_s <= strlen($sss); $sss_s++)
                                            {
                                                $sss_sub = substr($sss, $sss_s,1);
                                                $this->pdf->MultiCell(10, 10, $sss_sub, 0, 'C', false, 0, $sss_x, $line_amount, true, 0, false, false, 0, 'T', true);   
                                                $sss_x = $sss_x + 4.6;
                                            }
                                        break;
                                    case 3:
                                            $sss_x = 162;
                                            for($sss_s = 0 ; $sss_s <= strlen($sss); $sss_s++)
                                            {
                                                $sss_sub = substr($sss, $sss_s,1);
                                                $this->pdf->MultiCell(10, 10, $sss_sub, 0, 'C', false, 0, $sss_x, $line_amount, true, 0, false, false, 0, 'T', true);   
                                                $sss_x = $sss_x + 4.4;
                                            }
                                        break;
                                    case 2:
                                            $sss_x = 166.5;
                                            for($sss_s = 0 ; $sss_s <= strlen($sss); $sss_s++)
                                            {
                                                $sss_sub = substr($sss, $sss_s,1);
                                                $this->pdf->MultiCell(10, 10, $sss_sub, 0, 'C', false, 0, $sss_x, $line_amount, true, 0, false, false, 0, 'T', true);   
                                                $sss_x = $sss_x + 4.2;
                                            }
                                        break;
                                    case 1:
                                            $sss_x = 170.5;
                                            for($sss_s = 0 ; $sss_s <= strlen($sss); $sss_s++)
                                            {
                                                $sss_sub = substr($sss, $sss_s,1);
                                                $this->pdf->MultiCell(10, 10, $sss_sub, 0, 'C', false, 0, $sss_x, $line_amount, true, 0, false, false, 0, 'T', true);   
                                                $sss_x = $sss_x + 4.2;
                                            }
                                        break;
                                }
                                $sss_per_total_2nd += $sss;
                                $sss_grand_total_2nd += $sss;
                                switch (strlen($ec)) 
                                {
                                    case 3:
                                            $ec_x = 210.5;
                                            for($ec_s = 0 ; $ec_s <= strlen($ec); $ec_s++)
                                            {
                                                $ec_sub = substr($ec, $ec_s,1);
                                                $this->pdf->MultiCell(10, 10, $ec_sub, 0, 'C', false, 0, $ec_x, $line_amount, true, 0, false, false, 0, 'T', true);   
                                                $ec_x = $ec_x + 4;
                                            }
                                        break;
                                    case 2:
                                            $ec_x = 214;
                                            for($ec_s = 0 ; $ec_s <= strlen($ec); $ec_s++)
                                            {
                                                $ec_sub = substr($ec, $ec_s,1);
                                                $this->pdf->MultiCell(10, 10, $ec_sub, 0, 'C', false, 0, $ec_x, $line_amount, true, 0, false, false, 0, 'T', true);   
                                                $ec_x = $ec_x + 4.2;
                                            }
                                        break;
                                    case 1:
                                            $ec_x = 218;
                                            for($ec_s = 0 ; $ec_s <= strlen($ec); $ec_s++)
                                            {
                                                $ec_sub = substr($ec, $ec_s,1);
                                                $this->pdf->MultiCell(10, 10, $ec_sub, 0, 'C', false, 0, $ec_x, $line_amount, true, 0, false, false, 0, 'T', true);   
                                                $ec_x = $ec_x + 4.2;
                                            }
                                        break;
                                }
                                $ec_per_total_2nd += $ec;
                                $ec_grand_total_2nd += $ec;
                            break;
                            case 3:
                                switch (strlen($sss)) 
                                {
                                    case 5:
                                            $sss_x = 175;
                                            for($sss_s = 0 ; $sss_s <= strlen($sss); $sss_s++)
                                            {
                                                $sss_sub = substr($sss, $sss_s,1);
                                                $this->pdf->MultiCell(10, 10, $sss_sub, 0, 'C', false, 0, $sss_x, $line_amount, true, 0, false, false, 0, 'T', true);   
                                                $sss_x = $sss_x + 4.5;
                                            }
                                        break;
                                    case 4:
                                            $sss_x = 179.5;
                                            for($sss_s = 0 ; $sss_s <= strlen($sss); $sss_s++)
                                            {
                                                $sss_sub = substr($sss, $sss_s,1);
                                                $this->pdf->MultiCell(10, 10, $sss_sub, 0, 'C', false, 0, $sss_x, $line_amount, true, 0, false, false, 0, 'T', true);   
                                                $sss_x = $sss_x + 4.6;
                                            }
                                        break;
                                    case 3:
                                            $sss_x = 184;
                                            for($sss_s = 0 ; $sss_s <= strlen($sss); $sss_s++)
                                            {
                                                $sss_sub = substr($sss, $sss_s,1);
                                                $this->pdf->MultiCell(10, 10, $sss_sub, 0, 'C', false, 0, $sss_x, $line_amount, true, 0, false, false, 0, 'T', true);   
                                                $sss_x = $sss_x + 4.4;
                                            }
                                        break;
                                    case 2:
                                            $sss_x = 189;
                                            for($sss_s = 0 ; $sss_s <= strlen($sss); $sss_s++)
                                            {
                                                $sss_sub = substr($sss, $sss_s,1);
                                                $this->pdf->MultiCell(10, 10, $sss_sub, 0, 'C', false, 0, $sss_x, $line_amount, true, 0, false, false, 0, 'T', true);   
                                                $sss_x = $sss_x + 4.2;
                                            }
                                        break;
                                    case 1:
                                            $sss_x = 192.8;
                                            for($sss_s = 0 ; $sss_s <= strlen($sss); $sss_s++)
                                            {
                                                $sss_sub = substr($sss, $sss_s,1);
                                                $this->pdf->MultiCell(10, 10, $sss_sub, 0, 'C', false, 0, $sss_x, $line_amount, true, 0, false, false, 0, 'T', true);   
                                                $sss_x = $sss_x + 4.2;
                                            }
                                        break;
                                }
                                $sss_per_total_3rd += $sss;
                                $sss_grand_total_3rd += $sss;
                                switch (strlen($ec)) 
                                {
                                    case 3:
                                            $ec_x = 222.5;
                                            for($ec_s = 0 ; $ec_s <= strlen($ec); $ec_s++)
                                            {
                                                $ec_sub = substr($ec, $ec_s,1);
                                                $this->pdf->MultiCell(10, 10, $ec_sub, 0, 'C', false, 0, $ec_x, $line_amount, true, 0, false, false, 0, 'T', true);   
                                                $ec_x = $ec_x + 4;
                                            }
                                        break;
                                    case 2:
                                            $ec_x = 226;
                                            for($ec_s = 0 ; $ec_s <= strlen($ec); $ec_s++)
                                            {
                                                $ec_sub = substr($ec, $ec_s,1);
                                                $this->pdf->MultiCell(10, 10, $ec_sub, 0, 'C', false, 0, $ec_x, $line_amount, true, 0, false, false, 0, 'T', true);   
                                                $ec_x = $ec_x + 4.2;
                                            }
                                        break;
                                    case 1:
                                            $ec_x = 230.3;
                                            for($ec_s = 0 ; $ec_s <= strlen($ec); $ec_s++)
                                            {
                                                $ec_sub = substr($ec, $ec_s,1);
                                                $this->pdf->MultiCell(10, 10, $ec_sub, 0, 'C', false, 0, $ec_x, $line_amount, true, 0, false, false, 0, 'T', true);   
                                                $ec_x = $ec_x + 4.2;
                                            }
                                        break;
                                }
                                $ec_per_total_3rd += $ec;
                                $ec_grand_total_3rd += $ec;
                            break;
                        }
                    }                   
                }
                else
                {
                    if($employee_id != $value->employee_id)
                    {      
                        // $line_amount = $line_amount+5.85;
                        if(!empty($value->semister))
                        {
                            $month_num = date("m",strtotime($value->semister));
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
                            switch ($position_month) 
                            {
                                case 1:
                                    switch (strlen($sss)) 
                                    {
                                        case 5:
                                                $sss_x = 129;
                                                for($sss_s = 0 ; $sss_s <= strlen($sss); $sss_s++)
                                                {
                                                    $sss_sub = substr($sss, $sss_s,1);
                                                    $this->pdf->MultiCell(10, 10, $sss_sub, 0, 'C', false, 0, $sss_x, $line_amount, true, 0, false, false, 0, 'T', true);   
                                                    $sss_x = $sss_x + 4.8;
                                                }
                                            break;
                                        case 4:
                                                $sss_x = 134;
                                                for($sss_s = 0 ; $sss_s <= strlen($sss); $sss_s++)
                                                {
                                                    $sss_sub = substr($sss, $sss_s,1);
                                                    $this->pdf->MultiCell(10, 10, $sss_sub, 0, 'C', false, 0, $sss_x, $line_amount, true, 0, false, false, 0, 'T', true);   
                                                    $sss_x = $sss_x + 4.6;
                                                }
                                            break;
                                        case 3:
                                                $sss_x = 139;
                                                for($sss_s = 0 ; $sss_s <= strlen($sss); $sss_s++)
                                                {
                                                    $sss_sub = substr($sss, $sss_s,1);
                                                    $this->pdf->MultiCell(10, 10, $sss_sub, 0, 'C', false, 0, $sss_x, $line_amount, true, 0, false, false, 0, 'T', true);   
                                                    $sss_x = $sss_x + 4.4;
                                                }
                                            break;
                                        case 2:
                                                $sss_x = 143.5;
                                                for($sss_s = 0 ; $sss_s <= strlen($sss); $sss_s++)
                                                {
                                                    $sss_sub = substr($sss, $sss_s,1);
                                                    $this->pdf->MultiCell(10, 10, $sss_sub, 0, 'C', false, 0, $sss_x, $line_amount, true, 0, false, false, 0, 'T', true);   
                                                    $sss_x = $sss_x + 4.2;
                                                }
                                            break;
                                        case 1:
                                                $sss_x = 147.5;
                                                for($sss_s = 0 ; $sss_s <= strlen($sss); $sss_s++)
                                                {
                                                    $sss_sub = substr($sss, $sss_s,1);
                                                    $this->pdf->MultiCell(10, 10, $sss_sub, 0, 'C', false, 0, $sss_x, $line_amount, true, 0, false, false, 0, 'T', true);   
                                                    $sss_x = $sss_x + 4.2;
                                                }
                                            break;
                                    }
                                    $sss_per_total_1st += $sss;
                                    $sss_grand_total_1st += $sss;
                                    switch (strlen($ec)) 
                                    {
                                        case 3:
                                                $ec_x = 197.5;
                                                for($ec_s = 0 ; $ec_s <= strlen($ec); $ec_s++)
                                                {
                                                    $ec_sub = substr($ec, $ec_s,1);
                                                    $this->pdf->MultiCell(10, 10, $ec_sub, 0, 'C', false, 0, $ec_x, $line_amount, true, 0, false, false, 0, 'T', true);   
                                                    $ec_x = $ec_x + 4.4;
                                                }
                                            break;
                                        case 2:
                                                $ec_x = 202;
                                                for($ec_s = 0 ; $ec_s <= strlen($ec); $ec_s++)
                                                {
                                                    $ec_sub = substr($ec, $ec_s,1);
                                                    $this->pdf->MultiCell(10, 10, $ec_sub, 0, 'C', false, 0, $ec_x, $line_amount, true, 0, false, false, 0, 'T', true);   
                                                    $ec_x = $ec_x + 4.2;
                                                }
                                            break;
                                        case 1:
                                                $ec_x = 206.5;
                                                for($ec_s = 0 ; $ec_s <= strlen($ec); $ec_s++)
                                                {
                                                    $ec_sub = substr($ec, $ec_s,1);
                                                    $this->pdf->MultiCell(10, 10, $ec_sub, 0, 'C', false, 0, $ec_x, $line_amount, true, 0, false, false, 0, 'T', true);   
                                                    $ec_x = $ec_x + 4.2;
                                                }
                                            break;
                                    }
                                    $ec_per_total_1st += $ec;
                                    $ec_grand_total_1st += $ec;
                                break;
                                case 2:
                                    switch (strlen($sss)) 
                                    {
                                        case 5:
                                                $sss_x = 152;
                                                for($sss_s = 0 ; $sss_s <= strlen($sss); $sss_s++)
                                                {
                                                    $sss_sub = substr($sss, $sss_s,1);
                                                    $this->pdf->MultiCell(10, 10, $sss_sub, 0, 'C', false, 0, $sss_x, $line_amount, true, 0, false, false, 0, 'T', true);   
                                                    $sss_x = $sss_x + 4.8;
                                                }
                                            break;
                                        case 4:
                                                $sss_x = 157;
                                                for($sss_s = 0 ; $sss_s <= strlen($sss); $sss_s++)
                                                {
                                                    $sss_sub = substr($sss, $sss_s,1);
                                                    $this->pdf->MultiCell(10, 10, $sss_sub, 0, 'C', false, 0, $sss_x, $line_amount, true, 0, false, false, 0, 'T', true);   
                                                    $sss_x = $sss_x + 4.6;
                                                }
                                            break;
                                        case 3:
                                                $sss_x = 162;
                                                for($sss_s = 0 ; $sss_s <= strlen($sss); $sss_s++)
                                                {
                                                    $sss_sub = substr($sss, $sss_s,1);
                                                    $this->pdf->MultiCell(10, 10, $sss_sub, 0, 'C', false, 0, $sss_x, $line_amount, true, 0, false, false, 0, 'T', true);   
                                                    $sss_x = $sss_x + 4.4;
                                                }
                                            break;
                                        case 2:
                                                $sss_x = 166.5;
                                                for($sss_s = 0 ; $sss_s <= strlen($sss); $sss_s++)
                                                {
                                                    $sss_sub = substr($sss, $sss_s,1);
                                                    $this->pdf->MultiCell(10, 10, $sss_sub, 0, 'C', false, 0, $sss_x, $line_amount, true, 0, false, false, 0, 'T', true);   
                                                    $sss_x = $sss_x + 4.2;
                                                }
                                            break;
                                        case 1:
                                                $sss_x = 170.5;
                                                for($sss_s = 0 ; $sss_s <= strlen($sss); $sss_s++)
                                                {
                                                    $sss_sub = substr($sss, $sss_s,1);
                                                    $this->pdf->MultiCell(10, 10, $sss_sub, 0, 'C', false, 0, $sss_x, $line_amount, true, 0, false, false, 0, 'T', true);   
                                                    $sss_x = $sss_x + 4.2;
                                                }
                                            break;
                                    }
                                    $sss_per_total_2nd += $sss;
                                    $sss_grand_total_2nd += $sss;
                                    switch (strlen($ec)) 
                                    {
                                        case 3:
                                                $ec_x = 210.5;
                                                for($ec_s = 0 ; $ec_s <= strlen($ec); $ec_s++)
                                                {
                                                    $ec_sub = substr($ec, $ec_s,1);
                                                    $this->pdf->MultiCell(10, 10, $ec_sub, 0, 'C', false, 0, $ec_x, $line_amount, true, 0, false, false, 0, 'T', true);   
                                                    $ec_x = $ec_x + 4;
                                                }
                                            break;
                                        case 2:
                                                $ec_x = 214;
                                                for($ec_s = 0 ; $ec_s <= strlen($ec); $ec_s++)
                                                {
                                                    $ec_sub = substr($ec, $ec_s,1);
                                                    $this->pdf->MultiCell(10, 10, $ec_sub, 0, 'C', false, 0, $ec_x, $line_amount, true, 0, false, false, 0, 'T', true);   
                                                    $ec_x = $ec_x + 4.2;
                                                }
                                            break;
                                        case 1:
                                                $ec_x = 218;
                                                for($ec_s = 0 ; $ec_s <= strlen($ec); $ec_s++)
                                                {
                                                    $ec_sub = substr($ec, $ec_s,1);
                                                    $this->pdf->MultiCell(10, 10, $ec_sub, 0, 'C', false, 0, $ec_x, $line_amount, true, 0, false, false, 0, 'T', true);   
                                                    $ec_x = $ec_x + 4.2;
                                                }
                                            break;
                                    }
                                    $ec_per_total_2nd += $ec;
                                    $ec_grand_total_2nd += $ec;
                                break;
                                case 3:
                                    switch (strlen($sss)) 
                                    {
                                        case 5:
                                                $sss_x = 175;
                                                for($sss_s = 0 ; $sss_s <= strlen($sss); $sss_s++)
                                                {
                                                    $sss_sub = substr($sss, $sss_s,1);
                                                    $this->pdf->MultiCell(10, 10, $sss_sub, 0, 'C', false, 0, $sss_x, $line_amount, true, 0, false, false, 0, 'T', true);   
                                                    $sss_x = $sss_x + 4.5;
                                                }
                                            break;
                                        case 4:
                                                $sss_x = 179.5;
                                                for($sss_s = 0 ; $sss_s <= strlen($sss); $sss_s++)
                                                {
                                                    $sss_sub = substr($sss, $sss_s,1);
                                                    $this->pdf->MultiCell(10, 10, $sss_sub, 0, 'C', false, 0, $sss_x, $line_amount, true, 0, false, false, 0, 'T', true);   
                                                    $sss_x = $sss_x + 4.6;
                                                }
                                            break;
                                        case 3:
                                                $sss_x = 184;
                                                for($sss_s = 0 ; $sss_s <= strlen($sss); $sss_s++)
                                                {
                                                    $sss_sub = substr($sss, $sss_s,1);
                                                    $this->pdf->MultiCell(10, 10, $sss_sub, 0, 'C', false, 0, $sss_x, $line_amount, true, 0, false, false, 0, 'T', true);   
                                                    $sss_x = $sss_x + 4.4;
                                                }
                                            break;
                                        case 2:
                                                $sss_x = 189;
                                                for($sss_s = 0 ; $sss_s <= strlen($sss); $sss_s++)
                                                {
                                                    $sss_sub = substr($sss, $sss_s,1);
                                                    $this->pdf->MultiCell(10, 10, $sss_sub, 0, 'C', false, 0, $sss_x, $line_amount, true, 0, false, false, 0, 'T', true);   
                                                    $sss_x = $sss_x + 4.2;
                                                }
                                            break;
                                        case 1:
                                                $sss_x = 192.8;
                                                for($sss_s = 0 ; $sss_s <= strlen($sss); $sss_s++)
                                                {
                                                    $sss_sub = substr($sss, $sss_s,1);
                                                    $this->pdf->MultiCell(10, 10, $sss_sub, 0, 'C', false, 0, $sss_x, $line_amount, true, 0, false, false, 0, 'T', true);   
                                                    $sss_x = $sss_x + 4.2;
                                                }
                                            break;
                                    }
                                    $sss_per_total_3rd += $sss;
                                    $sss_grand_total_3rd += $sss;
                                    switch (strlen($ec)) 
                                    {
                                        case 3:
                                                $ec_x = 222.5;
                                                for($ec_s = 0 ; $ec_s <= strlen($ec); $ec_s++)
                                                {
                                                    $ec_sub = substr($ec, $ec_s,1);
                                                    $this->pdf->MultiCell(10, 10, $ec_sub, 0, 'C', false, 0, $ec_x, $line_amount, true, 0, false, false, 0, 'T', true);   
                                                    $ec_x = $ec_x + 4;
                                                }
                                            break;
                                        case 2:
                                                $ec_x = 226;
                                                for($ec_s = 0 ; $ec_s <= strlen($ec); $ec_s++)
                                                {
                                                    $ec_sub = substr($ec, $ec_s,1);
                                                    $this->pdf->MultiCell(10, 10, $ec_sub, 0, 'C', false, 0, $ec_x, $line_amount, true, 0, false, false, 0, 'T', true);   
                                                    $ec_x = $ec_x + 4.2;
                                                }
                                            break;
                                        case 1:
                                                $ec_x = 230.3;
                                                for($ec_s = 0 ; $ec_s <= strlen($ec); $ec_s++)
                                                {
                                                    $ec_sub = substr($ec, $ec_s,1);
                                                    $this->pdf->MultiCell(10, 10, $ec_sub, 0, 'C', false, 0, $ec_x, $line_amount, true, 0, false, false, 0, 'T', true);   
                                                    $ec_x = $ec_x + 4.2;
                                                }
                                            break;
                                    }
                                    $ec_per_total_3rd += $ec;
                                    $ec_grand_total_3rd += $ec;
                                break;
                            }
                        }                   
                    }
                    else
                    {
                        if(!empty($value->semister))
                        {
                            $month_num = date("m",strtotime($value->semister));
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
                            switch ($position_month) 
                            {
                                case 1:
                                    switch (strlen($sss)) 
                                    {
                                        case 5:
                                                $sss_x = 129;
                                                for($sss_s = 0 ; $sss_s <= strlen($sss); $sss_s++)
                                                {
                                                    $sss_sub = substr($sss, $sss_s,1);
                                                    $this->pdf->MultiCell(10, 10, $sss_sub, 0, 'C', false, 0, $sss_x, $line_amount, true, 0, false, false, 0, 'T', true);   
                                                    $sss_x = $sss_x + 4.8;
                                                }
                                            break;
                                        case 4:
                                                $sss_x = 134;
                                                for($sss_s = 0 ; $sss_s <= strlen($sss); $sss_s++)
                                                {
                                                    $sss_sub = substr($sss, $sss_s,1);
                                                    $this->pdf->MultiCell(10, 10, $sss_sub, 0, 'C', false, 0, $sss_x, $line_amount, true, 0, false, false, 0, 'T', true);   
                                                    $sss_x = $sss_x + 4.6;
                                                }
                                            break;
                                        case 3:
                                                $sss_x = 139;
                                                for($sss_s = 0 ; $sss_s <= strlen($sss); $sss_s++)
                                                {
                                                    $sss_sub = substr($sss, $sss_s,1);
                                                    $this->pdf->MultiCell(10, 10, $sss_sub, 0, 'C', false, 0, $sss_x, $line_amount, true, 0, false, false, 0, 'T', true);   
                                                    $sss_x = $sss_x + 4.4;
                                                }
                                            break;
                                        case 2:
                                                $sss_x = 143.5;
                                                for($sss_s = 0 ; $sss_s <= strlen($sss); $sss_s++)
                                                {
                                                    $sss_sub = substr($sss, $sss_s,1);
                                                    $this->pdf->MultiCell(10, 10, $sss_sub, 0, 'C', false, 0, $sss_x, $line_amount, true, 0, false, false, 0, 'T', true);   
                                                    $sss_x = $sss_x + 4.2;
                                                }
                                            break;
                                        case 1:
                                                $sss_x = 147.5;
                                                for($sss_s = 0 ; $sss_s <= strlen($sss); $sss_s++)
                                                {
                                                    $sss_sub = substr($sss, $sss_s,1);
                                                    $this->pdf->MultiCell(10, 10, $sss_sub, 0, 'C', false, 0, $sss_x, $line_amount, true, 0, false, false, 0, 'T', true);   
                                                    $sss_x = $sss_x + 4.2;
                                                }
                                            break;
                                    }
                                    $sss_per_total_1st += $sss;
                                    $sss_grand_total_1st += $sss;
                                    switch (strlen($ec)) 
                                    {
                                        case 3:
                                                $ec_x = 197.5;
                                                for($ec_s = 0 ; $ec_s <= strlen($ec); $ec_s++)
                                                {
                                                    $ec_sub = substr($ec, $ec_s,1);
                                                    $this->pdf->MultiCell(10, 10, $ec_sub, 0, 'C', false, 0, $ec_x, $line_amount, true, 0, false, false, 0, 'T', true);   
                                                    $ec_x = $ec_x + 4.4;
                                                }
                                            break;
                                        case 2:
                                                $ec_x = 202;
                                                for($ec_s = 0 ; $ec_s <= strlen($ec); $ec_s++)
                                                {
                                                    $ec_sub = substr($ec, $ec_s,1);
                                                    $this->pdf->MultiCell(10, 10, $ec_sub, 0, 'C', false, 0, $ec_x, $line_amount, true, 0, false, false, 0, 'T', true);   
                                                    $ec_x = $ec_x + 4.2;
                                                }
                                            break;
                                        case 1:
                                                $ec_x = 206.5;
                                                for($ec_s = 0 ; $ec_s <= strlen($ec); $ec_s++)
                                                {
                                                    $ec_sub = substr($ec, $ec_s,1);
                                                    $this->pdf->MultiCell(10, 10, $ec_sub, 0, 'C', false, 0, $ec_x, $line_amount, true, 0, false, false, 0, 'T', true);   
                                                    $ec_x = $ec_x + 4.2;
                                                }
                                            break;
                                    }
                                    $ec_per_total_1st += $ec;
                                    $ec_grand_total_1st += $ec;
                                break;
                                case 2:
                                    switch (strlen($sss)) 
                                    {
                                        case 5:
                                                $sss_x = 152;
                                                for($sss_s = 0 ; $sss_s <= strlen($sss); $sss_s++)
                                                {
                                                    $sss_sub = substr($sss, $sss_s,1);
                                                    $this->pdf->MultiCell(10, 10, $sss_sub, 0, 'C', false, 0, $sss_x, $line_amount, true, 0, false, false, 0, 'T', true);   
                                                    $sss_x = $sss_x + 4.8;
                                                }
                                            break;
                                        case 4:
                                                $sss_x = 157;
                                                for($sss_s = 0 ; $sss_s <= strlen($sss); $sss_s++)
                                                {
                                                    $sss_sub = substr($sss, $sss_s,1);
                                                    $this->pdf->MultiCell(10, 10, $sss_sub, 0, 'C', false, 0, $sss_x, $line_amount, true, 0, false, false, 0, 'T', true);   
                                                    $sss_x = $sss_x + 4.6;
                                                }
                                            break;
                                        case 3:
                                                $sss_x = 162;
                                                for($sss_s = 0 ; $sss_s <= strlen($sss); $sss_s++)
                                                {
                                                    $sss_sub = substr($sss, $sss_s,1);
                                                    $this->pdf->MultiCell(10, 10, $sss_sub, 0, 'C', false, 0, $sss_x, $line_amount, true, 0, false, false, 0, 'T', true);   
                                                    $sss_x = $sss_x + 4.4;
                                                }
                                            break;
                                        case 2:
                                                $sss_x = 166.5;
                                                for($sss_s = 0 ; $sss_s <= strlen($sss); $sss_s++)
                                                {
                                                    $sss_sub = substr($sss, $sss_s,1);
                                                    $this->pdf->MultiCell(10, 10, $sss_sub, 0, 'C', false, 0, $sss_x, $line_amount, true, 0, false, false, 0, 'T', true);   
                                                    $sss_x = $sss_x + 4.2;
                                                }
                                            break;
                                        case 1:
                                                $sss_x = 170.5;
                                                for($sss_s = 0 ; $sss_s <= strlen($sss); $sss_s++)
                                                {
                                                    $sss_sub = substr($sss, $sss_s,1);
                                                    $this->pdf->MultiCell(10, 10, $sss_sub, 0, 'C', false, 0, $sss_x, $line_amount, true, 0, false, false, 0, 'T', true);   
                                                    $sss_x = $sss_x + 4.2;
                                                }
                                            break;
                                    }
                                    $sss_per_total_2nd += $sss;
                                    $sss_grand_total_2nd += $sss;
                                    switch (strlen($ec)) 
                                    {
                                        case 3:
                                                $ec_x = 210.5;
                                                for($ec_s = 0 ; $ec_s <= strlen($ec); $ec_s++)
                                                {
                                                    $ec_sub = substr($ec, $ec_s,1);
                                                    $this->pdf->MultiCell(10, 10, $ec_sub, 0, 'C', false, 0, $ec_x, $line_amount, true, 0, false, false, 0, 'T', true);   
                                                    $ec_x = $ec_x + 4;
                                                }
                                            break;
                                        case 2:
                                                $ec_x = 214;
                                                for($ec_s = 0 ; $ec_s <= strlen($ec); $ec_s++)
                                                {
                                                    $ec_sub = substr($ec, $ec_s,1);
                                                    $this->pdf->MultiCell(10, 10, $ec_sub, 0, 'C', false, 0, $ec_x, $line_amount, true, 0, false, false, 0, 'T', true);   
                                                    $ec_x = $ec_x + 4.2;
                                                }
                                            break;
                                        case 1:
                                                $ec_x = 218;
                                                for($ec_s = 0 ; $ec_s <= strlen($ec); $ec_s++)
                                                {
                                                    $ec_sub = substr($ec, $ec_s,1);
                                                    $this->pdf->MultiCell(10, 10, $ec_sub, 0, 'C', false, 0, $ec_x, $line_amount, true, 0, false, false, 0, 'T', true);   
                                                    $ec_x = $ec_x + 4.2;
                                                }
                                            break;
                                    }
                                    $ec_per_total_2nd += $ec;
                                    $ec_grand_total_2nd += $ec;
                                break;
                                case 3:
                                    switch (strlen($sss)) 
                                    {
                                        case 5:
                                                $sss_x = 175;
                                                for($sss_s = 0 ; $sss_s <= strlen($sss); $sss_s++)
                                                {
                                                    $sss_sub = substr($sss, $sss_s,1);
                                                    $this->pdf->MultiCell(10, 10, $sss_sub, 0, 'C', false, 0, $sss_x, $line_amount, true, 0, false, false, 0, 'T', true);   
                                                    $sss_x = $sss_x + 4.5;
                                                }
                                            break;
                                        case 4:
                                                $sss_x = 179.5;
                                                for($sss_s = 0 ; $sss_s <= strlen($sss); $sss_s++)
                                                {
                                                    $sss_sub = substr($sss, $sss_s,1);
                                                    $this->pdf->MultiCell(10, 10, $sss_sub, 0, 'C', false, 0, $sss_x, $line_amount, true, 0, false, false, 0, 'T', true);   
                                                    $sss_x = $sss_x + 4.6;
                                                }
                                            break;
                                        case 3:
                                                $sss_x = 184;
                                                for($sss_s = 0 ; $sss_s <= strlen($sss); $sss_s++)
                                                {
                                                    $sss_sub = substr($sss, $sss_s,1);
                                                    $this->pdf->MultiCell(10, 10, $sss_sub, 0, 'C', false, 0, $sss_x, $line_amount, true, 0, false, false, 0, 'T', true);   
                                                    $sss_x = $sss_x + 4.4;
                                                }
                                            break;
                                        case 2:
                                                $sss_x = 189;
                                                for($sss_s = 0 ; $sss_s <= strlen($sss); $sss_s++)
                                                {
                                                    $sss_sub = substr($sss, $sss_s,1);
                                                    $this->pdf->MultiCell(10, 10, $sss_sub, 0, 'C', false, 0, $sss_x, $line_amount, true, 0, false, false, 0, 'T', true);   
                                                    $sss_x = $sss_x + 4.2;
                                                }
                                            break;
                                        case 1:
                                                $sss_x = 192.8;
                                                for($sss_s = 0 ; $sss_s <= strlen($sss); $sss_s++)
                                                {
                                                    $sss_sub = substr($sss, $sss_s,1);
                                                    $this->pdf->MultiCell(10, 10, $sss_sub, 0, 'C', false, 0, $sss_x, $line_amount, true, 0, false, false, 0, 'T', true);   
                                                    $sss_x = $sss_x + 4.2;
                                                }
                                            break;
                                    }
                                    $sss_per_total_3rd += $sss;
                                    $sss_grand_total_3rd += $sss;
                                    switch (strlen($ec)) 
                                    {
                                        case 3:
                                                $ec_x = 222.5;
                                                for($ec_s = 0 ; $ec_s <= strlen($ec); $ec_s++)
                                                {
                                                    $ec_sub = substr($ec, $ec_s,1);
                                                    $this->pdf->MultiCell(10, 10, $ec_sub, 0, 'C', false, 0, $ec_x, $line_amount, true, 0, false, false, 0, 'T', true);   
                                                    $ec_x = $ec_x + 4;
                                                }
                                            break;
                                        case 2:
                                                $ec_x = 226;
                                                for($ec_s = 0 ; $ec_s <= strlen($ec); $ec_s++)
                                                {
                                                    $ec_sub = substr($ec, $ec_s,1);
                                                    $this->pdf->MultiCell(10, 10, $ec_sub, 0, 'C', false, 0, $ec_x, $line_amount, true, 0, false, false, 0, 'T', true);   
                                                    $ec_x = $ec_x + 4.2;
                                                }
                                            break;
                                        case 1:
                                                $ec_x = 230.3;
                                                for($ec_s = 0 ; $ec_s <= strlen($ec); $ec_s++)
                                                {
                                                    $ec_sub = substr($ec, $ec_s,1);
                                                    $this->pdf->MultiCell(10, 10, $ec_sub, 0, 'C', false, 0, $ec_x, $line_amount, true, 0, false, false, 0, 'T', true);   
                                                    $ec_x = $ec_x + 4.2;
                                                }
                                            break;
                                    }
                                    $ec_per_total_3rd += $ec;
                                    $ec_grand_total_3rd += $ec;
                                break;
                            }
                        }                   
                    }
                }
                if($counter > $allowed_count_per_page)
                {
                    $employee_id = '';     
                }
                else
                {
                    $employee_id = $value->employee_id;
                }
            }
        }    
        if($page_no_certified == $number_of_page)
        {
            if($value_count != $allowed_count_per_page)
            {
                $value_count_2 = $value_count + 1;
                if($value_count_2 == 1) {
                    $line = 66.5;
                } elseif($value_count_2 == 2) {
                    $line = 72.5;
                } elseif($value_count_2 == 3) {
                    $line = 78;
                } elseif($value_count_2 == 4) {
                    $line = 84;
                } elseif($value_count_2 == 5) {
                    $line = 90;
                } elseif($value_count_2 == 6) {
                    $line = 96;
                } elseif($value_count_2 == 7) {
                    $line = 101;
                } elseif($value_count_2 == 8) {
                    $line = 107;
                } elseif($value_count_2 == 9) {
                    $line = 112.5;
                } elseif($value_count_2 == 10) {
                    $line = 118.5;
                } elseif($value_count_2 == 11) {
                    $line = 124.5;
                } elseif($value_count_2 == 12) {
                    $line = 131;
                } elseif($value_count_2 == 13) {
                    $line = 138;
                } elseif($value_count_2 == 14) {
                    $line = 144;
                } else {
                    $line = 149.5;
                }
                $this->pdf->MultiCell(94, 10, '- - - - -      N O T H I N G  F O L L O W S      - - - - -', 0, 'L', false, 0, 54, $line, true, 0, false, false, 0, 'T', true); 
            }
            $line_per = 154.5;
            switch (strlen($sss_per_total_1st)) 
            {
                case 5:
                        $sss_x_per = 129;
                        for($sss_per = 0 ; $sss_per <= strlen($sss_per_total_1st); $sss_per++)
                        {
                            $sss_sub_per = substr($sss_per_total_1st, $sss_per,1);
                            $this->pdf->MultiCell(10, 10, $sss_sub_per, 0, 'C', false, 0, $sss_x_per, $line_per, true, 0, false, false, 0, 'T', true);   
                            $sss_x_per = $sss_x_per + 4.8;
                        }
                    break;
                case 4:
                        $sss_x_per = 134;
                        for($sss_per = 0 ; $sss_per <= strlen($sss_per_total_1st); $sss_per++)
                        {
                            $sss_sub_per = substr($sss_per_total_1st, $sss_per,1);
                            $this->pdf->MultiCell(10, 10, $sss_sub_per, 0, 'C', false, 0, $sss_x_per, $line_per, true, 0, false, false, 0, 'T', true);   
                            $sss_x_per = $sss_x_per + 4.6;
                        }
                    break;
                case 3:
                        $sss_x_per = 139;
                        for($sss_per = 0 ; $sss_per <= strlen($sss_per_total_1st); $sss_per++)
                        {
                            $sss_sub_per = substr($sss_per_total_1st, $sss_per,1);
                            $this->pdf->MultiCell(10, 10, $sss_sub_per, 0, 'C', false, 0, $sss_x_per, $line_per, true, 0, false, false, 0, 'T', true);   
                            $sss_x_per = $sss_x_per + 4.4;
                        }
                    break;
                case 2:
                        $sss_x_per = 143.5;
                        for($sss_per = 0 ; $sss_per <= strlen($sss_per_total_1st); $sss_per++)
                        {
                            $sss_sub_per = substr($sss_per_total_1st, $sss_s,1);
                            $this->pdf->MultiCell(10, 10, $sss_sub_per, 0, 'C', false, 0, $sss_x_per, $line_per, true, 0, false, false, 0, 'T', true);   
                            $sss_x_per = $sss_x_per + 4.2;
                        }
                    break;
                case 1:
                        $sss_x_per = 147.5;
                        for($sss_per = 0 ; $sss_per <= strlen($sss_per_total_1st); $sss_per++)
                        {
                            $sss_sub_per = substr($sss_per_total_1st, $sss_per,1);
                            $this->pdf->MultiCell(10, 10, $sss_sub_per, 0, 'C', false, 0, $sss_x_per, $line_per, true, 0, false, false, 0, 'T', true);   
                            $sss_x_per = $sss_x_per + 4.2;
                        }
                    break;
            }
            switch (strlen($ec_per_total_1st)) 
            {
                case 3:
                        $ec_x_per = 197.5;
                        for($ec_s_per = 0 ; $ec_s_per <= strlen($ec_per_total_1st); $ec_s_per++)
                        {
                            $ec_sub_per = substr($ec_per_total_1st, $ec_s_per,1);
                            $this->pdf->MultiCell(10, 10, $ec_sub_per, 0, 'C', false, 0, $ec_x_per, $line_per, true, 0, false, false, 0, 'T', true);   
                            $ec_x_per = $ec_x_per + 4.4;
                        }
                    break;
                case 2:
                        $ec_x_per = 202;
                        for($ec_s_per = 0 ; $ec_s_per <= strlen($ec_per_total_1st); $ec_s_per++)
                        {
                            $ec_sub_per = substr($ec_per_total_1st, $ec_s_per,1);
                            $this->pdf->MultiCell(10, 10, $ec_sub_per, 0, 'C', false, 0, $ec_x_per, $line_per, true, 0, false, false, 0, 'T', true);   
                            $ec_x_per = $ec_x_per + 4.2;
                        }
                    break;
                case 1:
                        $ec_x_per = 206.5;
                        for($ec_s_per = 0 ; $ec_s_per <= strlen($ec_per_total_1st); $ec_s_per++)
                        {
                            $ec_sub_per = substr($ec_per_total_1st, $ec_s_per,1);
                            $this->pdf->MultiCell(10, 10, $ec_sub_per, 0, 'C', false, 0, $ec_x_per, $line_per, true, 0, false, false, 0, 'T', true);   
                            $ec_x_per = $ec_x_per + 4.2;
                        }
                    break;
            }
            switch (strlen($sss_per_total_2nd)) 
            {
                case 5:
                        $sss_x_per = 152;
                        for($sss_s_per = 0 ; $sss_s_per <= strlen($sss_per_total_2nd); $sss_s_per++)
                        {
                            $sss_sub_per = substr($sss_per_total_2nd, $sss_s_per,1);
                            $this->pdf->MultiCell(10, 10, $sss_sub_per, 0, 'C', false, 0, $sss_x_per, $line_per, true, 0, false, false, 0, 'T', true);   
                            $sss_x_per = $sss_x_per + 4.8;
                        }
                    break;
                case 4:
                        $sss_x_per = 157;
                        for($sss_s_per = 0 ; $sss_s_per <= strlen($sss_per_total_2nd); $sss_s_per++)
                        {
                            $sss_sub_per = substr($sss_per_total_2nd, $sss_s_per,1);
                            $this->pdf->MultiCell(10, 10, $sss_sub_per, 0, 'C', false, 0, $sss_x_per, $line_per, true, 0, false, false, 0, 'T', true);   
                            $sss_x_per = $sss_x_per + 4.6;
                        }
                    break;
                case 3:
                        $sss_x_per = 162;
                        for($sss_s_per = 0 ; $sss_s_per <= strlen($sss_per_total_2nd); $sss_s_per++)
                        {
                            $sss_sub_per = substr($sss_per_total_2nd, $sss_s_per,1);
                            $this->pdf->MultiCell(10, 10, $sss_sub_per, 0, 'C', false, 0, $sss_x_per, $line_per, true, 0, false, false, 0, 'T', true);   
                            $sss_x_per = $sss_x_per + 4.4;
                        }
                    break;
                case 2:
                        $sss_x_per = 166.5;
                        for($sss_s_per = 0 ; $sss_s_per <= strlen($sss_per_total_2nd); $sss_s_per++)
                        {
                            $sss_sub_per = substr($sss_per_total_2nd, $sss_s_per,1);
                            $this->pdf->MultiCell(10, 10, $sss_sub_per, 0, 'C', false, 0, $sss_x_per, $line_per, true, 0, false, false, 0, 'T', true);   
                            $sss_x_per = $sss_x_per + 4.2;
                        }
                    break;
                case 1:
                        $sss_x_per = 170.5;
                        for($sss_s_per = 0 ; $sss_s_per <= strlen($sss_per_total_2nd); $sss_s_per++)
                        {
                            $sss_sub_per = substr($sss_per_total_2nd, $sss_s_per,1);
                            $this->pdf->MultiCell(10, 10, $sss_sub_per, 0, 'C', false, 0, $sss_x_per, $line_per, true, 0, false, false, 0, 'T', true);   
                            $sss_x_per = $sss_x_per + 4.2;
                        }
                    break;
            }
            switch (strlen($ec_per_total_2nd)) 
            {
                case 3:
                        $ec_x_per = 210.5;
                        for($ec_s_per = 0 ; $ec_s_per <= strlen($ec_per_total_2nd); $ec_s_per++)
                        {
                            $ec_sub_per = substr($ec_per_total_2nd, $ec_s_per,1);
                            $this->pdf->MultiCell(10, 10, $ec_sub_per, 0, 'C', false, 0, $ec_x_per, $line_per, true, 0, false, false, 0, 'T', true);   
                            $ec_x_per = $ec_x_per + 4;
                        }
                    break;
                case 2:
                        $ec_x_per = 214;
                        for($ec_s_per = 0 ; $ec_s_per <= strlen($ec_per_total_2nd); $ec_s_per++)
                        {
                            $ec_sub_per = substr($ec_per_total_2nd, $ec_s_per,1);
                            $this->pdf->MultiCell(10, 10, $ec_sub_per, 0, 'C', false, 0, $ec_x_per, $line_per, true, 0, false, false, 0, 'T', true);   
                            $ec_x_per = $ec_x_per + 4.2;
                        }
                    break;
                case 1:
                        $ec_x_per = 218;
                        for($ec_s_per = 0 ; $ec_s_per <= strlen($ec_per_total_2nd); $ec_s_per++)
                        {
                            $ec_sub_per = substr($ec_per_total_2nd, $ec_s_per,1);
                            $this->pdf->MultiCell(10, 10, $ec_sub_per, 0, 'C', false, 0, $ec_x_per, $line_per, true, 0, false, false, 0, 'T', true);   
                            $ec_x_per = $ec_x_per + 4.2;
                        }
                    break;
            }
            switch (strlen($sss_per_total_3rd)) 
            {
                case 5:
                        $sss_x_per = 175;
                        for($sss_s_per = 0 ; $sss_s_per <= strlen($sss_per_total_3rd); $sss_s_per++)
                        {
                            $sss_sub_per = substr($sss_per_total_3rd, $sss_s_per,1);
                            $this->pdf->MultiCell(10, 10, $sss_sub_per, 0, 'C', false, 0, $sss_x_per, $line_per, true, 0, false, false, 0, 'T', true);   
                            $sss_x_per = $sss_x_per + 4.5;
                        }
                    break;
                case 4:
                        $sss_x_per = 179.5;
                        for($sss_s_per = 0 ; $sss_s_per <= strlen($sss_per_total_3rd); $sss_s_per++)
                        {
                            $sss_sub_per = substr($sss_per_total_3rd, $sss_s_per,1);
                            $this->pdf->MultiCell(10, 10, $sss_sub_per, 0, 'C', false, 0, $sss_x_per, $line_per, true, 0, false, false, 0, 'T', true);   
                            $sss_x_per = $sss_x_per + 4.6;
                        }
                    break;
                case 3:
                        $sss_x_per = 184;
                        for($sss_s_per = 0 ; $sss_s_per <= strlen($sss_per_total_3rd); $sss_s_per++)
                        {
                            $sss_sub_per = substr($sss_per_total_3rd, $sss_s_per,1);
                            $this->pdf->MultiCell(10, 10, $sss_sub_per, 0, 'C', false, 0, $sss_x_per, $line_per, true, 0, false, false, 0, 'T', true);   
                            $sss_x_per = $sss_x_per + 4.4;
                        }
                    break;
                case 2:
                        $sss_x_per = 189;
                        for($sss_s_per = 0 ; $sss_s_per <= strlen($sss_per_total_3rd); $sss_s_per++)
                        {
                            $sss_sub_per = substr($sss_per_total_3rd, $sss_s_per,1);
                            $this->pdf->MultiCell(10, 10, $sss_sub_per, 0, 'C', false, 0, $sss_x_per, $line_per, true, 0, false, false, 0, 'T', true);   
                            $sss_x_per = $sss_x_per + 4.2;
                        }
                    break;
                case 1:
                        $sss_x_per = 192.8;
                        for($sss_s_per = 0 ; $sss_s_per <= strlen($sss_per_total_3rd); $sss_s_per++)
                        {
                            $sss_sub_per = substr($sss_per_total_3rd, $sss_s_per,1);
                            $this->pdf->MultiCell(10, 10, $sss_sub_per, 0, 'C', false, 0, $sss_x_per, $line_per, true, 0, false, false, 0, 'T', true);   
                            $sss_x_per = $sss_x_per + 4.2;
                        }
                    break;
            }
            switch (strlen($ec_per_total_3rd)) 
            {
                case 3:
                        $ec_x_per = 222.5;
                        for($ec_s_per = 0 ; $ec_s_per <= strlen($ec_per_total_3rd); $ec_s_per++)
                        {
                            $ec_sub_per = substr($ec_per_total_3rd, $ec_s_per,1);
                            $this->pdf->MultiCell(10, 10, $ec_sub_per, 0, 'C', false, 0, $ec_x_per, $line_per, true, 0, false, false, 0, 'T', true);   
                            $ec_x_per = $ec_x_per + 4;
                        }
                    break;
                case 2:
                        $ec_x_per = 226;
                        for($ec_s = 0 ; $ec_s <= strlen($ec_per_total_3rd); $ec_s++)
                        {
                            $ec_sub_per = substr($ec_per_total_3rd, $ec_s,1);
                            $this->pdf->MultiCell(10, 10, $ec_sub_per, 0, 'C', false, 0, $ec_x_per, $line_per, true, 0, false, false, 0, 'T', true);   
                            $ec_x_per = $ec_x_per + 4.2;
                        }
                    break;
                case 1:
                        $ec_x_per = 230.3;
                        for($ec_s_per = 0 ; $ec_s_per <= strlen($ec_per_total_3rd); $ec_s_per++)
                        {
                            $ec_sub_per = substr($ec_per_total_3rd, $ec_s_per,1);
                            $this->pdf->MultiCell(10, 10, $ec_sub_per, 0, 'C', false, 0, $ec_x_per, $line_per, true, 0, false, false, 0, 'T', true);   
                            $ec_x_per = $ec_x_per + 4.2;
                        }
                    break;
            }
            $this->pdf->MultiCell(25.5, 10, number_format($sss_grand_total_1st,2,'.',','), 0, 'R', false, 0, 23, 167.2, true, 0, false, false, 0, 'T', true);
            $this->pdf->MultiCell(22, 10, number_format($ec_grand_total_1st,2,'.',','), 0, 'R', false, 0, 47.5, 167.2, true, 0, false, false, 0, 'T', true);
            $this->pdf->MultiCell(24, 10, number_format($sss_grand_total_1st+$ec_grand_total_1st,2,'.',','), 0, 'R', false, 0, 68.5, 167.2, true, 0, false, false, 0, 'T', true);
            $this->pdf->MultiCell(25.5, 10, number_format($sss_grand_total_2nd,2,'.',','), 0, 'R', false, 0, 23, 171.5, true, 0, false, false, 0, 'T', true);
            $this->pdf->MultiCell(22, 10, number_format($ec_grand_total_2nd,2,'.',','), 0, 'R', false, 0, 47.5, 171.5, true, 0, false, false, 0, 'T', true);
            $this->pdf->MultiCell(24, 10, number_format($sss_grand_total_2nd+$ec_grand_total_2nd,2,'.',','), 0, 'R', false, 0, 68.5, 171.5, true, 0, false, false, 0, 'T', true);
            $this->pdf->MultiCell(25.5, 10, number_format($sss_grand_total_3rd,2,'.',','), 0, 'R', false, 0, 23, 175.5, true, 0, false, false, 0, 'T', true);
            $this->pdf->MultiCell(22, 10, number_format($ec_grand_total_3rd,2,'.',','), 0, 'R', false, 0, 47.5, 175.5, true, 0, false, false, 0, 'T', true);
            $this->pdf->MultiCell(24, 10, number_format($sss_grand_total_3rd+$ec_grand_total_3rd,2,'.',','), 0, 'R', false, 0, 68.5, 175.5, true, 0, false, false, 0, 'T', true);
        }
    }

    function get_payroll_government($company_id, $employee_id, $date_from, $date_to, $title) {
        $qry = "SELECT 
              u.employee_id,
              CONCAT(
                u.lastname,
                ', ',
                u.firstname,
                ' ',
                u.middleinitial
              ) AS employee_name,
              e.sss AS sss_no,
              IFNULL(payroll_detail.sss, '0.000') AS sss,
              IFNULL(
                payroll_detail.sss_company,
                '0.000'
              ) AS sss_company,
              IFNULL(payroll_detail.sss_ec, '0.000') AS sss_ec,
              IFNULL(
                payroll_detail.sss_total,
                '0.000'
              ) AS sss_total,
              IFNULL(payroll_detail.semister, '') AS semister 
            FROM
              {$this->db->dbprefix}user u 
              INNER JOIN {$this->db->dbprefix}employee e 
                ON u.employee_id = e.employee_id 
              LEFT JOIN 
                (SELECT 
                  ec.employee_id,
                  ec.payroll_date AS semister,
                  SUM(ec.employee) AS sss,
                  SUM(ec.company) AS sss_company,
                  SUM(ec.ec) AS sss_ec,
                  SUM(ec.employee) + SUM(ec.company) + SUM(ec.ec) AS sss_total 
                FROM
                  {$this->db->dbprefix}employee_contribution ec 
                WHERE ec.payroll_date BETWEEN '{$date_from}' 
                  AND '{$date_to}' 
                  AND ec.transaction_id = '49' 
                GROUP BY ec.employee_id,
                  YEAR(ec.payroll_date),
                  MONTH(ec.payroll_date)) AS payroll_detail 
                ON payroll_detail.employee_id = u.employee_id 
            WHERE semister != '' ";
        if(!empty($employee_id)){ $qry .= " AND u.employee_id IN ({$employee_id})"; }
        if(!empty($company_id)){ $qry .= " AND u.company_id IN ({$company_id})"; }
        $qry .= "ORDER BY u.lastname,
              u.firstname,
              u.middleinitial,
              semister ASC ";
        $result = $this->db->query($qry)->result();
        return $result;
    }

    function get_payroll_government_counter($company_id, $employee_id, $date_from, $date_to, $title) {
        $qry = "SELECT 
              u.employee_id,
              CONCAT(
                u.lastname,
                ', ',
                u.firstname,
                ' ',
                u.middleinitial
              ) AS employee_name,
              e.sss AS sss_no,
              IFNULL(payroll_detail.sss, '0.000') AS sss,
              IFNULL(
                payroll_detail.sss_company,
                '0.000'
              ) AS sss_company,
              IFNULL(payroll_detail.sss_ec, '0.000') AS sss_ec,
              IFNULL(
                payroll_detail.sss_total,
                '0.000'
              ) AS sss_total,
              u.employee_id,
              IFNULL(payroll_detail.semister, '') AS semister 
            FROM
              {$this->db->dbprefix}user u 
              INNER JOIN {$this->db->dbprefix}employee e 
                ON u.employee_id = e.employee_id 
              LEFT JOIN 
                (SELECT 
                  ec.employee_id,
                  ec.payroll_date AS semister,
                  SUM(ec.employee) AS sss,
                  SUM(ec.company) AS sss_company,
                  SUM(ec.ec) AS sss_ec,
                  SUM(ec.employee) + SUM(ec.company) + SUM(ec.ec) AS sss_total 
                FROM
                  {$this->db->dbprefix}employee_contribution ec 
                WHERE ec.payroll_date BETWEEN '{$date_from}' 
                  AND '{$date_to}' 
                  AND ec.transaction_id = '49' 
                GROUP BY ec.employee_id) AS payroll_detail 
                ON payroll_detail.employee_id = u.employee_id 
            WHERE semister != '' ";
        if(!empty($employee_id)){ $qry .= " AND u.employee_id IN ({$employee_id})"; }
        if(!empty($company_id)){ $qry .= " AND u.company_id IN ({$company_id})"; }
        $qry .= "ORDER BY u.lastname,
              u.firstname,
              u.middleinitial,
              semister ASC ";
        $result = $this->db->query($qry);
        return $result;
    }

    function export_sss_monthly($company_id, $employee_id, $date_from, $date_to, $title)
    {
        ini_set("max_execution_time", 7200);
        ini_set("memory_limit", "1024M");
        $company_setting_res = $this->db->query('SELECT * FROM '.$this->db->dbprefix('user_company').' WHERE company_id = "'.$company_id.'"')->row();
        $result = $this->get_payroll_government_counter($company_id, $employee_id, $date_from, $date_to);
        $total_no_employees = $result->num_rows();
        $this->pdf->SetFontSize( 8 );
        $this->pdf->SetMargins(5, 5, 5);
        
        $company = $company_setting_res->company;
        $company_sss_no = $company_setting_res->sss_no;
        $company_address = $company_setting_res->address;
        $company_tin_no = $company_setting_res->vat_registration;
        $company_zip = $company_setting_res->zipcode;
        $company_telephone = $company_setting_res->telephone;
        if($total_no_employees != 0)
        {
            $t_sss_e_g = 0;
            $t_sss_c_g = 0;
            $t_sss_ec_g = 0;
            $t_sss_t_g = 0;            
            $t_sss_e = 0;
            $t_sss_c = 0;
            $t_sss_ec = 0;
            $t_sss_t = 0;
            $count = 0; 
            $cnt = 1;
            $page_cnt = 1;
            $cnt_page = 1;
            $xcel_dtl = array();
            $employee_id = '';
            foreach ($result->result() as $key => $value) 
            {    
                if(empty($employee_id)) {
                    $xcel_dtl[$page_cnt] = '<table style="width:100%;">';
                    $xcel_dtl[$page_cnt] .= '<tr>';
                    $xcel_dtl[$page_cnt] .= '<td style="width:5%;"></td>'; 
                    $xcel_dtl[$page_cnt] .= '<td style="width:25%;"></td>'; 
                    $xcel_dtl[$page_cnt] .= '<td style="width:15%;"></td>'; 
                    $xcel_dtl[$page_cnt] .= '<td style="width:15%;"></td>'; 
                    $xcel_dtl[$page_cnt] .= '<td style="width:15%;"></td>'; 
                    $xcel_dtl[$page_cnt] .= '<td style="width:10%;"></td>'; 
                    $xcel_dtl[$page_cnt] .= '<td style="width:15%;"></td>'; 
                    $xcel_dtl[$page_cnt] .= '</tr>';
                    $xcel_dtl[$page_cnt] .= '<tr><td colspan="6" style="font-size:30px;"></td><td colspan="1" style="text-align:right;font-size:50px;">Page '.$page_cnt.' of eto ang page total</td></tr>';
                    $xcel_dtl[$page_cnt] .= '<tr>';
                    $xcel_dtl[$page_cnt] .= '<td colspan="7" style="text-align:center;font-size:100px;">';
                    $xcel_dtl[$page_cnt] .=  $title.'<br>'.date("F",strtotime($date_from)).' '.date("Y",strtotime($date_from)).'<br>'.$company_setting_res->company;
                    $xcel_dtl[$page_cnt] .= '</td>';
                    $xcel_dtl[$page_cnt] .= '</tr>';
                    $xcel_dtl[$page_cnt] .= '<tr><td colspan="7" style="text-align:right;font-size:50px;">Rundate : '.date("F d, Y").'</td></tr>';
                    $xcel_dtl[$page_cnt] .= '<tr><td></td></tr>';        
                    $xcel_dtl[$page_cnt] .= '<tr>';
                    $xcel_dtl[$page_cnt] .= '<td colspan="2" style="text-align:center;background-color:#CCCCCC;border-top:1px solid black;border-left:1px solid black;border-right:1px solid black;border-bottom:1px solid black;">';
                    $xcel_dtl[$page_cnt] .= 'Name of Employee';
                    $xcel_dtl[$page_cnt] .= '</td>';
                    $xcel_dtl[$page_cnt] .= '<td style="text-align:center;background-color:#CCCCCC;border-top:1px solid black;border-left:1px solid black;border-right:1px solid black;border-bottom:1px solid black;">';
                    $xcel_dtl[$page_cnt] .= 'SSS Number';
                    $xcel_dtl[$page_cnt] .= '</td>';
                    $xcel_dtl[$page_cnt] .= '<td style="text-align:center;background-color:#CCCCCC;border-top:1px solid black;border-left:1px solid black;border-right:1px solid black;border-bottom:1px solid black;">';
                    $xcel_dtl[$page_cnt] .= 'Employee';   
                    $xcel_dtl[$page_cnt] .= '</td>';
                    $xcel_dtl[$page_cnt] .= '<td style="text-align:center;background-color:#CCCCCC;border-top:1px solid black;border-left:1px solid black;border-right:1px solid black;border-bottom:1px solid black;">';
                    $xcel_dtl[$page_cnt] .= 'Employer';        
                    $xcel_dtl[$page_cnt] .= '</td>';
                    $xcel_dtl[$page_cnt] .= '<td style="text-align:center;background-color:#CCCCCC;border-top:1px solid black;border-left:1px solid black;border-right:1px solid black;border-bottom:1px solid black;">';
                    $xcel_dtl[$page_cnt] .= 'EC';
                    $xcel_dtl[$page_cnt] .= '</td>';
                    $xcel_dtl[$page_cnt] .= '<td style="text-align:center;background-color:#CCCCCC;border-top:1px solid black;border-left:1px solid black;border-right:1px solid black;border-bottom:1px solid black;">';
                    $xcel_dtl[$page_cnt] .= 'Total';
                    $xcel_dtl[$page_cnt] .= '</td>';
                    $xcel_dtl[$page_cnt] .= '</tr>';
                    $xcel_dtl[$page_cnt] .= '</table>';
                }
                if($cnt_page > 70) {
                    $xcel_dtl[$page_cnt] .= '<table style="width:100%;">';
                    $xcel_dtl[$page_cnt] .= '<tr>';
                    $xcel_dtl[$page_cnt] .= '<td colspan="3" style="text-align:left;background-color:#DDDDDD;"><strong>Page Total</strong></td>';  
                    $xcel_dtl[$page_cnt] .= '<td style="text-align:right;background-color:#DDDDDD;"><strong>'.number_format($t_sss_e,2,'.',',').'</strong></td>'; 
                    $xcel_dtl[$page_cnt] .= '<td style="text-align:right;background-color:#DDDDDD;"><strong>'.number_format($t_sss_c,2,'.',',').'</strong></td>'; 
                    $xcel_dtl[$page_cnt] .= '<td style="text-align:right;background-color:#DDDDDD;"><strong>'.number_format($t_sss_ec,2,'.',',').'</strong></td>'; 
                    $xcel_dtl[$page_cnt] .= '<td style="text-align:right;background-color:#DDDDDD;"><strong>'.number_format($t_sss_t,2,'.',',').'</strong></td>'; 
                    $xcel_dtl[$page_cnt] .= '</tr>';
                    $xcel_dtl[$page_cnt] .= '</table>';
                    $page_cnt = $page_cnt+1;        
                    $t_sss_e = 0;
                    $t_sss_c = 0;
                    $t_sss_ec = 0;
                    $t_sss_t = 0;
                    $cnt_page = 1;
                    $xcel_dtl[$page_cnt] = '<table style="width:100%;">';
                    $xcel_dtl[$page_cnt] .= '<tr>';
                    $xcel_dtl[$page_cnt] .= '<td style="width:5%;"></td>'; 
                    $xcel_dtl[$page_cnt] .= '<td style="width:25%;"></td>'; 
                    $xcel_dtl[$page_cnt] .= '<td style="width:15%;"></td>'; 
                    $xcel_dtl[$page_cnt] .= '<td style="width:15%;"></td>'; 
                    $xcel_dtl[$page_cnt] .= '<td style="width:15%;"></td>'; 
                    $xcel_dtl[$page_cnt] .= '<td style="width:10%;"></td>'; 
                    $xcel_dtl[$page_cnt] .= '<td style="width:15%;"></td>'; 
                    $xcel_dtl[$page_cnt] .= '</tr>';
                    $xcel_dtl[$page_cnt] .= '<tr><td colspan="6" style="font-size:30px;"></td><td colspan="1" style="text-align:right;font-size:50px;">Page '.$page_cnt.' of eto ang page total</td></tr>';
                    $xcel_dtl[$page_cnt] .= '<tr>';
                    $xcel_dtl[$page_cnt] .= '<td colspan="7" style="text-align:center;font-size:100px;">';
                    $xcel_dtl[$page_cnt] .=  $title.'<br>'.date("F",strtotime($date_from)).' '.date("Y",strtotime($date_from)).'<br>'.$company_setting_res->company;
                    $xcel_dtl[$page_cnt] .= '</td>';
                    $xcel_dtl[$page_cnt] .= '</tr>';
                    $xcel_dtl[$page_cnt] .= '<tr><td colspan="7" style="text-align:right;font-size:50px;">Rundate : '.date("F d, Y").'</td></tr>';
                    $xcel_dtl[$page_cnt] .= '<tr><td></td></tr>';        
                    $xcel_dtl[$page_cnt] .= '<tr>';
                    $xcel_dtl[$page_cnt] .= '<td colspan="2" style="text-align:center;background-color:#CCCCCC;border-top:1px solid black;border-left:1px solid black;border-right:1px solid black;border-bottom:1px solid black;">';
                    $xcel_dtl[$page_cnt] .= 'Name of Employee';
                    $xcel_dtl[$page_cnt] .= '</td>';
                    $xcel_dtl[$page_cnt] .= '<td style="text-align:center;background-color:#CCCCCC;border-top:1px solid black;border-left:1px solid black;border-right:1px solid black;border-bottom:1px solid black;">';
                    $xcel_dtl[$page_cnt] .= 'SSS Number';
                    $xcel_dtl[$page_cnt] .= '</td>';
                    $xcel_dtl[$page_cnt] .= '<td style="text-align:center;background-color:#CCCCCC;border-top:1px solid black;border-left:1px solid black;border-right:1px solid black;border-bottom:1px solid black;">';
                    $xcel_dtl[$page_cnt] .= 'Employee';   
                    $xcel_dtl[$page_cnt] .= '</td>';
                    $xcel_dtl[$page_cnt] .= '<td style="text-align:center;background-color:#CCCCCC;border-top:1px solid black;border-left:1px solid black;border-right:1px solid black;border-bottom:1px solid black;">';
                    $xcel_dtl[$page_cnt] .= 'Employer';        
                    $xcel_dtl[$page_cnt] .= '</td>';
                    $xcel_dtl[$page_cnt] .= '<td style="text-align:center;background-color:#CCCCCC;border-top:1px solid black;border-left:1px solid black;border-right:1px solid black;border-bottom:1px solid black;">';
                    $xcel_dtl[$page_cnt] .= 'EC';
                    $xcel_dtl[$page_cnt] .= '</td>';
                    $xcel_dtl[$page_cnt] .= '<td style="text-align:center;background-color:#CCCCCC;border-top:1px solid black;border-left:1px solid black;border-right:1px solid black;border-bottom:1px solid black;">';
                    $xcel_dtl[$page_cnt] .= 'Total';
                    $xcel_dtl[$page_cnt] .= '</td>';
                    $xcel_dtl[$page_cnt] .= '</tr>';
                    $xcel_dtl[$page_cnt] .= '</table>';
                }
                $xcel_dtl[$page_cnt] .= '<table style="width:100%">';        
                $xcel_dtl[$page_cnt] .= '<tr>';
                $xcel_dtl[$page_cnt] .= '<td style="text-align:left;width:5%;">'.$cnt++.'.</td>'; 
                $xcel_dtl[$page_cnt] .= '<td style="text-align:left;width:25%;">'.$value->employee_name.'</td>'; 
                $xcel_dtl[$page_cnt] .= '<td style="text-align:center;width:15%;">'.$value->sss_no.'</td>'; 
                $xcel_dtl[$page_cnt] .= '<td style="text-align:right;width:15%;">'.number_format($value->sss,2,'.',',').'</td>'; 
                $xcel_dtl[$page_cnt] .= '<td style="text-align:right;width:15%;">'.number_format($value->sss_company,2,'.',',').'</td>'; 
                $xcel_dtl[$page_cnt] .= '<td style="text-align:right;width:10%;">'.number_format($value->sss_ec,2,'.',',').'</td>'; 
                $xcel_dtl[$page_cnt] .= '<td style="text-align:right;width:15%;">'.number_format($value->sss_total,2,'.',',').'</td>'; 
                $xcel_dtl[$page_cnt] .= '</tr>';
                $xcel_dtl[$page_cnt] .= '</table>';
                $t_sss_e += $value->sss;
                $t_sss_c += $value->sss_company;
                $t_sss_ec += $value->sss_ec;
                $t_sss_t += $value->sss_total;
                $t_sss_e_g += $value->sss;
                $t_sss_c_g += $value->sss_company;
                $t_sss_ec_g += $value->sss_ec;
                $t_sss_t_g += $value->sss_total;
                $employee_id = $value->employee_id;
                $cnt_page++;
            }
            if($cnt_page < 70) {
                for ($i=$cnt_page; $i < 71; $i++) { 
                    $xcel_dtl[$page_cnt] .= '<table style="width:100%">';        
                    $xcel_dtl[$page_cnt] .= '<tr>';
                    $xcel_dtl[$page_cnt] .= '<td style="text-align:left;width:5%;">'.$cnt++.'.</td>'; 
                    $xcel_dtl[$page_cnt] .= '<td style="text-align:left;width:25%;">&nbsp;</td>'; 
                    $xcel_dtl[$page_cnt] .= '<td style="text-align:center;width:15%;">&nbsp;</td>'; 
                    $xcel_dtl[$page_cnt] .= '<td style="text-align:right;width:15%;">&nbsp;</td>'; 
                    $xcel_dtl[$page_cnt] .= '<td style="text-align:right;width:15%;">&nbsp;</td>'; 
                    $xcel_dtl[$page_cnt] .= '<td style="text-align:right;width:10%;">&nbsp;</td>'; 
                    $xcel_dtl[$page_cnt] .= '<td style="text-align:right;width:15%;">&nbsp;</td>'; 
                    $xcel_dtl[$page_cnt] .= '</tr>';
                    $xcel_dtl[$page_cnt] .= '</table>';
                }
            }
            $xcel_dtl[$page_cnt] .= '<table style="width:100%;">';
            $xcel_dtl[$page_cnt] .= '<tr>';
            $xcel_dtl[$page_cnt] .= '<td colspan="3" style="text-align:left;background-color:#DDDDDD;"><strong>Page Total</strong></td>';  
            $xcel_dtl[$page_cnt] .= '<td style="text-align:right;background-color:#DDDDDD;"><strong>'.number_format($t_sss_e,2,'.',',').'</strong></td>'; 
            $xcel_dtl[$page_cnt] .= '<td style="text-align:right;background-color:#DDDDDD;"><strong>'.number_format($t_sss_c,2,'.',',').'</strong></td>'; 
            $xcel_dtl[$page_cnt] .= '<td style="text-align:right;background-color:#DDDDDD;"><strong>'.number_format($t_sss_ec,2,'.',',').'</strong></td>'; 
            $xcel_dtl[$page_cnt] .= '<td style="text-align:right;background-color:#DDDDDD;"><strong>'.number_format($t_sss_t,2,'.',',').'</strong></td>'; 
            $xcel_dtl[$page_cnt] .= '</tr>';
            $xcel_dtl[$page_cnt] .= '<tr>';
            $xcel_dtl[$page_cnt] .= '<td colspan="3" style="text-align:left;background-color:#DDDDDD;"><strong>GrandTotal</strong></td>';  
            $xcel_dtl[$page_cnt] .= '<td style="text-align:right;background-color:#DDDDDD;"><strong>'.number_format($t_sss_e_g,2,'.',',').'</strong></td>'; 
            $xcel_dtl[$page_cnt] .= '<td style="text-align:right;background-color:#DDDDDD;"><strong>'.number_format($t_sss_c_g,2,'.',',').'</strong></td>'; 
            $xcel_dtl[$page_cnt] .= '<td style="text-align:right;background-color:#DDDDDD;"><strong>'.number_format($t_sss_ec_g,2,'.',',').'</strong></td>'; 
            $xcel_dtl[$page_cnt] .= '<td style="text-align:right;background-color:#DDDDDD;"><strong>'.number_format($t_sss_t_g,2,'.',',').'</strong></td>'; 
            $xcel_dtl[$page_cnt] .= '</tr>';
            $xcel_dtl[$page_cnt] .= '</table>';
            $page_last = count($xcel_dtl);
            foreach ($xcel_dtl as $key => $value) {
                $this->pdf->addPage('P', 'A4', true);
                $output_all = str_replace('eto ang page total', $page_last, $value);
                $this->pdf->writeHTML($output_all, true, false, true, false, '');  
            }
        }
        else
        {
            $this->pdf->addPage('P', 'LETTER', true);
            $this->pdf->SetXY(100, 20);
            $this->pdf->writeHTML("No Record Found!", true, false, true, false, '');
        }
    }

    function export_sss_certificate($company_id, $employee_id, $date_from, $date_to, $title)
    {
        if(!empty($employee_id)){
            $employee_id = 'AND employee_id IN ('.$employee_id.')';
        }
        
        $employee = $this->db->query("SELECT employee_id FROM {$this->db->dbprefix}user WHERE company_id = $company_id $employee_id")->result();

        $current_date = getdate(date("U"));
        
        foreach ($employee as $key => $value) {

            $contribution_qry = "SELECT payroll_date, (sum(employee) + sum(company) + sum(ec) ) as total_contribution
                                FROM {$this->db->dbprefix}employee_contribution 
                                WHERE employee_id = ({$value->employee_id}) AND payroll_date between '{$date_from}' AND '{$date_to}' AND transaction_id = 49
                                GROUP BY MONTH(payroll_date),YEAR(payroll_date)
                                ORDER BY employee_id,transaction_id, payroll_date";
            $contribution_res = $this->db->query($contribution_qry);
            $contribution_count = $contribution_res->num_rows();

            if($contribution_count != 0){
                
                $date_from_n = date('F, Y', strtotime($date_from));
                $date_to_n = date('F, Y', strtotime($date_to));
                //$date_to_n = date('m/d/Y', strtotime($date_to));

                $user_dtl_res = $this->db->query("SELECT *,b.sss as sss_id FROM {$this->db->dbprefix}user a 
                                                LEFT JOIN {$this->db->dbprefix}employee b ON  b.employee_id = a.employee_id 
                                                LEFT JOIN {$this->db->dbprefix}user_company_department c ON a.department_id = c.department_id 
                                                LEFT JOIN {$this->db->dbprefix}user_position d ON a.position_id = d.position_id
                                                WHERE a.employee_id = $value->employee_id")->row();
                $salutation = $user_dtl_res->salutation;

                $name = $user_dtl_res->lastname.', '.$user_dtl_res->firstname.' '.($user_dtl_res->aux = ''?'':$user_dtl_res->aux).' '.$user_dtl_res->middlename;
                $emp_sss_id = $user_dtl_res->sss_id;
                
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
                                        This is to certify that Mr/Ms. '.$name.' with SSS Number
                                    </td>
                                </tr>
                                <tr>
                                    <td width="100%" style="text-align:left; ">'.$emp_sss_id.' had contributed the <b><u>SSS Premium</u></b> to FIRST BALFOUR, INC covering below Months of Contributions.
                                    </td>
                                </tr>
                            </table>';                    
                    
                    $xcel_cont .= '<div>
                            <table >
                                <tr>
                                    <td colspan="15"></td>
                                    <td colspan="20%" style="text-align:left; "><u>MONTH</u></td>
                                    <td colspan="30%" style="text-align:center; "><u>EE/ER/EC SSS Contribution</u></td>
                                    <td colspan="20%" style="text-align:right; "><u>YEAR</u></td>
                                    <td colspan="15%"></td>
                                </tr>
                            </table>
                            ';
                    
                    foreach($contribution_res->result() as $cont_key=>$cont_value) 
                    {
                        $month = date('F',strtotime($cont_value->payroll_date));
                        $year = date('Y',strtotime($cont_value->payroll_date));
                        $amount = $cont_value->total_contribution;
                        
                        $currency = 'P';
                        $xcel_cont .='
                                <table>
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

    function export_sss_to_disk($company_id, $employee_id, $date_from, $date_to, $title)
    {
        $year_num = date("Y",strtotime($date_from));
        $day_num = date("d",strtotime($date_from));
        $month_num = date("m",strtotime($date_from)); 
        $payroll_date = $year_num.$month_num;

        $company_setting_res = $this->db->query('SELECT * FROM '.$this->db->dbprefix('user_company').' WHERE company_id = "'.$company_id.'"')->row();      

        $company_name = $company_setting_res->company;
        $company_add = $company_setting_res->address;
        $company_sss_id = str_replace('-', '', $company_setting_res->sss_no);
        $company_code = $company_setting_res->company_code;
        
        $payroll_date_res = $this->db->query('SELECT payroll_date FROM '.$this->db->dbprefix('payroll_closed_transaction')." WHERE payroll_date BETWEEN '{$date_from}' AND '{$date_to}'")->row();
        $p_date = $payroll_date_res->payroll_date;
        
        $contribution_data_qry = "  SELECT e.sss AS employee_gov_id, sum(ec.employee) AS employee_contribution, sum(ec.company) AS employer_contribution, sum(ec.ec) as ec,
                                        lastname, firstname, middleinitial, aux
                                    FROM {$this->db->dbprefix}employee_contribution ec
                                    LEFT JOIN {$this->db->dbprefix}user u ON ec.employee_id = u.employee_id
                                    LEFT JOIN {$this->db->dbprefix}employee e ON ec.employee_id = e.employee_id
                                    WHERE ec.company_id = {$company_id}
                                    AND ec.transaction_id = 49
                                    AND EXTRACT(YEAR_MONTH FROM ec.payroll_date) = {$payroll_date}
                                    GROUP BY e.sss, lastname, firstname, middleinitial
                                    ORDER BY lastname, firstname, middleinitial";
        $contribution_data_res = $this->db->query($contribution_data_qry);
        
        $total_contribution = $this->db->query("SELECT sum(ec.employee) AS employee_contribution, sum(ec.company) AS employer_contribution, sum(ec.ec) as ec
                                    FROM {$this->db->dbprefix}employee_contribution ec
                                    LEFT JOIN {$this->db->dbprefix}user u ON ec.employee_id = u.employee_id
                                    LEFT JOIN {$this->db->dbprefix}employee e ON ec.employee_id = e.employee_id
                                    WHERE ec.company_id = {$company_id}
                                    AND ec.transaction_id = 49
                                    AND EXTRACT(YEAR_MONTH FROM ec.payroll_date) = '{$payroll_date}'")->row();
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
        
        //$Data_header .= '00'.$year_num.$month_num.str_pad(' ',9).str_replace('-','',date('Ymd',strtotime($p_date))).$year_num.$month_num.str_pad($company_name,40).$company_sss_id.str_pad('C',4," ",STR_PAD_LEFT)."\r\n";
        $Data_header .= '00'.str_pad($company_setting_res->company,30," ",STR_PAD_RIGHT).date('mY',strtotime($p_date)).$company_sss_id."\r\n";

        fwrite($Handle, $Data_header); 

        foreach ($contribution_data_res->result() as $key => $value) {

            $employee_gov_id = str_pad(str_replace('-','',$value->employee_gov_id ),10);//either of the following SSS PhilHealth TIN
            
            $last_name  = str_replace(' *', '', $value->lastname);

            $lastname = str_pad($last_name, 15);
            if (strpos($lastname, 'ñ') !== false) {
                $lastname = substr(str_pad($last_name, 16),0,16);
            }
            else     
            {
                $lastname = substr(str_pad($last_name, 15),0,15);
            }

            if(strlen($value->firstname > 14)){
                $first_name = $value->firstname;
            }else{
                $first_name = $value->firstname.' '.$value->aux;
            }

            $firstname = str_pad($first_name, 15);
            if (strpos($firstname, 'ñ') !== false) {
                $firstname = substr(str_pad($first_name, 16),0,16);
            }
            else     
            {
                $firstname = substr(str_pad($first_name, 15),0,15);
            }

            $middleinitial = str_pad(substr(str_replace('.', '',$value->middleinitial ),0,1),1);

            $contribution = $value->employee_contribution+$value->employer_contribution;
            $contribution = str_pad( number_format($contribution,2,'.',''),8," ",STR_PAD_LEFT);
            $ec = str_pad( number_format($value->ec,2,'.',''),6," ",STR_PAD_LEFT);

            switch ($month_num) {
                case '01':
                case '04':
                case '07':
                case '10':
                    $cont_1 = $contribution;
                    $ec_1 = $ec;
                    break;
                case '02':
                case '05':
                case '08':
                case '11':
                    $cont_2 = $contribution;
                    $ec_2 = $ec;
                    break;
                case '03':
                case '06':
                case '09':
                case '12':
                    $cont_3 = $contribution;
                    $ec_3 = $ec;
                    break;
            }

            $spaceN = str_pad('N',7," ",STR_PAD_LEFT);
            $pc = str_pad('0.00',6," ",STR_PAD_LEFT);
            $c = str_pad('0.00',8," ",STR_PAD_LEFT);

            $Data = '20'.$lastname.$firstname.$middleinitial.$employee_gov_id.( $cont_1 != '' ? $cont_1 : $c ).( $cont_2 != '' ? $cont_2: $c ).( $cont_3 != '' ? $cont_3 : $c ).$pc.$pc.$pc.( $ec_1 != '' ? $ec_1 : $pc ).( $ec_2 != '' ? $ec_2 : $pc ).( $ec_3 != '' ? $ec_3 : $pc ).$spaceN."\r\n";
            fwrite($Handle, $Data); 
        }

        $tot_contribution = $total_contribution->employee_contribution + $total_contribution->employer_contribution;
        $tot_contribution = str_pad( number_format($tot_contribution,2,'.',''),12," ",STR_PAD_LEFT);
        $tot_ec = str_pad( number_format($total_contribution->ec,2,'.',''),10," ",STR_PAD_LEFT);
        switch ($month_num) {
                case '01':
                case '04':
                case '07':
                case '10':
                    $t_cont_1 = $tot_contribution;
                    $t_ec_1 = $tot_ec;
                    break;
                case '02':
                case '05':
                case '08':
                case '11':
                    $t_cont_2 = $tot_contribution;
                    $t_ec_2 = $tot_ec;
                    break;
                case '03':
                case '06':
                case '09':
                case '12':
                    $t_cont_3 = $tot_contribution;
                    $t_ec_3 = $tot_ec;
                    break;
        }
        $t_pc = str_pad('0.00',10," ",STR_PAD_LEFT);
        $t_c = str_pad('0.00',12," ",STR_PAD_LEFT);
        $Data_footer = "99".($t_cont_1 != '' ? $t_cont_1 : $t_c).($t_cont_2 != '' ? $t_cont_2 : $t_c).($t_cont_3 != '' ? $t_cont_3 : $t_c).$t_pc.$t_pc.$t_pc.($t_ec_1 != '' ? $t_ec_1 : $t_pc).($t_ec_2 != '' ? $t_ec_2 : $t_pc).($t_ec_3 != '' ? $t_ec_3 : $t_pc)."\r\n";
        fwrite($Handle, $Data_footer); 
        
        fclose($Handle); 
        readfile($File);
        exit();
        // END of To Disk //
    }

    function export_sss_loan_to_disk($company_id, $employee_id, $date_from, $date_to, $title)
    {
        $year_num = date("Y",strtotime($date_from));
        $year_hed = date("y",strtotime($date_from));
        $day_num = date("d",strtotime($date_from));
        $month_num = date("m",strtotime($date_from)); 
        $payroll_date = $year_num.$month_num;

        $company_setting_res = $this->db->query('SELECT * FROM '.$this->db->dbprefix('user_company').' WHERE company_id = "'.$company_id.'"')->row();      

        $company_name = $company_setting_res->company;
        $company_add = $company_setting_res->address;
        $company_sss_id = str_replace('-', '', $company_setting_res->sss_no);
        $company_code = $company_setting_res->company_code;
        
        $loan_data_qry = "  SELECT e.sss AS employee_gov_id, u.lastname, u.firstname, u.middleinitial, u.aux, SUM(lp.amount) as loan_amount, el.loan_principal, el.release_date as release_date
                            FROM {$this->db->dbprefix}employee_loan el
                            JOIN {$this->db->dbprefix}employee_loan_payment lp ON lp.employee_loan_id = el.employee_loan_id
                            JOIN {$this->db->dbprefix}employee e ON e.employee_id = el.employee_id
                            JOIN {$this->db->dbprefix}user u ON el.employee_id = u.employee_id
                            JOIN {$this->db->dbprefix}payroll_loan pl ON el.loan_id = pl.loan_id
                            WHERE pl.loan_type_id IN (SELECT loan_type_id FROM {$this->db->dbprefix}payroll_loan_type WHERE loan_type LIKE '%SSS%') 
                                AND EXTRACT(YEAR_MONTH FROM lp.date_paid) = {$payroll_date}
                            GROUP BY el.employee_id";
        $loan_data_res = $this->db->query($loan_data_qry);
        
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
        
        $Data_header .= '00'.str_pad($company_sss_id,10,"0",STR_PAD_LEFT).str_pad($company_name,30).$month_num.$year_hed."\r\n";
        
        fwrite($Handle, $Data_header); 
        $cnt = 0;
        foreach ($loan_data_res->result() as $key => $value) {

            $employee_gov_id = str_pad(str_replace('-','',$value->employee_gov_id ),10);//either of the following SSS PhilHealth TIN

            $last_name  = str_replace(' *', '', $value->lastname);

            $lastname = str_pad($last_name, 15);
            if (strpos($lastname, 'ñ') !== false) {
                $lastname = substr(str_pad($last_name, 16),0,16);
            }
            else     
            {
                $lastname = substr(str_pad($last_name, 15),0,15);
            }

            if(strlen($value->firstname > 14)){
                $first_name = $value->firstname;
            }else{
                $first_name = $value->firstname.' '.$value->aux;
            }

            $firstname = str_pad($first_name, 15);
            if (strpos($firstname, 'ñ') !== false) {
                $firstname = substr(str_pad($first_name, 16),0,16);
            }
            else     
            {
                $firstname = substr(str_pad($first_name, 15),0,15);
            }
            $middleinitial = str_pad(substr(str_replace('.', '',$value->middleinitial ),0,1),1);

            $release_date = date('ymd',strtotime($value->release_date));
            $loan_principal = str_pad(str_replace('.','',$value->loan_principal),8,"0",STR_PAD_LEFT);
            $loan_amount = str_pad(str_replace('.','',number_format($value->loan_amount,2,'.','')),12,"0",STR_PAD_LEFT);

            $Data = '10'.$employee_gov_id.$lastname.$firstname.$middleinitial.' S'.$release_date.$loan_principal.$loan_amount."\r\n"; 
            fwrite($Handle, $Data); 

            $loan_amount_t += $loan_amount;
            $cnt++;
        }

        $loan_amount_t  = str_pad(str_replace('.','',$loan_amount_t),18,"0",STR_PAD_LEFT);
        $Data_footer = "99".str_pad(($cnt),4,"0",STR_PAD_LEFT).$loan_amount_t."\r\n";
        fwrite($Handle, $Data_footer); 
        
        fclose($Handle); 
        readfile($File);
        exit();
        // END of To Disk //
    } 

    //Page header
    function Header() {
        // Title
        $this->Cell(0, 15, 'dasdasdasdasdasdasdasdas', 0, false, 'C', 0, '', 0, false, 'M', 'M');
    }

    // Page footer
    function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('helvetica', 'I', 8);
        // Page number
        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}

/* End of file */
/* Location: system/application */
?>