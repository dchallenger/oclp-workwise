<script>init_datepick;</script>
<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed'); ?>

<div class="icon-label-group">
   <div style="display: block;" class="icon-label add-more-div"><a href="javascript:void(0);" class="icon-16-add icon-16-add-listview add-more" rel="budget"><span>Add Budget</span></a></div>
</div>

<div class="form-multiple-add-budget">

    <input type="hidden" class="add-more-flag" value="budget" />

    <fieldset>
    <?php 
        if (count($budget) > 0):
        $budget_count = 0;
        foreach ($budget as $data):

    ?>

                 <div class="form-multiple-add" style="display: block;">
                    <h3 class="form-head">
                        <div class="align-right">
                            <span class="fh-delete">
                                <a href="javascript:void(0)" class="delete-detail" rel="budget">DELETE</a>
                            </span>
                        </div>
                    </h3>
                    <div class="form-item odd ">
                        <label class="label-desc gray" for="date">Training Cost Name:<span class="red font-large">*</span></label>
				        <div class="text-input-wrap">				
							<input type="text" class="input-text cost_name" value="<?= $data['training_cost_name'] ?>" id="training_cost_name" name="budget[training_cost_name][]">
						</div>                                    
					</div>
                    <div class="form-item even ">
                        <label class="label-desc gray" for="date">Investment Cost:<span class="red font-large">*</span></label>
                        <div class="text-input-wrap">               
                            <input type="text" class="input-text cost" style="width:20%;" value="<?= $data['cost'] ?>" name="budget[cost][]">
                        </div>                                    
                    </div>
                    <div class="form-item odd">
                        <label class="label-desc gray" for="date">Remarks:</label>
                        <div class="select-input-wrap">               
                           <textarea name="budget[remarks][]" class="remarks" ><?= $data['remarks'] ?></textarea>
                        </div>                                    
                    </div>
                    <div class="form-item even ">
                        <label class="label-desc gray" for="date">No. of Particulars:<span class="red font-large">*</span></label>
                        <div class="text-input-wrap">               
                            <input type="text" class="input-text pax" style="width:20%;" value="<?= $data['pax'] ?>" name="budget[pax][]">
                        </div>                                    
                    </div>
                    <div class="form-item even ">
                        <label class="label-desc gray" for="date">Total:</label>
                        <div class="text-input-wrap">               
                            <input type="text" class="input-text total" readonly="" style="width:20%;" value="<?= $data['total'] ?>" name="budget[total][]">
                        </div>                                    
                    </div>
                    <div class="clear"></div>
                </div>
                <div class="clear"></div>     

    <?php

        $budget_count++;

        endforeach;
        endif;
    ?>
    </fieldset>
</div>
<hr />  
<div >
    <fieldset>
        <div class="form-item odd">
            <label class="label-desc gray" for="date">Total Investment Cost:</label>
            <div class="text-input-wrap">               
                <input type="text" class="input-text total_cost" style="width:20%;" value="<?= $budget_total_cost ?>" readonly="" name="total_cost">
            </div>                                    
        </div>
        <div class="form-item odd">
            <label class="label-desc gray" for="date">Total No. of Particulars:</label>
            <div class="text-input-wrap">               
                <input type="text" class="input-text total_pax" style="width:20%;" value="<?= $budget_total_pax ?>" readonly="" name="total_pax">
            </div>                                    
        </div>
    </fieldset>
</div>
