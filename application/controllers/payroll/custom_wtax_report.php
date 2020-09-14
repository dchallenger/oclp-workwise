<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class custom_wtax_report extends MY_Controller
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

        $date_from = date("Y-m-d",strtotime($_POST['date_range_from']));
        $date_to = date("Y-m-d",strtotime($_POST['date_range_to']));

        $this->load->library('pdf');
        $html = $this->export_wtax($paycode_id, $company_id, $employee_id, $date_from, $date_to, "Withholding Tax");        
        $title = "Withholding Tax";
        $this->pdf->writeHTML($html, true, false, true, false, '');
        $this->pdf->Output(ucwords(str_replace(" ","_",$title))."_" . date('dmYHis') . '.pdf', 'I');
    }

    function export_wtax($paycode_id, $company_id, $employee_id, $date_from, $date_to, $title){   

        if(!empty($company_id)){

            $company = " AND u.company_id IN ($company_id)";
        }
        if(!empty($employee_id)){
            
            $employee = " AND u.employee_id IN ($employee_id)";   
        }

        if(!empty($paycode_id)){
            $pay_code = 'AND pct.paycode_id = '.$paycode_id;
        }

        $mdate = getdate(date("U"));
        $mdate = "$mdate[weekday], $mdate[month] $mdate[mday], $mdate[year]";

        $qry_tot = $this->db->query("SELECT count(distinct pct.employee_id) as cnt
                                    FROM {$this->db->dbprefix}payroll_closed_transaction pct 
                                    LEFT JOIN {$this->db->dbprefix}user u on u.employee_id = pct.employee_id
                                    WHERE 1 AND pct.deleted = 0 AND pct.payroll_date BETWEEN '{$date_from}' AND '{$date_to}' $company $employee $pay_code
                                    GROUP BY pct.cost_code")->result();
        $tot_emp = 0;
        foreach ($qry_tot as $tot) {
            $tot_emp = $tot_emp + $tot->cnt;
        }
        $proj_qry = "SELECT distinct pct.cost_code FROM {$this->db->dbprefix}payroll_closed_transaction pct
                                    LEFT JOIN {$this->db->dbprefix}user u on u.employee_id = pct.employee_id
                                    WHERE 1 AND pct.deleted = 0 AND pct.payroll_date BETWEEN '{$date_from}' AND '{$date_to}' $company $employee $pay_code
                                    ORDER BY pct.cost_code";
        // dbug($proj_qry);
        $proj_cnt = $this->db->query($proj_qry)->num_rows();
        $proj_record = $this->db->query($proj_qry)->result();
        $tot_cnt = 0;
        $page = 1;
        if( $proj_cnt > 0 ){
            foreach ($proj_record as $key => $value) {
                
                $qry = "SELECT distinct pct.employee_id FROM {$this->db->dbprefix}payroll_closed_transaction pct
                    JOIN {$this->db->dbprefix}user u on pct.employee_id = u.employee_id
                    WHERE pct.deleted = 0 AND pct.payroll_date BETWEEN '{$date_from}' AND '{$date_to}' AND pct.cost_code = '{$value->cost_code}' $company $employee $pay_code";
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
                        $this->pdf->addPage('P', 'A4', true);    
                        $this->pdf->SetFontSize( 8);            
                        $xcel_hed = '';

                        $xcel_hed .= '<table style="width:100%;">
                                        <tr>
                                            <td style=" width:50%  ; text-align:left   ; font-size:7  ; ">Run Date: '.date("F d, Y H:i A").'</td>
                                            <td style=" width:50%  ; text-align:right  ; font-size:7  ; ">Page '.$page.'</td>
                                        </tr>
                                        <tr>
                                            <td width="100%" style="text-align:center;">'.$company->company.'</td>
                                        </tr>
                                        <tr>
                                            <td width="100%" style="text-align:center;">'.$title.'</td>
                                        </tr>
                                        <tr>
                                            <td width="100%" style="text-align:center;">Month of '.date('F d, Y',strtotime($date_to)).'</td>
                                        </tr>
                                        <tr>
                                            <td width="100%" style="text-align:center;">As of '.$mdate .'</td>
                                        </tr> 
                                        <tr>
                                            <td width:100% style="font-size:10px;"></td>
                                        </tr>
                                        <tr>
                                            <td width:100% >'.$value->cost_code.'</td>
                                        </tr>
                                        <tr>
                                            <td width:100% style="font-size:10px;"></td>
                                        </tr>
                                        <tr> 
                                            <td width="5%"  style="text-align:left;">No. </td>
                                            <td width="12%" style="text-align:left;">Emp. No. </td>
                                            <td width="38%" style="text-align:left;">Employee Name </td>
                                            <td width="15%" style="text-align:center;">TIN</td>
                                            <td width="15%" style="text-align:right;">Gross Income </td>
                                            <td width="15%" style="text-align:right;">WhTax</td>
                                            
                                        </tr>
                                        <tr> 
                                            <td width="100%" style="font-size:1; border-bottom:3px solid black;"></td>
                                        </tr>
                                        <tr> 
                                            <td width="100%" style="font-size:4;"></td>
                                        </tr>';

                        $limit = ($i - 1) * $allowed_count_per_page;
                        $dtl_qry = "SELECT distinct e.employee_id, e.id_number, u.lastname, u.firstname, u.middleinitial, u.aux, e.tin FROM {$this->db->dbprefix}payroll_closed_transaction pct
                                    JOIN {$this->db->dbprefix}user u on pct.employee_id = u.employee_id
                                    JOIN {$this->db->dbprefix}employee e on pct.employee_id = e.employee_id
                                    WHERE pct.deleted = 0 AND pct.payroll_date BETWEEN '{$date_from}' AND '{$date_to}' AND pct.cost_code = '{$value->cost_code}' $company $employee $pay_code
                                    ORDER BY u.lastname, u.firstname, u.middleinitial
                                    LIMIT {$limit},{$allowed_count_per_page}";
                        $dtl_res = $this->db->query($dtl_qry);

                        $count = 0;
                        $total_gross = 0;
                        $total_whtax = 0;
                        foreach ($dtl_res->result() as $key => $dtl_value) 
                        {            
                            $inc= $this->db->query("select sum(amount) as amount from {$this->db->dbprefix}payroll_closed_transaction 
                                where deleted = 0 AND employee_id = $dtl_value->employee_id and transaction_type_id = 1 and payroll_date BETWEEN '{$date_from}' AND '{$date_to}'")->row();
                            $ded = $this->db->query("select sum(amount) as amount from {$this->db->dbprefix}payroll_closed_transaction 
                                where deleted = 0 AND employee_id = $dtl_value->employee_id and transaction_code IN ('ABSENCES','DEDUCTION_LATE','DEDUCTION_UNDERTIME','LWOP') and payroll_date BETWEEN '{$date_from}' AND '{$date_to}'")->row();
                            $gross = $inc->amount - $ded->amount ;

                            $whtax = $this->db->query("select sum(amount) as amount from {$this->db->dbprefix}payroll_closed_transaction 
                                where deleted = 0 AND employee_id = $dtl_value->employee_id and transaction_code IN ('WHTAX') and payroll_date BETWEEN '{$date_from}' AND '{$date_to}'")->row();
                            if(!empty($dtl_value->aux)){
                                $aux = ' '.$dtl_value->aux.' ';
                            }else{
                                $aux = ' ';
                            }
                                
                            $name = str_replace(' *', '', $dtl_value->lastname).', '.$dtl_value->firstname.$aux.$dtl_value->middleinitial;
                            $xcel_hed .= '
                                        <tr>
                                            <td width="5%"  style="text-align:left;">'.$cnt.'</td>
                                            <td width="12%" style="text-align:left;">'.$dtl_value->id_number.'</td>
                                            <td width="38%" style="text-align:left;">'.$name.'</td>
                                            <td width="15%" style="text-align:center;">'.$dtl_value->tin.'</td> 
                                            <td width="15%" style="text-align:right;">'.number_format($gross,2,'.',',').'</td>
                                            <td width="15%" style="text-align:right;">'.number_format($whtax->amount,2,'.',',').'</td>
                                            
                                        </tr>';
                            $count++;
                            $cnt++;
                            $tot_cnt++;
                            $total_gross += $gross ;
                            $total_whtax += $whtax->amount; 
                            $g_gross += $gross ;
                            $g_whtax += $whtax->amount; 
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
                        $xcel_hed .= '  
                                        <tr> 
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
                                            <td width="15%" style="text-align:right;"></td>
                                            <td width="38%" style="text-align:center;"><b>Total:</b></td>
                                            <td width="15%" style="text-align:right;">'.number_format($total_gross,2,'.',',').'</td>
                                            <td width="15%" style="text-align:right;">'.number_format($total_whtax,2,'.',',').'</td>
                                        </tr>
                                    </table>';

                        if($tot_cnt == $tot_emp ){
                            $xcel_hed .= '
                                    <table style="width:100%;">
                                        <tr>
                                            <td width="5%"  style="text-align:left;"></td>
                                            <td width="12%" style="text-align:left;"></td>
                                            <td width="15%" style="text-align:right;"></td>
                                            <td width="38%" style="text-align:center;"><b>Grand Total:</b></td>
                                            <td width="15%" style="text-align:right;">'.number_format($g_gross,2,'.',',').'</td>
                                            <td width="15%" style="text-align:right;">'.number_format($g_whtax,2,'.',',').'</td>
                                        </tr>
                                        <tr><td></td></tr><tr><td></td></tr>
                                        <tr>
                                            <td width="100%" style="font-size:20;"></td>
                                        </tr>
                                        <tr>
                                            <td width="15%"  style="text-align:right;">Prepared By: </td>
                                            <td width="15%"  style="text-align:left;"> </td>
                                            <td width="30%"  style="text-align:right;">Approved By: </td>
                                            <td width="15%"  style="text-align:right;"></td>
                                            <td width="25%"  style="text-align:left;"> </td>
                                        </tr>
                                        <tr>
                                            <td width="15%"  style="text-align:right;"></td>
                                            <td width="15%"  style="text-align:center; border-top-width: 3px solid black ; ">Name / Signature</td>
                                            <td width="30%"  style="text-align:right;"></td>
                                            <td width="15%"  style="text-align:center; border-top-width: 3px solid black ; ">Name / Signature</td>
                                            <td width="25%"  style="text-align:right;"></td>
                                            
                                        </tr>';
                        }
                        $xcel_hed .='   
                                
                            </table>';
                        $this->pdf->writeHTML($xcel_hed, true, false, true, false, ''); 
                        $page++;
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