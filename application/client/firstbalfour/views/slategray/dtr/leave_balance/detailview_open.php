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
                        <th>Previous Year Balance</th>
                        <th>Earned Credits <?php echo $balance->year ?></th>
                        <th>Used</th>
                        <th>Remaining</th>
                        <th>Paid</th>
                    </tr>
                </thead>
                <tbody id="listview-tbody">
                    <tr class="odd">
                        <td>Vacation Leave</td>
                        <td align="center" rowspan="2" style="vertical-align:middle;"><?php echo number_format($balance->carried_vl,2,'.',',')?></td>
                        <td align="center" rowspan="2" style="vertical-align:middle;"><?php echo number_format($balance->vl,2,'.',',')?></td>
                        <td align="center"><?php echo number_format($balance->vl_used,2,'.',',')?></td>
                        <td align="center" rowspan="2" style="vertical-align:middle;"><?php echo number_format(($balance->vl + $balance->carried_vl) - ($balance->vl_used+$balance->el_used),2,'.',',')?></td>
                        <td align="center" rowspan="2" style="vertical-align:middle;">0.00</td>
                    </tr>
                    <tr class="odd">
                        <td>Emergency Leave</td>
                        <td align="center"><?php echo number_format($balance->el_used,2,'.',',')?></td>
                    </tr>
                    <tr  class="even">
                        <td>Sick Leave</td>
                        <td align="center"><?php echo number_format($balance->carried_sl,2,'.',',')?></td>
                        <td align="center"><?php echo number_format($balance->sl,2,'.',',')?></td>
                        <td align="center"><?php echo number_format($balance->sl_used,2,'.',',')?></td>
                        <td align="center"><?php echo number_format(($balance->sl + $balance->carried_sl) - $balance->sl_used,2,'.',',')?></td>
                        <td align="center"><?php echo number_format($balance->paid_sl,2,'.',',')?></td>
                    </tr>
                    <!-- <tr  class="odd">
                        <td>Birthday Leave</td>
                        <td align="center"><?php echo number_format($balance->carried_bl,2,'.',',')?></td>
                        <td align="center"><?php echo number_format($balance->bl,2,'.',',')?></td>
                        <td align="center"><?php echo number_format($balance->bl_used,2,'.',',')?></td>
                        <td align="center"><?php echo number_format(($balance->bl) - $balance->bl_used,2,'.',',')?></td>
                        <td align="center"><?php echo number_format($balance->paid_bil,2,'.',',')?></td>                        
                    </tr>  -->                   
                    <?php if($this->config->item('client_no') != 2): ?>
                    
                    <?php endif; ?>
                    <?php 
                        $emp = $this->hdicore->_get_userinfo($balance->employee_id);
                    ?>                    
                    <tr  class="odd">
                        <td><?php echo ($emp->sex == "male" ? "Paternity Leave" : "Maternity Leave") ?></td>
                        <td align="center">0.00</td>
                        <td align="center"><?php echo ($balance->mpl != '' ? number_format($balance->mpl,2,'.',',') : '0.00')?></td>
                        <td align="center"><?php echo number_format($balance->mpl_used,2,'.',',')?></td>
                        <td align="center"><?php echo number_format($balance->mpl - $balance->mpl_used,2,'.',',') ?></td>
                        <td align="center">0.00</td>
                    </tr>
<!--                     <tr  class="odd">
                        <td>Base-off Leave</td>
                        <td align="center"><?php echo number_format($balance->bol,2,'.',',')?></td>
                        <td align="center"><?php echo number_format($balance->bol_used,2,'.',',')?></td>
                        <td align="center"><?php echo number_format($balance->bol_used,2,'.',',')?></td>
                        <td align="center"><?php echo number_format($balance->bol - $balance->bol_used,2,'.',',')?></td>
                        <td align="center">0.00</td>                        
                    </tr>                    
                    <tr  class="odd">
                        <td>Service Incentive Leave</td>
                        <td align="center"><?php echo number_format($balance->sil,2,'.',',')?></td>
                        <td align="center"><?php echo number_format($balance->sil_used,2,'.',',')?></td>
                        <td align="center"><?php echo number_format($balance->sil_used,2,'.',',')?></td>
                        <td align="center"><?php echo number_format($balance->sil - $balance->sil_used,2,'.',',')?></td>
                        <td align="center">0.00</td>                        
                    </tr>
                    <tr  class="odd">
                        <td>Union Leave</td>
                        <td align="center"><?php echo number_format($balance->ul,2,'.',',')?></td>
                        <td align="center"><?php echo number_format($balance->ul_used,2,'.',',')?></td>
                        <td align="center"><?php echo number_format($balance->ul_used,2,'.',',')?></td>
                        <td align="center"><?php echo number_format($balance->ul - $balance->ul_used,2,'.',',')?></td>
                        <td align="center">0.00</td>                        
                    </tr>    -->                                     
                </tbody>
            </table>
        </form>
        <div class="spacer"></div>
        <div class="clear"></div>
        <?php $this->load->view($buttons);
endif;
?>
    <!-- END MAIN CONTENT -->