<?php  $this->load->view($this->userinfo['rtheme'].'/design/table-of-contents')?>

<div class="leftpane">

<ul class="icon-filters">
	<li><a href="#"><img src="<?php echo base_url() . $this->userinfo['theme'] ?>/icons/icon-postedjobs.png" /><span>Posted Jobs</span></a></li>
  <li><a href="#"><img src="<?php echo base_url() . $this->userinfo['theme'] ?>/icons/icon-position.png" /><span>Position</span></a></li>
  <li><a href="#"><img src="<?php echo base_url() . $this->userinfo['theme'] ?>/icons/icon-application.png" /><span>Application</span></a></li>
</ul>

<ul class="list-counters">
	<li><a href="#"><span>For Interview</span> <span class="ctr-blue">8</span></a></li>
  <li><a href="#"><span>Recommended</span> <span class="ctr-blue">10</span></a></li>
  <li><a href="#"><span>For Further Evaluation</span> <span class="ctr-blue">4</span></a></li>
  <li><a href="#"><span>Failed</span> <span class="ctr-blue">1</span></a></li>
</ul>


</div>

<div class="rightpane">
	<div class="rightpane-header">
		<img src="<?php echo base_url() . $this->userinfo['theme'] ?>/icons/vcard.png" />
		<h3>Posted Jobs</h3>
  </div>
	
  <form class="style2 jobsearch">
  	<div class="jobsearch-span3">
    		<label class="label-desc gray">Applicant's Name</label>
        <div class="text-input-wrap"><input type="text" class="input-text text-right" value="" id="number_required" name="number_required"></div>
    </div>
    <div class="spacer"></div>
    <div class="jobsearch-span1">
    		<label class="label-desc gray">Gender</label>
        <div class="select-input-wrap"><select id="position_id" name="position_id">
					</select>
				</div>
    </div>
    <div class="jobsearch-span1">
    		<label class="label-desc gray">Status</label>
        <div class="select-input-wrap"><select id="position_id" name="position_id">
					</select>
				</div>
    </div>
    <div class="jobsearch-span1">
    		<label class="label-desc gray">Age</label>
        <div class="select-input-wrap"><select id="position_id" name="position_id">
					</select>
				</div>
    </div>
    <div class="spacer"></div>
     <div class="jobsearch-span1">
    		<label class="label-desc gray">Education</label>
        <div class="select-input-wrap"><select id="position_id" name="position_id">
					</select>
				</div>
    </div>
    <div class="jobsearch-span1">
    		<label class="label-desc gray">Field</label>
        <div class="select-input-wrap"><select id="position_id" name="position_id">
					</select>
				</div>
    </div>
    <div class="jobsearch-span1">
    		<label class="label-desc gray">Career</label>
        <div class="select-input-wrap"><select id="position_id" name="position_id">
					</select>
				</div>
    </div>
    <div class="spacer"></div>
    
    <div class="icon-label-group align-right">
    	<div class="icon-label">
    	<a href="javascript:void(0)" module_link="admin/template_manager" container="jqgridcontainer" class="icon-16-search	"><span>Search</span></a></div></div>
  </form>
  <p>&nbsp;</p>

<table width="100%" border="0" class="simple-table" cellpadding="5">
  <tr>
    <th bgcolor="#CCCCCC">Company</th>
    <th bgcolor="#CCCCCC">Position</th>
    <th bgcolor="#CCCCCC">Date Needed</th>
    <th bgcolor="#CCCCCC">Status</th>
    <th bgcolor="#CCCCCC">Action</th>
  </tr>
  <tr>
    <td>HDI Adventures</td>
    <td><strong>Manger</strong><br /><small><em>ADVMGR-201101</em></small></td>
    <td>11/12/11</td>
    <td>approved</td>
    <td>
    <span class="icon-group">
    		<a href="javascript:void(0)" tooltip="Print" module_link="admin/module" class="icon-button icon-16-print" original-title=""></a>
        <a module_link="admin/module" href="javascript:void(0)" tooltip="Cancel" class="icon-button icon-16-cancel" original-title=""></a>
        <a record_id="31" module_link="admin/module" href="javascript:void(0)" tooltip="Search" class="icon-button icon-16-search" original-title=""></a>
        <a href="javascript:void(0)" module_link="admin/module" container="jqgridcontainer" tooltip="Post to Web" class="icon-button icon-16-publish delete-single" original-title=""></a></span></td>
  </tr>
  <tr>
    <td>HDI Systech</td>
    <td><strong>Programmer</strong><br /><small><em>SYSPGMR-201101</em></small></td>
    <td>11/20/11</td>
    <td>for pooling</td>
    <td><span class="icon-group">
    		<a href="javascript:void(0)" tooltip="Print" module_link="admin/module" class="icon-button icon-16-print" original-title=""></a>
        <a module_link="admin/module" href="javascript:void(0)" tooltip="Cancel" class="icon-button icon-16-cancel" original-title=""></a>
        <a record_id="31" module_link="admin/module" href="javascript:void(0)" tooltip="Search" class="icon-button icon-16-search" original-title=""></a>
        <a href="javascript:void(0)" module_link="admin/module" container="jqgridcontainer" tooltip="Post to Web" class="icon-button icon-16-publish delete-single" original-title=""></a></span></td>
  </tr>
</table>

</div><!-- rightpane -->



<!-- EDIT END
----------------------------------------------------------------------------------->
</div>