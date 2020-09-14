<div style="clear:both">&nbsp;</div>
<h3>Manpower Served (For HR Used Only)</h3>
<table id="listview-list" class="default-table boxtype" style="width:100%">
	<thead>
		<tr>
			<td>Name(s)</td>
			<td>Date Hired</td>
			<td>Source of Awareness</td>
			<td>Salary</td>
		</tr>
	</thead>	
	<tbody>
		<?php
			if ($manpower_served && $manpower_served->num_rows() > 0){
				foreach ($manpower_served->result() as $row) {
		?>
					<tr>
						<td><?php echo $row->firstname ?>&nbsp;<?php echo $row->lastname ?></td>
						<td><?php echo $row->hired_date ?></td>
						<td><?php echo $row->referred_by ?></td>
						<td><?php echo number_format($starting_salary,2, '.', ',') ?></td>
					</tr>		
		<?php
				}
			}
		?>
	</tbody>
</table>