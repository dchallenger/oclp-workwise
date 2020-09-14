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
            <input type="hidden" name="previous_page" id="previous_page" value="<?=base_url().$this->module_link?>"/>             
            <div class="col-2-form view">
                <?php
                    if ($id_no_config && $id_no_config->num_rows() > 0){
                        $str = '';
                        $ctr = 1;
                        foreach ($id_no_config->result() as $row_info) {
                ?>  
                            <div class="form-item view odd">
                                <label for="logo" class="label-desc view gray">Id Number Config Type: </label>
                                <div class="text-input-wrap">
                                    <?php echo $row_info->employee_id_number_config_type ?>
                                </div>
                            </div>
                            <div class="form-item view even">
                                <label for="title" class="label-desc view gray">Id Number Config Value: </label>
                                <div class="text-input-wrap">
                                    <?php echo $row_info->employee_id_number_config_value ?>
                                </div>
                            </div>
                            <div class="clear"></div>         
                <?php
                            if ($row_info->employee_id_number_config_value != ''){
                                $str .= $row_info->employee_id_number_config_value;
                            }
                            if ($ctr < $id_no_config->num_rows() && $row_info->employee_id_number_config_value!= ''){
                                $str .= '-';   
                            }
                            $ctr++;                            
                        }
                    }                    
                ?>                  
                <div class="form-item view odd">
                    <label for="logo" class="label-desc view gray">Employee Number Format: </label>
                    <div class="text-input-wrap">
                        <?php 
                            dbug($str);
                        ?>
                    </div>
                </div>                
            </div>
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