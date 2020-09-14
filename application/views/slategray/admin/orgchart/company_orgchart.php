<div class="select-input-wrap">
	Select: 
  <select id="record_id" name="record_id" style="width:50%">
    <option value="">Select…</option>
    <?php
    	$qry =  "SELECT a.*, b.company
			FROM {$this->db->dbprefix}orgchart a
			LEFT JOIN {$this->db->dbprefix}user_company b on b.company_id = a.company_id
			WHERE a.deleted = 0";
			$result = $this->db->query( $qry );
			if( $result->num_rows() > 0 ){
				foreach( $result->result() as $row ){
					echo '<option value="'. $row->orgchart_id .'">'. $row->company .' &nbsp; - &nbsp;  '.$row->description.'</option>';
				}
			}
		?>
  </select>
</div>
<div class="clear"></div>
<div class="ocd-div"></div>
<br /><br />
<div id="chart" class="orgChart"></div>
<br /><br />
<div id="docs" class="docs"></div>

