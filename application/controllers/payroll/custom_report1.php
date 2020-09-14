<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class custom_report1 extends my_controller
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

    function get_parameters(){
        $report_type = array("Allotment By Vessel","Vessel Costing","Vessel Costing (Exclusion)","Embark/Disembark","Payroll Sheet","Payroll Summary Per Vessel");
        $report_type_html = '<select id="report_type_id" name="report_type_id">';
            foreach($report_type as $report_type_id => $report_type_value){
                $report_type_html .= '<option value="'.$report_type_id.'">'.$report_type_value.'</option>';
            }
        $report_type_html .= '</select>'; 

        $company = $this->db->query('SELECT * FROM '.$this->db->dbprefix('user_company').'')->result_array();
        $company_html = '<select id="company_id" multiple="multiple" class="multi-select" name="company_id[]">';
            foreach($company as $company_record){
                $company_html .= '<option value="'.$company_record["company_id"].'">'.$company_record["company"].'</option>';
            }
        $company_html .= '</select>';

        $employee = $this->db->query("SELECT
                                          b.firstname,
                                          b.middlename,
                                          b.lastname,
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
                $employee_html .= '<option value="'.$employee_record["employee_id"].'">'.$employee_record["lastname"].' '.$employee_record["firstname"].'</option>';
            }
        $employee_html .= '</select>';        

        $response->report_type_html = $report_type_html;
        $response->employee_html = $employee_html;
        $response->company_html = $company_html;
        $data['json'] = $response;
        $this->load->view('template/ajax', $data);  
    }

    function employee_multiple(){
        $qry_com = " AND 1";
        $qry_sta = " AND 1";
        $qry_sch = " AND 1";
        if(isset($_POST['company_id']))
        {
            $company_arr = array();
            foreach ($_POST['company_id'] as $value) 
            {
                $company_arr[] = $value;    
            }
            $company_id = implode(',', $company_arr);
            if(!empty($company_id))
            {
                $qry_com = " AND b.company_id IN ({$company_id})";
            }
        }

        $employee = $this->db->query("SELECT
                                          b.firstname,
                                          b.middlename,
                                          b.lastname,
                                          b.company_id,
                                          c.payroll_schedule_id,
                                          a.status_id,
                                          a.employee_id
                                        FROM {$this->db->dbprefix}employee a
                                          LEFT JOIN {$this->db->dbprefix}user b
                                            ON a.employee_id = b.employee_id
                                          LEFT JOIN {$this->db->dbprefix}employee_payroll c
                                            ON a.employee_id = c.employee_id 
                                        WHERE 1 {$qry_com} {$qry_sta} {$qry_sch} 
                                        ORDER BY b.lastname")->result_array();
        $employee_html = '<select id="employee_id" multiple="multiple" class="multi-select" name="employee_id[]">';

        // dbug($this->db->last_query());

        foreach($employee as $employee_record){
            $employee_html .= '<option value="'.$employee_record["employee_id"].'">'.$employee_record["lastname"].' '.$employee_record["firstname"].'</option>';
        }
        $employee_html .= '</select>';
        $response->employee_html = $employee_html;

        $data['json'] = $response;
        $this->load->view('template/ajax', $data);  
    }

    function export_report(){
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

        $payroll_date = date("Y-m-d",strtotime($_POST['date']));  

        $this->load->library('pdf');

        switch ($_POST['report_type_id']) 
        {
            //Allotment By Vessel
            case '0':
                $html = $this->export_allotment($company_id, $employee_id, $payroll_date, "Allotment Report By Vessel");
                $title = "Allotment Report By Vessel";
                break;            
            //Vessel Coating
            case '1':
                $html = $this->export_vessel_costing($company_id, $employee_id, $payroll_date, "Vessel Coating Report");
                $title = "Vessel Costing Report";
                break;
            //Vessel Coating (Exclusion)
            case '2':
                $html = $this->export_vessel_costing_exclusion($company_id, $employee_id, $payroll_date, "Vessel Coating Report (Exclusion)");
                $title = "Vessel Costing Report (Exclusion)";
                break;
            //Embark/Disembark
            case '3':
            	$html = $this->export_embark_disembark($company_id, $employee_id, $payroll_date, "Embark / Disembark Report");
                $title = "Embark / Disembark Report";
                break;
            //Payroll Sheet
         	case '4':
            	$html = $this->export_payroll_sheet($company_id, $employee_id, $payroll_date, "Payroll Sheet");
                $title = "Payroll Sheet";
                break;
            //Payroll Summary Per Vessel
            case '5':
                $html = $this->export_payroll_summary_per_vessel($company_id, $employee_id, $payroll_date, "Payroll Summary Per Vessel");
                $title = "Payroll Summary Per Vessel";
                break;
        }
        $this->pdf->writeHTML($html, true, false, true, false, '');
        $this->pdf->Output(ucwords(str_replace(" ","_",$title))."_" . date('dmYHis') . '.pdf', 'I');
    }

    function export_allotment($company_id, $employee_id, $payroll_date, $title){
        $company_setting_res = $this->db->query('SELECT * FROM '.$this->db->dbprefix('user_company').' WHERE company_id = "'.$company_id.'"')->row();
        
        $this->db->where('payroll_date',$payroll_date);
        $period = $this->db->get('payroll_period')->row();
        $date_from = $period->date_from;
        $date_to = $period->date_to;

        if($period->period_status_id == 3){
            $transaction = "payroll_closed_transaction";
        }
        else{
            $transaction = "payroll_current_transaction";
        }

        if(!empty($company_id)){
            $company = " AND company_id IN ($company_id)";
        }
        if(!empty($employee_id)){
            $employee = " AND p.employee_id IN ($employee_id)";   
        }

        $qry = "  SELECT vessel_code, vessel, sum(amount) as allotment 
                            FROM {$this->db->dbprefix}$transaction p
                            LEFT JOIN {$this->db->dbprefix}employee e ON p.employee_id = e.employee_id
                            LEFT JOIN {$this->db->dbprefix}user u ON u.employee_id = e.employee_id
                            LEFT JOIN {$this->db->dbprefix}vessel v ON v.vessel_id = e.vessel_id 
                            WHERE 1 AND payroll_date = '{$payroll_date}' AND e.vessel_id IS NOT NULL AND transaction_code = 'NETPAY' $company $employee
                            GROUP BY vessel_code,vessel
                            ORDER BY vessel_code";
        
        $res = $this->db->query($qry);
        $cnt = 1;
        $total_no_employees = $res->num_rows();
        $allowed_count_per_page = 45;
        $page_with = $total_no_employees/$allowed_count_per_page;
        $page_floor = floor($page_with);

        $number_of_page = $page_floor;
        if($page_with > $page_floor)
        {
            $number_of_page = $page_floor + 1;
        }        
        if($total_no_employees != 0)
        {
            // $vessel_allotment = 0;
            
            for($i=1;$i<=$number_of_page; $i++)
            {                   
                $xcel_hed = '';
                $this->pdf->SetMargins(10, 10, 10, true);
                $this->pdf->addPage('P', 'LETTER', true);
                $this->pdf->SetFontSize( 10 );                
                $xcel_hed .= '';
                $xcel_hed .= '  <table style="width:100%;">
                                    <tr>
                                        <td style=" width:50%  ; text-align:left   ; font-size:9  ; ">'.$company_setting_res->company.'</td>
                                        <td style=" width:50%  ; text-align:right  ; font-size:9  ; ">Page '.$i.' of '.$number_of_page.'</td>
                                    </tr>
                                    <tr>
                                        <td style=" width:100% ; text-align:left   ; font-size:9  ; ">'.date("m/d/Y H:i:s").'</td>
                                    </tr>
                                    <tr><td></td></tr>
                                    <tr>
                                        <td style=" width:100% ; text-align:center ; font-size:11 ; "><h2>'.$title.'</h2></td>
                                    </tr>
                                    <tr>
                                        <td style=" width:100% ; text-align:center ; font-size:10 ; ">Pay Period: '.date("m/d/Y",strtotime($date_from)).' To: '.date("m/d/Y",strtotime($date_to)).'</td>
                                    </tr>
                                    <tr><td></td></tr>
                                    <tr>
                                        <td style=" width:20%  ; text-align:left   ; "><b>Vessel Code</b></td>
                                        <td style=" width:60%  ; text-align:left   ; "><b>Name</b></td>
                                        <td style=" width:20%  ; text-align:right  ; "><b>Net Pay</b></td>
                                    </tr>
                                    <tr>
                                        <td style=" width:100% ; font-size:2 ; "></td>
                                    </tr>
                                    <tr>
                                        <td style=" width:100% ; font-size:2 ; border-top-width:3px ; "></td>
                                    </tr>';

                $detail_qry = "   SELECT vessel_code, vessel, sum(amount) as allotment
                                            FROM {$this->db->dbprefix}$transaction p
                                            LEFT JOIN {$this->db->dbprefix}employee e ON p.employee_id = e.employee_id
                                            LEFT JOIN {$this->db->dbprefix}user u ON u.employee_id = e.employee_id
                                            LEFT JOIN {$this->db->dbprefix}vessel v ON v.vessel_id = e.vessel_id 
                                            WHERE 1 AND payroll_date = '{$payroll_date}' AND e.vessel_id IS NOT NULL AND transaction_code = 'NETPAY' $company $employee
                                            GROUP BY vessel_code,vessel
                                            ORDER BY vessel_code";
                $limit = ($i - 1) * $allowed_count_per_page;
                $detail_qry .= " LIMIT {$limit},{$allowed_count_per_page}";
                $detail_res = $this->db->query($detail_qry);
                
                $count = 0;
                foreach ($detail_res->result() as $key => $value) 
                {            
                    $xcel_hed .= '  <tr>
                                        <td style=" width:20%  ; text-align:left  ; ">'.$value->vessel_code.'</td>
                                        <td style=" width:60%  ; text-align:left  ; ">'.$value->vessel.'</td>
                                        <td style=" width:20%  ; text-align:right ; ">'.number_format($value->allotment,2,'.',',').'</td>
                                    </tr>';
                    $total_vessel_allotment += $value->allotment;
                    $grand_total_vessel_allotment += $value->allotment;
                    $count++;
                }
                
                if($count != $allowed_count_per_page)
                {
                    for ($space=1; $space <= ($allowed_count_per_page - $count); $space++) 
                    {
                        $xcel_hed .= '<tr><td></td></tr>';
                    }   
                }
                
                if($i == $number_of_page)
                {
                    $xcel_hed .= '<tr>
                                    <td style=" width:50%  ; text-align:left   ; background-color:#DDDDDD ; "><b>Grand Total</b></td>
                                    <td style=" width:50%  ; text-align:right  ; background-color:#DDDDDD ; "><b>'.number_format($grand_total_vessel_allotment,2,'.',',').'</b></td>
                                </tr>';
                }
                else{
                    $xcel_hed .= '  <tr>
                                    <td style=" width:50%  ; text-align:left   ; background-color:#DDDDDD ; "><b>Page Total</b></td>
                                    <td style=" width:50%  ; text-align:right  ; background-color:#DDDDDD ; "><b>'.number_format($total_vessel_allotment,2,'.',',').'</b></td>
                                </tr>';
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

    function export_vessel_costing($company_id, $employee_id, $payroll_date, $title){
        $company_setting_res = $this->db->query('SELECT * FROM '.$this->db->dbprefix('user_company').' WHERE company_id = "'.$company_id.'"')->row();
        
        $this->db->where('payroll_date',$payroll_date);
        $period = $this->db->get('payroll_period')->row();
        $date_from = $period->date_from;
        $date_to = $period->date_to;

        if($period->period_status_id == 3){
            $transaction = "payroll_closed_transaction";
        }
        else{
            $transaction = "payroll_current_transaction";
        }

        if(!empty($company_id)){
            $company = " AND company_id IN ($company_id)";
        }
        if(!empty($employee_id)){
            $employee = " AND p.employee_id IN ($employee_id)";   
        }


        $qry = "SELECT vessel_code, vessel, COUNT(DISTINCT e.employee_id) as no_of_employee
                FROM {$this->db->dbprefix}$transaction p
                LEFT JOIN {$this->db->dbprefix}employee e ON p.employee_id = e.employee_id
                LEFT JOIN {$this->db->dbprefix}user u ON p.employee_id = u.employee_id
                LEFT JOIN {$this->db->dbprefix}vessel v ON v.vessel_id = e.vessel_id
                WHERE 1 AND payroll_date = '{$payroll_date}' AND e.vessel_id IS NOT NULL AND (e.bank_account_no != '' AND e.bank_account_no IS NOT NULL) $company $employee
                GROUP BY vessel_code, vessel
                ORDER BY vessel_code";

        $res = $this->db->query($qry);
        $cnt = 1;
        $total_no_employees = $res->num_rows();
        $allowed_count_per_page = 45;
        $page_with = $total_no_employees/$allowed_count_per_page;
        $page_floor = floor($page_with);

        $number_of_page = $page_floor;
        if($page_with > $page_floor)
        {
            $number_of_page = $page_floor + 1;
        }        
        if($total_no_employees != 0)
        {
            for($i=1;$i<=$number_of_page; $i++)
            {                   
                $xcel_hed = '';
                $this->pdf->SetMargins(10, 10, 10, true);
                $this->pdf->addPage('P', 'LETTER', true);   
                $this->pdf->SetFontSize( 10 );             
                $xcel_hed .= '';
                $xcel_hed .= '  <table style="width:100%;">
                                     <tr>
                                        <td style=" width:50%  ; text-align:left   ; font-size:9  ; ">'.$company_setting_res->company.'</td>
                                        <td style=" width:50%  ; text-align:right  ; font-size:9  ; ">Page '.$i.' of '.$number_of_page.'</td>
                                    </tr>
                                    <tr>
                                        <td style=" width:100% ; text-align:left   ; font-size:9  ; ">'.date("m/d/Y H:i:s").'</td>
                                    </tr>
                                    <tr><td></td></tr>
                                    <tr>
                                        <td style=" width:100% ; text-align:center ; font-size:11 ; "><h2>'.$title.'</h2></td>
                                    </tr>
                                    <tr>
                                        <td style=" width:100% ; text-align:center ; font-size:10 ; ">Pay Period: '.date("m/d/Y",strtotime($date_from)).' To: '.date("m/d/Y",strtotime($date_to)).'</td>
                                    </tr>
                                    <tr><td></td></tr>
                                    <tr>
                                        <td style=" width:15%  ; text-align:left   ; "><b>Vessel Code</b></td>
                                        <td style=" width:30%  ; text-align:left   ; "><b>Description</b></td>
                                        <td style=" width:19%  ; text-align:right  ; "><b>No. of Employees</b></td>
                                        <td style=" width:18%  ; text-align:right  ; "><b>Gross Pay</b></td>
                                        <td style=" width:18%  ; text-align:right  ; "><b>Net Pay</b></td>
                                    </tr>
                                    <tr>
                                        <td style=" width:100% ; font-size:2 ; "></td>
                                    </tr>
                                    <tr>
                                        <td style=" width:100% ; font-size:2 ; border-top-width:3px ; "></td>
                                    </tr>';

                $detail_qry = " SELECT vessel_code, vessel, COUNT(DISTINCT e.employee_id) as no_of_employee
                                FROM {$this->db->dbprefix}$transaction p
                                LEFT JOIN {$this->db->dbprefix}employee e ON p.employee_id = e.employee_id
                                LEFT JOIN {$this->db->dbprefix}user u ON p.employee_id = u.employee_id
                                LEFT JOIN {$this->db->dbprefix}vessel v ON v.vessel_id = e.vessel_id
                                WHERE 1 AND payroll_date = '{$payroll_date}' AND e.vessel_id IS NOT NULL AND (e.bank_account_no != '' AND e.bank_account_no IS NOT NULL) $company $employee
                                GROUP BY vessel_code, vessel
                                ORDER BY vessel_code";
                
                $limit = ($i - 1) * $allowed_count_per_page;
                $vessel_costing_detail_qry .= " LIMIT {$limit},{$allowed_count_per_page}";

                $detail_res = $this->db->query($detail_qry);
                
                $count = 0;
                foreach ($detail_res->result() as $key => $value) 
                {            
                    $gross_pay_qry = "  SELECT sum(amount) as gross_pay
                                        FROM {$this->db->dbprefix}$transaction p
                                        LEFT JOIN {$this->db->dbprefix}employee e ON p.employee_id = e.employee_id
                                        LEFT JOIN {$this->db->dbprefix}user u ON p.employee_id = u.employee_id
                                        LEFT JOIN {$this->db->dbprefix}vessel v ON v.vessel_id = e.vessel_id
                                        WHERE 1 AND payroll_date = '{$payroll_date}' AND vessel_code = '{$value->vessel_code}' AND transaction_code = 'SALARY'
                                            AND e.vessel_id IS NOT NULL AND (e.bank_account_no != '' AND e.bank_account_no IS NOT NULL) $company $employee
                                        GROUP BY vessel_code, vessel";

                    $gross_pay_res = $this->db->query($gross_pay_qry)->row();

                    $net_pay_qry = "SELECT sum(amount) as net_pay
                                    FROM {$this->db->dbprefix}$transaction p
                                    LEFT JOIN {$this->db->dbprefix}employee e ON p.employee_id = e.employee_id
                                    LEFT JOIN {$this->db->dbprefix}user u ON p.employee_id = u.employee_id
                                    LEFT JOIN {$this->db->dbprefix}vessel v ON v.vessel_id = e.vessel_id
                                    WHERE 1 AND payroll_date = '{$payroll_date}' AND vessel_code = '{$value->vessel_code}' AND transaction_code = 'NETPAY' $company $employee
                                        AND e.vessel_id IS NOT NULL AND (e.bank_account_no != '' AND e.bank_account_no IS NOT NULL)
                                    GROUP BY vessel_code, vessel";

                    $net_pay_res = $this->db->query($net_pay_qry)->row();
                    
                    $xcel_hed .= '  <tr>
                                        <td style=" width:15%  ; text-align:left   ; ">'.$value->vessel_code.'</td>
                                        <td style=" width:30%  ; text-align:left   ; ">'.$value->vessel.'</td>
                                        <td style=" width:19%  ; text-align:right  ; ">'.$value->no_of_employee.'</td> 
                                        <td style=" width:18%  ; text-align:right  ; ">'.number_format($gross_pay_res->gross_pay,2,'.',',').'</td>
                                        <td style=" width:18%  ; text-align:right  ; ">'.number_format($net_pay_res->net_pay,2,'.',',').'</td>
                                    </tr>';

                    $total_employee += $value->no_of_employee;
                    $total_gross_pay += $gross_pay_res->gross_pay;
                    $total_net_pay += $net_pay_res->net_pay;
                    $grand_total_employee += $value->no_of_employee;
                    $grand_total_gross_pay += $gross_pay_res->gross_pay;
                    $grand_total_net_pay += $net_pay_res->net_pay;
                    $count++;
                }
                
                if($count != $allowed_count_per_page)
                {
                    for ($space=1; $space <= ($allowed_count_per_page - $count); $space++) 
                    {
                        $xcel_hed .= '<tr><td></td></tr>';
                    }   
                }
                
                if($i == $number_of_page)
                {
                    $xcel_hed .= '<tr>
                                    <td style=" width:45%  ; text-align:left   ; background-color:#DDDDDD ; "><b>Grand Total</b></td>
                                    <td style=" width:19%  ; text-align:right  ; background-color:#DDDDDD ; "><b>'.$grand_total_employee.'</b></td>
                                    <td style=" width:18%  ; text-align:right  ; background-color:#DDDDDD ; "><b>'.number_format($grand_total_gross_pay,2,'.',',').'</b></td>
                                    <td style=" width:18%  ; text-align:right  ; background-color:#DDDDDD ; "><b>'.number_format($grand_total_net_pay,2,'.',',').'</b></td>
                                </tr>';
                }
                else{
                    $xcel_hed .= '<tr>
                                    <td style=" width:45%  ; text-align:left   ; background-color:#DDDDDD ; "><b>Page Total</b></td>
                                    <td style=" width:19%  ; text-align:right  ; background-color:#DDDDDD ; "><b>'.$total_employee.'</b></td>
                                    <td style=" width:15%  ; text-align:right  ; background-color:#DDDDDD ; "><b>'.number_format($total_gross_pay,2,'.',',').'</b></td>
                                    <td style=" width:15%  ; text-align:right  ; background-color:#DDDDDD ; "><b>'.number_format($total_net_pay,2,'.',',').'</b></td>
                                </tr>';    
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

    function export_vessel_costing_exclusion($company_id, $employee_id, $payroll_date, $title){
        $company_setting_res = $this->db->query('SELECT * FROM '.$this->db->dbprefix('user_company').' WHERE company_id = "'.$company_id.'"')->row();
        
        $this->db->where('payroll_date',$payroll_date);
        $period = $this->db->get('payroll_period')->row();
        $date_from = $period->date_from;
        $date_to = $period->date_to;

        if($period->period_status_id == 3){
            $transaction = "payroll_closed_transaction";
        }
        else{
            $transaction = "payroll_current_transaction";
        }

        if(!empty($company_id)){
            $company = " AND company_id IN ($company_id)";
        }
        if(!empty($employee_id)){
            $employee = " AND p.employee_id IN ($employee_id)";   
        }

        $qry = "SELECT vessel_code, vessel, COUNT(DISTINCT e.employee_id) as no_of_employee
                FROM {$this->db->dbprefix}$transaction p 
                LEFT JOIN {$this->db->dbprefix}employee e ON p.employee_id = e.employee_id
                LEFT JOIN {$this->db->dbprefix}user u ON p.employee_id = u.employee_id
                LEFT JOIN {$this->db->dbprefix}vessel v ON v.vessel_id = e.vessel_id
                WHERE 1 AND payroll_date = '{$payroll_date}' AND e.vessel_id IS NOT NULL AND (e.bank_account_no IS NULL OR e.bank_account_no = '') $company $employee
                GROUP BY vessel_code,vessel
                ORDER BY vessel_code";

        $res = $this->db->query($qry);
        $cnt = 1;
        $total_no_employees = $res->num_rows();
        $allowed_count_per_page = 50;
        $page_with = $total_no_employees/$allowed_count_per_page;
        $page_floor = floor($page_with);

        $number_of_page = $page_floor;
        if($page_with > $page_floor)
        {
            $number_of_page = $page_floor + 1;
        }        
        if($total_no_employees != 0)
        {
            for($i=1;$i<=$number_of_page; $i++)
            {                   
                $xcel_hed = '';
                $this->pdf->addPage('P', 'LETTER', true);                
                $xcel_hed .= '';
                $xcel_hed .= '  <table style="width:100%;">
                                    <tr>
                                        <td style=" width:50%  ; text-align:left   ; font-size:9  ; ">'.$company_setting_res->company.'</td>
                                        <td style=" width:50%  ; text-align:right  ; font-size:9  ; ">Page '.$i.' of '.$number_of_page.'</td>
                                    </tr>
                                    <tr>
                                        <td style=" width:100% ; text-align:left   ; font-size:9  ; ">'.date("m/d/Y H:i:s").'</td>
                                    </tr>
                                    <tr><td></td></tr>
                                    <tr>
                                        <td style=" width:100% ; text-align:center ; font-size:11 ; "><h2>'.$title.'</h2></td>
                                    </tr>
                                    <tr>
                                        <td style=" width:100% ; text-align:center ; font-size:10 ; ">Pay Period: '.date("m/d/Y",strtotime($date_from)).' To: '.date("m/d/Y",strtotime($date_to)).'</td>
                                    </tr>
                                    <tr><td></td></tr>
                                    <tr>
                                        <td style=" width:15%  ; text-align:left   ; "><b>Vessel Code</b></td>
                                        <td style=" width:30%  ; text-align:left   ; "><b>Description</b></td>
                                        <td style=" width:19%  ; text-align:right  ; "><b>No. of Employees</b></td>
                                        <td style=" width:18%  ; text-align:right  ; "><b>Gross Pay</b></td>
                                        <td style=" width:18%  ; text-align:right  ; "><b>Net Pay</b></td>
                                    </tr>
                                    <tr>
                                        <td style=" width:100% ; font-size:2 ; "></td>
                                    </tr>
                                    <tr>
                                        <td style=" width:100% ; font-size:2 ; border-top-width:3px ; "></td>
                                    </tr>';

                $detail_qry = " SELECT vessel_code, vessel, COUNT(DISTINCT e.employee_id) as no_of_employee
                                FROM {$this->db->dbprefix}payroll_closed_transaction p
                                LEFT JOIN {$this->db->dbprefix}employee e ON p.employee_id = e.employee_id
                                LEFT JOIN {$this->db->dbprefix}user u ON p.employee_id = u.employee_id
                                LEFT JOIN {$this->db->dbprefix}vessel v ON v.vessel_id = e.vessel_id
                                WHERE 1 AND payroll_date = '{$payroll_date}' AND e.vessel_id IS NOT NULL AND (e.bank_account_no IS NULL OR e.bank_account_no = '') $company $employee
                                GROUP BY vessel_code,vessel
                                ORDER BY vessel_code";

                $limit = ($i - 1) * $allowed_count_per_page;
                $detail_qry .= " LIMIT {$limit},{$allowed_count_per_page}";

                $allotment_detail_res = $this->db->query($detail_qry);
                
                $count = 0;
                foreach ($allotment_detail_res->result() as $key => $value) 
                {   
                    
                    $gross_pay_qry = "  SELECT sum(amount) as gross_pay
                                        FROM {$this->db->dbprefix}payroll_closed_transaction p
                                        LEFT JOIN {$this->db->dbprefix}employee e ON p.employee_id = e.employee_id
                                        LEFT JOIN {$this->db->dbprefix}user u ON p.employee_id = u.employee_id
                                        LEFT JOIN {$this->db->dbprefix}vessel v ON v.vessel_id = e.vessel_id 
                                        WHERE 1 AND payroll_date = '{$payroll_date}' AND vessel_code = '{$value->vessel_code}' AND transaction_code = 'SALARY'  $company $employee
                                            AND e.vessel_id IS NOT NULL AND (e.bank_account_no IS NULL OR e.bank_account_no = '')
                                        GROUP BY vessel_code, vessel";

                    $gross_pay_res = $this->db->query($gross_pay_qry)->row();

                    $net_pay_qry = "SELECT sum(amount) as net_pay
                                    FROM {$this->db->dbprefix}payroll_closed_transaction p
                                    LEFT JOIN {$this->db->dbprefix}employee e ON p.employee_id = e.employee_id
                                    LEFT JOIN {$this->db->dbprefix}user u ON p.employee_id = u.employee_id
                                    LEFT JOIN {$this->db->dbprefix}vessel v ON v.vessel_id = e.vessel_id
                                    WHERE 1 AND payroll_date = '{$payroll_date}' AND vessel_code = '{$value->vessel_code}' AND transaction_code = 'NETPAY'  $company $employee
                                        AND e.vessel_id IS NOT NULL AND (e.bank_account_no IS NULL OR e.bank_account_no = '')
                                    GROUP BY vessel_code, vessel";

                    $net_pay_res = $this->db->query($net_pay_qry)->row();
                    
                    $xcel_hed .= '  <tr>
                                        <td style=" width:15%  ; text-align:left   ; ">'.$value->vessel_code.'</td>
                                        <td style=" width:30%  ; text-align:left   ; ">'.$value->vessel.'</td>
                                        <td style=" width:19%  ; text-align:right  ; ">'.$value->no_of_employee.'</td> 
                                        <td style=" width:18%  ; text-align:right  ; ">'.number_format($gross_pay_res->gross_pay,2,'.',',').'</td>
                                        <td style=" width:18%  ; text-align:right  ; ">'.number_format($net_pay_res->net_pay,2,'.',',').'</td>
                                    </tr>';

                    $total_employee += $value->no_of_employee;
                    $total_gross_pay += $gross_pay_res->gross_pay;
                    $total_net_pay += $net_pay_res->net_pay;
                    $grand_total_employee += $value->no_of_employee;
                    $grand_total_gross_pay += $gross_pay_res->gross_pay;
                    $grand_total_net_pay += $net_pay_res->net_pay;
                    $count++;
                }
                
                if($count != $allowed_count_per_page)
                {
                    for ($space=1; $space <= ($allowed_count_per_page - $count); $space++) 
                    {
                        $xcel_hed .= '<tr><td></td></tr>';
                    }   
                }
                if($i == $number_of_page)
                {
                    $xcel_hed .= '<tr>
                                    <td style=" width:45%  ; text-align:left   ; background-color:#DDDDDD ; "><b>Grand Total</b></td>
                                    <td style=" width:19%  ; text-align:right  ; background-color:#DDDDDD ; "><b>'.$grand_total_employee.'</b></td>
                                    <td style=" width:18%  ; text-align:right  ; background-color:#DDDDDD ; "><b>'.number_format($grand_total_gross_pay,2,'.',',').'</b></td>
                                    <td style=" width:18%  ; text-align:right  ; background-color:#DDDDDD ; "><b>'.number_format($grand_total_net_pay,2,'.',',').'</b></td>
                                </tr>';
                }
                else{
                    $xcel_hed .= '<tr>
                                    <td style=" width:45%  ; text-align:left   ; background-color:#DDDDDD ; "><b>Page Total</b></td>
                                    <td style=" width:19%  ; text-align:right  ; background-color:#DDDDDD ; "><b>'.$total_employee.'</b></td>
                                    <td style=" width:15%  ; text-align:right  ; background-color:#DDDDDD ; "><b>'.number_format($total_gross_pay,2,'.',',').'</b></td>
                                    <td style=" width:15%  ; text-align:right  ; background-color:#DDDDDD ; "><b>'.number_format($total_net_pay,2,'.',',').'</b></td>
                                </tr>';    
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
   
    function export_embark_disembark($company_id, $employee_id, $payroll_date, $title){

        $company_setting_res = $this->db->query('SELECT * FROM '.$this->db->dbprefix('user_company').' WHERE company_id = "'.$company_id.'"')->row();
        
        $this->db->where('payroll_date',$payroll_date);
        $period = $this->db->get('payroll_period')->row();
        // $date_from = date("Y-m-d", strtotime($period->date_from));
        $date_to = date("Y-m-d", strtotime($period->date_to));

        $date_from = date("Y-m-d", strtotime('2013-06-01'));

        $qry = "SELECT * FROM {$this->db->dbprefix}employee_vessel_history WHERE 1 AND (DATE(date_embark) BETWEEN '{$date_from}' 
                AND '{$date_to}' OR DATE(date_disembark) BETWEEN '{$date_from}' AND '{$date_to}')";
        if(!empty($employee_id)){ $qry .= " AND employee_id IN ({$employee_id})"; }

        $res = $this->db->query($qry);
        $cnt = 1;
        $total_no_employees = $res->num_rows();
        $allowed_count_per_page = 9;
        $page_with = $total_no_employees/$allowed_count_per_page;
        $page_floor = floor($page_with);

        $number_of_page = $page_floor;
        if($page_with > $page_floor)
        {
            $number_of_page = $page_floor + 1;
        }        
        if($total_no_employees != 0)
        {
            for($i=1;$i<=$number_of_page; $i++)
            {  
                $xcel_hed = '';
                $this->pdf->SetMargins(5,5,5,5, true);
                $this->pdf->addPage('P', 'LETTER', true); 

                $xcel_hed .= '';
                $xcel_hed .= '  <table style="width:100%;">
                                    <tr>
                                        <td style=" width:50%  ; text-align:left   ; font-size:9  ; ">'.$company_setting_res->company.'</td>
                                        <td style=" width:50%  ; text-align:right  ; font-size:9  ; ">Page '.$i.' of '.$number_of_page.'</td>
                                    </tr>
                                    <tr>
                                        <td style=" width:100% ; text-align:left   ; font-size:9  ; ">'.date("m/d/Y H:i:s").'</td>
                                    </tr>
                                    <tr><td></td></tr>
                                    <tr>
                                        <td style=" width:100% ; text-align:center ; font-size:11 ; "><h2>'.$title.'</h2></td>
                                    </tr>
                                    <tr>
                                        <td style=" width:100% ; text-align:center ; font-size:10 ; ">Pay Period: '.date("m/d/Y",strtotime($date_from)).' To: '.date("m/d/Y",strtotime($date_to)).'</td>
                                    </tr>
                                    <tr><td></td></tr>
                                    <tr>
                                        <td style=" width:15%  ; text-align:left   ; "><b>Vessel Code</b></td>
                                        <td style=" width:30%  ; text-align:left   ; "><b>Description</b></td>
                                        <td style=" width:19%  ; text-align:right  ; "><b>No. of Employees</b></td>
                                        <td style=" width:18%  ; text-align:right  ; "><b>Gross Pay</b></td>
                                        <td style=" width:18%  ; text-align:right  ; "><b>Net Pay</b></td>
                                    </tr>
                                    <tr>
                                        <td style=" width:100% ; font-size:2 ; "></td>
                                    </tr>
                                    <tr>
                                        <td style=" width:100% ; font-size:2 ; border-top-width:3px ; "></td>
                                    </tr>';

                $ed_qry = " SELECT * FROM {$this->db->dbprefix}employee_vessel_history ed
                            LEFT JOIN {$this->db->dbprefix}user u ON ed.employee_id = u.employee_id
                            LEFT JOIN {$this->db->dbprefix}vessel v ON ed.vessel_id = v.vessel_id
                            WHERE 1 AND (DATE(date_embark) BETWEEN '{$date_from}' AND '{$date_to}' OR DATE(date_disembark) BETWEEN '{$date_from}' AND '{$date_to}')";

                if(!empty($employee_id)){
                    $ed_qry .= " AND ed.employee_id IN ({$employee_id})"; 
                }
                
                $ed_qry .= "GROUP BY ed.employee_id
                            ORDER BY u.lastname, u.firstname, u.middlename";
                
                $limit = ($i - 1) * $allowed_count_per_page;
                $ed_qry .= " LIMIT {$limit},{$allowed_count_per_page}";
                $ed_res = $this->db->query($ed_qry);
                
                $count = 0;
                foreach ($ed_res->result() as $key => $value){            
            
                    $qry = $this->db->query('SELECT * FROM '.$this->db->dbprefix('employee_vessel_history').' WHERE employee_id = "'.$value->employee_id.'"');
                    $emp_embark_disembark = $qry->row();
                    $count_row = $qry->num_rows();

                    if($count_row > 0){
                        $emp_code = $value->employee_code;
                        $emp_name = $value->lastname.', '.$value->firstname.' '.$value->middlename;
                        
                        $xcel_hed .= '  <tr><td></td></tr>
                                        <tr>
                                            <td style=" width:100% ; text-align:left   ; ">Employee Code: '.$emp_code.' - '.$emp_name.'</td>
                                        </tr>
                                        <tr><td></td></tr>
                                        <tr>
                                            <td style=" width: 2%  ; text-align:center ; "></td>
                                            <td style=" width:10%  ; text-align:center ; ">Date</td>
                                            <td style=" width:10%  ; text-align:center ; ">Time</td>
                                            <td style=" width:10%  ; text-align:left   ; ">Embark</td>
                                            <td style=" width:10%  ; text-align:left   ; ">Disembark</td>
                                            <td style=" width:10%  ; text-align:left   ; ">Report #</td>
                                            <td style=" width:18%  ; text-align:left   ; "></td>
                                            <td style=" width:10%  ; text-align:left   ; ">Position</td>
                                            <td style=" width:10%  ; text-align:left   ; ">Reason</td>
                                            <td style=" width:10%  ; text-align:left   ; ">Remarks</td>
                                        </tr>';

                        $count++;
                    }
                    $dtl_res = "SELECT * FROM {$this->db->dbprefix}employee_vessel_history e
                                LEFT JOIN {$this->db->dbprefix}vessel v on e.vessel_id = v.vessel_id
                                LEFT JOIN {$this->db->dbprefix}user u on u.employee_id = e.employee_id
                                LEFT JOIN {$this->db->dbprefix}user_position p on p.position_id = u.position_id
                                WHERE e.employee_id = {$value->employee_id}
                                AND (DATE(date_embark) BETWEEN '{$date_from}' AND '{$date_to}' OR DATE(date_disembark) BETWEEN '{$date_from}' AND '{$date_to}')";
                    $result_x = $this->db->query($dtl_res);

                    foreach($result_x->result() as $res_key=>$res_value){
                            $employee_vessel_id = $res_value->employee_vessel_id;
                            
                            $dtl_x = $this->db->query('SELECT * FROM '.$this->db->dbprefix('employee_vessel_history').' WHERE employee_vessel_id = "'.$employee_vessel_id.'"')->row();
                            if(!empty($dtl_x->date_disembark)){
                                $date_d = date("Y-m-d", strtotime($res_value->date_disembark));
                                $time_d = date("H:i:s", strtotime($res_value->date_disembark));

                                $xcel_hed .= '<tr>
                                                <td style=" width: 2%  ; text-align:center ; "></td>
                                                <td style=" width:10%  ; text-align:center ; ">'.$date_d.'</td>
                                                <td style=" width:10%  ; text-align:center ; ">'.$time_d.'</td>
                                                <td style=" width:10%  ; text-align:left   ; "></td>
                                                <td style=" width:10%  ; text-align:left   ; ">'.$res_value->vessel_code.'</td>
                                                <td style=" width:10%  ; text-align:left   ; ">'.$res_value->report_no.'</td>
                                                <td style=" width:18%  ; text-align:left   ; "></td>
                                                <td style=" width:10%  ; text-align:left   ; ">'.$res_value->position_code.'</td>
                                                <td style=" width:10%  ; text-align:left   ; ">'.$res_value->disembark_reason.'</td>
                                                <td style=" width:10%  ; text-align:left   ; ">'.$res_value->disembark_remarks.'</td>';
                            }
                            else{
                                $date_e = date("Y-m-d", strtotime($res_value->date_embark));
                                $time_e = date("H:i:s", strtotime($res_value->date_embark));
                                
                                $xcel_hed .= '<tr>
                                                <td style=" width: 2%  ; text-align:center ; "></td>
                                                <td style=" width:10%  ; text-align:center ; ">'.$date_d.'</td>
                                                <td style=" width:10%  ; text-align:center ; ">'.$time_d.'</td>
                                                <td style=" width:10%  ; text-align:left   ; "></td>
                                                <td style=" width:10%  ; text-align:left   ; ">'.$res_value->vessel_code.'</td>
                                                <td style=" width:10%  ; text-align:left   ; ">'.$res_value->report_no.'</td>
                                                <td style=" width:18%  ; text-align:left   ; "></td>
                                                <td style=" width:10%  ; text-align:left   ; ">'.$res_value->position_code.'</td>
                                                <td style=" width:10%  ; text-align:left   ; ">'.$res_value->disembark_reason.'</td>
                                                <td style=" width:10%  ; text-align:left   ; ">'.$res_value->disembark_remarks.'</td>';
                            }
                        $xcel_hed .= '</tr>';
                    }
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

    function export_payroll_sheet($company_id, $employee_id, $payroll_date, $title){
        $company_setting_res = $this->db->query('SELECT * FROM '.$this->db->dbprefix('user_company').' WHERE company_id = "'.$company_id.'"')->row();

        $this->db->where('payroll_date',$payroll_date);
        $period = $this->db->get('payroll_period')->row();
        $date_from = $period->date_from;
        $date_to = $period->date_to;

        if($period->period_status_id == 3){

            $transaction = "payroll_closed_transaction";
        }
        else{

            $transaction = "payroll_current_transaction";
        }

        if(!empty($company_id)){

            $company = " AND company_id IN ($company_id)";
        }
        if(!empty($employee_id)){

            $employee = " AND p.employee_id IN ($employee_id)";   
        }

        $qry = "SELECT DISTINCT v.vessel_id FROM {$this->db->dbprefix}vessel v
                LEFT JOIN {$this->db->dbprefix}employee e ON v.vessel_id = e.vessel_id
                LEFT JOIN {$this->db->dbprefix}user u ON u.employee_id = e.employee_id
                LEFT JOIN {$this->db->dbprefix}$transaction p ON p.employee_id = e.employee_id
                WHERE payroll_date = '{$payroll_date}' AND e.vessel_id IS NOT NULL $company $employee";

        $vessel = $this->db->query($qry);
        $vessel_num = $vessel->num_rows();
        $total_no_employees =$vessel->num_rows();
        
        $cnt = 1;
        $allowed_count_per_page = 30;
        
        foreach ($vessel->result() as $index => $vessel_value) {
            
            $emp_in_vessel = "SELECT DISTINCT e.employee_id FROM {$this->db->dbprefix}employee e
                LEFT JOIN {$this->db->dbprefix}vessel v ON v.vessel_id = e.vessel_id
                LEFT JOIN {$this->db->dbprefix}user u ON u.employee_id = e.employee_id
                LEFT JOIN {$this->db->dbprefix}$transaction p ON p.employee_id = e.employee_id
                WHERE payroll_date = '{$payroll_date}' AND e.vessel_id = $vessel_value->vessel_id $company $employee";

            $total_no_employees = $this->db->query($emp_in_vessel)->num_rows();
        }

        $page_with = $total_no_employees/$allowed_count_per_page;
        $page_floor = floor($page_with);
        $number_of_page = $page_floor;

        if($page_with > $page_floor){

             $number_of_page = $page_floor + 1;
        }        
        if($total_no_employees != 0)
        {
            for($i=1;$i<=$number_of_page; $i++)
            {                   
                $ix = $i;
                foreach ($vessel->result() as $index => $vessel_value) {
                    
                    $vessel_res = $this->db->get_where('vessel',array('vessel_id'=>$vessel_value->vessel_id))->row();

                    $xcel_hed = '';
                    $this->pdf->SetMargins(10,10,10,true);
                    $this->pdf->addPage('L', 'LEGAL', true);
                    $this->pdf->SetFontSize( 8 );                
                    $xcel_hed .= '';
                    $xcel_hed .= '<table style="width:100%;">
                                    <tr>
                                        <td style=" width:50%  ; text-align:left   ; font-size:9  ; ">'.$company_setting_res->company.'</td>
                                        <td style=" width:50%  ; text-align:right  ; font-size:9  ; ">Page '.$i.' of '.$number_of_page.'</td>
                                    </tr>
                                    <tr>
                                        <td style=" width:100% ; text-align:left   ; font-size:9  ; ">'.date("m/d/Y H:i:s").'</td>
                                    </tr>
                                    <tr><td></td></tr>
                                    <tr>
                                        <td style=" width:100% ; text-align:center ; font-size:11 ; "><h2>'.$title.'</h2></td>
                                    </tr>
                                    <tr>
                                        <td style=" width:100% ; text-align:center ; font-size:11 ; ">Disburse Vessel No.</td>
                                    </tr>
                                    <tr>
                                        <td style=" width:100% ; text-align:center ; font-size:11 ; ">Vessel: '.$vessel_res->vessel_code.' - '.$vessel_res->vessel.'</td>
                                    </tr>
                                    <tr>
                                        <td style=" width:100% ; text-align:center ; font-size:10 ; ">Pay Period: '.date("m/d/Y",strtotime($date_from)).' To: '.date("m/d/Y",strtotime($date_to)).'</td>
                                    </tr>
                                    <tr><td></td></tr>
                                    <tr>
                                        <td style=" width:13% ; text-align:left   ; ">Name</td>
                                        <td style=" width: 5% ; text-align:right  ; ">Rate</td>
                                        <td style=" width: 3% ; text-align:right  ; ">Days</td>
                                        <td style=" width: 6% ; text-align:right  ; ">Gross Pay</td>
                                        <td style=" width: 5% ; text-align:right  ; ">Allotment</td>
                                        <td style=" width: 5% ; text-align:right  ; ">WHT</td>
                                        <td style=" width: 5% ; text-align:right  ; ">SSS</td>
                                        <td style=" width: 5% ; text-align:right  ; ">PHIC</td>
                                        <td style=" width: 5% ; text-align:right  ; ">HDMF</td>
                                        <td style=" width: 4% ; text-align:right  ; ">Retro</td>
                                        <td style=" width: 4% ; text-align:right  ; ">Adjust</td>
                                        <td style=" width: 4% ; text-align:right  ; ">Allow</td>
                                        <td style=" width: 4% ; text-align:right  ; ">Meal</td>
                                        <td style=" width: 4% ; text-align:right  ; ">SS Loan</td>
                                        <td style=" width: 4% ; text-align:right  ; ">Pgb Loan</td>
                                        <td style=" width: 4% ; text-align:right  ; ">Othr Loans</td>
                                        <td style=" width: 4% ; text-align:right  ; ">Union</td>
                                        <td style=" width: 4% ; text-align:right  ; ">Othr Ded</td>
                                        <td style=" width: 6% ; text-align:right  ; ">Amt Disbursed</td>
                                        <td style=" width: 6% ; text-align:left   ; ">Signature</td>
                                    </tr>
                                    <tr>
                                        <td style=" width:100% ; font-size:2 ; "></td>
                                    </tr>
                                    <tr>
                                        <td style=" width:100% ; font-size:2 ; border-top-width:3px ; "></td>
                                    </tr>';

                    $payroll_detail_qry = " SELECT DISTINCT e.employee_id FROM {$this->db->dbprefix}$transaction p
                                            LEFT JOIN {$this->db->dbprefix}employee e ON p.employee_id = e.employee_id
                                            LEFT JOIN {$this->db->dbprefix}user u ON u.employee_id = e.employee_id
                                            LEFT JOIN {$this->db->dbprefix}vessel v ON v.vessel_id = e.vessel_id
                                            WHERE payroll_date = '{$payroll_date}' AND e.vessel_id IS NOT NULL AND v.vessel_id = {$vessel_res->vessel_id} $employee $company";
                    
                    $payroll_detail_res = $this->db->query($payroll_detail_qry);

                    $count = 0;
                    foreach ($payroll_detail_res->result() as $key => $value){

                        $name = $this->db->get_where('user',array('employee_id'=>$value->employee_id))->row();
                        $rate = $this->db->query(" SELECT amount FROM {$this->db->dbprefix}$transaction 
                                                   WHERE employee_id = $value->employee_id AND payroll_date='$payroll_date' AND transaction_code='SALARY'")->row();
                        $days = date_diff($date_from, $date_to);
                        
                        $gross_qry = "SELECT sum(amount) as amount FROM {$this->db->dbprefix}$transaction 
                            WHERE transaction_id IN (SELECT transaction_id FROM {$this->db->dbprefix}payroll_transaction 
                                WHERE transaction_class_id IN ( SELECT transaction_class_id FROM {$this->db->dbprefix}payroll_transaction_class
                                    WHERE transaction_class_code IN ('SALARY','ALLOWANCE_RECURRING','ALLOWANCE_BATCH','BENEFIT_RECURRING',
                                        'BENEFIT_BATCH','OVERTIME','ECOLA','BONUS','BONUS_TAXABLE'
                                        )
                                    )
                                )
                            AND employee_id = $value->employee_id AND payroll_date = '$payroll_date'";
                        $gross_res = $this->db->query($gross_qry)->row();

                        $wht = $this->db->query(" SELECT amount FROM {$this->db->dbprefix}$transaction 
                                                   WHERE employee_id = $value->employee_id AND payroll_date='$payroll_date' AND transaction_code='WHTAX'")->row();

                        $sss = $this->db->query(" SELECT amount FROM {$this->db->dbprefix}$transaction 
                                                   WHERE employee_id = $value->employee_id AND payroll_date='$payroll_date' AND transaction_code='SSS_EMP'")->row();

                        $phic = $this->db->query(" SELECT amount FROM {$this->db->dbprefix}$transaction 
                                                   WHERE employee_id = $value->employee_id AND payroll_date='$payroll_date' AND transaction_code='PHIC_EMP'")->row();

                        $hdmf = $this->db->query(" SELECT amount FROM {$this->db->dbprefix}$transaction 
                                                   WHERE employee_id = $value->employee_id AND payroll_date='$payroll_date' AND transaction_code='HDMF_EMP'")->row();
                        
                        $allowance = $this->db->query(" SELECT amount FROM {$this->db->dbprefix}$transaction 
                                                   WHERE employee_id = $value->employee_id AND payroll_date='$payroll_date' AND transaction_code='ECOLA'")->row();

                        $meal = $this->db->query(" SELECT amount FROM {$this->db->dbprefix}$transaction 
                                                   WHERE employee_id = $value->employee_id AND payroll_date='$payroll_date' AND transaction_code='MEAL'")->row();

                        $sss_loan = $this->db->query(" SELECT amount FROM {$this->db->dbprefix}$transaction 
                                                   WHERE employee_id = $value->employee_id AND payroll_date='$payroll_date' AND transaction_code='SSSLN'")->row();

                        $pgb_loan = $this->db->query(" SELECT amount FROM {$this->db->dbprefix}$transaction 
                                                   WHERE employee_id = $value->employee_id AND payroll_date='$payroll_date' AND transaction_code='HDMFLN'")->row();
                        
                        $loan_qry = "SELECT sum(amount) as amount FROM {$this->db->dbprefix}$transaction 
                            WHERE transaction_id IN (SELECT transaction_id FROM {$this->db->dbprefix}payroll_transaction 
                                WHERE transaction_class_id IN ( SELECT transaction_class_id FROM {$this->db->dbprefix}payroll_transaction_class
                                    WHERE transaction_class_code IN ('LOAN_AMORTIZATION','LOAN_INTEREST'
                                        )
                                    )
                                )
                            AND employee_id = $value->employee_id AND payroll_date = '$payroll_date'";
                        $loan_res = $this->db->query($loan_qry)->row();

                        $ded_qry = "SELECT sum(amount) as amount FROM {$this->db->dbprefix}$transaction 
                            WHERE transaction_id IN (SELECT transaction_id FROM {$this->db->dbprefix}payroll_transaction 
                                WHERE transaction_class_id IN ( SELECT transaction_class_id FROM {$this->db->dbprefix}payroll_transaction_class
                                    WHERE transaction_class_code IN ('ABSENCES','CASH_ADVANCE','DEDSHC','DEDUCTION_BATCH',
                                        'DEDUCTION_LATE','DEDUCTION_RECURRING','DEDUCTION_UNDERTIME','LOAN_AMORTIZATION','LOAN_INTEREST'
                                        )
                                    )
                                )
                            AND employee_id = $value->employee_id AND payroll_date = '$payroll_date'";
                        $ded_res = $this->db->query($ded_qry)->row();

                        $ded_qry = "SELECT sum(amount) as amount FROM {$this->db->dbprefix}$transaction 
                            WHERE transaction_id IN (SELECT transaction_id FROM {$this->db->dbprefix}payroll_transaction 
                                WHERE transaction_class_id IN ( SELECT transaction_class_id FROM {$this->db->dbprefix}payroll_transaction_class
                                    WHERE transaction_class_code IN ('ABSENCES','CASH_ADVANCE','DEDSHC','DEDUCTION_BATCH',
                                        'DEDUCTION_LATE','DEDUCTION_RECURRING','DEDUCTION_UNDERTIME'
                                        )
                                    )
                                )
                            AND employee_id = $value->employee_id AND payroll_date = '$payroll_date'";
                        $ded_res = $this->db->query($ded_qry)->row();

                        $amt_disbursed = $this->db->query(" SELECT amount FROM {$this->db->dbprefix}$transaction 
                                                   WHERE employee_id = $value->employee_id AND payroll_date='$payroll_date' AND transaction_code='NETPAY'")->row();

                        $xcel_hed .= '  <tr>
                                            <td style=" width:13% ; text-align:left   ; ">'.$name->lastname.', '.$name->firstname.' '.$name->middlename.'</td>
                                            <td style=" width: 5% ; text-align:right  ; ">'.number_format($rate->amount,2,'.',',').'</td>
                                            <td style=" width: 3% ; text-align:right  ; ">'.$days.'</td>
                                            <td style=" width: 6% ; text-align:right  ; ">'.number_format($gross_res->amount,2,'.',',').'</td>
                                            <td style=" width: 5% ; text-align:right  ; ">'.number_format($allotment->amount,2,'.',',').'</td>
                                            <td style=" width: 5% ; text-align:right  ; ">'.number_format($wht->amount,2,'.',',').'</td>
                                            <td style=" width: 5% ; text-align:right  ; ">'.number_format($sss->amount,2,'.',',').'</td>
                                            <td style=" width: 5% ; text-align:right  ; ">'.number_format($phic->amount,2,'.',',').'</td>
                                            <td style=" width: 5% ; text-align:right  ; ">'.number_format($hdmf->amount,2,'.',',').'</td>
                                            <td style=" width: 4% ; text-align:right  ; ">'.number_format($retro->amount,2,'.',',').'</td>
                                            <td style=" width: 4% ; text-align:right  ; ">'.number_format($adjust->amount,2,'.',',').'</td>
                                            <td style=" width: 4% ; text-align:right  ; ">'.number_format($allowance->amount,2,'.',',').'</td>
                                            <td style=" width: 4% ; text-align:right  ; ">'.number_format($meal->amount,2,'.',',').'</td>
                                            <td style=" width: 4% ; text-align:right  ; ">'.number_format($sss_loan->amount,2,'.',',').'</td>
                                            <td style=" width: 4% ; text-align:right  ; ">'.number_format($pgb_loan->amount,2,'.',',').'</td>
                                            <td style=" width: 4% ; text-align:right  ; ">'.number_format($loan_res->amount,2,'.',',').'</td>
                                            <td style=" width: 4% ; text-align:right  ; ">'.number_format($union_amt->amount,2,'.',',').'</td>
                                            <td style=" width: 4% ; text-align:right  ; ">'.number_format($ded_res->amount,2,'.',',').'</td>
                                            <td style=" width: 6% ; text-align:right  ; ">'.number_format($amt_disbursed->amount,2,'.',',').'</td>
                                            <td style=" width: 6% ; text-align:left   ; border-bottom-width:2px"></td>
                                        </tr>';
                        
                        $t_gross_pay += $gross_res->amount;
                        $t_allotment += $allotment->amount;
                        $t_wht += $wht->amount;
                        $t_sss += $sss->amount;
                        $t_phic += $phic->amount;
                        $t_hdmf += $hdmf->amount;
                        $t_retro += $retro->amount;
                        $t_adjust += $adjust->amount;
                        $t_allowance += $allowance->amount;
                        $t_meal += $meal->amount;
                        $t_sss_loan += $sss_loan->amount;
                        $t_pgb_loan += $pgb_loan->amount;
                        $t_other_loan += $loan_res->amount;
                        $t_other_dedtns += $ded_res->other_dedtns;
                        $t_amt_disbursed += $amt_disbursed->amount;

                        $count++;
                    }
                    
                    $xcel_hed .= '   <tr>
                                        <td></td></tr>
                                    <tr>
                                        <td style=" width:21% ; text-align:right  ; background-color:#DDDDDD ; "><b>Total</b></td>
                                        <td style=" width: 6% ; text-align:right  ; background-color:#DDDDDD ; "><b>'.number_format($t_gross_pay,2,'.',',').'</b></td>
                                        <td style=" width: 5% ; text-align:right  ; background-color:#DDDDDD ; "><b>'.number_format($t_allotment,2,'.',',').'</b></td>
                                        <td style=" width: 5% ; text-align:right  ; background-color:#DDDDDD ; "><b>'.number_format($t_wht,2,'.',',').'</b></td>
                                        <td style=" width: 5% ; text-align:right  ; background-color:#DDDDDD ; "><b>'.number_format($t_sss,2,'.',',').'</b></td>
                                        <td style=" width: 5% ; text-align:right  ; background-color:#DDDDDD ; "><b>'.number_format($t_phic,2,'.',',').'</b></td>
                                        <td style=" width: 5% ; text-align:right  ; background-color:#DDDDDD ; "><b>'.number_format($t_hdmf,2,'.',',').'</b></td>
                                        <td style=" width: 4% ; text-align:right  ; background-color:#DDDDDD ; "><b>'.number_format($t_retro,2,'.',',').'</b></td>
                                        <td style=" width: 4% ; text-align:right  ; background-color:#DDDDDD ; "><b>'.number_format($t_adjust,2,'.',',').'</b></td>
                                        <td style=" width: 4% ; text-align:right  ; background-color:#DDDDDD ; "><b>'.number_format($t_allowance,2,'.',',').'</b></td>
                                        <td style=" width: 4% ; text-align:right  ; background-color:#DDDDDD ; "><b>'.number_format($t_meal,2,'.',',').'</b></td>
                                        <td style=" width: 4% ; text-align:right  ; background-color:#DDDDDD ; "><b>'.number_format($t_sss_loan,2,'.',',').'</b></td>
                                        <td style=" width: 4% ; text-align:right  ; background-color:#DDDDDD ; "><b>'.number_format($t_pgb_loan,2,'.',',').'</b></td>
                                        <td style=" width: 4% ; text-align:right  ; background-color:#DDDDDD ; "><b>'.number_format($t_other_loan,2,'.',',').'</b></td>
                                        <td style=" width: 4% ; text-align:right  ; background-color:#DDDDDD ; "><b>'.number_format($t_union_amt,2,'.',',').'</b></td>
                                        <td style=" width: 4% ; text-align:right  ; background-color:#DDDDDD ; "><b>'.number_format($t_other_dedtns,2,'.',',').'</b></td>
                                        <td style=" width: 6% ; text-align:right  ; background-color:#DDDDDD ; "><b>'.number_format($t_amt_disbursed,2,'.',',').'</b></td>
                                    </tr>
                                </table>';
                    $this->pdf->writeHTML($xcel_hed, true, false, true, false, '');

                    $ix++;
                }
            }
        }
        else
        {
            $this->pdf->addPage('P', 'LETTER', true);
            $this->pdf->SetXY(100, 20);
            $this->pdf->writeHTML("No Record Found!", true, false, true, false, '');
        }   
    }

    function export_payroll_summary_per_vessel($company_id, $employee_id, $payroll_date, $title){
        
        $company_setting_res = $this->db->query('SELECT * FROM '.$this->db->dbprefix('user_company').' WHERE company_id = "'.$company_id.'"')->row();
        
        $this->db->where('payroll_date',$payroll_date);
        $period = $this->db->get('payroll_period')->row();
        $date_from = $period->date_from;
        $date_to = $period->date_to;

        if($period->period_status_id == 3){

            $transaction = "payroll_closed_transaction";
        }
        else{

            $transaction = "payroll_current_transaction";
        }

        if(!empty($company_id)){

            $company = " AND company_id IN ($company_id)";
        }
        if(!empty($employee_id)){
            
            $employee = " AND p.employee_id IN ($employee_id)";   
        }

        $qry = "SELECT DISTINCT vessel_code
                        FROM {$this->db->dbprefix}$transaction p
                        LEFT JOIN {$this->db->dbprefix}employee e ON p.employee_id = e.employee_id
                        LEFT JOIN {$this->db->dbprefix}user u ON p.employee_id = u.employee_id
                        LEFT JOIN {$this->db->dbprefix}vessel v ON v.vessel_id = e.vessel_id
                        WHERE 1 AND e.vessel_id IS NOT NULL AND payroll_date = '{$payroll_date}' $employee $company";
        
        $payroll = $this->db->query($qry)->row();
        $payroll_res = $this->db->query($qry);
        $cnt = 1;
        $total_no_employees = $payroll_res->num_rows();
        $allowed_count_per_page = 30;
        $page_with = $total_no_employees/$allowed_count_per_page;
        $page_floor = floor($page_with);
        
        $number_of_page = $page_floor;
        if($page_with > $page_floor)
        {
            $number_of_page = $page_floor + 1;
        }        
        if($total_no_employees != 0)
        {
            for($i=1;$i<=$number_of_page; $i++)
            {                   
                $xcel_hed = '';
                $this->pdf->SetMargins(10,10,10,true);
                $this->pdf->addPage('L', 'LEGAL', true);                
                $xcel_hed .= '';
                $xcel_hed .= '  <table style="width:100%;">
                                    <tr>
                                        <td style=" width:50%  ; text-align:left   ; font-size:9  ; ">'.$company_setting_res->company.'</td>
                                        <td style=" width:50%  ; text-align:right  ; font-size:9  ; ">Page '.$i.' of '.$number_of_page.'</td>
                                    </tr>
                                    <tr>
                                        <td style=" width:100% ; text-align:left   ; font-size:9  ; ">'.date("m/d/Y H:i:s").'</td>
                                    </tr>
                                    <tr><td></td></tr>
                                    <tr>
                                        <td style=" width:100% ; text-align:center ; font-size:11 ; "><h2>'.$title.'</h2></td>
                                    </tr>
                                    <tr>
                                        <td style=" width:100% ; text-align:center ; font-size:10 ; ">Pay Period: '.date("m/d/Y",strtotime($date_from)).' To: '.date("m/d/Y",strtotime($date_to)).'</td>
                                    </tr>
                                    <tr>
                                        <td style=" width:100% ; text-align:center ; font-size:11 ; ">For Pay Mode:</td>
                                    </tr>
                                    <tr><td></td></tr>


                                    <tr>
                                        <td style="width:6%;"></td>
                                        <td style="width:7%;"></td>
                                        <td style="width:5%;"></td>
                                        <td style="width:5%;"></td>
                                        <td style="width:5%;"></td>
                                        <td style="width:5%;"></td>
                                        <td style="width:5%;"></td>
                                        <td style="width:5%;"></td>
                                        <td style="width:5%;"></td>
                                        <td style="width:5%;"></td>
                                        <td style="width:5%;"></td>
                                        <td style="width:5%;"></td>
                                        <td style="width:7%;"></td>
                                        <td style="width:5%;"></td>
                                        <td style="width:7%;"></td>
                                        <td style="width:6%;"></td>
                                        <td style="width:6%;"></td>
                                        <td style="width:7%;"></td>
                                    </tr>
                                    <tr>
                                        <td colspan="6" style="text-align:left;font-size:100px;">'.$company_setting_res->company.'</td>
                                        <td colspan="18" style="text-align:right;font-size:80px;">Page '.$i.' of '.$number_of_page.'</td>
                                    </tr>
                                    <tr>
                                        <td colspan="6" style="text-align:left;font-size:80px;">'.date("m/d/Y H:i:s").'</td>
                                        <td colspan="18" style="text-align:right;font-size:80px;"></td>
                                    </tr>
                                    <tr>
                                        <td colspan="18" style="text-align:center;font-size:100px;"><h2>'.$title.'</h2></td>
                                    </tr>
                                    <tr>
                                        <td colspan="18" style="text-align:center;font-size:100px;">Pay Period: '.date("m/d/Y",strtotime($date_from)).' To: '.date("m/d/Y",strtotime($date_to)).'</td>
                                    </tr>
                                    <tr>
                                        <td colspan="18" style="text-align:center;font-size:100px;">For Pay Mode: </td>
                                    </tr>
                                    <tr><td></td></tr>
                                    <tr>
                                        <td colspan="2" style="text-align:left;font-size:90px;background-color:#DDDDDD;"></td>
                                        <td colspan="3" style="text-align:center;font-size:90px;background-color:#DDDDDD;">SSS</td>
                                        <td colspan="3" style="text-align:center;font-size:90px;background-color:#DDDDDD;">HDMF</td>
                                        <td colspan="3" style="text-align:center;font-size:90px;background-color:#DDDDDD;">PHIC</td>
                                        <td colspan="7" style="text-align:left;font-size:90px;background-color:#DDDDDD;"></td>
                                    </tr>
                                    <tr>
                                        <td colspan="1" style="text-align:left;background-color:#dDDddD;font-size:90px;">VSL</td>
                                        <td style="text-align:right;background-color:#dDDddD;font-size:90px;">Gross Pay</td>
                                        <td style="text-align:right;background-color:#dDDddD;font-size:90px;">Employee</td>
                                        <td style="text-align:right;background-color:#dDDddD;font-size:90px;">Employer</td>
                                        <td style="text-align:right;background-color:#dDDddD;font-size:90px;">Total</td>
                                        <td style="text-align:right;background-color:#dDDddD;font-size:90px;">Employee</td>
                                        <td style="text-align:right;background-color:#dDDddD;font-size:90px;">Employer</td>
                                        <td style="text-align:right;background-color:#dDDddD;font-size:90px;">Total</td>
                                        <td style="text-align:right;background-color:#dDDddD;font-size:90px;">Employee</td>
                                        <td style="text-align:right;background-color:#dDDddD;font-size:90px;">Employer</td>
                                        <td style="text-align:right;background-color:#dDDddD;font-size:90px;">Total</td>
                                        <td style="text-align:right;background-color:#dDDddD;font-size:90px;">SSS Loan</td>
                                        <td style="text-align:right;background-color:#dDDddD;font-size:90px;">HDMF Loan</td>
                                        <td style="text-align:right;background-color:#dDDddD;font-size:90px;">W/Tax</td>
                                        <td style="text-align:right;background-color:#dDDddD;font-size:90px;">Deduction</td>
                                        <td style="text-align:right;background-color:#dDDddD;font-size:90px;">Comp Loan</td>
                                        <td style="text-align:right;background-color:#dDDddD;font-size:90px;">Allotment</td>
                                        <td style="text-align:right;background-color:#DDDDDD;font-size:90px;">Net Pay</td>\
                                    </tr>';

                $payroll_detail_qry = " SELECT DISTINCT vessel_code
                                        FROM {$this->db->dbprefix}payroll_closed_transaction p
                                        LEFT JOIN {$this->db->dbprefix}employee e ON p.employee_id = e.employee_id
                                        LEFT JOIN {$this->db->dbprefix}vessel v ON v.vessel_id = e.vessel_id
                                        WHERE payroll_date = '{$payroll_date}' AND e.vessel_id IS NOT NULL";
                $allotment_detail_res = $this->db->query($payroll_detail_qry);
                
                $count = 0;
                foreach ($allotment_detail_res->result() as $key => $value) 
                {            
                    
                    $qry = "SELECT sum(amount) as amount
                            FROM {$this->db->dbprefix}payroll_closed_transaction p
                            LEFT JOIN {$this->db->dbprefix}employee e ON p.employee_id = e.employee_id
                            LEFT JOIN {$this->db->dbprefix}vessel v ON v.vessel_id = e.vessel_id
                            WHERE payroll_date = '{$payroll_date}' AND vessel_code = '{$value->vessel_code}' AND transaction_code IN ";

                    $gross_pay = $this->db->query($qry."('SALARY')")->row();
                    $sss_ee = $this->db->query($qry."('SSS_EMP')")->row();
                    $hdmf_ee = $this->db->query($qry."('HDMF_EMP')")->row();
                    $phic_ee = $this->db->query($qry."('PHIC_EMP')")->row();
                    $sss_loan = $this->db->query($qry."('SSS LOAN')")->row();// to implemented
                    $hdmf_loan = $this->db->query($qry."('HDMF LOAN')")->row();// to implemented
                    $wht = $this->db->query($qry."('WHTAX')")->row();
                    $deduction = $this->db->query($qry."('ABSENCES','DEDUCTION_LATE','DEDUCTION_UNDERTIME')")->row();
                    $comp_loan = $this->db->query($qry."('COMP LOAN')")->row();// to implemented
                    $allotment = $this->db->query($qry."('ALLOTMENT')")->row();// to implemented
                    $netpay = $this->db->query($qry."('NETPAY')")->row();
                    
                    $cont = "   SELECT SUM(company) as amount
                                FROM {$this->db->dbprefix}employee_contribution c
                                LEFT JOIN {$this->db->dbprefix}employee e ON c.employee_id = e.employee_id
                                LEFT JOIN {$this->db->dbprefix}vessel v ON v.vessel_id = e.vessel_id
                                LEFT JOIN {$this->db->dbprefix}payroll_transaction t ON c.transaction_id = t.transaction_id 
                                WHERE payroll_date = '{$payroll_date}' AND vessel_code = '{$value->vessel_code}' AND transaction_code IN ";
                    
                    $sss_er = $this->db->query($cont."('SSS_EMP')")->row();
                    $hdmf_er = $this->db->query($cont."('HDMF_EMP')")->row();
                    $phic_er = $this->db->query($cont."('PHIC_EMP')")->row();

                    $sss_total = $sss_ee->amount + $sss_er->amount;
                    $hdmf_total = $hdmf_ee->amount + $hdmf_er->amount;
                    $phic_total = $phic_ee->amount + $phic_er->amount;


                    $xcel_hed .= '  <tr>
                                        <td style="text-align:left;font-size:90px;">'.$value->vessel_code.'</td>
                                        <td style="text-align:right;font-size:90px;">'.number_format($gross_pay->amount,2,'.',',').'</td>
                                        <td style="text-align:right;font-size:90px;">'.number_format($sss_ee->amount,2,'.',',').'</td>
                                        <td style="text-align:right;font-size:90px;">'.number_format($sss_er->amount,2,'.',',').'</td>
                                        <td style="text-align:right;font-size:90px;">'.number_format($sss_total,2,'.',',').'</td>
                                        <td style="text-align:right;font-size:90px;">'.number_format($hdmf_ee->amount,2,'.',',').'</td>
                                        <td style="text-align:right;font-size:90px;">'.number_format($hdmf_er->amount,2,'.',',').'</td>
                                        <td style="text-align:right;font-size:90px;">'.number_format($hdmf_total,2,'.',',').'</td>
                                        <td style="text-align:right;font-size:90px;">'.number_format($phic_ee->amount,2,'.',',').'</td>
                                        <td style="text-align:right;font-size:90px;">'.number_format($phic_er->amount,2,'.',',').'</td>
                                        <td style="text-align:right;font-size:90px;">'.number_format($phic_total,2,'.',',').'</td>
                                        <td style="text-align:right;font-size:90px;">'.number_format($sss_loan->amount,2,'.',',').'</td>
                                        <td style="text-align:right;font-size:90px;">'.number_format($hdmf_loan->amount,2,'.',',').'</td>
                                        <td style="text-align:right;font-size:90px;">'.number_format($wht->amount,2,'.',',').'</td>
                                        <td style="text-align:right;font-size:90px;">'.number_format($deduction->amount,2,'.',',').'</td>
                                        <td style="text-align:right;font-size:90px;">'.number_format($comp_loan->amount,2,'.',',').'</td>
                                        <td style="text-align:right;font-size:90px;">'.number_format($allotment->amount,2,'.',',').'</td>
                                        <td style="text-align:right;font-size:90px;">'.number_format($netpay->amount,2,'.',',').'</td>
                                    </tr>';
                    
                    $t_gross_pay += $gross_pay->amount;
                    $t_sss_ee += $sss_ee->amount;
                    $t_sss_er += $sss_er->amount;
                    $t_sss_total += $sss_total;
                    $t_hdmf_ee += $hdmf_ee->amount;
                    $t_hdmf_er += $hdmf_er->amount;
                    $t_hdmf_total += $hdmf_total;
                    $t_phic_ee += $phic_ee->amount;
                    $t_phic_er += $phic_er->amount;
                    $t_phic_total += $phic_total;
                    $t_sss_loan += $sss_loan->amount;
                    $t_hdmf_loan += $hdmf_loan->amount;
                    $t_wht += $wht->amount;
                    $t_deduction += $deduction->amount;
                    $t_comp_loan += $comp_loan->amount;
                    $t_allotment += $allotment->amount;
                    $t_netpay += $natpay->amount;

                    $gt_gross_pay += $gross_pay->amount;
                    $gt_sss_ee += $sss_ee->amount;
                    $gt_sss_er += $sss_er->amount;
                    $gt_sss_total += $sss_total;
                    $gt_hdmf_ee += $hdmf_ee->amount;
                    $gt_hdmf_er += $hdmf_er->amount;
                    $gt_hdmf_total += $hdmf_total;
                    $gt_phic_ee += $phic_ee->amount;
                    $gt_phic_er += $phic_er->amount;
                    $gt_phic_total += $phic_total;
                    $gt_sss_loan += $sss_loan->amount;
                    $gt_hdmf_loan += $hdmf_loan->amount;
                    $gt_wht += $wht->amount;
                    $gt_deduction += $deduction->amount;
                    $gt_comp_loan += $comp_loan->amount;
                    $gt_allotment += $allotment->amount;
                    $gt_netpay += $natpay->amount;
                    
                    $count++;
                }
                
                if($count != $allowed_count_per_page)
                {
                    for ($space=1; $space <= ($allowed_count_per_page - $count); $space++) 
                    {
                        $xcel_hed .= '<tr><td></td></tr>';
                    }   
                }
                if($i != $number_of_page){
                $xcel_hed .= '      <tr><td></td></tr>
                                    <tr>
                                        <td colspan="1" style="text-align:left;background-color:#DDDDDD;font-size:85px;"><b>Total</b></td>
                                        <td style="text-align:right;background-color:#DDDDDD;font-size:90px;"><b>'.number_format($t_gross_pay,2,'.',',').'</b></td>
                                        <td style="text-align:right;background-color:#DDDDDD;font-size:90px;"><b>'.number_format($t_sss_ee,2,'.',',').'</b></td>
                                        <td style="text-align:right;background-color:#DDDDDD;font-size:90px;"><b>'.number_format($t_sss_er,2,'.',',').'</b></td>
                                        <td style="text-align:right;background-color:#DDDDDD;font-size:90px;"><b>'.number_format($t_sss_total,2,'.',',').'</b></td>
                                        <td style="text-align:right;background-color:#DDDDDD;font-size:90px;"><b>'.number_format($t_hdmf_ee,2,'.',',').'</b></td>
                                        <td style="text-align:right;background-color:#DDDDDD;font-size:90px;"><b>'.number_format($t_hdmf_er,2,'.',',').'</b></td>
                                        <td style="text-align:right;background-color:#DDDDDD;font-size:90px;"><b>'.number_format($t_hdmf_total,2,'.',',').'</b></td>
                                        <td style="text-align:right;background-color:#DDDDDD;font-size:90px;"><b>'.number_format($t_phic_ee,2,'.',',').'</b></td>
                                        <td style="text-align:right;background-color:#DDDDDD;font-size:90px;"><b>'.number_format($t_phic_er,2,'.',',').'</b></td>
                                        <td style="text-align:right;background-color:#DDDDDD;font-size:90px;"><b>'.number_format($t_phic_total,2,'.',',').'</b></td>
                                        <td style="text-align:right;background-color:#DDDDDD;font-size:90px;"><b>'.number_format($t_sss_loan,2,'.',',').'</b></td>
                                        <td style="text-align:right;background-color:#DDDDDD;font-size:90px;"><b>'.number_format($t_hdmf_loan,2,'.',',').'</b></td>
                                        <td style="text-align:right;background-color:#DDDDDD;font-size:90px;"><b>'.number_format($t_wht,2,'.',',').'</b></td>
                                        <td style="text-align:right;background-color:#DDDDDD;font-size:90px;"><b>'.number_format($t_deduction,2,'.',',').'</b></td>
                                        <td style="text-align:right;background-color:#DDDDDD;font-size:90px;"><b>'.number_format($t_comp_loan,2,'.',',').'</b></td>
                                        <td style="text-align:right;background-color:#DDDDDD;font-size:90px;"><b>'.number_format($t_allotment,2,'.',',').'</b></td>
                                        <td style="text-align:right;background-color:#DDDDDD;font-size:90px;"><b>'.number_format($t_netpay,2,'.',',').'</b></td>
                                    </tr>';
                }
                if($i == $number_of_page)
                {
                    $xcel_hed .= '  <tr>
                                        <td colspan="1" style="text-align:left;background-color:#DDDDDD;font-size:85px;"><b>Grand Total</b></td>
                                        <td style="text-align:right;background-color:#DDDDDD;font-size:90px;"><b>'.number_format($gt_gross_pay,2,'.',',').'</b></td>
                                        <td style="text-align:right;background-color:#DDDDDD;font-size:90px;"><b>'.number_format($gt_sss_ee,2,'.',',').'</b></td>
                                        <td style="text-align:right;background-color:#DDDDDD;font-size:90px;"><b>'.number_format($gt_sss_er,2,'.',',').'</b></td>
                                        <td style="text-align:right;background-color:#DDDDDD;font-size:90px;"><b>'.number_format($gt_sss_total,2,'.',',').'</b></td>
                                        <td style="text-align:right;background-color:#DDDDDD;font-size:90px;"><b>'.number_format($gt_hdmf_ee,2,'.',',').'</b></td>
                                        <td style="text-align:right;background-color:#DDDDDD;font-size:90px;"><b>'.number_format($gt_hdmf_er,2,'.',',').'</b></td>
                                        <td style="text-align:right;background-color:#DDDDDD;font-size:90px;"><b>'.number_format($gt_hdmf_total,2,'.',',').'</b></td>
                                        <td style="text-align:right;background-color:#DDDDDD;font-size:90px;"><b>'.number_format($gt_phic_ee,2,'.',',').'</b></td>
                                        <td style="text-align:right;background-color:#DDDDDD;font-size:90px;"><b>'.number_format($gt_phic_er,2,'.',',').'</b></td>
                                        <td style="text-align:right;background-color:#DDDDDD;font-size:90px;"><b>'.number_format($gt_phic_total,2,'.',',').'</b></td>
                                        <td style="text-align:right;background-color:#DDDDDD;font-size:90px;"><b>'.number_format($gt_sss_loan,2,'.',',').'</b></td>
                                        <td style="text-align:right;background-color:#DDDDDD;font-size:90px;"><b>'.number_format($gt_hdmf_loan,2,'.',',').'</b></td>
                                        <td style="text-align:right;background-color:#DDDDDD;font-size:90px;"><b>'.number_format($gt_wht,2,'.',',').'</b></td>
                                        <td style="text-align:right;background-color:#DDDDDD;font-size:90px;"><b>'.number_format($gt_deduction,2,'.',',').'</b></td>
                                        <td style="text-align:right;background-color:#DDDDDD;font-size:90px;"><b>'.number_format($gt_comp_loan,2,'.',',').'</b></td>
                                        <td style="text-align:right;background-color:#DDDDDD;font-size:90px;"><b>'.number_format($gt_allotment,2,'.',',').'</b></td>
                                        <td style="text-align:right;background-color:#DDDDDD;font-size:90px;"><b>'.number_format($gt_netpay,2,'.',',').'</b></td>
                                    </tr>';
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
}
/* End of file */
/* Location: system/application */
?>
