<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class payslip_control_total extends my_controller
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

    

    function export_report(){
        
        $company_id = $_POST['company_id'];
        $code_status_id = $_POST['code_status_id'];
        $payroll_date = date("Y-m-d",strtotime($_POST['payroll_date']));
        $this->load->library('pdf');
        $html = $this->export_employee($code_status_id, $company_id, $employee_id, $payroll_date, $title);        
        $title = "Employee Records";
        
        $this->pdf->Output(ucwords(str_replace(" ","_",$title))."_" . date('dmYHis') . '.pdf', 'I');
    }
    
    function export_employee($code_status_id, $company_id, $employee_id, $payroll_date, $title){

        $company_setting_res = $this->db->query('SELECT * FROM '.$this->db->dbprefix('user_company').' WHERE company_id = "'.$company_id.'"')->row();

        // check if employee_id is null or empty
        if(!empty($company_id)){

            $company = " AND company_id IN ($company_id)";
        }
        if(!empty($employee_id)){
            
            $employee = " AND u.employee_id IN ($employee_id)";   
        }

        if(!empty($code_status_id)){
            $code_status = 'AND pct.code_status_id = '.$code_status_id;
        }

        $proj_qry = $this->db->query("SELECT distinct pct.code_status_id FROM {$this->db->dbprefix}payroll_closed_transaction pct
                                    LEFT JOIN {$this->db->dbprefix}user u on u.employee_id = pct.employee_id
                                    WHERE 1 AND pct.payroll_date = '{$payroll_date}' $company $employee $code_status");
        
        $proj_cnt = $proj_qry->num_rows();
        $proj_record = $proj_qry->result();

        if( $proj_cnt > 0 ){
            foreach ($proj_record as $key => $proj) {

                $dtl_cnt = $this->db->query("SELECT distinct pct.employee_id FROM {$this->db->dbprefix}payroll_closed_transaction pct
                                    WHERE pct.payroll_date = '{$payroll_date}' $code_status ")->num_rows();

                $mdate = getdate(date("U"));
                $mdate = "$mdate[weekday], $mdate[month] $mdate[mday], $mdate[year]";

                $cs = $this->db->query("select code_status from {$this->db->dbprefix}code_status where code_status_id = '{$code_status_id}' ")->row();

                //BASIC
                $basic = $this->db->query(" SELECT sum(amount) as amount FROM {$this->db->dbprefix}payroll_closed_transaction pct
                                            WHERE payroll_date = '{$payroll_date}' AND transaction_code IN ('SALARY') $code_status ")->row();
                //ABSENT
                $absences = $this->db->query("SELECT sum(amount) as amount FROM {$this->db->dbprefix}payroll_closed_transaction pct
                                            WHERE payroll_date = '{$payroll_date}' AND transaction_code IN ('ABSENCES', 'LWOP') $code_status")->row();

                //TARDY
                $tardy = $this->db->query(" SELECT sum(amount) as amount FROM {$this->db->dbprefix}payroll_closed_transaction pct
                                            WHERE payroll_date = '{$payroll_date}' AND transaction_code IN ('DEDUCTION_LATE') $code_status ")->row();

                //UNDERTIME
                $undertime = $this->db->query("SELECT sum(amount) as amount FROM {$this->db->dbprefix}payroll_closed_transaction pct
                                            WHERE payroll_date = '{$payroll_date}' AND transaction_code IN ('DEDUCTION_UNDERTIME') $code_status ")->row();

                $tot_inc = $this->db->query("SELECT  SUM(amount) as amount
                                            FROM {$this->db->dbprefix}payroll_closed_transaction pct
                                            LEFT JOIN {$this->db->dbprefix}payroll_transaction pt ON pct.transaction_id = pt.transaction_id
                                            WHERE payroll_date = '{$payroll_date}' AND pct.transaction_type_id IN (1,2) AND pct.transaction_code != 'salary' AND pt.transaction_class_id != 10 $code_status ")->row();

                $ovrtme = $this->db->query("SELECT  SUM(amount) as amount
                                            FROM {$this->db->dbprefix}payroll_closed_transaction pct
                                            LEFT JOIN {$this->db->dbprefix}payroll_transaction pt ON pct.transaction_id = pt.transaction_id
                                            WHERE payroll_date = '{$payroll_date}' AND pct.transaction_type_id IN (1,2) AND pct.transaction_code != 'salary' AND pt.transaction_class_id = 10 $code_status ")->row();

                $total_1 = $tot_inc->amount + $basic->amount - ($tardy->amount + $undertime->amount + $absences->amount) + $ovrtme->amount;

                $income = $this->db->query("SELECT pt.transaction_label, SUM(amount) as amount
                                            FROM {$this->db->dbprefix}payroll_closed_transaction pct
                                            LEFT JOIN {$this->db->dbprefix}payroll_transaction pt ON pct.transaction_id = pt.transaction_id
                                            WHERE payroll_date = '{$payroll_date}' AND pct.transaction_type_id IN (1,2) AND pct.transaction_code != 'salary' AND pt.transaction_class_id != 10 $code_status
                                            GROUP BY pt.transaction_label")->result();

                $netpay = $this->db->query("SELECT sum(amount) as amount FROM {$this->db->dbprefix}payroll_closed_transaction pct
                                            WHERE payroll_date = '{$payroll_date}' AND transaction_code IN ('NETPAY') $code_status ")->row();

                $philhealth = $this->db->query("SELECT sum(amount) as amount FROM {$this->db->dbprefix}payroll_closed_transaction pct
                                            WHERE payroll_date = '{$payroll_date}' AND transaction_code IN ('PHIC_EMP') $code_status ")->row();

                $pagibig = $this->db->query("SELECT sum(amount) as amount FROM {$this->db->dbprefix}payroll_closed_transaction pct
                                            WHERE payroll_date = '{$payroll_date}' AND transaction_code IN ('HDMF_EMP') $code_status ")->row();

                $sss = $this->db->query("   SELECT sum(amount) as amount FROM {$this->db->dbprefix}payroll_closed_transaction pct
                                            WHERE payroll_date = '{$payroll_date}' AND transaction_code IN ('SSS_EMP') $code_status ")->row();

                $whtax = $this->db->query(" SELECT sum(amount) as amount FROM {$this->db->dbprefix}payroll_closed_transaction pct
                                            WHERE payroll_date = '{$payroll_date}' AND transaction_code IN ('WHTAX') $code_status ")->row();

                $loan = $this->db->query("  SELECT pt.transaction_label, sum(amount) as amount FROM {$this->db->dbprefix}payroll_closed_transaction pct
                                            LEFT JOIN {$this->db->dbprefix}payroll_transaction pt on pct.transaction_id = pt.transaction_id
                                            WHERE payroll_date = '{$payroll_date}' AND pt.transaction_code != 'WHTAX' AND pt.transaction_type_id = 3 AND pt.transaction_class_id = 26 $code_status
                                            GROUP BY pt.transaction_label")->result();

                $oth_ded = $this->db->query("SELECT pt.transaction_label, sum(amount) as amount FROM {$this->db->dbprefix}payroll_closed_transaction pct
                                            LEFT JOIN {$this->db->dbprefix}payroll_transaction pt on pct.transaction_id = pt.transaction_id
                                            WHERE payroll_date = '{$payroll_date}' AND pt.transaction_code != 'WHTAX' AND pt.transaction_type_id = 3 AND pt.transaction_class_id != 26 $code_status
                                            GROUP BY pt.transaction_label")->result();

                $tot_loan = $this->db->query("SELECT sum(amount) as amount FROM {$this->db->dbprefix}payroll_closed_transaction pct
                                            LEFT JOIN {$this->db->dbprefix}payroll_transaction pt on pct.transaction_id = pt.transaction_id
                                            WHERE payroll_date = '{$payroll_date}' AND pt.transaction_code != 'WHTAX' AND pt.transaction_type_id = 3 AND pt.transaction_class_id = 26 $code_status ")->row();

                $tot_oth_ded = $this->db->query("SELECT sum(amount) as amount FROM {$this->db->dbprefix}payroll_closed_transaction pct
                                            LEFT JOIN {$this->db->dbprefix}payroll_transaction pt on pct.transaction_id = pt.transaction_id
                                            WHERE payroll_date = '{$payroll_date}' AND pt.transaction_code != 'WHTAX' AND pt.transaction_type_id = 3 AND pt.transaction_class_id != 26 $code_status ")->row();

                $total_2 = $netpay->amount + $philhealth->amount + $pagibig->amount + $sss->amount + $whtax->amount + $tot_loan->amount + $tot_oth_ded->amount; 

                if($dtl_cnt > 0){
                              

                    $xcel_hed = '
                        <table style="width:100%;">
                            <tr>
                                <td width="100%" style="font-size: 11; "><b>'.$company_setting_res->company.'</b></td>
                            </tr>
                            <tr><td width="100%" style="font-size:2; ">&nbsp;</td></tr>
                            <tr>
                                <td width="100%">CONTROL TOTAL</td>
                            </tr>
                            <tr>
                                <td width="100%" >FOR THE PERIOD : '.date("m/d/Y",strtotime($payroll_date)).'</td>
                            </tr>
                            <tr>
                                <td width="100%" style="text-align:Left;">AS OF '.$mdate.'</td>
                            </tr>
                            <tr>
                                <td width="100%"><strong>'.$cs->code_status.'</strong></td>
                            </tr>
                            <tr><td width="100%" style="font-size:3; ">&nbsp;</td></tr>
                            <tr> 
                                <td width="84%" style="font-size:2; border-bottom:5px solid black;">&nbsp;</td>
                            </tr>
                            <tr><td width="100%" style="font-size:3; ">&nbsp;</td></tr>
                            <tr>
                                <td width="2%">&nbsp;</td>
                                <td width="60%"  style="text-align:Left;">Basic Salary</td>
                                <td width="20%"  style="text-align:right;">'.( $basic->amount != "" ? number_format($basic->amount,2,'.',',') : "0.00" ).'</td>
                            </tr>
                            <tr>
                                <td width="2%">&nbsp;</td>
                                <td width="60%"  style="text-align:Left;">Absent</td>
                                <td width="20%"  style="text-align:right;"> -'.( $absences->amount != "" ? number_format($absences->amount,2,'.',',') : "0.00" ).'</td>
                            </tr>
                            <tr>
                                <td width="2%">&nbsp;</td>
                                <td width="60%"  style="text-align:Left;">Tardy</td>
                                <td width="20%"  style="text-align:right;"> -'.( $tardy->amount != "" ? number_format($tardy->amount,2,'.',',') : "0.00" ).'</td>
                            </tr>
                            <tr>
                                <td width="2%">&nbsp;</td>
                                <td width="60%"  style="text-align:Left;">Undertime</td>
                                <td width="20%"  style="text-align:right;"> -'.( $undertime->amount != "" ? number_format($undertime->amount,2,'.',',') : "0.00" ).'</td>
                            </tr>';

                    foreach ($income as $key => $inc) {

                            $xcel_hed.='
                                <tr>
                                    <td width="2%">&nbsp;</td>
                                    <td width="60%"  style="text-align:Left;">'.$inc->transaction_label.'</td>
                                    <td width="20%"  style="text-align:right;">'.( $inc->amount != "" ? number_format($inc->amount,2,'.',',') : "0.00" ).'</td>
                                </tr>';
                    }
                    if($ovrtme->amount > 0){
                        $xcel_hed.='
                                <tr>
                                    <td width="2%">&nbsp;</td>
                                    <td width="60%"  style="text-align:Left;">Overtime Pay</td>
                                    <td width="20%"  style="text-align:right;">'.( $ovrtme->amount != "" ? number_format($ovrtme->amount,2,'.',',') : "0.00" ).'</td>
                                </tr>';
                    }
                        $xcel_hed.='
                            <tr><td width="100%" style="font-size:3; ">&nbsp;</td></tr>
                            <tr>
                                <td width="2%" style="border-bottom:4px solid black; border-left:4px solid black; border-top:4px solid black; ">&nbsp;</td>
                                <td width="60%"  style="text-align:Left; border-bottom:4px solid black;  border-top:4px solid black;  ">TOTAL</td>
                                <td width="20%"  style="text-align:right; border-bottom:4px solid black; border-top:4px solid black;  ">'.( $total_1 != "" ? number_format($total_1,2,'.',',') : "0.00" ).'</td>
                                <td width="2%" style="border-bottom:4px solid black; border-right:4px solid black; border-top:4px solid black; ">&nbsp;</td>
                            </tr>
                            <tr><td width="100%" style="font-size:3; ">&nbsp;</td></tr>
                            <tr>
                                <td width="2%">&nbsp;</td>
                                <td width="60%"  style="text-align:Left;">Net Salary</td>
                                <td width="20%"  style="text-align:right;">'.( $netpay->amount != "" ? number_format($netpay->amount,2,'.',',') : "0.00" ).'</td>
                            </tr>
                            <tr>
                                <td width="2%">&nbsp;</td>
                                <td width="60%"  style="text-align:Left;">Employee MCR</td>
                                <td width="20%"  style="text-align:right;">'.( $philhealth->amount != "" ? number_format($philhealth->amount,2,'.',',') : "0.00" ).'</td>
                            </tr>
                            <tr>
                                <td width="2%">&nbsp;</td>
                                <td width="60%"  style="text-align:Left;">Employee Pag-Ibig</td>
                                <td width="20%"  style="text-align:right;">'.( $pagibig->amount != "" ? number_format($pagibig->amount,2,'.',',') : "0.00" ).'</td>
                            </tr>
                            <tr>
                                <td width="2%">&nbsp;</td>
                                <td width="60%"  style="text-align:Left;">Employee SSS</td>
                                <td width="20%"  style="text-align:right;">'.( $sss->amount != "" ? number_format($sss->amount,2,'.',',') : "0.00" ).'</td>
                            </tr>
                            <tr>
                                <td width="2%">&nbsp;</td>
                                <td width="60%"  style="text-align:Left;">Whtax</td>
                                <td width="20%"  style="text-align:right;">'.( $whtax->amount != "" ? number_format($whtax->amount,2,'.',',') : "0.00" ).'</td>
                            </tr>';
                    foreach ($loan as $key => $ln) {
                        $xcel_hed.='
                            <tr>
                                <td width="2%">&nbsp;</td>
                                <td width="60%"  style="text-align:Left;">'.$ln->transaction_label.'</td>
                                <td width="20%"  style="text-align:right;">'.( $ln->amount != "" ? number_format($ln->amount,2,'.',',') : "0.00" ).'</td>
                            </tr>';
                    }
                    foreach ($oth_ded as $key => $ded) {
                        $xcel_hed.='
                            <tr>
                                <td width="2%">&nbsp;</td>
                                <td width="60%"  style="text-align:Left;">'.$ded->transaction_label.'</td>
                                <td width="20%"  style="text-align:right;">'.( $ded->amount != "" ? number_format($ded->amount,2,'.',',') : "0.00" ).'</td>
                            </tr>';
                    }
                    $xcel_hed.='
                            <tr><td width="100%" style="font-size:3; ">&nbsp;</td></tr>
                            <tr>
                                <td width="2%" style="border-bottom:4px solid black; border-left:4px solid black; border-top:4px solid black; ">&nbsp;</td>
                                <td width="60%"  style="text-align:Left; border-bottom:4px solid black;  border-top:4px solid black;  ">TOTAL</td>
                                <td width="20%"  style="text-align:right; border-bottom:4px solid black; border-top:4px solid black;  ">'.( $total_2 != "" ? number_format($total_2,2,'.',',') : "0.00" ).'</td>
                                <td width="2%" style="border-bottom:4px solid black; border-right:4px solid black; border-top:4px solid black; ">&nbsp;</td>
                            </tr>
                            <tr><td width="100%" style="font-size:3; ">&nbsp;</td></tr>';
                        

                    $xcel_hed .= '
                            <tr>
                                <td width="2%" style="border-bottom:4px solid black; border-left:4px solid black; border-top:4px solid black; ">&nbsp;</td>
                                <td width="60%"  style="text-align:Left; border-bottom:4px solid black;  border-top:4px solid black;  ">NO. OF EMPLOYEES</td>
                                <td width="20%"  style="text-align:right; border-bottom:4px solid black; border-top:4px solid black;  ">'.( number_format($dtl_cnt,0,'.',',') ).'</td>
                                <td width="2%" style="border-bottom:4px solid black; border-right:4px solid black; border-top:4px solid black; ">&nbsp;</td>
                            </tr>
                            
                            <tr>
                                <td width="2%">&nbsp;</td>
                                <td width="60%">PREPARED BY:</td>
                                <td width="20%">REVIEWED BY:</td>
                            </tr>
                        </table>';

                }
                $this->pdf->SetMargins(10, 10, 10, true);   
                $this->pdf->SetAutoPageBreak(TRUE);
                $this->pdf->addPage('P', 'A4', true);    
                $this->pdf->SetFont( '', 'B', 9, '', false);  
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
