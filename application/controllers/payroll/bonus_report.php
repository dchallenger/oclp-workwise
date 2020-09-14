<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class bonus_report extends my_controller
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
        $report_type = array("Bonus");
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
                $html = $this->export_bonus($company_id, $employee_id, $payroll_date, "Bonus Report");
                $title = "Bonus";
                break;            
            
        }
        $this->pdf->writeHTML($html, true, false, true, false, '');
        $this->pdf->Output(ucwords(str_replace(" ","_",$title))."_" . date('dmYHis') . '.pdf', 'I');
    }

    function export_bonus($company_id, $employee_id, $payroll_date, $title){

        $this->pdf->addPage('P', 'LEGAL', true);
        $this->pdf->SetFontSize( 8 );

        /*  Header */
        /*  BEGIN  */
        $qry = "SELECT DISTINCT payroll_date 
            FROM {$this->db->dbprefix}payroll_closed_transaction
            WHERE employee_id IN ($employee_id)
            AND transaction_id = 10
            AND inserted_from_id = 6
            ORDER BY payroll_date";
        $res = $this->db->query($qry)->result();
        $res_row = $this->db->query($qry)->num_rows();
        
        $xcel = '<table>
                    <tr>
                        <td style="border:1px solid black;text-align:center;"></td>
                        <td colspan = "2" style="border:1px solid black;text-align:center;"></td>
                        <td colspan = "'.$res_row.'" style="border:1px solid black;text-align:center;">Bonus</td>
                    </tr>
                    <tr>
                        <td style="border:1px solid black;text-align:left;">Employee ID</td>
                        <td colspan="2" style="border:1px solid black;text-align:left;">Employee</td>';
        for ($count=0; $count < $res_row  ; $count++) { 
               $xcel .='<td style="border:1px solid black;text-align:center;vertical-align:top;">'.$res[$count]->payroll_date.'</td>';
        }

        $xcel .= '  </tr>';

        /*   END   */

        /* DETAILS */
        /*  BEGIN  */
        $emp_separate = explode(',', $employee_id);
        reset($emp_separate);
        
        foreach ($emp_separate as $key => $emp_id) {   
            
            $employee =$this->db->query("SELECT id_number, lastname, firstname, middlename 
                        FROM {$this->db->dbprefix}user u
                        JOIN {$this->db->dbprefix}employee e on u.employee_id = e.employee_id
                        where e.employee_id = $emp_id")->row(); 
            
            $xcel .='<tr>
                        <td style="border:1px solid black;text-align:left;">'.$employee->id_number.'</td>
                        <td colspan = "2" style="border:1px solid black;text-align:left;">'.$employee->lastname.', '.$employee->firstname.' '.$employee->middlename.'</td>';
        
            $qry_1 = "SELECT DISTINCT payroll_date 
                    FROM {$this->db->dbprefix}payroll_closed_transaction
                    WHERE employee_id = $emp_id
                    AND transaction_id = 10
                    AND inserted_from_id = 6
                    ORDER BY payroll_date";

            $pd_res_1 = $this->db->query($qry_1)->result();
            
            for ($ctr2=0; $ctr2 < $res_row  ; $ctr2++) {    
                $pd_qry_1 = "SELECT sum( amount ) as amount
                          FROM {$this->db->dbprefix}payroll_closed_transaction
                          WHERE transaction_id = 10
                          AND inserted_from_id = 6
                          AND employee_id = $emp_id
                          AND payroll_date = '".$pd_res_1[$ctr2]->payroll_date."'
                    GROUP BY employee_id, payroll_date ";

                $res_1 = $this->db->query($pd_qry_1)->row();


                    $xcel .='<td style="border:1px solid black;text-align:center;vertical-align:top;">'.$res_1->amount.'</td>';
                }
            
            $xcel .='   </tr>';
        }

        $xcel .= '</table>';
        /*   END   */

        $this->pdf->writeHTML($xcel, true, false, true, false, '');  
        $this->pdf->lastPage();   
    }
}
/* End of file */
/* Location: system/application */
?>
