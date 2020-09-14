<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class standard_report extends MY_Controller
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
        $report_type = array("Payslip", "Bank Remittance", "Coinage", "Final Pay");
        // $report_type = array("Payslip", "Bank Remittance", "Coinage", "Final Pay","Embark / Disembark");
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

    function employee_multiple(){

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

        // $date_from = date("Y-m-d",strtotime($_POST['date_range_from']));
        // $date_to = date("Y-m-d",strtotime($_POST['date_range_to']));
        $payroll_date = date("Y-m-d",strtotime($_POST['date']));  
        
        $this->load->library('pdf');
        switch ($_POST['report_type_id']) 
        {
            //payslip
            case '0':
                $html = $this->export_payslip($company_id, $employee_id, $payroll_date, "Employee Payslip");
                $title = "Employee Payslip";
                break;            
            //bank remittance
            case '1':
                $html = $this->export_remittance($company_id, $employee_id, $payroll_date, "Bank Remittance");
                $title = "Bank Remittance";
                break;
            //coinage
            case '2':
                $html = $this->export_coinage($company_id, $employee_id, $payroll_date, "Coinage Report");
                $title = "Coinage Report";
                $this->pdf->addPage('L', 'LEGAL', true);
                $this->pdf->SetFontSize( 8 );
                break;
            //final pay
            case '3':
                $html = $this->export_final_pay($company_id, $employee_id, $payroll_date, "Final Pay");
                $title = 'Final Pay';
                break;
        }   
        
        $this->pdf->writeHTML($html, true, false, true, false, '');
        $this->pdf->Output(ucwords(str_replace(" ","_",$title))."_" . date('dmYHis') . '.pdf', 'I');
    }

    function export_payslip($company_id, $employee_id, $payroll_date, $title){
        switch (CLIENT_DIR) {
            case 'asianshipping':
                          
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
                        $this->pdf->SetFontSize( 9 );            
                        $xcel_hed .= '';

                        $detail_qry = " SELECT p.employee_id, u.lastname, u.firstname, u.aux u.middlename FROM {$this->db->dbprefix}$transaction p
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
                            $gross_amt = $this->db->query("SELECT sum(amount) as amount FROM {$this->db->dbprefix}$transaction p
                                    WHERE deleted = 0 AND on_hold = 0 AND payroll_date = '{$payroll_date}' AND p.employee_id = $value->employee_id 
                                        AND transaction_type_id IN ( SELECT transaction_type_id FROM {$this->db->dbprefix}payroll_transaction_type WHERE operation = '+' )")->row();                

                            $tot_ded_amt = $this->db->query("SELECT sum(amount) as amount FROM {$this->db->dbprefix}$transaction p
                                    WHERE deleted = 0 AND on_hold = 0 AND payroll_date = '{$payroll_date}' AND p.employee_id = $value->employee_id 
                                        AND transaction_type_id IN ( SELECT transaction_type_id FROM {$this->db->dbprefix}payroll_transaction_type WHERE operation = '-' )")->row();
                            $detail_qry = " SELECT * FROM {$this->db->dbprefix}$transaction p
                                    WHERE deleted = 0 AND on_hold = 0 AND payroll_date = '{$payroll_date}' AND p.employee_id = $value->employee_id";
                    
                            $detail_res = $this->db->query($detail_qry);
                            
                            $absent_amt = '';
                            $late_amt = '';
                            $undertime_amt = '';
                            
                            foreach ($detail_res->result() as $dtl){
                                switch ($dtl->transaction_code) {
                                    case 'SALARY':
                                        $sal_unit = $dtl->quantity;
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
                                        $absent_unit = $dtl->quantity;
                                        $absent_amt = $dtl->amount;
                                        break;
                                    case 'DEDUCTION_LATE':
                                        $late_unit = $dtl->quantity;
                                        $late_amt = $dtl->amount;
                                        break;
                                    case 'DEDUCTION_UNDERTIME':
                                        $undertime_unit = $dtl->quantity;
                                        $undertime_amt = $dtl->amount;
                                        break;
                                    case 'NETPAY':
                                        $netpay_amt = $dtl->amount;
                                        break;
                                    // OT Regular
                                    case 'REGOT':
                                        $regot_unit = $dtl->quantity;
                                        $regot = $dtl->amount;
                                        break;
                                    case 'REGND':
                                        $regnd_unit = $dtl->quantity;
                                        $regnd = $dtl->amount; 
                                        break;
                                    case 'REGOT_ND':
                                        $regot_nd_unit = $dtl->quantity;
                                        $regot_nd = $dtl->amount;
                                        break;
                                    // Rest Day OT
                                    case 'RDOT':
                                        $rd0t_unit = $dtl->quantity;
                                        $rdot = $dtl->amount;
                                        break;
                                    case 'RDOT_ND':
                                        $rdot_nd_unit = $dtl->quantity;
                                        $rdot_nd = $dtl->amount;
                                        break;
                                    case 'RDOT_EXCESS':
                                        $rdot_x_unit = $dtl->quantity;
                                        $rdot_x = $dtl->amount;
                                        break;
                                    case 'RDOT_ND_EXCESS':
                                        $rdot_nd_x_unit = $dtl->quantity;
                                        $rdot_nd_x = $dtl->amount;
                                        break;
                                    // Leg OT
                                    case 'LEGOT';
                                        $legot_unit = $dtl->quantity;
                                        $legot = $dtl->amount;
                                        break;
                                    case 'LEGOT_ND';
                                        $legot_nd_unit = $dtl->quantity;
                                        $legot_nd = $dtl->amount;
                                        break;
                                    case 'LEGOT_EXCESS';
                                        $legot_x_unit = $dtl->quantity;
                                        $legot_x = $dtl->amount;
                                        break;
                                    case 'LEGOT_ND_EXCESS';
                                        $legot_nd_x = $dtl->amount;
                                        break;
                                    case 'LEGRDOT';
                                        $legrdot_unit = $dtl->quantity;
                                        $legrdot = $dtl->amount;
                                        break;
                                    case 'LEGRDOT_ND';
                                        $legrdot_nd_unit = $dtl->quantity;
                                        $legrdot_nd = $dtl->amount;
                                        break;
                                    case 'LEGRDOT_EXCESS';
                                        $legrdot_x_unit = $dtl->quantity;
                                        $legrdot_x = $dtl->amount;
                                        break;
                                    case 'LEGRDOT_ND_EXCESS';
                                        $legrdot_nd_x_unit = $dtl->quantity;
                                        $legrdot_nd_x = $dtl->amount;
                                        break;
                                    // SPL OT
                                    case 'SPEOT';
                                        $speot_unit = $dtl->quantity;
                                        $speot = $dtl->amount;
                                        break;
                                    case 'SPEOT_ND';
                                        $speot_nd_unit = $dtl->quantity;
                                        $speot_nd = $dtl->amount;
                                        break;
                                    case 'SPEOT_EXCESS';
                                        $speot_x_unit = $dtl->quantity;
                                        $speot_x = $dtl->amount;
                                        break;
                                    case 'SPEOT_ND_EXCESS';
                                        $speot_nd_x_unit = $dtl->quantity;
                                        $speot_nd_x = $dtl->amount;
                                        break;
                                    case 'SPERDOT';
                                        $sperdot_unit = $dtl->quantity;
                                        $sperdot = $dtl->amount;
                                        break;
                                    case 'SPERDOT_ND';
                                        $sperdot_nd_unit = $dtl->quantity;
                                        $sperdot_nd = $dtl->amount;
                                        break;
                                    case 'SPERDOT_EXCESS';
                                        $sperdot_x_unit = $dtl->quantity;
                                        $sperdot_x = $dtl->amount;
                                        break;
                                    case 'SPERDOT_ND_EXCESS';
                                        $sperdot_nd_x_unit = $dtl->quantity;
                                        $sperdot_nd_x = $dtl->amount;
                                        break;
                               }
                            }
                            $regular_overtime = $regot + $regnd + $regot_nd;
                            $restday_overtime = $rdot + $rdot_nd + $rdot_x + $rdot_nd_x;
                            $legal_overtime = $legot + $legot_nd + $legot_x + $legot_nd_x + $legrdot + $legrdot_nd + $legrdot_x + $legrdot_nd_x;
                            $special_overtime = $speot + $speot_nd + $speot_x + $speot_nd_x + $sperdot + $sperdot_nd + $sperdot_x + $sperdot_nd_x;

                            $regular_overtime_unit = $regot_unit + $regnd_unit + $regot_nd_unit;
                            $restday_overtime_unit = $rdot_unit + $rdot_nd_unit + $rdot_x_unit + $rdot_nd_x_unit;
                            $legal_overtime_unit = $legot_unit + $legot_nd_unit + $legot_x_unit + $legot_nd_x_unit + $legrdot_unit + $legrdot_nd_unit + $legrdot_x_unit + $legrdot_nd_x_unit_unit;
                            $special_overtime_unit = $speot_unit + $speot_nd_unit + $speot_x_unit + $speot_nd_x_unit + $sperdot_unit + $sperdot_nd_unit + $sperdot_x_unit + $sperdot_nd_x_unit;

                            $tardiness_amt = $late_amt_unit + $undertime_amt_unit;
                            $tardiness_amt = $late_amt + $undertime_amt;

                            $emp_name = $value->lastname.', '.$value->firstname.' '.$value->aux.' '.$value->middlename;
                            $xcel_hed .= '<table style="width:100%;">
                                            <tr><td></td></tr><tr><td></td></tr>
                                            <tr>
                                                <td style=" width:100% ; text-align:left   ; ">'.$company_setting_res->company.'</td>
                                            </tr>
                                            <tr>
                                                <td style=" width:15%  ; text-align:left  ; ">Payroll Period: </td>
                                                <td style=" width:35%  ; text-align:left  ; ">'.date('M d, Y',strtotime($date_from)).' - '.date('M d, Y',strtotime($date_to)).'</td>
                                                <td style=" width:20%  ; text-align:left  ; ">Batch No.: </td>
                                                <td style=" width:30%  ; text-align:left  ; ">Prepared by: '.$this->userinfo['nickname'].'</td>
                                            </tr>
                                            <tr>
                                                <td style=" width:15%  ; text-align:left  ; ">Employee Name: </td>
                                                <td style=" width:85%  ; text-align:left  ; ">'.$emp_name.'</td>
                                            </tr>
                                            <tr>
                                                <td style=" width:32%  ; text-align:left   ; font-size:8 ; border-right-width: 1px ; "></td>
                                                <td style=" width:22%  ; text-align:center ; font-size:8 ; border-right-width: 1px ; border-bottom-width: 1px ; ">OTHER BENEFITS</td>
                                                <td style=" width:16%  ; text-align:center ; font-size:8 ; border-right-width: 1px ; border-bottom-width: 1px ; ">MAND. DEDUCT`N</td>
                                                <td style=" width:20%  ; text-align:center ; font-size:8 ; border-right-width: 1px ; border-bottom-width: 1px ; ">OTHER DEDUCTION</td>
                                                <td style=" width:13%  ; text-align:right  ; font-size:8 ; ">OUTSTANDING</td>
                                            </tr>
                                            <tr>
                                                <td style=" width:16%  ; text-align:left   ; font-size:8 ; border-bottom-width: 1px ; ">DESCRIPTION</td>
                                                <td style=" width: 6%  ; text-align:right  ; font-size:8 ; border-bottom-width: 1px ; ">UNIT</td>
                                                <td style=" width:10%  ; text-align:right  ; font-size:8 ; border-right-width: 1px ; border-bottom-width: 1px ; ">AMOUNT</td>
                                                <td style=" width:12%  ; text-align:left   ; font-size:8 ; border-right-width: 1px ; border-bottom-width: 1px ; ">CODE</td>
                                                <td style=" width:10%  ; text-align:right  ; font-size:8 ; border-right-width: 1px ; border-bottom-width: 1px ; ">AMOUNT</td>
                                                <td style=" width: 6%  ; text-align:left   ; font-size:8 ; border-right-width: 1px ; border-bottom-width: 1px ; ">DESC.</td>
                                                <td style=" width:10%  ; text-align:right  ; font-size:8 ; border-right-width: 1px ; border-bottom-width: 1px ; ">AMOUNT</td>
                                                <td style=" width:10%  ; text-align:left   ; font-size:8 ; border-right-width: 1px ; border-bottom-width: 1px ; ">CODE</td>
                                                <td style=" width:10%  ; text-align:right  ; font-size:8 ; border-right-width: 1px ; border-bottom-width: 1px ; ">AMOUNT</td>
                                                <td style=" width:13%  ; text-align:right  ; font-size:8 ; border-bottom-width: 1px ; ">BALANCE</td>
                                            </tr>
                                            <tr>
                                                <td style=" width:16%  ; text-align:left   ; font-size:8 ; ">Regular Day</td>
                                                <td style=" width: 6%  ; text-align:right  ; font-size:8 ; ">'.$sal_unit.'</td>
                                                <td style=" width:10%  ; text-align:right  ; font-size:8 ; border-right-width: 1px ; ">'.number_format($sal_amt,2,'.',',').'</td>
                                                <td style=" width:12%  ; text-align:left   ; font-size:8 ; border-right-width: 1px ; border-bottom-width: 1px ; "></td>
                                                <td style=" width:10%  ; text-align:right  ; font-size:8 ; border-right-width: 1px ; border-bottom-width: 1px ; "></td>
                                                <td style=" width: 6%  ; text-align:left   ; font-size:8 ; border-right-width: 1px ; ">SSS</td>
                                                <td style=" width:10%  ; text-align:right  ; font-size:8 ; border-right-width: 1px ; ">'.number_format($sss_amt,2,'.',',').'</td>
                                                <td style=" width:10%  ; text-align:left   ; font-size:8 ; border-right-width: 1px ; "></td>
                                                <td style=" width:10%  ; text-align:right  ; font-size:8 ; border-right-width: 1px ; "></td>
                                                <td style=" width:13%  ; text-align:right  ; font-size:8 ; "></td>
                                            </tr>
                                            <tr>
                                                <td style=" width:16%  ; text-align:left   ; font-size:8 ; ">OT (H)</td>
                                                <td style=" width: 6%  ; text-align:right  ; font-size:8 ; ">'.$regular_overtime_unit.'</td>
                                                <td style=" width:10%  ; text-align:right  ; font-size:8 ; border-right-width: 1px ; ">'.number_format($regular_overtime,2,'.',',').'</td>
                                                <td style=" width:12%  ; text-align:right  ; font-size:8 ; border-right-width: 1px ; ">Total</td>
                                                <td style=" width:10%  ; text-align:right  ; font-size:8 ; border-right-width: 1px ; "></td>
                                                <td style=" width: 6%  ; text-align:left   ; font-size:8 ; border-right-width: 1px ; ">PHIC</td>
                                                <td style=" width:10%  ; text-align:right  ; font-size:8 ; border-right-width: 1px ; ">'.number_format($phic_amt,2,'.',',').'</td>
                                                <td style=" width:10%  ; text-align:left   ; font-size:8 ; border-right-width: 1px ; border-bottom-width: 1px ; "></td>
                                                <td style=" width:10%  ; text-align:right  ; font-size:8 ; border-right-width: 1px ; border-bottom-width: 1px ; "></td>
                                                <td style=" width:13%  ; text-align:right  ; font-size:8 ; border-bottom-width: 1px ; "></td>
                                            </tr>
                                            <tr>
                                                <td style=" width:16%  ; text-align:left   ; font-size:8 ; ">Sunday (H)</td>
                                                <td style=" width: 6%  ; text-align:right  ; font-size:8 ; ">'.$restday_overtime_unit.'</td>
                                                <td style=" width:10%  ; text-align:right  ; font-size:8 ; border-right-width: 1px ; ">'.number_format($restday_overtime,2,'.',',').'</td>
                                                <td style=" width:12%  ; text-align:right  ; font-size:8 ; "></td>
                                                <td style=" width:10%  ; text-align:right  ; font-size:8 ; border-right-width: 1px ; "></td>
                                                <td style=" width: 6%  ; text-align:left   ; font-size:8 ; border-right-width: 1px ; ">HDMF</td>
                                                <td style=" width:10%  ; text-align:right  ; font-size:8 ; border-right-width: 1px ; ">'.number_format($hdmf_amt,2,'.',',').'</td>
                                                <td style=" width:10%  ; text-align:right  ; font-size:8 ; border-right-width: 1px ; ">Total</td>
                                                <td style=" width:10%  ; text-align:right  ; font-size:8 ; border-right-width: 1px ; "></td>
                                                <td style=" width:13%  ; text-align:right  ; font-size:8 ; "></td>
                                            </tr>
                                            <tr>
                                                <td style=" width:16%  ; text-align:left   ; font-size:8 ; ">Spc. Hol (H)</td>
                                                <td style=" width: 6%  ; text-align:right  ; font-size:8 ; ">'.$special_overtime_unit.'</td>
                                                <td style=" width:10%  ; text-align:right  ; font-size:8 ; border-right-width: 1px ; ">'.number_format($special_overtime,2,'.',',').'</td>
                                                <td style=" width:12%  ; text-align:right  ; font-size:8 ; "></td>
                                                <td style=" width:10%  ; text-align:right  ; font-size:8 ; border-right-width: 1px ; "></td>
                                                <td style=" width: 6%  ; text-align:left   ; font-size:8 ; border-right-width: 1px ; ">W/Tax</td>
                                                <td style=" width:10%  ; text-align:right  ; font-size:8 ; border-right-width: 1px ; ">'.number_format($whtax_amt,2,'.',',').'</td>
                                                <td style=" width:10%  ; text-align:right  ; font-size:8 ; "></td>
                                                <td style=" width:10%  ; text-align:right  ; font-size:8 ; "></td>
                                                <td style=" width:13%  ; text-align:right  ; font-size:8 ; "></td>
                                            </tr>
                                            <tr>
                                                <td style=" width:16%  ; text-align:left   ; font-size:8 ; ">Leg. Hol (H)</td>
                                                <td style=" width: 6%  ; text-align:right  ; font-size:8 ; ">'.$legal_overtime_unit.'</td>
                                                <td style=" width:10%  ; text-align:right  ; font-size:8 ; border-right-width: 1px ; ">'.number_format($legal_overtime,2,'.',',').'</td>
                                                <td style=" width:12%  ; text-align:right  ; font-size:8 ; "></td>
                                                <td style=" width:10%  ; text-align:right  ; font-size:8 ; border-right-width: 1px ; "></td>
                                                <td style=" width: 6%  ; text-align:left   ; font-size:8 ; border-right-width: 1px ; "></td>
                                                <td style=" width:10%  ; text-align:right  ; font-size:8 ; border-right-width: 1px ; "></td>
                                                <td style=" width:10%  ; text-align:right  ; font-size:8 ; "></td>
                                                <td style=" width:10%  ; text-align:right  ; font-size:8 ; "></td>
                                                <td style=" width:13%  ; text-align:right  ; font-size:8 ; "></td>
                                            </tr>
                                            <tr>
                                                <td style=" width:16%  ; text-align:left   ; font-size:8 ; ">Leave</td>
                                                <td style=" width: 6%  ; text-align:right  ; font-size:8 ; "></td>
                                                <td style=" width:10%  ; text-align:right  ; font-size:8 ; border-right-width: 1px ; "></td>
                                                <td style=" width:12%  ; text-align:right  ; font-size:8 ; "></td>
                                                <td style=" width:10%  ; text-align:right  ; font-size:8 ; border-right-width: 1px ; "></td>
                                                <td style=" width: 6%  ; text-align:left   ; font-size:8 ; border-right-width: 1px ; border-bottom-width: 1px ; ">Retro:</td>
                                                <td style=" width:10%  ; text-align:right  ; font-size:8 ; border-right-width: 1px ; "></td>
                                                <td style=" width:10%  ; text-align:right  ; font-size:8 ; "></td>
                                                <td style=" width:10%  ; text-align:right  ; font-size:8 ; "></td>
                                                <td style=" width:13%  ; text-align:right  ; font-size:8 ; "></td>
                                            </tr>
                                            <tr>
                                                <td style=" width:16%  ; text-align:left   ; font-size:8 ; ">Absences</td>
                                                <td style=" width: 6%  ; text-align:right  ; font-size:8 ; ">'.$absent_amt_unit.'</td>
                                                <td style=" width:10%  ; text-align:right  ; font-size:8 ; border-right-width: 1px ; ">'.number_format($$absent_amt,2,'.',',').'</td>
                                                <td style=" width:12%  ; text-align:right  ; font-size:8 ; "></td>
                                                <td style=" width:10%  ; text-align:right  ; font-size:8 ; border-right-width: 1px ; "></td>
                                                <td style=" width: 6%  ; text-align:left   ; font-size:8 ; border-right-width: 1px ; ">A</td>
                                                <td style=" width:10%  ; text-align:right  ; font-size:8 ; border-right-width: 1px ; "></td>
                                                <td style=" width:10%  ; text-align:right  ; font-size:8 ; "></td>
                                                <td style=" width:10%  ; text-align:right  ; font-size:8 ; "></td>
                                                <td style=" width:13%  ; text-align:right  ; font-size:8 ; "></td>
                                            </tr>
                                            <tr>
                                                <td style=" width:16%  ; text-align:left   ; font-size:8 ; ">Tardiness</td>
                                                <td style=" width: 6%  ; text-align:right  ; font-size:8 ; ">'.$tardiness_amt_unit.'</td>
                                                <td style=" width:10%  ; text-align:right  ; font-size:8 ; border-right-width: 1px ; ">'.number_format($tardiness_amt,2,'.',',').'</td>
                                                <td style=" width:12%  ; text-align:right  ; font-size:8 ; "></td>
                                                <td style=" width:10%  ; text-align:right  ; font-size:8 ; border-right-width: 1px ; "></td>
                                                <td style=" width: 6%  ; text-align:left   ; font-size:8 ; border-right-width: 1px ; ">B</td>
                                                <td style=" width:10%  ; text-align:right  ; font-size:8 ; border-right-width: 1px ; "></td>
                                                <td style=" width:10%  ; text-align:right  ; font-size:8 ; "></td>
                                                <td style=" width:10%  ; text-align:right  ; font-size:8 ; "></td>
                                                <td style=" width:13%  ; text-align:right  ; font-size:8 ; "></td>
                                            </tr>
                                            <tr>
                                                <td style=" width:16%  ; text-align:left   ; font-size:8 ; ">Adjustment</td>
                                                <td style=" width: 6%  ; text-align:right  ; font-size:8 ; "></td>
                                                <td style=" width:10%  ; text-align:right  ; font-size:8 ; border-right-width: 1px ; "></td>
                                                <td style=" width:12%  ; text-align:right  ; font-size:8 ; "></td>
                                                <td style=" width:10%  ; text-align:right  ; font-size:8 ; border-right-width: 1px ;  "></td>
                                                <td style=" width: 6%  ; text-align:left   ; font-size:8 ; border-right-width: 1px ; ">C</td>
                                                <td style=" width:10%  ; text-align:right  ; font-size:8 ; border-right-width: 1px ; "></td>
                                                <td style=" width:10%  ; text-align:right  ; font-size:8 ; "></td>
                                                <td style=" width:10%  ; text-align:right  ; font-size:8 ; "></td>
                                                <td style=" width:13%  ; text-align:right  ; font-size:8 ; "></td>
                                            </tr>
                                            <tr>
                                                <td style=" width:16%  ; text-align:left   ; font-size:8 ; ">Remarks:</td>
                                                <td style=" width: 6%  ; text-align:right  ; font-size:8 ; "></td>
                                                <td style=" width:10%  ; text-align:right  ; font-size:8 ; border-right-width: 1px ; "></td>
                                                <td style=" width:12%  ; text-align:right  ; font-size:8 ; border-bottom-width: 1px ; "></td>
                                                <td style=" width:10%  ; text-align:right  ; font-size:8 ; border-right-width: 1px ; border-bottom-width: 1px ; "></td>
                                                <td style=" width: 6%  ; text-align:left   ; font-size:8 ; border-right-width: 1px ; border-bottom-width: 1px ; "></td>
                                                <td style=" width:10%  ; text-align:right  ; font-size:8 ; border-right-width: 1px ; border-bottom-width: 1px ; "></td>
                                                <td style=" width:10%  ; text-align:right  ; font-size:8 ; border-bottom-width: 1px ; "></td>
                                                <td style=" width:10%  ; text-align:right  ; font-size:8 ; border-bottom-width: 1px ; "></td>
                                                <td style=" width:13%  ; text-align:right  ; font-size:8 ; border-bottom-width: 1px ; "></td>
                                            </tr>
                                            <tr>
                                                <td style=" width:16%  ; text-align:left   ; font-size:8 ; "></td>
                                                <td style=" width: 6%  ; text-align:right  ; font-size:8 ; "></td>
                                                <td style=" width:10%  ; text-align:right  ; font-size:8 ; border-right-width: 1px ; "></td>
                                                <td style=" width:20%  ; text-align:left   ; font-size:8 ; ">GROSS PAY</td>
                                                <td style=" width: 2%  ; text-align:right  ; font-size:8 ; ">:</td>
                                                <td style=" width:16%  ; text-align:right  ; font-size:8 ; ">'.number_format($gross_amt->amount,2,'.',',').'</td>
                                                <td style=" width:10%  ; text-align:right  ; font-size:8 ; "></td>
                                                <td style=" width:10%  ; text-align:right  ; font-size:8 ; "></td>
                                                <td style=" width:13%  ; text-align:right  ; font-size:8 ; "></td>
                                            </tr>
                                            <tr>
                                                <td style=" width:16%  ; text-align:left   ; font-size:8 ; "></td>
                                                <td style=" width: 6%  ; text-align:right  ; font-size:8 ; "></td>
                                                <td style=" width:10%  ; text-align:right  ; font-size:8 ; border-right-width: 1px ; "></td>
                                                <td style=" width:20%  ; text-align:left   ; font-size:8 ; ">TOTAL DEDUCTION</td>
                                                <td style=" width: 2%  ; text-align:right  ; font-size:8 ; ">:</td>
                                                <td style=" width:16%  ; text-align:right  ; font-size:8 ; ">'.number_format($tot_ded_amt->amount,2,'.',',').'</td>
                                                <td style=" width:10%  ; text-align:right  ; font-size:8 ; "></td>
                                                <td style=" width:10%  ; text-align:right  ; font-size:8 ; "></td>
                                                <td style=" width:13%  ; text-align:right  ; font-size:8 ; "></td>
                                            </tr>
                                            <tr>
                                                <td style=" width:16%  ; text-align:left   ; font-size:8 ; "></td>
                                                <td style=" width: 6%  ; text-align:right  ; font-size:8 ; "></td>
                                                <td style=" width:10%  ; text-align:right  ; font-size:8 ; border-right-width: 1px ; "></td>
                                                <td style=" width:20%  ; text-align:left   ; font-size:8 ; ">NET PAY</td>
                                                <td style=" width: 2%  ; text-align:right  ; font-size:8 ; ">:</td>
                                                <td style=" width:16%  ; text-align:right  ; font-size:8 ; ">'.number_format($netpay_amt,2,'.',',').'</td>
                                                <td style=" width:10%  ; text-align:right  ; font-size:8 ; "></td>
                                                <td style=" width:10%  ; text-align:right  ; font-size:8 ; ">Received By</td>
                                                <td style=" width:13%  ; text-align:right  ; font-size:8 ; "></td>
                                            </tr>
                                            <tr>
                                                <td style=" width:100% ; font-size:2 ; "></td>
                                            </tr>
                                            <tr>
                                                <td style=" width:100% ; text-align:left   ; ">Note: Keep this pay slip. It is your record of earning and deductions</td>
                                            </tr>
                                            <tr><td></td></tr><tr><td></td></tr>
                                            
                                        </table>';
                            $count++;
                        }
                        $this->pdf->writeHTML($xcel_hed, true, false, true, false, '');
                    }
                }
                break;
            default:
                $emp_separate = explode(',', $employee_id);
                reset($emp_separate);
                foreach ($emp_separate as $key => $value) {

                    $emp_count = $this->db->query('SELECT payroll_date FROM '.$this->db->dbprefix('payroll_closed_transaction')." WHERE payroll_date = '{$payroll_date}' AND employee_id = {$value}")->num_rows();
                    
                    if($emp_count > 0){
                        $this->pdf->addPage('F', 'LEGAL', true);
                        $this->pdf->SetFontSize( 8 );
                        
                        $company_setting_res = $this->db->query('SELECT * FROM '.$this->db->dbprefix('user_company').' WHERE company_id = "'.$company_id.'"')->row();
                        $company = $company_setting_res->company;

                        $payroll_period_res = $this->db->query('SELECT date_from, date_to FROM '.$this->db->dbprefix('payroll_period')." WHERE payroll_date ='{$payroll_date}'")->row();
                        
                        $period_from = $payroll_period_res->date_from;
                        $period_to = $payroll_period_res->date_to;

                        $user_dtl_res = $this->db->query("  SELECT *,b.tin as tin_id,  a.aux, c.department as department, d.position as position 
                                                            FROM {$this->db->dbprefix}user a LEFT JOIN {$this->db->dbprefix}employee b ON  b.employee_id = a.employee_id 
                                                            LEFT JOIN {$this->db->dbprefix}user_company_department c ON a.department_id = c.department_id 
                                                            LEFT JOIN {$this->db->dbprefix}user_position d ON a.position_id = d.position_id".' WHERE a.employee_id = ("'.$value.'")')->row();
                        
                        $emp_id = $user_dtl_res->employee_id;
                        $emp_name = $user_dtl_res->lastname.', '.$user_dtl_res->firstname.' '.$user_dtl_res->aux.' '.$user_dtl_res->middleinitial;
                        $dept = $user_dtl_res->department;
                        $post = $user_dtl_res->position;
                        $tin = $user_dtl_res->tin_id;

                        $earning_dtl_res = "SELECT t.transaction_label as transaction_label, ct.quantity as quantity, ct.amount as amount 
                                            FROM {$this->db->dbprefix}payroll_closed_transaction ct
                                            LEFT JOIN {$this->db->dbprefix}payroll_transaction t ON ct.transaction_id = t.transaction_id
                                            LEFT JOIN {$this->db->dbprefix}payroll_transaction_type tt ON t.transaction_type_id = tt.transaction_type_id
                                            WHERE ct.employee_id = ({$value}) AND ct.payroll_date = '{$payroll_date}' AND tt.operation ='+'
                                            ORDER BY tt.sort_order";
                        $earning_dtl_res_x = $this->db->query($earning_dtl_res);
                        
                        $deduction_dtl_res = "SELECT t.transaction_label as transaction_label, ct.quantity as quantity, ct.amount as amount
                                            FROM {$this->db->dbprefix}payroll_closed_transaction ct
                                            LEFT JOIN {$this->db->dbprefix}payroll_transaction t ON ct.transaction_id = t.transaction_id
                                            LEFT JOIN {$this->db->dbprefix}payroll_transaction_type tt ON t.transaction_type_id = tt.transaction_type_id
                                            WHERE ct.employee_id = ({$value}) AND ct.payroll_date = '{$payroll_date}' AND tt.operation = '-'
                                            ORDER BY tt.sort_order";
                        $deduction_dtl_res_x = $this->db->query($deduction_dtl_res);

                        $page_new ='
                            <table>
                                <tr>
                                    <td colspan="17" style="text-align:center;">
                                        <h3>'.$title.' </h3>
                                    </td>
                                </tr>
                               <tr><td></td></tr>
                            </table>
                            <table style="width: 100%">
                                    <tr>
                                        <td style="border:1px solid black;text-align:left;">Employee No.:</td>
                                        <td colspan="2.5" style="border:1px solid black;text-align:left;">'.$emp_id.'</td>
                                        <td style="border:1px solid black;text-align:left;">Company:</td>
                                        <td colspan="2.5" style="border:1px solid black;text-align:left;">'.$company.'</td>
                                    </tr>    
                                    <tr>
                                        <td style="border:1px solid black;text-align:left;">Employee Name:</td>
                                        <td colspan="2.5" style="border:1px solid black;text-align:left;">'.$emp_name.'</td>
                                        <td style="border:1px solid black;text-align:left;">Department:</td>
                                        <td colspan="2.5" style="border:1px solid black;text-align:left;">'.$dept.'</td>
                                    </tr>
                                    <tr>
                                        <td style="border:1px solid black;text-align:left;">Position:</td>
                                        <td colspan="2.5" style="border:1px solid black;text-align:left;">'.$post.'</td>
                                        <td style="border:1px solid black;text-align:left;">Period:</td>
                                        <td colspan="2.5" style="border:1px solid black;text-align:left;">'.date('d-M-Y',strtotime($period_from)).' - '.date('d-M-Y',strtotime($period_to)).'</td>
                                    </tr>
                                    <tr>
                                        <td style="border:1px solid black;text-align:left;">TIN:</td>
                                        <td colspan="2.5" style="border:1px solid black;text-align:left;">'.$tin.'</td>
                                        <td style="border:1px solid black;text-align:left;">Payroll Date:</td>
                                        <td colspan="2.5" style="border:1px solid black;text-align:left;">'.date('d-M-Y',strtotime($payroll_date)).'</td>
                                    </tr>
                                </table>
                                <div>
                            <table>
                                <tr>
                                <td colspan="2.5"><h2>Earnings</h2></td>
                                <td colspan="1" style="text-align:right"><h4>Units</h4></td>
                                <td colspan="2" style="text-align:right"><h4>Amount</h4></td>
                                </tr>
                            </table>
                            <hr/> </div>';

                        foreach($earning_dtl_res_x->result() as $ear_key=>$ear_value)
                        {
                            $transaction_label_ear = $ear_value->transaction_label;
                            $quantity_ear = $ear_value->quantity;
                            $amount_ear = $ear_value->amount; 
                            
                            $page_new .='<table>
                                <tr>
                                    <td colspan="2.5">
                                    '.$transaction_label_ear.'
                                    </td>
                                    <td colspan="1" style="text-align:right">
                                    '.$quantity_ear.'
                                    </td>
                                    <td colspan="2" style="text-align:right">
                                    '.$amount_ear.'
                                    </td>
                                </tr>
                            </table>';
                        }

                        $page_new .='<div>
                        <table>
                            <tr>
                                <td colspan="2.5"><h2>Deductions</h2></td>
                                <td colspan="1" style="text-align:right"><h4></h4></td>
                                <td colspan="2" style="text-align:right"><h4></h4></td>
                            </tr>
                        </table>
                        <hr/> </div>';

                        foreach($deduction_dtl_res_x->result() as $ded_key=>$ded_value)
                        {
                            $transaction_label_ded = $ded_value->transaction_label;
                            $quantity_ded = $ded_value->quantity;
                            $amount_ded = $ded_value->amount; 
                        
                            $page_new .='<table>
                                <tr>
                                    <td colspan="2.5">
                                    '.$transaction_label_ded.'
                                    </td>
                                    <td colspan="1" style="text-align:right">
                                    '.$quantity_ded.'
                                    </td>
                                    <td colspan="2" style="text-align:right">
                                    '.$amount_ded.'
                                    </td>
                                </tr>
                            </table>';
                        }
                    }
                    $this->pdf->writeHTML($page_new, true, false, true, false, '');
                }
                break;
        }
    }

    function export_coinage($company_id, $employee_id, $payroll_date, $title){

        $payroll_period_res = $this->db->query('SELECT date_from, date_to FROM '.$this->db->dbprefix('payroll_period')." WHERE payroll_date ='{$payroll_date}'")->row();
            
        $period_from = $payroll_period_res->date_from;
        $period_to = $payroll_period_res->date_to;
        
        $month_set = '01-'.str_pad($month, 2, "0", STR_PAD_LEFT).'-'.$year;
        $xcel = '<table>
                    <tr>
                        <td colspan="17" style="text-align:center;">
                            '.$title.' from '.date('F d, Y',strtotime($period_from)).' to '.date('F d, Y',strtotime($period_to)).'
                        </td>
                    </tr>
                    <tr><td></td></tr>
                </table>';
        $xcel .= '<table>
                    <tr>
                        <td style="border:1px solid black;text-align:center;">Employee ID</td>
                        <td colspan="2" style="border:1px solid black;text-align:center;">Employee</td>
                        <td style="border:1px solid black;text-align:center;vertical-align:top;">Netpay</td>
                        <td style="border:1px solid black;text-align:center;vertical-align:top;">1000</td>
                        <td style="border:1px solid black;text-align:center;vertical-align:top;">500</td>
                        <td style="border:1px solid black;text-align:center;vertical-align:top;">100</td>
                        <td style="border:1px solid black;text-align:center;vertical-align:top;">50</td>
                        <td style="border:1px solid black;text-align:center;vertical-align:top;">20</td>
                        <td style="border:1px solid black;text-align:center;vertical-align:top;">10</td>
                        <td style="border:1px solid black;text-align:center;vertical-align:top;">5</td>
                        <td style="border:1px solid black;text-align:center;vertical-align:top;">1 Peso</td>
                        <td style="border:1px solid black;text-align:center;vertical-align:top;">25c</td>
                        <td style="border:1px solid black;text-align:center;vertical-align:top;">10c</td>
                        <td style="border:1px solid black;text-align:center;vertical-align:top;">5c</td>
                        <td style="border:1px solid black;text-align:center;vertical-align:top;">1c</td>
                        <td style="border:1px solid black;text-align:center;vertical-align:top;">Computed</td>
                    </tr>                    
                </table>';
        return $xcel;
    }

    function export_remittance($company_id, $employee_id, $payroll_date, $title){
        
        // SELECT BANK
        $company = $this->db->query('SELECT * FROM '.$this->db->dbprefix('user_company').' WHERE company_id = "'.$company_id.'"')->row();
        $bank_id = $company->bank_id;

        $bank = $this->db->query('SELECT * FROM '.$this->db->dbprefix('bank').' WHERE bank_id = "'.$bank_id.'"')->row();
        $bank_code_numeric = $bank->bank_code_numeric;
        $batch_no = $bank->batch_no;
        $branch_code = $bank->branch_code;
        $account_no = $bank->account_no;
        $ceiling_amount = str_replace('.','',$bank->ceiling_amount);

        $this->db->where('payroll_date',$payroll_date);
        $period = $this->db->get('payroll_period')->row();

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

        $total_payroll_qry = "SELECT sum(bank_account_no) AS bank_total, sum(amount) AS amount_total, 
                    sum( ( (RIGHT(LEFT(bank_account_no,6),2)*amount) + (RIGHT(LEFT(bank_account_no,8),2)*amount) + (RIGHT(LEFT(bank_account_no,10),2)*amount) ) )AS hash_total
                    FROM {$this->db->dbprefix}$transaction p
                    LEFT JOIN {$this->db->dbprefix}employee e ON e.employee_id = p.employee_id
                    LEFT JOIN {$this->db->dbprefix}user u ON u.employee_id = p.employee_id
                    WHERE payroll_date = '{$payroll_date}' AND transaction_code = 'NETPAY' $employee $company";

        $total_payroll_res = $this->db->query($total_payroll_qry)->row();

        $amount_total = str_replace('.','',$total_payroll_res->amount_total);
        $bank_total = str_replace('.','',$total_payroll_res->bank_total);
        $hash_total = str_replace('.','',$total_payroll_res->hash_total);

        $payroll_qry = "SELECT bank_account_no, amount, 
                    ( (RIGHT(LEFT(bank_account_no,6),2)*amount) + (RIGHT(LEFT(bank_account_no,8),2)*amount) + (RIGHT(LEFT(bank_account_no,10),2)*amount) ) AS hash
                    FROM {$this->db->dbprefix}$transaction p
                    LEFT JOIN {$this->db->dbprefix}employee e ON e.employee_id = p.employee_id
                    LEFT JOIN {$this->db->dbprefix}user u ON u.employee_id = p.employee_id
                    WHERE payroll_date = '{$payroll_date}' AND transaction_code = 'NETPAY' $employee $company ";
        $payroll_res = $this->db->query($payroll_qry); 
        
        if($bank_id == 3){
            /* BPI */
            // START of To Disk //
            $File = $bank_code_numeric; 
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
            
            $Data_header .= 'H'.str_pad($bank_code_numeric,5,"0",STR_PAD_LEFT).date('mdy',strtotime($payroll_date)).str_pad($batch_no,2,"0",STR_PAD_LEFT).'1'.str_pad($account_no,10,"0",STR_PAD_LEFT).substr($account_no, 0, -7).str_pad($ceiling_amount,12,"0",STR_PAD_LEFT).str_pad($amount_total,12,"0",STR_PAD_LEFT).$bank->bank_type."\r\n";
            
            fwrite($Handle, $Data_header); 
            

            $total_line = 0;
            foreach ($payroll_res->result() as $key => $value) {

                $Data = "D".str_pad($bank_code_numeric,5,"0",STR_PAD_LEFT).date('mdy',strtotime($payroll_date)).str_pad($batch_no,2,"0",STR_PAD_LEFT).'3'.str_pad($value->bank_account_no,10,"0",STR_PAD_LEFT).str_pad(str_replace('.','',$value->amount),12,"0",STR_PAD_LEFT).str_pad(str_replace('.', '', number_format($value->hash,2,'.','')),12,"0",STR_PAD_LEFT)."\r\n"; 
                fwrite($Handle, $Data); 

                $total_line++;
            }
                                   
            $Data_footer = "T".str_pad($bank_code_numeric,5,"0",STR_PAD_LEFT).date('mdy',strtotime($payroll_date)).str_pad($batch_no,2,"0",STR_PAD_LEFT).'2'.str_pad($account_no,10,"0",STR_PAD_LEFT).str_pad($bank_total,15,"0",STR_PAD_LEFT).str_pad($amount_total,15,"0",STR_PAD_LEFT).str_pad($hash_total,18,"0",STR_PAD_LEFT).str_pad($total_line,5,"0",STR_PAD_LEFT)."\r\n";
            fwrite($Handle, $Data_footer); 
            
            fclose($Handle); 
            readfile($File);
            exit();
            // END of To Disk //
        }
        if($bank_id == 4){
            /* METROBANK (MBTC) */
            // START of To Disk //
            $File = $bank_code_numeric; 
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
            // END of To Disk //
        }
    }
    
    function export_final_pay($company_id, $employee_id, $payroll_date, $title){
    }

}

/* End of file */
/* Location: system/application */
?>
