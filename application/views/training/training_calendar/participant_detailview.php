<table id="module-participant" style="width:100%;" class="default-table boxtype">
    <colgroup width="15%"></colgroup>
    <thead>
	    <tr>
	        <th style="vertical-align:middle;" class="odd">
	        	Employee Name
	        </th>
	        <th style="vertical-align:middle;" class="odd">
	        	Status
	        </th>
	        <th style="vertical-align:middle;" class="odd">
	        	No Show
	        </th>
            <th style="vertical-align:middle;" class="odd">
                Remarks
            </th>
	    </tr>
    </thead>
    <tbody>
    	<?php 

        $total_confirmed = 0;
        
        foreach( $participant as $participant_info ){ 

            $rand = rand(1,10000);
        ?>
            <tr>

                <td style="text-align:center; vertical-align:middle;"><?= $participant_info['name'] ?></td>
                <td style="text-align:center; vertical-align:middle;">
                    <?php 

                    if( $participant_info['participant_status_id'] == "2" ){
                        $total_confirmed++;
                    }

                    foreach( $participant_status_list as $participant_status ){          

                        ?>
                        <?php if( $participant_status['participant_status_id'] == $participant_info['participant_status_id'] ){ echo  $participant_status['participant_status']; } ?>
                    <?php } ?>
                </td>
                <td style="text-align:center; vertical-align:middle;">
                    <?php
                    	if( $participant_info['no_show'] == 1 ){
                    		echo "Yes"; 
                    	}
                    	elseif( $participant_info['no_show'] == 0 ){
                    		echo "No"; 
                    	}
                    ?>
                </td>
                <td style="text-align:left; vertical-align:middle;"><?=$participant_info['remarks']?></td>
            </tr>

        <?php } ?>
    </tbody>
</table>

<br />

<div class="col-2-form view">    
<div>
    <div class="form-item view odd">
        <label for="total_confirmed" class="label-desc view gray">
            Total Confirmed:
        </label>
        <div class="text-input-wrap"><?php echo $total_confirmed; ?></div>
    </div>
</div>
</div>