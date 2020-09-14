<tr class="employee-row" id="employee-<?php echo $user->employee_id?>">
	<td><?php echo $user->lastname?>, <?php echo $user->firstname?></td>
	<td align="right">
		<?php
			switch( $form->application_code ){
				case 'SL':
					echo $balance->sl - $balance->sl_used;
					break;
				case 'VL':
					echo $balance->vl - $balance->vl_used;
					break;
				case 'MTPL':
					echo $balance->vl - $balance->sl_used -  $balance->vl_used;
					break;
			}
		?>
	</td>
	<td align="right"><input type="text" name="amount[<?php echo $user->employee_id?>]" value="" class="text-right"></td>
	<td align="center">
		<span class="icon-group"><a href="javascript:delete_employee_row(<?php echo $user->employee_id?>)" tooltip="Delete" class="icon-button icon-16-delete"></a></span>
	</td>
</tr>