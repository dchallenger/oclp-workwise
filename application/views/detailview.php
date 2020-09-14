<!-- start #page-head -->
<div id="page-head" class="page-info">
    <div id="page-title">
        <h2 class="page-title"><span class="title"><?= $this->detailview_title; ?></span></h2>    
    </div>  
    <div id="page-desc" class="align-left"><p><?= $this->detailview_description ?></p></div>                        
    
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
        <div class="form-submit-btn">
            <div class="icon-label-group">
                <div class="icon-label">
                    <a class="icon-16-edit" href="javascript:void(0);" onclick="edit()">
                        <span>Edit</span>
                    </a>            
                </div>
            </div>
            <div class="or-cancel">
                <span class="or">or</span>
                <a class="cancel" href="javascript:void(0)" rel="action-back">Go Back</a>
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
                    $this->uitype_detail->showFieldDetail($field);
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
                <div class="icon-label">
                    <a class="icon-16-edit" href="javascript:void(0);" onclick="edit()">
                        <span>Edit</span>
                    </a>            
                </div>
            </div>
            <div class="or-cancel">
                <span class="or">or</span>
                <a class="cancel" href="javascript:void(0)" rel="action-back">Go Back</a>
            </div>
        </div><?php
endif;
?>
    <!-- END MAIN CONTENT -->

</div>