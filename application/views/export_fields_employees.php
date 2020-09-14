<?php $ci =& get_instance();?>

<form id="export-form" method="post" action="<?=site_url('admin/export_query/export')?>">
	<input type="hidden" name="<?=$ci->key_field?>" value="<?=${$ci->key_field}?>"/>	
	<input type="hidden" name="criteria" value="<?=$description?>"/>
	<input type="hidden" name="export_query_id" value="<?=$export_query_id?>"/>
	<div id="form-div">
		<h3 class="form-head">Export</h3>
		<div class="col-1-form">
			<div class="form-item view odd">
				<label class="label-desc view gray" for="machine_operated">Description:</label>
				<div class="text-input-wrap"><?=$description?></div>
			</div>		
			<div class="form-item odd">
				<label class="label-desc gray" for="fields">Fields: </label>
				<div class="multiselect-input-wrap">
					<?=form_dropdown('fields[]', $fields, '', 'multiple="multiple" id="multiselect-fields"')?>
				</div>
			</div>
			<div class="form-item odd">
				<label class="label-desc gray" for="fields">Type: </label>
				<div class="select-input-wrap">
					<select name="export_type">
						<option value="excel">Excel</option>
						<option value="pdf">PDF</option>
						<option value="html">HTML</option>
					</select>
				</div>
			</div>			
		</div>
	</div>
</form>
<script type="text/javascript">
	$('select[name="fields[]"] option').attr('selected', 'selected');
</script>
<!-- <link rel="stylesheet" type="text/css" href="<?= css_path('ui.multiselect.css')?>" />

<script type="text/javascript" src="<?=site_url('lib/multiselect-michael/jquery.localisation-min.js')?>"></script>
<script type="text/javascript" src="<?=site_url('lib/multiselect-michael/jquery.scrollTo-min.js')?>" ></script>
<script type="text/javascript" src="<?=site_url('lib/multiselect-michael/ui.multiselect.js')?>" ></script>

<script type="text/javascript">
	$("#multiselect-fields").multiselect();
</script> -->