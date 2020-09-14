<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed'); ?>

<div>
    <?php
    if (count($training) > 0):
        foreach ($training as $data):
            $date_from = ($data['from_date'] == "0000-00-00" || $data['from_date'] == "1970-01-01" ? "" : date('m/d/Y', strtotime($data['from_date'])));
            $date_to = ($data['date_to'] == "0000-00-00" || $data['date_from'] == "1970-01-01" ? "" : date('m/d/Y', strtotime($data['date_to'])));           
        ?>
            <div>
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="training[course][]">
                        Course:
                    </label>
                    <div class="text-input-wrap"><?= $data['course'] ?></div>
                </div>
                <div class="form-item view even">
                    <label class="label-desc view gray" for="training[institution][]">
                        Institution:
                    </label>
                    <div class="text-input-wrap"><?= $data['institution'] ?></div>
                </div>
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="training[address][]">
                        Address:
                    </label>
                    <div class="text-input-wrap"><?= $data['address'] ?></div>
                </div>
                <div class="form-item view even">
                    <label class="label-desc view gray" for="training[remarks][]">
                        Remarks:
                    </label>
                    <div class="text-input-wrap"><?= nl2br($data['remarks']) ?></div>
                </div>
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="training[from_date][]">
                        Date From:
                    </label>
                    <div class="text-input-wrap"><?= $date_from ?></div>
                </div>
                <div class="form-item view even">
                    <label class="label-desc view gray" for="training[to_date][]">
                        Date To:
                    </label>
                    <div class="text-input-wrap"><?= $date_to ?></div>
                </div>
            </div>
            <div class="clear"></div>
            <hr />
        <?php endforeach; ?>
    <?php endif; ?>
</div>