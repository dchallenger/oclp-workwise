<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed'); ?>
<fieldset>
<div>
    <span style="font-weight:bold;font-size:14px" class="gray">List of friends / acquiantances and relatives working in other construction and engineering companies.</span>
</div>
<div class="form-multiple-add-otherinfo1" >
    <input type="hidden" class="add-more-flag" value="otherinfo1" />
    <?php
    if (count($otherinfo1) > 0):
        foreach ($otherinfo1 as $data):
            if(!isset($enable_edit) && ($enable_edit != 1)){
            ?>
            <div class="form-multiple-add" style="display: block;">
                <h3 class="form-head">
                    <div class="align-right">
                        <span class="fh-delete"><a href="javascript:void(0)" class="delete-detail">DELETE</a></span>
                    </div>
                </h3>                
                <div class="form-item odd">
                    <label class="label-desc gray" for="otherinfo1[name][]">
                        Name:                        
                    </label>
                    <div class="text-input-wrap">
                        <input type="text" class="input-text" value="<?= $data['name'] ?>" name="otherinfo1[name][]">
                    </div>
                </div>
                <div class="form-item even">
                    <label class="label-desc gray" for="otherinfo1[relation][]">
                        Relation:
                    </label>
                    <div class="text-input-wrap">
                        <input type="text" class="input-text" value="<?= $data['relation'] ?>" name="otherinfo1[relation][]">
                    </div>
                </div>
                <div class="form-item odd">
                    <label class="label-desc gray" for="otherinfo1[occupation][]">
                        Occupation:                        
                    </label>
                    <div class="text-input-wrap">
                        <input type="text" class="input-text" value="<?= $data['occupation'] ?>" name="otherinfo1[occupation][]">
                    </div>
                </div>
                <div class="form-item even">
                    <label class="label-desc gray" for="otherinfo1[company][]">
                        Company:
                    </label>
                    <div class="text-input-wrap">
                        <input type="text" class="input-text" value="<?= $data['company'] ?>"  name="otherinfo1[company][]">
                    </div>
                </div>                
            </div>
            <div class="clear"></div>
            <?php 
                }else{
             ?>
             <h3 class="form-head">
                <div class="align-right">
                    <span class="fh-delete"><a href="javascript:void(0)" class="delete-detail">DELETE</a></span>
                </div>
            </h3>
            <div class="form-multiple-add" style="display: block;">
                <div class="form-item odd">
                    <label class="label-desc gray" for="otherinfo1[name][]">
                        Name:                        
                    </label>
                    <div class="text-input-wrap">
                        <input type="text" class="input-text"  readonly="readonly" style="opacity:0.5;" value="<?= $data['name'] ?>" name="otherinfo1[name][]">
                    </div>
                </div>
                <div class="form-item even">
                    <label class="label-desc gray" for="otherinfo1[relation][]">
                        Relation:
                    </label>
                    <div class="text-input-wrap">
                        <input type="text" class="input-text"  readonly="readonly" style="opacity:0.5;" value="<?= $data['relation'] ?>" name="otherinfo1[relation][]">
                    </div>
                </div>
                <div class="form-item even">
                    <label class="label-desc gray" for="otherinfo1[occupation][]">
                        Occupation:                        
                    </label>
                    <div class="text-input-wrap">
                        <input type="text" class="input-text"  readonly="readonly" style="opacity:0.5;" value="<?= $data['occupation'] ?>" name="otherinfo1[occupation][]">
                    </div>
                </div>
                <div class="form-item odd">
                    <label class="label-desc gray" for="otherinfo1[company][]">
                        Company:
                    </label>
                    <div class="text-input-wrap">
                        <input type="text" class="input-text"  readonly="readonly" style="opacity:0.5;" value="<?= $data['company'] ?>"  name="otherinfo1[company][]">
                    </div>
                </div>                
            </div>
            <div class="clear"></div>
             <?php } ?>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
</fieldset>
