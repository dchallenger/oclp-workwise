<div class="details-div"><!-- style="display:none" -->
    <h3 class="form-head">Benefit Package Details</h3>
    <div class="text-input-wrap">
		<span style="display: inline-block; vertical-align: middle;">
			<a tooltip="Add Benefit" class="icon-16-add icon-button add_row" href="javascript:void(0)">Add Benefit</a>
		</span>
	</div>

<?php
	if ($this->input->post('record_id') != -1):

		$user_benefit = $this->db->get('user_benefit');

		$flag = count($benefits);
		foreach($benefits as $benefit):
	?>
    <table style="width:100%" class="default-table boxtype" id="details-list">
    <tr>
    	<td>
			<label class="label-desc gray" for="update_benefit[]"> Benefits: </label>
			<div class="select-input-wrap">
				<select id="update_benefit[]" class="select" name="update_benefit[]">
					<?php
					foreach ( $user_benefit->result() as $u_ben ):
						if ($u_ben->user_benefit_id == $benefit->user_benefit_id){
					?>
						<option value="<?php echo $u_ben->user_benefit_id ?>" selected="selected"><?php echo $u_ben->user_benefit ?></option>
					<?php
						}
						else{
					?>
						<option value="<?php echo $u_ben->user_benefit_id ?>"><?php echo $u_ben->user_benefit ?></option>
					<?php	
						}
					endforeach;
					?>
				</select>
			</div>
    	</td>
    	<td>
			<label class="label-desc gray" for="update_benefit_description[]"> Description: </label>
			<div class="text-input-wrap">
				<input type="hidden" name="detail_record_id[]" id="detail_record_id[]" value="<?php echo $benefit->recruitment_benefit_package_detail_id ?>" />
				<input id="update_benefit_description[]" class="input-text" type="text" name="update_benefit_description[]" value="<?php echo $benefit->description ?>">
			</div>
    	</td>
    	<td>
    		<label class="label-desc gray" for="update_delete[]"></label>
			<div class="text-input-wrap">
				<?php //if ($flag > 1) { ?>
					<a class="icon-16-delete icon-button update_delete_row" id="<?php echo $benefit->recruitment_benefit_package_detail_id ?>" href="javascript:void(0)" original-title=""></a>
				<?php //} ?>
			</div>
    	</td>
	</tr>
	</table>
    <div class="clear"></div>
    <div class="spacer"></div>

<?php
		endforeach;
	endif;
?>
</div>