
<?php 
$check = json_decode($raw_data['fourth_month'], true);

$checklist = $column_fields['onboarding']['fourth_month'];

	foreach ($checklist as $key => $value):?>

		<div class="col-2-form">

			<div class="form-item odd " >
				<input type="checkbox" value="<?=$key?>" name="fourth_month[]" <?=(in_array($key, $check)) ? "checked='checked'" : ' ' ;?>>
				<span><?=$value->description?></span>
			</div>
			<div class="form-item even ">
				<div class="text-input-wrap">
					<?=$value->person_responsible?>
				</div>
			</div>
			<?php //endforeach;?>
		</div>

<?php endforeach;?>