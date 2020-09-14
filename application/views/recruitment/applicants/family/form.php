<div class="form-multiple-add" style="display: block;">
     <h3 class="form-head">
        <div class="align-right">
            <span class="fh-delete"><a href="#" class="delete-detail">DELETE</a></span>
        </div>
    </h3>
    <div class="form-item odd">
        <label class="label-desc gray" for="family[name][]">
            Name:            
        </label>
        <div class="text-input-wrap"><input type="text" class="input-text" value="" name="family[name][]"></div>
    </div>
    <div class="form-item even">
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
            echo form_dropdown('family[relationship][]', $options);
            ?>
        </div>        
    </div>
    <div class="form-item odd">
        <label class="label-desc gray" for="family[birth_date][]">
            Birthdate:
        </label>
        <div class="text-input-wrap">
            <input type="text" readonly="readonly" class="input-text date date" value="" name="family[birth_date][]" /> <span></span>
        </div>
    </div>
    <div class="form-item even">
        <label class="label-desc gray" for="family[occupation][]">
            Occupation:
        </label>
        <div class="text-input-wrap"><input type="text" class="input-text" value="" name="family[occupation][]"></div>
    </div>
    <div class="form-item odd" style="display:none">
        <label class="label-desc gray" for="family[employer][]">
            Employer:
        </label>
        <div class="text-input-wrap"><input type="text" class="input-text" value="" name="family[employer][]"></div>
    </div>
    <div class="clear"></div>
</div>