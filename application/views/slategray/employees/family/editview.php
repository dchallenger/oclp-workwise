<!-- <fieldset> -->

<div class="form-multiple-add-family">
    <input type="hidden" class="add-more-flag" value="family" />
    <div class="icon-label add-more-div" style="float:left;">
        <a class="icon-16-add icon-16-add-listview add-more" href="javascript:void(0);" rel="family" onClick="clone()">
            <span>Add</span>
        </a>
    </div>

    <?php
        $this->db->where('deleted', 0);
        $this->db->where('employee_update_id', $this->input->post('record_id'));
        $pos=$this->db->get('employee_update')->result_array();
        $user = $this->hdicore->_get_userinfo( $pos[0]['employee_id'] );
        echo "<script>$('label[for=\"dummy_position\"]').siblings('.text-input-wrap').text('".$user->position."');</script>";
    ?>

    <br /><br /><br />
    <div id="family_container"></div>
    <div id="family_sample_form">
        <div class="form-multiple-add 1f family_sample" style="display: none;padding-bottom:30px;">
             <div style="width:100%;height:39px;display:block;border-bottom: 1px solid #BBB;">&nbsp;&nbsp;
                <span class="fh-delete" style="float:right;margin-left:5px;border-radius:5px;background-color:#595B5E;padding-left:15px;padding-right:15px;padding-top:5px;padding-bottom:5px;cursor:pointer;">
                    <a class="delete-detail" style ="color:#fff;" onClick="removeClone(this)" href="javascript:void(0)">DELETE</a>
                </span>
             </div>
            <div class="form-item odd" style="padding-top:10px;">
                <label class="label-desc gray" for="family[name][]">
                    Name:
                    <span class="red font-large">*</span>
                </label>
                <div class="text-input-wrap"><input type="text" id="nameText" class="input-text" name="family[]"></div>
            </div>
            <div class="form-item even" style="padding-top:10px;"> 
                <label class="label-desc gray" for="family[relationship][]">
                    Relationship:
                </label>
                <div class="select-input-wrap">
                    <select name="family[]">
                        <option value="">Select..</option>
                        <option value="Mother">Mother</option>
                        <option value="Father">Father</option>
                        <option value="Sister">Sister</option>
                        <option value="Brother">Brother</option>
                        <option value="Spouse">Spouse</option>
                        <option value="Child">Child</option>
                        <option value="Guardian">Guardian</option>
                    </select>
                </div>
            </div>
             <div class="form-item odd bday">
                <label class="label-desc gray" for="family[birthdate][]">
                    Birthdate:
                </label>
                <div class="text-input-wrap" class="dateRemover">
                     <input type="text" class="input-text datepicker date d date" name="family[]"> <span></span>
                </div>
            </div>
            <div class="form-item even">
                <label class="label-desc gray" for="family[occupation][]">
                    Occupation:
                </label>
                <div class="text-input-wrap"><input type="text" class="input-text" name="family[]"></div>
            </div>
            <div class="form-item odd">
                <label class="label-desc gray" for="family[employer][]">
                    Employer:
                </label>
                <div class="text-input-wrap"><input type="text" class="input-text" name="family[]"></div>
            </div>



            <!-- changes for add entry -->
            <div class="form-item even" style="padding-top:10px;"> 
                <label class="label-desc gray" for="family[relationship][]">
                    Educational Attainment:
                </label>
                <div class="select-input-wrap">
                    <select name="family[]">
                        <option value="">Select..</option>
                        <option value="Elementary">Elementary</option>
                        <option value="College">College</option>
                        <option value="Highschool">Highschool</option>
                        <option value="Graduate Studies">Graduate Studies</option>
                        <option value="Vocational">Vocational</option>
                    </select>
                </div>
            </div>
            <div class="form-item odd">
                <label class="label-desc gray" for="family[employer][]">
                    Degree:
                </label>
                <div class="text-input-wrap"><input type="text" class="input-text" name="family[]"></div>
            </div>
            <div class="form-item even">
                Hospitalization Dependent:
                    <input type="checkbox" value="0" class="hospitalization_dependents dependent_check">
                    <input type="text" value="0" class="input-text dependent_value" name="family[]" style="display:none">
            </div>              
            <div class="form-item odd">
                BIR Dependent:  
                    <input type="checkbox" value="0" class="bir_dependents dependent_check">
                    <input type="text" value="0" class="input-text dependent_value" name="family[]" style="display:none">
            </div>    
            <!-- changes for add entry -->

            <div class="form-item odd" style="display:none">
                <label class="label-desc gray" for="family[already_exist][]">
                    Exist:
                </label>
                <div class="text-input-wrap"> <input type="text" id="ae" class="input-text" name="family[]"></div>
            </div>
            <div class="form-item even" style="display:none">
                <label class="label-desc gray" for="family[flagcount][]">
                    flagcount:
                </label>
                <div class="text-input-wrap"> <input type="text" id="flagcount" class="input-text" name="family[]"></div>
            </div>
            <div class="clear"></div>
        </div>

        <div class="clear"></div>
    </div>
</div>
