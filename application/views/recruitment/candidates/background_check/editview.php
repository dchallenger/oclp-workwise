<input type="hidden" name="candidate_id" value="<?=$candidate_id?>" id="candidate_id">
<input type="hidden" value="<?=$record_id?>" id="bc_record_id">

<?php if ($record_id != -1):?>
<input type="hidden" value="<?=$reference_ids?>" id="reference_ids">
<?php endif;?>
<div id="reference-info-div"></div> 