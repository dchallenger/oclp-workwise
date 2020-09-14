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
			<th>OT ND</th>
			<th>OT ND Excess</th>
		</tr>
	</thead>
	<tbody>
		<?php
			$rates = $this->db->get_where('day_type_and_rates_matrix', array('employment_status_id' => $this->input->post('employment_status_id')));
			if( $rates->num_rows() > 0 ){
				foreach( $rates->result() as $rate): ?>
					<tr class="odd">
						<td rowspan=2 style="vertical-align:middle; font-weight:bold"><?php echo $rate->day_type?></td>
						<td>Rate</td>
						<td align="center"><?php echo $rate->ot?></td>
						<td align="center"><?php echo $rate->ot_excess?></td>
						<td align="center"><?php echo $rate->nd?></td>
						<td align="center"><?php echo $rate->ndot?></td>
						<td align="center"><?php echo $rate->ndot_excess?></td>
					</tr>
					<tr class="even">
						<td>Code</td>
						<td align="center"><?php echo $rate->ot_code?></td>
						<td align="center"><?php echo $rate->ot_excess_code?></td>
						<td align="center"><?php echo $rate->nd_code?></td>
						<td align="center"><?php echo $rate->ndot_code?></td>
						<td align="center"><?php echo $rate->ndot_excess_code?></td>
					</tr> <?php
				endforeach;
			}
			else{ ?>
				<tbody>
					<tr class="odd">
						<td style="vertical-align:middle; font-weight:bold" rowspan="2">Regular</td>
						<td>Rate</td>
						<td align="center"></td>
						<td align="center"></td>
						<td align="center"></td>
						<td align="center"></td>
						<td align="center"></td>
					</tr>
					<tr class="even">
						<td>Code</td>
						<td align="center"></td>
						<td align="center"></td>
						<td align="center"></td>
						<td align="center"></td>
						<td align="center"></td>
					</tr> 
					<tr class="odd">
						<td style="vertical-align:middle; font-weight:bold" rowspan="2">Rest Day</td>
						<td>Rate</td>
						<td align="center"></td>
						<td align="center"></td>
						<td align="center"></td>
						<td align="center"></td>
						<td align="center"></td>
					</tr>
					<tr class="even">
						<td>Code</td>
						<td align="center"></td>
						<td align="center"></td>
						<td align="center"></td>
						<td align="center"></td>
						<td align="center"></td>
					</tr> 				
					<tr class="odd">
						<td style="vertical-align:middle; font-weight:bold" rowspan="2">Legal Holiday</td>
						<td>Rate</td>
						<td align="center"></td>
						<td align="center"></td>
						<td align="center"></td>
						<td align="center"></td>
						<td align="center"></td>
					</tr>
					<tr class="even">
						<td>Code</td>
						<td align="center"></td>
						<td align="center"></td>
						<td align="center"></td>
						<td align="center"></td>
						<td align="center"></td>
					</tr> 				
					<tr class="odd">
						<td style="vertical-align:middle; font-weight:bold" rowspan="2">Special Holiday</td>
						<td>Rate</td>
						<td align="center"></td>
						<td align="center"></td>
						<td align="center"></td>
						<td align="center"></td>
						<td align="center"></td>
					</tr>
					<tr class="even">
						<td>Code</td>
						<td align="center"></td>
						<td align="center"></td>
						<td align="center"></td>
						<td align="center"></td>
						<td align="center"></td>
					</tr> 
					<tr class="odd">
						<td style="vertical-align:middle; font-weight:bold" rowspan="2">Legal Holiday Rest Day</td>
						<td>Rate</td>
						<td align="center"></td>
						<td align="center"></td>
						<td align="center"></td>
						<td align="center"></td>
						<td align="center"></td>
					</tr>
					<tr class="even">
						<td>Code</td>
						<td align="center"></td>
						<td align="center"></td>
						<td align="center"></td>
						<td align="center"></td>
						<td align="center"></td>
					</tr>
					<tr class="odd">
						<td style="vertical-align:middle; font-weight:bold" rowspan="2">Special Holiday Rest Day</td>
						<td>Rate</td>
						<td align="center"></td>
						<td align="center"></td>
						<td align="center"></td>
						<td align="center"></td>
						<td align="center"></td>
					</tr>
					<tr class="even">
						<td>Code</td>
						<td align="center"></td>
						<td align="center"></td>
						<td align="center"></td>
						<td align="center"></td>
						<td align="center"></td>
					</tr>
					<tr class="odd">
						<td style="vertical-align:middle; font-weight:bold" rowspan="2">Double Holiday</td>
						<td>Rate</td>
						<td align="center"></td>
						<td align="center"></td>
						<td align="center"></td>
						<td align="center"></td>
						<td align="center"></td>
					</tr>
					<tr class="even">
						<td>Code</td>
						<td align="center"></td>
						<td align="center"></td>
						<td align="center"></td>
						<td align="center"></td>
						<td align="center"></td>
					</tr> 				<tr class="odd">
						<td style="vertical-align:middle; font-weight:bold" rowspan="2">Double Holiday Rest Day</td>
						<td>Rate</td>
						<td align="center"></td>
						<td align="center"></td>
						<td align="center"></td>
						<td align="center"></td>
						<td align="center"></td>
					</tr>
					<tr class="even">
						<td>Code</td>
						<td align="center"></td>
						<td align="center"></td>
						<td align="center"></td>
						<td align="center"></td>
						<td align="center"></td>
					</tr>
				</tbody> <?php
			}
		?>
	</tbody>	
</table>