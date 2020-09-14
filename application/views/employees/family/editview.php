<fieldset>
<div class="form-multiple-add-family">
    <input type="hidden" class="add-more-flag" value="family" />
    <input type="hidden" class="" id="no_family" value="<?php echo (count($family) > 0 ? count($family) : 0)?>" />
    <?php
    if (count($family) > 0):
        $ctr = 1;
        $no = 0;
        foreach ($family as $data):
            if(!isset($enable_edit) && ($enable_edit != 1)){
            $this->db->where('record_id',$data['record_id']);
            $this->db->like('occupation','(DECEASED)');
            $disabled = $this->db->get('employee_family');
            if($disabled->num_rows > 0)
                $disable_me = 'readonly="readonly"';
            else
                $disable_me = '';
            ?>

            <div class="form-multiple-add" style="display: block;">
                <h3 class="form-head">
                    <div class="align-right">
                        <span class="fh-delete"><a href="javascript:void(0)" class="delete-detail">DELETE</a></span>
                    </div>
                </h3>
                <div class="form-item odd">
                    <label class="label-desc gray" for="family[name][]">
                        Name:
                        <span class="red font-large">*</span>
                    </label>
                    <div class="text-input-wrap"><input type="text" class="input-text" <?= $disable_me ?> value="<?= $data['name'] ?>" name="family[name][]"></div>
                </div>
                <div class="form-item even relation_id">
                    <label class="label-desc gray" for="family[relationship][]">
                        Relationship:
                    </label>
                    <div class="select-input-wrap">                        
                        <?php 
                        $options = array(
                            ''        => 'Select&hellip;',
                            'Brother' => 'Brother',                            
                            'Child'   => 'Child', 
                            'Father'  => 'Father',
                            'Guardian'   => 'Guardian',                                                                                   
                            'Mother'  => 'Mother',
                            'Sister'  => 'Sister',                            
                            'Spouse'  => 'Spouse'
                            );

                        // if ($data['relationship'] == 'Spouse') {
                        //     $options['Spouse'] = 'Spouse';
                        // }

                        echo form_dropdown('family[relationship][]', $options, $data['relationship']);
                        ?>
                    </div>
                </div>
                <div class="form-item odd bday">
                    <label class="label-desc gray" for="family[birth_date][]">
                        Date of Birth:
                    </label>
                    <div class="text-input-wrap">
                        <input type="text" readonly="readonly" id="fam_bday<?php echo $ctr ?>" class="input-text datepicker date" value="<?= ($data['birth_date'] == "0000-00-00" || $data['birth_date'] == "1970-01-01" ? "" : date('m/d/Y', strtotime($data['birth_date']))) ?>" name="family[birth_date][]" /> <span></span>
                    </div>
                </div>
                <div class="form-item even">
                    <label class="label-desc gray" for="family[occupation][]">
                        Occupation:
                    </label>
                    <div class="text-input-wrap"><input type="text" class="input-text" <?= $disable_me ?> value="<?= $data['occupation'] ?>" name="family[occupation][]"></div>
                </div>
                <div class="form-item odd">
                    <label class="label-desc gray" for="family[employer][]">
                        Employer:
                    </label>
                    <div class="text-input-wrap"><input type="text" <?= $disable_me ?> class="input-text" value="<?= $data['employer'] ?>" name="family[employer][]"></div>
                </div>                
                <?php if($this->config->item('client_no') != 3) { ?>
                    <div class="form-item even">
                        <!-- <label class="label-desc gray" for="family[ecf_dependent][]"> -->
                            
                        <!-- </label> -->
                        <!-- <div class="text-input-wrap"> -->
                            ECF Dependent:
                            <?php if($data['ecf_dependent']==1){ ?>
                                <input type="checkbox" value="1" checked="checked" class="dependent_check" <?= ($disabled->num_rows() > 0 ? 'disabled="disabled"' : '') ?>>
                                <input type="hidden" value="1" class="input-text dependent_value" name="family[ecf_dependent][]" <?= $disable_me ?>>
                            <?php }else{ ?>
                                <input type="checkbox" value="0" class="dependent_check" <?= ($disabled->num_rows() > 0 ? 'disabled="disabled"' : '') ?>>
                                <input type="hidden" value="0" class="input-text dependent_value" name="family[ecf_dependent][]" <?= $disable_me ?>>
                            <?php } ?>
                        <!-- </div> -->
                    </div>
                    <div class="form-item even">
                        <!-- <label class="label-desc gray" for="family[bir_dependents][]"> -->
                            
                        <!-- </label> -->
                        <!-- <div class="text-input-wrap"> -->
                          BIR Dependent:  
                            <?php if($data['bir_dependents']==1){ ?>
                                <input type="checkbox" value="1" checked="checked" class="bir_dependents dependent_check" <?= ($disabled->num_rows() > 0 ? 'disabled="disabled"' : '') ?>>
                                <input type="hidden" value="1" class="input-text dependent_value" name="family[bir_dependents][]" <?= $disable_me ?>>
                            <?php }else{ ?>
                                <input type="checkbox" value="0" class="bir_dependents dependent_check" <?= ($disabled->num_rows() > 0 ? 'disabled="disabled"' : '') ?>>
                                <input type="hidden" value="0" class="input-text dependent_value" name="family[bir_dependents][]" <?= $disable_me ?>>
                            <?php } ?>
                        <!-- </div> -->
                    </div>
                    <div class="form-item even">
                        <!-- <label class="label-desc gray" for="family[bir_dependents][]"> -->
                            
                        <!-- </label> -->
                        <!-- <div class="text-input-wrap"> -->
                            Hospitalization Dependent:
                            <?php if($data['hospitalization_dependents']==1){ ?>
                                <input type="checkbox" value="1" checked="checked" class="hospitalization_dependents dependent_check" <?= ($disabled->num_rows() > 0 ? 'disabled="disabled"' : '') ?>>
                                <input type="hidden" value="1" class="input-text dependent_value" name="family[hospitalization_dependents][]" <?= $disable_me ?>>
                            <?php }else{ ?>
                                <input type="checkbox" value="0" class="hospitalization_dependents dependent_check" <?= ($disabled->num_rows() > 0 ? 'disabled="disabled"' : '') ?>>
                                <input type="hidden" value="0" class="input-text dependent_value" name="family[hospitalization_dependents][]" <?= $disable_me ?>>
                            <?php } ?>
                        <!-- </div> -->
                    </div>
                <?php } ?>
                <div class="form-item odd spouseshow">
                    <label class="label-desc gray" for="family[educational_attainment][]">
                        Educational Attainment :
                    </label>
                    <div class="select-input-wrap">                        
                        <?php 
                        $options = array(
                            ''        => 'Select&hellip;',
                            'Elementary'  => 'Elementary',
                            'Highschool'  => 'Highschool',
                            'College' => 'College',
                            'Graduate Studies'  => 'Graduate Studies',
                            'Vocational' => 'Vocational'
                            );
                        echo form_dropdown('family[educational_attainment][]', $options, $data['educational_attainment']);
                        ?>
                    </div>                    
                </div>
                <div class="form-item <?= (CLIENT_DIR == "firstbalfour" ? 'even' : 'odd') ?> spouseshow">
                    <label class="label-desc gray" for="family[degree][]">
                        Degree :
                    </label>
                    <div class="text-input-wrap"><input <?= $disable_me ?> type="text" class="input-text" value="<?= $data['degree'] ?>" name="family[degree][]"></div>
                </div>
                <div class="clear"></div>
            </div>
            <div class="clear"></div>
            <?php 
                }else{
             ?>
            <div class="form-multiple-add" style="display: block;">
                <h3 class="form-head">
                    <div class="align-right">
                        <span class="fh-delete"><a href="javascript:void(0)" class="delete-detail">DELETE</a></span>
                    </div>
                </h3>
                <div class="form-item odd">
                    <label class="label-desc gray" for="family[name][]">
                        Name:
                        <span class="red font-large">*</span>
                    </label>
                    <div class="text-input-wrap"><input type="text" style="opacity:0.5;"  readonly="readonly" class="input-text" value="<?= $data['name'] ?>" name="family[name][]"></div>
                </div>
                <div class="form-item even">
                    <label class="label-desc gray" for="family[relationship][]">
                        Relationship:
                    </label>
                    <div class="text-input-wrap"><input type="text" style="opacity:0.5;"  readonly="readonly" class="input-text" value="<?= $data['relationship'] ?>" name="family[relationship][]"></div>
                </div>
                <div class="form-item odd">
                    <label class="label-desc gray" for="family[birth_date][]">
                        Birthdate:
                    </label>
                    <div class="text-input-wrap">
                        <input type="text"  readonly="readonly" style="width:30%; opacity:0.5;" class="input-text" value="<?= ($data['birth_date'] == "0000-00-00" || $data['birth_date'] == "1970-01-01" ? "" : date('m/d/Y', strtotime($data['birth_date']))) ?>" name="family[birth_date][]" /> <span></span>
                    </div>
                </div>
                <div class="form-item even">
                    <label class="label-desc gray" for="family[occupation][]">
                        Occupation:
                    </label>
                    <div class="text-input-wrap"><input type="text"  style="opacity:0.5;"  readonly="readonly" class="input-text" value="<?= $data['occupation'] ?>" name="family[occupation][]"></div>
                </div>
                <div class="form-item odd">
                    <label class="label-desc gray" for="family[employer][]">
                        Employer:
                    </label>
                    <div class="text-input-wrap"><input type="text"  readonly="readonly" style="opacity:0.5;" class="input-text" value="<?= $data['employer'] ?>" name="family[employer][]"></div>
                </div>
                <div class="form-item even">
                    <div class="text-input-wrap">
                        <?php if($data['ecf_dependent']==1){ ?>
                            <input type="checkbox" value="1" checked="checked" class="input-text dependent_check">
                            <input type="hidden" value="1" class="input-text dependent_value" name="family[ecf_dependent][]">
                        <?php }else{ ?>
                            <input type="checkbox" value="0" class="input-text dependent_check">
                            <input type="hidden" value="0" class="input-text dependent_value" name="family[ecf_dependent][]">
                        <?php } ?>
                    </div>
                </div>
                <div class="form-item even">
                    <label class="label-desc gray" for="family[bir_dependents][]">BIR Qualified Defendents:</label>
                    <?php if($data['bir_dependent']==1){ ?>
                        <input type="checkbox" value="1" checked="checked" class="input-text dependent_check">
                        <input type="hidden" value="1" class="input-text dependent_value" name="family[bir_dependents][]">
                    <?php }else{ ?>
                        <input type="checkbox" value="0" class="input-text dependent_check">
                        <input type="hidden" value="0" class="input-text dependent_value" name="family[bir_dependents][]">
                    <?php } ?>
                </div>
                   <div class="form-item odd spouseshow">
                    <label class="label-desc gray" for="family[employer][]">
                        Educational Attainment :
                    </label>
                    <div class="text-input-wrap"><input type="text" class="input-text" value="<?= $data['educational_attainment'] ?>" name="family[educational_attainment][]"></div>
                </div>
                <div class="form-item odd spouseshow">
                    <label class="label-desc gray" for="family[employer][]">
                        Degree Obtained :
                    </label>
                    <div class="text-input-wrap"><input type="text" class="input-text" value="<?= $data['degree'] ?>" name="family[degree][]"></div>
                </div>
                <div class="clear"></div>
            </div>
            <div class="clear"></div>
             <?php } $ctr++; $no++;  ?>
        <?php endforeach; ?>
<?php endif; ?>
</div>
</fieldset>