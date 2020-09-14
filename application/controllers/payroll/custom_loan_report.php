<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class custom_loan_report extends MY_Controller
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

    function get_parameters(){
        $report_type = array("Loan Detail","Loan Summary");
        $report_type_html = '<select id="report_type_id" name="report_type_id">';
            foreach($report_type as $report_type_id => $report_type_value){
                $report_type_html .= '<option value="'.$report_type_id.'">'.$report_type_value.'</option>';
            }
        $report_type_html .= '</select>'; 

        $loan_type_res = $this->db->query("SELECT * FROM {$this->db->dbprefix}payroll_loan_type")->result_array();
        $loan_type_html = '<select id="loan_type_id" multiple="multiple" class="multi-select" name="loan_type_id[]">';            
            foreach($loan_type_res as $loan_type_value){
                $loan_type_html .= '<option value="'.$loan_type_value["loan_type_id"].'">'.$loan_type_value["loan_type"].'</option>';
            }
        $loan_type_html .= '</select>'; 

        $loan = $this->db->query("SELECT loan_id, loan FROM {$this->db->dbprefix}payroll_loan
                                          WHERE 1 {$qry_com} {$qry_sta} {$qry_sch} 
                                        ORDER BY loan")->result_array();
        $loan_html = '<select id="loan_id" multiple="multiple" class="multi-select" name="loan_id[]">';
            foreach($loan as $loan_record){
                $loan_html .= '<option value="'.$loan_record["loan_id"].'">'.$loan_record["loan"].'</option>';
            }
        $loan_html .= '</select>';
        
        $paycode = $this->db->query("SELECT
                                          paycode_id,
                                          paycode
                                        FROM {$this->db->dbprefix}payroll_paycode")->result_array();

        $paycode_html = '<select id="paycode_id" multiple="multiple" class="multi-select" name="paycode_id[]">';
        foreach($paycode as $paycode_record){
            $paycode_html .= '<option value="'.$paycode_record["paycode_id"].'">'.$paycode_record["paycode"].'</option>';
        }
        $paycode_html .= '</select>';

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
        $response->loan_type_html = $loan_type_html;
        $response->loan_html = $loan_html;
        $response->paycode_html = $paycode_html;
        $response->company_html = $company_html;
        $response->employee_html = $employee_html;
        $data['json'] = $response;
        $this->load->view('template/ajax', $data);  
    }

    function get_parameters_paycode() {
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
            $pay_code = 'c.paycode_id IN ('.$paycode.')';
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
                                        WHERE b.company_id IS NOT NULL AND {$pay_code}
                                        GROUP BY d.company")->result_array();

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
                                            ON a.employee_id = c.employee_id 
                                        WHERE b.company_id IS NOT NULL AND {$pay_code}
                                        ORDER BY b.lastname")->result_array();
        $employee_html = '<select id="employee_id" multiple="multiple" class="multi-select" name="employee_id[]">';
        foreach($employee as $employee_record){
            $employee_html .= '<option value="'.$employee_record["employee_id"].'">'.$employee_record["lastname"].' '.$employee_record["firstname"].'</option>';
        }
        $employee_html .= '</select>';        

        $response->employee_html = $employee_html;
        $response->company_html = $company_html;
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
            $pay_code = 'c.paycode_id IN ('.$paycode.')';
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
                                          b.company_id,
                                          c.payroll_schedule_id,
                                          a.status_id,
                                          a.employee_id
                                        FROM {$this->db->dbprefix}employee a
                                          LEFT JOIN {$this->db->dbprefix}user b
                                            ON a.employee_id = b.employee_id
                                          LEFT JOIN {$this->db->dbprefix}employee_payroll c
                                            ON a.employee_id = c.employee_id 
                                        WHERE b.company_id IS NOT NULL AND {$pay_code} AND {$company_cd}
                                        ORDER BY b.lastname")->result_array();
        $employee_html = '<select id="employee_id" multiple="multiple" class="multi-select" name="employee_id[]">';
        foreach($employee as $employee_record){
            $employee_html .= '<option value="'.$employee_record["employee_id"].'">'.$employee_record["lastname"].' '.$employee_record["firstname"].'</option>';
        }
        $employee_html .= '</select>';        

        $response->employee_html = $employee_html;
        $data['json'] = $response;
        $this->load->view('template/ajax', $data);  
    }

    function loan_multiple(){
        $qry_com = " AND 1";
        $qry_sta = " AND 1";
        $qry_sch = " AND 1";
        if(isset($_POST['loan_type_id']))
        {
            $loan_arr = array();
            foreach ($_POST['loan_type_id'] as $value) 
            {
                $loan_arr[] = $value;    
            }
            $loan_type_id = implode(',', $loan_arr);
            if(!empty($loan_type_id))
            {
                $qry_com = " AND pl.loan_type_id IN ({$loan_type_id})";
            }
        }

        $loan = $this->db->query("SELECT distinct pl.loan_id, pl.loan FROM {$this->db->dbprefix}payroll_loan pl JOIN {$this->db->dbprefix}employee_loan el ON el.loan_id = pl.loan_id
                                          WHERE 1 {$qry_com} {$qry_sta} {$qry_sch} 
                                        ORDER BY loan")->result_array();
        $loan_html = '<select id="loan_id" multiple="multiple" class="multi-select" name="loan_id[]">';

        foreach($loan as $loan_record){
            $loan_html .= '<option value="'.$loan_record["loan_id"].'">'.$loan_record["loan"].'</option>';
        }
        $loan_html .= '</select>';
        $response->loan_html = $loan_html;

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

        $loan_type_id = ''; 
        if(isset($_POST['loan_type_id']))
        {
            $loan_type_arr = array();
            foreach ($_POST['loan_type_id'] as $value) 
            {
                $loan_type_arr[] = $value;    
            }
            $loan_type_id = implode(',', $loan_type_arr);
        }

        $loan_id = '';
        if(isset($_POST['loan_id']))
        {
            $loan_arr = array();
            foreach ($_POST['loan_id'] as $value) 
            {
                 $loan_arr[] = $value;
            }
            $loan_id = implode(',',$loan_arr);
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

        $date_from = date("Y-m-d",strtotime($_POST['date_range_from']));
        $date_to = date("Y-m-d",strtotime($_POST['date_range_to']));

        $this->load->library('pdf');

        switch ($_POST['report_type_id']) 
        {
            //detail
            case '0':
                $html = $this->export_loan_detail($company_id, $employee_id, $loan_type_id, $loan_id, $date_from, $date_to, $paycode_id, "Employee Loans (Details)");
                $title = "Employee Loans (Details)";
                break;            
            //summary
            case '1':
                $html = $this->export_loan_summary($company_id, $employee_id, $loan_type_id, $loan_id, $date_from, $date_to, $paycode_id, "Employee Loans Summary");
                $title = "Employee Loans Summary";
                break;
        }
        $this->pdf->writeHTML($html, true, false, true, false, '');
        $this->pdf->Output(ucwords(str_replace(" ","_",$title))."_" . date('dmYHis') . '.pdf', 'I');
    }

    function export_loan_detail($company_id, $employee_id, $loan_type_id, $loan_id, $date_from, $date_to, $paycode_id, $title){   

        // check if employee_id is null or empty
        if(!empty($company_id)) {
            $company_qry = " AND company_id IN ($company_id)";
        }

        if(!empty($employee_id)) {            
            $employee_qry = " AND p.employee_id IN ($employee_id)";   
        }

        if(!empty($paycode_id)) {
            $paycode_qry = " AND paycode_id IN ($paycode_id)";
        }
        $record = $this->db->query("SELECT distinct l.employee_id 
                                    FROM {$this->db->dbprefix}employee_loan l 
                                    JOIN {$this->db->dbprefix}user u ON u.employee_id = l.employee_id
                                    JOIN {$this->db->dbprefix}employee_payroll p ON l.employee_id = p.employee_id
                                    WHERE loan_id IN ({$loan_id}) $company_qry $employee_qry $paycode_qry ");
        $record_cnt = $record->num_rows();
        $emp_record = $record->result();

        if( $record_cnt > 0 ){
            foreach ($emp_record as $key => $emp) {
                
                $emploan = $this->db->query("SELECT loan, id_number, CONCAT(lastname, ', ', firstname, IF(u.aux !='' AND u.aux != ' ' AND u.aux IS NOT NULL, CONCAT(' ',u.aux,' '), ' ') , middlename) as emp_name, loan_status, release_date, start_date, amount, no_payments, no_payments_paid, no_payments_remaining, running_balance, system_amortization, total_amount_paid, last_payment_date
                                            FROM hr_employee_loan el 
                                            JOIN hr_payroll_loan pl ON el.loan_id = pl.loan_id 
                                            JOIN hr_employee e ON e.employee_id = el.employee_id 
                                            JOIN hr_user u ON u.employee_id = el.employee_id 
                                            JOIN hr_loan_status s ON s.loan_status_id = el.loan_status_id
                                            WHERE el.employee_id = $emp->employee_id and el.loan_id IN ($loan_id)
                                            ORDER BY lastname, firstname, middlename, loan");
                $loan_empname = $emploan->row();
                $loan_cnt = $emploan->num_rows();
                $loan_dtl = $emploan->result();

                if($loan_cnt > 0){
                    $this->pdf->SetMargins(10, 10, 10, true);   
                    $this->pdf->SetAutoPageBreak(TRUE);
                    $this->pdf->addPage('L', 'A4', true);    
                    $this->pdf->SetFontSize( 8);            

                    $xcel_hed = '
                        <table style="width:100%;">
                            <tr>
                                <td width="100%" style="text-align:center;">'.$title.'</td>
                            </tr>
                            <tr>
                                <td width="100%" style="text-align:center;">As of '.date('M Y',strtotime($date_from)).'</td>
                            </tr>
                            <tr><td width="100%"></td></tr>
                            <tr>
                                <td width="100%" style="text-align:Left;">'.$loan_empname->id_number.' - '.str_replace(' *', '', $loan_empname->emp_name).'</td>
                            </tr>
                            <tr><td width="100%"></td></tr>
                            <tr> 
                                <td width="100%" style="font-size:1; border-bottom:3px solid black;"></td>
                            </tr>
                            <tr>
                                <td width="7%"  style="text-align:Left;">Loan Status</td>
                                <td width="14%"  style="text-align:Left;">Loan</td>
                                <td width="7%"  style="text-align:Left;">Release Date</td>
                                <td width="9%"  style="text-align:right;">Start of Payment</td>
                                <td width="10%"  style="text-align:Left;">Loan Amount</td>
                                <td width="10%"  style="text-align:Left;">Running Balance</td>
                                <td width="9%"  style="text-align:Left;">Amortization</td>
                                <td width="9%"  style="text-align:right;">No. of Payments</td>
                                <td width="9%"  style="text-align:right;">No. of Payments Paid</td>
                                <td width="9%"  style="text-align:right;">Remaining Payment/s</td>
                                <td width="7%"  style="text-align:right;">Last Payment Made</td>
                            </tr>
                            <tr> 
                                <td width="100%" style="font-size:1; border-bottom:3px solid black;"></td>
                            </tr>
                            <tr> 
                                <td width="100%" style="font-size:4;"></td>
                            </tr>';

                    foreach ($loan_dtl as $key => $dtl) {
                            $xcel_hed.='
                                <tr>
                                    <td width="7%"  style="text-align:Left;">'.$dtl->loan_status.'</td>
                                    <td width="14%"  style="text-align:Left;">'.$dtl->loan.'</td>
                                    <td width="7%"  style="text-align:Left;">'.date('Y-m-d',strtotime($dtl->release_date)).'</td>
                                    <td width="9%"  style="text-align:right;">'.date('Y-m-d',strtotime($dtl->start_date)).'</td>
                                    <td width="10%"  style="text-align:Left;">'.number_format($dtl->amount,2,'.',',').'</td>
                                    <td width="10%"  style="text-align:Left;">'.number_format($dtl->running_balance,2,'.',',').'</td>
                                    <td width="9%"  style="text-align:Left;">'.number_format($dtl->system_amortization,2,'.',',').'</td>
                                    <td width="9%"  style="text-align:right;">'.$dtl->no_payments.'</td>
                                    <td width="9%"  style="text-align:right;">'.$dtl->no_payments_paid.'</td>
                                    <td width="9%"  style="text-align:right;">'.$dtl->no_payments_remaining.'</td>
                                    <td width="7%"  style="text-align:right;">'.date('Y-m-d',strtotime($dtl->last_payment_date)).'</td>
                                </tr>';
                    }
                        $xcel_hed.='</table>';
                }
                $this->pdf->writeHTML($xcel_hed, true, false, true, false, '');
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
  
    function export_loan_summary($company_id, $employee_id, $loan_type_id, $loan_id, $date_from, $date_to, $paycode_id, $title){   

        if(!empty($company_id)){

            $company = " AND company_id IN ($company_id)";
        }
        if(!empty($employee_id)){
            
            $employee = " AND u.employee_id IN ($employee_id)";   
        }

        if(!empty($paycode_id)){
            $pay_code = 'AND ep.paycode_id IN ('.$paycode_id.')';
        }

        $record = $this->db->query("SELECT distinct pl.loan_id
                                        FROM hr_payroll_closed_transaction c
                                        JOIN hr_employee_loan el ON c.employee_id = el.employee_id
                                        JOIN hr_payroll_loan pl ON el.loan_id = pl.loan_id AND pl.amortization_transid = c.transaction_id
                                        JOIN hr_employee e ON e.employee_id = c.employee_id
                                        JOIN hr_user u ON c.employee_id = u.employee_id 
                                        JOIN hr_employee_payroll ep ON ep.employee_id = e.employee_id
                                        WHERE c.payroll_date BETWEEN '{$date_from}' AND '{$date_to}' AND pl.loan_id IN ({$loan_id}) $company $employee $pay_code ");
        $record_cnt = $record->num_rows();
        $loan = $record->result();

        if( $record_cnt > 0 ){
            foreach ($loan as $key => $value) {
                
                $emploan = $this->db->query("SELECT loan, id_number,  CONCAT(lastname, ', ', firstname, IF(u.aux !='' AND u.aux != ' ' AND u.aux IS NOT NULL, CONCAT(' ',u.aux), ' ') , middlename) AS emp_name, SUM(lp.amount) AS amount
                                            FROM hr_employee_loan el
                                            JOIN hr_employee_loan_payment lp ON lp.employee_loan_id = el.employee_loan_id
                                            JOIN hr_employee e ON e.employee_id = el.employee_id
                                            JOIN hr_user u ON el.employee_id = u.employee_id
                                            JOIN hr_payroll_loan pl ON el.loan_id = pl.loan_id
                                            JOIN hr_employee_payroll ep ON el.employee_id = ep.employee_id
                                            WHERE pl.loan_id = {$value->loan_id} $company $employee $pay_code
                                                AND lp.date_paid BETWEEN '{$date_from}' AND '{$date_to}'
                                            GROUP BY loan, id_number, lastname, firstname, middlename
                                            ORDER BY loan, lastname, firstname, middlename");

                $tot_amt = $this->db->query("SELECT SUM(lp.amount) AS amount
                                            FROM hr_employee_loan el
                                            JOIN hr_employee_loan_payment lp ON lp.employee_loan_id = el.employee_loan_id
                                            JOIN hr_employee e ON e.employee_id = el.employee_id
                                            JOIN hr_user u ON el.employee_id = u.employee_id
                                            JOIN hr_payroll_loan pl ON el.loan_id = pl.loan_id
                                            JOIN hr_employee_payroll ep ON el.employee_id = ep.employee_id
                                            WHERE pl.loan_id = {$value->loan_id} $company $employee $pay_code
                                                AND lp.date_paid BETWEEN '{$date_from}' AND '{$date_to}'")->row();
                // dbug($emploan->row());
                // dbug($emploan->result());
                $loan_name = $emploan->row();
                $loan_cnt = $emploan->num_rows();
                
                if($loan_cnt > 0){
                    $this->pdf->SetMargins(10, 10, 10, true);   
                    $this->pdf->SetAutoPageBreak(TRUE);
                    $this->pdf->addPage('P', 'A5', true);    
                    $this->pdf->SetFontSize( 8);            

                    $xcel_hed = '
                        <table style="width:100%;">
                            <tr>
                                <td width="100%" style="text-align:center;">'.$title.'</td>
                            </tr>
                            <tr>
                                <td width="100%" style="text-align:center;">For the Month of '.date('M Y',strtotime($date_from)).'</td>
                            </tr>
                            <tr><td width="100%"></td></tr>
                            <tr>
                                <td width="100%" style="text-align:Left;">'.$loan_name->loan.'</td>
                            </tr>
                            <tr><td width="100%"></td></tr>
                            <tr>
                                <td width="10%"  style="text-align:Left;">No.</td>
                                <td width="15%"  style="text-align:Left;">Emp. No.</td>
                                <td width="40%"  style="text-align:Left;">Employee Name</td>
                                <td width="35%"  style="text-align:right;">Amount</td>
                            </tr>
                            <tr> 
                                <td width="100%" style="font-size:1; border-bottom:3px solid black;"></td>
                            </tr>
                            <tr> 
                                <td width="100%" style="font-size:4;"></td>
                            </tr>';

                    $count = 1;
                    foreach ($emploan->result() as $key => $emp_loan) {
                            $xcel_hed.='
                                <tr>
                                    <td width="10%"  style="text-align:Left;">'.$count.'</td>
                                    <td width="15%"  style="text-align:Left;">'.$emp_loan->id_number.'</td>
                                    <td width="40%"  style="text-align:Left;">'.str_replace(' *', '', $emp_loan->emp_name).'</td>
                                    <td width="35%"  style="text-align:right;">'.number_format($emp_loan->amount,2,'.',',').'</td>
                                </tr>';

                                $count++;
                        }
                        $xcel_hed.='
                                <tr> 
                                    <td width="100%" style="font-size:1; border-bottom:3px solid black;"></td>
                                </tr>
                                <tr> 
                                    <td width="100%" style="font-size:4;"></td>
                                </tr>
                                <tr>
                                    <td width="60%"  style="text-align:right;"><h4>TOTAL :</h4></td>
                                    <td width="40%"  style="text-align:right;"><h4>'.number_format( $tot_amt->amount,2,'.',',').'</h4></td>
                                </tr>
                                </table>';
                }
                $this->pdf->writeHTML($xcel_hed, true, false, true, false, '');
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

}

/* End of file */
/* Location: system/application */
?>