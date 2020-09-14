<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed'); ?>

<div>
    <?php
    if (count($education) > 0):
        foreach ($education as $data):
            ?>
            <div style="display: block;">
                <div style="height: 10px; border-top: 4px solid #CCCCCC;"></div>
                <br />
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="education[education_level][]">
                        Educational Attainment:
                    </label>
                    <div class="text-input-wrap"><?= $data['education_level'] ?>
                    </div>
                </div>
                <div class="form-item view even">
                    <label class="label-desc view gray" for="education[school][]">
                        <br />School:
                    </label>
                    <div class="text-input-wrap">
                        <?= $data['school'] ?>                       
                    </div>
                </div>
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="education[degree][]">
                        Degree Obtained:
                    </label>
                    <div class="text-input-wrap"><?= $data['degree'] ?>
                    </div>
                </div>
                <div class="form-item view even">
                    <label class="label-desc view gray" for="education[course][]">
                        Course Taken:
                    </label>
                    <div class="text-input-wrap"><?= $data['course'] ?>
                    </div>
                </div>                                
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="education[date_from][]">
                        Date From:
                    </label>
                    <div class="text-input-wrap"><?= display_date('F Y', strtotime($data['date_from'])) ?></div>
                </div>
                <div class="form-item view even">
                    <label class="label-desc view gray" for="education[date_to][]">
                        Date To:
                    </label>
                    <div class="text-input-wrap"><?= display_date('F Y', strtotime($data['date_to'])) ?></div>
                </div>
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="education[honors_received][]">
                        Honors Received:
                    </label>
                    <div class="text-input-wrap"><?= $data['honors_received'] ?>
                    </div>
                </div>
               <div class="form-item view even">
                    <label class="label-desc view gray">
                        Graduated?(Yes/No):
                    </label>
                    <div class="text-input-wrap"><?=($data['graduate'] == 1) ? 'Yes' : 'No'?></div>
                </div>                               
            </div>
            <div class="clear"></div>
            <br />
        <?php endforeach; ?>
    <?php endif; ?>
</div>