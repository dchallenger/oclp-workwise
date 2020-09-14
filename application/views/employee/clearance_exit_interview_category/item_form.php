<div class="form-multiple-add" style="display: block;">
    <h3 class="form-head">
        <div class="align-right">
            <span class="fh-delete">
                <a href="javascript:void(0)" class="delete-detail" rel="item">DELETE</a>
                <input type="hidden" name="item[clearance_exit_interview_item_id][]" value="0" />
            </span>
        </div>
    </h3>
    <div class="form-item odd ">
        <label class="label-desc gray" for="date">Exit Interview Item No.:</label>
        <div class="text-input-wrap">               
            <input type="text" readonly="" class="input-text item_no" style="width:100px;" value="<?= $item_count ?>" name="item[clearance_exit_interview_item_no][]">
        </div>                                    
    </div>
    <div class="form-item even ">
        <label class="label-desc gray" for="date">Exit Interview Item:<span class="red font-large">*</span></label>
        <div class="text-input-wrap">               
            <input type="text" class="input-text clearance_exit_interview_item" value="<?= $data['clearance_exit_interview_item'] ?>" id="clearance_exit_interview_item" name="item[clearance_exit_interview_item][]">
        </div>                                    
    </div>
    <div class="form-item odd " style="display: none;">
        <label class="label-desc gray" for="date">Rating Type:<span class="red font-large">*</span></label>
        <div class="text-input-wrap">               
           <select name="item[exit_interview_score_type][]" class="exit_interview_score_type" style="width:250px;">
                <!-- <option value="" selected>Please Select</option> -->
                <?php 
                foreach( $item_score_type_list as $item_score_type_info ){ ?>
                    <option value="<?= $item_score_type_info['exit_interview_score_type_id'] ?>" ><?= $item_score_type_info['exit_interview_score_type'] ?></option>
                <?php 
                } 
                ?>
            </select>
        </div>                                    
    </div>

    <div class="clear"></div>
</div>
<div class="clear"></div>   