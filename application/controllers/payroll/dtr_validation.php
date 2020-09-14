<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class dtr_Validation extends my_controller
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

        $payroll_date = date("Y-m-d",strtotime($_POST['payroll_date']));  

        $this->load->library('pdf');
        $html = $this->export_dtr_validation( $payroll_date, "DTR Validation");        
        $title = "DTR Validation";

        $this->pdf->Output(ucwords(str_replace(" ","_",$title))."_" . date('dmYHis') . '.pdf', 'I');
    }

    function export_dtr_validation( $payroll_date, $title ){


        switch ($tran_type) {
            case 0:
                $transaction = "payroll_current_transaction";
                $on_hold = 'AND t.on_hold != 1';
                break;
            
            case 1:
                $transaction = "payroll_closed_transaction";
                break;
        }

        $bank_qry = $this->db->query('SELECT * FROM '.$this->db->dbprefix('bank').' WHERE bank_id = "'.$bank.'"')->row();
        $bank_type = $bank_qry->bank_type;
        $bank_code_numeric = $bank_qry->bank_code_numeric;
        $batch_no = $bank_qry->batch_no;
        $branch_code = $bank_qry->branch_code;
        $account_no = str_replace('-','',$bank_qry->account_no);
        $ceiling_amount = str_replace('.','',$bank_qry->ceiling_amount);

        if(empty($file_name)) {
            $filename = $bank_code_numeric;
        }

        else{
            $filename = $file_name;
        }
        
        if(!empty($company_id)){

            $company = " AND company_id IN ($company_id)";
        }
        if(!empty($employee_id)){
            
            $employee = " AND p.employee_id IN ($employee_id)";   
        }

        if(!empty($paycode_id)){
            $pay_code = 'AND t.paycode_id = '.$paycode_id;
        }

        $qry = "SELECT  e.id_number AS 'ID Number', CONCAT(u.lastname,', ', u.firstname, ' ', IF(u.aux !='' AND u.aux IS NOT NULL AND u.aux = ' ', CONCAT(u.aux,', '), ''), u.middlename) AS 'Employee Name',
                tps.payroll_date AS 'Payroll Date', tps.hours_worked AS 'HRS Worked', tps.lates AS 'Lates', tps.undertime AS 'Undertime',
                tps.absences AS 'Absences', tps.lwp AS 'LWP', tps.lwop AS 'LWOP', 
                tps.reg_ot AS 'REG OT', tps.reg_nd AS 'REG ND', tps.reg_ndot AS 'REG ND OT', 
                tps.rd_ot AS 'RD OT', tps.rd_ot_excess AS 'RD OT Excess', tps.rd_ndot AS 'RD ND OT', tps.rd_ndot_excess AS 'RD ND OT Excess',
                tps.leg_ot AS 'LEG OT', tps.leg_ot_excess AS 'LEG OT Excess', tps.leg_ndot AS 'LEG ND OT', tps.leg_ndot_excess AS 'LEG ND OT Excess',
                tps.spe_ot AS 'SPE OT', tps.spe_ot_excess AS 'SPE OT Excess', tps.spe_ndot AS 'SPE ND OT', tps.spe_ndot_excess AS 'SPE ND OT Excess',
                tps.legrd_ot AS 'LEG RD OT', tps.legrd_ot_excess AS 'LEG RD OT Excess', tps.legrd_ndot AS 'LEG RD ND OT', tps.legrd_ndot_excess AS 'LEG RD ND OT Excess',
                tps.sperd_ot AS 'SPE RD OT', tps.sperd_ot_excess AS 'SPE RD OT Excess', tps.sperd_ndot AS 'SPE RD ND OT', tps.sperd_ndot_excess AS 'SPE RD ND OT Excess',
                tps.dob_ot AS 'DOUBLE OT', tps.dob_ot_excess AS 'DOUBLE OT Excess', tps.dob_ndot AS 'DOUBLE ND OT', tps.dob_ndot_excess AS 'DOUBLE ND OT Excess',
                tps.dobrd_ot AS 'DOUBLE RD OT', tps.dobrd_ot_excess AS 'DOUBLE RD OT Excess', tps.dobrd_ndot AS 'DOUBLE RD ND OT', tps.dobrd_ndot_excess AS 'DOUBLE RD ND OT Excess'
                FROM hr_timekeeping_period_summary tps
                INNER JOIN hr_user u ON  u.employee_id = tps.employee_id
                INNER JOIN hr_employee e ON e.employee_id = tps.employee_id
                WHERE tps.payroll_date = '{$payroll_date}' AND tps.employee_id IN ( 
                    SELECT employee_id FROM hr_timekeeping_period_summary WHERE payroll_date = '{$payroll_date}' 
                    GROUP BY employee_id
                    HAVING COUNT(employee_id) > 1 )
                ORDER BY u.lastname, u.firstname, u.middlename";

        $res = $this->db->query($qry);

        $query = $res;
        $fields = $res->list_fields();

        //$export = $this->_export;
        $this->load->library('PHPExcel');       
        $this->load->library('PHPExcel/IOFactory');

        $objPHPExcel = new PHPExcel();

        $objPHPExcel->getProperties()->setTitle("DTR Validation")
                    ->setDescription("DTR Validation");
                       
        // Assign cell values
        $objPHPExcel->setActiveSheetIndex(0);
        $activeSheet = $objPHPExcel->getActiveSheet();

        //header
        // $alphabet  = range('A','Z');
        $alpha_ctr = 0;
        $sub_ctr   = 0;

        $letters = array();
        $letter = 'A';
        while ($letter !== 'AAA') {
            $letters[] = $letter++;
        }

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
        $styleHead= array(
            'font' => array(
                'bold' => true,
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
            )
        );

        foreach ($fields as $field) {
            $xcoor = $letters[$alpha_ctr];

            $activeSheet->setCellValueExplicit($xcoor . '5', $field, PHPExcel_Cell_DataType::TYPE_STRING);

            $objPHPExcel->getActiveSheet()->getStyle($xcoor . '5')->applyFromArray($styleArray);
            
            $alpha_ctr++;
        }

        $activeSheet->getHeaderFooter()->setOddFooter('&L&BPrinted By:'.$userinfo->firstname.' '.$userinfo->lastname.' &RPage &P of &N');

        $activeSheet->setCellValueExplicit('A1', 'DTR Validation', PHPExcel_Cell_DataType::TYPE_STRING); 
        $activeSheet->setCellValueExplicit('A2', 'Payroll Date:'.date('F d,Y',strtotime($payroll_date)), PHPExcel_Cell_DataType::TYPE_STRING); 

        $objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($styleHead);
        $objPHPExcel->getActiveSheet()->getStyle('A2')->applyFromArray($styleHead);
        $objPHPExcel->getActiveSheet()->getStyle('A3')->applyFromArray($styleArray);

        // contents.
        $line = 6;
        foreach ($query->result() as $row) {
            $sub_ctr   = 0;         
            $alpha_ctr = 0;

            foreach ($fields as $field) {
                if ($alpha_ctr >= count($letters)) {
                    $alpha_ctr = 0;
                    $sub_ctr++;
                }

                if ($sub_ctr > 0) {
                    $xcoor = $letters[$sub_ctr - 1] . $letters[$alpha_ctr];
                } 
                else {
                    $xcoor = $letters[$alpha_ctr];
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
        header('Content-Disposition: attachment;filename='.date('Y-m-d',strtotime($payroll_date)).'-BDO_CREDITING'.'.xls');
        header('Content-Transfer-Encoding: binary');
        
        $objWriter->save('php://output');   
    }
}
/* End of file */
/* Location: system/application */
?>
