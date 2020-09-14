<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class bir_report extends MY_Controller
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
        //Select Report Type
        $report_type = array("Monthly ITW", "BIR Certificate of ITW");
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

        //Select Employee
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
                $html = $this->export_monthly_itw($company_id, $employee_id, $date_from, $date_to, "Monthly ITW", $page_hidden);       
                $title = "Monthly ITW";
                break;
            case '1':
                $this->pdf->SetMargins(5, 5, 5);                   
                $this->pdf->SetFontSize( 8 );
                $html = $this->export_bir_certiicate($company_id, $employee_id, $date_from, $date_to, "BIR Certificate ITW");       
                $title = "BIR Certificate ITW";
                break;
        }
        $this->pdf->Output(ucwords(str_replace(" ","_",$title))."_" . date('dmYHis') . '.pdf', 'I');
    }

    //function export_title($company_id, $employee_id, $date_from, $date_to, $title, $page_hidden)
    function export_monthly_itw($company_id, $employee_id, $date_from, $date_to, $title)
    {
        dbug($employee_id); 
    }
    function export_bir_certiicate($company_id, $employee_id, $date_from, $date_to, $title)
    {
        $company_setting_res = $this->db->query('SELECT * FROM '.$this->db->dbprefix('user_company').' WHERE company_id = "'.$company_id.'"')->row();

        $this->pdf->addPage('F', 'FOLIO', true);
        $background = 'uploads/payroll_report/BIR_Form_2316.jpg';
        $this->pdf->SetAutoPageBreak(false, 0);
        $this->pdf->Image($background, 0, 0, 215.9, 330.2, 'JPG', '', '', false, 100, '', false, false, 0, false, 0, false);
        
        $bir_detail_qry = " SELECT CONCAT(u.lastname,', ', u.firstname, ' ', u.middlename) AS employee_name, u.lastname, u.firstname, u.middlename, u.aux,
                            e.tin as tin_id, CONCAT(e.perm_address1, ' ', e.perm_address2, ' ',c.city, ' ', e.perm_province) AS reg_add,
                            e.perm_zipcode AS perm_zipcode, u.birth_date AS birth_date, s.civil_status AS civil_status
                            FROM {$this->db->dbprefix}user u
                            LEFT JOIN {$this->db->dbprefix}employee e ON e.employee_id = u.employee_id
                            LEFT JOIN {$this->db->dbprefix}cities c ON c.city_id = e.perm_city
                            LEFT JOIN {$this->db->dbprefix}civil_status s ON s.civil_status_id = e.civil_status_id
                            WHERE e.employee_id = {$employee_id}
                            LIMIT 1";
        
        $bir_detail_res = $this->db->query($bir_detail_qry);
        
        //period
        //Year
        $year_dt = date('Y',strtotime($date_from)); 
        if(!empty($year_dt))
        {
            $line = 30;
            $yr_m_x = 43.5;   
            //$year_dt = str_replace('-', '', $year_dt);
            for($yr_m_s = 0 ; $yr_m_s <= strlen($year_dt); $yr_m_s++)
            {
                $yr_m_sub = substr($year_dt,$yr_m_s,1);
                $this->pdf->MultiCell(10, 10, $yr_m_sub, 0, 'C', false, 0, $yr_m_x, $line, true, 0, false, false, 0, 'T', true);                   
                $yr_m_x = $yr_m_x + 4;
            }
        }

        //From
        if(!empty($date_from))
        {
            $line = 30;
            $fr_m_x = 141.5;   
            $date_from = str_replace('-', '', $date_from);
            for($fr_m_s = 4 ; $fr_m_s <= strlen($date_from); $fr_m_s++)
            {
                $fr_m_sub = substr($date_from,$fr_m_s,1);
                $this->pdf->MultiCell(10, 10, $fr_m_sub, 0, 'C', false, 0, $fr_m_x, $line, true, 0, false, false, 0, 'T', true);                   
                $fr_m_x = $fr_m_x + 3.5;
            }
        }
        //To
        if(!empty($date_to))
        {
            $line = 30;
            $to_m_x = 181.5;   
            $date_to = str_replace('-', '', $date_to);
            for($to_m_s = 4 ; $to_m_s <= strlen($date_to); $to_m_s++)
            {
                $to_m_sub = substr($date_to,$to_m_s,1);
                $this->pdf->MultiCell(10, 10, $to_m_sub, 0, 'C', false, 0, $to_m_x, $line, true, 0, false, false, 0, 'T', true);   
                $to_m_x = $to_m_x + 3.5;
            }
        }

        foreach ($bir_detail_res->result() as $key => $value) 
        {
            $emp_name = $value->lastname.', '.$value->firstname.' '.$value->aux.' '.$value->middleinitial;
            $tin_id = $value->tin_id;
            $reg_add = $value->reg_add;
            $zip_code = $value->perm_zipcode;
            $birth_date = $value->birth_date;
            $civil_status = $value->civil_status;

            
            //Tin ID
            if(!empty($tin_id))
            {
                $line = 40;
                $tin_id_x = 43.5;
                $tin_id = str_replace("-", " ", $tin_id);
                for($tin_s = 0 ; $tin_s <= strlen($tin_id); $tin_s++)
                {
                    $tin_id_sub = substr($tin_id, $tin_s,1);
                    $this->pdf->MultiCell(10, 10, $tin_id_sub, 0, 'C', false, 0, $tin_id_x, $line, true, 0, false, false, 0, 'T', true);   
                    $tin_id_x = $tin_id_x + 3.95;
                } 
            }
            
            //employee name
            $line = 49;
            $name_x = 18;
            $this->pdf->MultiCell(83, 10, $emp_name, 0, 'L', false, 0, $name_x, $line, true, 0, false, false, 0, 'T', true);   

            
            //permanent/registered address
            $line = 57.7;
            $regadd_x = 18;
            $this->pdf->MultiCell(83, 10, $reg_add, 0, 'L', false, 0, $regadd_x, $line, true, 0, false, false, 0, 'T', true);   

            //ZIP Code 6A
            if(!empty($zip_code))
            {
                $line = 57.7;
                $zip_code_x = 89.5;
                for($zip_s = 0; $zip_s <= strlen($zip_code); $zip_s++ )
                {
                    $zip_code_sub = substr($zip_code, $zip_s,1);
                    $this->pdf->MultiCell(10, 10,  $zip_code_sub, 0, 'C', false, 0, $zip_code_x, $line, true, 0, false, false, 0, 'T', true);   
                    $zip_code_x = $zip_code_x + 4;
                }
            }

            //Birth Date
            // ******** BEGIN ******** //
            //for MM/DD
            if(!empty($birth_date))
            {
                $line = 83.2;
                $bday_x = 14.9;
                $birth_date = str_replace("-", "", $birth_date);
                for($birth_date_s = 4; $birth_date_s <= strlen($birth_date); $birth_date_s++)
                {
                    $birth_date_sub = substr($birth_date, $birth_date_s, 1);
                    $this->pdf->MultiCell(10, 10, $birth_date_sub , 0, 'C', false, 0, $bday_x, $line, true, 0, false, false, 0, 'T', true);   
                    $bday_x = $bday_x + 4.3;
                }
            }
            //for Year
            $birth_date_yr = date('Y',strtotime($birth_date));
            if(!empty($birth_date_yr))
            {
                $line = 83.2;
                $bday_x = 32.5;
                for($birth_date_s = 0; $birth_date_s <= strlen($birth_date_yr); $birth_date_s++)
                {
                    $birth_date_sub_yr = substr($birth_date_yr, $birth_date_s, 1);
                    $this->pdf->MultiCell(10, 10, $birth_date_sub_yr , 0, 'C', false, 0, $bday_x, $line, true, 0, false, false, 0, 'T', true);   
                    $bday_x = $bday_x + 5;
                }
            }
            // ********* END ********* //

            //Exempt Status
            if(!empty($civil_status))
            {
                //$civil_status = 'M';
                switch ($civil_status)
                {
                    case 'Single':
                        $line = 91.15;
                        $cs_x = 32;
                        $this->pdf->MultiCell(10, 10, 'x', 0, 'C', false, 0, $cs_x, $line, true, 0, false, false, 0, 'T', true);   
                        break;
                    
                    case 'Married':
                        $line = 91.15;
                        $cs_x = 61.8;
                        $this->pdf->MultiCell(10, 10, 'x', 0, 'C', false, 0, $cs_x, $line, true, 0, false, false, 0, 'T', true);   
                        break;
                    
                    default:
                        $this->pdf->MultiCell(10, 10, ' ', 0, 'C', false, 0, $cs_x, $line, true, 0, false, false, 0, 'T', true);   
                        break;
                }

            }

        }

        $contribution_res = $this->db->query('SELECT SUM(amount_total) as sum_amount FROM '.$this->db->dbprefix('payroll_closed_summary').' WHERE employee_id = "'.$employee_id.'"'.' AND transaction_code IN ("PHIC_EMP","SSS_EMP","HDMF_EMP","HDMF_EMP")')->row();
        
        $contribution_amount = $contribution_res->sum_amount;
        
        //to update the value will base on the query !!!!
        $rdo_code= "123"; //for testing not real value of rdo
        //RDO Code
        if(!empty($rdo_code))
        {
            $line = 49;
            $rdo_code_x = 90;
            for($rdo_s = 0; $rdo_s <= strlen($rdo_code); $rdo_s++)
            {
                $rdo_code_sub = substr($rdo_code, $rdo_s,1);
                $this->pdf->MultiCell(10, 10, $rdo_code_sub, 0, 'C', false, 0, $rdo_code_x, $line, true, 0, false, false, 0, 'T', true);   
                $rdo_code_x = $rdo_code_x + 5.8;
            }
        }
        
        // DEPENDENT DETAILS
        
        // ******** BEGIN ******** //
        $depend = "SELECT name, birth_date, record_id FROM hr_employee_family
            WHERE employee_id = {$employee_id} AND bir_dependents = 1
            ORDER BY birth_date";
        $depend_res = $this->db->query($depend);
        
        foreach ($depend_res->result() as $depends) {
            $line = 105.4;
            $depend_x = 18;
            $this->pdf->MultiCell(70, 10, $depends->name, 0, 'L', false, 0, $depend_x, $line, true, 0, false, false, 0, 'T', true);           
            

            //dependent birth day
            $depend_birht_date = $depends->birth_date; //for testing value
           
            //MM-DD
            if(!empty($depend_birht_date))
            {
                $line = 105.4;
                $depend_x = 72.6;   
                $depend_birht_date = str_replace("-", "", $depend_birht_date);
                for($depend_bday_s = 4; $depend_bday_s <= strlen($depend_birht_date); $depend_bday_s++)
                {
                    $depend_bday_sub = substr($depend_birht_date, $depend_bday_s, 1);
                    $this->pdf->MultiCell(10, 10, $depend_bday_sub , 0, 'C', false, 0, $depend_x, $line, true, 0, false, false, 0, 'T', true);   
                    $depend_x = $depend_x + 4.1;
                }
            }
            
            //yearf
            $depend_bday_year1 = date('Y', strtotime($depend_birht_date));
            if(!empty($depend_bday_year1))
            {
                $line = 105.4;
                $depend_x = 89.5;   
                for($depend_bday_s = 0; $depend_bday_s <= strlen($depend_bday_year1); $depend_bday_s++)
                {
                    $depend_bday_sub_y1 = substr($depend_bday_year1, $depend_bday_s, 1);
                    $this->pdf->MultiCell(10, 10, $depend_bday_sub_y1 , 0, 'C', false, 0, $depend_x, $line, true, 0, false, false, 0, 'T', true);   
                    $depend_x = $depend_x + 4.1;
                }
            }
        }
        // ********* END ********* //

        //statutory minimum wage rate per day

        $min_wage_rate_per_day = "404.00"; //for testing value
        $line = 122;
        $min_wage_x = 74.6;
        $this->pdf->MultiCell(35, 10, $min_wage_rate_per_day, 0, 'R', false, 0, $min_wage_x, $line, true, 0, false, false, 0, 'T', true);   
        
        //statutory minimum wage rate per month

        $min_wage_rate_per_month = "10504.00"; //for testing value
        $line = 127.5;
        $min_wage_x = 74.6;
        $this->pdf->MultiCell(35, 10, $min_wage_rate_per_month, 0, 'R', false, 0, $min_wage_x, $line, true, 0, false, false, 0, 'T', true);   

        //Part IV-A SUMMARY
        
        //******** START ********//

        $item_x = 64;
        
        // Item #21 = Item #41 + Item #55
        $item21_val = "21.00"; //for testing value
        $line = 198.5;
        $this->pdf->MultiCell(45, 10, $item21_val, 0, 'R', false, 0, $item_x, $line, true, 0, false, false, 0, 'T', true);   

        // Item #22 = Item #41
        $item22_val = "22.00"; //for testing value
        $line = 203.5;
        $this->pdf->MultiCell(45, 10, $item22_val, 0, 'R', false, 0, $item_x, $line, true, 0, false, false, 0, 'T', true);   

        // Item #23 = Item #55
        $item23_val = "23.00"; //for testing value
        $line = 209;
        $this->pdf->MultiCell(45, 10, $item23_val, 0, 'R', false, 0, $item_x, $line, true, 0, false, false, 0, 'T', true);   

        // Item #24
        $item24_val = "24.00"; //for testing value
        $line = 213.8;
        $this->pdf->MultiCell(45, 10, $item24_val, 0, 'R', false, 0, $item_x, $line, true, 0, false, false, 0, 'T', true);   

        // Item #25
        $item25_val = "25.00"; //for testing value
        $line = 219;
        $this->pdf->MultiCell(45, 10, $item25_val, 0, 'R', false, 0, $item_x, $line, true, 0, false, false, 0, 'T', true);   

        // Item #26
        $item26_val = "26.00"; //for testing value
        $line = 224.4;
        $this->pdf->MultiCell(45, 10, $item26_val, 0, 'R', false, 0, $item_x, $line, true, 0, false, false, 0, 'T', true);   

        // Item #27
        $item27_val = "27.00"; //for testing value
        $line = 229.4;
        $this->pdf->MultiCell(45, 10, $item27_val, 0, 'R', false, 0, $item_x, $line, true, 0, false, false, 0, 'T', true);   

        // Item #28
        $item28_val = "28.00"; //for testing value
        $line = 234.6;
        $this->pdf->MultiCell(45, 10, $item28_val, 0, 'R', false, 0, $item_x, $line, true, 0, false, false, 0, 'T', true);   

        // Item #29
        $item29_val = "29.00"; //for testing value
        $line = 239.8;
        $this->pdf->MultiCell(45, 10, $item29_val, 0, 'R', false, 0, $item_x, $line, true, 0, false, false, 0, 'T', true);   

        // Item #30A
        $item30A_val = "30A.00"; //for testing value
        $line = 246.8;
        $this->pdf->MultiCell(45, 10, $item30A_val, 0, 'R', false, 0, $item_x, $line, true, 0, false, false, 0, 'T', true);   

        // Item #30B
        $item30B_val = "30B.00"; //for testing value
        $line = 252.2;
        $this->pdf->MultiCell(45, 10, $item30B_val, 0, 'R', false, 0, $item_x, $line, true, 0, false, false, 0, 'T', true);   

        // Item #31
        $item31_val = "31.00"; //for testing value
        $line = 257.8;
        $this->pdf->MultiCell(45, 10, $item31_val, 0, 'R', false, 0, $item_x, $line, true, 0, false, false, 0, 'T', true);   

        //********* END *********//   

        //Part IV-B Details of Compensation Income and Tax WithHeld from Present Employer
        
        //******** START ********//

        // A. Non-Taxable/Exempt Compensation Income

        $item_x = 155;
        
        // Item #32
        $item32_val = "32.00"; //for testing value
        $line = 49.4;
        $this->pdf->MultiCell(45, 10, $item32_val, 0, 'R', false, 0, $item_x, $line, true, 0, false, false, 0, 'T', true);   

        // Item #33
        $item33_val = "33.00"; //for testing value
        $line = 59.8;
        $this->pdf->MultiCell(45, 10, $item33_val, 0, 'R', false, 0, $item_x, $line, true, 0, false, false, 0, 'T', true);   

        // Item #34
        $item34_val = "34.00"; //for testing value
        $line = 67;
        $this->pdf->MultiCell(45, 10, $item34_val, 0, 'R', false, 0, $item_x, $line, true, 0, false, false, 0, 'T', true);   

        // Item #35
        $item35_val = "35.00"; //for testing value
        $line = 73.8;
        $this->pdf->MultiCell(45, 10, $item35_val, 0, 'R', false, 0, $item_x, $line, true, 0, false, false, 0, 'T', true);   

        // Item #36
        $item36_val = "36.00"; //for testing value
        $line = 81;
        $this->pdf->MultiCell(45, 10, $item36_val, 0, 'R', false, 0, $item_x, $line, true, 0, false, false, 0, 'T', true);   

        // Item #32
        $item21_val = "21.00"; //for testing value
        $line = 198.5;
        $this->pdf->MultiCell(45, 10, $item21_val, 0, 'R', false, 0, $item_x, $line, true, 0, false, false, 0, 'T', true);   

        // Item #22 = Item #41
        $item22_val = "22.00"; //for testing value
        $line = 203.5;
        $this->pdf->MultiCell(45, 10, $item22_val, 0, 'R', false, 0, $item_x, $line, true, 0, false, false, 0, 'T', true);   

        // Item #23 = Item #55
        $item23_val = "23.00"; //for testing value
        $line = 209;
        $this->pdf->MultiCell(45, 10, $item23_val, 0, 'R', false, 0, $item_x, $line, true, 0, false, false, 0, 'T', true);   

        // Item #24
        $item24_val = "24.00"; //for testing value
        $line = 213.8;
        $this->pdf->MultiCell(45, 10, $item24_val, 0, 'R', false, 0, $item_x, $line, true, 0, false, false, 0, 'T', true);   

        // Item #25
        $item25_val = "25.00"; //for testing value
        $line = 219;
        $this->pdf->MultiCell(45, 10, $item25_val, 0, 'R', false, 0, $item_x, $line, true, 0, false, false, 0, 'T', true);   

        // Item #26
        $item26_val = "26.00"; //for testing value
        $line = 224.4;
        $this->pdf->MultiCell(45, 10, $item26_val, 0, 'R', false, 0, $item_x, $line, true, 0, false, false, 0, 'T', true);   

        // Item #27
        $item27_val = "27.00"; //for testing value
        $line = 229.4;
        $this->pdf->MultiCell(45, 10, $item27_val, 0, 'R', false, 0, $item_x, $line, true, 0, false, false, 0, 'T', true);   

        // Item #28
        $item28_val = "28.00"; //for testing value
        $line = 234.6;
        $this->pdf->MultiCell(45, 10, $item28_val, 0, 'R', false, 0, $item_x, $line, true, 0, false, false, 0, 'T', true);   

        // Item #29
        $item29_val = "29.00"; //for testing value
        $line = 239.8;
        $this->pdf->MultiCell(45, 10, $item29_val, 0, 'R', false, 0, $item_x, $line, true, 0, false, false, 0, 'T', true);   

        // Item #30A
        $item30A_val = "30A.00"; //for testing value
        $line = 246.8;
        $this->pdf->MultiCell(45, 10, $item30A_val, 0, 'R', false, 0, $item_x, $line, true, 0, false, false, 0, 'T', true);   

        // Item #30B
        $item30B_val = "30B.00"; //for testing value
        $line = 252.2;
        $this->pdf->MultiCell(45, 10, $item30B_val, 0, 'R', false, 0, $item_x, $line, true, 0, false, false, 0, 'T', true);   

        // Item #31
        $item31_val = "31.00"; //for testing value
        $line = 257.8;
        $this->pdf->MultiCell(45, 10, $item31_val, 0, 'R', false, 0, $item_x, $line, true, 0, false, false, 0, 'T', true);   

        //********* END *********//   

        
    }
}

/* End of file */
/* Location: system/application */
?>