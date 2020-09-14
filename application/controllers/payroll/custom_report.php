<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class custom_report extends my_controller
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
        $report_type = array("Payroll Journal (Summary)", "Incomes Summary", "Deductions Summary", "Payslip","Withholding tax","Deduction","Payrolll Master List");
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
            //summary
            case '0':
                $html = $this->export_payroll_summary($company_id, $employee_id, $payroll_date, "Payroll Journal (Summary)");
                $title = "Payroll Journal (Summary)";
                break;            
            //earnings
            case '1':
                $html = $this->export_incomes_summary($company_id, $employee_id, $payroll_date, "Payroll Incomes Summary");
                $title = "Payroll Incomes Summary";
                break;
            case '2':
                $html = $this->export_deductions_summary($company_id, $employee_id, $payroll_date, "Payroll Deduction Summary");
                $title = "Payroll Deductions Summary";
                break;
            case '3':
                $html = $this->export_payslip($company_id, $employee_id, $payroll_date, "Payslip");
                $title = "Payslip";
                break;
            case '4':
                $html = $this->export_whtax($company_id, $employee_id, $payroll_date, "Withholding Tax");
                $title = "BIR Withholding Tax Report";
                break;
            case '5':
                $html = $this->export_ded($company_id, $employee_id, $payroll_date, "Deduction");
                $title = "Capex Report";
                break;
            case '6':
                $html = $this->export_masterlist($company_id, $employee_id, $payroll_date, "Payrolll Master List");
                $title = "PAYROLL MASTER LIST";
                break;
        }
        $this->pdf->writeHTML($html, true, false, true, false, '');
        $this->pdf->Output(ucwords(str_replace(" ","_",$title))."_" . date('dmYHis') . '.pdf', 'I');
    }

    function export_payroll_summary($company_id, $employee_id, $payroll_date, $title){
        /*  REPORT  FORMAT 
        
        Company Name                                                    
        Payroll Journal (Summary)                                                   
        For The Payroll Dated 06/10/2013 : 20113060001                                                  
                                                            
        Emp. No.    Employee Name   Basic   Overtime    Other inc.  Gross inc.  Tax Held    SSS Cont.   PHIC    HDMF    Tardiness   Other Ded.  Total Ded.  Net Pay
        
        |                                                               DETAILS OF TRANSACTIONS                                                                     |
                                                            
        Grand Total:                                                    
                                                            
        Prepared By:    Name            Check By:   Name            Approved By:    Name                        
        |               Position                    Position                        Position                        
                                                            
                                                            
        Run Date:  July 04, 2013 11:17AM                                                                                                                Page 2 of 2 

        */
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

        $qry = "SELECT DISTINCT p.employee_id FROM {$this->db->dbprefix}$transaction p
                LEFT JOIN {$this->db->dbprefix}employee e ON p.employee_id = e.employee_id
                LEFT JOIN {$this->db->dbprefix}user u ON u.employee_id = e.employee_id
                WHERE 1 AND p.deleted = 0 AND payroll_date = '{$payroll_date}' $company $employee";
        
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
                $this->pdf->SetMargins(10,10,10,true);
                $this->pdf->addPage('L', 'LEGAL', true);  
                $this->pdf->SetFontSize( 8 );

                $xcel_hed ='
                    <table>
                        <tr>
                            <td style=" width:100% ; text-align:left ; ">'.$company_setting_res->company.'</td>
                        </tr>
                        <tr>
                            <td style=" width:100% ; text-align:left ; ">'.$title.'</td>
                        </tr>
                        <tr>
                            <td style=" width:100% ; text-align:left ; ">For The Payroll Dated '.date("m/d/Y",strtotime($payroll_date)).' : ('.$date_from. ' - '. $date_to .')</td>
                        </tr>
                        <tr>
                            <td style=" width:100% ; text-align:left ; "></td>
                        </tr>
                        <tr> 
                            <td style=" width: 4% ; text-align:left;">Emp. No.</td>
                            <td style=" width:14% ; text-align:left;">Name of Employee</td>
                            <td style=" width: 8% ; text-align:right;">Basic</td>
                            <td style=" width: 7% ; text-align:right;">Overtime</td>
                            <td style=" width: 7% ; text-align:right;">Other Inc.</td>
                            <td style=" width: 7% ; text-align:right;">Gross Inc.</td>
                            <td style=" width: 7% ; text-align:right;">Tax Held</td>
                            <td style=" width: 6% ; text-align:right;">SSS Cont.</td>
                            <td style=" width: 6% ; text-align:right;">PHIC</td>
                            <td style=" width: 6% ; text-align:right;">HDMF</td>
                            <td style=" width: 6% ; text-align:right;">Tardiness</td>
                            <td style=" width: 7% ; text-align:right;">Other Ded.</td>
                            <td style=" width: 7% ; text-align:right;">Total Ded.</td>
                            <td style=" width: 7% ; text-align:right;">Net Pay</td>
                        </tr>
                        <tr>
                            <td style=" width:100% ; font-size:2 ; "></td>
                        </tr>
                        <tr>
                            <td style=" width:100% ; font-size:2 ; border-top-width:3px ; "></td>
                        </tr>';

                $payroll_detail_qry = " SELECT DISTINCT e.employee_id, id_number, u.lastname, u.firstname, u.middlename FROM {$this->db->dbprefix}$transaction p
                                        LEFT JOIN {$this->db->dbprefix}employee e ON p.employee_id = e.employee_id
                                        LEFT JOIN {$this->db->dbprefix}user u ON u.employee_id = e.employee_id
                                        WHERE 1 and p.deleted = 0 AND payroll_date = '{$payroll_date}' $employee $company
                                        ORDER BY u.lastname, u.firstname, u.middlename";
                
                $limit = ($i - 1) * $allowed_count_per_page;
                $payroll_detail_qry .= " LIMIT {$limit},{$allowed_count_per_page}";

                $payroll_detail_res = $this->db->query($payroll_detail_qry);

                $count = 0;
                foreach ($payroll_detail_res->result() as $key => $value){

                    $emp_name = $value->lastname.', '.$value->firstname.' '.$value->middlename;

                    $ot_amt = $this->db->query("SELECT sum(amount) as amount FROM {$this->db->dbprefix}$transaction p
                                    WHERE deleted = 0 AND payroll_date = '{$payroll_date}' AND p.employee_id = $value->employee_id 
                                        AND transaction_id IN (SELECT transaction_id FROM {$this->db->dbprefix}payroll_transaction WHERE transaction_class_id = 10)")->row();

                    $other_amt = $this->db->query("SELECT sum(amount) as amount FROM {$this->db->dbprefix}$transaction p
                                    WHERE deleted = 0 AND payroll_date = '{$payroll_date}' AND p.employee_id = $value->employee_id AND transaction_code != 'SALARY' 
                                        AND transaction_id NOT IN ( SELECT transaction_id FROM {$this->db->dbprefix}payroll_transaction WHERE transaction_class_id = 10 )
                                        AND transaction_type_id IN ( SELECT transaction_type_id FROM {$this->db->dbprefix}payroll_transaction_type WHERE operation = '+' )")->row();
    
                    $gross_amt = $this->db->query("SELECT sum(amount) as amount FROM {$this->db->dbprefix}$transaction p
                                    WHERE deleted = 0 AND payroll_date = '{$payroll_date}' AND p.employee_id = $value->employee_id 
                                        AND transaction_type_id IN ( SELECT transaction_type_id FROM {$this->db->dbprefix}payroll_transaction_type WHERE operation = '+' )")->row();                

                    $oth_ded_amt = $this->db->query("SELECT sum(amount) as amount FROM {$this->db->dbprefix}$transaction p
                                    WHERE deleted = 0 AND payroll_date = '{$payroll_date}' AND p.employee_id = $value->employee_id 
                                        AND transaction_type_id IN ( SELECT transaction_type_id FROM {$this->db->dbprefix}payroll_transaction_type WHERE operation = '-' AND transaction_type_id != 5 AND transaction_code !='WHTAX')")->row();

                    $tot_ded_amt = $this->db->query("SELECT sum(amount) as amount FROM {$this->db->dbprefix}$transaction p
                                    WHERE deleted = 0 AND payroll_date = '{$payroll_date}' AND p.employee_id = $value->employee_id 
                                        AND transaction_type_id IN ( SELECT transaction_type_id FROM {$this->db->dbprefix}payroll_transaction_type WHERE operation = '-' )")->row();                

                    $detail_qry = " SELECT * FROM {$this->db->dbprefix}$transaction p
                                    WHERE deleted = 0 AND payroll_date = '{$payroll_date}' AND p.employee_id = $value->employee_id";
                    
                    $detail_res = $this->db->query($detail_qry);

                    $absent_amt = '';
                    $late_amt = '';
                    $undertime_amt = '';
                    
                    foreach ($detail_res->result() as $dtl){
                        switch ($dtl->transaction_code) {
                            case 'SALARY':
                                $sal_amt = $dtl->amount;
                                break;
                            case 'WHTAX':
                                $whtax_amt = $dtl->amount;
                                break;
                            case 'SSS_EMP':
                                $sss_amt = $dtl->amount;
                                break;
                            case 'PHIC_EMP':
                                $phic_amt = $dtl->amount;
                                break;
                            case 'HDMF_EMP':
                                $hdmf_amt = $dtl->amount;
                                break;
                            case 'ABSENCES':
                                $absent_amt = $dtl->amount;
                                break;
                            case 'DEDUCTION_LATE':
                                $late_amt = $dtl->amount;
                                break;
                            case 'DEDUCTION_UNDERTIME':
                                $undertime_amt = $dtl->amount;
                                break;
                            case 'NETPAY':
                                $netpay_amt = $dtl->amount;
                                break;
                       }
                    }

                    $tardiness_amt = $absent_amt + $late_amt + $undertime_amt;

                    $xcel_hed .='<tr>
                                    <td style=" width: 4% ; text-align:left ">'.$value->id_number.'</td>
                                    <td style=" width:14% ; text-align:left">'.$emp_name.'</td>
                                    <td style=" width: 8% ; text-align:right">'.$sal_amt.' </td>
                                    <td style=" width: 7% ; text-align:right;">'.number_format($ot_amt->amount,2,'.',',').'</td>
                                    <td style=" width: 7% ; text-align:right;">'.number_format($other_amt->amount,2,'.',',').'</td>
                                    <td style=" width: 7% ; text-align:right;">'.number_format($gross_amt->amount,2,'.',',').'</td>
                                    <td style=" width: 7% ; text-align:right;">'.$whtax_amt.'</td>
                                    <td style=" width: 6% ; text-align:right;">'.$sss_amt.'</td>
                                    <td style=" width: 6% ; text-align:right;">'.$phic_amt.'</td>
                                    <td style=" width: 6% ; text-align:right;">'.$hdmf_amt.'</td>
                                    <td style=" width: 6% ; text-align:right;">'.number_format($tardiness_amt,2,'.',',').'</td>
                                    <td style=" width: 7% ; text-align:right;">'.number_format($oth_ded_amt->amount,2,'.',',').'</td>
                                    <td style=" width: 7% ; text-align:right;">'.number_format($tot_ded_amt->amount,2,'.',',').'</td>
                                    <td style=" width: 7% ; text-align:right;">'.$netpay_amt.'</td>
                                </tr>';
                    $t_sal_amt += $sal_amt;
                    $t_ot_amt += $ot_amt->amount;
                    $t_other_amt += $other_amt->amount;
                    $t_gross_amt += $gross_amt->amount;
                    $t_whtax_amt += $whtax_amt;
                    $t_sss_amt += $sss_amt;
                    $t_phic_amt += $phic_amt;
                    $t_hdmf_amt += $hdmf_amt;
                    $t_tardiness_amt += $tardiness_amt;
                    $t_oth_ded_amt += $oth_ded_amt->amount;
                    $t_tot_ded_amt += $tot_ded_amt->amount;
                    $t_netpay_amt += $netpay_amt;
                    $count++;
                    $cnt++;
                }
                $xcel_hed .='   <tr>
                                    <td style=" width:100% ; font-size:2 ; "></td>
                                </tr>
                                <tr>
                                    <td style=" width:100% ; font-size:2 ; border-top-width:3px ; "></td>
                                </tr>';
                if($i == $number_of_page)
                {
                    $xcel_hed .= '<tr>
                                    <td style=" width:18% ; text-align:left  ; ">Grand Total : '.$cnt.'</td>
                                    <td style=" width: 8% ; text-align:right ; ">'.number_format($t_sal_amt,2,'.',',').'</td>
                                    <td style=" width: 7% ; text-align:right ; ">'.number_format($t_ot_amt,2,'.',',').'</td>
                                    <td style=" width: 7% ; text-align:right ; ">'.number_format($t_other_amt,2,'.',',').'</td>
                                    <td style=" width: 7% ; text-align:right ; ">'.number_format($t_gross_amt,2,'.',',').'</td>
                                    <td style=" width: 7% ; text-align:right ; ">'.number_format($t_whtax_amt,2,'.',',').'</td>
                                    <td style=" width: 6% ; text-align:right ; ">'.number_format($t_sss_amt,2,'.',',').'</td>
                                    <td style=" width: 6% ; text-align:right ; ">'.number_format($t_phic_amt,2,'.',',').'</td>
                                    <td style=" width: 6% ; text-align:right ; ">'.number_format($t_hdmf_amt,2,'.',',').'</td>
                                    <td style=" width: 6% ; text-align:right ; ">'.number_format($t_tardiness_amt,2,'.',',').'</td>
                                    <td style=" width: 7% ; text-align:right ; ">'.number_format($t_oth_ded_amt,2,'.',',').'</td>
                                    <td style=" width: 7% ; text-align:right ; ">'.number_format($t_tot_ded_amt,2,'.',',').'</td>
                                    <td style=" width: 7% ; text-align:right ; ">'.number_format($t_netpay_amt,2,'.',',').'</td>
                                </tr>';
                }
                if($count != $allowed_count_per_page)
                {
                    for ($space=1; $space <= ($allowed_count_per_page - $count); $space++) 
                    {
                        $xcel_hed .= '<tr><td></td></tr>';
                    }   
                }
                $xcel_hed .='   
                                <tr>
                                    <td style=" width:50%  ; text-align:left   ; font-size:7  ; ">Run Date: '.date("F d, Y H:i A").'</td>
                                    <td style=" width:50%  ; text-align:right  ; font-size:7  ; ">Page '.$i.' of '.$number_of_page.'</td>
                                </tr>
                            </table>';
                $this->pdf->writeHTML($xcel_hed, true, false, true, false, '');  
                $this->pdf->lastPage();     
            }
        }
        else
        {
            $this->pdf->addPage('P', 'LETTER', true);
            $this->pdf->SetXY(100, 20);
            $this->pdf->writeHTML("No Record Found!", true, false, true, false, '');
        }
    }

    function export_incomes_summary($company_id, $employee_id, $payroll_date, $title){

        /* REPORT FORMAT

        Company Name                                                            
        Payroll Incomes Summary                                                         
        For The Payroll Dated 06/10/2013 : 20113060001                                                          
                                                            
        No. Emp. #  Name of Employee    Dept.   Basic   Reg Hrs OT  ND  NDOT    RST RSTND   RSTXS   RSTXSND SPL Others  Gross Income
        
        |                                                   DETAILS OF TRANSACTIONS                                                 |
                                                            
        |                                                                                               Grand Total:        
                                                            
                                                            
        Run Date:  July 04, 2013 11:17AM                                                                                Page 2 of 2 
        */

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

        $qry = "SELECT DISTINCT p.employee_id FROM {$this->db->dbprefix}$transaction p
                LEFT JOIN {$this->db->dbprefix}employee e ON p.employee_id = e.employee_id
                LEFT JOIN {$this->db->dbprefix}user u ON u.employee_id = e.employee_id
                WHERE 1 AND p.deleted = 0 AND payroll_date = '{$payroll_date}' $company $employee";
        
        $res = $this->db->query($qry);
        $cnt = 1;
        $total_no_employees = $res->num_rows();
        $allowed_count_per_page = 2;
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
                $this->pdf->SetMargins(10,10,10,true);
                $this->pdf->addPage('L', 'LEGAL', true);  
                $this->pdf->SetFontSize( 8 );

                $xcel_hed .='<table>
                                <tr>
                                    <td style=" width:100% ; text-align:left ; ">'.$company_setting_res->company.'</td>
                                </tr>
                                <tr>
                                    <td style=" width:100% ; text-align:left ; ">'.$title.'</td>
                                </tr>
                                <tr>
                                    <td style=" width:100% ; text-align:left ; ">For The Payroll Dated '.date("m/d/Y",strtotime($payroll_date)).' : ('.$date_from. ' - '. $date_to .')</td>
                                </tr>
                                <tr>
                                    <td style=" width:100% ; text-align:left ; "></td>
                                </tr>
                                <tr>
                                    <td style=" width: 3% ; text-align:left  ; ">No.</td>
                                    <td style=" width: 4% ; text-align:left  ; ">Emp. #</td>
                                    <td style=" width:12% ; text-align:left  ; ">Name of Employee</td>';

                $tran_qry = "   SELECT DISTINCT p.transaction_code FROM {$this->db->dbprefix}$transaction p
                                WHERE 1 AND p.deleted = 0 AND payroll_date = '{$payroll_date}' AND transaction_code != 'SALARY' 
                                    AND transaction_type_id IN ( SELECT transaction_type_id FROM {$this->db->dbprefix}payroll_transaction_type WHERE operation = '+' )
                                ORDER BY transaction_code";
                
                $tran_res = $this->db->query($tran_qry);
                
                $tran_cnt = 0;
                foreach ($tran_res->result() as $key => $tran) {
                    
                    $xcel_hed .='<td style=" width: 7% ; text-align:right ; ">'.str_replace('_', ' ', $tran->transaction_code).'</td>';

                    $tran_cnt++;
                }

                $xcel_hed .= '  </tr>
                                <tr>
                                    <td style=" width:100% ; font-size:2 ; "></td>
                                </tr>
                                <tr>
                                    <td style=" width:100% ; font-size:2 ; border-top-width:3px ; "></td>
                                </tr>';

                $emp_qry = "SELECT DISTINCT p.employee_id, e.id_number, u.lastname, u.firstname, u.middlename FROM {$this->db->dbprefix}$transaction p
                                            LEFT JOIN {$this->db->dbprefix}employee e ON p.employee_id = e.employee_id
                                            LEFT JOIN {$this->db->dbprefix}user u ON u.employee_id = e.employee_id
                                            WHERE 1 AND p.deleted = 0 AND payroll_date = '{$payroll_date}' $company $employee
                                            ORDER BY u.lastname, u.firstname, u.middlename ";
                                                
                $limit = ($i - 1) * $allowed_count_per_page;
                $emp_qry .= " LIMIT {$limit},{$allowed_count_per_page}";
                
                $emp_res = $this->db->query($emp_qry);

                foreach ($emp_res->result() as $key => $emp_id) {   
                    
                    $xcel_hed .='<tr>
                                <td style=" width: 3% ; text-align:left  ; ">'.$count.'</td>
                                <td style=" width: 4% ; text-align:left  ; ">'.$emp_id->id_number.'</td>
                                <td style=" width:12% ; text-align:left  ; ">'.$emp_id->lastname.', '.$emp_id->firstname.' '.$emp_id->middlename.'</td>';
                
                    $qry_1 = "SELECT DISTINCT transaction_code FROM {$this->db->dbprefix}$transaction p
                            LEFT JOIN {$this->db->dbprefix}employee e ON p.employee_id = e.employee_id
                            LEFT JOIN {$this->db->dbprefix}user u ON u.employee_id = e.employee_id
                            WHERE 1 AND p.deleted = 0 AND payroll_date = '{$payroll_date}' AND transaction_code != 'SALARY' 
                                AND transaction_type_id IN ( SELECT transaction_type_id FROM {$this->db->dbprefix}payroll_transaction_type WHERE operation = '+' )
                            ORDER BY transaction_code";
                    


                    $pd_res_1 = $this->db->query($qry_1)->result();

                    for ($ctr2=0; $ctr2 < $total_no_employees  ; $ctr2++) {    
                        
                        $pd_qry_1 = "SELECT sum( amount ) as amount
                                    FROM {$this->db->dbprefix}$transaction p
                                    WHERE transaction_code = '".$pd_res_1[$ctr2]->transaction_code."'
                                    AND payroll_date = '{$payroll_date}' AND transaction_code != 'SALARY' AND p.employee_id = $emp_id->employee_id
                                    GROUP BY employee_id, payroll_date ";
                        $res_1 = $this->db->query($pd_qry_1)->row();
                        
                        if($res_1->amount == 0){
                            $dtl_amt = '-';
                        }
                        else{
                            $dtl_amt = $res_1->amount;
                        }
                        $xcel_hed .='<td style=" width: 7% ; text-align:right ; ">'.$dtl_amt.'</td>';
                    }
                    $xcel_hed .='   </tr>';
                    $count++;
                }

                if($i == $number_of_page){
                    $xcel_hed .='<tr>
                                    <td style=" width:100% ; font-size:2 ; "></td>
                                </tr>
                                <tr>
                                    <td style=" width:100% ; font-size:2 ; border-top-width:3px ; "></td>
                                </tr>
                                <tr>
                                    <td style=" width:19% ; text-align:left  ; ">Grand Total : </td>';
                    
                    $tot_qry = "SELECT DISTINCT transaction_code FROM {$this->db->dbprefix}$transaction p
                            LEFT JOIN {$this->db->dbprefix}employee e ON p.employee_id = e.employee_id
                            LEFT JOIN {$this->db->dbprefix}user u ON u.employee_id = e.employee_id
                            WHERE 1 AND p.deleted = 0 AND payroll_date = '{$payroll_date}' AND transaction_code != 'SALARY' 
                                AND transaction_type_id IN ( SELECT transaction_type_id FROM {$this->db->dbprefix}payroll_transaction_type WHERE operation = '+' )
                            ORDER BY transaction_code";
                    $tot_res = $this->db->query($tot_qry)->result();

                    for ($tot_ctr2=0; $tot_ctr2 < $total_no_employees  ; $tot_ctr2++) {    
                    
                        $dtl_qry = "SELECT sum( amount ) as amount
                                    FROM {$this->db->dbprefix}$transaction p
                                    WHERE transaction_code = '".$tot_res[$tot_ctr2]->transaction_code."'
                                    AND payroll_date = '{$payroll_date}' AND transaction_code != 'SALARY'
                                    GROUP BY payroll_date ";
                        $dtl_res = $this->db->query($dtl_qry)->row();
                        
                        if($dtl_res->amount == 0){
                            $tot_dtl_amt = '-';
                        }
                        else{
                            $tot_dtl_amt = $dtl_res->amount;
                       }
                       $xcel_hed .='   <td style=" width: 7% ; text-align:right ; ">'.$tot_dtl_amt.'</td>';
                    }
                    $xcel_hed .='</tr>';
                }
                
                if($count != $allowed_count_per_page)
                {
                    for ($space=1; $space <= ($allowed_count_per_page - $count); $space++) 
                    {
                        $xcel_hed .= '<tr><td></td></tr>';
                    }   
                }
                $xcel_hed .='   
                                <tr>
                                    <td style=" width:50%  ; text-align:left   ; font-size:7  ; ">Run Date: '.date("F d, Y H:i A").'</td>
                                    <td style=" width:50%  ; text-align:right  ; font-size:7  ; ">Page '.$i.' of '.$number_of_page.'</td>
                                </tr>
                            </table>';
                $this->pdf->writeHTML($xcel_hed, true, false, true, false, '');  
                $this->pdf->lastPage();   
           }
        }
        else
        {
            $this->pdf->addPage('P', 'LETTER', true);
            $this->pdf->SetXY(100, 20);
            $this->pdf->writeHTML("No Record Found!", true, false, true, false, '');
        }
    }

    function export_deductions_summary($company_id, $employee_id, $payroll_date, $title){
        /* REPORT FORMAT
        
        Company Name                                                            
        Payroll Deductions Summary                                                          
        For The Payroll Dated 06/10/2013 : 20113060001                                                          
                                                                    
        No. Emp. #  Name of Employee    Dept.   Wtax    SSS PHIC    HDMF    Absent  Late    Undertime   SSSLoan HDMFLoan    Personal    Others  Gross Ded. 16
        |                                                           DETAILS OF TRANSACTIONS                                                                 |
                                                            
        |                                                                                                                          Grand Total:     
                                                                    
                                                                    
        Run Date:  July 04, 2013 11:17AM                                                                                                        Page 2 of 2 

        */      
        //Page header
        $this->pdf->SetMargins(10, 10, 10, true);
        $this->pdf->SetAutoPageBreak(TRUE);
        $this->pdf->addPage('L', 'FOLIO', true);
        $this->pdf->SetFontSize( 8);
        
        /* header  padding-left: 10px;*/
        $company_setting_res = $this->db->query('SELECT * FROM '.$this->db->dbprefix('user_company').' WHERE company_id = "'.$company_id.'"')->row();
        $company = $company_setting_res->company;
        
        $deduction_summary ='
            <table>
                <tr><td style=" width:100% ; text-align:left">'.$company.'</td></tr>
                <tr><td style=" width:100% ; text-align:left">'.$title.'</td></tr>
                <tr><td style=" width:100% ; text-align:left">For The Payroll Dated '.date("m/d/Y",strtotime($payroll_date)).' : </td></tr>
                <tr><td style=" width:100% ; text-align:left"></td></tr>
                <tr> 
                    <td colspan="3%" style="text-align:left;">No.</td>
                    <td colspan="5%" style="text-align:left;">Emp. #</td>
                    <td colspan="15%" style="text-align:left">Name of Employee</td>
                    <td colspan="8%" style="text-align:left">Dept.</td>
                    <td colspan="6%" style="text-align:right">Wtax</td>
                    <td colspan="5%" style="text-align:right">SSS</td>
                    <td colspan="5%" style="text-align:right">PHIC</td>
                    <td colspan="5%" style="text-align:right">HDFM</td>
                    <td colspan="5%" style="text-align:right">Absent</td>
                    <td colspan="5%" style="text-align:right">Late</td>
                    <td colspan="6%" style="text-align:right">Undertime</td>
                    <td colspan="6%" style="text-align:right">SSS Loan</td>
                    <td colspan="6%" style="text-align:right">HDMF Loan</td>
                    <td colspan="6%" style="text-align:right">Personal</td>
                    <td colspan="6%" style="text-align:right">Others</td>
                    <td colspan="7%" style="text-align:right">Gross Ded.</td>
                </tr>
                <tr> 
                    <td style=" width:100% ; font-size:1; border-bottom:3px solid black;"></td>
                </tr>
                <tr> 
                    <td style=" width:100% ; font-size:1;"></td>
                </tr>
        ';

        $emp_separate = explode(',', $employee_id);
        reset($emp_separate);
        foreach ($emp_separate as $key => $value) {
        
            //check transaction
            $this->db->where('employee_id', $value );
            $this->db->where('payroll_date', $payroll_date);
            $count = $this->db->get('payroll_current_transaction')->num_rows();
                
            if($count > 0){ 
            
                $this->db->where('employee_id', $value);
                $employee = $this->db->get('user')->row();
                $emp_id = $employee->employee_id;
                $emp_name = $employee->lastname.', '.$employee->firstname.''.$employee->middlename;
                $dept_id = $employee->department_id;

                $this->db->where('department_id', $dept_id);
                $dept = $this->db->get('user_company_department')->row();
                $dept_code = $dept->department_code;
                $deduction_summary .='
                    <tr> 
                        <td colspan="3%" style="text-align:left">No.</td>
                        <td colspan="5%" style="text-align:left">'.$emp_id.'</td>
                        <td colspan="15%" style="text-align:left">'.$emp_name.'</td>
                        <td colspan="8%" style="text-align:left">'.$dept_code.'</td>
                        
                ';
                $where = "transaction_code = 'WHTAX'";
                $this->db->where($where);
                $this->db->where('employee_id', $value );
                $this->db->where('payroll_date', $payroll_date);
                $ded_Wtax = $this->db->get('payroll_current_transaction')->row();

                $where = "transaction_code = 'SSS_EMP'";
                $this->db->where($where);
                $this->db->where('employee_id', $value );
                $this->db->where('payroll_date', $payroll_date);
                $ded_sss = $this->db->get('payroll_current_transaction')->row();

                $where = "transaction_code = 'PHIC_EMP'";
                $this->db->where($where);
                $this->db->where('employee_id', $value );
                $this->db->where('payroll_date', $payroll_date);
                $ded_phic = $this->db->get('payroll_current_transaction')->row();

                $where = "transaction_code = 'HDMF_EMP'";
                $this->db->where($where);
                $this->db->where('employee_id', $value );
                $this->db->where('payroll_date', $payroll_date);
                $ded_hdmf = $this->db->get('payroll_current_transaction')->row();
                    
                $where = "transaction_code = 'ABSENCES'";
                $this->db->where($where);
                $this->db->where('employee_id', $value );
                $this->db->where('payroll_date', $payroll_date);
                $ded_abs = $this->db->get('payroll_current_transaction')->row();

                $where = "transaction_code = 'DEDUCTION_LATE'";
                $this->db->where($where);
                $this->db->where('employee_id', $value );
                $this->db->where('payroll_date', $payroll_date);
                $ded_late = $this->db->get('payroll_current_transaction')->row();

                $where = "transaction_code = 'DEDUCTION_UNDERTIME'";
                $this->db->where($where);
                $this->db->where('employee_id', $value );
                $this->db->where('payroll_date', $payroll_date);
                $ded_under = $this->db->get('payroll_current_transaction')->row();

                $where = "transaction_code = 'SSS LOAN'";
                $this->db->where($where);
                $this->db->where('employee_id', $value );
                $this->db->where('payroll_date', $payroll_date);
                $ded_sss_loan = $this->db->get('payroll_current_transaction')->row();
                
                $where = "transaction_code = 'HDMF LOAN'";
                $this->db->where($where);
                $this->db->where('employee_id', $value );
                $this->db->where('payroll_date', $payroll_date);
                $ded_hdmf_loan = $this->db->get('payroll_current_transaction')->row();

                $where = "transaction_code = 'PERSONAL'";
                $this->db->where($where);
                $this->db->where('employee_id', $value );
                $this->db->where('payroll_date', $payroll_date);
                $ded_personal = $this->db->get('payroll_current_transaction')->row();
                
                $where = "transaction_type_id IN (3,4,5) and transaction_code NOT IN ('SSS_EMP','HDMF_EMP','PHIC_EMP','WHTAX','ABSENCES','DEDUCTION_UNDERTIME','DEDUCTION_LATE')";
                $this->db->where($where);
                $this->db->where('employee_id', $value );
                $this->db->where('payroll_date', $payroll_date);
                $ded_oth = $this->db->get('payroll_current_transaction')->row();
                
                $ded_gross = $ded_sss->amount + $ded_phic->amount + $ded_hdmf->amount + $ded_abs->amount + $ded_late->amount + $ded_under->amount + $ded_sss_loan->amount + $ded_hdmf_loan->amount + $ded_personal->amount + $ded_oth->amount + $ded_Wtax->amount;
                
                $ded_gross = number_format($ded_gross,2,'.',',');
                $ded_oth = number_format($ded_oth->amount,2,'.',',');   
                $ded_Wtax = number_format($ded_Wtax->amount,2,'.',',');
                $ded_sss = number_format($ded_sss->amount,2,'.',',');
                $ded_phic = number_format($ded_phic->amount,2,'.',',');
                $ded_hdmf = number_format($ded_hdmf->amount,2,'.',',');
                $ded_abs = number_format($ded_abs->amount,2,'.',',');
                $ded_late = number_format($ded_late->amount,2,'.',',');
                $ded_under = number_format($ded_under->amount,2,'.',',');       
                $ded_sss_loan = number_format($ded_sss_loan->amount,2,'.',','); 
                $ded_hdmf_loan = number_format($ded_hdmf_loan->amount,2,'.',',');   
                $ded_personal = number_format($ded_personal->amount,2,'.',','); 

                

                    if ($ded_Wtax > 0){
                        $deduction_summary .= '<td colspan="6%" style="text-align:right">'.$ded_Wtax.'</td>';
                    }
                    else{
                        $deduction_summary .= '<td colspan="6%" style="text-align:right"></td>';    
                    }

                    if ($ded_sss > 0){
                        $deduction_summary .= '<td colspan="5%" style="text-align:right">'.$ded_sss.' </td>';
                    }
                    else{
                        $deduction_summary .= '<td colspan="5%" style="text-align:right"></td>';    
                    }

                    if($ded_phic > 0){                      
                        $deduction_summary .= '<td colspan="5%" style="text-align:right">'.$ded_phic.' </td>';
                    }
                    else{
                        $deduction_summary .= '<td colspan="5%" style="text-align:right"></td>';    
                    }

                    if($ded_hdmf > 0){
                        $deduction_summary .= '<td colspan="5%" style="text-align:right">'.$ded_hdmf.' </td>';
                    }
                    else{
                        $deduction_summary .= '<td colspan="5%" style="text-align:right"></td>';    
                    }
                
                    if($ded_abs > 0){
                        $deduction_summary .= '<td colspan="5%" style="text-align:right">'.$ded_abs.' </td>';
                    }
                    else{
                        $deduction_summary .= '<td colspan="5%" style="text-align:right"></td>';    
                    }

                    if($ded_late > 0){
                        $deduction_summary .= '<td colspan="5%" style="text-align:right">'.$ded_late.' </td>';
                    }
                    else{
                        $deduction_summary .= '<td colspan="5%" style="text-align:right"></td>';    
                    }

                    if($ded_under > 0){
                        $deduction_summary .= '<td colspan="6%" style="text-align:right">'.$ded_under.' </td>';
                    }
                    else{
                        $deduction_summary .= '<td colspan="6%" style="text-align:right"></td>';    
                    }

                    if($ded_sss_loan>0){
                        $deduction_summary .= '<td colspan="6%" style="text-align:right">'.$ded_sss_loan.' </td>';
                    }
                    else{
                        $deduction_summary .= '<td colspan="6%" style="text-align:right"></td>';    
                    }
                    if($ded_hdmf_loan > 0){
                        $deduction_summary .= '<td colspan="6%" style="text-align:right">'.$ded_hdmf_loan.' </td>';
                    }
                    else{
                        $deduction_summary .= '<td colspan="6%" style="text-align:right"></td>';    
                    }

                    if($ded_personal > 0){
                        $deduction_summary .= '<td colspan="6%" style="text-align:right">'.$ded_personal.' </td>';
                    }
                    else{
                        $deduction_summary .= '<td colspan="6%" style="text-align:right"></td>';    
                    }
                
                    if($ded_oth > 0){
                        $deduction_summary .= '<td colspan="6%" style="text-align:right">'.$ded_oth.' </td>';
                    }
                    else{
                        $deduction_summary .= '<td colspan="6%" style="text-align:right"></td>';    
                    }
                
                    if($ded_gross > 0){
                        $deduction_summary .= '<td colspan="7%" style="text-align:right">'.$ded_gross.'</td></tr>';
                    }
                    else{
                        $deduction_summary .= '<td colspan="7%" style="text-align:right"></td></tr>';
                    }
            }
        }
        $deduction_summary .='</table>';
        $this->pdf->writeHTML($deduction_summary, true, false, true, false, '');  
        $this->pdf->lastPage();   
    }
        
    function export_payslip($company_id, $employee_id, $payroll_date, $title){
        /* REPOERT FORMAT

        |                   COMPANY NAME                        
        |                      PAYSLIP                      
        |   _________________           _____________________           
        |       PAY NO.                     PERIOD ENDING       
        |   _________________           _____________________                       
        |     BRANCH CODE                     DEPARTMENT        
        |   __________________________       ________________
        |    EMPLOYEE NAME                      EMPLOYEE NO.    
            
        |   =================================================                       
        |   TAXABLE EARNINGS:                       
        |       Basic Salary                        ######  
        |       Allowance                           ######  
        |           GROSS PAY                       ######  
        |   LESS:                       
        |       PHILHEALTH                          ######  
        |       SSS                                 ######  
        |       PAG IBIG                            ######  
        |           TOTAL DEDUCTION BEFORE TAX      ######  
        |           TOTAL TAXABLE INCOME            ######  
        |   DEDUCTIONS:                     
        |       BIR WITHHOLDING TAX                 ######  
        |       TOTAL DEDUCTIONS                    ######  
        |   -------------------------------------------------
        |                           NET PAY         ######  
        |   =================================================                       
        |   TIN                             12131131313213  
        |   SSS No.                             2121231231  
        |   HDMF No.                            1212123131  
        |   PHILHEALTH No.                      1211131321  
        |   TAX EXEMPTION               (ME1)     75000.00  
        |   YTD DEDUCTIONS                          ######  
        |   YTD EARNINGS (TX + NT)                  ######  
        |   YTD INCOME (TX)                         ######  
        |   TYTD TAX                                ######  
        |                           
        |   MONTHLY RATE                            ######  
        |   DAILY RATE                              ######  
        
        */
        $company = $this->db->query('SELECT * FROM '.$this->db->dbprefix('user_company').' WHERE company_id = "'.$company_id.'"')->row();
       
        $emp_separate = explode(',', $employee_id);
        reset($emp_separate);
        foreach ($emp_separate as $key => $value) {
            
            $emp_count = $this->db->query('SELECT payroll_date FROM '.$this->db->dbprefix('payroll_current_transaction')." WHERE payroll_date = '{$payroll_date}' AND employee_id = {$value}")->num_rows();

            $res = $this->db->query("SELECT e.id_number, u.lastname, u.firstname, u.middlename, d.department_code, e.tin, e.sss, e.pagibig, e.philhealth, p.salary, p.total_year_days
                        FROM {$this->db->dbprefix}user u 
                        LEFT JOIN {$this->db->dbprefix}user_company_department d on d.department_id = u.department_id
                        LEFT JOIN {$this->db->dbprefix}employee e on u.employee_id = e.employee_id
                        LEFT JOIN {$this->db->dbprefix}employee_payroll p on u.employee_id = p.employee_id
                        WHERE u.employee_id = {$value}")->row();

            $monthly_rate = $this->encrypt->decode($res->salary);
            $daily_rate = $monthly_rate / ($res->total_year_days/12) ;

            $taxcode = $this->db->query("SELECT t.amount FROM {$this->db->dbprefix}employee_payroll p LEFT JOIN {$this->db->dbprefix}taxcode t on p.taxcode_id = t.taxcode_id 
                                                WHERE p.employee_id = $value")->row();

            $pay_salary = $this->db->query("SELECT amount FROM {$this->db->dbprefix}payroll_current_transaction WHERE employee_id = $value AND payroll_date = '{$payroll_date}' 
                                                AND transaction_code = 'SALARY'")->row();
            $pay_net = $this->db->query("SELECT amount FROM {$this->db->dbprefix}payroll_current_transaction WHERE employee_id = $value AND payroll_date = '{$payroll_date}' 
                                            AND transaction_code = 'NETPAY'")->row();
            $pay_sss = $this->db->query("SELECT amount FROM {$this->db->dbprefix}payroll_current_transaction WHERE employee_id = $value AND payroll_date = '{$payroll_date}' 
                                            AND transaction_code = 'SSS_EMP'")->row();
            $pay_hdmf = $this->db->query("SELECT amount FROM {$this->db->dbprefix}payroll_current_transaction WHERE employee_id = $value AND payroll_date = '{$payroll_date}' 
                                            AND transaction_code = 'HDMF_EMP'")->row();
            $pay_phic = $this->db->query("SELECT amount FROM {$this->db->dbprefix}payroll_current_transaction WHERE employee_id = $value AND payroll_date = '{$payroll_date}' 
                                            AND transaction_code = 'PHIC_EMP'")->row();
            $pay_tax = $this->db->query("SELECT amount FROM {$this->db->dbprefix}payroll_current_transaction WHERE employee_id = $value AND payroll_date = '{$payroll_date}' 
                                            AND transaction_code = 'WHTAX'")->row();
            $pay_earn = $this->db->query("SELECT sum(amount) as amount FROM {$this->db->dbprefix}payroll_current_transaction WHERE employee_id = {$value} AND transaction_type_id IN (1,2,6,7,8) ")->row();

            $pay_inc = $this->db->query("SELECT sum(amount) as amount FROM {$this->db->dbprefix}payroll_current_transaction WHERE employee_id = {$value} AND transaction_code = 'NETPAY' ")->row();

            $pay_ded = $this->db->query("SELECT sum(amount) as amount FROM {$this->db->dbprefix}payroll_current_transaction WHERE employee_id = {$value} AND transaction_type_id IN (3,4,5) ")->row();

            $pay_ytdtax = $this->db->query("SELECT amount FROM {$this->db->dbprefix}payroll_current_transaction WHERE employee_id = $value AND transaction_code = 'WHTAX'")->row();
                            
            if($emp_count > 0){
                $this->pdf->SetMargins(10, 10, 10, true);   
                $this->pdf->SetAutoPageBreak(TRUE);
                $this->pdf->addPage('P', 'A5', true);    
                $this->pdf->SetFontSize( 8);            

                $xcel_hed = '
                    <table style="width:100%;">
                        <tr>
                            <td width="100%" style="text-align:center;">'.$company->company.'</td>
                        </tr>
                        <tr>
                            <td width="100%" style="text-align:center;">'.$title.'</td>
                        </tr>
                        <tr>
                            <td width="5%"  style="text-align:center"></td>
                            <td width="40%" style="border-bottom:1px solid black;text-align:center"></td>
                            <td width="10%" style="text-align:center"></td>
                            <td width="40%" style="border-bottom:1px solid black;text-align:center"></td>
                            <td width="5%"  style="text-align:center"></td>
                        </tr>                
                        <tr>
                            <td width="5%"  style="text-align:center"></td>
                            <td width="40%" style="text-align:center; font-size:7;">PAY NO.</td>
                            <td width="10%" style="text-align:center"></td>
                            <td width="40%" style="text-align:center; font-size:7;">PERIOD ENDING</td>
                            <td width="5%"  style="text-align:center"></td>
                        </tr> 
                        <tr>
                            <td width="5%"  style="text-align:center"></td>
                            <td width="40%" style="border-bottom:1px solid black; text-align:center"></td>
                            <td width="10%" style="text-align:center"></td>
                            <td width="40%" style="border-bottom:1px solid black; text-align:center">'.$res->department_code.'</td>
                            <td width="5%"  style="text-align:center"></td>
                        </tr> 
                        <tr>
                            <td width="5%"  style="text-align:center"></td>
                            <td width="40%" style="text-align:center; font-size:7;">BRANCH CODE</td>
                            <td width="10%" style="text-align:center"></td>
                            <td width="40%" style="text-align:center; font-size:7;">DEPAERTMENT</td>
                            <td width="5%"  style="text-align:center"></td>
                        </tr>  
                        <tr>
                            <td width="5%" style="text-align:center"></td>
                            <td width="55%" style="border-bottom:1px solid black;text-align:left;">'.$res->lastname.', '.$res->firstname.' '.$res->middleinitial.'</td>
                            <td width="5%" style="text-align:center"></td>
                            <td width="30%" style="border-bottom:1px solid black;text-align:center; ">'.$res->id_number.'</td>
                            <td width="5%" style="text-align:center; "></td>
                        </tr>
                        <tr>
                            <td width="5%" style="text-align:center"></td>
                            <td width="40%" style="text-align:center; font-size:7;">EMPLOYEE NAME</td>
                            <td width="20%" style="text-align:center"></td>
                            <td width="30%" style="text-align:center; font-size:7;">EMPLOYE NO.</td>
                            <td width="5%" style="text-align:center"></td>
                        </tr>
                        <tr>
                            <td width="100%" style="text-align:center;"></td>
                        </tr>
                        <tr>
                            <td width="4%" style="text-align:center"></td>
                            <td width="92%" style="text-align:center;">======================================================================</td>
                            <td width="4%" style="text-align:center"></td>
                        </tr>
                        <tr>
                            <td width="5%" style="text-align:center"></td>
                            <td width="90%" style="text-align:left;">TAXABLE EARNINGS:</td>
                        </tr>
                        <tr>
                            <td width="15%" style="text-align:center"></td>
                            <td width="45%" style="text-align:left;">Basic Salary</td>
                            <td width="5%" style="text-align:right;"></td>
                            <td width="30%" style="text-align:right;">'.$pay_salary->amount.'</td>
                            <td width="5%" style="text-align:right;"></td>
                        </tr>';
                
                $earnings = $this->db->query(" SELECT transaction_id, transaction_code, sum(amount) as amount 
                                                FROM {$this->db->dbprefix}payroll_current_transaction 
                                                WHERE payroll_date = '{$payroll_date}' and employee_id = {$value} 
                                                    AND transaction_type_id IN (1,2,6,7,8) and transaction_code NOT IN ('SALARY')
                                                GROUP BY transaction_id, transaction_code");

                foreach ($earnings->result() as $key => $earn) {

                $earn_code = $this->db->query("SELECT transaction_label FROM {$this->db->dbprefix}payroll_transaction WHERE transaction_id = {$earn->transaction_id}")->row();
                
                if($earn->amount > 0){  
                    $xcel_hed .= ' 
                        <tr>
                            <td width="15%" style="text-align:center"></td>
                            <td width="45%" style="text-align:left;">'.$earn_code->transaction_label.'</td>
                            <td width="5%" style="text-align:right;"></td>
                            <td width="30%" style="text-align:right;">'.$earn->amount.'</td>
                            <td width="5%" style="text-align:right;"></td>
                        </tr>';
                    }  
                }    


                $xcel_hed .= ' 
                        <tr>
                            <td width="5%" style="text-align:center"></td>
                            <td width="90%" style="text-align:left;">LESS:</td>
                        </tr>
                        <tr>
                            <td width="15%" style="text-align:center"></td>
                            <td width="45%" style="text-align:left;">PhilHealth</td>
                            <td width="5%" style="text-align:right;"></td>
                            <td width="30%" style="text-align:right;">'.$pay_phic->amount.'</td>
                            <td width="5%" style="text-align:right;"></td>
                        </tr>
                        <tr>
                            <td width="15%" style="text-align:center"></td>
                            <td width="45%" style="text-align:left;">SSS</td>
                            <td width="5%" style="text-align:right;"></td>
                            <td width="30%" style="text-align:right;">'.$pay_sss->amount.'</td>
                            <td width="5%" style="text-align:right;"></td>
                        </tr>
                        <tr>
                            <td width="15%" style="text-align:center"></td>
                            <td width="45%" style="text-align:left;">Pag-Ibig</td>
                            <td width="5%" style="text-align:right;"></td>
                            <td width="30%" style="text-align:right;">'.$pay_hdmf->amount.'</td>
                            <td width="5%" style="text-align:right;"></td>
                        </tr>';
                

                $xcel_hed .= '
                        <tr>
                            <td width="5%" style="text-align:center"></td>
                            <td width="90%" style="text-align:left;">DEDUCTIONS:</td>
                        </tr>';
                
                $deduction = $this->db->query(" SELECT transaction_id, transaction_code, sum(amount) as amount 
                                                FROM {$this->db->dbprefix}payroll_current_transaction 
                                                WHERE payroll_date = '{$payroll_date}' and employee_id = {$value} 
                                                    AND transaction_type_id IN (3,4,5) and transaction_code NOT IN ('SSS_EMP','HDMF_EMP','PHIC_EMP','WHTAX')
                                                GROUP BY transaction_id, transaction_code");

                foreach ($deduction->result() as $key => $ded) {
                    switch ($ded->transaction_code) {
                        case 'ABSENCES':
                            $ded_code = 'Absences';
                            break;
                        case 'DEDUCTION_LATE':
                            $ded_code = 'Late';
                            break;
                        case 'DEDUCTION_UNDERTIME':
                            $ded_code = 'Undertime';
                            break;
                        default:
                            $ded_code = $this->db->query("SELECT transaction_label FROM {$this->db->dbprefix}payroll_transaction WHERE transaction_id = {$ded->transaction_id}")->row();
                            $ded_code = $ded_code->transaction_label;
                            break;
                    }
                    if($ded->amount > 0){
                    $xcel_hed .= ' 
                        <tr>
                            <td width="15%" style="text-align:center"></td>
                            <td width="45%" style="text-align:left;">'.$ded_code.'</td>
                            <td width="5%" style="text-align:right;"></td>
                            <td width="30%" style="text-align:right;">'.$ded->amount.'</td>
                            <td width="5%" style="text-align:right;"></td>
                        </tr>';
                    }
                }    

                $xcel_hed .= '
                        <tr>
                            <td width="15%" style="text-align:center"></td>
                            <td width="45%" style="text-align:left;">BIR Withholding Tax</td>
                            <td width="5%" style="text-align:right;"></td>
                            <td width="30%" style="text-align:right;">'.$pay_tax->amount.'</td>
                            <td width="5%" style="text-align:right;"></td>
                        </tr>';
                        
                $xcel_hed .= '
                        <tr>
                            <td width="100%" style="text-align:center;">--------------------------------------------------------------------------------------------------------------------------</td>
                        </tr>
                        <tr>
                            <td width="15%" style="text-align:center"></td>
                            <td width="45%" style="text-align:right;">NET PAY</td>
                            <td width="5%" style="text-align:right;"></td>
                            <td width="30%" style="text-align:right;">'.$pay_net->amount.'</td>
                            <td width="5%" style="text-align:right;"></td>
                        </tr>
                        <tr>
                            <td width="4%" style="text-align:center"></td>
                            <td width="92%" style="text-align:center;">======================================================================</td>
                            <td width="4%" style="text-align:center"></td>
                        </tr>

                        <tr>
                            <td width="5%" style="font-size:7; text-align:center"></td>
                            <td width="40%" style="font-size:7; text-align:left;">TIN</td>
                            <td width="10%"  style="font-size:7; text-align:right;"></td>
                            <td width="40%" style="font-size:7; text-align:right;">'.$res->tin.'</td>
                            <td width="5%"  style="font-size:7; text-align:right;"></td>
                        </tr>
                        <tr>
                            <td width="5%" style="font-size:7; text-align:center"></td>
                            <td width="40%" style="font-size:7; text-align:left;">SSS No.</td>
                            <td width="10%"  style="font-size:7; text-align:right;"></td>
                            <td width="40%" style="font-size:7; text-align:right;">'.$res->sss.'</td>
                            <td width="5%"  style="font-size:7; text-align:right;"></td>
                        </tr>
                        <tr>
                            <td width="5%" style="font-size:7; text-align:center"></td>
                            <td width="40%" style="font-size:7; text-align:left;">HDMF No.</td>
                            <td width="10%"  style="font-size:7; text-align:right;"></td>
                            <td width="40%" style="font-size:7; text-align:right;">'.$res->pagibig.'</td>
                            <td width="5%"  style="font-size:7; text-align:right;"></td>
                        </tr>
                        <tr>
                            <td width="5%" style="font-size:7; text-align:center"></td>
                            <td width="40%" style="font-size:7; text-align:left;">PHILHEALTH No.</td>
                            <td width="10%"  style="font-size:7; text-align:right;"></td>
                            <td width="40%" style="font-size:7; text-align:right;">'.$res->philhealth.'</td>
                            <td width="5%"  style="font-size:7; text-align:right;"></td>
                        </tr>
                        <tr>
                            <td width="5%" style="font-size:7; text-align:center"></td>
                            <td width="40%" style="font-size:7; text-align:left;">TAX EXEMPTION</td>
                            <td width="10%"  style="font-size:7; text-align:right;"></td>
                            <td width="40%" style="font-size:7; text-align:right;">'.$taxcode->amount.'</td>
                            <td width="5%"  style="font-size:7; text-align:right;"></td>
                        </tr>
                        <tr>
                            <td width="5%" style="font-size:7; text-align:center"></td>
                            <td width="40%" style="font-size:7; text-align:left;">YTD DEDUCTIONS</td>
                            <td width="10%"  style="font-size:7; text-align:right;"></td>
                            <td width="40%" style="font-size:7; text-align:right;">'.$pay_ded->amount.'</td>
                            <td width="5%"  style="font-size:7; text-align:right;"></td>
                        </tr>
                        <tr>
                            <td width="5%" style="font-size:7; text-align:center"></td>
                            <td width="40%" style="font-size:7; text-align:left;">YTD EARNINGS (TX + NT)</td>
                            <td width="10%"  style="font-size:7; text-align:right;"></td>
                            <td width="40%" style="font-size:7; text-align:right;">'.$pay_earn->amount.'</td>
                            <td width="5%"  style="font-size:7; text-align:right;"></td>
                        </tr>
                        <tr>
                            <td width="5%" style="font-size:7; text-align:center"></td>
                            <td width="40%" style="font-size:7; text-align:left;">YTD INCOME (TX)</td>
                            <td width="10%"  style="font-size:7; text-align:right;"></td>
                            <td width="40%" style="font-size:7; text-align:right;">'.$pay_inc->amount.'</td>
                            <td width="5%"  style="font-size:7; text-align:right;"></td>
                        </tr>
                        <tr>
                            <td width="5%" style="font-size:7; text-align:center"></td>
                            <td width="40%" style="font-size:7; text-align:left;">TYTD TAX</td>
                            <td width="10%"  style="font-size:7; text-align:right;"></td>
                            <td width="40%" style="font-size:7; text-align:right;">'.$pay_ytdtax->amount.'</td>
                            <td width="5%"  style="font-size:7; text-align:right;"></td>
                        </tr>
                        <tr>
                            <td width="100%" style="font-size:5; text-align:center"></td>
                        </tr>
                        <tr>
                            <td width="5%" style="font-size:7; text-align:center"></td>
                            <td width="40%" style="font-size:7; text-align:left;">MONTHLY RATE</td>
                            <td width="10%"  style="font-size:7; text-align:right;"></td>
                            <td width="40%" style="font-size:7; text-align:right;">'.number_format($monthly_rate,2,'.',',').'</td>
                            <td width="5%"  style="font-size:7; text-align:right;"></td>
                        </tr>
                        <tr>
                            <td width="5%" style="font-size:7; text-align:center"></td>
                            <td width="40%" style="font-size:7; text-align:left;">DAILY RATE</td>
                            <td width="10%"  style="font-size:7; text-align:right;"></td>
                            <td width="40%" style="font-size:7; text-align:right;">'.number_format($daily_rate,2,'.',',').'</td>
                            <td width="5%"  style="font-size:7; text-align:right;"></td>
                        </tr>
                    </table>';
            }
           
            // else
            // {
            //     $this->pdf->SetMargins(10, 10, 10, true);   
            //     $this->pdf->SetAutoPageBreak(TRUE);
            //     $this->pdf->addPage('P', 'A5', true);    
            //     $this->pdf->SetFontSize( 8); 
            //     $this->pdf->writeHTML("No Record Found!", true, false, true, false, '');
            // }

            $this->pdf->writeHTML($xcel_hed, true, false, true, false, '');
        }
    }

    function export_whtax($company_id, $employee_id, $payroll_date, $title){
        
        $company = $this->db->query('SELECT * FROM '.$this->db->dbprefix('user_company').' WHERE company_id = "'.$company_id.'"')->row();
        
        if(!empty($employee_id)){
            $where = " AND u.employee_id IN ({$employee_id}) AND u.company_id IN ({$company_id})";
        }
        else{
            $where = " AND u.company_id IN ({$company_id})";
        }
        $qry = "SELECT * FROM {$this->db->dbprefix}payroll_current_transaction p
                LEFT JOIN {$this->db->dbprefix}user u on p.employee_id = u.employee_id
                WHERE transaction_code = 'WHTAX' and payroll_date = '$payroll_date' $where";
        $res = $this->db->query($qry);
        
        $cnt = 1;
        $total_no_employees = $res->num_rows();
        $allowed_count_per_page = 60;
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
                $this->pdf->SetAutoPageBreak(TRUE);
                $this->pdf->addPage('P', 'LETTER', true);    
                $this->pdf->SetFontSize( 8);            
                $xcel_hed = '';

                $xcel_hed .= '<table style="width:100%;">
                                <tr>
                                    <td width="100%" style="text-align:center;">'.$company->company.'</td>
                                </tr>
                                <tr>
                                    <td width="100%" style="text-align:center;">'.$title.'</td>
                                </tr>
                                <tr>
                                    <td width="100%" style="text-align:center;">For the Month of '.date('M Y',strtotime($payroll_date)).'</td>
                                </tr>
                                <tr>
                                    <td width:100% style="font-size:10px;"></td>
                                </tr>
                                <tr>
                                    <td width="5%" style="text-align:left;">No.</td>
                                    <td width="12%" style="text-align:left;">Emp. No.</td>
                                    <td width="38%" style="text-align:left;">Employee Name</td>
                                    <td width="15%" style="text-align:right;">1st Pay</td>
                                    <td width="15%" style="text-align:right;">2nd Pay</td>
                                    <td width="15%" style="text-align:right;">Total</td>
                                </tr>
                                <tr> 
                                    <td width="100%" style="font-size:1; border-bottom:3px solid black;"></td>
                                </tr>
                                <tr> 
                                    <td width="100%" style="font-size:4;"></td>
                                </tr>';

                $limit = ($i - 1) * $allowed_count_per_page;
                $dtl_qry = "SELECT e.id_number, u.lastname, u.firstname, u.middlename, sum(amount) as amount FROM {$this->db->dbprefix}payroll_current_transaction p
                            LEFT JOIN {$this->db->dbprefix}user u on p.employee_id = u.employee_id
                            LEFT JOIN {$this->db->dbprefix}employee e on p.employee_id = e.employee_id
                            WHERE transaction_code = 'WHTAX' and payroll_date = '$payroll_date' $where
                            GROUP BY e.id_number, u.lastname, u.firstname, u.middlename
                            ORDER BY u.lastname, u.firstname, u.middlename
                            LIMIT {$limit},{$allowed_count_per_page}";

                $dtl_res = $this->db->query($dtl_qry);
                
                $count = 0;
                foreach ($dtl_res->result() as $key => $value) 
                {            
                    $xcel_hed .= '
                                <tr>
                                    <td width="5%"  style="text-align:left;">'.$cnt.'</td>
                                    <td width="12%" style="text-align:left;">'.$value->id_number.'</td>
                                    <td width="38%" style="text-align:left;">'.$value->lastname.', '.$value->firstname.' '.$value->middlename.'</td>
                                    <td width="15%" style="text-align:right;">'.number_format($value->amount,2,'.',',').'</td>
                                    <td width="15%" style="text-align:right;">0.00</td>
                                    <td width="15%" style="text-align:right;">'.number_format($value->amount,2,'.',',').'</td> 
                                </tr>';
                    
                    $total_amount += $value->amount;
                    $count++;
                    $cnt++;
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
                $xcel_hed .= '  <tr> 
                                    <td width="100%" style="font-size:5;"></td>
                                </tr>
                                <tr> 
                                    <td width="100%" style="font-size:1; border-bottom:3px solid black;"></td>
                                </tr>
                                <tr> 
                                    <td width="100%" style="font-size:1;"></td>
                                </tr>
                                <tr>
                                    <td width="5%"  style="text-align:left;"></td>
                                    <td width="12%" style="text-align:left;"></td>
                                    <td width="38%" style="text-align:center;"><b>Total:</b></td>
                                    <td width="15%" style="text-align:right;">'.number_format($total_amount,2,'.',',').'</td>
                                    <td width="15%" style="text-align:right;">0.00</td>
                                    <td width="15%" style="text-align:right;">'.number_format($total_amount,2,'.',',').'</td>
                                </tr>';
                // if($i == $number_of_page)
                // {
                //     $xcel_hed .='<tr>
                //                     <td width="5%"  style="text-align:left;   background-color:#DDDDDD;"></td>
                //                     <td width="12%" style="text-align:left;   background-color:#DDDDDD;"></td>
                //                     <td width="38%" style="text-align:center; background-color:#DDDDDD;"><b>Grand Total:</b></td>
                //                     <td width="15%" style="text-align:right;  background-color:#DDDDDD;">'.number_format($total_amount,2,'.',',').'</td>
                //                     <td width="15%" style="text-align:right;  background-color:#DDDDDD;">0.00</td>
                //                     <td width="15%" style="text-align:right;  background-color:#DDDDDD;">'.number_format($total_amount,2,'.',',').'</td>
                //                 </tr>';
                // }
                $xcel_hed .= '  <tr>
                                    <td width="100%" style="font-size:20;"></td>
                                </tr>
                                <tr>
                                    <td width="15%"  style="text-align:right;">Prepared By: </td>
                                    <td width="15%"  style="text-align:left;"> Hannah Linco</td>
                                    <td width="15%"  style="text-align:right;">Checked By: </td>
                                    <td width="20%"  style="text-align:left;"> Roger S. Laciste</td>
                                    <td width="15%"  style="text-align:right;">Approved By: </td>
                                    <td width="20%"  style="text-align:left;"> Roger S. Laciste</td>
                                </tr>
                                <tr>
                                    <td width="15%"  style="text-align:right;"></td>
                                    <td width="15%"  style="text-align:left;"> HR Assistant</td>
                                    <td width="15%"  style="text-align:right;"></td>
                                    <td width="20%"  style="text-align:left;"> Senior Manager - Country</td>
                                    <td width="15%"  style="text-align:right;"></td>
                                    <td width="20%"  style="text-align:left;"> Senior Manager - Country</td>
                                </tr>
                            </table>';
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

    function export_ded($company_id, $employee_id, $payroll_date, $title){
        
        $company = $this->db->query('SELECT * FROM '.$this->db->dbprefix('user_company').' WHERE company_id = "'.$company_id.'"')->row();
        
        if(!empty($employee_id)){
            $where = " AND u.employee_id IN ({$employee_id}) AND u.company_id IN ({$company_id})";
        }
        else{
            $where = " AND u.company_id IN ({$company_id})";
        }
        $qry = "SELECT * FROM {$this->db->dbprefix}payroll_current_transaction p
                LEFT JOIN {$this->db->dbprefix}user u on p.employee_id = u.employee_id
                WHERE transaction_type_id IN (3,4,5) and payroll_date = '$payroll_date' $where";
        $res = $this->db->query($qry);
        
        $cnt = 1;
        $total_no_employees = $res->num_rows();
        $allowed_count_per_page = 60;
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
                $this->pdf->SetAutoPageBreak(TRUE);
                $this->pdf->addPage('P', 'LETTER', true);    
                $this->pdf->SetFontSize( 8);            
                $xcel_hed = '';

                $xcel_hed .= '<table style="width:100%;">
                                <tr>
                                    <td width="100%" style="text-align:center;">'.$company->company.'</td>
                                </tr>
                                <tr>
                                    <td width="100%" style="text-align:center;">'.$title.'</td>
                                </tr>
                                <tr>
                                    <td width="100%" style="text-align:center;">For the Month of '.date('M Y',strtotime($payroll_date)).'</td>
                                </tr>
                                <tr>
                                    <td width:100% style="font-size:10px;"></td>
                                </tr>
                                <tr>
                                    <td width="5%" style="text-align:left;">No.</td>
                                    <td width="12%" style="text-align:left;">Emp. No.</td>
                                    <td width="38%" style="text-align:left;">Employee Name</td>
                                    <td width="15%" style="text-align:right;">1st Pay</td>
                                    <td width="15%" style="text-align:right;">2nd Pay</td>
                                    <td width="15%" style="text-align:right;">Total</td>
                                </tr>
                                <tr> 
                                    <td width="100%" style="font-size:1; border-bottom:3px solid black;"></td>
                                </tr>
                                <tr> 
                                    <td width="100%" style="font-size:4;"></td>
                                </tr>';

                $limit = ($i - 1) * $allowed_count_per_page;
                $dtl_qry = "SELECT e.id_number, u.lastname, u.firstname, u.middlename, sum(amount) as amount FROM {$this->db->dbprefix}payroll_current_transaction p
                            LEFT JOIN {$this->db->dbprefix}user u on p.employee_id = u.employee_id
                            LEFT JOIN {$this->db->dbprefix}employee e on p.employee_id = e.employee_id
                            WHERE transaction_type_id IN (3,4,5) and payroll_date = '$payroll_date' $where
                            GROUP BY e.id_number, u.lastname, u.firstname, u.middlename
                            ORDER BY u.lastname, u.firstname, u.middlename
                            LIMIT {$limit},{$allowed_count_per_page}";

                $dtl_res = $this->db->query($dtl_qry);
                
                $count = 0;
                foreach ($dtl_res->result() as $key => $value) 
                {            
                    $xcel_hed .= '
                                <tr>
                                    <td width="5%"  style="text-align:left;">'.$cnt.'</td>
                                    <td width="12%" style="text-align:left;">'.$value->id_number.'</td>
                                    <td width="38%" style="text-align:left;">'.$value->lastname.', '.$value->firstname.' '.$value->middlename.'</td>
                                    <td width="15%" style="text-align:right;">'.number_format($value->amount,2,'.',',').'</td>
                                    <td width="15%" style="text-align:right;">0.00</td>
                                    <td width="15%" style="text-align:right;">'.number_format($value->amount,2,'.',',').'</td> 
                                </tr>';
                    
                    $total_amount += $value->amount;
                    $count++;
                    $cnt++;
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
                $xcel_hed .= '  <tr> 
                                    <td width="100%" style="font-size:5;"></td>
                                </tr>
                                <tr> 
                                    <td width="100%" style="font-size:1; border-bottom:3px solid black;"></td>
                                </tr>
                                <tr> 
                                    <td width="100%" style="font-size:1;"></td>
                                </tr>
                                <tr>
                                    <td width="5%"  style="text-align:left;"></td>
                                    <td width="12%" style="text-align:left;"></td>
                                    <td width="38%" style="text-align:center;"><b>Total:</b></td>
                                    <td width="15%" style="text-align:right;">'.number_format($total_amount,2,'.',',').'</td>
                                    <td width="15%" style="text-align:right;">0.00</td>
                                    <td width="15%" style="text-align:right;">'.number_format($total_amount,2,'.',',').'</td>
                                </tr>';
                // if($i == $number_of_page)
                // {
                //     $xcel_hed .='<tr>
                //                     <td width="5%"  style="text-align:left;   background-color:#DDDDDD;"></td>
                //                     <td width="12%" style="text-align:left;   background-color:#DDDDDD;"></td>
                //                     <td width="38%" style="text-align:center; background-color:#DDDDDD;"><b>Grand Total:</b></td>
                //                     <td width="15%" style="text-align:right;  background-color:#DDDDDD;">'.number_format($total_amount,2,'.',',').'</td>
                //                     <td width="15%" style="text-align:right;  background-color:#DDDDDD;">0.00</td>
                //                     <td width="15%" style="text-align:right;  background-color:#DDDDDD;">'.number_format($total_amount,2,'.',',').'</td>
                //                 </tr>';
                // }
                $xcel_hed .= '  <tr>
                                    <td width="100%" style="font-size:20;"></td>
                                </tr>
                                <tr>
                                    <td width="15%"  style="text-align:right;">Prepared By: </td>
                                    <td width="15%"  style="text-align:left;"> Hannah Linco</td>
                                    <td width="15%"  style="text-align:right;">Checked By: </td>
                                    <td width="20%"  style="text-align:left;"> Roger S. Laciste</td>
                                    <td width="15%"  style="text-align:right;">Approved By: </td>
                                    <td width="20%"  style="text-align:left;"> Roger S. Laciste</td>
                                </tr>
                                <tr>
                                    <td width="15%"  style="text-align:right;"></td>
                                    <td width="15%"  style="text-align:left;"> HR Assistant</td>
                                    <td width="15%"  style="text-align:right;"></td>
                                    <td width="20%"  style="text-align:left;"> Senior Manager - Country</td>
                                    <td width="15%"  style="text-align:right;"></td>
                                    <td width="20%"  style="text-align:left;"> Senior Manager - Country</td>
                                </tr>
                            </table>';
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

    function export_masterlist($company_id, $employee_id, $payroll_date, $title){
        
        $company = $this->db->query('SELECT * FROM '.$this->db->dbprefix('user_company').' WHERE company_id = "'.$company_id.'"')->row();
        
        if(!empty($employee_id)){
            $where = " AND u.employee_id IN ({$employee_id}) AND u.company_id IN ({$company_id})";
        }
        else{
            $where = " AND u.company_id IN ({$company_id})";
        }
        $qry = "SELECT * FROM {$this->db->dbprefix}payroll_current_transaction p
                LEFT JOIN {$this->db->dbprefix}user u on p.employee_id = u.employee_id
                WHERE transaction_code = 'NETPAY' and payroll_date = '$payroll_date' $where";
        $res = $this->db->query($qry);
        
        $cnt = 1;
        $total_no_employees = $res->num_rows();
        $allowed_count_per_page = 60;
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
                $this->pdf->SetAutoPageBreak(TRUE);
                $this->pdf->addPage('P', 'LETTER', true);    
                $this->pdf->SetFontSize( 8);            
                $xcel_hed = '';

                $xcel_hed .= '<table style="width:100%;">
                                <tr>
                                    <td width="100%" style="text-align:center;">'.$company->company.'</td>
                                </tr>
                                <tr>
                                    <td width="100%" style="text-align:center;">'.$title.'</td>
                                </tr>
                                <tr>
                                    <td width="100%" style="text-align:center;">For the Month of '.date('M Y',strtotime($payroll_date)).'</td>
                                </tr>
                                <tr>
                                    <td width:100% style="font-size:10px;"></td>
                                </tr>
                                <tr>
                                    <td width="5%" style="text-align:left;">No.</td>
                                    <td width="50%" style="text-align:left;">Employee Name</td>
                                    <td width="20%" style="text-align:center;">Account Number</td>
                                    <td width="20%" style="text-align:right;">Amount</td>
                                </tr>
                                <tr> 
                                    <td width="100%" style="font-size:1; border-bottom:3px solid black;"></td>
                                </tr>
                                <tr> 
                                    <td width="100%" style="font-size:4;"></td>
                                </tr>';

                $limit = ($i - 1) * $allowed_count_per_page;
                $dtl_qry = "SELECT e.bank_acct, u.lastname, u.firstname, u.middlename, 
                            sum( ( (RIGHT(LEFT(e.bank_acct,6),2)*amount) + (RIGHT(LEFT(e.bank_acct,8),2)*amount) + (RIGHT(LEFT(e.bank_acct,10),2)*amount) ) ) as amount 
                            FROM {$this->db->dbprefix}payroll_current_transaction p
                            LEFT JOIN {$this->db->dbprefix}user u on p.employee_id = u.employee_id
                            LEFT JOIN {$this->db->dbprefix}employee_payroll e on p.employee_id = e.employee_id
                            WHERE transaction_code = 'NETPAY' and payroll_date = '$payroll_date' $where
                            GROUP BY e.bank_acct, u.lastname, u.firstname, u.middlename
                            ORDER BY u.lastname, u.firstname, u.middlename
                            LIMIT {$limit},{$allowed_count_per_page}";

                $dtl_res = $this->db->query($dtl_qry);
                
                $count = 0;
                foreach ($dtl_res->result() as $key => $value) 
                {            
                    $xcel_hed .= '
                                <tr>
                                    <td width="5%" style="text-align:left;">'.$cnt.'</td>
                                    <td width="50%" style="text-align:left;">'.$value->lastname.', '.$value->firstname.' '.$value->middlename.'</td>
                                    <td width="20%" style="text-align:center;">'.$value->bank_acct.'</td>
                                    <td width="20%" style="text-align:right;">'.number_format($value->amount,2,'.',',').'</td> 
                                </tr>';
                    
                    $total_amount += $value->amount;
                    $count++;
                    $cnt++;
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
                $xcel_hed .= '  <tr> 
                                    <td width="100%" style="font-size:5;"></td>
                                </tr>
                                <tr> 
                                    <td width="100%" style="font-size:1; border-bottom:3px solid black;"></td>
                                </tr>
                                <tr> 
                                    <td width="100%" style="font-size:1;"></td>
                                </tr>
                                <tr>
                                    <td width="70%" style="text-align:left;"><b>TOTAL:</b></td>
                                    <td width="30%" style="text-align:right;">'.number_format($total_amount,2,'.',',').'</td>
                                </tr>';
                if($i == $number_of_page)
                {
                    $xcel_hed .='<tr>
                                    <td width="70%" style="text-align:left;"><b>TOTAL PAYROLL AMOUNT:</b></td>
                                    <td width="30%" style="text-align:right;">'.number_format($total_amount,2,'.',',').'</td>
                                </tr>';
                }
                $xcel_hed .= '  <tr>
                                    <td width="100%" style="font-size:20;"></td>
                                </tr>
                                <tr>
                                    <td width="15%"  style="text-align:right;">Prepared By: </td>
                                    <td width="15%"  style="text-align:left;"> Hannah Linco</td>
                                    <td width="15%"  style="text-align:right;">Checked By: </td>
                                    <td width="20%"  style="text-align:left;"> Roger S. Laciste</td>
                                    <td width="15%"  style="text-align:right;">Approved By: </td>
                                    <td width="20%"  style="text-align:left;"> Roger S. Laciste</td>
                                </tr>
                                <tr>
                                    <td width="15%"  style="text-align:right;"></td>
                                    <td width="15%"  style="text-align:left;"> HR Assistant</td>
                                    <td width="15%"  style="text-align:right;"></td>
                                    <td width="20%"  style="text-align:left;"> Senior Manager - Country</td>
                                    <td width="15%"  style="text-align:right;"></td>
                                    <td width="20%"  style="text-align:left;"> Senior Manager - Country</td>
                                </tr>
                            </table>';
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
