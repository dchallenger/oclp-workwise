<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed'); 
?>

<div class="form-multiple-add" style="display: block;">
    <h3 class="form-head">
        <div class="align-right">
            <span class="fh-delete"><a href="javascript:void(0)" class="delete-detail">DELETE</a></span>
        </div>
    </h3>
    <div class="form-item odd">
        <label class="label-desc gray" for="affiliates[name][]">
            Name of Organization:
        </label>
        <div class="select-input-wrap">
            <?php 
                $result =  $this->db->get_where('affiliation',array("deleted"=>0));

                $rows_array = array();
                if ($result && $result->num_rows() > 0):
                    $rows_array[$row->affiliation_id] = "Select name of Organization...";
                    foreach ($result->result() as $row) {
                        $rows_array[$row->affiliation_id] = $row->affiliation;
                    }
                endif;
                
                $affiliation = $rows_array; 
                                        
                echo form_dropdown('affiliates[affiliation_id][]', $affiliation);
            ?>
        </div>         
    </div>
    <div class="form-item odd">
        <label class="label-desc gray" for="affiliates[position][]">
            Position:
        </label>
        <div class="text-input-wrap"><input type="text" class="input-text" value="" name="affiliates[position][]">
        </div>
    </div>
    <div class="form-item odd">
        <label class="label-desc gray" for="affiliates[date_from][]">
            Dates of Membership:
        </label>
        <div class="text-input-wrap">
            <input type="text" name="affiliates[date_from][]" id="" value="" class="input-text month-year date_from"/>
            <img src="<?php echo base_url() ?>themes/slategray/icons/remove.png" alt="" title="" style="cursor:pointer;" class="clear-val-from">
            &nbsp;&nbsp;&nbsp;to&nbsp;&nbsp;&nbsp;
            <input type="text" name="affiliates[date_to][]" id="" value="" class="input-text month-year date_from" />
            <img src="<?php echo base_url() ?>themes/slategray/icons/remove.png" alt="" title="" style="cursor:pointer;" class="clear-val-to">
        </div>
    </div>     
<!--     <div class="form-item even">
        <label class="label-desc gray" for="affiliates[active][]">
            Status
        </label>
        <div class="text-input-wrap">
            <input type="radio" name="active_radio[<?php echo $rand; ?>][]" value="1" class="affiliates_active" style="width:10%"/>Active
            <input type="radio" name="active_radio[<?php echo $rand; ?>][]" value="0" class="affiliates_active" style="width:10%" checked="checked" />Resigned
            <input type="hidden" class="active_hidden" name="affiliates[active][]" value="0" />
        </div>
    </div>
    <div class="form-item odd">
        <label class="label-desc gray" for="affiliates[position][]">
            Position:
        </label>
        <div class="text-input-wrap"><input type="text" class="input-text" style="width:40%" value="" name="affiliates[position][]">
        </div>
    </div>
    <div class="form-item even">
        <label class="label-desc gray" for="affiliates[date_joined][]">
            Date Joined:
        </label>
        <div class="text-input-wrap">
             <input type="text" name="affiliates[date_joined][]" id="" value="" class="input-text month-year date_from" />
        </div>
    </div>
    <div class="form-item odd">
        <label class="label-desc gray" for="education[date_resigned][]">
            Date Resigned:
        </label>                
        <div class="text-input-wrap">                                          
            <input type="text" name="affiliates[date_resigned][]" value="" id="affiliates_date_resigned" class="input-text month-year date_from date_resigned" />
        </div>                
    </div> -->
    <div class="clear"></div>
</div>