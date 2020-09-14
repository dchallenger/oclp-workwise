<div class="form-multiple-add" style="display: block;">
    <h3 class="form-head">
        <div class="align-right">
            <span class="fh-delete">
                <a href="javascript:void(0)" class="delete-detail" rel="item">DELETE</a>
                <input type="hidden" name="item[position_skills_id][]" value="0" />
                <input type="hidden" name="item[skills_item_id][]" value="0" />
            </span>
        </div>
    </h3>
    <div class="form-item odd ">
        <label class="label-desc gray" for="date">Item No.:</label>
        <div class="text-input-wrap">               
            <input type="text" readonly="" class="input-text item_no" style="width:100px;" value="<?= $item_count ?>" name="item[skills_item_no][]">
        </div>                                    
    </div>
    <div class="form-item even ">
        <label class="label-desc gray" for="date">Criteria:<span class="red font-large">*</span></label>
        <div class="text-input-wrap">               
            <input type="text" class="input-text skills_item" value="<?= $data['skills_item'] ?>" id="skills_item" name="item[skills_item][]">
        </div>                                    
    </div>
    <div class="form-item odd ">
        <label class="label-desc gray" for="date">Rating Type:<span class="red font-large">*</span></label>
        <div class="text-input-wrap">               
           <select name="item[score_type][]" class="score_type" style="width:250px;">
                <option value="" selected>Please Select</option>
                <?php 
                foreach( $item_score_type_list as $item_score_type_info ){ ?>
                    <option value="<?= $item_score_type_info['score_type_id'] ?>" ><?= $item_score_type_info['score_type'] ?></option>
                <?php 
                } 
                ?>
            </select>
        </div>                                    
    </div>
    <div class="form-item even ">
        <label class="label-desc gray" for="date">Weight:<span class="red font-large">*</span></label>
        <div class="text-input-wrap">               
            <input type="text" class="input-text item_weight" value="0" id="item_weight" name="item[item_weight][]">
        </div>                                    
    </div>
    <div class="multiple_type" style="display:none;">
        <div class="form-item odd ">
            <label class="label-desc gray" for="date">Sub Criteria 1:<span class="red font-large">*</span></label>
            <div class="text-input-wrap">               
                <input type="text" class="input-text sub_criteria" value="<?= $data['sub_criteria1'] ?>" id="sub_criteria1" name="item[subcriteria][<?= $item_count ?>][sub_criteria1]">
            </div>                                      
        </div>
        <div class="form-item even ">
            <label class="label-desc gray" for="date">Sub Criteria 2:<span class="red font-large">*</span></label>
            <div class="text-input-wrap">               
                <input type="text" class="input-text sub_criteria" value="<?= $data['sub_criteria2'] ?>" id="sub_criteria2" name="item[subcriteria][<?= $item_count ?>][sub_criteria2]">
            </div>                                      
        </div>
        <div class="form-item odd ">
            <label class="label-desc gray" for="date">Sub Criteria 3:<span class="red font-large">*</span></label>
            <div class="text-input-wrap">               
                <input type="text" class="input-text sub_criteria" value="<?= $data['sub_criteria3'] ?>" id="sub_criteria3" name="item[subcriteria][<?= $item_count ?>][sub_criteria3]">
            </div>                                      
        </div>
        <div class="form-item even ">
            <label class="label-desc gray" for="date">Sub Criteria 4:<span class="red font-large">*</span></label>
            <div class="text-input-wrap">               
                <input type="text" class="input-text sub_criteria" value="<?= $data['sub_criteria4'] ?>" id="sub_criteria4" name="item[subcriteria][<?= $item_count ?>][sub_criteria4]">
            </div>                                      
        </div>
    </div>


    <div class="clear"></div>
</div>
<div class="clear"></div>   