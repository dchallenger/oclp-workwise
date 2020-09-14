<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed'); ?>
 
<div>

	<?php 
        if (count($category) > 0):

        $category_count = 0;
        foreach ($category as $category_data):

    ?>

    <div>
        <div class="form-item view odd">
            <label class="label-desc view gray" for="category">
                Category: 
            </label>
            <div class="text-input-wrap">
            	<?= $category_data['revalida_category'] ?>
            </div>
        </div>
        <div class="form-item view even">
            <label class="label-desc view gray" for="category_weight">
                Weight:
            </label>
            <div class="text-input-wrap">
            	<?= $category_data['revalida_category_weight'] ?>%
            </div>
        </div>
        <br /><br /><br />

        <?php

            if (count($category_data['items']) > 0):

            $item_count = 0;
            foreach ($category_data['items'] as $item_data):

        ?>

        <div>
        	<div class="form-item view odd">
	            <label class="label-desc view gray" for="category">
	                Item No.: 
	            </label>
	            <div class="text-input-wrap">
	            	<?= $item_data['training_revalida_item_no']; ?>.
	            </div>
	        </div>
	        <div class="form-item view even">
	            <label class="label-desc view gray" for="category_weight">
	                Description:
	            </label>
	            <div class="text-input-wrap">
	            	<?= $item_data['description']; ?>
	            </div>
	        </div>
	        <div class="form-item view odd">
	            <label class="label-desc view gray" for="category">
	                Rating Type: 
	            </label>
	            <div class="text-input-wrap">

	            	<?php 
	            		foreach( $item_score_type_list as $item_score_type_info ){
	            			if( $item_score_type_info['score_type_id'] == $item_data['score_type'] ){
	            				echo $item_score_type_info['score_type'];
	            			}
	            		}
	            	?>


	            </div>
	        </div>
	        <div class="form-item view even">
	            <label class="label-desc view gray" for="category_weight">
	                Weight:
	            </label>
	            <div class="text-input-wrap">
	            	<?= $item_data['item_weight']; ?>%
	            </div>
	        </div>
        </div>
        <br /><br /><br /><br />

        <?php

            $item_count++;

            endforeach;
            endif;
        ?>

        
    </div>
    <div class="clear"></div>
    <div style="height: 10px;"></div>
    <div style="height: 10px; border-top: 2px solid #CCCCCC;"></div>

    <?php

        $category_count++;

        endforeach;
        endif;
    ?>


</div>