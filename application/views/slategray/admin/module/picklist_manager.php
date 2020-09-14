<script type="text/javascript">
	$( document ).ready( function() {
		showPicklistManager();
		$('input[name="picklist_type"]').live('change', function (){
			showPicklistManager();
		});
	});
</script>
<?php
	if( $this->input->post('record_id') != -1 )
	{
		//get picklist details
		$picklist = $this->db->get_where('picklist', array('picklist_id' =>  $this->input->post('record_id') ))->row();
		$id_column = $picklist->picklist_name.'_id';
		$name_column = $picklist->picklist_name;
		$picklist_table = $picklist->picklist_table; ?>
        <div id="picklist-manager" style="display:none">
            <h3 class="form-head">Picklist Values</h3>
            <?php
            	if($picklist->picklist_type == "Table"): ?>
					<div class="form-submit-btn align-right nopadding">
                        <div class="icon-label-group">
                            <div class="icon-label">
                                <a href="javascript:void(0)" class="icon-16-add" onclick="edit_picklist_value('-1', '<?=$name_column?>', '<?=$picklist_table?>')">                        
                                    <span>Add To the List</span>
                                </a>            
                            </div>
                        </div>
                    </div>
				<?
				endif;
			?>
            <div class="clear"></div>
            <table style="width:100%" class="default-table boxtype" id="fieldgroup-list">
                <col width="35%">
                <col width="50%">
                <col width="15%">
                <thead>
                    <tr>
                        <th>Values</th>
                        <th>Description</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="picklist-values"></tbody>
            </table>
            <div class="spacer"></div>
        </div>
<?php
	}
	else{
	
	}
?>