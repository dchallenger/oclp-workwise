<script type="text/javascript">
	$(document).ready(function() {
		$('#tabs').tabs();
		$('.accordion').accordion();
	});
</script>
<!-- start #page-head -->

<div id="page-head" class="page-info">
	<div id="page-title">
		<h2 class="page-title"><span class="title">Applicant View Template</span></h2>
	</div>
	<div id="page-desc" class="align-left">
		<p>
			<?= $this->detailview_description ?>
		</p>
	</div>
	<?php
	// Page Nav Structure
	if (isset($pnav))
		echo $pnav;
	?>
	<div class="clear"></div>
</div>
<!-- end #page-head -->

<div class="sidebar-wrap">
	<div class="styleguide-links">
		<h3>Design Style Guide</h3>
		<?php $this->load->view($this->userinfo['rtheme'] . '/design/table-of-contents') ?>
	</div>
	<div class="searchbar ui-widget">
		<input type="text" value="Search Site" class="input-text ui-autocomplete-input" onfocus="if (this.value == 'Search Site') {this.value = '';}" onblur="if (this.value == '') {this.value = 'Search Site';}" id="searchbar" autocomplete="off" role="textbox" aria-autocomplete="list" aria-haspopup="true">
	</div>
</div>
<div id="body-content-wrap">


	<!-- INFO VIEW (with additional edit buttons)
	----------------------------------------------------------------------------------->
	<hr />
	<div class="spacer"></div>
	<div class="wizard-leftcol">
		<ul>
			<li><a href="javascript:(void)"><span>1</span>Basic Information</a></li>
			<li><a href="javascript:(void)"><span>2</span>Personal</a></li>
			<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
			<li class="wizard-active"><a href="javascript:(void)"><span>3</span>Details</a></li>
		</ul>


	</div>
	<div class="wizard-rightcol">
		<div class="wizard-header"> <img src="<?php echo base_url() . $this->userinfo['theme'] ?>/images/wizard-iconpic.png" />
			<h2>Reyes, Benjamin</h2>

			<div class="page-navigator align-right">
				<div class="btn-prev-disabled"> 
					<a href="javascript:void(0)">
						<span>Prev</span></a>
				</div>
				<div class="btn-prev hidden">
					<a onclick="prev_wizard()" href="javascript:void(0)">
						<span>Prev</span></a>
				</div>
				<div class="btn-next">
					<a onclick="next_wizard()" href="javascript:void(0)">
						<span>Next</span></a>
				</div>
				<div class="btn-next-disabled hidden"> 
					<a href="javascript:void(0)">
						<span>Next</span></a>
				</div>
			</div>

			<div class="icon-label-group align-right">
				<div class="icon-label">
					<a onclick="edit()" href="javascript:void(0);" class="icon-16-add icon-16-add-listview">
						<span>Add</span></a>
				</div>
				<div class="icon-label">
					<a href="javascript:void(0);" class="icon-16-listback" rel="back-to-list">
						<span>Back to list</span></a>
				</div>
			</div>
		</div>
		<div class="wizard-type-form hidden current-wizard wizard-first" id="fg-35" fg_id="35" style="display: block;">
			<form enctype="multipart/form-data" method="post" id="record-form" name="record-form" class="style2 detail-view">
				<input type="hidden" value="3" id="record_id" name="record_id">
				<input type="hidden" value="3" id="return_record_id" name="return_record_id">
				<input type="hidden" value="http://localhost/hdi.resource/recruitment/applicants/detail" id="previous_page" name="previous_page">
				<input type="hidden" value="" id="prev_search_str" name="prev_search_str">
				<input type="hidden" value="all" id="prev_search_field" name="prev_search_field">
				<input type="hidden" value="" id="prev_search_option" name="prev_search_option">
				<div id="tabs">
					<ul>
						<li><a href="#tab-record">201 Record</a></li>
						<li><a href="#tab-employment-data">Employment Data</a></li>
						<li><a href="#tab-performance-records">Performance Records</a></li>
						<li><a href="#tab-compben">Compensation and Benefits</a></li>
						<li><a href="#tab-medical-records">Medical Records</a></li>
						<li><a href="#tab-references">Relatives and References</a></li>
					</ul>
					<div id="tab-record">
						<div id="accordion-tab-record" class="accordion">
							<h3><a href="#">Educational Background</a></h3>
							<p></p>
							<h3><a href="#">Employment History</a></h3>
							<p></p>
							<h3><a href="#">Licenses</a></h3>
							<p></p>
							<h3><a href="#">Exams Taken</a></h3>
							<p></p>
							<h3><a href="#">Skills</a></h3>
							<p></p>
							<h3><a href="#">Training Taken</a></h3>
							<p></p>
						</div>
					</div>
					<div id="tab-employment-data">
						<div id="accordion-employment-data" class="accordion">
							<h3><a href="#">Employee Movement</a></h3>
							<p></p>
							<h3><a href="#">Career Preferences</a></h3>
							<p></p>
							<h3><a href="#">Property Accountability</a></h3>
							<p></p>
							<h3><a href="#">Employee Tickler</a></h3>
							<p></p>
						</div>
					</div>
					<div id="tab-performance-records">adawddaw</div>
					<div id="tab-compben">adwawdadw</div>
					<div id="tab-medical-records">adwwadwad</div>
					<div id="tab-references">
						<div id="accordion-references" class="accordion">
							<h3><a href="#">Emergency Contacts</a></h3>
							<p></p>
							<h3><a href="#">References</a></h3>
							<p></p>
							<h3><a href="#">Family Background</a></h3>
							<div>
								<div class="form-item view odd">
									<label class="label-desc view gray" for="firstname">Name:</label>
									<div class="text-input-wrap"> teteSSSS </div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</form>
		</div>

		<div class="page-navigator align-right">
			<div class="btn-prev-disabled"> 
				<a href="javascript:void(0)">
					<span>Prev</span></a>
			</div>
			<div class="btn-prev hidden">
				<a onclick="prev_wizard()" href="javascript:void(0)">
					<span>Prev</span></a>
			</div>
			<div class="btn-next">
				<a onclick="next_wizard()" href="javascript:void(0)">
					<span>Next</span></a>
			</div>
			<div class="btn-next-disabled hidden"> 
				<a href="javascript:void(0)">
					<span>Next</span></a>
			</div>
		</div>

		<div class="icon-label-group align-right">
			<div class="icon-label">
				<a onclick="edit()" href="javascript:void(0);" class="icon-16-add icon-16-add-listview">
					<span>Add</span></a>
			</div>
			<div class="icon-label">
				<a href="javascript:void(0);" class="icon-16-listback" rel="back-to-list">
					<span>Back to list</span></a>
			</div>
		</div>
	</div>

	<!-- END MAIN CONTENT -->
</dl>
