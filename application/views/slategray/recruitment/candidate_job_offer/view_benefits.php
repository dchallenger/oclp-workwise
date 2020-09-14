<div class="col-1-form view">     
    <?php 
        if ($benefits && $benefits->num_rows() > 0){
            foreach ($benefits->result() as $row) {
    ?>
                <div class="form-item view odd ">
                    <label class="label-desc view gray" for="applicant_id"><?php echo $row->benefit ?>:</label>
                    <div class="text-input-wrap"><?php echo $row->value ?><span style="padding-left:10px"><?php echo $row->units ?></span></div>   
                </div>    
    <?php              
            }
        }
    ?>  
</div>