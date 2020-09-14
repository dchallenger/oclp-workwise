
<?php

if($values)
{
	foreach($values as $value)
	{
?>
	<tr class="leave_row">
		<td class="td_parent">
			<a class="decrease_year hidden">« </a>
				<span class="succeeding_year"><?= ($value['tenure'] == 1 ? $value['tenure'].' Year' : $value['tenure'].' Years'); ?></span>
				<input type="textbox" name="incremented_leave[tenure][]" id="tenure_leave" style="display:none;" value="<?= $value['tenure']; ?>" />
			<a class="increase_year hidden"> »</a>
		</td>
		<td>
			<input name="incremented_leave[leave_accumulated][]" type="textbox" class="input-text text-right leave_accumulated" style="width:30%"  value="<?= $value['leave_accumulated']; ?>"/>
			<a class="icon-button icon-16-delete align-right" href="javascript:void(0)"></a>
		</td>
	</tr>
<?php
	}
} else {
?>
	<tr class="leave_row">
		<td class="td_parent">
			<a class="decrease_year hidden">« </a>
				<span class="succeeding_year">1 Year</span>
				<input type="textbox" name="incremented_leave[tenure][]" id="tenure_leave" style="display:none;" value="1" />
			<a class="increase_year hidden"> »</a>
		</td>
		<td>
			<input name="incremented_leave[leave_accumulated][]" type="textbox" class="input-text text-right leave_accumulated" style="width:30%" />
			<a class="icon-button icon-16-delete align-right" href="javascript:void(0)"></a>
		</td>
	</tr>
<?php
}
?>