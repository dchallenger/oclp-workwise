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
    <div class="form-item even">
        <label class="label-desc gray" for="family[family_benefit][]">Family Benefit:</label>
        <div class="multiselect-input-wrap">
            <?php
                $this->db->where('deleted',0);
                $this->db->order_by('family_benefit','ASC');
                $family_benefit = $this->db->get('family_benefit')->result_array();        
                print '<select id="family_benefit" name="family[family_benefit_id]" multiple="multiple">';
                    foreach($family_benefit as $family_benefit_record){
                        print '<option value="'.$family_benefit_record["family_benefit_id"].'" '.($family_benefit_record["family_benefit_id"] == $data['family_benefit_id'] ? 'SELECTED' : '').'>'.$family_benefit_record["family_benefit"].'</option>';
                    }
                print '</select>';                                       
            ?>
        </div>
    </div>         
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
