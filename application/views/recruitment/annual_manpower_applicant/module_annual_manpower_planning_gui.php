<style type="text/css" media="screen">
    .text-left{ text-align: left !important}
    .rotate { height: 60px; vertical-align: bottom; cursor: pointer;  -moz-user-select: none; -webkit-user-select: none; }
    tbody tr th.module-name { cursor: pointer; -moz-user-select: none; -webkit-user-select: none; }
    .rotate div { -moz-transform: rotate(290deg); -webkit-transform: rotate(290deg); filter: progid:DXImageTransform.Microsoft.BasicImage(rotation=3.1); display: block; width: 16px; text-align: center; margin: 0 auto;}
</style>
<?php 
    $annual_manpower_planning = $this->db->dbprefix('annual_manpower_planning');
    $annual_manpower_planning_id = $_POST['record_id'];
    $annual_manpower_planning_header = $this->db->query("SELECT * FROM {$annual_manpower_planning}  WHERE annual_manpower_planning_id = '{$annual_manpower_planning_id}'")->row();
    
   $this->db->join('user','user.employee_id = annual_manpower_planning_evaluation_remarks.remarked_by','left');
    $this->db->where('annual_manpower_planning_evaluation_remarks.annual_manpower_planning_id',$annual_manpower_planning_id);
    $this->db->group_by('annual_manpower_planning_evaluation_remarks.remarked_by');
    $this->db->order_by('annual_manpower_planning_evaluation_remarks.date_remarked','ASC');
    $annual_manpower_planning_evaluation_remarks = $this->db->get('annual_manpower_planning_evaluation_remarks');


    $annual_status_id = '';
    if($_POST['record_id'] != -1)
    {
        $annual_status_id = $annual_manpower_planning_header->annual_manpower_planning_status_id;        
    }    
?>
<h3 class="form-head">Planning Details</h3>

<?php

    if( $annual_status_id == 4 && $annual_manpower_planning_evaluation_remarks->num_rows() > 0 ){

        foreach( $annual_manpower_planning_evaluation_remarks->result() as $remarks_info ){

?>
    <div class="col-2-form view"> 
        <div class="form-item view odd ">
            <label class="label-desc view gray" for="remarks">Remarked By:</label>         
            <div class="text-input-wrap"><?= $remarks_info->firstname." ".$remarks_info->lastname ?></div>
        </div>
        <div class="form-item view odd ">
            <label class="label-desc view gray" for="remarks">Date:</label>         
            <div class="text-input-wrap"><?= date('F d,Y',strtotime($remarks_info->date_remarked) ); ?></div>
        </div>                        
        <div class="form-item view odd ">
            <label class="label-desc view gray" for="remarks">Remarks:</label>         
            <div class="text-input-wrap"><?= $remarks_info->remarks ?></div>
        </div>
    </div>
    <br />
    
<?php

        } ?>


<p><span style="font-weight:bold;">Note:</span> All highlighted records needs to be evaluated</p><br />

<?php

    }
?>
<!-- <p class="form-group-description align-left">Check all that applies. You can also click the <strong><em>action name</em></strong> or the <strong><em>module name</em></strong> to check the column or the rows respectively.</p> -->

<div class="clear"></div>
<div class="spacer"></div>
<div id="module-access-container">
    <table class="default-table boxtype" style="width:100%" id="module-access">
            <colgroup width="15%"></colgroup>
            <thead>
                <tr class="">
                    <th style="text-align:left;" colspan="14">Positions with Incumbent</th>
                </tr>
                <tr class="">
                    <th style="vertical-align:middle">Employees</th><th class="action-name font-smaller even"><div>Jan</div></th><th class="action-name font-smaller odd"><div>Feb</div></th><th class="action-name font-smaller even"><div>Mar</div></th><th class="action-name font-smaller odd"><div>Apr</div></th><th class="action-name font-smaller even"><div>May</div></th><th class="action-name font-smaller odd"><div>Jun</div></th><th class="action-name font-smaller even"><div>Jul</div></th><th class="action-name font-smaller odd"><div>Aug</div></th><th class="action-name font-smaller even"><div>Sep</div></th><th class="action-name font-smaller odd"><div>Oct</div></th><th class="action-name font-smaller even"><div>Nov</div></th><th class="action-name font-smaller odd"><div>Dec</div></th><th class="even"><span></span></th></tr>
            </thead>
            <tbody class="structure_list"></tbody>
        </table>
</div>
<br />

<!-- New Headcount -->
<?php /*
if( $_POST['record_id'] == '-1' || ( $annual_status_id == 1 || $annual_status_id == 4 ) )
{
?>
    
    <div class="form-submit-btn align-right nopadding">
        <div class="icon-label-group">
            <div class="icon-label">
                <a rel="action-addnewposition" class="icon-16-add add_new_job" href="javascript:void(NULL)" onclick="">
                    <span>Add New Position</span>
                </a>            
            </div>
            <div class="icon-label">
                <a rel="action-addnewposition" class="icon-16-add add_existing_job" href="javascript:void(NULL)" onclick="">
                    <span>Add Existing Position</span>
                </a>            
            </div>
        </div>
    </div>
    <br />

<?php
} */
?>


