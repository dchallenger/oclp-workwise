<script type="text/javascript">
	$(document).ready(function () {
		$('.parent>li>ul').hide();

		$('.child-trigger').click(function () {
			$(this).parent().children('ul').toggle('fast');
			if ($(this).text() == '+') {
				$(this).text('-');
				$(this).removeClass('notice');
				$(this).addClass('default');
			} else {
				$(this).text('+');	
				$(this).addClass('notice');
				$(this).removeClass('default');				
			}
		});

		$('.parent-select').click(function () {
			if ($(this).attr('checked') == 'checked') {				
				$(this).parent().children('ul').children().find('input').attr('checked', 'checked');
			} else {
				$(this).parent().children('ul').children().find('input').removeAttr('checked');
			}
		})
	});
</script>

<style>
	.parent { 
		list-style-type: none; 
		margin-left: 0px;
	}

	.parent>li>ul {
		margin-left: 20px;
	}

	.child-trigger { font-weight: bold; }

	.child-trigger:hover {
		cursor: pointer;
	}
</style>

<div class="container">
	<h2>Select Modules to be installed</h2>
	<?php echo form_open('install/create_admin');?>
	<div class="well">	
	<?php
	    list_modules($modules);

	    function list_modules($modules) {
	        $ci =& get_instance();
	        echo '<ul class="parent">';
	        foreach ($modules as $module_id => $module) :            
	        	if (!empty($module['children'])) :
	            	echo '<li>';
	            	echo '<input type="checkbox" class="parent-select" name="module_ids[]" value="' . $module['module_id'] . '"/> ' . $module['short_name'];
	            	echo '&nbsp;<span class="label notice child-trigger">+</span>';
	                echo list_modules($module['children']);
	            ;else:
	            	echo '<li><input type="checkbox" name="module_ids[]" value="' . $module['module_id'] . '"/> ' . $module['short_name'];
	            endif;
	            echo '</li>';            
	        endforeach;
	        echo '</ul>';
	    }
	?>   
	</div> 
	<input type="hidden" name="hash" value="<?php echo $hash;?>" />
	<input class="btn primary" type="submit" value="Next" />
	<?php echo form_close();?>
</div>