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
<?php 
$annual_manpower_planning = $this->db->dbprefix('annual_manpower_planning');
$annual_manpower_planning_id = $_POST['record_id'];
$annual_manpower_planning_header = $this->db->query("SELECT * FROM {$annual_manpower_planning}  WHERE annual_manpower_planning_id = '{$annual_manpower_planning_id}'")->row();
switch ($annual_manpower_planning_header->category_id) {
    case 1: //by division
        $category = $this->db->get_where('user_company_division', array('division_id' => $annual_manpower_planning_header->category_value_id))->row();
        $category_value = $category->division;
        break;
    case 3: //by group
        $category = $this->db->get_where('group_name', array('group_name_id' => $annual_manpower_planning_header->category_value_id))->row();
        $category_value = $category->group_name;
        break;
    case 4: //by department
        $category = $this->db->get_where('user_company_department', array('department_id' => $annual_manpower_planning_header->category_value_id))->row();
        $category_value = $category->department;
        break;
    case 2: //by project
        $category = $this->db->get_where('project_name', array('project_name_id' => $annual_manpower_planning_header->category_value_id))->row();
        $category_value =  $category->project_name;
        break;                                                                          
}

$view_click = '';
$annual_status_id = '';
if($_POST['record_id'] != -1)
{
    $annual_status_id = $annual_manpower_planning_header->annual_manpower_planning_status_id;
    $approvers = explode(',', $annual_manpower_planning_header->approver_id);

    if($annual_manpower_planning_header->created_by == $this->userinfo['user_id'])
    {
        $view_click = 'editor';
    }
    elseif( $annual_manpower_planning_header->annual_manpower_planning_status_id == 2)
    {
        if ($this->is_superadmin) {
            $view_click = 'approver';   
        }elseif ((in_array($this->userinfo['user_id'], $approvers))) {

            $approver = $this->db->get_where('annual_manpower_planning_approver', array('amp_id' => $annual_manpower_planning_header->annual_manpower_planning_id, 'approver' => $this->userinfo['user_id']))->row();
            if ($approver->status == 2 ) {
                $view_click = 'approver';    
            }
            
        }

    }
    elseif($annual_manpower_planning_header->annual_manpower_planning_status_id == 6 && $this->user_access[$this->module_id]['post'] == 1 ){

        $view_click = 'reviewer'; 

    }
    elseif( ( $annual_manpower_planning_header->annual_manpower_planning_status_id != 6 && $annual_manpower_planning_header->annual_manpower_planning_status_id != 2 ) && ( $annual_manpower_planning_header->annual_user_division_id == $this->userinfo['user_id'] || $this->user_access[$this->module_id]['post'] == 1 ) ){

        $view_click = 'admin_view';

    }

}
?>
<?php if (isset($error)) : ?>
        <div id="message_box" class="attention" style="padding-left: 60px;width:60%;margin: 0 auto;">
            <img src="<?= base_url() . $this->userinfo['theme']; ?>/images/exclamation-big.png" alt="" >
            <h3 style="margin: 0.3em 0 0.5em 0">Oops! <?= $error ?></h3>

            <p><?= $error2 ?></p>
        </div>
