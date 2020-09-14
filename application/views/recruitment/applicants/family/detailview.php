<div>
    <?php
    if (count($family) > 0):
        foreach ($family as $data):
            ?>

            <div>
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
                        Birthdate:
                    </label>
                    <div class="text-input-wrap"><?= display_date($this->config->item('display_date_format'), strtotime($data['birth_date'])) ?></div> <span></span>
                </div>
                <div class="form-item view even">
                    <label class="label-desc view gray" for="family[occupation][]">
                        Occupation:
                    </label>
                    <div class="text-input-wrap"><?= $data['occupation'] ?></div>
                </div>
                <div class="form-item view odd" style="display:none">
                    <label class="label-desc view gray" for="family[employer][]">
                        Employer:
                    </label>
                    <div class="text-input-wrap"><?= $data['employer'] ?></div>
                </div>
                <div class="clear"></div>
                
            </div>
            <div class="clear"></div>
        <?php endforeach; ?>
<?php endif; ?>
</div>
