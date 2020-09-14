<?php 
    //dbug($this->userinfo);
    $annual_manpower_planning = $this->db->dbprefix('annual_manpower_planning');
    $annual_manpower_planning_id = $_POST['record_id'];
    $annual_manpower_planning_header = $this->db->query("SELECT * FROM {$annual_manpower_planning}  WHERE annual_manpower_planning_id = '{$annual_manpower_planning_id}'")->row();
    
    $view_click = '';
    $annual_status_id = '';
    if($_POST['record_id'] != -1)
    {
        $annual_status_id = $annual_manpower_planning_header->annual_manpower_planning_status_id;
        if($annual_manpower_planning_header->created_by == $this->userinfo['user_id'])
        {
            $view_click = 'editor';
        }
        if( ( $annual_manpower_planning_header->annual_user_division_id == $this->userinfo['user_id'] ) && ( $annual_manpower_planning_header->created_by != $this->userinfo['user_id'] ) )
        {
            $view_click = 'approver';   
        }
    }

if($_POST['record_id'] == -1)
{
?>
<div class="icon-label-group">
    <div class="icon-label">
        <a rel="record-save" class="icon-16-disk" href="javascript:void(0);" onclick="validate_ajax_save('', <?php echo $show_wizard_control ? 1 : 0 ?>)">
            <span>Save</span>
        </a>
    </div>
    <div class="icon-label">
        <a rel="record-save-back" class="icon-16-disk-back" href="javascript:void(0);" onclick="validate_ajax_save('back', <?php echo $show_wizard_control ? 1 : 0 ?>)">
            <span>Save &amp; Back</span>
        </a>
    </div>
    
    <div class="icon-label">
        <a href="javascript:void(0)" onclick="validate_ajax_save('email', <?php echo $show_wizard_control ? 1 : 0 ?>, goto_detail)" class="icon-16-disk-back" rel="record-save-email">
            <span>Send Request</span>
        </a>
    </div>
</div>
<?php
}
elseif( $view_click == 'editor' )
{
?>
<div class="icon-label-group">
    <?php
    if($annual_manpower_planning_header->annual_manpower_planning_status_id == 1 || $annual_manpower_planning_header->annual_manpower_planning_status_id == 4)
    {
    ?>
    <div class="icon-label">
        <a rel="record-save" class="icon-16-disk" href="javascript:void(0);" onclick="validate_ajax_save('', <?php echo $show_wizard_control ? 1 : 0 ?>)">
            <span>Save</span>
        </a>
    </div>
    <div class="icon-label">
        <a rel="record-save-back" class="icon-16-disk-back" href="javascript:void(0);" onclick="validate_ajax_save('back', <?php echo $show_wizard_control ? 1 : 0 ?>)">
            <span>Save &amp; Back</span>
        </a>
    </div>    
    <div class="icon-label">
        <a href="javascript:void(0)" onclick="validate_ajax_save('email', <?php echo $show_wizard_control ? 1 : 0 ?>, goto_detail)" class="icon-16-disk-back" rel="record-save-email">
            <span>Send Request</span>
        </a>
    </div>
    <?php
    }
    elseif($annual_manpower_planning_header->annual_manpower_planning_status_id == 2)
    {
    ?>
    <div class="icon-label">
        <a href="javascript:void(0)" onclick="validate_ajax_save('email', <?php echo $show_wizard_control ? 1 : 0 ?>, goto_detail)" class="icon-16-disk-back" rel="record-save-email">
            <span>Resend Request</span>
        </a>
    </div>
    <?php
    }
    ?>
</div>
<?php
}
else if( $view_click == 'approver')
{
?>
<div class="icon-label-group">
    <div class="icon-label">
        <a class="icon-16-approve approve-class status-buttons" href="javascript:void(0)" onclick="ajax_save_custom(<?=$_POST['record_id'];?>,'approve')">
            <span>Approve</span>
        </a>
    </div>
    <div class="icon-label">
        <a class="icon-16-disapprove disapprove-class status-buttons" href="javascript:void(0)" onclick="ajax_save_custom(<?=$_POST['record_id'];?>,'disapprove')">
            <span>Disapprove</span>
        </a>
    </div>
</div>
<?php
}
?>
<div class="or-cancel">
    <span class="or">or</span>
    <a class="cancel" href="javascript:void(0)" onclick="backtolistview()">Cancel</a>
</div>
<input type="hidden" name="annual_status_id" id="annual_status_id" value="<?=$annual_status_id;?>">