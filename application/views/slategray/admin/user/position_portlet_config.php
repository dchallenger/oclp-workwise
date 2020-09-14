<div class="spacer"></div>
<h3 class="form-head">Portlet Configuration</h3>
<div class="spacer"></div>
<table id="module-access" style="width:100%" class="default-table boxtype">
    <colgroup width="15%"></colgroup>
	<thead>
	    <tr>
	        
            <th rowspan="2" class="vertical-align" width="34%">Portlet</th>
            <th class="even" colspan="2" width="33%">Visible</th>
            <th class="odd" colspan="3" width="33%">Access</th>
	    </tr>
        <tr>
            <th class="even font-smaller" width="16%">Yes</th>
            <th class="odd font-smaller" width="17%">No</th>
            <th class="even font-smaller" width="10%">All Data</th>
            <th class="even font-smaller" width="10%">Group</th>
            <th class="odd font-smaller" width="10%">Personal Only</th>
	    </tr>
    </thead>
    <tbody>
        <?php
        	//get position portlet config
			if( $this->input->post('record_id') != -1 ){
				$this->db->select('portlet_config');
				$portlet_config = $this->db->get_where('user_position', array('position_id' => $this->input->post('record_id')))->row();
				$portlet_config = unserialize($portlet_config->portlet_config);
				if( !is_array($portlet_config) ){
					$portlet_config = array();
				}
			}
			
			//get portlet lisst
			$this->db->order_by('portlet_name');
			$portlets = $this->db->get_where('portlet', array('deleted' => 0));
			if( $portlets->num_rows() > 0 ) :
				foreach($portlets->result_array() as $index => $portlet) : ?>
                    <tr class="<?=($index % 2 == 0 ? "even" : "odd")?>">
                        <th class="text-left module-name" style="border-top: none"><?=$portlet['portlet_name']?></th>
                        <?php
                        	if ( isset($portlet_config[$portlet['portlet_id']]) && $portlet_config[$portlet['portlet_id']]['visible'] == 1){
								$visible = 'checked="checked"';
								$not_visible = '';  
							}
							else{
								$visible = "";
								$not_visible = 'checked="checked"';
							}
						?>
                        <td align="center"><input type="radio" value="1" name="visible[<?=$portlet['portlet_id']?>]" <?=$visible?>/></td>
                        <td align="center"><input type="radio" value="0" name="visible[<?=$portlet['portlet_id']?>]" <?=$not_visible?>/></td>
                        <?php
                        	if ( isset($portlet_config[$portlet['portlet_id']]) && $portlet_config[$portlet['portlet_id']]['access'] == "all"){
								$all = 'checked="checked"';
								$group = '';
								$personal = '';  
							}
							elseif ( isset($portlet_config[$portlet['portlet_id']]) && $portlet_config[$portlet['portlet_id']]['access'] == "group" ){
								$all = '';
								$group = 'checked="checked"';
								$personal = '';
							}
							else{
								$all = '';
								$group = '';
								$personal = 'checked="checked"';
							}
						?>
                        <td align="center"><input type="radio" value="all" name="access[<?=$portlet['portlet_id']?>]" <?=$all?>/></td>
                        <td align="center"><input type="radio" value="group" name="access[<?=$portlet['portlet_id']?>]" <?=$group?>/></td>
                        <td align="center"><input type="radio" value="personal" name="access[<?=$portlet['portlet_id']?>]" <?=$personal?>/></td>    	
                    </tr><?	
				endforeach;
            endif;
        ?>
    </tbody>
</table>
<div class="spacer"></div>