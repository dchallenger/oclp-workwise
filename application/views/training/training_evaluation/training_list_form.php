<?php 

$answer_training_array = array();

if( $answer_training_list->num_rows() > 0 ){
  foreach( $answer_training_list->result() as $answer_training_list_info ){
    array_push($answer_training_array,$answer_training_list_info->training_subject_id);
  }
}


if( $training_list->num_rows() > 0 ){

	foreach( $training_list->result() as $training_list_info ){

	?>
		<tr>
           <td style="text-align:center;"><input type="checkbox" value="<?= $training_list_info->training_subject_id ?>" <?php if( $type == 'detail' ){ echo "disabled"; } ?> <?php if( in_array($training_list_info->training_subject_id, $answer_training_array) ){ echo "checked"; } ?> name="training_list[]"></td>
           <td style="text-align:left;"><?= $training_list_info->training_subject ?></td>
       	</tr>

   	<?php

    }				

}
else{

	?>

	<tr>
           <td style="text-align:center;" colspan="2">No training subject list available</td>
       	</tr>

    <?php

}

?>