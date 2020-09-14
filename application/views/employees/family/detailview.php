<div>
    <?php
    if (count($family) > 0):
        foreach ($family as $data):
            ?>

            <div>
                <div style="height: 10px; border-top: 4px solid #CCCCCC;"></div>
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="family[name][]">
                        Name:
                        <span class="red font-large">*</span>
                    </label>
                    <div class="text-input-wrap"><?= $data['name'] ?></div>
                </div>
                <div class="form-item view even">
                    <label class="label-desc view gray" for="family[relationship][]">
                        Relationship:
                    </label>
                    <div class="text-input-wrap"><?= $data['relationship'] ?></div>
                </div>
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="family[birth_date][]">
                        Date of Birth:
                    </label>
                    <div class="text-input-wrap"><?= ($data['birth_date'] == "0000-00-00" || $data['birth_date'] == "1970-01-01" ? "" : date('m/d/Y', strtotime($data['birth_date']))) ?></div> <span></span>
                </div>
                <div class="form-item view even">
                    <label class="label-desc view gray" for="family[occupation][]">
                        Occupation:
                    </label>
                    <div class="text-input-wrap"><?= $data['occupation'] ?></div>
                </div>
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="family[employer][]">
                        Employer:
                    </label>
                    <div class="text-input-wrap"><?= $data['employer'] ?></div>
                </div>
                 <div class="form-item view even">
                    <label class="label-desc view gray" for="family[degree][]">
                        Degree Obtained:
                    </label>
                    <div class="text-input-wrap"><?= $data['degree'] ?></div>
                </div>
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="family[educational_attainment][]">
                        Educational Attainment:
                    </label>
                    <div class="text-input-wrap"><?= $data['educational_attainment'] ?></div>
                </div>
                 <div class="form-item view even">
                    <label class="label-desc view gray" for="family[degree][]">
                        Family Benefit:
                    </label>
                    <div class="text-input-wrap">
                        <?
                            $str_array = array();
                            if ($data['ecf_dependent'] != 0):
                                $str_array[] = "ECF Dependent";
                            endif;
                            if ($data['bir_dependents'] != 0):
                                $str_array[] = "BIR Dependent";
                            endif;
                            if ($data['hospitalization_dependents'] != 0 && $data['hospitalization_dependents'] != ''):
                                $str_array[] = "Hospitalization Dependent";
                            endif;  
                            $str = implode(',', $str_array);                            
                            echo $str; 
                        ?>
                    </div>
                </div>               
                <div class="clear"></div>
                
            </div>
            <div class="clear"></div>
            <div style="height: 40px;"></div>
        <?php endforeach; ?>
<?php endif; ?>
</div>
