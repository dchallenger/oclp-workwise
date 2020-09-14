<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class govt_certificate_report extends MY_Controller
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

    function get_parameters()
    {
        //Select Report type:
        $report_type = array("SSS Certificate", "Pag-Ibig Certificate", "PhilHealth Certificate");
        $report_type_html = '<select id="report_type_id" name="report_type_id">';
            foreach($report_type as $report_type_id => $report_type_value){
                $report_type_html .= '<option value="'.$report_type_id.'">'.$report_type_value.'</option>';
            }
        $report_type_html .= '</select>'; 

        //Select Company
        $company = $this->db->query('SELECT * FROM '.$this->db->dbprefix('user_company').'')->result_array();
        $company_html = '<select id="company_id" multiple="multiple" class="multi-select" name="company_id">';
            foreach($company as $company_record){
                 $company_html .= '<option value="'.$company_record["company_id"].'">'.$company_record["company"].'</option>';
            }
        $company_html .= '</select>';

        //Select Employee's
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

    function employee_multiple()
    {
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

    function export_report()
    {
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
        
        $date_from = date("Y-m-d", strtotime($_POST['date_range_from']));
        $date_to = date("Y-m-d", strtotime($_POST['date_range_to']));

        $this->load->library('pdf');        
        switch ($_POST['report_type_id']) 
        {
            case '0':
                $html = $this->export_sss_certificate($company_id, $employee_id, $date_from, $date_to, "SSS Certificate");        
                $title = "SSS Certificate";
                break;
            case '1':
                $html = $this->export_hdmf_certificate($company_id, $employee_id, $date_from, $date_to, "Pag-Ibig Certificate");
                $title = "Pag-Ibig Certificate";
                break;
            case '2':
                $html = $this->export_philhealth_certificate($company_id, $employee_id, $date_from, $date_to, "PhilHealth Certificate");
                $title = "PhilHealth Certificate";
                break;
        }
        $this->pdf->writeHTML($html, true, false, true, false, '');
        $this->pdf->Output(ucwords(str_replace(" ","_",$title))."_" . date('dmYHis') . '.pdf', 'I');
    }

    function export_sss_certificate($company_id, $employee_id, $date_from, $date_to, $title){
        
        $company_setting_res = $this->db->query('SELECT * FROM '.$this->db->dbprefix('user_company').' WHERE company_id = "'.$company_id.'"')->row();

        $emp_separate = explode(',', $employee_id);
        reset($emp_separate);
        foreach ($emp_separate as $key => $value) {

            $qry = "SELECT * FROM {$this->db->dbprefix}payroll_closed_transaction p
                    LEFT JOIN {$this->db->dbprefix}user u on p.employee_id = u.employee_id
                    LEFT JOIN {$this->db->dbprefix}employee e on p.employee_id = e.employee_id
                    WHERE p.payroll_date between '{$date_from}' AND '{$date_to}' AND p.employee_id = {$value}";
            
            $emp_count = $this->db->query($qry)->num_rows();
            $res = $this->db->query($qry)->row();

            if($emp_count > 0){
                $dtl_qry = "SELECT t.payroll_date, sbr_sss_date, sbr_sss, sum(amount) as amount  FROM hr_payroll_closed_transaction t
                            LEFT JOIN hr_payroll_period p ON p.payroll_date = t.payroll_date
                            WHERE transaction_code = 'SSS_EMP' AND t.payroll_date between '{$date_from}' AND '{$date_to}' AND t.employee_id = $value
                            GROUP BY MONTH(t.payroll_date),YEAR(t.payroll_date), sbr_sss_date, sbr_sss  
                            ORDER BY t.payroll_date DESC";

                $dtl_res = $this->db->query($dtl_qry);

                $this->pdf->addPage('P', 'A4', true);
                $this->pdf->SetFont( 'Times', '',12);
                
                
                $xcel = '<table>
                            <tr><td></td></tr><tr><td></td></tr><tr><td></td></tr>
                            <tr>
                                <td style=" width:100% ; text-align:center ; font-size:16 ; "><b>CERTIFICATE OF SSS CONTRIBUTION</b></td>
                            </tr>
                            <tr><td></td></tr>
                            <tr>
                                <td style=" width:100% ; text-align:left   ; ">To whom it may concern: </td>
                            </tr>
                            <tr><td></td></tr>
                            <tr>
                                <td style=" width:100% ; text-align:left   ; ">This is to certify that '.$company_setting_res->company.' with SSS No. <b>'.$company_setting_res->sss_no.'</b> has remitted the following contributions for : </td>
                            </tr>
                            <tr><td></td></tr>
                            <tr>
                                <td style=" width:35%  ; text-align:left   ; ">Name of Employee </td>
                                <td style=" width: 7%  ; text-align:left   ; ">: </td>
                                <td style=" width:58%  ; text-align:left   ; "><b>'.$res->firstname.' '.$res->middlename.' '.$res->lastname.'</b></td>
                            </tr>
                            <tr><td></td></tr>
                            <tr>
                                <td style=" width:35%  ; text-align:left   ; ">SSS Number</td>
                                <td style=" width: 7%  ; text-align:left   ; ">: </td>
                                <td style=" width:58%  ; text-align:left   ; "><b>'.$res->sss.'</b></td>
                            </tr>
                            <tr><td></td></tr>
                            
                        </table>
                        <table style="width: 100%">
                            <tr>
                                <td style=" width:25%  ; font-size: 4 ; text-align:center ; border-right-width:3px ; border-top-width:3px ; border-left-width:3px ; "></td>
                                <td style=" width:25%  ; font-size: 4 ; text-align:center ; border-right-width:3px ; border-top-width:3px ; "></td>
                                <td style=" width:35%  ; font-size: 4 ; text-align:center ; border-right-width:3px ; border-top-width:3px ; "></td>
                                <td style=" width:15%  ; font-size: 4 ; text-align:center ; border-right-width:3px ; border-top-width:3px ; "></td>
                            </tr>
                            <tr>
                                <td style=" width:25%  ; font-size:11 ; text-align:center ; border-right-width:3px ; border-bottom-width:3px ; border-left-width:3px ; "><b>Applicable Month</b></td>
                                <td style=" width:25%  ; font-size:11 ; text-align:center ; border-right-width:3px ; border-bottom-width:3px ; "><b>Date Paid</b></td>
                                <td style=" width:35%  ; font-size:11 ; text-align:center ; border-right-width:3px ; border-bottom-width:3px ; "><b>OR Number</b></td>
                                <td style=" width:15%  ; font-size:11 ; text-align:center ; border-right-width:3px ; border-bottom-width:3px ; "><b>Amount</b></td>
                            </tr>';    
                
                foreach ($dtl_res->result() as $key => $dtl) {
                    $xcel .='<tr>
                                <td style=" width:25%  ; font-size: 4 ; text-align:center ; border-right-width:3px ; border-top-width:3px ; border-left-width:3px ; "></td>
                                <td style=" width:25%  ; font-size: 4 ; text-align:center ; border-right-width:3px ; border-top-width:3px ; "></td>
                                <td style=" width:35%  ; font-size: 4 ; text-align:center ; border-right-width:3px ; border-top-width:3px ; "></td>
                                <td style=" width:15%  ; font-size: 4 ; text-align:center ; border-right-width:3px ; border-top-width:3px ; "></td>
                            </tr>
                            <tr>
                                <td style=" width:25%  ; font-size:11 ; text-align:right  ; border-right-width:3px ; border-bottom-width:3px ; border-left-width:3px ; ">'.date("F Y",strtotime($dtl->payroll_date)).'</td>
                                <td style=" width:25%  ; font-size:11 ; text-align:right  ; border-right-width:3px ; border-bottom-width:3px ; ">'.date("F j, Y",strtotime($dtl->sbr_sss_date)).'</td>
                                <td style=" width:35%  ; font-size:11 ; text-align:center ; border-right-width:3px ; border-bottom-width:3px ; ">'.$dtl->sbr_sss.'</td>
                                <td style=" width:15%  ; font-size:11 ; text-align:center ; border-right-width:3px ; border-bottom-width:3px ; ">'.$dtl->amount.'</td>
                            </tr>'; 
                }
                
                $xcel .= '</table>
                            <tr><td></td></tr>
                            <tr>
                                <td style=" width:100% ; text-align:left   ; ">This certification is issued upon the request of <b>'.$res->salutation.' '.$res->lastname.'</b> for whatever legal purpose it may serve her.</td>
                            </tr>
                            <tr><td></td></tr>
                            <tr>
                                <td style=" width:100% ; text-align:left   ; ">Given this '.date("dS").' day of '.date("F Y").' at '.$company_setting_res->address.'.</td>
                            </tr>
                            <tr><td></td></tr>
                            <tr><td></td></tr>
                            <tr>
                                <td style=" width:100% ; text-align:left   ; "><b>'.$this->userinfo['firstname'].' '.$this->userinfo['middlename'].' '.$this->userinfo['lastname'].'</b></td>
                            </tr>
                            <tr>
                                <td style=" width:100% ; text-align:left   ; "><b>'.$this->userinfo['department'].'</b></td>
                            </tr>';


                
            }
            $this->pdf->writeHTML($xcel, true, false, true, false, '');
        }
    }

    function export_hdmf_certificate($company_id, $employee_id, $date_from, $date_to, $title){
        
        $company_setting_res = $this->db->query('SELECT * FROM '.$this->db->dbprefix('user_company').' WHERE company_id = "'.$company_id.'"')->row();

        $emp_separate = explode(',', $employee_id);
        reset($emp_separate);
        foreach ($emp_separate as $key => $value) {

            $qry = "SELECT * FROM {$this->db->dbprefix}payroll_closed_transaction p
                    LEFT JOIN {$this->db->dbprefix}user u on p.employee_id = u.employee_id
                    LEFT JOIN {$this->db->dbprefix}employee e on p.employee_id = e.employee_id
                    WHERE p.payroll_date between '{$date_from}' AND '{$date_to}' AND p.employee_id = {$value}";
            
            $emp_count = $this->db->query($qry)->num_rows();
            $res = $this->db->query($qry)->row();

            if($emp_count > 0){
                $dtl_qry = "SELECT t.payroll_date, sbr_sss_date, sbr_sss, sum(amount) as amount  FROM hr_payroll_closed_transaction t
                            LEFT JOIN hr_payroll_period p ON p.payroll_date = t.payroll_date
                            WHERE transaction_code = 'HDMF_EMP' AND t.payroll_date between '{$date_from}' AND '{$date_to}' AND t.employee_id = $value
                            GROUP BY MONTH(t.payroll_date),YEAR(t.payroll_date), sbr_sss_date, sbr_sss  
                            ORDER BY t.payroll_date DESC";

                $dtl_res = $this->db->query($dtl_qry);

                $this->pdf->addPage('P', 'A4', true);
                $this->pdf->SetFont( 'Times', '',12);
                
                
                $xcel = '<table>
                            <tr><td></td></tr><tr><td></td></tr><tr><td></td></tr>
                            <tr>
                                <td style=" width:100% ; text-align:center ; font-size:16 ; "><b>CERTIFICATE OF PAGIBIG CONTRIBUTION</b></td>
                            </tr>
                            <tr><td></td></tr>
                            <tr>
                                <td style=" width:100% ; text-align:left   ; ">To whom it may concern: </td>
                            </tr>
                            <tr><td></td></tr>
                            <tr>
                                <td style=" width:100% ; text-align:left   ; ">This is to certify that '.$company_setting_res->company.' with SSS No. <b>'.$company_setting_res->sss_no.'</b> has remitted the following contributions for : </td>
                            </tr>
                            <tr><td></td></tr>
                            <tr>
                                <td style=" width:35%  ; text-align:left   ; ">Name of Employee </td>
                                <td style=" width: 7%  ; text-align:left   ; ">: </td>
                                <td style=" width:58%  ; text-align:left   ; "><b>'.$res->firstname.' '.$res->middlename.' '.$res->lastname.'</b></td>
                            </tr>
                            <tr><td></td></tr>
                            <tr>
                                <td style=" width:35%  ; text-align:left   ; ">PAGIBIG/SSS Number</td>
                                <td style=" width: 7%  ; text-align:left   ; ">: </td>
                                <td style=" width:58%  ; text-align:left   ; "><b>'.$res->sss.'</b></td>
                            </tr>
                            <tr><td></td></tr>
                            
                        </table>
                        <table style="width: 100%">
                            <tr>
                                <td style=" width:25%  ; font-size: 4 ; text-align:center ; border-right-width:3px ; border-top-width:3px ; border-left-width:3px ; "></td>
                                <td style=" width:25%  ; font-size: 4 ; text-align:center ; border-right-width:3px ; border-top-width:3px ; "></td>
                                <td style=" width:35%  ; font-size: 4 ; text-align:center ; border-right-width:3px ; border-top-width:3px ; "></td>
                                <td style=" width:15%  ; font-size: 4 ; text-align:center ; border-right-width:3px ; border-top-width:3px ; "></td>
                            </tr>
                            <tr>
                                <td style=" width:25%  ; font-size:11 ; text-align:center ; border-right-width:3px ; border-bottom-width:3px ; border-left-width:3px ; "><b>Applicable Month</b></td>
                                <td style=" width:25%  ; font-size:11 ; text-align:center ; border-right-width:3px ; border-bottom-width:3px ; "><b>Date Paid</b></td>
                                <td style=" width:35%  ; font-size:11 ; text-align:center ; border-right-width:3px ; border-bottom-width:3px ; "><b>OR Number</b></td>
                                <td style=" width:15%  ; font-size:11 ; text-align:center ; border-right-width:3px ; border-bottom-width:3px ; "><b>Amount</b></td>
                            </tr>';    
                
                foreach ($dtl_res->result() as $key => $dtl) {
                    $xcel .='<tr>
                                <td style=" width:25%  ; font-size: 4 ; text-align:center ; border-right-width:3px ; border-top-width:3px ; border-left-width:3px ; "></td>
                                <td style=" width:25%  ; font-size: 4 ; text-align:center ; border-right-width:3px ; border-top-width:3px ; "></td>
                                <td style=" width:35%  ; font-size: 4 ; text-align:center ; border-right-width:3px ; border-top-width:3px ; "></td>
                                <td style=" width:15%  ; font-size: 4 ; text-align:center ; border-right-width:3px ; border-top-width:3px ; "></td>
                            </tr>
                            <tr>
                                <td style=" width:25%  ; font-size:11 ; text-align:right  ; border-right-width:3px ; border-bottom-width:3px ; border-left-width:3px ; ">'.date("F Y",strtotime($dtl->payroll_date)).'</td>
                                <td style=" width:25%  ; font-size:11 ; text-align:right  ; border-right-width:3px ; border-bottom-width:3px ; ">'.date("F j, Y",strtotime($dtl->sbr_sss_date)).'</td>
                                <td style=" width:35%  ; font-size:11 ; text-align:center ; border-right-width:3px ; border-bottom-width:3px ; ">'.$dtl->sbr_sss.'</td>
                                <td style=" width:15%  ; font-size:11 ; text-align:center ; border-right-width:3px ; border-bottom-width:3px ; ">'.$dtl->amount.'</td>
                            </tr>'; 
                }
                
                $xcel .= '</table>
                            <tr><td></td></tr>
                            <tr>
                                <td style=" width:100% ; text-align:left   ; ">This certification is issued upon the request of <b>'.$res->salutation.' '.$res->lastname.'</b> for whatever legal purpose it may serve her.</td>
                            </tr>
                            <tr><td></td></tr>
                            <tr>
                                <td style=" width:100% ; text-align:left   ; ">Given this '.date("dS").' day of '.date("F Y").' at '.$company_setting_res->address.'.</td>
                            </tr>
                            <tr><td></td></tr>
                            <tr><td></td></tr>
                            <tr>
                                <td style=" width:100% ; text-align:left   ; "><b>'.$this->userinfo['firstname'].' '.$this->userinfo['middlename'].' '.$this->userinfo['lastname'].'</b></td>
                            </tr>
                            <tr>
                                <td style=" width:100% ; text-align:left   ; "><b>'.$this->userinfo['department'].'</b></td>
                            </tr>';


                
            }
            $this->pdf->writeHTML($xcel, true, false, true, false, '');
        }
    }

    function export_philhealth_certificate($company_id, $employee_id, $date_from, $date_to, $title){
        
        $company_setting_res = $this->db->query('SELECT * FROM '.$this->db->dbprefix('user_company').' WHERE company_id = "'.$company_id.'"')->row();

        $emp_separate = explode(',', $employee_id);
        reset($emp_separate);
        foreach ($emp_separate as $key => $value) {

            $qry = "SELECT * FROM {$this->db->dbprefix}payroll_closed_transaction p
                    LEFT JOIN {$this->db->dbprefix}user u on p.employee_id = u.employee_id
                    LEFT JOIN {$this->db->dbprefix}employee e on p.employee_id = e.employee_id
                    WHERE p.payroll_date between '{$date_from}' AND '{$date_to}' AND p.employee_id = {$value}";
            
            $emp_count = $this->db->query($qry)->num_rows();
            $res = $this->db->query($qry)->row();

            if($emp_count > 0){
                $dtl_qry = "SELECT t.payroll_date, sbr_sss_date, sbr_sss, sum(amount) as amount  FROM hr_payroll_closed_transaction t
                            LEFT JOIN hr_payroll_period p ON p.payroll_date = t.payroll_date
                            WHERE transaction_code = 'PHIC_EMP' AND t.payroll_date between '{$date_from}' AND '{$date_to}' AND t.employee_id = $value
                            GROUP BY MONTH(t.payroll_date),YEAR(t.payroll_date), sbr_sss_date, sbr_sss  
                            ORDER BY t.payroll_date DESC";

                $dtl_res = $this->db->query($dtl_qry);

                $this->pdf->addPage('P', 'A4', true);
                $this->pdf->SetFont( 'Times', '',12);
                
                
                $xcel = '<table>
                            <tr><td></td></tr><tr><td></td></tr><tr><td></td></tr>
                            <tr>
                                <td style=" width:100% ; text-align:center ; font-size:16 ; "><b>CERTIFICATE OF PHILHEALTH CONTRIBUTION</b></td>
                            </tr>
                            <tr><td></td></tr>
                            <tr>
                                <td style=" width:100% ; text-align:left   ; ">To whom it may concern: </td>
                            </tr>
                            <tr><td></td></tr>
                            <tr>
                                <td style=" width:100% ; text-align:left   ; ">This is to certify that '.$company_setting_res->company.' with SSS No. <b>'.$company_setting_res->philhealth_no.'</b> has remitted the following contributions for : </td>
                            </tr>
                            <tr><td></td></tr>
                            <tr>
                                <td style=" width:35%  ; text-align:left   ; ">Name of Employee </td>
                                <td style=" width: 7%  ; text-align:left   ; ">: </td>
                                <td style=" width:58%  ; text-align:left   ; "><b>'.$res->firstname.' '.$res->middlename.' '.$res->lastname.'</b></td>
                            </tr>
                            <tr><td></td></tr>
                            <tr>
                                <td style=" width:35%  ; text-align:left   ; ">PhilHealth Number</td>
                                <td style=" width: 7%  ; text-align:left   ; ">: </td>
                                <td style=" width:58%  ; text-align:left   ; "><b>'.$res->philhealth.'</b></td>
                            </tr>
                            <tr><td></td></tr>
                            
                        </table>
                        <table style="width: 100%">
                            <tr>
                                <td style=" width:25%  ; font-size: 4 ; text-align:center ; border-right-width:3px ; border-top-width:3px ; border-left-width:3px ; "></td>
                                <td style=" width:25%  ; font-size: 4 ; text-align:center ; border-right-width:3px ; border-top-width:3px ; "></td>
                                <td style=" width:35%  ; font-size: 4 ; text-align:center ; border-right-width:3px ; border-top-width:3px ; "></td>
                                <td style=" width:15%  ; font-size: 4 ; text-align:center ; border-right-width:3px ; border-top-width:3px ; "></td>
                            </tr>
                            <tr>
                                <td style=" width:25%  ; font-size:11 ; text-align:center ; border-right-width:3px ; border-bottom-width:3px ; border-left-width:3px ; "><b>Applicable Month</b></td>
                                <td style=" width:25%  ; font-size:11 ; text-align:center ; border-right-width:3px ; border-bottom-width:3px ; "><b>Date Paid</b></td>
                                <td style=" width:35%  ; font-size:11 ; text-align:center ; border-right-width:3px ; border-bottom-width:3px ; "><b>OR Number</b></td>
                                <td style=" width:15%  ; font-size:11 ; text-align:center ; border-right-width:3px ; border-bottom-width:3px ; "><b>Amount</b></td>
                            </tr>';    
                
                foreach ($dtl_res->result() as $key => $dtl) {
                    $xcel .='<tr>
                                <td style=" width:25%  ; font-size: 4 ; text-align:center ; border-right-width:3px ; border-top-width:3px ; border-left-width:3px ; "></td>
                                <td style=" width:25%  ; font-size: 4 ; text-align:center ; border-right-width:3px ; border-top-width:3px ; "></td>
                                <td style=" width:35%  ; font-size: 4 ; text-align:center ; border-right-width:3px ; border-top-width:3px ; "></td>
                                <td style=" width:15%  ; font-size: 4 ; text-align:center ; border-right-width:3px ; border-top-width:3px ; "></td>
                            </tr>
                            <tr>
                                <td style=" width:25%  ; font-size:11 ; text-align:right  ; border-right-width:3px ; border-bottom-width:3px ; border-left-width:3px ; ">'.date("F Y",strtotime($dtl->payroll_date)).'</td>
                                <td style=" width:25%  ; font-size:11 ; text-align:right  ; border-right-width:3px ; border-bottom-width:3px ; ">'.date("F j, Y",strtotime($dtl->sbr_phic_date)).'</td>
                                <td style=" width:35%  ; font-size:11 ; text-align:center ; border-right-width:3px ; border-bottom-width:3px ; ">'.$dtl->sbr_phic.'</td>
                                <td style=" width:15%  ; font-size:11 ; text-align:center ; border-right-width:3px ; border-bottom-width:3px ; ">'.$dtl->amount.'</td>
                            </tr>'; 
                }
                
                $xcel .= '</table>
                            <tr><td></td></tr>
                            <tr>
                                <td style=" width:100% ; text-align:left   ; ">This certification is issued upon the request of <b>'.$res->salutation.' '.$res->lastname.'</b> for whatever legal purpose it may serve her.</td>
                            </tr>
                            <tr><td></td></tr>
                            <tr>
                                <td style=" width:100% ; text-align:left   ; ">Given this '.date("dS").' day of '.date("F Y").' at '.$company_setting_res->address.'.</td>
                            </tr>
                            <tr><td></td></tr>
                            <tr><td></td></tr>
                            <tr>
                                <td style=" width:100% ; text-align:left   ; "><b>'.$this->userinfo['firstname'].' '.$this->userinfo['middlename'].' '.$this->userinfo['lastname'].'</b></td>
                            </tr>
                            <tr>
                                <td style=" width:100% ; text-align:left   ; "><b>'.$this->userinfo['department'].'</b></td>
                            </tr>';


                
            }
            $this->pdf->writeHTML($xcel, true, false, true, false, '');
        }
    }
}

/* End of file */
/* Location: system/application */
?>