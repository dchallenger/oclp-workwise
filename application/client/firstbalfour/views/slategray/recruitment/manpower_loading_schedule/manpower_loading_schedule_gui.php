<style type="text/css" media="screen">
    .text-left{ text-align: left !important}
    .rotate { height: 60px; vertical-align: bottom; cursor: pointer;  -moz-user-select: none; -webkit-user-select: none; }
    tbody tr th.module-name { cursor: pointer; -moz-user-select: none; -webkit-user-select: none; }
    .rotate div { -moz-transform: rotate(290deg); -webkit-transform: rotate(290deg); filter: progid:DXImageTransform.Microsoft.BasicImage(rotation=3.1); display: block; width: 16px; text-align: center; margin: 0 auto;}
</style>

<?php 
    $manpower_loading_status = 0;
    if ($_POST['record_id'] != '-1' ) {
        $this->db->where('manpower_loading_schedule_id', $_POST['record_id']);
        $manpower_loading = $this->db->get('manpower_loading_schedule')->row();
        $manpower_loading_status = $manpower_loading->manpower_loading_schedule_status_id;
    }   

?>

<h3 class="form-head">Manpower Loading Schedule Setup</h3>

<div class="clear"></div>
<div class="spacer"></div>
<div id="module-access-container">
    <table class="default-table boxtype" style="width:100%" id="module-access">
            <colgroup width="15%"></colgroup>
            <thead>
                <tr class="">
                    <th style="text-align:left;" colspan="15">&nbsp;</th>
                </tr>
                <tr class="">
                    <th style="vertical-align:middle">Category</th><th class="action-name font-smaller even"><div>Remarks (Head Count)</div></th><th class="action-name font-smaller even"><div>Jan</div></th><th class="action-name font-smaller odd"><div>Feb</div></th><th class="action-name font-smaller even"><div>Mar</div></th><th class="action-name font-smaller odd"><div>Apr</div></th><th class="action-name font-smaller even"><div>May</div></th><th class="action-name font-smaller odd"><div>Jun</div></th><th class="action-name font-smaller even"><div>Jul</div></th><th class="action-name font-smaller odd"><div>Aug</div></th><th class="action-name font-smaller even"><div>Sep</div></th><th class="action-name font-smaller odd"><div>Oct</div></th><th class="action-name font-smaller even"><div>Nov</div></th><th class="action-name font-smaller odd"><div>Dec</div></th></tr>
            </thead>
        </table>
</div>
<br />

<?php
if( $_POST['record_id'] == '-1' || ( $manpower_loading_status == 1 || $manpower_loading_status == 4 ) )
{
?>
   <div class="clear"></div>
    <div class="spacer"></div>
    <div class="form-submit-btn align-right nopadding add_new_job_container">
        <div class="icon-label-group">  
   
               <div class="select-input-wrap" >
                    <select id="add_existing_position" name="add_existing_position">
                            <option value=" "></option>
                    </select> 
                </div>        

        </div>
        <div class="icon-label-group">
            <div class="icon-label">
                <a rel="action-addnewposition" class="icon-16-add add_existing_job" href="javascript:void(NULL)" onclick="">
                    <span>Add Position</span>
                </a>            
            </div>
        </div>
    </div> 
    <br />

<?php
}
?>