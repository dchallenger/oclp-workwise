<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Alphalist_report extends my_controller
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
        //$report_type = array("Schedule 7.1", "Schedule 7.2", "Schedule 7.3", "Schedule 7.3 Cont.", "Schedule 7.4", "Schedule 7.4 Cont.");
        $report_type = array("Terminated Within the Year", "Minimum Wage Earner ", "With No Previous Employer", "With No Previous Employer Cont.", "With Previous Employer", "With Previous Employer Cont.");
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

        // $date_from = date("Y-m-d",strtotime($_POST['date_range_from']));
        // $date_to = date("Y-m-d",strtotime($_POST['date_range_to']));  
        $payroll_date = $_POST['date']; 
        
        $payroll_date = 2013; 
        $this->load->library('pdf');

        switch ($_POST['report_type_id']) 
        {
            //summary
            case '0':
                $html = $this->export_schedule_7_1($company_id, $employee_id, $payroll_date, "Schedule 7.1");
                $title = "Schedule 7.1";
                break;            
            //earnings
            case '1':
                $html = $this->export_schedule_7_2($company_id, $employee_id, $payroll_date, "Schedule 7.2");
                $title = "Schedule 7.2";
                break;
            case '2':
            	$html = $this->export_schedule_7_3($company_id, $employee_id, $payroll_date, "Schedule 7.3");
                $title = "Schedule 7.3";
                break;
            case '3':
                $html = $this->export_schedule_7_3_cont($company_id, $employee_id, $payroll_date, "Schedule 7.3 (continuation)");
                $title = "Schedule 7.3 (continuation)";
                break;    
         	case '4':
            	$html = $this->export_schedule_7_4($company_id, $employee_id, $payroll_date, "Schedule 7.4");
                $title = "Schedule 7.4";
                break;
            case '5':
                $html = $this->export_schedule_7_4_cont($company_id, $employee_id, $payroll_date, "Schedule 7.4 (continuation)");
                $title = "Schedule 7.4 (continuation)";
                break;    
        }
        $this->pdf->writeHTML($html, true, false, true, false, '');
        $this->pdf->Output(ucwords(str_replace(" ","_",$title))."_" . date('dmYHis') . '.pdf', 'I');
    }

    function export_schedule_7_1($company_id, $employee_id, $payroll_date, $title){
        $this->pdf->SetMargins(10, 10, 10, true);
        $this->pdf->SetAutoPageBreak(TRUE);
        $this->pdf->addPage('L', 'FOLIO', true);
        $this->pdf->SetFontSize( 7);
        
        $schedule_7_1 ='
            <table style="width: 100%">
                <tr>
                    <td width="10%" style="border-bottom:1px solid black; border-top:1px solid black; border-left:1px solid black; text-align:left; vertical-align:top;"><strong>'.$title.'</strong></td>
                    <td width="90%" style="border-bottom:1px solid black; border-top:1px solid black; border-right:1px solid black; text-align:center; vertical-align:top;"><strong>ALPHALIST OF EMPLOYEES TERMINATED BEFORE DECEMBER 31 (Reported Under BIR Form 2316)</strong> </td>
                </tr>
                <tr>
                    <td width="5%"   style="border-top:1px solid black; border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">SEQ</td>
                    <td width="10%"  style="border-top:1px solid black; border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">TAXPAYER</td>
                    <td width="24%"  style="border-top:1px solid black; border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">NAME OF EMPLOYEES</td>
                    <td width="44%"  style="border-top:1px solid black; border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">(4) GROSS COMPENSATION INCOME</td>
                    <td width="10%"  style="border-top:1px solid black; border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;"></td>
                    <td width="7%"   style="border-top:1px solid black; border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;"></td>
                </tr>
                <tr>
                    <td width="5%"   style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">NO.</td>
                    <td width="10%"  style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">IDENTIFICATION</td>
                    <td width="8%"   style="border-right:1px solid black; border-left:1px solid black; border-top:1px solid black; text-align:center; vertical-align:top;">Last</td>
                    <td width="8%"   style="border-right:1px solid black; border-left:1px solid black; border-top:1px solid black; text-align:center; vertical-align:top;">First</td>
                    <td width="8%"   style="border-right:1px solid black; border-left:1px solid black; border-top:1px solid black; text-align:center; vertical-align:top;">Middle</td>
                    <td width="33%"  style="border-right:1px solid black; border-left:1px solid black; border-top:1px solid black; border-bottom:1px solid black; text-align:center; vertical-align:top;">NON-TAXABLE</td>
                    <td width="11%"  style="border-right:1px solid black; border-left:1px solid black; border-top:1px solid black; border-bottom:1px solid black; text-align:center; vertical-align:top;">TAXABLE</td>
                    <td width="10%"  style="border-right:1px solid black; border-left:1px solid black; border-top:1px solid black; text-align:center; vertical-align:top;">Amount of</td>
                    <td width="7%"   style="border-right:1px solid black; border-left:1px solid black; border-top:1px solid black; text-align:center; vertical-align:top;">Premium Paid</td>
                </tr>
                <tr>
                    <td width="5%"   style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;"></td>
                    <td width="10%"  style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">NUBMER</td>
                    <td width="8%"   style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">Name</td>
                    <td width="8%"   style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">Name</td>
                    <td width="8%"   style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">Name</td>
                    <td width="10%"  style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">13th Month Pay</td>
                    <td width="12%"  style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">SSS,GSIS,PHIC,& Pag-Ibig</td>
                    <td width="11%"  style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">Other Forms</td>
                    <td width="11%"  style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">Salaries & Other Forms</td>
                    <td width="10%"  style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">Exemption</td>
                    <td width="7%"   style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">on health and/or</td>
                </tr>
                <tr>
                    <td width="5%"   style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;"></td>
                    <td width="10%"  style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;"></td>
                    <td width="8%"   style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;"></td>
                    <td width="8%"   style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;"></td>
                    <td width="8%"   style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;"></td>
                    <td width="10%"  style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">& Other Benefits</td>
                    <td width="12%"  style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">Contributions, and Union Dues</td>
                    <td width="11%"  style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">of Compensation</td>
                    <td width="11%"  style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">of Compensation</td>
                    <td width="10%"  style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;"></td>
                    <td width="7%"   style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">Hospital</td>
                </tr>
                <tr>
                    <td width="5%"   style="border-right:1px solid black; border-left:1px solid black; border-bottom:3px solid black; text-align:center; vertical-align:top;">(1)</td>
                    <td width="10%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:3px solid black; text-align:center; vertical-align:top;">(2)</td>
                    <td width="8%"   style="border-right:1px solid black; border-left:1px solid black; border-bottom:3px solid black; text-align:center; vertical-align:top;">(3a)</td>
                    <td width="8%"   style="border-right:1px solid black; border-left:1px solid black; border-bottom:3px solid black; text-align:center; vertical-align:top;">(3b)</td>
                    <td width="8%"   style="border-right:1px solid black; border-left:1px solid black; border-bottom:3px solid black; text-align:center; vertical-align:top;">(3c)</td>
                    <td width="10%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:3px solid black; text-align:center; vertical-align:top;">4(a)</td>
                    <td width="12%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:3px solid black; text-align:center; vertical-align:top;">4(b)</td>
                    <td width="11%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:3px solid black; text-align:center; vertical-align:top;">4(c)</td>
                    <td width="11%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:3px solid black; text-align:center; vertical-align:top;">4(d)</td>
                    <td width="10%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:3px solid black; text-align:center; vertical-align:top;">(5)</td>  
                    <td width="7%"   style="border-right:1px solid black; border-left:1px solid black; border-bottom:3px solid black; text-align:center; vertical-align:top;">Insurance(6)</td>
                </tr>

        ';
        $count = 1;
        $qry = $this->db->query("SELECT * FROM ".$this->db->dbprefix('user').' WHERE company_id = 4');
            
        foreach ($qry->result() as $value) {
            
            $cont = $this->db->query('SELECT sum(amount) as amount FROM '.$this->db->dbprefix('payroll_closed_transaction').' WHERE transaction_id IN (49,50,52) AND employee_id = '.$value->employee_id.'')->row();
            if($cont->amount > 0){
                $cont_amount = $cont->amount;
            }
            else {
                $cont_amount = '0.00';   
            }

            $exempt = $this->db->query("SELECT amount FROM {$this->db->dbprefix}employee_payroll p JOIN {$this->db->dbprefix}taxcode t ON t.taxcode_id = p.taxcode_id WHERE p.employee_id = $value->employee_id")->row();
            if($cont->amount > 0){
                $exempt_amount = $exempt->amount;
            }
            else {
                $exempt_amount = '0.00';   
            }


            $schedule_7_1 .='
                    <tr>
                        <td width="5%"   style="border-left:1px solid black; border-right:1px solid black; border-top:1px solid black; border-bottom:1px solid black; text-align:left; vertical-align:top;">'.$count.'.</td>
                        <td width="10%"  style="border-left:1px solid black; border-right:1px solid black; border-top:1px solid black; border-bottom:1px solid black; text-align:center; vertical-align:top;">'.$value->tin.'</td>
                        <td width="8%"   style="border-left:1px solid black; border-right:1px solid black; border-top:1px solid black; border-bottom:1px solid black; text-align:left; vertical-align:top;">'.$value->lastname.'</td>
                        <td width="8%"   style="border-left:1px solid black; border-right:1px solid black; border-top:1px solid black; border-bottom:1px solid black; text-align:left; vertical-align:top;">'.$value->firstname.'</td>
                        <td width="8%"   style="border-left:1px solid black; border-right:1px solid black; border-top:1px solid black; border-bottom:1px solid black; text-align:left; vertical-align:top;">'.$value->middlename.'</td>
                        <td width="10%"  style="border-left:1px solid black; border-right:1px solid black; border-top:1px solid black; border-bottom:1px solid black; text-align:right; vertical-align:top;"></td>
                        <td width="12%"  style="border-left:1px solid black; border-right:1px solid black; border-top:1px solid black; border-bottom:1px solid black; text-align:right; vertical-align:top;">'.$cont_amount.'</td>
                        <td width="11%"  style="border-left:1px solid black; border-right:1px solid black; border-top:1px solid black; border-bottom:1px solid black; text-align:right; vertical-align:top;"></td>
                        <td width="11%"  style="border-left:1px solid black; border-right:1px solid black; border-top:1px solid black; border-bottom:1px solid black; text-align:right; vertical-align:top;"></td>
                        <td width="10%"  style="border-left:1px solid black; border-right:1px solid black; border-top:1px solid black; border-bottom:1px solid black; text-align:right; vertical-align:top;">'.$exempt_amount.'</td>  
                        <td width="7%"   style="border-left:1px solid black; border-right:1px solid black; border-top:1px solid black; border-bottom:1px solid black; text-align:right; vertical-align:top;"></td>
                    </tr>';
            $count++;
            $tot_cont += $cont->amount;
            $tot_exempt += $exempt->amount;
        }
        $schedule_7_1 .='
                    <tr>
                        <td width="5%"   style="border-left:1px solid black; border-right:1px solid black; border-top:1px solid black; border-bottom:1px solid black;"></td>
                        <td width="10%"  style="border-left:1px solid black; border-right:1px solid black; border-top:1px solid black; border-bottom:1px solid black;"></td>
                        <td width="8%"   style="border-left:1px solid black; border-right:1px solid black; border-top:1px solid black; border-bottom:1px solid black;"></td>
                        <td width="8%"   style="border-left:1px solid black; border-right:1px solid black; border-top:1px solid black; border-bottom:1px solid black;"></td>
                        <td width="8%"   style="border-left:1px solid black; border-right:1px solid black; border-top:1px solid black; border-bottom:1px solid black;"></td>
                        <td width="10%"  style="border-left:1px solid black; border-right:1px solid black; border-top:1px solid black; border-bottom:1px solid black;"></td>
                        <td width="12%"  style="border-left:1px solid black; border-right:1px solid black; border-top:1px solid black; border-bottom:1px solid black;"></td>
                        <td width="11%"  style="border-left:1px solid black; border-right:1px solid black; border-top:1px solid black; border-bottom:1px solid black;"></td>
                        <td width="11%"  style="border-left:1px solid black; border-right:1px solid black; border-top:1px solid black; border-bottom:1px solid black;"></td>
                        <td width="10%"  style="border-left:1px solid black; border-right:1px solid black; border-top:1px solid black; border-bottom:1px solid black;"></td>  
                        <td width="7%"   style="border-left:1px solid black; border-right:1px solid black; border-top:1px solid black; border-bottom:1px solid black;"></td>
                    </tr>
                    <tr>
                        <td width="5%"   style="border-left:1px solid black; border-right:1px solid black; border-top:1px solid black; border-bottom:1px solid black;"></td>
                        <td width="10%"  style="border-left:1px solid black; border-right:1px solid black; border-top:1px solid black; border-bottom:1px solid black;"></td>
                        <td width="8%"   style="border-left:1px solid black; border-right:1px solid black; border-top:1px solid black; border-bottom:1px solid black;"></td>
                        <td width="8%"   style="border-left:1px solid black; border-right:1px solid black; border-top:1px solid black; border-bottom:1px solid black;"></td>
                        <td width="8%"   style="border-left:1px solid black; border-right:1px solid black; border-top:1px solid black; border-bottom:1px solid black; text-align:right; vertical-align:top;">TOTAL</td>
                        <td width="10%"  style="border-left:1px solid black; border-right:1px solid black; border-top:1px solid black; border-bottom:1px solid black; vertical-align:top;">
                            <table>
                                <tr>
                                    <td width=2% style="text-align:left;">P</td>
                                    <td width=98% style="text-align:right;">'.number_format(0,2).'</td>
                               </tr>
                            </table>
                        </td>
                        <td width="12%"  style="border-left:1px solid black; border-right:1px solid black; border-top:1px solid black; border-bottom:1px solid black; vertical-align:top;">
                            <table>
                                <tr>
                                    <td width=2% style="text-align:left;">P</td>
                                    <td width=98% style="text-align:right;">'.number_format($tot_cont,2).'</td>
                               </tr>
                            </table>
                        </td>
                        <td width="11%"  style="border-left:1px solid black; border-right:1px solid black; border-top:1px solid black; border-bottom:1px solid black; vertical-align:top;">
                            <table>
                                <tr>
                                    <td width=2% style="text-align:left;">P</td>
                                    <td width=98% style="text-align:right;">'.number_format(0,2).'</td>
                               </tr>
                            </table>
                        </td>
                        <td width="11%"  style="border-left:1px solid black; border-right:1px solid black; border-top:1px solid black; border-bottom:1px solid black; vertical-align:top;">
                            <table>
                                <tr>
                                    <td width=2% style="text-align:left;">P</td>
                                    <td width=98% style="text-align:right;">'.number_format(0,2).'</td>
                               </tr>
                            </table>
                        </td>
                        <td width="10%"  style="border-left:1px solid black; border-right:1px solid black; border-top:1px solid black; border-bottom:1px solid black; vertical-align:top;">
                            <table>
                                <tr>
                                    <td width=2% style="text-align:left;">P</td>
                                    <td width=98% style="text-align:right;">'.number_format($tot_exempt,2).'</td>
                               </tr>
                            </table>
                        </td>  
                        <td width="7%"   style="border-left:1px solid black; border-right:1px solid black; border-top:1px solid black; border-bottom:1px solid black; vertical-align:top;">
                            <table>
                                <tr>
                                    <td width=2% style="text-align:left;">P</td>
                                    <td width=98% style="text-align:right;">'.number_format(0,2).'</td>
                               </tr>
                            </table>
                        </td>
                    </tr>';

        $schedule_7_1 .='</table>';
        $this->pdf->writeHTML($schedule_7_1, true, false, true, false, '');  
        $this->pdf->lastPage();  
    }

    function export_schedule_7_2($company_id, $employee_id, $payroll_date, $title){

        $this->pdf->SetMargins(10, 10, 10, true);
        $this->pdf->SetAutoPageBreak(TRUE);
        $this->pdf->addPage('L', 'FOLIO', true);
        $this->pdf->SetFontSize( 7);
        
        /* header  padding-left: 10px;*/
        
        $schedule_7_2 ='
            <table style="width: 100%">
                <tr>
                    <td width="10%" style="border-bottom:1px solid black; border-top:1px solid black; border-left:1px solid black; text-align:left; vertical-align:top;"><strong>'.$title.'</strong></td>
                    <td width="90%" style="border-bottom:1px solid black; border-top:1px solid black; border-right:1px solid black; text-align:center; vertical-align:top;"><strong>ALPHALIST OF EMPLOYEES WHOSE COMPENSATION INCOME ARE EXEMPT FROM WITHHOLDING TAX BUT SUBJECT TO INCOME TAX (Reported Under BIR Form 2316)</strong></td>
                </tr>
                <tr>
                    <td width="5%"   style="border-top:1px solid black; border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">SEQ</td>
                    <td width="10%"  style="border-top:1px solid black; border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">TAXPAYER</td>
                    <td width="24%"  style="border-top:1px solid black; border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">NAME OF EMPLOYEES</td>
                    <td width="46%"  style="border-top:1px solid black; border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">(4) GROSS COMPENSATION INCOME</td>
                    <td width="7%"   style="border-top:1px solid black; border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;"></td>
                    <td width="8%"   style="border-top:1px solid black; border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;"></td>
                </tr>
                <tr>
                    <td width="5%"   style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">NO.</td>
                    <td width="10%"  style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">IDENTIFICATION</td>
                    <td width="8%"   style="border-right:1px solid black; border-left:1px solid black; border-top:1px solid black; text-align:center; vertical-align:top;">Last</td>
                    <td width="8%"   style="border-right:1px solid black; border-left:1px solid black; border-top:1px solid black; text-align:center; vertical-align:top;">First</td>
                    <td width="8%"   style="border-right:1px solid black; border-left:1px solid black; border-top:1px solid black; text-align:center; vertical-align:top;">Middle</td>
                    <td width="35%"  style="border-right:1px solid black; border-left:1px solid black; border-top:1px solid black; border-bottom:1px solid black; text-align:center; vertical-align:top;">NON-TAXABLE</td>
                    <td width="11%"  style="border-right:1px solid black; border-left:1px solid black; border-top:1px solid black; border-bottom:1px solid black; text-align:center; vertical-align:top;">TAXABLE</td>
                    <td width="7%"   style="border-right:1px solid black; border-left:1px solid black; border-top:1px solid black; text-align:center; vertical-align:top;">Amount of</td>
                    <td width="8%"   style="border-right:1px solid black; border-left:1px solid black; border-top:1px solid black; text-align:center; vertical-align:top;">Premium Paid</td>
                </tr>
                <tr>
                    <td width="5%"   style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;"></td>
                    <td width="10%"  style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">NUBMER</td>
                    <td width="8%"   style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">Name</td>
                    <td width="8%"   style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">Name</td>
                    <td width="8%"   style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">Name</td>
                    <td width="10%"  style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">13th Month Pay</td>
                    <td width="14%"  style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">SSS,GSIS,PHIC,& Pag-Ibig</td>
                    <td width="11%"  style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">Other Forms</td>
                    <td width="11%"  style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">Salaries & Other Forms</td>
                    <td width="7%"   style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">Exemption</td>
                    <td width="8%"   style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">on health and/or</td>
                </tr>
                <tr>
                    <td width="5%"   style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;"></td>
                    <td width="10%"  style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;"></td>
                    <td width="8%"   style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;"></td>
                    <td width="8%"   style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;"></td>
                    <td width="8%"   style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;"></td>
                    <td width="10%"  style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">& Other Benefits</td>
                    <td width="14%"  style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">Contributions, and Union Dues</td>
                    <td width="11%"  style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">of Compensation</td>
                    <td width="11%"  style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">of Compensation</td>
                    <td width="7%"   style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;"></td>
                    <td width="8%"   style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">Hospital</td>
                </tr>
                <tr>
                    <td width="5%"   style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:center; vertical-align:top;">(1)</td>
                    <td width="10%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:center; vertical-align:top;">(2)</td>
                    <td width="8%"   style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:center; vertical-align:top;">(3a)</td>
                    <td width="8%"   style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:center; vertical-align:top;">(3b)</td>
                    <td width="8%"   style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:center; vertical-align:top;">(3c)</td>
                    <td width="10%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:center; vertical-align:top;">4(a)</td>
                    <td width="14%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:center; vertical-align:top;">4(b)</td>
                    <td width="11%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:center; vertical-align:top;">4(c)</td>
                    <td width="11%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:center; vertical-align:top;">4(d)</td>
                    <td width="7%"   style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:center; vertical-align:top;">(5)</td>  
                    <td width="8%"   style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:center; vertical-align:top;">Insurance(6)</td>
                </tr>

        ';


        $emp_separate = explode(',', $employee_id);
        reset($emp_separate);
        $ctr;
        foreach ($emp_separate as $key => $value) {
            //check transaction
            $this->db->where('employee_id', $value );
            $this->db->where('year(payroll_date)', $payroll_date);
            $count = $this->db->get('payroll_closed_transaction')->num_rows();

            if($count > 0){
                $this->db->where('employee_id', $value);
                $employee = $this->db->get('user')->row();
                $dept_id = $employee->department_id;

                $this->db->where('employee_id', $value);
                $emp = $this->db->get('employee')->row();

                $qry = "SELECT sum(amount) as amount FROM {$this->db->dbprefix}payroll_closed_transaction WHERE employee_id = $value and year(payroll_date) = '".$payroll_date."' and transaction_code IN ";
               

                 // 13th month and other
                $p_ben = $this->db->query("SELECT sum(amount) as amount 
                                    FROM {$this->db->dbprefix}payroll_closed_transaction c
                                    left join {$this->db->dbprefix}payroll_transaction t on t.transaction_id = c.transaction_id
                                    WHERE t.transaction_type_id in (8)
                                    and employee_id = $value and year(payroll_date) = '".$payroll_date."'")->row();

                // contribution
                $p_cont = $this->db->query($qry."('SSS_EMP','PHIC_EMP','HDMF_EMP')")->row();
                
                // compensation
                $p_comp = $this->db->query("SELECT sum(amount) as amount 
                                    FROM {$this->db->dbprefix}payroll_closed_transaction c
                                    left join {$this->db->dbprefix}payroll_transaction t on t.transaction_id = c.transaction_id
                                    WHERE t.transaction_type_id in (2,6,7)
                                    and employee_id = $value and year(payroll_date) = '".$payroll_date."'")->row();

                // salaries and other forms of benefits
                $p_sal = $this->db->query("SELECT sum(amount) as amount 
                                    FROM {$this->db->dbprefix}payroll_closed_transaction c
                                    left join {$this->db->dbprefix}payroll_transaction t on t.transaction_id = c.transaction_id
                                    WHERE t.transaction_type_id in (1)
                                    and employee_id = $value and year(payroll_date) = '".$payroll_date."'")->row();

                


                $p_benefits = number_format($p_ben->amount,2,'.',','); 
                $p_contribution = number_format($p_cont->amount,2,'.',',');   
                $p_salary = number_format($p_sal->amount,2,'.',',');
                $p_compensation = number_format($p_comp->amount,2,'.',',');   


                $schedule_7_2 .='
                    <tr> 
                       
                        <td width="5%"   style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:center; vertical-align:top;">'.$ctr.'</td>
                        <td width="10%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:center; vertical-align:top;">'.$emp->tin.'</td>
                        <td width="8%"   style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:left; vertical-align:top;">'.$employee->lastname.'</td>
                        <td width="8%"   style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:left; vertical-align:top;">'.$employee->firstname.'</td>
                        <td width="8%"   style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:left; vertical-align:top;">'.$employee->middlename.'</td>';

                    if ($p_salary > 0){
                        $schedule_7_2 .= '<td width="10%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:right; vertical-align:top;">'.$p_benefits.'</td>';
                    }
                    else{
                        $schedule_7_2 .= '<td width="10%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:right; vertical-align:top;">-</td>';    
                    }

                    if ($p_contribution > 0){
                        $schedule_7_2 .= '<td width="14%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:right; vertical-align:top;">'.$p_contribution.'</td>';
                    }
                    else{
                        $schedule_7_2 .= '<td width="14%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:right; vertical-align:top;">-</td>';    
                    }

                    if($p_salary > 0){                     
                        $schedule_7_2 .= '<td width="11%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:right; vertical-align:top;">'.$p_compensation.'</td>';
                    }
                    else{
                        $schedule_7_2 .= '<td width="11%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:right; vertical-align:top;">-</td>';    
                    }

                    if($p_salary > 0){
                        $schedule_7_2 .= '<td width="11%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:right; vertical-align:top;">'.$p_salary.'</td>';
                    }
                    else{
                        $schedule_7_2 .= '<td width="11%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:right; vertical-align:top;">-</td>';    
                    }
                $schedule_7_2 .='
                        <td width="7%"   style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:right; vertical-align:top;">-</td>  
                        <td width="8%"   style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:right; vertical-align:top;">-</td>
                    </tr>';
            }
            $ctr++;

        }
        $schedule_7_2 .='</table>';
        $this->pdf->writeHTML($schedule_7_2, true, false, true, false, '');  
        $this->pdf->lastPage();     
    }

    function export_schedule_7_3($company_id, $employee_id, $payroll_date, $title){

        $this->pdf->SetMargins(10, 10, 10, true);
        $this->pdf->SetAutoPageBreak(TRUE);
        $this->pdf->addPage('L', 'FOLIO', true);
        $this->pdf->SetFontSize( 7);
        
        /* header  padding-left: 10px;*/
        
        $schedule_7_3 ='
            <table style="width: 100%">
                <tr>
                   <td width="10%" style="border-bottom:1px solid black; border-top:1px solid black; border-left:1px solid black; text-align:left; vertical-align:top;"><strong>'.$title.'</strong></td>
                    <td width="90%" style="border-bottom:1px solid black; border-top:1px solid black; border-right:1px solid black; text-align:center; vertical-align:top;"><strong>ALPHALIST OF EMPLOYEES AS OF DECEMBER 31 WITH NO PREVIOUS EMPLOYER WITHIN THE YEAR (Reported Under BIR Form 2316)</strong></td>
                </tr>
                <tr>
                    <td width="5%"   style="border-top:1px solid black; border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">SEQ</td>
                    <td width="10%"  style="border-top:1px solid black; border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">TAXPAYER</td>
                    <td width="24%"  style="border-top:1px solid black; border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">NAME OF EMPLOYEES</td>
                    <td width="61%"  style="border-top:1px solid black; border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">(4) GROSS COMPENSATION INCOME</td>
                </tr>
                <tr>
                    <td width="5%"   style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">NO.</td>
                    <td width="10%"  style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">IDENTIFICATION</td>
                    <td width="8%"   style="border-right:1px solid black; border-left:1px solid black; border-top:1px solid black; text-align:center; vertical-align:top;">Last</td>
                    <td width="8%"   style="border-right:1px solid black; border-left:1px solid black; border-top:1px solid black; text-align:center; vertical-align:top;">First</td>
                    <td width="8%"   style="border-right:1px solid black; border-left:1px solid black; border-top:1px solid black; text-align:center; vertical-align:top;">Middle</td>
                    <td width="35%"  style="border-right:1px solid black; border-left:1px solid black; border-top:1px solid black; border-bottom:1px solid black; text-align:center; vertical-align:top;">NON-TAXABLE</td>
                    <td width="26%"  style="border-right:1px solid black; border-left:1px solid black; border-top:1px solid black; border-bottom:1px solid black; text-align:center; vertical-align:top;">TAXABLE</td>
                </tr>
                <tr>
                    <td width="5%"   style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;"></td>
                    <td width="10%"  style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">NUBMER</td>
                    <td width="8%"   style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">Name</td>
                    <td width="8%"   style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">Name</td>
                    <td width="8%"   style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">Name</td>
                    <td width="10%"  style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">13th Month Pay</td>
                    <td width="14%"  style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">SSS,GSIS,PHIC,& Pag-Ibig</td>
                    <td width="11%"  style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">Other Forms</td>
                    <td width="11%"  style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">13th Month Pay</td>
                    <td width="15%"   style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">Salaries & Other Forms</td>
                </tr>
                <tr>
                    <td width="5%"   style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;"></td>
                    <td width="10%"  style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;"></td>
                    <td width="8%"   style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;"></td>
                    <td width="8%"   style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;"></td>
                    <td width="8%"   style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;"></td>
                    <td width="10%"  style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">& Other Benefits</td>
                    <td width="14%"  style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">Contributions, and Union Dues</td>
                    <td width="11%"  style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">of Compensation</td>
                    <td width="11%"  style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">& Other Benefits</td>
                    <td width="15%"   style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">of Compensation</td>
                </tr>
                <tr>
                    <td width="5%"   style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:center; vertical-align:top;">(1)</td>
                    <td width="10%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:center; vertical-align:top;">(2)</td>
                    <td width="8%"   style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:center; vertical-align:top;">(3a)</td>
                    <td width="8%"   style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:center; vertical-align:top;">(3b)</td>
                    <td width="8%"   style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:center; vertical-align:top;">(3c)</td>
                    <td width="10%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:center; vertical-align:top;">4(a)</td>
                    <td width="14%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:center; vertical-align:top;">4(b)</td>
                    <td width="11%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:center; vertical-align:top;">4(c)</td>
                    <td width="11%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:center; vertical-align:top;">4(d)</td>
                    <td width="15%"   style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:center; vertical-align:top;">4(e)</td>
                </tr>

        ';

        $emp_separate = explode(',', $employee_id);
        reset($emp_separate);
        $ctr = 0;
        foreach ($emp_separate as $key => $value) {
            //check transaction
            $this->db->where('employee_id', $value );
            $this->db->where('year(payroll_date)', $payroll_date);
            $count = $this->db->get('payroll_closed_transaction')->num_rows();

            if($count > 0){
                $this->db->where('employee_id', $value);
                $employee = $this->db->get('user')->row();
                $dept_id = $employee->department_id;

                $this->db->where('employee_id', $value);
                $emp = $this->db->get('employee')->row();

                $qry = "SELECT sum(amount) as amount FROM {$this->db->dbprefix}payroll_closed_transaction WHERE employee_id = $value and year(payroll_date) = '".$payroll_date."' and transaction_code IN ";
               

                 // 13th month and other
                $p_ben = $this->db->query("SELECT sum(amount) as amount 
                                    FROM {$this->db->dbprefix}payroll_closed_transaction c
                                    left join {$this->db->dbprefix}payroll_transaction t on t.transaction_id = c.transaction_id
                                    WHERE t.transaction_type_id in (8)
                                    and employee_id = $value and year(payroll_date) = '".$payroll_date."'")->row();

                // contribution
                $p_cont = $this->db->query($qry."('SSS_EMP','PHIC_EMP','HDMF_EMP')")->row();
                
                // compensation
                $p_comp = $this->db->query("SELECT sum(amount) as amount 
                                    FROM {$this->db->dbprefix}payroll_closed_transaction c
                                    left join {$this->db->dbprefix}payroll_transaction t on t.transaction_id = c.transaction_id
                                    WHERE t.transaction_type_id in (2,6,7)
                                    and employee_id = $value and year(payroll_date) = '".$payroll_date."'")->row();

                // salaries and other forms of benefits
                $p_sal = $this->db->query("SELECT sum(amount) as amount 
                                    FROM {$this->db->dbprefix}payroll_closed_transaction c
                                    left join {$this->db->dbprefix}payroll_transaction t on t.transaction_id = c.transaction_id
                                    WHERE t.transaction_type_id in (1)
                                    and employee_id = $value and year(payroll_date) = '".$payroll_date."'")->row();

                


                $p_benefits = number_format($p_ben->amount,2,'.',','); 
                $p_contribution = number_format($p_cont->amount,2,'.',',');   
                $p_salary = number_format($p_sal->amount,2,'.',',');
                $p_compensation = number_format($p_comp->amount,2,'.',',');   


                $schedule_7_3 .='
                    <tr> 
                       
                        <td width="5%"   style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:center; vertical-align:top;">'.$ctr.'</td>
                        <td width="10%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:center; vertical-align:top;">'.$emp->tin.'</td>
                        <td width="8%"   style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:left; vertical-align:top;">'.$employee->lastname.'</td>
                        <td width="8%"   style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:left; vertical-align:top;">'.$employee->firstname.'</td>
                        <td width="8%"   style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:left; vertical-align:top;">'.$employee->middlename.'</td>';

                    if ($p_salary > 0){
                        $schedule_7_3 .= '<td width="10%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:right; vertical-align:top;">'.$p_benefits.'</td>';
                    }
                    else{
                        $schedule_7_3 .= '<td width="10%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:right; vertical-align:top;">-</td>';    
                    }

                    if ($p_contribution > 0){
                        $schedule_7_3 .= '<td width="14%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:right; vertical-align:top;">'.$p_contribution.'</td>';
                    }
                    else{
                        $schedule_7_3 .= '<td width="14%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:right; vertical-align:top;">-</td>';    
                    }

                    if($p_salary > 0){                     
                        $schedule_7_3 .= '<td width="11%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:right; vertical-align:top;">'.$p_compensation.'</td>';
                    }
                    else{
                        $schedule_7_3 .= '<td width="11%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:right; vertical-align:top;">-</td>';    
                    }

                    if($p_salary > 0){
                        $schedule_7_3 .= '<td width="11%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:right; vertical-align:top;">'.$p_salary.'</td>';
                    }
                    else{
                        $schedule_7_3 .= '<td width="11%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:right; vertical-align:top;">-</td>';    
                    }

                    if($p_salary > 0){
                        $schedule_7_3 .= '<td width="15%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:right; vertical-align:top;">'.$p_salary.'</td>';
                    }
                    else{
                        $schedule_7_3 .= '<td width="15%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:right; vertical-align:top;">-</td>';    
                    }
                $schedule_7_3 .='</tr>';
            }
            $ctr++;
        }
        $schedule_7_3 .='</table>';
        $this->pdf->writeHTML($schedule_7_3, true, false, true, false, '');  
        $this->pdf->lastPage();  
    }

    function export_schedule_7_3_cont($company_id, $employee_id, $payroll_date, $title){
        $this->pdf->SetMargins(10, 10, 10, true);
        $this->pdf->SetAutoPageBreak(TRUE);
        $this->pdf->addPage('L', 'FOLIO', true);
        $this->pdf->SetFontSize( 7);
        
        /* header  padding-left: 10px;*/
        
        $schedule_7_3_cont ='
            <table style="width: 100%">
                <tr>
                   <td width="20%" style="border-bottom:1px solid black; border-top:1px solid black; border-left:1px solid black; text-align:left; vertical-align:top;"><strong>'.$title.'</strong></td>
                    <td width="80%" style="border-bottom:1px solid black; border-top:1px solid black; border-right:1px solid black; text-align:center; vertical-align:top;"><strong>ALPHALIST OF EMPLOYEES AS OF DECEMBER 31 WITH NO PREVIOUS EMPLOYER WITHIN THE YEAR</strong></td>
                </tr>
                <tr>
                    <td width="11%" style="border-top:1px solid black; border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">AMOUNT OF</td>
                    <td width="15%" style="border-top:1px solid black; border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">Premium Paid on</td>
                    <td width="12%" style="border-top:1px solid black; border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">TAX DUE</td>
                    <td width="12%" style="border-top:1px solid black; border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">TAX WITHHELD</td>
                    <td width="28%" style="border-top:1px solid black; border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">YEAR-END ADJUSTMENT (9a or 9b)</td>
                    <td width="22%" style="border-top:1px solid black; border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">AMOUNT OF TAX</td>
                </tr>
                <tr>
                    <td width="11%" style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">EXEMPTION</td>
                    <td width="15%" style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">Health and/or</td>
                    <td width="12%" style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">(JAN. - DEC.)</td>
                    <td width="12%" style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">(JAN. - NOV.)</td>
                    <td width="12%" style="border-right:1px solid black; border-left:1px solid black; border-top:1px solid black; text-align:center; vertical-align:top;">AMOUNT WITHHELD</td>
                    <td width="16%" style="border-right:1px solid black; border-left:1px solid black; border-top:1px solid black; text-align:center; vertical-align:top;">OVER WITHHELD TAX</td>
                    <td width="22%" style="border-right:1px solid black; border-left:1px solid black; border-top:1px solid black; text-align:center; vertical-align:top;">WITHHELD</td>
                </tr>
                <tr>
                    <td width="11%" style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;"></td>
                    <td width="15%" style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">Hospital Insurance</td>
                    <td width="12%" style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;"></td>
                    <td width="12%" style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;"></td>
                    <td width="12%" style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">AND PAID FOR</td>
                    <td width="16%" Style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">REFUNDED TO</td>
                    <td width="22%" style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">AS ADJSUTED</td>
                </tr>
                <tr>
                    <td width="11%" style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;"></td>
                    <td width="15%" style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;"></td>
                    <td width="12%" style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;"></td>
                    <td width="12%" style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;"></td>
                    <td width="12%" style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">IN DECEMBER</td>
                    <td width="16%" Style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">EMPLOYEE</td>
                    <td width="22%" style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">(to be reflected in BIR Form 2316)</td>
                </tr>
                <tr>
                    <td width="11%"   style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:center; vertical-align:top;">(5)</td>
                    <td width="15%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:center; vertical-align:top;">(6)</td>
                    <td width="12%"   style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:center; vertical-align:top;">(7)</td>
                    <td width="12%"   style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:center; vertical-align:top;">(8)</td>
                    <td width="12%"   style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:center; vertical-align:top;">(9a) = (7) - (8)</td>
                    <td width="16%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:center; vertical-align:top;">(9b) = (8) - (7)</td>
                    <td width="22%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:center; vertical-align:top;">(10) = (8+9a) or (8-9b)</td>
                </tr>

        ';
        $emp_separate = explode(',', $employee_id);
        reset($emp_separate);
        foreach ($emp_separate as $key => $value) {
            //check transaction
            $this->db->where('employee_id', $value );
            $this->db->where('year(payroll_date)', $payroll_date);
            $count = $this->db->get('payroll_closed_transaction')->num_rows();

            if($count > 0){
                $this->db->where('employee_id', $value);
                $employee = $this->db->get('user')->row();
                $dept_id = $employee->department_id;

                $this->db->where('employee_id', $value);
                $emp = $this->db->get('employee')->row();

                $qry = "SELECT sum(amount) as amount FROM {$this->db->dbprefix}payroll_closed_transaction WHERE employee_id = $value and year(payroll_date) = '".$payroll_date."' and transaction_code IN ";
               

                 // 13th month and other
                $p_ben = $this->db->query("SELECT sum(amount) as amount 
                                    FROM {$this->db->dbprefix}payroll_closed_transaction c
                                    left join {$this->db->dbprefix}payroll_transaction t on t.transaction_id = c.transaction_id
                                    WHERE t.transaction_type_id in (8)
                                    and employee_id = $value and year(payroll_date) = '".$payroll_date."'")->row();

                // contribution
                $p_cont = $this->db->query($qry."('SSS_EMP','PHIC_EMP','HDMF_EMP')")->row();
                
                // compensation
                $p_comp = $this->db->query("SELECT sum(amount) as amount 
                                    FROM {$this->db->dbprefix}payroll_closed_transaction c
                                    left join {$this->db->dbprefix}payroll_transaction t on t.transaction_id = c.transaction_id
                                    WHERE t.transaction_type_id in (2,6,7)
                                    and employee_id = $value and year(payroll_date) = '".$payroll_date."'")->row();

                // salaries and other forms of benefits
                $p_sal = $this->db->query("SELECT sum(amount) as amount 
                                    FROM {$this->db->dbprefix}payroll_closed_transaction c
                                    left join {$this->db->dbprefix}payroll_transaction t on t.transaction_id = c.transaction_id
                                    WHERE t.transaction_type_id in (1)
                                    and employee_id = $value and year(payroll_date) = '".$payroll_date."'")->row();

                


                $p_benefits = number_format($p_ben->amount,2,'.',','); 
                $p_contribution = number_format($p_cont->amount,2,'.',',');   
                $p_salary = number_format($p_sal->amount,2,'.',',');
                $p_compensation = number_format($p_comp->amount,2,'.',',');   


                $schedule_7_3_cont .='<tr> ';
                       
                    if ($p_salary > 0){
                        $schedule_7_3_cont .= '<td width="11%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:right; vertical-align:top;">'.$p_benefits.'</td>';
                    }
                    else{
                        $schedule_7_3_cont .= '<td width="11%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:right; vertical-align:top;">-</td>';    
                    }

                    if ($p_contribution > 0){
                        $schedule_7_3_cont .= '<td width="15%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:right; vertical-align:top;">'.$p_contribution.'</td>';
                    }
                    else{
                        $schedule_7_3_cont .= '<td width="15%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:right; vertical-align:top;">-</td>';    
                    }

                    if($p_salary > 0){                     
                        $schedule_7_3_cont .= '<td width="12%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:right; vertical-align:top;">'.$p_compensation.'</td>';
                    }
                    else{
                        $schedule_7_3_cont .= '<td width="12%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:right; vertical-align:top;">-</td>';    
                    }

                    if($p_salary > 0){
                        $schedule_7_3_cont .= '<td width="12%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:right; vertical-align:top;">'.$p_salary.'</td>';
                    }
                    else{
                        $schedule_7_3_cont .= '<td width="12%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:right; vertical-align:top;">-</td>';    
                    }

                    if($p_salary > 0){
                        $schedule_7_3_cont .= '<td width="12%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:right; vertical-align:top;">'.$p_salary.'</td>';
                    }
                    else{
                        $schedule_7_3_cont .= '<td width="12%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:right; vertical-align:top;">-</td>';    
                    }

                    if($p_salary > 0){
                        $schedule_7_3_cont .= '<td width="16%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:right; vertical-align:top;">'.$p_salary.'</td>';
                    }
                    else{
                        $schedule_7_3_cont .= '<td width="16%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:right; vertical-align:top;">-</td>';    
                    }

                    if($p_salary > 0){
                        $schedule_7_3_cont .= '<td width="22%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:right; vertical-align:top;">'.$p_salary.'</td>';
                    }
                    else{
                        $schedule_7_3_cont .= '<td width="22%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:right; vertical-align:top;">-</td>';    
                    }
                $schedule_7_3_cont .='</tr>';
                    
            }

        }

        $schedule_7_3_cont .='</table>';
        $this->pdf->writeHTML($schedule_7_3_cont, true, false, true, false, '');  
        $this->pdf->lastPage();  
    }

    function export_schedule_7_4($company_id, $employee_id, $payroll_date, $title){
        $this->pdf->SetMargins(10, 10, 10, true);
        $this->pdf->SetAutoPageBreak(TRUE);
        $this->pdf->addPage('L', 'FOLIO', true);
        $this->pdf->SetFontSize( 7);
        
        /* header  padding-left: 10px;*/
        
        $schedule_7_4 ='
            <table style="width: 100%">
                <tr>
                   <td width="10%" style="border-bottom:1px solid black; border-top:1px solid black; border-left:1px solid black; text-align:left; vertical-align:top;"><strong>'.$title.'</strong></td>
                    <td width="90%" style="border-bottom:1px solid black; border-top:1px solid black; border-right:1px solid black; text-align:center; vertical-align:top;"><strong>ALPHALIST OF EMPLOYEES AS OF DECEMBER 31 WITH PREVIOUS EMPLOYER/S WITHIN THE YEAR (Reported Under Form 2316)</strong></td>
                </tr>
                <tr>
                    <td width="3%"   style="border-top:1px solid black; border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">SEQ</td>
                    <td width="7%"   style="border-top:1px solid black; border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">TAXPAYER</td>
                    <td width="18%"  style="border-top:1px solid black; border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">NAME OF EMPLOYEES</td>
                    <td width="72%"  style="border-top:1px solid black; border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:center; vertical-align:top;">(4) GROSS COMPENSATION INCOME</td>
                </tr>
                <tr>
                    <td width="3%"   style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">NO</td>
                    <td width="7%"   style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">IDENTIFICATION</td>
                    <td width="6%"   style="border-left:1px solid black; border-bottom:1px solid black; text-align:center; vertical-align:top;"></td>
                    <td width="6%"   style="border-bottom:1px solid black; text-align:center; vertical-align:top;"></td>
                    <td width="6%"   style="border-right:1px solid black; border-bottom:1px solid black; text-align:center; vertical-align:top;"></td>
                    <td width="48%"  style="border-right:1px solid black; border-left:1px solid black; border-top:1px solid black; border-bottom:1px solid black; text-align:center; vertical-align:top;">PREVIOUS EMPLOYER</td>
                    <td width="24%"  style="border-right:1px solid black; border-left:1px solid black; border-top:1px solid black; border-bottom:1px solid black; text-align:center; vertical-align:top;">PRESENT EMPLOYER</td>
                </tr>
                <tr>
                    <td width="3%"   style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;"></td>
                    <td width="7%"  style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">NUBMER</td>
                    <td width="6%"   style="border-right:1px solid black; border-left:1px solid black; border-top:1px solid black; text-align:center; vertical-align:top;">Last</td>
                    <td width="6%"   style="border-right:1px solid black; border-left:1px solid black; border-top:1px solid black; text-align:center; vertical-align:top;">First</td>
                    <td width="6%"   style="border-right:1px solid black; border-left:1px solid black; border-top:1px solid black; text-align:center; vertical-align:top;">Middle</td>
                    <td width="24%"  style="border-right:1px solid black; border-left:1px solid black; border-top:1px solid black; border-bottom:1px solid black; text-align:center; vertical-align:top;">NON - TAXABLE</td>
                    <td width="24%"  style="border-right:1px solid black; border-left:1px solid black; border-top:1px solid black; border-bottom:1px solid black; text-align:center; vertical-align:top;">TAXABLE</td>
                    <td width="24%"  style="border-right:1px solid black; border-left:1px solid black; border-top:1px solid black; border-bottom:1px solid black; text-align:center; vertical-align:top;">NON - TAXABLE</td>
                </tr>
                <tr>
                    <td width="3%"   style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;"></td>
                    <td width="7%"  style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;"></td>
                    <td width="6%"   style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">Name</td>
                    <td width="6%"   style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">Name</td>
                    <td width="6%"   style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">Name</td>
                    <td width="7.5%"  style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">13th Month Pay</td>
                    <td width="8%"  style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top; font-size:6;">SALARIES &</td>
                    <td width="8.5%"  style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top; font-size:6;">SSS,GSIS,PHIC,& </td>
                    <td width="7.5%"  style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">13th Month Pay</td>
                    <td width="8%"  style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top; font-size:6;">SALARIES &</td>
                    <td width="8.5%"  style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top; font-size:6;">Total Taxable</td>
                    <td width="7.5%"  style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">13th Month Pay</td>
                    <td width="8%"  style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top; font-size:6;">SALARIES &</td>
                    <td width="8.5%"  style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top; font-size:6;">SSS,GSIS,PHIC &</td>
                </tr>
                <tr>
                    <td width="3%"   style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;"></td>
                    <td width="7%"  style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;"></td>
                    <td width="6%"   style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;"></td>
                    <td width="6%"   style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;"></td>
                    <td width="6%"   style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;"></td>
                    <td width="7.5%"  style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">& Other</td>
                    <td width="8%"  style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top; font-size:6;">OTHER FORMS</td>
                    <td width="8.5%"  style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top; font-size:6;">Pag-Ibig Contributions</td>
                    <td width="7.5%"  style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">& Other</td>
                    <td width="8%"  style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top; font-size:6;">OTHER FORMS</td>
                    <td width="8.5%"  style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top; font-size:6;">(Previous Employer)</td>
                    <td width="7.5%"  style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">& Other</td>
                    <td width="8%"  style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top; font-size:6;">OTHER FORMS</td>
                    <td width="8.5%"  style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top; font-size:6;">Pag-Ibig Contributions,</td>
                </tr>
                <tr>
                    <td width="3%"   style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;"></td>
                    <td width="7%"  style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;"></td>
                    <td width="6%"   style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;"></td>
                    <td width="6%"   style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;"></td>
                    <td width="6%"   style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;"></td>
                    <td width="7.5%"  style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">Benefits</td>
                    <td width="8%"  style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top; font-size:6;">OF COMPENSATION</td>
                    <td width="8.5%"  style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top; font-size:6;">and Union Dues</td>
                    <td width="7.5%"  style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">Benefits</td>
                    <td width="8%"  style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top; font-size:6;">OF COMPENSATION</td>
                    <td width="8.5%"  style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top; font-size:6;"></td>
                    <td width="7.5%"  style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">Benefits</td>
                    <td width="8%"  style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top; font-size:6;">OF COMPENSATION</td>
                    <td width="8.5%"  style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top; font-size:6;">and Union Dues</td>
                </tr>
                <tr>
                    <td width="3%"   style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:center; vertical-align:top;">(1)</td>
                    <td width="7%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:center; vertical-align:top;">(2)</td>
                    <td width="6%"   style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:center; vertical-align:top;">(3a)</td>
                    <td width="6%"   style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:center; vertical-align:top;">(3b)</td>
                    <td width="6%"   style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:center; vertical-align:top;">(3c)</td>
                    <td width="7.5%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:center; vertical-align:top;">(4a)</td>
                    <td width="8%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:center; vertical-align:top;">(4b)</td>
                    <td width="8.5%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:center; vertical-align:top;">(4c)</td>
                    <td width="7.5%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:center; vertical-align:top;">(4d)</td>
                    <td width="8%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:center; vertical-align:top;">(4e)</td>
                    <td width="8.5%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:center; vertical-align:top;">(4f = 4d + 4e) </td>
                    <td width="7.5%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:center; vertical-align:top;">(4g)</td>
                    <td width="8%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:center; vertical-align:top;">(4h)</td>
                    <td width="8.5%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:center; vertical-align:top;">(4i)</td>
                </tr>

        ';
        $emp_separate = explode(',', $employee_id);
        reset($emp_separate);
        $ctr = 0;
        foreach ($emp_separate as $key => $value) {
            //check transaction
            
            $this->db->where('employee_id', $value );
            $this->db->where('year(payroll_date)', $payroll_date);
            $count = $this->db->get('payroll_closed_transaction')->num_rows();

            if($count > 0){
                $this->db->where('employee_id', $value);
                $employee = $this->db->get('user')->row();
                $dept_id = $employee->department_id;

                $this->db->where('employee_id', $value);
                $emp = $this->db->get('employee')->row();

                $qry = "SELECT sum(amount) as amount FROM {$this->db->dbprefix}payroll_closed_transaction WHERE employee_id = $value and year(payroll_date) = '".$payroll_date."' and transaction_code IN ";
               

                 // 13th month and other
                $p_ben = $this->db->query("SELECT sum(amount) as amount 
                                    FROM {$this->db->dbprefix}payroll_closed_transaction c
                                    left join {$this->db->dbprefix}payroll_transaction t on t.transaction_id = c.transaction_id
                                    WHERE t.transaction_type_id in (8)
                                    and employee_id = $value and year(payroll_date) = '".$payroll_date."'")->row();

                // contribution
                $p_cont = $this->db->query($qry."('SSS_EMP','PHIC_EMP','HDMF_EMP')")->row();
                
                // compensation
                $p_comp = $this->db->query("SELECT sum(amount) as amount 
                                    FROM {$this->db->dbprefix}payroll_closed_transaction c
                                    left join {$this->db->dbprefix}payroll_transaction t on t.transaction_id = c.transaction_id
                                    WHERE t.transaction_type_id in (2,6,7)
                                    and employee_id = $value and year(payroll_date) = '".$payroll_date."'")->row();

                // salaries and other forms of benefits
                $p_sal = $this->db->query("SELECT sum(amount) as amount 
                                    FROM {$this->db->dbprefix}payroll_closed_transaction c
                                    left join {$this->db->dbprefix}payroll_transaction t on t.transaction_id = c.transaction_id
                                    WHERE t.transaction_type_id in (1)
                                    and employee_id = $value and year(payroll_date) = '".$payroll_date."'")->row();

                


                $p_benefits = number_format($p_ben->amount,2,'.',','); 
                $p_contribution = number_format($p_cont->amount,2,'.',',');   
                $p_salary = number_format($p_sal->amount,2,'.',',');
                $p_compensation = number_format($p_comp->amount,2,'.',',');   


                $schedule_7_4 .='
                    <tr> 
                       
                        <td width="3%"   style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:center; vertical-align:top;">'.$ctr.'</td>
                        <td width="7%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:center; vertical-align:top;">'.$emp->tin.'</td>
                        <td width="6%"   style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:left; vertical-align:top;">'.$employee->lastname.'</td>
                        <td width="6%"   style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:left; vertical-align:top;">'.$employee->firstname.'</td>
                        <td width="6%"   style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:left; vertical-align:top;">'.$employee->middlename.'</td>';

                    if ($p_salary > 0){
                        $schedule_7_4 .= '<td width="7.5%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:right; vertical-align:top;">'.$p_benefits.'</td>';
                    }
                    else{
                        $schedule_7_4 .= '<td width="7.5%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:right; vertical-align:top;">-</td>';    
                    }

                    if ($p_contribution > 0){
                        $schedule_7_4 .= '<td width="8%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:right; vertical-align:top;">'.$p_contribution.'</td>';
                    }
                    else{
                        $schedule_7_4 .= '<td width="8%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:right; vertical-align:top;">-</td>';    
                    }

                    if($p_salary > 0){                     
                        $schedule_7_4 .= '<td width="8.5%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:right; vertical-align:top;">'.$p_compensation.'</td>';
                    }
                    else{
                        $schedule_7_4 .= '<td width="8.5%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:right; vertical-align:top;">-</td>';    
                    }

                    if($p_salary > 0){
                        $schedule_7_4 .= '<td width="7.5%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:right; vertical-align:top;">'.$p_salary.'</td>';
                    }
                    else{
                        $schedule_7_4 .= '<td width="7.5%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:right; vertical-align:top;">-</td>';    
                    }

                    if($p_salary > 0){
                        $schedule_7_4 .= '<td width="8%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:right; vertical-align:top;">'.$p_salary.'</td>';
                    }
                    else{
                        $schedule_7_4 .= '<td width="8%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:right; vertical-align:top;">-</td>';    
                    }

                    if($p_salary > 0){                     
                        $schedule_7_4 .= '<td width="8.5%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:right; vertical-align:top;">'.$p_compensation.'</td>';
                    }
                    else{
                        $schedule_7_4 .= '<td width="8.5%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:right; vertical-align:top;">-</td>';    
                    }

                    if($p_salary > 0){
                        $schedule_7_4 .= '<td width="7.5%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:right; vertical-align:top;">'.$p_salary.'</td>';
                    }
                    else{
                        $schedule_7_4 .= '<td width="7.5%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:right; vertical-align:top;">-</td>';    
                    }

                    if ($p_contribution > 0){
                        $schedule_7_4 .= '<td width="8%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:right; vertical-align:top;">'.$p_contribution.'</td>';
                    }
                    else{
                        $schedule_7_4 .= '<td width="8%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:right; vertical-align:top;">-</td>';    
                    }

                    if($p_salary > 0){                     
                        $schedule_7_4 .= '<td width="8.5%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:right; vertical-align:top;">'.$p_compensation.'</td>';
                    }
                    else{
                        $schedule_7_4 .= '<td width="8.5%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:right; vertical-align:top;">-</td>';    
                    }
                $schedule_7_4 .='</tr>';
            }
            $ctr++;
        }
        $schedule_7_4 .='</table>';
        $this->pdf->writeHTML($schedule_7_4, true, false, true, false, '');  
        $this->pdf->lastPage();  
    }

    function export_schedule_7_4_cont($company_id, $employee_id, $payroll_date, $title){

        $this->pdf->SetMargins(10, 10, 10, true);
        $this->pdf->SetAutoPageBreak(TRUE);
        $this->pdf->addPage('L', 'FOLIO', true);
        $this->pdf->SetFontSize( 7);
        
        /* header  padding-left: 10px;*/
        
        $schedule_7_4_cont ='
            <table style="width: 100%">
                <tr>
                    <td width="20%" style="border-bottom:1px solid black; border-top:1px solid black; border-left:1px solid black; text-align:left; vertical-align:top;"><strong>'.$title.'</strong></td>
                    <td width="80%" style="border-bottom:1px solid black; border-top:1px solid black; border-right:1px solid black; text-align:center; vertical-align:top;"><strong>ALPHALIST OF EMPLOYEES AS OF DECEMBER 31 WITH PREVIOUS EMPLOYER/S WITHIN THE YEAR</strong></td>
                </tr>
                <tr>
                    <td width="100%"  style="border-bottom:1px solid black; border-top:1px solid black; border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;"></td>
                </tr>
                <tr>
                    <td width="18%"  style="border-right:1px solid black; border-left:1px solid black; border-top:1px solid black; border-bottom:1px solid black; text-align:center; vertical-align:top;">PREVIOUS EMPLOYER</td>
                    <td width="9%"   style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">Total Taxable</td>
                    <td width="8%"   style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">AMOUNT</td>
                    <td width="9%"   style="border-left:1px solid black; border-bottom:1px solid black; text-align:center; vertical-align:top;">Premium Paid on</td>
                    <td width="9%"   style="border-bottom:1px solid black; text-align:center; vertical-align:top;">TAX</td>
                    <td width="17%"  style="border-right:1px solid black; border-bottom:1px solid black; text-align:center; vertical-align:top;">TAX WITHHELD</td>
                    <td width="19%"  style="border-right:1px solid black; border-bottom:1px solid black; text-align:center; vertical-align:top;">YEAR - END ADJUSTMENT (9a or 9b)</td>
                    <td width="11%"  style="border-right:1px solid black; border-bottom:1px solid black; text-align:center; vertical-align:top;">AMOUNT OF TAX</td>
                </tr>
                <tr>
                    <td width="18%"  style="border-right:1px solid black; border-left:1px solid black; border-top:1px solid black; border-bottom:1px solid black; text-align:center; vertical-align:top;">TAXABLE</td>
                    <td width="9%"   style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top; font-size:6;">(Previous & Present</td>
                    <td width="8%"   style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">OF</td>
                    <td width="9%"   style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">Health and/or</td>
                    <td width="9%"   style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">DUE</td>
                    <td width="17%"  style="border-right:1px solid black; border-bottom:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">(JAN. - NOV.)</td>
                    <td width="9%"   style="border-right:1px solid black; border-bottom:1px solid black; text-align:center; vertical-align:top;">AMOUNT W/HELD</td>
                    <td width="10%"  style="border-right:1px solid black; border-bottom:1px solid black; text-align:center; vertical-align:top;">OVER WITHHELD TAX</td>
                    <td width="11%"  style="border-right:1px solid black; border-bottom:1px solid black; text-align:center; vertical-align:top;">WITHELD</td>
                </tr>
                <tr>
                    <td width="10%"  style="border-right:1px solid black; border-left:1px solid black; border-top:1px solid black; text-align:center; vertical-align:top;">13th Month Pay</td>
                    <td width="8%"   style="border-right:1px solid black; border-left:1px solid black; border-top:1px solid black; text-align:center; vertical-align:top; font-size:6;">SALARIES &</td>
                    <td width="9%"   style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top; font-size:6;">Employers)</td>
                    <td width="8%"   style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top; ">EXEMPTION</td>
                    <td width="9%"   style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">Hospital</td>
                    <td width="9%"   style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">(JAN. - DEC.)</td>
                    <td width="8%"   style="border-right:1px solid black; border-left:1px solid black; border-top:1px solid black; text-align:center; vertical-align:top;">PREVIOUS</td>
                    <td width="9%"   style="border-right:1px solid black; border-left:1px solid black; border-top:1px solid black; text-align:center; vertical-align:top;">PRESENT</td>
                    <td width="9%"   style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">& PAID FOR</td>
                    <td width="10%"  style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">REFUNDED TO</td>
                    <td width="11%"  style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">AS ADJUSTED</td>
                </tr>
                <tr>
                    <td width="10%"  style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">& Other</td>
                    <td width="8%"   style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top; font-size:6;">OTHER FORMS</td>
                    <td width="9%"   style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top; font-size:6;"></td>
                    <td width="8%"   style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;"></td>
                    <td width="9%"   style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">Insurance</td>
                    <td width="9%"   style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;"></td>
                    <td width="8%"   style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">EMPLOYER</td>
                    <td width="9%"   style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">EMPLOYER</td>
                    <td width="9%"   style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">IN DECEMBER</td>
                    <td width="10%"  style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">EMPLOYEE</td>
                    <td width="11%"  style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top; font-size:5;">(To be reflected in Form 2316 issued</td>
                </tr>
                <tr>
                    <td width="10%"  style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;">Benefits</td>
                    <td width="8%"   style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top; font-size:6;">OF COMPENSATION</td>
                    <td width="9%"   style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top; font-size:6;"></td>
                    <td width="8%"   style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;"></td>
                    <td width="9%"   style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;"></td>
                    <td width="9%"   style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;"></td>
                    <td width="8%"   style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;"></td>
                    <td width="9%"   style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;"></td>
                    <td width="9%"   style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;"></td>
                    <td width="10%"  style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top;"></td>
                    <td width="11%"  style="border-right:1px solid black; border-left:1px solid black; text-align:center; vertical-align:top; font-size:5;">by the present employer)</td>
                </tr>
                <tr>
                    <td width="10%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:center; vertical-align:top;">(4j)</td>
                    <td width="8%"   style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:center; vertical-align:top;">(4k)</td>
                    <td width="9%"   style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:center; vertical-align:top;">(4l = 4f + 4j + 4k)</td>
                    <td width="8%"   style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:center; vertical-align:top;">(5)</td>
                    <td width="9%"   style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:center; vertical-align:top;">(6)</td>
                    <td width="9%"   style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:center; vertical-align:top;">(7)</td>
                    <td width="8%"   style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:center; vertical-align:top;">(8a)</td>
                    <td width="9%"   style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:center; vertical-align:top;">(8b) </td>
                    <td width="9%"   style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:center; vertical-align:top;">(9a) = (7) - (8a +8b)</td>
                    <td width="10%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:center; vertical-align:top;">(9b) = (8a +8b) - (7)</td>
                    <td width="11%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:center; vertical-align:top;">(10) = (8b + 9a) or (8b - 9b)</td>
                </tr>

        ';
        $emp_separate = explode(',', $employee_id);
        reset($emp_separate);
        foreach ($emp_separate as $key => $value) {
            //check transaction
            $this->db->where('employee_id', $value );
            $this->db->where('year(payroll_date)', $payroll_date);
            $count = $this->db->get('payroll_closed_transaction')->num_rows();

            if($count > 0){
                $this->db->where('employee_id', $value);
                $employee = $this->db->get('user')->row();
                $dept_id = $employee->department_id;

                $this->db->where('employee_id', $value);
                $emp = $this->db->get('employee')->row();

                $qry = "SELECT sum(amount) as amount FROM {$this->db->dbprefix}payroll_closed_transaction WHERE employee_id = $value and year(payroll_date) = '".$payroll_date."' and transaction_code IN ";
               

                 // 13th month and other
                $p_ben = $this->db->query("SELECT sum(amount) as amount 
                                    FROM {$this->db->dbprefix}payroll_closed_transaction c
                                    left join {$this->db->dbprefix}payroll_transaction t on t.transaction_id = c.transaction_id
                                    WHERE t.transaction_type_id in (8)
                                    and employee_id = $value and year(payroll_date) = '".$payroll_date."'")->row();

                // contribution
                $p_cont = $this->db->query($qry."('SSS_EMP','PHIC_EMP','HDMF_EMP')")->row();
                
                // compensation
                $p_comp = $this->db->query("SELECT sum(amount) as amount 
                                    FROM {$this->db->dbprefix}payroll_closed_transaction c
                                    left join {$this->db->dbprefix}payroll_transaction t on t.transaction_id = c.transaction_id
                                    WHERE t.transaction_type_id in (2,6,7)
                                    and employee_id = $value and year(payroll_date) = '".$payroll_date."'")->row();

                // salaries and other forms of benefits
                $p_sal = $this->db->query("SELECT sum(amount) as amount 
                                    FROM {$this->db->dbprefix}payroll_closed_transaction c
                                    left join {$this->db->dbprefix}payroll_transaction t on t.transaction_id = c.transaction_id
                                    WHERE t.transaction_type_id in (1)
                                    and employee_id = $value and year(payroll_date) = '".$payroll_date."'")->row();

                


                $p_benefits = number_format($p_ben->amount,2,'.',','); 
                $p_contribution = number_format($p_cont->amount,2,'.',',');   
                $p_salary = number_format($p_sal->amount,2,'.',',');
                $p_compensation = number_format($p_comp->amount,2,'.',',');   


                $schedule_7_4_cont .='<tr> ';
                       
                    if ($p_salary > 0){
                        $schedule_7_4_cont .= '<td width="10%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:right; vertical-align:top;">'.$p_benefits.'</td>';
                    }
                    else{
                        $schedule_7_4_cont .= '<td width="10%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:right; vertical-align:top;">-</td>';    
                    }

                    if ($p_contribution > 0){
                        $schedule_7_4_cont .= '<td width="8%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:right; vertical-align:top;">'.$p_contribution.'</td>';
                    }
                    else{
                        $schedule_7_4_cont .= '<td width="8%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:right; vertical-align:top;">-</td>';    
                    }

                    if($p_salary > 0){                     
                        $schedule_7_4_cont .= '<td width="9%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:right; vertical-align:top;">'.$p_compensation.'</td>';
                    }
                    else{
                        $schedule_7_4_cont .= '<td width="9%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:right; vertical-align:top;">-</td>';    
                    }

                    if($p_salary > 0){
                        $schedule_7_4_cont .= '<td width="8%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:right; vertical-align:top;">'.$p_salary.'</td>';
                    }
                    else{
                        $schedule_7_4_cont .= '<td width="8%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:right; vertical-align:top;">-</td>';    
                    }

                    if($p_salary > 0){
                        $schedule_7_4_cont .= '<td width="9%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:right; vertical-align:top;">'.$p_salary.'</td>';
                    }
                    else{
                        $schedule_7_4_cont .= '<td width="9%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:right; vertical-align:top;">-</td>';    
                    }

                    if($p_salary > 0){
                        $schedule_7_4_cont .= '<td width="9%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:right; vertical-align:top;">'.$p_salary.'</td>';
                    }
                    else{
                        $schedule_7_4_cont .= '<td width="9%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:right; vertical-align:top;">-</td>';    
                    }

                    if($p_salary > 0){
                        $schedule_7_4_cont .= '<td width="8%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:right; vertical-align:top;">'.$p_salary.'</td>';
                    }
                    else{
                        $schedule_7_4_cont .= '<td width="8%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:right; vertical-align:top;">-</td>';    
                    }

                    if($p_salary > 0){
                        $schedule_7_4_cont .= '<td width="9%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:right; vertical-align:top;">'.$p_salary.'</td>';
                    }
                    else{
                        $schedule_7_4_cont .= '<td width="9%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:right; vertical-align:top;">-</td>';    
                    }

                    if($p_salary > 0){
                        $schedule_7_4_cont .= '<td width="9%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:right; vertical-align:top;">'.$p_salary.'</td>';
                    }
                    else{
                        $schedule_7_4_cont .= '<td width="9%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:right; vertical-align:top;">-</td>';    
                    }

                    if($p_salary > 0){
                        $schedule_7_4_cont .= '<td width="10%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:right; vertical-align:top;">'.$p_salary.'</td>';
                    }
                    else{
                        $schedule_7_4_cont .= '<td width="10%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:right; vertical-align:top;">-</td>';    
                    }

                    if($p_salary > 0){
                        $schedule_7_4_cont .= '<td width="11%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:right; vertical-align:top;">'.$p_salary.'</td>';
                    }
                    else{
                        $schedule_7_4_cont .= '<td width="11%"  style="border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; text-align:right; vertical-align:top;">-</td>';    
                    }
                $schedule_7_4_cont .='</tr>';
                    
            }

        }
        $schedule_7_4_cont .='</table>';
        $this->pdf->writeHTML($schedule_7_4_cont, true, false, true, false, '');  
        $this->pdf->lastPage();  
    }

}
/* End of file */
/* Location: system/application */
?>