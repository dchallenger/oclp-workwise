<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class cash_voucher extends my_controller
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
        $report_type = array("Cash Voucher","Employee List");
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
            case '0':
                $html = $this->export_cash_voucher($company_id, $employee_id, $payroll_date, "Cash Voucher");
                $title = "Cash Voucher";
                break;
            case '1':
                $html = $this->export_employee_list($company_id, $employee_id, $payroll_date, "Employee List");
                $title = "Employee List";
                break;
        }

        $this->pdf->writeHTML($html, true, false, true, false, '');
        $this->pdf->Output(ucwords(str_replace(" ","_",$title))."_" . date('dmYHis') . '.pdf', 'I');
    }

    private function convert_number_to_words($number) {

        $hyphen      = '-';
        $conjunction = ' ';
        $separator   = ', ';
        $negative    = 'negative ';
        $decimal     = ' and ';
        $dictionary  = array(
            0                   => 'Zero',
            1                   => 'One',
            2                   => 'Two',
            3                   => 'Three',
            4                   => 'Four',
            5                   => 'Five',
            6                   => 'Six',
            7                   => 'Seven',
            8                   => 'Eight',
            9                   => 'Nine',
            10                  => 'Ten',
            11                  => 'Eleven',
            12                  => 'Twelve',
            13                  => 'Thirteen',
            14                  => 'Fourteen',
            15                  => 'Fifteen',
            16                  => 'Sixteen',
            17                  => 'Seventeen',
            18                  => 'Eighteen',
            19                  => 'Nineteen',
            20                  => 'Twenty',
            30                  => 'Thirty',
            40                  => 'Fourty',
            50                  => 'Fifty',
            60                  => 'Sixty',
            70                  => 'Seventy',
            80                  => 'Eighty',
            90                  => 'Ninety',
            100                 => 'Hundred',
            1000                => 'Thousand',
            1000000             => 'Million',
            1000000000          => 'Billion',
            1000000000000       => 'Trillion',
            1000000000000000    => 'Quadrillion',
            1000000000000000000 => 'Quintillion'
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

    function export_cash_voucher($company_id, $employee_id, $payroll_date, $title){
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

        $qry = "SELECT * FROM {$this->db->dbprefix}$transaction p
                LEFT JOIN {$this->db->dbprefix}user u ON p.employee_id = u.employee_id
                LEFT JOIN {$this->db->dbprefix}employee_payroll e on e.employee_id = p.employee_id and payment_type_id = 2
                WHERE 1 AND payroll_date = '{$payroll_date}' AND transaction_code = 'NETPAY'
                $company $employee
                ORDER BY u.lastname, u.firstname, u.middlename";
        
        $res = $this->db->query($qry);
        $cnt = 1;
        $total_no_employees = $res->num_rows();
        $allowed_count_per_page = 3;
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

                $detail_qry = " SELECT * FROM {$this->db->dbprefix}$transaction p
                                LEFT JOIN {$this->db->dbprefix}user u ON p.employee_id = u.employee_id
                                LEFT JOIN {$this->db->dbprefix}employee_payroll e on e.employee_id = p.employee_id and payment_type_id = 2
                                WHERE 1 AND payroll_date = '{$payroll_date}' AND transaction_code = 'NETPAY'
                                $company $employee
                                ORDER BY u.lastname, u.firstname, u.middlename";
                
                $limit = ($i - 1) * $allowed_count_per_page;

                $detail_qry .= " LIMIT {$limit},{$allowed_count_per_page}";
                $detail_res = $this->db->query($detail_qry);
                
                $count = 0;
                foreach ($detail_res->result() as $key => $value) 
                {            
                    $emp_name = $value->lastname.', '.$value->firstname.' '.$value->middlename;
                    $xcel_hed .= '  <table style="width:100%;">
                                    <tr>
                                        <td style=" width:100% ; text-align:center; font-size:11; "><b>'.$company_setting_res->company.'</b></td>
                                    </tr>
                                    <tr>
                                        <td style=" width:100% ; font-size:3; "></td>
                                    </tr>
                                    <tr>
                                        <td style=" width:46.5% ; text-align:right  ; font-size:11 ; "><b>C A S H </b></td>
                                        <td style=" width:53.5% ; text-align:left   ; font-size:11 ; "><b> V O U C H E R</b></td>
                                    </tr>
                                    <tr>
                                        <td style=" width:100% ; font-size:9 ; "></td>
                                    </tr>
                                    <tr>
                                        <td style=" width:11%  ; text-align:left   ; "><b>PAID TO :</b></td>
                                        <td style=" width:54%  ; text-align:left   ; font-size:9 ; border-bottom-width:3px ; ">'.$emp_name.'</td>
                                        <td style=" width:16%  ; text-align:right  ; "><b>DATE:</b></td>
                                        <td style=" width:19%  ; text-align:left   ; font-size:9 ; border-bottom-width:3px ; ">'.$payroll_date.'</td>
                                    </tr>
                                    
                                    <tr>
                                        <td style=" width:11%  ; text-align:left   ; "><b>ADDRESS:</b></td>
                                        <td style=" width:54%  ; text-align:left   ; font-size:9 ; border-bottom-width:3px ; "></td>
                                        <td style=" width:16%  ; text-align:right  ; "><b>PREPARED BY:</b></td>
                                        <td style=" width:19%  ; text-align:left   ; font-size:9 ; border-bottom-width:3px ; ">'.$this->userinfo['nickname'].'</td>
                                    </tr>
                                    <tr>
                                        <td style=" width:100% ; font-size:9 ; border-bottom-width:3px; "></td>
                                    </tr>
                                    <tr>
                                        <td style=" width:100% ; font-size:3 ; "></td>
                                    </tr>
                                    <tr>
                                        <td style=" width:90%  ; text-align:center ; font-size:10 ; border-bottom-width:1px ; ">DESCRIPTION</td>
                                        <td style=" width:10%  ; text-align:right  ; font-size:10 ; border-bottom-width:1px ; ">AMOUNT</td>
                                    </tr>
                                    <tr>
                                        <td style=" width:100% ; font-size:3 ; "></td>
                                    </tr>
                                    <tr>
                                        <td style=" width:90%  ; text-align:center ; font-size:10 ; ">Salary for the Period : '.date('M d, Y',strtotime($date_from)).' - '.date('M d, Y',strtotime($date_to)).'</td>
                                        <td style=" width:10%  ; text-align:right  ; font-size:10 ; ">'.$value->amount.'</td>
                                    </tr>
                                    <tr>
                                        <td style=" width:100% ; font-size:24 ;  "></td>
                                    </tr>
                                    <tr>
                                        <td style=" width:100% ; font-size:9 ; border-top-width:3px; "></td>
                                    </tr>
                                    <tr>
                                        <td style=" width:10%  ; text-align:left   ; "><b>PESOS:</b></td>
                                        <td style=" width:90%  ; text-align:left   ; font-size:9 ; font-style:italic ; border-bottom-width:3px ; "><b>'.$this->convert_number_to_words($value->amount).' Only</b></td>
                                    </tr>
                                    <tr>
                                        <td style=" width:17%  ; text-align:left   ; "><b>BANK/CHECK NO.</b></td>
                                        <td style=" width: 3%  ; text-align:center ; "><b>:</b></td>
                                        <td style=" width:20%  ; text-align:left   ; border-bottom-width:3px ; "></td>
                                        <td style=" width:25%  ; text-align:left   ; "></td>
                                        <td style=" width:35%  ; text-align:left   ; "><b>Received Payment By:</b></td>
                                    </tr>
                                    <tr>
                                        <td style=" width:17%  ; text-align:left   ; "><b>AMOUNT</b></td>
                                        <td style=" width: 3%  ; text-align:center ; "><b>:</b></td>
                                        <td style=" width:20%  ; text-align:left   ; border-bottom-width:3px ; "></td>
                                        <td style=" width:60%  ; text-align:left   ; "></td>
                                    </tr>
                                    <tr>
                                        <td style=" width:17%  ; text-align:left   ; "><b>DATE</b></td>
                                        <td style=" width: 3%  ; text-align:center ; "><b>:</b></td>
                                        <td style=" width:20%  ; text-align:left   ; border-bottom-width:3px ; "></td>
                                        <td style=" width:25%  ; text-align:left   ; "></td>
                                        <td style=" width:35%  ; text-align:left   ; border-bottom-width:3px ; ">   </td>
                                    </tr>
                                    <tr>
                                        <td style=" width:100% ; font-size:3 ; "></td>
                                    </tr>
                                    <tr>
                                        <td style=" width: 8%  ; text-align:right  ; font-size:8 ; font-style:italic ; "><b>NOTE :</b></td>
                                        <td style=" width:92%  ; text-align:left   ; font-size:8 ; font-style:italic ; "> Keep this voucher. It is your record of your total earnings.</td>
                                    </tr>
                                    <tr>
                                        <td style=" width:100% ; font-size:3 ; "></td>
                                    </tr>
                                    <tr>
                                        <td style=" width:100% ; font-size:8 ; border-top-width:3px ; "></td>
                                    </tr>
                                    <tr><td></td></tr>
                                </table>';
                    
                    $count++;
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
    function export_employee_list($company_id, $employee_id, $payroll_date, $title){
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

        $qry = "SELECT * FROM {$this->db->dbprefix}$transaction p
                LEFT JOIN {$this->db->dbprefix}user u ON p.employee_id = u.employee_id
                LEFT JOIN {$this->db->dbprefix}employee_payroll e on e.employee_id = p.employee_id and payment_type_id = 2
                WHERE 1 AND payroll_date = '{$payroll_date}' AND transaction_code = 'NETPAY'
                $company $employee
                ORDER BY u.lastname, u.firstname, u.middlename";
        
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
                $this->pdf->SetMargins(10, 10, 10, true);
                $this->pdf->addPage('P', 'LETTER', true);    
                $this->pdf->SetFontSize( 10 );            
                $xcel_hed .= '';
                $xcel_hed .= '  <table style="width:100%;">
                                    <tr>
                                        <td style=" width:100% ; text-align:center; font-size:11; "><b>'.$company_setting_res->company.'</b></td>
                                    </tr>
                                    <tr>
                                        <td style=" width:100% ; text-align:center  ; font-size:11 ; "><b>Employee List</b></td>
                                    </tr>
                                    <tr>
                                        <td style=" width:100%  ; text-align:center ; font-size:10 ; "><b>Salary for the Period : '.$date_from.' - '.$date_to.'</b></td>
                                    </tr>
                                    <tr>
                                        <td style=" width:100% ; font-size:6; "></td>
                                    </tr>
                                    <tr>
                                        <td style=" width:10%  ; text-align:left   ; "><b>No.</b></td>
                                        <td style=" width:20%  ; text-align:left   ; "><b>Employee ID</b></td>
                                        <td style=" width:55%  ; text-align:left   ; "><b>Name of Employee</b></td>
                                        <td style=" width:15%  ; text-align:right  ; "><b>Amount</b></td>
                                    </tr>
                                    <tr>
                                        <td style=" width:100% ; font-size:2; border-bottom-width:3px; "></td>
                                    </tr>
                                    <tr>
                                        <td style=" width:100% ; font-size:2; "></td>
                                    </tr>';
                $detail_qry = " SELECT * FROM {$this->db->dbprefix}$transaction p
                                LEFT JOIN {$this->db->dbprefix}user u ON p.employee_id = u.employee_id
                                LEFT JOIN {$this->db->dbprefix}employee_payroll e on e.employee_id = p.employee_id and payment_type_id = 2
                                WHERE 1 AND payroll_date = '{$payroll_date}' AND transaction_code = 'NETPAY'
                                $company $employee
                                ORDER BY u.lastname, u.firstname, u.middlename";
                
                $limit = ($i - 1) * $allowed_count_per_page;

                $detail_qry .= " LIMIT {$limit},{$allowed_count_per_page}";
                $detail_res = $this->db->query($detail_qry);
                $count = 0;
                foreach ($detail_res->result() as $key => $value) 
                {            
                    $emp_name = $value->lastname.', '.$value->firstname.' '.$value->middlename;
                    $xcel_hed .= '  <tr>
                                        <td style=" width:10%  ; text-align:left   ; ">'.$cnt.'</td>
                                        <td style=" width:20%  ; text-align:left   ; ">'.$value->id_number.'</td>
                                        <td style=" width:55%  ; text-align:left   ; ">'.$emp_name.'</td>
                                        <td style=" width:15%  ; text-align:right  ; ">'.$value->amount.'</td>
                                    </tr>';
                    $cnt++;
                    $count++;
                }

                if($count != $allowed_count_per_page)
                {   
                    
                    for ($space=1; $space <= ($allowed_count_per_page - $count); $space++) 
                    {
                        $xcel_hed .= '<tr><td></td></tr>';
                    }   
                }
                $xcel_hed .= '      <tr>
                                        <td style=" width:100% ; font-size:2 ; border-bottom-width:3px ; "></td>
                                    </tr>
                                    <tr>
                                        <td style=" width:100% ; font-size:2 ; "></td>
                                    </tr>
                                    <tr>
                                        <td style=" width:100% ; font-size:9 ; text-align:right ; ">Page '.$i.' of '.$number_of_page.'</td>
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
