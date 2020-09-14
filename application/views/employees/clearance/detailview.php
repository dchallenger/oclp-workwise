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
	        <div>
			
				<div class="icon-label-group align-right">                    
					<div class="icon-label">
						<a href="javascript:go_back();" class="icon-16-listback" rel="back-to-list">
							<span>Back to list</span> </a>
					</div>
				</div>
		
			<div class="spacer"></div>     
			<!-- #Preemployment Data-->
			<?php if (count($raw_data) > 0): ?>
				<div class="col-2-form view">
					<div class="form-item view odd">
						<label class="label-desc view gray">Employee:</label> 
						<div class="text-input-wrap"><?php echo $raw_data['employee']; ?></div>
					</div>
					<div class="form-item view even">
						<label class="label-desc view gray">Position:</label>
						<div class="text-input-wrap"><?php echo $raw_data['position']; ?></div>
					</div>
					<div class="form-item view odd">
						<label class="label-desc view gray">Company:</label>
						<div class="text-input-wrap"><?php echo $raw_data['company']; ?></div>
					</div>
					<div class="form-item view even">
						<label class="label-desc view gray">Department:</label>
						<div class="text-input-wrap"><?php echo $raw_data['department']; ?></div>
					</div>
					<div class="form-item view odd">
						<label class="label-desc view gray">Status:</label>
						<div class="text-input-wrap"><?php echo ($raw_data['status'] == '2') ? 'Approved'  : 'Pending'; ?></div>
					</div>
				</div>
				<div class="spacer"></div>				
			<?php endif; ?>
			
			<!-- #END Preemployment Data-->
			<form class="style2 detail-view" name="record-form" id="record-form" method="post" enctype="multipart/form-data">
				<input type="hidden" name="employee_id" id="employee_id" value="<?= $raw_data['user_id']?>" />
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
				foreach ($submodules as $sub):
				?>			
						<li rel="<?php echo $sub['link'] ?>">
							<span class="ctr"><?= $ctr++ ?></span>
							<div>
								<?php if ($this->user_access[$this->module_id]['edit'] === 1) {?>
								<a href="javascript:void(0)">
									<span class="trigger-edit"><?php echo $sub['label'] ?></span>
								</a>
								<?php } else{?>
									<?php if ($this->user_access[$sub['module_id']]['edit'] == 1){ ?>
									<a href="javascript:void(0)">
										<span class="trigger-edit"><?php echo $sub['label'] ?></span>
									</a>
									<?php }else{ ?>
									<span style="color: #B0AEAF;font-size: 14px;text-transform: uppercase;">
										<?php echo $sub['label'] ?>
									</span>
									<?php } ?>
								<?php }?>
								<!--<a class="trigger-print">Print</a>-->
								<?php if (isset($sub['status']['completed_by'])): ?>
									<span class="completed">
										<br />
										<small>Completed by: <?php echo $sub['status']['completed_by'] ?> on <?php echo $sub['status']['completed_on'] ?></small>
									</span>                    
								<?php elseif (isset($sub['status']['updated_by'])): ?>
									<span class="completed">
										<br />
										<small>Last update by: <?php echo $sub['status']['updated_by'] ?> on <?php echo $sub['status']['updated_on'] ?></small>
									</span>
								<?php endif; ?></div>                                
							<span class="pe-actions hidden">
								<?php if( $raw_data['status'] && $raw_data['status'] != 3 ){ ?>
								<?php if ($this->user_access[$sub['module_id']]['edit'] == 1): ?>
									<a href="javascript:void(0);" class="trigger-edit">Edit</a> | 
								<?php endif; ?>
								<?php if ($this->user_access[$sub['module_id']]['delete'] == 1): ?>
									<a href="javascript:void(0);" class="trigger-delete">Reset</a> | 
								<?php endif; ?>
								<?php } ?>                        
								<?php if ($this->user_access[$sub['module_id']]['print'] == 1): ?>
									<a href="javascript:void(0);" class="trigger-print">Print</a> 	
								<?php endif;?>
							</span>
						</li>
					<?php endforeach;?>
					<li rel="employee/deed_of_release">
						<span class="ctr"><?=$ctr++?></span>
						<?php if ($this->user_access[$this->module_id]['edit'] === 1) {?>
						<div><a href="javascript:void(0)">
								<span class="trigger-print">Deed of Release, Waiver, and Quitclaim</span>
							</a>							
							</div>                                
						<span class="pe-actions hidden" style="display: none;">                  
							<a class="trigger-print" href="javascript:void(0);">Print</a> 	
						</span>
						<?php }else{?>
						<div>
							<span style="color: #B0AEAF;font-size: 14px;text-transform: uppercase;">Deed of Release, Waiver, and Quitclaim</span>
														
						</div>  
						<?php }?>
					</li>
					<li rel="employee/quickclaim_received">
						<span class="ctr"><?=$ctr++?></span>
						<?php if ($this->user_access[$this->module_id]['edit'] === 1) {?>
						<div><a href="javascript:void(0)">
								<span>Certificate of Employment, and Certificate of Quitclaim received</span>
							</a>							
							</div>                                
						<span class="pe-actions hidden" style="display: none;">
							<?php if( $raw_data['quitclaim_received'] == 1 ){ ?>
							<a class="trigger-coe" href="javascript:void(0);">Print CoE</a>           
							<a class="trigger-quickclaim" href="javascript:void(0);">Print Quickclaim</a> 
							<?php } ?>	
						</span>
						<?php }else{?>
						<div>
							<span style="color: #B0AEAF;font-size: 14px;text-transform: uppercase;">Certificate of Employment, and Certificate of Quitclaim received</span>
						</div>
						<?php }?>
					</li>			
			</ul>            

			<div class="icon-label-group align-right">                
				<div class="icon-label">
					<a href="javascript:go_back();" class="icon-16-listback" rel="back-to-list">
						<span>Back to list</span> </a>
				</div>
			</div>
	        </div>
<?php endif; ?>
	<!-- END MAIN CONTENT -->

</div>