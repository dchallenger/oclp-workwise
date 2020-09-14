<div class="competency-div" count="">
    <div class="form-multiple-add-competency" style="display: block;">
        <h3 class="form-head">
            <div class="align-right">
                <span class="fh-delete">
                    <a href="javascript:void(0)" class="delete-detail" rel="competency">DELETE</a>
                    <input type="hidden" name="" value="" />
                </span>
            </div>
        </h3>
        <div class="form-item odd ">
            <label class="label-desc gray" for="date">Competency:<span class="red font-large">*</span></label>
            <div class="text-input-wrap">               
                <input type="text" class="input-text competency_name" value=""  name="competencies[<?=$rand?>]"  >

            </div>                                    
        </div>
        <div class="form-item odd">
            <div class="icon-label-group">
        	    <div class="icon-label add-more-div" style="display: block;">
        	        <a rel="level" class="icon-16-add icon-16-add-listview add-more" href="javascript:void(0);" count="<?=$rand?>">
        	            <span>Add Level</span>
        	        </a>
        	    </div>
            </div>                               
        </div>
    <div class="clear"></div>
    </div> 

    <div class="form-multiple-add-level-group" >    
    <input type="hidden" class="rand" name="rand[]" value="<?=$rand?>" />
        <fieldset class="level">
        </fieldset>
    </div>

</div>