<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Gl_report extends MY_Controller
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
	// default jqgrid controller method
	function index(){
		if($this->user_access[$this->module_id]['list'] != 1){
			$this->session->set_flashdata('flashdata', 'You dont have sufficient privilege to execute the list action of '.$this->module_name.'! Please contact the System Administrator.');
			redirect( base_url() );
		}
		$_POST['record_id'] = -1;
		$this->edit();
	}

	function detail(){
		parent::detail();

		//additional module detail routine here
		$data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/js/detailview.js"></script>';
		$data['content'] = 'detailview';

		//other views to load
		$data['views'] = array();

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

	function edit(){
		parent::edit();
	
		//additional module edit routine here
		$data['show_wizard_control'] = false;
		$data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/js/editview.js"></script>';
		$data['scripts'][] = jqgrid_listview();
		$data['content'] = 'editview';
	
		//other views to load
		$data['views'] = array();
		$data['views_outside_record_form'] = array();
		$data['buttons'] = $this->module_link . '/buttons';

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

	function ajax_save(){
		parent::ajax_save();

		//additional module save routine here
	}

	function delete(){
		parent::delete();

		//additional module delete routine here
	}
	// END - default module functions

	// START custom module funtions
	function listview(){
		$processing_type_id = $this->input->post('processing_type_id');
		$company_id = $this->input->post('company_id');
		$payroll_date = date('Y-m-d', strtotime($this->input->post('payroll_date')));
		
		$accounts = $this->db->get_where('payroll_account', array('deleted' => 0));
	    if( $accounts->num_rows() > 0 ){
	        foreach( $accounts->result() as  $row ){
	            $account[$row->account_id] = array(
	            	'account_code' => $row->account_code,
	            	'account_name' => $row->account_name
	        	);
	        }
	    }

		//get transactions
		$qry = "SELECT a.transaction_id, a.employee_id, b.department_id, e.transaction_code,
		IF(f.transaction_label_override = '' OR f.transaction_label_override IS NULL, e.transaction_label, f.transaction_label_override) as transaction_label, SUM(a.amount) as amount, 
		e.credit_account_id AS default_credit_account_id, e.debit_account_id AS default_debit_account_id, f.credit_account_id, f.debit_account_id
		FROM {$this->db->dbprefix}payroll_current_transaction a
		LEFT JOIN {$this->db->dbprefix}user b ON b.employee_id = a.employee_id
		LEFT JOIN {$this->db->dbprefix}user_company c ON c.company_id = b.company_id
		LEFT JOIN {$this->db->dbprefix}payroll_period d ON d.payroll_period_id = a.period_id
		LEFT JOIN {$this->db->dbprefix}payroll_transaction e ON e.transaction_id = a.transaction_id
		LEFT JOIN {$this->db->dbprefix}department_account_mapping f ON (f.department_id = b.department_id AND f.transaction_id = a.transaction_id)
		LEFT JOIN {$this->db->dbprefix}payroll_transaction_class g ON g.transaction_class_id = e.transaction_class_id
		WHERE
		a.deleted = 0 AND a.payroll_date <= '{$payroll_date}' AND a.on_hold = 0
		AND b.company_id = '{$company_id}'
		AND d.period_processing_type_id = '{$processing_type_id}'
		GROUP BY a.transaction_id
		ORDER BY e.transaction_code";

		//ORDER BY g.transaction_class_id

		$transactions = $this->db->query( $qry );
		
		$response->last_query = $this->db->last_query();
		if( $transactions->num_rows() > 0 ){
			foreach( $transactions->result() as $trans ){
				if( $trans->amount <> 0 && !( empty($trans->default_credit_account_id) && empty($trans->default_debit_account_id) && empty($trans->credit_account_id) && empty($trans->debit_account_id) ) ){
					$credit_account_id = $trans->default_credit_account_id;
					if(!empty($trans->credit_account_id)) $credit_account_id = $trans->credit_account_id;
					$debit_account_id = $trans->default_debit_account_id;
					if(!empty($trans->debit_account_id)) $debit_account_id = $trans->debit_account_id;

					if( !empty($credit_account_id) ){
						if( !isset($transaction[$trans->transaction_id][$credit_account_id]['credit'][0]) ) $transaction[$trans->transaction_id][$credit_account_id]['credit'][0] = 0;
						$transaction[$trans->transaction_id][$credit_account_id]['transaction_code'] = $trans->transaction_code;
						$transaction[$trans->transaction_id][$credit_account_id]['transaction_label'] = $trans->transaction_label;
						$transaction[$trans->transaction_id][$credit_account_id]['account_code'] = $account[$credit_account_id]['account_code'];
						$transaction[$trans->transaction_id][$credit_account_id]['account_name'] = $account[$credit_account_id]['account_name'];
						$transaction[$trans->transaction_id][$credit_account_id]['credit'][] = $trans->amount;
					}

					if( !empty($debit_account_id) ){
						if( !isset($transaction[$trans->transaction_id][$debit_account_id]['debit'][0]) ) $transaction[$trans->transaction_id][$debit_account_id]['debit'][0] = 0;
						$transaction[$trans->transaction_id][$debit_account_id]['transaction_code'] = $trans->transaction_code;
						$transaction[$trans->transaction_id][$debit_account_id]['transaction_label'] = $trans->transaction_label;
						$transaction[$trans->transaction_id][$debit_account_id]['account_code'] = $account[$debit_account_id]['account_code'];
						$transaction[$trans->transaction_id][$debit_account_id]['account_name'] = $account[$debit_account_id]['account_name'];
						$transaction[$trans->transaction_id][$debit_account_id]['debit'][] = $trans->amount;
					}
				}
			}

			if( isset($transaction) ){
				$response->rows = array();
				$ctr = 0;
				foreach( $transaction as $transaction_id => $accnt ){
					foreach( $transaction[$transaction_id] as $account_id => $detail ){
						$response->rows[$ctr]['id'] = $transaction_id.' - '.$account_id;
						$cell = array();
						$cell[] = $response->rows[$ctr]['id'];
						$cell[] = $detail['transaction_code'];
						$cell[] = $detail['transaction_label'];
						$cell[] = $detail['account_code'];
						$cell[] = $detail['account_name'];
						$cell[] = isset( $detail['credit'] ) ? number_format( array_sum($detail['credit']), 2, '.', ',') : '';
						$cell[] = isset( $detail['debit'] ) ? number_format( array_sum($detail['debit']), 2, '.', ',') : '';
						$response->rows[$ctr]['cell'] = $cell;
						$ctr++;
					}
				}
			}
		}

		$response->page = 1;
		$response->total = 1;
		$response->records = isset($ctr) ? $ctr + 1 : 0; //$total rows;

		$data['json'] = $response;                		
		$this->load->view($this->userinfo['rtheme'].'/template/ajax', $data);
	}
	// END custom module funtions

}

/* End of file */
/* Location: system/application */