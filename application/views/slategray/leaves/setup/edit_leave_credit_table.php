<div class="col-2-form">
	<div class="icon-label add-more-div align-right">
		<a class="icon-16-add icon-16-add-listview add-more" href="javascript:void(0);" onClick="add_leave()">
			<span>Add</span>
		</a>
	</div>
	<div class="spacer"></div>
	<table class="default-table boxtype" style="width: 100%">
		<thead>
			<!-- <tr> -->
				<th class="odd" style="width:15%">
					Succeeding Years
				</th>
				<th class="even">
					Leave
				</th>
			<!-- </tr> -->
		</thead>
	<tbody style="text-align:center" class="leave-table">
		<?= $fields; ?>
	</tbody>
</table>
</div>

<!-- <tr class="leave_row">
			<td>
				<span class="succeeding_year">1 Year</span>
			</td>
			<td>
				<input name="incremented_leave[]" id="incremented_leave[]" type="textbox" class="input-text text-right leave_accumulated" style="width:30%" />
			</td>
		</tr>

<script>
	function add_leave()
	{
		$('.leave-table').append($('.leave_row:last').clone(true));
	}
</script> -->