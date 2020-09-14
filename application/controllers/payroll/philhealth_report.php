<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class philhealth_report extends MY_Controller
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
        $report_type = array("Monthly Report (RF1)", "Quarterly Report (RF1)", "Remittance Certification", "RF1 to Disk");
        $report_type_html = '<select id="report_type_id" name="report_type_id">';
            foreach($report_type as $report_type_id => $report_type_value){
                $report_type_html .= '<option value="'.$report_type_id.'">'.$report_type_value.'</option>';
            }
        $report_type_html .= '</select>'; 


        $cost_code = $this->db->query('SELECT cost_code_id, cost_code FROM '.$this->db->dbprefix('cost_code_xxx').'')->result_array();
        $cost_code_html = '<select id="cost_code_id"  multiple="multiple" class="multi-select" name="cost_code_id[]">';
            foreach($cost_code as $cost_code_record){
                 $cost_code_html .= '<option value="'.$cost_code_record["cost_code_id"].'">'.$cost_code_record["cost_code"].'</option>';
            }
        $cost_code_html .= '</select>'; 

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
        $response->cost_code_html = $cost_code_html;
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

        $cost_code_id = '';
        if(isset($_POST['cost_code_id']))
        {
            $cost_code_arr = array();
            foreach ($_POST['cost_code_id'] as $value2) 
            {
                $cost_code_arr[] = $value2;    
            }
            $cost_code_id = implode(',', $cost_code_arr);
        }
        
        $this->load->library('pdf');        
        switch ($_POST['report_type_id']) 
        {
            //monthly
            case '0':
                $this->pdf->SetMargins(5, 5, 5);                   
                $this->pdf->SetFontSize( 8 );
                $html = $this->export_philhealth_monthly($company_id, $employee_id, $date_from, $date_to, "PhilHealth Premium Contribution",$cost_code_id);
                $title = "PhilHealth Premium Contribution";
                break;
            
            //quarterly
            case '1':
                $this->pdf->SetMargins(5, 5, 5);                   
                $this->pdf->SetFontSize( 8 );
                $html = $this->export_philhealth_quarterly($company_id, $employee_id, $date_from, $date_to, "PhilHealth Premium Contribution");
                $title = "PhilHealth Premium Contribution";
                break;
            
            //remittance certificate
            case '2':
                $html = $this->export_philhealth_certificate($company_id, $employee_id, $date_from, $date_to, "CERTIFICATE OF PREMIUM PAYMENT");
                $title = "CERTIFICATE OF PREMIUM PAYMENT";
                break;
            
            //to disk
            case '3':
                $html = $this->export_philhealth_to_disk($company_id, $employee_id, $date_from, $date_to, "PhilHealth RF1 to Disk");       
                $title = "PhilHealth RF1 to Disk";
                break;
        }
        $this->pdf->writeHTML($html, true, false, true, false, '');
        $this->pdf->Output(ucwords(str_replace(" ","_",$title))."_" . date('dmYHis') . '.pdf', 'I');
    }

    function export_philhealth_monthly($company_id, $employee_id, $date_from, $date_to, $title, $cost_code_id){   
        ini_set("memory_limit", "2048M");
        set_time_limit( 7200 );
        
        if(!empty($cost_code_id)){
            $costcode = 'AND cc.cost_code_id IN ('.$cost_code_id.')';
        }

        $company_setting_res = $this->db->query('SELECT * FROM '.$this->db->dbprefix('user_company').' WHERE company_id = "'.$company_id.'"')->row();
        $monthly_detail_qry = " SELECT  CONCAT(b.lastname,', ',b.firstname,' ',IF(b.aux = '','',b.aux),' ',IF(b.middleinitial IS NULL,'',b.middleinitial)) AS employee_name,
                                    SUM(a.employee) AS philhealth, SUM(a.company) AS philhealth_company, a.msb_id AS philhealth_msb,
                                    bb.philhealth AS philhealth_no, b.birth_date AS birthday
                                FROM {$this->db->dbprefix}payroll_period c
                                LEFT JOIN {$this->db->dbprefix}employee_contribution a ON c.payroll_period_id = a.payroll_period_id
                                LEFT JOIN {$this->db->dbprefix}user b ON a.employee_id = b.employee_id
                                LEFT JOIN {$this->db->dbprefix}payroll_transaction aa ON aa.transaction_id = a.transaction_id 
                                LEFT JOIN {$this->db->dbprefix}payroll_transaction_class ab ON ab.transaction_class_id = aa.transaction_class_id
                                LEFT JOIN {$this->db->dbprefix}employee bb ON b.employee_id = bb.employee_id
                                LEFT JOIN {$this->db->dbprefix}employee_work_assignment w on b.employee_id = w.employee_id
                                LEFT JOIN {$this->db->dbprefix}cost_code_xxx cc on w.cost_code = cc.cost_code
                                WHERE 1 AND aa.transaction_id = '50' AND bb.id_number IS NOT NULL
                                    AND a.payroll_date BETWEEN '{$date_from}' AND '{$date_to}' $costcode";
        if(!empty($employee_id)){ $monthly_detail_qry .= " AND b.employee_id IN ({$employee_id})"; }
        if(!empty($company_id)){ $monthly_detail_qry .= " AND b.company_id IN ({$company_id})"; }
        $monthly_detail_qry .= "GROUP BY b.employee_id
                                ORDER BY b.lastname, b.firstname, b.middleinitial";
        $monthly_detail_res = $this->db->query($monthly_detail_qry);
        $total_no_employees = $monthly_detail_res->num_rows();
        $allowed_count_per_page = 10;
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
        $company_address = $company_setting_res->address;
        $company_tin_no = $company_setting_res->vat_registration;
        $company_email = $company_setting_res->company_email;
        $company_zip = $company_setting_res->zipcode;
        $company_telephone = $company_setting_res->telephone;
        $company_philhealth_no = $company_setting_res->philhealth_no; 
        $company_type = 1; 

        if($total_no_employees != 0)
        {
            $philhealth_user_total = 0;
            $philhealth_company_total = 0;
            for($i=1;$i<=$number_of_page; $i++)
            {
                $this->pdf->addPage('L', 'LEGAL', true);
                $background = 'uploads/payroll_report/philhealth_monthly-1_web.jpg';
                $this->pdf->SetAutoPageBreak(false, 0);
                $this->pdf->Image($background, 35, 13.4, 310, 181, 'JPG', '', '', false, 100, '', false, false, 0, false, 0, false);
                $page_no_certified = $i;

                if(!empty($company_philhealth_no))
                {
                    $company_philhealth_x = 76.5;
                    $company_philhealth_x2 = 95;
                    $company_philhealth_x3 = 167;
                    $company_philhealth_no = str_replace("-", "", $company_philhealth_no);
                    for($c_s = 0 ; $c_s <= strlen($company_philhealth_no); $c_s++)
                    {
                        if($c_s < 2)
                        {
                            $company_sub = substr($company_philhealth_no, $c_s,1);
                            $this->pdf->MultiCell(10, 10, $company_sub, 0, 'C', false, 0, $company_philhealth_x, 30, true, 0, false, false, 0, 'T', true);   
                            $company_philhealth_x = $company_philhealth_x + 8.10;
                        }
                        elseif($c_s > 1 && $c_s < 11)
                        {
                            $company_sub = substr($company_philhealth_no, $c_s,1);
                            $this->pdf->MultiCell(10, 10, $company_sub, 0, 'C', false, 0, $company_philhealth_x2, 30, true, 0, false, false, 0, 'T', true);   
                            $company_philhealth_x2 = $company_philhealth_x2 + 7.85;
                        }
                        else
                        {
                            $company_sub = substr($company_philhealth_no, $c_s,1);
                            $this->pdf->MultiCell(10, 10, $company_sub, 0, 'C', false, 0, $company_philhealth_x3, 30, true, 0, false, false, 0, 'T', true);   
                            $company_philhealth_x = $company_philhealth_x + 8.10;
                        }
                    } 
                } 

                if(!empty($company_tin_no))
                {
                    $company_tin_x = 76.5;
                    $company_tin_x2 = 103;
                    $company_tin_x3 = 129;
                    $company_tin_x4 = 154;
                    $company_tin_no = str_replace("-", "", $company_tin_no);
                    for($c_s = 0 ; $c_s <= strlen($company_tin_no); $c_s++)
                    {
                        if($c_s < 3)
                        {                            
                            $company_sub = substr($company_tin_no, $c_s,1);
                            $this->pdf->MultiCell(10, 10, $company_sub, 0, 'C', false, 0, $company_tin_x, 35, true, 0, false, false, 0, 'T', true);   
                            $company_tin_x = $company_tin_x + 8.10;
                        }
                        elseif($c_s > 2 && $c_s < 6)
                        {
                            $company_sub = substr($company_tin_no, $c_s,1);
                            $this->pdf->MultiCell(10, 10, $company_sub, 0, 'C', false, 0, $company_tin_x2, 35, true, 0, false, false, 0, 'T', true);   
                            $company_tin_x2 = $company_tin_x2 + 8.10;
                        }
                        elseif($c_s > 5 && $c_s < 9)
                        {
                            $company_sub = substr($company_tin_no, $c_s,1);
                            $this->pdf->MultiCell(10, 10, $company_sub, 0, 'C', false, 0, $company_tin_x3, 35, true, 0, false, false, 0, 'T', true);   
                            $company_tin_x3 = $company_tin_x3 + 8.10;
                        }
                        else
                        {
                            $company_sub = substr($company_tin_no, $c_s,1);
                            $this->pdf->MultiCell(10, 10, $company_sub, 0, 'C', false, 0, $company_tin_x4, 35, true, 0, false, false, 0, 'T', true);   
                            $company_tin_x4 = $company_tin_x4 + 8.10;
                        }

                    } 
                }

                if(strlen($company) > 81)
                {
                    $company = substr($company, 0,81).'...';
                }
                $this->pdf->MultiCell(133, 10, $company, 0, 'L', false, 0, 81, 41.5, true, 0, false, false, 0, 'T', true);

                if(strlen($company_address) > 81)
                {
                    $company_address1 = substr($company_address, 0,83);
                    $this->pdf->MultiCell(133, 10, $company_address1, 0, 'L', false, 0, 81, 46, true, 0, false, false, 0, 'T', true);

                    if(strlen($company_address) > 160)
                    {
                        $company_address2 = substr($company_address, 83,81).'...';
                    }
                    else
                    {
                        $company_address2 = substr($company_address, 83,81);
                    }
                    $this->pdf->MultiCell(133, 10, $company_address2, 0, 'L', false, 0, 81, 50, true, 0, false, false, 0, 'T', true);
                }
                else
                {
                    $this->pdf->MultiCell(133, 10, $company_address, 0, 'L', false, 0, 81, 46, true, 0, false, false, 0, 'T', true);   
                }

                if(strlen($company_telephone) > 27)
                {
                    $company_telephone = substr($company_telephone, 0,27).'...';
                }
                $this->pdf->MultiCell(47, 10, $company_telephone, 0, 'L', false, 0, 81, 54, true, 0, false, false, 0, 'T', true);

                if(strlen($company_email) > 27)
                {
                    $company_email = substr($company_email, 0,27).'...';
                }
                $this->pdf->MultiCell(47, 10, $company_email, 0, 'L', false, 0, 167, 54, true, 0, false, false, 0, 'T', true);

                $m1_type = 'x';
                switch ($company_type) {
                    // case 'private':
                    case 1:
                        $this->pdf->SetXY(234, 45);
                        break; 
                    // case 'government':
                    case 2:
                        $this->pdf->SetXY(234, 49);
                        break;
                    // case 'household':
                    case 3:
                        $this->pdf->SetXY(234, 54);
                        break;
                }
                $this->pdf->writeHTML($m1_type, true, false, true, false, '');
                $this->pdf->MultiCell(31.5, 10, $month_set.' '.$year_set, 0, 'C', false, 0, 313.5, 51, true, 0, false, false, 0, 'T', true);   
                $monthly_detail_value_qry = "SELECT b.lastname,b.firstname, b.middlename,b.salutation,b.sex,b.aux,
                                             SUM(a.employee) AS philhealth, SUM(a.company) AS philhealth_company,
                                             a.msb_id AS philhealth_msb, bb.philhealth AS philhealth_no,
                                             b.birth_date AS birthday
                                             FROM {$this->db->dbprefix}payroll_period c
                                             LEFT JOIN {$this->db->dbprefix}employee_contribution a ON c.payroll_period_id = a.payroll_period_id
                                             LEFT JOIN {$this->db->dbprefix}user b ON a.employee_id = b.employee_id
                                             LEFT JOIN {$this->db->dbprefix}payroll_transaction aa ON aa.transaction_id = a.transaction_id
                                             LEFT JOIN {$this->db->dbprefix}payroll_transaction_class ab ON ab.transaction_class_id = aa.transaction_class_id
                                             LEFT JOIN {$this->db->dbprefix}employee bb ON b.employee_id = bb.employee_id
                                             LEFT JOIN {$this->db->dbprefix}employee_work_assignment w on b.employee_id = w.employee_id
                                             LEFT JOIN {$this->db->dbprefix}cost_code_xxx cc on w.cost_code = cc.cost_code
                                             WHERE 1 AND ab.transaction_class_id = '18' AND bb.id_number IS NOT NULL
                                                AND a.payroll_date BETWEEN '{$date_from}' AND '{$date_to}' $costcode";
                if(!empty($employee_id)){ $monthly_detail_value_qry .= " AND b.employee_id IN ({$employee_id})"; }
                if(!empty($company_id)){ $monthly_detail_value_qry .= " AND b.company_id IN ({$company_id})"; }
                $monthly_detail_value_qry .= "GROUP BY b.employee_id
                                             ORDER BY b.lastname, b.firstname, b.middleinitial";
                $limit = ($i - 1) * $allowed_count_per_page;
                $monthly_detail_value_qry .= " LIMIT {$limit},{$allowed_count_per_page}";
                $monthly_detail_value_res = $this->db->query($monthly_detail_value_qry);
                
                $line = 64;
                $philhealth_user_per = 0;
                $philhealth_company_per = 0;
                $value_count = 1;                
                foreach ($monthly_detail_value_res->result() as $key => $value) 
                {
                    if($value_count == 1) { $line = 78.8; }
                    elseif($value_count == 2) { $line = 85.8; }
                    elseif($value_count == 3) { $line = 93.8; }
                    elseif($value_count == 4) { $line = 101.8; }
                    elseif($value_count == 5) { $line = 109; }
                    elseif($value_count == 6) { $line = 116.8; }
                    elseif($value_count == 7) { $line = 125; }
                    elseif($value_count == 8) { $line = 132.5; }
                    elseif($value_count == 9) { $line = 141; }
                    else { $line = 148; }
                    $philhealth_no_user = $value->philhealth_no;
                    $philhealth_msb_user = $value->philhealth_msb;
                    $philhealth_user = $value->philhealth;
                    $philhealth_company = $value->philhealth_company;
                    $birthday_user = $value->birthday;
                    $lastname = str_replace(' *', '', $value->lastname);
                    $firstname = $value->firstname;
                    $middlename = $value->middlename;
                    $salutation = $value->aux;
                    // $salutation = str_replace(".", "", $value->salutation);
                    $sex = strtoupper(substr($value->sex, 0,1));
                    if(!empty($philhealth_no_user))
                    {
                        $user_phil_no_x = 40.5;
                        $user_phil_no_x2 = 53.5;
                        $user_phil_no_x3 = 103.5;
                        $philhealth_no_user = str_replace("-", "", $philhealth_no_user);
                        for($c_s = 0 ; $c_s <= strlen($philhealth_no_user); $c_s++)
                        {
                            if($c_s < 2)
                            {
                                $user_sss_no_sub = substr($philhealth_no_user, $c_s,1);
                                $this->pdf->MultiCell(10, 10, $user_sss_no_sub, 0, 'C', false, 0, $user_phil_no_x, $line, true, 0, false, false, 0, 'T', true);   
                                $user_phil_no_x = $user_phil_no_x + 6;
                            }
                            elseif($c_s > 1 && $c_s < 11)
                            {
                                $user_sss_no_sub = substr($philhealth_no_user, $c_s,1);
                                $this->pdf->MultiCell(10, 10, $user_sss_no_sub, 0, 'C', false, 0, $user_phil_no_x2, $line, true, 0, false, false, 0, 'T', true);   
                                $user_phil_no_x2 = $user_phil_no_x2 + 5.4;   
                            }
                            else
                            {
                                $user_sss_no_sub = substr($philhealth_no_user, $c_s,1);
                                $this->pdf->MultiCell(10, 10, $user_sss_no_sub, 0, 'C', false, 0, $user_phil_no_x3, $line, true, 0, false, false, 0, 'T', true);   
                                $user_phil_no_x3 = $user_phil_no_x3 + 5.4;   
                            }
                        } 
                    }
                    else
                    {
                        if(!empty($birthday_user))
                        {
                            $bday_month = date("m",strtotime($birthday_user));
                            $bday_day = date("d",strtotime($birthday_user));
                            $bday_year = date("Y",strtotime($birthday_user));
                            $this->pdf->MultiCell(39, 10, $bday_month, 0, 'C', false, 0, 216, $line, true, 0, false, false, 0, 'T', true);    
                            $this->pdf->MultiCell(39, 10, $bday_day, 0, 'C', false, 0, 222, $line, true, 0, false, false, 0, 'T', true);    
                            $this->pdf->MultiCell(39, 10, $bday_year, 0, 'C', false, 0, 230, $line, true, 0, false, false, 0, 'T', true);    
                        }
                        $this->pdf->MultiCell(39, 10, $sex, 0, 'C', false, 0, 240, $line, true, 0, false, false, 0, 'T', true);    
                    }

                    if(strlen($lastname) > 22)
                    {
                        $lastname = substr($lastname, 0,22).'...';
                    }
                    $this->pdf->MultiCell(39, 10, $lastname, 0, 'C', false, 0, 112, $line, true, 0, false, false, 0, 'T', true);
                    $this->pdf->MultiCell(12, 10, $salutation, 0, 'C', false, 0, 151, $line, true, 0, false, false, 0, 'T', true);   
                    if(strlen($firstname) > 19)
                    {
                        $firstname = substr($firstname, 0,19).'...';
                    }
                    $this->pdf->MultiCell(35, 10, $firstname, 0, 'C', false, 0, 163, $line, true, 0, false, false, 0, 'T', true);   
                    if(strlen($middlename) > 19)
                    {
                        $middlename = substr($middlename, 0,19).'...';
                    }
                    $this->pdf->MultiCell(35, 10, $middlename, 0, 'C', false, 0, 197, $line, true, 0, false, false, 0, 'T', true);   

                    $this->pdf->MultiCell(10, 10, $philhealth_msb_user, 0, 'C', false, 0, 264, $line, true, 0, false, false, 0, 'T', true);
                    $this->pdf->MultiCell(21, 10, $philhealth_user, 0, 'C', false, 0, 273.5, $line, true, 0, false, false, 0, 'T', true);
                    $this->pdf->MultiCell(20, 10, $philhealth_company, 0, 'C', false, 0, 294, $line, true, 0, false, false, 0, 'T', true);
                    $philhealth_user_per += $philhealth_user;
                    $philhealth_user_total += $philhealth_user;
                    $philhealth_company_per += $philhealth_company;
                    $philhealth_company_total += $philhealth_company;
                    $value_count++;
                }
                $philhealth_user_per_total = $philhealth_user_per + $philhealth_company_per;
                $this->pdf->MultiCell(32, 10, ($value_count-1), 0, 'C', false, 0, 34, 161.5, true, 0, false, false, 0, 'T', true);
                $this->pdf->MultiCell(21.5, 10, number_format($philhealth_user_per,2,".",","), 0, 'C', false, 0, 273, 156.5, true, 0, false, false, 0, 'T', true);
                $this->pdf->MultiCell(20, 10, number_format($philhealth_company_per,2,".",","), 0, 'C', false, 0, 294, 156.5, true, 0, false, false, 0, 'T', true);
                $this->pdf->MultiCell(41.5, 10, number_format($philhealth_user_per_total,2,".",","), 0, 'C', false, 0, 273, 161.5, true, 0, false, false, 0, 'T', true);
                if($i == $number_of_page)
                {
                    $philhealth_user_total_total = $philhealth_user_total + $philhealth_company_total;
                    $this->pdf->MultiCell(21.5, 10, number_format($philhealth_user_total,2,".",","), 0, 'C', false, 0, 273, 166.5, true, 0, false, false, 0, 'T', true);
                    $this->pdf->MultiCell(20, 10, number_format($philhealth_company_total,2,".",","), 0, 'C', false, 0, 294, 166.5, true, 0, false, false, 0, 'T', true);
                    $this->pdf->MultiCell(41.5, 10, number_format($philhealth_user_total_total,2,".",","), 0, 'C', false, 0, 273, 171.5, true, 0, false, false, 0, 'T', true);
                }
                $this->pdf->MultiCell(11.5, 10, $i, 0, 'C', false, 0, 309, 189.5, true, 0, false, false, 0, 'T', true);
                $this->pdf->MultiCell(10.5, 10, $number_of_page, 0, 'C', false, 0, 324, 189.5, true, 0, false, false, 0, 'T', true);
            }   
        }
        else
        {
            $this->pdf->addPage('P', 'LETTER', true);
            $this->pdf->SetXY(100, 20);
            $this->pdf->writeHTML("No Record Found!", true, false, true, false, '');
        }
    }

    function export_philhealth_quarterly($company_id, $employee_id, $date_from, $date_to, $title)
    {   
        ini_set("memory_limit", "2048M");
        set_time_limit( 7200 );

        $company_setting_res = $this->db->query('SELECT * FROM '.$this->db->dbprefix('user_company').' WHERE company_id = "'.$company_id.'"')->row();
        $monthly_detail_qry = "SELECT";
        $monthly_detail_qry .= " b.lastname, b.firstname, IF(b.middleinitial IS NULL,'',b.middleinitial), b.aux,";
        $monthly_detail_qry .= " SUM(a.employee) AS philhealth,";
        $monthly_detail_qry .= " SUM(a.company) AS philhealth_company,";
        $monthly_detail_qry .= " a.msb_id AS philhealth_msb,";
        $monthly_detail_qry .= " bb.philhealth AS philhealth_no,";
        $monthly_detail_qry .= " b.birth_date AS birthday";
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
        $company = $company_setting_res->company;
        $company_address = $company_setting_res->address;
        $company_tin_no = $company_setting_res->vat_registration;
        $company_email = $company_setting_res->company_email;
        $company_zip = $company_setting_res->zipcode;
        $company_telephone = $company_setting_res->telephone;
        $company_philhealth_no = $company_setting_res->philhealth_no; 
        $company_sss_no = $company_setting_res->sss_no; 
        $company_type = 1; 
        $company_type_2 = 'regular';

        $month_num = date("m",strtotime($date_from)); 
        $year_num = date("y",strtotime($date_from)); 
        if($month_num < 04) { $quarter_end = 1; }
        elseif($month_num < 07) { $quarter_end = 2; }
        elseif($month_num < 10) { $quarter_end = 3; }
        elseif($month_num > 09) { $quarter_end = 4; }
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
        $total_employee = 0;
        if($total_no_employees != 0)
        {
            $philhealth_user_total = 0;
            $philhealth_company_total = 0;
            for($i=1;$i<=$number_of_page; $i++)
            {
                $this->pdf->addPage('L', 'LEGAL', true);
                $background = 'uploads/payroll_report/philhealth_quarterly-1_web.jpg';
                $this->pdf->SetAutoPageBreak(false, 0);
                $this->pdf->Image($background, 33.2, 14.4, 309.2, 189, 'JPG', '', '', false, 100, '', false, false, 0, false, 0, false);
                $page_no_certified = $i;

                if(!empty($company_philhealth_no))
                {
                    $company_philhealth_x = 68;
                    $company_philhealth_x2 = 78;
                    $company_philhealth_x3 = 111.5;
                    $company_philhealth_no = str_replace("-", "", $company_philhealth_no);
                    for($c_s = 0 ; $c_s <= strlen($company_philhealth_no); $c_s++)
                    {
                        if($c_s < 2)
                        {
                            $company_sub = substr($company_philhealth_no, $c_s,1);
                            $this->pdf->MultiCell(10, 10, $company_sub, 0, 'C', false, 0, $company_philhealth_x, 33, true, 0, false, false, 0, 'T', true);   
                            $company_philhealth_x = $company_philhealth_x + 3.5;
                        }
                        elseif($c_s > 1 && $c_s < 11)
                        {
                            $company_sub = substr($company_philhealth_no, $c_s,1);
                            $this->pdf->MultiCell(10, 10, $company_sub, 0, 'C', false, 0, $company_philhealth_x2, 33, true, 0, false, false, 0, 'T', true);   
                            $company_philhealth_x2 = $company_philhealth_x2 + 3.4;
                        }
                        else
                        {
                            $company_sub = substr($company_philhealth_no, $c_s,1);
                            $this->pdf->MultiCell(10, 10, $company_sub, 0, 'C', false, 0, $company_philhealth_x3, 33, true, 0, false, false, 0, 'T', true);   
                            $company_philhealth_x = $company_philhealth_x + 8.10;
                        }
                    } 
                } 

                if(!empty($company_tin_no))
                {
                    $company_tin_x = 68;
                    $company_tin_x2 = 81.5;
                    $company_tin_x3 = 94.5;
                    $company_tin_x4 = 108;
                    $company_tin_no = str_replace("-", "", $company_tin_no);
                    for($c_s = 0 ; $c_s <= strlen($company_tin_no); $c_s++)
                    {
                        if($c_s < 3)
                        {                            
                            $company_sub = substr($company_tin_no, $c_s,1);
                            $this->pdf->MultiCell(10, 10, $company_sub, 0, 'C', false, 0, $company_tin_x, 40, true, 0, false, false, 0, 'T', true);   
                            $company_tin_x = $company_tin_x + 3.5;
                        }
                        elseif($c_s > 2 && $c_s < 6)
                        {
                            $company_sub = substr($company_tin_no, $c_s,1);
                            $this->pdf->MultiCell(10, 10, $company_sub, 0, 'C', false, 0, $company_tin_x2, 40, true, 0, false, false, 0, 'T', true);   
                            $company_tin_x2 = $company_tin_x2 + 3.5;
                        }
                        elseif($c_s > 5 && $c_s < 9)
                        {
                            $company_sub = substr($company_tin_no, $c_s,1);
                            $this->pdf->MultiCell(10, 10, $company_sub, 0, 'C', false, 0, $company_tin_x3, 40, true, 0, false, false, 0, 'T', true);   
                            $company_tin_x3 = $company_tin_x3 + 3.5;
                        }
                        else
                        {
                            $company_sub = substr($company_tin_no, $c_s,1);
                            $this->pdf->MultiCell(10, 10, $company_sub, 0, 'C', false, 0, $company_tin_x4, 40, true, 0, false, false, 0, 'T', true);   
                            $company_tin_x4 = $company_tin_x4 + 3.5;
                        }

                    } 
                }

                if(strlen($company) > 46)
                {
                    $company = substr($company, 0,46).'...';
                }
                $this->pdf->MultiCell(78, 10, $company, 0, 'C', false, 0, 84, 54, true, 0, false, false, 0, 'T', true);
                if(strlen($company_address) > 47)
                {
                    $company_address1 = substr($company_address, 0,47);
                    $this->pdf->MultiCell(78, 10, $company_address1, 0, 'C', false, 0, 84, 59, true, 0, false, false, 0, 'T', true);
                    if(strlen($company_address) > 89)
                    {
                        $company_address2 = substr($company_address, 47,40).'...';
                    }
                    else
                    {
                        $company_address2 = substr($company_address, 47,44);
                    }
                    $this->pdf->MultiCell(68, 10, $company_address2, 0, 'L', false, 0, 40, 63, true, 0, false, false, 0, 'T', true);
                }
                else
                {
                    $this->pdf->MultiCell(78, 10, $company_address, 0, 'C', false, 0, 84, 59, true, 0, false, false, 0, 'T', true);   
                }

                if(strlen($company_telephone) > 27)
                {
                    $company_telephone = substr($company_telephone, 0,27).'...';
                }
                $this->pdf->MultiCell(32, 10, $company_telephone, 0, 'C', false, 0, 131, 63, true, 0, false, false, 0, 'T', true);


                $m1_type = 'x';
                if($company_type_2 == 'regular')
                {                    
                    $this->pdf->SetXY(169, 53.5);
                    $this->pdf->writeHTML($m1_type, true, false, true, false, '');
                    switch ($company_type) {
                        // case 'private':
                        case 1:
                            if(!empty($company_sss_no))
                            {
                                $company_sss_x = 198.5;
                                $company_sss_x2 = 208.5;
                                $company_sss_x4 = 235.5;
                                $company_sss_no = str_replace("-", "", $company_sss_no);
                                for($c_s = 0 ; $c_s <= strlen($company_sss_no); $c_s++)
                                {
                                    if($c_s < 2)
                                    {                            
                                        $company_sub = substr($company_sss_no, $c_s,1);
                                        $this->pdf->MultiCell(10, 10, $company_sub, 0, 'C', false, 0, $company_sss_x, 58.5, true, 0, false, false, 0, 'T', true);   
                                        $company_sss_x = $company_sss_x + 3;
                                    }
                                    elseif($c_s > 2 && $c_s < 10)
                                    {
                                        $company_sub = substr($company_sss_no, $c_s,1);
                                        $this->pdf->MultiCell(10, 10, $company_sub, 0, 'C', false, 0, $company_sss_x2, 58.5, true, 0, false, false, 0, 'T', true);   
                                        $company_sss_x2 = $company_sss_x2 + 3.4;
                                    }
                                    else
                                    {
                                        $company_sub = substr($company_sss_no, $c_s,1);
                                        $this->pdf->MultiCell(10, 10, $company_sub, 0, 'C', false, 0, $company_sss_x4, 58.5, true, 0, false, false, 0, 'T', true);   
                                        $company_sss_x4 = $company_sss_x4 + 3.5;
                                    }

                                } 
                            }                            
                            $this->pdf->SetXY(175.5, 58);
                            break; 
                        // case 'government':
                        case 2:
                            $this->pdf->SetXY(175.5, 62.5);
                            break;
                    }
                    $this->pdf->writeHTML($m1_type, true, false, true, false, '');
                }
                else
                {
                    $this->pdf->SetXY(169, 67);
                    $this->pdf->writeHTML($m1_type, true, false, true, false, '');
                }

                switch ($quarter_end) {
                    case 1:
                        $this->pdf->SetXY(292.9, 53.5);
                        $this->pdf->writeHTML("x", true, false, true, false, '');
                         $this->pdf->MultiCell(9, 10, $year_num, 0, 'C', false, 0, 331, 54.2, true, 0, false, false, 0, 'T', true);  
                        break;
                    case 2:
                        $this->pdf->SetXY(292.9, 58);
                        $this->pdf->writeHTML("x", true, false, true, false, '');
                         $this->pdf->MultiCell(9, 10, $year_num, 0, 'C', false, 0, 331, 59, true, 0, false, false, 0, 'T', true);  
                        break;
                    case 3:
                        $this->pdf->SetXY(292.9, 62.5);
                        $this->pdf->writeHTML("x", true, false, true, false, '');
                         $this->pdf->MultiCell(9, 10, $year_num, 0, 'C', false, 0, 331, 63.5, true, 0, false, false, 0, 'T', true);  
                        break;
                    case 4:
                        $this->pdf->SetXY(292.9, 67);
                        $this->pdf->writeHTML("x", true, false, true, false, '');
                         $this->pdf->MultiCell(9, 10, $year_num, 0, 'C', false, 0, 331, 67.5, true, 0, false, false, 0, 'T', true);  
                        break;
                }

                $monthly_detail_value_qry = "SELECT";
                $monthly_detail_value_qry .= " b.lastname,b.firstname, b.middleinitial,b.salutation,b.sex,b.aux,";
                $monthly_detail_value_qry .= " SUM(a.employee) AS philhealth,";
                $monthly_detail_value_qry .= " SUM(a.company) AS philhealth_company,";
                $monthly_detail_value_qry .= " a.msb_id AS philhealth_msb,";
                $monthly_detail_value_qry .= " bb.philhealth AS philhealth_no,";
                $monthly_detail_value_qry .= " bb.sss AS sss_no,";
                $monthly_detail_value_qry .= " b.birth_date AS birthday";
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
                $monthly_detail_value_qry .= " ORDER BY b.lastname, b.firstname, b.middlename";
                $limit = ($i - 1) * $allowed_count_per_page;
                $monthly_detail_value_qry .= " LIMIT {$limit},{$allowed_count_per_page}";
                // dbug($monthly_detail_value_qry);
                $monthly_detail_value_res = $this->db->query($monthly_detail_value_qry);
                
                $line = 64;
                $philhealth_user_per = 0;
                $philhealth_company_per = 0;
                $value_count = 1;                
                foreach ($monthly_detail_value_res->result() as $key => $value) 
                {
                    if($value_count == 1) { $line = 93.5; }
                    elseif($value_count == 2) { $line = 99; }
                    elseif($value_count == 3) { $line = 104; }
                    elseif($value_count == 4) { $line = 109; }
                    elseif($value_count == 5) { $line = 114; }
                    elseif($value_count == 6) { $line = 119; }
                    elseif($value_count == 7) { $line = 124; }
                    elseif($value_count == 8) { $line = 129.5; }
                    elseif($value_count == 9) { $line = 135; }
                    elseif($value_count == 10) { $line = 140; }
                    elseif($value_count == 11) { $line = 145; }
                    elseif($value_count == 12) { $line = 150; }
                    elseif($value_count == 13) { $line = 155; }
                    elseif($value_count == 14) { $line = 160; } 
                    else { $line = 165; }
                    $philhealth_no_user = $value->philhealth_no;
                    $sss_no_user = $value->sss_no;
                    $philhealth_msb_user = $value->philhealth_msb;
                    $philhealth_user = $value->philhealth;
                    $philhealth_company = $value->philhealth_company;
                    $birthday_user = $value->birthday;
                    $lastname = str_replace(' *', '', $value->lastname);
                    
                    if(!empty($value->aux) && $value->aux !=''){
                        $firstname = $value->firstname.' '.$value->aux;    
                    }else{
                        $firstname = $value->firstname;
                    }
                    $middlename = $value->middleinitial;
                    $salutation = $value->aux;
                    // $salutation = str_replace(".", "", $value->salutation);
                    $sex = strtoupper(substr($value->sex, 0,1));
                    if(!empty($philhealth_no_user))
                    {
                        $philhealth_no_user = str_replace("-", "", $philhealth_no_user);
                        $this->pdf->MultiCell(21.5, 10, $philhealth_no_user, 0, 'C', false, 0, 130.5, $line, true, 0, false, false, 0, 'T', true);   
                    }
                    else
                    {
                        if(!empty($sss_no_user))
                        {
                            $sss_no_user = str_replace("-", "", $sss_no_user);
                            $this->pdf->MultiCell(21.5, 10, $sss_no_user, 0, 'C', false, 0, 130.5, $line, true, 0, false, false, 0, 'T', true);   
                        }
                    }
                    if(strlen($lastname) > 27)
                    {
                        $lastname = substr($lastname, 0,27).'...';
                    }
                    $this->pdf->MultiCell(47, 10, $lastname, 0, 'C', false, 0, 35, $line, true, 0, false, false, 0, 'T', true);
                    if(strlen($firstname) > 25)
                    {
                        $firstname = substr($firstname, 0,25).'...';
                    }
                    $this->pdf->MultiCell(44, 10, $firstname, 0, 'C', false, 0, 81, $line, true, 0, false, false, 0, 'T', true);   
                    if(strlen($middlename) > 19)
                    {
                        $middlename = substr($middlename, 0,19).'...';
                    }
                    $this->pdf->MultiCell(35, 10, $middlename, 0, 'C', false, 0, 111, $line, true, 0, false, false, 0, 'T', true);   

                    switch ($position_month) {
                        case 1:
                            $this->pdf->MultiCell(8.2, 10, $philhealth_msb_user, 0, 'C', false, 0, 150.5, $line, true, 0, false, false, 0, 'T', true);
                            $this->pdf->MultiCell(17.8, 10, $philhealth_user, 0, 'C', false, 0, 171, $line, true, 0, false, false, 0, 'T', true);
                            $this->pdf->MultiCell(18, 10, $philhealth_company, 0, 'C', false, 0, 187.2, $line, true, 0, false, false, 0, 'T', true);
                            break;
                        case 2:
                            $this->pdf->MultiCell(8.2, 10, $philhealth_msb_user, 0, 'C', false, 0, 157, $line, true, 0, false, false, 0, 'T', true);
                            $this->pdf->MultiCell(17.8, 10, $philhealth_user, 0, 'C', false, 0, 204, $line, true, 0, false, false, 0, 'T', true);
                            $this->pdf->MultiCell(18, 10, $philhealth_company, 0, 'C', false, 0, 221, $line, true, 0, false, false, 0, 'T', true);
                            break;
                        case 3:
                            $this->pdf->MultiCell(8.2, 10, $philhealth_msb_user, 0, 'C', false, 0, 164, $line, true, 0, false, false, 0, 'T', true);
                            $this->pdf->MultiCell(17.8, 10, $philhealth_user, 0, 'C', false, 0, 238, $line, true, 0, false, false, 0, 'T', true);
                            $this->pdf->MultiCell(18, 10, $philhealth_company, 0, 'C', false, 0, 254.5, $line, true, 0, false, false, 0, 'T', true);
                            break;
                    }
                    $philhealth_user_per += $philhealth_user;
                    $philhealth_user_total += $philhealth_user;
                    $philhealth_company_per += $philhealth_company;
                    $philhealth_company_total += $philhealth_company;
                    $value_count++;
                    $total_employee++;
                }
                $line2 = 173.5;
                $line3 = 178;
                $philhealth_user_per_total = $philhealth_user_per + $philhealth_company_per;
                switch ($position_month) {
                    case 1:
                        $this->pdf->MultiCell(17.8, 10, number_format($philhealth_user_per,2,".",","), 0, 'C', false, 0, 171, $line2, true, 0, false, false, 0, 'T', true);
                        $this->pdf->MultiCell(18, 10, number_format($philhealth_company_per,2,".",","), 0, 'C', false, 0, 187.2, $line2, true, 0, false, false, 0, 'T', true);
                        $this->pdf->MultiCell(34.5, 10, number_format($philhealth_user_per_total,2,".",","), 0, 'C', false, 0, 171, $line3, true, 0, false, false, 0, 'T', true);
                        break;
                    case 2:
                        $this->pdf->MultiCell(17.8, 10, number_format($philhealth_user_per,2,".",","), 0, 'C', false, 0, 204, $line2, true, 0, false, false, 0, 'T', true);
                        $this->pdf->MultiCell(18, 10, number_format($philhealth_company_per,2,".",","), 0, 'C', false, 0, 221, $line2, true, 0, false, false, 0, 'T', true);
                        $this->pdf->MultiCell(34.5, 10, number_format($philhealth_user_per_total,2,".",","), 0, 'C', false, 0, 204.2, $line3, true, 0, false, false, 0, 'T', true);
                        break;
                    case 3:
                        $this->pdf->MultiCell(17.8, 10, number_format($philhealth_user_per,2,".",","), 0, 'C', false, 0, 238, $line2, true, 0, false, false, 0, 'T', true);
                        $this->pdf->MultiCell(18, 10, number_format($philhealth_company_per,2,".",","), 0, 'C', false, 0, 254.5, $line2, true, 0, false, false, 0, 'T', true);
                        $this->pdf->MultiCell(34.5, 10, number_format($philhealth_user_per_total,2,".",","), 0, 'C', false, 0, 238, $line3, true, 0, false, false, 0, 'T', true);
                        break;
                }

                if($i == $number_of_page)
                {              
                    $line2 = 186;
                    $line3 = 190;      
                    $philhealth_user_total_total = $philhealth_user_total + $philhealth_company_total;
                    switch ($position_month) {
                        case 1:                            
                            $this->pdf->MultiCell(17.8, 10, number_format($philhealth_user_total,2,".",","), 0, 'C', false, 0, 171, $line2, true, 0, false, false, 0, 'T', true);
                            $this->pdf->MultiCell(18, 10, number_format($philhealth_company_total,2,".",","), 0, 'C', false, 0, 187.2, $line2, true, 0, false, false, 0, 'T', true);
                            $this->pdf->MultiCell(34.5, 10, number_format($philhealth_user_total_total,2,".",","), 0, 'C', false, 0, 171, $line3, true, 0, false, false, 0, 'T', true);
                            $this->pdf->MultiCell(24, 10, number_format($philhealth_user_total_total,2,".",","), 0, 'C', false, 0, 51, 178, true, 0, false, false, 0, 'T', true);
                            $this->pdf->MultiCell(24.5, 10, $total_employee, 0, 'C', false, 0, 107.5, 178, true, 0, false, false, 0, 'T', true);
                            break;
                        case 2:
                            $this->pdf->MultiCell(17.8, 10, number_format($philhealth_user_total,2,".",","), 0, 'C', false, 0, 204, $line2, true, 0, false, false, 0, 'T', true);
                            $this->pdf->MultiCell(18, 10, number_format($philhealth_company_total,2,".",","), 0, 'C', false, 0, 221, $line2, true, 0, false, false, 0, 'T', true);
                            $this->pdf->MultiCell(34.5, 10, number_format($philhealth_user_total_total,2,".",","), 0, 'C', false, 0, 204.2, $line3, true, 0, false, false, 0, 'T', true);
                            $this->pdf->MultiCell(24, 10, number_format($philhealth_user_total_total,2,".",","), 0, 'C', false, 0, 51, 182, true, 0, false, false, 0, 'T', true);
                            $this->pdf->MultiCell(24.5, 10, $total_employee, 0, 'C', false, 0, 107.5, 182, true, 0, false, false, 0, 'T', true);
                            break;
                        case 3:
                            $this->pdf->MultiCell(17.8, 10, number_format($philhealth_user_total,2,".",","), 0, 'C', false, 0, 238, $line2, true, 0, false, false, 0, 'T', true);
                            $this->pdf->MultiCell(18, 10, number_format($philhealth_company_total,2,".",","), 0, 'C', false, 0, 254.5, $line2, true, 0, false, false, 0, 'T', true);
                            $this->pdf->MultiCell(34.5, 10, number_format($philhealth_user_total_total,2,".",","), 0, 'C', false, 0, 238, $line3, true, 0, false, false, 0, 'T', true);
                            $this->pdf->MultiCell(24, 10, number_format($philhealth_user_total_total,2,".",","), 0, 'C', false, 0, 51, 186, true, 0, false, false, 0, 'T', true);
                            $this->pdf->MultiCell(24.5, 10, $total_employee, 0, 'C', false, 0, 107.5, 186, true, 0, false, false, 0, 'T', true);
                            break;
                    }
                }
                $this->pdf->MultiCell(12.5, 10, $i, 0, 'C', false, 0, 304, 198.5, true, 0, false, false, 0, 'T', true);
                $this->pdf->MultiCell(12.5, 10, $number_of_page, 0, 'C', false, 0, 318, 198.5, true, 0, false, false, 0, 'T', true);
            }   
        }
        else
        {
            $this->pdf->addPage('P', 'LETTER', true);
            $this->pdf->SetXY(100, 20);
            $this->pdf->writeHTML("No Record Found!", true, false, true, false, '');
        }
    }

    function export_philhealth_certificate($company_id, $employee_id, $date_from, $date_to, $title)
    {
        ini_set("memory_limit", "2048M");
        set_time_limit( 7200 );

        if(!empty($employee_id)){
            $employee_id = 'AND employee_id IN ('.$employee_id.')';
        }
        
        $employee = $this->db->query("SELECT employee_id FROM {$this->db->dbprefix}user WHERE company_id = $company_id $employee_id")->result();

        $current_date = getdate(date("U"));
        
        foreach ($employee as $key => $value) {

            $contribution_qry = "SELECT payroll_date , (SUM(employee) + SUM(company) ) as total_contribution
                                FROM {$this->db->dbprefix}employee_contribution
                                WHERE employee_id = ({$value->employee_id}) AND payroll_date between '{$date_from}' AND '{$date_to}' AND transaction_id = 50
                                GROUP BY MONTH(payroll_date),YEAR(payroll_date)
                                ORDER BY employee_id,transaction_id, payroll_date";
            $contribution_res = $this->db->query($contribution_qry);
            $contribution_count = $contribution_res->num_rows();

            if ($contribution_count != 0)
            {
                $date_from_n = date('F, Y', strtotime($date_from));
                $date_to_n = date('F, Y', strtotime($date_to));
                //$date_to_n = date('m/d/Y', strtotime($date_to));

                $user_dtl_res = $this->db->query("SELECT *,b.sss as sss_id FROM {$this->db->dbprefix}user a 
                                                LEFT JOIN {$this->db->dbprefix}employee b ON  b.employee_id = a.employee_id 
                                                LEFT JOIN {$this->db->dbprefix}user_company_department c ON a.department_id = c.department_id 
                                                LEFT JOIN {$this->db->dbprefix}user_position d ON a.position_id = d.position_id
                                                WHERE a.employee_id = $value->employee_id")->row();
                $salutation = $user_dtl_res->salutation;
                $name = $user_dtl_res->lastname.', '.$user_dtl_res->firstname.' '.($user_dtl_res->aux == ''?'':$user_dtl_res->aux).' '.$user_dtl_res->middleinitial;
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
                                    <td width="100%" style="text-align:left; ">'.$emp_sss_id.' had contributed the <b><u>PhilHealth Premium</u></b> to FIRST BALFOUR, INC covering below Months of Contributions.
                                    </td>
                                </tr>
                            </table>';                    
                    
                    $xcel_cont .= '<div>
                            <table >
                                <tr>
                                    <td colspan="15"></td>
                                    <td colspan="20%" style="text-align:left; "><u>MONTH</u></td>
                                    <td colspan="30%" style="text-align:center; "><u>EE/ER/EC PhilHealth Contribution</u></td>
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

    function export_philhealth_to_disk($company_id, $employee_id, $date_from, $date_to, $title)
    {
        ini_set("memory_limit", "2048M");
        set_time_limit( 7200 );

        $year_num = date("Y",strtotime($date_from));
        $day_num = date("d",strtotime($date_from));
        $month_num = date("m",strtotime($date_from)); 
        $payroll_date = $year_num.$month_num;
        
        $payroll_date_res = $this->db->query('SELECT payroll_date FROM '.$this->db->dbprefix('payroll_closed_transaction')." WHERE payroll_date BETWEEN '{$date_from}' AND '{$date_to}'")->row();
        $p_date = $payroll_date_res->payroll_date;

        $company_setting_res = $this->db->query('SELECT * FROM '.$this->db->dbprefix('user_company').' WHERE company_id = "'.$company_id.'"')->row();      

        $company_name = $company_setting_res->company;
        $company_add = $company_setting_res->address;
        $company_philhealth_id = str_replace('-', '', $company_setting_res->philhealth_no);
        $company_code = $company_setting_res->company_code;
        
        $contribution_data_qry = "  SELECT CASE WHEN e.philhealth = ' ' THEN CASE WHEN e.sss = ' ' THEN e.tin ELSE e.sss END ELSE e.philhealth END AS employee_gov_id,
                                    sum(ec.employee) AS employee_contribution, sum(ec.company) AS employer_contribution, sum(ep.salary) as salary,
                                    lastname, firstname, middleinitial, aux, ep.employee_id as emp_id
                                    FROM {$this->db->dbprefix}employee_contribution ec
                                    LEFT JOIN {$this->db->dbprefix}user u ON ec.employee_id = u.employee_id
                                    LEFT JOIN {$this->db->dbprefix}employee e ON ec.employee_id = e.employee_id
                                    LEFT JOIN {$this->db->dbprefix}employee_payroll ep ON ec.employee_id = ep.employee_id
                                    WHERE ec.company_id = {$company_id}
                                    AND ec.transaction_id = 50
                                    AND EXTRACT(YEAR_MONTH FROM ec.payroll_date) = {$payroll_date}
                                    GROUP BY ep.employee_id
                                    ORDER BY lastname, firstname, middleinitial";
        $contribution_data_res = $this->db->query($contribution_data_qry);
       
        $total_contribution_qry = "  SELECT sum(ec.employee)+ sum(ec.company) AS total_contribution
                                    FROM {$this->db->dbprefix}employee_contribution ec
                                    LEFT JOIN {$this->db->dbprefix}user u ON ec.employee_id = u.employee_id
                                    LEFT JOIN {$this->db->dbprefix}employee e ON ec.employee_id = e.employee_id
                                    LEFT JOIN {$this->db->dbprefix}employee_payroll ep ON ec.employee_id = ep.employee_id
                                    WHERE ec.company_id = {$company_id}
                                    AND ec.transaction_id = 50
                                    AND EXTRACT(YEAR_MONTH FROM ec.payroll_date) = {$payroll_date}
                                    ORDER BY lastname, firstname, middleinitial";
        $total_contribution_res = $this->db->query($total_contribution_qry)->row();
        
        $month_num_q = $month_num;
        switch($month_num_q){
            case 01:
            case 02:
            case 03:
                $quarter = 1;
                break;
            case 04:
            case 05:
            case 06:
                $quarter = 2;
                break;
            case 07:
            case 08:
            case 09:
                $quarter = 3;
                break;
            case 10:
            case 11:
            case 12:
                $quarter = 4;
                break;
        }

        $month_num_p = $month_num;
        switch($month_num_q){
            case 01:
            case 04:
            case 07:
            case 010:
                $period = 1;
                break;
            case 02:
            case 05:
            case 08:
            case 11:
                $period = 2;
                break;
            case 03:
            case 06:
            case 09:
            case 12:
                $period = 3;
                break;
        }

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
        $Data_header = "REMITTANCE REPORT\r\n";
        $Data_header .= $company_name."\r\n";
        $Data_header .= $company_add."\r\n";
        $Data_header .= $company_philhealth_id.$quarter.$year_num.'R'."\r\n"; 
        $Data_header .= "MEMBERS\r\n";
        fwrite($Handle, $Data_header); 

        $total_line = 0;
        foreach ($contribution_data_res->result() as $key => $value) {

            $employee_gov_id = str_pad(str_replace('-','',$value->employee_gov_id ),12," ",STR_PAD_RIGHT);//either of the following SSS PhilHealth TIN

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
            $middleinitial = str_pad(substr(str_replace('.', '',$value->middleinitial ),0,1),1," ",STR_PAD_RIGHT);
            $emp_salary = $this->encrypt->decode( $value->salary );
            $employee_salary = str_pad(str_replace('.', '',number_format($emp_salary,2,'.','')),8,"0",STR_PAD_LEFT);
            $employee_contribution = sprintf('%06d',str_pad(str_replace('.', '', number_format($value->employee_contribution,2,'.','')),6));
            $employer_contribution = sprintf('%06d',str_pad(str_replace('.', '', number_format($value->employer_contribution,2,'.','')),6));
            
            $per1 = str_pad("0",12,"0",STR_PAD_LEFT);
            $per2 = str_pad("0",12,"0",STR_PAD_LEFT);
            $per3 = str_pad("0",12,"0",STR_PAD_LEFT);

            switch($period){
                case 1:
                    $Data = $employee_gov_id.str_replace('*',' ',$lastname).$firstname.$middleinitial.$employee_salary.$employee_contribution.$employer_contribution.$per2.$per3.str_pad("0",15,"0",STR_PAD_LEFT)."\r\n"; 
                    fwrite($Handle, $Data); 
                    break;
                case 2:
                    $Data = $employee_gov_id.str_replace('*',' ',$lastname).$firstname.$middleinitial.$employee_salary.$per1.$employee_contribution.$employer_contribution.$per3.str_pad("0",15,"0",STR_PAD_LEFT)."\r\n"; 
                    fwrite($Handle, $Data); 
                    break;
                case 3:
                    $Data = $employee_gov_id.str_replace('*',' ',$lastname).$firstname.$middleinitial.$employee_salary.$per1.$per2.$employee_contribution.$employer_contribution.str_pad("0",15,"0",STR_PAD_LEFT)."\r\n"; 
                    fwrite($Handle, $Data); 
                    break;
            }
            
            $total_line++;
        }
        $Data_footer = "M5-SUMMARY\r\n";
        $Data_footer .= $period.str_pad(str_replace('.','',$total_contribution_res->total_contribution),15,"0",STR_PAD_LEFT).str_pad($total_line,15,"0",STR_PAD_LEFT).date('mdY',strtotime($p_date)).str_pad($total_line,9,"0",STR_PAD_LEFT)."\r\n";
        $Data_footer .= "GRAND TOTAL".str_pad(str_replace('.','',$total_contribution_res->total_contribution),15,"0",STR_PAD_LEFT)."\r\n";
        fwrite($Handle, $Data_footer); 
        
        fclose($Handle); 
        readfile($File);
        exit();
        // END of To Disk ///
    }
}

/* End of file */
/* Location: system/application */
?>