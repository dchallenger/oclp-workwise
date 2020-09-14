<table id="listview-list" class="default-table boxtype" style="width:100%">
        <colgroup>
          <col width="30%">
          <col width="20%">
          <col width="20%">
          <col width="20%">
          <col width="10%">
        </colgroup>
        <thead>
            <tr>
                <th>Transaction</th>
                <th>Code</th>
                <th>Mode</th>
                <th>Amount</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody id="listview-tbody"> <?php
        	foreach( $csts as $ctr => $cst ):
						$class = $ctr % 2 == 0 ? "even" : "odd"; ?>
						<tr class="<?php echo $class?>">
							<td><?php echo $cst['transaction_label']?></td>
              <td align="center"><?php echo $cst['transaction_code']?></td>
              <td align="center"><?php echo $cst['payroll_transaction_mode']?></td>
              <td align="right"><?php if( $cst['amount'] <> 0 ) echo $cst['amount'];?></td>
              <td align="center">
              	<div class="icon-group nowrap"><a onclick="edit_cst( <?php echo $cst['cst_id']?> )" class="icon-button icon-16-edit" href="javascript:void(0)" tooltip="Edit"></a><a onclick="delete_cst( <?php echo $cst['cst_id']?> )" class="icon-button icon-16-delete" href="javascript:void(0)" tooltip="Delete"></a></div>
              </td>
						</tr> <?php
					endforeach; ?>
        </tbody>
    </table>