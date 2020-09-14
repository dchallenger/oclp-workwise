<?php 

$check = json_decode($raw_data['regularization'], true);
$checklist = $column_fields['onboarding']['regularization'];

	foreach ($checklist as $key => $value):?>

		<div class="col-2-form">

			<div class="form-item odd " >
				<input type="checkbox" value="<?=$key?>" name="regularization[]" <?=(in_array($key, $check)) ? "checked='checked'" : ' ' ;?>>
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