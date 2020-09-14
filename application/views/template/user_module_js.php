<script type="text/javascript">
	function User(){
		var user_id = "<?php echo isset($this->user->user_id) ? $this->user->user_id : ''?>";
		var nicename = "<?php echo isset($this->user->user_id) ? $this->userinfo['firstname'].' '.$this->userinfo['lastname'] : 'Anonymous'?>";
		var avatar = "<?php echo isset($this->userinfo['photo']) ? $this->userinfo['photo'] : ''?>";
		var user_theme = "<?php echo $this->userinfo['theme']?>";
		var birth_date = "<?php echo isset($this->userinfo['birth_date']) ? $this->userinfo['birth_date'] : ''?>";
		var idle_time = "<?php echo ($this->config->item('idle_time'))?>";
		<?php if($this->module != 'login' && $this->module != 'design' && $this->module != 'deve
			lopment') foreach($this->user_access[$this->module_id] as $action => $value){echo 'var '. $action .'_control = "'. $value .'";'."\r\n";}?>				
		this.get_value = function( to_get ){
			if( eval( to_get ) == undefined )
				return false;
			else
				return eval( to_get );
		};
	}
	var user = new User();
	
	function Module(){
		var base_url = "<?php echo base_url()?>";
		var fullpath = "<?php echo str_replace("\\", "\\\\", FCPATH)?>";
		var module_id = "<?php echo $this->module_id?>";
		var module_link = "<?php echo $this->module_link?>";
		var view = "<?php echo $method?>";
		var record_id = "<?php echo $this->input->post( 'record_id' ) ? $this->input->post( 'record_id' ) : '-1'?>";
		this.get_value = function( to_get ){
			if( eval( to_get ) == undefined )
				return false;
			else
				return eval( to_get );
		};

		this.set_value = function ( to_set, value ){
			if( eval( to_set ) == undefined ){
				return false;
			}
			else{
				eval( to_set + ' = ' + value );	
				return true;
			}
		}

	}			
	var module = new Module();
	var view = "<?php echo $method?>";
</script>