<!-- start #page-head -->
<div id="page-head" class="page-info">
	<div id="page-title">
		<h2 class="page-title"><span class="title"><?= $this->detailview_title; ?></span></h2>
	</div>
	<div id="page-desc" class="align-left"><p><?= $this->detailview_description ?></p></div>

	<?php
	// Page Nav Structure
	if (isset($pnav))
		echo $pnav;
	?>
	<div class="clear"></div>
</div><!-- end #page-head -->

<?php $this->load->view($this->userinfo['rtheme'] . '/template/sidebar'); ?>
<div id="body-content-wrap">
	<div class="leftpane">
		<?php $this->load->view($this->userinfo['rtheme'] . '/recruitment/preemployment/template/checklist-counter'); ?>
	</div>
	<!-- content alert messages -->
	<div id="message-container">
		<?php
		if (isset($msg)) {
			echo is_array($msg) ? implode("\n", $msg) : $msg;
		}
		if (isset($flashdata)) {
			echo $flashdata;
		}
		?>
	</div>
	<!-- content alert messages -->

	<!-- PLACE YOUR MAIN CONTENT HERE -->
	<?php if (isset($error)) : ?>
	        <div id="message_box" class="attention" style="padding-left: 60px;width:60%;margin: 0 auto;">
			<img src="<?= base_url() . $this->userinfo['theme']; ?>/images/exclamation-big.png" alt="" >
			<h3 style="margin: 0.3em 0 0.5em 0">Oops! <?= $error ?></h3>

			<p><?= $error2 ?></p>
	        </div>
	<? else : ?>
	        <div class="rightpane">
			<div class="wizard-header">   
				<div class="icon-label-group align-right">                    
					<div class="icon-label">
						<a href="javascript:void(0);" class="icon-16-listback" rel="back-to-list">
							<span>Back to list</span> </a>
					</div>
				</div>
			</div>

			<!-- #Preemployment Data-->
			<?php if (count($raw_data) > 0): ?>
				<div class="col-2-form view">
					<div class="form-item view odd">
						<label class="label-desc view gray">Applicant Name:</label> 
						<div class="text-input-wrap"><?php echo $raw_data['applicant_name']; ?></div>
					</div>
					<div class="form-item view even">
						<label class="label-desc view gray">Position / Requested By:</label>
						<div class="text-input-wrap"><?php echo $raw_data['requested_by']; ?></div>
					</div>
					<div class="form-item view odd">
						<label class="label-desc view gray">Subsidiary / Department:</label>
						<div class="text-input-wrap"><?php echo $raw_data['company'] . ' - ' . $raw_data['department']; ?></div>
					</div>
				</div>

				<div class="spacer"></div>                        
			<?php endif; ?>
			
			<!-- #END Preemployment Data-->
			<form class="style2 detail-view" name="record-form" id="record-form" method="post" enctype="multipart/form-data">
				<input type="hidden" name="applicant_id" id="applicant_id" value="<?= $raw_data['applicant_id']?>" />
				<input type="hidden" name="record_id" id="record_id" value="<?= $this->input->post('record_id') ?>" />
				<input type="hidden" name="return_record_id" id="return_record_id" value="<?= $this->input->post('record_id') ?>" />
				<input type="hidden" name="previous_page" id="previous_page" value="<?= base_url() . $this->module_link ?>/detail"/>
				<input type="hidden" name="prev_search_str" id="prev_search_str" value="<?= $this->input->post('prev_search_str') ?>"/>
				<input type="hidden" name="prev_search_field" id="prev_search_field" value="<?= $this->input->post('prev_search_field') ?>"/>
				<input type="hidden" name="prev_search_option" id="prev_search_option" value="<?= $this->input->post('prev_search_option') ?>"/>                
				<?php ?>

				<?php
				if (sizeof($views) > 0) :
					foreach ($views as $view) :
						$this->load->view($this->userinfo['rtheme'] . '/' . $view);
					endforeach;
				endif;
				?>
			</form>
			<div class="clear"></div>
			<?php $ctr = 1 ?>
			<ul class="rightpane-list">
				<?php
				foreach ($checklists as $checklist):
					if ($this->hdicore->module_active($checklist['module_id'])):
						if ($checklist['code'] == 'preemployment_201' &&
							!$this->hdicore->module_active('hris_201')
						) {
							continue;
						}
						?>
						<li rel="<?php echo $checklist['link'] ?>">
							<span class="ctr"><?= $ctr++ ?></span>
							<div><a href="javascript:void(0)">
									<span class="trigger-edit"><?php echo $checklist['label'] ?></span>
								</a>
								<!--<a class="trigger-print">Print</a>-->
			<?php if (isset($checklist['status']['completed_by'])): ?>
									<span class="completed">
										<br />
										<small>Completed by: <?php echo $checklist['status']['completed_by'] ?> on <?php echo $checklist['status']['completed_on'] ?></small>
									</span>                    
			<?php elseif (isset($checklist['status']['updated_by'])): ?>
									<span class="completed">
										<br />
										<small>Last update by: <?php echo $checklist['status']['updated_by'] ?> on <?php echo $checklist['status']['updated_on'] ?></small>
									</span>
								<?php endif; ?></div>                                
							<span class="pe-actions hidden">
								<?php if ($this->user_access[$checklist['module_id']]['edit'] == 1): ?>
									<a href="#" class="trigger-edit">Edit</a> | 
								<?php endif; ?>
								<?php if ($this->user_access[$checklist['module_id']]['delete'] == 1): ?>
									<a href="#" class="trigger-delete">Reset</a> | 
								<?php endif; ?>                            
								<?php if ($this->user_access[$checklist['module_id']]['print'] == 1): ?>
									<a href="#" class="trigger-print">Print</a> | 
			<?php endif; ?>                        
							</span>
						</li>
						<?php
					endif;
				endforeach;
				?>
			</ul>            

			<div class="icon-label-group align-right">                
				<div class="icon-label">
					<a href="javascript:void(0);" class="icon-16-listback" rel="back-to-list">
						<span>Back to list</span> </a>
				</div>
			</div>
	        </div>
<?php endif; ?>
	<!-- END MAIN CONTENT -->

</div>