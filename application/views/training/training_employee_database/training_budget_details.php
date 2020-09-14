<?php 
	if( $employee_training_total > 0 ){
		foreach( $employee_training as $employee_training_info ){
?>

<div class="col-2-form view">     
    <div class="form-item view odd ">
        <label class="label-desc view gray" for="training_course_id">Service Bond:</label>
        <div class="text-input-wrap">
            <?=($employee_training_info['service_bond'] == 1) ? 'Yes' : 'No'?>
        </div>		
    </div>	

    <div class="form-item view even">
        <label class="label-desc view gray" for="start_date">Reallocation From:</label>
        <div class="text-input-wrap">
            <?php
                // $type = $this->db->get_where('training_type', array('training_type_id' => $employee_training_info['allocated'] ))->row();
                // echo $type->training_type . ' Budget';

                if ($employee_training_info['allocated'] == "combined") {
                    $this->db->where('training_type_id !=', $employee_training_info['training_type_id']);
                } else {
                    $this->db->where('training_type_id', $employee_training_info['allocated']);
                }

                $training_types = $this->db->get('training_type');
                
                $training_type = "";
                $reallocate = "";

                if ($training_types && $training_types->num_rows() > 0) {
                    if ($training_types->num_rows() == 1) {
                        $reallocate = $training_types->row()->training_type;
                    } else {
                        foreach ($training_types->result() as $type) {
                            $training_type[] = $type->training_type;
                        }
                        $reallocate = implode(' / ', $training_type);
                    }
                    
                } 

                echo ($reallocate && $reallocate != "")  ? $reallocate . ' Budget' : ''; 
                ?>            
        </div>      
    </div>  

    <div class="form-item view odd">
        <label class="label-desc view gray" for="provider">Investment:</label>
        <div class="text-input-wrap">
            <?= $employee_training_info['investment'] ?>
        </div>      
    </div>  

    <div class="form-item view even">
        <label class="label-desc view gray" for="end_date">Remaining Budget After Reallocation:</label>
        <div class="text-input-wrap">
            <?= number_format($employee_training_info['remaining_allocated'], 2, '.', ',') ?>                  
        </div>      
    </div>  
    
    <div class="form-item view odd">
        <label class="label-desc view gray" for="provider">Not Budgeted / Re-allocation:</label>
        <div class="text-input-wrap">
            <?php 
                switch ($employee_training_info['budgeted']) {
                    case '1':
                        echo 'Not Budgeted';
                        break;
                    case '2':
                        echo 'Re-allocation';
                        break;
                    default:
                       echo " ";
                        break;
                }
            ?>
        </div>      
    </div>  

    <div class="form-item view even">
        <label class="label-desc view gray" for="venue">% IDP Completion after this training:</label>
        <div class="text-input-wrap">
            <?= $employee_training_info['idp_completion'] ?>                  
        </div>		
    </div>	

    <div class="form-item view odd">
        <label class="label-desc view gray" for="provider">Remarks:</label>
        <div class="text-input-wrap">
            <?= $employee_training_info['remarks'] ?>
        </div>      
    </div>  
 
</div>

<table id="module-participant" style="width:100%;" class="default-table boxtype">
    <colgroup width="15%"></colgroup>
    <thead>
        <tr>
            <th style="vertical-align:middle;" class="odd">
                Budget Name
            </th>
            <th style="vertical-align:middle;" class="odd">
                Budget Amount
            </th>
            <th style="vertical-align:middle;" class="odd">
                Remaining Budget
            </th>
            <th style="vertical-align:middle;" class="odd">
                Amount in Excess
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Individual Training Budget</td>
            <td align="center"><?= number_format($employee_training_info['itb'], 2, '.', ',') ?></td>
            <td align="center"><?= number_format($employee_training_info['remaining_itb'], 2, '.', ',') ?></td>
            <td align="center"><?= number_format($employee_training_info['excess_itb'], 2, '.', ',') ?></td>
        </tr>
        <tr>
            <td>Common Training Budget</td>
            <td align="center"><?= number_format($employee_training_info['ctb'], 2, '.', ',') ?></td>
            <td align="center"><?= number_format($employee_training_info['remaining_ctb'], 2, '.', ',') ?></td>
            <td align="center"><?= number_format($employee_training_info['excess_ctb'], 2, '.', ',') ?></td>
        </tr>
        <tr>
            <td>Supplemental Training Budget</td>
            <td align="center"><?= number_format($employee_training_info['stb'], 2, '.', ',') ?></td>
            <td align="center"><?= number_format($employee_training_info['remaining_stb'], 2, '.', ',') ?></td>
            <td align="center"><?= number_format($employee_training_info['excess_stb'], 2, '.', ',') ?></td>
        </tr>

        <tr>
            <td>Total Training Budget</td>
            <td align="center"><?= number_format( ( $employee_training_info['stb'] + $employee_training_info['ctb'] + $employee_training_info['itb']  ), 2, '.', ',') ?></td>
            <td align="center"><?= number_format(( $employee_training_info['remaining_stb'] + $employee_training_info['remaining_ctb'] + $employee_training_info['remaining_itb'] ), 2, '.', ',') ?></td>
            <td align="center"><?= number_format(($employee_training_info['excess_stb'] + $employee_training_info['excess_ctb'] + $employee_training_info['excess_itb']), 2, '.', ',') ?></td>
        </tr>
    </tbody>
</table>





<?php
		}
	}
	else{
	?>

	<div style="text-align:center;" >
		No Record Found
	</div>

	<?php
	}
?>