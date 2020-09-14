<div class="spacer"></div>
<table class="default-table" style="width: 100%">
	<thead>
		<tr><th colspan="2">Supervisor or Manager: Please confirm turnover of the following:</th></tr>
	</thead>
	<tbody>
		<?php foreach ($checklist as $c):?>
		<tr>
			<td><?=$c['name']?>:</td>
			<td>
				<p><?=$c['description']?></p>
				Comment/Status:<br />
				<?=$status_data[$c['employee_clearance_form_checklist_id']]?>
			</td>
		<?php endforeach;?>
	</tbody>
</table>
<script type="text/javascript">
	$('.default-table tbody tr:even').addClass('even');
	$('.default-table tbody tr:odd').addClass('odd');	
</script>