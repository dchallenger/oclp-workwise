<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 
    $record_id = $this->input->post('record_id');
?>
<div class="icon-label-group align-left">
	 <strong>How well did the training help the participant archieve his/her learning objectives?</strong>
	 <span><br>(Please refer to learning/objective in EPAF)</span>
     <h3>&nbsp;</h3>
</div>
<div class="clear"></div>
<div class="clear"></div>

<?php if ($record_id != '-1'): 
    $objective = json_decode($records->training_objectives, true);
    $post = array();
    $pre = array();
?>

<?php foreach ($objective['objective'] as $id => $obj):?>
<div class="objective_type">
    <div class="form-item odd ">
        <label class="label-desc gray" for="objective">
            Objective:<span class="red font-large">*</span>
        </label>
        <div class="text-input-wrap">   
           <input type="text" name="objective[objective][]" value="<?=$obj?>" class="input-text objective" readonly="readonly">
        </div>
    </div>
    <div class="form-item even "> 
        <label class="label-desc gray" for="post_rating">
            Rating (Post Training after 3 months):
        </label>
        <div class="select-input-wrap">
            <select name="objective[post_rating][]" class="post_rating post" rating="pre_rate">
                <option value="">Select ... </option>
                <?php //$post = array();
                 foreach ($ratings as $key => $rating):
                    if($rating->training_rating_scale_id == $objective['post_rating'][$id])
                        $post[] = $rating->training_rating_scale;
                ?>
                <option value="<?=$rating->training_rating_scale_id?>" <?=($rating->training_rating_scale_id == $objective['post_rating'][$id]) ? 'SELECTED="SELECTED"' : '' ?> post-rate="<?=$rating->training_rating_scale?>"><?=$rating->training_rating_scale?> - <?=$rating->description?></option>
                <?php endforeach;?>
            </select>
        </div> 
    </div>
    <div class="form-item odd ">
        <label class="label-desc gray" for="pre_rating">
            Rating (Pre-Training):
        </label>
        <div class="text-input-wrap">   
            <?php $pre_rating_result = $this->db->get_where('training_rating_scale', array('training_rating_scale_id' => $objective['rating'][$id]))->row(); 
                    $pre_rating = $pre_rating_result->training_rating_scale. ' - ' . $pre_rating_result->description;
                    $pre[] = $pre_rating_result->training_rating_scale;
            ?>
           <input type="text" value="<?=$pre_rating?>" class="input-text pre_rating pre" rating="pre_rate" readonly="readonly" pre-rate="<?=$pre_rating_result->training_rating_scale?>">
           <input type="hidden" name="objective[rating][]" value="<?=$objective['rating'][$id]?>" class="input-text pre_rating">
        </div>
    </div>
    <div class="form-item even">
        <label class="label-desc gray" for="gap">
            Gap:
        </label>
        <div class="text-input-wrap">   
           <input type="text" name="objective[gap][]" value="<?=$objective['gap'][$id]?>" class="input-text gap" readonly="readonly">
        </div>
    </div>
    <div class="clear"></div>
</div> 
<div class="clear"></div>
<h3 class="form-head">&nbsp;</h3>
<div class="clear"></div>
<?php 
        endforeach;
    endif;

$total_pre = array_sum($pre);
$total_post = array_sum($post);
$total_gap = $total_post - $total_pre;
    ?>
<input type="hidden" value="<?=$total_pre?>" id="total_pre" />
<input type="hidden" value="<?=$total_gap?>" id="total_gap" />
<div id="training-objective"></div>

