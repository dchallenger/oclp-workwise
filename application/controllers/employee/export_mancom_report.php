<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class export_mancom_report extends my_controller
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

        $this->load->library('pdf');
        $html = $this->export_employee($paycode_id, $company_id, $employee_id, $title);        
        $title = "Employee Records";
        $this->pdf->addPage('L', 'A4', true);
        $this->pdf->SetFontSize( 8 );
        $this->pdf->writeHTML($html, true, false, true, false, '');
        $this->pdf->Output(ucwords(str_replace(" ","_",$title))."_" . date('dmYHis') . '.pdf', 'I');
    }
    
    function export_employee($paycode_id, $company_id, $employee_id, $title){

        if(!empty($company_id)){

            $company = " AND u.company_id IN ($company_id)";
        }
        if(!empty($employee_id)){
            
            $employee = " AND e.employee_id IN ($employee_id)";   
        }

        if(!empty($paycode_id)){
            $pay_code = 'AND ep.paycode_id IN ('.$paycode_id.')';
        }

        $qry = "SELECT  e.id_number AS 'ID NUMBER', 
                CONCAT(u.firstname, ' ',u.middlename,' ', u.lastname, IF(u.aux !='' AND u.aux != ' ' AND u.aux IS NOT NULL, CONCAT(' ',u.aux), '')  ) AS 'EMPLOYEE NAME',
                u.sex AS 'GENDER', u.birth_date AS 'BIRTH DATE', e.sss AS 'SSS NUMBER', e.tin AS 'TIN', ur.job_rank AS 'EMPLOYMENT TYPE', e.original_hired_date AS 'DATE EMPLOYED',
                e.employed_date AS 'CONTRACT START', e.regular_date AS 'DATE PERMANENT', e.end_date AS 'DATE TERMINATED', es.employment_status AS 'EMPLOYMENT STATUS', ep.salary AS 'CURRENT SALARY',
                rt.payroll_rate_type AS 'SALARY RATE TYPE', up.position AS 'CURRENT POSITION', d.department AS 'CURRENT DEPARTMENT', d.department AS 'DEPT', pn.project_name AS 'PROJECT',
                cd.division AS 'DIVISION', g.group_name AS 'GROUP', cs.code_status AS 'CODE STATUS', IF(e.CBE = 1, 'Y', 'N') AS 'INSURANCE TYPE', pp.paycode AS 'PAYCODE', t.taxcode AS 'TAX STATUS', IF(ep.fixed_rate = 1, 'Y', 'N') AS 'ALLOWED OT'
                FROM hr_employee e
                LEFT JOIN hr_employee_payroll ep ON e.employee_id = ep.employee_id
                LEFT JOIN hr_user u ON u.employee_id = e.employee_id
                -- LEFT JOIN hr_employee_work_assignment a ON a.employee_id = e.employee_id
                LEFT JOIN hr_employment_status es ON es.employment_status_id = e.status_id
                LEFT JOIN hr_user_rank ur ON ur.job_rank_id = e.rank_id
                LEFT JOIN hr_payroll_rate_type rt ON ep.payroll_rate_type_id = rt.payroll_rate_type_id
                LEFT JOIN hr_user_position up ON up.position_id = u.position_id
                LEFT JOIN hr_user_company_department d ON u.department_id = d.department_id
                LEFT JOIN hr_project_name pn ON pn.project_name_id = u.project_name_id
                LEFT JOIN hr_user_company_division cd ON cd.division_id = u.division_id
                LEFT JOIN hr_group_name g ON g.group_name_id = u.group_name_id
                LEFT JOIN hr_code_status cs ON cs.code_status_id = u.code_status_id
                LEFT JOIN hr_payroll_paycode pp ON pp.paycode_id = ep.paycode_id
                LEFT JOIN hr_taxcode t ON ep.taxcode_id = t.taxcode_id
                WHERE 1 $company $employee $pay_code";
                // WHERE 1 and a.assignment = 1 $company $employee $pay_code";

        $res = $this->db->query($qry);

        $query = $res;
        $fields = $res->list_fields();

        //$export = $this->_export;
        $this->load->library('PHPExcel');       
        $this->load->library('PHPExcel/IOFactory');

        $objPHPExcel = new PHPExcel();

        $objPHPExcel->getProperties()->setTitle("Employee Records")
                    ->setDescription("Employee Records");
                       
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
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true); 
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('R')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('S')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('T')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('U')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('V')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('W')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('X')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('Y')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('Z')->setAutoSize(true);
                              
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
        $mdate = getdate(date("U"));
        $mdate = "$mdate[month] $mdate[mday], $mdate[year]";
        
        $activeSheet->setCellValueExplicit('A1', 'MANCOM REPORTS', PHPExcel_Cell_DataType::TYPE_STRING); 
        $activeSheet->setCellValueExplicit('A2', 'AS OF : '.$mdate, PHPExcel_Cell_DataType::TYPE_STRING); 

        $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT)->applyFromArray(array("font" => array( "bold" => true)));;
        $objPHPExcel->getActiveSheet()->getStyle('A2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT)->applyFromArray(array("font" => array( "bold" => true)));;

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
                
                if($xcoor == 'M'){
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit($xcoor . $line, $this->encrypt->decode($row->{$field}), PHPExcel_Cell_DataType::TYPE_STRING); 
                }
                else{
                $objPHPExcel->getActiveSheet()->setCellValueExplicit($xcoor . $line, $row->{$field}, PHPExcel_Cell_DataType::TYPE_STRING); 
                }
                
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
        header('Content-Disposition: attachment;filename=MANCOM_REPORTS.xls');
        header('Content-Transfer-Encoding: binary');
        
        $objWriter->save('php://output');   
    }
    }
    
/* End of file */
/* Location: system/application */
?>
