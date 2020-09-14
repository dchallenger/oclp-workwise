<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Payroll extends MY_Model
{
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * [regular_processing description]
	 * @param  [type] $period [description]
	 * @return [type]         [description]
	 */
	function regular_processing( $period ){
		$this->db->order_by('transaction_class_id');
		$transaction_classes = $this->db->get_where('payroll_transaction_class', array('regular_processing' => 1, 'deleted' => 0));

		return $this->_processing( $period, $transaction_classes );
	}

	/**
	 * [special_processing description]
	 * @param  [type] $period [description]
	 * @return [type]         [description]
	 */
	function special_processing( $period ){
		$this->db->order_by('transaction_class_id');
		$transaction_classes = $this->db->get_where('payroll_transaction_class', array('special_processing' => 1, 'deleted' => 0));
		
		return $this->_processing( $period, $transaction_classes );
	}

	function finalpay_processing( $period ){
		$where = 'transaction_class_id IN ('.$period->transaction_class_id.')';
		$this->db->order_by('transaction_class_id');
		$this->db->where('deleted', 0);
		$this->db->where($where);
		$transaction_classes = $this->db->get('payroll_transaction_class');
		return $this->_processing( $period, $transaction_classes );
	}

	/**
	 * [_processing description]
	 * @param  [type] $period              [description]
	 * @param  [type] $transaction_classes [description]
	 * @return [type]                      [description]
	 */
	function _processing( $period, $transaction_classes ){

		$employees = $this->_get_employee_set( $period );
		
		if( $employees->num_rows() > 0 ){
			//load resources
			$this->load->library('encrypt');
			$this->load->helper('file');

			$progressfile = 'uploads/'.$this->user->user_id . '-' . $this->module.'-'. $period->payroll_period_id.'-progresslog.txt';
			
			$ctr = 1;
			$total = $employees->num_rows() * $transaction_classes->num_rows();
			$this->_get_local_setup( $period );

			foreach($employees->result() as $employee){
				$this->base_on_earning = array();
				$this->loan_interest = array();
				$current_transaction = array();
				$base_on_earning = array();
				
				if( $this->config->item('use_day_type_matrix') ){
					$this->_load_day_rates_matrix( $employee->status_id );
				}

				//decode salaries
				$employee->salary = $this->encrypt->decode( $employee->salary );

				if(isset($employee->minimum_type_id)){
					switch ($employee->minimum_type_id){
						case 1:
							$employee->minimum_takehome = $this->encrypt->decode( $employee->minimum_takehome );
						break;
						case 2:
							$minimum_takehome_percent = $this->encrypt->decode( $employee->minimum_takehome );
							$employee->minimum_takehome = $employee->salary * ( $minimum_takehome_percent / 100 );
							break;
					}
				}
				else{
					$employee->minimum_takehome = $this->encrypt->decode( $employee->minimum_takehome );
				}

				$total_year_days = (float)$employee->total_year_days;
				$employee->total_year_days = empty( $total_year_days ) ? $this->config->item('total_year_days')  : $employee->total_year_days;

				//get dtr period summary
				$dtr_summary = $this->db->get_where('timekeeping_period_summary', array('payroll_date' => $period->payroll_date, 'deleted' => 0, 'employee_id' => $employee->employee_id));
				if($dtr_summary->num_rows() == 1)
					$dtr_summary = $dtr_summary->row();
				else
					$dtr_summary = false;
				
				//get salary
				$salary =  $this->_get_employee_salary_for_period( $employee, $dtr_summary, $period);
				foreach( $transaction_classes->result() as $transaction_class ){
					write_file($progressfile, number_format(($ctr++ / $total) * 100, 2).' ('.$ctr.'/'.$total.' employee/ current:'.$employee->employee_id.')');
					$transactions = $this->db->get_where('payroll_transaction', array('deleted' => 0, 'transaction_class_id' => $transaction_class->transaction_class_id));
					if( $transactions->num_rows() > 0 ){
						foreach( $transactions->result() as $transaction ){
							switch( $transaction_class->transaction_class_code  ){
								case 'SALARY':
									//insert salary to current transaction
									$current_transaction[] = array(
										'period_id' => $period->payroll_period_id,
										'processing_type_id' => $period->period_processing_type_id,
										'payroll_date' => $period->payroll_date,	
										'transaction_id' => $transaction->transaction_id,
										'transaction_code' => $transaction->transaction_code,
										'transaction_type_id' => $transaction->transaction_type_id,
										'employee_id' => $employee->employee_id,
										'unit_rate' => $salary['period'],
										'amount' => $salary['period'],
										'inserted_from_id' => 1
									);
									break;
								case 'ALLOWANCE_RECURRING':
								case 'BENEFIT_RECURRING':
								case 'DE_MINIMIS_BENEFIT_RECURRING':
								case 'FRINGE_BENEFIT_RECURRING':
								case 'DEDUCTION_RECURRING':
									$recurrings = $this->_get_recurring_transaction( $transaction, $period, $employee, $salary );
									if( $recurrings ){
										foreach($recurrings as $recurring){
											$current_transaction[] = array(
												'period_id' => $period->payroll_period_id,
												'processing_type_id' => $period->period_processing_type_id,
												'payroll_date' => $period->payroll_date,	
												'transaction_id' => $transaction->transaction_id,
												'transaction_code' => $transaction->transaction_code,
												'transaction_type_id' => $transaction->transaction_type_id,
												'employee_id' => $employee->employee_id,
												'unit_rate' => $recurring['amount'],
												'amount' => $recurring['amount'],
												'inserted_from_id' => 2,
												'record_id' => $recurring['record_id'],
												'record_from' => 'payroll_recurring'
											);
										}
									}

									if( $transaction_class->transaction_class_code ){
										//get ot allowances
										$qry = "SELECT SUM(amount) AS amount
										FROM `{$this->db->dbprefix}payroll_ot_allowance`
										WHERE payroll_date = '{$period->payroll_date}' AND transaction_id = {$transaction->transaction_id} AND employee_id = {$employee->employee_id}";
										$ot_allowance = $this->db->query( $qry )->row();
										if( !empty($ot_allowance->amount) ){
											$current_transaction[] = array(
												'period_id' => $period->payroll_period_id,
												'processing_type_id' => $period->period_processing_type_id,
												'payroll_date' => $period->payroll_date,	
												'transaction_id' => $transaction->transaction_id,
												'transaction_code' => $transaction->transaction_code,
												'transaction_type_id' => $transaction->transaction_type_id,
												'employee_id' => $employee->employee_id,
												'unit_rate' => $ot_allowance->amount,
												'amount' => $ot_allowance->amount,
												'inserted_from_id' => 2
											);
										}
									}

									break;
								case 'ALLOWANCE_BATCH':
								case 'BENEFIT_BATCH':
								case 'DE_MINIMIS_BENEFIT_BATCH':
								case 'FRINGE_BENEFIT_BATCH':
								case 'DEDUCTION_BATCH':
								case 'CASH_ADVANCE':
									$batch_entries = $this->_get_batch_entry( $transaction, $period, $employee );
									if( $batch_entries ){
										foreach($batch_entries as $batch_entry){
											$current_transaction[] = array(
												'period_id' => $period->payroll_period_id,
												'processing_type_id' => $period->period_processing_type_id,
												'payroll_date' => $period->payroll_date,	
												'transaction_id' => $transaction->transaction_id,
												'transaction_code' => $transaction->transaction_code,
												'transaction_type_id' => $transaction->transaction_type_id,
												'employee_id' => $employee->employee_id,
												'quantity' => $batch_entry['quantity'],
												'unit_rate' => $batch_entry['unit_rate'],
												'amount' => $batch_entry['amount'],
												'inserted_from_id' => 3,
												'record_id' => $batch_entry['record_id'],
												'record_from' => 'payroll_batch_entry'
											);
										}
									}
									break;
								case 'OVERTIME':
									$premium = $this->_get_premium_rate( $transaction->transaction_code, $dtr_summary , $employee->fixed_rate);
									if( $premium ){
										
										$otrate = round( $premium['ot_rate'] * ( $salary['daily'] / 8), 2);	
										
										$current_transaction[] = array(
											'period_id' => $period->payroll_period_id,
											'processing_type_id' => $period->period_processing_type_id,
											'payroll_date' => $period->payroll_date,	
											'transaction_id' => $transaction->transaction_id,
											'transaction_code' => $transaction->transaction_code,
											'transaction_type_id' => $transaction->transaction_type_id,
											'employee_id' => $employee->employee_id,
											'unit_rate' =>  $otrate,
											'quantity' => $premium['ot_quantity'],
											'amount' => round($otrate * $premium['ot_quantity'], 2),
											'inserted_from_id' => 1,
										);
									}
									break;
								case 'LEAVES':
									if( $employee->payroll_rate_type_id != 6 && $dtr_summary && !empty($dtr_summary->lwop) ){
										$current_transaction[] = array(
											'period_id' => $period->payroll_period_id,
											'processing_type_id' => $period->period_processing_type_id,
											'payroll_date' => $period->payroll_date,	
											'transaction_id' => $transaction->transaction_id,
											'transaction_code' => $transaction->transaction_code,
											'transaction_type_id' => $transaction->transaction_type_id,
											'employee_id' => $employee->employee_id,
											'unit_rate' =>  round( $salary['daily'] / 8, 2),
											'quantity' => $dtr_summary->lwop,
											'amount' => round($salary['daily'] / 8 * $dtr_summary->lwop, 2),
											'inserted_from_id' => 1,
										);
									}

									//check for leave conversion
									$qry = "SELECT a.*, b.* 
									FROM `{$this->db->dbprefix}payroll_leave_conversion_employee` a
									LEFT JOIN `{$this->db->dbprefix}payroll_leave_conversion` b ON b.leave_convert_id = a.leave_convert_id
									WHERE b.payroll_date = '{$period->payroll_date}' AND a.employee_id = {$employee->employee_id}
									AND b.transaction_id = {$transaction->transaction_id}";
									$leave_conversion = $this->db->query( $qry );
									if( $this->db->_error_message() == '' && $leave_conversion->num_rows() > 0 ){
										$lc = $leave_conversion->row();
										//make sure that amount is not greater than what is convetible
										if($lc->amount > $lc->maximum_convertible) $lc->amount = $lc->maximum_convertible;

										//check for taxable
										if($lc->amount > $lc->nontaxable){
											$nontaxble = $lc->nontaxable;
											$taxable = $lc->amount - $lc->nontaxable;	
										}
										else{
											$nontaxble = $lc->amount;
											$taxable = 0;	
										}

										if($nontaxble > 0){
											$current_transaction[] = array(
												'period_id' => $period->payroll_period_id,
												'processing_type_id' => $period->period_processing_type_id,
												'payroll_date' => $period->payroll_date,	
												'transaction_id' => $transaction->transaction_id,
												'transaction_code' => $transaction->transaction_code,
												'transaction_type_id' => $transaction->transaction_type_id,
												'employee_id' => $employee->employee_id,
												'unit_rate' =>  round( $salary['daily'], 2),
												'quantity' => $nontaxble,
												'amount' => round($salary['daily'] * $nontaxble, 2),
												'inserted_from_id' => 7,
												'record_from' => 'payroll_leave_conversion',
												'record_id' => $lc->leave_convert_id,
											);
										}

										if( $taxable > 0 ){
											$taxable_transaction = $this->db->get_where('payroll_transaction', array('transaction_id' => $lc->taxable_transaction_id))->row();
												$current_transaction[] = array(
												'period_id' => $period->payroll_period_id,
												'processing_type_id' => $period->period_processing_type_id,
												'payroll_date' => $period->payroll_date,	
												'transaction_id' => $taxable_transaction->transaction_id,
												'transaction_code' => $taxable_transaction->transaction_code,
												'transaction_type_id' => $taxable_transaction->transaction_type_id,
												'employee_id' => $employee->employee_id,
												'unit_rate' =>  round( $salary['daily'], 2),
												'quantity' => $taxable,
												'amount' => round($salary['daily'] * $taxable, 2),
												'inserted_from_id' => 7,
												'record_from' => 'payroll_leave_conversion',
												'record_id' => $lc->leave_convert_id,
											);
										}
									}
									break;
								case 'ABSENCES':
									if( $employee->payroll_rate_type_id != 6 && $dtr_summary && !empty($dtr_summary->absences) ){
										$current_transaction[] = array(
											'period_id' => $period->payroll_period_id,
											'processing_type_id' => $period->period_processing_type_id,
											'payroll_date' => $period->payroll_date,	
											'transaction_id' => $transaction->transaction_id,
											'transaction_code' => $transaction->transaction_code,
											'transaction_type_id' => $transaction->transaction_type_id,
											'employee_id' => $employee->employee_id,
											'unit_rate' =>  $salary['daily'],
											'quantity' => $dtr_summary->absences,
											'amount' => round($salary['daily'] * $dtr_summary->absences, 2),
											'inserted_from_id' => 1,
										);
									}
									break;
								case 'DEDUCTION_LATE':
									if( $employee->payroll_rate_type_id != 6 && $dtr_summary && !empty($dtr_summary->lates) ){
										$current_transaction[] = array(
											'period_id' => $period->payroll_period_id,
											'processing_type_id' => $period->period_processing_type_id,
											'payroll_date' => $period->payroll_date,	
											'transaction_id' => $transaction->transaction_id,
											'transaction_code' => $transaction->transaction_code,
											'transaction_type_id' => $transaction->transaction_type_id,
											'employee_id' => $employee->employee_id,
											'unit_rate' =>  round( $salary['daily'] / 8, 2),
											'quantity' => $dtr_summary->lates,
											'amount' => round($salary['daily'] / 8 * $dtr_summary->lates, 2),
											'inserted_from_id' => 1,
										);
									}
									break;
								case 'DEDUCTION_UNDERTIME':
									if( $employee->payroll_rate_type_id != 6 && $dtr_summary && !empty($dtr_summary->undertime) ){
										$current_transaction[] = array(
											'period_id' => $period->payroll_period_id,
											'processing_type_id' => $period->period_processing_type_id,
											'payroll_date' => $period->payroll_date,	
											'transaction_id' => $transaction->transaction_id,
											'transaction_code' => $transaction->transaction_code,
											'transaction_type_id' => $transaction->transaction_type_id,
											'employee_id' => $employee->employee_id,
											'unit_rate' =>  round( $salary['daily'] / 8, 2),
											'quantity' => $dtr_summary->undertime,
											'amount' => round($salary['daily'] / 8 * $dtr_summary->undertime, 2),
											'inserted_from_id' => 1,
										);
									}
									break;
								case 'TAXABLE_DEDUCTION':
								case 'SHARED_DEDUCTION':
									$recurrings = $this->_get_recurring_transaction( $transaction, $period, $employee, $salary );
									if( $recurrings ){
										foreach($recurrings as $recurring){
											$current_transaction[] = array(
												'period_id' => $period->payroll_period_id,
												'processing_type_id' => $period->period_processing_type_id,
												'payroll_date' => $period->payroll_date,	
												'transaction_id' => $transaction->transaction_id,
												'transaction_code' => $transaction->transaction_code,
												'transaction_type_id' => $transaction->transaction_type_id,
												'employee_id' => $employee->employee_id,
												'unit_rate' => $recurring['amount'],
												'amount' => $recurring['amount'],
												'inserted_from_id' => 2,
												'record_id' => $recurring['record_id'],
												'record_from' => 'payroll_recurring'
											);
										}
									}
									$batch_entries = $this->_get_batch_entry( $transaction, $period, $employee );
									
									if( $batch_entries ){
										foreach($batch_entries as $batch_entry){
											$current_transaction[] = array(
												'period_id' => $period->payroll_period_id,
												'processing_type_id' => $period->period_processing_type_id,
												'payroll_date' => $period->payroll_date,	
												'transaction_id' => $transaction->transaction_id,
												'transaction_code' => $transaction->transaction_code,
												'transaction_type_id' => $transaction->transaction_type_id,
												'employee_id' => $employee->employee_id,
												'quantity' => $batch_entry['quantity'],
												'unit_rate' => $batch_entry['unit_rate'],
												'amount' => $batch_entry['amount'],
												'inserted_from_id' => 3,
												'record_id' => $batch_entry['record_id'],
												'record_from' => 'payroll_batch_entry'
											);
										}
									}
									break;
								case 'ECOLA':
									
									if( $salary['minimum_wage'] && !empty($salary['ecola']) ){
										if( $dtr_summary ){
											$salary['ecola_period'] -= $salary['ecola'] * ( $dtr_summary->lwop + $dtr_summary->absences );
										}

										$current_transaction[] = array(
											'period_id' => $period->payroll_period_id,
											'processing_type_id' => $period->period_processing_type_id,
											'payroll_date' => $period->payroll_date,	
											'transaction_id' => $transaction->transaction_id,
											'transaction_code' => $transaction->transaction_code,
											'transaction_type_id' => $transaction->transaction_type_id,
											'employee_id' => $employee->employee_id,
											'unit_rate' => $salary['ecola_period'],
											'quantity' => 1,
											'amount' => $salary['ecola_period'],
											'inserted_from_id' => 1,
										);
									}
									break;
								case 'SSS_EMP':
									if( in_array($period->week, explode(',', $employee->sss_week) ) ){
										$govt_contribution = $this->_get_govt_contribution($employee->sss_mode, 'sss', $transaction, $period, $employee, $salary);
										if( $govt_contribution ){
											if( $govt_contribution['amount'] != 'base_on_earning' ){
												$employee_contribution = array(
													'payroll_period_id' => $period->payroll_period_id,
													'payroll_date' => $period->payroll_date,
													'employee_id' => $employee->employee_id,
													'company_id' => $employee->company_id,
													'transaction_id' => $transaction->transaction_id,
													'employee' => $govt_contribution['amount'],
													'company' => $govt_contribution['detail']['ershare'],
													'ec' => $govt_contribution['detail']['ec']
												);

												$this->db->insert('employee_contribution', $employee_contribution);
											}

											if(isset($govt_contribution['detail'])) unset($govt_contribution['detail']);
											$current_transaction[] = $govt_contribution;
										}
									}
									break;
								case 'PHIC_EMP':
									if( in_array($period->week, explode(',', $employee->phic_week) ) ){
										$govt_contribution = $this->_get_govt_contribution($employee->phic_mode, 'phic', $transaction, $period, $employee, $salary);
										if( $govt_contribution ){
											if( $govt_contribution['amount'] != 'base_on_earning' ){
												$employee_contribution = array(
													'payroll_period_id' => $period->payroll_period_id,
													'payroll_date' => $period->payroll_date,
													'employee_id' => $employee->employee_id,
													'company_id' => $employee->company_id,
													'transaction_id' => $transaction->transaction_id,
													'employee' => $govt_contribution['amount'],
													'company' => $govt_contribution['detail']['ershare'],
													'ec' => $govt_contribution['detail']['ec'],
													'msb_id' => $govt_contribution['detail']['msb_id']
												);

												$this->db->insert('employee_contribution', $employee_contribution);
											}

											if(isset($govt_contribution['detail'])) unset($govt_contribution['detail']);

											$current_transaction[] = $govt_contribution;
										}
									}
									break;
								case 'HDMF_EMP':
									if( in_array($period->week, explode(',', $employee->hdmf_week) ) ){
										$govt_contribution = $this->_get_govt_contribution($employee->hdmf_mode, 'hdmf', $transaction, $period, $employee, $salary);
										if( $govt_contribution ){
											if( $govt_contribution['amount'] != 'base_on_earning' ){
												$employee_contribution = array(
													'payroll_period_id' => $period->payroll_period_id,
													'payroll_date' => $period->payroll_date,
													'employee_id' => $employee->employee_id,
													'company_id' => $employee->company_id,
													'transaction_id' => $transaction->transaction_id,
													'employee' => $govt_contribution['amount'],
													'company' => $govt_contribution['detail']['ershare'],
													'ec' => $govt_contribution['detail']['ec']
												);

												$this->db->insert('employee_contribution', $employee_contribution);
											}

											if(isset($govt_contribution['detail'])) unset($govt_contribution['detail']);
											$current_transaction[] = $govt_contribution;
										}
									}
									break;
								case 'BONUS':
									//assume at this point that all earnings are calculated, start inserting transactions
									$this->_insert_transactions( $current_transaction, $period, $employee, $salary );
									$current_transaction = array();
									
									$bonuses = $this->_get_bonus( $period, $employee, $transaction, $salary );
									if( $bonuses ){
										foreach($bonuses as $bonus){
											$current_transaction[] = array(
												'period_id' => $period->payroll_period_id,
												'processing_type_id' => $period->period_processing_type_id,
												'payroll_date' => $period->payroll_date,	
												'transaction_id' => $transaction->transaction_id,
												'transaction_code' => $transaction->transaction_code,
												'transaction_type_id' => $transaction->transaction_type_id,
												'employee_id' => $employee->employee_id,
												'unit_rate' => $bonus['amount'],
												'amount' => $bonus['amount'],
												'inserted_from_id' => 5,
												'record_id' => $bonus['record_id'],
												'record_from' => 'payroll_bonus'
											);
										}
									}
									break;
								case 'WHTAX':
									//assume at this point that all earnings are calculated, start inserting transactions
									$this->_insert_transactions( $current_transaction, $period, $employee, $salary );
									$current_transaction = array();
									if($period->period_processing_type_id == 3){
										$employee->tax_mode = 2;
									}
									//excempt for tax if mwe
									if( !$salary['minimum_wage'] ){
										switch($employee->tax_mode){
											case '1': //tax table
												if($this->schedule->remaining_period != 0)
													$whtax = $this->_get_whtax( $employee, $period );
												else
													$whtax = $this->_get_annualized_whtax( $employee, $period, $salary );
												break;
											case '2': //annualized
												$whtax = $this->_get_annualized_whtax( $employee, $period, $salary );
												break;
											case '3': //manual
												break;
											case '4': //cummulative
												break;
										}
										if($employee->tax_mode != '5'){
											$current_transaction[] = array(
												'period_id' => $period->payroll_period_id,
												'processing_type_id' => $period->period_processing_type_id,
												'payroll_date' => $period->payroll_date,	
												'transaction_id' => $transaction->transaction_id,
												'transaction_code' => $transaction->transaction_code,
												'transaction_type_id' => $transaction->transaction_type_id,
												'employee_id' => $employee->employee_id,
												'unit_rate' =>  $whtax,
												'quantity' => 1,
												'amount' => $whtax,
												'inserted_from_id' => 1,
											);
										}
									}
									break;
								case 'LOAN_AMORTIZATION':
									$loans = $this->_get_loans( $period, $employee, $transaction );
									if($loans){
										foreach($loans as $loan){
											$current_transaction[] = $loan;	
										}
									}
									break;
								case 'LOAN_INTEREST':
									foreach($this->loan_interest as $interest){
										$current_transaction[] = array(
											'period_id' => $period->payroll_period_id,
											'processing_type_id' => $period->period_processing_type_id,
											'payroll_date' => $period->payroll_date,	
											'transaction_id' => $transaction->transaction_id,
											'transaction_code' => $transaction->transaction_code,
											'transaction_type_id' => $transaction->transaction_type_id,
											'employee_id' => $employee->employee_id,
											'unit_rate' =>  $interest['interest'],
											'amount' => $interest['interest'],
											'inserted_from_id' => $interest['inserted_from_id'],
											'record_id' => $interest['record_id'],
											'record_from' => $interest['record_from'],
										);
									}
									break;
								case 'NETPAY':
									$this->_insert_transactions( $current_transaction, $period, $employee, $salary );
									$current_transaction = array();

									//unhold all transaction
									$this->_unhold_transactions($employee, $period);
									
									$netpay = $this->_get_employee_netpay( $employee, $period );
									
									if( $netpay < $employee->minimum_takehome ){
										$deductions = $this->_get_transaction( $employee, $period, '3,4', 'result',  true, true, false );
										if($deductions){
											foreach( $deductions as $deduction ){
												$netpay += 	$deduction->amount;
												$this->db->update('payroll_current_transaction', array('on_hold' => 1), array('current_transaction_id' => $deduction->current_transaction_id ));
												if($netpay > $employee->minimum_takehome ) break;
											}
										}
									}

									$netpay = array(
										'period_id' => $period->payroll_period_id,
										'processing_type_id' => $period->period_processing_type_id,
										'payroll_date' => $period->payroll_date,	
										'transaction_id' => $transaction->transaction_id,
										'transaction_code' => $transaction->transaction_code,
										'transaction_type_id' => $transaction->transaction_type_id,
										'employee_id' => $employee->employee_id,
										'unit_rate' => $netpay,
										'amount' => $netpay,
										'inserted_from_id' => 1
									);

									$this->db->insert('payroll_current_transaction', $netpay );
									break;
							}
						}
					}
				}

				if( !empty( $employee->resigned_date ) ){
					if( $employee->resigned_date >= $period->date_from &&  $employee->resigned_date <= $period->date_to ){
						//hold all transaction
						$this->db->update('payroll_current_transaction', array('on_hold' => 1, 'processing_type_id' => 3), array('employee_id' => $employee->employee_id));
					}
				}

				// delete from payroll register delegate outbound employees
				if(isset($employee->delegates_type_id) && $employee->delegates_type_id ==  2){
					$this->db->delete('payroll_current_transaction', array('employee_id' => $employee->employee_id, 'period_id' => $period->payroll_period_id));
				}
				$this->db->where('assignment',1);
				$this->db->where('employee_id', $employee->employee_id);
        		$work_assignment = $this->db->get('employee_work_assignment')->row();

				$this->db->update('payroll_current_transaction', 
								array('paycode_id' => $employee->paycode_id, 'department_id' => $work_assignment->department_id, 
									  'division_id' => $work_assignment->division_id, 'project_name_id' => $work_assignment->project_name_id,
									  'code_status_id' => $work_assignment->code_status_id, 'cost_code' => $work_assignment->cost_code ), 
								array('employee_id' => $employee->employee_id, 'period_id' => $period->payroll_period_id ) 
								);
			}
		}

		return true;
	}

	/**
	 * [_get_employee_set description]
	 * @param  [type] $period [description]
	 * @return [type]         [description]
	 */
	function _get_employee_set( $period ){
		switch( $period->apply_to_id ){
			case 1: //employees
				$where = "b.employee_id";
				break;
			case 2: //company
				$where = "b.company_id";
				break;
			case 3: //division
				$where = "b.division_id";
				break;
			case 4: //department
				$where = "b.department_id";
				break;
			case 5: //paycode
				$where = "a.paycode_id";
				break;
		}
		if($period->period_processing_type_id == 3 ){
			$qry = "select a.*, b.*, c.*
			FROM {$this->db->dbprefix}employee_payroll a
			LEFT JOIN {$this->db->dbprefix}user b on a.employee_id = b.employee_id
			LEFT JOIN {$this->db->dbprefix}employee c on c.employee_id = b.employee_id
			LEFT JOIN {$this->db->dbprefix}timekeeping_period_summary t on t.employee_id = b.employee_id
			WHERE {$where} IN ({$period->apply_to}) AND a.payroll_schedule_id = {$period->payroll_schedule_id}
			AND b.deleted = 0 AND a.quitclaim = 0 AND c.employee_id is not null 
			AND c.employed_date <= '{$period->date_to}' AND t.payroll_date = '{$period->payroll_date}' AND t.deleted = 0";
			//get affected employees
		}
		else{
			$qry = "select a.*, b.*, c.*
			FROM {$this->db->dbprefix}employee_payroll a
			LEFT JOIN {$this->db->dbprefix}user b on a.employee_id = b.employee_id
			LEFT JOIN {$this->db->dbprefix}employee c on c.employee_id = b.employee_id
			LEFT JOIN {$this->db->dbprefix}timekeeping_period_summary t on t.employee_id = b.employee_id
			WHERE {$where} IN ({$period->apply_to}) AND a.payroll_schedule_id = {$period->payroll_schedule_id}
			AND b.deleted = 0 AND a.quitclaim = 0 AND c.employee_id is not null 
			AND IF(c.resigned_date is not null, c.resigned_date > '{$period->date_from}', b.inactive = 0)
			AND c.employed_date <= '{$period->date_to}' AND t.payroll_date = '{$period->payroll_date}' AND t.deleted = 0";
			//get affected employees
		}
		return $this->db->query( $qry );
	}

	/**
	 * [_get_local_setup description]
	 * @return [type] [description]
	 */
	function _get_local_setup( $period ){
		if( $this->config->item('use_day_type_matrix') ){

		}
		else{
			$this->_load_day_rates();
		}

		$nontaxable_bonus_per_annum = $this->config->item('nontaxable_bonus_per_annum');
		$this->nontaxable_bonus_per_annum = str_replace(',', '', $nontaxable_bonus_per_annum);

		$this->schedule = $this->db->get_where('payroll_schedule', array('payroll_schedule_id' => $period->payroll_schedule_id))->row();
		$qry = "SELECT COUNT(payroll_period_id) as cnt
		FROM {$this->db->dbprefix}payroll_period
		WHERE YEAR(payroll_date) = YEAR('{$period->payroll_date}') AND deleted = 0 AND period_status_id = 3
			AND apply_to IN ($period->apply_to) AND apply_to_id IN ($period->apply_to_id)
			AND payroll_schedule_id = $period->payroll_schedule_id AND period_processing_type_id = $period->period_processing_type_id";
			
		$close_period = $this->db->query( $qry )->row();

		$this->schedule->remaining_period = $this->schedule->total_period_per_annum - ($close_period->cnt + 1); //include currently being processed period
	}

	function _load_day_rates(){
		$this->regday = $this->db->get_where('day_type_and_rates', array('day_prefix' => 'reg'))->row();
		$this->rdday = $this->db->get_where('day_type_and_rates', array('day_prefix' => 'rd'))->row();
		$this->legday = $this->db->get_where('day_type_and_rates', array('day_prefix' => 'leg'))->row();
		$this->speday = $this->db->get_where('day_type_and_rates', array('day_prefix' => 'spe'))->row();
		$this->dobday = $this->db->get_where('day_type_and_rates', array('day_prefix' => 'dob'))->row();
		$this->legrdday = $this->db->get_where('day_type_and_rates', array('day_prefix' => 'legrd'))->row();
		$this->sperdday = $this->db->get_where('day_type_and_rates', array('day_prefix' => 'sperd'))->row();
		$this->dobrdday = $this->db->get_where('day_type_and_rates', array('day_prefix' => 'dobrd'))->row();
	}

	function _load_day_rates_matrix( $employment_status_id ){
		$this->regday = $this->db->get_where('day_type_and_rates_matrix', array('day_prefix' => 'reg', 'employment_status_id' => $employment_status_id))->row();
		$this->rdday = $this->db->get_where('day_type_and_rates_matrix', array('day_prefix' => 'rd', 'employment_status_id' => $employment_status_id))->row();
		$this->legday = $this->db->get_where('day_type_and_rates_matrix', array('day_prefix' => 'leg', 'employment_status_id' => $employment_status_id))->row();
		$this->speday = $this->db->get_where('day_type_and_rates_matrix', array('day_prefix' => 'spe', 'employment_status_id' => $employment_status_id))->row();
		$this->dobday = $this->db->get_where('day_type_and_rates_matrix', array('day_prefix' => 'dob', 'employment_status_id' => $employment_status_id))->row();
		$this->legrdday = $this->db->get_where('day_type_and_rates_matrix', array('day_prefix' => 'legrd', 'employment_status_id' => $employment_status_id))->row();
		$this->sperdday = $this->db->get_where('day_type_and_rates_matrix', array('day_prefix' => 'sperd', 'employment_status_id' => $employment_status_id))->row();
		$this->dobrdday = $this->db->get_where('day_type_and_rates_matrix', array('day_prefix' => 'dobrd', 'employment_status_id' => $employment_status_id))->row();
	}

	/**
	 * [_get_transaction description]
	 * @param  [type]  $employee                  [description]
	 * @param  [type]  $period                    [description]
	 * @param  [type]  $transaction_type_id       [description]
	 * @param  string  $type                      [description]
	 * @param  boolean $exclude_govt_contribution [description]
	 * @param  boolean $current                   [description]
	 * @param  boolean $this_period_only          [description]
	 * @param  integer 1 = month, 2 year          [description]
	 * @return [type]                             [description]
	 */
	function _get_transaction( $employee, $period, $transaction_type_id, $type = 'sum', $exclude_govt_contribution = true, $current = true, $this_period_only = true, $scope = 1){
		$qry = "select a.*, c.operation";
		if($current)
			$qry .= " FROM {$this->db->dbprefix}payroll_current_transaction a";
		else
			$qry .= " FROM {$this->db->dbprefix}payroll_closed_transaction a";
		
		$qry .= " LEFT JOIN {$this->db->dbprefix}payroll_transaction b on b.transaction_code = a.transaction_code
		LEFT JOIN {$this->db->dbprefix}payroll_transaction_type c on c.transaction_type_id = b.transaction_type_id
		LEFT JOIN {$this->db->dbprefix}payroll_period d on a.period_id = d.payroll_period_id
		LEFT JOIN {$this->db->dbprefix}payroll_transaction_class e on e.transaction_class_id = b.transaction_class_id		
		WHERE a.deleted = 0 AND a.employee_id = {$employee->employee_id} AND a.transaction_type_id IN ({$transaction_type_id})";
		
		if($exclude_govt_contribution) $qry .= " AND e.government_mandated != 1";
		
		if( $current )
			$qry .= " AND d.period_processing_type_id = {$period->period_processing_type_id} AND a.on_hold = 0";
		else{
			switch($scope){
				case 1: //month
					$qry .= " AND MONTH(a.actual_payroll_date) = MONTH('{$period->payroll_date}')";
					break;
				case 2: //year
					$qry .= " AND YEAR(a.actual_payroll_date) = YEAR('{$period->payroll_date}')";
					break;
			}
		}
		if( $this_period_only ) $qry .= " AND a.period_id = {$period->payroll_period_id}";
		$transactions = $this->db->query( $qry );

		switch( $type ){
			case 'result':
				if($transactions->num_rows() > 0){
					return $transactions->result();
				}
				return false;
				break;
			case 'sum':
				$sum = array(0);
				if($transactions->num_rows() > 0){
					foreach( $transactions->result() as  $transaction){
						$sum[] = $transaction->operation == '+' ? $transaction->amount : -1 * $transaction->amount;
					}
				}
				return array_sum( $sum );
				break;
		}
		
	}

	/**
	 * [_get_ot_rate description]
	 * @param  [type] $transaction_code [description]
	 * @return [type]                   [description]
	 */
	function _get_ot_rate( $transaction_code){
		switch($transaction_code){
			case 'REGOT':
				return $this->regday->ot;
				break;
			case 'REGND':
				return $this->regday->nd;
				break;
			case 'REGOT_ND':
				return $this->regday->ndot;
				break;
			case 'RDOT':
				return $this->rdday->ot;
				break;
			case 'RDOT_ND':
				return $this->rdday->ndot;
				break;
			case 'RDOT_EXCESS':
				return $this->rdday->ot_excess;
				break;
			case 'RDOT_ND_EXCESS':
				return $this->rdday->ndot_excess;
				break;
			case 'LEGOT':
				return $this->legday->ot;	
				break;
			case 'LEGOT_ND':
				return $this->legday->ndot;
				break;
			case 'LEGOT_EXCESS':
				return $this->legday->ot_excess;
				break;
			case 'LEGOT_ND_EXCESS':
				return $this->legday->ndot_excess;
				break;
			case 'SPEOT':
				return $this->speday->ot;
				break;
			case 'SPEOT_ND':
				return $this->speday->ndot;
				break;
			case 'SPEOT_EXCESS':
				return $this->speday->ot_excess;
				break;
			case 'SPEOT_ND_EXCESS':
				return $this->speday->ndot_excess;
				break;
			case 'DOBOT':
				return $this->dobday->ot;
				break;
			case 'DOBOT_ND':
				return $this->dobday->ndot;
				break;
			case 'DOBOT_EXCESS':
				return $this->dobday->ot_excess;
				break;
			case 'DOBOT_ND_EXCESS':
				return $this->dobday->ndot_excess;
				break;
			case 'LEGRDOT':
				return $this->legrdday->ot;
				break;
			case 'LEGRDOT_ND':
				return $this->legrdday->ndot;
				break;
			case 'LEGRDOT_EXCESS':
				return $this->legrdday->ot_excess;
				break;
			case 'LEGRDOT_ND_EXCESS':
				return $this->legrdday->ndot_excess;
				break;
			case 'SPERDOT':
				return $this->sperdday->ot;
				break;
			case 'SPERDOT_ND':
				return $this->sperdday->ndot;
				break;
			case 'SPERDOT_EXCESS':
				return $this->sperdday->ot_excess;
				break;
			case 'SPERDOT_ND_EXCESS':
				return $this->sperdday->ndot_excess;
				break;
			case 'DOBRDOT':
				return $this->dobrdday->ot;
				break;
			case 'DOBRDOT_ND':
				return $this->dobrdday->ndot;
				break;
			case 'DOBRDOT_EXCESS':
				return $this->dobrdday->ot_excess;
				break;
			case 'DOBRDOT_ND_EXCESS':
				return $this->dobrdday->ndot_excess;
				break;
		}
		return false;
	}

	/**
	 * [_get_premium_rate description]
	 * @param  [type] $transaction_code [description]
	 * @param  [type] $dtr_summary      [description]
	 * @return [type]                   [description]
	 */
	function _get_premium_rate( $transaction_code, $dtr_summary, $fixed_rate ){
		
		$current_transaction = array();

		if($fixed_rate == 0){

			if($dtr_summary){
				switch($transaction_code){
					case 'REGOT':
						if( !empty( $dtr_summary->reg_ot ) ){
							$premium['ot_rate'] = $this->regday->ot;
							$premium['ot_quantity'] = $dtr_summary->reg_ot;	
						}
						break;
					case 'REGND':
						if( !empty( $dtr_summary->reg_nd ) ){
							$premium['ot_rate'] = $this->regday->nd;
							$premium['ot_quantity'] = $dtr_summary->reg_nd;	
						}
						break;
					case 'REGOT_ND':
						if( !empty( $dtr_summary->reg_ndot ) ){
							$premium['ot_rate'] = $this->regday->ndot;
							$premium['ot_quantity'] = $dtr_summary->reg_ndot;		
						}
						break;
					case 'RDOT':
						if( !empty( $dtr_summary->rd_ot ) ){
							$premium['ot_rate'] = $this->rdday->ot;
							$premium['ot_quantity'] = $dtr_summary->rd_ot;	
						}
						break;
					case 'RDOT_ND':
						if( !empty( $dtr_summary->rd_ndot ) ){
							$premium['ot_rate'] = $this->rdday->ndot;
							$premium['ot_quantity'] = $dtr_summary->rd_ndot;		
						}
						break;
					case 'RDOT_EXCESS':
						if( !empty( $dtr_summary->rd_ot_excess ) ){
							$premium['ot_rate'] = $this->rdday->ot_excess;
							$premium['ot_quantity'] = $dtr_summary->rd_ot_excess;	
						}
						break;
					case 'RDOT_ND_EXCESS':
						if( !empty( $dtr_summary->rd_ndot_excess ) ){
							$premium['ot_rate'] = $this->rdday->ndot_excess;
							$premium['ot_quantity'] = $dtr_summary->rd_ndot_excess;		
						}
						break;
					case 'LEGOT':
						if( !empty( $dtr_summary->leg_ot ) ){
							$premium['ot_rate'] = $this->legday->ot;
							$premium['ot_quantity'] = $dtr_summary->leg_ot;	
						}	
						break;
					case 'LEGOT_ND':
						if( !empty( $dtr_summary->leg_ndot ) ){
							$premium['ot_rate'] = $this->legday->ndot;
							$premium['ot_quantity'] = $dtr_summary->leg_ndot;		
						}
						break;
					case 'LEGOT_EXCESS':
						if( !empty( $dtr_summary->leg_ot_excess ) ){
							$premium['ot_rate'] = $this->legday->ot_excess;
							$premium['ot_quantity'] = $dtr_summary->leg_ot_excess;	
						}
						break;
					case 'LEGOT_ND_EXCESS':
						if( !empty( $dtr_summary->leg_ndot_excess ) ){
							$premium['ot_rate'] = $this->legday->ndot_excess;
							$premium['ot_quantity'] = $dtr_summary->leg_ndot_excess;		
						}
						break;
					case 'SPEOT':
						if( !empty( $dtr_summary->spe_ot ) ){
							$premium['ot_rate'] = $this->speday->ot;
							$premium['ot_quantity'] = $dtr_summary->spe_ot;	
						}
						break;
					case 'SPEOT_ND':
						if( !empty( $dtr_summary->spe_ndot ) ){
							$premium['ot_rate'] = $this->speday->ndot;
							$premium['ot_quantity'] = $dtr_summary->spe_ndot;		
						}
						break;
					case 'SPEOT_EXCESS':
						if( !empty( $dtr_summary->spe_ot_excess ) ){
							$premium['ot_rate'] = $this->speday->ot_excess;
							$premium['ot_quantity'] = $dtr_summary->spe_ot_excess;	
						}
						break;
					case 'SPEOT_ND_EXCESS':
						if( !empty( $dtr_summary->spe_ndot_excess ) ){
							$premium['ot_rate'] = $this->speday->ndot_excess;
							$premium['ot_quantity'] = $dtr_summary->spe_ndot_excess;		
						}
						break;
					case 'DOBOT':
						if( !empty( $dtr_summary->dob_ot ) ){
							$premium['ot_rate'] = $this->dobday->ot;
							$premium['ot_quantity'] = $dtr_summary->dob_ot;	
						}
						break;
					case 'DOBOT_ND':
						if( !empty( $dtr_summary->dob_ndot ) ){
							$premium['ot_rate'] = $this->dobday->ndot;
							$premium['ot_quantity'] = $dtr_summary->dob_ndot;		
						}
						break;
					case 'DOBOT_EXCESS':
						if( !empty( $dtr_summary->dob_ot_excess ) ){
							$premium['ot_rate'] = $this->dobday->ot_excess;
							$premium['ot_quantity'] = $dtr_summary->dob_ot_excess;	
						}
						break;
					case 'DOBOT_ND_EXCESS':
						if( !empty( $dtr_summary->dob_ndot_excess ) ){
							$premium['ot_rate'] = $this->dobday->ndot_excess;
							$premium['ot_quantity'] = $dtr_summary->dob_ndot_excess;		
						}
						break;
					case 'LEGRDOT':
						if( !empty( $dtr_summary->legrd_ot ) ){
							$premium['ot_rate'] = $this->legrdday->ot;
							$premium['ot_quantity'] = $dtr_summary->legrd_ot;	
						}
						break;
					case 'LEGRDOT_ND':
						if( !empty( $dtr_summary->legrd_ndot ) ){
							$premium['ot_rate'] = $this->legrdday->ndot;
							$premium['ot_quantity'] = $dtr_summary->legrd_ndot;		
						}
						break;
					case 'LEGRDOT_EXCESS':
						if( !empty( $dtr_summary->legrd_ot_excess ) ){
							$premium['ot_rate'] = $this->legrdday->ot_excess;
							$premium['ot_quantity'] = $dtr_summary->legrd_ot_excess;	
						}
						break;
					case 'LEGRDOT_ND_EXCESS':
						if( !empty( $dtr_summary->legrd_ndot_excess ) ){
							$premium['ot_rate'] = $this->legrdday->ndot_excess;
							$premium['ot_quantity'] = $dtr_summary->legrd_ndot_excess;		
						}
						break;
					case 'SPERDOT':
						if( !empty( $dtr_summary->sperd_ot ) ){
							$premium['ot_rate'] = $this->sperdday->ot;
							$premium['ot_quantity'] = $dtr_summary->sperd_ot;	
						}
						break;
					case 'SPERDOT_ND':
						if( !empty( $dtr_summary->sperd_ndot ) ){
							$premium['ot_rate'] = $this->sperdday->ndot;
							$premium['ot_quantity'] = $dtr_summary->sperd_ndot;		
						}
						break;
					case 'SPERDOT_EXCESS':
						if( !empty( $dtr_summary->sperd_ot_excess ) ){
							$premium['ot_rate'] = $this->sperdday->ot_excess;
							$premium['ot_quantity'] = $dtr_summary->sperd_ot_excess;	
						}
						break;
					case 'SPERDOT_ND_EXCESS':
						if( !empty( $dtr_summary->sperd_ndot_excess ) ){
							$premium['ot_rate'] = $this->sperdday->ndot_excess;
							$premium['ot_quantity'] = $dtr_summary->sperd_ndot_excess;		
						}
						break;
					case 'DOBRDOT':
						if( !empty( $dtr_summary->dobrd_ot ) ){
							$premium['ot_rate'] = $this->dobrdday->ot;
							$premium['ot_quantity'] = $dtr_summary->dobrd_ot;	
						}
						break;
					case 'DOBRDOT_ND':
						if( !empty( $dtr_summary->dobrd_ndot ) ){
							$premium['ot_rate'] = $this->dobrdday->ndot;
							$premium['ot_quantity'] = $dtr_summary->dobrd_ndot;		
							}
						break;
					case 'DOBRDOT_EXCESS':
						if( !empty( $dtr_summary->dobrd_ot_excess ) ){
							$premium['ot_rate'] = $this->dobrdday->ot_excess;
							$premium['ot_quantity'] = $dtr_summary->dobrd_ot_excess;	
						}
						break;
					case 'DOBRDOT_ND_EXCESS':
						if( !empty( $dtr_summary->dobrd_ndot_excess ) ){
							$premium['ot_rate'] = $this->dobrdday->ndot_excess;
							$premium['ot_quantity'] = $dtr_summary->dobrd_ndot_excess;		
						}
						break;
					case 'SEMIRD': // hdi custom
						if( isset($dtr_summary->semi_ot) && !empty( $dtr_summary->semi_ot ) ){
							$premium['ot_rate'] = $this->regday->ot - 1;
							$premium['ot_quantity'] = $dtr_summary->semi_ot;	
						}
						break;
				}
			}
		}
		else{
			if($dtr_summary){
				switch($transaction_code){
					case 'REGOT':
						if( !empty( $dtr_summary->reg_ot ) ){
							$premium['ot_rate'] = 1;
							$premium['ot_quantity'] = $dtr_summary->reg_ot;	
						}
						break;
					case 'REGND':
						if( !empty( $dtr_summary->reg_nd ) ){
							$premium['ot_rate'] = 0.25;
							$premium['ot_quantity'] = $dtr_summary->reg_nd;	
						}
						break;
					case 'REGOT_ND':
						if( !empty( $dtr_summary->reg_ndot ) ){
							$premium['ot_rate'] = 0.25;
							$premium['ot_quantity'] = $dtr_summary->reg_ndot;		
						}
						break;
					case 'RDOT':
						if( !empty( $dtr_summary->rd_ot ) ){
							$premium['ot_rate'] = 1;
							$premium['ot_quantity'] = $dtr_summary->rd_ot;	
						}
						break;
					case 'RDOT_ND':
						if( !empty( $dtr_summary->rd_ndot ) ){
							$premium['ot_rate'] = 0.25;
							$premium['ot_quantity'] = $dtr_summary->rd_ndot;		
						}
						break;
					case 'RDOT_EXCESS':
						if( !empty( $dtr_summary->rd_ot_excess ) ){
							$premium['ot_rate'] = 1;
							$premium['ot_quantity'] = $dtr_summary->rd_ot_excess;	
						}
						break;
					case 'RDOT_ND_EXCESS':
						if( !empty( $dtr_summary->rd_ndot_excess ) ){
							$premium['ot_rate'] = 0.25;
							$premium['ot_quantity'] = $dtr_summary->rd_ndot_excess;		
						}
						break;
					case 'LEGOT':
						if( !empty( $dtr_summary->leg_ot ) ){
							$premium['ot_rate'] = 1;
							$premium['ot_quantity'] = $dtr_summary->leg_ot;	
						}	
						break;
					case 'LEGOT_ND':
						if( !empty( $dtr_summary->leg_ndot ) ){
							$premium['ot_rate'] = 0.25;
							$premium['ot_quantity'] = $dtr_summary->leg_ndot;		
						}
						break;
					case 'LEGOT_EXCESS':
						if( !empty( $dtr_summary->leg_ot_excess ) ){
							$premium['ot_rate'] = 1;
							$premium['ot_quantity'] = $dtr_summary->leg_ot_excess;	
						}
						break;
					case 'LEGOT_ND_EXCESS':
						if( !empty( $dtr_summary->leg_ndot_excess ) ){
							$premium['ot_rate'] = 0.25;
							$premium['ot_quantity'] = $dtr_summary->leg_ndot_excess;		
						}
						break;
					case 'SPEOT':
						if( !empty( $dtr_summary->spe_ot ) ){
							$premium['ot_rate'] = 1;
							$premium['ot_quantity'] = $dtr_summary->spe_ot;	
						}
						break;
					case 'SPEOT_ND':
						if( !empty( $dtr_summary->spe_ndot ) ){
							$premium['ot_rate'] = 0.25;
							$premium['ot_quantity'] = $dtr_summary->spe_ndot;		
						}
						break;
					case 'SPEOT_EXCESS':
						if( !empty( $dtr_summary->spe_ot_excess ) ){
							$premium['ot_rate'] = 1;
							$premium['ot_quantity'] = $dtr_summary->spe_ot_excess;	
						}
						break;
					case 'SPEOT_ND_EXCESS':
						if( !empty( $dtr_summary->spe_ndot_excess ) ){
							$premium['ot_rate'] = 0.25;
							$premium['ot_quantity'] = $dtr_summary->spe_ndot_excess;		
						}
						break;
					case 'DOBOT':
						if( !empty( $dtr_summary->dob_ot ) ){
							$premium['ot_rate'] = 1;
							$premium['ot_quantity'] = $dtr_summary->dob_ot;	
						}
						break;
					case 'DOBOT_ND':
						if( !empty( $dtr_summary->dob_ndot ) ){
							$premium['ot_rate'] = 0.25;
							$premium['ot_quantity'] = $dtr_summary->dob_ndot;		
						}
						break;
					case 'DOBOT_EXCESS':
						if( !empty( $dtr_summary->dob_ot_excess ) ){
							$premium['ot_rate'] = 1;
							$premium['ot_quantity'] = $dtr_summary->dob_ot_excess;	
						}
						break;
					case 'DOBOT_ND_EXCESS':
						if( !empty( $dtr_summary->dob_ndot_excess ) ){
							$premium['ot_rate'] = 0.25;
							$premium['ot_quantity'] = $dtr_summary->dob_ndot_excess;		
						}
						break;
					case 'LEGRDOT':
						if( !empty( $dtr_summary->legrd_ot ) ){
							$premium['ot_rate'] = 1;
							$premium['ot_quantity'] = $dtr_summary->legrd_ot;	
						}
						break;
					case 'LEGRDOT_ND':
						if( !empty( $dtr_summary->legrd_ndot ) ){
							$premium['ot_rate'] = 0.25;
							$premium['ot_quantity'] = $dtr_summary->legrd_ndot;		
						}
						break;
					case 'LEGRDOT_EXCESS':
						if( !empty( $dtr_summary->legrd_ot_excess ) ){
							$premium['ot_rate'] = 1;
							$premium['ot_quantity'] = $dtr_summary->legrd_ot_excess;	
						}
						break;
					case 'LEGRDOT_ND_EXCESS':
						if( !empty( $dtr_summary->legrd_ndot_excess ) ){
							$premium['ot_rate'] = 0.25;
							$premium['ot_quantity'] = $dtr_summary->legrd_ndot_excess;		
						}
						break;
					case 'SPERDOT':
						if( !empty( $dtr_summary->sperd_ot ) ){
							$premium['ot_rate'] = 1;
							$premium['ot_quantity'] = $dtr_summary->sperd_ot;	
						}
						break;
					case 'SPERDOT_ND':
						if( !empty( $dtr_summary->sperd_ndot ) ){
							$premium['ot_rate'] = 0.25;
							$premium['ot_quantity'] = $dtr_summary->sperd_ndot;		
						}
						break;
					case 'SPERDOT_EXCESS':
						if( !empty( $dtr_summary->sperd_ot_excess ) ){
							$premium['ot_rate'] = 1;
							$premium['ot_quantity'] = $dtr_summary->sperd_ot_excess;	
						}
						break;
					case 'SPERDOT_ND_EXCESS':
						if( !empty( $dtr_summary->sperd_ndot_excess ) ){
							$premium['ot_rate'] = 0.25;
							$premium['ot_quantity'] = $dtr_summary->sperd_ndot_excess;		
						}
						break;
					case 'DOBRDOT':
						if( !empty( $dtr_summary->dobrd_ot ) ){
							$premium['ot_rate'] = 1;
							$premium['ot_quantity'] = $dtr_summary->dobrd_ot;	
						}
						break;
					case 'DOBRDOT_ND':
						if( !empty( $dtr_summary->dobrd_ndot ) ){
							$premium['ot_rate'] = 0.25;
							$premium['ot_quantity'] = $dtr_summary->dobrd_ndot;		
							}
						break;
					case 'DOBRDOT_EXCESS':
						if( !empty( $dtr_summary->dobrd_ot_excess ) ){
							$premium['ot_rate'] = 1;
							$premium['ot_quantity'] = $dtr_summary->dobrd_ot_excess;	
						}
						break;
					case 'DOBRDOT_ND_EXCESS':
						if( !empty( $dtr_summary->dobrd_ndot_excess ) ){
							$premium['ot_rate'] = 0.25;
							$premium['ot_quantity'] = $dtr_summary->dobrd_ndot_excess;		
						}
						break;
					case 'SEMIRD': // hdi custom
						if( isset($dtr_summary->semi_ot) && !empty( $dtr_summary->semi_ot ) ){
							$premium['ot_rate'] = $this->regday->ot - 1;
							$premium['ot_quantity'] = $dtr_summary->semi_ot;	
						}
						break;
				}
			}
		}
		return isset($premium) ? $premium : false;
	}
	
	/**
	 * [_get_recurring_transaction description]
	 * @param  [type] $transaction [description]
	 * @param  [type] $period      [description]
	 * @param  [type] $employee    [description]
	 * @return [type]              [description]
	 */
	function _get_recurring_transaction( $transaction, $period, $employee, $salary ){
		$qry = "select a.*, b.transaction_method_id, b.week
		FROM {$this->db->dbprefix}payroll_recurring_employee a
		LEFT JOIN {$this->db->dbprefix}payroll_recurring b ON a.recurring_id = b.recurring_id
		WHERE b.deleted = 0 AND a.employee_id = {$employee->employee_id} AND b.transaction_id = {$transaction->transaction_id}
		AND '{$period->date_from}' >= b.date_from AND '{$period->date_from}' <= b.date_to";
		$recurrings = $this->db->query($qry);
		
		if( $recurrings->num_rows() > 0 ){
			foreach($recurrings->result() as $recurring){
				//check inclusion of transaction in period
				if(in_array($period->week, explode(',', $recurring->week))){
					switch($recurring->transaction_method_id){
						case '1': //Earning %
							$this->base_on_earning[] = array(
								'transaction' => $transaction,
								'period' => $period,
								'employee' => $employee,
								'salary' =>$salary,
								'recurring' => $recurring
							);
							$amount = 'base_on_earning';
							break;
						case '2': //Period Salary %
							$amount = round( $salary['period'] * $recurring->amount / 100, 2);
							break;
						case '3': //Fixed
							$amount = round( $recurring->amount, 2);
							break;
						case '4': //Period Salary and Attendance (%) 
							if(CLIENT_DIR == 'oams'){
								switch ($employee->payroll_rate_type_id) {
									case 2: //Monthly
										$dtr_summary = $this->db->get_where('timekeeping_period_summary', array('employee_id' => $employee->employee_id, 'payroll_date' => $period->payroll_date))->row();
										$total_ded = ( $dtr_summary->lates + $dtr_summary->undertime + $dtr_summary->lwop + ($dtr_summary->absences * 8) );
										
										$allow_rate_per_day = $recurring->amount / ( $employee->total_year_days / 12 );
										$allow_rate_per_hour = $allow_rate_per_day / 8;
										$allow_amount = $recurring->amount / 2;

										$amount = $allow_amount - ( $allow_rate_per_hour * $total_ded);
										break;
									
									case 3: //Daily
										$dtr_summary = $this->db->get_where('timekeeping_period_summary', array('employee_id' => $employee->employee_id, 'payroll_date' => $period->payroll_date))->row();
										$allow_rate_per_day = $recurring->amount / ( $employee->total_year_days / 12 );
										$amount = $dtr_summary->hours_worked * ($allow_rate_per_day / 8);
										break;
								}
							}
							else{
								$dtr_summary = $this->db->get_where('timekeeping_period_summary', array('employee_id' => $employee->employee_id, 'payroll_date' => $period->payroll_date))->row();
								$allow_rate_per_day = $recurring->amount / ( $employee->total_year_days / 12 );
								$amount = $dtr_summary->hours_worked * ($allow_rate_per_day / 8);
							}
							break;
						case '5': //Actual Salary %
							$amount = round( $employee->salary * $recurring->amount / 100, 2);
							break;
					}
					
					$transactions[] = array(
						'amount' => $amount,
						'record_id' => $recurring->recurring_id
					);
				}
			}
		}

		return isset($transactions) ? $transactions : false;
	}

	/**
	 * [_get_batch_entry description]
	 * @param  [type] $transaction [description]
	 * @param  [type] $period      [description]
	 * @param  [type] $employee    [description]
	 * @return [type]              [description]
	 */
	function _get_batch_entry( $transaction, $period, $employee ){
		$qry = "SELECT a.*, b.doc_no
		FROM {$this->db->dbprefix}payroll_batch_entry_employee a
		LEFT JOIN {$this->db->dbprefix}payroll_batch_entry b on b.batch_entry_id = a.batch_entry_id
		WHERE b.deleted = 0 AND b.payroll_date = '{$period->payroll_date}' AND a.employee_id = '{$employee->employee_id}'
		AND b.transaction_id = '{$transaction->transaction_id}'";

		$batch_entries = $this->db->query( $qry );
		
		if( $batch_entries->num_rows() > 0 ){
			foreach($batch_entries->result() as $row){
				$batch_entry[] =array(
					'doc_no' => $row->doc_no,
					'quantity' => $row->quantity,
					'unit_rate' => $row->unit_rate,
					'amount' => $row->amount,
					'record_id' => $row->batch_entry_id
				);
			}
		}

		return isset($batch_entry) ? $batch_entry : false;
	}

	/**
	 * [_get_bonus description]
	 * @param  [type] $period      [description]
	 * @param  [type] $employee    [description]
	 * @param  [type] $transaction [description]
	 * @param  [type] $salary      [description]
	 * @return [type]              [description]
	 */
	function _get_bonus( $period, $employee, $transaction, $salary ){
		$qry = "SELECT a.amount, a.employee_id, b.*
		FROM {$this->db->dbprefix}payroll_bonus_employee a
		LEFT JOIN {$this->db->dbprefix}payroll_bonus b on b.bonus_id = a.bonus_id
		WHERE b.deleted = 0 AND b.payroll_date = '{$period->payroll_date}' AND a.employee_id = '{$employee->employee_id}'
		AND b.bonus_transaction_id = '{$transaction->transaction_id}'";

		$bonuses = $this->db->query($qry);
		
		if( $bonuses->num_rows() > 0 ){
			foreach($bonuses->result() as $bonus){
				switch($bonus->transaction_method_id){
					case '1': //Earning %
						$this->base_on_earning[] = array(
							'transaction' => $transaction,
							'period' => $period,
							'employee' => $employee,
							'salary' =>$salary,
							'bonus' => $bonus
						);
						$amount = 'base_on_earning';
						break;
					case '2': //Period Salary %
						$amount = round( $salary['period'] * $bonus->amount / 100, 2);
						break;
					case '3': //Fixed
						$amount = round( $bonus->amount, 2);
						break;
					case '4': //YTD Salary and Attendance %
						$amount = $this->_get_ytd_salary($employee->employee_id, $bonus->date_from, $bonus->date_to, true);
						$amount = $amount / 12;
						$amount = round( $amount * $bonus->amount / 100, 2);
						break;
					case '5': //Actual Salary %
						$amount = round( $employee->salary * $bonus->amount / 100, 2);
						break;	
				}
				
				$transactions[] = array(
					'amount' => $amount,
					'record_id' => $bonus->bonus_id
				);

				if($amount > $this->nontaxable_bonus_per_annum){

				}
			}
		}
		else{
			//check for accrual
			$qry = "SELECT a.amount, a.employee_id, b.*
			FROM {$this->db->dbprefix}payroll_bonus_employee a
			LEFT JOIN {$this->db->dbprefix}payroll_bonus b on b.bonus_id = a.bonus_id
			WHERE b.deleted = 0 AND '{$period->payroll_date}' BETWEEN b.date_from and b.date_to AND a.employee_id = '{$employee->employee_id}'
			AND b.bonus_transaction_id = '{$transaction->transaction_id}'";

			$bonuses = $this->db->query($qry);
		}

		return isset($transactions) ? $transactions : false;
	}
	
	/**
	 * [_get_employee_netpay description]
	 * @param  [type] $employee [description]
	 * @param  [type] $period   [description]
	 * @return [type]           [description]
	 */
	function _get_employee_netpay( $employee, $period ){
		$qry = "select a.*, c.operation
		FROM {$this->db->dbprefix}payroll_current_transaction a
		LEFT JOIN {$this->db->dbprefix}payroll_transaction b on b.transaction_code = a.transaction_code
		LEFT JOIN {$this->db->dbprefix}payroll_transaction_type c on c.transaction_type_id = b.transaction_type_id
		LEFT JOIN {$this->db->dbprefix}payroll_period d on a.period_id = d.payroll_period_id
		WHERE a.deleted = 0 AND a.employee_id = {$employee->employee_id} AND a.on_hold = 0 AND a.deleted = 0 AND b.deleted = 0
			AND d.period_processing_type_id = {$period->period_processing_type_id} AND a.transaction_type_id is not null";
		
		$netpay = array(0);
		$current_transaction = $this->db->query( $qry );
		foreach( $current_transaction->result() as  $transaction){
			$netpay[] = $transaction->operation == '+' ? $transaction->amount : -1 * $transaction->amount;
		}
		
		return array_sum( $netpay );
	}
	
	/**
	 * [_get_employee_salary_for_period description]
	 * @param  array  $employee [description]
	 * @param  array  $period   [description]
	 * @return [type]           [description]
	 */
	function _get_employee_salary_for_period( $employee = array(), $dtr_summary = false, $period ){
		$sched_rate = $this->db->get_where('payroll_schedule_rate_divisor', array('payroll_rate_type_id' => $employee->payroll_rate_type_id, 'payroll_schedule_id' => $employee->payroll_schedule_id))->row();
		//get daily salary
		switch( $employee->payroll_rate_type_id ){
			case 1: //yearly
				$salary['period'] = $employee->salary / $sched_rate->divisor;
				$salary['daily'] = $employee->salary / $employee->total_year_days;
				break;
			case 2: //Monthly
				$salary['period'] = $employee->salary / $sched_rate->divisor;
				$salary['daily'] = $employee->salary * 12 / $employee->total_year_days;
				break;
			case 3: //Daily Averaged
				$salary['period'] = $employee->salary * $sched_rate->divisor;
				$salary['daily'] = $employee->salary;
				break;
			case 4: //Piece Rate
				$dtr_summary = $this->db->get_where('timekeeping_period_summary', array('employee_id' => $employee->employee_id, 'payroll_date' => $period->payroll_date))->row();
				$salary['daily'] = $employee->salary * $dtr_summary->hours_worked;
				break;
			case 5: //Commision
				$salary['period'] = $salary['daily'] = 0;
				break;
			case 6: //Daily Actual
				$salary['period'] = $dtr_summary ? $employee->salary * $dtr_summary->hours_worked / 8 : 0;
				$salary['daily'] = $employee->salary;
				break;	
		}

		//check if minimum wage
		$salary['minimum_wage'] = false;
		$salary['ecola'] = 0;
		$salary['ecola_period'] = 0;

		if(CLIENT_DIR == 'firstbalfour'){
			if( $employee->paycode_id == 8 || $employee->paycode_id == 5){
				$salary['minimum_wage'] = true;
			}
			if( !empty($employee->location_id) ){
				$location = $this->db->get_where('user_location', array('location_id' => $employee->location_id));
				if($location->num_rows() == 1){
					$location = $location->row();
					$city = $this->db->get_where('cities', array('city_id' => $location->city_id));
					if($city->num_rows() == 1){
						$city = $city->row();
						if( ($salary['daily'] + (float)$city->ecola) <= (float)$city->minimum_wage ){
							$salary['minimum_wage'] = true;
							$salary['ecola'] = (float)$city->ecola;
							$salary['ecola_period'] = $this->_get_ecola_for_period( $employee, $salary, $period );
						}
					}
				}
			}
		}
		else {
			if( !empty($employee->location_id) ){
				$location = $this->db->get_where('user_location', array('location_id' => $employee->location_id));
				if($location->num_rows() == 1){
					$location = $location->row();
					$city = $this->db->get_where('cities', array('city_id' => $location->city_id));
					if($city->num_rows() == 1){
						$city = $city->row();
						if( ($salary['daily'] + (float)$city->ecola) <= (float)$city->minimum_wage ){
							$salary['minimum_wage'] = true;
							$salary['ecola'] = (float)$city->ecola;
							$salary['ecola_period'] = $this->_get_ecola_for_period( $employee, $salary, $period );
						}
					}
				}
			}
		}

		return $salary;
	}

	/**
	 * [_get_ecola_for_period description]
	 * @param  [type] $employee [description]
	 * @param  [type] $salary   [description]
	 * @return [type]           [description]
	 */
	function _get_ecola_for_period( $employee, $salary, $period ){
		$sched_rate = $this->db->get_where('payroll_schedule_rate_divisor', array('payroll_rate_type_id' => $employee->payroll_rate_type_id, 'payroll_schedule_id' => $employee->payroll_schedule_id))->row();
		switch( $employee->payroll_rate_type_id ){
			case 1: //yearly
				$ecola = $salary['ecola'] * $employee->total_year_days;
				$ecola = $ecola / $sched_rate->divisor;
				break;
			case 2: //Monthly
				$ecola = $salary['ecola'] * $employee->total_year_days / 12;
				$ecola = $ecola / $sched_rate->divisor;
				break;
			case 3: //Daily
				$ecola = $salary['ecola'] * $sched_rate->divisor;
				break;
			case 4: //Piece Rate
			case 5: //Commision
				$ecola = 0;
				break;
			case 6:
				$dtr_summary = $this->db->get_where('timekeeping_period_summary', array('employee_id' => $employee->employee_id, 'payroll_date' => $period->payroll_date))->row();
				$ecola = $dtr_summary ? $salary['ecola'] * $dtr_summary->hours_worked / 8 : 0;
				break;

		}

		return $ecola;
	}

	/**
	 * [_get_sss_employee_contribution description]
	 * @param  [type] $period   [description]
	 * @param  [type] $employee [description]
	 * @param  [type] $salary   [description]
	 * @return [type]           [description]
	 */
	function _get_monthly_govt_contribution( $period, $employee, $earning, $code ){
		$code_week = $code.'_week';
		$no_contributions = sizeof( explode(',', $employee->$code_week) );

		//adjust earning to match monthly contribution table
		switch( $period->payroll_schedule_id ){
			case 1: //yearly
				$bracket = $earning / 12;
				break;
			case 2: //semi-annual
				$bracket = $earning / 6;
				break;
			case 3: //quarterly
				$bracket = $earning / 3;
				break;
			case 4: //Monthly
				$bracket = $earning;
				break;
			case 5: //Semi-monthly
				$bracket = $earning * 2;
				break;
			case 6: //Weekly
				$bracket = $earning * 4.33; //base on 52 week year
				break;
		}

		$this->db->where('code', $code);
		$this->db->where("`from` <= '{$bracket}' AND `to` >= '{$bracket}'");
		$govt_contribution = $this->db->get('payroll_govt_contribution')->row();

		$contribution['eeshare'] = round($govt_contribution->eeshare / $no_contributions, 2);
		$contribution['ershare'] = round($govt_contribution->ershare / $no_contributions, 2);
		$contribution['ec'] = round($govt_contribution->ec / $no_contributions, 2);
		$contribution['msb_id'] = $govt_contribution->msb_id;

		return $contribution;
	}

	/**
	 * [_get_monthly_govt_contribution_actual description]
	 * @param  [type] $period      [description]
	 * @param  [type] $employee    [description]
	 * @param  [type] $transaction [description]
	 * @param  [type] $earning     [description]
	 * @param  [type] $code        [description]
	 * @return [type]              [description]
	 */
	function _get_monthly_govt_contribution_actual( $period, $employee, $transaction, $earning, $code ){
		//get paid for same month
		$qry = "SELECT IF(SUM(employee) is null, 0, SUM(employee)) AS eeshare, IF(SUM(company) is null, 0, SUM(company)) AS ershare, IF(SUM(ec) is null, 0, SUM(ec)) AS ec
		FROM {$this->db->dbprefix}employee_contribution a
		LEFT JOIN {$this->db->dbprefix}payroll_period b ON b.payroll_period_id = a.payroll_period_id
		WHERE a.employee_id = {$employee->employee_id} AND MONTH(a.payroll_date) = MONTH('{$period->payroll_date}')
		AND a.transaction_id = {$transaction->transaction_id} AND b.period_status_id = 3 AND b.deleted = 0";
		$paid = $this->db->query( $qry )->row();
		$paid_row = $this->db->query( $qry )->num_rows();
		
		if($code == 'sss'){
			$bracket = $earning + $this->_get_transaction( $employee, $period, '1,5', 'sum', true, false, false );
		}

		if($code == 'phic'){
			$bracket = $earning;
		}

		$this->db->where('code', $code);
		$this->db->where("`from` <= '{$bracket}' AND `to` >= '{$bracket}'");
		$govt_contribution = $this->db->get('payroll_govt_contribution');
		
		$max_share = $this->db->query("SELECT MAX(eeshare) as eeshare, MAX(ershare) as ershare, MAX(ec) as ec FROM hr_payroll_govt_contribution WHERE CODE = '$code'")->row();

		if($govt_contribution->num_rows() > 0){
			$govt_contribution = $govt_contribution->row();

			if($code == 'phic'){
				if($paid_row > 0){
					$phic_share = $govt_contribution->eeshare + $paid->eeshare;

					if($phic_share > $max_share->eeshare){
						$contribution['eeshare'] = round($max_share->eeshare - $paid->eeshare, 2);
						$contribution['ershare'] = round($max_share->ershare - $paid->ershare, 2);
						$contribution['ec'] = round($govt_contribution->ec, 2);
						$contribution['msb_id'] = $govt_contribution->msb_id;	
					}
					else{
						$contribution['eeshare'] = round($govt_contribution->eeshare, 2);
						$contribution['ershare'] = round($govt_contribution->ershare, 2);
						$contribution['ec'] = round($govt_contribution->ec, 2);
						$contribution['msb_id'] = $govt_contribution->msb_id;
					}
				}
				else{
					$contribution['eeshare'] = round($govt_contribution->eeshare, 2);
					$contribution['ershare'] = round($govt_contribution->ershare, 2);
					$contribution['ec'] = round($govt_contribution->ec, 2);
					$contribution['msb_id'] = $govt_contribution->msb_id;
				}
			}
			else{
				$contribution['eeshare'] = round($govt_contribution->eeshare - $paid->eeshare, 2);
				$contribution['ershare'] = round($govt_contribution->ershare - $paid->ershare, 2);
				$contribution['ec'] = round($govt_contribution->ec - $paid->ec, 2);
				$contribution['msb_id'] = $govt_contribution->msb_id;
			}
		}
		else{
			$contribution['eeshare'] = 0;
			$contribution['ershare'] = 0;
			$contribution['ec'] = 0;
			$contribution['msb_id'] = 0;
		}
		return $contribution;
	}

	function _get_monthly_govt_contribution_actual_hdmf( $period, $employee, $transaction, $earning, $code ){
		//get paid for same month
		$qry = "SELECT IF(SUM(employee) is null, 0, SUM(employee)) AS eeshare, IF(SUM(company) is null, 0, SUM(company)) AS ershare, IF(SUM(ec) is null, 0, SUM(ec)) AS ec
		FROM {$this->db->dbprefix}employee_contribution a
		LEFT JOIN {$this->db->dbprefix}payroll_period b ON b.payroll_period_id = a.payroll_period_id
		WHERE a.employee_id = {$employee->employee_id} AND MONTH(a.payroll_date) = MONTH('{$period->payroll_date}')
		AND a.transaction_id = {$transaction->transaction_id} AND b.period_status_id = 3";

		$paid = $this->db->query( $qry )->row();

		if($paid->eeshare == 100 ){
			$contribution['eeshare'] = 0;
			$contribution['ershare'] = 0;
			$contribution['ec'] = 0;
		}
		else{
			$hdmf_eeshare = $this->_get_transaction( $employee, $period, '1,5', 'sum', true, true, true ) * .02;
			if ($hdmf_eeshare > 100){
				$hdmf_eeshare = 100;
			}
			$hdmf_ershare = $hdmf_eeshare;	
			
			if($hdmf_eeshare + $paid->eeshare > 100){
				$contribution['eeshare'] = round(100 - $paid->eeshare, 2);
				$contribution['ershare'] = round(100 - $paid->ershare, 2);
				$contribution['ec'] = 0;				
			}
			else{
				$contribution['eeshare'] = round($hdmf_eeshare, 2);
				$contribution['ershare'] = round($hdmf_ershare, 2);
				$contribution['ec'] = 0;	
			}
		}
		return $contribution;
	}

	/**
	 * [_get_govt_contribution description]
	 * @param  [type] $mode        [description]
	 * @param  [type] $code        [description]
	 * @param  [type] $transaction [description]
	 * @param  [type] $period      [description]
	 * @param  [type] $employee    [description]
	 * @return [type]              [description]
	 */
	function _get_govt_contribution($mode, $code, $transaction, $period, $employee, $salary){
		switch($mode){
			case 1: //default, base on salary
				$govt_contribution = $this->_get_monthly_govt_contribution($period, $employee, ($salary['period'] + $salary['ecola_period']), $code);
				if( !$transaction ) return $govt_contribution;
				return array(
					'period_id' => $period->payroll_period_id,
					'processing_type_id' => $period->period_processing_type_id,
					'payroll_date' => $period->payroll_date,	
					'transaction_id' => $transaction->transaction_id,
					'transaction_code' => $transaction->transaction_code,
					'transaction_type_id' => $transaction->transaction_type_id,
					'employee_id' => $employee->employee_id,
					'unit_rate' =>  $govt_contribution['eeshare'],
					'amount' => $govt_contribution['eeshare'],
					'inserted_from_id' => 1,
					'detail' => $govt_contribution
				);
				break;
			case 3: //manual
				$code_amount = $code.'_amount';
				$govt_contribution = $this->_get_monthly_govt_contribution($period, $employee, $employee->salary, $code);
				return array(
					'period_id' => $period->payroll_period_id,
					'processing_type_id' => $period->period_processing_type_id,
					'payroll_date' => $period->payroll_date,	
					'transaction_id' => $transaction->transaction_id,
					'transaction_code' => $transaction->transaction_code,
					'transaction_type_id' => $transaction->transaction_type_id,
					'employee_id' => $employee->employee_id,
					'unit_rate' =>  $employee->$code_amount,
					'amount' => $employee->$code_amount,
					'inserted_from_id' => 1,
					'detail' => $govt_contribution
				);
				break;
			case 4: //custom
				break;
			case 5: //actual earning
				$this->base_on_earning[] = array(
					'transaction' => $transaction,
					'period' => $period,
					'employee' => $employee,
					'salary' =>$salary,
					'govt_code' => $code
				);
				return array(
					'period_id' => $period->payroll_period_id,
					'processing_type_id' => $period->period_processing_type_id,
					'payroll_date' => $period->payroll_date,	
					'transaction_id' => $transaction->transaction_id,
					'transaction_code' => $transaction->transaction_code,
					'transaction_type_id' => $transaction->transaction_type_id,
					'employee_id' => $employee->employee_id,
					'unit_rate' =>  'base_on_earning',
					'amount' => 'base_on_earning',
					'inserted_from_id' => 1,
				);
				break;	
		}
		return false;
	}
	
	/**
	 * [_get_whtax description]
	 * @param  [type] $employee [description]
	 * @param  [type] $period   [description]
	 * @return [type]           [description]
	 */
	function _get_whtax( $employee, $period ){
		$earning = $this->_get_transaction( $employee, $period, '1,5', 'sum', false, true, false );
		$current_bonus = $this->_get_transaction( $employee, $period, '8', 'sum', false, true, true );

		if($current_bonus > $this->nontaxable_bonus_per_annum){
			$earning += ($current_bonus - $this->nontaxable_bonus_per_annum);
		}
		
		switch( $period->payroll_schedule_id ){
			case 1: //yearly
				$bracket = $earning / 12;
				$employee->payroll_schedule_id = 4;
				break;
			case 2: //semi-annual
				$bracket = $earning  / 6;
				$employee->payroll_schedule_id = 4;
				break;
			case 3: //quarterly
				$bracket = $earning / 3;
				$employee->payroll_schedule_id = 4;
				break;
			default:
				$bracket = $earning;
		}

		if($bracket >= 1 || ($employee->taxcode_id == 6 && $bracket < 1)){
			$qry = "select a.*
			FROM {$this->db->dbprefix}payroll_whtax_table a
			where {$bracket} BETWEEN a.salary_from AND a.salary_to AND a.payroll_schedule_id = {$employee->payroll_schedule_id} AND a.taxcode_id = {$employee->taxcode_id}";
			$whtax_table = $this->db->query( $qry )->row();
			
			$excess = $earning - $whtax_table->salary_from;
			$excess_percentage = $excess * $whtax_table->excess_percentage / 100;
			$whtax = $whtax_table->fixed_amount + $excess_percentage;
			return $whtax;
		}
		
		return 0;
	}

	/**
	 * [_get_annualized_whtax description]
	 * @param  [type] $employee [description]
	 * @param  [type] $period   [description]
	 * @param  [type] $salary   [description]
	 * @return [type]           [description]
	 */
	function _get_annualized_whtax( $employee, $period, $salary ){
		$current_earnings = $this->_get_transaction( $employee, $period, '1,5', 'sum', false, true, false, 2 );
		$closed_earnings = $this->_get_transaction( $employee, $period, '1,5', 'sum', false, false, false, 2 );
		$ytd_earning = $current_earnings + $closed_earnings;
		// dbug($ytd_earning . ' = '. $current_earnings.' + '.$closed_earnings);
		//get taxable bonus
		$ytd_earning += $this->_get_taxable_bonus( $employee, $period ); 

		// dbug('bonus'.$ytd_earning);
		if($period->period_processing_type_id == 3 ){
			$remain = 1;
			$current_govt_contr = $this->_get_transaction( $employee, $period, '5', 'sum', false, true, false, 2 );
			$closed_govt_contr = $this->_get_transaction( $employee, $period, '5', 'sum', false, false, false, 2 );
		}
		else{
			$remain = $this->schedule->remaining_period;
		}
		
		// dbug($current_govt_contr .' + '.$closed_govt_contr );

		if( $this->schedule->remaining_period != 0  && $period->period_processing_type_id == 1){

			//project govt mandated deduction
			$ytd_earning += $salary['period'] * ($remain - 1);

			$phic_period = $this->_get_govt_contribution(1, 'phic', false, $period, $employee, $salary);
			$phic_week = sizeof( explode(',',$employee->phic_week) );
			$phic_no_paid = floor( ($this->schedule->total_period_per_annum - ($remain - 1) ) / $phic_week );
			$phic_yearly_no_contributions = $phic_week *  12;
			$ytd_earning -= $phic_period['eeshare'] * ( $phic_yearly_no_contributions - $phic_no_paid );

			$hdmf_period = $this->_get_govt_contribution(1, 'hdmf', false, $period, $employee, $salary);
			$hdmf_week = sizeof( explode(',',$employee->hdmf_week) );
			$hdmf_no_paid = floor( ($this->schedule->total_period_per_annum - ($remain - 1) ) / $hdmf_week );
			$hdmf_yearly_no_contributions = $hdmf_week *  12;
			$ytd_earning -= $hdmf_period['eeshare'] * ( $hdmf_yearly_no_contributions - $hdmf_no_paid );

			$sss_period = $this->_get_govt_contribution(1, 'sss', false, $period, $employee, $salary);
			$sss_week = sizeof( explode(',',$employee->sss_week) );
			$sss_no_paid = floor( ($this->schedule->total_period_per_annum - ($remain - 1) ) / $sss_week );
			$sss_yearly_no_contributions = $sss_week *  12;
			$ytd_earning -= $sss_period['eeshare'] * ( $sss_yearly_no_contributions - $sss_no_paid );
		}
		

		
		//remove excemption
		$taxcode = $this->db->get_where('taxcode', array('taxcode_id' => $employee->taxcode_id))->row();
		$taxable_earnings = $ytd_earning - $taxcode->amount;
		// dbug($taxable_earnings.' = '.$ytd_earning.' - '.$taxcode->amount);
		$tax = 0;

		if( $taxable_earnings > 0){
			$qry = "select a.*
			FROM {$this->db->dbprefix}payroll_annual_tax a
			where {$taxable_earnings} BETWEEN a.from AND a.to";
			$annual_tax = $this->db->query( $qry )->row();
			$excess = $taxable_earnings - $annual_tax->from;

			// dbug($excess.' = '.$taxable_earnings.' - '.$annual_tax->from);

			$excess_percentage = $excess * $annual_tax->rate / 100;

			// dbug($excess_percentage.' = '.$excess.' * '.$annual_tax->rate);
			
			$tax = $annual_tax->amount + $excess_percentage;
			// dbug($tax.' = '.$annual_tax->amount.' + '.$excess_percentage);
		}
		
		$paid_tax = $this->_employee_ytd_tax( $employee, $period );
		// dbug('$paid_tax '.$paid_tax );

		if( $this->schedule->remaining_period != 0 ){
			return ($tax - $paid_tax) / $remain; //$this->schedule->remaining_period includes the current period
		}

		return $tax - $paid_tax;
	}

	/**
	 * [_get_taxable_bonus description]
	 * @param  [type] $employee [description]
	 * @param  [type] $period   [description]
	 * @return [type]           [description]
	 */
	function _get_taxable_bonus( $employee, $period ){
		$ytd_bonus = $this->_get_ytd_bonus($employee, $period);
		$de_minismis_excess = $this->_get_de_minimis_excess( $employee, $period );

		$total_bonus = $ytd_bonus + $de_minismis_excess;

		if($total_bonus > $this->nontaxable_bonus_per_annum){
			return $total_bonus - $this->nontaxable_bonus_per_annum;
		}

		return 0;
	}

	/**
	 * [_get_ytd_bonus description]
	 * @param  [type] $employee [description]
	 * @param  [type] $period   [description]
	 * @return [type]           [description]
	 */
	function _get_ytd_bonus( $employee, $period ){
		$current_bonus = $this->_get_transaction( $employee, $period, '8', 'sum', false, true, true, 2);
		$closed_bonus = $this->_get_transaction( $employee, $period, '8', 'sum', false, false, false, 2 );
		
		return $current_bonus + $closed_bonus;
	}

	/**
	 * [_employee_ytd_tax description]
	 * @param  [type] $employee [description]
	 * @param  [type] $period   [description]
	 * @return [type]           [description]
	 */
	function _employee_ytd_tax( $employee, $period ){
		//get closed whtax
		$qry = "SELECT IF(SUM(amount) IS NOT NULL, SUM(amount), 0) AS paid_tax
		FROM {$this->db->dbprefix}payroll_closed_transaction
		WHERE employee_id = {$employee->employee_id} AND YEAR(actual_payroll_date) = YEAR('{$period->payroll_date}') AND transaction_code = 'WHTAX' AND deleted = 0";
		$paid_tax = $this->db->query( $qry )->row();

		return $paid_tax->paid_tax;
	}
	
	/**
	 * [_get_de_minimis_excess description]
	 * @param  [type] $employee [description]
	 * @param  [type] $period   [description]
	 * @return [type]           [description]
	 */
	function _get_de_minimis_excess( $employee, $period ){	
		$benefits = array();

		//get current de minimis
		$qry = "select a.*, c.operation
		FROM {$this->db->dbprefix}payroll_current_transaction a
		LEFT JOIN {$this->db->dbprefix}payroll_transaction b on b.transaction_code = a.transaction_code
		LEFT JOIN {$this->db->dbprefix}payroll_transaction_type c on c.transaction_type_id = b.transaction_type_id
		LEFT JOIN {$this->db->dbprefix}payroll_period d on a.period_id = d.payroll_period_id
		LEFT JOIN {$this->db->dbprefix}payroll_transaction_class e on e.transaction_class_id = b.transaction_class_id
		WHERE a.employee_id = {$employee->employee_id} AND a.transaction_type_id IN (6)
		AND d.period_processing_type_id = {$period->period_processing_type_id} AND a.on_hold = 0";

		$current = $this->db->query( $qry );

		if( $current->num_rows() > 0 ){
			foreach( $current->result() as $row ){
				$benefits[$row->transaction_id][] = $row;
			}
		}

		//get de minimis closed transactions
		$qry = "select a.*, c.operation
		FROM {$this->db->dbprefix}payroll_closed_transaction a
		LEFT JOIN {$this->db->dbprefix}payroll_transaction b on b.transaction_code = a.transaction_code
		LEFT JOIN {$this->db->dbprefix}payroll_transaction_type c on c.transaction_type_id = b.transaction_type_id
		LEFT JOIN {$this->db->dbprefix}payroll_period d on a.period_id = d.payroll_period_id
		LEFT JOIN {$this->db->dbprefix}payroll_transaction_class e on e.transaction_class_id = b.transaction_class_id
		WHERE a.employee_id = {$employee->employee_id} AND a.transaction_type_id IN (6)
		AND YEAR(a.actual_payroll_date) = YEAR('{$period->payroll_date}')";

		$closed = $this->db->query( $qry );

		if( $closed->num_rows() > 0 ){
			foreach( $closed->result() as $row ){
				$benefits[$row->transaction_id][] = $row;
			}
		}

		$taxable_benefit = 0;

		foreach($benefits as $transaction_id => $rows){
			$sum = array();
			foreach($rows as $row){
				$sum[] = $row->operation == '+' ? $row->amount : -1 * $row->amount;
			}
			$sum = array_sum( $bonus );
			$transaction = $this->db->get_where('payroll_transaction', array('transaction_id' => $transaction_id))->row();
			if( $transaction->per_annum_cap < $sum ){
				$taxable_benefit += ($sum - $transaction->per_annum_cap);	
			}
		}

		return $taxable_benefit;
	}

	/**
	 * [_get_loans description]
	 * @param  [type] $period      [description]
	 * @param  [type] $employee    [description]
	 * @param  [type] $transaction [description]
	 * @return [type]              [description]
	 */
	function _get_loans( $period, $employee, $transaction ){
		$qry = "select a.*, b.loan_mode_id
		FROM {$this->db->dbprefix}employee_loan a
		LEFT JOIN {$this->db->dbprefix}payroll_loan b on b.loan_id = a.loan_id
		WHERE a.employee_id = {$employee->employee_id} AND a.loan_status_id NOT IN (3,4) and a.start_date <= '{$period->payroll_date}'
		AND a.running_balance > 0 AND b.amortization_transid = {$transaction->transaction_id} AND a.deleted = 0";
		$loans = $this->db->query( $qry );
		
		if( $loans->num_rows() > 0 ){
			$current_transaction = array();
			foreach($loans->result() as $loan){
				$weeks = explode(',', $loan->week);
				if( in_array($period->week, $weeks) ){
					switch( $loan->payment_mode_id ){
						case 1:
						case 4:
							$values = $this->_system_amortization_interest( $loan, $employee );
							break;
						case 2:
						case 3:
							$values = $this->_user_amortization_interest( $loan, $employee );
							break;
					}
					
					$current_transaction[] = array(
						'period_id' => $period->payroll_period_id,
						'processing_type_id' => $period->period_processing_type_id,
						'payroll_date' => $period->payroll_date,	
						'transaction_id' => $transaction->transaction_id,
						'transaction_code' => $transaction->transaction_code,
						'transaction_type_id' => $transaction->transaction_type_id,
						'employee_id' => $employee->employee_id,
						'unit_rate' =>  $values['amortization'],
						'amount' => $values['amortization'],
						'inserted_from_id' => 4,
						'record_id' => $loan->employee_loan_id,
						'record_from' => 'employee_loan'
					);
					$this->loan_interest[] = array(
						'interest' => $values['interest'],
						'inserted_from_id' => 5,
						'record_id' => $loan->employee_loan_id,
						'record_from' => 'employee_loan'

					);
				}
			}
			return $current_transaction;
		}
		return false;
	}

	/**
	 * [_system_amortization_interest description]
	 * @param  [type] $loan     [description]
	 * @param  [type] $employee [description]
	 * @return [type]           [description]
	 */
	function _system_amortization_interest( $loan, $employee ){
		//$no_of_payments = sizeof(explode(',', $loan->week));
		$no_of_payments = 1;
		$amortization = round($loan->system_amortization/$no_of_payments, 2);
		
		switch($loan->loan_mode_id){
			case 1: //simple
			case 3: //diminishing with equal payments
				$interest = round($loan->system_interest/$no_of_payments, 2);
				break;
			case 2: //diminishing
				switch( $loan->interest_type_id ){
					case 1: //amount, convert to percent
						$interest_percentage = $loan->interest / $loan->amount;
						break;
					case 2: //percent
						$interest_percentage = $loan->interest / 100;
						break;
				}
				$interest = ($loan->amount - ($loan->no_payments_paid * $loan->system_amortization)) * $interest_percentage;
				$interest = round($interest/$no_of_payments,2);
				break;
		}

		return array('amortization' => $amortization, 'interest' => $interest);
	}

	/**
	 * [_user_amortization_interest description]
	 * @param  [type] $loan     [description]
	 * @param  [type] $employee [description]
	 * @return [type]           [description]
	 */
	function _user_amortization_interest( $loan, $employee ){
		// $no_of_payments = sizeof(explode(',', $loan->week));
		$no_of_payments = 1;
		$amortization = round($loan->user_amortization/$no_of_payments, 2);
		$interest = round($loan->user_interest/$no_of_payments, 2);
		return array('amortization' => $amortization, 'interest' => $interest);
	}

	/**
	 * [_unhold_transactions description]
	 * @param  [type] $employee [description]
	 * @param  [type] $period   [description]
	 * @return [type]           [description]
	 */
	function _unhold_transactions( $employee, $period ){
		$qry = "update {$this->db->dbprefix}payroll_current_transaction a
		LEFT JOIN {$this->db->dbprefix}payroll_transaction b on b.transaction_code = a.transaction_code
		LEFT JOIN {$this->db->dbprefix}payroll_transaction_type c on c.transaction_type_id = b.transaction_type_id
		LEFT JOIN {$this->db->dbprefix}payroll_period d on a.period_id = d.payroll_period_id
		SET a.on_hold = 0
		WHERE a.employee_id = {$employee->employee_id} AND d.period_processing_type_id = {$period->period_processing_type_id}";

		$this->db->query( $qry );
	}

	/**
	 * [close_period description]
	 * @param  [type] $period [description]
	 * @return [type]         [description]
	 */
	function close_period( $period ){
		switch( $period->apply_to_id ){
			case 1: //employees
				$where = "b.employee_id";
				break;
			case 2: //company
				$where = "b.company_id";
				break;
			case 3: //division
				$where = "b.division_id";
				break;
			case 4: //department
				$where = "b.department_id";
				break;
			case 5: //paycode
				$where = "t.paycode_id";
				break;
		}
		$qry = "select distinct a.*, b.*, c.*, d.company, d.logo, e.department, f.position
		FROM {$this->db->dbprefix}employee_payroll a
		LEFT JOIN {$this->db->dbprefix}user b on a.employee_id = b.employee_id
		LEFT JOIN {$this->db->dbprefix}employee c on c.employee_id = b.employee_id
		LEFT JOIN {$this->db->dbprefix}user_company d on d.company_id = b.company_id
		LEFT JOIN {$this->db->dbprefix}user_company_department e on e.department_id = b.department_id
		LEFT JOIN {$this->db->dbprefix}user_position f on f.position_id = b.position_id
		LEFT JOIN {$this->db->dbprefix}payroll_current_transaction t on t.employee_id = b.employee_id
		WHERE {$where} IN ({$period->apply_to}) AND a.payroll_schedule_id = {$period->payroll_schedule_id}
		AND b.deleted = 0 AND b.inactive = 0 AND a.quitclaim = 0 AND c.employee_id is not null
		AND t.period_id = $period->payroll_period_id ";
		$employees = $this->db->query( $qry );
		
		if( $employees->num_rows() > 0 ){
			$this->load->library('encrypt');

			$progressfile = 'uploads/'.$this->user->user_id . '-' . $this->module.'-'. $period->payroll_period_id.'-progresslog.txt';
			$ctr = 0;
			$total = $employees->num_rows();
			$this->load->library('dom_pdf');
			foreach( $employees->result() as $employee ){
				write_file($progressfile, number_format(($ctr++ / $total) * 100, 2).' ('.$ctr.'/'.$total.' employee/ current:'.$employee->employee_id.')');	

				//get all transctions
				$qry = "SELECT a.*, c.transaction_label
				FROM {$this->db->dbprefix}payroll_current_transaction a
				LEFT JOIN {$this->db->dbprefix}payroll_period b on b.payroll_period_id = a.period_id
				LEFT JOIN {$this->db->dbprefix}payroll_transaction c on c.transaction_id = a.transaction_id
				WHERE a.employee_id = {$employee->employee_id}
				AND b.period_processing_type_id = {$period->period_processing_type_id}";

				$transactions = $this->db->query( $qry );

				if( $transactions->num_rows() > 0 ){
					foreach($transactions->result_array() as $transaction ){
						$on_hold = $transaction['on_hold'] == 0 ? false : true;
						if( !$on_hold ){
							if( !empty( $transaction['record_from'] ) ){
								switch( $transaction['record_from'] ){
									case 'employee_loan';
										$loan = $this->_get_loan_detail( $transaction );
										$transaction['transaction_label'] .= ' - ' . $loan->loan;
										break;	
								}
							}

							switch($transaction['transaction_type_id']){
								case 1:
								case 2:
								case 6:
								case 7:
								case 8:
									$pdata['earning'][] = $transaction;
									break;
								case 3:
								case 4:
								case 5:
									$pdata['deduction'][] = $transaction;
									break;
								default:
									$pdata['netpay'] = $transaction;
									break;
							}

							$this->db->delete('payroll_current_transaction', array('current_transaction_id' => $transaction['current_transaction_id']));
							
							unset( $transaction['current_transaction_id'] );
							unset( $transaction['on_hold'] );
							unset( $transaction['transaction_label'] );
							
							$transaction['actual_payroll_date'] = $period->payroll_date;
							$this->db->insert('payroll_closed_transaction', $transaction);
						}

						//check for loan
						if( $transaction['record_from'] === 'employee_loan' ){
							
							$loan_payment = array(
								'employee_loan_id' => $transaction['record_id'],
								'payroll_date' => $transaction['payroll_date'],
							);

							if(!$on_hold){
								$loan_payment['paid'] = 1;
								$loan_payment['date_paid'] = $transaction['actual_payroll_date'];
							}

							switch( $transaction['inserted_from_id'] ){
								case 4: //amortization
									$loan_payment['type'] = 1;
									break;
								case 5: //interest
									$loan_payment['type'] = 2;
									break;
							}

							$loan_payment['amount'] = $transaction['amount'];

							$checker = $this->db->get_where('employee_loan_payment', array('type' => $loan_payment['type'], 'employee_loan_id' => $transaction['record_id'], 'payroll_date' => $transaction['payroll_date']));
							if( $checker->num_rows() == 1 ){
								$this->db->update('employee_loan_payment', $loan_payment, array('type' => $loan_payment['type'], 'employee_loan_id' => $transaction['record_id'], 'payroll_date' => $transaction['payroll_date']));
							}
							else if($checker->num_rows() == 0){
								$this->db->insert('employee_loan_payment', $loan_payment);
							}

							$loan = $this->db->get_where('employee_loan', array('employee_loan_id' => $transaction['record_id']))->row();

							//update paid amount and arrears
							//Paid
							$qry = "SELECT COUNT(amount) AS payments_maid, SUM(amount) AS total
							FROM {$this->db->dbprefix}employee_loan_payment
							WHERE paid = 1 AND type = 1 AND employee_loan_id ={$transaction['record_id']}";
							$amort_paid = $this->db->query( $qry )->row();

							$qry = "SELECT SUM(amount) AS total
							FROM {$this->db->dbprefix}employee_loan_payment
							WHERE paid = 1 AND type = 2  AND employee_loan_id ={$transaction['record_id']}";
							$interest_paid = $this->db->query( $qry )->row();
						
							//Arrears
							$qry = "SELECT SUM(amount) AS total
							FROM {$this->db->dbprefix}employee_loan_payment
							WHERE paid = 0 AND type = 1  AND employee_loan_id ={$transaction['record_id']}";
							$amort_arrears = $this->db->query( $qry )->row();

							$qry = "SELECT SUM(amount) AS total
							FROM {$this->db->dbprefix}employee_loan_payment
							WHERE paid = 0 AND type = 2 AND employee_loan_id ={$transaction['record_id']}";
							$interest_arrears = $this->db->query( $qry )->row();

							$update_loan = array(
								'total_amount_paid' => $amort_paid->total + $interest_paid->total,
								'no_payments_paid' => $amort_paid->payments_maid,
								'total_arrears' => $amort_arrears->total + $interest_arrears->total,
								'running_balance' => $loan->beginning_balance - ($amort_paid->total + $interest_paid->total),
							);

							if(!$on_hold){
								$update_loan['last_payment_date'] = $transaction['actual_payroll_date'];
								if($loan_payment['type'] == 1) $update_loan['no_payments_remaining'] = $loan->no_payments_remaining - 1;
							}

							if($update_loan['running_balance'] <= 0 || ($update_loan['total_amount_paid'] + $update_loan['total_arrears']) == $loan->beginning_balance){
								$update_loan['loan_status_id'] = 4;
							}

							$this->db->update('employee_loan', $update_loan, array('employee_loan_id' =>  $transaction['record_id']));
						}

						//check for leave conversion
						if( $transaction['record_from'] === 'payroll_leave_conversion' && $transaction['inserted_from_id'] == 7 && $transaction['transaction_code'] == 'LEAVE_CONVERSION' ){
							$lc = $this->db->get_where('payroll_leave_conversion', array('leave_convert_id' => $transaction['record_id']))->row();
							$lc_emp = $this->db->get_where('payroll_leave_conversion_employee', array('leave_convert_id' => $transaction['record_id'], 'employee_id' => $employee->employee_id))->row();
							$lb = $this->db->get_where('employee_leave_balance', array('employee_id' => $employee->employee_id, 'year' => $lc->year))->row_array();
							log_message('error', 'asd');
							switch($lc->application_form_id){
								case 1; //sl
									$lb['sl_used'] += $lc_emp->amount;
									break;
								case 2; //vl
								case 27; //mptl
									$lb['vl_used'] += $lc_emp->amount;
									break;
								case 3; //el
									$lb['vl_used'] += $lc_emp->amount;
									break;
							}
							$this->db->update('employee_leave_balance', $lb, array('employee_id' => $employee->employee_id, 'year' => $lc->year));
						}
					}
				}
				
				$folder = 'uploads/payslip/'. $employee->id_number;
				if(!file_exists( $folder )) mkdir( $folder, 0777, true);
				$filename = $period->payroll_date.'.pdf';
				$file = $folder .'/'. $filename;

				$pdata['employee'] = $employee;
				$pdata['period'] = $period;
				
				//create the payslip
				$html = $this->load->view($this->userinfo['rtheme']. '/' . $this->module_link .'/payslip', $pdata, true);
				$html = preg_replace('/>\s+</', "><", $html);

				$pslip = new Dom_pdf();
				$pslip->set_paper( 'letter', 'portrait' );
				$pslip->load_view('template/pdf', array( 'html' => $html ));
				$pslip->render();
				$pslip->get_canvas()->get_cpdf()->setEncryption($this->encrypt->decode($employee->system_encrypted_password), $this->encrypt->decode($employee->system_encrypted_password), array('print', 'modify', 'copy', 'add'), false);
				$pdf = $pslip->output();
				file_put_contents( $file , $pdf);
				unset($pslip);
				unset($pdata);
			}
			
			unlink($progressfile);
			
		}
		

		$this->db->update('payroll_period', array('period_status_id' => 3), array('payroll_period_id' => $period->payroll_period_id));
		return true;
	}

	/**
	 * [_get_loan_detail description]
	 * @param  [type] $transaction [description]
	 * @return [type]              [description]
	 */
	function _get_loan_detail( $transaction ){
		$emp_loan = $this->db->get_where('employee_loan', array('employee_loan_id' => $transaction['record_id']))->row();
		return $this->db->get_where('payroll_loan', array('loan_id' => $emp_loan->loan_id))->row();
	}

	/**
	 * [get_unit_rate description]
	 * @param  [type] $transaction [description]
	 * @param  [type] $salary      [description]
	 * @return [type]              [description]
	 */
	function get_unit_rate( $transaction, $salary ){
		$unit_rate = '';
		switch( $transaction->transaction_class_code  ){
			case 'SALARY':
				$unit_rate = $salary['period'];
				break;
			case 'OVERTIME':
				$premium = $this->_get_ot_rate( $transaction->transaction_code );
				if( $premium ){
					$unit_rate = round( $premium * ( $salary['daily'] / 8), 2);
				}
				break;
			case 'LEAVES':
				$unit_rate = round( $salary['daily'] / 8, 2);
				break;
			case 'ABSENCES':
				$unit_rate = $salary['daily'];
				break;
			case 'DEDUCTION_LATE':
				$unit_rate = round( $salary['daily'] / 8, 2);
				break;
			case 'DEDUCTION_UNDERTIME':
				$unit_rate = round( $salary['daily'] / 8, 2);
				break;
			case 'ECOLA':
				$unit_rate = $salary['ecola_period'];
				break;
		}

		return $unit_rate;
	}

	/**
	 * [_get_ytd_salary description]
	 * @param  [type]  $employee_id [description]
	 * @param  [type]  $date_from   [description]
	 * @param  [type]  $date_to     [description]
	 * @param  boolean $period      [description]
	 * @return [type]               [description]
	 */
	function _get_ytd_salary( $employee_id, $date_from, $date_to, $include_current = false ){
		$sum = array(0);
		
		//get from closed transactions
		$qry = "select a.*, c.operation
		FROM {$this->db->dbprefix}payroll_closed_transaction a
		LEFT JOIN {$this->db->dbprefix}payroll_transaction b on b.transaction_code = a.transaction_code
		LEFT JOIN {$this->db->dbprefix}payroll_transaction_type c on c.transaction_type_id = b.transaction_type_id
		LEFT JOIN {$this->db->dbprefix}payroll_transaction_class d on d.transaction_class_id = b.transaction_class_id
		WHERE a.employee_id = {$employee_id} AND d.transaction_class_code IN ('SALARY','LEAVES', 'ABSENCES')
		AND a.actual_payroll_date between '{$date_from}' AND '{$date_to}' AND period_id is not null";
		
		$transactions = $this->db->query( $qry );
		if($transactions->num_rows() > 0){
			foreach( $transactions->result() as  $transaction){
				$sum[] = $transaction->operation == '+' ? $transaction->amount : -1 * $transaction->amount;
			}
		}

		if( $include_current ){
			$qry = "select a.*, c.operation
			FROM {$this->db->dbprefix}payroll_current_transaction a
			LEFT JOIN {$this->db->dbprefix}payroll_transaction b on b.transaction_code = a.transaction_code
			LEFT JOIN {$this->db->dbprefix}payroll_transaction_type c on c.transaction_type_id = b.transaction_type_id
			LEFT JOIN {$this->db->dbprefix}payroll_transaction_class d on d.transaction_class_id = b.transaction_class_id
			WHERE a.employee_id = {$employee_id} AND d.transaction_class_code IN ('SALARY','LEAVES', 'ABSENCES')
			AND a.on_hold = 0 AND a.payroll_date between '{$date_from}' AND '{$date_to}'";	

			$transactions = $this->db->query( $qry );
			if($transactions->num_rows() > 0){
				foreach( $transactions->result() as  $transaction){
					$sum[] = $transaction->operation == '+' ? $transaction->amount : -1 * $transaction->amount;
				}
			}
		}

		return array_sum( $sum );
	}

	/**
	 * [_insert_transactions description]
	 * @param  [type] $current_transaction [description]
	 * @param  [type] $period              [description]
	 * @param  [type] $employee            [description]
	 * @param  [type] $salary              [description]
	 * @return [type]                      [description]
	 */
	function _insert_transactions( $current_transaction, $period, $employee, $salary ){
		$base_on_earning = array();
		
		foreach($current_transaction as $trans){
			$baseonearning = $trans['amount'] === 'base_on_earning';
			if($baseonearning){
				$trans['amount'] = 0;
				$trans['unit_rate'] = 0;	
			
				$this->db->insert('payroll_current_transaction', $trans );
			}
			else{
				if( !empty($trans['amount']) ) $this->db->insert('payroll_current_transaction', $trans );
			}
			
			if($baseonearning){
				$base_on_earning[$this->db->insert_id()] = array_shift($this->base_on_earning);
			}
		}

		//upadte transactions base on earnings
		if(sizeof($base_on_earning) > 0){
			$earning = $this->_get_transaction( $employee, $period, '1,5', 'sum', true, true, true );
			foreach( $base_on_earning as $current_transaction_id => $args ){
				switch( true ){
					case isset($args['recurring']):
						$recurring = $args['recurring'];
						$amount = round( $recurring->amount / 100 * $earning, 2);
						$data = array(
							'unit_rate' => $amount, 
							'amount' => $amount
						);
						break;

					case isset($args['govt_code']):

						$earning = $this->_get_transaction( $employee, $period, '1,5', 'sum', true, true, true );

						if($args['govt_code'] == 'hdmf' && CLIENT_DIR == 'firstbalfour'){
							$transaction = $args['transaction'];
							$govt_contribution = $this->_get_monthly_govt_contribution_actual_hdmf($period, $employee,  $transaction, ($salary['period']+$salary['ecola_period']), $args['govt_code']);
							$data = array(
								'unit_rate' => $govt_contribution['eeshare'], 
								'amount' => $govt_contribution['eeshare']
							);
						}
						else{
							$transaction = $args['transaction'];
							$govt_contribution = $this->_get_monthly_govt_contribution_actual($period, $employee,  $transaction, ($earning+$salary['ecola_period']), $args['govt_code']);
							$data = array(
								'unit_rate' => $govt_contribution['eeshare'], 
								'amount' => $govt_contribution['eeshare']
							);
						}
						$employee_contribution = array(
							'payroll_period_id' => $period->payroll_period_id,
							'payroll_date' => $period->payroll_date,
							'employee_id' => $employee->employee_id,
							'company_id' => $employee->company_id,
							'transaction_id' => $transaction->transaction_id,
							'employee' => $govt_contribution['eeshare'],
							'company' => $govt_contribution['ershare'],
							'ec' => $govt_contribution['ec'],
							'paycode_id' => $employee->paycode_id
						);

						$this->db->insert('employee_contribution', $employee_contribution);
						break;
					case isset($args['bonus']):
						$bonus = $args['bonus'];
						$amount = round( $bonus->amount / 100 * $earning, 2);
						$data = array(
							'unit_rate' => $amount, 
							'amount' => $amount
						);
						break;
				}
				$this->db->update('payroll_current_transaction', $data, array('current_transaction_id' => $current_transaction_id));
			}
		}
	}

}