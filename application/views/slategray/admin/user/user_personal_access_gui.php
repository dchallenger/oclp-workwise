<div id="access-table-div">
<div class="spacer"></div>
<div fg_id="9999" id="fg-9999" class="<?php echo $show_wizard_control ? 'wizard-type-form hidden' : ''?>">
<h3 class="form-head">
	Personal Module Access
	<?php if( !$show_wizard_control ) :?><a style="font-size: 12px; line-height: 18px;" onclick="toggleFieldGroupVisibility( $( this ) );" class="align-right other-link noborder" href="javascript:void(0)">Show</a><?php endif;?>
</h3>
<div class="toggle_personal_access hidden">
    <div class="form-submit-btn align-right nopadding">
        <div class="icon-label-group">
            <div class="icon-label">
                <a class="icon-16-add" href="javascript:void(0)" onclick="addCustomAccess()">
                    <span>Add Custom Access</span>
                </a>            
            </div>
        </div>
    </div>
    <div class="clear"></div> 
    <table id="module-access" style="width:100%" class="default-table boxtype">
        <colgroup width="25%"></colgroup>
        <colgroup width="25%"></colgroup>
        <colgroup width="25%"></colgroup>
        <colgroup width="25%"></colgroup>
        <?php         
            //get current personal access
            if( $this->input->post('record_id') != -1 ){
                $this->db->order_by('module_id');
				$this->db->select('user_access.*, module.short_name');
				$this->db->join('module', 'user_access.module_id = module.module_id', 'left');
				$result = $this->db->get_where('user_access', array('user_id' => $this->input->post('record_id')));
                if ( $result->num_rows() > 0 ) {
                    $user_module_access = $result->result_array();
                } 
                else {
                    $user_module_access = array();
                }
            }else{
                $user_module_access = array();
            }
        ?>
        <thead>
            <tr>
                <th style="vertical-align:middle">Module</th>
                <th style="vertical-align:middle">Module Action</th>
                <th style="vertical-align:middle">Access</th>
                <th style="vertical-align:middle">&nbsp;</th>
            </tr>
        </thead>
        <tbody id="custom-access">
            <?php
				$ctr = 0;
				foreach($user_module_access as $index => $user_access):?>
					<tr class="<?=($ctr % 2 == 0 ? "even" : "odd")?>" id="<?=$user_access['action']?>-<?=$user_access['module_id']?>">
                		<td>
                        	<input name="<?=$user_access['action']?>[<?=$user_access['module_id']?>]" value="<?=$user_access['access']?>" type="hidden">
							<?=$user_access['short_name']?>
                        </td>
                        <td><?=$user_access['action']?></td>
                        <td><?=($user_access['access'] == 1 ? 'Yes' : 'No')?></td>
                        <td>
							<span class="icon-group"><a href="javascript:void(0)" tooltip="Edit" class="icon-button icon-16-edit" onclick="addCustomAccess('<?=$user_access['action']?>-<?=$user_access['module_id']?>-<?=$user_access['access']?>')"></a><a href="javascript:void(0)" tooltip="Delete" class="icon-button icon-16-delete delete-single" onclick="deleteCustomAccess('<?=$user_access['action']?>-<?=$user_access['module_id']?>')"></a></span>
                        </td>
                	</tr><?
					$ctr++;
				endforeach;
			?>   
        </tbody>
    </table>
    <div class="spacer"></div>
</div>
</div>
</div>