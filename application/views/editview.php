<?php 
$record_id = $this->input->post('record_id'); 
if (!isset($buttons)) {
    $buttons = 'template/edit-buttons-default';
}

$buttons = $this->userinfo['rtheme'] . '/' . $buttons;
?>
<!-- start #page-head -->
<div id="page-head" class="page-info">
    <div id="page-title">
        <h2 class="page-title"><span class="title"><?= $this->editview_title; ?></span></h2>
    </div>
    <div id="page-desc" class="align-left"><p><?= $this->editview_description ?></p></div>

    <div class="clear"></div>
</div><!-- end #page-head -->
<?php $this->load->view($this->userinfo['rtheme'] . '/template/sidebar'); ?>
<div id="body-content-wrap">

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
        <div class="form-submit-btn <?php echo $show_wizard_control && isset($fieldgroups) && sizeof($fieldgroups) > 1 ? 'hidden' : '' ?>">
            <?php $this->load->view($buttons)?>            
        </div>
        <div class="clear"></div>
        <div class="page-navigator align-right <?php echo $show_wizard_control ? '' : 'hidden' ?>">
            <div class="btn-prev-disabled"><a href="javascript:void(0)"><span>Prev</span></a></div>
            <div class="btn-prev"><a href="javascript:void(0)" onclick="prev_wizard()"><span>Prev</span></a></div>
            <div class="btn-next"><a href="javascript:void(0)" onclick="next_wizard()"><span>Next</span></a></div>
            <div class="btn-next-disabled"><a href="javascript:void(0)"><span>Next</span></a></div>
        </div>
        <div class="clear"></div>
        <form class="style2 edit-view" name="record-form" id="record-form" method="post" enctype="multipart/form-data">
            <input type="hidden" name="record_id" rel="dynamic" id="record_id" value="<?php echo isset( $duplicate ) ? '-1' : $this->input->post('record_id') ?>" />
            <input type="hidden" name="prev_search_str" id="prev_search_str" value="<?= $this->input->post('prev_search_str') ?>"/>
            <input type="hidden" name="prev_search_field" id="prev_search_field" value="<?= $this->input->post('prev_search_field') ?>"/>
            <input type="hidden" name="prev_search_option" id="prev_search_option" value="<?= $this->input->post('prev_search_option') ?>"/>
            <input type="hidden" name="prev_search_page" id="prev_search_page" value="<?= $this->input->post('prev_search_page') ?>"/>

            <div id="form-div">
                <?php
                if (isset($fieldgroups) && sizeof($fieldgroups) > 0) :
                    $load_jqgrid_in_boxy = false;
                    $load_ckeditor = false;
                    $load_multiselect = false;
                    $load_uploadify = false;
                    $js = array();
                    foreach ($fieldgroups as $fieldgroup) :
                        ?>
                        <div fg_id="<?php echo $fieldgroup['fieldgroup_id'] ?>" id="fg-<?php echo $fieldgroup['fieldgroup_id'] ?>" class="<?php echo $show_wizard_control ? 'wizard-type-form hidden' : '' ?>">
                            <h3 class="form-head"><?= $fieldgroup['fieldgroup_label'] ?><?php if (!$show_wizard_control) : ?><a href="javascript:void(0)" class="align-right other-link noborder" onclick="toggleFieldGroupVisibility( $( this ) );" style="font-size: 12px;line-height: 18px;">Hide</a><?php endif; ?></h3>
                            <div class="<?= !empty($fieldgroup['layout']) ? 'col-1-form' : 'col-2-form' ?>" >
                                <?php
                                if ($fieldgroup['edit_customview'] != "" && $fieldgroup['edit_customview_position'] == 1)
                                    $this->load->view($this->userinfo['rtheme'] . '/' . $fieldgroup['edit_customview']);
                                if (isset($fieldgroup['fields']) && sizeof($fieldgroup['fields']) > 0) :
                                    foreach ($fieldgroup['fields'] as $field) :
                                        //set js validation params
                                        $datatypes = explode('~', $field['datatype']);
                                        $is_mandatory = false;
										$is_readonly = in_array( 'R', $datatypes )? true : false;
                                        if( $is_readonly ) $js['readonly'][] = $field['fieldname'];
                                        foreach ($datatypes as $datatype) {
                                            if ($datatype == "M") {
                                                $js['mandatory'][] = array($field['fieldname'], $field['fieldlabel']);
                                                $is_mandatory = true;
                                            }
                                            if ($datatype == "I" && !$is_readonly)
                                                $js['integer'][] = array($field['fieldname'], $field['fieldlabel']);
                                            if ($datatype == "F" && !$is_readonly)
                                                $js['float'][] = array($field['fieldname'], $field['fieldlabel']);
                                            if ($datatype == "E")
                                                $js['email'][] = array($field['fieldname'], $field['fieldlabel']);
                                            if ($datatype == "U")
                                                $js['url'][] = array($field['fieldname'], $field['fieldlabel']);
                                            if ($datatype == "P")
                                                $js['password'][] = array($field['fieldname'], $field['fieldlabel']);
                                            if (preg_match("/LE/", $datatype) > 0)
                                                $js['le'][] = array($field['fieldname'], $field['fieldlabel'], substr($datatype, 2));
                                            if (preg_match("/LT/", $datatype) > 0)
                                                $js['lt'][] = array($field['fieldname'], $field['fieldlabel'], substr($datatype, 2));
                                            if (preg_match("/GE/", $datatype) > 0)
                                                $js['ge'][] = array($field['fieldname'], $field['fieldlabel'], substr($datatype, 2));
                                            if (preg_match("/GT/", $datatype) > 0)
                                                $js['gt'][] = array($field['fieldname'], $field['fieldlabel'], substr($datatype, 2));
                                        }
                                        $this->uitype_edit->showFieldInput($field, $is_mandatory, $fieldgroup['use_tabindex'], $is_readonly);
                                        if ($field['uitype_id'] == 5 && !$is_readonly)
                                            $js['date'][] = array($field['fieldname'], $field['fieldlabel']);
                                        if ($field['uitype_id'] == 24 && !$is_readonly)
                                            $js['date_from_to'][] = array($field['fieldname'], $field['fieldlabel']);
                                        if ($field['uitype_id'] == 26 && !$is_readonly)
                                            $js['time_start_end'][] = array($field['fieldname'], $field['fieldlabel']);
                                        if ($field['uitype_id'] == 13 && !$is_readonly)
                                            $load_jqgrid_in_boxy = true;
                                        if ($field['uitype_id'] == 16) $js['ckeditor'][] = array($field['fieldname'], $field['fieldlabel']);

                                        if ($field['uitype_id'] == 11) {
                                            $js['single_upload'][] = array($field['fieldname'], $field['fieldlabel']);
                                            $load_uploadify = true;
                                        }
                                        if ($field['uitype_id'] == 20) {
                                            $js['multiple_upload'][] = array($field['fieldname'], $field['fieldlabel'], $field['field_id']);
                                            $load_uploadify = true;
                                        }
                                        if ($field['uitype_id'] == 21) {
                                            $js['multiselect'][] = array($field['fieldname'], $field['fieldlabel']);
                                            $load_multiselect = true;
                                        }
                                    endforeach;
                                endif;
                                if ($fieldgroup['edit_customview'] != "" && $fieldgroup['edit_customview_position'] == 3)
                                    $this->load->view($this->userinfo['rtheme'] . '/' . $fieldgroup['edit_customview']);
                                ?>
                            </div>
                            <div class="spacer"></div>
                        </div><?php
            //create js validation
            if ($show_wizard_control) {
                $js['fg_id'] = $fieldgroup['fieldgroup_id'];
                $this->load->view($this->userinfo['rtheme'] . '/template/edit-wizard-form-js', $js);
                $js = array();
            }
        endforeach;
        //load additional js base on field and validation
        if ($load_jqgrid_in_boxy)
            echo jqgrid_in_boxy();
        if ($load_multiselect)
            echo multiselect_script();
        if ($load_uploadify)
            echo uploadify_script();

        //create js validation
        if (!$show_wizard_control)
            $this->load->view($this->userinfo['rtheme'] . '/template/edit-form-js', $js);
    endif;

    if (sizeof($views) > 0) :
        foreach ($views as $view) :
            $this->load->view($this->userinfo['rtheme'] . '/' . $view);
        endforeach;
    endif;
    ?>
                <div class="clear"></div>
            </div>
        </form>
    <?php
    if (isset($views_outside_record_form) && sizeof($views_outside_record_form) > 0) :
        foreach ($views_outside_record_form as $view) :
            $this->load->view($this->userinfo['rtheme'] . '/' . $view);
        endforeach;
    endif;
    ?>
        <div class="page-navigator align-right <?php echo $show_wizard_control ? '' : 'hidden' ?>">
            <div class="btn-prev-disabled"><a href="javascript:void(0)"><span>Prev</span></a></div>
            <div class="btn-prev"><a href="javascript:void(0)" onclick="prev_wizard()"><span>Prev</span></a></div>
            <div class="btn-next"><a href="javascript:void(0)" onclick="next_wizard()"><span>Next</span></a></div>
            <div class="btn-next-disabled"><a href="javascript:void(0)"><span>Next</span></a></div>
        </div>
        <div class="clear"></div>
        <div class="form-submit-btn <?php echo $show_wizard_control && isset($fieldgroups) && sizeof($fieldgroups) > 1 ? 'hidden' : '' ?>">
            <?php $this->load->view($buttons)?>
        </div>
        <div class="clear"></div><?php
endif;
?>
    <!-- END MAIN CONTENT -->
</div>