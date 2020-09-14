<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class employment_certificate_report extends MY_Controller
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
        $report_type = array("COE w/ Salary","COE w/o Salary");
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
                $html = $this->export_coe_with_salary($company_id, $employee_id, $date_from, $date_to, "Certificate of Employment");                                
                $title = "Certificate of Employment";
                break;
            case '1':
                $html = $this->export_coe_without_salary($company_id, $employee_id, $date_given, "Certificate of Employment");        
                $title = "Certificate of Employment";
                break;
        }
        $this->pdf->writeHTML($html, true, false, true, false, '');
        $this->pdf->Output(ucwords(str_replace(" ","_",$title))."_" . date('dmYHis') . '.pdf', 'I');
    }

    function export_coe_with_salary($company_id, $employee_id, $date_from, $date_to,$title){

        $company_setting_res = $this->db->query('SELECT * FROM '.$this->db->dbprefix('user_company').' WHERE company_id = "'.$company_id.'"')->row();

        $emp_separate = explode(',', $employee_id);
        reset($emp_separate);
        foreach ($emp_separate as $key => $value) {
            $qry = "SELECT * FROM {$this->db->dbprefix}user u
                    LEFT JOIN {$this->db->dbprefix}employee e ON u.employee_id = e.employee_id
                    LEFT JOIN {$this->db->dbprefix}employee_payroll p ON u.employee_id = p.employee_id
                    left join {$this->db->dbprefix}user_company_department d ON u.department_id = d.department_id
                    left join {$this->db->dbprefix}user_position up ON u.position_id = up.position_id
                    WHERE u.employee_id = {$value}";

            $emp_count = $this->db->query($qry)->num_rows();
            $res = $this->db->query($qry)->row();

            if($emp_count > 0){
                $this->pdf->addPage('P', 'A4', true);
                $this->pdf->SetFont( 'Times', '',12);
                
                $xcel .= '<table>
                            <tr><td style="width:100% ; font-size:130 ; "></td></tr>
                            <tr>    
                                <td style="width:100% ; text-align:center ; font-size:18 ; "><b><u>C</u> <u>E</u> <u>R</u> <u>T</u> <u>I</u> <u>F</u> <u>I</u> <u>C</u> <u>A</u> <u>T</u> <u>E</u> &nbsp; <u>O</u> <u>F</u> &nbsp; <u>E</u> <u>M</u> <u>P</u> <u>L</u> <u>O</u> <u>Y</u> <u>M</u> <u>E</u> <u>N</u> <u>T</u></b></td>
                            </tr>
                            <tr><td></td></tr><tr><td></td></tr>
                            <tr>
                                <td style="width:100% ; ">To whom it may concern,</td>
                            </tr>
                            <tr><td></td></tr><tr><td></td></tr>
                            <tr>
                                <td style="width:100% ; ">This is to certify that <b>'.$res->salutation.' '.$res->firstname.' '.$res->middlename.' '.$res->lastname.'</b> is an employee of <b>'.$company_setting_res->company.'</b> since '.date("F j, Y",strtotime($res->employed_date)).' up to present as <b>'.$res->position.'</b> under our <b>'.$res->department.'.</b></td>
                            </tr>
                            <tr><td></td></tr>
                            <tr>
                                <td style=" width:25%  ; text-align:left   ; "><i>Basic Salary</i></td>
                                <td style=" width: 7%  ; text-align:left   ; "><b>: </b></td>
                                <td style=" width:68%  ; text-align:left   ; "><b>'.$this->encrypt->decode($res->salary).'</b></td>
                            </tr>
                            <tr>
                                <td style=" width:25%  ; text-align:left   ; "><i>13th Mo. Pay</i></td>
                                <td style=" width: 7%  ; text-align:left   ; "><b>: </b></td>
                                <td style=" width:68%  ; text-align:left   ; "><b></b></td>
                            </tr>
                            <tr><td></td></tr>
                            <tr>
                                <td style="width:100% ; ">This certification is issued upon the request of '.$res->salutation.' '.$res->lastname.' for whatever legal purpose it may serve him.</td>
                            </tr>
                            <tr><td></td></tr>
                            <tr>
                                <td style=" width:100% ; text-align:left   ; ">Given this '.date("dS").' day of '.date("F Y").' at '.$company_setting_res->address.'.</td>
                            </tr>
                            <tr><td></td></tr><tr><td></td></tr>
                            <tr><td></td></tr><tr><td></td></tr>
                            <tr>
                                <td style=" width:100% ; text-align:left   ; "><b>'.$this->userinfo['firstname'].' '.$this->userinfo['middlename'].' '.$this->userinfo['lastname'].'</b></td>
                            </tr>
                            <tr>
                                <td style=" width:100% ; text-align:left   ; "><i>'.$this->userinfo['position'].'</i></td>
                            </tr>
                            <tr>
                                <td style=" width:100% ; text-align:left   ; "><b>'.$this->userinfo['department'].'</b></td>
                            </tr>
                        </table>';
            }
            $this->pdf->writeHTML($xcel, true, false, true, false, '');
        }
    }

    function export_coe_without_salary($company_id, $employee_id, $date_from, $date_to,$title){

        $company_setting_res = $this->db->query('SELECT * FROM '.$this->db->dbprefix('user_company').' WHERE company_id = "'.$company_id.'"')->row();

        $emp_separate = explode(',', $employee_id);
        reset($emp_separate);
        foreach ($emp_separate as $key => $value) {
            $qry = "SELECT * FROM {$this->db->dbprefix}user u
                    LEFT JOIN {$this->db->dbprefix}employee e ON u.employee_id = e.employee_id
                    LEFT JOIN {$this->db->dbprefix}employee_payroll p ON u.employee_id = p.employee_id
                    left join {$this->db->dbprefix}user_company_department d ON u.department_id = d.department_id
                    left join {$this->db->dbprefix}user_position up ON u.position_id = up.position_id
                    WHERE u.employee_id = {$value}";

            $emp_count = $this->db->query($qry)->num_rows();
            $res = $this->db->query($qry)->row();

            if($emp_count > 0){
                $this->pdf->addPage('P', 'A4', true);
                $this->pdf->SetFont( 'Times', '',12);
                
                $xcel .= '<table>
                            <tr><td style="width:100% ; font-size:130 ; "></td></tr>
                            <tr>    
                                <td style="width:100% ; text-align:center ; font-size:18 ; "><b><u>C</u> <u>E</u> <u>R</u> <u>T</u> <u>I</u> <u>F</u> <u>I</u> <u>C</u> <u>A</u> <u>T</u> <u>E</u> &nbsp; <u>O</u> <u>F</u> &nbsp; <u>E</u> <u>M</u> <u>P</u> <u>L</u> <u>O</u> <u>Y</u> <u>M</u> <u>E</u> <u>N</u> <u>T</u></b></td>
                            </tr>
                            <tr><td></td></tr><tr><td></td></tr>
                            <tr>
                                <td style="width:100% ; ">To whom it may concern,</td>
                            </tr>
                            <tr><td></td></tr><tr><td></td></tr>
                            <tr>
                                <td style="width:100% ; ">This is to certify that <b>'.$res->salutation.' '.$res->firstname.' '.$res->middlename.' '.$res->lastname.'</b> is an employee of <b>'.$company_setting_res->company.'</b> since '.date("F j, Y",strtotime($res->employed_date)).' up to present as <b>'.$res->position.'</b> under our <b>'.$res->department.'.</b></td>
                            </tr>
                            <tr><td></td></tr>
                            <tr><td></td></tr>
                            <tr>
                                <td style="width:100% ; ">This certification is issued upon the request of '.$res->salutation.' '.$res->lastname.' for whatever legal purpose it may serve him.</td>
                            </tr>
                            <tr><td></td></tr>
                            <tr>
                                <td style=" width:100% ; text-align:left   ; ">Given this '.date("dS").' day of '.date("F Y").' at '.$company_setting_res->address.'.</td>
                            </tr>
                            <tr><td></td></tr><tr><td></td></tr>
                            <tr><td></td></tr><tr><td></td></tr>
                            <tr>
                                <td style=" width:100% ; text-align:left   ; "><b>'.$this->userinfo['firstname'].' '.$this->userinfo['middlename'].' '.$this->userinfo['lastname'].'</b></td>
                            </tr>
                            <tr>
                                <td style=" width:100% ; text-align:left   ; "><i>'.$this->userinfo['position'].'</i></td>
                            </tr>
                            <tr>
                                <td style=" width:100% ; text-align:left   ; "><b>'.$this->userinfo['department'].'</b></td>
                            </tr>
                        </table>';
            }
            $this->pdf->writeHTML($xcel, true, false, true, false, '');
        }
    }
}
/* End of file */
/* Location: system/application */
?>