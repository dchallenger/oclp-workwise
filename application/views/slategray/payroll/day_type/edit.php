<?php $this->load->view($this->module_link.'/save-button')?>
<form id="record-form">
	<table id="" class="default-table boxtype" style="width:100%">
		<colgroup width="18.5%"></colgroup>
		<colgroup width="7%"></colgroup>
		<colgroup width="13%"></colgroup>
		<colgroup width="13%"></colgroup>
		<colgroup width="13%"></colgroup>
		<colgroup width="13%"></colgroup>
		<colgroup width="13%"></colgroup>
		<colgroup width="13%"></colgroup>
		<thead>
			<tr>
				<th colspan=2 row=span>Day Type</th>
				<th>OT</th>
				<th>OT Excess</th>
				<th>ND</th>
				<th>ND Excess</th>
				<th>OT ND</th>
				<th>OT ND Excess</th>
			</tr>
		</thead>
		<tbody>
			<?php
				$rates = $this->db->get('day_type_and_rates');
				foreach( $rates->result() as $rate): ?>
					<tr class="odd">
						<td rowspan=2 style="vertical-align:middle; font-weight:bold"><?php echo $rate->day_type?></td>
						<td>Rate</td>
						<td align="center">
							<input type="text" name="ot[<?php echo $rate->day_prefix?>]" value="<?php echo $rate->ot?>" style="width:96%; text-align: right;">
						</td>
						<td align="center">
							<input type="text" name="ot_excess[<?php echo $rate->day_prefix?>]" value="<?php echo $rate->ot_excess?>" style="width:96%; text-align: right;">
						</td>
						<td align="center">
							<input type="text" name="nd[<?php echo $rate->day_prefix?>]" value="<?php echo $rate->nd?>" style="width:96%; text-align: right;">
						</td>
						<td align="center">
							<input type="text" name="nd_excess[<?php echo $rate->day_prefix?>]" value="<?php echo $rate->nd_excess?>" style="width:96%; text-align: right;">
						</td>
						<td align="center">
							<input type="text" name="ndot[<?php echo $rate->day_prefix?>]" value="<?php echo $rate->ndot?>" style="width:96%; text-align: right;">
						</td>
						<td align="center">
							<input type="text" name="ndot_excess[<?php echo $rate->day_prefix?>]" value="<?php echo $rate->ndot_excess?>" style="width:96%; text-align: right;">
						</td>
					</tr>
					<tr class="even">
						<td>Code</td>
						<td align="center">
							<input type="text" name="ot_code[<?php echo $rate->day_prefix?>]" value="<?php echo $rate->ot_code?>" style="width:96%; text-align: right;">
						</td>
						<td align="center">
							<input type="text" name="ot_excess_code[<?php echo $rate->day_prefix?>]" value="<?php echo $rate->ot_excess_code?>" style="width:96%; text-align: right;">
						</td>
						<td align="center">
							<input type="text" name="nd_code[<?php echo $rate->day_prefix?>]" value="<?php echo $rate->nd_code?>" style="width:96%; text-align: right;">
						</td>
						<td align="center">
							<input type="text" name="nd_excess_code[<?php echo $rate->day_prefix?>]" value="<?php echo $rate->nd_excess_code?>" style="width:96%; text-align: right;">
						</td>
						<td align="center">
							<input type="text" name="ndot_code[<?php echo $rate->day_prefix?>]" value="<?php echo $rate->ndot_code?>" style="width:96%; text-align: right;">
						</td>
						<td align="center">
							<input type="text" name="ndot_excesscode[<?php echo $rate->day_prefix?>]" value="<?php echo $rate->ndot_excess_code?>" style="width:96%; text-align: right;">
						</td>
					</tr> <?php
				endforeach;
			?>
		</tbody>	
	</table>
</form>
<?php $this->load->view($this->module_link.'/save-button')?>