<table id="listview-list" class="default-table boxtype" style="width:100%">
    <colgroup>
        <col width="20%">
        <col width="40%">
        <col width="20%">
        <col width="20%">
    </colgroup>
    <thead>
        <tr>
            <th rowspan="2">Transaction Code</th>
            <th rowspan="2">Transaction Label</th>
            <th colspan="2">Acccounts</th>
        </tr>
        <tr>
            <th>Credit</th>
            <th>Debit</th>
        </tr>
    </thead><?php
    $record_id = $this->input->post('record_id');
    if( $record_id != '-1' ){
        $accnts = $this->db->get_where('department_account_mapping', array('department_id' => $record_id ));
        if( $accnts->num_rows() > 0 ){
            foreach( $accnts->result() as $accnt ){
                $mapping[$accnt->transaction_id] = array(
                    'transaction_label_override' => $accnt->transaction_label_override,
                    'credit_account_id' => $accnt->credit_account_id,
                    'debit_account_id' => $accnt->debit_account_id
                );
            }
        }
    }
    $option[''] = '';
    $accounts = $this->db->get_where('payroll_account', array('deleted' => 0));
    if( $accounts->num_rows() > 0 ){
        foreach( $accounts->result() as  $row ){
            $option[$row->account_id] = $row->account_code;
        }
    }

    $qry = "SELECT a.*
    FROM {$this->db->dbprefix}payroll_transaction a
    LEFT JOIN {$this->db->dbprefix}payroll_transaction_class b on b.transaction_class_id = a.transaction_class_id
    WHERE a.deleted = 0
    ORDER BY b.transaction_class_id";
    $transactions = $this->db->query( $qry );
    if( $transactions->num_rows() ){
        $ctr = 0;
        foreach($transactions->result() as $transaction){ 
            $class= "odd";
            if($ctr % 2 == 0) $class= "even";
            $ctr++;?>
            <tr class="<?php echo $class?>">
                <td>
                    <input type="hidden" value="<?php echo $transaction->transaction_id?>" name="transaction_id[<?php echo $transaction->transaction_id?>]">
                    <?php echo $transaction->transaction_code?>
                </td>
                <td><?php
                    $value = $transaction->transaction_label;
                    if(isset($mapping[$transaction->transaction_id]) && !empty($mapping[$transaction->transaction_id]['transaction_label_override'])) $value = $mapping[$transaction->transaction_id]['transaction_label_override'];
                    ?>
                    <input type="text" class="input-text" value="<?php echo $value?>" name="transaction_label_override[<?php echo $transaction->transaction_id?>]">
                </td>
                <td> <?php 
                    $value = $transaction->credit_account_id;
                    if(isset($mapping[$transaction->transaction_id])) $value = $mapping[$transaction->transaction_id]['credit_account_id'];
                    echo form_dropdown('credit_account_id['.$transaction->transaction_id.']', $option, $value, 'class="accnt-ddlb" style="width:180px;"'); ?>
                </td>
                <td> <?php 
                    $value = $transaction->debit_account_id;
                    if(isset($mapping[$transaction->transaction_id])) $value = $mapping[$transaction->transaction_id]['debit_account_id'];
                    echo form_dropdown('debit_account_id['.$transaction->transaction_id.']', $option, $value, 'class="accnt-ddlb" style="width:180px;"'); ?>
                </td>
            </tr>
        <?php
        }
    }
    ?>
 </table>

<script>
	$(document).ready(function(){
		$('#btn-panel').trigger('click');
        $('.accnt-ddlb').each(function(){
			$(this).chosen();
		})
	});
</script>