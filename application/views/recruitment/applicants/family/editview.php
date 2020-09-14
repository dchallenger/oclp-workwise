
<div class="form-multiple-add-family">
    <input type="hidden" class="add-more-flag" value="family" /> 
    <input type="hidden" class="" id="no_family" value="<?php echo (count($family) > 0 ? count($family) : 0)?>" />    
    <small>*Click ADD BUTTON for additional family entry </small>
    <input type="hidden" class="add-more-flag" value="family" />    
    <?php 
    if (count($family) > 0 && $family[0]['record_id'] > 0):
        $ctr = 1;
        $no = 0;        
        foreach ($family as $data):
            ?>
			<fieldset>
            <div class="form-multiple-add" style="display: block;">
                <h3 class="form-head">
                    <div class="align-right">
                        <span class="fh-delete"><a href="javascript:void(0)" class="delete-detail">DELETE</a></span>
                    </div>
                </h3>
                <div class="form-item odd">
                    <label class="label-desc gray" for="family[name][]">
                        Name:                        
                    </label>
                    <div class="text-input-wrap"><input type="text" class="input-text" value="<?= $data['name'] ?>" name="family[name][]"></div>
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
                        
                        //if ($data['relationship'] == 'Spouse') {
                        //    $options['Spouse'] = 'Spouse';
                        //}

                        echo form_dropdown('family[relationship][]', $options, $data['relationship']);
                        ?>
                    </div>
                </div>
                <div class="form-item odd">
                    <label class="label-desc gray" for="family[birth_date][]">
                        Birthdate:
                    </label>
                    <div class="text-input-wrap">
                        <input type="text" readonly="readonly" id="fam_bday<?php echo $ctr ?>" class="input-text datepicker date" value="<?= ($data['birth_date'] == "0000-00-00" || $data['birth_date'] == "1970-01-01" ? "" : date('m/d/Y', strtotime($data['birth_date']))) ?>" name="family[birth_date][]" /> <span></span>
                    </div>
                </div>
                <div class="form-item even">
                    <label class="label-desc gray" for="family[occupation][]">
                        Occupation:
                    </label>
                    <div class="text-input-wrap"><input type="text" class="input-text" value="<?= $data['occupation'] ?>" name="family[occupation][]"></div>
                </div>
                <div class="form-item odd" style="display:none">
                    <label class="label-desc gray" for="family[employer][]">
                        Employer:
                    </label>
                    <div class="text-input-wrap"><input type="text" class="input-text" value="<?= $data['employer'] ?>" name="family[employer][]"></div>
                </div>
                <div class="clear"></div>
               
            </div>
            </fieldset>
            <div class="spacer"></div>
        <?php $ctr++; $no++; endforeach; ?>
<?php ;else:
        $family = array('Father', 'Mother', 'Guardian', 'Brother', 'Sister');
        ?>
        <input type="hidden" id="d-flag" value="1" />
        <?php foreach ($family as $data):?>
            <fieldset>
            <div class="form-multiple-add" style="display: block;">
                <h3 class="form-head">
                    <div class="align-right">
                        <span class="fh-delete"><a href="javascript:void(0)" class="delete-detail">DELETE</a></span>
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
                            'Mother'  => 'Mother',
                            'Father'  => 'Father',
                            'Brother' => 'Brother',
                            'Sister'  => 'Sister',
                            'Child'   => 'Child',
                            'Guardian'   => 'Guardian',
                            );
                        
                        //if ($data['relationship'] == 'Spouse') {
                        //    $options['Spouse'] = 'Spouse';
                       // }

                        echo form_dropdown('family[relationship][]', $options, $data, 'id="' . $data . '"');
                        ?>
                    </div>
                </div>
                <div class="form-item odd">
                    <label class="label-desc gray" for="family[birth_date][]">
                        Birthdate:
                    </label>
                    <div class="text-input-wrap">
                        <input type="text" readonly="readonly" class="input-text date" value="" name="family[birth_date][]" /> <span></span>
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
            </fieldset>
            <div class="spacer"></div>
        <?php endforeach; ?>        

        <script type="text/javascript">
            $(document).ready(function () {
                $('#civil_status_id').change(function () {      
                    if ($('#civil_status_id').val() != '1') {
                        if ($('select[name="family[relationship][]"] option[value="Spouse"]').size() == 0) {
                            $('select[name="family[relationship][]"]').append($('<option></option>').val('Spouse').text('Spouse'));
                        }   

                        if ($('#d-flag').val() == '1') {
                            $('#Father').val('Spouse');
                            $('#Mother').val('Child');
                            $('#Guardian').val('Father');
                            $('#Brother').val('Mother');
                            $('#Sister').val('Guardian');                            
                        }
                    } else {
                        if ($('#Mother').val() == 'Child') {
                            $('#Father').val('Father');
                            $('#Mother').val('Mother');
                            $('#Guardian').val('Guardian');
                            $('#Brother').val('Brother');
                            $('#Sister').val('Sister');
                        }

                       // $('select[name="family[relationship][]"] option[value="Spouse"]').remove();                        
                    }                     
                }).trigger('change');                               
            });
        </script>        
<?php endif; ?>
</div>
