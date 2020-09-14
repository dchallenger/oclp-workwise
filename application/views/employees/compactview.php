<?php 
if (!isset($buttons)) {
    $buttons = '/template/detail-buttons';
}

$buttons = $this->userinfo['rtheme'] . $buttons;
?>
<!-- start #page-head -->
<div id="page-head" class="page-info">
    <div id="page-title">
        <h2 class="page-title"><span class="title"><?= $this->detailview_title; ?></span></h2>
    </div>
    <div id="page-desc" class="align-left"><p><?= $this->detailview_description ?></p></div>

    <?php
    // Page Nav Structure
    if (isset($pnav))
        echo $pnav;
    ?>
    <div class="clear"></div>
</div><!-- end #page-head -->
<script type="text/javascript">    
    $(document).ready(function () {
        add_layout_switcher(0);   
    });
</script>

<?php $this->load->view($this->userinfo['rtheme'] . '/template/sidebar'); ?>
<div id="body-content-wrap">
    <div class="wizard-leftcol">
        <ul>
            <?php
            if (isset($fieldgroups) && sizeof($fieldgroups) > 0) :
                $load_jqgrid_in_boxy = false;
                $load_ckeditor = false;
                $load_multiselect = false;
                $load_uploadify = false;
                $js = array();
                $ctr = 1;
                foreach ($fieldgroups as $fieldgroup) :
                    ?>
                    <li>
                        <a class="leftcol-control" rel="fg-<?php echo $fieldgroup['fieldgroup_id'] ?>" href="javascript:void(0)"><span><?php echo $ctr++; ?></span><?php echo $fieldgroup['fieldgroup_label']; ?></a>
                    </li>
                    <?php
                endforeach;
            endif;
            ?>
           <div class="layout-switch-toggle align-left" style="margin-top:50px"></div>
        </ul>     
    </div>
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
        <div class="wizard-rightcol">
            <div class="wizard-header">
                <?php if (isset($applicant_name)):?>
                <img src="<?php echo base_url() . $this->userinfo['theme'] ?>/images/wizard-iconpic.png" />
                <h2><?php echo $applicant_name; ?></h2>
                <?php endif;?>
                <div class="page-navigator align-right">
                    <div class="btn-prev-disabled"> <a href="javascript:void(0)"><span>Prev</span></a></div>
                    <div class="btn-prev hidden"> <a onclick="prev_wizard()" href="javascript:void(0)" class="tipsy-autons"><span>Prev</span></a></div>
                    <div class="btn-next"> <a onclick="next_wizard()" href="javascript:void(0)" class="tipsy-autons"><span>Next</span></a></div>
                    <div class="btn-next-disabled hidden"> <a href="javascript:void(0)"><span>Next</span></a></div>
                </div>

                <div class="icon-label-group align-right">
                    <?php echo $this->load->view($buttons);?>
                    <div class="icon-label">
                        <a href="javascript:void(0);" class="icon-16-listback" rel="back-to-list">
                            <span>Back to list</span> </a>
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
                        <div fg_id="<?php echo $fieldgroup['fieldgroup_id'] ?>" id="fg-<?php echo $fieldgroup['fieldgroup_id'] ?>" class="<?php echo $show_wizard_control ? 'wizard-type-form hidden' : '' ?>">
                            <h3 class="form-head"><?= $fieldgroup['fieldgroup_label'] ?></h3>
                            <div class="<?= !empty($fieldgroup['layout']) ? 'col-1-form' : 'col-2-form' ?> view"> <?php
            if ($fieldgroup['detail_customview'] != "" && $fieldgroup['detail_customview_position'] == 1) {
                $this->load->view($this->userinfo['rtheme'] . '/' . $fieldgroup['detail_customview']);
            }
            if (isset($fieldgroup['fields'])) :
                foreach ($fieldgroup['fields'] as $field) :
                    $this->uitype_detail->showFieldDetail($field);
                endforeach;
            endif;
            if ($fieldgroup['detail_customview'] != "" && $fieldgroup['detail_customview_position'] == 3) {
                $this->load->view($this->userinfo['rtheme'] . '/' . $fieldgroup['detail_customview']);
            }
                        ?>
                            </div>
                        </div><?php
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
            <div class="page-navigator align-right">
                <div class="btn-prev-disabled"> <a href="javascript:void(0)"><span>Prev</span></a></div>
                <div class="btn-prev hidden"> <a onclick="prev_wizard()" href="javascript:void(0)" class="tipsy-autons"><span>Prev</span></a></div>
                <div class="btn-next"> <a onclick="next_wizard()" href="javascript:void(0)" class="tipsy-autons"><span>Next</span></a></div>
                <div class="btn-next-disabled hidden"> <a href="javascript:void(0)"><span>Next</span></a></div>
            </div>

            <div class="icon-label-group align-right">
                <?php echo $this->load->view($buttons);?>
                <div class="icon-label">
                    <a href="javascript:void(0);" class="icon-16-listback" rel="back-to-list">
                        <span>Back to list</span> </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <!-- END MAIN CONTENT -->

</div>