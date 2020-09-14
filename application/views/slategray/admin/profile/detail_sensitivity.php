<?php
	$sensitivity = $this->db->get_where('sensitivity', array('deleted' => 0))->result();
	$modules = $this->db->get_where('module', array('deleted' => 0, 'sensitivity_filter' => 1));
	$profile = $this->db->get_where('profile', array( $this->key_field => $this->key_field_val));
	if($profile->num_rows() == 1){
		$profile = $profile->row();
		$sensitivity_set = unserialize($profile->record_sensitivity);
	}
	else{
		$sensitivity_set = array();
	}
?>

<table id="module-access" style="width:100%" class="default-table boxtype">
	<thead>
		<tr>
			<th width="140px" rowspan=2>Module</th>
			<th colspan="4">Can view data that has sensitivity</th>
		</tr>
		<tr>
			<th class="action-name font-smaller even"><div>Low</div></th>
			<th class="action-name font-smaller odd"><div>Medium</div></th>
			<th class="action-name font-smaller even"><div>High</div></th>
			<th class="action-name font-smaller odd"><div>Critical</div></th>
		</tr>
	</thead>
	<tbody><?php
	if($modules->num_rows() > 0){
		$ctr = 0;
		foreach( $modules->result() as $row ){ ?>
			<tr class="<?php echo $ctr % 2 ? 'even':'odd'?>">
				<th style="border-top: none" class="text-left">
					<span class="chk"><?php echo $row->short_name?></span>
        		</th>
        		<td class="odd" align="center"><?php echo isset($sensitivity_set[$row->module_id][1]) ? 'Yes' : 'No' ?></td>
        		<td class="odd" align="center"><?php echo isset($sensitivity_set[$row->module_id][2]) ? 'Yes' : 'No' ?></td>
        		<td class="odd" align="center"><?php echo isset($sensitivity_set[$row->module_id][3]) ? 'Yes' : 'No' ?></td>
        		<td class="odd" align="center"><?php echo isset($sensitivity_set[$row->module_id][4]) ? 'Yes' : 'No' ?></td>
			</tr>
		<?php
			$ctr++;
		}
	}?>
	</tbody>
</table>