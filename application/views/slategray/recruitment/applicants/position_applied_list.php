<div style="width:85%; min-width:400px;">
	<strong><?php echo $applicant_name; ?></strong>
</div>
<br />
<div>
	<table style="width:85%; min-width:400px;">
		<thead>
			<tr>
				<td><strong>Position Applied</strong></td>
				<td><strong>Applied Date</strong></td>
				<td><strong>Status</strong></td>
			</tr>
		</thead>
		<tbody>
			<?php foreach( $position_list as $position_record ){ ?>
				<tr>
					<td><?php echo $position_record['position']; ?></td>
					<td><?php echo date($this->config->item('display_date_format'),strtotime($position_record['applied_date'])); ?></td>
					<td><?php echo $position_record['application_status']; ?></td>
				</tr>
			<?php } ?>
		</tbody>
	</table>
</div>