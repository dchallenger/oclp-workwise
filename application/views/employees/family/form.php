<div class="form-multiple-add" style="display: block;">
     <h3 class="form-head">
        <div class="align-right">
            <span class="fh-delete"><a href="#" class="delete-detail">DELETE</a></span>
        </div>
    </h3>
    <div class="form-item odd">
        <label class="label-desc gray" for="family[name][]">
            Name:
            <span class="red font-large">*</span>
        </label>
        <div class="text-input-wrap"><input type="text" class="input-text" value="" name="family[name][]"></div>
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

            echo form_dropdown('family[relationship][]', $options);
            ?>
        </div>
    </div>
    <div class="form-item odd bday">
        <label class="label-desc gray" for="family[birth_date][]">
            Date of Birth:
        </label>
        <div class="text-input-wrap">
            <input type="text" readonly="readonly" class="input-text datepicker date date" value="" name="family[birth_date][]" /> <span></span>
        </div>
    </div>
    <div class="form-item even">
        <label class="label-desc gray" for="family[occupation][]">
            Occupation:
        </label>
        <div class="text-input-wrap"><input type="text" class="input-text" value="" name="family[occupation][]"></div>
    </div>
    <div class="form-item odd">
        <label class="label-desc gray" for="family[employer][]">
            Employer:
        </label>
        <div class="text-input-wrap"><input type="text" class="input-text" value="" name="family[employer][]"></div>
    </div>
    <?php if($this->config->item('client_no') != 3) { ?>
        <div class="form-item even">
            <!-- <label class="label-desc gray" for="family[ecf_dependent][]"> -->
                <!-- ECF Dependent: -->
               ECF Dependent : <input type="checkbox" value="0" class="dependent_check"> 
                <input type="hidden" value="0" class="input-text dependent_value" name="family[ecf_dependent][]">
            <!-- </label> -->
            <!-- <div class="text-input-wrap">
                
            </div> -->
        </div>
        <div class="form-item even">
            <!-- <label class="label-desc gray" for="family[ecf_dependent][]"> 
                     class="input-text dependent_check"-->
                <span> BIR Dependent : </span><input type="checkbox" value="0" class="bir_dependents dependent_check">
                <input type="hidden" value="0" class="input-text dependent_value" name="family[bir_dependents][]">
            <!-- </label> -->
            <!-- <div class="text-input-wrap">
                
            </div> -->
        </div>
        <div class="form-item even">
            <!-- <label class="label-desc gray" for="family[ecf_dependent][]"> 
                     class="input-text dependent_check"-->
                <span> Hospitalization Dependent : </span><input type="checkbox" value="0" class="hospitalization_dependents dependent_check">
                <input type="hidden" value="0" class="input-text dependent_value" name="family[hospitalization_dependents][]">
            <!-- </label> -->
            <!-- <div class="text-input-wrap">
                
            </div> -->
        </div>        
    <?php } ?>
    <div class="form-item <?= (CLIENT_DIR == "firstbalfour" ? 'even' : 'odd') ?> spouseshow">
        <label class="label-desc gray" for="family[educational_attainment][]">
            Educational Attainment :
        </label>
        <div class="select-input-wrap">                        
            <?php 
            $options = array(
                ''        => 'Select&hellip;',
                'Elementary'  => 'Elementary',
                'College' => 'College',
                'Highschool'  => 'Highschool',
                'Graduate Studies'  => 'Graduate Studies',
                'Vocational' => 'Vocational'
                );
            echo form_dropdown('family[educational_attainment][]', $options);
            ?>
        </div>         
    </div>
    <div class="form-item odd spouseshow">
        <label class="label-desc gray" for="family[employer][]">
            Degree Obtained :
        </label>
        <div class="text-input-wrap"><input type="text" class="input-text" value="" name="family[degree][]"></div>
    </div>
    <div class="clear"></div>
    <hr />
</div>
