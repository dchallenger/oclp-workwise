<?php  $this->load->view($this->userinfo['rtheme'].'/design/table-of-contents')?>



<!-- EDIT START
----------------------------------------------------------------------------------->

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
		<img src="<?php echo base_url() . $this->userinfo['theme'] ?>/icons/drawer.png" />
		<h3>Application</h3>
  </div>

<table width="100%" border="0" class="simple-table" cellpadding="5">
  <tr>
    <th bgcolor="#CCCCCC" scope="col">Name</th>
    <th bgcolor="#CCCCCC" scope="col">Gender</th>
    <th bgcolor="#CCCCCC" scope="col">Prefered Position 1</th>
    <th bgcolor="#CCCCCC" scope="col">Prefered Position 2</th>
    <th bgcolor="#CCCCCC" scope="col">Application Status</th>
    <th bgcolor="#CCCCCC" scope="col">Action</th>
  </tr>
  <tr>
    <td>James Garcia</td>
    <td>M</td>
    <td>Developer</td>
    <td>Encoder</td>
    <td>approved</td>
    <td> <span class="icon-group">
    		<a href="javascript:void(0)" tooltip="Print" module_link="admin/module" class="icon-button icon-16-print	" original-title=""></a>
        </span></td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
</table>





</div><!-- rightpane -->



<!-- EDIT END
----------------------------------------------------------------------------------->
</div>