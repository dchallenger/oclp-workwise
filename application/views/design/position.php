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
		<img src="<?php echo base_url() . $this->userinfo['theme'] ?>/icons/briefcase.png" />
		<h3>Position</h3>
  </div>
	
  
  

<table width="100%" border="0" class="simple-table" cellpadding="5">
  <tr>
    <th bgcolor="#CCCCCC">Company</th>
    <th bgcolor="#CCCCCC">Position</th>
    <th bgcolor="#CCCCCC">No. of Employees</th>
    <th bgcolor="#CCCCCC">Action</th>
  </tr>
  <tr>
    <td>HDI Adventures</td>
    <td><strong>Manger</strong><br /><small><em>ADVMGR-201101</em></small></td>
    <td>40</td>
    <td>
    <span class="icon-group">
    		<a href="javascript:void(0)" tooltip="View" module_link="admin/module" class="icon-button icon-16-info	" original-title=""></a>
        </span></td>
  </tr>
  <tr>
    <td>HDI Systech</td>
    <td><strong>Programmer</strong><br /><small><em>SYSPGMR-201101</em></small></td>
    <td>20</td>
    <td>  <span class="icon-group">
    		<a href="javascript:void(0)" tooltip="View" module_link="admin/module" class="icon-button icon-16-info	" original-title=""></a>
        </span></td>
  </tr>
</table>

</div><!-- rightpane -->



<!-- EDIT END
----------------------------------------------------------------------------------->
</div>