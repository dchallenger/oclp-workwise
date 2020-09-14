<div>
 <?php
   $this->db->join('benefit','employee_movement_benefit.benefit_id = benefit.benefit_id','left');
   $list_movement_benefit = $this->db->get_where('employee_movement_benefit',array('employee_movement_id'=>$this->input->post('record_id')))->result_array();
     if (isset($list_movement_benefit) && sizeof($list_movement_benefit) > 0) :
         foreach ($list_movement_benefit as $fieldgroup) :
 ?>
	<div>
        <!-- <div style="height: 10px; border-top: 4px solid #CCCCCC;"></div> -->
           	<div class="form-item view odd">
           	    <label class="label-desc view gray" for="benefit[type]">
           	        Benefit Type:
           	    </label>
           	    <div class="text-input-wrap"><?= $fieldgroup['benefit'] ?></div>
           	</div>
       	    <div class="form-item view even">
       		    <label class="label-desc view gray" for="benefit[value]">
       	 	        Value:
       	        </label>
       	        <div class="text-input-wrap"><?= $fieldgroup['value'] ?></div>
       	    </div>
        	     
       	<div class="clear"></div>
                
    </div>

        <!-- <div class="clear"></div> -->

        <!-- <div style="height: 40px;"></div> -->

<?php
         endforeach;
     endif;
?>
</div>

