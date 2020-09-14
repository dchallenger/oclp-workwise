<?php
	$qry = "select a.*, b.employee_health_type
	FROM {$this->db->dbprefix}employee_health a
	LEFT JOIN {$this->db->dbprefix}employee_health_type b on b.employee_health_type_id = a.health_type
	WHERE a.deleted = 0 AND employee_id = {$this->key_field_val}";
	$recs = $this->db->query( $qry );
	if( $recs->num_rows() > 0 ){ ?>
		<table class="default-table boxtype" width="100%">
			<thead>
				<tr>
					<th style="text-align:left;">Type</th>
					<th style="text-align:left;">Date</th>
					<th style="text-align:left;">Health Provider</th>
					<th style="text-align:left;">Diagnosis</th>
					<th style="text-align:left;">Recommendation</th>
					<th style="text-align:left;">Attachments</th>
				</tr>
			</thead>
			<tbody> <?php
				foreach( $recs->result() as $ctr => $rec ) : ?>
					<tr class="<?php echo ($ctr % 2) == 0 ? "even": "odd"?>">
						<td><?php echo $rec->employee_health_type?></td>
						<td><?php echo ($rec->date_of_completion != null ? date($this->config->item('display_date_format'), strtotime($rec->date_of_completion)) : '');?></td>
						<td><?php echo $rec->health_provider?></td>
						<td><?php echo $rec->diagnosis?></td>
						<td><?php echo $rec->recommendation?></td>
						<td><?php
							if( !empty( $rec->content ) ){
								$uploads = explode(',', $rec->content);
								$files = array();
								foreach( $uploads as $upload_id ){
									$file = $this->hdicore->get_file_upload_data($upload_id);
									$files[] = show_file_dl( $file ).' ';
								}
								echo implode(', ', $files);
							} ?>
						</td>
					</tr> <?php
				endforeach; ?>
			</tbody>
		</table> <?php
	}
?>