<? else : ?>
        <div class="form-submit-btn">
            <div class="icon-label-group">
               <?php
                if( $view_click == 'approver')
                {
                ?>
                
                  <!--   <div class="icon-label">
                        <a class="icon-16-edit" href="javascript:void(0);" onclick="edit()">
                            <span>Edit</span>
                        </a>   
                        <a class="icon-16-export" href="javascript:void(0)" onclick="export_list();">
                            <span>Export to excel</span>
                        </a>          
                    </div>-->
                
                    <div class="icon-label">
                        <a class="icon-16-approve approve-class status-buttons" href="javascript:void(0)" onclick="ajax_save_custom(<?=$_POST['record_id'];?>,'approve')">
                            <span>Approve</span>
                        </a>
                    </div>
                    <div class="icon-label">
                        <a class="icon-16-disapprove disapprove-class status-buttons" href="javascript:void(0)" onclick="ajax_save_custom(<?=$_POST['record_id'];?>,'disapprove')">
                            <span>Re-evaluate</span>
                        </a>
                    </div>
                <?php
                }
                
                if( $view_click == 'editor' && ( $annual_status_id == 1 || $annual_status_id == 4 ) )
                {
                    ?>

                    <div class="icon-label">
                         <a class="icon-16-edit" href="javascript:void(0);" onclick="edit()">
                            <span>Edit</span>
                        </a>   <!--
                        <a class="icon-16-export" href="javascript:void(0)" onclick="export_list();">
                            <span>Export to excel</span>
                        </a>          -->
                    </div>

                <?php
                }

                if( $view_click == 'editor' && ( $annual_status_id != 1 || $annual_status_id != 4 ) ){

                ?>

                    <!-- <div class="icon-label">
                        <a class="icon-16-export" href="javascript:void(0)" onclick="export_list();">
                            <span>Export to excel</span>
                        </a>          
                    </div> -->

                    <?php

            }

                if( $view_click == 'reviewer' ){
                    ?>

                   <!--  <div class="icon-label">
                        <a class="icon-16-edit" href="javascript:void(0);" onclick="edit()">
                            <span>Edit</span>
                        </a>   
                        <a class="icon-16-export" href="javascript:void(0)" onclick="export_list();">
                            <span>Export to excel</span>
                        </a>          
                    </div>-->

                    <div class="icon-label">
                        <a class="icon-16-tick" href="javascript:void(0)" onclick="change_status(<?php echo $annual_manpower_planning_header->annual_manpower_planning_id; ?>,2,true);">
                            <span>Mark as Reviewed</span>
                        </a>          
                    </div>

                    <div class="icon-label">
                        <a class="icon-16-approve approve-class status-buttons" href="javascript:void(0)" onclick="ajax_save_custom(<?=$_POST['record_id'];?>,'approve')">
                            <span>Approve</span>
                        </a>
                    </div>
                    <div class="icon-label">
                        <a class="icon-16-disapprove disapprove-class status-buttons" href="javascript:void(0)" onclick="ajax_save_custom(<?=$_POST['record_id'];?>,'disapprove')">
                            <span>Re-evaluate</span>
                        </a>
                    </div>

                    <?php
                }

                if( $view_click == 'admin_view' ){
                    ?>

                    <!-- <div class="icon-label">
                        <a class="icon-16-edit" href="javascript:void(0);" onclick="edit()">
                            <span>Edit</span>
                        </a>  
                        <a class="icon-16-export" href="javascript:void(0)" onclick="export_list();">
                            <span>Export to excel</span>
                        </a>          
                    </div> -->

                    <?php
                }

                ?>
                <div class="icon-label">
                    <a rel="back-to-list" class="icon-16-listback" href="javascript:void(0);">
                        <span>Back to list</span>
                    </a>
                </div>
            </div>
        </div>
        <form class="style2 detail-view" name="record-form" id="record-form" method="post" enctype="multipart/form-data">
            <input type="hidden" name="record_id" id="record_id" value="<?= $this->input->post('record_id') ?>" />
            <input type="hidden" name="return_record_id" id="return_record_id" value="<?= $this->input->post('record_id') ?>" />
            <input type="hidden" name="previous_page" id="previous_page" value="<?= base_url() . $this->module_link ?>/detail"/>        
            <input type="hidden" name="prev_search_str" id="prev_search_str" value="<?= $this->input->post('prev_search_str') ?>"/> 
            <input type="hidden" name="prev_search_field" id="prev_search_field" value="<?= $this->input->post('prev_search_field') ?>"/>
            <input type="hidden" name="prev_search_option" id="prev_search_option" value="<?= $this->input->post('prev_search_option') ?>"/>
    <?php
    if (isset($fieldgroups) && sizeof($fieldgroups) > 0) :
        foreach ($fieldgroups as $fieldgroup) :
            ?>
                    <h3 class="form-head"><?= $fieldgroup['fieldgroup_label'] ?></h3>
                    <div class="<?= !empty($fieldgroup['layout']) ? 'col-1-form' : 'col-2-form' ?> view"> <?php
            if ($fieldgroup['detail_customview'] != "" && $fieldgroup['detail_customview_position'] == 1) {
                $this->load->view($this->userinfo['rtheme'] . '/' . $fieldgroup['detail_customview']);
            }
            if (isset($fieldgroup['fields'])) :
                foreach ($fieldgroup['fields'] as $field) :
                    if ($field['fieldname'] == "year"): ?>
                        <div class="form-item view <?= ($field['sequence'] % 2 == 0 ? 'even' : 'odd') ?>">
                            <label for="<?= $field['fieldname'] ?>" class="label-desc view gray"><?= $field['fieldlabel'] ?>:</label>
                            <div class="text-input-wrap">
                                <?php echo $year; ?>
                            </div>      
                        </div><?php
                    elseif ($field['fieldname'] == "category_value_id"): ?>
                        <div class="form-item view <?= ($field['sequence'] % 2 == 0 ? 'even' : 'odd') ?>">
                            <label for="<?= $field['fieldname'] ?>" class="label-desc view gray"><?= $field['fieldlabel'] ?>:</label>
                            <div class="text-input-wrap">
                                <?php echo $category_value; ?>
                            </div>      
                        </div><?php                        
                    else:
                        $this->uitype_detail->showFieldDetail($field);                        
                    endif;
                endforeach;
            endif;
            if ($fieldgroup['detail_customview'] != "" && $fieldgroup['detail_customview_position'] == 3) {
                $this->load->view($this->userinfo['rtheme'] . '/' . $fieldgroup['detail_customview']);
            }
            ?>
                    </div> <?php
        endforeach;
    endif;

    if (sizeof($views) > 0) :
        foreach ($views as $view) :
            $this->load->view($this->userinfo['rtheme'] . '/' . $view);
        endforeach;
    endif;
    ?>
        </form>
        <div class="clear"></div>
        <div class="form-submit-btn">
            <div class="icon-label-group">
            
            <?php
            if( $view_click == 'approver')
            {
            ?>
                <!--<div class="icon-label">
                     <a class="icon-16-edit" href="javascript:void(0);" onclick="edit()">
                        <span>Edit</span>
                    </a>   
                    <a class="icon-16-export" href="javascript:void(0)" onclick="export_list();">
                        <span>Export to excel</span>
                    </a>          
                </div>-->
                <div class="icon-label">
                    <a class="icon-16-approve approve-class status-buttons" href="javascript:void(0)" onclick="ajax_save_custom(<?=$_POST['record_id'];?>,'approve')">
                        <span>Approve</span>
                    </a>
                </div>
                <div class="icon-label">
                    <a class="icon-16-disapprove disapprove-class status-buttons" href="javascript:void(0)" onclick="ajax_save_custom(<?=$_POST['record_id'];?>,'disapprove')">
                        <span>Re-evaluate</span>
                    </a>
                </div>
            <?php
            }
            
            if( $view_click == 'editor' && ( $annual_status_id == 1 || $annual_status_id == 4 ) )
                {
                    ?>

                    <!--<div class="icon-label">
                         <a class="icon-16-edit" href="javascript:void(0);" onclick="edit()">
                            <span>Edit</span>
                        </a>   
                        <a class="icon-16-export" href="javascript:void(0)" onclick="export_list();">
                            <span>Export to excel</span>
                        </a>          
                    </div>-->

                <?php
                }

            if( $view_click == 'editor' && ( $annual_status_id != 1 || $annual_status_id != 4 ) ){

                ?>

                    <!-- <div class="icon-label">
                        <a class="icon-16-export" href="javascript:void(0)" onclick="export_list();">
                            <span>Export to excel</span>
                        </a>          
                    </div> -->

                    <?php

            }

            if( $view_click == 'reviewer' ){
                    ?>

                    <!--<div class="icon-label">
                         <a class="icon-16-edit" href="javascript:void(0);" onclick="edit()">
                            <span>Edit</span>
                        </a>   
                        <a class="icon-16-export" href="javascript:void(0)" onclick="export_list();">
                            <span>Export to excel</span>
                        </a>          
                    </div>-->
                    <div class="icon-label">
                        <a class="icon-16-tick" href="javascript:void(0)" onclick="change_status(<?php echo $annual_manpower_planning_header->annual_manpower_planning_id; ?>,2);">
                            <span>Mark as Reviewed</span>
                        </a>          
                    </div>

                    <div class="icon-label">
                        <a class="icon-16-approve approve-class status-buttons" href="javascript:void(0)" onclick="ajax_save_custom(<?=$_POST['record_id'];?>,'approve')">
                            <span>Approve</span>
                        </a>
                    </div>
                    <div class="icon-label">
                        <a class="icon-16-disapprove disapprove-class status-buttons" href="javascript:void(0)" onclick="ajax_save_custom(<?=$_POST['record_id'];?>,'disapprove')">
                            <span>Re-evaluate</span>
                        </a>
                    </div>

                    <?php
                }

                if( $view_click == 'admin_view' ){
                    ?>

                   <!-- <div class="icon-label">
                         <a class="icon-16-edit" href="javascript:void(0);" onclick="edit()">
                            <span>Edit</span>
                        </a>   
                        <a class="icon-16-export" href="javascript:void(0)" onclick="export_list();">
                            <span>Export to excel</span>
                        </a>          
                    </div>-->

                    <?php
                }
            ?>
            <div class="icon-label">
                    <a rel="back-to-list" class="icon-16-listback" href="javascript:void(0);">
                        <span>Back to list</span>
                    </a>
                </div>
            </div>
        </div><?php
endif;
?>
    <!-- END MAIN CONTENT -->

</div>