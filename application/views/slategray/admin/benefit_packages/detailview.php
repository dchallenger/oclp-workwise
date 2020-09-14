<div class="details-div"><!-- style="display:none" -->
    <h3 class="form-head">Benefit Package Details</h3>
    <table style="width:100%" class="default-table boxtype" id="details-list">
<?php
	foreach($benefits as $benefit):
?>
    <tr>
    	<td>
			<label class="label-desc gray" for="update_benefit[]"> Benefits: </label>
			<div class="select-input-wrap">
<?php 
			echo $this->db->get_where('user_benefit', array('user_benefit_id' => $benefit->user_benefit_id))->row()->user_benefit
?>
			</div>
    	</td>
    	<td>
			<label class="label-desc gray" for="update_benefit_description[]"> Description: </label>
			<div class="text-input-wrap">
				<?php echo $benefit->description; ?>
			</div>
    	</td>
	</tr>

<?php
	endforeach;
?>
	</table>
    <div class="clear"></div>
    <div class="spacer"></div>
</div>