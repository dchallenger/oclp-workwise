<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class loan_report extends MY_Controller
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
        $report_type_res = $this->db->query("SELECT * FROM {$this->db->dbprefix}payroll_loan_type")->result_array();
        $report_type_html = '<select id="report_type_id" multiple="multiple" class="multi-select" name="report_type_id[]">';            
            foreach($report_type_res as $report_type_value){
                $report_type_html .= '<option value="'.$report_type_value["loan_type_id"].'">'.$report_type_value["loan_type"].'</option>';
            }
        $report_type_html .= '</select>'; 

        $loan_status_res = $this->db->query("SELECT * FROM {$this->db->dbprefix}loan_status")->result_array();
        $loan_status_html = '<select id="loan_status_id" multiple="multiple" class="multi-select" name="loan_status_id[]">';            
            foreach($loan_status_res as $loan_status_value){
                $loan_status_html .= '<option value="'.$loan_status_value["loan_status_id"].'">'.$loan_status_value["loan_status"].'</option>';
            }
        $loan_status_html .= '</select>'; 

        $company = $this->db->query('SELECT * FROM '.$this->db->dbprefix('user_company').'')->result_array();
        $company_html = '<select id="company_id" multiple="multiple" class="multi-select" name="company_id[]">';
            foreach($company as $company_record){
                $company_html .= '<option value="'.$company_record["company_id"].'">'.$company_record["company"].'</option>';
            }
        $company_html .= '</select>';

        $response->report_type_html = $report_type_html;
        $response->loan_status_html = $loan_status_html;
        $response->company_html = $company_html;
        $data['json'] = $response;
        $this->load->view('template/ajax', $data);  
    }

    function export_report()
    {
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

        $loan_id = ''; 
        if(isset($_POST['report_type_id']))
        {
            $loan_arr = array();
            foreach ($_POST['report_type_id'] as $value) 
            {
                $loan_arr[] = $value;    
            }
            $loan_id = implode(',', $loan_arr);
        }

        $loan_status_id = '';
        if(isset($_POST['loan_status_id']))
        {
            $loan_stat_arr = array();
            foreach ($_POST['loan_status_id'] as $value) 
            {
                 $loan_stat_arr[] = $value;
            }
            $loan_status_id = implode(',',$loan_stat_arr);
        }
        
        $this->load->library('pdf');
        $html = $this->export_loan($company_id, $loan_id, $loan_status_id, "Employee Loans");
        $title = "Employee Loans";
        $this->pdf->SetMargins(5, 5, 5);
        $this->pdf->addPage('P', 'LETTER', true);
        $this->pdf->SetFontSize( 8 );
        $this->pdf->writeHTML($html, true, false, true, false, '');
        $this->pdf->Output(ucwords(str_replace(" ","_",$title))."_" . date('dmYHis') . '.pdf', 'I');
    }

    function export_loan($company_id, $loan_id, $loan_status_id, $title)
    {   
        
        $xcel = '<table style="width:100%;">
                    <tr>
                        <td colspan="8" style="text-align:center;font-size:100px;">
                            '.$title.'
                        </td>
                    </tr>
                    <tr><td></td></tr>
                </table>';
        
        $xcel .= '
            <table style="width:100%; padding: 10px, 10px, 10px, 10px" border="1" >
                <tr>
                    <td colspan=2.5 style="text-align:center; font-size:6;"><b>Employee</b></td>
                    <td colspan=2 style="text-align:center; font-size:6;"><b>Loan<br>Status<br>Amount</b></td>
                    <td colspan=1.5 style="text-align:center; font-size:6;"><b>Loan Interest<br>Loan Interest Amount<br>Monthly Payable</b></td>
                    <td colspan=1.5 style="text-align:center; font-size:6;"><b>No.of Payments<br>No.of Payments Paid<br>No.of Payments Remaining</b></td>
                    <td colspan=1.5 style="text-align:center; font-size:6;"><b>Release Date<br>Start Date<br>Last Pay Date</b></td>
                    <td colspan=1.5 style="text-align:center; font-size:6;"><b>Beginging Balance<br>Running Balance<br>Total Amount Paid</b></td>
                </tr>
            </table>';

        $loan_dtl_res = $this->db->query('SELECT * FROM '.$this->db->dbprefix('employee_loan').' WHERE loan_id IN ('.$loan_id.') AND loan_status_id IN ('.$loan_status_id.')')->result();
        
        foreach ($loan_dtl_res as $key => $value) 
        {
            $emp_name_res = $this->db->query('SELECT * FROM '.$this->db->dbprefix('user').' WHERE employee_id = "'.$value->employee_id.'"')->row();
            $emp_name = $emp_name_res->lastname.', '.$emp_name_res->firstname.' '.$emp_name_res->aux.' '.$emp_name_res->middleinitial;
            
            $loan_name_res = $this->db->query('SELECT * FROM '.$this->db->dbprefix('payroll_loan_type').' WHERE loan_type_id = '.$value->loan_id.'')->row();
            $loan_name = $loan_name_res->loan_type;

            $loan_status_res = $this->db->query('SELECT * FROM '.$this->db->dbprefix('loan_status').' WHERE loan_status_id = '.$value->loan_status_id.'')->row();
            $loan_status = $loan_status_res->loan_status;

            $description = $value->description;
            $status = $value->loan_status_id;
            $amount = $value->amount;            

            $interest = $value->interest;
            $system_interest = $value->system_interest;
            $system_amortization = $value->system_amortization;

            $no_payments = $value->no_payments;
            $no_payments_paid = $value->no_payments_paid;
            $no_payments_rem = $value->no_payments_remaining;

            $release_date = $value->release_date;
            $start_date = $value->start_date;
            $last_paymemt_date = $value->last_payment_date;

            $beginning_bal = $value->beginning_balance;
            $running_bal = $value->running_balance;
            $total_amount_paid = $value->total_amount_paid;

        $xcel .= '
            <table style="width:100%; padding: 10px, 10px, 10px, 10px" border="1">
                <tr>
                    <td colspan=2.5 style="text-align:center;"><b>'.$emp_name.'</b><br><br>'.$loan_name.'<br>'.$loan_status.'</td>
                    <td colspan=2 style="text-align:center;">&nbsp;<br>'.$description.'<br>'.$status.'<br>'.$amount.'</td>
                    <td colspan=1.5 style="text-align:rigth;">&nbsp;<br>'.$interest.'<br>'.$system_interest.'<br>'.$system_amortization.'</td>
                    <td colspan=1.5 style="text-align:rigth;">&nbsp;<br>'.$no_payments.'<br>'.$no_payments_paid.'<br>'.$no_payments_rem.'</td>
                    <td colspan=1.5 style="text-align:rigth;">&nbsp;<br>'.$release_date.'<br>'.$start_date.'<br>'.$last_paymemt_date.'</td>
                    <td colspan=1.5 style="text-align:rigth;">&nbsp;<br>'.$beginning_bal.'<br>'.$running_bal.'<br>'.$total_amount_paid.'</td>
                </tr>
            </table>
        ';
        }
        
        return $xcel;
    }
}

/* End of file */
/* Location: system/application */
?>