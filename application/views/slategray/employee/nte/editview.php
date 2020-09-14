<?php
if (!isset($buttons)) $buttons = 'template/edit-buttons-default';
$buttons = $this->userinfo['rtheme'] . '/' . $buttons;
?>

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

        <?php
        if (isset($views_before_record_form) && sizeof($views_before_record_form) > 0) :
            foreach ($views_before_record_form as $view) :
                $this->load->view($view);
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
        <form class="style2 edit-view" name="record-form" id="record-form" method="post" enctype="multipart/form-data">
            <input type="hidden" name="record_id" rel="dynamic" id="record_id" value="<?= $this->input->post('record_id') ?>" />
            <input type="hidden" name="prev_search_str" id="prev_search_str" value="<?= $this->input->post('prev_search_str') ?>"/>
            <input type="hidden" name="prev_search_field" id="prev_search_field" value="<?= $this->input->post('prev_search_field') ?>"/>
            <input type="hidden" name="prev_search_option" id="prev_search_option" value="<?= $this->input->post('prev_search_option') ?>"/>
            <input type="hidden" name="prev_search_page" id="prev_search_page" value="<?= $this->input->post('prev_search_page') ?>"/>
<div id="form-div">
            	<div class="" id="fg-213" fg_id="213">
              	<h3 class="form-head">Notice To Explain<a style="font-size: 12px;line-height: 18px;" onclick="toggleFieldGroupVisibility( $( this ) );" class="align-right other-link noborder" href="javascript:void(0)">Hide</a></h3>
                <div class="col-2-form">
                	<div class="form-item odd ">
                  	<label class="label-desc gray" for="employee_id">Employee:</label>
                    <div class="text-input-wrap"><?php echo $nte->employee?></div>
                  </div>
                  <div class="form-item even ">
                  	<label class="label-desc gray" for="ir_id">Status:</label>
                    <div class="textarea-input-wrap"><?php echo $nte->status?></div> 
                  </div>
                  <div class="form-item odd ">
                  	<label class="label-desc gray" for="ir_id">Offense:</label>
                    <div class="textarea-input-wrap"><?php echo $nte->offence?></div> 
                  </div>
                  <div class="form-item odd ">
                  	<label class="label-desc gray" for="date_issued">Issued By:</label>
                    <div class="text-input-wrap"><?php echo $nte->issued_by?></div>
                  </div>
                  <div class="form-item even ">
                  	<label class="label-desc gray" for="date_issued">Date Issued:</label>
                    <div class="text-input-wrap"><?php echo $nte->date_issued?></div>
                  </div>
                  <div class="form-item odd ">
                    <label class="label-desc gray" for="ir_id">Details of Violation:</label>
                    <div class="textarea-input-wrap"><?php echo $nte->details?></div>
                  </div>
                  <div class="form-item even ">
                    <label class="label-desc gray" for="date_replied">Date Replied:</label>
                    <div class="text-input-wrap"><?php echo $nte->date_replied?></div>
                  </div>
                  <div class="form-item odd ">
                  	<label class="label-desc gray" for="explaination">Explanation:<span class="red font-large">*</span></label>
                    <div class="textarea-input-wrap"><textarea class="input-textarea" id="explaination" name="explaination" rows="5"><?php echo $nte->explaination?></textarea></div>
                  </div>
                </div>
                <div class="spacer"></div>
              </div>
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
endif; ?>
<!-- END MAIN CONTENT -->