<?php
if (isset($jqgrid)) {
	$jqgrid = $jqgrid;
} else {
	$jqgrid = 'template/jqgrid';
}
?>

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
	<div class="">             
		<!-- div class="rightpane-header">
			<img src="< ?php echo site_url('themes/' . $this->userinfo['rtheme'] . '/' . $this->listview_image); ?>">
			<h3><?= $this->listview_title ?></h3>
		</div -->
		<form name="record-form" id="record-form" method="post" enctype="multipart/form-data">
			<?php if (isset($mrf_id)): ?>
				<input type="hidden" name="mrf_id" value="<?php echo $mrf_id; ?>" />
			<?php endif; ?>
			<input type="hidden" name="record_id" id="record_id"  />
			<input type="hidden" name="previous_page" id="previous_page" value="<?= base_url() . $this->module_link ?>"/>
			<input type="hidden" name="prev_search_str" id="prev_search_str" value="<?= $this->input->post('prev_search_str') ?>"/>
			<input type="hidden" name="prev_search_field" id="prev_search_field" value="<?= $this->input->post('prev_search_field') ?>"/>
			<input type="hidden" name="prev_search_option" id="prev_search_option" value="<?= $this->input->post('prev_search_option') ?>"/>
		</form>

		<table id="jqgridcontainer"></table>
		<div id="jqgridpager"></div>
		<?php echo $this->load->view($this->userinfo['rtheme'] . '/' . $jqgrid); ?>
		<div class="clear"></div>

		<div class="search-wrap hidden">
			<?php if (isset($mrf)): ?>            
				<strong>Manpower Request: </strong><em><?php echo anchor('recruitment/manpower/detail/' . $mrf['request_id'], $mrf['document_number']); ?></em> - <?php echo $mrf['position'] . '[' . $mrf['number_required'] . ']'; ?>
			<?php endif; ?>
			<?php if($filter_true): ?>
				<strong style="font-size:15px"> <?= $default_query_val ?> </strong>
			<?php endif; ?>
			<div class="align-right">
				<?php
				if (isset($show_search) && $show_search == true):
					$this->db->where('fieldlabel', 'candidate_search_trigger');
					$result = $this->db->get('field');
					$field = $result->row_array();
					?>
					<a style="padding-left:20px;" onclick="getRelatedModule(<?php echo $field['field_id'] ?>, 'applicant_id', <?= $mrf_id ?>)" href="javascript:void(0);" class="icon-16-search"><span>Advanced Search</span></a>
					<?php
				;else: ?>
					<div class="search-trigger" tooltip="Search Options">
						<div><a href="javascrpt:void(0)" class="icon-16-search-opts">Search Options</a></div>
					</div>
					<div class="search-input">
						<form class="search" name="form-search-jqgridcontainer" id="form-search-jqgridcontainer" jqgridcontainer="jqgridcontainer">
							<?php
							if ($this->input->post('prev_search_str') && $this->input->post('prev_search_str') != "") {
								$srch_str = $this->input->post('prev_search_str');
							} else {
								$srch_str = "Search...";
							}
							?>
							<input id="search" class="search-jqgridcontainer" type="text" value="<?php echo $srch_str ?>" onclick="" onfocus="javascript:($(this).val()=='Search...' ? $(this).val('') : '')" onblur="javascript:($(this).val()=='' ? $(this).val('Search...') : '')"/>
							<input type="button" id="search-btn" class="search-btn" value="Search">
						</form>
					</div>
					<div class="clear"></div>
					<form class="style2 search-options search-options-jqgridcontainer hidden">
						<div class="col-2-form nomargin">
							<div class="form-item align-left">
								<label class="label">Search in</space>
									<div class="clear"></div>
									<div class="select-input-wrap nomargin">
										<select class="searchfield-jqgridcontainer" id="searchfield-jqgridcontainer" name="searchfield-jqgridcontainer">
											<?php
											foreach ($this->listview_columns as $index => $column) :
												if ($column['name'] != "action") :
													echo '<option value="' . $column['name'] . '">' . $this->listview_column_names[$index] . '</option>';
												endif;
											endforeach;
											?>
										</select>
									</div>
							</div>
							<div class="form-item align-left">
								<label class="label">Other filter</label>
								<div class="clear"></div>
								<div class="select-input-wrap nomargin">
									<select class="input-select" id="searchop-jqgridcontainer" name="searchop-jqgridcontainer">
										<option value="eq">equal</option>
										<option value="ne">not equal</option>
										<option value="lt">less</option>
										<option value="le">less or equal</option>
										<option value="gt">greater</option>
										<option value="ge">greater or equal</option>
										<option value="bw">begins with</option>
										<option value="bn">does not begin with</option>
										<option value="in">is in</option>
										<option value="ni">is not in</option>
										<option value="ew">ends with</option>
										<option value="en">does not end with</option>
										<option value="cn" selected="selected">contains</option>
										<option value="nc">does not contain</option>
									</select>
								</div>
							</div>
							<div class="clear"></div>
						</div>
					</form>
				</div>
<?php endif; ?>
		</div>
	</div><!-- END RIGHT PANE -->
	<!-- END MAIN CONTENT -->
</div>