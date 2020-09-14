<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class custom_summary_per_paycode extends my_controller
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
        //Select Report type:
        $report_type = array("Summary Per Payment Code", "Bank and Cash Summary");
        $report_type_html = '<select id="report_type_id" name="report_type_id">';
            foreach($report_type as $report_type_id => $report_type_value){
                $report_type_html .= '<option value="'.$report_type_id.'">'.$report_type_value.'</option>';
            }
        $report_type_html .= '</select>'; 

        $tran_type = array("Current", "Historical");
        $tran_type_html = '<select id="tran_type_id" name="tran_type_id">';
            foreach($tran_type as $tran_type_id => $tran_type_value){
                $tran_type_html .= '<option value="'.$tran_type_id.'">'.$tran_type_value.'</option>';
            }
        $tran_type_html .= '</select>';

        $response->report_type_html = $report_type_html;
        $response->tran_type_html = $tran_type_html;
        $data['json'] = $response;
        $this->load->view('template/ajax', $data);  
    }

    function export_report(){

        $paycode_id = $_POST['paycode_id'];
        $tran_type = $_POST['tran_type_id'];
        $payroll_date = date("Y-m-d",strtotime($_POST['payroll_date']));
        $this->load->library('pdf');
        switch ($_POST['report_type_id']){
            case '0':
                $title = "SUMMARY PER PAYMENT CODE";
                $html = $this->export_per_paycode($paycode_id, $tran_type, $payroll_date, $title);
                break;
            case '1':
                $title = "BANK AND CASH SUMMARY";
                $html = $this->export_bank_summary($paycode_id, $tran_type, $payroll_date, $title);
                break;
        }
        $this->pdf->writeHTML($html, true, false, true, false, '');
        $this->pdf->Output(ucwords(str_replace(" ","_",$title))."_" . date('dmYHis') . '.pdf', 'I');
    }
    
    function export_per_paycode($paycode_id, $tran_type, $payroll_date, $title){
        
        switch ($tran_type) {
            case 0:
                $transaction = "payroll_current_transaction";
                $on_hold = 'AND pct.on_hold = 0';
                break;
            
            case 1:
                $transaction = "payroll_closed_transaction";
                break;
        }        
        $this->db->where('payroll_date',$payroll_date);
        $period = $this->db->get('payroll_period')->row();
        $date_period = date("m/d/Y",strtotime($period->date_from)).' TO '.date("m/d/Y",strtotime($period->date_to));

        $company_setting_res = $this->db->query('SELECT * FROM '.$this->db->dbprefix('user_company').' WHERE company_id = 1 ')->row();

        $paycode = $this->db->query("select paycode from {$this->db->dbprefix}payroll_paycode where paycode_id = $paycode_id")->row();

        $qry = $this->db->query("SELECT pt.transaction_label as label, SUM(amount) as amount FROM {$this->db->dbprefix}$transaction pct
                                LEFT JOIN {$this->db->dbprefix}payroll_transaction pt ON pct.transaction_id = pt.transaction_id
                                LEFT JOIN {$this->db->dbprefix}payroll_transaction_type ptt ON pt.transaction_type_id = ptt.transaction_type_id
                                LEFT JOIN hr_employee_work_assignment w ON w.employee_id = pct.employee_id
                                LEFT JOIN {$this->db->dbprefix}user u on u.employee_id = pct.employee_id
                                WHERE payroll_date = '{$payroll_date}' AND paycode_id = {$paycode_id} AND w.assignment = 1 AND u.inactive = 0 AND pct.deleted = 0 $on_hold
                                GROUP BY pct.transaction_code
                                ORDER BY ptt.operation, pt.transaction_label");
        $qry_cnt = $qry->num_rows();
        
        $mdate = getdate(date("U"));
        $mdate = "$mdate[weekday], $mdate[month] $mdate[mday], $mdate[year]";
        
        if( $qry_cnt > 0 ){

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
                        <td width="100%" style="font-size: 10; ">'.$title.'</td>
                    </tr>
                    <tr>
                        <td width="100%" >FOR THE PERIOD : '.$date_period.'</td>
                    </tr>
                    <tr>
                        <td width="100%" style="text-align:Left;">PAY CODE : '.$paycode->paycode.'</td>
                    </tr>
                    <tr>
                        <td width="100%" style="text-align:Left;">AS OF '.$mdate.'</td>
                    </tr>
                    <tr><td width="100%" style="font-size:8; "></td></tr>
                    <tr>
                        <td width="2%"></td>
                        <td width="60%"  style="text-align:center;"><b>TRANSACTIONS</b></td>
                        <td width="20%"  style="text-align:right;"><b>AMOUNT</b></td>
                    </tr>
                    <tr> 
                        <td width="84%" style="font-size:2; border-bottom:5px solid black;"></td>
                    </tr>
                    <tr><td width="100%" style="font-size:3; "></td></tr>';

            foreach ($qry->result() as $key => $value) {

                $xcel_hed.='
                            <tr>
                                <td width="2%"></td>
                                <td width="60%"  style="text-align:Left;">'.$value->label.'</td>
                                <td width="20%"  style="text-align:right;">'.( $value->amount != "" ? number_format($value->amount,2,'.',',') : "0.00" ).'</td>
                            </tr>';
            }
            $xcel_hed.='
                    <tr> 
                        <td width="84%" style="font-size:4; border-bottom:5px solid black;"></td>
                    </tr>
                    <tr><td width="100%" style="font-size:3; "></td></tr>

                </table>';
            $this->pdf->writeHTML($xcel_hed, true, false, true, false, '');
        }
        else{
            $this->pdf->SetMargins(10, 10, 10, true);   
            $this->pdf->SetAutoPageBreak(TRUE);
            $this->pdf->addPage('P', 'A4', true);    
            $this->pdf->SetFontSize( 8); 
            $this->pdf->writeHTML("No Record Found!", true, false, true, false, '');
        }
    }
    function export_bank_summary($paycode_id, $tran_type, $payroll_date, $title){
        
        switch ($tran_type) {
            case 0:
                $transaction = "payroll_current_transaction";
                $on_hold = 'AND pct.on_hold = 0';
                break;
            
            case 1:
                $transaction = "payroll_closed_transaction";
                break;
        }        
        $this->db->where('payroll_date',$payroll_date);

        $period = $this->db->get('payroll_period')->row();

        $date_period = date("m/d/Y",strtotime($period->date_from)).' TO '.date("m/d/Y",strtotime($period->date_to));

        $company_setting_res = $this->db->query('SELECT * FROM '.$this->db->dbprefix('user_company').' WHERE company_id = 1 ')->row();

        $paycode = $this->db->query("select paycode from {$this->db->dbprefix}payroll_paycode where paycode_id = $paycode_id")->row();

        $qry = $this->db->query("SELECT bank, COUNT( DISTINCT pct.employee_id ) as emp_cnt , sum( amount ) as amount
                                FROM {$this->db->dbprefix}$transaction pct
                                LEFT JOIN {$this->db->dbprefix}employee_payroll ep ON pct.employee_id = ep.employee_id
                                LEFT JOIN {$this->db->dbprefix}employee_work_assignment w ON pct.employee_id = w.employee_id
                                LEFT JOIN {$this->db->dbprefix}bank b on b.bank_id = ep.bank_id
                                LEFT JOIN {$this->db->dbprefix}user u on u.employee_id = pct.employee_id
                                WHERE payroll_date = '{$payroll_date}' AND pct.paycode_id = {$paycode_id} AND u.inactive = 0
                                AND payment_type_id = 1 AND transaction_code = 'netpay' AND w.assignment = 1 AND pct.deleted = 0 $on_hold
                                GROUP BY bank");
        $qry_cnt = $qry->num_rows();
        
        $cash = $this->db->query("SELECT COUNT( DISTINCT pct.employee_id ) as emp_cnt , sum( amount ) as amount
                                FROM {$this->db->dbprefix}$transaction pct
                                LEFT JOIN {$this->db->dbprefix}employee_payroll ep ON pct.employee_id = ep.employee_id
                                LEFT JOIN {$this->db->dbprefix}employee_work_assignment w ON pct.employee_id = w.employee_id
                                LEFT JOIN {$this->db->dbprefix}user u on u.employee_id = pct.employee_id
                                WHERE payroll_date = '{$payroll_date}' AND pct.paycode_id = {$paycode_id} AND u.inactive = 0
                                AND payment_type_id = 2 AND transaction_code = 'netpay' AND w.assignment = 1 AND pct.deleted = 0 $on_hold")->row();
        
        $mdate = getdate(date("U"));
        $mdate = "$mdate[weekday], $mdate[month] $mdate[mday], $mdate[year]";
        
        if( $qry_cnt > 0 ){
            $this->pdf->SetMargins(10, 10, 10 , true); 
            $this->pdf->addPage('P', 'A4', true);    
            $this->pdf->SetFontSize( 10);            

            $xcel_hed = '
                <table>
                    <tr>
                        <td width="100%" style="font-size: 11; "><b>'.$company_setting_res->company.'</b></td>
                    </tr>
                    <tr><td width="100%" style="font-size:2; "></td></tr>
                    <tr>
                        <td width="100%" style="font-size: 10; ">'.$title.'</td>
                    </tr>
                    <tr>
                        <td width="100%" >FOR THE PERIOD : '.$date_period.'</td>
                    </tr>
                    <tr>
                        <td width="100%" style="text-align:Left;">PAY CODE : '.$paycode->paycode.'</td>
                    </tr>
                    <tr>
                        <td width="100%" style="text-align:Left;">AS OF '.$mdate.'</td>
                    </tr>
                    <tr><td width="100%" style="font-size:5; "></td></tr>
                    <tr>
                                <td width="50%"  style="text-align:Left;"></td>
                                <td width="20%"  style="text-align:right;"><b>Amount</b></td>
                                <td width="20%"  style="text-align:right;"><b>No. of Employees</b></td>
                    </tr>
                    <tr> 
                        <td width="90%" style="font-size:2; border-bottom:5px solid black;"></td>
                    </tr>
                    <tr><td width="100%" style="font-size:3; "></td></tr>
                    <tr>
                        <td width="50%"  style="text-align:Left;">Cash</td>
                        <td width="20%"  style="text-align:right;">'.( $cash->amount != "" ? number_format($cash->amount,2,'.',',') : "0.00" ).'</td>
                        <td width="20%"  style="text-align:right;">'.( $cash->emp_cnt != "" ? $cash->emp_cnt : "0" ).'</td>
                    </tr>';

            foreach ($qry->result() as $key => $value) {

                $xcel_hed.='
                            <tr>
                                <td width="50%"  style="text-align:Left;">'.$value->bank.'</td>
                                <td width="20%"  style="text-align:right;">'.( $value->amount != "" ? number_format($value->amount,2,'.',',') : "0.00" ).'</td>
                                <td width="20%"  style="text-align:right;">'.( $value->emp_cnt != "" ? $value->emp_cnt : "0" ).'</td>
                            </tr>';
                $total_amount += $value->amount;
                $total_employee += $value->emp_cnt;
            }
            $xcel_hed.='
                    <tr> 
                        <td width="90%" style="font-size:4; border-bottom:5px solid black;"></td>
                    </tr>
                    <tr><td width="100%" style="font-size:3; "></td></tr>
                    <tr>
                        <td width="50%"  style="text-align:right;"><b>TOTAL</b></td>
                        <td width="20%"  style="text-align:right;"><b>'.number_format(($total_amount + $cash->amount) ,2,'.',',').'</b></td>
                        <td width="20%"  style="text-align:right;"><b>'.($total_employee + $cash->emp_cnt).'</b></td>
                    </tr>

                </table>';
            $this->pdf->writeHTML($xcel_hed, true, false, true, false, '');
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
