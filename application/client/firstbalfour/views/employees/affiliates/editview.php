<script>init_datepick;</script>
<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed'); ?>

<div class="form-multiple-add-affiliates">
    <input type="hidden" class="add-more-flag" value="affiliates" />
    <?php

    $ctr = 0;

    if (count($affiliates) > 0):
        foreach ($affiliates as $data):
            if(!isset($enable_edit) && ($enable_edit != 1)){?>
            <fieldset>
                 <div class="form-multiple-add" style="display: block;">
                    <h3 class="form-head">
                        <div class="align-right">
                            <span class="fh-delete">
                                <a href="javascript:void(0)" class="delete-detail">DELETE</a>
                            </span>
                        </div>
                    </h3>
                    <div class="form-item odd">
                        <label class="label-desc gray" for="affiliates[name][]">
                            Name of Affiliation:
                        </label>
                        <div class="select-input-wrap">
                            <?php
                                $this->db->order_by('affiliation'); 
                                $result =  $this->db->get_where('affiliation',array("deleted"=>0));

                                $rows_array = array();
                                if ($result && $result->num_rows() > 0):
                                    $rows_array[$row->affiliation_id] = "Select name of affiliation...";
                                    foreach ($result->result() as $row) {
                                        $rows_array[$row->affiliation_id] = $row->affiliation;
                                    }
                                endif;
                                
                                $affiliation = $rows_array; 
                                                        
                                echo form_dropdown('affiliates[affiliation_id][]', $affiliation, $data['affiliation_id']);
                            ?>
                        </div>  
                    </div>
                    <div class="form-item even">
                        <label class="label-desc gray" for="affiliates[active][]">
                            Status:
                        </label>
                        <div class="text-input-wrap">
                            <?php /* if($data['active'] == 1){ ?>
                             <input type="checkbox" value="1" style="width:10%" class="affiliates_active" checked="checked">Active
                             <input type="hidden" class="active_hidden" name="affiliates[active][]" value="1" />
                             <?php }else{ ?>
                             <input type="checkbox" id="affiliates_active" value="" style="width:10%" class="affiliates_active">Active
                             <input type="hidden" class="active_hidden" name="affiliates[active][]" value="0" />
                             <?php } */ ?>
                             <?php 
                             if($data['active'] == 1){ ?>
                                <input type="radio" name="active_radio[<?php echo $ctr; ?>][]" value="1" class="affiliates_active" style="width:10%" checked="checked"/>Active
                                <input type="radio" name="active_radio[<?php echo $ctr; ?>][]" value="0" class="affiliates_active" style="width:10%" />Resigned
                                <input type="hidden" class="active_hidden" name="affiliates[active][]" value="1" />
                            <?php }else{ ?>
                                <input type="radio" name="active_radio[<?php echo $ctr; ?>][]" value="1" class="affiliates_active" style="width:10%"/>Active
                                <input type="radio" name="active_radio[<?php echo $ctr; ?>][]" value="0" class="affiliates_active" style="width:10%" checked="checked"/>Resigned
                                <input type="hidden" class="active_hidden" name="affiliates[active][]" value="0" />
                             <?php } ?>                             
                        </div>
                    </div>
                    <div class="form-item odd">
                        <label class="label-desc gray" for="affiliates[position][]">
                            Position:
                        </label>
                        <div class="text-input-wrap"><input type="text" class="input-text" value="<?= $data['position'] ?>" name="affiliates[position][]">
                        </div>
                    </div>
                     <div class="form-item even">
                        <label class="label-desc gray" for="affiliates[date_resigned][]">
                            Year Resigned:
                        </label>
                        <div class="text-input-wrap">
                         <?php if($data['date_resigned'] != "0000-00-00" && $data['date_resigned'] != NULL && $data['date_resigned'] != NULL){  ?>
                            <input type="text" name="affiliates[date_resigned][]" id="" value="<?= date('Y', strtotime($data['date_resigned'])) ?>" class="input-text year-dtp date_from"/>
                          <?php  }else{  ?>
                            <input type="text" class="input-text year-dtp date_from" readonly="readonly" style="width:30%;" name="affiliates[date_resigned][]" id="" />
                          <?php } ?>
                        </div>
                    </div>
                    <div class="form-item odd">
                        <label class="label-desc gray" for="affiliates[date_joined][]">
                            Year Joined:
                        </label>
                        <div class="text-input-wrap">
                            <?php if($data['date_joined'] != "0000-00-00" && $data['date_joined'] != NULL){  ?>
                            <input type="text" name="affiliates[date_joined][]" value="<?= date('Y', strtotime($data['date_joined'])) ?>" class="input-text year-dtp date_from"/>
                            <?php  }else{  ?>
                             <input type="text" name="affiliates[date_joined][]" value="" class="input-text year-dtp date_from"/>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="clear"></div>
                 </div>
                 <div class="clear"></div>   
            </fieldset>
             <?php 
                }else{
             ?>
              <fieldset>
                 <div class="form-multiple-add" style="display: block;">
                    <h3 class="form-head">
                        <div class="align-right">
                            <span class="fh-delete">
                                <a href="javascript:void(0)" class="delete-detail">DELETE</a>
                            </span>
                        </div>
                    </h3>
                    <div class="form-item odd">
                        <label class="label-desc gray" for="affiliates[name][]">
                            Name of Affiliation:
                        </label>
                        <div class="text-input-wrap">
                            <input type="text" class="input-text" style="opacity:0.5;"  readonly="readonly" value="<?= $data['name'] ?>" name="affiliates[name][]">
                        </div>
                    </div>
                     <div class="form-item even">
                        <label class="label-desc gray" for="affiliates[active][]">
                            Status:
                        </label>
                        <div class="text-input-wrap">
                            <?php /* if($data['active'] == 1){ ?>
                             <input type="checkbox" value="1" style="width:10%"  disabled="disabled" class="affiliates_active" checked="checked">Active
                             <input type="hidden" class="active_hidden" name="affiliates[active][]" value="1" />
                             <?php }else{ ?>
                             <input type="checkbox" id="affiliates_active"  disabled="disabled" value="" style="width:10%" class="affiliates_active">Active
                             <input type="hidden" class="active_hidden" name="affiliates[active][]" value="0" />
                             <?php } */ ?>
                             <?php 
                             if($data['active'] == 1){ ?>
                              <input type="radio"  readonly="readonly" disabled="disbaled" name="active_radio[<?php echo $ctr; ?>][]" value="1" class="affiliates_active" style="width:10%" checked="checked"/>Active
                             <input type="radio"  readonly="readonly" disabled="disbaled" name="active_radio[<?php echo $ctr; ?>][]" value="0" class="affiliates_active" style="width:10%" />Resigned
                             <input type="hidden" class="active_hidden" name="affiliates[active][]" value="1" />
                             <?php }else{ ?>
                             <input type="radio"  readonly="readonly" disabled="disbaled" name="active_radio[<?php echo $ctr; ?>][]" value="1" class="affiliates_active" style="width:10%"/>Active
                             <input type="radio"  readonly="readonly" disabled="disbaled" name="active_radio[<?php echo $ctr; ?>][]" value="0" class="affiliates_active" style="width:10%" checked="checked" />Resigned
                             <input type="hidden" class="active_hidden" name="affiliates[active][]" value="0" />
                             <?php } ?>
                        </div>
                    </div>
                    <div class="form-item odd">
                        <label class="label-desc gray" for="affiliates[position][]">
                            Position:
                        </label>
                        <div class="text-input-wrap"><input type="text" style="opacity:0.5;"  readonly="readonly" class="input-text" value="<?= $data['position'] ?>" name="affiliates[position][]">
                        </div>
                    </div>
                    <div class="form-item even">
                        <label class="label-desc gray" for="affiliates[date_resigned][]">
                            Date Resigned:
                        </label>
                        <div class="text-input-wrap">
                         <?php if($data['date_resigned'] != "0000-00-00"){  ?>
                            <input type="text" name="affiliates[date_resigned][]" style="width:30% opacity:0.5;"  readonly="readonly" id="affiliates_date_resigned" value="<?= date('F Y', strtotime($data['date_resigned'])) ?>" class="input-text"/>
                          <?php  }else{  ?>
                            <input type="text" class="input-text" value="" readonly="readonly" style="width:30%; opacity:0.5;" name="affiliates[date_resigned][]" id="affiliates_date_resigned" />
                            <img class="ui-datepicker-trigger" style="display:none;" src="<?php echo base_url(); ?>themes/slategray/icons/calendar-month.png" alt="" title="">
                          <?php } ?>
                        </div>
                    </div>
                    <div class="form-item odd">
                        <label class="label-desc gray" for="affiliates[date_joined][]">
                            Date Joined:
                        </label>
                        <div class="text-input-wrap">
                            <input type="text" name="affiliates[date_joined][]" style="width:30% opacity:0.5;"  readonly="readonly" id="affiliates_date_joined" value="<?= date('F Y', strtotime($data['date_joined'])) ?>" class="input-text"/>         
                        </div>
                    </div>
                    <div class="clear"></div>
                 </div>
                 <div class="clear"></div>   
            </fieldset>
             <?php } ?>
        <?php 
          $ctr++;
        endforeach; ?>
    <?php endif; ?>
</div>
