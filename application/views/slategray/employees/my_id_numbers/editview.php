<?php 
$record_id = $this->input->post('record_id'); 
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
            <input type="hidden" name="record_id" rel="dynamic" id="record_id" value="<?php echo $employee_record->user_id ?>" />
            <input type="hidden" name="prev_search_str" id="prev_search_str" value="<?= $this->input->post('prev_search_str') ?>"/>
            <input type="hidden" name="prev_search_field" id="prev_search_field" value="<?= $this->input->post('prev_search_field') ?>"/>
            <input type="hidden" name="prev_search_option" id="prev_search_option" value="<?= $this->input->post('prev_search_option') ?>"/>
            <input type="hidden" name="prev_search_page" id="prev_search_page" value="<?= $this->input->post('prev_search_page') ?>"/>

            <div id="form-div">
                <div class="wizard-form"> 
                    <div class="col-2-form">
                        <div class="form-item odd ">
                            <label class="label-desc gray" for="sss">SSS:</label>
                            <div class="text-input-wrap"><input type="text" class="input-text" value="<?php echo $employee_record->sss ?>" id="sss" name="sss"></div>                                    
                        </div>                
                        <div class="form-item even ">
                            <label class="label-desc gray" for="tin">TIN:</label>
                            <div class="text-input-wrap"><input type="text" class="input-text" value="<?php echo $employee_record->tin ?>" id="tin" name="tin"></div>                                    
                        </div>             
<!--                         <div class="form-item odd ">
                            <label class="label-desc gray" for="sss_existing_loan">With Existing Loan:</label>
                            <div class="radio-input-wrap">
                                <input type="radio" class="input-radio" value="1" id="sss_existing_loan-yes" name="sss_existing_loan" <?php echo ($employee_record->sss_existing_loan == 1 ? 'checked="checked"': '') ?>>
                                <label class="check-radio-label gray" for="sss_existing_loan-yes">Yes</label>
                                <input type="radio" class="input-radio" value="0" id="sss_existing_loan-no" name="sss_existing_loan" <?php echo ($employee_record->sss_existing_loan == 0 ? 'checked="checked"': '') ?>>
                                <label class="check-radio-label gray" for="sss_existing_loan-no">No</label>
                            </div>                                    
                        </div>  -->               
<!--                         <div class="form-item even ">
                            <label class="label-desc gray" for="tin_with_bir">With BIR Form 2316:</label>
                            <div class="radio-input-wrap">
                                <input type="radio" class="input-radio" value="1" id="tin_with_bir-yes" name="tin_with_bir" <?php echo ($employee_record->tin_with_bir == 1 ? 'checked="checked"': '') ?>>
                                <label class="check-radio-label gray" for="tin_with_bir-yes">Yes</label>
                                <input type="radio" class="input-radio" value="0" id="tin_with_bir-no" name="tin_with_bir" <?php echo ($employee_record->tin_with_bir == 0 ? 'checked="checked"': '') ?>>
                                <label class="check-radio-label gray" for="tin_with_bir-no">No</label>
                            </div>
                        </div> -->
<!--                         <div class="form-item odd ">
                            <label class="label-desc gray" for="sss_current_balance">Current Balance:</label>
                            <div class="text-input-wrap"><input type="text" class="input-text text-right" value="<?php echo $employee_record->sss_current_balance ?>" id="sss_current_balance" name="sss_current_balance"></div>                                    
                        </div> -->
                        <div class="form-item odd ">
                            <label class="label-desc gray" for="pagibig">Pag-Ibig:</label>
                            <div class="text-input-wrap"><input type="text" class="input-text" value="<?php echo $employee_record->pagibig ?>" id="pagibig" name="pagibig"></div>                                    
                        </div>                            
                        <div class="form-item even ">
                            <label class="label-desc gray" for="tax_status">Tax Status:</label>
                            <div class="select-input-wrap">
                                <select id="tax_status" name="tax_status">
                                    <option value="">Select…</option>
                                    <option value="1">Single/Married</option>
                                    <option value="2">Single/Married with 1 dependent/s</option>
                                    <option value="3">Single/Married with 2 dependent/s</option>
                                    <option value="4">Single/Married with 3 dependent/s</option>
                                    <option value="5">Single/Married with 4 dependent/s</option>
                                </select>
                            </div>
                        </div>
<!--                         <div class="form-item odd ">
                            <label class="label-desc gray" for="sss_balance_date">As of:</label>
                            <div class="text-input-wrap">
                                <input type="hidden" id="sss_balance_date" name="sss_balance_date" value="<?php echo $employee_record->sss_balance_date ?>">
                                <input type="text" id="sss_balance_date-temp" style="width:30%;" name="sss_balance_date-temp" class="input-text" value="<?php echo $employee_record->sss_balance_date ?>">
                            </div>                                    
                        </div>   -->            
                        <div class="form-item odd ">
                            <label class="label-desc gray" for="philhealth">Philhealth:</label>
                            <div class="text-input-wrap"><input type="text" class="input-text" value="<?php echo $employee_record->philhealth ?>" id="philhealth" name="philhealth"></div>                                    
                        </div>                          
                        <div class="form-item even ">
                            <label class="label-desc gray" for="bank_account_no">Bank Account No:</label>
                            <div class="text-input-wrap"><input type="text" class="input-text" value="" id="bank_account_no" name="bank_account_no"></div>                                    
                        </div>                
<!--                         <div class="form-item odd ">
                            <label class="label-desc gray" for="pagibig_existing_load">With Existing Loan:</label>
                            <div class="radio-input-wrap">
                                <input type="radio" class="input-radio" value="1" id="pagibig_existing_load-yes" name="pagibig_existing_load">
                                <label class="check-radio-label gray" for="pagibig_existing_load-yes">Yes</label>
                                <input type="radio" checked="checked" class="input-radio" value="0" id="pagibig_existing_load-no" name="pagibig_existing_load">
                                <label class="check-radio-label gray" for="pagibig_existing_load-no">No</label>
                            </div>                                    
                        </div>                
                        <div class="form-item odd ">
                            <label class="label-desc gray" for="pagibig_current_balance">Current Balance:</label>
                            <div class="text-input-wrap"><input type="text" class="input-text" value="<?php echo $employee_record->pagibig_current_balance ?>" id="pagibig_current_balance" name="pagibig_current_balance"></div>                                    
                        </div>              
                        <div class="form-item odd ">
                            <label class="label-desc gray" for="pagibig_balance_date">As of:</label>
                            <div class="text-input-wrap">
                                <input type="hidden" id="pagibig_balance_date" name="pagibig_balance_date" value="<?php echo $employee_record->pagibig_balance_date ?>">
                                <input type="text" id="pagibig_balance_date-temp" style="width:30%;" name="pagibig_balance_date-temp" class="input-text" value="<?php echo $employee_record->pagibig_balance_date ?>">                                    
                            </div>
                        </div> -->
                    </div>         
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