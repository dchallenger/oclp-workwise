<table id="module-participant" style="width:100%;" class="default-table boxtype">
    <colgroup width="15%"></colgroup>
    <thead>
	    <tr>
	        <th style="vertical-align:middle;" class="odd">
	        	&nbsp;
	        </th>
	        <th style="vertical-align:middle;" class="odd">
	        	Training Subject
	        </th>
    		<th class="odd">
    			<span>&nbsp;</span>
    		</th>
	    </tr>
    </thead>
    <tbody>
    <?php 

    	if( $training_subject_count > 0 ){ 
    		foreach( $training_subject_list as $training_subject_info ){
    ?>
    <tr>
       <td style="text-align:center;"><input type="checkbox" name="training_list[]" value="<?= $training_subject_info['training_subject_id'] ?>" <?php if( $training_subject_info['checked'] == 1 ){ echo "checked"; } ?> <?php if( $training_subject_info['used'] == 1 ){ echo "disabled"; } ?> /></td>
       <td style="text-align:left;"><?= $training_subject_info['training_subject'] ?></td>
   	</tr>
    <?php 
			}
		}
		else{
     ?>
     <tr>
     	<td style="text-align:center;" colspan="2">No Training Subject Available</td>
     </tr>
     <?php } ?>
    </tbody>
</table>

