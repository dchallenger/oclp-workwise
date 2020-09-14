<div class="col-2-form">
	<table class="default-table boxtype" style="width: 100%">
		<thead>
			<th class="odd" style="width:15%">
				Succeeding Years
			</th>
			<th class="even">
				Leave
			</th>
		</thead>
	<tbody style="text-align:center" class="leave-table">
	<?php
	    if (count($values) > 0):
	        foreach ($values as $data):
	            ?>
	        	<tr>
					<td>
						<span><?= ($data['tenure'] == 1 ? $data['tenure'].' Year' : $data['tenure'].' Years'); ?></span>
					</td>
					<td>
						<span><?= $data['leave_accumulated']; ?></span>
					</td>
				</tr>
	        <?php endforeach; ?>
	<?php endif; ?>
	</tbody>
</table>
</div>