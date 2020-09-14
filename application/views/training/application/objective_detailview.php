<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 
    $record_id = $this->input->post('record_id');
?>
<div class="icon-label-group align-left">
	 <strong>What do you need to learn/acquire from this program to help you to perform well?</strong>
	 <span><br>(Please rate current level of competencies - knowledge, skills abilities in this areas/objectives)</span>
</div>
<div class="clear"></div>
<h3>&nbsp;</h3>
<div class="clear"></div>

<?php if ($record_id != '-1'): 
    $objective = json_decode($records->training_objectives, true);
?>

<?php foreach ($objective['objective'] as $id => $obj):?>
<div class="objective_type">
    <div class="form-item view odd ">
        <label class="label-desc view gray" for="objective">
            Objective:
        </label>
        <div class="text-input-wrap">   
           <?=$obj?>
        </div>
    </div>

    <div class="form-item even view">
        <label class="label-desc view gray" for="rating">
             Rating (Please do self-rate):
        </label>
        <div class="text-input-wrap">
            <?php foreach ($ratings as $key => $rating):?>
            <?=($rating->training_rating_scale_id == $objective['rating'][$id]) ? $rating->training_rating_scale .'-' .$rating->description : '' ?>
            <?php endforeach;?>

        </div> 
    </div>
    <div class="clear"></div>
</div> 

<?php 
        endforeach;
    endif;?>

<div id="training-objective"></div>



<h3 class="form-head">&nbsp;</h3>


<?php $this->load->view('training/application/action_detailview')?>


<h3 class="form-head">&nbsp;</h3>


<?php $this->load->view('training/application/transfer_detailview')?>