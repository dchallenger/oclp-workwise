
<?php 
	$forms['company'] = json_decode($raw_data['company_forms'], true);
	$forms['documents'] = json_decode($raw_data['document_forms'], true);
	$forms['government'] = json_decode($raw_data['government_forms'], true);


$checklist = $column_fields['checklist'];

	foreach ($checklist as $key => $field_val):?>
	<div class="" id="fg-<?=ucfirst($key)?>" fg_id="<?=ucfirst($key)?>" style="margin-left:2%">
	<h5 class="form-head"><?=ucfirst($key)?> Forms
		<a style="font-size: 12px;line-height: 18px;" onclick="toggleFieldGroupVisibility( $( this ) );" class="align-right other-link noborder" href="javascript:void(0)">Hide</a>
	</h5>
		<div class="col-2-form">
			<?php foreach ($checklist[$key] as $val => $value):
			$check = $forms[$key]['check_box'];
			// dbug($val.'='.$check);
			?>

			<div class="form-item odd " style="border-bottom: 1px dotted rgb(204, 204, 204); padding-bottom: 10px;">
				<input type="checkbox" value="<?=$val?>" name="<?=$key?>[check_box][]" <?=(in_array($val, $check)) ? "checked='checked'" : ' ' ;?>>
				<span><?=$value->description?></span>
			</div>
			<div class="form-item even ">
				<div class="text-input-wrap">
					<input type="text" class="input-text" value="<?=$forms[$key]['remarks'][$val]?>"  name="<?=$key?>[remarks][]">
				</div>
			</div>
			<?php endforeach;?>
		</div>
	</div>
<?php endforeach;?>