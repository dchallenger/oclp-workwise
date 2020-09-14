<?php
	if (!isset($buttons)) $buttons = 'template/detail-buttons-default';
	$buttons = $this->userinfo['rtheme'] . '/' . $buttons;
?>
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
        <?php $this->load->view($buttons)?>
        <form class="style2 detail-view" name="record-form" id="record-form" method="post" enctype="multipart/form-data">
            <input type="hidden" name="record_id" id="record_id" value="<?= $this->input->post('record_id') ?>" />
            <input type="hidden" name="return_record_id" id="return_record_id" value="<?= $this->input->post('record_id') ?>" />
            <input type="hidden" name="previous_page" id="previous_page" value="<?= base_url() . $this->module_link ?>/detail"/>        
            <input type="hidden" name="prev_search_str" id="prev_search_str" value="<?= $this->input->post('prev_search_str') ?>"/> 
            <input type="hidden" name="prev_search_field" id="prev_search_field" value="<?= $this->input->post('prev_search_field') ?>"/>
            <input type="hidden" name="prev_search_option" id="prev_search_option" value="<?= $this->input->post('prev_search_option') ?>"/>
            <h3 class="form-head">Employee</h3>
            <div class="col-2-form view">
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="employee_id">Employee:</label>
                    <div class="text-input-wrap">
                        <?php $emp = $this->hdicore->_get_userinfo($balance->employee_id);
                        echo $emp->firstname.' '.$emp->lastname;
                        ?>
                    </div>      
                </div>
                <div class="form-item view even ">
                    <label class="label-desc view gray" for="year">Year:</label>
                    <div class="text-input-wrap"><?php echo $balance->year?></div>      
                </div>
            </div>

            <table id="listview-list" class="default-table boxtype" style="width:100%">
                <thead>
                    <tr>
                        <th>Type of Leave</th>
                        <th>Old</th>
                        <th>New</th>
                        <th>Used</th>
                        <th>Remaining</th>
                        <th>Paid</th>
                    </tr>
                </thead>
                <tbody id="listview-tbody">
                    <tr class="odd">
                        <td>Vacation Leave</td>
                        <td align="center"><?php echo $balance->carried_vl?></td>
                        <td align="center"><?php echo $balance->vl?></td>
                        <td align="center"><?php echo $balance->vl_used?></td>
                        <td align="center"><?php echo ($balance->vl + $balance->carried_vl) - $balance->vl_used?></td>
                        <td align="center"></td>
                    </tr>
                    <tr  class="even">
                        <td>Sick Leave</td>
                        <td align="center"><?php echo $balance->carried_sl?></td>
                        <td align="center"><?php echo $balance->sl?></td>
                        <td align="center"><?php echo $balance->sl_used?></td>
                        <td align="center"><?php echo ($balance->sl + $balance->carried_sl) - $balance->sl_used?></td>
                        <td align="center"><?php echo $balance->paid_sl?></td>
                    </tr>
                    <?php if($this->config->item('client_no') != 2): ?>
                    <tr class="odd">
                        <td>Emergency Leave</td>
                        <td align="center"></td>
                        <td align="center"><?php echo $balance->el?></td>
                        <td align="center"><?php echo $balance->el_used?></td>
                        <td align="center"><?php echo $balance->el- $balance->el_used?></td>
                        <td align="center"></td>
                    </tr>
                <?php endif; ?>
                    <?php 
                        $emp = $this->hdicore->_get_userinfo($balance->employee_id);
                    ?>                    
                    <tr  class="even">
                        <td><?php echo ($emp->sex == "male" ? "Paternity Leave" : "Maternity Leave") ?></td>
                        <td align="center"></td>
                        <td align="center"><?php echo $balance->mpl?></td>
                        <td align="center"><?php echo $balance->mpl_used?></td>
                        <td align="center"><?php echo $balance->mpl - $balance->mpl_used?></td>
                        <td align="center"></td>
                    </tr>
<!--                     <tr  class="odd">
                        <td>Bereavement Leave</td>
                        <td align="center"><?php echo $balance->bl?></td>
                        <td align="center"><?php echo $balance->bl_used?></td>
                        <td align="center"><?php echo $balance->bl- $balance->bl_used?></td>
                    </tr> -->
                </tbody>
            </table>
        </form>
        <div class="spacer"></div>
        <div class="clear"></div>
        <?php $this->load->view($buttons);
endif;
?>
    <!-- END MAIN CONTENT -->