<div class="clear"></div>
<div class="spacer"></div>
<div id="headcount-container"></div>


<!-- new table -->
<div class="clear"></div>
<div class="spacer"></div>
<div id="headcount-existing-container">
    <table id="module-exist-headcount" style="width:100%" class="default-table boxtype">
                    <colgroup width="15%"></colgroup>
                    <thead>
                        <tr class="">
                            <th colspan="17" style="text-align:left;">Existing Job</th>
                        </tr>
                        <tr class="">
                            <th style="vertical-align:middle"><small>&nbsp;</small></th>
                            <th class="action-name font-smaller even"><div>Previous AMP</div></th><th class="action-name font-smaller even"><div>Jan</div></th><th class="action-name font-smaller odd"><div>Feb</div></th><th class="action-name font-smaller even"><div>Mar</div></th><th class="action-name font-smaller odd"><div>Apr</div></th><th class="action-name font-smaller even"><div>May</div></th><th class="action-name font-smaller odd"><div>Jun</div></th><th class="action-name font-smaller even"><div>Jul</div></th><th class="action-name font-smaller odd"><div>Aug</div></th><th class="action-name font-smaller even"><div>Sep</div></th><th class="action-name font-smaller odd"><div>Oct</div></th><th class="action-name font-smaller even"><div>Nov</div></th><th class="action-name font-smaller odd"><div>Dec</div></th>
                            <th class="action-name font-smaller even"><span>Total</span></th>
                            <th class="action-name font-smaller odd"><div></div></th>
                            <th class="action-name font-smaller even"><span><small>&nbsp;</small></span></th>
                        </tr>
                    </thead><tbody class="existing_headcount_position_empty"><tr><td colspan="17" style="text-align:center; font-weight:bold;">No existing job available</td></tr></tbody>
                </table>
</div>

<?php
if( $_POST['record_id'] == '-1' || ( $annual_status_id == 1 || $annual_status_id == 4 ) )
{
?>
    <div class="clear"></div>
    <div class="spacer"></div>
    <div class="form-submit-btn align-right nopadding add_new_job_container">
        <div class="icon-label-group">
            <div class="icon-label">
                <a rel="action-addnewheadcountposition" class="icon-16-add add_new_headcount_job" href="javascript:void(NULL)" onclick="">
                    <span>Add New Job</span>
                </a>            
            </div>
        </div>
    </div>
    <br />

<?php
}
?>

<div class="clear"></div>
<div class="spacer"></div>
<div id="headcount-new-container">
    <table id="module-new-headcount" style="width:100%" class="default-table boxtype">
                <colgroup width="15%"></colgroup>
                <thead>
                    <tr class="">
                        <th colspan="16" style="text-align:left;">New Job</th>
                    </tr>
                    <tr class="">
                        <th style="vertical-align:middle">&nbsp;</th>
                        <th class="action-name font-smaller even"><div>Jan</div></th>
                        <th class="action-name font-smaller odd"><div>Feb</div></th>
                        <th class="action-name font-smaller even"><div>Mar</div></th>
                        <th class="action-name font-smaller odd"><div>Apr</div></th>
                        <th class="action-name font-smaller even"><div>May</div></th>
                        <th class="action-name font-smaller odd"><div>Jun</div></th>
                        <th class="action-name font-smaller even"><div>Jul</div></th>
                        <th class="action-name font-smaller odd"><div>Aug</div></th>
                        <th class="action-name font-smaller even"><div>Sep</div></th>
                        <th class="action-name font-smaller odd"><div>Oct</div></th>
                        <th class="action-name font-smaller even"><div>Nov</div></th>
                        <th class="action-name font-smaller odd"><div>Dec</div></th>
                        <th class="action-name font-smaller even"><span>Total</span></th>
                        <th class="action-name font-smaller odd"><div></div></th>
                        <th class="action-name font-smaller even"><span>&nbsp;</span></th>
                    </tr>
                </thead><tbody class="new_headcount_position_empty"><tr><td colspan="17" style="text-align:center; font-weight:bold;">No new job added</td></tr></tbody></table>
</div>




<?php
if( $_POST['record_id'] == '-1' || ( $annual_status_id == 1 || $annual_status_id == 4 ) )
{
?>
<!--
<div class="form-submit-btn align-right nopadding">
    <div class="icon-label-group">
        <div class="icon-label">
                <a rel="action-addnewposition" class="icon-16-add add_new_job" href="javascript:void(NULL)" onclick="">
                    <span>Add New Job</span>
                </a>            
            </div>
            <div class="icon-label">
                <a rel="action-addnewposition" class="icon-16-add add_existing_job" href="javascript:void(NULL)" onclick="">
                    <span>Add Existing Job</span>
                </a>            
            </div>
    </div>
</div>
-->
<?php
}
?>