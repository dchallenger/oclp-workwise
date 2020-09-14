<?php //$this->load->view($this->module_link.'/save-button')?>
<h4>Employment Status: <?php echo $this->employment_status->employment_status?></h4>
<form id="record-form">
	<input type="hidden" name="employment_status_id" value="<?php echo $this->input->post('record_id')?>">
	<table id="" class="default-table boxtype" style="width:100%">
		<colgroup width="18.5%"></colgroup>
		<colgroup width="7%"></colgroup>
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
				$rates = $this->db->get_where('day_type_and_rates_matrix', array('employment_status_id' => $this->input->post('record_id')));
				if( $rates->num_rows() > 0 ){
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
								<input type="text" name="ndot_code[<?php echo $rate->day_prefix?>]" value="<?php echo $rate->ndot_code?>" style="width:96%; text-align: right;">
							</td>
							<td align="center">
								<input type="text" name="ndot_excess_code[<?php echo $rate->day_prefix?>]" value="<?php echo $rate->ndot_excess_code?>" style="width:96%; text-align: right;">
							</td>
						</tr> <?php
					endforeach;
				}
				else{ ?>
					<tbody>
						<tr class="odd">
							<td style="vertical-align:middle; font-weight:bold" rowspan="2">Regular</td>
							<td>Rate</td>
							<td align="center">
								<input type="text" style="width:96%; text-align: right;" value="" name="ot[reg]">
							</td>
							<td align="center">
								<input type="text" style="width:96%; text-align: right;" value="" name="ot_excess[reg]">
							</td>
							<td align="center">
								<input type="text" style="width:96%; text-align: right;" value="" name="nd[reg]">
							</td>
							<td align="center">
								<input type="text" style="width:96%; text-align: right;" value="" name="ndot[reg]">
							</td>
							<td align="center">
								<input type="text" style="width:96%; text-align: right;" value="" name="ndot_excess[reg]">
							</td>
						</tr>
						<tr class="even">
							<td>Code</td>
							<td align="center">
								<input type="text" style="width:96%; text-align: right;" value="" name="ot_code[reg]">
							</td>
							<td align="center">
								<input type="text" style="width:96%; text-align: right;" value="" name="ot_excess_code[reg]">
							</td>
							<td align="center">
								<input type="text" style="width:96%; text-align: right;" value="" name="nd_code[reg]">
							</td>
							<td align="center">
								<input type="text" style="width:96%; text-align: right;" value="" name="ndot_code[reg]">
							</td>
							<td align="center">
								<input type="text" style="width:96%; text-align: right;" value="" name="ndot_excess_code[reg]">
							</td>
						</tr> 						<tr class="odd">
							<td style="vertical-align:middle; font-weight:bold" rowspan="2">Rest Day</td>
							<td>Rate</td>
							<td align="center">
								<input type="text" style="width:96%; text-align: right;" value="" name="ot[rd]">
							</td>
							<td align="center">
								<input type="text" style="width:96%; text-align: right;" value="" name="ot_excess[rd]">
							</td>
							<td align="center">
								<input type="text" style="width:96%; text-align: right;" value="" name="nd[rd]">
							</td>
							<td align="center">
								<input type="text" style="width:96%; text-align: right;" value="" name="ndot[rd]">
							</td>
							<td align="center">
								<input type="text" style="width:96%; text-align: right;" value="" name="ndot_excess[rd]">
							</td>
						</tr>
						<tr class="even">
							<td>Code</td>
							<td align="center">
								<input type="text" style="width:96%; text-align: right;" value="" name="ot_code[rd]">
							</td>
							<td align="center">
								<input type="text" style="width:96%; text-align: right;" value="" name="ot_excess_code[rd]">
							</td>
							<td align="center">
								<input type="text" style="width:96%; text-align: right;" value="" name="nd_code[rd]">
							</td>
							<td align="center">
								<input type="text" style="width:96%; text-align: right;" value="" name="ndot_code[rd]">
							</td>
							<td align="center">
								<input type="text" style="width:96%; text-align: right;" value="" name="ndot_excess_code[rd]">
							</td>
						</tr> 						
						<tr class="odd">
							<td style="vertical-align:middle; font-weight:bold" rowspan="2">Legal Holiday</td>
							<td>Rate</td>
							<td align="center">
								<input type="text" style="width:96%; text-align: right;" value="" name="ot[leg]">
							</td>
							<td align="center">
								<input type="text" style="width:96%; text-align: right;" value="" name="ot_excess[leg]">
							</td>
							<td align="center">
								<input type="text" style="width:96%; text-align: right;" value="" name="nd[leg]">
							</td>
							<td align="center">
								<input type="text" style="width:96%; text-align: right;" value="" name="ndot[leg]">
							</td>
							<td align="center">
								<input type="text" style="width:96%; text-align: right;" value="" name="ndot_excess[leg]">
							</td>
						</tr>
						<tr class="even">
							<td>Code</td>
							<td align="center">
								<input type="text" style="width:96%; text-align: right;" value="" name="ot_code[leg]">
							</td>
							<td align="center">
								<input type="text" style="width:96%; text-align: right;" value="" name="ot_excess_code[leg]">
							</td>
							<td align="center">
								<input type="text" style="width:96%; text-align: right;" value="" name="nd_code[leg]">
							</td>
							<td align="center">
								<input type="text" style="width:96%; text-align: right;" value="" name="ndot_code[leg]">
							</td>
							<td align="center">
								<input type="text" style="width:96%; text-align: right;" value="" name="ndot_excess_code[leg]">
							</td>
						</tr> 					
						<tr class="odd">
							<td style="vertical-align:middle; font-weight:bold" rowspan="2">Special Holiday</td>
							<td>Rate</td>
							<td align="center">
								<input type="text" style="width:96%; text-align: right;" value="" name="ot[spe]">
							</td>
							<td align="center">
								<input type="text" style="width:96%; text-align: right;" value="" name="ot_excess[spe]">
							</td>
							<td align="center">
								<input type="text" style="width:96%; text-align: right;" value="" name="nd[spe]">
							</td>
							<td align="center">
								<input type="text" style="width:96%; text-align: right;" value="" name="ndot[spe]">
							</td>
							<td align="center">
								<input type="text" style="width:96%; text-align: right;" value="" name="ndot_excess[spe]">
							</td>
						</tr>
						<tr class="even">
							<td>Code</td>
							<td align="center">
								<input type="text" style="width:96%; text-align: right;" value="" name="ot_code[spe]">
							</td>
							<td align="center">
								<input type="text" style="width:96%; text-align: right;" value="" name="ot_excess_code[spe]">
							</td>
							<td align="center">
								<input type="text" style="width:96%; text-align: right;" value="" name="nd_code[spe]">
							</td>
							<td align="center">
								<input type="text" style="width:96%; text-align: right;" value="" name="ndot_code[spe]">
							</td>
							<td align="center">
								<input type="text" style="width:96%; text-align: right;" value="" name="ndot_excess_code[spe]">
							</td>
						</tr> 				
						<tr class="odd">
							<td style="vertical-align:middle; font-weight:bold" rowspan="2">Legal Holiday Rest Day</td>
							<td>Rate</td>
							<td align="center">
								<input type="text" style="width:96%; text-align: right;" value="" name="ot[legrd]">
							</td>
							<td align="center">
								<input type="text" style="width:96%; text-align: right;" value="" name="ot_excess[legrd]">
							</td>
							<td align="center">
								<input type="text" style="width:96%; text-align: right;" value="" name="nd[legrd]">
							</td>
							<td align="center">
								<input type="text" style="width:96%; text-align: right;" value="" name="ndot[legrd]">
							</td>
							<td align="center">
								<input type="text" style="width:96%; text-align: right;" value="" name="ndot_excess[legrd]">
							</td>
						</tr>
						<tr class="even">
							<td>Code</td>
							<td align="center">
								<input type="text" style="width:96%; text-align: right;" value="" name="ot_code[legrd]">
							</td>
							<td align="center">
								<input type="text" style="width:96%; text-align: right;" value="" name="ot_excess_code[legrd]">
							</td>
							<td align="center">
								<input type="text" style="width:96%; text-align: right;" value="" name="nd_code[legrd]">
							</td>
							<td align="center">
								<input type="text" style="width:96%; text-align: right;" value="" name="ndot_code[legrd]">
							</td>
							<td align="center">
								<input type="text" style="width:96%; text-align: right;" value="" name="ndot_excess_code[legrd]">
							</td>
						</tr> 				
						<tr class="odd">
							<td style="vertical-align:middle; font-weight:bold" rowspan="2">Special Holiday Rest Day</td>
							<td>Rate</td>
							<td align="center">
								<input type="text" style="width:96%; text-align: right;" value="" name="ot[sperd]">
							</td>
							<td align="center">
								<input type="text" style="width:96%; text-align: right;" value="" name="ot_excess[sperd]">
							</td>
							<td align="center">
								<input type="text" style="width:96%; text-align: right;" value="" name="nd[sperd]">
							</td>
							<td align="center">
								<input type="text" style="width:96%; text-align: right;" value="" name="ndot[sperd]">
							</td>
							<td align="center">
								<input type="text" style="width:96%; text-align: right;" value="" name="ndot_excess[sperd]">
							</td>
						</tr>
						<tr class="even">
							<td>Code</td>
							<td align="center">
								<input type="text" style="width:96%; text-align: right;" value="" name="ot_code[sperd]">
							</td>
							<td align="center">
								<input type="text" style="width:96%; text-align: right;" value="" name="ot_excess_code[sperd]">
							</td>
							<td align="center">
								<input type="text" style="width:96%; text-align: right;" value="" name="nd_code[sperd]">
							</td>
							<td align="center">
								<input type="text" style="width:96%; text-align: right;" value="" name="ndot_code[sperd]">
							</td>
							<td align="center">
								<input type="text" style="width:96%; text-align: right;" value="" name="ndot_excess_code[sperd]">
							</td>
						</tr> 				
						<tr class="odd">
							<td style="vertical-align:middle; font-weight:bold" rowspan="2">Double Holiday</td>
							<td>Rate</td>
							<td align="center">
								<input type="text" style="width:96%; text-align: right;" value="" name="ot[dob]">
							</td>
							<td align="center">
								<input type="text" style="width:96%; text-align: right;" value="" name="ot_excess[dob]">
							</td>
							<td align="center">
								<input type="text" style="width:96%; text-align: right;" value="" name="nd[dob]">
							</td>
							<td align="center">
								<input type="text" style="width:96%; text-align: right;" value="" name="ndot[dob]">
							</td>
							<td align="center">
								<input type="text" style="width:96%; text-align: right;" value="" name="ndot_excess[dob]">
							</td>
						</tr>
						<tr class="even">
							<td>Code</td>
							<td align="center">
								<input type="text" style="width:96%; text-align: right;" value="" name="ot_code[dob]">
							</td>
							<td align="center">
								<input type="text" style="width:96%; text-align: right;" value="" name="ot_excess_code[dob]">
							</td>
							<td align="center">
								<input type="text" style="width:96%; text-align: right;" value="" name="nd_code[dob]">
							</td>
							<td align="center">
								<input type="text" style="width:96%; text-align: right;" value="" name="ndot_code[dob]">
							</td>
							<td align="center">
								<input type="text" style="width:96%; text-align: right;" value="" name="ndot_excess_code[dob]">
							</td>
						</tr> 					
						<tr class="odd">
							<td style="vertical-align:middle; font-weight:bold" rowspan="2">Double Holiday Rest Day</td>
							<td>Rate</td>
							<td align="center">
								<input type="text" style="width:96%; text-align: right;" value="" name="ot[dobrd]">
							</td>
							<td align="center">
								<input type="text" style="width:96%; text-align: right;" value="" name="ot_excess[dobrd]">
							</td>
							<td align="center">
								<input type="text" style="width:96%; text-align: right;" value="" name="nd[dobrd]">
							</td>
							<td align="center">
								<input type="text" style="width:96%; text-align: right;" value="" name="ndot[dobrd]">
							</td>
							<td align="center">
								<input type="text" style="width:96%; text-align: right;" value="" name="ndot_excess[dobrd]">
							</td>
						</tr>
						<tr class="even">
							<td>Code</td>
							<td align="center">
								<input type="text" style="width:96%; text-align: right;" value="" name="ot_code[dobrd]">
							</td>
							<td align="center">
								<input type="text" style="width:96%; text-align: right;" value="" name="ot_excess_code[dobrd]">
							</td>
							<td align="center">
								<input type="text" style="width:96%; text-align: right;" value="" name="nd_code[dobrd]">
							</td>
							<td align="center">
								<input type="text" style="width:96%; text-align: right;" value="" name="ndot_code[dobrd]">
							</td>
							<td align="center">
								<input type="text" style="width:96%; text-align: right;" value="" name="ndot_excess_code[dobrd]">
							</td>
						</tr>
					</tbody><?php
				}
			?>
		</tbody>	
	</table>
</form>
<?php $this->load->view($this->module_link.'/save-button')?>