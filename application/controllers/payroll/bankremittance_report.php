<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class bankremittance_report extends MY_Controller
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

    // START - default module functions
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

        //Select Report type:
        // $report_type = array("Bank Remittance", "Bank To Disk");
        $report_type = array("Bank Remittance", "Bank To Disk", "Deductions", "Income Non-Tax", "Income Taxable", "Loans");
        $report_type_html = '<select id="report_type_id" name="report_type_id">';
            foreach($report_type as $report_type_id => $report_type_value){
                $report_type_html .= '<option value="'.$report_type_id.'">'.$report_type_value.'</option>';
            }
        $report_type_html .= '</select>';   

        // type
        $tran_type = array("Current", "Historical");
        $tran_type_html = '<select id="tran_type_id" name="tran_type_id">';
            foreach($tran_type as $tran_type_id => $tran_type_value){
                $tran_type_html .= '<option value="'.$tran_type_id.'">'.$tran_type_value.'</option>';
            }
        $tran_type_html .= '</select>';

        //paycode
        $paycode = $this->db->query('SELECT * FROM '.$this->db->dbprefix('payroll_paycode').'')->result_array();
        $paycode_html = '<select id="paycode_id" name="paycode_id">';
        $paycode_html .= '<option value="">Select...</option>';
            foreach($paycode as $paycode_record){
                 $paycode_html .= '<option value="'.$paycode_record["paycode_id"].'">'.$paycode_record["paycode"].'</option>';
            }
        $paycode_html .= '</select>'; 

        //Select Company
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
        $response->tran_type_html = $tran_type_html;
        $response->employee_html = $employee_html;
        $response->company_html = $company_html;
        $response->paycode_html = $paycode_html;
        $data['json'] = $response;
        $this->load->view('template/ajax', $data);  
    }

    function employee_multiple(){

        $company_id = $_POST['company_id'];
        $qry_com = " AND b.company_id IN ({$company_id})";
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
                                        WHERE 1 {$qry_com}
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

        $posting_date = date("Y-m-d",strtotime($_POST['posting_date']));  
        $payroll_date = date("Y-m-d",strtotime($_POST['payroll_date']));  

        $bank = $_POST['bank_id'];
        $file_name   = $_POST['file_name'];

        $paycode_id = $_POST['paycode_id'];

        $tran_type = $_POST['tran_type_id'];
        
        $this->load->library('pdf');
        switch ($_POST['report_type_id']) 
        {
            //payslip
            case '0':
                $html = $this->export_remittance($company_id, $employee_id, $payroll_date, $posting_date, $title, $tran_type, $bank, $paycode_id );
                $title = "BANK REMITTANCE";
                break;            
            //bank remittance
            case '1':
                $html = $this->export_to_disk($company_id, $employee_id, $payroll_date, $posting_date, $title, $tran_type, $bank, $file_name, $paycode_id );
                $title = "Bank to Disk";
                break;
            case '2':
                $html = $this->export_deductions($company_id, $employee_id, $payroll_date, $posting_date, $title, $tran_type, $bank, $paycode_id );
                $title = "Bank to Disk";
                break;
            case '3':
                $html = $this->export_inc_non_tax($company_id, $employee_id, $payroll_date, $posting_date, $title, $tran_type, $bank, $paycode_id );
                $title = "Bank to Disk";
                break;
            case '4':
                $html = $this->export_income_tax($company_id, $employee_id, $payroll_date, $posting_date, $title, $tran_type, $bank, $paycode_id );
                $title = "Bank to Disk";
                break;
            case '5':
                $html = $this->export_loans($company_id, $employee_id, $payroll_date, $posting_date, $title, $tran_type, $bank, $paycode_id );
                $title = "Bank to Disk";
                break;
        }   
        
        $this->pdf->writeHTML($html, true, false, true, false, '');
        $this->pdf->Output(ucwords(str_replace(" ","_",$title))."_" . date('dmYHis') . '.pdf', 'I');
    }


    
    function export_remittance( $company_id, $employee_id, $payroll_date, $posting_date, $title, $tran_type, $bank, $paycode_id ){
        
        $company_setting_res = $this->db->query('SELECT * FROM '.$this->db->dbprefix('user_company').' WHERE company_id = "'.$company_id.'"')->row();

        switch ($tran_type) {
            case 0:
                $transaction = "payroll_current_transaction";
                break;
            
            case 1:
                $transaction = "payroll_closed_transaction";
                break;
        }

        $bank_qry = $this->db->query('SELECT * FROM '.$this->db->dbprefix('bank').' WHERE bank_id = "'.$bank.'"')->row();
        $bank_code_numeric = $bank_qry->bank_code_numeric;
        $batch_no = $bank_qry->batch_no;
        $branch_code = $bank_qry->branch_code;
        $account_no = str_replace('-','',$bank_qry->account_no);
        $ceiling_amount = str_replace('.','',$bank_qry->ceiling_amount);

        if(empty($file_name)) {
            $filename = $bank_code_numeric;
        }

        else{
            $filename = $file_name;
        }
        
        if(!empty($company_id)){

            $company = " AND company_id IN ($company_id)";
        }
        if(!empty($employee_id)){
            
            $employee = " AND p.employee_id IN ($employee_id)";   
        }

        if(!empty($paycode_id)){
            $pay_code = 'AND p.paycode_id = '.$paycode_id;
        }

        $qry = "SELECT * FROM {$this->db->dbprefix}$transaction p
                LEFT JOIN {$this->db->dbprefix}employee_payroll e ON e.employee_id = p.employee_id
                LEFT JOIN {$this->db->dbprefix}user u ON u.employee_id = p.employee_id
                WHERE payroll_date = '{$payroll_date}' AND transaction_code = 'NETPAY' AND u.inactive != 1
                    AND e.bank_id = $bank AND e.payment_type_id = 1 $employee $company $pay_code";

        $res = $this->db->query($qry);
        $cnt = 1;
        $total_no_employees = $res->num_rows();
        switch ($bank) {
            case '2':
                $allowed_count_per_page = 45;
                break;
            case '3':
                $allowed_count_per_page = 45;
                break;
            case '6':
                $allowed_count_per_page = 45;
                break;
            case '7':
                $allowed_count_per_page = 40;
                break;
        }
        
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
                $this->pdf->addPage('P', 'A4', true);  
                $this->pdf->SetFontSize( 10 );

                $total_payroll_qry = "SELECT sum(amount) AS amount_total
                            FROM {$this->db->dbprefix}$transaction p
                            LEFT JOIN {$this->db->dbprefix}employee_payroll e ON e.employee_id = p.employee_id
                            LEFT JOIN {$this->db->dbprefix}user u ON u.employee_id = p.employee_id 
                            WHERE payroll_date = '{$payroll_date}' AND transaction_code = 'NETPAY' AND u.inactive != 1
                                AND e.bank_id = $bank  AND e.payment_type_id = 1 $employee $company $pay_code";

                $total_payroll_res = $this->db->query($total_payroll_qry)->row();

                $dtl_qry = "SELECT bank_acct , amount, ( (LEFT(RIGHT(bank_acct,6),2)*amount) + (LEFT(RIGHT(bank_acct,4),2)*amount) + (LEFT(RIGHT(bank_acct,2),2)*amount) ) AS hash_amt
                            FROM {$this->db->dbprefix}$transaction p
                            LEFT JOIN {$this->db->dbprefix}employee_payroll e ON e.employee_id = p.employee_id
                            LEFT JOIN {$this->db->dbprefix}user u ON u.employee_id = p.employee_id
                            WHERE payroll_date = '{$payroll_date}' AND transaction_code = 'NETPAY' AND u.inactive != 1
                                AND e.bank_id = $bank  AND e.payment_type_id = 1 $employee $company $pay_code";
                $limit = ($i - 1) * $allowed_count_per_page;
                $dtl_qry .= " LIMIT {$limit},{$allowed_count_per_page}";
                $dtl_res = $this->db->query($dtl_qry);
                switch ($bank) {
                    //BDO
                    case '1':
                        break;
                    case '2':
                        $xcel_hed =' 
                            <table>
                                <tr>
                                    <td style=" width:50%  ; text-align:left   ; font-size: 9  ; ">'.date("F d, Y H:i A").'</td>
                                    <td style=" width:50%  ; text-align:right  ; font-size: 9  ; ">Page '.$i.' of '.$number_of_page.'</td>
                                </tr>
                                <tr><td></td></tr>
                                <tr>
                                    <td style=" width:100% ; text-align:left ; font-size: 10 "><b>BANCO DE ORO</b></td>
                                </tr>
                                <tr>
                                    <td style=" width:100% ; text-align:left ; font-size: 10 ">ORTIGAS EXCHANGE ROAD BRANCH</td>
                                </tr>
                                <tr>
                                    <td style=" width:100% ; text-align:left ; font-size: 10 ">G/F PSEC EAST TOWER, EXCHANGE ROAD</td>
                                </tr>
                                <tr>
                                    <td style=" width:100% ; text-align:left ; font-size: 10 ">ORTIGAS CENTER, PASIG CITY</td>
                                </tr>
                                <tr><td></td></tr>
                                <tr>
                                    <td style=" width:100% ; text-align:center ; ">Attention : <b>GEORGE R. DOSADO</b></td>
                                </tr>
                                <tr>
                                    <td style=" width:100% ; text-align:center ; "><b>Branch Manager</b></td>
                                </tr>
                                <tr><td></td></tr>
                                <tr>
                                    <td style=" width:100% ; text-align:left ; ">          Please debit CURRENT ACCOUNT number <b>3430086730</b> and credit account of the following employees upon receipt hereof on ______________________________.</td>
                                </tr>
                                <tr>
                                    <td style=" width:100% ; text-align:left ; "></td>
                                </tr>
                            
                                <tr> 
                                    <td style=" width: 15%  ; text-align:left  ; "></td>
                                    <td style=" width: 10% ; text-align:left  ; border-top-width:3px ; border-bottom-width:3px; border-right-width:3px ; border-left-width:3px ; "></td>
                                    <td style=" width: 30% ; text-align:center; border-top-width:3px ; border-bottom-width:3px; border-right-width:3px ; border-left-width:3px ; "><b>ACCOUNT NUMBER</b></td>
                                    <td style=" width: 30% ; text-align:right ; border-top-width:3px ; border-bottom-width:3px; border-right-width:3px ; border-left-width:3px ; "><b>AMOUNT</b></td>
                                    <td style=" width: 15%  ; text-align:left  ; "></td>
                                </tr>
                            </table>';
                        foreach ($dtl_res->result() as $key => $value) {
                            $xcel_hed .= '
                            <table>
                                <tr> 
                                    <td style=" width: 15% ; text-align:left  ; "></td>
                                    <td style=" width: 10% ; text-align:left  ; border-top-width:3px ; border-bottom-width:3px; border-right-width:3px ; border-left-width:3px ; ">'.$cnt.'</td>
                                    <td style=" width: 30% ; text-align:center; border-top-width:3px ; border-bottom-width:3px; border-right-width:3px ; border-left-width:3px ; ">'.$value->bank_acct.'</td>
                                    <td style=" width: 30% ; text-align:right ; border-top-width:3px ; border-bottom-width:3px; border-right-width:3px ; border-left-width:3px ; ">'.number_format($value->amount,2,'.',',').'</td>
                                    <td style=" width: 15% ; text-align:left  ; "></td>
                                </tr>
                            </table>';
                                $cnt++;
                        }
                        if($i == $number_of_page){

                            $xcel_hed .= '
                            <table>
                                <tr><td></td></tr>
                                <tr>
                                    <td style=" width: 15% ; text-align:right ; "></td>
                                    <td style=" width: 10% ; text-align:right ; "></td>
                                    <td style=" width: 30% ; text-align:right ;  border-top-width:3px ; border-bottom-width:3px; border-right-width:3px ; border-left-width:3px ;">GRAND TOTAL</td>
                                    <td style=" width: 30% ; text-align:right ;  border-top-width:3px ; border-bottom-width:3px; border-right-width:3px ; border-left-width:3px ;"><b>'.number_format($total_payroll_res->amount_total,2,'.',',').'</b></td>
                                    <td style=" width: 15% ; text-align:left  ; "></td>
                                </tr>
                                <tr><td></td></tr>
                                <tr>
                                    <td style=" width:100% ; text-align:left ; ">&nbsp;&nbsp;Please make sure that no copy of this bank advise shall be given to any company personnel without the express permission of the undersigned.</td>
                                </tr>
                                <tr><td></td></tr>
                                <tr><td></td></tr>
                                <tr><td></td></tr>
                                <tr>
                                    <td style=" width:10% ; text-align:left ; "></td>
                                    <td style=" width:30% ; text-align:left ; ">_____________________________</td>
                                    <td style=" width:20% ; text-align:left ; "></td>
                                    <td style=" width:30% ; text-align:left ; ">_____________________________</td>
                                    <td style=" width:10% ; text-align:left ; "></td>
                                </tr>
                            </table>';
                        }
                        break;

                    //BPI
                    case '3':
                        $xcel_hed ='
                            <table>
                                <tr>
                                    <td style=" width:50%  ; text-align:left   ; font-size: 9  ; ">'.date("F d, Y H:i A").'</td>
                                    <td style=" width:50%  ; text-align:right  ; font-size: 9  ; ">Page '.$i.' of '.$number_of_page.'</td>
                                </tr>
                                <tr>
                                    <td style=" width:100% ; text-align:left ; font-size: 9 ">'.$company_setting_res->company.'</td>
                                </tr>
                                <tr><td></td></tr>
                                <tr>
                                    <td style=" width:100% ; text-align:center ; "><h3>Payroll Transaction Prooflist</h3></td>
                                </tr>
                                <tr>
                                    <td style=" width:100% ; text-align:center ; ">Payroll Date : '.date("m/d/Y",strtotime($posting_date)).'</td>
                                </tr>
                                <tr>
                                    <td style=" width:100% ; text-align:left ; "></td>
                                </tr>
                                <tr> 
                                    <td style=" width: 20% ; text-align:left;">Company Code : </td>
                                    <td style=" width: 30% ; text-align:left;">'.$bank_code_numeric.'</td>
                                    <td style=" width: 20% ; text-align:left;">Ceiling Amount : </td>
                                    <td style=" width: 30% ; text-align:right;">'.number_format($bank_qry->ceiling_amount,2,'.',',').'</td>
                                </tr>
                                <tr> 
                                    <td style=" width: 20% ; text-align:left;">Account Number : </td>
                                    <td style=" width: 30% ; text-align:left;">'.$bank_qry->account_no.'</td>
                                    <td style=" width: 20% ; text-align:left;">Record Count : </td>
                                    <td style=" width: 30% ; text-align:right;">'.$total_no_employees.'</td>
                                </tr>
                                <tr> 
                                    <td style=" width: 20% ; text-align:left;">Batch No : </td>
                                    <td style=" width: 30% ; text-align:left;">'.$batch_no.'</td>
                                    <td style=" width: 20% ; text-align:left;">Total Payroll Amount : </td>
                                    <td style=" width: 30% ; text-align:right;">'.number_format($total_payroll_res->amount_total,2,'.',',').'</td>
                                </tr>
                                
                                <tr>
                                    <td style=" width:100% ; font-size:2 ; "></td>
                                </tr>
                                <tr>
                                    <td style=" width:100% ; font-size:2 ; border-top:3px solid black; "></td>
                                </tr>
                                <tr> 
                                    <td style=" width:100% ; font-size:3 ; border-top:3px solid black;"></td>
                                </tr>
                                <tr>
                                    <td style=" width: 25% ; text-align:center  ;"><H4>Account Number</H4></td>
                                    <td style=" width: 25% ; text-align:right   ;"><H4>Transaction Amount</H4></td>
                                    <td style=" width: 25% ; text-align:right   ;"><H4>Horizontal Hash</H4></td>
                                    <td style=" width: 25% ; text-align:center  ;"><H4>Remarks</H4></td>
                                </tr>
                                <tr> 
                                    <td style=" width:100% ; font-size:1 ; border-bottom:3px solid black;"></td>
                                </tr>
                                <tr> 
                                    <td width="100%" style="font-size:4;"></td>
                                </tr>
                                ';
                        $count = 0;
                        foreach ($dtl_res->result() as $key => $value) {
                            $xcel_hed .='
                                <tr>
                                    <td style=" width: 25% ; text-align:center ;">'.$value->bank_acct.'</td>
                                    <td style=" width: 25% ; text-align:right  ;">'.$value->amount.'</td>
                                    <td style=" width: 25% ; text-align:right  ;">'.number_format($value->hash_amt,2,'.',',').'</td>
                                    <td style=" width: 25% ; text-align:center ;"></td>
                                </tr>';
                                $count++;
                        }
                        if($count != $allowed_count_per_page)
                        {
                            for ($space=1; $space <= ($allowed_count_per_page - $count); $space++) 
                            {
                                $xcel_hed .= '<tr><td></td></tr>';
                            }   
                        }                 
                        
                        $xcel_hed .='   <tr><td></td></tr><tr><td></td></tr><tr><td></td></tr>
                                        <tr>
                                            <td style=" width:12%  ; text-align:left   ; font-size: 9 ; ">Prepared By : </td>
                                            <td style=" width:36%  ; text-align:left  ; font-size: 9 ; border-bottom:3px solid black; "></td>
                                            <td style=" width: 2%  ; text-align:left  ; font-size: 9 ; "></td>
                                            <td style=" width:12%  ; text-align:left   ; font-size: 9 ; ">Approved By : </td>
                                            <td style=" width:36%  ; text-align:left  ; font-size: 9 ; border-bottom:3px solid black; "></td>
                                        </tr>
                                    </table>';
                        
                        break;

                    //UCPB
                    case '6':
                        $xcel_hed =' 
                            <table>
                                <tr>
                                    <td style=" width:50%  ; text-align:left   ; font-size: 9  ; ">'.date("F d, Y H:i A").'</td>
                                    <td style=" width:50%  ; text-align:right  ; font-size: 9  ; ">Page '.$i.' of '.$number_of_page.'</td>
                                </tr>
                                <tr><td></td></tr>
                                <tr>
                                    <td style=" width:100% ; text-align:left ; "><b>UCPB</b></td>
                                </tr>
                                <tr>
                                    <td style=" width:100% ; text-align:left ; "><b>GROUND FLOOR WEST TEKTITE TOWER</b></td>
                                </tr>
                                <tr>
                                    <td style=" width:100% ; text-align:left ; "><b>ORTIGAS CENTER PASIG CITY</b></td>
                                </tr>
                                <tr>
                                    <td style=" width:100% ; text-align:left ; "><b>PASIG METRO MANILA</b></td>
                                </tr>
                                <tr><td></td></tr>
                                <tr>
                                    <td style=" width:100% ; text-align:left ; ">Attention : THE BRANCH MANAGER</td>
                                </tr>
                                <tr><td></td></tr>
                                <tr>
                                    <td style=" width:100% ; text-align:left ; font-size: 9 ">This is to authorize you to debit SAVINGS ACCOUNT NO. 196-000580-0 of FIRST BALFOUR, INC. the amount in P E S O S</td>
                                </tr>
                                <tr>
                                    <td style=" width:100% ; text-align:left ; "><b>***'.$this->convert_number_to_words($total_payroll_res->amount_total).' PESOS ONLY ***(P***'.number_format($total_payroll_res->amount_total,2,'.',',').'***) </b>and credit the following accounts :</td>
                                </tr>
                                <tr>
                                    <td style=" width:100% ; text-align:left ; "></td>
                                </tr>
                                <tr><td></td></tr>
                                <tr>
                                    <td style=" width:20% ; text-align:left ; ">Filename : </td>
                                    <td style=" width: 5% ; text-align:left ; ">:</td>
                                    <td style=" width:20% ; text-align:left ; ">CM.TXT</td>
                                </tr>
                                <tr>
                                    <td style=" width:20% ; text-align:left ; ">Total Payroll Amount : </td>
                                    <td style=" width: 5% ; text-align:left ; ">:</td>
                                    <td style=" width:20% ; text-align:left ; ">'.number_format($total_payroll_res->amount_total,2,'.',',').'</td>
                                </tr>
                                <tr>
                                    <td style=" width:20% ; text-align:left ; ">Payroll Credit Date : </td>
                                    <td style=" width: 5% ; text-align:left ; ">:</td>
                                    <td style=" width:20% ; text-align:left ; ">'.date("F d, Y",strtotime( $posting_date)).'</td>
                                </tr>
                                <tr>
                                    <td style=" width:20% ; text-align:left ; ">Record Count : </td>
                                    <td style=" width: 5% ; text-align:left ; ">:</td>
                                    <td style=" width:20% ; text-align:left ; ">'.$total_no_employees.'</td>
                                </tr>
                                <tr><td></td></tr>
                                <tr> 
                                    <td style=" width: 10% ; text-align:left  ; border-top-width:3px ; border-bottom-width:3px; border-right-width:3px ; border-left-width:3px ; "><b>REC. NO</b></td>
                                    <td style=" width: 22% ; text-align:center; border-top-width:3px ; border-bottom-width:3px; border-right-width:3px ; border-left-width:3px ; "><b>BANK ACCT#</b></td>
                                    <td style=" width: 30% ; text-align:right ; border-top-width:3px ; border-bottom-width:3px; border-right-width:3px ; border-left-width:3px ; "><b>CREDIT AMOUNT</b></td>
                                </tr>
                                
                            </table>';
                        foreach ($dtl_res->result() as $key => $value) {
                            $xcel_hed .= '
                            <table>
                                <tr> 
                                    <td style=" width: 10% ; text-align:left  ; border-top-width:3px ; border-bottom-width:3px; border-right-width:3px ; border-left-width:3px ; ">'.$cnt.'</td>
                                    <td style=" width: 22% ; text-align:center; border-top-width:3px ; border-bottom-width:3px; border-right-width:3px ; border-left-width:3px ; ">'.$value->bank_acct.'</td>
                                    <td style=" width: 30% ; text-align:right ; border-top-width:3px ; border-bottom-width:3px; border-right-width:3px ; border-left-width:3px ; ">'.number_format($value->amount,2,'.',',').'</td>
                                </tr>
                            </table>';
                                $cnt++;
                        }
                        if($i == $number_of_page){

                            $xcel_hed .= '
                            <table>
                                <tr><td></td></tr>
                                <tr>   
                                    <td style=" width: 3% ; text-align:left  ;  border-top-width:3px ; border-bottom-width:3px; border-left-width:3px ;"></td> 
                                    <td style=" width: 19% ; text-align:left  ;  border-top-width:3px ; border-bottom-width:3px; ">NO. OF RECORDS : </td>
                                    <td style=" width: 5% ; text-align:right ;  border-top-width:3px ; border-bottom-width:3px; border-right-width:3px ; ">'.($cnt-1).'</td>
                                    <td style=" width: 2% ; text-align:left  ; "></td>
                                    <td style=" width: 3% ; text-align:left  ;  border-top-width:3px ; border-bottom-width:3px; border-left-width:3px ;"></td> 
                                    <td style=" width: 15% ; text-align:left  ;  border-top-width:3px ; border-bottom-width:3px; ">GRAND TOTAL :</td>
                                    <td style=" width: 15% ; text-align:right ;  border-top-width:3px ; border-bottom-width:3px; border-right-width:3px ; ">'.number_format($total_payroll_res->amount_total,2,'.',',').'</td>
                                </tr>
                                <tr><td></td></tr>
                                <tr>
                                    <td style=" width: 5% ; text-align:left ; "></td>
                                    <td style=" width:25% ; text-align:left ; ">Disekette External Label</td>
                                    <td style=" width:20% ; text-align:left ; "></td>
                                </tr>
                                <tr>
                                    <td style=" width: 5% ; text-align:left ; "></td>
                                    <td style=" width:20% ; text-align:left ; ">Debit Account Code</td>
                                    <td style=" width: 5% ; text-align:left ; ">:</td>
                                    <td style=" width:20% ; text-align:left ; ">196-000580-0</td>
                                </tr>
                                <tr>
                                    <td style=" width: 5% ; text-align:left ; "></td>
                                    <td style=" width:20% ; text-align:left ; ">Filename</td>
                                    <td style=" width: 5% ; text-align:left ; ">:</td>
                                    <td style=" width:20% ; text-align:left ; ">CM.TXT</td>
                                </tr>
                                <tr>
                                    <td style=" width: 5% ; text-align:left ; "></td>
                                    <td style=" width:20% ; text-align:left ; ">Total Payroll Amount</td>
                                    <td style=" width: 5% ; text-align:left ; ">:</td>
                                    <td style=" width:20% ; text-align:left ; ">'.number_format($total_payroll_res->amount_total,2,'.',',').'</td>
                                </tr>
                                <tr>
                                    <td style=" width: 5% ; text-align:left ; "></td>
                                    <td style=" width:20% ; text-align:left ; ">Number of Records</td>
                                    <td style=" width: 5% ; text-align:left ; ">:</td>
                                    <td style=" width:20% ; text-align:left ; ">'.($cnt-1).'</td>
                                </tr>

                            </table>';
                        }
                        break;

                    // Security Bank    
                    case '7':
                        $xcel_hed ='
                            <table>
                               <tr>
                                    <td style=" width:100%  ; text-align:right  ; font-size: 9 ; ">Page '.$i.' of '.$number_of_page.'</td>
                                </tr>
                                <tr><td></td></tr>
                                <tr>
                                    <td style=" width:100% ; text-align:center ; "><h3>Security Bank Corporation</h3></td>
                                </tr>
                                <tr>
                                    <td style=" width:100% ; text-align:center ; font-size: 9 ; ">Payroll for: '.$company_setting_res->company.'</td>
                                </tr>
                                <tr>
                                    <td style=" width:100% ; text-align:center ; font-size: 9 ; ">Company Payroll Funding / Debit Account : <u>'.$bank_qry->account_no.'</u></td>
                                </tr>
                                <tr>
                                    <td style=" width:100% ; text-align:center ; font-size: 9 ; ">Payroll Credit Date : '.date("m/d/Y",strtotime($posting_date)).'</td>
                                </tr>
                                <tr> 
                                    <td width="100%" style="font-size:8;"></td>
                                </tr>
                                <tr>
                                    <td style=" width: 10% ; font-size: 3 ; text-align:center; border-top-width: 3px; border-right-width: 3px; border-left-width: 3px; "></td>
                                    <td style=" width: 45% ; font-size: 3 ; text-align:center; border-top-width: 3px; border-right-width: 3px; "></td>
                                    <td style=" width: 45% ; font-size: 3 ; text-align:center; border-top-width: 3px; border-right-width: 3px; "></td>
                                </tr>
                                <tr>
                                    <td style=" width: 10% ; text-align:center; border-right-width: 3px; border-left-width: 3px; "><H5>#</H5></td>
                                    <td style=" width: 45% ; text-align:center; border-right-width: 3px; "><H5>ACCOOUNT NUMBER</H5></td>
                                    <td style=" width: 45% ; text-align:center; border-right-width: 3px; "><H5>CREDIT AMOUNT</H5></td>
                                </tr>
                                <tr>
                                    <td style=" width: 10% ; font-size: 3 ; text-align:center; border-right-width: 3px; border-bottom-width:3px ; border-left-width: 3px; "></td>
                                    <td style=" width: 45% ; font-size: 3 ; text-align:center; border-right-width: 3px; border-bottom-width:3px ; "></td>
                                    <td style=" width: 45% ; font-size: 3 ; text-align:center; border-right-width: 3px; border-bottom-width:3px ; "></td>
                                </tr>

                                <tr> 
                                    <td style=" width:100% ; font-size:4;"></td>
                                </tr>
                                ';
                        $count = 0;
                        foreach ($dtl_res->result() as $key => $value) {
                            $xcel_hed .='
                                <tr>
                                    <td style=" width: 10% ; font-size: 1 ; text-align:center; border-top-width: 3px; border-right-width: 3px; border-left-width: 3px; "></td>
                                    <td style=" width: 45% ; font-size: 1 ; text-align:center; border-top-width: 3px; border-right-width: 3px; "></td>
                                    <td style=" width: 45% ; font-size: 1 ; text-align:center; border-top-width: 3px; border-right-width: 3px; "></td>
                                </tr>
                                <tr>
                                    <td style=" width: 10% ; text-align:center; border-right-width: 3px; border-left-width: 3px; ">'.$cnt.'</td>
                                    <td style=" width: 45% ; text-align:center; border-right-width: 3px; ">'.$value->bank_acct.'</td>
                                    <td style=" width: 45% ; text-align:center; border-right-width: 3px; ">'.$value->amount.'</td>
                                </tr>
                                <tr>
                                    <td style=" width: 10% ; font-size: 1 ; text-align:center; border-right-width: 3px; border-bottom-width:3px ; border-left-width: 3px; "></td>
                                    <td style=" width: 45% ; font-size: 1 ; text-align:center; border-right-width: 3px; border-bottom-width:3px ; "></td>
                                    <td style=" width: 45% ; font-size: 1 ; text-align:center; border-right-width: 3px; border-bottom-width:3px ; "></td>
                                </tr>
                                ';

                                $count++;
                                $cnt++;
                        }
                        if($count != $allowed_count_per_page)
                        {
                            for ($space=1; $space <= ($allowed_count_per_page - $count); $space++) 
                            {
                                $xcel_hed .= '<tr><td></td></tr>';
                            }   
                        }                 
                        $xcel_hed .='   <tr><td></td></tr>
                                        <tr> 
                                            <td style=" width:100% ; text-align:left ; font-size: 9 ; ">TOTAL ITEM COUNT : '.$total_no_employees.'</td>
                                        </tr>
                                        <tr> 
                                            <td style=" width:100% ; text-align:left ; font-size: 9 ; ">TOTAL AMOUNT : '.number_format($total_payroll_res->amount_total,2,'.',',').'</td>
                                        </tr>
                                        <tr><td></td></tr>
                                        <tr>
                                            <td style=" width:5%   ; text-align:left  ; font-size: 9 ;  "></td>
                                            <td style=" width:24%  ; text-align:left  ; font-size: 9 ; border-bottom:3px solid black; "></td>
                                            <td style=" width:9%   ; text-align:left  ; font-size: 9 ;  "></td>
                                            <td style=" width:24%  ; text-align:left  ; font-size: 9 ; border-bottom:3px solid black; "></td>
                                            <td style=" width:9%   ; text-align:left  ; font-size: 9 ;  "></td>
                                            <td style=" width:24%  ; text-align:left  ; font-size: 9 ; border-bottom:3px solid black; "></td>
                                            <td style=" width:5%   ; text-align:left  ; font-size: 9 ;  "></td>
                                        </tr>
                                        <tr>
                                            <td style=" width:5%   ; text-align:left  ; font-size: 9 ;  "></td>
                                            <td style=" width:24%  ; text-align:left  ; font-size: 9 ;  ">Prepared By</td>
                                            <td style=" width:9%   ; text-align:left  ; font-size: 9 ;  "></td>
                                            <td style=" width:24%  ; text-align:left  ; font-size: 9 ;  ">Checked By</td>
                                            <td style=" width:9%   ; text-align:left  ; font-size: 9 ;  "></td>
                                            <td style=" width:24%  ; text-align:left  ; font-size: 9 ;  ">Approved By</td>
                                            <td style=" width:5%   ; text-align:left  ; font-size: 9 ;  "></td>
                                        </tr>
                                    </table>';
                    
                        break;
                }
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

    function export_to_disk( $company_id, $employee_id, $payroll_date, $posting_date, $title, $tran_type, $bank, $file_name, $paycode_id ){

        switch ($tran_type) {
            case 0:
                $transaction = "payroll_current_transaction";
                $on_hold = " AND p.on_hold != 1 ";
                break;
            
            case 1:
                $transaction = "payroll_closed_transaction";
                break;
        }

        $bank_qry = $this->db->query('SELECT * FROM '.$this->db->dbprefix('bank').' WHERE bank_id = "'.$bank.'"')->row();
        $bank_code_numeric = $bank_qry->bank_code_numeric;
        $batch_no = $bank_qry->batch_no;
        $branch_code = $bank_qry->branch_code;
        $account_no = str_replace('-','',$bank_qry->account_no);
        $ceiling_amount = str_replace('.','',$bank_qry->ceiling_amount);

        if(empty($file_name)) {
            $filename = $bank_code_numeric;
        }

        else{
            $filename = $file_name;
        }
        
        if(!empty($company_id)){

            $company = " AND company_id IN ($company_id)";
        }
        if(!empty($employee_id)){
            
            $employee = " AND p.employee_id IN ($employee_id)";   
        }

        if(!empty($paycode_id)){
            $pay_code = 'AND p.paycode_id = '.$paycode_id;
        }

        switch ($bank) {
            //ALLIED
            case '1':
                break;
            //BDO
            case '2':
                $qry = "SELECT e.bank_acct as 'ACCOUNT NUMBER', p.amount as 'AMOUNT'
                    FROM {$this->db->dbprefix}$transaction p
                    LEFT JOIN {$this->db->dbprefix}employee_payroll e ON e.employee_id = p.employee_id
                    LEFT JOIN {$this->db->dbprefix}user u ON u.employee_id = p.employee_id
                    WHERE payroll_date = '{$payroll_date}' AND transaction_code = 'NETPAY' $on_hold AND u.inactive != 1
                        AND e.bank_id = $bank AND e.payment_type_id = 1 $employee $company $pay_code";
                $res = $this->db->query($qry);

                $query = $res;
                $fields = $res->list_fields();

                //$export = $this->_export;
                $this->load->library('PHPExcel');       
                $this->load->library('PHPExcel/IOFactory');

                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setTitle("Payroll Crediting Advice")
                            ->setDescription("Payroll Crediting Advice");
                               
                // Assign cell values
                $objPHPExcel->setActiveSheetIndex(0);
                $activeSheet = $objPHPExcel->getActiveSheet();

                //header
                $alphabet  = range('A','Z');
                $alpha_ctr = 0;
                $sub_ctr   = 0;

                //Default column width
                $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);                 
                $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);                 
                $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);  
                $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);  
                               
                //Initialize style
                $styleArray = array(
                    'font' => array(
                        'bold' => true,
                    ),
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    )
                );

                foreach ($fields as $field) {
                    $xcoor = $alphabet[$alpha_ctr];

                    $activeSheet->setCellValueExplicit($xcoor . '4', $field, PHPExcel_Cell_DataType::TYPE_STRING);

                    $objPHPExcel->getActiveSheet()->getStyle($xcoor . '4')->applyFromArray($styleArray);
                    
                    $alpha_ctr++;
                }

                for($ctr=1; $ctr<4; $ctr++){

                    $objPHPExcel->getActiveSheet()->mergeCells($alphabet[0].$ctr.':'.$alphabet[$alpha_ctr - 1].$ctr);

                }

                $activeSheet->getHeaderFooter()->setOddFooter('&L&BPrinted By:'.$userinfo->firstname.' '.$userinfo->lastname.' &RPage &P of &N');

                $activeSheet->setCellValueExplicit('A1', 'BDO Payroll Crediting Advice', PHPExcel_Cell_DataType::TYPE_STRING); 
                $activeSheet->setCellValueExplicit('A2', date('F d,Y',strtotime($payroll_date)), PHPExcel_Cell_DataType::TYPE_STRING); 

                $objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArray);
                $objPHPExcel->getActiveSheet()->getStyle('A2')->applyFromArray($styleArray);
                $objPHPExcel->getActiveSheet()->getStyle('A3')->applyFromArray($styleArray);

                // contents.
                $line = 5;
                foreach ($query->result() as $row) {
                    $sub_ctr   = 0;         
                    $alpha_ctr = 0;

                    foreach ($fields as $field) {
                        if ($alpha_ctr >= count($alphabet)) {
                            $alpha_ctr = 0;
                            $sub_ctr++;
                        }

                        if ($sub_ctr > 0) {
                            $xcoor = $alphabet[$sub_ctr - 1] . $alphabet[$alpha_ctr];
                        } 
                        else {
                            $xcoor = $alphabet[$alpha_ctr];
                        }

                        $objPHPExcel->getActiveSheet()->setCellValueExplicit($xcoor . $line, $row->{$field}, PHPExcel_Cell_DataType::TYPE_STRING); 

                        $alpha_ctr++;                   
                    }
                    $line++;
                }   

                // Save it as an excel 2003 file
                $objWriter = IOFactory::createWriter($objPHPExcel, 'Excel5');

                header('Pragma: public');
                header('Expires: 0');
                header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                header('Content-Type: application/force-download');
                header('Content-Type: application/octet-stream');
                header('Content-Type: application/download');
                header('Content-Disposition: attachment;filename='.date('Y-m-d',strtotime($payroll_date)).'-BDO_CREDITING'.'.xls');
                header('Content-Transfer-Encoding: binary');
                
                $objWriter->save('php://output');   
                break;

            //BPI
            case '3':
                $total_payroll_qry = "SELECT sum(bank_acct) AS bank_total, sum(amount) AS amount_total, 
                    sum( ( (RIGHT(LEFT(bank_acct,6),2)*amount) + (RIGHT(LEFT(bank_acct,8),2)*amount) + (RIGHT(LEFT(bank_acct,10),2)*amount) ) )AS hash_total
                    FROM {$this->db->dbprefix}$transaction p
                    LEFT JOIN {$this->db->dbprefix}employee_payroll e ON e.employee_id = p.employee_id
                    LEFT JOIN {$this->db->dbprefix}user u ON u.employee_id = p.employee_id
                    WHERE payroll_date = '{$payroll_date}' AND transaction_code = 'NETPAY' $on_hold
                        AND e.bank_id = $bank  AND e.payment_type_id = 1 $employee $company $pay_code ";

                $total_payroll_res = $this->db->query($total_payroll_qry)->row();

                $amount_total = str_replace('.','',$total_payroll_res->amount_total);
                $bank_total = str_replace('.','',$total_payroll_res->bank_total);
                $hash_total = str_replace('.','',$total_payroll_res->hash_total);

                $payroll_qry = "SELECT bank_acct, amount, 
                            ( (RIGHT(LEFT(bank_acct,6),2)*amount) + (RIGHT(LEFT(bank_acct,8),2)*amount) + (RIGHT(LEFT(bank_acct,10),2)*amount) ) AS hash
                            FROM {$this->db->dbprefix}$transaction p
                            LEFT JOIN {$this->db->dbprefix}employee_payroll e ON e.employee_id = p.employee_id
                            LEFT JOIN {$this->db->dbprefix}user u ON u.employee_id = p.employee_id
                            WHERE payroll_date = '{$payroll_date}' AND transaction_code = 'NETPAY' $on_hold
                                AND e.bank_id = $bank  AND e.payment_type_id = 1 $employee $company  $pay_code ";
                $payroll_res = $this->db->query($payroll_qry);

                $File = $filename; 
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
                
                $Data_header .= 'H'.str_pad($bank_code_numeric,5,"0",STR_PAD_LEFT).date('mdy',strtotime($posting_date)).str_pad($batch_no,2,"0",STR_PAD_LEFT).'1'.str_pad($account_no,10,"0",STR_PAD_LEFT).substr($account_no, 0, -7).str_pad($ceiling_amount,12,"0",STR_PAD_LEFT).str_pad($amount_total,12,"0",STR_PAD_LEFT).'1'."\r\n";
                
                fwrite($Handle, $Data_header); 
                

                $total_line = 0;
                foreach ($payroll_res->result() as $key => $value) {

                    $Data = "D".str_pad($bank_code_numeric,5,"0",STR_PAD_LEFT).date('mdy',strtotime($posting_date)).str_pad($batch_no,2,"0",STR_PAD_LEFT).'3'.str_pad($value->bank_acct,10,"0",STR_PAD_LEFT).str_pad(str_replace('.','',$value->amount),12,"0",STR_PAD_LEFT).str_pad(str_replace('.', '', number_format($value->hash,2,'.','')),12,"0",STR_PAD_LEFT)."\r\n"; 
                    fwrite($Handle, $Data); 

                    $total_line++;
                }
                                       
                $Data_footer = "T".str_pad($bank_code_numeric,5,"0",STR_PAD_LEFT).date('mdy',strtotime($posting_date)).str_pad($batch_no,2,"0",STR_PAD_LEFT).'2'.str_pad($account_no,10,"0",STR_PAD_LEFT).str_pad($bank_total,15,"0",STR_PAD_LEFT).str_pad($amount_total,15,"0",STR_PAD_LEFT).str_pad($hash_total,18,"0",STR_PAD_LEFT).str_pad($total_line,5,"0",STR_PAD_LEFT)."\r\n";
                fwrite($Handle, $Data_footer); 
                
                fclose($Handle); 
                readfile($File);
                exit();
                break;
            //METROBANK
            case '4':
                $File = $filename; 
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
                
                foreach ($payroll_res->result() as $key => $value) {

                    $Data = "2".str_pad($branch_code,3,"0",STR_PAD_LEFT)."26"."001".str_pad($branch_code,3,"0",STR_PAD_LEFT)."0000000".str_pad($company->company,40," ",STR_PAD_LEFT).str_pad($value->bank_account_no,10,"0",STR_PAD_LEFT).str_pad(str_replace('.','',$value->amount),15,"0",STR_PAD_LEFT)."9".str_pad($bank_code_numeric,5,"0",STR_PAD_LEFT).date('Ymd',strtotime($payroll_date))."\r\n"; 
                    fwrite($Handle, $Data); 

                    $total_line++;
                }
                
                fclose($Handle); 
                readfile($File);
                exit();
                break;
            //UBP
            case '5':

                break;
            //UCPB
            case '6':
                $total_payroll_qry = "SELECT sum(amount) AS amount_total
                    FROM {$this->db->dbprefix}$transaction p
                    LEFT JOIN {$this->db->dbprefix}employee_payroll e ON e.employee_id = p.employee_id
                    LEFT JOIN {$this->db->dbprefix}user u ON u.employee_id = p.employee_id
                    WHERE payroll_date = '{$payroll_date}' AND transaction_code = 'NETPAY' $on_hold AND u.inactive != 1
                        AND e.bank_id = $bank AND e.payment_type_id = 1 $employee $company $pay_code";

                $total_payroll_res = $this->db->query($total_payroll_qry)->row();

                $amount_total = str_replace('.','',$total_payroll_res->amount_total);

                $payroll_qry = "SELECT bank_acct, amount
                            FROM {$this->db->dbprefix}$transaction p
                            LEFT JOIN {$this->db->dbprefix}employee_payroll e ON e.employee_id = p.employee_id
                            LEFT JOIN {$this->db->dbprefix}user u ON u.employee_id = p.employee_id
                            WHERE payroll_date = '{$payroll_date}' AND transaction_code = 'NETPAY' $on_hold AND u.inactive != 1
                                AND e.bank_id = $bank  AND e.payment_type_id = 1 $employee $company $pay_code";
                $payroll_res = $this->db->query($payroll_qry);

                $File = $filename; 
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
                
                $Data_header .= str_pad($account_no,12,"0",STR_PAD_LEFT).str_pad($batch_no,12,"0",STR_PAD_LEFT).str_pad($amount_total,15,"0",STR_PAD_LEFT).date('Ymd',strtotime($posting_date))."\r\n";
                
                fwrite($Handle, $Data_header); 
                
                $total_line = 0;
                foreach ($payroll_res->result() as $key => $value) {

                    $Data = str_pad($account_no,12,"0",STR_PAD_LEFT).str_pad(str_replace('-', '', $value->bank_acct),12,"0",STR_PAD_LEFT).str_pad(str_replace('.','',$value->amount),15,"0",STR_PAD_LEFT).date('Ymd',strtotime($posting_date))."\r\n"; 
                    fwrite($Handle, $Data); 

                    $total_line++;
                }
              
                fclose($Handle); 
                readfile($File);
                exit();
                break;
            //SECURITY BANK
            case '7':
                $total_payroll_qry = "SELECT sum(amount) AS amount_total
                    FROM {$this->db->dbprefix}$transaction p
                    LEFT JOIN {$this->db->dbprefix}employee_payroll e ON e.employee_id = p.employee_id
                    LEFT JOIN {$this->db->dbprefix}user u ON u.employee_id = p.employee_id
                    WHERE payroll_date = '{$payroll_date}' AND transaction_code = 'NETPAY' $on_hold AND u.inactive != 1
                        AND e.bank_id = $bank AND e.payment_type_id = 1 $employee $company $pay_code";

                $total_payroll_res = $this->db->query($total_payroll_qry)->row();

                $amount_total = str_replace('.','',$total_payroll_res->amount_total);

                $payroll_qry = "SELECT bank_acct, amount
                            FROM {$this->db->dbprefix}$transaction p
                            LEFT JOIN {$this->db->dbprefix}employee_payroll e ON e.employee_id = p.employee_id
                            LEFT JOIN {$this->db->dbprefix}user u ON u.employee_id = p.employee_id
                            WHERE payroll_date = '{$payroll_date}' AND transaction_code = 'NETPAY' $on_hold AND u.inactive != 1
                                AND e.bank_id = $bank AND e.payment_type_id = 1 $employee $company $pay_code ";
                $payroll_res = $this->db->query($payroll_qry);

                $File = $filename; 
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
                
                $Data_header .= 'PHP01'.str_pad($account_no,13,"0",STR_PAD_LEFT).date('mdy',strtotime($posting_date)).'200'.str_pad($amount_total,13,"0",STR_PAD_LEFT)."\r\n";
                
                fwrite($Handle, $Data_header); 
                
                $total_line = 0;
                foreach ($payroll_res->result() as $key => $value) {

                    $Data = 'PHP10'.str_pad(str_replace('-', '', $value->bank_acct),13,"0",STR_PAD_LEFT).str_pad($bank_code_numeric,4,"0",STR_PAD_LEFT).'00700'.str_pad(str_replace('.','',$value->amount),13,"0",STR_PAD_LEFT)."\r\n"; 
                    fwrite($Handle, $Data); 

                    $total_line++;
                }
              
                fclose($Handle); 
                readfile($File);
                exit();
                break;
        }
    }

    private function convert_number_to_words($number) {

        $hyphen      = '-';
        $conjunction = ' ';
        $separator   = ', ';
        $negative    = 'negative ';
        $decimal     = ' and ';
        $dictionary  = array(
            0                   => 'ZERO',
            1                   => 'ONE',
            2                   => 'TWO',
            3                   => 'THREE',
            4                   => 'FOUR',
            5                   => 'FIVE',
            6                   => 'SIX',
            7                   => 'SEVEN',
            8                   => 'EIGHT',
            9                   => 'NINE',
            10                  => 'TEN',
            11                  => 'ELEVEN',
            12                  => 'TWELVE',
            13                  => 'THIRTEEN',
            14                  => 'FOURTEEN',
            15                  => 'FIFTEEN',
            16                  => 'SIXTEEN',
            17                  => 'SEVENTEEN',
            18                  => 'EIGHTEEN',
            19                  => 'NINETEEN',
            20                  => 'TWENTY',
            30                  => 'THIRTY',
            40                  => 'FOURTY',
            50                  => 'FIFTY',
            60                  => 'SIXTY',
            70                  => 'SEVENTY',
            80                  => 'EIGHTY',
            90                  => 'NINETY',
            100                 => 'HUNDRED',
            1000                => 'THOUSAND',
            1000000             => 'MILLION',
            1000000000          => 'BILLION',
            1000000000000       => 'TRILLION',
            1000000000000000    => 'QUADRILLION',
            1000000000000000000 => 'QUINTILLION'
        );

        if (!is_numeric($number)) {
            return false;
        }

        if (($number >= 0 && (int) $number < 0) || (int) $number < 0 - PHP_INT_MAX) {
            // overflow
            trigger_error(
                'convert_number_to_words only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX,
                E_USER_WARNING
            );
            return false;
        }

        if ($number < 0) {
            return $negative . $this->convert_number_to_words(abs($number));
        }

        $string = $fraction = null;

        if (strpos($number, '.') !== false) {
            list($number, $fraction) = explode('.', $number);
        }

        switch (true) {
            case $number < 21:
                $string = $dictionary[$number];
                break;
            case $number < 100:
                $tens   = ((int) ($number / 10)) * 10;
                $units  = $number % 10;
                $string = $dictionary[$tens];
                if ($units) {
                    $string .= $hyphen . $dictionary[$units];
                }
                break;
            case $number < 1000:
                $hundreds  = $number / 100;
                $remainder = $number % 100;
                $string = $dictionary[$hundreds] . ' ' . $dictionary[100];
                if ($remainder) {
                    $string .= $conjunction . $this->convert_number_to_words($remainder);
                }
                break;
            default:
                $baseUnit = pow(1000, floor(log($number, 1000)));
                $numBaseUnits = (int) ($number / $baseUnit);
                $remainder = $number % $baseUnit;
                $string = $this->convert_number_to_words($numBaseUnits) . ' ' . $dictionary[$baseUnit];
                if ($remainder) {
                    $string .= $remainder < 100 ? $conjunction : $separator;
                    $string .= $this->convert_number_to_words($remainder);
                }
                break;
        }

        if (null !== $fraction && is_numeric($fraction)) {
            $string .= $decimal;
            $words = array();
            foreach (str_split((string) $fraction) as $number) {
                $words[] = $number;
            }
            $dec .= implode('',$words);
            $string .= $dec.'/100';
        }

        return $string;
    }

    function export_deductions($company_id, $employee_id, $payroll_date, $posting_date, $title, $tran_type, $bank, $paycode_id ){

        switch ($tran_type) {
            case 0:
                $transaction = "payroll_current_transaction";
                $on_hold = 'AND t.on_hold != 1';
                break;
            
            case 1:
                $transaction = "payroll_closed_transaction";
                break;
        }

        $bank_qry = $this->db->query('SELECT * FROM '.$this->db->dbprefix('bank').' WHERE bank_id = "'.$bank.'"')->row();
        $bank_code_numeric = $bank_qry->bank_code_numeric;
        $batch_no = $bank_qry->batch_no;
        $branch_code = $bank_qry->branch_code;
        $account_no = str_replace('-','',$bank_qry->account_no);
        $ceiling_amount = str_replace('.','',$bank_qry->ceiling_amount);

        if(empty($file_name)) {
            $filename = $bank_code_numeric;
        }

        else{
            $filename = $file_name;
        }
        
        if(!empty($company_id)){

            $company = " AND company_id IN ($company_id)";
        }
        if(!empty($employee_id)){
            
            $employee = " AND p.employee_id IN ($employee_id)";   
        }

        if(!empty($paycode_id)){
            $pay_code = 'AND t.paycode_id = '.$paycode_id;
        }

        $qry = "SELECT t.transaction_code as 'TRAN CODE', id_number as 'ID NUMBER', CONCAT(lastname, ', ', firstname) as 'EMP NAME', amount as 'AMOUNT',paycode
                FROM {$this->db->dbprefix}$transaction t 
                LEFT JOIN hr_employee e ON e.employee_id = t.employee_id
                LEFT JOIN hr_user u ON u.employee_id = t.employee_id
                LEFT JOIN hr_payroll_transaction pt ON pt.transaction_id = t.transaction_id 
                LEFT JOIN hr_employee_payroll p ON p.employee_id = t.employee_id
                LEFT JOIN hr_payroll_paycode c ON c.paycode_id = p.paycode_id
                WHERE t.transaction_type_id = 3 AND transaction_class_id != 10 AND t.transaction_code NOT IN ('WHTAX')
                    AND t.transaction_id NOT IN (SELECT amortization_transid FROM hr_payroll_loan) $on_hold AND u.inactive != 1 $pay_code $employee $company
                ORDER BY t.transaction_code"; 

        $res = $this->db->query($qry);

        $query = $res;
        $fields = $res->list_fields();

        //$export = $this->_export;
        $this->load->library('PHPExcel');       
        $this->load->library('PHPExcel/IOFactory');

        $objPHPExcel = new PHPExcel();

        $objPHPExcel->getProperties()->setTitle("Payroll Crediting Advice")
                    ->setDescription("Payroll Crediting Advice");
                       
        // Assign cell values
        $objPHPExcel->setActiveSheetIndex(0);
        $activeSheet = $objPHPExcel->getActiveSheet();

        //header
        $alphabet  = range('A','Z');
        $alpha_ctr = 0;
        $sub_ctr   = 0;

        //Default column width
        $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);                 
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);                 
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true); 
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);  
                       
        //Initialize style
        $styleArray = array(
            'font' => array(
                'bold' => true,
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            )
        );

        foreach ($fields as $field) {
            $xcoor = $alphabet[$alpha_ctr];

            $activeSheet->setCellValueExplicit($xcoor . '5', $field, PHPExcel_Cell_DataType::TYPE_STRING);

            $objPHPExcel->getActiveSheet()->getStyle($xcoor . '5')->applyFromArray($styleArray);
            
            $alpha_ctr++;
        }

        for($ctr=1; $ctr<5; $ctr++){

            $objPHPExcel->getActiveSheet()->mergeCells($alphabet[0].$ctr.':'.$alphabet[$alpha_ctr - 1].$ctr);

        }

        $activeSheet->getHeaderFooter()->setOddFooter('&L&BPrinted By:'.$userinfo->firstname.' '.$userinfo->lastname.' &RPage &P of &N');

        $activeSheet->setCellValueExplicit('A1', 'PAYROLL REGISTER', PHPExcel_Cell_DataType::TYPE_STRING); 
        $activeSheet->setCellValueExplicit('A2', date('F d,Y',strtotime($payroll_date)), PHPExcel_Cell_DataType::TYPE_STRING); 

        $objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArray);
        $objPHPExcel->getActiveSheet()->getStyle('A2')->applyFromArray($styleArray);
        $objPHPExcel->getActiveSheet()->getStyle('A3')->applyFromArray($styleArray);

        // contents.
        $line = 5;
        foreach ($query->result() as $row) {
            $sub_ctr   = 0;         
            $alpha_ctr = 0;

            foreach ($fields as $field) {
                if ($alpha_ctr >= count($alphabet)) {
                    $alpha_ctr = 0;
                    $sub_ctr++;
                }

                if ($sub_ctr > 0) {
                    $xcoor = $alphabet[$sub_ctr - 1] . $alphabet[$alpha_ctr];
                } 
                else {
                    $xcoor = $alphabet[$alpha_ctr];
                }

                $objPHPExcel->getActiveSheet()->setCellValueExplicit($xcoor . $line, $row->{$field}, PHPExcel_Cell_DataType::TYPE_STRING); 

                $alpha_ctr++;                   
            }
            $line++;
        }   

        // Save it as an excel 2003 file
        $objWriter = IOFactory::createWriter($objPHPExcel, 'Excel5');

        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Content-Type: application/force-download');
        header('Content-Type: application/octet-stream');
        header('Content-Type: application/download');
        header('Content-Disposition: attachment;filename='.date('Y-m-d',strtotime($payroll_date)).'-BDO_CREDITING'.'.xls');
        header('Content-Transfer-Encoding: binary');
        
        $objWriter->save('php://output');   
    }

    function export_inc_non_tax($company_id, $employee_id, $payroll_date, $posting_date, $title, $tran_type, $bank, $paycode_id ){


        switch ($tran_type) {
            case 0:
                $transaction = "payroll_current_transaction";
                $on_hold = 'AND t.on_hold != 1';
                break;
            
            case 1:
                $transaction = "payroll_closed_transaction";
                break;
        }

        $bank_qry = $this->db->query('SELECT * FROM '.$this->db->dbprefix('bank').' WHERE bank_id = "'.$bank.'"')->row();
        $bank_code_numeric = $bank_qry->bank_code_numeric;
        $batch_no = $bank_qry->batch_no;
        $branch_code = $bank_qry->branch_code;
        $account_no = str_replace('-','',$bank_qry->account_no);
        $ceiling_amount = str_replace('.','',$bank_qry->ceiling_amount);

        if(empty($file_name)) {
            $filename = $bank_code_numeric;
        }

        else{
            $filename = $file_name;
        }
        
        if(!empty($company_id)){

            $company = " AND company_id IN ($company_id)";
        }
        if(!empty($employee_id)){
            
            $employee = " AND p.employee_id IN ($employee_id)";   
        }

        if(!empty($paycode_id)){
            $pay_code = 'AND t.paycode_id = '.$paycode_id;
        }

        $qry = "SELECT t.transaction_code as 'TRAN CODE', id_number as 'ID NUMBER', CONCAT(lastname, ', ', firstname) as 'EMP NAME', amount as 'AMOUNT',paycode
                FROM {$this->db->dbprefix}$transaction t 
                LEFT JOIN hr_employee e ON e.employee_id = t.employee_id
                LEFT JOIN hr_user u ON u.employee_id = t.employee_id
                LEFT JOIN hr_payroll_transaction pt ON pt.transaction_id = t.transaction_id 
                LEFT JOIN hr_employee_payroll p ON p.employee_id = t.employee_id
                LEFT JOIN hr_payroll_paycode c ON c.paycode_id = p.paycode_id
                WHERE t.transaction_type_id = 2  $on_hold AND u.inactive != 1 $pay_code $employee $company
                ORDER BY t.transaction_code";

        $res = $this->db->query($qry);

        $query = $res;
        $fields = $res->list_fields();

        //$export = $this->_export;
        $this->load->library('PHPExcel');       
        $this->load->library('PHPExcel/IOFactory');

        $objPHPExcel = new PHPExcel();

        $objPHPExcel->getProperties()->setTitle("Payroll Crediting Advice")
                    ->setDescription("Payroll Crediting Advice");
                       
        // Assign cell values
        $objPHPExcel->setActiveSheetIndex(0);
        $activeSheet = $objPHPExcel->getActiveSheet();

        //header
        $alphabet  = range('A','Z');
        $alpha_ctr = 0;
        $sub_ctr   = 0;

        //Default column width
        $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);                 
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);                 
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true); 
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);  
                       
        //Initialize style
        $styleArray = array(
            'font' => array(
                'bold' => true,
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            )
        );

        foreach ($fields as $field) {
            $xcoor = $alphabet[$alpha_ctr];

            $activeSheet->setCellValueExplicit($xcoor . '5', $field, PHPExcel_Cell_DataType::TYPE_STRING);

            $objPHPExcel->getActiveSheet()->getStyle($xcoor . '5')->applyFromArray($styleArray);
            
            $alpha_ctr++;
        }

        for($ctr=1; $ctr<5; $ctr++){

            $objPHPExcel->getActiveSheet()->mergeCells($alphabet[0].$ctr.':'.$alphabet[$alpha_ctr - 1].$ctr);

        }

        $activeSheet->getHeaderFooter()->setOddFooter('&L&BPrinted By:'.$userinfo->firstname.' '.$userinfo->lastname.' &RPage &P of &N');

        $activeSheet->setCellValueExplicit('A1', 'PAYROLL REGISTER', PHPExcel_Cell_DataType::TYPE_STRING); 
        $activeSheet->setCellValueExplicit('A2', date('F d,Y',strtotime($payroll_date)), PHPExcel_Cell_DataType::TYPE_STRING); 

        $objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArray);
        $objPHPExcel->getActiveSheet()->getStyle('A2')->applyFromArray($styleArray);
        $objPHPExcel->getActiveSheet()->getStyle('A3')->applyFromArray($styleArray);

        // contents.
        $line = 5;
        foreach ($query->result() as $row) {
            $sub_ctr   = 0;         
            $alpha_ctr = 0;

            foreach ($fields as $field) {
                if ($alpha_ctr >= count($alphabet)) {
                    $alpha_ctr = 0;
                    $sub_ctr++;
                }

                if ($sub_ctr > 0) {
                    $xcoor = $alphabet[$sub_ctr - 1] . $alphabet[$alpha_ctr];
                } 
                else {
                    $xcoor = $alphabet[$alpha_ctr];
                }

                $objPHPExcel->getActiveSheet()->setCellValueExplicit($xcoor . $line, $row->{$field}, PHPExcel_Cell_DataType::TYPE_STRING); 

                $alpha_ctr++;                   
            }
            $line++;
        }   

        // Save it as an excel 2003 file
        $objWriter = IOFactory::createWriter($objPHPExcel, 'Excel5');

        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Content-Type: application/force-download');
        header('Content-Type: application/octet-stream');
        header('Content-Type: application/download');
        header('Content-Disposition: attachment;filename='.date('Y-m-d',strtotime($payroll_date)).'-BDO_CREDITING'.'.xls');
        header('Content-Transfer-Encoding: binary');
        
        $objWriter->save('php://output');   
    }

    function export_income_tax($company_id, $employee_id, $payroll_date, $posting_date, $title, $tran_type, $bank, $paycode_id ){



        switch ($tran_type) {
            case 0:
                $transaction = "payroll_current_transaction";
                $on_hold = 'AND t.on_hold != 1';
                break;
            
            case 1:
                $transaction = "payroll_closed_transaction";
                break;
        }

        $bank_qry = $this->db->query('SELECT * FROM '.$this->db->dbprefix('bank').' WHERE bank_id = "'.$bank.'"')->row();
        $bank_code_numeric = $bank_qry->bank_code_numeric;
        $batch_no = $bank_qry->batch_no;
        $branch_code = $bank_qry->branch_code;
        $account_no = str_replace('-','',$bank_qry->account_no);
        $ceiling_amount = str_replace('.','',$bank_qry->ceiling_amount);

        if(empty($file_name)) {
            $filename = $bank_code_numeric;
        }

        else{
            $filename = $file_name;
        }
        
        if(!empty($company_id)){

            $company = " AND company_id IN ($company_id)";
        }
        if(!empty($employee_id)){
            
            $employee = " AND p.employee_id IN ($employee_id)";   
        }

        if(!empty($paycode_id)){
            $pay_code = 'AND t.paycode_id = '.$paycode_id;
        }

        $qry = "SELECT t.transaction_code as 'TRAN CODE', id_number as 'ID NUMBER', CONCAT(lastname, ', ', firstname) as 'EMP NAME', amount as 'AMOUNT',paycode
                FROM {$this->db->dbprefix}$transaction t 
                LEFT JOIN hr_employee e ON e.employee_id = t.employee_id
                LEFT JOIN hr_user u ON u.employee_id = t.employee_id
                LEFT JOIN hr_payroll_transaction pt ON pt.transaction_id = t.transaction_id 
                LEFT JOIN hr_employee_payroll p ON p.employee_id = t.employee_id
                LEFT JOIN hr_payroll_paycode c ON c.paycode_id = p.paycode_id
                WHERE t.transaction_type_id = 1 AND transaction_class_id != 10 AND t.transaction_code NOT IN ('SALARY') $on_hold AND u.inactive != 1 $pay_code $employee $company
                ORDER BY t.transaction_code";

        $res = $this->db->query($qry);

        $query = $res;
        $fields = $res->list_fields();

        //$export = $this->_export;
        $this->load->library('PHPExcel');       
        $this->load->library('PHPExcel/IOFactory');

        $objPHPExcel = new PHPExcel();

        $objPHPExcel->getProperties()->setTitle("Payroll Crediting Advice")
                    ->setDescription("Payroll Crediting Advice");
                       
        // Assign cell values
        $objPHPExcel->setActiveSheetIndex(0);
        $activeSheet = $objPHPExcel->getActiveSheet();

        //header
        $alphabet  = range('A','Z');
        $alpha_ctr = 0;
        $sub_ctr   = 0;

        //Default column width
        $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);                 
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);                 
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true); 
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);  
                       
        //Initialize style
        $styleArray = array(
            'font' => array(
                'bold' => true,
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            )
        );

        foreach ($fields as $field) {
            $xcoor = $alphabet[$alpha_ctr];

            $activeSheet->setCellValueExplicit($xcoor . '5', $field, PHPExcel_Cell_DataType::TYPE_STRING);

            $objPHPExcel->getActiveSheet()->getStyle($xcoor . '5')->applyFromArray($styleArray);
            
            $alpha_ctr++;
        }

        for($ctr=1; $ctr<5; $ctr++){

            $objPHPExcel->getActiveSheet()->mergeCells($alphabet[0].$ctr.':'.$alphabet[$alpha_ctr - 1].$ctr);

        }

        $activeSheet->getHeaderFooter()->setOddFooter('&L&BPrinted By:'.$userinfo->firstname.' '.$userinfo->lastname.' &RPage &P of &N');

        $activeSheet->setCellValueExplicit('A1', 'PAYROLL REGISTER', PHPExcel_Cell_DataType::TYPE_STRING); 
        $activeSheet->setCellValueExplicit('A2', date('F d,Y',strtotime($payroll_date)), PHPExcel_Cell_DataType::TYPE_STRING); 

        $objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArray);
        $objPHPExcel->getActiveSheet()->getStyle('A2')->applyFromArray($styleArray);
        $objPHPExcel->getActiveSheet()->getStyle('A3')->applyFromArray($styleArray);

        // contents.
        $line = 5;
        foreach ($query->result() as $row) {
            $sub_ctr   = 0;         
            $alpha_ctr = 0;

            foreach ($fields as $field) {
                if ($alpha_ctr >= count($alphabet)) {
                    $alpha_ctr = 0;
                    $sub_ctr++;
                }

                if ($sub_ctr > 0) {
                    $xcoor = $alphabet[$sub_ctr - 1] . $alphabet[$alpha_ctr];
                } 
                else {
                    $xcoor = $alphabet[$alpha_ctr];
                }

                $objPHPExcel->getActiveSheet()->setCellValueExplicit($xcoor . $line, $row->{$field}, PHPExcel_Cell_DataType::TYPE_STRING); 

                $alpha_ctr++;                   
            }
            $line++;
        }   

        // Save it as an excel 2003 file
        $objWriter = IOFactory::createWriter($objPHPExcel, 'Excel5');

        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Content-Type: application/force-download');
        header('Content-Type: application/octet-stream');
        header('Content-Type: application/download');
        header('Content-Disposition: attachment;filename='.date('Y-m-d',strtotime($payroll_date)).'-BDO_CREDITING'.'.xls');
        header('Content-Transfer-Encoding: binary');
        
        $objWriter->save('php://output');   
    }

    function export_loans($company_id, $employee_id, $payroll_date, $posting_date, $title, $tran_type, $bank, $paycode_id ){


        switch ($tran_type) {
            case 0:
                $transaction = "payroll_current_transaction";
                $on_hold = 'AND t.on_hold != 1';
                break;
            
            case 1:
                $transaction = "payroll_closed_transaction";
                break;
        }

        $bank_qry = $this->db->query('SELECT * FROM '.$this->db->dbprefix('bank').' WHERE bank_id = "'.$bank.'"')->row();
        $bank_type = $bank_qry->bank_type;
        $bank_code_numeric = $bank_qry->bank_code_numeric;
        $batch_no = $bank_qry->batch_no;
        $branch_code = $bank_qry->branch_code;
        $account_no = str_replace('-','',$bank_qry->account_no);
        $ceiling_amount = str_replace('.','',$bank_qry->ceiling_amount);

        if(empty($file_name)) {
            $filename = $bank_code_numeric;
        }

        else{
            $filename = $file_name;
        }
        
        if(!empty($company_id)){

            $company = " AND company_id IN ($company_id)";
        }
        if(!empty($employee_id)){
            
            $employee = " AND p.employee_id IN ($employee_id)";   
        }

        if(!empty($paycode_id)){
            $pay_code = 'AND t.paycode_id = '.$paycode_id;
        }

        $qry = "SELECT t.transaction_code as 'TRAN CODE', id_number as 'ID NUMBER', CONCAT(lastname, ', ', firstname) as 'EMP NAME', amount as 'AMOUNT',paycode
                FROM {$this->db->dbprefix}$transaction t 
                LEFT JOIN hr_employee e ON e.employee_id = t.employee_id
                LEFT JOIN hr_user u ON u.employee_id = t.employee_id
                LEFT JOIN hr_payroll_transaction pt ON pt.transaction_id = t.transaction_id 
                LEFT JOIN hr_employee_payroll p ON p.employee_id = t.employee_id
                LEFT JOIN hr_payroll_paycode c ON c.paycode_id = p.paycode_id
                WHERE t.transaction_type_id = 3 AND transaction_class_id != 10 AND t.transaction_code NOT IN ('WHTAX')
                    AND t.transaction_id IN (SELECT amortization_transid FROM hr_payroll_loan) $on_hold AND u.inactive != 1 $pay_code $employee $company
                ORDER BY t.transaction_code";

        $res = $this->db->query($qry);

        $query = $res;
        $fields = $res->list_fields();

        //$export = $this->_export;
        $this->load->library('PHPExcel');       
        $this->load->library('PHPExcel/IOFactory');

        $objPHPExcel = new PHPExcel();

        $objPHPExcel->getProperties()->setTitle("Payroll Crediting Advice")
                    ->setDescription("Payroll Crediting Advice");
                       
        // Assign cell values
        $objPHPExcel->setActiveSheetIndex(0);
        $activeSheet = $objPHPExcel->getActiveSheet();

        //header
        $alphabet  = range('A','Z');
        $alpha_ctr = 0;
        $sub_ctr   = 0;

        //Default column width
        $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);                 
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);                 
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true); 
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);  
                       
        //Initialize style
        $styleArray = array(
            'font' => array(
                'bold' => true,
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            )
        );

        foreach ($fields as $field) {
            $xcoor = $alphabet[$alpha_ctr];

            $activeSheet->setCellValueExplicit($xcoor . '5', $field, PHPExcel_Cell_DataType::TYPE_STRING);

            $objPHPExcel->getActiveSheet()->getStyle($xcoor . '5')->applyFromArray($styleArray);
            
            $alpha_ctr++;
        }

        for($ctr=1; $ctr<5; $ctr++){

            $objPHPExcel->getActiveSheet()->mergeCells($alphabet[0].$ctr.':'.$alphabet[$alpha_ctr - 1].$ctr);

        }

        $activeSheet->getHeaderFooter()->setOddFooter('&L&BPrinted By:'.$userinfo->firstname.' '.$userinfo->lastname.' &RPage &P of &N');

        $activeSheet->setCellValueExplicit('A1', 'PAYROLL REGISTER', PHPExcel_Cell_DataType::TYPE_STRING); 
        $activeSheet->setCellValueExplicit('A2', date('F d,Y',strtotime($payroll_date)), PHPExcel_Cell_DataType::TYPE_STRING); 

        $objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArray);
        $objPHPExcel->getActiveSheet()->getStyle('A2')->applyFromArray($styleArray);
        $objPHPExcel->getActiveSheet()->getStyle('A3')->applyFromArray($styleArray);

        // contents.
        $line = 5;
        foreach ($query->result() as $row) {
            $sub_ctr   = 0;         
            $alpha_ctr = 0;

            foreach ($fields as $field) {
                if ($alpha_ctr >= count($alphabet)) {
                    $alpha_ctr = 0;
                    $sub_ctr++;
                }

                if ($sub_ctr > 0) {
                    $xcoor = $alphabet[$sub_ctr - 1] . $alphabet[$alpha_ctr];
                } 
                else {
                    $xcoor = $alphabet[$alpha_ctr];
                }

                $objPHPExcel->getActiveSheet()->setCellValueExplicit($xcoor . $line, $row->{$field}, PHPExcel_Cell_DataType::TYPE_STRING); 

                $alpha_ctr++;                   
            }
            $line++;
        }   

        // Save it as an excel 2003 file
        $objWriter = IOFactory::createWriter($objPHPExcel, 'Excel5');

        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Content-Type: application/force-download');
        header('Content-Type: application/octet-stream');
        header('Content-Type: application/download');
        header('Content-Disposition: attachment;filename='.date('Y-m-d',strtotime($payroll_date)).'-BDO_CREDITING'.'.xls');
        header('Content-Transfer-Encoding: binary');
        
        $objWriter->save('php://output');   
    }
}

/* End of file */
/* Location: system/application */
?>
