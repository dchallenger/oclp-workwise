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
    <div class="form-item odd view ">
        <label class="label-desc view gray" for="objective">
            Objective:
        </label>
        <div class="text-input-wrap"><?=$obj?></div>
    </div>
    <div class="form-item even view "> 
        <label class="label-desc gray view" for="post_rating">
            Rating (Post Training after 3 months):
        </label>
        <div class="input-input-wrap">
            <?php foreach ($ratings as $key => $rating):?>
                <?=($rating->training_rating_scale_id == $objective['post_rating'][$id]) ? $rating->training_rating_scale. ' - ' .$rating->description : '' ?>
            <?php endforeach;?>
        </div> 
    </div>
    <div class="form-item odd view">
        <label class="label-desc gray view" for="pre_rating">
            Rating (Pre-Training):
        </label>
        <div class="text-input-wrap">   
            <?php $pre_rating_result = $this->db->get_where('training_rating_scale', array('training_rating_scale_id' => $objective['rating'][$id]))->row(); 
                    $pre_rating = $pre_rating_result->training_rating_scale. ' - ' . $pre_rating_result->description;
                    $pre[] = $pre_rating_result->training_rating_scale;
            ?>
            <?=$pre_rating?>
           
        </div>
    </div>
    <div class="form-item even view">
        <label class="label-desc gray view" for="gap">
            Gap:
        </label>
        <div class="text-input-wrap"><?=$objective['gap'][$id]?></div>
    </div>
    <div class="clear"></div>
</div> 
<div class="clear"></div>
<h3 class="form-head">&nbsp;</h3>
<div class="clear"></div>
<?php 
        endforeach;
    endif;
    ?>
<div id="training-objective"></div>
