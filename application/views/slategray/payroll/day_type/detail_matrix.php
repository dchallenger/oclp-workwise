<?php //$this->load->view($this->module_link.'/edit-button')?>
<?php
	$employment_statuses = $this->db->get_where('employment_status', array('deleted' => 0, 'active' => 1));
?>
<select name="employment_status_id" style="width:200px;"><?php
    foreach($employment_statuses->result() as $row){ ?>
        <option value="<?php echo $row->employment_status_id?>"><?php echo $row->employment_status?></option> <?php
    }?>     
</select>
<div id="day-types"></div>
<?php $this->load->view($this->module_link.'/edit-button-matrix')?>

<script>
	$(document).ready(function(){
		$('select[name="employment_status_id"]').chosen();
		$('select[name="employment_status_id"]').change(function(){
			$.ajax({
				url: module.get_value('base_url') + module.get_value('module_link') + "/get_day_type_matrix",
				type:"POST",	
				data: 'employment_status_id='+$('select[name="employment_status_id"]').val(),
				dataType: "json",
				beforeSend: function(){
					$('#day-types').block({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>' });  		
				},
				success: function( data ){
					$('#day-types').unblock();
					$('#day-types').html(data.detail);
				}
			});
		});
		$('select[name="employment_status_id"]').trigger('change');
	});

	function edit_matrix(){
		window.location =  module.get_value('base_url') + module.get_value('module_link') + "/edit_matrix/"+$('select[name="employment_status_id"]').val();
	}
</script>