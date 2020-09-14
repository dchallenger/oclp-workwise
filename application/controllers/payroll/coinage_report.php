<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class coinage_report extends my_controller
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

        $cost_code = $_POST['cost_code'];

        $payroll_date = date("Y-m-d",strtotime($_POST['user_id']));  

        $this->load->library('pdf');
        $html = $this->export_coinage($cost_code, $paycode_id, $company_id, $employee_id, $payroll_date, $title);
        $this->pdf->writeHTML($html, true, false, true, false, '');
        $this->pdf->Output(ucwords(str_replace(" ","_",$title))."_" . date('dmYHis') . '.pdf', 'I');
        
    }
        
    function export_coinage($cost_code, $paycode_id, $company_id, $employee_id, $payroll_date, $title) {

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
            $pay_code = 'AND ct.paycode_id = '.$paycode_id;
        }

        if(!empty($cost_code)){
            $costcode = 'AND cc.cost_code_id = '.$cost_code;
        }

        $paycode = $this->db->query("select paycode from {$this->db->dbprefix}payroll_paycode where paycode_id = $paycode_id")->row();
        $count_emp = $this->db->query("SELECT ct.employee_id FROM {$this->db->dbprefix}user u
                                    LEFT JOIN {$this->db->dbprefix}employee_work_assignment w on u.employee_id = w.employee_id
                                    LEFT JOIN {$this->db->dbprefix}employee_payroll e ON u.employee_id = e.employee_id
                                    LEFT JOIN {$this->db->dbprefix}payroll_closed_transaction ct on u.employee_id = ct.employee_id
                                    LEFT JOIN {$this->db->dbprefix}cost_code_xxx cc on w.cost_code = cc.cost_code
                                    WHERE 1 AND payroll_date = '{$payroll_date}'  AND transaction_code = 'NETPAY' AND payment_type_id = 2 $costcode $company $employee $pay_code ")->num_rows();
        
        $proj_qry = $this->db->query("SELECT distinct w.cost_code FROM {$this->db->dbprefix}user u
                                    LEFT JOIN {$this->db->dbprefix}employee_work_assignment w on u.employee_id = w.employee_id
                                    LEFT JOIN {$this->db->dbprefix}employee_payroll e ON u.employee_id = e.employee_id
                                    LEFT JOIN {$this->db->dbprefix}payroll_closed_transaction ct on u.employee_id = ct.employee_id
                                    LEFT JOIN {$this->db->dbprefix}cost_code_xxx cc on w.cost_code = cc.cost_code
                                    WHERE 1 AND payroll_date = '{$payroll_date}'  AND transaction_code = 'NETPAY' AND payment_type_id = 2 $costcode $company $employee $pay_code 
                                    ORDER BY w.cost_code");

        // $count_emp = $this->db->query("SELECT ct.employee_id FROM {$this->db->dbprefix}user u
        //                             LEFT JOIN {$this->db->dbprefix}employee_work_assignment w on u.employee_id = w.employee_id
        //                             LEFT JOIN {$this->db->dbprefix}employee_payroll e ON u.employee_id = e.employee_id
        //                             LEFT JOIN {$this->db->dbprefix}payroll_current_transaction ct on u.employee_id = ct.employee_id and ct.on_hold = 0 and ct.deleted = 0
        //                             LEFT JOIN {$this->db->dbprefix}cost_code_xxx cc on w.cost_code = cc.cost_code
        //                             WHERE 1 AND payroll_date = '{$payroll_date}'  AND transaction_code = 'NETPAY' AND payment_type_id = 2 $costcode $company $employee $pay_code ")->num_rows();
        
        // $proj_qry = $this->db->query("SELECT distinct w.cost_code FROM {$this->db->dbprefix}user u
        //                             LEFT JOIN {$this->db->dbprefix}employee_work_assignment w on u.employee_id = w.employee_id
        //                             LEFT JOIN {$this->db->dbprefix}employee_payroll e ON u.employee_id = e.employee_id
        //                             LEFT JOIN {$this->db->dbprefix}payroll_current_transaction ct on u.employee_id = ct.employee_id and ct.on_hold = 0 and ct.deleted = 0
        //                             LEFT JOIN {$this->db->dbprefix}cost_code_xxx cc on w.cost_code = cc.cost_code
        //                             WHERE 1 AND payroll_date = '{$payroll_date}'  AND transaction_code = 'NETPAY' AND payment_type_id = 2 $costcode $company $employee $pay_code 
        //                             ORDER BY w.cost_code");
        
        $proj_cnt = $proj_qry->num_rows();
        $proj_record = $proj_qry->result();
        $emp_cnt = 1;
        if( $proj_cnt > 0 ){
            $cnt_page = 1;
            $cnt = 1;
            foreach ($proj_record as $key => $proj) {

                $qry = $this->db->query("SELECT u.employee_id, id_number,  lastname, firstname, middleinitial, aux, amount
                                    FROM {$this->db->dbprefix}user u
                                    LEFT JOIN {$this->db->dbprefix}employee_work_assignment w on u.employee_id = w.employee_id
                                    LEFT JOIN {$this->db->dbprefix}employee_payroll e ON u.employee_id = e.employee_id
                                    LEFT JOIN {$this->db->dbprefix}employee ee ON u.employee_id = ee.employee_id
                                    LEFT JOIN {$this->db->dbprefix}payroll_closed_transaction ct on u.employee_id = ct.employee_id
                                    WHERE w.cost_code = '{$proj->cost_code}'    AND payroll_date = '{$payroll_date}' AND transaction_code = 'NETPAY' AND payment_type_id = 2  $pay_code 
                                    ORDER BY lastname, firstname, middleinitial");

                // $qry = $this->db->query("SELECT u.employee_id, id_number,  lastname, firstname, middleinitial, aux, amount
                //                     FROM {$this->db->dbprefix}user u
                //                     LEFT JOIN {$this->db->dbprefix}employee_work_assignment w on u.employee_id = w.employee_id
                //                     LEFT JOIN {$this->db->dbprefix}employee_payroll e ON u.employee_id = e.employee_id
                //                     LEFT JOIN {$this->db->dbprefix}employee ee ON u.employee_id = ee.employee_id
                //                     LEFT JOIN {$this->db->dbprefix}payroll_current_transaction ct on u.employee_id = ct.employee_id and ct.on_hold = 0 and ct.deleted = 0
                //                     WHERE w.cost_code = '{$proj->cost_code}'    AND payroll_date = '{$payroll_date}' AND transaction_code = 'NETPAY' AND payment_type_id = 2  $pay_code 
                //                     ORDER BY lastname, firstname, middleinitial");
                
                $mdate = getdate(date("U"));
                $mdate = "$mdate[weekday], $mdate[month] $mdate[mday], $mdate[year]";

                $total_no_employees = $qry->num_rows();
                $allowed_count_per_page = 40;
                $page_with = $total_no_employees/$allowed_count_per_page;
                $page_floor = floor($page_with);

                $number_of_page = $page_floor;

                if($page_with > $page_floor){

                    $number_of_page = $page_floor + 1;
                }  
                
                if($total_no_employees > 0){
                    
                    for($i=1;$i<=$number_of_page; $i++){  
                        $limit = ($i - 1) * $allowed_count_per_page;
                        $dtl = $this->db->query("SELECT distinct u.employee_id, id_number,  lastname, firstname, middleinitial, aux, amount
                                    FROM {$this->db->dbprefix}user u
                                    LEFT JOIN {$this->db->dbprefix}employee_work_assignment w on u.employee_id = w.employee_id
                                    LEFT JOIN {$this->db->dbprefix}employee_payroll e ON u.employee_id = e.employee_id
                                    LEFT JOIN {$this->db->dbprefix}employee ee ON u.employee_id = ee.employee_id
                                    LEFT JOIN {$this->db->dbprefix}payroll_closed_transaction ct on u.employee_id = ct.employee_id
                                    WHERE w.cost_code = '{$proj->cost_code}'AND payroll_date = '{$payroll_date}' AND transaction_code = 'NETPAY' AND payment_type_id = 2  $pay_code 
                                    ORDER BY lastname, firstname, middleinitial
                                    LIMIT {$limit},{$allowed_count_per_page}");
                        
                        $tot_dtl = $this->db->query("SELECT sum(amount) as amount
                                    FROM {$this->db->dbprefix}user u
                                    LEFT JOIN {$this->db->dbprefix}employee_work_assignment w on u.employee_id = w.employee_id
                                    LEFT JOIN {$this->db->dbprefix}employee_payroll e ON u.employee_id = e.employee_id
                                    LEFT JOIN {$this->db->dbprefix}employee ee ON u.employee_id = ee.employee_id
                                    LEFT JOIN {$this->db->dbprefix}payroll_closed_transaction ct on u.employee_id = ct.employee_id
                                    WHERE w.cost_code = '{$proj->cost_code}'AND payroll_date = '{$payroll_date}' AND transaction_code = 'NETPAY' AND payment_type_id = 2  $pay_code ")->row();

                        // $dtl = $this->db->query("SELECT distinct u.employee_id, id_number,  lastname, firstname, middleinitial, aux, amount
                        //             FROM {$this->db->dbprefix}user u
                        //             LEFT JOIN {$this->db->dbprefix}employee_work_assignment w on u.employee_id = w.employee_id
                        //             LEFT JOIN {$this->db->dbprefix}employee_payroll e ON u.employee_id = e.employee_id
                        //             LEFT JOIN {$this->db->dbprefix}employee ee ON u.employee_id = ee.employee_id
                        //             LEFT JOIN {$this->db->dbprefix}payroll_current_transaction ct on u.employee_id = ct.employee_id  and ct.on_hold = 0 and ct.deleted = 0
                        //             WHERE w.cost_code = '{$proj->cost_code}'AND payroll_date = '{$payroll_date}' AND transaction_code = 'NETPAY' AND payment_type_id = 2  $pay_code 
                        //             ORDER BY lastname, firstname, middleinitial
                        //             LIMIT {$limit},{$allowed_count_per_page}");
                        
                        // $tot_dtl = $this->db->query("SELECT sum(amount) as amount
                        //             FROM {$this->db->dbprefix}user u
                        //             LEFT JOIN {$this->db->dbprefix}employee_work_assignment w on u.employee_id = w.employee_id
                        //             LEFT JOIN {$this->db->dbprefix}employee_payroll e ON u.employee_id = e.employee_id
                        //             LEFT JOIN {$this->db->dbprefix}employee ee ON u.employee_id = ee.employee_id
                        //             LEFT JOIN {$this->db->dbprefix}payroll_current_transaction ct on u.employee_id = ct.employee_id  and ct.on_hold = 0 and ct.deleted = 0
                        //             WHERE w.cost_code = '{$proj->cost_code}'AND payroll_date = '{$payroll_date}' AND transaction_code = 'NETPAY' AND payment_type_id = 2  $pay_code ")->row();

                        $this->pdf->SetMargins(10, 10, 10, true);   
                        $this->pdf->SetAutoPageBreak(TRUE);
                        $this->pdf->addPage('L', 'LEGAL', true);    
                        $this->pdf->SetFontSize( 8);            

                        $xcel_hed ='
                            <table>
                                <tr>
                                    <td style=" width:50%  ; text-align:left   ; font-size: 12 ; color:blue ; "><b>'.$company_setting_res->company.'</b></td>
                                    <td style=" width:50%  ; text-align:right  ; font-size:7  ; ">Run Date: '.date("m/d/Y").'</td>
                                </tr>
                                <tr>
                                    <td style=" width:50% ; text-align:left ; font-size: 9 ; ">PAYROLL SYSTEM</td>
                                    <td style=" width:50%  ; text-align:right  ; font-size:7  ; ">Page : '.$cnt_page.'</td>
                                </tr>
                                <tr>
                                    <td style=" width:100% ; text-align:left ; font-size: 9 ; ">'.date("F d, Y", strtotime($payroll_date)).'</td>
                                </tr>
                                <tr>
                                    <td style=" width:100% ; text-align:left ; font-size: 9 ; ">PAYCODE : '.$paycode->paycode.'</td>
                                </tr>
                                <tr>
                                    <td style=" width:100% ; text-align:left ; font-size: 9 ; "></td>
                                </tr>
                                <tr>
                                    <td style=" width:10% ; text-align:left   ; border-top-width:1px ; border-right-width:1px ; border-left-width:1px ; border-bottom-width:1px ; ">EMP ID#</td>
                                    <td style=" width:18% ; text-align:left ; border-top-width:1px ; border-right-width:1px ; border-left-width:1px ; border-bottom-width:1px ; ">EMPLOYEE NAME</td>
                                    <td style=" width:10% ; text-align:center ; border-top-width:1px ; border-right-width:1px ; border-left-width:1px ; border-bottom-width:1px ; ">COST CENTER</td>
                                    <td style=" width:10% ; text-align:center ; border-top-width:1px ; border-right-width:1px ; border-left-width:1px ; border-bottom-width:1px ; ">TOTAL PAY</td>
                                    <td style=" width: 4% ; text-align:center ; border-top-width:1px ; border-right-width:1px ; border-left-width:1px ; border-bottom-width:1px ; ">1000</td>
                                    <td style=" width: 4% ; text-align:center ; border-top-width:1px ; border-right-width:1px ; border-left-width:1px ; border-bottom-width:1px ; ">500</td>
                                    <td style=" width: 4% ; text-align:center ; border-top-width:1px ; border-right-width:1px ; border-left-width:1px ; border-bottom-width:1px ; ">200</td>
                                    <td style=" width: 4% ; text-align:center ; border-top-width:1px ; border-right-width:1px ; border-left-width:1px ; border-bottom-width:1px ; ">100</td>
                                    <td style=" width: 4% ; text-align:center ; border-top-width:1px ; border-right-width:1px ; border-left-width:1px ; border-bottom-width:1px ; ">50</td>
                                    <td style=" width: 4% ; text-align:center ; border-top-width:1px ; border-right-width:1px ; border-left-width:1px ; border-bottom-width:1px ; ">20</td>
                                    <td style=" width: 4% ; text-align:center ; border-top-width:1px ; border-right-width:1px ; border-left-width:1px ; border-bottom-width:1px ; ">10</td>
                                    <td style=" width: 4% ; text-align:center ; border-top-width:1px ; border-right-width:1px ; border-left-width:1px ; border-bottom-width:1px ; ">5</td>
                                    <td style=" width: 4% ; text-align:center ; border-top-width:1px ; border-right-width:1px ; border-left-width:1px ; border-bottom-width:1px ; ">1</td>
                                    <td style=" width: 4% ; text-align:center ; border-top-width:1px ; border-right-width:1px ; border-left-width:1px ; border-bottom-width:1px ; ">.25</td>
                                    <td style=" width: 4% ; text-align:center ; border-top-width:1px ; border-right-width:1px ; border-left-width:1px ; border-bottom-width:1px ; ">.10</td>
                                    <td style=" width: 4% ; text-align:center ; border-top-width:1px ; border-right-width:1px ; border-left-width:1px ; border-bottom-width:1px ; ">.05</td>
                                    <td style=" width: 4% ; text-align:center ; border-top-width:1px ; border-right-width:1px ; border-left-width:1px ; border-bottom-width:1px ; ">.01</td>
                                </tr>
                                <tr>
                                    <td style=" width:100% ; font-size:2 ; "></td>
                                </tr>
                                <tr>
                                    <td style=" width:100% ; font-size:2 ; "></td>
                                </tr>';

                        $tot_amt = 0;   
                        $tot_onet = 0;
                        $tot_fiveh = 0;
                        $tot_twoh = 0;
                        $tot_oneh = 0;
                        $tot_fifty = 0;
                        $tot_twenty = 0;
                        $tot_ten = 0;
                        $tot_five = 0;
                        $tot_one = 0;
                        $tot_twofivec = 0;
                        $tot_tenc = 0;
                        $tot_fivec = 0;
                        $tot_onec = 0;

                        foreach ($dtl->result() as $key => $dtl_res) {

                            $line_onet = floor(($dtl_res->amount/1000));
                            $line_fiveh = floor(($dtl_res->amount-(1000*$line_onet))/500);            
                            $line_twoh = floor(($dtl_res->amount-(1000*$line_onet)-(500*$line_fiveh))/200);
                            $line_oneh = floor(($dtl_res->amount-(1000*$line_onet)-(500*$line_fiveh)-(200*$line_twoh))/100);
                            $line_fifty = floor(($dtl_res->amount-(1000*$line_onet)-(500*$line_fiveh)-(200*$line_twoh)-(100*$line_oneh))/50);
                            $line_twenty = floor(($dtl_res->amount-(1000*$line_onet)-(500*$line_fiveh)-(200*$line_twoh)-(100*$line_oneh)-(50*$line_fifty))/20);
                            $line_ten = floor(($dtl_res->amount-(1000*$line_onet)-(500*$line_fiveh)-(200*$line_twoh)-(100*$line_oneh)-(50*$line_fifty)-(20*$line_twenty))/10);
                            $line_five = floor(($dtl_res->amount-(1000*$line_onet)-(500*$line_fiveh)-(200*$line_twoh)-(100*$line_oneh)-(50*$line_fifty)-(20*$line_twenty)-(10*$line_ten))/5);
                            $line_one = floor(($dtl_res->amount-(1000*$line_onet)-(500*$line_fiveh)-(200*$line_twoh)-(100*$line_oneh)-(50*$line_fifty)-(20*$line_twenty)-(10*$line_ten)-(5*$line_five))/1);
                            $line_twofivec = floor(($dtl_res->amount-(1000*$line_onet)-(500*$line_fiveh)-(200*$line_twoh)-(100*$line_oneh)-(50*$line_fifty)-(20*$line_twenty)-(10*$line_ten)-(5*$line_five)-(1*$line_one))/0.25);
                            $line_tenc = floor(($dtl_res->amount-(1000*$line_onet)-(500*$line_fiveh)-(200*$line_twoh)-(100*$line_oneh)-(50*$line_fifty)-(20*$line_twenty)-(10*$line_ten)-(5*$line_five)-(1*$line_one)-(0.25*$line_twofivec))/0.1);
                            $line_fivec = floor(($dtl_res->amount-(1000*$line_onet)-(500*$line_fiveh)-(200*$line_twoh)-(100*$line_oneh)-(50*$line_fifty)-(20*$line_twenty)-(10*$line_ten)-(5*$line_five)-(1*$line_one)-(0.25*$line_twofivec)-(0.1*$line_tenc))/0.05);
                            $line_onec = floor(($dtl_res->amount-(1000*$line_onet)-(500*$line_fiveh)-(200*$line_twoh)-(100*$line_oneh)-(50*$line_fifty)-(20*$line_twenty)-(10*$line_ten)-(5*$line_five)-(1*$line_one)-(0.25*$line_twofivec)-(0.1*$line_tenc)-(0.05*$line_fivec))/0.01);

                            if(!empty($dtl_res->aux)){
                                $aux = ' '.$dtl_res->aux.' ';
                            }else{
                                $aux = ' ';
                            }
                            $emp_name = $dtl_res->lastname.', '.$dtl_res->firstname.$aux.$dtl_res->middleinitial;
                            
                            $xcel_hed .='
                                <tr>
                                    <td style=" width:10% ; text-align:left   ; border-top-width:1px ; border-right-width:1px ; border-left-width:1px ; border-bottom-width:1px ; ">'.$dtl_res->id_number.'</td>
                                    <td style=" width:18% ; text-align:left   ; border-top-width:1px ; border-right-width:1px ; border-left-width:1px ; border-bottom-width:1px ; ">'.$emp_name.'</td>
                                    <td style=" width:10% ; text-align:center ; border-top-width:1px ; border-right-width:1px ; border-left-width:1px ; border-bottom-width:1px ; ">'.$proj->cost_code.'</td>
                                    <td style=" width:10% ; text-align:center ; border-top-width:1px ; border-right-width:1px ; border-left-width:1px ; border-bottom-width:1px ; ">'.number_format($dtl_res->amount,2,'.',',').'</td>
                                    <td style=" width: 4% ; text-align:center ; border-top-width:1px ; border-right-width:1px ; border-left-width:1px ; border-bottom-width:1px ; ">'.$line_onet.'</td>
                                    <td style=" width: 4% ; text-align:center ; border-top-width:1px ; border-right-width:1px ; border-left-width:1px ; border-bottom-width:1px ; ">'.$line_fiveh.'</td>
                                    <td style=" width: 4% ; text-align:center ; border-top-width:1px ; border-right-width:1px ; border-left-width:1px ; border-bottom-width:1px ; ">'.$line_twoh.'</td>
                                    <td style=" width: 4% ; text-align:center ; border-top-width:1px ; border-right-width:1px ; border-left-width:1px ; border-bottom-width:1px ; ">'.$line_oneh.'</td>
                                    <td style=" width: 4% ; text-align:center ; border-top-width:1px ; border-right-width:1px ; border-left-width:1px ; border-bottom-width:1px ; ">'.$line_fifty.'</td>
                                    <td style=" width: 4% ; text-align:center ; border-top-width:1px ; border-right-width:1px ; border-left-width:1px ; border-bottom-width:1px ; ">'.$line_twenty.'</td>
                                    <td style=" width: 4% ; text-align:center ; border-top-width:1px ; border-right-width:1px ; border-left-width:1px ; border-bottom-width:1px ; ">'.$line_ten.'</td>
                                    <td style=" width: 4% ; text-align:center ; border-top-width:1px ; border-right-width:1px ; border-left-width:1px ; border-bottom-width:1px ; ">'.$line_five.'</td>
                                    <td style=" width: 4% ; text-align:center ; border-top-width:1px ; border-right-width:1px ; border-left-width:1px ; border-bottom-width:1px ; ">'.$line_one.'</td>
                                    <td style=" width: 4% ; text-align:center ; border-top-width:1px ; border-right-width:1px ; border-left-width:1px ; border-bottom-width:1px ; ">'.$line_twofivec.'</td>
                                    <td style=" width: 4% ; text-align:center ; border-top-width:1px ; border-right-width:1px ; border-left-width:1px ; border-bottom-width:1px ; ">'.$line_tenc.'</td>
                                    <td style=" width: 4% ; text-align:center ; border-top-width:1px ; border-right-width:1px ; border-left-width:1px ; border-bottom-width:1px ; ">'.$line_fivec.'</td>
                                    <td style=" width: 4% ; text-align:center ; border-top-width:1px ; border-right-width:1px ; border-left-width:1px ; border-bottom-width:1px ; ">'.$line_onec.'</td>
                                </tr>';

                                $cnt++;
                                $emp_cnt++;

                                $tot_amt += $dtl_res->amount;
                                $tot_onet += $line_onet;
                                $tot_fiveh += $line_fiveh;
                                $tot_twoh += $line_twoh;
                                $tot_oneh += $line_oneh;
                                $tot_fifty += $line_fifty;
                                $tot_twenty += $line_twenty;
                                $tot_ten += $line_ten;
                                $tot_five += $line_five;
                                $tot_one += $line_one;
                                $tot_twofivec += $line_twofivec;
                                $tot_tenc += $line_tenc;
                                $tot_fivec += $line_fivec;
                                $tot_onec += $line_onec;

                                $g_amt += $dtl_res->amount;
                                $g_onet += $line_onet;
                                $g_fiveh += $line_fiveh;
                                $g_twoh += $line_twoh;
                                $g_oneh += $line_oneh;
                                $g_fifty += $line_fifty;
                                $g_twenty += $line_twenty;
                                $g_ten += $line_ten;
                                $g_five += $line_five;
                                $g_one += $line_one;
                                $g_twofivec += $line_twofivec;
                                $g_tenc += $line_tenc;
                                $g_fivec += $line_fivec;
                                $g_onec += $line_onec;
                        }

                        $xcel_hed .='
                                    <tr><td></td></tr>
                                    <tr>
                                        <td style=" width:10% ; text-align:left   ;">TOTAL</td>
                                        <td style=" width:18% ; text-align:left   ; ">'.$total_no_employees.'</td>
                                        <td style=" width:10% ; text-align:center ; "></td>
                                        <td style=" width:10% ; text-align:center ; ">'.number_format($tot_amt,2,'.',',').'</td>
                                        <td style=" width: 4% ; text-align:center ; ">'.$tot_onet.'</td>
                                        <td style=" width: 4% ; text-align:center ; ">'.$tot_fiveh.'</td>
                                        <td style=" width: 4% ; text-align:center ; ">'.$tot_twoh.'</td>
                                        <td style=" width: 4% ; text-align:center ; ">'.$tot_oneh.'</td>
                                        <td style=" width: 4% ; text-align:center ; ">'.$tot_fifty.'</td>
                                        <td style=" width: 4% ; text-align:center ; ">'.$tot_twenty.'</td>
                                        <td style=" width: 4% ; text-align:center ; ">'.$tot_ten.'</td>
                                        <td style=" width: 4% ; text-align:center ; ">'.$tot_five.'</td>
                                        <td style=" width: 4% ; text-align:center ; ">'.$tot_one.'</td>
                                        <td style=" width: 4% ; text-align:center ; ">'.$tot_twofivec.'</td>
                                        <td style=" width: 4% ; text-align:center ; ">'.$tot_tenc.'</td>
                                        <td style=" width: 4% ; text-align:center ; ">'.$tot_fivec.'</td>
                                        <td style=" width: 4% ; text-align:center ; ">'.$tot_onec.'</td>
                                    </tr>';

                        if(($emp_cnt - 1) == $count_emp){ 
                            
                            $xcel_hed .='
                                    <tr><td></td></tr>
                                    <tr>
                                        <td style=" width:10% ; text-align:left   ;">GRAND TOTAL</td>
                                        <td style=" width:18% ; text-align:left   ; ">'.( $emp_cnt - 1 ).'</td>
                                        <td style=" width:10% ; text-align:center ; "></td>
                                        <td style=" width:10% ; text-align:center ; ">'.number_format($g_amt,2,'.',',').'</td>
                                        <td style=" width: 4% ; text-align:center ; ">'.$g_onet.'</td>
                                        <td style=" width: 4% ; text-align:center ; ">'.$g_fiveh.'</td>
                                        <td style=" width: 4% ; text-align:center ; ">'.$g_twoh.'</td>
                                        <td style=" width: 4% ; text-align:center ; ">'.$g_oneh.'</td>
                                        <td style=" width: 4% ; text-align:center ; ">'.$g_fifty.'</td>
                                        <td style=" width: 4% ; text-align:center ; ">'.$g_twenty.'</td>
                                        <td style=" width: 4% ; text-align:center ; ">'.$g_ten.'</td>
                                        <td style=" width: 4% ; text-align:center ; ">'.$g_five.'</td>
                                        <td style=" width: 4% ; text-align:center ; ">'.$g_one.'</td>
                                        <td style=" width: 4% ; text-align:center ; ">'.$g_twofivec.'</td>
                                        <td style=" width: 4% ; text-align:center ; ">'.$g_tenc.'</td>
                                        <td style=" width: 4% ; text-align:center ; ">'.$g_fivec.'</td>
                                        <td style=" width: 4% ; text-align:center ; ">'.$g_onec.'</td>
                                    </tr>';
                            }

                        $xcel_hed .='</table>';

                        $this->pdf->writeHTML($xcel_hed, true, false, true, false, '');
                        $cnt_page++;    
                    }
                }
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
