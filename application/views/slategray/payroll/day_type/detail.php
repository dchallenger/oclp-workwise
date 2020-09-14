<?php $this->load->view($this->module_link.'/edit-button')?>
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
					<td align="center"><?php echo $rate->ot?></td>
					<td align="center"><?php echo $rate->ot_excess?></td>
					<td align="center"><?php echo $rate->nd?></td>
					<td align="center"><?php echo $rate->nd_excess?></td>
					<td align="center"><?php echo $rate->ndot?></td>
					<td align="center"><?php echo $rate->ndot_excess?></td>
				</tr>
				<tr class="even">
					<td>Code</td>
					<td align="center"><?php echo $rate->ot_code?></td>
					<td align="center"><?php echo $rate->ot_excess_code?></td>
					<td align="center"><?php echo $rate->nd_code?></td>
					<td align="center"><?php echo $rate->nd_excess_code?></td>
					<td align="center"><?php echo $rate->ndot_code?></td>
					<td align="center"><?php echo $rate->ndot_excess_code?></td>
				</tr> <?php
			endforeach;
		?>
	</tbody>	
</table>
<?php $this->load->view($this->module_link.'/edit-button')?>