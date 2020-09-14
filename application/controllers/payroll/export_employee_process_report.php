<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class export_employee_process_report extends my_controller
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
    function get_parameters()
    {
        //Select Report type:
        $report_type = array("Processed Employee", "Not Process Employee", "Employee On Hold");
        $report_type_html = '<select id="report_type_id" name="report_type_id">';
            foreach($report_type as $report_type_id => $report_type_value){
                $report_type_html .= '<option value="'.$report_type_id.'">'.$report_type_value.'</option>';
            }
        $report_type_html .= '</select>'; 

        
        $response->report_type_html = $report_type_html;
        $data['json'] = $response;
        $this->load->view('template/ajax', $data);  
    }
    
    function export_report(){
        $payroll_date = date("Y-m-d",strtotime($_POST['payroll_date']));
        $status = $_POST['report_type_id'];

        $this->load->library('pdf');
        $html = $this->export_employee($payroll_date, $status, $title);        
        $title = "Employee List";
        $this->pdf->addPage('L', 'A4', true);
        $this->pdf->SetFontSize( 8 );
        $this->pdf->writeHTML($html, true, false, true, false, '');
        $this->pdf->Output(ucwords(str_replace(" ","_",$title))."_" . date('dmYHis') . '.pdf', 'I');
    }
    
    function export_employee($payroll_date, $status, $title){

        switch ($status) {
            case '0':
                $qry = "SELECT e.id_number AS 'ID Number', u.lastname AS 'Last Name', u.firstname AS 'First Name', u.aux AS 'Suffix', u.middlename AS 'Middle Name', 
                            e.employed_date AS 'Hired Date',e.original_hired_date AS 'Original Hired Date', e.resigned_date AS 'Resigned Date', IF( u.inactive = 1, 'Inactive', 'Active') AS 'Active/Inactive',
                            es.employment_status AS 'Employment Status', ur.job_rank AS 'Employee Type',
                            w.cost_code AS 'Cost Code', pp.paycode AS 'Current Pay Code', ppx.paycode AS 'Pay Code on Payroll', b.bank AS 'Bank', ppt.payment_type AS 'Payment Type', 
                            ps.payroll_schedule AS 'Payroll Schedule', prt.payroll_rate_type AS 'Payroll Rate Type', IF(pct.amount IS NOT NULL, 'Processed', 'Not Process') AS 'IF Processed',
                            IF(e.CBE = 1, 'Y', 'N') AS 'CBE', pct.amount as 'NETPAY'
                        FROM {$this->db->dbprefix}user u
                        LEFT JOIN {$this->db->dbprefix}employee e ON e.employee_id = u.employee_id
                        LEFT JOIN {$this->db->dbprefix}employee_payroll ep ON ep.employee_id = u.employee_id
                        LEFT JOIN {$this->db->dbprefix}payroll_current_transaction pct ON pct.employee_id = u.employee_id
                        LEFT JOIN {$this->db->dbprefix}employee_work_assignment w ON w.employee_id = u.employee_id
                        LEFT JOIN {$this->db->dbprefix}payroll_paycode pp ON ep.paycode_id = pp.paycode_id
                        LEFT JOIN {$this->db->dbprefix}payroll_paycode ppx ON pct.paycode_id = ppx.paycode_id
                        LEFT JOIN {$this->db->dbprefix}bank b ON ep.bank_id = b.bank_id
                        LEFT JOIN {$this->db->dbprefix}payroll_payment_type ppt ON ppt.payment_type_id = ep.payment_type_id
                        LEFT JOIN {$this->db->dbprefix}payroll_schedule ps ON ps.payroll_schedule_id = ep.payroll_schedule_id
                        LEFT JOIN {$this->db->dbprefix}payroll_rate_type prt ON prt.payroll_rate_type_id = ep.payroll_rate_type_id
                        LEFT JOIN {$this->db->dbprefix}employment_status es on es.employment_status_id = e.status_id
                        LEFT JOIN {$this->db->dbprefix}user_rank ur on ur.job_rank_id = e.rank_id
                        WHERE pct.payroll_date = '{$payroll_date}' AND pct.transaction_code = 'netpay' AND w.assignment = 1 AND pct.on_hold = 0 AND pct.deleted = 0";
                break;
            
            case '1':
                $qry = "SELECT e.id_number AS 'ID Number', u.lastname AS 'Last Name', u.firstname AS 'First Name', u.aux AS 'Suffix', u.middlename AS 'Middle Name', 
                            e.employed_date AS 'Hired Date',e.original_hired_date AS 'Original Hired Date', e.resigned_date AS 'Resigned Date', IF( u.inactive = 1, 'Inactive', 'Active') AS 'Active/Inactive',
                            es.employment_status AS 'Employment Status', ur.job_rank AS 'Employee Type',
                            w.cost_code AS 'Cost Code', pp.paycode AS 'Current Pay Code', NULL AS 'Pay Code on Payroll', b.bank AS 'Bank', ppt.payment_type AS 'Payment Type', 
                            ps.payroll_schedule AS 'Payroll Schedule', prt.payroll_rate_type AS 'Payroll Rate Type', 'Not Process' AS 'IF Processed',IF(e.CBE = 1, 'Y', 'N') AS 'CBE'
                        FROM {$this->db->dbprefix}user u
                        LEFT JOIN {$this->db->dbprefix}employee e ON e.employee_id = u.employee_id
                        LEFT JOIN {$this->db->dbprefix}employee_payroll ep ON ep.employee_id = u.employee_id
                        LEFT JOIN {$this->db->dbprefix}employee_work_assignment w ON w.employee_id = u.employee_id
                        LEFT JOIN {$this->db->dbprefix}payroll_paycode pp ON ep.paycode_id = pp.paycode_id
                        LEFT JOIN {$this->db->dbprefix}bank b ON ep.bank_id = b.bank_id
                        LEFT JOIN {$this->db->dbprefix}payroll_payment_type ppt ON ppt.payment_type_id = ep.payment_type_id
                        LEFT JOIN {$this->db->dbprefix}payroll_schedule ps ON ps.payroll_schedule_id = ep.payroll_schedule_id
                        LEFT JOIN {$this->db->dbprefix}payroll_rate_type prt ON prt.payroll_rate_type_id = ep.payroll_rate_type_id
                        LEFT JOIN {$this->db->dbprefix}employment_status es on es.employment_status_id = e.status_id
                        LEFT JOIN {$this->db->dbprefix}user_rank ur on ur.job_rank_id = e.rank_id
                        WHERE w.assignment = 1 AND u.employee_id NOT IN ( SELECT employee_id FROM {$this->db->dbprefix}payroll_current_transaction WHERE payroll_date = '{$payroll_date}')";
                break;

            case '2':
                $qry = "SELECT e.id_number AS 'ID Number', u.lastname AS 'Last Name', u.firstname AS 'First Name', u.aux AS 'Suffix', u.middlename AS 'Middle Name', 
                            e.employed_date AS 'Hired Date',e.original_hired_date AS 'Original Hired Date', e.resigned_date AS 'Resigned Date', IF( u.inactive = 1, 'Inactive', 'Active') AS 'Active/Inactive',
                            es.employment_status AS 'Employment Status', ur.job_rank AS 'Employee Type',
                            w.cost_code AS 'Cost Code', pp.paycode AS 'Current Pay Code', ppx.paycode AS 'Pay Code on Payroll', b.bank AS 'Bank', ppt.payment_type AS 'Payment Type', 
                            ps.payroll_schedule AS 'Payroll Schedule', prt.payroll_rate_type AS 'Payroll Rate Type', IF(pct.amount IS NOT NULL, 'Processed', 'Not Process') AS 'IF Processed',
                            IF(e.CBE = 1, 'Y', 'N') AS 'CBE',pct.amount as 'NETPAY'
                        FROM {$this->db->dbprefix}user u
                        LEFT JOIN {$this->db->dbprefix}employee e ON e.employee_id = u.employee_id
                        LEFT JOIN {$this->db->dbprefix}employee_payroll ep ON ep.employee_id = u.employee_id
                        LEFT JOIN {$this->db->dbprefix}payroll_current_transaction pct ON pct.employee_id = u.employee_id
                        LEFT JOIN {$this->db->dbprefix}employee_work_assignment w ON w.employee_id = u.employee_id
                        LEFT JOIN {$this->db->dbprefix}payroll_paycode pp ON ep.paycode_id = pp.paycode_id
                        LEFT JOIN {$this->db->dbprefix}payroll_paycode ppx ON pct.paycode_id = ppx.paycode_id
                        LEFT JOIN {$this->db->dbprefix}bank b ON ep.bank_id = b.bank_id
                        LEFT JOIN {$this->db->dbprefix}payroll_payment_type ppt ON ppt.payment_type_id = ep.payment_type_id
                        LEFT JOIN {$this->db->dbprefix}payroll_schedule ps ON ps.payroll_schedule_id = ep.payroll_schedule_id
                        LEFT JOIN {$this->db->dbprefix}payroll_rate_type prt ON prt.payroll_rate_type_id = ep.payroll_rate_type_id
                        LEFT JOIN {$this->db->dbprefix}employment_status es on es.employment_status_id = e.status_id
                        LEFT JOIN {$this->db->dbprefix}user_rank ur on ur.job_rank_id = e.rank_id
                        WHERE pct.payroll_date = '{$payroll_date}' AND pct.transaction_code = 'netpay' AND w.assignment = 1 AND pct.on_hold = 1 AND pct.deleted = 0 ";
                break;
        }
       
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
        
        $activeSheet->setCellValueExplicit('A1', 'EMPLOYEE RECORDS', PHPExcel_Cell_DataType::TYPE_STRING); 
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
        header('Content-Disposition: attachment;filename=EMPLOYEE_RECORDS.xls');
        header('Content-Transfer-Encoding: binary');
        
        $objWriter->save('php://output');   
    }
    }
    
/* End of file */
/* Location: system/application */
?>
