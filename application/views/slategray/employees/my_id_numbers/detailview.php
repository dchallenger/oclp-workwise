<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed'); ?>

<?php
    switch ($employee_record->tax_status) {
            case 1:
                $tax_status = "Single/Married";
                break;
            case 2:
                $tax_status = "Single/Married with 1 dependent/s";
                break;
            case 3:
                $tax_status = "Single/Married with 2 dependent/s";
                break;
            case 4:
                $tax_status = "Single/Married with 3 dependent/s";
                break;
            case 5:
                $tax_status = "Single/Married with 4 dependent/s";
                break;                                                
            default:
                # code...
                break;
        }    
?>

<div class="wizard-form">
    <div class="col-2-form view">
        <div style="height: 10px; border-top: 4px solid #CCCCCC;"></div>
        <div class="form-item view odd">
            <label class="label-desc view gray" for="sss">sss:</label>
            <div class="text-input-wrap"><?= $employee_record->sss ?></div>
        </div>
        <div class="form-item view even">
            <label class="label-desc view gray" for="tin">TIN:</label>
            <div class="text-input-wrap"><?= $employee_record->tin ?></div>
        </div>
<!--         <div class="form-item view odd">
            <label class="label-desc view gray" for="sss">With Existing Loan:</label>
            <div class="text-input-wrap"><?= ($employee_record->sss_existing_loan == 0 ? "No" : "Yes") ?></div>
        </div>
        <div class="form-item view odd">
            <label class="label-desc view gray" for="tin">Current Balance:</label>
            <div class="text-input-wrap"><?= $employee_record->sss_current_balance ?></div>
        </div>  --> 
        <div class="form-item view even">
            <label class="label-desc view gray" for="tin">Tax Status:</label>
            <div class="text-input-wrap"><?= $tax_status ?></div>
        </div>     
<!--         <div class="form-item view odd">
            <label class="label-desc view gray" for="tin">As of:</label>
            <div class="text-input-wrap"><?= $employee_record->sss_balance_date ?></div>
        </div>  --> 
        <div class="form-item view odd">
            <label class="label-desc view gray" for="tin">Pag-Ibig:</label>
            <div class="text-input-wrap"><?= $employee_record->pagibig ?></div>
        </div>         
        <div class="form-item view odd">
            <label class="label-desc view gray" for="tin">Philhealth:</label>
            <div class="text-input-wrap"><?= $employee_record->philhealth ?></div>
        </div>      
        <div class="form-item view even">
            <label class="label-desc view gray" for="tin">Bank Account No:</label>
            <div class="text-input-wrap"><?= $employee_record->bank_account_no ?></div>
        </div>      
<!--         <div class="form-item view odd">
            <label class="label-desc view gray" for="tin">With Existing Loan:</label>
            <div class="text-input-wrap"><?= ($employee_record->pagibig_existing_load == 0 ? "No" : "Yes") ?></div>
        </div>  
        <div class="form-item view odd">
            <label class="label-desc view gray" for="tin">Current Balance:</label>
            <div class="text-input-wrap"><?= $employee_record->pagibig_current_balance ?></div>
        </div>   
        <div class="form-item view odd">
            <label class="label-desc view gray" for="tin">As of:</label>
            <div class="text-input-wrap"><?= $employee_record->pagibig_balance_date ?></div>
        </div>  -->                                  
    </div>
    <div class="clear"></div>
    <div style="height: 40px;"></div>
</div>
