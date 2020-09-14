<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class payroll_crediting_advice_report extends MY_Controller
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
        $report_type = array("Payroll Crediting Advice", "LOI Certificate");
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

        $date_from = date("Y-m-d",strtotime($_POST['date_range_from']));
        $date_to = date("Y-m-d",strtotime($_POST['date_range_to']));

        $this->load->library('pdf');
        switch ($_POST['report_type_id']) 
        {
            //payrolll crediting Advice
            case '0':
                $html = $this->export_payroll_crediting_advice($company_id, $employee_id, $date_from, $date_to, "Payroll Crediting Advice");
                $title = "Payroll Crediting Advice";
                break;
            //LOI Certificate
            case '1':
                $html = $this->export_loi_certificate($company_id, $employee_id, $date_from, $date_to, "LOI Certificate");
                $title = "LOI Certificate";
                break;
            
        }   
        
        $this->pdf->writeHTML($html, true, false, true, false, '');
        $this->pdf->Output(ucwords(str_replace(" ","_",$title))."_" . date('dmYHis') . '.pdf', 'I');
    }
    
    function export_payroll_crediting_advice($company_id, $employee_id, $date_from, $date_to, $title){
         /* EXPORT TO EXCEL */
        $this->_excel_export($company_id, $employee_id, $date_from, $date_to);
    }    

    function export_loi_certificate($company_id, $employee_id, $date_from, $date_to, $title){
       

        /* BEGIN LOI */
        $this->pdf->addPage('P', 'LETTER', true);
        $this->pdf->SetFontSize( 10);

        /* Get current date */
        $current_date = getdate(date("U"));

        /* Declare the following field needed*/

        $company_setting_res = $this->db->query('SELECT * FROM '.$this->db->dbprefix('user_company').' WHERE company_id = "'.$company_id.'"')->row();
        $company_bank_acct_no = $company_setting_res->bank_account_no;

        $salutation = 'Ms.';
        $person_last = 'Aguas';
        $person_name = 'Kathryn D. Aguas'; 
        $person_post = 'Relationship Manager';
        $bank_branch = 'UnionBank Insular-Ayala Branch';
        $bank_address1 = 'Ground Floor Insular Life Building';
        $bank_address2 = 'Ayala Avenue cor Paseo de Roxas St.';
        $bank_address3 = 'Makati City';
        $person_author = 'Michael Chang/ Olga T. Ponce';

        $where = "payroll_date BETWEEN '{$date_from}' AND '{$date_to}' AND employee_id IN ($employee_id) AND transaction_code = 'NETPAY'";
        $this->db->where($where,null,false);
        $this->db->select_sum('amount');
        $total_amount = $this->db->get('payroll_closed_transaction')->row();
        $total_amount = $total_amount->amount;

        /* TEMPLATE OF LOI */
        $page_hed = '
            <div>
                <table>
                    <tr></td></tr><tr><td></td></tr><tr><td></td></tr><tr></td></tr><tr><td></td></tr><tr><td></td></tr><tr></td></tr><tr><td></td></tr><tr><td></td></tr>
                </table>
            </div>
            <div>
                <table>
                    <tr>
                        <td colspan="100%" style="text-align:right;">'."$current_date[month] $current_date[mday], $current_date[year]".'</td>
                    </tr>
                    <tr><td></td></tr><tr><td></td></tr><tr><td></td></tr><tr><td></td></tr><tr><td></td></tr><tr><td></td></tr>
                    <tr>
                        <td colspan="100%" style="text-align:left;">'.$person_name.'</td>
                    </tr>
                    <tr>
                        <td colspan="100%" style="text-align:left;">'.$person_post.'</td>
                    </tr>
                    <tr>
                        <td colspan="100%" style="text-align:left;">'.$bank_branch.'</td>
                    </tr>
                    <tr>
                        <td colspan="100%" style="text-align:left;">'.$bank_address1.'</td>
                    </tr>
                    <tr>
                        <td colspan="100%" style="text-align:left;">'.$bank_address2.'</td>
                    </tr>
                    <tr>
                        <td colspan="100%" style="text-align:left;">'.$bank_address3.'</td>
                    </tr>
                    <tr><td></td></tr><tr><td></td></tr><tr><td></td></tr><tr><td></td></tr>
                    <tr>
                        <td colspan="100%" style="text-align:left;"> Dear '.$salutation.' '.$person_last.', </td>
                    </tr>
                    <tr><td colspan="100%" ></td></tr>
                    <tr>
                        <td colspan="20%" ></td>
                        <td colspan="80%" style="text-align:left;">Please debit  <b>Php '.$total_amount.'</b>, from our account # '.$company_bank_acct_no.'</td>
                    </tr>
                    <tr>
                        <td colspan="10%" ></td>
                        <td colspan="90%" style="text-align:left;">and credit to the employees listed on the following pages.</td>
                    </tr>
                    <tr><td></td></tr><tr><td></td></tr><tr><td></td></tr><tr><td></td></tr>
                    <tr>
                        <td colspan="100%" style="text-align:left;"> Thank you. </td>
                    </tr>
                    <tr><td></td></tr><tr><td></td></tr><tr><td></td></tr><tr><td></td></tr><tr><td></td></tr><tr><td></td></tr>
                    <tr>
                        <td colspan="70%" ></td>
                        <td colspan="30%" style="text-align:left;">Sincerely</td>
                    </tr>
                    <tr><td></td></tr>
                    <tr>
                        <td colspan="70%" ></td>
                        <td colspan="30%" style="text-align:center;">'.$person_author.'</td>
                    </tr>
                </table>
            </div>
        ';
        /* END */
        $this->pdf->writeHTML($page_hed, true, false, true, false, '');  
        $this->pdf->lastPage();   
    }
    
    private function _excel_export($company_id, $employee_id, $date_from, $date_to){

        $qry = "SELECT '001' as 'CURRENCY', c.bank_account_no as 'EMPLOYER ACCT. #',
            1 as 'PAY CODE', e.bank_account_no as 'EMP ACCT. #', t.amount as AMOUNT,
            u.lastname as SURNAME, u.firstname as FIRSTNAME
            FROM {$this->db->dbprefix}user u
            LEFT JOIN {$this->db->dbprefix}employee e ON e.employee_id = u.employee_id
            LEFT JOIN {$this->db->dbprefix}payroll_closed_transaction t ON t.employee_id = u.employee_id
            LEFT JOIN {$this->db->dbprefix}user_company c ON c.company_id = u.company_id
            WHERE t.transaction_code = 'NETPAY' AND u.company_id = {$company_id}
            AND e.employee_id in ({$employee_id}) AND t.payroll_date BETWEEN '{$date_from}' and '{$date_to}'";
        
        $res = $this->db->query($qry);

        $query = $res;
        $fields = $res->list_fields();

        //$export = $this->_export;
        $this->load->library('PHPExcel');       
        $this->load->library('PHPExcel/IOFactory');

        $objPHPExcel = new PHPExcel();

        $objPHPExcel->getProperties()->setTitle("Payroll Crediting Advice")
                    ->setDescription("Payroll Crediting Advice");
                       
        // Assign cell values
        $objPHPExcel->setActiveSheetIndex(0);
        $activeSheet = $objPHPExcel->getActiveSheet();

        //header
        $alphabet  = range('A','Z');
        $alpha_ctr = 0;
        $sub_ctr   = 0;

        //Default column width
        $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);                 
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);                 
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);  
                       
        //Initialize style
        $styleArray = array(
            'font' => array(
                'bold' => true,
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            )
        );

        foreach ($fields as $field) {
            $xcoor = $alphabet[$alpha_ctr];

            $activeSheet->setCellValueExplicit($xcoor . '4', $field, PHPExcel_Cell_DataType::TYPE_STRING);

            $objPHPExcel->getActiveSheet()->getStyle($xcoor . '4')->applyFromArray($styleArray);
            
            $alpha_ctr++;
        }

        for($ctr=1; $ctr<4; $ctr++){

            $objPHPExcel->getActiveSheet()->mergeCells($alphabet[0].$ctr.':'.$alphabet[$alpha_ctr - 1].$ctr);

        }

        $activeSheet->getHeaderFooter()->setOddFooter('&L&BPrinted By:'.$userinfo->firstname.' '.$userinfo->lastname.' &RPage &P of &N');

        $activeSheet->setCellValueExplicit('A1', 'Payroll Crediting Advice', PHPExcel_Cell_DataType::TYPE_STRING); 
        $activeSheet->setCellValueExplicit('A2', date('F d,Y',strtotime($date_from)).' - '.date('F d,Y',strtotime($date_to)), PHPExcel_Cell_DataType::TYPE_STRING); 

        $objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArray);
        $objPHPExcel->getActiveSheet()->getStyle('A2')->applyFromArray($styleArray);
        $objPHPExcel->getActiveSheet()->getStyle('A3')->applyFromArray($styleArray);

        // contents.
        $line = 5;
        foreach ($query->result() as $row) {
            $sub_ctr   = 0;         
            $alpha_ctr = 0;

            foreach ($fields as $field) {
                if ($alpha_ctr >= count($alphabet)) {
                    $alpha_ctr = 0;
                    $sub_ctr++;
                }

                if ($sub_ctr > 0) {
                    $xcoor = $alphabet[$sub_ctr - 1] . $alphabet[$alpha_ctr];
                } 
                else {
                    $xcoor = $alphabet[$alpha_ctr];
                }

                $objPHPExcel->getActiveSheet()->setCellValueExplicit($xcoor . $line, $row->{$field}, PHPExcel_Cell_DataType::TYPE_STRING); 

                $alpha_ctr++;                   
            }
            $line++;
        }   

        // Save it as an excel 2003 file
        $objWriter = IOFactory::createWriter($objPHPExcel, 'Excel5');

        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Content-Type: application/force-download');
        header('Content-Type: application/octet-stream');
        header('Content-Type: application/download');
        header('Content-Disposition: attachment;filename='.date('Y-m-d').'-Payroll_Crediting_Advice'.'.xls');
        header('Content-Transfer-Encoding: binary');
        
        $objWriter->save('php://output');   
    }

}

/* End of file */
/* Location: system/application */
?>