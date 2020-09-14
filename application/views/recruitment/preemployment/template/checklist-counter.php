<?php $this->load->helper('preemployment'); ?>
<ul class="list-counters2">
	<?php
	foreach ($checklists as $checklist):
		if ($this->hdicore->module_active($checklist['module_id'])):
			if ($checklist['code'] == 'preemployment_201' &&
				!$this->hdicore->module_active('hris_201')
			) {
				continue;
			}

			$count = get_checklist_count($checklist['table']);
			?>
			<li>            
				<a href="<?php echo site_url($checklist['link']); ?>">            
					<span class="ctr-orange <?= ($count > 0) ? '' : 'inactive' ?>"><?= $count ?></span>
					<span><?php echo $checklist['label'] ?></span>
				</a>
			</li>
			<?php
		endif;
	endforeach;
	?>
</ul>