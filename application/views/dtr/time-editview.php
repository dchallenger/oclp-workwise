<?php 
$record_id = $this->input->post('record_id'); 
if (!isset($buttons)) {
    $buttons = 'template/edit-buttons-default';
}

$buttons = $this->userinfo['rtheme'] . '/' . $buttons;
?>
        <div class="form-submit-btn <?php echo $show_wizard_control && isset($fieldgroups) && sizeof($fieldgroups) > 1 ? 'hidden' : '' ?>">
            <?php $this->load->view($buttons)?>            
        </div>

            <div id="form-div">

                <div id="fg-1" class="" fg_id="1">

                    <h3 class="form-head">

                    <!-- <span style="margin-left:44%"> -->
                        Time Display
                    <!-- </span> -->

                    <!-- <a class="align-right other-link noborder" style="font-size: 12px;line-height: 18px;" onclick="toggleFieldGroupVisibility( $( this ) );" href="javascript:void(0)">Hide</a> -->

                    </h3>

                <span style="margin-left:54%"><b><?= date($this->config->item('display_date_format'), strtotime(date('Y-m-d'))); ?></b></span>

                <div id="digiclock" style="margin-left:30%"></div>

                <div class="spacer"></div>

            </div>
        </div>

                
                