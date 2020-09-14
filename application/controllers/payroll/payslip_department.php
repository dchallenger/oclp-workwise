<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class payslip_department extends my_controller
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

        $paycode = $this->db->query("SELECT
                                          paycode_id,
                                          paycode
                                        FROM {$this->db->dbprefix}payroll_paycode")->result_array();

        $paycode_html = '<select id="paycode_id" multiple="multiple" class="multi-select" name="paycode_id[]">';
        foreach($paycode as $paycode_record){
            $paycode_html .= '<option value="'.$paycode_record["paycode_id"].'">'.$paycode_record["paycode"].'</option>';
        }
        $paycode_html .= '</select>';

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
                                        WHERE b.company_id IS NOT NULL
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
                                        WHERE b.company_id IS NOT NULL
                                        ORDER BY b.lastname")->result_array();
        $employee_html = '<select id="employee_id" multiple="multiple" class="multi-select" name="employee_id[]">';
        foreach($employee as $employee_record){
            $employee_html .= '<option value="'.$employee_record["employee_id"].'">'.$employee_record["lastname"].' '.$employee_record["firstname"].'</option>';
        }
        $employee_html .= '</select>';        

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

        $payroll_date = date("Y-m-d",strtotime($_POST['payroll_date']));

        $this->load->library('pdf');
        $html = $this->export_employee($paycode_id, $company_id, $employee_id, $payroll_date, $title);        
        $title = "Employee Records";
        $this->pdf->addPage('L', 'A4', true);
        $this->pdf->SetFontSize( 8 );
        $this->pdf->writeHTML($html, true, false, true, false, '');
        $this->pdf->Output(ucwords(str_replace(" ","_",$title))."_" . date('dmYHis') . '.pdf', 'I');
    }
    
    function export_employee($paycode_id, $company_id, $employee_id, $payroll_date, $title){
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
            $pay_code = 'AND pct.paycode_id = '.$paycode_id;
        }

        $paycode = $this->db->query("select paycode from {$this->db->dbprefix}payroll_paycode where paycode_id = $paycode_id")->row();

        $proj_qry = $this->db->query("SELECT distinct pct.cost_code, pct.code_status_id FROM {$this->db->dbprefix}payroll_closed_transaction pct
                                    LEFT JOIN {$this->db->dbprefix}user u on u.employee_id = pct.employee_id
                                    WHERE 1 AND pct.payroll_date = '{$payroll_date}' $company $employee $pay_code
                                    ORDER BY pct.cost_code,pct.code_status_id");
        
        $proj_cnt = $proj_qry->num_rows();
        $proj_record = $proj_qry->result();

        if( $proj_cnt > 0 ){
            foreach ($proj_record as $key => $proj) {
                switch ($proj->code_status_id) {
                    case 1:
                        $code_status = '-0-63010'; //PMT
                        break;
                    case 2:
                        $code_status = '-0-13010'; //DIRECT 
                        break;
                    case 3:
                        $code_status = '-0-13020'; // INDIRECT
                        break;
                    default:
                        $code_status = '';
                        break;
                }

                $dtl_cnt = $this->db->query("SELECT * FROM {$this->db->dbprefix}payroll_closed_transaction pct
                                    WHERE pct.cost_code = '{$proj->cost_code}' AND pct.code_status_id = {$proj->code_status_id} AND pct.payroll_date = '{$payroll_date}' $pay_code ")->num_rows();

                $mdate = getdate(date("U"));
                $mdate = "$mdate[weekday], $mdate[month] $mdate[mday], $mdate[year]";


                //BASIC
                $basic = $this->db->query(" SELECT sum(amount) as amount FROM {$this->db->dbprefix}payroll_closed_transaction pct
                                            WHERE payroll_date = '{$payroll_date}' AND transaction_code IN ('SALARY') AND cost_code = '{$proj->cost_code}' AND pct.code_status_id = {$proj->code_status_id} $pay_code ")->row();
                //ABSENT
                $absences = $this->db->query("SELECT sum(amount) as amount FROM {$this->db->dbprefix}payroll_closed_transaction pct
                                            WHERE payroll_date = '{$payroll_date}' AND transaction_code IN ('ABSENCES', 'LWOP') AND cost_code = '{$proj->cost_code}' AND pct.code_status_id = {$proj->code_status_id} $pay_code ")->row();

                //TARDY
                $tardy = $this->db->query(" SELECT sum(amount) as amount FROM {$this->db->dbprefix}payroll_closed_transaction pct
                                            WHERE payroll_date = '{$payroll_date}' AND transaction_code IN ('DEDUCTION_LATE') AND cost_code = '{$proj->cost_code}' AND pct.code_status_id = {$proj->code_status_id} $pay_code ")->row();

                //UNDERTIME
                $undertime = $this->db->query("SELECT sum(amount) as amount FROM {$this->db->dbprefix}payroll_closed_transaction pct
                                            WHERE payroll_date = '{$payroll_date}' AND transaction_code IN ('DEDUCTION_UNDERTIME') AND cost_code = '{$proj->cost_code}' AND pct.code_status_id = {$proj->code_status_id} $pay_code ")->row();

                $tot_inc = $this->db->query("SELECT  SUM(amount) as amount
                                            FROM {$this->db->dbprefix}payroll_closed_transaction pct
                                            LEFT JOIN {$this->db->dbprefix}payroll_transaction pt ON pct.transaction_id = pt.transaction_id
                                            WHERE payroll_date = '{$payroll_date}' AND pct.transaction_type_id IN (1,2) AND pct.transaction_code != 'salary' AND pt.transaction_class_id != 10 AND cost_code = '{$proj->cost_code}' AND pct.code_status_id = {$proj->code_status_id} $pay_code ")->row();

                $ovrtme = $this->db->query("SELECT  SUM(amount) as amount
                                            FROM {$this->db->dbprefix}payroll_closed_transaction pct
                                            LEFT JOIN {$this->db->dbprefix}payroll_transaction pt ON pct.transaction_id = pt.transaction_id
                                            WHERE payroll_date = '{$payroll_date}' AND pct.transaction_type_id IN (1,2) AND pct.transaction_code != 'salary' AND pt.transaction_class_id = 10 AND cost_code = '{$proj->cost_code}' AND pct.code_status_id = {$proj->code_status_id} $pay_code ")->row();

                $total_1 = $tot_inc->amount + $basic->amount - ($tardy->amount + $undertime->amount + $absences->amount) + $ovrtme->amount;

                $income = $this->db->query("SELECT pt.transaction_label, SUM(amount) as amount
                                            FROM {$this->db->dbprefix}payroll_closed_transaction pct
                                            LEFT JOIN {$this->db->dbprefix}payroll_transaction pt ON pct.transaction_id = pt.transaction_id
                                            WHERE payroll_date = '{$payroll_date}' AND pct.transaction_type_id IN (1,2) AND pct.transaction_code != 'salary' AND pt.transaction_class_id != 10 AND cost_code = '{$proj->cost_code}' AND pct.code_status_id = {$proj->code_status_id} $pay_code
                                            GROUP BY pt.transaction_label
                                            ORDER BY pt.transaction_label")->result();

                $netpay = $this->db->query("SELECT sum(amount) as amount FROM {$this->db->dbprefix}payroll_closed_transaction pct
                                            WHERE payroll_date = '{$payroll_date}' AND transaction_code IN ('NETPAY') AND cost_code = '{$proj->cost_code}' AND pct.code_status_id = {$proj->code_status_id} $pay_code ")->row();

                $philhealth = $this->db->query("SELECT sum(amount) as amount FROM {$this->db->dbprefix}payroll_closed_transaction pct
                                            WHERE payroll_date = '{$payroll_date}' AND transaction_code IN ('PHIC_EMP') AND cost_code = '{$proj->cost_code}' AND pct.code_status_id = {$proj->code_status_id} $pay_code ")->row();

                $pagibig = $this->db->query("SELECT sum(amount) as amount FROM {$this->db->dbprefix}payroll_closed_transaction pct
                                            WHERE payroll_date = '{$payroll_date}' AND transaction_code IN ('HDMF_EMP') AND cost_code = '{$proj->cost_code}' AND pct.code_status_id = {$proj->code_status_id} $pay_code ")->row();

                $sss = $this->db->query("   SELECT sum(amount) as amount FROM {$this->db->dbprefix}payroll_closed_transaction pct
                                            WHERE payroll_date = '{$payroll_date}' AND transaction_code IN ('SSS_EMP') AND cost_code = '{$proj->cost_code}' AND pct.code_status_id = {$proj->code_status_id} $pay_code ")->row();

                $whtax = $this->db->query(" SELECT sum(amount) as amount FROM {$this->db->dbprefix}payroll_closed_transaction pct
                                            WHERE payroll_date = '{$payroll_date}' AND transaction_code IN ('WHTAX') AND cost_code = '{$proj->cost_code}' AND pct.code_status_id = {$proj->code_status_id} $pay_code ")->row();

                $loan = $this->db->query("  SELECT pt.transaction_label, sum(amount) as amount FROM {$this->db->dbprefix}payroll_closed_transaction pct
                                            LEFT JOIN {$this->db->dbprefix}payroll_transaction pt on pct.transaction_id = pt.transaction_id
                                            WHERE payroll_date = '{$payroll_date}' AND pt.transaction_code != 'WHTAX' AND pt.transaction_type_id = 3 AND pt.transaction_class_id = 26 AND cost_code = '{$proj->cost_code}' AND pct.code_status_id = {$proj->code_status_id} $pay_code
                                            GROUP BY pt.transaction_label
                                            ORDER BY pct.transaction_code")->result();

                $oth_ded = $this->db->query("SELECT pt.transaction_label, sum(amount) as amount FROM {$this->db->dbprefix}payroll_closed_transaction pct
                                            LEFT JOIN {$this->db->dbprefix}payroll_transaction pt on pct.transaction_id = pt.transaction_id
                                            WHERE payroll_date = '{$payroll_date}' AND pt.transaction_code != 'WHTAX' AND pt.transaction_type_id = 3 AND pt.transaction_class_id != 26 AND cost_code = '{$proj->cost_code}' AND pct.code_status_id = {$proj->code_status_id} $pay_code 
                                            GROUP BY pt.transaction_label
                                            ORDER BY pct.transaction_code")->result();

                $tot_loan = $this->db->query("SELECT sum(amount) as amount FROM {$this->db->dbprefix}payroll_closed_transaction pct
                                            LEFT JOIN {$this->db->dbprefix}payroll_transaction pt on pct.transaction_id = pt.transaction_id
                                            WHERE payroll_date = '{$payroll_date}' AND pt.transaction_code != 'WHTAX' AND pt.transaction_type_id = 3 AND pt.transaction_class_id = 26 AND cost_code = '{$proj->cost_code}' AND pct.code_status_id = {$proj->code_status_id} $pay_code ")->row();

                $tot_oth_ded = $this->db->query("SELECT sum(amount) as amount FROM {$this->db->dbprefix}payroll_closed_transaction pct
                                            LEFT JOIN {$this->db->dbprefix}payroll_transaction pt on pct.transaction_id = pt.transaction_id
                                            WHERE payroll_date = '{$payroll_date}' AND pt.transaction_code != 'WHTAX' AND pt.transaction_type_id = 3 AND pt.transaction_class_id != 26 AND cost_code = '{$proj->cost_code}' AND pct.code_status_id = {$proj->code_status_id} $pay_code ")->row();

                $total_2 = $netpay->amount + $philhealth->amount + $pagibig->amount + $sss->amount + $whtax->amount + $tot_loan->amount + $tot_oth_ded->amount; 

                if($dtl_cnt > 0){
                    $this->pdf->SetMargins(10, 10, 10, true);   
                    $this->pdf->SetAutoPageBreak(TRUE);
                    $this->pdf->addPage('P', 'A4', true);    
                    $this->pdf->SetFontSize( 9);            

                    $xcel_hed = '
                        <table style="width:100%;">
                            <tr>
                                <td width="100%" style="font-size: 11; "><b>'.$company_setting_res->company.'</b></td>
                            </tr>
                            <tr><td width="100%" style="font-size:2; "></td></tr>
                            <tr>
                                <td width="100%">PAYSLIP-CONTROL TOTAL / Department</td>
                            </tr>
                            <tr>
                                <td width="100%" >FOR THE PERIOD : FROM '.$date_period.'</td>
                            </tr>
                            <tr>
                                <td width="100%" style="text-align:Left;">AS OF '.$mdate.' PAY CODE : '.$paycode->paycode.'</td>
                            </tr>
                            <tr><td width="100%" style="font-size:3; "></td></tr>
                            <tr> 
                                <td width="100%" style="font-size:2; border-bottom:5px solid black;"></td>
                            </tr>
                            <tr><td width="100%" style="font-size:3; "></td></tr>
                            <tr>
                                <td width="100%"><strong>DEPARTMENT : '.$proj->cost_code.$code_status.'</strong></td>
                            </tr>
                            <h4>
                            <tr>
                                <td width="2%"></td>
                                <td width="60%"  style="text-align:Left;">Basic Salary</td>
                                <td width="20%"  style="text-align:right;">'.( $basic->amount != "" ? number_format($basic->amount,2,'.',',') : "0.00" ).'</td>
                            </tr>
                            <tr>
                                <td width="2%"></td>
                                <td width="60%"  style="text-align:Left;">Absent</td>
                                <td width="20%"  style="text-align:right;"> -'.( $absences->amount != "" ? number_format($absences->amount,2,'.',',') : "0.00" ).'</td>
                            </tr>
                            <tr>
                                <td width="2%"></td>
                                <td width="60%"  style="text-align:Left;">Tardy</td>
                                <td width="20%"  style="text-align:right;"> -'.( $tardy->amount != "" ? number_format($tardy->amount,2,'.',',') : "0.00" ).'</td>
                            </tr>
                            <tr>
                                <td width="2%"></td>
                                <td width="60%"  style="text-align:Left;">Undertime</td>
                                <td width="20%"  style="text-align:right;"> -'.( $undertime->amount != "" ? number_format($undertime->amount,2,'.',',') : "0.00" ).'</td>
                            </tr>';

                    foreach ($income as $key => $inc) {

                            $xcel_hed.='
                                <tr>
                                    <td width="2%"></td>
                                    <td width="60%"  style="text-align:Left;">'.$inc->transaction_label.'</td>
                                    <td width="20%"  style="text-align:right;">'.( $inc->amount != "" ? number_format($inc->amount,2,'.',',') : "0.00" ).'</td>
                                </tr>';
                    }
                    if($ovrtme->amount > 0){
                        $xcel_hed.='
                                <tr>
                                    <td width="2%"></td>
                                    <td width="60%"  style="text-align:Left;">Overtime Pay</td>
                                    <td width="20%"  style="text-align:right;">'.( $ovrtme->amount != "" ? number_format($ovrtme->amount,2,'.',',') : "0.00" ).'</td>
                                </tr>';
                    }
                    
                    $xcel_hed.='</h4>
                            <tr><h3>
                                <td width="2%"></td>
                                <td width="60%"  style="text-align:Left;">TOTAL</td>
                                <td width="20%"  style="text-align:right;">'.( $total_1 != "" ? number_format($total_1,2,'.',',') : "0.00" ).'</td>
                                </h3>
                            </tr>
                            <h4>
                            <tr>
                                <td width="2%"></td>
                                <td width="60%"  style="text-align:Left;">Net Salary</td>
                                <td width="20%"  style="text-align:right;">'.( $netpay->amount != "" ? number_format($netpay->amount,2,'.',',') : "0.00" ).'</td>
                            </tr>
                            <tr>
                                <td width="2%"></td>
                                <td width="60%"  style="text-align:Left;">Employee MCR</td>
                                <td width="20%"  style="text-align:right;">'.( $philhealth->amount != "" ? number_format($philhealth->amount,2,'.',',') : "0.00" ).'</td>
                            </tr>
                            <tr>
                                <td width="2%"></td>
                                <td width="60%"  style="text-align:Left;">Employee Pag-Ibig</td>
                                <td width="20%"  style="text-align:right;">'.( $pagibig->amount != "" ? number_format($pagibig->amount,2,'.',',') : "0.00" ).'</td>
                            </tr>
                            <tr>
                                <td width="2%"></td>
                                <td width="60%"  style="text-align:Left;">Employee SSS</td>
                                <td width="20%"  style="text-align:right;">'.( $sss->amount != "" ? number_format($sss->amount,2,'.',',') : "0.00" ).'</td>
                            </tr>
                            <tr>
                                <td width="2%"></td>
                                <td width="60%"  style="text-align:Left;">Whtax</td>
                                <td width="20%"  style="text-align:right;">'.( $whtax->amount != "" ? number_format($whtax->amount,2,'.',',') : "0.00" ).'</td>
                            </tr>';
                       
                    foreach ($loan as $key => $ln) {
                        $xcel_hed.='
                            <tr>
                                <td width="2%"></td>
                                <td width="60%"  style="text-align:Left;">'.$ln->transaction_label.'</td>
                                <td width="20%"  style="text-align:right;">'.( $ln->amount != "" ? number_format($ln->amount,2,'.',',') : "0.00" ).'</td>
                            </tr>';
                    }
                    foreach ($oth_ded as $key => $ded) {
                        $xcel_hed.='
                            <tr>
                                <td width="2%"></td>
                                <td width="60%"  style="text-align:Left;">'.$ded->transaction_label.'</td>
                                <td width="20%"  style="text-align:right;">'.( $ded->amount != "" ? number_format($ded->amount,2,'.',',') : "0.00" ).'</td>
                            </tr>';
                    }
                    $xcel_hed.='</h4>
                            <tr><h3>
                                <td width="2%"></td>
                                <td width="60%"  style="text-align:Left;">TOTAL</td>
                                <td width="20%"  style="text-align:right;">'.( $total_2 != "" ? number_format($total_2,2,'.',',') : "0.00" ).'</td>
                                </h3>
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
