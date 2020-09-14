<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 
    $record_id = $this->input->post('record_id');
?>
<div class="icon-label-group align-left">
	 <strong>What do you need to learn/acquire from this program to help you to perform well?</strong>
	 <span><br>(Please rate current level of competencies - knowledge, skills abilities in this areas/objectives)</span>
</div>
<div class="align-right">
<div class="form-item odd ">
    <div class="icon-label-group">
        <div style="display: block;" class="icon-label ">
            <a href="javascript:void(0);" class="icon-16-add icon-16-add-listview add_row" rel="objective" type="objective">
                <span>Add Objectives</span>
            </a>
        </div>
    </div>
</div> 
</div>
<div class="clear"></div>

<?php if ($record_id != '-1'): 
    $objective = json_decode($records->training_objectives, true);
?>

<?php foreach ($objective['objective'] as $id => $obj):?>
<div class="objective_type">
    <h3 class="form-head">
        <div class="align-right">
            <span class="fh-delete">
                <a href="javascript:void(0)" class="delete-detail delete_row" >DELETE</a>
                <input type="hidden" name="" value="" />
            </span>
        </div>
    </h3>
    <div class="form-item odd ">
        <label class="label-desc gray" for="objective">
            Objective:<span class="red font-large">*</span>
        </label>
        <div class="text-input-wrap">   
           <input type="text" name="objective[objective][]" value="<?=$obj?>" class="input-text objective">
        </div>
    </div>

    <div class="form-item even ">
        <label class="label-desc gray" for="rating">
            Rating (Please do self-rate): <span class="red font-large">*</span>
        </label>
        <div class="select-input-wrap">
            <select name="objective[rating][]" class="rating">
                <option value="">Select ... </option>
                <?php foreach ($ratings as $key => $rating):?>
                <option value="<?=$rating->training_rating_scale_id?>" <?=($rating->training_rating_scale_id == $objective['rating'][$id]) ? 'SELECTED="SELECTED"' : '' ?> ><?=$rating->training_rating_scale?> - <?=$rating->description?></option>
                <?php endforeach;?>
            </select>
        </div> 
    </div>
    <div class="clear"></div>
</div> 

<?php 
        endforeach; ?>
<?php else:?>
<div class="objective_type">
    <h3 class="form-head">
        <div class="align-right">
            <!-- <span class="fh-delete">
                <a href="javascript:void(0)" class="delete-detail delete_row" >DELETE</a>
                <input type="hidden" name="" value="" />
            </span> -->
        </div>
    </h3>
    <div class="form-item odd ">
        <label class="label-desc gray" for="objective">
            Objective:<span class="red font-large">*</span>
        </label>
        <div class="text-input-wrap">   
           <input type="text" name="objective[objective][]" value="" class="input-text objective">
        </div>
    </div>

    <div class="form-item even ">
        <label class="label-desc gray" for="rating">
           Rating (Please do self-rate): <span class="red font-large">*</span>                                                      
        </label>
        <div class="select-input-wrap">
            <select name="objective[rating][]" class="rating">
                <option value="">Select ... </option>
                <?php foreach ($ratings as $key => $rating):?>
                <option value="<?=$rating->training_rating_scale_id?>"><?=$rating->training_rating_scale?> - <?=$rating->description?></option>
                <?php endforeach;?>
            </select>
        </div> 
    </div>
    <div class="clear"></div>

</div>
<?php endif;?>

<div id="training-objective"></div>

<h3 class="form-head">&nbsp;</h3>


<?php $this->load->view('training/application/action_editview')?>


<h3 class="form-head">&nbsp;</h3>


<?php $this->load->view('training/application/transfer_editview')?>